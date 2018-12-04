<?php
/***********************************************************************/
/** \file   comdef-config.inc.php
    \brief  The basic configuration file for a CoMDEF server.
*/
    defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.
    defined('BMLT_COOKIE') or define('BMLT_COOKIE', 'bmlt_cookie');    // The base name for the BMLT cookie.
    defined('_LANG_COOKIE_NAME') or define('_LANG_COOKIE_NAME', 'bmlt_admin_lang_pref');                 // The special admin language selector cookie.
    ini_set('display_errors', 0);   // Keep it all in the family.
    
    global  $comdef_global_language;                ///< The local server language enum (Will default to English)
    global  $comdef_global_languages;               ///< This is an array of all available languages.
    global  $comdef_global_more_details_address;    ///< This is a format string for the way the address line is displayed in the "more details" screen.
    global  $comdef_global_list_address;            ///< The same, but for the list.
    
    // This is new for 2.6.2. We have a default set of hunt ranges that can be overidden.
    global  $comdef_map_radius_ranges;
    // These are specified in whatever unit system we are using for our display ('mi' or 'km').
    $comdef_map_radius_ranges = array ( 0.0625, 0.125, 0.1875, 0.25, 0.4375, 0.5, 0.5625, 0.75, 0.8125, 1.0,
                                        1.25, 1.5, 1.75, 2.0, 2.25, 2.5, 2.75, 3.0, 3.25, 3.5, 3.75, 4.0, 4.25, 4.5, 4.75, 5.0,
                                        5.5, 6.0, 6.5, 7.0, 7.5, 8.0, 8.5, 9.0, 9.5, 10.0,
                                        11.0, 12.0, 13.0, 14.0, 15.0,
                                        17.5, 20.0, 22.5, 25.0, 27.5, 30.0,
                                        35.0, 40.0, 45.0, 50.0,
                                        60.0, 70.0, 80.0, 90.0, 100.0,
                                        150, 200 );

    /// We default to English.
    $comdef_global_language = "en";
    $comdef_global_languages = array ();

    global  $_COMDEF_DEBUG; ///< This flag, when set to true, will cause extra debug information to be output.
    $_COMDEF_DEBUG = false;
    /** Uncomment this for debug mode. */
    // $_COMDEF_DEBUG = true;
    
    /** We scan through the "lang" directory, and pick out the available localizations.
        These will be kept in the comdef_global_languages array.
    */
    if ($handle = opendir(dirname(__FILE__).'/../../local_server/server_admin/lang')) {
        while (false !== ($file = readdir($handle))) {
            if (!('.' == $file[0])
                && is_dir(dirname(dirname(dirname(__FILE__))).'/local_server/server_admin/lang/'.$file)
                && file_exists(dirname(dirname(dirname(__FILE__))).'/local_server/server_admin/lang/'.$file.'/name.txt')
                && file_exists(dirname(dirname(dirname(__FILE__))).'/local_server/server_admin/lang/'.$comdef_global_language.'/format_codes.php') ) {
                /// The "name.txt" file contains -ONLY- the language name, in its local language.
                $name = file_get_contents(dirname(dirname(dirname(__FILE__))).'/local_server/server_admin/lang/'.$file.'/name.txt');
                /// Each language has a text file, which has its name, in local language.
                $comdef_global_languages[$file] = $name;
            }
        }
    }
    
    $comdef_global_more_details_address = '@@%%location_text%%, @@%%location_street%%@@ (%%location_info%%)@@, %%location_city_subsection%%@@, %%location_municipality%%@@ (%%location_neighborhood%%)@@, %%location_province%%@@ %%location_postal_code_1%%';
    $comdef_global_list_address =  '@@%%location_text%%, @@%%location_street%% @@%%location_info%%';
    
    /// Include the automatically generated configuration.
    if (file_exists(dirname(__FILE__)."/get-config.php")) {
        include(dirname(__FILE__)."/get-config.php");
    } else {
        throw ( new Exception("No get-config.php file!") );
    }
