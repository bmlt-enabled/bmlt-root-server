<?php
/***********************************************************************/
/** \file	single_display.php

	\brief	This file will return XHTML for display of a single meeting.

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
/** \brief Call this function to display a single meeting.
	
	\returns a string. The HTML for the displayed meeting.
*/	
function DisplaySingleMeeting ( $in_http_vars	///< The combined $_GET and $_POST parameters in an associative array.
								)
	{
	$ret = '';
	
	require_once ( dirname ( __FILE__ ).'/search_results_single_meeting.php' );
	if ( isset ( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		$server = c_comdef_server::MakeServer();
		
		if ( $server instanceof c_comdef_server )
			{
			$localized_strings = c_comdef_server::GetLocalStrings();
			
			if ( !isset ( $in_http_vars['bmlt_root'] ) || !$in_http_vars['bmlt_root'] )
				{
				$in_http_vars['bmlt_root'] = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/../';
				}
	
			// This can be changed in the auto config.
			$in_http_vars['bmlt_root'] = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root'] );
			if ( $in_http_vars['bmlt_root'] == '/' )
				{
				$in_http_vars['bmlt_root'] = '';
				}
			$location_of_images = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root']."themes/".$localized_strings['theme']."/html/images" );
			$location_of_throbber = "$location_of_images/Throbber.gif";
		
			$ret = "";
			$pathname = dirname ( __FILE__ ).'/../../client/html/search_results_list.js';
			$script = file_get_contents ( $pathname );
			// If we are able to edit meetings, then we'll put up a hidden <div>for editing.
			if ( c_comdef_server::GetCurrentUserObj() instanceof c_comdef_user )
				{
				// This is the JavaScript for handling the dynamic and AJAX behavior.
				$pathname = dirname ( __FILE__ ).'/../../admin/admin_meeting.js';
				$script .= file_get_contents ( $pathname );
				}
			
			$script = preg_replace( "|\/\*.*?\*\/|s", "", $script );
			$script = preg_replace( "|[\n\r]+|s", "\n", $script );
			$script = preg_replace( '#\/\/.*?[\n]#', "", $script );
			$script = preg_replace( "|\n+|s", " ", $script );
			$script = preg_replace( "|\t+|s", " ", $script );
			$script = preg_replace( "| +|s", " ", $script );
			// If we are able to edit meetings, then we'll put up a hidden <div>for editing.
			if ( c_comdef_server::GetCurrentUserObj() instanceof c_comdef_user )
				{
				if ( !isset ( $in_http_vars['script_name'] ) || !$in_http_vars['script_name'] )
					{
					$script_name = preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] );
					}
				else
					{
					$script_name = $in_http_vars['script_name'];
					}
	
				$repl = dirname ( $script_name ).'/../../admin/edit_meeting_ajax.php';
	
				$script = str_replace( "##EDIT_URI##", $repl, $script );
	
				$script = str_replace( "##DIRTY_CONFIRM##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['dirty_confirm'] ), $script );
				$script = str_replace( "##DELETE_CONFIRM##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['delete_confirm'] ), $script );
				$script = str_replace( "##REVERT_CONFIRM##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['change_revert_confirm'] ), $script );
				$repl = dirname ( $script_name ).'/../admin/change_meeting_ajax.php';
				$script = str_replace( "##CHANGE_URI##", $repl, $script );
				$repl = dirname ( $script_name ).'/../admin/delete_meeting_ajax.php';
				$script = str_replace( "##DELETE_URI##", $repl, $script );
				$repl = dirname ( $script_name ).'/../admin/revert_meeting_ajax.php';
				$script = str_replace( "##REVERT_URI##", $repl, $script );
				}
		
			if ( isset ( $in_http_vars['satellite'] ) && $in_http_vars['satellite'] )
				{
				$ajax_call = $in_http_vars['satellite'];
				$ajax_handler = '';
				if ( isset ( $in_http_vars['ajax_handler'] ) && $in_http_vars['ajax_handler'] )
					{
					$ajax_call = preg_replace ( '|^\/\/|', '/', $in_http_vars['ajax_handler'] );
					$ajax_handler = '&ajax_handler='.c_comdef_htmlspecialchars ( $in_http_vars['ajax_handler'] );
					}
				$pathname = "$ajax_call?redirect_ajax=contact_form.php$ajax_handler&supports_ajax=yes&contact_form&meeting_id=";
				}
			else
				{
				$pathname = $in_http_vars['bmlt_root'].'client/html/contact_form.php?contact_form&meeting_id=';
				}
			
			$script = str_replace( "##CONTACT_URI##", $pathname, $script );
			$script = str_replace( '##IMAGE_DIR##', $location_of_images, $script );
			$ret .= '<script type="text/javascript">'.$script.'</script>';
			$ret .= '<div id="c_comdef_search_results_edit_hidden_div" style="display:none" class="c_comdef_search_results_edit_hidden_div no_print"></div>';
			
			if ( c_comdef_server::GetCurrentUserObj() )
				{
				$ret .= '<div id="geocoder_browser_edit_meeting_map_div" class="geocoder_browser_edit_meeting_hidden"><div id="goo_map_workplace_container" class="goo_map_workplace_container_div"><noscript><h1>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['noscript_warning'] ).'</h1></noscript><div id="goo_map_workplace" class="map_work_div"><div id="meeting_map" class="map_square"></div><a href="javascript:CloseMap()">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['close_window'] ).'</a></div></div></div>';
				}
				
			$ret .= "<div id=\"c_comdef_search_results_single_ajax_throbber_div\" class=\"c_comdef_search_results_single_ajax_throbber_div\" style=\"display:none\"><img id=\"c_comdef_search_results_single_ajax_throbber_img\" class=\"c_comdef_search_results_single_ajax_throbber_img\" alt=\"busy\" src=\"".c_comdef_htmlspecialchars ( $location_of_throbber )."\" /></div>";
			$ret .= "<div id=\"c_comdef_search_results_single_ajax_div\" class=\"c_comdef_search_results_single_ajax_div\" style=\"display:none\"></div>";
			$in_http_vars['no_close'] = true;
			$ret .= DisplayOneMeeting ( $in_http_vars );
			$longitude = $in_http_vars['out_longitude'];
			$latitude = $in_http_vars['out_latitude'];
			$ret .= "<script type=\"text/javascript\">$script;map_lat = '$latitude';map_lng = '$longitude';window.onload = MakeMainMap;</script>";
			}
		else
			{
			$ret = DisplayOneMeeting ( $in_http_vars );
			}
		}
	
	return $ret;
	}
?>