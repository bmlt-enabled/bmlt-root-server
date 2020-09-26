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
$report = '';

defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

if (zlib_get_coding_type() === false) {
    ob_start("ob_gzhandler");
} else {
    ob_start();
}

// This contains the PDO database access stuff.
require_once(dirname(__FILE__).'/../../server/classes/c_comdef_dbsingleton.class.php');
require_once(dirname(__FILE__).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once(dirname(__FILE__).'/../../server/shared/Array2Json.php');

function dropEverything($dbPrefix)
{
    $dropSql = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $dbPrefix), file_get_contents(dirname(__FILE__).'/sql_files/dropEverything.sql'));
    $value_array = array();
    c_comdef_dbsingleton::preparedExec($dropSql, $value_array);
}

// return a string for adding to auto-config.inc.php that initializes variable $v
// (this is set up only for string values)
function make_initialization($v, $comment = '')
{
    global $http_vars;
    $c = (empty($comment)) ? '' : ' // ' . $comment;
    return '$' . $v . ' = \'' . addcslashes($http_vars[$v], '\'\\') . '\';' . $c;
}

// Check for whitespace at the beginning or end of the value provided for database information (DataBase User etc),
// and if found return a warning that can be included in the popup notification for a failed database test.
function whitespace_warnings()
{
    global $comdef_install_wizard_strings, $http_vars;
    $warn = '';
    foreach ([['Database_Host', 'dbServer'], ['Table_Prefix', 'dbPrefix'], ['Database_Name', 'dbName'], ['Database_User', 'dbUser'], ['Database_PW', 'dbPassword']] as $pair) {
        $value = $http_vars[$pair[1]];
        if ($value != trim($value)) {
            $field_name = str_replace(':', '', $comdef_install_wizard_strings[$pair[0]]);
            $warn .= '   ' . sprintf($comdef_install_wizard_strings['Database_Whitespace_Note'], $field_name);
        }
    }
    return $warn;
}

// We do everything we can to ensure that the requested language file is loaded.
if (file_exists(dirname(__FILE__).'/../server_admin/lang/'.$lang.'/install_wizard_strings.php')) {
    require_once(dirname(__FILE__).'/../server_admin/lang/'.$lang.'/install_wizard_strings.php');
} else {
    $lang = 'en';
    require_once(dirname(__FILE__).'/../server_admin/lang/en/install_wizard_strings.php');
}

if (isset($http_vars['ajax_req'])        && ($http_vars['ajax_req'] == 'initialize_server')
    &&  isset($http_vars['dbName'])          && $http_vars['dbName']
    &&  isset($http_vars['dbUser'])          && $http_vars['dbUser']
    &&  isset($http_vars['dbPassword'])      && $http_vars['dbPassword']
    &&  isset($http_vars['dbType'])          && $http_vars['dbType']
    &&  isset($http_vars['dbServer'])        && $http_vars['dbServer']
    &&  isset($http_vars['dbPrefix'])        && $http_vars['dbPrefix']
    &&  isset($http_vars['admin_login'])     && $http_vars['admin_login']
    &&  isset($http_vars['admin_password'])  && $http_vars['admin_password']  // This is cleartext, but that can't be helped. This is the only place in the installer where this happens.
     ) {
    $value_array = array();
    $db_prefix = ($http_vars['dbType'] != 'mysql') ? $http_vars['dbName'].'.' : '';

    $sql[] = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/initialMeetingsStructure.sql'));
    $sql[] = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/initialFormatsStructure.sql'));
    $sql[] = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/initialChangesStructure.sql'));
    $sql[] = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/initialServiceBodiesStructure.sql'));
    $sql[] = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/InitialUsersStructure.sql'));
    $sql[] = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/InitialMeetingsData.sql'));
    $sql[] = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/initialDbVersionStructure.sql'));
    $sql[] = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/initialDbVersionData.sql'));

    // Our SQL is now ready to be set to the server. We need to use PDO, as that is the abstraction mechanism used by the server.

    $response = array(
        'dbStatus' => false,
        'dbReport' => '',
        'configStatus' => false,
        'configReport' => '',
        'importStatus' => false,
        'importReport' => ''
    );

    $nawsImport = null;
    // If a NAWS import file is provided, instantiate the importer up front to discover
    // any spreadsheet formatting/validation errors
    if (!empty($_FILES) && isset($_FILES['thefile'])) {
        require_once(__DIR__ . '/../server_admin/NAWSImport.php');

        try {
            $nawsImport = new NAWSImport($_FILES['thefile']['tmp_name'], $http_vars['initialValueForPublished']=='TRUE');
        } catch (Exception $e) {
            $response['importReport'] = $e->getMessage();
            echo array2json($response);
            ob_end_flush();
            die();
        }
    }

    // Initialize Database
    try {
        // We connect the PDO layer:
        c_comdef_dbsingleton::init($http_vars['dbType'], $http_vars['dbServer'], $http_vars['dbName'], $http_vars['dbUser'], $http_vars['dbPassword']);
        
        try {
            // First, we make sure that the database does not already exist. If so, we immediately fail, as we will not overwrite an existing database.
            $result = c_comdef_dbsingleton::preparedQuery('SELECT * FROM '.$db_prefix.$http_vars['dbPrefix'].'_comdef_users WHERE 1', array());
            $response['dbReport'] = $comdef_install_wizard_strings['AJAX_Handler_DB_Established_Error'];
            echo array2json($response);
            ob_end_flush();
            die();
        } catch (Exception $e2) {
            $result = null;
        }

        // Create schema
        $value_array = array();
        foreach ($sql as $sql_statement) {
            c_comdef_dbsingleton::preparedExec($sql_statement, $value_array);
        }

        // Create server admin
        $serveradmin_name = $comdef_install_wizard_strings['ServerAdminName'];
        $serveradmin_desc = $comdef_install_wizard_strings['ServerAdminDesc'];
        $sql_serveradmin = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/serverAdmin.sql'));
        $salt = $http_vars['salt'];
        $max_crypt = true;
        $sql_array = array ( $serveradmin_name, $serveradmin_desc, $http_vars['admin_login'], FullCrypt($http_vars['admin_password'], $salt, $max_crypt), $lang );
        c_comdef_dbsingleton::preparedExec($sql_serveradmin, $sql_array);

        // Create formats
        // Formats are special. There are diacriticals that need to be escaped, so we make sure they get set into the values array.
        foreach (glob(dirname(__FILE__).'/sql_files/InitialFormatsData-*.sql') as $filename) {
            $sql_temp = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents($filename));
            $value_array = array();
            $sql_temp = str_replace("\\'", "`", $sql_temp);
            preg_match_all("|'(.*?)'|", $sql_temp, $value_array);
            $value_array = $value_array[0];
            for ($c = 0; $c < count($value_array); $c++) {
                $value_array[$c] = preg_replace("|'(.*?)'|", "$1", $value_array[$c]);
                $value_array[$c] = str_replace("`", "'", $value_array[$c]);
            }
            $sql_temp = preg_replace("|'.*?'|", "?", $sql_temp);
            c_comdef_dbsingleton::preparedExec($sql_temp, $value_array);
        }
        $response['dbStatus'] = true;
    } catch (Exception $e) {
        $response['dbReport'] = $comdef_install_wizard_strings['AJAX_Handler_DB_Connect_Error'];
        echo array2json($response);
        ob_end_flush();
        die();
    }

    // Initialize Config File
    $config_path = dirname(__FILE__) . '/../../../auto-config.inc.php';
    if (file_exists($config_path)) {
        // For security, if the file already exists, we will not try to write it. This is to
        // prevent malicious actors from using this endpoint to write malicious code after the
        // root server has been set up.
        echo array2json($response);
        ob_end_flush();
        die();
    }

    try {
        $lines = [];
        $lines[] = '<?php';
        $lines[] = 'defined(\'BMLT_EXEC\') or die (\'Cannot Execute Directly\');   // Makes sure that this file is in the correct context.';
        $lines[] = '';
        $lines[] = '// These are the settings created by the installer wizard.';
        $lines[] = '';
        $lines[] = '// Database settings:';
        $lines[] = make_initialization('dbType', 'This is the PHP PDO driver name for your database.');
        $lines[] = make_initialization('dbName', 'This is the name of the database.');
        $lines[] = make_initialization('dbUser', 'This is the SQL user that is authorized for the above database.');
        $lines[] = make_initialization('dbPassword', 'This is the password for the above authorized user. Make it a big, ugly hairy one. It is powerful, and there is no need to remember it.');
        $lines[] = make_initialization('dbServer', 'This is the host/server for accessing the database.');
        $lines[] = make_initialization('dbPrefix', 'This is a table name prefix that can be used to differentiate tables used by different root server instances that share the same database.');
        $lines[] = '';
        $lines[] = '// Location and Map settings:';
        $lines[] = make_initialization('region_bias', 'This is a 2-letter code for a \'region bias,\' which helps Google Maps to figure out ambiguous search queries.');
        $lines[] = make_initialization('gkey', 'This is the Google Maps JavaScript API Key, necessary for using Google Maps.');
        $lines[] = '$search_spec_map_center = array(\'longitude\' => ' . $http_vars['search_spec_map_center_longitude'] . ', \'latitude\' => ' . $http_vars['search_spec_map_center_latitude'] . ', \'zoom\' => ' . $http_vars['search_spec_map_center_zoom'] . ');';
        $lines[] = make_initialization('comdef_distance_units');
        $lines[] = '';
        $lines[] = '// Display settings:';
        $lines[] = make_initialization('bmlt_title');
        $lines[] = make_initialization('banner_text');
        $lines[] = '';
        $lines[] = '// Miscellaneous settings:';
        // the remaining statements to output settings aren't converted to use the make_initialization function since
        // some of them are for non-string values (and none of them will ever involve strings containing single quotes)
        $lines[] = '$comdef_global_language = \''             . $http_vars['comdef_global_language']              . '\'; // This is the 2-letter code for the default root server localization (will default to \'en\' -English, if the localization is not available).';
        $lines[] = '$min_pw_len = '                           . $http_vars['min_pw_len']                           . ';   // The minimum number of characters in a user account password for this root server.';
        $lines[] = '$number_of_meetings_for_auto = '          . $http_vars['number_of_meetings_for_auto']          . ';   // This is an approximation of the number of meetings to search for in the auto-search feature. The higher the number, the wider the radius.';
        $lines[] = '$change_depth_for_meetings = '            . $http_vars['change_depth_for_meetings']            . ';   // This is how many changes should be recorded for each meeting. The higher the number, the larger the database will grow, as this can become quite substantial.";';
        $lines[] = '$default_duration_time = \''              . $http_vars['default_duration_time']                . '\'; // This is the default duration for meetings that have no duration specified.';
        $lines[] = '$g_enable_language_selector = '           . $http_vars['g_enable_language_selector']           . ';   // Set this to TRUE (or 1) to enable a popup on the login screen that allows the administrator to select their language.';
        $lines[] = '$g_enable_semantic_admin = '              . $http_vars['g_enable_semantic_admin']              . ';   // If this is TRUE (or 1), then Semantic Administration for this Server is enabled (Administrators can log in using apps).';
        $lines[] = '$g_defaultClosedStatus = '                . $http_vars['g_defaultClosedStatus']                . ';   // If this is FALSE (or 0), then the default (unspecified) Open/Closed format for meetings reported to NAWS is OPEN. Otherwise, it is CLOSED.';
        $lines[] = '// These reflect the way that we handle contact emails.';
        $lines[] = '$g_enable_email_contact = '               . $http_vars['g_enable_email_contact']               . ';   // If this is TRUE (or 1), then this will enable the ability to contact meeting list contacts via a secure email form.';
        $lines[] = '$include_service_body_admin_on_emails = ' . $http_vars['include_service_body_admin_on_emails'] . ';   // If this is TRUE (or 1), then any emails sent using the meeting contact will include the Service Body Admin contact for the meeting Service body (ignored, if $g_enable_email_contact is FALSE).';
        $lines[] = '$include_every_admin_on_emails = '        . $http_vars['include_every_admin_on_emails']        . ';   // If this is TRUE (or 1), then any emails sent using the meeting contact will include all Service Body Admin contacts (including the Server Administrator) for the meeting (ignored, if $g_enable_email_contact or $include_service_body_admin_on_emails is FALSE).';
        $lines[] = '';
        $lines[] = '//The server languages are supported by default, the langs specified here add to them';
        $lines[] = '$format_lang_names = array(';
        $flnStr = $http_vars['format_lang_names'];
        if (isset($flnStr) && $flnStr!='') {
            $fln = json_decode($flnStr);
            if (is_object($fln)) {
                foreach ($fln as $key => $value) {
                    $lines[] = "'".$key."' => '".$value."',";
                }
            }
        }
        $lines[] = ');';
        $lines[] = '// These are \'hard-coded,\' but can be changed later:';
        $lines[] = '$time_format = \''        . $http_vars['time_format']        . '\';  // The PHP date() format for the times displayed.';
        $lines[] = '$change_date_format = \'' . $http_vars['change_date_format'] . '\';  // The PHP date() format for times/dates displayed in the change records.';
        $lines[] = '$admin_session_name = \'' . $http_vars['admin_session_name'] . '\';  // This is merely the \'tag\' used to identify the BMLT admin session.';
        $lines[] = '';
        if (!file_put_contents($config_path, implode("\n", $lines))) {
            throw new Exception();
        }
        chmod($config_path, 0644);
        $response['configStatus'] = true;
    } catch (Exception $e) {
        if (!is_null($nawsImport)) {
            // If the user was attempting an import, just undo the whole installation when
            // there is a failure to write the configuration file
            dropEverything($http_vars['dbPrefix']);
        }
        echo array2json($response);
        ob_end_flush();
        die();
    }

    // If a NAWS CSV is provided to prime the database, import it
    if (!is_null($nawsImport)) {
        require_once(__DIR__.'/../../server/c_comdef_server.class.php');
        try {
            $server = c_comdef_server::MakeServer();
            $adminLogin = $http_vars['admin_login'];
            $encryptedPassword = $server->GetEncryptedPW($http_vars['admin_login'], $http_vars['admin_password']);
            $_SESSION[$http_vars['admin_session_name']] = "$adminLogin\t$encryptedPassword";
            require_once(__DIR__.'/../server_admin/c_comdef_admin_ajax_handler.class.php');
            $nawsImport->import();
            $response['importStatus'] = true;
        } catch (Exception $e) {
            // Drop all the tables
            dropEverything($http_vars['dbPrefix']);

            // Delete the config file
            unlink($config_path);

            $response['importReport'] = $e->getMessage();
            echo array2json($response);
            ob_end_flush();
            die();
        }
    } else {
        $response['importStatus'] = true;
        $response['importReport'] = 'No CSV was provided, so no meetings were imported.';
    }

    echo array2json($response);
} elseif ((isset($http_vars['ajax_req'])    && ($http_vars['ajax_req'] == 'test') || ($http_vars['ajax_req'] == 'test_comprehensive'))
        &&  isset($http_vars['dbName'])      && $http_vars['dbName']
        &&  isset($http_vars['dbUser'])      && $http_vars['dbUser']
        &&  isset($http_vars['dbPassword'])  && $http_vars['dbPassword']
        &&  isset($http_vars['dbType'])      && $http_vars['dbType']
        &&  isset($http_vars['dbServer'])    && $http_vars['dbServer']
        &&  isset($http_vars['dbPrefix'])    && $http_vars['dbPrefix']
        ) {
    try {
        c_comdef_dbsingleton::init($http_vars['dbType'], $http_vars['dbServer'], $http_vars['dbName'], $http_vars['dbUser'], $http_vars['dbPassword']);
        c_comdef_dbsingleton::connect();

        try {
            $db_prefix = ($http_vars['dbType'] != 'mysql') ? $http_vars['dbName'].'.' : '';
            $result = c_comdef_dbsingleton::preparedQuery('SELECT * FROM '.$db_prefix.$http_vars['dbPrefix'].'_comdef_users WHERE 1', array());
            if ($http_vars['ajax_req'] == 'test_comprehensive') {
                echo "{'success':false, 'message':'" . str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Fail2']) . whitespace_warnings() . "'}";
            } else {
                echo '0';
            }
        } catch (Exception $e2) {
            if ($http_vars['ajax_req'] == 'test_comprehensive') {
                echo "{'success':true, 'message':'" . str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Success']) . whitespace_warnings() . "'}";
            } else {
                echo '1';
            }
        }
    } catch (Exception $e) {
        if ($http_vars['ajax_req'] == 'test_comprehensive') {
            echo "{'success':false, 'message':'".str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Fail'].$e->getMessage()) . whitespace_warnings() . "'}";
        } else {
            echo '-1';
        }
    }
} elseif (isset($http_vars['ajax_req']) && ($http_vars['ajax_req'] == 'test')) {
    echo '-1';
} elseif (isset($http_vars['ajax_req']) && ($http_vars['ajax_req'] == 'initialize_server')) {
    echo array2json(array ( 'dbStatus' => false, 'report' => $comdef_install_wizard_strings['AJAX_Handler_DB_Incomplete_Error'] ));
} else {
    if ($http_vars['ajax_req'] == 'test_comprehensive') {
        echo "{'success':false, 'message':'".str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Fail']). whitespace_warnings() . "'}";
    } else {
        echo 'ERROR';
    }
}

ob_end_flush();

die();  // Make sure we stop here.
