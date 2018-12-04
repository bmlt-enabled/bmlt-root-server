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

if (isset($http_vars['ajax_req'])        && ($http_vars['ajax_req'] == 'initialize_db')
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
    
    try {
        // We connect the PDO layer:
        c_comdef_dbsingleton::init($http_vars['dbType'], $http_vars['dbServer'], $http_vars['dbName'], $http_vars['dbUser'], $http_vars['dbPassword']);
        
        try {
            // First, we make sure that the database does not already exist. If so, we immediately fail, as we will not overwrite an existing database.
            $result = c_comdef_dbsingleton::preparedQuery('SELECT * FROM '.$db_prefix.$http_vars['dbPrefix'].'_comdef_users WHERE 1', array());
            $response = array ( 'status' => 'false', 'report' => $comdef_install_wizard_strings['AJAX_Handler_DB_Established_Error'] );
        } catch (Exception $e2) {
            $result = null;
        }
        
        if (!isset($result) || !is_array($result) || !count($result)) {
            $value_array = array();
            $response['status'] = true;
            $response['report'] = '';
            foreach ($sql as $sql_statement) {
                c_comdef_dbsingleton::preparedExec($sql_statement, $value_array);
            }
            
            $serveradmin_name = $comdef_install_wizard_strings['ServerAdminName'];
            $serveradmin_desc = $comdef_install_wizard_strings['ServerAdminDesc'];

            $sql_serveradmin = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/serverAdmin.sql'));
            $salt = $http_vars['salt'];
            $max_crypt = true;
            $sql_array = array ( $serveradmin_name, $serveradmin_desc, $http_vars['admin_login'], FullCrypt($http_vars['admin_password'], $salt, $max_crypt), $lang );

            c_comdef_dbsingleton::preparedExec($sql_serveradmin, $sql_array);

            // Formats are special. There are diacriticals that need to be escaped, so we make sure they get set into the values array.
            $sql_temp = str_replace('%%PREFIX%%', preg_replace('|[^a-z_\.\-A-Z0-9]|', '', $db_prefix.$http_vars['dbPrefix']), file_get_contents(dirname(__FILE__).'/sql_files/InitialFormatsData.sql'));
                
            $value_array = array();
            $sql_temp = str_replace("\\'", "`", $sql_temp);
            
            if (preg_match_all("|'(.*?)'|", $sql_temp, $value_array)) {
            }
                {
                $value_array = $value_array[0];
            for ($c = 0; $c < count($value_array); $c++) {
                $value_array[$c] = preg_replace("|'(.*?)'|", "$1", $value_array[$c]);
                $value_array[$c] = str_replace("`", "'", $value_array[$c]);
            }
                
                $sql_temp = preg_replace("|'.*?'|", "?", $sql_temp);
                c_comdef_dbsingleton::preparedExec($sql_temp, $value_array);
                }
        }
        echo array2json($response);
    } catch (Exception $e) {
        die(print_r($e, true));
        $response = array ( 'status' => false, 'report' => $comdef_install_wizard_strings['AJAX_Handler_DB_Connect_Error'] );
        echo array2json($response);
    }
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
        
        if ($http_vars['ajax_req'] == 'test_comprehensive') {
            try {
                $db_prefix = ($http_vars['dbType'] != 'mysql') ? $http_vars['dbName'].'.' : '';
                $result = c_comdef_dbsingleton::preparedQuery('SELECT * FROM '.$db_prefix.$http_vars['dbPrefix'].'_comdef_users WHERE 1', array());
                echo "{'success':false, 'message':'".str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Fail2'])."'}";
            } catch (EXception $e2) {
                echo "{'success':true, 'message':'".str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Success'])."'}";
            }
        } else {
            echo '1';
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
} elseif (isset($http_vars['ajax_req']) && ($http_vars['ajax_req'] == 'initialize_db')) {
    echo array2json(array ( 'status' => false, 'report' => $comdef_install_wizard_strings['AJAX_Handler_DB_Incomplete_Error'] ));
} else {
    if ($http_vars['ajax_req'] == 'test_comprehensive') {
        echo "{'success':false, 'message':'".str_replace("'", "\'", $comdef_install_wizard_strings['Database_TestButton_Fail'])."'}";
    } else {
        echo 'ERROR';
    }
}

ob_end_flush();

die();  // Make sure we stop here.
