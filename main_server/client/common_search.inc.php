<?php
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

/***********************************************************************/
/** 	\file	common_search.inc.php

	\brief	This file contains a routine that allows a search to be
	established with an existing c_comdef_search_manager object.

    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://magshare.org/bmlt

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

/*******************************************************************/
/** \brief This function sets up the search manager to do the specified
	search. It does not actually do the search.
*/
function SetUpSearch (	&$in_search_manager,	///< A reference to an instance of c_comdef_search_manager. The search manager to set up.
						&$in_http_vars			/**< The various HTTP GET and POST parameters.
													 The values that are important to the search are:
														- 'services'
															This is an array of positive integers.
															This is interpreted as an array of integers. Each integer represents the ID of a Service Body.
															A positive integer means that the search will look specifically for meetings that contain that
															Service Body ID.
															If the integer is negative (preceded by a minus sign -), then the criteria will be to look
															for meetings that don't contain that ID.
															If no 'services' values are given, then the search will not use the Service Body field as a
															search criteria.
														
														- 'weekdays'
															This is an array of positive integers ( 1-7).
															This is interpreted as an array of integers. Each integer represents a weekday (1 -> Sunday, 7 -> Saturday).
															A positive integer means that the search will look specifically for meetings that occur on that weekday.
															If the integer is negative (preceded by a minus sign -), then the criteria will be to look
															for meetings that don't occur on that weekday.
															If no 'weekdays' values are given, then the search will not use the weekday field as a
															search criteria.
															
														- 'bmlt_search_type'
															This is set to 'advanced' if the search is an advanced one (we need to take more criteria into consideration).
															
														- 'advanced_search_mode'
															This is set if the call was made from the advanced search page.
														
														- 'advanced_formats'
															This is the formats array, but is only counted if the bmlt_search_type is set to 'advanced'.
														
														- 'advanced_service_bodies'
															This is the same, but for Service Bodies.
														
														- 'advanced_weekdays'
															...and weekdays.
															
														- 'advanced_radius'
															...and radius (in degrees)
															
														- advanced_mapmode
															If this is true (1), then the Advanced form is using its map.
															
														- 'advanced_published'
															This is a switch to indicate whether or not to display published or unpublished meetings.
															It is only viable for logged-in users, and can have these values:
																- -1	Search for ONLY unpublished meetings
																-  0	Search for published and unpublished meetings.
																-  1	Search for ONLY published meetings.

														- 'formats'
															This is an array of positive integers.
															This is interpreted as an array of integers. Each integer represents a format shared ID.
															A format ID means that the search will look specifically for meetings that have that format.
															If the format is negative (preceded by a minus sign -), then the criteria will be to look
															for meetings that don't have that format.
															If no 'formats' values are given, then the search will not use the formats field as a
															search criteria.
														
														- 'langs'
															This is an array of 2-character strings.
															This is interpreted as an array of strings. Each string represents a language code, and is a 2-character string.
															A language string means that the search will look specifically for meetings that are in that language.
															If the language is preceded by a minus sign -, then the criteria will be to look
															for meetings that are not in that language.
															If no 'langs' values are given, then the search will not use the lang_enum field as a
															search criteria.
														
														The following values specify a start time "window." The meeting must start on, or after StartsAfterH/M, and
														can start no later than StartsBeforeH/M
														
														- 'StartsAfterH'
															A positive integer between 0 and 23. The hour of the minimal start time for meetings, in military time.
														- 'StartsAfterM'
															A positive integer between 0 and 59. The minute of the minimal start time for meetings, in military time.
														- 'StartsBeforeH'
															A positive integer between 0 and 23. The hour of the maximal start time for meetings, in military time.
														- 'StartsBeforeM'
															A positive integer between 0 and 59. The minute of the maximal start time for meetings, in military time.
															
														The following values specify a time duration "window." The meeting can last no longer than MaxDurationH/M,
														and no less than MinDurationH/M.
														
														- 'MinDurationH'
															A positive integer. This is the number of hours in the minimal duration.
														- 'MinDurationM'
															A positive integer. This is the number of minutes in the minimal duration.
														- 'MaxDurationH'
															A positive integer. This is the number of hours in the maximal duration.
														- 'MaxDurationM'
															A positive integer. This is the number of minutes in the maximal duration.
															
														This is how meetings are located. We don't use address lookups. Instead, we geolocate the meetings via the
														longitude and latitude fields in each record. If you don't specify a geolocation, then the entire database
														is searched. If you do specify one, then only the portion within the radius is searched.
														
														- 'geo_width'
															A floating point number. This is the radius (not diameter) of the search, in MILES (not Kilometers).
															If this is negative, then it should be an integer, and that indicates an auto-radius is requested to
															find the number of meetings in the integer.
															
														- 'geo_width_km'
															A floating point number. This is the radius (not diameter) of the search, in KILOMETERS (not Miles).
															If this is negative, then it should be an integer, and that indicates an auto-radius is requested to
															find the number of meetings in the integer.
															
														- 'long_val'
															If one of the three radius specifiers is zero or undefined, this is ignored.
															This is a floating point number that specifies the longitude, in degrees, of the center of the search radius.
															
														- 'lat_val'
															If one of the three radius specifiers is zero or undefined, this is ignored.
															This is a floating point number that specifies the latitude, in degrees, of the center of the search radius.
														
														- 'SearchString'
															A string. If this is specified, then all the string fields of the meetings specified by the above criteria
															will be searched for the string. By default, if the language supports metaphone (sound like search), then
															that is used.
															
														- 'StringSearchIsAnAddress'
															A boolean. Nonzero means that the given string should not be checked against any of the fields in the meeting
															data. Instead, it is to be considered a submission to the Google Maps geocode, and will be used to determine
															a cernter point in a local search.
															
														- 'SearchStringAll'
															If nonzero, then all of the words in the search string will have to be matched for a meetings to qualify.
															
														- 'SearchStringExact'
															If nonzero, metaphone will not be used, and the spelling must be exact.
															
														- 'SearchStringRadius'
															If specified, the radius of the search around the address (ignored if StringSearchIsAnAddress is false).
															The units are in whatever the server units are for this language (can be miles or kilometers).
															Negative numbers must always be integers, and specify a rough target number of meetings for auto-radius.
															
														- 'meeting_ids'
															An array of positive integers. Each integer is an ID of an individual meeting. If this is set, all other
															search criteria are ignored.
														
														- 'meeting_key'
															A string. This is the exact name of the key to match. If this is null (Default), the following three are ignored.
																NOTE:	As of 1.5, the behavior of this field has changed.
																		If it is an array, then the string search is done via metaphone, unless meeting_key_match_case is true.
																		If it is an array, then the search is done on all the fields in the array, assuming they are all text.
																		Non-string fields are ignored.
																	
														- 'meeting_key_value'
															A string. The value to match.
																NOTE:	As of Version 1.5, this is matched with a metaphone match, as well as the RegEx match.
															
														- 'meeting_key_match_case'
															If true, the case must match. Default is false.
																NOTE:	As of Version 1.5, setting this to TRUE also stops the metaphone search.
															
														- 'meeting_key_contains'
															If this is false, then the string must be complete. Default is true (contains).
															
														- 'sort_results_by_distance'
															If this is true, then, if possible, the search results will be sorted by distance from the radius center.
												*/
						)
{
	// If we have a meeting ID array, then that defines the entire search. We ignore everything else
	if ( isset ( $in_http_vars['meeting_ids'] ) && is_array ( $in_http_vars['meeting_ids'] ) && count ( $in_http_vars['meeting_ids'] ) )
		{
		$in_search_manager->SetMeetingIDArray( $in_http_vars['meeting_ids'] );
		}
	else
		{
		if ( isset ( $in_http_vars['sort_results_by_distance'] ) )
			{
			$in_search_manager->SetSortByDistance ( $in_http_vars['sort_results_by_distance'] );
			}
		
		// The first thing we do is try to resolve any address lookups.
		if ( isset ( $in_http_vars['SearchString'] ) && isset ( $in_http_vars['StringSearchIsAnAddress'] )
			&& trim ( $in_http_vars['SearchString'] ) && $in_http_vars['StringSearchIsAnAddress'] )
			{
			$search_string = trim ( $in_http_vars['SearchString'] );
			if ( $search_string )
				{
				$geo_search = (isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width']) ? true : ((isset ( $in_http_vars['geo_width_km'] ) && $in_http_vars['geo_width_km']) ? true : false);

				// We do a geocode to find out if this is an address.
				if ( !$geo_search )
					{
					$geo = GetGeocodeFromString ( $search_string, $in_http_vars['advanced_weekdays'] );
					if ( is_array ( $geo ) && count ( $geo ) )
						{
						$localized_strings = c_comdef_server::GetLocalStrings();

						$in_http_vars['long_val'] = $geo['longitude'];
						$in_http_vars['lat_val'] = $geo['latitude'];
						
						if ( isset ( $in_http_vars['SearchStringRadius'] ) && floatval ( $in_http_vars['SearchStringRadius'] ) != 0.0 )
							{
							if ( intval ( $in_http_vars['SearchStringRadius'] ) < 0 )
								{
								$geo['radius'] = intval ( $in_http_vars['SearchStringRadius'] );
								}
							else
								{
								$geo['radius'] = floatval ( $in_http_vars['SearchStringRadius'] );
								}
							}
						
						if ( $localized_strings['dist_units'] == 'mi' )
							{
							unset ( $in_http_vars['geo_width_km'] );
							$in_http_vars['geo_width'] = $geo['radius'];
							}
						else
							{
							unset ( $in_http_vars['geo_width'] );
							$in_http_vars['geo_width_km'] = $geo['radius'];
							}
						
						/* We need to undef these, because they can step on the long/lat. */
						unset ( $in_http_vars['SearchString'] );
						unset ( $in_http_vars['StringSearchIsAnAddress'] );
						unset ( $in_http_vars['SearchStringRadius'] );
						}
					}
				}
			}

		// First, set up the services.
		if ( isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset ( $in_http_vars['advanced_service_bodies'] ) && is_array ( $in_http_vars['advanced_service_bodies'] ) && count ( $in_http_vars['advanced_service_bodies'] ) )
			{
			$in_http_vars['services'] = $in_http_vars['advanced_service_bodies'];
			}
		
		// Look for Service bodies.
		if ( isset ( $in_http_vars['services'] ) && is_array ( $in_http_vars['services'] ) && count ( $in_http_vars['services'] ) )
			{
			$sb =& $in_search_manager->GetServiceBodies();
			foreach ( $in_http_vars['services'] as $service )
				{
				$sb[intval($service)] = 1;
				}
			}
		else
		    {
		    unset ( $in_http_vars['services'] );
		    }
		
		if (   !( isset ( $in_http_vars['geo_width_km'] ) && $in_http_vars['geo_width_km'] )
		    && !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] )
		    && isset ( $in_http_vars['bmlt_search_type'] )
		    && ($in_http_vars['bmlt_search_type'] == 'advanced')
		    && isset ( $in_http_vars['advanced_radius'] )
		    && isset ( $in_http_vars['advanced_mapmode'] )
		    && $in_http_vars['advanced_mapmode']
		    && ( floatval ( $in_http_vars['advanced_radius'] != 0.0 ) )
		    && isset ( $in_http_vars['lat_val'] )
		    && isset ( $in_http_vars['long_val'] )
		    && ( (floatval ( $in_http_vars['lat_val'] ) != 0.0) || (floatval ( $in_http_vars['long_val'] ) != 0.0) )
		    )
			{
            if ( $localized_strings['dist_units'] == 'mi' )
                {
			    $in_http_vars['geo_width'] = $in_http_vars['advanced_radius'];
			    unset ( $in_http_vars['geo_width_km'] );
			    }
			else
			    {
			    $in_http_vars['geo_width_km'] = $in_http_vars['advanced_radius'];
			    unset ( $in_http_vars['geo_width'] );
			    }
			}
		
		// If we aren't doing any geographic searches, then we won't have a search center.
		if ( !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] ) && !( isset ( $in_http_vars['geo_width_km'] ) && $in_http_vars['geo_width_km'] ) )
			{
			unset ( $in_http_vars['lat_val'] );
			unset ( $in_http_vars['long_val'] );
			}
		
		// Next, set up the weekdays.
		if ( isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset ( $in_http_vars['advanced_weekdays'] ) && ((is_array ( $in_http_vars['advanced_weekdays'] ) && count ( $in_http_vars['advanced_weekdays'] )) || isset ($in_http_vars['advanced_weekdays'])) )
			{
			$in_http_vars['weekdays'] = $in_http_vars['advanced_weekdays'];
			}
		
		if ( isset ( $in_http_vars['weekdays'] ) && is_array ( $in_http_vars['weekdays'] ) && count ( $in_http_vars['weekdays'] ) )
			{
			$wd =& $in_search_manager->GetWeekdays();
			foreach ( $in_http_vars['weekdays'] as $weekday )
				{
				$wd[intval($weekday)] = 1;
				}
			}
		elseif ( isset ( $in_http_vars['weekdays'] ) )
			{
			$wd =& $in_search_manager->GetWeekdays();
			$wd[intval($in_http_vars['weekdays'])] = 1;
			}
		
		// Next, set up the formats.
		
		if ( isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset ( $in_http_vars['advanced_formats'] ) && is_array ( $in_http_vars['advanced_formats'] ) && count ( $in_http_vars['advanced_formats'] ) )
			{
			$in_http_vars['formats'] = $in_http_vars['advanced_formats'];
			}
		
		if ( isset ( $in_http_vars['formats'] ) && is_array ( $in_http_vars['formats'] ) && count ( $in_http_vars['formats'] ) )
			{
			$fm =& $in_search_manager->GetFormats();
			foreach ( $in_http_vars['formats'] as $format )
				{
				$fm[intval($format)] = 1;
				}
			}
		
		// Next, set up the languages.
		if ( isset ( $in_http_vars['langs'] ) && is_array ( $in_http_vars['langs'] ) && count ( $in_http_vars['langs'] ) )
			{
			$lan =& $in_search_manager->GetLanguages();
			foreach ( $in_http_vars['langs'] as $lng )
				{
				$lan[$lng] = 1;
				}
			}
		
		// Next, set up the advanced published option.
		if ( isset ( $in_http_vars['advanced_published'] ) )
			{
			$in_search_manager->SetPublished( intval ( $in_http_vars['advanced_published'] ) );
			}
		
		// Set the start window.
		$start_time = null;
		$end_time = null;
		
		// Next, the minimum start time..
		if ( isset ( $in_http_vars['StartsAfterH'] ) || isset ( $in_http_vars['StartsAfterM'] ) )
			{
			$start_hour = min ( 23, max ( 0, intval ( $in_http_vars['StartsAfterH'] ) ) );
			$start_minute = min ( 59, max ( 0, intval ( $in_http_vars['StartsAfterM'] ) ) );
			$start_time = mktime ( $start_hour, $start_minute );
			}
		
		// Next, the maximum start time..
		if ( isset ( $in_http_vars['StartsBeforeH'] ) || isset ( $in_http_vars['StartsBeforeM'] ) )
			{
			$end_hour = min ( 23, max ( 0, intval ( $in_http_vars['StartsBeforeH'] ) ) );
			$end_minute = min ( 59, max ( 0, intval ( $in_http_vars['StartsBeforeM'] ) ) );
			$end_time = mktime ( $end_hour, $end_minute );
			}
		
		$in_search_manager->SetStartTime ( $start_time, $end_time );
		
		// Set the duration window.
		$max_duration_time = null;
		$min_duration_time = null;
		
		// Next, the minimum start time..
		if ( isset ( $in_http_vars['MaxDurationH'] ) || isset ( $in_http_vars['MaxDurationM'] ) )
			{
			$max_duration_hour = min ( 23, max ( 0, intval ( $in_http_vars['MaxDurationH'] ) ) );
			$max_duration_minute = min ( 59, max ( 0, intval ( $in_http_vars['MaxDurationM'] ) ) );
			$max_duration_time = mktime ( $max_duration_hour, $max_duration_minute );
			}
		
		// Next, the maximum start time..
		if ( isset ( $in_http_vars['MinDurationH'] ) || isset ( $in_http_vars['MinDurationM'] ) )
			{
			$min_duration_hour = min ( 23, max ( 0, intval ( $in_http_vars['MinDurationH'] ) ) );
			$min_duration_minute = min ( 59, max ( 0, intval ( $in_http_vars['MinDurationM'] ) ) );
			$min_duration_time = mktime ( $min_duration_hour, $min_duration_minute );
			}
		
		$in_search_manager->SetDuration ( $max_duration_time, $min_duration_time );
	
		// Next, we deal with a geolocated search radius.
		
		if (	(isset ( $in_http_vars['geo_width'] ) && ($in_http_vars['geo_width'] != 0))
			||	(isset ( $in_http_vars['geo_width_km'] ) && ($in_http_vars['geo_width_km'] != 0) ) )
			{
			$long = $in_http_vars['long_val'];
			$lat = $in_http_vars['lat_val'];
			$radius_in_miles = 0;
			$radius_in_km = 0;
			$radius_auto = 0;
			
			if ( isset ( $in_http_vars['geo_width'] ) && ( $in_http_vars['geo_width'] != 0 ) )
				{
				if ( $in_http_vars['geo_width'] < 0 )
					{
					$radius_auto = 0 - intval($in_http_vars['geo_width']);
					}
				else
					{
					$radius_in_miles = floatval ( $in_http_vars['geo_width'] );
					}
				}
			elseif ( isset ( $in_http_vars['geo_width_km'] ) && ( $in_http_vars['geo_width_km'] != 0 ) )
				{
				if ( $in_http_vars['geo_width_km'] < 0 )
					{
					$radius_auto = 0 - intval($in_http_vars['geo_width_km']);
					}
				else
					{
					$radius_in_km = floatval ( $in_http_vars['geo_width_km'] );
					}
				}
			
			if ( $radius_in_miles > 0 )
				{
				$in_search_manager->SetSearchRadiusAndCenterInMiles ( $radius_in_miles, $long, $lat );
				}
			elseif ( $radius_in_km > 0 )
				{
				$in_search_manager->SetSearchRadiusAndCenterInKm ( $radius_in_km, $long, $lat );
				}
			elseif ( $radius_auto > 0 )
				{
				$in_search_manager->SetSearchRadiusAndCenterAuto ( $radius_auto, $long, $lat );
				}
			}
			
		if ( !(is_array ( $in_http_vars['meeting_key'] ) && count ( $in_http_vars['meeting_key'] )) )
			{
			// And last, but not least, a string search:
			$search_string = $in_http_vars['SearchString'];
			if ( $search_string )
				{
				$find_all = (isset ( $in_http_vars['SearchStringAll'] ) && $in_http_vars['SearchStringAll']) ? true :false;
				$literal = (isset ( $in_http_vars['SearchStringExact'] ) && $in_http_vars['SearchStringExact']) ? true :false;				
				$in_search_manager->SetSearchString ( $search_string, $find_all, $literal );
				}
			}
			
		if ( isset ( $in_http_vars['meeting_key'] ) && $in_http_vars['meeting_key'] )
			{
			// This is true by default.
			if ( !isset ( $in_http_vars['meeting_key_contains'] ) )
				{
				$in_http_vars['meeting_key_contains'] = true;
				}
			
			// This is false by default.
			if ( !isset ( $in_http_vars['meeting_key_match_case'] ) )
				{
				$in_http_vars['meeting_key_match_case'] = false;
				}
			$in_search_manager->SetKeyValueSearch ( $in_http_vars['meeting_key'], $in_http_vars['meeting_key_value'], $in_http_vars['meeting_key_match_case'], $in_http_vars['meeting_key_contains'] );
			}
		}
}

/*******************************************************************/
/** \brief This displays the format keys, along with abbreviations to
	display when the cursor is over them.
	
	\returns a string, containing the HTML rendered by the function, or,
	if the $lite parameter is set to true, an associative, multidimensional
	array, containing the information.
*/
function BuildFormats ( $in_mtg_obj, 	///< A reference to an instance of c_comdef_meeting.
						$lite = false	///< If this is set to true, then the formats will be returned in an associative array, instead of as HTML. Default is false.
						)
{
	$formats = "";
	
	$formats_obj = $in_mtg_obj->GetMeetingDataValue('formats');
	
	if ( is_array ( $formats_obj ) && count ( $formats_obj ) )
		{
		foreach ( $formats_obj as $format )
			{
			if ( $format instanceof c_comdef_format )
				{
				$key = htmlspecialchars($format->GetKey());
				$name = c_comdef_htmlspecialchars($format->GetLocalName());
				$desc = c_comdef_htmlspecialchars($format->GetLocalDescription());
				if ( $lite )
					{
					$formats[$key]['name'] = $name;
					$formats[$key]['desc'] = $desc;
					}
				else
					{
					$formatspacer = '';
					if ( $formats )
						{
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
function BuildTown ( $in_mtg_obj ///< A reference to an instance of c_comdef_meeting.
					)
{
	$location_borough = c_comdef_htmlspecialchars ( trim ( stripslashes ( $in_mtg_obj->GetMeetingDataValue('location_city_subsection') ) ) );
	$location_town = c_comdef_htmlspecialchars ( trim ( stripslashes ( $in_mtg_obj->GetMeetingDataValue('location_municipality') ) ) );
	$location_neighborhood = c_comdef_htmlspecialchars ( trim ( stripslashes ( $in_mtg_obj->GetMeetingDataValue('location_neighborhood') ) ) );
	$location_province = c_comdef_htmlspecialchars ( trim ( stripslashes ( $in_mtg_obj->GetMeetingDataValue('location_province') ) ) );
	
	if ( $location_province )
		{
		$location_town .= ', '.$location_province;
		}
	
	if ( $location_borough )
		{
		$location_town = "<span class=\"c_comdef_search_results_town\">$location_borough</span>, <span class=\"c_comdef_search_results_town\">$location_town</span>";
		}
	
	if ( $location_neighborhood )
		{
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
function BuildTime ( $in_time, ///< A string. The value of the time field.
					$in_integer = false	///< If true, the time is returned as an integer (Military time).
					)
{
	$localized_strings = c_comdef_server::GetLocalStrings();
	
	$time = null;
	
	if ( $in_integer )
		{
		$time = intval ( str_replace ( ':', '', $in_time ) ) / 100;
		}
	else
		{
		if ( ($in_time == "00:00:00") || ($in_time == "23:59:00") )
			{
			$time = c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['time_midnight'] );
			}
		elseif ( $in_time == "12:00:00" )
			{
			$time = c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['time_noon'] );
			}
		else
			{
			$time = c_comdef_htmlspecialchars ( date( $localized_strings['time_format'], strtotime ( $in_time ) ) );
			}
		}
	
	return $time;
}

/*******************************************************************/
/** \brief This combines the location and street address fields.
	
	\returns a string, containing the HTML rendered by the function.
*/
function BuildLocation ( $in_mtg_obj ///< A reference to an instance of c_comdef_meeting.
						)
{
	$ret = "";
	
	if ( $in_mtg_obj instanceof c_comdef_meeting )
		{
		$location_text = c_comdef_htmlspecialchars ( trim ( stripslashes ( $in_mtg_obj->GetMeetingDataValue('location_text') ) ) );
		$street = c_comdef_htmlspecialchars ( trim ( stripslashes ( $in_mtg_obj->GetMeetingDataValue('location_street') ) ) );
		$info = c_comdef_htmlspecialchars ( trim ( stripslashes ( $in_mtg_obj->GetMeetingDataValue('location_info') ) ) );
		
		if ( $location_text )
			{
			$ret .= $location_text;
			}
		
		if ( $street )
			{
			if ( $ret )
				{
				$ret .= ", ";
				}
			$ret .= $street;
			}
		
		if ( $info )
			{
			if ( $ret )
				{
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

function GetGeocodeFromString ( $in_string,	///< The string to be checked.
								$in_weekday_tinyint_array	///< An array of weekdays in which to filter for.
								)
{
	$ret = null;
	$localized_strings = c_comdef_server::GetLocalStrings();
	
	$geo_uri = $localized_strings['comdef_search_results_strings']['ServerMapsURL'];
	
	if ( $localized_strings['region_bias'] )
		{
		$in_string .= ','.strtoupper($localized_strings['region_bias']);	// Kludge. Attach the region bias string to the text search.
		$geo_uri .= '&region='.$localized_strings['region_bias'];
		}
	
	$in_string = urlencode ( $in_string );
	
	$geo_uri = str_replace ( '##SEARCH_STRING##', $in_string, $geo_uri );
	$geo_uri = str_replace ( '##KEY##', urlencode ( $gkey ), $geo_uri );
	
	// We set up a 200-mile bounds, in order to encourage Google to look in the proper place.
	$m_p_deg = 100 / (111.321 * cos(deg2rad ( $localized_strings['search_spec_map_center']['latitude'] )) * 1.609344);	// Degrees for 100 miles.
	$bounds_ar = strval ( $localized_strings['search_spec_map_center']['latitude'] - $m_p_deg ).",". strval ( $localized_strings['search_spec_map_center']['longitude'] - $m_p_deg );		// Southwest corner
	$bounds_ar .= "|";
	$bounds_ar .= strval ( $localized_strings['search_spec_map_center']['latitude'] + $m_p_deg ).",".strval ( $localized_strings['search_spec_map_center']['longitude'] + $m_p_deg );		// Northeast corner

	$geo_uri .= '&bounds='.$bounds_ar;
	
	$geo_uri .= '&sensor=false';

	$geo_xml = call_curl ( $geo_uri, false );

	$geo_xml = DOMDocument::loadXML ( $geo_xml );
	
	if ( $geo_xml instanceof DOMDocument )
		{
		$response = $geo_xml->getElementsByTagName('Response');
		if ( ($response instanceof DOMNodeList) && $response->length )
			{
			for ( $c = 0; $c < $response->length; $c++ )
				{
				$places = $response->item($c)->getElementsByTagName('Placemark');
				if ( ($places instanceof DOMNodeList) && $places->length )
					{
					$points = $places->item($c)->getElementsByTagName('Point');
					if ( ($points instanceof DOMNodeList) && $points->length )
						{
						$coords = $points->item($c)->getElementsByTagName('coordinates');
						if ( ($coords instanceof DOMNodeList) && $points->length )
							{
							list ( $ret['longitude'], $ret['latitude'] ) = explode( ",", $coords->item(0)->nodeValue );
							$radius = c_comdef_server::HuntForRadius ( $localized_strings['number_of_meetings_for_auto'], $ret['longitude'], $ret['latitude'], $in_weekday_tinyint_array );
							if ( $radius )
								{
                                // The native units for the radius search is km. We need to convert to miles, if we are in miles.
                                if ( $localized_strings['dist_units'] == 'mi' )
                                    {
                                    $radius /= 1.609344;
                                    }

								$ret['radius'] = $radius;
								break;
								}
							else
								{
								$ret = null;
								}
							}
						}
					}
				}
			}
		}
	
	return $ret;
}
?>