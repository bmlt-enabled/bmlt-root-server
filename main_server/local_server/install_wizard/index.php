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
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

$config_file_path = dirname(dirname(dirname(__FILE__))).'/server/config/get-config.php';

if (file_exists($config_file_path)) {
    require_once($config_file_path);
} else {
    die('The Config File Is Missing!');
}

// These are defaults for some of the newer fields.
if (!isset($region_bias) || (null === $region_bias)) {
    $region_bias = 'us';
}

if (!isset($banner_text) || (null === $banner_text)) {
    $banner_text = '';
}

if (!isset($comdef_global_language) || !$comdef_global_language) {
    $comdef_global_language = 'en';
}

// We only invoke the wizard if the configuration has not been done.
if (!(
            isset($dbType) && $dbType
        &&  isset($dbName) && $dbName
        &&  isset($dbServer) && $dbServer
        &&  isset($dbUser) && $dbUser
        &&  isset($dbPassword) && $dbPassword
        &&  isset($dbPrefix) && $dbPrefix
        &&  isset($bmlt_title) && $bmlt_title
        &&  isset($min_pw_len) && $min_pw_len
        &&  isset($search_spec_map_center) && is_array($search_spec_map_center) && count($search_spec_map_center)
        &&  isset($number_of_meetings_for_auto) && $number_of_meetings_for_auto
        &&  isset($time_format) && $time_format
        &&  isset($change_date_format) && $change_date_format
        &&  isset($change_depth_for_meetings) && $change_depth_for_meetings
        &&  isset($admin_session_name)
        &&  isset($comdef_distance_units) && $comdef_distance_units
        )
    ) {
    if (isset($http_vars['lang_enum']) && $http_vars['lang_enum']) {
        $lang = $http_vars['lang_enum'];
    } else {
        $lang = isset($comdef_global_language) && $comdef_global_language ? $comdef_global_language : 'en';
    }
    
    require_once(dirname(__FILE__).'/installer.php');
}
