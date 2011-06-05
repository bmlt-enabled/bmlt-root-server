<?php
/***********************************************************************/
/** 	\file	edit_users.php

	\brief	This file is included in the control panel to provide a UI
	for editing user accounts in the BMLT.
	Only Server Administrators will be able to see or use this.

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
/** \brief	Displays the user editor form.

	\returns HTML for the user editor display.
*/
function DisplayUserEditor ( $in_http_vars	///< An associative array, containing the combined $_GET and $_POST parameters.
									)
	{
	$server = c_comdef_server::MakeServer();
	$ret = null;

	if ( $server instanceof c_comdef_server )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();
				
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
		$location_of_throbber = "$location_of_images/Throbber.gif";

		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
		
		if ( !isset ( $in_http_vars['script_name'] ) || !$in_http_vars['script_name'] )
			{
			$script_name = preg_replace ( '#(^\/+)|(\/+$)#', '/', preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] ) );
			}
		else
			{
			$script_name = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['script_name'] );
			}
		
		$cur_user =& c_comdef_server::GetCurrentUserObj();
		
		if ( $cur_user instanceof c_comdef_user )
			{
			$ret = "<div id=\"edit_user_container_div\" class=\"edit_user_div_";
			if ( preg_match ( "|open_user|", $_SERVER['QUERY_STRING'] ) )
				{
				$ret .= 'open';
				}
			else
				{
				$ret .= 'closed';
				}
			$ret .= "\">";
			$ret .= "<a href=\"javascript:ToggleUserEditDiv()\"";
			if ( !c_comdef_server::IsUserServerAdmin() )
				{
				$ret .= " id=\"one_user_editor_".c_comdef_htmlspecialchars($cur_user->GetID())."_a\"";
				}
			$ret .= ">";
			$ret .= c_comdef_htmlspecialchars ( c_comdef_server::IsUserServerAdmin() ? $localized_strings['comdef_search_admin_strings']['Edit_Users']['Edit_Users'] : $localized_strings['comdef_search_admin_strings']['Edit_Users']['Edit_My_Info']).$localized_strings['prompt_delimiter'];
			$ret .= "</a>";
			$ret .= "<form class=\"user_editor_form\" action=\"#\" method=\"post\" onsubmit=\"return false\">";
			$ret .= "<div id=\"user_editor_list_div\" class=\"user_editor_main_div\" style=\"display: ";
			if ( preg_match ( "|open_user|", $_SERVER['QUERY_STRING'] ) )
				{
				$ret .= 'block';
				}
			else
				{
				$ret .= 'none';
				}
			$ret .= "\">";
		
			// All users can edit their own info.
			
			$user_array = array ( &$cur_user );
			
			// Only server admins can edit multiple users.
			if ( c_comdef_server::IsUserServerAdmin() )
				{
				$user_obj =& c_comdef_server::GetServerUsersObj();
				
				if ( $user_obj instanceof c_comdef_users )
					{
					$user_array_temp = $user_obj->GetUsersArray();
					
					// OK, we should have at least one user.
					if ( is_array ( $user_array_temp ) && count ( $user_array ) )
						{
						foreach ( $user_array_temp as &$user )
							{
							if ( $user instanceof c_comdef_user )
								{
								// The user editor doesn't get displayed unless we have permission (can't be too sure).
								// We also skip our own ID, as that is the first one.
								if ( $user->UserCanEdit() && ($user->GetID() != $cur_user->GetID()) )
									{
									array_push ( $user_array, $user );
									}
								}
							}
						}
					}
				
				// This will become a "Create New User" form.
				array_push ( $user_array, null );
				}
			
			foreach ( $user_array as &$user )
				{
				$ret .= DisplayOneUserForEditing ( $user, $localized_strings['comdef_search_admin_strings']['Edit_Users'], $lang_enum );
				}
			$ret .= '</div>';
			$ret .= '</form>';
			$ret .= '</div>';
			}
		}
	
	return $ret;
	}

/*******************************************************************/
/** \brief	Displays the user editor form for one single user.

	\returns HTML for the user editor display for the user.
*/
function DisplayOneUserForEditing ( $in_user_obj,	///< A reference to a c_comdef_user instance, representing the user to be edited.
									$in_strings,	///< An array of strings. The language-specific strings to use.
									$in_lang_enum	///< An enum string. The current language.
								)
	{
	$cur_user =& c_comdef_server::GetCurrentUserObj();
	
	$localized_strings = c_comdef_server::GetLocalStrings();
				
	if ( $cur_user instanceof c_comdef_user )
		{
		if ( !($in_user_obj instanceof c_comdef_user) && c_comdef_server::IsUserServerAdmin() )
			{
			$in_user_obj = new c_comdef_user ( c_comdef_server::GetServer(), 0, _USER_LEVEL_DISABLED, null, null, null, $in_lang_enum, null, null );
			}
		
		if ( $in_user_obj->UserCanEdit($cur_user) )	// Can't be too sure...
			{
			$ret = '';
			if ( c_comdef_server::IsUserServerAdmin() && $in_user_obj->GetID() )
				{
				$ret .= '<div class="edit_one_user_div_closed" id="edit_one_user_div_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'">';
				$ret .= "<a id=\"one_user_editor_".c_comdef_htmlspecialchars($in_user_obj->GetID())."_a\" href=\"javascript:ToggleOneUserEditDiv(".c_comdef_htmlspecialchars($in_user_obj->GetID()).")\">";
				$ret .= c_comdef_htmlspecialchars ( $in_user_obj->GetLocalName() ).$localized_strings['prompt_delimiter'];
				$ret .= "</a>";
				}
			
			$ret .= '<fieldset id="user_edit_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'_fieldset" class="user_edit_fieldset';
			if ( !$in_user_obj->GetID() )	// If this is a new user form, then we use different styling.
				{
				$ret .= "_new";
				}
			$ret .= '" style="display:'.(( c_comdef_server::IsUserServerAdmin() && $in_user_obj->GetID() )? 'none' : 'block').'">';
				$ret .= '<legend id="one_user_editor_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'_legend">';
				if ( $in_user_obj->GetID() )
					{
					$ret .= "(";
					$ret .= $in_user_obj->GetID();
					$ret .= ") ";
					$ret .= $in_user_obj->GetLocalName();
					}
				else
					{
					$ret .= c_comdef_htmlspecialchars ( $in_strings['new_user_name'] );
					}
				$ret .= '</legend>';
				
				$ret .= '<div class="user_editor_line_div">';
					$ret .= '<label for="user_login_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'">';
						$ret .= c_comdef_htmlspecialchars ( $in_strings['user_login_string'] ).$localized_strings['prompt_delimiter'];
					$ret .= '</label>';
					$ret .= '<input type="text" id="user_login_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'" value="';
					if ( $in_user_obj->GetID() )
						{
						$ret .= c_comdef_htmlspecialchars ( $in_user_obj->GetLogin() );
						}
					$ret .= '" size="24"';
							
					if ( $cur_user->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN )
						{
						$ret .= ' disabled="disabled"';
						}
					
					$ret .= ' onkeyup="EnableUserChangeButton(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')" />';
				$ret .= '</div>';
				
				$ret .= '<div class="user_editor_line_div">';
					$ret .= '<label for="user_name_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'">';
						$ret .= c_comdef_htmlspecialchars ( $in_strings['user_name_string'] ).$localized_strings['prompt_delimiter'];
					$ret .= '</label>';
					$ret .= '<input type="text" id="user_name_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'" value="';
					if ( $in_user_obj->GetID() )
						{
						$ret .= c_comdef_htmlspecialchars ( $in_user_obj->GetLocalName() );
						}
					$ret .= '" size="64"';
					$ret .= ' onkeyup="EnableUserChangeButton(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')" />';
				$ret .= '</div>';
				
				$ret .= '<div class="user_editor_line_div">';
					$ret .= '<label for="user_email_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'">';
						$ret .= c_comdef_htmlspecialchars ( $in_strings['user_email_string'] ).$localized_strings['prompt_delimiter'];
					$ret .= '</label>';
					$ret .= '<input type="text" id="user_email_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'" value="';
					if ( $in_user_obj->GetID() )
						{
						$ret .= c_comdef_htmlspecialchars ( $in_user_obj->GetEmailAddress() );
						}
					$ret .= '" size="64"';
					$ret .= ' onkeyup="EnableUserChangeButton(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')" />';
				$ret .= '</div>';
				
				$ret .= '<div class="user_editor_line_div">';
					$ret .= '<label for="user_description_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'">';
						$ret .= c_comdef_htmlspecialchars ( $in_strings['user_description_string'] ).$localized_strings['prompt_delimiter'];
					$ret .= '</label>';
					$ret .= '<textarea id="user_description_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'" rows="3" cols="64"';
					$ret .= ' onkeyup="EnableUserChangeButton(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')"';
					$ret .= '>';
					$ret .= c_comdef_htmlspecialchars ( $in_user_obj->GetLocalDescription() );
					$ret .= '</textarea>';
				$ret .= '</div>';
				
				if ( ($in_user_obj->GetID() != 1) && c_comdef_server::IsUserServerAdmin() && ($in_user_obj->GetID() != $cur_user->GetID()) )
					{
					$ret .= '<div class="user_editor_line_div">';
						$ret .= '<label class="user_level_select_label" for="user_level_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'">';
							$ret .= c_comdef_htmlspecialchars ( $in_strings['user_level_string'] ).$localized_strings['prompt_delimiter'];
						$ret .= '</label>';
						$ret .= '<select class="user_level_select" id="user_level_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'"';
						$ret .= ' onchange="EnableUserChangeButton(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')">';
							$ret .= '<option value="'._USER_LEVEL_DISABLED.'"';
							if ( $in_user_obj->GetUserLevel() == _USER_LEVEL_DISABLED )
								{
								$ret .= ' selected="selected"';
								}
							$ret .= '>'.c_comdef_htmlspecialchars ( $in_strings[_USER_LEVEL_DISABLED] ).'</option>';
							$ret .= '<option value="'._USER_LEVEL_OBSERVER.'"';
							if ( $in_user_obj->GetUserLevel() == _USER_LEVEL_OBSERVER )
								{
								$ret .= ' selected="selected"';
								}
							$ret .= '>'.c_comdef_htmlspecialchars ( $in_strings[_USER_LEVEL_OBSERVER] ).'</option>';
							$ret .= '<option value="'._USER_LEVEL_EDITOR.'"';
							if ( $in_user_obj->GetUserLevel() == _USER_LEVEL_EDITOR )
								{
								$ret .= ' selected="selected"';
								}
							$ret .= '>'.c_comdef_htmlspecialchars ( $in_strings[_USER_LEVEL_EDITOR] ).'</option>';
							$ret .= '<option value="'._USER_LEVEL_SERVICE_BODY_ADMIN.'"';
							if ( $in_user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN )
								{
								$ret .= ' selected="selected"';
								}
							$ret .= '>'.c_comdef_htmlspecialchars ( $in_strings[_USER_LEVEL_SERVICE_BODY_ADMIN] ).'</option>';
// Only the first user can be a server admin.
// 							$ret .= '<option value="'._USER_LEVEL_SERVER_ADMIN.'"';
// 							if ( c_comdef_server::IsUserServerAdmin() )
// 								{
// 								$ret .= ' selected="selected"';
// 								}
// 							$ret .= '>'.c_comdef_htmlspecialchars ( $in_strings[_USER_LEVEL_SERVER_ADMIN] ).'</option>';
						$ret .= '</select>';
					$ret .= '</div>';
					}
				
				$ret .= '<div class="user_editor_line_div">';
					$ret .= '<label class="user_level_select_label" for="user_lang_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'">'.c_comdef_htmlspecialchars ( $in_strings['Language'] ).$localized_strings['prompt_delimiter'].'</label>';
					$ret .= '<select class="user_level_select" id="user_lang_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'" ';
					$ret .= ' onchange="EnableUserChangeButton(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')">';
						$language = c_comdef_server::GetServerLangs();
						
						foreach ( $language as $key => $value )
							{
							$ret .= "<option value=\"$key\"";
							if ( $key == $in_user_obj->GetLocalLang() )
								{
								$ret .= " selected=\"selected\"";
								}
							$ret .= ">".c_comdef_htmlspecialchars ( trim ( $value ) )."</option>\n";
							}
					$ret .= '</select>';
				$ret .= '</div>';
				
				if ( $in_user_obj->GetID() )
					{
					$ret .= '<div class="clear_both"></div>';
				
					$ret .= '<div class="user_editor_line_div_user_pw">';
					}
				else
					{
					$ret .= '<div class="user_editor_line_div">';
					}
					$ret .= '<label for="user_password_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'">';
						$ret .= c_comdef_htmlspecialchars ( $in_strings['user_password_string'] ).$localized_strings['prompt_delimiter'];
					$ret .= '</label>';
					$ret .= '<input type="text" id="user_password_string_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'" value="';
					$ret .= '" size="32"';
					$ret .= ' onkeyup="EnableUserChangeButton(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')" />';
					if ( $in_user_obj->GetID() )	// New users don't have an existing password, so this warning is not necessary.
						{
						$ret .= '<span class="pw_warning">'.c_comdef_htmlspecialchars ( $in_strings['user_password_string_warning'] ).'</span>';
						}
				$ret .= '</div>';

				$ret .= '<div class="clear_both"></div>';
				$ret .= '<div class="user_editor_line_div_buttons">';
				if ( !$in_user_obj->GetID() )
					{
					$ret .= '<div class="new_submit_div">';
						$ret .= '<img style="display:none" id="user_0_submit_throbber" alt="ajax throbber" class="bmlt_submit_throbber_img" src="placeholder" />';
						$ret .= '<input type="button" onclick="SubmitUser(\'0\',\''.c_comdef_htmlspecialchars($_SERVER['SCRIPT_NAME']).'?edit_cp&amp;open_user\')" value="'.c_comdef_htmlspecialchars ( $in_strings['New_Name'] ).'" id="user_0_submit" disabled="disabled" />';
					$ret .= '</div>';
					}
				else
					{
					// Only server admins can delete users, we can't delete ourselves, and user 1 cannot be deleted.
					if ( c_comdef_server::IsUserServerAdmin() && ($in_user_obj->GetID() > 1) && ($in_user_obj->GetID() != $cur_user->GetID()) )
						{
						$ret .= '<div class="delete_div"><input type="button" value="'.c_comdef_htmlspecialchars ( $in_strings['delete_user_name'] ).'" id="user_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'_delete" onclick="DeleteUser(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')" /></div>';
						}
					$ret .= '<div class="submit_div">';
						$ret .= '<img style="display:none" id="user_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'_submit_throbber" alt="ajax throbber" class="bmlt_submit_throbber_img" src="placeholder" />';
						$ret .= '<input type="button" value="'.c_comdef_htmlspecialchars ( (c_comdef_server::IsUserServerAdmin() ? $in_strings['change_user_name'] : $in_strings['Change_My_Info']) ).'" id="user_'.c_comdef_htmlspecialchars($in_user_obj->GetID()).'_submit" disabled="disabled" onclick="SubmitUser(\''.c_comdef_htmlspecialchars($in_user_obj->GetID()).'\')" />';
					$ret .= '</div>';
					}
				$ret .= '</div>';
				$ret .= '<div class="clear_both"></div>';
			$ret .= '</fieldset>';
			if ( c_comdef_server::IsUserServerAdmin() && $in_user_obj->GetID() )
				{
				$ret .= '</div>';
				}
			}
		}
	return $ret;
	}
?>