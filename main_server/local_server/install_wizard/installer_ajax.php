<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

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

    // Our SQL is now ready to be set to the server. We need to use PDO, as that is the abstraction mechanism used by the server.

    $response = array(
        'dbStatus' => false,
        'dbReport' => '',
        'configStatus' => false,
        'configReport' => ''
    );

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
        $sql_temp = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/InitialFormatsData.sql'));

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
        $lines[] = '$dbType = \''     . $http_vars['dbType'] .     '\'; // This is the PHP PDO driver name for your database.';
        $lines[] = '$dbName = \''     . $http_vars['dbName'] .     '\'; // This is the name of the database.';
        $lines[] = '$dbUser = \''     . $http_vars['dbUser'] .     '\'; // This is the SQL user that is authorized for the above database.';
        $lines[] = '$dbPassword = \'' . $http_vars['dbPassword'] . '\'; // This is the password for the above authorized user. Make it a big, ugly hairy one. It is powerful, and there is no need to remember it.';
        $lines[] = '$dbServer = \''   . $http_vars['dbServer'] .   '\'; // This is the host/server for accessing the database.';
        $lines[] = '$dbPrefix = \''   . $http_vars['dbPrefix'] .   '\'; // This is a table name prefix that can be used to differentiate tables used by different root server instances that share the same database.';
        $lines[] = '';
        $lines[] = '// Location and Map settings:';
        $lines[] = '$region_bias = \''              . $http_vars['region_bias']           . '\';     // This is a 2-letter code for a \'region bias,\' which helps Google Maps to figure out ambiguous search queries.';
        $lines[] = '$gkey = \''                     . $http_vars['gkey']                  . '\';     // This is the Google Maps JavaScript API Key, necessary for using Google Maps.';
        $lines[] = '$search_spec_map_center = array(\'longitude\' => ' . $http_vars['search_spec_map_center_longitude'] . ', \'latitude\' => ' . $http_vars['search_spec_map_center_latitude'] . ', \'zoom\' => ' . $http_vars['search_spec_map_center_zoom'] . ');';
        $lines[] = '$comdef_distance_units = \''    . $http_vars['comdef_distance_units'] . '\';';
        $lines[] = '';
        $lines[] = '// Display settings:';
        $lines[] = '$bmlt_title = \''  . $http_vars['bmlt_title']  . '\';';
        $lines[] = '$banner_text = \'' . $http_vars['banner_text'] . '\';';
        $lines[] = '';
        $lines[] = '// Miscellaneous settings:';
        $lines[] = '$comdef_global_language = \''             . $http_vars['$comdef_global_language']              . '\'; // This is the 2-letter code for the default root server localization (will default to \'en\' -English, if the localization is not available).';
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
        $lines[] = '// These are \'hard-coded,\' but can be changed later:';
        $lines[] = '$time_format = \''        . $http_vars['time_format']        . '\';  // The PHP date() format for the times displayed.';
        $lines[] = '$change_date_format = \'' . $http_vars['change_date_format'] . '\';  // The PHP date() format for times/dates displayed in the change records.';
        $lines[] = '$admin_session_name = \'' . $http_vars['admin_session_name'] . '\';  // This is merely the \'tag\' used to identify the BMLT admin session.';
        $lines[] = '';
        file_put_contents($config_path, implode("\n", $lines));
        chmod($config_path, 0644);
        $response['configStatus'] = true;
    } catch (Exception $e) {
        echo array2json($response);
        ob_end_flush();
        die();
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
                echo "{'success':false, 'message':'" . str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Fail2']) . "'}";
            } else {
                echo '0';
            }
        } catch (EXception $e2) {
            if ($http_vars['ajax_req'] == 'test_comprehensive') {
                echo "{'success':true, 'message':'" . str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Success']) . "'}";
            } else {
                echo '1';
            }
        }
    } catch (Exception $e) {
        if ($http_vars['ajax_req'] == 'test_comprehensive') {
            echo "{'success':false, 'message':'".str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Fail'].$e->getMessage())."'}";
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
        echo "{'success':false, 'message':'".str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Fail'])."'}";
    } else {
        echo 'ERROR';
    }
}

ob_end_flush();

die();  // Make sure we stop here.
