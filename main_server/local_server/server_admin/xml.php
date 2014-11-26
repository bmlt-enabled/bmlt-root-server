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
define ( 'BMLT_EXEC', 1 );
require_once ( dirname ( __FILE__ ).'/../../../server/config/get-config.php';

if ( file_exists ( $config_file_path ) )
    {
    include ( $config_file_path );
    }

// We only do this if the capability has been enabled in the auto-config file.
if ( isset ( $g_enable_semantic_admin ) && ($g_enable_semantic_admin == TRUE) )
    {
    require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
    require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2XML.php');
    require_once ( dirname ( __FILE__ ).'/../../client_interface/csv/search_results_csv.php' );

    /***************************************************************************************************************
    ************************************************* MAIN CONTEXT *************************************************
    ***************************************************************************************************************/

    global  $http_vars;
    $http_vars = array_merge ( $_GET, $_POST );
    $url_path = 'http://'.$_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').'/'.dirname ( $_SERVER['PHP_SELF'] ).'/xml.php';
    $lang_enum = '';

    // We use a cookie to store the language pref.
    if ( isset ( $_COOKIE ) && isset ( $_COOKIE['bmlt_admin_lang_pref'] ) && $_COOKIE['bmlt_admin_lang_pref'] )
        {
        $lang_enum = $_COOKIE['bmlt_admin_lang_pref'];
        }

    if ( isset ( $http_vars['lang_enum'] ) && $http_vars['lang_enum'] )
        {
        $lang_enum = $http_vars['lang_enum'];
        }

    $http_vars['lang_enum'] = $lang_enum;       // Quick and dirty way to ensure that this gets properly propagated.
    
    require_once ( dirname ( __FILE__ ).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
    require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
    require_once ( dirname ( __FILE__ ).'/../db_connect.php');
    
    DB_Connect_and_Upgrade ( );

    $server = c_comdef_server::MakeServer();
    
    if ( $server instanceof c_comdef_server )
        {
        ob_start();
        session_start();
        require_once ( dirname ( __FILE__ ).'/c_comdef_admin_xml_handler.class.php' );
        
        ob_end_flush();
        }
    else
        {
        die ( '<h1>NO SERVER!</h1>' );
        }
    }
?>