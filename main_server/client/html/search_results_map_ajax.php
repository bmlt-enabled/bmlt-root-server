<?php
/***********************************************************************/
/** 	\file	search_results_map_ajax.php

	\brief	This file is called from the AJAX handler of the map JS,
	and returns a JSON object that represents all the meetings found by
	the search, indicated in the GET parameters.
	
	It echoes "0" if no meetings are found.

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
	define ( 'BMLT_EXEC', true );	// This is a security verifier. Keeps files from being executed outside of the context
	require_once ( dirname ( __FILE__ ).'/../c_comdef_meeting_search_manager.class.php' );
	require_once ( dirname ( __FILE__ ).'/../common_search.inc.php' );
	require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2Json.php');

	$search_manager = new c_comdef_meeting_search_manager;

	if ( $search_manager instanceof c_comdef_meeting_search_manager )
		{
		unset ( $_GET['SearchString'] );
		$http_vars = array_merge_recursive ( $_GET, $_POST );
		SetUpSearch ( $search_manager, $http_vars );
		$search_manager->DoSearch();
		$search_results = $search_manager->GetSearchResultsAsArray();
		
		$localized_strings = c_comdef_server::GetLocalStrings();
		
		if ( is_array ( $search_results ) && count ( $search_results ) )
			{
			$ret_array = array ( count ( $search_results ) );
			$index = 1;
			
			foreach ( $search_results as &$mtg_obj )
				{
				if ( $mtg_obj instanceof c_comdef_meeting )
					{
					$id = $mtg_obj->GetID();
					$weekday = $localized_strings['weekdays'][$mtg_obj->GetMeetingDataValue('weekday_tinyint') -1];
					$time = BuildTime ( $mtg_obj->GetMeetingDataValue('start_time') );
					$location = BuildLocation ( $mtg_obj );
					$town = BuildTown ( $mtg_obj );
					$formats_ar = BuildFormats ( $mtg_obj, true );
					$formats = '';
					if ( is_array ( $formats_ar ) && count ( $formats_ar ) )
						{
						$formats = array();
						foreach ( $formats_ar as $key => $value )
							{
							array_push ( $formats, htmlspecialchars ( $key ) );
							}
						
						$formats = join ( ",", $formats );
						}
					
					$long = $mtg_obj->GetMeetingDataValue('longitude');
					$lat = $mtg_obj->GetMeetingDataValue('latitude');
					$d_suffix = $localized_strings['dist_units'] == 'mi' ? $localized_strings['comdef_search_results_strings']['Radius_Display']['km'] : $localized_strings['comdef_search_results_strings']['Radius_Display']['miles'];
					
					if ( ($long != 0.0)  || ($lat != 0.0) )	// We don't add meetings with empty long/lat
						{
						$name = $mtg_obj->GetMeetingDataValue('meeting_name');
									
						// If there is no meeting name, then we use the generic "NA Meeting" filler.
						if ( !$name )
							{
							$name = c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Value_Prompts']['generic'] );
							}
	
						$mtg_array = array ( 'id' => $id,
											 'lng' => $long,
											 'lat' => $lat,
											 'sortindex' => ($mtg_obj->GetMeetingDataValue('weekday_tinyint') * 10000) + BuildTime ( $mtg_obj->GetMeetingDataValue('start_time'), true ),
											 'name' => array ( 'prompt' => $mtg_obj->GetMeetingDataPrompt('meeting_name'), 'value' => $name ),
											 'weekday' => array ( 'prompt' => $localized_strings['comdef_search_results_strings']['Column_Headers']['weekday_tinyint'], 'value' => $weekday ),
											 'time' => array ( 'prompt' => $localized_strings['comdef_search_results_strings']['Column_Headers']['start_time'], 'value' => $time ),
											 'location' => array ( 'prompt' => $localized_strings['comdef_search_results_strings']['Column_Headers']['location'], 'value' => $location ),
											 'town' => array ( 'prompt' => $localized_strings['comdef_search_results_strings']['Column_Headers']['location_municipality'], 'value' => $town ),
											 'formats' => array ( 'prompt' => $localized_strings['comdef_search_results_strings']['Column_Headers']['formats'], 'value' => $formats ),
											 'single_link' => array ( 'text' => $localized_strings['comdef_search_results_strings']['meeting_link_text'], 'title' => $localized_strings['comdef_search_results_strings']['meeting_link'] )
											);
						$ret_array[$index++] = $mtg_array;
						}
					}
				}
			
			$ret_array = array2json ( $ret_array );
	        header ( 'Content-type: application/json' );
			if ( zlib_get_coding_type() === false )
				{
					ob_start("ob_gzhandler");
				}
				else
				{
					ob_start();
				}

			echo $ret_array;
			ob_end_flush();
			}
		else
			{
			echo '0';
			}
		}
	else
		{
		echo '0';
		}
?>