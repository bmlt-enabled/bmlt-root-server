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
    require_once(dirname(__FILE__)."/../server/shared/classes/VenueType.php");
    
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

    // New-style migrations
    $dbMigrations = array(
        // Add database migrations here. The first item is the database version, the second item is a function to run.
        // Version 1 will never run, and is just an example. The first actual migration will begin with version 2.
        array(1, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
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
        }),
        array(3, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_formats";
            $updateSqlTemplate =  "UPDATE `$table` SET `worldid_mixed` = '%s' WHERE `shared_id_bigint` = %s AND `key_string` = '%s' AND `lang_enum` = 'en' AND `name_string` = '%s' AND `format_type_enum` = '%s'";
            $updateSql = sprintf($updateSqlTemplate, 'CH', '5', 'CH', 'Closed Holidays', 'FC3');
            c_comdef_dbsingleton::preparedExec($updateSql);
            $updateSql = sprintf($updateSqlTemplate, 'NC', '16', 'NC', 'No Children', 'FC3');
            c_comdef_dbsingleton::preparedExec($updateSql);
            $updateSql = sprintf($updateSqlTemplate, 'LC', '51', 'LC', 'Living Clean', 'FC1');
            c_comdef_dbsingleton::preparedExec($updateSql);
        }),
        array(4, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_formats";
            $updateSqlTemplate =  "UPDATE `$table` SET `worldid_mixed` = '%s' WHERE `shared_id_bigint` = %s AND `key_string` = '%s' AND `lang_enum` = 'en' AND `name_string` = '%s' AND `format_type_enum` = '%s'";
            $updateSql = sprintf($updateSqlTemplate, 'NS', '37', 'NS', 'No Smoking', 'FC1');
            c_comdef_dbsingleton::preparedExec($updateSql);
        }),
        array(5, function () {
            return;
        }),
        array(6, function () {
            return;
        }),
        array(7, function () {
            return;
        }),
        array(8, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_meetings_data";
            $check_column_sql = "SELECT COUNT(*) AS count FROM `$table` WHERE `key`='phone_meeting_number' AND meetingid_bigint = 0";
            $rows = c_comdef_dbsingleton::preparedQuery($check_column_sql);
            if (is_array($rows) && count($rows)) {
                $row = $rows[0];
                if (intval($row['count']) == 0) {
                    $sql = "INSERT INTO `$table` (`meetingid_bigint`, `key`, `field_prompt`, `lang_enum`, `visibility`, `data_string`, `data_bigint`, `data_double`) VALUES (0, 'phone_meeting_number', 'Phone Meeting Dial-in Number', 'en', 0, 'Phone Meeting Dial-in Number', NULL, NULL)";
                    c_comdef_dbsingleton::preparedExec($sql);
                }
            }
        }),
        array(9, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_meetings_data";
            $check_column_sql = "SELECT COUNT(*) AS count FROM `$table` WHERE `key`='virtual_meeting_link' AND meetingid_bigint = 0";
            $rows = c_comdef_dbsingleton::preparedQuery($check_column_sql);
            if (is_array($rows) && count($rows)) {
                $row = $rows[0];
                if (intval($row['count']) == 0) {
                    $sql = "INSERT INTO `$table` (`meetingid_bigint`, `key`, `field_prompt`, `lang_enum`, `visibility`, `data_string`, `data_bigint`, `data_double`) VALUES (0, 'virtual_meeting_link', 'Virtual Meeting Link', 'en', 0, 'Virtual Meeting Link', NULL, NULL)";
                    c_comdef_dbsingleton::preparedExec($sql);
                }
            }
        }),
        array(10, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_formats";
            $check = "SELECT COUNT(*) AS count FROM `$table` WHERE `key_string` = 'VM'";
            $check = c_comdef_dbsingleton::preparedQuery($check);
            if (is_array($check) && count($check)) {
                $check = $check[0];
                if (intval($check['count']) == 0) {
                    $next_id = "SELECT MAX(shared_id_bigint) + 1 AS next_id FROM `$table`";
                    $next_id = c_comdef_dbsingleton::preparedQuery($next_id);
                    $next_id = $next_id[0];
                    $next_id = $next_id['next_id'];
                    $langs = array("en", "es", "fa", "fr", "it", "pl", "pt", "sv");
                    foreach ($langs as $lang) {
                        $sql = "INSERT INTO `$table` (`shared_id_bigint`, `key_string`, `icon_blob`, `worldid_mixed`, `lang_enum`,`name_string`, `description_string`, `format_type_enum`) VALUES ($next_id, 'VM', NULL, NULL, '$lang', 'Virtual Meeting', 'Meets Virtually', 'FC2')";
                        c_comdef_dbsingleton::preparedExec($sql);
                    }
                } else {
                    $sql = "UPDATE `$table` SET `description_string` = 'Meets Virtually' WHERE `key_string` = 'VM'";
                    c_comdef_dbsingleton::preparedExec($sql);
                }
            }
        }),
        array(11, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_formats";
            $check = "SELECT COUNT(*) AS count FROM `$table` WHERE `key_string` = 'TC'";
            $check = c_comdef_dbsingleton::preparedQuery($check);
            if (is_array($check) && count($check)) {
                $check = $check[0];
                if (intval($check['count']) == 0) {
                    $next_id = "SELECT MAX(shared_id_bigint) + 1 AS next_id FROM `$table`";
                    $next_id = c_comdef_dbsingleton::preparedQuery($next_id);
                    $next_id = $next_id[0];
                    $next_id = $next_id['next_id'];
                    $langs = array("en", "es", "fa", "fr", "it", "pl", "pt", "sv");
                    foreach ($langs as $lang) {
                        $sql = "INSERT INTO `$table` (`shared_id_bigint`, `key_string`, `icon_blob`, `worldid_mixed`, `lang_enum`,`name_string`, `description_string`, `format_type_enum`) VALUES ($next_id, 'TC', NULL, NULL, '$lang', 'Temporarily Closed', 'Facility is Temporarily Closed', 'O')";
                        c_comdef_dbsingleton::preparedExec($sql);
                    }
                } else {
                    $sql = "UPDATE `$table` SET `description_string` = 'Facility is Temporarily Closed' WHERE `key_string` = 'TC'";
                    c_comdef_dbsingleton::preparedExec($sql);
                }
            }
        }),
        array(12, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_meetings_data";
            $check_column_sql = "SELECT COUNT(*) AS count FROM `$table` WHERE `key`='virtual_meeting_additional_info' AND meetingid_bigint = 0";
            $rows = c_comdef_dbsingleton::preparedQuery($check_column_sql);
            if (is_array($rows) && count($rows)) {
                $row = $rows[0];
                if (intval($row['count']) == 0) {
                    $sql = "INSERT INTO `$table` (`meetingid_bigint`, `key`, `field_prompt`, `lang_enum`, `visibility`, `data_string`, `data_bigint`, `data_double`) VALUES (0, 'virtual_meeting_additional_info', 'Virtual Meeting Additional Info', 'en', 0, 'Virtual Meeting Additional Info', NULL, NULL)";
                    c_comdef_dbsingleton::preparedExec($sql);
                }
            }
        }),
        array(13, function () {
            // Add HY (hybrid) as an officially supported format, and map it to its NAWS format
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_formats";
            $check = "SELECT COUNT(*) AS count FROM `$table` WHERE `key_string` = 'HY'";
            $check = c_comdef_dbsingleton::preparedQuery($check);
            if (is_array($check) && count($check)) {
                $check = $check[0];
                if (intval($check['count']) == 0) {
                    $next_id = "SELECT MAX(shared_id_bigint) + 1 AS next_id FROM `$table`";
                    $next_id = c_comdef_dbsingleton::preparedQuery($next_id);
                    $next_id = $next_id[0];
                    $next_id = $next_id['next_id'];
                    $langs = array("en", "es", "fa", "fr", "it", "pl", "pt", "ru", "sv");
                    foreach ($langs as $lang) {
                        $sql = "INSERT INTO `$table` (`shared_id_bigint`, `key_string`, `icon_blob`, `worldid_mixed`, `lang_enum`,`name_string`, `description_string`, `format_type_enum`) VALUES ($next_id, 'HY', NULL, 'HYBR', '$lang', 'Hybrid Meeting', 'Meets Virtually and In-person', 'FC2')";
                        c_comdef_dbsingleton::preparedExec($sql);
                    }
                } else {
                    $sql = "UPDATE `$table` SET `worldid_mixed` = 'HYBR' WHERE `key_string` = 'HY'";
                    c_comdef_dbsingleton::preparedExec($sql);
                }
            }
        }),
        array(14, function () {
            // Map existing VM formats to the new NAWS format
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_formats";
            $id = "SELECT `shared_id_bigint` FROM `$table` WHERE `key_string` = 'VM' AND `lang_enum` = 'en'";
            $id = c_comdef_dbsingleton::preparedQuery($id);
            if (is_array($id) && count($id)) {
                $id = $id[0];
                $id = $id['shared_id_bigint'];
                $sql = "UPDATE `$table` SET `worldid_mixed` = 'VM' WHERE `shared_id_bigint` = $id";
                c_comdef_dbsingleton::preparedExec($sql);
            }
        }),
        array(15, function () {
            // Map existing TC formats to the new NAWS format
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_formats";
            $id = "SELECT `shared_id_bigint` FROM `$table` WHERE `key_string` = 'TC' AND `lang_enum` = 'en'";
            $id = c_comdef_dbsingleton::preparedQuery($id);
            if (is_array($id) && count($id)) {
                $id = $id[0];
                $id = $id['shared_id_bigint'];
                $sql = "UPDATE `$table` SET `worldid_mixed` = 'TC' WHERE `shared_id_bigint` = $id";
                c_comdef_dbsingleton::preparedExec($sql);
            }
        }),
        array(16, function () {
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_meetings_main";
            $alter_sql = "ALTER TABLE $table ADD COLUMN `time_zone` VARCHAR(40) DEFAULT NULL AFTER `duration_time`";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            $create_sql = "CREATE INDEX `time_zone` ON $table (`time_zone`)";
            c_comdef_dbsingleton::preparedExec($create_sql);
        }),
        array(17, function () {
            // This migration subsumed by 18 (which also adds an additional check that every format for the virtual location
            // types HY, TC, and VM has a key in every language, in addition to the checks that were in this migration).
            return;
        }),
        array(18, function () {
            // Ensure that the database includes the 3 virtual location format types HY, TC, and VM, which are now used
            // for the venue type radio buttons. If they aren't there, add them; and also make sure they map to the
            // correct NAWS formats and format types. If there are multiple formats named say VM (which would be weird
            // but possible), this code will only update the one with the smallest shared id.  Also make sure that every
            // existing format for HY, TC, and VM has a key.
            function fix_formats($key, $naws, $name, $descr, $type)
            {
                $dbPrefix = $GLOBALS['dbPrefix'];
                $table = "$dbPrefix" . "_comdef_formats";
                $langs = array('en', 'dk', 'de', 'es', 'fa', 'fr', 'it', 'pl', 'pt', 'ru', 'sv');
                // if there are additional languages, fix those formats as well
                if (array_key_exists('format_lang_names', $GLOBALS)) {
                    $langs = array_merge($langs, array_keys($GLOBALS['format_lang_names']));
                }
                $id = null;  // this will be the shared_id of the format we're fixing or adding
                // first see if there is an English language version of the format with the correct key (take the one with the smallest shared id if more than one)
                $q1 = "SELECT `shared_id_bigint` FROM `$table` WHERE `key_string` = '$key' AND `lang_enum` = 'en' ORDER BY `shared_id_bigint`";
                $result1 = c_comdef_dbsingleton::preparedQuery($q1);
                if (is_array($result1) && count($result1)) {
                    // English version of the format found
                    $id = $result1[0]['shared_id_bigint'];
                }
                if (!$id) {
                    // No English language version of the format found. See if there is a format with the correct NAWS key; if so, use
                    // that. (This seems safe -- there should not be a format to do something different that would map to that NAWS format.)
                    $q2 = "SELECT `shared_id_bigint` FROM `$table` WHERE `worldid_mixed` = '$naws' ORDER BY `shared_id_bigint`";
                    $result2 = c_comdef_dbsingleton::preparedQuery($q2);
                    if (is_array($result2) && count($result2)) {
                        $id = $result2[0]['shared_id_bigint'];
                    }
                }
                if (!$id) {
                    // No format with the correct NAWS key either.  Next see if there is a format in some other language with the given key; if so, use
                    // that.  This is a bit riskier -- although it is unlikely there would be a format with the correct key that does something different.
                    $q3 = "SELECT `shared_id_bigint` FROM `$table` WHERE `key_string` = '$key' ORDER BY `shared_id_bigint`";
                    $result3 = c_comdef_dbsingleton::preparedQuery($q3);
                    if (is_array($result3) && count($result3)) {
                        $id = $result3[0]['shared_id_bigint'];
                    }
                }
                if (!$id) {
                    // No luck finding an existing format, period. Get a new shared ID.
                    $next_id = "SELECT MAX(shared_id_bigint) + 1 AS next_id FROM `$table`";
                    $next_id = c_comdef_dbsingleton::preparedQuery($next_id);
                    $next_id = $next_id[0];
                    $id = $next_id['next_id'];
                }
                // $id will be the shared ID for the desired format.  First update any existing formats with this $id.
                $sql = "UPDATE `$table` SET `worldid_mixed` = '$naws', `format_type_enum` = '$type' WHERE `shared_id_bigint` = $id";
                c_comdef_dbsingleton::preparedExec($sql);
                // Make sure the key is correct for the English version of this format, and that the key is filled in for all versions.
                // If the key is missing for some language, use the English key.
                $sql = "UPDATE `$table` SET `key_string` = '$key' WHERE `shared_id_bigint` = '$id' AND (`lang_enum` = 'en' OR TRIM(`key_string`)='')";
                c_comdef_dbsingleton::preparedExec($sql);
                // For each language, add a format for that language if there isn't one already.
                // It will be in English - the server admin can translate it later.
                foreach ($langs as $lang) {
                    $q4 = "SELECT * FROM `$table` WHERE `shared_id_bigint` = '$id' AND `lang_enum` = '$lang'";
                    $result4 = c_comdef_dbsingleton::preparedQuery($q4);
                    if (!is_array($result4) || count($result4) == 0) {
                        $sql = "INSERT INTO `$table` (`shared_id_bigint`, `key_string`, `icon_blob`, `worldid_mixed`, `lang_enum`,`name_string`, `description_string`, `format_type_enum`) VALUES ($id, '$key', NULL, '$naws', '$lang', '$name', '$descr', '$type')";
                        c_comdef_dbsingleton::preparedExec($sql);
                    }
                }
            }
            fix_formats('VM', 'VM', 'Virtual Meeting', 'Meets Virtually', 'FC2');
            fix_formats('HY', 'HYBR', 'Hybrid Meeting', 'Meets Virtually and In-person', 'FC2');
            fix_formats('TC', 'TC', 'Temporarily Closed', 'Facility is Temporarily Closed', 'FC2');
        }),
        array(19, function () {
            // Change the name of the TC format from 'Temporarily Closed' to 'Temporarily Closed Facility' for all the versions that are unchanged from the default.
            // This will change the English version, and all versions in other languages that aren't translated yet from English.  (In theory there could be several
            // formats with the TC key in languages other than English, with the English words -- unlikely, but if there are any we convert them as well.)
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_formats";
            $sql = "UPDATE `$table` SET `name_string` = 'Temporarily Closed Facility' WHERE `key_string` = 'TC' AND `name_string` = 'Temporarily Closed' AND `description_string` = 'Facility is Temporarily Closed'";
            c_comdef_dbsingleton::preparedExec($sql);
        }),
        array(20, function () {
            // Add the venue_type column to the meetings table
            $dbPrefix = $GLOBALS['dbPrefix'];
            $table = "$dbPrefix" . "_comdef_meetings_main";
            $alter_sql = "ALTER TABLE `$table` ADD `venue_type` TINYINT(4) UNSIGNED DEFAULT NULL AFTER `weekday_tinyint`";
            c_comdef_dbsingleton::preparedExec($alter_sql);
            $create_sql = "CREATE INDEX `venue_type` ON $table (`venue_type`)";
            c_comdef_dbsingleton::preparedExec($create_sql);
        }),
        array(21, function () {
            // Populate the venue_type based on existing format selections
            // The logic is copied from setFormatCheckboxes in server_admin_javascript.js
            function getFormatId($key)
            {
                $dbPrefix = $GLOBALS['dbPrefix'];
                $formatsTable = $dbPrefix . "_comdef_formats";
                $q1 = "SELECT `shared_id_bigint` FROM `$formatsTable` WHERE `key_string` = '$key' AND `lang_enum` = 'en' ORDER BY `shared_id_bigint`";
                $result1 = c_comdef_dbsingleton::preparedQuery($q1);
                if (is_array($result1) && count($result1)) {
                    // English version of the format found
                    return $result1[0]['shared_id_bigint'];
                }
                return false;
            }

            function getFormatStrFilter($formatId, $hasFormat)
            {
                $filter = "(";
                if ($hasFormat) {
                    $filter .= "`formats` IS NOT NULL AND (";
                    $filter .= "  `formats` = '$formatId' OR ";
                    $filter .= "  `formats` LIKE '$formatId,%' OR ";
                    $filter .= "  `formats` LIKE '%,$formatId' OR ";
                    $filter .= "  `formats` LIKE '%,$formatId,%'";
                    $filter .= ")";
                } else {
                    $filter .= "`formats` IS NULL OR (";
                    $filter .= "  `formats` != '$formatId' AND ";
                    $filter .= "  `formats` NOT LIKE '$formatId,%' AND ";
                    $filter .= "  `formats` NOT LIKE '%,$formatId' AND ";
                    $filter .= "  `formats` NOT LIKE '%,$formatId,%'";
                    $filter .= ")";
                }
                return $filter . ")";
            }

            $VENUE_TYPE_HYBRID = VenueType::HYBRID;
            $VENUE_TYPE_VIRTUAL = VenueType::VIRTUAL;
            $VENUE_TYPE_IN_PERSON = VenueType::IN_PERSON;
            $vmFormatId = getFormatId("VM");
            $hyFormatId = getFormatId("HY");
            $tcFormatId = getFormatId("TC");
            if (!$vmFormatId || !$hyFormatId || !$tcFormatId) {
                // The formats don't exist... not sure how this could happen
                // Also not sure what to do if this happens
                return;
            }

            $dbPrefix = $GLOBALS['dbPrefix'];
            $meetingsTable = $dbPrefix . "_comdef_meetings_main";

            // HYBRID
            // !VM && !TC && HY
            $updateSql = "UPDATE `$meetingsTable` SET `venue_type` = $VENUE_TYPE_HYBRID WHERE `venue_type` IS NULL AND ";
            $updateSql .= getFormatStrFilter($vmFormatId, false) . " AND ";
            $updateSql .= getFormatStrFilter($tcFormatId, false) . " AND ";
            $updateSql .= getFormatStrFilter($hyFormatId, true);
            c_comdef_dbsingleton::preparedExec($updateSql);

            // VIRTUAL
            // VM && TC && !HY
            // In the UI, this is Virtual TC, but not sure if this is actually a useful designation for filtering, so just use VIRTUAL
            $updateSql = "UPDATE `$meetingsTable` SET `venue_type` = $VENUE_TYPE_VIRTUAL WHERE `venue_type` IS NULL AND ";
            $updateSql .= getFormatStrFilter($vmFormatId, true) . " AND ";
            $updateSql .= getFormatStrFilter($tcFormatId, true) . " AND ";
            $updateSql .= getFormatStrFilter($hyFormatId, false);
            c_comdef_dbsingleton::preparedExec($updateSql);

            // VM && !TC && !HY
            $updateSql = "UPDATE `$meetingsTable` SET `venue_type` = $VENUE_TYPE_VIRTUAL WHERE `venue_type` IS NULL AND ";
            $updateSql .= getFormatStrFilter($vmFormatId, true) . " AND ";
            $updateSql .= getFormatStrFilter($tcFormatId, false) . " AND ";
            $updateSql .= getFormatStrFilter($hyFormatId, false);
            c_comdef_dbsingleton::preparedExec($updateSql);

            // IN_PERSON
            // !VM && !TC && !HY
            $updateSql = "UPDATE `$meetingsTable` SET `venue_type` = $VENUE_TYPE_IN_PERSON WHERE `venue_type` IS NULL AND ";
            $updateSql .= getFormatStrFilter($vmFormatId, false) . " AND ";
            $updateSql .= getFormatStrFilter($tcFormatId, false) . " AND ";
            $updateSql .= getFormatStrFilter($hyFormatId, false);
            c_comdef_dbsingleton::preparedExec($updateSql);
        })
    );
    // WHEN ADDING A NEW DATABASE MIGRATION, REMEMBER TO BUMP THE VERSION IN local_server/install_wizard/sql_files/initialDbVersionData.sql

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
