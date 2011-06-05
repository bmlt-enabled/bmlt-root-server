<?php
/***********************************************************************/
/** \file	edit_service_bodies.php

	\brief	Displays a form for editing Service bodies. Only Server Admins
	can create and delete bodies, but Service Body Admins that are the principal
	user or an editor of a Service Body in a higher level of the hierarchy
	can edit the data in a Service Body.

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
/** \brief	This returns a form, used to edit all the Service Bodies.

	\returns a string, containing the HTML for the form
*/
function DisplayServiceBodyEditor ( $in_http_vars	///< An associative array, containing the combined $_GET and $_POST parameters.
									)
	{
	$server = c_comdef_server::MakeServer();
	$ret = null;

	$cur_user =& c_comdef_server::GetCurrentUserObj();
			
	if ( ($cur_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) || c_comdef_server::IsUserServerAdmin() )
		{
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
			
			if ( $cur_user instanceof c_comdef_user )
				{
				$ret = "<div id=\"edit_service_container_div\" class=\"edit_service_div_";
				if ( preg_match ( "|open_sb|", $_SERVER['QUERY_STRING'] ) )
					{
					$ret .= 'open';
					}
				else
					{
					$ret .= 'closed';
					}
				$ret .= "\"><a class=\"edit_service_div_a\" href=\"javascript:ToggleNewServiceDiv()\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['Edit_Bodies'] ).$localized_strings['prompt_delimiter']."</a><form class=\"sb_editor_form\" action=\"#\" method=\"post\" onsubmit=\"return false\"><div id=\"service_body_editor_list_div\" class=\"service_body_editor_div\" style=\"display:";
				if ( preg_match ( "|open_sb|", $_SERVER['QUERY_STRING'] ) )
					{
					$ret .= 'block';
					}
				else
					{
					$ret .= 'none';
					}
				$ret .= "\">";
	
				$sb_hierarchical = $server->GetServiceBodyArrayHierarchical();
				
				if ( is_array ( $sb_hierarchical ) && count ( $sb_hierarchical ) )
					{
					$sb_hierarchical = $sb_hierarchical['dependents'];
					
					$ret .= '<div class="sb_nesting">';
					foreach ( $sb_hierarchical as &$sb_arr )
						{
						$servicebody_obj =& $sb_arr['object'];
						
						if ( isset ( $sb_arr['dependents'] ) )
							{
							$children =& $sb_arr['dependents'];
							}
						else
							{
							$children = null;
							}
						
						if ( $servicebody_obj instanceof c_comdef_service_body )
							{						
							$ret .= DisplayOneServiceBodyEditor ( $servicebody_obj, $in_http_vars, $children );
							}
						}
					$ret .= '</div>';
					}
	
				if ( ($any_service_body_admin_can_create_service_bodies && ($cur_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN)) || c_comdef_server::IsUserServerAdmin() )
					{
					$servicebody_obj = null;
					// We add a null at the end in order to have a "create new" form.
					$ret .= DisplayOneServiceBodyEditor ( $servicebody_obj, $in_http_vars, null );
					}
	
				$ret .= '</div></form></div>';
				}
			}
		}
	
	return $ret;
	}
		
/*******************************************************************/
/** \brief	Returns the HTML for one Service Body

	\returns a string, containing the HTML for the service body.
*/
function DisplayOneServiceBodyEditor (	&$in_servicebody_obj,	///< A reference to a c_comdef_service_body instance. If the instance is null, it will create a new Service Body fieldset.
										$in_http_vars,			///< The current HTTP variables.
										$in_children			///< An associative nested array of references to "child" Service Bodies.
										)
	{
	$cur_user =& c_comdef_server::GetCurrentUserObj();
	
	$ret = null;

	if ( ($cur_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) || c_comdef_server::IsUserServerAdmin() )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();
				
		// This can be changed in the auto config.
		$location_of_images = dirname ( $_SERVER['SCRIPT_NAME'] )."/../client/html/images";
		$location_of_throbber = "$location_of_images/Throbber.gif";
	
		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
		
		if ( !isset ( $in_http_vars['script_name'] ) || !$in_http_vars['script_name'] )
			{
			$script_name = preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] );
			}
		else
			{
			$script_name = $in_http_vars['script_name'];
			}
		
		if ( null == $in_servicebody_obj && (($any_service_body_admin_can_create_service_bodies && ($cur_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN)) || c_comdef_server::IsUserServerAdmin()) )
			{
			$in_servicebody_obj = new c_comdef_service_body;
			if ( $in_servicebody_obj instanceof c_comdef_service_body )
				{
				$in_servicebody_obj->SetLocalName ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['New_Name'] );
				$in_servicebody_obj->SetPrincipalUserID($cur_user->GetID());
				$in_servicebody_obj->SetID(0);
				}
			}
		
		if ( ($in_servicebody_obj instanceof c_comdef_service_body) && $in_servicebody_obj->UserCanEdit($cur_user) )
			{
			if ( $in_servicebody_obj->GetID() )
				{
				$ret .= '<div class="edit_one_sb_div_closed" id="edit_one_div_sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'">';
				$ret .= "<a class=\"edit_one_sb_a_closed\" id=\"edit_one_div_sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."_a\" href=\"javascript:ToggleOneSBEditDiv(".c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).")\">";
				$ret .= c_comdef_htmlspecialchars ( $in_servicebody_obj->GetLocalName() ).$localized_strings['prompt_delimiter'];
				$ret .= "</a>";
				}
			
			$ret .= '<fieldset id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_servicebodyeditor" class="service_body_display_fieldset';
			if ( !$in_servicebody_obj->GetID() )
				{
				$ret .= '_new';
				}
			if ( $in_servicebody_obj->GetID() )
				{
				$ret .= '" style="display:none';
				}
			$ret .= '">';
				$ret .= '<legend id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_legend">';
				if ( $in_servicebody_obj->GetID() )
					{
					$ret .= '('.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).') ';
					}
				
				$ret .= c_comdef_htmlspecialchars($in_servicebody_obj->GetLocalName()).'</legend>';
				$ret .= '<input type="hidden" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_original_id" value="'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'" />';
				$ret .= '<div class="sb_edit_div">';
					$ret .= '<label for="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_worldid_mixed">';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['World_ID'] ).$localized_strings['prompt_delimiter'];
					$ret .= '</label>';
					$ret .= '<input type="text" size="64" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_worldid_mixed" value="';
					$ret .= c_comdef_htmlspecialchars($in_servicebody_obj->GetWorldID());
					$ret .= "\" onkeyup=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" />";
				$ret .= '</div>';
				$ret .= '<div class="sb_edit_div">';
					$ret .= '<label for="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_name_string">';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['Name'] ).$localized_strings['prompt_delimiter'];
					$ret .= '</label>';
					$ret .= '<input type="text" size="64" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_name_string" value="';
					if ( $in_servicebody_obj->GetID() )
						{
						$ret .= c_comdef_htmlspecialchars($in_servicebody_obj->GetLocalName());
						}
					$ret .= "\" onkeyup=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" />";
				$ret .= '</div>';
				$ret .= '<div class="sb_edit_div">';
					$ret .= '<label for="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_description_string">';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['Description'] ).$localized_strings['prompt_delimiter'];
					$ret .= '</label>';
					$ret .= '<textarea class="sb_desc_textarea" cols="64" rows="5" onkeyup="EnableSBChangeButton(\'sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'\')" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_description_string" onchange="EnableSBChangeButton(\'sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\">";
					$ret .= c_comdef_htmlspecialchars($in_servicebody_obj->GetLocalDescription());
					$ret .= "</textarea>";
				$ret .= '</div>';
				$ret .= '<div class="sb_edit_div">';
					$ret .= '<label for="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_lang_enum">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['Language'] ).$localized_strings['prompt_delimiter'].'</label><select id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_lang_enum"'." onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\">";
						$language = c_comdef_server::GetServerLangs();
						
						foreach ( $language as $key => $value )
							{
							$ret .= "<option value=\"$key\"";
							if ( $key == $in_servicebody_obj->GetLocalLang() )
								{
								$ret .= " selected=\"selected\"";
								}
							$ret .= ">".c_comdef_htmlspecialchars ( trim ( $value ) )."</option>\n";
							}
					$ret .= '</select>';
				$ret .= '</div>';
			$ret .= '<div class="sb_edit_div">';
				$bd_array = c_comdef_server::GetServer()->GetAllServiceIDs();
				$ret .= "<label for=\"sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."_parent_bigint\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['Primary_Parent'] ).$localized_strings['prompt_delimiter']."</label> <select id=\"sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."_parent_bigint\" onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\">";
					$sel = false;
					foreach ( $bd_array as $key => $sb_id )
						{
						if ( !$in_servicebody_obj->GetID() || ((intval ( $in_servicebody_obj->GetID() ) != intval ( $sb_id )) && !IsSBRecursive ( $in_servicebody_obj->GetID(), $sb_id )) )
							{
							$ret .= "<option value=\"$sb_id\"";
							if ( intval ( $in_servicebody_obj->GetOwnerID() ) == intval ( $sb_id ) )
								{
								$ret .= " selected=\"selected\"";
								$sel = true;
								}
							$ret .= ">".c_comdef_htmlspecialchars ( $key )."</option>";
							}
						}
					$ret .= "<option value=\"\" disabled=\"disabled\"></option><option value=\"\"";
					
					if ( !$sel )
						{
						$ret .= " selected=\"selected\"";
						}
						
					$ret .= ">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['no_primary_owner'] )."</option>";
				$ret .= "</select>";
			$ret .= '</div>';
			
			$ret .= '<div class="sb_edit_div">';
				$ret .= "<label for=\"sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."_type\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['sb_type'] ).$localized_strings['prompt_delimiter']." </label><select id=\"sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."_type\" onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\">";
					$sel = false;
					$bd_array = array ( c_comdef_service_body__GRP__ => "Group", c_comdef_service_body__ASC__ => "Area Service Committee", c_comdef_service_body__RSC__ => "Regional Service Committee", c_comdef_service_body__WSC__ => "World Service Committee", c_comdef_service_body__MAS__ => "Metro Area", c_comdef_service_body__ZFM__ => "Zonal Forum" );
					foreach ( $bd_array as $key => $type )
						{
						$ret .= "<option value=\"".c_comdef_htmlspecialchars ( $key )."\"";
						if ( $in_servicebody_obj->GetSBType() == $key )
							{
							$ret .= " selected=\"selected\"";
							$sel = true;
							}
						$ret .= ">".c_comdef_htmlspecialchars ( $type )."</option>";
						}
					if ( !$sel )
						{
						$ret .= "<option value=\"\" selected=\"selected\" disabled=\"disabled\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['select_sb'] )."</option>";
						}
						
				$ret .= "</select>";
			$ret .= '</div>';
			$ret .= '<div class="sb_edit_div">';
				$users = c_comdef_server::GetUsersByLevelObj(_USER_LEVEL_SERVICE_BODY_ADMIN );
				$ret .= '<label for="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_principal_user_bigint">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['sb_admin'] ).$localized_strings['prompt_delimiter'].' </label><select id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_principal_user_bigint"'." onchange=\"ServiceBodyAdminChanged('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."',this.value);EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\">";
					foreach ( $users as &$user )
						{
						$ret .= "<option value=\"".c_comdef_htmlspecialchars($user->GetID())."\"";
						if ( $in_servicebody_obj->GetID() )
							{
							if ( $user->GetID() == $in_servicebody_obj->GetPrincipalUserID() )
								{
								$ret .= " selected=\"selected\"";
								}
							}
						else
							{
							if ( $user->GetID() == $cur_user->GetID() )
								{
								$ret .= " selected=\"selected\"";
								}
							}
						$ret .= ">".c_comdef_htmlspecialchars($user->GetLocalName())."</option>\n";
						}
				$ret .= '</select>';
			$ret .= '</div>';
			$ret .= '<div class="sb_edit_div">';
				$ret .= '<div class="sb_editor_check_label">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['sb_editors'] ).$localized_strings['prompt_delimiter'].'</div>';
					$ret .= '<div class="sb_checkbox_array_div" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_checkboxes">';
					$users = c_comdef_server::GetUsersByLevelObj( _USER_LEVEL_OBSERVER, true );
					$editors = $in_servicebody_obj->GetEditors();
					foreach ( $users as &$user )
						{
						// We don't allow disabled or server admins to be listed here.
						if ( ($user->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) )
							{
							$ret .= '<div class="sb_user_check_div"><div class="sb_check_div"><input type="checkbox" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_editor_'.c_comdef_htmlspecialchars($user->GetID()).'" value="'.c_comdef_htmlspecialchars($user->GetID()).'"';
							if ( $user->GetID() == $in_servicebody_obj->GetPrincipalUserID() )
								{
								$ret .= " disabled=\"disabled\"";
								}
							elseif ( in_array ( intval ( $user->GetID() ), $editors ) )
								{
								$ret .= ' checked="checked"';
								}
							$ret .= " onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" /></div><div class=\"sb_check_title_div\">";
							$ret .= "<label for=\"sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_editor_'.c_comdef_htmlspecialchars($user->GetID())."\" class=\"";
							$ret .= (($user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) ? 'service_body_admin_label' : 'meeting_list_editor_label');
							$ret .= "\">".c_comdef_htmlspecialchars($user->GetLocalName()).' ('.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Users'][$user->GetUserLevel()] ).')'.'</label></div></div>';
							}
						}
					$ret .= '</div>';
			$ret .= '</div>';
			$ret .= '<div class="sb_edit_div">';
				$ret .= '<label for="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_uri_string">';
				$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['sb_uri'] ).$localized_strings['prompt_delimiter'].' ';
				$ret .= '</label>';
				$ret .= '<input type="text" size="64" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_uri_string" value="';
				$ret .= c_comdef_htmlspecialchars($in_servicebody_obj->GetURI());
				$ret .= "\" onkeyup=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" />";
			$ret .= '</div>';
			$ret .= '<div class="sb_edit_div">';
				$ret .= '<label for="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_kml_uri_string">';
				$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['sb_kml_uri'] ).$localized_strings['prompt_delimiter'].' ';
				$ret .= '</label>';
				$ret .= '<input type="text" size="64" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_kml_uri_string" value="';
				$ret .= c_comdef_htmlspecialchars($in_servicebody_obj->GetKMLURI());
				$ret .= "\" onkeyup=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" />";
			$ret .= '</div>';
			$ret .= '<div class="sb_edit_div">';
				$ret .= '<label for="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_sb_meeting_email">';
				$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['sb_ContactEmail'] ).$localized_strings['prompt_delimiter'];
				$ret .= '</label>';
				$ret .= '<input type="text" size="64" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_sb_meeting_email" value="';
				if ( $in_servicebody_obj->GetID() )
					{
					$ret .= c_comdef_htmlspecialchars($in_servicebody_obj->GetContactEmail(false));
					}
				$ret .= "\" onkeyup=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" onchange=\"EnableSBChangeButton('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."')\" />";
			$ret .= '</div>';
			$ret .= '<div class="sb_button_div">';
			if ( !$in_servicebody_obj->GetID() )
				{
				$ret .= '<div class="new_submit_div">';
					$ret .= '<img style="display:none" id="sb_0_submit_throbber" alt="ajax throbber" class="bmlt_submit_throbber_img" src="placeholder" />';
					$ret .= '<input type="button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['New_Name'] ).'" id="sb_0_submit" disabled="disabled"'." onclick=\"SubmitServiceBody('sb_0','".c_comdef_htmlspecialchars($_SERVER['SCRIPT_NAME'])."?edit_cp&amp;open_sb')\" />";
				$ret .= "</div>";
				}
			else
				{
				if ( ($any_service_body_admin_can_create_service_bodies && ($cur_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN)) || c_comdef_server::IsUserServerAdmin() )
					{
					$ret .= '<div class="delete_div"><input type="button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['delete_sb_name'] ).'" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_delete" onclick="DeleteServiceBody(\''.c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."','".c_comdef_htmlspecialchars($_SERVER['SCRIPT_NAME'])."?edit_cp&amp;open_sb')\" /></div>";
					}
				$ret .= '<div class="submit_div">';
					$ret .= '<img style="display:none" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_submit_throbber" alt="ajax throbber" class="bmlt_submit_throbber_img" src="placeholder" />';
					$ret .= '<input type="button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['change_sb_name'] ).'" id="sb_'.c_comdef_htmlspecialchars($in_servicebody_obj->GetID()).'_submit" disabled="disabled"'." onclick=\"SubmitServiceBody('sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."','sb_".c_comdef_htmlspecialchars($in_servicebody_obj->GetID())."_submit')\" />";
				$ret .= "</div>";
				}
			$ret .= '</div><div class="sb_clear_both"></div>';
			$ret .= '<div class="sb_nesting">';
			if ( is_array ( $in_children ) && count ( $in_children ) )
				{
				foreach ( $in_children as &$sb_arr )
					{
					$servicebody_obj =& $sb_arr['object'];
					
					if ( isset ( $sb_arr['dependents'] ) )
						{
						$children =& $sb_arr['dependents'];
						}
					else
						{
						$children = null;
						}
					
					if ( $servicebody_obj instanceof c_comdef_service_body )
						{						
						$ret .= DisplayOneServiceBodyEditor ( $servicebody_obj, $in_http_vars, $children );
						}
					}
				}
			$ret .= '</div>';
			$ret .= '<div class="sb_clear_both"></div>';
			$ret .= '</fieldset>';
			if ( $in_servicebody_obj->GetID() )
				{
				$ret .= '</div>';
				}
	
			$first_sb = false;
			}
		elseif ( is_array ( $in_children ) && count ( $in_children ) )
			{
			foreach ( $in_children as &$sb_arr )
				{
				$servicebody_obj =& $sb_arr['object'];
				
				if ( isset ( $sb_arr['dependents'] ) )
					{
					$children =& $sb_arr['dependents'];
					}
				else
					{
					$children = null;
					}
				
				if ( $servicebody_obj instanceof c_comdef_service_body )
					{						
					$ret .= DisplayOneServiceBodyEditor ( $servicebody_obj, $in_http_vars, $children );
					}
				}
			}
		}
	
	return $ret;
	}

/*******************************************************************/
/** \brief	Check to see if a Service Body might loop, if introduced
	to a given hierarchy.

	\returns a boolean. True if the given Service Body appears in the
	hierarchy above it.
*/
function IsSBRecursive (	$in_sb_id,			///< The ID of the Service Body to check.
							$in_hierarchy_id	///< An ID for the Service Body Hierarchy to Check.
						)
	{
	$ret = false;
	$server =& c_comdef_server::GetServer();
	
	if ( $server instanceof c_comdef_server )
		{
		$sb_to_check =& $server->GetServiceBodyByIDObj($in_hierarchy_id);
		
		$parent = $sb_to_check->GetOwnerID();
		$parent2 = $sb_to_check->GetOwner2ID();
		
		if ( ($parent == $in_sb_id) || ($parent2 == $in_sb_id) )
			{
			$ret = true;
			}
		else
			{
			if ( $parent )
				{
				$sb_to_check =& $server->GetServiceBodyByIDObj($parent);
				
				if ( $sb_to_check instanceof c_comdef_service_body )
					{
					$ret = IsSBRecursive ( $in_sb_id, $sb_to_check->GetID() );
					}
				}
			
			if ( !$ret && $parent2 )
				{
				$sb_to_check =& $server->GetServiceBodyByIDObj($parent2);
			
				if ( $sb_to_check instanceof c_comdef_service_body )
					{
					$ret = IsSBRecursive ( $in_sb_id, $sb_to_check->GetID() );
					}
				}
			}
		}
	
	return $ret;
	}
?>
