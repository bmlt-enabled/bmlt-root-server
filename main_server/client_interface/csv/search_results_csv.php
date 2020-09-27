<?php
/***********************************************************************/
/**     \file   search_results_csv.php

    \brief  This file represents a View layer of the BMLT MVC pattern. It
    will do a meeting search, and return the results as comma-separated
    values (CSV). It is not an object-oriented file, and is quite simple
    to use. For many people, the procedural View Layer files may be all
    they need to see. The object-oriented stuff is encapsulated within.

    The way you use this file is to call DisplaySearchResultsCSV with an
    array that contains values that specify the search.

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

require_once(dirname(__FILE__).'/common_search.inc.php');
require(dirname(__FILE__).'/../../server/config/get-config.php'); // Just to make sure we have an early copy.

global $g_format_dictionary;    ///< This is a dictionary used to translate formats to NAWS format. It uses the format shared IDs in the server's language.

bmlt_populate_format_dictionary();

/// If you wish to override this, simply set this up in your /get-config.php file. That will supersede this.
/// Actually... this block of code never, ever gets hit because the above call to bmlt_populate_format_dictionary
/// will always set $g_format_dictionary. We should... probably remove it?
if (!isset($g_format_dictionary) || !is_array($g_format_dictionary) || !count($g_format_dictionary)) {
    /// This is the default set.
    /// The right side is the BMLT side, and the left side is the NAWS code. The left side should not be changed.
    $g_format_dictionary = array (
                                'CPT'       => null,
                                'MED'       => null,
                                'QA'        => null,
                                'RA'        => null,
                                'BEG'       => array(1),
                                'BT'        => array(3),
                                'OPEN'      => array(4),
                                'CAN'       => array(6),
                                'CH'        => array(5),
                                'CW'        => array(7),
                                'DISC'      => array(8),
                                'GL'        => array(10),
                                'GP'        => array(52),
                                'IP'        => array(12),
                                'IW'        => array(13),
                                'JFT'       => array(14),
                                'LIT'       => array(36),
                                'M'         => array(15),
                                'CLOSED'    => array(17),
                                'NC'        => array(16),
                                'NS'        => array(37),
                                'SMOK'      => array(25),
                                'SPK'       => array(22),
                                'STEP'      => array(27),
                                'SWG'       => array(23),
                                'TOP'       => array(29),
                                'TRAD'      => array(30),
                                'VAR'       => array(19),
                                'W'         => array(32),
                                'WCHR'      => array(33),
                                'Y'         => array(34)
                                );
}

/*******************************************************************/
/** \brief This reads in the server format codes, and populates the
           format dictionary with the NAWS IDs.
*/
function bmlt_populate_format_dictionary()
{
    global $g_format_dictionary;    ///< This is a dictionary used to translate formats to NAWS format. It uses the format shared IDs in the server's language.
    
    $server = c_comdef_server::MakeServer();
    $localized_strings = c_comdef_server::GetLocalStrings();
    $formats_array = c_comdef_server::GetServer()->GetFormatsObj()->GetFormatsArray();

    foreach ($formats_array['en'] as $format) {
        if ($format instanceof c_comdef_format) {
            $world_id = $format->GetWorldID();
            $shared_id = $format->GetSharedID();
            if ($world_id && $shared_id) {
                if (is_array($g_format_dictionary) && array_key_exists($world_id, $g_format_dictionary)) {
                    array_push($g_format_dictionary[$world_id], $shared_id);
                } else {
                    $g_format_dictionary[$world_id] = array( $shared_id );
                }
            }
        }
    }
}

/*******************************************************************/
/** \brief This function does a search, then builds a CSV result,
    with each row being a meeting. The first row is a row of keys.

    \returns a string, containing CSV data, with the first row a key header.
*/
function DisplaySearchResultsCSV(
    $in_http_vars,
    // The various HTTP GET and POST parameters.
    //    If this is defined and set to 'yes', then that means the client supports AJAX.
    //       - 'supports_ajax'
    //           We serve non-JavaScript content to clients that don't support AJAX, even if they support JavaScript.
    //
    //    The values that are important to the list paging are:
    //       - 'page_num'
    //           This is a positive integer, specifying which page of results to display.
    //
    //       - 'page_size'
    //           This is the number of meetings to list on one "page" of results.
    //           The search results are paged, so that a large search is broken
    //           into multiple pages of page_display_size results.
    //
    //       - 'sort_key'
    //           This is the key to use for sorting. There are three possible values:
    //               - 'town'
    //                   This is sorted by town, borough and neighborhood first, weekday and time second.
    //               - 'weekday'
    //                   This is sorted by weekday first, town, borough and neighborhood second, then time
    //               - 'time'
    //                   This is sorted by weekday first, time, second, then town, borough and neighborhood.
    //
    //       - 'sort_dir'
    //           This is the direction of the sort. It can be one of the following:
    //               - 'asc'
    //                   Ascending, from least to greatest.
    //               - 'desc'
    //                   Descending, from greatest to least.
    //
    //       These are used to specify a search:
    //
    //       - 'services'
    //           This is an array of positive integers.
    //           This is interpreted as an array of integers. Each integer represents the ID of a Service Body.
    //           A positive integer means that the search will look specifically for meetings that contain that
    //           Service Body ID.
    //           If the integer is negative (preceded by a minus sign -), then the criteria will be to look
    //           for meetings that don't contain that ID.
    //           If no 'services' values are given, then the search will not use the Service Body field as a
    //           search criteria.
    //
    //       - 'weekdays'
    //           This is an array of positive integers ( 1-7).
    //           This is interpreted as an array of integers. Each integer represents a weekday (1 -> Sunday, 7 -> Saturday).
    //           A positive integer means that the search will look specifically for meetings that occur on that weekday.
    //           If the integer is negative (preceded by a minus sign -), then the criteria will be to look
    //           for meetings that don't occur on that weekday.
    //           If no 'weekdays' values are given, then the search will not use the weekday field as a
    //           search criteria.
    //
    //       - 'formats'
    //           This is an array of positive integers.
    //           This is interpreted as an array of integers. Each integer represents a format shared ID.
    //           A format ID means that the search will look specifically for meetings that have that format.
    //           If the format is negative (preceded by a minus sign -), then the criteria will be to look
    //           for meetings that don't have that format.
    //           If no 'formats' values are given, then the search will not use the formats field as a
    //           search criteria.
    //
    //       - 'langs'
    //           This is an array of 2-character strings.
    //           This is interpreted as an array of strings. Each string represents a language code, and is a 2-character string.
    //           A language string means that the search will look specifically for meetings that are in that language.
    //           If the language is preceded by a minus sign -, then the criteria will be to look
    //           for meetings that are not in that language.
    //           If no 'langs' values are given, then the search will not use the lang_enum field as a
    //           search criteria.
    //
    //       - 'bmlt_search_type'
    //           This is set to 'advanced' if the search is an advanced one (we need to take more criteria into consideration).
    //
    //       - 'advanced_formats'
    //           This is the formats array, but is only counted if the bmlt_search_type is set to 'advanced'.
    //
    //       - 'advanced_service_bodies'
    //           This is the same, but for Service Bodies.
    //
    //       - 'advanced_weekdays'
    //           ...and weekdays.
    //
    //       - 'advanced_radius'
    //           ...and radius (in degrees)
    //
    //       - advanced_mapmode
    //           If this is true (1), then the Advanced form is using its map.
    //
    //       The following values specify a start time "window." The meeting must start on, or after StartsAfterH/M, and
    //       can start no later than StartsBeforeH/M
    //
    //       - 'StartsAfterH'
    //           A positive integer between 0 and 23. The hour of the minimal start time for meetings, in military time.
    //       - 'StartsAfterM'
    //           A positive integer between 0 and 59. The minute of the minimal start time for meetings, in military time.
    //       - 'StartsBeforeH'
    //           A positive integer between 0 and 23. The hour of the maximal start time for meetings, in military time.
    //       - 'StartsBeforeM'
    //           A positive integer between 0 and 59. The minute of the maximal start time for meetings, in military time.
    //
    //       The following values specify a time duration "window." The meeting can last no longer than MaxDurationH/M,
    //       and no less than MinDurationH/M.
    //
    //       - 'MinDurationH'
    //           A positive integer. This is the number of hours in the minimal duration.
    //       - 'MinDurationM'
    //           A positive integer. This is the number of minutes in the minimal duration.
    //       - 'MaxDurationH'
    //           A positive integer. This is the number of hours in the maximal duration.
    //       - 'MaxDurationM'
    //           A positive integer. This is the number of minutes in the maximal duration.
    //
    //       This is how meetings are located. We don't use address lookups. Instead, we geolocate the meetings via the
    //       longitude and latitude fields in each record. If you don't specify a geolocation, then the entire database
    //       is searched. If you do specify one, then only the portion within the radius is searched.
    //
    //       - 'geo_width'
    //           A floating point number. This is the radius (not diameter) of the search, in MILES (not Kilometers).
    //           If this is negative, then it should be an integer, and that indicates an auto-radius is requested to
    //           find the number of meetings in the integer.
    //
    //       - 'geo_width_km'
    //           A floating point number. This is the radius (not diameter) of the search, in KILOMETERS (not Miles).
    //           If this is negative, then it should be an integer, and that indicates an auto-radius is requested to
    //           find the number of meetings in the integer.
    //
    //       - 'long_val'
    //           If one of the three radius specifiers is zero or undefined, this is ignored.
    //           This is a floating point number that specifies the longitude, in degrees, of the center of the search radius.
    //
    //       - 'lat_val'
    //           If one of the three radius specifiers is zero or undefined, this is ignored.
    //           This is a floating point number that specifies the latitude, in degrees, of the center of the search radius.
    //
    //       - 'SearchString'
    //           A string. If this is specified, then all the string fields of the meetings specified by the above criteria
    //           will be searched for the string. By default, if the language supports metaphone (sound like search), then
    //           that is used.
    //
    //       - 'StringSearchIsAnAddress'
    //           A boolean. Nonzero means that the given string should not be checked against any of the fields in the meeting
    //           data. Instead, it is to be considered a submission to the Google Maps geocode, and will be used to determine
    //           a cernter point in a local search.
    //
    //       - 'SearchStringAll'
    //           If nonzero, then all of the words in the search string will have to be matched for a meetings to qualify.
    //
    //       - 'SearchStringExact'
    //           If nonzero, metaphone will not be used, and the spelling must be exact.
    //
    //       - 'meeting_ids'
    //           An array of positive integers. Each integer is an ID of an individual meeting. If this is set, all other
    //           search criteria are ignored.
    //
    //          - 'sort_keys'
    //              This is a comma-separated list of sort keys. The leftmost one will be the top priority, and the rightmost the lowest.
    //              The sort depth will be the number of keys.
    //              The direction will be assumed 'asc', unless 'desc' is one of the keys (it can be anywhere in the list).
    //
    //          - 'simple_other_fields'
    //              Set this to '1' in order to prevent the server from separating values with the prompt separator.
    &$return_array = null,  ///< If this is supplied, then the result will be saved in this as an array. It must be an empty array, supplied by reference.
    &$return_geocode = null,
    // If this is supplied, the response will be an associative array, with the search center and radius.
    // It will return:
    //  - 'radius' The radius of the search, in Km
    //  - 'longitude' The longitude of the search center
    //  - 'latitude' Th latitude of the search center
    &$return_results = null,    ///< If supplied, should point to an array that will be filled with the actual meeting objects that comprise the result.
    $in_supress_hidden_concat = false,  ///< If true, then hidden fields will not have their prompts encoded
    $in_editor_only = false     ///< If true, then only meetings for which the current logged-in user can edit/observe are returned.
) {
    $ret = null;
    require_once(dirname(__FILE__).'/c_comdef_meeting_search_manager.class.php');

    $search_manager = new c_comdef_meeting_search_manager;
    
    if ($search_manager instanceof c_comdef_meeting_search_manager) {
        $localized_strings = c_comdef_server::GetLocalStrings();

        $lang_enum = c_comdef_server::GetServer()->GetLocalLang();
    
        // This can be changed in the auto config.
        include(dirname(__FILE__).'/../../server/config/get-config.php');
    
        if (isset($in_http_vars['lang_enum']) && $in_http_vars['lang_enum']) {
            $lang_enum = $in_http_vars['lang_enum'];
        }
        
        if (!isset($in_http_vars['results_per_page'])) {
            $in_http_vars['results_per_page'] = 0;
        }
        
        if (isset($default_sort_key) && !isset($in_http_vars['sort_key']) && !isset($in_http_vars['sort_keys'])) {
            $in_http_vars['sort_key'] = $default_sort_key;
        }
    
        if ((!isset($in_http_vars['sort_dir']) || ( ($in_http_vars['sort_dir'] != 'desc') && ($in_http_vars['sort_dir'] != 'asc') ) ) && !isset($in_http_vars['sort_keys'])) {
            $in_http_vars['sort_dir'] = 'asc';
        }
    
        SetUpSearch($search_manager, $in_http_vars);
        
        if (isset($in_http_vars['page_size'])) {
            $search_manager->SetResultsPerPage($in_http_vars['page_size']);
        }
        
        if (isset($in_http_vars['sort_dir'])) {
            $sort_dir_desc = ($in_http_vars['sort_dir'] == "desc") ? true : false;
        
            if (isset($localized_strings['default_sorts']) && isset($in_http_vars['sort_key']) && isset($sort_dir_desc) && !isset($in_http_vars['sort_keys'])) {
                $search_manager->SetSort($localized_strings['default_sorts'][$in_http_vars['sort_key']], $sort_dir_desc, 0);
            }
        }
            
        $search_manager->DoSearch();
        
        $long = null;
        $lat = null;
        
        if (isset($in_http_vars['long_val'])) {
            $long = $in_http_vars['long_val'];
        }
        
        if (isset($in_http_vars['lat_val'])) {
            $lat = $in_http_vars['lat_val'];
        }
        
        if (isset($in_http_vars['geo_width'])) {
            $my_radius = $in_http_vars['geo_width'];
        } elseif (isset($in_http_vars['geo_width_km'])) {
            $my_radius = $in_http_vars['geo_width_km'];
        }
        
        if (isset($my_radius) && ($my_radius < 0)) {
            $my_radius = $search_manager->GetRadius($localized_strings['dist_units'] == 'mi');
        }
        
        if (isset($return_geocode)) {
            $return_geocode = nil;
            
            if ($search_manager->GetRadius(false)) {
                $return_geocode['radius'] = $search_manager->GetRadius(false);
                $return_geocode['longitude'] = $search_manager->GetLongitude();
                $return_geocode['latitude'] = $search_manager->GetLatitude();
            }
        }
        
        $num_pages = $search_manager->GetNumberOfPages();
        $num_results = $search_manager->GetNumberOfResults();
            
        $page_no = 1;
        
        if (isset($in_http_vars['page_num']) && (0 < intval($in_http_vars['page_num']))) {
            $page_no = intval($in_http_vars['page_num']);
        }
        
        if (1 > intval($page_no)) {
            $page_no = 1;
        }
        
        if ($page_no > $num_pages) {
            $page_no = $num_pages;
        }
        
        $page_data = $search_manager->GetPageOfResults($page_no);
        
        if ($page_data instanceof c_comdef_meeting_search_manager) {
            $keys = c_comdef_meeting::GetAllMeetingKeys();
            // This is a required one for data export.
            if (!in_array('meeting_name', $keys)) {
                $keys[] = 'meeting_name';
            }
            
            $keys[] = 'root_server_uri';
            $keys[] = 'format_shared_id_list';
            
            $ret = '"'.join('","', $keys).'"';

            $formats = c_comdef_server::GetServer()->GetFormatsObj();
            $formats_keys = array();
            $formats_keys_header = array();
            
            $ret .= "\n";
       
            $in_ar = $page_data->GetSearchResultsAsArray();
        
            if (isset($return_results) && is_array($return_results)) {
                $return_results = $in_ar;
            }
            
            foreach ($in_ar as &$mtg_obj) {
                $line = array();
                $formats_ar = $formats_keys;
                                    
                if ($mtg_obj instanceof c_comdef_meeting) {
                    if (!$in_editor_only || $mtg_obj->UserCanObserve()) {
                        $format_shared_id_list = array();
                        $first = true;
                        foreach ($keys as $key) {
                            if (trim($key)) {
                                $val = $mtg_obj->GetMeetingDataValue($key);
                    
                                if (($key == 'meeting_name') && !$val) {    // No meeting name results in a generic "NA Meeting" as the name.
                                    $val = $localized_strings['comdef_server_admin_strings']['Value_Prompts']['generic'];
                                }
                            
                                if (isset($val)) {
                                    if (($key == 'formats')) {
                                        if (($key == 'formats') && is_array($val) && count($val)) {
                                            $v_ar = array();
                                            foreach ($val as $format) {
                                                if ($format instanceof c_comdef_format) {
                                                    array_push($v_ar, $format->GetKey());
                                                    array_push($format_shared_id_list, $format->GetSharedID());
                                                }
                                            }
                                            $val = join(',', $v_ar);
                                            $val = preg_replace('|"|', '\\"', preg_replace('|[\r\n\t]+|', ' ', $val));
                                        } elseif (is_string($val)) {
                                            $val = preg_replace('|"|', '\\"', preg_replace('|[\r\n\t]+|', ' ', $val));
                                        }
                                    }
                        
                                    if (($key == 'formats') && $val) {
                                        $f_list = explode(',', $val);
                            
                                        if (is_array($f_list) && count($f_list)) {
                                            foreach ($f_list as $format) {
                                                $formats_ar[$format] = 1;
                                            }
                                        }
                                    }
                            
                                    if ($val) {
                                        if ($mtg_obj->IsItemHidden($key)) {
                                            if ($mtg_obj->UserCanObserve()) {
                                                if (!$in_supress_hidden_concat && !isset($in_http_vars['simple_other_fields'])) {
                                                    $val = preg_replace('|.*?\#\@\-\@\#|', '', $mtg_obj->GetMeetingDataValue($key));    // Strip out any old accidentally introduced separators.
                                                    $val = 'observer_only#@-@#'.$mtg_obj->GetMeetingDataPrompt($key).'#@-@#'.$val;
                                                }
                                            } else {
                                                $val = '';
                                            }
                                        } else {
                                            switch ($key) {
                                                // We don't do anything for the standard fields.
                                                case 'distance_in_miles':
                                                case 'distance_in_km':
                                                case 'id_bigint':
                                                case 'worldid_mixed':
                                                case 'shared_group_id_bigint':
                                                case 'service_body_bigint':
                                                case 'weekday_tinyint':
                                                case 'start_time':
                                                case 'duration_time':
                                                case 'time_zone':
                                                case 'formats':
                                                case 'lang_enum':
                                                case 'longitude':
                                                case 'latitude':
                                                case 'latitude':
                                                case 'published':
                                                case 'email_contact':
                                                case 'meeting_name':
                                                case 'location_text':
                                                case 'location_info':
                                                case 'location_street':
                                                case 'location_city_subsection':
                                                case 'location_neighborhood':
                                                case 'location_municipality':
                                                case 'location_sub_province':
                                                case 'location_province':
                                                case 'location_postal_code_1':
                                                case 'location_nation':
                                                case 'comments':
                                                case 'virtual_meeting_link':
                                                case 'virtual_meeting_additional_info':
                                                case 'phone_meeting_number':
                                                    break;
                                    
                                                // The rest get the prompt/value treatment, unless otherwise requested.
                                                default:
                                                    if ($val && !isset($in_http_vars['simple_other_fields'])) {
                                                        $val = preg_replace('|.*?\#\@\-\@\#|', '', $val);    // Strip out any old accidentally introduced separators.
                                                        $val = $mtg_obj->GetMeetingDataPrompt($key).'#@-@#'.$val;
                                                    }
                                                    break;
                                            }
                                        }
                                    }
                                } else {
                                    $val = '';
                                }
                    
                                $val = trim(preg_replace("|[\n\r]+|", "; ", $val));
                        
                                $line[$key] = $val;
                            }
                        }
                    
                        if (!isset($line['duration_time']) || !$line['duration_time'] || ($line['duration_time'] == '00:00:00')) {
                            $line['duration_time'] = $localized_strings['default_duration_time'];
                        }
                        
                        if (isset($format_shared_id_list) && is_array($format_shared_id_list) && count($format_shared_id_list)) {
                            sort($format_shared_id_list);
                            $line['format_shared_id_list'] = implode(',', $format_shared_id_list);
                        }
                            
                        $line['root_server_uri'] = dirname(dirname(GetURLToMainServerDirectory(true)));
                        
                        if (is_array($line) && count($line)) {
                            if (is_array($return_array)) {
                                array_push($return_array, $line);
                            }
                
                            $ret .= '"'.join('","', $line).'"';
                    
                            $ret .= "\n";
                        }
                    }
                }
            }
        }
    }

    return $ret;
}

/********************************************************************/
/*                      NAWS LIST GENERATION                        */
/* The following functions are used to generate a CSV file that is  */
/* in a format suitable for NA World Services (NAWS). Because they  */
/* often change their format, it needs to be extremely flexible.    */
/* The heart is a "translator dictionary," that matches fields in a */
/* standard meeting object to the fields expected by NAWS. If the   */
/* content of a dictionary entry is a function, then a translation  */
/* is done by calling a function. Otherwise, if the content is a    */
/* field name, the contents of that field are simply transferred    */
/* without interpretation.                                          */
/********************************************************************/

/*******************************************************************/
/**
    \brief Returns the CSV file in NAWS format

    \returns A string, consisting of a CSV file, in the format required by NAWS.
*/
function ReturnNAWSFormatCSV(
    $in_http_vars,  ///< The HTTP GET and POST parameters.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    // This is a dictionary that is used to translate the meeting data from the BMLT format to the NAWS format.
    $transfer_dictionary = array(   'Committee'          => 'BMLT_FuncNAWSReturnMeetingNAWSID',
                                    'CommitteeName'      => 'meeting_name',
                                    'AddDate'            => null,
                                    'AreaRegion'         => 'BMLT_FuncNAWSReturnMeetingServiceBodyNAWSID',
                                    'ParentName'         => 'BMLT_FuncNAWSReturnMeetingServiceBodyName',
                                    'ComemID'            => null,
                                    'ContactID'          => null,
                                    'ContactName'        => null,
                                    'CompanyName'        => null,
                                    'ContactAddrID'      => null,
                                    'ContactAddress1'    => null,
                                    'ContactAddress2'    => null,
                                    'ContactCity'        => null,
                                    'ContactState'       => null,
                                    'ContactZip'         => null,
                                    'ContactCountry'     => null,
                                    'ContactPhone'       => null,
                                    'MeetingID'          => null,
                                    'Room'               => 'BMLT_FuncNAWSReturnNonNawsFormats',
                                    'Closed'             => 'BMLT_FuncNAWSReturnOpenOrClosed',
                                    'WheelChr'           => 'BMLT_FuncNAWSReturnWheelchair',
                                    'Day'                => 'BMLT_FuncNAWSReturnWeekday',
                                    'Time'               => 'BMLT_FuncNAWSReturnTime',
                                    'Language1'          => 'BMLT_FuncNAWSReturnLanguage1',
                                    'Language2'          => null,
                                    'Language3'          => null,
                                    'LocationId'         => null,
                                    'Place'              => 'location_text',
                                    'Address'            => 'location_street',
                                    'City'               => 'BMLT_FuncNAWSReturnMeetingTown',
                                    'LocBorough'         => 'location_neighborhood',
                                    'State'              => 'location_province',
                                    'Zip'                => 'location_postal_code_1',
                                    'Country'            => 'location_nation',
                                    'Directions'         => 'BMLT_FuncNAWSReturnDirections',
                                    'Institutional'      => 'BMLT_FuncNAWSReturnInst',
                                    'Format1'            => 'BMLT_FuncNAWSReturnFormat1',
                                    'Format2'            => 'BMLT_FuncNAWSReturnFormat2',
                                    'Format3'            => 'BMLT_FuncNAWSReturnFormat3',
                                    'Format4'            => 'BMLT_FuncNAWSReturnFormat4',
                                    'Format5'            => 'BMLT_FuncNAWSReturnFormat5',
                                    'Delete'             => null,
                                    'LastChanged'        => 'BMLT_FuncNAWSReturnLastMeetingChangeTime',
                                    'Longitude'          => 'longitude',
                                    'Latitude'           => 'latitude',
                                    'ContactGP'          => null,
                                    'PhoneMeetingNumber' => 'phone_meeting_number',
                                    'VirtualMeetingLink' => 'virtual_meeting_link',
                                    'VirtualMeetingInfo' => 'virtual_meeting_additional_info',
                                    'TimeZone'           => 'time_zone',
                                    'bmlt_id'            => 'id_bigint',
                                    'unpublished'        => 'BMLT_FuncNAWSReturnPublishedStatus'
                                );
    
    $ret = null;

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
    $ret_array = array ();  // If we supply an array as a second parameter, we will get the dump returned in a two-dimensional array.
    DisplaySearchResultsCSV($in_http_vars, $ret_array);  // Start off by getting the CSV dump in the same manner as the normal CSV dump.

    if (is_array($ret_array) && count($ret_array)) {
        $ret = '"'.join('","', array_keys($transfer_dictionary)).'"'; // This is the header line.
        foreach ($ret_array as $one_meeting) {
            if (is_array($one_meeting) && count($one_meeting)) {
                $line = array();
                foreach ($transfer_dictionary as $key => $value) {
                    // See if this is a function.
                    if (function_exists($value) && is_callable($value) && preg_match('|^BMLT_FuncNAWSReturn|', $value)) {
                        $value = $value($one_meeting['id_bigint'], $server);
                    } elseif (isset($one_meeting[$value])) {   // See if we just transfer the value with no change.
                        $value = $one_meeting[$value];
                    }
                    array_push($line, $value);
                }

                if (is_array($line) && count($line)) {
                    $ret .= "\n".'"'.join('","', $line).'"';
                }
            }
        }
    }

    $del_meetings = ReturnNAWSDeletedMeetings($server, $transfer_dictionary, $in_http_vars['services']); // We append deleted meetings to the end.
    
    if (is_array($del_meetings) && count($del_meetings)) {
        foreach ($del_meetings as $one_meeting) {
            if (is_array($one_meeting) && count($one_meeting)) {
                $ret .= "\n".'"'.join('","', $one_meeting).'"';
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns deleted meetings with NAWS IDs.

    \description This queries every deleted meeting. The meetings returned are
    not restricted to the search parameters, and may repeat from previous dumps.
    Only meetings that had World IDs are returned.

    \returns An array of World IDs and change dates. These each represent deleted meetings.
*/
function ReturnNAWSDeletedMeetings(
    &$server,                   ///< A reference to an instance of c_comdef_server
    $in_transfer_dictionary,    ///< The transfer dictionary
    $in_services                ///< Any Service body IDs
) {
    $ret = null;
    
    // We start by getting all the meetings that have been deleted (Could be quite a few).
    $changes = $server->GetChangesFromOTypeAndCType('c_comdef_meeting', 'comdef_change_type_delete');

    if ($changes instanceof c_comdef_changes) {
        $ret = array();
        $c_array = $changes->GetChangesObjects();

        if (is_array($c_array) && count($c_array)) {
            foreach ($c_array as &$change) {
                $b_obj = $change->GetBeforeObject();
                if ($b_obj instanceof c_comdef_meeting) {
                    $line = null;
                    if (!$server->GetOneMeeting($b_obj->GetID())) {  // Must be currently deleted.
                        if (is_array($in_services) && count($in_services)) {
                            $found = false;
                            reset($in_services);
                            foreach ($in_services as $sb_id) {
                                if (!$found) {
                                    if (intval($b_obj->GetServiceBodyID()) == intval($sb_id)) {
                                        $found = true;
                                    }
                                }
                            }
                        }
                            
                        $value = intval(preg_replace('|\D*?|', '', $b_obj->GetMeetingDataValue('worldid_mixed')));
                        
                        if ($value && $found) {
                            foreach ($in_transfer_dictionary as $key => $value2) {
                                if (($key != 'Delete')) {
                                    $value1 = null;
                                    // See if this is a function.
                                    if (function_exists($value2) && is_callable($value2) && preg_match('|^BMLT_FuncNAWSReturn|', $value2)) {
                                        if ($value2 == 'BMLT_FuncNAWSReturnLastMeetingChangeTime') {
                                            $value1 =  date('n/j/y', $change->GetChangeDate());
                                        } else {
                                            $value1 = $value2($b_obj, $server);
                                        }
                                    } else // See if we just transfer the value with no change.
                                        {
                                        $value1 = $b_obj->GetMeetingDataValue($value2);
                                    }
                                } else {
                                    $value1 = 'D';
                                }
                                
                                $line[$key] = $value1;
                            }
                        }
                    }
                    
                    array_push($ret, $line);
                }
            }
        }
    }
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns '' or '1', if the meeting is unpublished or not (used for the NAWS format)

    \returns A string, '' or '1'.
*/
function BMLT_FuncNAWSReturnPublishedStatus(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID). This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = '';
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }

    if ($the_meeting instanceof c_comdef_meeting) {
        $ret = $the_meeting->IsPublished() ? '' : '1';
    }
        
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns 'OPEN' or 'CLOSED', if the meeting is open or closed (used for the NAWS format)

    \returns A string, 'OPEN' or 'CLOSED'.
*/
function BMLT_FuncNAWSReturnOpenOrClosed(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID). This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    global $g_format_dictionary;
    
    $localized_strings = c_comdef_server::GetLocalStrings();

    $ret = $localized_strings['default_closed_status'] ? 'CLOSED' : 'OPEN'; // This is the default closed/open status.
    $opposite = $localized_strings['default_closed_status'] ? 'OPEN' : 'CLOSED';
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    $ids = $g_format_dictionary[$opposite];

    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');
        
        if (is_array($formats) && count($formats) && is_array($ids)) {
            foreach ($ids as $id) {
                if (isset($formats[$id])) {
                    $ret = $opposite;
                    break;
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns 'TRUE' or 'FALSE', if the meeting is or is not wheelchair-accessible (used for the NAWS format)

    \returns A string, 'TRUE' or 'FALSE'.
*/
function BMLT_FuncNAWSReturnWheelchair(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    global $g_format_dictionary;
    
    $ret = 'FALSE';
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    $ids = $g_format_dictionary['WCHR'];
            
    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');
        
        if (is_array($formats) && count($formats) && is_array($ids)) {
            foreach ($ids as $id) {
                if (isset($formats[$id])) {
                    $ret = 'TRUE';
                    break;
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns 'TRUE' or 'FALSE', if the meeting is or is not an institution meeting (used for the NAWS format)

    \returns A string, 'TRUE' or 'FALSE' (It will always be FALSE).
*/
function BMLT_FuncNAWSReturnInst(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = 'FALSE';
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the weekday the meeting gathers (used for the NAWS format)

    \returns A string ('Monday' - 'Friday').
*/
function BMLT_FuncNAWSReturnWeekday(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = null;
    
    $weekdays = array ( null, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $ret = $weekdays[$the_meeting->GetMeetingDataValue('weekday_tinyint')];
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the weekday the meeting gathers (used for the NAWS format)

    \returns A string (the time, in pure military time - no seconds).
*/
function BMLT_FuncNAWSReturnTime(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = null;
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $ret = explode(':', $the_meeting->GetMeetingDataValue('start_time'));
        if (is_array($ret) && count($ret) > 1) {
            $ret = $ret[0].$ret[1];
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the first alternative lanuage (if any)

    \returns A string.
*/
function BMLT_FuncNAWSReturnLanguage1(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = '';

    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }

    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');
        $lang = $server->GetLocalLang();
        
        if (is_array($formats) && count($formats)) {
            foreach ($formats as $format) {
                if ($format instanceof c_comdef_format) {
                    if ('LANG' == $format->GetWorldID()) {
                        $ret = strtoupper(trim($format->GetKey()));
                        break;
                    }
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the NAWS ID for the meeting (used for the NAWS format)

    \returns A string The meeting ID, in NAWS form (G0000000).
*/
function BMLT_FuncNAWSReturnMeetingNAWSID(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = null;
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $world_id = trim($the_meeting->GetMeetingDataValue('worldid_mixed'));
        $is_olm = (preg_match("/^OLM/", $world_id) != false);
        $world_id = preg_replace('|\D*?|', '', $world_id);
        $world_id = intval($world_id);
        if ($world_id) {
            if ($is_olm) {
                $ret = sprintf('OLM%06d', $world_id);
            } else {
                $ret = sprintf('G%08d', $world_id);
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the town field the meeting (used for the NAWS format). This may use the borough name, instead.

    \returns A string The meeting town.
*/
function BMLT_FuncNAWSReturnMeetingTown(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = null;
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        // Our first choice is the borough/ku.
        $ret = trim($the_meeting->GetMeetingDataValue('location_city_subsection'));

        if (!$ret) {
            $ret = trim($the_meeting->GetMeetingDataValue('location_municipality'));
        }
        
        // If all else fails, we use the neighborhood.
        if (!$ret) {
            $ret = trim($the_meeting->GetMeetingDataValue('location_neighborhood'));
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the latest changed date for the given meeting.

    \returns a date in ISO form ('2013-01-31').
*/
function BMLT_FuncNAWSReturnLastMeetingChangeTime(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = null;

    $changes_obj = $server->GetChangesFromIDAndType('c_comdef_meeting', $in_meeting_id);
    
    if ($changes_obj instanceof c_comdef_changes) {
        $changes_objects = $changes_obj->GetChangesObjects();
        
        if (is_array($changes_objects) && count($changes_objects)) {
            $last_date = 0;
            foreach ($changes_objects as $change) {
                $last_date = max($last_date, $change->GetChangeDate());
            }
                
            if ($last_date) {
                $ret = date('n/j/y', $last_date);
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the NAWS ID for the meeting's Service Body (used for the NAWS format)

    \returns A string The Service Body ID, in NAWS form (RG/AR0000000).
*/
function BMLT_FuncNAWSReturnMeetingServiceBodyNAWSID(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = null;
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $service_body = $the_meeting->GetServiceBodyObj();

        $ret2 = intval(preg_replace('|\D*?|', '', trim($service_body->GetWorldID())));
        
        if ($service_body instanceof c_comdef_service_body) {
            if ($service_body->GetSBType() == c_comdef_service_body__ASC__) {
                if ($ret2) {
                    $ret = sprintf('AR%05d', $ret2);
                }
            } elseif ($service_body->GetSBType() == c_comdef_service_body__RSC__) {
                if ($ret2) {
                    $ret = sprintf('RG%03d', $ret2);
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
\brief Returns a string of all formats that don't map to NAWS codes.

\returns A string The format codes name_string.
 */
function BMLT_FuncNAWSReturnNonNawsFormats(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {

    $ret = "";

    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }

    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');

        if (is_array($formats) && count($formats)) {
            foreach ($formats as $format) {
                if ($format != null && !$format->GetWorldID()) {
                    $ret .= $format->GetLocalName();
                    $ret .= ',';
                }
            }

            $ret = rtrim($ret, ',');
        }
    }

    return $ret;
}

/*******************************************************************/
/**
\brief Returns a string of location_info and comments fields.

\returns A string The location_info and comments fields.
 */
function BMLT_FuncNAWSReturnDirections(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {

    $ret = "";

    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }

    if ($the_meeting instanceof c_comdef_meeting) {
        $ret = trim($the_meeting->GetMeetingDataValue('location_info'));

        if ($the_meeting->GetMeetingDataValue('comments')) {
            if ($ret) {
                $ret .= ", ";
            }
            $ret .= trim($the_meeting->GetMeetingDataValue('comments'));
        }
    }

    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the first format (used for the NAWS format)

    \returns A string The format code, in NAWS form.
*/
function BMLT_FuncNAWSReturnFormat1(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    global $g_format_dictionary;
    $ret = null;
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');
        
        if (is_array($formats) && count($formats)) {
            foreach ($g_format_dictionary as $n_format => $b_formats) {
                foreach ($b_formats as $b_format) {
                    if (($n_format != 'OPEN') && ($n_format != 'CLOSED') && ($n_format != 'WCHR')) {
                        if (isset($formats[$b_format])) {
                            $ret = $n_format;
                            break;
                        }
                    }
                }
                if ($ret) {
                    break;
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the second format (used for the NAWS format)

    \returns A string The format code, in NAWS form.
*/
function BMLT_FuncNAWSReturnFormat2(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    global $g_format_dictionary;
    $ret = null;
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');
        
        if (is_array($formats) && count($formats)) {
            $count = 1;
            foreach ($g_format_dictionary as $n_format => $b_formats) {
                if (($n_format != 'OPEN') && ($n_format != 'CLOSED') && ($n_format != 'WCHR')) {
                    foreach ($b_formats as $b_format) {
                        if (isset($formats[$b_format])) {
                            if (!$count--) {
                                $ret = $n_format;
                                break;
                            }
                        }
                    }
                }
                if ($ret) {
                    break;
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the third format (used for the NAWS format)

    \returns A string The format code, in NAWS form.
*/
function BMLT_FuncNAWSReturnFormat3(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    global $g_format_dictionary;
    $ret = null;
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');

        if (is_array($formats) && count($formats)) {
            $count = 2;
            foreach ($g_format_dictionary as $n_format => $b_formats) {
                if (($n_format != 'OPEN') && ($n_format != 'CLOSED') && ($n_format != 'WCHR')) {
                    foreach ($b_formats as $b_format) {
                        if (isset($formats[$b_format])) {
                            if (!$count--) {
                                $ret = $n_format;
                                break;
                            }
                        }
                    }
                }
                if ($ret) {
                    break;
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the fourth format (used for the NAWS format)

    \returns A string The format code, in NAWS form.
*/
function BMLT_FuncNAWSReturnFormat4(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    global $g_format_dictionary;
    $ret = null;
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');

        if (is_array($formats) && count($formats)) {
            $count = 3;
            foreach ($g_format_dictionary as $n_format => $b_formats) {
                if (($n_format != 'OPEN') && ($n_format != 'CLOSED') && ($n_format != 'WCHR')) {
                    foreach ($b_formats as $b_format) {
                        if (isset($formats[$b_format])) {
                            if (!$count--) {
                                $ret = $n_format;
                                break;
                            }
                        }
                    }
                }
                if ($ret) {
                    break;
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the fifth format (used for the NAWS format)

    \returns A string The format code, in NAWS form.
*/
function BMLT_FuncNAWSReturnFormat5(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    global $g_format_dictionary;
    $ret = null;
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $formats = $the_meeting->GetMeetingDataValue('formats');

        if (is_array($formats) && count($formats)) {
            $count = 4;
            foreach ($g_format_dictionary as $n_format => $b_formats) {
                if (($n_format != 'OPEN') && ($n_format != 'CLOSED') && ($n_format != 'WCHR')) {
                    foreach ($b_formats as $b_format) {
                        if (isset($formats[$b_format])) {
                            if (!$count--) {
                                $ret = $n_format;
                                break;
                            }
                        }
                    }
                }
                if ($ret) {
                    break;
                }
            }
        }
    }
    
    return $ret;
}

/*******************************************************************/
/**
    \brief Returns the string for the name for the meeting's Service Body (used for the NAWS format)

    \returns A string The Service Body name.
*/
function BMLT_FuncNAWSReturnMeetingServiceBodyName(
    $in_meeting_id, ///< The ID of the meeting (internal DB ID) This can also be a meeting object.
    &$server        ///< A reference to an instance of c_comdef_server
) {
    $ret = null;
    
    if ($in_meeting_id instanceof c_comdef_meeting) {
        $the_meeting = $in_meeting_id;
    } else {
        $the_meeting = $server->GetOneMeeting($in_meeting_id);
    }
    
    if ($the_meeting instanceof c_comdef_meeting) {
        $service_body = $the_meeting->GetServiceBodyObj();
        
        while (!$ret && ($service_body instanceof c_comdef_service_body)) {
            if (($service_body->GetSBType() == c_comdef_service_body__ASC__) || ($service_body->GetSBType() == c_comdef_service_body__RSC__)) {
                $ret = $service_body->GetLocalName();
            } else {
                $service_body = $service_body->GetOwnerIDObject();
            }
        }
    }
    
    return $ret;
}
