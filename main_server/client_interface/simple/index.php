<?php
/***********************************************************************/
/** 	\file	client_interface/simple/index.php

	\brief	This file is a very simple interface that is designed to return
	a basic XHTML string, in response to a search.
	In order to use this, you need to call: <ROOT SERVER BASE URI>/client_interface/simple/
	with the same parameters that you would send to an advanced search. The results
	will be returned as XHTML data.
	
	This file can be called from other servers.

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
require_once ( dirname ( __FILE__ ).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');

$server = c_comdef_server::MakeServer();
$ret = null;

if ( $server instanceof c_comdef_server )
	{
	/*******************************************************************/
	/**
		\brief Queries the local server, and returns processes XHTML.
		
		This requires that the "switcher=" parameter be set in the GET or
		POST parameters:
			- 'GetSearchResults'
				This returns the search results.

		\returns CSV data, with the first row a key header.
	*/
	function parse_redirect (
							&$server	///< A reference to an instance of c_comdef_server
							)
		{
		$result = null;
		$http_vars = array_merge_recursive ( $_GET, $_POST );
		
		if ( !isset ( $http_vars['lang_enum'] ) || !$http_vars['lang_enum'] )
			{
			$http_vars['lang_enum'] = $server->GetLocalLang();
			}
		
		// Just to be safe, we override any root passed in. We know where our root is, and we don't need to be told.
		$http_vars['bmlt_root'] = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] )."/../../";
		
		switch ( $http_vars['switcher'] )
			{
			case 'GetSearchResults':
				$result = GetSearchResults ( $http_vars, isset ( $http_vars['block_mode'] ), $http_vars['container_id'] );
			break;
			
			case 'GetFormats':
				$lang = null;
				
				if ( isset ( $http_vars['lang_enum'] ) )
					{
					$lang = $http_vars['lang_enum'];
					}
				
				$result = GetFormats ( $server, isset ( $http_vars['block_mode'] ), $http_vars['container_id'], $lang );
			break;
			
			default:
				$result = HandleDefault ( $http_vars );
			break;
			}
		
		return $result;
		}
	
	/*******************************************************************/
	/**
		\brief	This returns the search results, in whatever form was requested.
		
		\returns XHTML data. It will either be a table, or block elements.
	*/	
	function GetSearchResults ( 
								$in_http_vars,			///< The HTTP GET and POST parameters.
								$in_block = false,		///< If this is true, the results will be sent back as block elements (div tags), as opposed to a table. Default is false.
								$in_container_id = null	///< This is an optional ID for the "wrapper."
								)
		{
		$localized_strings = c_comdef_server::GetLocalStrings();
	
		if ( !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] ) && isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset ( $in_http_vars['advanced_radius'] ) && isset ( $in_http_vars['advanced_mapmode'] ) && $in_http_vars['advanced_mapmode'] && ( floatval ( $in_http_vars['advanced_radius'] != 0.0 ) ) && isset ( $in_http_vars['lat_val'] ) &&  isset ( $in_http_vars['long_val'] ) && ( (floatval ( $in_http_vars['lat_val'] ) != 0.0) || (floatval ( $in_http_vars['long_val'] ) != 0.0) ) )
			{
			$in_http_vars['geo_width'] = $in_http_vars['advanced_radius'];
			}
		elseif ( !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] ) && isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') )
			{
			$in_http_vars['lat_val'] = null;
			$in_http_vars['long_val'] = null;
			}
		elseif ( !isset ( $in_http_vars['geo_loc'] ) || $in_http_vars['geo_loc'] != 'yes' )
			{
			if ( !isset( $in_http_vars['geo_width'] ) )
				{
				$in_http_vars['geo_width'] = 0;
				}
			}

		require_once ( dirname ( __FILE__ ).'/../../client/html/search_results_csv.php' );
		$results = DisplaySearchResultsCSV ( $in_http_vars );
		
		$ret = '';
		
		// What we do, is to parse the CSV return. We'll pick out certain fields, and format these into a table or block element return.
		if ( $results )
			{
			// Start by turning the CSV into an array of meeting lines.
			$results = explode ( "\n", $results );

			if ( is_array ( $results ) && count ( $results ) )
				{
				$ret = $in_block ? '<div class="bmlt_simple_meetings_div"'.($in_container_id ? ' id="'.c_comdef_htmlspecialchars ( $in_container_id ).'"' : '').'>' : '<table class="bmlt_simple_meetings_table"'.($in_container_id ? ' id="'.c_comdef_htmlspecialchars ( $in_container_id ).'"' : '').' cellpadding="0" cellspacing="0" summary="Meetings">';
				$keys = preg_replace ( '|^"|', '', preg_replace ( '|"$|', '', explode ( '","', $results[0] ) ) );
				
				$alt = 1;	// This is used to provide an alternating class.
				// We skip the first line, because that is the field header.
				for ( $count = 1; $count < count ( $results ); $count++ )
					{
					$meeting = $results[$count];
					
					if ( $meeting )
						{
						if ( $alt == 1 )
							{
							$alt = 0;
							}
						else
							{
							$alt = 1;
							}
						
						$meeting = preg_replace ( '|^"|', '', preg_replace ( '|"$|', '', explode ( '","', $meeting ) ) );
						
						if ( is_array ( $meeting ) && count ( $meeting ) )
							{
							// This is for convenience. We turn the meeting array into an associative one by adding the keys.
							$meeting = array_combine ( $keys, $meeting );
							
							$single_uri = 'http://'.$_SERVER['SERVER_NAME'].preg_replace ( '#(.*\/).*?\/.*?\/.*?$#', "$1", $_SERVER['SCRIPT_NAME'] ).'index.php?single_meeting_id='.intval ( $meeting['id_bigint'] );

							$location_borough = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_city_subsection'] ) ) );
							$location_neighborhood = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_neighborhood'] ) ) );
							$location_province = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_province'] ) ) );
							$location_nation = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_nation'] ) ) );
							
							if ( trim ( stripslashes ( $meeting['location_municipality'] ) ) )
								{
								if ( $location_borough )
									{
									// We do it this verbose way, so we will scrag the comma if we want to hide the town.
									$town = "<span class=\"c_comdef_search_results_borough\">$location_borough</span><span class=\"c_comdef_search_results_municipality\">, ".c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_municipality'] ) ) )."</span>";
									}
								else
									{
									$town = "<span class=\"c_comdef_search_results_municipality\">".c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_municipality'] ) ) )."</span>";
									}
								}
							elseif ( $location_borough )
								{
								$town = "<span class=\"c_comdef_search_results_municipality_borough\">$location_borough</span>";
								}
							
							if ( $location_province )
								{
								$town = "$town<span class=\"c_comdef_search_results_province\">, $location_province</span>";
								}
							
							if ( $location_nation )
								{
								$town = "$town<span class=\"c_comdef_search_results_nation\">, $location_nation</span>";
								}
							
							if ( $location_neighborhood )
								{
								$town = "$town<span class=\"c_comdef_search_results_neighborhood\"> ($location_neighborhood)</span>";
								}
	
							$weekday = $localized_strings['weekdays'][intval ( $meeting['weekday_tinyint'] ) -1];
							$time = BuildMeetingTime ( $meeting['start_time'] );
							
							$address = '';
							$location_text = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_text'] ) ) );
							$street = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_street'] ) ) );
							$info = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['location_info'] ) ) );
							
							if ( $location_text )
								{
								$address .= $location_text;
								}
							
							if ( $street )
								{
								if ( $address )
									{
									$address .= ", ";
									}
								$address .= $street;
								}
							
							if ( $info )
								{
								if ( $address )
									{
									$address .= " ";
									}
								$address .= "($info)";
								}
							
							$name = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['meeting_name'] ) ) );
							$format = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting['formats'] ) ) );
							
							$name_uri = urlencode( htmlspecialchars_decode ( $name ) );
							
							$map_uri = str_replace ( "##LONG##", c_comdef_htmlspecialchars ( $meeting['longitude'] ), str_replace ( "##LAT##", c_comdef_htmlspecialchars ( $meeting['latitude'] ), str_replace ( "##NAME##", $name_uri, $localized_strings['comdef_search_results_strings']['MapsURL'] ) ) );
							
							if ( $time && $weekday && $address )
								{
								$ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_div bmlt_alt_'.intval ( $alt ).'">' : '<tr class="bmlt_simple_meeting_one_meeting_tr bmlt_alt_'.intval ( $alt ).'">';
									$ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_town_div">' : '<td class="bmlt_simple_meeting_one_meeting_town_td">';
									$ret .= $town;
									$ret .= $in_block ? '</div>' : '</td>';
									$ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_name_div">' : '<td class="bmlt_simple_meeting_one_meeting_name_td">';
									$ret .= '<a href="'.$single_uri.'">';
									if ( $name )
										{
										$ret .= $name;
										}
									else
										{
										$ret .= $localized_strings['comdef_search_results_strings']['Value_Prompts']['generic'];
										}
									$ret .= '</a>';
									$ret .= $in_block ? '</div>' : '</td>';
								
									$ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_time_div">' : '<td class="bmlt_simple_meeting_one_meeting_time_td">';
									$ret .= $time;
									$ret .= $in_block ? '</div>' : '</td>';
								
									$ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_weekday_div">' : '<td class="bmlt_simple_meeting_one_meeting_weekday_td">';
									$ret .= $weekday;
									$ret .= $in_block ? '</div>' : '</td>';
								
									$ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_address_div">' : '<td class="bmlt_simple_meeting_one_meeting_address_td">';
									$ret .= '<a href="'.$map_uri.'">';
									$ret .= $address;
									$ret .= '</a>';
									$ret .= $in_block ? '</div>' : '</td>';
								
									$ret .= $in_block ? '<div class="bmlt_simple_meeting_one_meeting_format_div">' : '<td class="bmlt_simple_meeting_one_meeting_format_td">';
									$ret .= $format;
									$ret .= $in_block ? '</div>' : '</td>';
								
								$ret .= $in_block ? '</div>' : '</tr>';
								}
							}
						}
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
	function BuildMeetingTime ( $in_time ///< A string. The value of the time field.
								)
	{
		$localized_strings = c_comdef_server::GetLocalStrings();
		
		$time = null;
		
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
			include ( dirname ( __FILE__ ).'/../../server/config/auto-config.inc.php' );
	
			$time = c_comdef_htmlspecialchars ( date( $time_format, strtotime ( $in_time ) ) );
			}
		
		return $time;
	}
	
	/*******************************************************************/
	/**
		\brief	This returns the complete formats table.
		
		\returns CSV data, with the first row a key header.
	*/	
	function GetFormats ( 	
						&$server,					///< A reference to an instance of c_comdef_server
						$in_block = false,			///< If this is true, the results will be sent back as block elements (div tags), as opposed to a table. Default is false.
						$in_container_id = null,	///< This is an optional ID for the "wrapper."
						$in_lang = null				///< The language of the formats to be returned. Default is null (server language). Can be an array.
						)
		{
		$my_keys = array (	'key_string',
							'name_string',
							'description_string'
							);
		
		$ret = $in_block ? '<div class="bmlt_simple_format_div"'.($in_container_id ? ' id="'.c_comdef_htmlspecialchars ( $in_container_id ).'"' : '').'>' : '<table class="bmlt_simple_format_table"'.($in_container_id ? ' id="'.c_comdef_htmlspecialchars ( $in_container_id ).'"' : '').' cellpadding="0" cellspacing="0" summary="Format Codes">';
		
		$formats_obj = $server->GetFormatsObj();
		if ( $formats_obj instanceof c_comdef_formats )
			{
			$langs = $server->GetServerLangs();
			
			if ( is_array ( $in_lang ) && count ( $in_lang ) )
				{
				$langs2 = array();
				foreach ( $in_lang as $key )
					{
					if ( array_key_exists ( $key, $langs ) )
						{
						$langs2[$key] = $langs[$key];
						}
					}
				
				$langs = $langs2;
				}
			elseif ( array_key_exists ( $in_lang, $langs ) )
				{
				$langs = array ( $in_lang => $langs[$in_lang] );
				}
		
			foreach ( $langs as $key => $value )
				{
				$format_array =  $formats_obj->GetFormatsByLanguage ( $key );
				
				if ( is_array ( $format_array ) && count ( $format_array ) )
					{
					$alt = 1;	// This is used to provide an alternating style.
					foreach ( $format_array as $format )
						{
						if ( $format instanceof c_comdef_format )
							{
							if ( $alt == 1 )
								{
								$alt = 0;
								}
							else
								{
								$alt = 1;
								}
						
							$ret .= $in_block ? '<div class="bmlt_simple_format_one_format_div bmlt_alt_'.intval ( $alt ).'">' : '<tr class="bmlt_simple_format_one_format_tr bmlt_alt_'.intval ( $alt ).'">';
							foreach ( $my_keys as $ky )
								{
								$ret .= ($in_block ?  '<div' : '<td').' class="';
								
								$val = '';
								
								switch ( $ky )
									{
									case	'key_string':
										$ret .= 'bmlt_simple_format_one_format_key';
										$val = $format->GetKey();
									break;
									
									case	'name_string':
										$ret .= 'bmlt_simple_format_one_format_name';
										$val = $format->GetLocalName();
									break;
									
									case	'description_string':
										$ret .= 'bmlt_simple_format_one_format_description';
										$val = $format->GetLocalDescription();
									break;
									
									default:
										$ret .= 'bmlt_simple_format_one_format_unknown';
									break;
									}
								
								$ret .= $in_block ?  '_div">' : '_td">';
								$ret .= c_comdef_htmlspecialchars ( trim ( $val ) );
								$ret .= $in_block ?  '</div>' : '</td>';
								}
							$ret .= $in_block ? '</div>' : '</tr>';
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
	function HandleDefault ( 
							$in_http_vars	///< The HTTP GET and POST parameters.
							)
		{
		return "You must supply either 'switcher=GetSearchResults' or 'switcher=GetFormats'";
		}
	
	/*******************************************************************/
	/**
		\brief Handles no server available (error).
		
		\returns null;
	*/	
	function HandleNoServer ( )
		{
		return null;
		}
	
	$ret = parse_redirect ( $server );
	}
else
	{
	$ret = HandleNoServer ( );
	}

$handler = 'ob_gzhandler';

// Server-side includes (and some other implementations) can't handle compressed data in the response. If "nocompress" is specified, then the GZIP handler isn't used.
if ( isset ($_GET['nocompress']) || isset ($_POST['nocompress']) )
	{
	$handler = null;
	}

ob_start($handler);
	$ret = preg_replace('/<!--(.|\s)*?-->/', '', $ret);
	$ret = preg_replace('/\/\*(.|\s)*?\*\//', '', $ret);
	$ret = preg_replace( "|\s+\/\/.*|", " ", $ret );
	$ret = preg_replace( "/\s+/", " ", $ret );
	echo $ret;
ob_end_flush();
?>