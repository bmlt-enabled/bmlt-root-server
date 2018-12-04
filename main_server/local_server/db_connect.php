<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: http://bmlt.magshare.org

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this code.  If not, see <http://www.gnu.org/licenses/>.
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

    try {
        // Version 1.3 added 1 column to the main meeting table.
        $table = "$dbPrefix"."_comdef_meetings_main";

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
        $table = "$dbPrefix"."_comdef_service_bodies";

        $alter_sql = "ALTER TABLE $table ADD sb_meeting_email VARCHAR(255) DEFAULT NULL";
        c_comdef_dbsingleton::preparedExec($alter_sql);
        $alter_sql = "CREATE INDEX sb_meeting_email ON $table (sb_meeting_email)";
        c_comdef_dbsingleton::preparedExec($alter_sql);
    } catch (Exception $e) {
        // We don't die if the thing already exists. We just mosey on along as if nothing happened.
    }

    try {
        // Version 1.3.6 added 1 column to the main meeting table for meeting-specific email contact.
        $table = "$dbPrefix"."_comdef_meetings_main";

        $alter_sql = "ALTER TABLE $table ADD email_contact VARCHAR(255) DEFAULT NULL";
        c_comdef_dbsingleton::preparedExec($alter_sql);
        $alter_sql = "CREATE INDEX email_contact ON $table (email_contact)";
        c_comdef_dbsingleton::preparedExec($alter_sql);
    } catch (Exception $e) {
        // We don't die if the thing already exists. We just mosey on along as if nothing happened.
    }
        
    try {
        // Version 1.3.6 added 1 column to the meeting data tables for visibility.
        $table = "$dbPrefix"."_comdef_meetings_data";
        $alter_sql = "ALTER TABLE `$table` ADD `visibility` INT( 1 ) NULL DEFAULT NULL AFTER `lang_enum`";
        c_comdef_dbsingleton::preparedExec($alter_sql);
        $alter_sql = "CREATE INDEX visibility ON $table (visibility)";
        c_comdef_dbsingleton::preparedExec($alter_sql);
        $table = "$dbPrefix"."_comdef_meetings_longdata";
        $alter_sql = "ALTER TABLE `$table` ADD `visibility` INT( 1 ) NULL DEFAULT NULL AFTER `lang_enum`";
        c_comdef_dbsingleton::preparedExec($alter_sql);
        $alter_sql = "CREATE INDEX visibility ON $table (visibility)";
        c_comdef_dbsingleton::preparedExec($alter_sql);
    } catch (Exception $e) {
        // We don't die if the thing already exists. We just mosey on along as if nothing happened.
    }

    try {
        // Version 2.11.0 added 1 column to the users table for user ownership.
        $table = "$dbPrefix"."_comdef_users";
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
}
