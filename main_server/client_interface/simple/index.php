<?php
/***********************************************************************/
/**     \file   client_interface/simple/index.php

    \brief  This file is a very simple interface that is designed to return
    a basic XHTML string, in response to a search.
    In order to use this, you need to call: <ROOT SERVER BASE URI>/client_interface/simple/
    with the same parameters that you would send to an advanced search. The results
    will be returned as XHTML data.

    This file can be called from other servers.

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
defined('BMLT_EXEC') or define('BMLT_EXEC', true); // This is a security verifier. Keeps files from being executed outside of the context
require_once(dirname(__FILE__).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once(dirname(__FILE__).'/../csv/csv.php');

$server = c_comdef_server::MakeServer();
$ret = null;

if ($server instanceof c_comdef_server) {
    /*******************************************************************/
    /**
        \brief Queries the local server, and returns processes XHTML.

        This requires that the "switcher=" parameter be set in the GET or
        POST parameters:
            - 'GetSearchResults'
                This returns the search results.

        \returns CSV data, with the first row a key header.
    */
    function parse_redirect_simple(
        &$server    ///< A reference to an instance of c_comdef_server
    ) {
        $result = null;
        $http_vars = array_merge_recursive($_GET, $_POST);
        
        if (!isset($http_vars['lang_enum']) || !$http_vars['lang_enum']) {
            $http_vars['lang_enum'] = $server->GetLocalLang();
        }
        
        // Just to be safe, we override any root passed in. We know where our root is, and we don't need to be told.
        $http_vars['bmlt_root'] = 'http://'.$_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? ':'.$_SERVER['SERVER_PORT'] : '').dirname($_SERVER['SCRIPT_NAME'])."/../../";
        
        switch ($http_vars['switcher']) {
            case 'GetSearchResults':
                $container = (isset($http_vars['container_id']) && $http_vars['container_id']) ? $http_vars['container_id'] : null;
                $result = GetSimpleSearchResults($http_vars, isset($http_vars['block_mode']), $container);
                break;
            
            case 'GetFormats':
                $lang = null;
                
                if (isset($http_vars['lang_enum'])) {
                    $lang = $http_vars['lang_enum'];
                }
                
                unset($http_vars['lang_enum']);
                unset($http_vars['switcher']);
                $container = (isset($http_vars['container_id']) && $http_vars['container_id']) ? $http_vars['container_id'] : null;
                $result = GetSimpleFormats($server, isset($http_vars['block_mode']), $container, $lang, $http_vars);
                break;
            
            default:
                $result = HandleSimpleDefault($http_vars);
                break;
        }
        
        return $result;
    }
    
    /*******************************************************************/
    /**
        \brief  This returns the search results, in whatever form was requested.

        \returns XHTML data. It will either be a table, or block elements.
    */
    function GetSimpleSearchResults(
        $in_http_vars,          ///< The HTTP GET and POST parameters.
        $in_block = false,      ///< If this is true, the results will be sent back as block elements (div tags), as opposed to a table. Default is false.
        $in_container_id = null ///< This is an optional ID for the "wrapper."
    ) {
        $localized_strings = c_comdef_server::GetLocalStrings();
        $original_weekday = -1;
        $current_weekday = -1;
    
        if (!( isset($in_http_vars['geo_width']) && $in_http_vars['geo_width'] ) && isset($in_http_vars['bmlt_search_type']) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset($in_http_vars['advanced_radius']) && isset($in_http_vars['advanced_mapmode']) && $in_http_vars['advanced_mapmode'] && ( floatval($in_http_vars['advanced_radius'] != 0.0) ) && isset($in_http_vars['lat_val']) &&  isset($in_http_vars['long_val']) && ( (floatval($in_http_vars['lat_val']) != 0.0) || (floatval($in_http_vars['long_val']) != 0.0) )) {
            $in_http_vars['geo_width'] = $in_http_vars['advanced_radius'];
        } elseif (!( isset($in_http_vars['geo_width']) && $in_http_vars['geo_width'] ) && isset($in_http_vars['bmlt_search_type']) && ($in_http_vars['bmlt_search_type'] == 'advanced')) {
            $in_http_vars['lat_val'] = null;
            $in_http_vars['long_val'] = null;
        } elseif (!isset($in_http_vars['geo_loc']) || $in_http_vars['geo_loc'] != 'yes') {
            if (!isset($in_http_vars['geo_width'])) {
                $in_http_vars['geo_width'] = 0;
            }
        }
        
        if (!(isset($in_http_vars['sort_key']) && $in_http_vars['sort_key']) && !(isset($in_http_vars['sort_keys']) && $in_http_vars['sort_keys'])) {
            $in_http_vars['sort_key'] = 'time';
        }

        require_once(dirname(__FILE__).'/../csv/search_results_csv.php');
        $results = DisplaySearchResultsCSV($in_http_vars);
        
        $ret = '';
        
        // What we do, is to parse the CSV return. We'll pick out certain fields, and format these into a table or block element return.
        if ($results) {
            if (isset($in_http_vars['single_uri']) && $in_http_vars['single_uri']) {
                $single_uri = $in_http_vars['single_uri'];
            }
            
            // Start by turning the CSV into an array of meeting lines.
            // TODO Fix csv parsing
            $results = explode("\n", $results);

            if (is_array($results) && count($results)) {
                $ret = $in_block ? '<div class="bmlt_simple_meetings_div"'.($in_container_id ? ' id="'.c_comdef_htmlspecialchars($in_container_id).'"' : '').'>' : '<table class="bmlt_simple_meetings_table"'.($in_container_id ? ' id="'.c_comdef_htmlspecialchars($in_container_id).'"' : '').' cellpadding="0" cellspacing="0" summary="Meetings">';
                $keys = preg_replace('|^"|', '', preg_replace('|"$|', '', explode('","', $results[0])));
                $weekday_div = false;
                
                $alt = 1;   // This is used to provide an alternating class.
                // We skip the first line, because that is the field header.
                for ($count = 1; $count < count($results); $count++) {
                    $meeting = $results[$count];
                    
                    if ($meeting) {
                        if ($alt == 1) {
                            $alt = 0;
                        } else {
                            $alt = 1;
                        }
                        
                        $meeting = preg_replace('|^"|', '', preg_replace('|"$|', '', explode('","', $meeting)));
                        if (is_array($meeting) && count($meeting)) {
                            if (count($meeting) > count($keys)) {
                                $keys[] = 'unused';
                            }
                            
                            // This is for convenience. We turn the meeting array into an associative one by adding the keys.
                            $meeting = array_combine($keys, $meeting);
                            $location_borough = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_city_subsection'])));
                            $location_neighborhood = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_neighborhood'])));
                            $location_province = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_province'])));
                            $location_nation = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_nation'])));
                            $location_postal_code_1 = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_postal_code_1'])));
                            $location_municipality = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_municipality'])));
                            $town = '';
                            
                            if ($location_municipality) {
                                if ($location_borough) {
                                    // We do it this verbose way, so we will scrag the comma if we want to hide the town.
                                    $town = "<span class=\"c_comdef_search_results_borough\">$location_borough</span><span class=\"bmlt_separator bmlt_separator_comma c_comdef_search_results_municipality_separator\">, </span><span class=\"c_comdef_search_results_municipality\">$location_municipality</span>";
                                } else {
                                    $town = "<span class=\"c_comdef_search_results_municipality\">$location_municipality</span>";
                                }
                            } elseif ($location_borough) {
                                $town = "<span class=\"c_comdef_search_results_municipality_borough\">$location_borough</span>";
                            }
                            
                            if ($location_province) {
                                if ($town) {
                                    $town .= '<span class="bmlt_separator bmlt_separator_comma c_comdef_search_results_province_separator">, </span>';
                                }
                                
                                $town .= "<span class=\"c_comdef_search_results_province\">$location_province</span>";
                            }
                            
                            if ($location_postal_code_1) {
                                if ($town) {
                                    $town .= '<span class="bmlt_separator bmlt_separator_comma c_comdef_search_results_zip_separator">, </span>';
                                }
                                
                                $town .= "<span class=\"c_comdef_search_results_zip\">$location_postal_code_1</span>";
                            }
                            
                            if ($location_nation) {
                                if ($town) {
                                    $town .= '<span class="bmlt_separator bmlt_separator_comma c_comdef_search_results_nation_separator">, </span>';
                                }
                                
                                $town .= "<span class=\"c_comdef_search_results_nation\">$location_nation</span>";
                            }
                            
                            if ($location_neighborhood) {
                                $town_temp = '';
                                
                                if ($town) {
                                    $town_temp = '<span class="bmlt_separator bmlt_separator_paren bmlt_separator_open_paren bmlt_separator_neighborhood_open_paren"> (</span>';
                                }
                                    
                                $town_temp .= "<span class=\"c_comdef_search_results_neighborhood\">$location_neighborhood</span>";
                                
                                if ($town) {
                                    $town_temp .= '<span class="bmlt_separator bmlt_separator_paren bmlt_separator_close_paren bmlt_separator_neighborhood_close_paren">)</span>';
                                }
                                
                                $town .= $town_temp;
                            }
    
                            $weekday = c_comdef_htmlspecialchars($localized_strings['weekdays'][intval($meeting['weekday_tinyint'])]);
                            $time = BuildMeetingTime($meeting['start_time']);
                            
                            $address = '';
                            $location_text = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_text'])));
                            $street = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_street'])));
                            $info = c_comdef_htmlspecialchars(trim(stripslashes($meeting['location_info'])));
                            
                            if ($location_text) {
                                $address = "<span class=\"bmlt_simple_list_location_text\">$location_text</span>";
                            }
                            
                            if ($street) {
                                if ($address) {
                                    $address .= '<span class="bmlt_separator bmlt_separator_comma bmlt_simple_list_location_street_separator">, </span>';
                                }
                                
                                $address .= "<span class=\"bmlt_simple_list_location_street\">$street</span>";
                            }
                            
                            if ($info) {
                                if ($address) {
                                    $address .= '<span class="bmlt_separator bmlt_separator_space bmlt_simple_list_location_info_separator"> </span>';
                                }
                                
                                $address .= "<span class=\"bmlt_simple_list_location_info\">($info)</span>";
                            }
                            
                            $name = c_comdef_htmlspecialchars(trim(stripslashes($meeting['meeting_name'])));
                            $format = c_comdef_htmlspecialchars(trim(stripslashes($meeting['formats'])));
                            
                            $name_uri = urlencode(htmlspecialchars_decode($name));
                            
                            $map_uri = str_replace("##LONG##", c_comdef_htmlspecialchars($meeting['longitude']), str_replace("##LAT##", c_comdef_htmlspecialchars($meeting['latitude']), str_replace("##NAME##", $name_uri, $localized_strings['comdef_server_admin_strings']['MapsURL'])));
                            
                            if ($time && $weekday && $address) {
                                $meeting_weekday = $meeting['weekday_tinyint'];
                                    
                                if (7 < $meeting_weekday) {
                                    $meeting_weekday = 1;
                                }
                                
                                if (($current_weekday != $meeting_weekday) && $in_block) {
                                    if ($current_weekday != -1) {
                                        $weekday_div = false;
                                        $ret .= '</div>';
                                    }
                                    
                                    $current_weekday = $meeting_weekday;
                                    
                                    $ret .= '<div class="bmlt_simple_meeting_weekday_div_'.$current_weekday.'">';
                                    $weekday_div = true;
                                    if (isset($in_http_vars['weekday_header']) && $in_http_vars['weekday_header']) {
                                        $ret .= '<div id="weekday-start-'.$current_weekday.'" class="weekday-header weekday-index-'.$current_weekday.'">'.htmlspecialchars($weekday).'</div>';
                                    }
                                }
                                
                                $ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_div bmlt_alt_'.intval($alt).'">' : '<tr class="bmlt_simple_meeting_one_meeting_tr bmlt_alt_'.intval($alt).'">';
                                    $ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_town_div">' : '<td class="bmlt_simple_meeting_one_meeting_town_td">';
                                    $ret .= $town;
                                    $ret .= $in_block ? '</div>' : '</td>';
                                    $ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_name_div">' : '<td class="bmlt_simple_meeting_one_meeting_name_td">';
                                    
                                if (isset($single_uri) && $single_uri) {
                                    $ret .= '<a href="'.htmlspecialchars($single_uri).intval($meeting['id_bigint']).'">';
                                }
                                    
                                if ($name) {
                                    $ret .= $name;
                                } else {
                                    $ret .= $localized_strings['comdef_server_admin_strings']['Value_Prompts']['generic'];
                                }
                                    
                                if (isset($single_uri) && $single_uri) {
                                    $ret .= '</a>';
                                }
                                    
                                    $ret .= $in_block ? '</div>' : '</td>';
                                
                                    $ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_time_div">' : '<td class="bmlt_simple_meeting_one_meeting_time_td">';
                                    $ret .= $time;
                                    $ret .= $in_block ? '</div>' : '</td>';
                                
                                    $ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_weekday_div">' : '<td class="bmlt_simple_meeting_one_meeting_weekday_td">';
                                    $ret .= $weekday;
                                    $ret .= $in_block ? '</div>' : '</td>';
                                
                                    $ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_address_div">' : '<td class="bmlt_simple_meeting_one_meeting_address_td">';
                                    $ret .= '<a href="'.$map_uri.'">'.$address.'</a>';
                                    $ret .= $in_block ? '</div>' : '</td>';
                                
                                    $ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_format_div">' : '<td class="bmlt_simple_meeting_one_meeting_format_td">';
                                    $ret .= $format;
                                    $ret .= $in_block ? '</div>' : '</td>';
                                
                                $ret .= $in_block ? '<div class="bmlt_clear_div"></div></div>' : '</tr>';
                            }
                        }
                    }
                }
                
                if ($weekday_div && $in_block) {
                    $ret .= '</div>';
                }
                
                $ret .= $in_block ? '</div>' : '</table>';
            }
        }
        
        return $ret;
    }
    
    /*******************************************************************/
    /** \brief This creates a time string to be displayed for the meeting.
        The display is done in non-military time, and "midnight" and
        "noon" are substituted for 12:59:00, 00:00:00 and 12:00:00

        \returns a string, containing the HTML rendered by the function.
    */
    function BuildMeetingTime( $in_time ///< A string. The value of the time field.
                                )
    {
        $localized_strings = c_comdef_server::GetLocalStrings();
        
        $time = null;
        
        if (($in_time == "00:00:00") || ($in_time >= "23:55:00")) {
            $time = c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_midnight_label']);
        } elseif ($in_time == "12:00:00") {
            $time = c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_noon_label']);
        } else {
            include(dirname(__FILE__).'/../../server/config/get-config.php');
    
            $time = c_comdef_htmlspecialchars(date($time_format, strtotime($in_time)));
        }
        
        return $time;
    }
    
    /*******************************************************************/
    /**
        \brief  This returns the complete formats table.

        \returns XHTML data, with the first row a key header.
    */
    function GetSimpleFormats(
        &$server,                   ///< A reference to an instance of c_comdef_server
        $in_block = false,          ///< If this is true, the results will be sent back as block elements (div tags), as opposed to a table. Default is false.
        $in_container_id = null,    ///< This is an optional ID for the "wrapper."
        $in_lang = null,            ///< The language of the formats to be returned. Default is null (server language). Can be an array.
        $in_search_params = null    ///< If this is supplied, then it is a search parameter list. The idea is that only the formats used in the returned meetings will be displayed.
    ) {
        $my_keys = array (  'key_string',
                            'name_string',
                            'description_string'
                            );
        
        $ret = $in_block ? '<div class="bmlt_simple_format_div"'.($in_container_id ? ' id="'.c_comdef_htmlspecialchars($in_container_id).'"' : '').'>' : '<table class="bmlt_simple_format_table"'.($in_container_id ? ' id="'.c_comdef_htmlspecialchars($in_container_id).'"' : '').' cellpadding="0" cellspacing="0" summary="Format Codes">';
        $formats_ar = array();
        
        if (isset($in_search_params) && is_array($in_search_params) && count($in_search_params)) {
            require_once(dirname(__FILE__).'/../csv/search_results_csv.php');
            $results = GetSearchResults($in_search_params, $formats_ar);
        }

        $formats_obj = $server->GetFormatsObj();
        if ($formats_obj instanceof c_comdef_formats) {
            $langs = $server->GetServerLangs();
            
            if (is_array($in_lang) && count($in_lang)) {
                $langs2 = array();
                foreach ($in_lang as $key) {
                    if (array_key_exists($key, $langs)) {
                        $langs2[$key] = $langs[$key];
                    }
                }
                
                $langs = $langs2;
            } elseif (array_key_exists($in_lang, $langs)) {
                $langs = array ( $in_lang => $langs[$in_lang] );
            }
        
            foreach ($langs as $key => $value) {
                $format_array =  $formats_obj->GetFormatsByLanguage($key);

                if (is_array($format_array) && count($format_array)) {
                    usort($format_array, function ($a, $b) {
                        return strnatcasecmp($a->GetKey(), $b->GetKey());
                    });

                    $alt = 1;   // This is used to provide an alternating style.
                    foreach ($format_array as $format) {
                        $has = false;
                        if ($format instanceof c_comdef_format) {
                            if (($formats_ar != null)) {
                                foreach ($formats_ar as $format_obj) {
                                    if ($format->GetSharedID() && ($format_obj->GetSharedID() == $format->GetSharedID())) {
                                        $has = true;
                                        break;
                                    }
                                }
                            }
                                
                            if ($has) {
                                if ($alt == 1) {
                                    $alt = 0;
                                } else {
                                    $alt = 1;
                                }
                            
                                $ret .= $in_block ? '<div class="bmlt_simple_format_one_format_div bmlt_alt_'.intval($alt).'">' : '<tr class="bmlt_simple_format_one_format_tr bmlt_alt_'.intval($alt).'">';
                                foreach ($my_keys as $ky) {
                                    $ret .= ($in_block ?  '<div' : '<td').' class="';
                                
                                    $val = '';
                                
                                    switch ($ky) {
                                        case 'key_string':
                                            $ret .= 'bmlt_simple_format_one_format_key';
                                            $val = $format->GetKey();
                                            break;
                                    
                                        case 'name_string':
                                            $ret .= 'bmlt_simple_format_one_format_name';
                                            $val = $format->GetLocalName();
                                            break;
                                    
                                        case 'description_string':
                                            $ret .= 'bmlt_simple_format_one_format_description';
                                            $val = $format->GetLocalDescription();
                                            break;
                                    
                                        default:
                                            $ret .= 'bmlt_simple_format_one_format_unknown';
                                            break;
                                    }
                                
                                    $ret .= $in_block ?  '_div">' : '_td">';
                                    $ret .= c_comdef_htmlspecialchars(trim($val));
                                    $ret .= $in_block ?  '</div>' : '</td>';
                                }
                                $ret .= $in_block ? '<div class="bmlt_clear_div"></div></div>' : '</tr>';
                            }
                        }
                    }
                }
            }
        }
        
        $ret .= $in_block ? '</div>' : '</table>';
        
        return $ret;
    }
    
    /*******************************************************************/
    /**
        \brief Handles no command supplied (error)

        \returns English error string (not XML).
    */
    function HandleSimpleDefault(
        $in_http_vars   ///< The HTTP GET and POST parameters.
    ) {
        return "You must supply either 'switcher=GetSearchResults' or 'switcher=GetFormats'";
    }
    
    $ret = parse_redirect_simple($server);
} else {
    $ret = HandleNoServer();
}

$handler = 'ob_gzhandler';

// Server-side includes (and some other implementations) can't handle compressed data in the response. If "nocompress" is specified, then the GZIP handler isn't used.
if (isset($_GET['nocompress']) || isset($_POST['nocompress'])) {
    $handler = null;
}

ob_start($handler);
    $ret = preg_replace('/<!--(.|\s)*?-->/', '', $ret);
    $ret = preg_replace('/\/\*(.|\s)*?\*\//', '', $ret);
    $ret = preg_replace("|\s+\/\/.*|", " ", $ret);
    $ret = preg_replace("/\s+/", " ", $ret);
    echo $ret;
ob_end_flush();
