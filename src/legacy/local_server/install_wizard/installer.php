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
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

if (isset($http_vars['ajax_req']) && $http_vars['ajax_req']) {
    require_once(dirname(__FILE__).'/installer_ajax.php');
} else {
    require_once(dirname(__FILE__).'/../../server/shared/Array2Json.php');

    // We do everything we can to ensure that the requested language file is loaded.
    if (file_exists(dirname(__FILE__).'/../server_admin/lang/'.$lang.'/install_wizard_strings.php')) {
        require_once(dirname(__FILE__).'/../server_admin/lang/'.$lang.'/install_wizard_strings.php');
    } else {
        require_once(dirname(__FILE__).'/../server_admin/lang/en/install_wizard_strings.php');
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
                            'min_pw_len'                    =>  $comdef_install_wizard_strings['DefaultPasswordLength'],
                            'number_of_meetings_for_auto'   =>  $comdef_install_wizard_strings['DefaultMeetingCount'],
                            'change_depth_for_meetings'     =>  $comdef_install_wizard_strings['DefaultChangeDepth'],

                            'region_bias'                   =>  $comdef_install_wizard_strings['DefaultRegionBias'],
                            'search_spec_map_center'        =>  $comdef_install_wizard_strings['search_spec_map_center'],
                            'bmlt_title'                    =>  $comdef_install_wizard_strings['TitleTextInitialText'],
                            'banner_text'                   =>  $comdef_install_wizard_strings['BannerTextInitialText'],
                            'comdef_distance_units'         =>  $comdef_install_wizard_strings['DefaultDistanceUnits'],
                            'default_duration_time'         =>  $comdef_install_wizard_strings['DefaultDurationTime'],
                            'enable_language_selector'      =>  false,

                            /* These are "hard-coded," and can be changed later. */
                            'default_duration'              =>  $comdef_install_wizard_strings['DurationTextInitialText'],
                            'time_format'                   =>  $comdef_install_wizard_strings['time_format'],
                            'change_date_format'            =>  $comdef_install_wizard_strings['change_date_format'],
                            'admin_session_name'            =>  'BMLT_Admin'
                            );

    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
        <head>
            <meta http-equiv="content-type" content="text/html; charset=utf-8" />
            <meta http-equiv="Content-Script-Type" content="text/javascript" />
            <title>BMLT Installer</title>
            <link rel="stylesheet" href="<?php echo 'local_server/server_admin/style/install_wizard_styles.css?v='. time() ?>" />
        </head>
        <body>
            <?php require_once(dirname(__FILE__).'/installer_guts.php'); ?>
        </body>
    </html>
    <?php
    die();  // We stop all processing here. This prevents the script from continuing to the rest of the admin section.
}
?>
