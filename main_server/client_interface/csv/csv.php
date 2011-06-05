<?php
/***********************************************************************/
/** 	\file	client_interface/csv/index.php

	\brief	This file is a very simple interface that is designed to return
	a basic CSV (Comma-Separated Values) string, in response to a search.
	In order to use this, you need to call: <ROOT SERVER BASE URI>/client_interface/csv/
	with the same parameters that you would send to an advanced search. The results
	will be returned as a CSV file.
	
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

require_once ( dirname ( __FILE__ ).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2Json.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2XML.php');

/*******************************************************************/
/**
	\brief Queries the local server, and returns processed CSV data
	
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
			$result2 = GetSearchResults ( $http_vars );
			
			if ( isset ( $http_vars['xml_data'] ) )
				{
				$result = '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>';
				$xsd_uri = 'http://'.htmlspecialchars ( str_replace ( '/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/GetSearchResults.php' ) );
				$result .= "<meetings xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
				$result .= TranslateToXML ( $result2 );
				$result .= "</meetings>";
				}
			elseif ( isset ( $http_vars['json_data'] ) )
				{
				$result = TranslateToJSON ( $result2 );
				}
			else
				{
				$result = $result2;
				}
		break;
		
		case 'GetFormats':
			$lang = null;
			
			if ( isset ( $http_vars['lang_enum'] ) )
				{
				$lang = $http_vars['lang_enum'];
				}

			$result2 = GetFormats ( $server, $lang );
			
			if ( isset ( $http_vars['xml_data'] ) )
				{
				$result = '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>';
				$xsd_uri = 'http://'.htmlspecialchars ( str_replace ( '/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/GetFormats.php' ) );
				$result .= "<formats xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
				$result .= TranslateToXML ( $result2 );
				$result .= "</formats>";
				}
			elseif ( isset ( $http_vars['json_data'] ) )
				{
				$result = TranslateToJSON ( $result2 );
				}
			else
				{
				$result = $result2;
				}
		break;
		
		case 'GetChanges':
			$start_date = null;
			$end_date = null;
			
			if ( isset ( $http_vars['start_date'] ) )
				{
				$start_date = strtotime ( trim($http_vars['start_date']) );
				}
			
			if ( isset ( $http_vars['end_date'] ) )
				{
				$end_date = strtotime ( trim($http_vars['end_date']) );
				}
			$result2 = GetChanges ( $start_date, $end_date );
			
			if ( isset ( $http_vars['xml_data'] ) )
				{
				$result = '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>';
				$xsd_uri = 'http://'.htmlspecialchars ( str_replace ( '/client_interface/xml', '/client_interface/xsd', $_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/GetChanges.php' ) );
				$result .= "<changes xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";
				$result .= TranslateToXML ( $result2 );
				$result .= "</changes>";
				}
			elseif ( isset ( $http_vars['json_data'] ) )
				{
				$result = TranslateToJSON ( $result2 );
				}
			else
				{
				$result = $result2;
				}
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
	
	\returns CSV data, with the first row a key header.
*/	
function GetSearchResults ( 
							$in_http_vars	///< The HTTP GET and POST parameters.
							)
	{
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
	$result = DisplaySearchResultsCSV ( $in_http_vars );
	if ( isset ( $in_http_vars['data_field_key'] ) && $in_http_vars['data_field_key'] )
		{
		// At this point, we have everything in a CSV. We separate out just the field we want.
		$temp_keyed_array = array();
		$result = explode ( "\n", $result );
		$keys = array_shift ( $result );
		$keys = explode ( "\",\"", trim ( $keys, '"' ) );
		$the_keys = explode ( ',', $in_http_vars['data_field_key'] );
		
		$result2 = array();
		foreach ( $result as $row )
			{
			if ( $row )
				{
				$index = 0;
				$row = explode ( '","', trim ( $row, '",' ) );
				$row_columns = array();
				foreach ( $row as $column )
					{
					if ( isset ( $column ) )
						{
						if ( in_array ( $keys[$index++], $the_keys ) )
							{
							array_push ( $row_columns, $column );
							}
						}
					}
				$result2[$row[0]] = '"'.join ( '","', $row_columns ).'"';
				}
			}

		$the_keys = array_intersect ( $keys, $the_keys );
		$result = '"'.join ( '","', $the_keys )."\"\n".join ( "\n", $result2 );
		}
	
	return $result;
	}

/*******************************************************************/
/**
	\brief	This returns the complete formats table.
	
	\returns CSV data, with the first row a key header.
*/	
function GetFormats ( 	
					&$server,		///< A reference to an instance of c_comdef_server
					$in_lang = null	///< The language of the formats to be returned.
					)
	{
	$my_keys = array (	'key_string',
						'name_string',
						'description_string',
						'lang',
						'id'
						);
	
	$ret = null;
	
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
	
		$ret .= '"'.join ( '","', $my_keys )."\"\n";
		foreach ( $langs as $key => $value )
			{
			$format_array =  $formats_obj->GetFormatsByLanguage ( $key );
			
			if ( is_array ( $format_array ) && count ( $format_array ) )
				{
				foreach ( $format_array as $format )
					{
					if ( $format instanceof c_comdef_format )
						{
						$line = '';
						foreach ( $my_keys as $ky )
							{
							if ( $line )
								{
								$line .= ',';
								}
							
							$val = '';
							
							switch ( $ky )
								{
								case	'lang':
									$val = $key;
								break;
								
								case	'id':
									$val = $format->GetSharedID();
								break;
								
								case	'key_string':
									$val = $format->GetKey();
								break;
								
								case	'name_string':
									$val = $format->GetLocalName();
								break;
								
								case	'description_string':
									$val = $format->GetLocalDescription();
								break;
								}
							
							$line .= '"'.str_replace ( '"', '\"', trim ( $val ) ).'"';
							}
						$ret .= "$line\n";
						}
					}
				}
			}
		}
	
	return $ret;
	}

/*******************************************************************/
/**
	\brief	This returns change records.
	
	\returns CSV data, with the first row a key header.
*/	
function GetChanges ( 	
					$in_start_date = null,	///< Optional. A start date (In PHP time() format). If supplied, then only changes on, or after this date will be returned.
					$in_end_date = null		///< Optional. An end date (In PHP time() format). If supplied, then only changes that occurred on, or before this date will be returned.
					)
	{
	$ret = null;
	
	try
		{
		$change_objects = c_comdef_server::GetChangesFromIDAndType ( 'c_comdef_meeting', null, $in_start_date, $in_end_date );
		if ( $change_objects instanceof c_comdef_changes )
			{
			$obj_array =& $change_objects->GetChangesObjects();
			
			if ( is_array ( $obj_array ) && count ( $obj_array ) )
				{
				$localized_strings = c_comdef_server::GetLocalStrings();
				include ( dirname ( __FILE__ ).'/../../server/config/auto-config.inc.php' );
				$ret = '"date_int","date_string","change_type","meeting_id","meeting_name","user_id","user_name","details"'."\n";

				foreach ( $obj_array as $change )
					{
					$change_type = $change->GetChangeType();
					$date_int = intval($change->GetChangeDate());
					$date_string = date ($change_date_format, $date_int );
					$meeting_id = intval ( $change->GetBeforeObjectID() );
					
					if ( !$meeting_id )
						{
						$meeting_id = intval ( $change->GetAfterObjectID() );
						}
				
					$b_obj = $change->GetBeforeObject();
					$a_obj = $change->GetAfterObject();
					
					$meeting_name = '';
					$user_name = '';

					if ( $b_obj instanceof c_comdef_meeting )
						{
						$meeting_name = str_replace ( '"', "'", preg_replace ( "/[\n\r]/", " ", $b_obj->GetMeetingDataValue ( 'meeting_name' )) );
						}
					elseif ( $a_obj instanceof c_comdef_meeting )
						{
						$meeting_name = str_replace ( '"', "'", preg_replace ( "/[\n\r]/", " ", $a_obj->GetMeetingDataValue ( 'meeting_name' )) );
						}
					$user_id = intval ( $change->GetUserID() );
					
					$user = c_comdef_server::GetUserByIDObj ( $user_id );
					
					if ( $user instanceof c_comdef_user )
						{
						$user_name = str_replace ( '"', "'", preg_replace ( "/[\n\r]/", " ", $user->GetLocalName()));
						}

					$details = '';
					$desc = $change->DetailedChangeDescription();
					
					if ( $desc && isset ( $desc['details'] ) && is_array ( $desc['details'] ) )
						{
						// We need to prevent double-quotes, as they are the string delimiters, so we replace them with single-quotes.
						$details = str_replace ( '"', "'", html_entity_decode ( preg_replace ( "/[\n\r]/", " ", join ( " ", $desc['details'] ) ) ) );
						}
					
					$change_line = array();

					if ( $date_int )
						{
						$change_line['date_int'] = $date_int;
						}
					else
						{
						$change_line['date_int'] = 0;
						}
					
					if ( $date_string )
						{
						$change_line['date_string'] = $date_string;
						}
					else
						{
						$change_line['date_string'] = '';
						}
					
					if ( $change_type )
						{
						$change_line['change_type'] = $change_type;
						}
					else
						{
						$change_line['change_type'] = '';
						}
					
					if ( $meeting_id )
						{
						$change_line['meeting_id'] = $meeting_id;
						}
					else
						{
						$change_line['meeting_id'] = 0;
						}
					
					if ( $meeting_name )
						{
						$change_line['meeting_name'] = $meeting_name;
						}
					else
						{
						$change_line['meeting_name'] = '';
						}
					
					if ( $user_id )
						{
						$change_line['user_id'] = $user_id;
						}
					else
						{
						$change_line['user_id'] = 0;
						}
					
					if ( $user_name )
						{
						$change_line['user_name'] = $user_name;
						}
					else
						{
						$change_line['user_name'] = '';
						}
					
					if ( $details )
						{
						$change_line['details'] = $details;
						}
					else
						{
						$change_line['details'] = '';
						}
					
					$line = '"'.join('","',$change_line).'"';
					$ret .= "$line\n";
					}
				}
			}
		}
	catch ( Exception $e )
		{
		}

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
	return "You must supply one of the following: 'switcher=GetSearchResults', 'switcher=GetFormats' or 'switcher=GetChanges'";
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

/*******************************************************************/
/**
	\brief Translates CSV to JSON.
	
	\returns a JSON string, with all the data in the CSV.
*/	
function TranslateToJSON ( $in_csv_data	///< An array of CSV data, with the first element being the field names.
						)
	{
	$out_json_data;	///< The Output JSON data (a string).
	$temp_keyed_array = array();
	$in_csv_data = explode ( "\n", $in_csv_data );
	$keys = array_shift ( $in_csv_data );
	$keys = explode ( "\",\"", trim ( $keys, '"' ) );
	
	foreach ( $in_csv_data as $row )
		{
		if ( $row )
			{
			$line = null;
			$index = 0;
			$row = explode ( '","', trim ( $row, '",' ) );
			foreach ( $row as $column )
				{
				if ( isset ( $column ) )
					{
					$line[$keys[$index++]] = $column;
					}
				}
			array_push ( $temp_keyed_array, $line );
			}
		}
	
	$out_json_data = array2json ( $temp_keyed_array );

	return $out_json_data;
	}

/*******************************************************************/
/**
	\brief Translates CSV to XML.
	
	\returns an XML string, with all the data in the CSV.
*/	
function TranslateToXML ( 	$in_csv_data	///< An array of CSV data, with the first element being the field names.
						)
	{
	$out_xml_data;	///< The Output JSON data (a string).
	$temp_keyed_array = array();
	$in_csv_data = explode ( "\n", $in_csv_data );
	$keys = array_shift ( $in_csv_data );
	$keys = rtrim ( ltrim ( $keys, '"' ), '",' );
	$keys = preg_split ( '/","/', $keys );
	
	foreach ( $in_csv_data as $row )
		{
		if ( $row )
			{
			$line = null;
			$index = 0;
			$row_t = rtrim ( ltrim ( $row, '"' ), '",' );
			$row_t = preg_split ( '/","/', $row_t );
			foreach ( $row_t as $column )
				{
				if ( isset ( $column ) )
					{
					$line[$keys[$index++]] = $column;
					}
				}
			array_push ( $temp_keyed_array, $line );
			}
		}

	$out_xml_data = array2xml ( $temp_keyed_array, 'not_used', false );

	return $out_xml_data;
	}
?>