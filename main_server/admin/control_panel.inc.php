<?php
/***********************************************************************/
/** \file	control_panel.inc.php

	\brief	Displays an administration "control panel."

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

	require_once ( dirname ( __FILE__ ).'/../server/c_comdef_server.class.php' );

/*******************************************************************/
/** \brief	This allows administrators to access functions, such as
	creating new meetings, or un-deleting deleted meetings, administering
	formats, users and Service bodies, etc.

	This uses parameters in the HTTP query, which comprise the only input.
	
	Authorization is done through the standard session, and only functions
	for which a user is authorized are accessible. If the user has not yet
	logged in, a login prompt is displayed and authorized prior to any
	other display.
	
	\returns display-ready HTML for the control panel.
*/
function GetControlPanelHTML ( $in_http_vars,	///< The $_GET and $_POST variables, in an associative array.
								$root_offset	///< Used to allow the index file to be included.
							)
	{
	$ret = null;
	
	$server = c_comdef_server::MakeServer();
	
	if ( $server instanceof c_comdef_server )
		{
		require_once ( dirname ( __FILE__ ).'/c_comdef_login.php' );
		$localized_strings = c_comdef_server::GetLocalStrings();
		
		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
		
		if ( !isset ( $in_http_vars['script_name'] ) || !$in_http_vars['script_name'] )
			{
			$script_name = preg_replace ( '#(^\/+)|(\/+$)#', '/', preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] ) );
			}
		else
			{
			$script_name = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['script_name'] );
			}
		
		if ( !isset ( $in_http_vars['bmlt_root'] ) || !$in_http_vars['bmlt_root'] )
			{
			$in_http_vars['bmlt_root'] = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/../';
			}
			
		$in_http_vars['bmlt_root'] = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root'] );
		if ( $in_http_vars['bmlt_root'] == '/' )
			{
			$in_http_vars['bmlt_root'] = '';
			}
		$location_of_images = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root']."themes/".$localized_strings['theme']."/html/images" );
		
		// This is the JavaScript for handling the dynamic and AJAX behavior.
		$pathname = dirname ( __FILE__ ).'/admin_formats.js';
		$script = file_get_contents ( $pathname );
		$pathname = dirname ( __FILE__ ).'/admin_meetings.js';
		$script .= file_get_contents ( $pathname );
		$pathname = dirname ( __FILE__ ).'/admin_meeting.js';
		$script .= file_get_contents ( $pathname );
		$pathname = dirname ( __FILE__ ).'/admin_service_bodies.js';
		$script .= file_get_contents ( $pathname );
		$pathname = dirname ( __FILE__ ).'/admin_users.js';
		$script .= file_get_contents ( $pathname );
		$pathname = dirname ( __FILE__ ).'/reports.js';
		$script .= file_get_contents ( $pathname );
		$pathname = dirname ( __FILE__ ).'/../client/html/search_results_list.js';
		$script .= file_get_contents ( $pathname );
		$script = preg_replace( "|\/\*.*?\*\/|s", "", $script );
		$script = preg_replace( "|[\n\r]+|s", "\n", $script );
		$script = preg_replace( '#\/\/.*?[\n]#', "", $script );
		$script = preg_replace( "|\n+|s", " ", $script );
		$script = preg_replace( "|\t+|s", " ", $script );
		$script = preg_replace( "| +|s", " ", $script );
		$script = str_replace( "##SB_DELETE_CONFIRM##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['delete_sb_confirm'] ), $script );
		$script = str_replace( "##SB_DELETE_MESSAGE##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['deleted_sb'] ), $script );
		$script = str_replace( "##DIRTY_CONFIRM##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['dirty_confirm'] ), $script );
		$script = str_replace( "##DELETE_CONFIRM##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['delete_confirm'] ), $script );
		$script = str_replace( "##REVERT_CONFIRM##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['change_revert_confirm'] ), $script );
		$script = str_replace( "##BAD_EMAIL_ALERT##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_format_bad'] ), $script );
		$script = str_replace( "##REVERT_CONFIRM_FORMAT##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['change_revert_confirm'] ), $script );
		$script = str_replace( "##DELETE_CONFIRM_USER##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['delete_user_confirm'] ), $script );
		$script = str_replace( "##DELETE_CONFIRM_FORMAT##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['delete_confirm'] ), $script );
		$script = str_replace( "##DEL_BUTTON##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['delete_button'] ), $script );
		$script = str_replace( "##CH_BUTTON##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['change_button'] ), $script );
		$script = str_replace( "##ADD_BUTTON##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['add_button'] ), $script );
		$script = str_replace( "##NEW_FORMAT##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['new_format'] ), $script );
		$script = str_replace( "##NO_BLANK##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['no_blank'] ), $script );
		$script = str_replace( '##IMAGE_DIR##', $location_of_images, $script );
		$script = str_replace( '##PW_TOO_SHORT##', c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['pw_too_short'] ), $script );
		$script = str_replace( '##MIN_PW_LEN##', intval ( $min_pw_len ), $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/change_service_body_ajax.php' );
		$script = str_replace( "##CHANGE_URI_SB##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/change_user_ajax.php' );
		$script = str_replace( "##CHANGE_URI_USER##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/change_format_ajax.php' );
		$script = str_replace( "##CHANGE_URI_FORMAT##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/delete_user_ajax.php' );
		$script = str_replace( "##DELETE_URI_USER##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/delete_format_ajax.php' );
		$script = str_replace( "##DELETE_URI_FORMAT##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/revert_format_ajax.php' );
		$script = str_replace( "##REVERT_URI_FORMAT##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/format_sorter_ajax.php' );
		$script = str_replace( "##SORT_FORMAT_URI##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/change_meeting_ajax.php' );
		$script = str_replace( "##CHANGE_URI##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/delete_meeting_ajax.php' );
		$script = str_replace( "##DELETE_URI##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/delete_service_body_ajax.php' );
		$script = str_replace( "##DELETE_URI_SB##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/perm_delete_handler_ajax.php' );
		$script = str_replace( "##PERM_DELETE_URI##", $repl, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/revert_meeting_ajax.php' );
		$script = str_replace( "##REVERT_URI##", $repl, $script );
		$location_of_single = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $_SERVER['SCRIPT_NAME'] )."/client/html/search_results_list_ajax.php?supports_ajax=yes&scriptname=$script_name" );
		$script = str_replace( "##SINGLE_LOC##", $location_of_single, $script );
		$repl = preg_replace ( '#(^\/+)|(\/+$)#', '/', dirname ( $script_name ).$root_offset.'admin/edit_meeting_ajax.php' );
		$script = str_replace( "##EDIT_URI##", $repl, $script );
		$repl = htmlspecialchars ( $in_http_vars['bmlt_root'].'admin/reports_ajax.php' );
		$script = str_replace( "##REPORTS_DISPLAY_URI##", $repl, $script );
		$ret .= '<script type="text/javascript">'.$script.'</script>';

		$ret .= '<div id="bmlt_control_panel" class="bmlt_control_panel">';
		$cur_user =& c_comdef_server::GetCurrentUserObj();
			if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) && ($cur_user->GetUserLevel() != _USER_LEVEL_OBSERVER) )
				{
				include ( dirname ( __FILE__ ).'/edit_meetings.php' );
				$ret .= DisplayMeetingForm ( $in_http_vars );
				}
	
			if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) && ($cur_user->GetUserLevel() != _USER_LEVEL_OBSERVER) )
				{
				include ( dirname ( __FILE__ ).'/edit_formats.php' );
				$ret .= DisplayFormatsForEdit ( $in_http_vars );
				}
	
			if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) && ($cur_user->GetUserLevel() != _USER_LEVEL_OBSERVER) )
				{
				include ( dirname ( __FILE__ ).'/edit_service_bodies.php' );
				$ret .= DisplayServiceBodyEditor ( $in_http_vars );
				}
	
			if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) )
				{
				include ( dirname ( __FILE__ ).'/edit_users.php' );
				$ret .= DisplayUserEditor ( $in_http_vars );
				}
			
			if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) && ($cur_user->GetUserLevel() != _USER_LEVEL_OBSERVER) )
				{
				include ( dirname ( __FILE__ ).'/reports.php' );
				$ret .= DisplayReportsDiv ( $in_http_vars );
				}
		$ret .= '</div>';
		}
	
	return $ret;
}
?>