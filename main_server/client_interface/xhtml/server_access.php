<?php
/***********************************************************************/
/** 	\file	client_interface/xhtml/server_access.php

	\brief	This file is used as the "switchboard" for satellite servers.
	It keys on the supplied query parameters, and returns XHTML-compliant XML
	that can be embedded directly into a Web page, or transformed via XSLT.

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

// define ( '__DEBUG_MODE__', 1 ); // Uncomment to make the CSS and JavaScript easier to trace (and less efficient).

defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

/*******************************************************************/
/**
	\brief Queries the local server, and returns processed XHTML, JSON, CSV or XML.
	
	This requires that the "switcher=" parameter be set in the GET or
	POST parameters:
		- 'GetServerInfo'
		- 'GetServerInfoXML'
			Returns XML, with info about the server.
			
		- 'GetServerLanguages'
		- 'GetServerLanguagesCSV'
			Returns a CSV of the languages supported by this server.
		
		- 'GetServiceBodiesJSON'
			Returns a JSON object, with the supported Service bodies.
			
		- 'GetServiceBodiesXML'
			Returns XML data (very simple and raw), with the supported Service bodies.
			
		- 'GetHeaderXHTML'
			Returns the head stuff for the displayed page.
			
		- 'GetSimpleSearchForm'
			Returns the search form.
			
		- 'GetOneMeeting'
			Return a meeting, based on an ID.
			
		- 'GetMeetingsByID'
			Return an array of meetings, based on an array of IDs
			
		- 'GetSearchResults'
			This returns the search results.
			
		- 'RedirectAJAX'
			This is used to redirect AJAX calls, as cross-domain calls are barred.
	
	\returns XHTML-compliant XML.
*/
function parse_redirect (
						&$server	///< A reference to an instance of c_comdef_server
						)
	{
	$result = null;
	$http_vars = array_merge_recursive ( $_GET, $_POST );

	// Just to be safe, we override any root passed in. We know where our root is, and we don't need to be told.
	$http_vars['bmlt_root'] = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] )."/../../";
	
	// See what the initial display will be.
	switch ( $http_vars['start_view'] )
		{
		case 'map':
			$http_vars['start_view'] = 'map';
		break;
		
		case 'text':
			$http_vars['start_view'] = 'text';
		break;
		
		case 'advanced':
			$http_vars['start_view'] = 'advanced';
		break;
		
		case 'advanced_map':
			$http_vars['start_view'] = 'advanced';
			$http_vars['start_view_adv'] = 'map';
		break;
		
		case 'advanced_text':
			$http_vars['start_view'] = 'advanced';
			$http_vars['start_view_adv'] = 'text';
		break;
		}
	
	switch ( $http_vars['switcher'] )
		{
		case 'GetServerInfo':
		case 'GetServerInfoXML':
			$result = GetServerInfo ( $server );
		break;
		
		case 'GetServerLanguages':
		case 'GetServerLanguagesCSV':
			$result = GetServerLanguages ( $server );
		break;
		
		case 'GetServiceBodiesJSON':
			$result = array2json ( GetServiceBodies ( $server ) );
		break;
		
		case 'GetServiceBodiesXML':
			$result = sb_array_XML ( GetServiceBodies ( $server ) );
		break;
		
		case 'GetHeaderXHTML':
			$result = GetHeaderXHTML ( $server, $http_vars );
		break;
		
		case 'GetSimpleSearchForm':
			$result = GetSimpleSearchForm ( $server, $http_vars );
		break;
		
		case 'GetOneMeeting':
			$result = GetOneMeeting ( $server, $http_vars );
		break;
		
		case 'GetSearchResults':
			$result = GetSearchResults ( $http_vars );
		break;
		
		case 'RedirectAJAX':
			$result = RedirectAJAX ( $http_vars );
		break;
		
		default:
			$result = HandleDefault ( $http_vars );
		break;
		}
	
	return $result;
	}

/*******************************************************************/
/**
	\brief	This returns a string that contains an XML page with info
	about the server.
	
	\returns an XML string that contains the pertinent info (at the moment, only the version).
*/	
function GetServerInfo ( 
						&$in_server	///< A reference to an instance of c_comdef_server
						)
{
	$ret = file_get_contents ( dirname ( __FILE__ )."/../serverInfo.xml" );
	
	return $ret;
}

/*******************************************************************/
/**
	\brief	This returns a string that contains a comma-delimited list
	of all the languages supported by the server. Each language is represented
	by the code, first, then the string name. Example: '"en","English","es","Espa–ol"'.
	
	\returns a comma-separated values string. Each value is wrapped in double-quotes.
*/	
function GetServerLanguages ( 
							&$in_server	///< A reference to an instance of c_comdef_server
							)
{
	$langs = $in_server->GetServerLangs();
	$ret = array();

	foreach ( $langs as $enum => $name )
		{
		array_push ( $ret, $enum );
		array_push ( $ret, $name );
		}
	
	return '"'.join ( '","', $ret ).'"';
}

/*******************************************************************/
/**
	\brief	This returns an array, containing the supported Service bodies
		from the server. Nested bodies are represented by nested objects.
	
	\returns an array, ready for XMLIzing or JSONizing.
*/	
function GetServiceBodies ( 
								&$in_server	///< A reference to an instance of c_comdef_server
								)
{
	$ret = null;
	
	$service_body_array = $in_server->GetServiceBodyArrayHierarchical();
	
	if ( is_array ( $service_body_array ) && count ( $service_body_array ) )
		{
		$service_body_array = $service_body_array['dependents'];
		$ret = array();
		foreach ( $service_body_array as &$service_body_ar )
			{
			$gr = GetServiceBodyArray ( $service_body_ar );
			array_push ( $ret, $gr );
			}
		}
	
	return $ret;
}

/*******************************************************************/
/**
	\brief Converts a Service Bodies array to an XML string.
*/
function sb_array_XML (	$in_array
						)
	{
	$ret = null;
	
	if ( is_array ( $in_array ) && count ( $in_array ) )
		{
		$ret = '<sb>';
		
		foreach ( $in_array as $service_body )
			{
			$id = intval ( $service_body['ID'] );
			$name = $service_body['NAME'];
			$type = $service_body['TYPE'];
			$ret .= '<'.htmlspecialchars ( strtolower ( $type ) ).' id="'.$id.'" name="'.htmlspecialchars ( $name ).'"';
			if ( is_array ( $service_body['dependents'] ) && count ( $service_body['dependents'] ) )
				{
				$ret .= '>'.sb_array_XML ( $service_body['dependents'] ).'</'.htmlspecialchars ( strtolower ( $type ) ).'>';
				}
			else
				{
				$ret .= '/>';
				}
			}
		
		$ret .= '</sb>';
		}
	
	return $ret;
	}

/*******************************************************************/
/**
	\brief	This returns an array, containing one Service body,
		along with any nested Service bodies.
	
	\returns an array, ready for XMLizing or JSONizing.
*/	
function GetServiceBodyArray ( &$in_service_body_ar	///< This is a reference to an array, as returned by c_comdef_server::GetServiceBodyArrayHierarchical().
								)
{
	$ret = null;
	
	if ( isset ( $in_service_body_ar['object'] ) )
		{
		$service_body = $in_service_body_ar['object'];
		if ( $service_body instanceof c_comdef_service_body )
			{
			$ret = array ( 'ID' => $service_body->GetID(), 'NAME' => $service_body->GetLocalName(), 'TYPE' => $service_body->GetSBType() );
			
			if ( isset ( $in_service_body_ar['dependents'] ) )
				{
				$ret['dependents'] = array();
				foreach ( $in_service_body_ar['dependents'] as &$service_body_ar )
					{
					// It's recursive, so we get the nesting properly.
					array_push ( $ret['dependents'], GetServiceBodyArray ( $service_body_ar ) );
					}
				}
			}
		}
	
	return $ret;
}

/*******************************************************************/
/**
	\brief	This returns a string that is the embedded XHTML to be
	placed in the page &gt;head&lt; element.
	However, we can return just the style files or the script files, in
	space-delimited lists. These are meant to be broken up and used 
	
	\returns XHTML-compliant XML.
*/	
function GetHeaderXHTML ( 
						&$in_server,	///< A reference to an instance of c_comdef_server
						$in_http_vars	/**< The HTTP GET and POST parameters.
										*/
						)
	{
	
	$stripper = 'js_stripper.php?filename=';
	if ( defined ( '__DEBUG_MODE__' ) )
	    {
	    $stripper = '';
	    }
	include ( dirname ( __FILE__ ).'/../../server/config/auto-config.inc.php' );
	$ajax_threads = htmlspecialchars ( $in_http_vars['bmlt_root'] )."server/shared/$stripper"."ajax_threads.js";
	if ( !isset( $in_http_vars['no_ajax_check'] ) || ($in_http_vars['no_ajax_check'] != 'yes') )
		{
		$check_ajax = htmlspecialchars ( $in_http_vars['bmlt_root'] )."server/shared/$stripper"."check_ajax.js";
		}
	
	if ( isset ( $in_http_vars['gmap_key'] ) && $in_http_vars['gmap_key'] )
		{
		$gkey = $in_http_vars['gmap_key'];
		}
		
	$google_include = "http://maps.google.com/maps?file=api&amp;v=2&amp;key=$gkey";
	$einsert = htmlspecialchars ( $in_http_vars['bmlt_root'] )."server/shared/$stripper"."einsert.js";
	
	$scripts = "$ajax_threads $check_ajax";

	if ( isset( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		$scripts .= " $google_include $einsert";
		}
	
	$stripper = 'style_stripper.php?filename=';
	if ( defined ( '__DEBUG_MODE__' ) )
	    {
	    $stripper = '';
	    }
	
	$localized_strings = c_comdef_server::GetLocalStrings();
	$search_spec = htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/html/$stripper"."search_specification.css";
	$search_results_single_meeting = htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/html/$stripper"."search_results_single_meeting.css";
	$search_results_single_meeting_print = htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/html/$stripper"."search_results_single_meeting_print.css";
	$search_results_list = htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/html/$stripper"."search_results_list.css";
	$search_results_map = htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/html/$stripper"."search_results_map.css";
	$search_results_print = htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/html/$stripper"."search_results_print.css";
	
	$styles = "$search_spec $search_results_list $search_results_map $search_results_single_meeting $search_results_single_meeting_print $search_results_print";
	
	if ( !isset ( $in_http_vars['style_only'] ) && !isset ( $in_http_vars['script_only'] ) )
		{
		ob_start ();
		if ( isset ( $ajax_threads ) && trim ( $ajax_threads ) )
			{
			echo '<script type="text/javascript" src="'.htmlspecialchars ( trim ( $ajax_threads ) ).'"></script>';
			}

		if ( isset ( $check_ajax ) && trim ( $check_ajax ) )
			{
			echo '<script type="text/javascript" src="'.htmlspecialchars ( trim ( $check_ajax ) ).'"></script>';
			}

		if ( isset( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
			{
			?>
			<script src="<?php echo $google_include ?>" type="text/javascript"></script>
			<script src="<?php echo $einsert ?>" type="text/javascript"></script>
			<?php
			}
			
		if ( isset ( $in_http_vars['search_form'] ) )
			{
			?>
			<link rel="stylesheet" href="<?php echo $search_spec ?>" />
			<?php
			}
		elseif ( (isset ( $in_http_vars['single_meeting_id'] ) && $in_http_vars['single_meeting_id']) || (isset ( $in_http_vars['do_search'] ) && $in_http_vars['do_search']) )
			{
			?>
			<link rel="stylesheet" href="<?php echo $search_spec ?>" />
			<link rel="stylesheet" href="<?php echo $search_results_single_meeting ?>" />
			<link rel="stylesheet" href="<?php echo $search_results_single_meeting_print ?>" media="print" />
			<?php
			}
			
		if ( isset ( $in_http_vars['do_search'] ) && $in_http_vars['do_search'] )
			{
			?>
			<link rel="stylesheet" href="<?php echo $search_results_list ?>" />
			<link rel="stylesheet" href="<?php echo $search_results_map ?>" />
			<link rel="stylesheet" href="<?php echo $search_results_print ?>" media="print" />
			<?php
			}
		
		$ret = ob_get_contents();
		ob_end_clean();
		}
	else
		{
		if ( isset ( $in_http_vars['style_only'] ) )
			{
			$ret = $styles;
			}
		else
			{
			$ret = $scripts;
			}
		}
	
	return $ret;
	}

/*******************************************************************/
/**
	\brief	This returns the basic search form.
	
	\returns XHTML-compliant XML.
*/	
function GetSimpleSearchForm ( 
							&$in_server,	///< A reference to an instance of c_comdef_server
							$in_http_vars	/**< The HTTP GET and POST parameters.
											*/
							)
	{
	require_once ( dirname ( __FILE__ ).'/../../client/html/search_specification.php' );
	return DisplayMeetingSearchForm ( $in_http_vars );
	}

/*******************************************************************/
/**
	\brief	This returns a single meeting.
	
	\returns XHTML-compliant XML.
*/	
function GetOneMeeting ( 
						&$in_server,	///< A reference to an instance of c_comdef_server
						$in_http_vars	/**< The HTTP GET and POST parameters.
										*/
						)
	{
	require_once ( dirname ( __FILE__ ).'/../../client/html/single_display.php' );
	echo DisplaySingleMeeting ( $in_http_vars );
	}

/*******************************************************************/
/**
	\brief	This returns the search results, in whatever form was requested.
	
	\returns XHTML-compliant XML.
*/	
function GetSearchResults ( 
						$in_http_vars	/**< The HTTP GET and POST parameters.
										*/
						)
	{
	if ( !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] ) && isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset ( $in_http_vars['advanced_radius'] ) && isset ( $in_http_vars['advanced_mapmode'] ) && $in_http_vars['advanced_mapmode'] && ( floatval ( $in_http_vars['advanced_radius'] != 0.0 ) ) && isset ( $in_http_vars['lat_val'] ) &&  isset ( $in_http_vars['long_val'] ) && ( (floatval ( $in_http_vars['lat_val'] ) != 0.0) || (floatval ( $in_http_vars['long_val'] ) != 0.0) ) )
		{
		$in_http_vars['geo_width'] = $in_http_vars['advanced_radius'];
		}
	elseif ( !isset ( $in_http_vars['geo_loc'] ) || $in_http_vars['geo_loc'] != 'yes' )
		{
		if ( !isset( $in_http_vars['geo_width'] ) )
			{
			$in_http_vars['geo_width'] = 0;
			}
		}
	
	if ( isset ( $in_http_vars['result_type_advanced'] ) && $in_http_vars['result_type_advanced'] && ($in_http_vars['disp_format'] != 'map') )
		{
		$in_http_vars['disp_format'] = $in_http_vars['result_type_advanced'];
		}
	
	if ( !(isset ( $in_http_vars['disp_format'] ) && $in_http_vars['disp_format'] == 'force_list')
		&& (isset ( $in_http_vars['StringSearchIsAnAddress'] ) && $in_http_vars['StringSearchIsAnAddress']) )
		{
		$in_http_vars['disp_format'] = 'map';
		}

	$in_http_vars['bmlt_root'] = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/../../';
		
	$disp_map = false;
	
	if ( (isset( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') ) && ($in_http_vars['disp_format'] != 'force_list') )
		{
		$disp_map = (isset ( $in_http_vars['disp_format'] ) && ($in_http_vars['disp_format'] == 'map')) || (isset ( $in_http_vars['disp_format'] ) && ($in_http_vars['disp_format'] == 'advanced_map'));
		
		$disp_map = $disp_map || $in_http_vars['geo_width'] != 0;
		
		$disp_map = $disp_map || (isset ( $in_http_vars['StringSearchIsAnAddress'] ) && $in_http_vars['StringSearchIsAnAddress']);
		}
		
	if ( $disp_map )
		{
		require_once ( dirname ( __FILE__ ).'/../../client/html/search_results_map.php' );
		$ret = DisplaySearchResultsMap ( $in_http_vars );
		}
	else
		{
		// KLUDGE ALERT!
		if ( $in_http_vars['disp_format'] == 'force_list' )
			{
			$in_http_vars['disp_format'] = 'list';
			}
		require_once ( dirname ( __FILE__ ).'/../../client/html/search_results_list.php' );
		$ret = DisplaySearchResultsList ( $in_http_vars );
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
	return "You must supply 'switcher=GetServerInfo', 'switcher=GetServerLanguages', 'switcher=GetHeaderXHTML', 'switcher=GetSimpleSearchForm', 'switcher=GetOneMeeting', 'switcher=GetSearchResults' or 'switcher=RedirectAJAX'";
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
	\brief	This redirects AJAX calls, so that we don't have cross-
	domain AJAX (not allowed by browsers, as a security risk).
	
	\returns a <a href="http://json.org">JSON-compliant</a> string.
*/	
function RedirectAJAX ( 
						$in_http_vars	/**< The HTTP GET and POST parameters.
										*/
						)
{
	$filename = $in_http_vars['redirect_ajax'];
	$f_path = dirname ( __FILE__ )."/../../client/html/$filename";
	include ( $f_path );
}
?>