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

if ( isset ( $http_vars['ajax_req'] ) && $http_vars['ajax_req'] )
    {
    require_once ( dirname ( __FILE__ ).'/installer_ajax.php');
    }
else
    {
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

    global  $prefs_array, $comdef_install_wizard_strings;

    // This is our preferences state array, and contains the hardwired defaults.
    $prefs_array = array (  'dbName'                        =>  '',
                            'dbUser'                        =>  '',
                            'dbPassword'                    =>  '',
                    
                            'dbType'                        =>  'mysql',
                            'dbServer'                      =>  'localhost',
                            'dbPrefix'                      =>  'na',
                    
                            'comdef_global_language'        =>  $lang,
                            'min_pw_len'                    =>  '6',
                    
                            'number_of_meetings_for_auto'   =>  10,
                            'change_depth_for_meetings'     =>  5,
                            
                            'region_bias'                   =>  $comdef_install_wizard_strings['DefaultRegionBias'],
                            'search_spec_map_center'        =>  $comdef_install_wizard_strings['search_spec_map_center'],
                            'bmlt_title'                    =>  $comdef_install_wizard_strings['TitleTextInitialText'],
                            'banner_text'                   =>  $comdef_install_wizard_strings['BannerTextInitialText'],
                            'comdef_distance_units'         =>  $comdef_install_wizard_strings['DefaultDistanceUnits'],
                            'default_duration'              =>  $comdef_install_wizard_strings['DurationTextInitialText'],
                            'time_format'                   =>  $comdef_install_wizard_strings['time_format'],
                            'change_date_format'            =>  $comdef_install_wizard_strings['change_date_format'],
                    
                            'admin_session_name'            =>  'BMLT_Admin',
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
    die();  // We stop all processing here. This prevents the script from continuing to the rest of the admin section.
    }
?>