<?php
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

/***********************************************************************/
/**     \file   common_search.inc.php

    \brief  This file contains a routine that allows a search to be
    established with an existing c_comdef_search_manager object.

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

/*******************************************************************/
/** \brief This function sets up the search manager to do the specified
    search. It does not actually do the search.
*/
function SetUpSearch(
    &$in_search_manager,    ///< A reference to an instance of c_comdef_search_manager. The search manager to set up.
    &$in_http_vars
    // The various HTTP GET and POST parameters.
    //    The values that are important to the search are:
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
    //       - 'recursive'
    //           If this is set to '1', then the 'services' key will recursively follow Service bodies.
    //
    //       - 'weekdays'
    //           This is an array of negative or positive nonzero integers (-7 - -1, 1 - 7).
    //           This is interpreted as an array of integers. Each integer represents a weekday (1 -> Sunday, 7 -> Saturday).
    //           A positive integer means that the search will look specifically for meetings that occur on that weekday.
    //           If the integer is negative (preceded by a minus sign -), then the criteria will be to look
    //           for meetings that don't occur on that weekday.
    //           If no 'weekdays' values are given, then the search will not use the weekday field as a
    //           search criteria.
    //
    //       - 'bmlt_search_type'
    //           This is set to 'advanced' if the search is an advanced one (we need to take more criteria into consideration).
    //
    //       - 'advanced_search_mode'
    //           This is set if the call was made from the advanced search page.
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
    //       - 'advanced_published'
    //           This is a switch to indicate whether or not to display published or unpublished meetings.
    //           It is only viable for logged-in users, and can have these values:
    //               - -1    Search for ONLY unpublished meetings
    //               -  0    Search for published and unpublished meetings.
    //               -  1    Search for ONLY published meetings.
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
    //       - 'formats_comparison_operator'
    //           This is a string used to set the operator used to compare included (positive integer) formats. Valid values
    //           and AND and OR.
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
    //       - 'SearchStringRadius'
    //           If specified, the radius of the search around the address (ignored if StringSearchIsAnAddress is false).
    //           The units are in whatever the server units are for this language (can be miles or kilometers).
    //           Negative numbers must always be integers, and specify a rough target number of meetings for auto-radius.
    //
    //       - 'meeting_ids'
    //           An array of positive integers. Each integer is an ID of an individual meeting. If this is set, all other
    //           search criteria are ignored.
    //
    //       - 'meeting_key'
    //           A string. This is the exact name of the key to match. If this is null (Default), the following three are ignored.
    //               NOTE:   As of 1.5, the behavior of this field has changed.
    //                       If it is an array, then the string search is done via metaphone, unless meeting_key_match_case is true.
    //                       If it is an array, then the search is done on all the fields in the array, assuming they are all text.
    //                       Non-string fields are ignored.
    //
    //       - 'meeting_key_value'
    //           A string. The value to match.
    //               NOTE:   As of Version 1.5, this is matched with a metaphone match, as well as the RegEx match.
    //
    //       - 'meeting_key_match_case'
    //           If true, the case must match. Default is false.
    //               NOTE:   As of Version 1.5, setting this to TRUE also stops the metaphone search.
    //
    //       - 'meeting_key_contains'
    //           If this is true, then the string can have partial matches. Default is false (literal).
    //
    //       - 'sort_results_by_distance'
    //           If this is true, then, if possible, the search results will be sorted by distance from the radius center.
    //           If this is set, then the 'sort_keys' parameter below will be ignored.
    //
    //       - 'sort_keys'
    //           This is a comma-separated list of sort keys. The leftmost one will be the top priority, and the rightmost the lowest.
    //           The sort depth will be the number of keys.
    //           The direction will be assumed 'asc', unless 'desc' is one of the keys (it can be anywhere in the list).
) {
    $search_string = isset($in_http_vars['SearchString']) ? trim($in_http_vars['SearchString']) : '';
    
    if ($search_string && !(isset($in_http_vars['StringSearchIsAnAddress']) && $in_http_vars['StringSearchIsAnAddress']) && intval($search_string) && (preg_match('|\d+|', $search_string) || preg_match('(|\d+|,)+', $search_string))) {
        $temp_ids = explode(',', $search_string);
        
        if (is_array($temp_ids) && count($temp_ids)) {
            $first = true;
            
            foreach ($temp_ids as $id) {
                $id = intval(trim($id));
                
                if ($id) {
                    if ($first) {
                        $in_http_vars['meeting_ids'] = null;
                        $first = false;
                    }
                    
                    $in_http_vars['meeting_ids'][] = $id;
                }
            }
        } else {
            $id = intval($search_string);
            
            if ($id) {
                $in_http_vars['meeting_ids'] = array ( intval($id) );
            }
        }
    }
    
    // If we have a meeting ID array, then that defines the entire search. We ignore everything else
    if (isset($in_http_vars['meeting_ids']) && is_array($in_http_vars['meeting_ids']) && count($in_http_vars['meeting_ids'])) {
        $in_search_manager->SetMeetingIDArray($in_http_vars['meeting_ids']);
    } else {
        if (isset($in_http_vars['sort_results_by_distance'])) {
            $in_search_manager->SetSortByDistance($in_http_vars['sort_results_by_distance']);
        } elseif (isset($in_http_vars['sort_keys']) && $in_http_vars['sort_keys']) {
            $sort_fields = array();
            $keys = explode(',', $in_http_vars['sort_keys']);
            $dir = 'asc';
            foreach ($keys as $key) {
                if (strtolower(trim($key)) == 'desc') {
                    $dir = 'desc';
                } else {
                    $templates = c_comdef_meeting::GetDataTableTemplate();
                    if ($templates && count($templates)) {
                        $additional = array ();
                        
                        foreach ($templates as $template) {
                            $value = $template['key'];
                            array_push($additional, $value);
                        }
                        
                        $standards = array ( 'weekday_tinyint', 'id_bigint', 'worldid_mixed', 'service_body_bigint', 'lang_enum', 'duration_time', 'start_time', 'longitude', 'latitude' );
                        $templates = array_merge($standards, $additional);
                        
                        if (in_array($key, $templates)) {
                            array_push($sort_fields, $key);
                        }
                    }
                }
            }
                
            $in_search_manager->SetSort($sort_fields, $dir == 'desc', count($sort_fields));
        }
        
        // The first thing we do is try to resolve any address lookups.
        if ($search_string && isset($in_http_vars['StringSearchIsAnAddress']) && $in_http_vars['StringSearchIsAnAddress']) {
            $geo_search = (isset($in_http_vars['geo_width']) && $in_http_vars['geo_width']) ? true : ((isset($in_http_vars['geo_width_km']) && $in_http_vars['geo_width_km']) ? true : false);

            // We do a geocode to find out if this is an address.
            if (!$geo_search) {
                $search_string = preg_replace('|,(\s*?)|', ', ', $search_string);    // This works around a bug caused by too-tight commas.
                $geo = GetGeocodeFromString($search_string, $in_http_vars['advanced_weekdays']);
                if (is_array($geo) && count($geo)) {
                    $localized_strings = c_comdef_server::GetLocalStrings();

                    $in_http_vars['long_val'] = $geo['longitude'];
                    $in_http_vars['lat_val'] = $geo['latitude'];
                    
                    if (isset($in_http_vars['SearchStringRadius']) && floatval($in_http_vars['SearchStringRadius']) != 0.0) {
                        if (intval($in_http_vars['SearchStringRadius']) < 0) {
                            $geo['radius'] = intval($in_http_vars['SearchStringRadius']);
                        } else {
                            $geo['radius'] = floatval($in_http_vars['SearchStringRadius']);
                        }
                    }
                    
                    if ($localized_strings['dist_units'] == 'mi') {
                        if (isset($in_http_vars['geo_width_km'])) {
                            unset($in_http_vars['geo_width_km']);
                        }
                        $in_http_vars['geo_width'] = $geo['radius'];
                    } else {
                        unset($in_http_vars['geo_width']);
                        $in_http_vars['geo_width_km'] = $geo['radius'];
                    }
                    
                    /* We need to undef these, because they can step on the long/lat. */
                    unset($search_string);
                    unset($in_http_vars['StringSearchIsAnAddress']);
                    unset($in_http_vars['SearchStringRadius']);
                }
            }
        }

        // First, set up the services.
        if (isset($in_http_vars['bmlt_search_type']) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset($in_http_vars['advanced_service_bodies']) && is_array($in_http_vars['advanced_service_bodies']) && count($in_http_vars['advanced_service_bodies'])) {
            $in_http_vars['services'] = $in_http_vars['advanced_service_bodies'];
        }
        
        if (isset($in_http_vars['services']) && !is_array($in_http_vars['services'])) {
            $in_http_vars['services'] = array ( $in_http_vars['services'] );
        }

        // Look for Service bodies.
        if (isset($in_http_vars['services']) && is_array($in_http_vars['services']) && count($in_http_vars['services'])) {
            $services = array();

            if (isset($in_http_vars['recursive']) && $in_http_vars['recursive']) {
                foreach ($in_http_vars['services'] as $service) {
                    $nested = GetAllContainedServiceBodyIDs(intval($service));
                    
                    if (isset($nested) && is_array($nested) && count($nested)) {
                        foreach ($nested as $sb_i) {
                            $sb_i = intval($sb_i);
                            $services[$sb_i] = $sb_i;
                        }
                    }
                }
            } else {
                $services = $in_http_vars['services'];
            }

            $sb =& $in_search_manager->GetServiceBodies();
            
            foreach ($services as $service) {
                $sb[intval($service)] = 1;
            }
        } else {
            unset($in_http_vars['services']);
        }
        

        if (!( isset($in_http_vars['geo_width_km']) && $in_http_vars['geo_width_km'] )
            && !( isset($in_http_vars['geo_width']) && $in_http_vars['geo_width'] )
            && isset($in_http_vars['bmlt_search_type'])
            && ($in_http_vars['bmlt_search_type'] == 'advanced')
            && isset($in_http_vars['advanced_radius'])
            && isset($in_http_vars['advanced_mapmode'])
            && $in_http_vars['advanced_mapmode']
            && ( floatval($in_http_vars['advanced_radius'] != 0.0) )
            && isset($in_http_vars['lat_val'])
            && isset($in_http_vars['long_val'])
            && ( (floatval($in_http_vars['lat_val']) != 0.0) || (floatval($in_http_vars['long_val']) != 0.0) )
            ) {
            if ($localized_strings['dist_units'] == 'mi') {
                $in_http_vars['geo_width'] = $in_http_vars['advanced_radius'];
                unset($in_http_vars['geo_width_km']);
            } else {
                $in_http_vars['geo_width_km'] = $in_http_vars['advanced_radius'];
                unset($in_http_vars['geo_width']);
            }
        }
        
        // If we aren't doing any geographic searches, then we won't have a search center.
        if (!( isset($in_http_vars['geo_width']) && $in_http_vars['geo_width'] ) && !( isset($in_http_vars['geo_width_km']) && $in_http_vars['geo_width_km'] )) {
            unset($in_http_vars['lat_val']);
            unset($in_http_vars['long_val']);
        }
        
        // Next, set up the weekdays.
        if (isset($in_http_vars['bmlt_search_type']) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset($in_http_vars['advanced_weekdays']) && ((is_array($in_http_vars['advanced_weekdays']) && count($in_http_vars['advanced_weekdays'])) || isset($in_http_vars['advanced_weekdays']))) {
            $in_http_vars['weekdays'] = $in_http_vars['advanced_weekdays'];
        }
        
        if (isset($in_http_vars['weekdays']) && !is_array($in_http_vars['weekdays']) && (intval(abs($in_http_vars['weekdays'])) > 0) && (intval(abs($in_http_vars['weekdays'])) < 8)) {
            $in_http_vars['weekdays'] = array ( intval($in_http_vars['weekdays']) );
        }

        if (isset($in_http_vars['weekdays']) && is_array($in_http_vars['weekdays']) && count($in_http_vars['weekdays'])) {
            $wd =& $in_search_manager->GetWeekdays();
            foreach ($in_http_vars['weekdays'] as $weekday) {
                $wd[abs(intval($weekday))] = intval($weekday) > 0 ? 1 : -1;
            }
        } elseif (isset($in_http_vars['weekdays'])) {
            $wd =& $in_search_manager->GetWeekdays();
            $wd[abs(intval($in_http_vars['weekdays']))] = intval(intval($in_http_vars['weekdays'])) > 0 ? 1 : -1;
        }

        // Next, set up the formats.
        
        if (isset($in_http_vars['bmlt_search_type']) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset($in_http_vars['advanced_formats']) && is_array($in_http_vars['advanced_formats']) && count($in_http_vars['advanced_formats'])) {
            $in_http_vars['formats'] = $in_http_vars['advanced_formats'];
        }
        
        if (isset($in_http_vars['formats'])) {
            if (!is_array($in_http_vars['formats'])) {
                $in_http_vars['formats'] = array ( intval($in_http_vars['formats']) );
            }
                
            $fm =& $in_search_manager->GetFormats();
            foreach ($in_http_vars['formats'] as $format) {
                $key = abs(intval($format));
                $fm[$key] = (intval($format) > 0) ? 1 : -1;
            }
        }

        if (isset($in_http_vars['formats_comparison_operator']) && $in_http_vars['formats_comparison_operator'] == "OR") {
            $in_search_manager->SetFormatsComparisonOperator("OR");
        }
        
        // Next, set up the languages.
        if (isset($in_http_vars['langs']) && is_array($in_http_vars['langs']) && count($in_http_vars['langs'])) {
            $lan =& $in_search_manager->GetLanguages();
            foreach ($in_http_vars['langs'] as $lng) {
                $lan[$lng] = 1;
            }
        }
        
        // Next, set up the advanced published option.
        if (isset($in_http_vars['advanced_published'])) {
            $in_search_manager->SetPublished(intval($in_http_vars['advanced_published']));
        } else {
            $in_search_manager->SetPublished(1);
        }
        
        // Set the start window.
        $start_time = null;
        $end_time = null;
        
        // Next, the minimum start time..
        if (isset($in_http_vars['StartsAfterH']) || isset($in_http_vars['StartsAfterM'])) {
            $start_hour = min(23, max(0, intval($in_http_vars['StartsAfterH'])));
            $start_minute = min(59, max(0, intval($in_http_vars['StartsAfterM'])));
            $start_time = mktime($start_hour, $start_minute);
        }
        
        // Next, the maximum start time..
        if (isset($in_http_vars['StartsBeforeH']) || isset($in_http_vars['StartsBeforeM'])) {
            $end_hour = min(23, max(0, intval($in_http_vars['StartsBeforeH'])));
            $end_minute = min(59, max(0, intval($in_http_vars['StartsBeforeM'])));
            $end_time = mktime($end_hour, $end_minute);
        }
        
        $in_search_manager->SetStartTime($start_time, $end_time);
        
        $end_time = null;

        // Next, the maximum end time..
        if (isset($in_http_vars['EndsBeforeH']) || isset($in_http_vars['EndsBeforeM'])) {
            $end_hour = min(23, max(0, intval($in_http_vars['EndsBeforeH'])));
            $end_minute = min(59, max(0, intval($in_http_vars['EndsBeforeM'])));
            $end_time = ($end_hour * 3600) + ($end_minute * 60);
        }
        
        $in_search_manager->SetEndTime($end_time);
        
        // Set the duration window.
        $max_duration_time = null;
        $min_duration_time = null;
        
        // Next, the minimum start time..
        if (isset($in_http_vars['MaxDurationH']) || isset($in_http_vars['MaxDurationM'])) {
            $max_duration_hour = min(23, max(0, intval($in_http_vars['MaxDurationH'])));
            $max_duration_minute = min(59, max(0, intval($in_http_vars['MaxDurationM'])));
            $max_duration_time = mktime($max_duration_hour, $max_duration_minute);
        }
        
        // Next, the maximum start time..
        if (isset($in_http_vars['MinDurationH']) || isset($in_http_vars['MinDurationM'])) {
            $min_duration_hour = min(23, max(0, intval($in_http_vars['MinDurationH'])));
            $min_duration_minute = min(59, max(0, intval($in_http_vars['MinDurationM'])));
            $min_duration_time = mktime($min_duration_hour, $min_duration_minute);
        }
        
        $in_search_manager->SetDuration($max_duration_time, $min_duration_time);
    
        // Next, we deal with a geolocated search radius.
        
        if ((isset($in_http_vars['geo_width']) && ($in_http_vars['geo_width'] != 0))
            ||  (isset($in_http_vars['geo_width_km']) && ($in_http_vars['geo_width_km'] != 0) )) {
            $long = isset($in_http_vars['long_val']) ? floatval($in_http_vars['long_val']) : 0;
            $lat = isset($in_http_vars['lat_val']) ? floatval($in_http_vars['lat_val']) : 0;
            $radius_in_miles = 0;
            $radius_in_km = 0;
            $local_strings = c_comdef_server::GetLocalStrings();
            $radius_auto = $local_strings['number_of_meetings_for_auto'];
            
            if (isset($in_http_vars['geo_width']) && ( $in_http_vars['geo_width'] != 0 )) {
                if ($in_http_vars['geo_width'] < 0) {
                    $radius_auto = 0 - intval($in_http_vars['geo_width']);
                } else {
                    $radius_in_miles = floatval($in_http_vars['geo_width']);
                }
            } elseif (isset($in_http_vars['geo_width_km']) && ( $in_http_vars['geo_width_km'] != 0 )) {
                if ($in_http_vars['geo_width_km'] < 0) {
                    $radius_auto = 0 - intval($in_http_vars['geo_width_km']);
                } else {
                    $radius_in_km = floatval($in_http_vars['geo_width_km']);
                }
            }
            
            if ($radius_in_miles > 0) {
                $in_search_manager->SetSearchRadiusAndCenterInMiles($radius_in_miles, $long, $lat);
            } elseif ($radius_in_km > 0) {
                $in_search_manager->SetSearchRadiusAndCenterInKm($radius_in_km, $long, $lat);
            } elseif ($radius_auto > 0) {
                $in_search_manager->SetSearchRadiusAndCenterAuto($radius_auto, $long, $lat);
            }
        }
            
        if ($search_string && (!isset($in_http_vars['meeting_key']) || !(is_array($in_http_vars['meeting_key']) && count($in_http_vars['meeting_key'])))) {
            // And last, but not least, a string search:
            $find_all = (isset($in_http_vars['SearchStringAll']) && $in_http_vars['SearchStringAll']) ? true :false;
            $literal = (isset($in_http_vars['SearchStringExact']) && $in_http_vars['SearchStringExact']) ? true :false;
            $in_search_manager->SetSearchString($search_string, $find_all, $literal);
        }
            
        if (isset($in_http_vars['meeting_key']) && $in_http_vars['meeting_key']) {
            // This is true by default.
            if (!isset($in_http_vars['meeting_key_contains'])) {
                $in_http_vars['meeting_key_contains'] = false;
            }
            
            // This is false by default.
            if (!isset($in_http_vars['meeting_key_match_case'])) {
                $in_http_vars['meeting_key_match_case'] = false;
            }
            $in_search_manager->SetKeyValueSearch($in_http_vars['meeting_key'], $in_http_vars['meeting_key_value'], $in_http_vars['meeting_key_match_case'], $in_http_vars['meeting_key_contains']);
        }
    }
}

/*******************************************************************/
/** \brief This gets all the Service bodies, and returns a one-dimensional
           arry, containing its ID, and the IDs of all the Service bodies
           that are contained in the array.

    \returns an array of integers.
*/
function GetAllContainedServiceBodyIDs( $in_parent_id = 0  ///< This is the ID of the top Service body (will not be included in the reponse).
                                        )
{
    $in_parent_id = intval($in_parent_id);
    $ret = array( $in_parent_id );
    
    $service_bodies = c_comdef_server::GetServer()->GetServiceBodyArray();
    
    foreach ($service_bodies as $service_body) {
        $sb_id = intval($service_body->GetID());
        $parent_id = intval($service_body->GetOwnerID());
        
        if ($in_parent_id == $parent_id) {
            $ret2 = GetAllContainedServiceBodyIDs($sb_id);
            $ret = array_merge($ret, $ret2);
        }
    }
    
    return $ret;
}

/*******************************************************************/
/** \brief This displays the format keys, along with abbreviations to
    display when the cursor is over them.

    \returns a string, containing the HTML rendered by the function, or,
    if the $lite parameter is set to true, an associative, multidimensional
    array, containing the information.
*/
function BuildFormats(
    $in_mtg_obj,    ///< A reference to an instance of c_comdef_meeting.
    $lite = false   ///< If this is set to true, then the formats will be returned in an associative array, instead of as HTML. Default is false.
) {
    $formats = "";
    
    $formats_obj = $in_mtg_obj->GetMeetingDataValue('formats');
    
    if (is_array($formats_obj) && count($formats_obj)) {
        foreach ($formats_obj as $format) {
            if ($format instanceof c_comdef_format) {
                $key = htmlspecialchars($format->GetKey());
                $name = c_comdef_htmlspecialchars($format->GetLocalName());
                $desc = c_comdef_htmlspecialchars($format->GetLocalDescription());
                if ($lite) {
                    $formats[$key]['name'] = $name;
                    $formats[$key]['desc'] = $desc;
                } else {
                    $formatspacer = '';
                    if ($formats) {
                        $formatspacer = ' ';
                    }
                    
                    $formats .= "<span class=\"c_comdef_search_results_single_format\"><abbr title=\"$desc\">$formatspacer$key</abbr></span>";
                }
            }
        }
    }
    
    return $formats;
}

/*******************************************************************/
/** \brief Combines the town, borough and neighborhood into one string.

    \returns a string, containing the HTML rendered by the function.
*/
function BuildTown( $in_mtg_obj ///< A reference to an instance of c_comdef_meeting.
                    )
{
    $location_borough = c_comdef_htmlspecialchars(trim(stripslashes($in_mtg_obj->GetMeetingDataValue('location_city_subsection'))));
    $location_town = c_comdef_htmlspecialchars(trim(stripslashes($in_mtg_obj->GetMeetingDataValue('location_municipality'))));
    $location_neighborhood = c_comdef_htmlspecialchars(trim(stripslashes($in_mtg_obj->GetMeetingDataValue('location_neighborhood'))));
    $location_province = c_comdef_htmlspecialchars(trim(stripslashes($in_mtg_obj->GetMeetingDataValue('location_province'))));
    
    if ($location_province) {
        $location_town .= ', '.$location_province;
    }
    
    if ($location_borough) {
        $location_town = "<span class=\"c_comdef_search_results_town\">$location_borough</span>, <span class=\"c_comdef_search_results_town\">$location_town</span>";
    }
    
    if ($location_neighborhood) {
        $location_town = "$location_town <span class=\"c_comdef_search_results_neighborhood\">($location_neighborhood)</span>";
    }
    
    return $location_town;
}

/*******************************************************************/
/** \brief This creates a time string to be displayed for the meeting.
    The display is done in non-military time, and "midnight" and
    "noon" are substituted for 12:59:00, 00:00:00 and 12:00:00

    \returns a string, containing the HTML rendered by the function.
*/
function BuildTime(
    $in_time, ///< A string. The value of the time field.
    $in_integer = false ///< If true, the time is returned as an integer (Military time).
) {
    $localized_strings = c_comdef_server::GetLocalStrings();
    
    $time = null;
    
    if ($in_integer) {
        $time = intval(str_replace(':', '', $in_time)) / 100;
    } else {
        if (($in_time == "00:00:00") || ($in_time == "23:59:00")) {
            $time = c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_noon_label']);
        } elseif ($in_time == "12:00:00") {
            $time = c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_midnight_label']);
        } else {
            $time = c_comdef_htmlspecialchars(date($localized_strings['time_format'], strtotime($in_time)));
        }
    }
    
    return $time;
}

/*******************************************************************/
/** \brief This combines the location and street address fields.

    \returns a string, containing the HTML rendered by the function.
*/
function BuildLocation( $in_mtg_obj ///< A reference to an instance of c_comdef_meeting.
                        )
{
    $ret = "";
    
    if ($in_mtg_obj instanceof c_comdef_meeting) {
        $location_text = c_comdef_htmlspecialchars(trim(stripslashes($in_mtg_obj->GetMeetingDataValue('location_text'))));
        $street = c_comdef_htmlspecialchars(trim(stripslashes($in_mtg_obj->GetMeetingDataValue('location_street'))));
        $info = c_comdef_htmlspecialchars(trim(stripslashes($in_mtg_obj->GetMeetingDataValue('location_info'))));
        
        if ($location_text) {
            $ret .= $location_text;
        }
        
        if ($street) {
            if ($ret) {
                $ret .= ", ";
            }
            $ret .= $street;
        }
        
        if ($info) {
            if ($ret) {
                $ret .= " ";
            }
            $ret .= "($info)";
        }
    }
    
    return $ret;
}

/*******************************************************************/
/** \brief This function uses the server-level Google Maps API to
    try to geocode an address from the string passed in. A instance
    of c_comdef_server needs to have been instantiated by the time
    this is called.

    \returns an associative array of two floating-point numbers,
    representing the longitude and latitude, in degrees, of any
    geocoded result. Null, if no valid result was returned.
*/

function GetGeocodeFromString(
    $in_string, ///< The string to be checked.
    $in_weekday_tinyint_array   ///< An array of weekdays in which to filter for.
) {
    $ret = null;
    $localized_strings = c_comdef_server::GetLocalStrings();
    
    $geo_uri = $localized_strings['comdef_server_admin_strings']['ServerMapsURL'];
    
    if ($localized_strings['region_bias']) {
        $geo_uri .= '&region='.$localized_strings['region_bias'];
    }
    
    if ($localized_strings['google_api_key']) {
        $geo_uri .= '&key='.$localized_strings['google_api_key'];
    }
    
    // Bit of a kludge. If the string is just a number (a postcode), then we add the region bias directly to it.
    if (is_numeric($in_string) && $localized_strings['region_bias']) {
        $in_string .= " ".$localized_strings['region_bias'];
    }
        
    $geo_uri = str_replace('##SEARCH_STRING##', urlencode($in_string), $geo_uri);
    
    // We set up a 200-mile bounds, in order to encourage Google to look in the proper place.
    $m_p_deg = 100 / (111.321 * cos(deg2rad($localized_strings['search_spec_map_center']['latitude'])) * 1.609344);  // Degrees for 100 miles.
    $bounds_ar = strval($localized_strings['search_spec_map_center']['latitude'] - $m_p_deg).",". strval($localized_strings['search_spec_map_center']['longitude'] - $m_p_deg);       // Southwest corner
    $bounds_ar .= "|";
    $bounds_ar .= strval($localized_strings['search_spec_map_center']['latitude'] + $m_p_deg).",".strval($localized_strings['search_spec_map_center']['longitude'] + $m_p_deg);       // Northeast corner

    $xml = simplexml_load_file($geo_uri);

    if ($xml->status == 'OK') {
        $ret['longitude'] = floatval($xml->result->geometry->location->lng);
        $ret['latitude'] = floatval($xml->result->geometry->location->lat);
        $radius = c_comdef_server::HuntForRadius($localized_strings['number_of_meetings_for_auto'], $ret['longitude'], $ret['latitude'], $in_weekday_tinyint_array);
        if ($radius) {
            // The native units for the radius search is km. We need to convert to miles, if we are in miles.
            if ($localized_strings['dist_units'] == 'mi') {
                $radius /= 1.609344;
            }

            $ret['radius'] = $radius;
        } else {
            $ret = null;
        }
    }
    
    return $ret;
}
