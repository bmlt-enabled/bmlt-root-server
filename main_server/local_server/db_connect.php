<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.
*/
/**
    \brief This function checks to make sure the database is correct for the current version.
*/
function DB_Connect_and_Upgrade()
{
    include(dirname(__FILE__)."/../server/config/get-config.php");
    
    if (!isset($dbType)) {
        $dbType = 'mysql';
    }
    
    if (!isset($dbServer)) {
        $dbServer = 'localhost';
    }
    
    if (!isset($dbPrefix)) {
        $dbPrefix = 'na';
    }
    
    c_comdef_dbsingleton::init($dbType, $dbServer, $dbName, $dbUser, $dbPassword, 'utf8');

    // Make sure version table exists
    $versionTableName = $dbPrefix . "_comdef_db_version";
    $sql = "SELECT COUNT(*) as count FROM information_schema.tables WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = '$versionTableName'";
    $rows = c_comdef_dbsingleton::preparedQuery($sql);
    if (is_array($rows) && count($rows)) {
        $row = $rows[0];
        if (intval($row['count']) == 0) {
            $sql = "CREATE TABLE $versionTableName (version INT)";
            c_comdef_dbsingleton::preparedExec($sql);
            $sql = "INSERT INTO $versionTableName VALUES(0)";
            c_comdef_dbsingleton::preparedExec($sql);
        }
    }

    // Get current db version
    $sql = "SELECT version FROM $versionTableName";
    $rows = c_comdef_dbsingleton::preparedQuery($sql);
    $dbVersion = intval($rows[0]['version']);

    // Make sure the first "migration" has been run. Basically, this is just applying the old style "migrations". After this
    // is run, the dbVersion will bet set to 1
    if ($dbVersion == 0) {
        try {
            // Version 1.3 added 1 column to the main meeting table.
            $table = "$dbPrefix" . "_comdef_meetings_main";

            // We start with default 1, to set the existing records, then we change to 0 for future records.
            $alter_sql = "ALTER TABLE $table ADD published TINYINT NOT NULL DEFAULT 1";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            $alter_sql = "ALTER TABLE $table ALTER COLUMN published SET DEFAULT 0";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            // Make sure we can look it up quickly.
            $alter_sql = "CREATE INDEX published ON $table (published)";
            c_comdef_dbsingleton::preparedExec($alter_sql);
        } catch (Exception $e) {
            // We don't die if the thing already exists. We just mosey on along as if nothing happened.
        }

        try {
            // Version 1.3.4 added 1 column to the service body table.
            $table = "$dbPrefix" . "_comdef_service_bodies";

            $alter_sql = "ALTER TABLE $table ADD sb_meeting_email VARCHAR(255) DEFAULT NULL";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            $alter_sql = "CREATE INDEX sb_meeting_email ON $table (sb_meeting_email)";
            c_comdef_dbsingleton::preparedExec($alter_sql);
        } catch (Exception $e) {
            // We don't die if the thing already exists. We just mosey on along as if nothing happened.
        }

        try {
            // Version 1.3.6 added 1 column to the main meeting table for meeting-specific email contact.
            $table = "$dbPrefix" . "_comdef_meetings_main";

            $alter_sql = "ALTER TABLE $table ADD email_contact VARCHAR(255) DEFAULT NULL";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            $alter_sql = "CREATE INDEX email_contact ON $table (email_contact)";
            c_comdef_dbsingleton::preparedExec($alter_sql);
        } catch (Exception $e) {
            // We don't die if the thing already exists. We just mosey on along as if nothing happened.
        }

        try {
            // Version 1.3.6 added 1 column to the meeting data tables for visibility.
            $table = "$dbPrefix" . "_comdef_meetings_data";
            $alter_sql = "ALTER TABLE `$table` ADD `visibility` INT( 1 ) NULL DEFAULT NULL AFTER `lang_enum`";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            $alter_sql = "CREATE INDEX visibility ON $table (visibility)";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            $table = "$dbPrefix" . "_comdef_meetings_longdata";
            $alter_sql = "ALTER TABLE `$table` ADD `visibility` INT( 1 ) NULL DEFAULT NULL AFTER `lang_enum`";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            $alter_sql = "CREATE INDEX visibility ON $table (visibility)";
            c_comdef_dbsingleton::preparedExec($alter_sql);
        } catch (Exception $e) {
            // We don't die if the thing already exists. We just mosey on along as if nothing happened.
        }

        try {
            // Version 2.11.0 added 1 column to the users table for user ownership.
            $table = "$dbPrefix" . "_comdef_users";
            $check_column_sql = "SELECT COUNT(*) AS count FROM information_schema.columns WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = '$table' AND COLUMN_NAME='owner_id_bigint'";
            $rows = c_comdef_dbsingleton::preparedQuery($check_column_sql);
            if (is_array($rows) && count($rows)) {
                $row = $rows[0];
                if (intval($row['count']) == 0) {
                    // Some databases in the wild have an invalid default datetime for the last_access_datetime column, which could cause this migration to fail
                    $alter_sql = "ALTER TABLE `$table` MODIFY COLUMN `last_access_datetime` datetime NOT NULL DEFAULT '1970-01-01 00:00:00'";
                    c_comdef_dbsingleton::preparedExec($alter_sql);
                    $alter_sql = "ALTER TABLE `$table` ADD `owner_id_bigint` BIGINT NOT NULL DEFAULT -1 AFTER `lang_enum`";
                    c_comdef_dbsingleton::preparedExec($alter_sql);
                    $alter_sql = "CREATE INDEX owner_id_bigint ON $table (owner_id_bigint)";
                    c_comdef_dbsingleton::preparedExec($alter_sql);
                }
            }
        } catch (Exception $e) {
            // We don't die if the thing already exists. We just mosey on along as if nothing happened.
        }

        try {
            // Version 2.13.1, update format_enum_type for the default open/closed formats.
            $table = "$dbPrefix" . "_comdef_formats";
            $check_column_sql = "SELECT COUNT(*) AS count FROM `$table` WHERE `shared_id_bigint`=4 AND `key_string`='C' AND `format_type_enum`='FC3'";
            $rows = c_comdef_dbsingleton::preparedQuery($check_column_sql);
            if (is_array($rows) && count($rows)) {
                $row = $rows[0];
                if (intval($row['count']) != 0) {
                    $update_sql = "UPDATE `$table` SET `format_type_enum`='O' WHERE `shared_id_bigint`=4 AND `key_string`='C'";
                    c_comdef_dbsingleton::preparedExec($update_sql);
                }
            }
        } catch (Exception $e) {
        }

        try {
            // Version 2.13.1, update format_enum_type for the default open/closed formats.
            $table = "$dbPrefix" . "_comdef_formats";
            $check_column_sql = "SELECT COUNT(*) AS count FROM `$table` WHERE `shared_id_bigint`=17 AND `key_string`='O' AND `format_type_enum`='FC3'";
            $rows = c_comdef_dbsingleton::preparedQuery($check_column_sql);
            if (is_array($rows) && count($rows)) {
                $row = $rows[0];
                if (intval($row['count']) != 0) {
                    $update_sql = "UPDATE `$table` SET `format_type_enum`='O' WHERE `shared_id_bigint`=17 AND `key_string`='O'";
                    c_comdef_dbsingleton::preparedExec($update_sql);
                }
            }
        } catch (Exception $e) {
        }

        $sql = "UPDATE $versionTableName SET version = 1";
        c_comdef_dbsingleton::preparedExec($sql);
    }

    // New-style migrations
    $dbMigrations = array(
        // Add database migrations here. The first item is the database version, the second item is a function to run.
        // Version 1 will never run, and is just an example. The first actual migration will begin with version 2.
        array(1, function () {
            $sql = "UPDATE test SET myColumn=1";
            c_comdef_dbsingleton::preparedExec($sql);
        })
    );

    foreach ($dbMigrations as $dbMigration) {
        $version = $dbMigration[0];
        if ($dbVersion >= $version) {
            continue;
        }

        c_comdef_dbsingleton::beginTransaction();

        try {
            $func = $dbMigration[1];
            call_user_func($func);
            $sql = "UPDATE $versionTableName SET version = $version";
            c_comdef_dbsingleton::preparedExec($sql);
        } catch (Exception $e) {
            c_comdef_dbsingleton::rollBack();
            echo $e->getMessage();
            die();
        }

        c_comdef_dbsingleton::commit();
    }
}
