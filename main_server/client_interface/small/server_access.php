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
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

/*******************************************************************/
/**
	\brief Reads in a file of CSS, JS or XHTML, and returns it optimized.
	
	\returns a string, containing the optimized browser code.
*/
function getOptimizedFile ( $in_file
							)
	{
	$ret = file_get_contents ( $in_file );
	
	return ( optimizeReturn ( $ret ) );
	}

/*******************************************************************/
/**
	\brief Optimizes the given browser code. It removes PHP, for safety.
	
	\returns a string, containing the optimized browser code.
*/
function optimizeReturn ( $in_data
						)
	{
	$script_head = '<script type="text/javascript">/* <![CDATA[ */';
	$script_foot = '/* ]]> */</script>';
	$style_head = '<style type="text/css">/* <![CDATA[ */';
	$style_foot = '/* ]]> */</style>';
	$ret = preg_replace('/\<\?php.*?\?\>/', '', $in_data);
	$ret = preg_replace('/<!--(.|\s)*?-->/', '', $ret);
	$ret = preg_replace('/\/\*(.|\s)*?\*\//', '', $ret);
	$ret = preg_replace( "|\s+\/\/.*|", " ", $ret );
	$ret = preg_replace( "/\s+/", " ", $ret );
	$ret = preg_replace( "|\<script type=\"text\/javascript\"\>(.*?)\<\/script\>|", "$script_head$1$script_foot", $ret );
	$ret = preg_replace( "|\<style type=\"text\/css\"\>(.*?)\<\/style\>|", "$style_head$1$style_foot", $ret );
	
	return $ret;
	}

/*******************************************************************/
/**
	\brief Queries the local server, and returns processed XHTML, JSON, CSV or XML.
	
	This requires that the "switcher=" parameter be set in the GET or
	POST parameters:
		- 'GetHeaderXHTML'
			Returns the head stuff for the displayed page.
			
		- 'GetSimpleSearchForm'
			Returns the search form.
			
		- 'RedirectAJAX'
			This is used to redirect AJAX calls, as cross-domain calls are barred.
	
	\returns XHTML-compliant XML.
*/
function parse_redirect (
						&$server	///< A reference to an instance of c_comdef_server
						)
	{
	$result = null;
	$http_vars = array_merge ( $_GET, $_POST );

	// Just to be safe, we override any root passed in. We know where our root is, and we don't need to be told.
	$http_vars['bmlt_root'] = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] )."/../../";
	
	switch ( $http_vars['switcher'] )
		{
		case 'GetHeaderXHTML':
			$result = GetHeaderXHTML ( $server, $http_vars );
		break;
		
		case 'GetSimpleSearchForm':
			$result = GetSimpleSearchForm ( $server, $http_vars );
		break;
		
		case 'GetSearchResults':
			$result = GetSearchResults ( $http_vars );
		break;
		
		case 'GetOneMeeting':
			$result = GetOneMeeting ( $server, $http_vars );
		break;
		
		case 'RedirectAJAX':
			$result = RedirectAJAX ( $http_vars );
		break;
		
		default:
			$result = HandleDefault ( $http_vars );
		break;
		}
	
	return ( optimizeReturn ( $result ) );
	}

/*******************************************************************/
/**
	\brief	This returns a string that is the embedded XHTML to be
	placed in the page &gt;head&lt; element.
	However, we can return just the style files or the script files, in
	space-delimited lists. These are menat to be broken up and used 
	
	\returns XHTML-compliant XML.
*/	
function GetHeaderXHTML ( 
						&$in_server,	///< A reference to an instance of c_comdef_server
						$in_http_vars	/**< The HTTP GET and POST parameters.
										*/
						)
	{
	include ( dirname ( __FILE__ ).'/../../server/config/auto-config.inc.php' );
	$ajax_threads = c_comdef_htmlspecialchars ( $in_http_vars['bmlt_root'] ).'server/shared/js_stripper.php?filename=ajax_threads.js';
	if ( !isset( $in_http_vars['no_ajax_check'] ) || ($in_http_vars['no_ajax_check'] != 'yes') )
		{
		$check_ajax = c_comdef_htmlspecialchars ( $in_http_vars['bmlt_root'] ).'server/shared/js_stripper.php?filename=check_ajax.js';
		}
	
	if ( isset ( $in_http_vars['gmap_key'] ) && $in_http_vars['gmap_key'] )
		{
		$gkey = $in_http_vars['gmap_key'];
		}
		
	$google_include = "http://maps.google.com/maps?file=api&amp;v=2&amp;key=$gkey";
	$einsert = c_comdef_htmlspecialchars ( $in_http_vars['bmlt_root'] )."server/shared/js_stripper.php?filename=einsert.js";
	
	$scripts = "$ajax_threads $check_ajax";

	if ( isset( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		$scripts .= " $google_include $einsert";
		}
	
	$search_spec = c_comdef_htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/small/style_stripper.php?filename=search_specification.css";
	$search_results_single_meeting = c_comdef_htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/small/style_stripper.php?filename=search_results_single_meeting.css";
	$search_results_list = c_comdef_htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/small/style_stripper.php?filename=search_results_list.css";
	$search_results_map = c_comdef_htmlspecialchars ( $in_http_vars['bmlt_root'] )."themes/".$localized_strings['theme']."/small/style_stripper.php?filename=search_results_map.css";
	
	$styles = "$search_spec $search_results_list $search_results_map $search_results_single_meeting $search_results_single_meeting_print $search_results_print";
	
	if ( !isset ( $in_http_vars['style_only'] ) && !isset ( $in_http_vars['script_only'] ) )
		{
		ob_start ();
		echo '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />';
		if ( isset ( $ajax_threads ) && trim ( $ajax_threads ) )
			{
			echo '<script type="text/javascript" src="'.c_comdef_htmlspecialchars ( trim ( $ajax_threads ) ).'"></script>';
			}

		if ( isset ( $check_ajax ) && trim ( $check_ajax ) )
			{
			echo '<script type="text/javascript" src="'.c_comdef_htmlspecialchars ( trim ( $check_ajax ) ).'"></script>';
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
			<?php
			}
			
		if ( isset ( $in_http_vars['do_search'] ) && $in_http_vars['do_search'] )
			{
			?>
			<link rel="stylesheet" href="<?php echo $search_results_list ?>" />
			<link rel="stylesheet" href="<?php echo $search_results_map ?>" />
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
	
	return ( optimizeReturn ( $ret ) );
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
	require_once ( dirname ( __FILE__ ).'/../../client/small/search_specification.php' );
	return DisplayMeetingSearchForm ( $in_http_vars );
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
		$disp_map = isset ( $in_http_vars['disp_format'] ) && ($in_http_vars['disp_format'] == 'map');
		
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
	\brief Handles no command supplied (error)
	
	\returns English error string (not XML).
*/	
function HandleDefault ( 
						$in_http_vars	///< The HTTP GET and POST parameters.
						)
	{
	header ( 'Location: ../xhtml/index.php' );
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