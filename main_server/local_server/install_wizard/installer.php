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
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );    // Makes sure that this file is in the correct context.

require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2Json.php');
require_once ( dirname ( __FILE__ ).'/../server_admin/PhpJsonXmlArrayStringInterchanger.inc.php' );

// We do everything we can to ensure that the requested language file is loaded.
if ( file_exists ( dirname ( __FILE__ ).'/../server_admin/lang/'.$lang.'/install_wizard_strings.php' ) )
    {
    require_once ( dirname ( __FILE__ ).'/../server_admin/lang/'.$lang.'/install_wizard_strings.php' );
    }
else
    {
    require_once ( dirname ( __FILE__ ).'/../server_admin/lang/en/install_wizard_strings.php' );
    }

global  $prefs_array;

// If this is an AJAX handler, then we reroute to our AJAX processing script.
if ( isset ( $http_vars['installer_ajax'] ) && isset ( $http_vars['prefs_json'] ) && $http_vars['prefs_json'] )
    {
    $json_tool = new PhpJsonXmlArrayStringInterchanger;
    
    // Unwind the current prefs state from the JSON input.
    $prefs_array = $json_tool->convertJsonToArray ( $http_vars['prefs_json'], true );
    
    require_once ( dirname ( __FILE__ ).'/installer_ajax.php' );
    }
else
    {
    // This is our preferences state array, and contains the hardwired defaults.
    $prefs_array = array (  'dbName'                        =>  'bmlt',
                            'dbUser'                        =>  '',
                            'dbPassword'                    =>  '',
                        
                            'dbType'                        =>  'mysql',
                            'dbServer'                      =>  'localhost',
                            'dbPrefix'                      =>  'na',
                        
                            'bmlt_title'                    =>  'Basic Meeting List Toolbox Administration',
                            'comdef_global_language'        =>  'en',
                            'min_pw_len'                    =>  '6',
                        
                            'region_bias'                   =>  'us',
                            'search_spec_map_center'        =>  array ( 'longitude' => -118.563659, 'latitude' => 34.235918, 'zoom' => 8 ),
                        
                            'number_of_meetings_for_auto'   =>  10,
                            'time_format'                   =>  'g:i A',
                            'change_date_format'            =>  'g:i A, n/j/Y',
                        
                            'change_depth_for_meetings'     =>  5,
                        
                            'banner_text'                   =>  'Administration Login',
                            'admin_session_name'            =>  'BMLT_Admin',
                            'comdef_distance_units'         =>  'mi',
                            'default_duration'              =>  'N.A. Meetings are usually 90 minutes long (an hour and a half), unless otherwise indicated.',
                            );

    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
            <title>BMLT Installer</title>
            <link rel="stylesheet" href="local_server/server_admin/style/install_wizard_styles.css" />
            <script type="text/javascript">var  g_prefs_state = <?php echo array2json ( $prefs_array ) ?>;</script>
            <script type="text/javascript" src="local_server/install_wizard/installer.js"></script>
        </head>
        <body>
            <h1>This Page Is Still Under Construction</h1>
            <h2>Until this install wizard is complete, you must manually place your auto-config.ing.php file at the same level as the main_server directory.</h2>
            <?php require_once ( dirname ( __FILE__ ).'/installer_guts.php' ); ?>
        </body>
    </html>
<?php
    }

die();  // We stop all processing here. This prevents the script from continuing to the rest of the admin section.
?>