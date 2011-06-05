<?php
/***********************************************************************/
/** \file	edit_meeting_ajax.php

	\brief	This simply verifies the editor, and gets the meeting edit display HTML.

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

	require_once ( dirname ( __FILE__ ).'/../server/c_comdef_server.class.php' );
	
	session_start();
	$server = c_comdef_server::MakeServer();
	
	if ( $server instanceof c_comdef_server )
		{
		$meeting_id = intval ( $_GET['single_meeting_id'] );
		$meeting_obj = c_comdef_server::GetOneMeeting ( $meeting_id );
		
		if ( $meeting_obj instanceof c_comdef_meeting )
			{
			if ( $meeting_obj->UserCanEdit() )
				{
				$opt = DisplayMeetingForEdit ( $meeting_obj );
				$opt = preg_replace('/<!--(.|\s)*?-->/', '', $opt);
				$opt = preg_replace('/\/\*(.|\s)*?\*\//', '', $opt);
				$opt = preg_replace( "|\s+\/\/.*|", " ", $opt );
				$opt = preg_replace( "/\s+/", " ", $opt );
				echo $opt;
				}
			}
		}

/*******************************************************************/
/** \brief	This is a sort comparator callback. Its purpose is to
			make sure that certain data items sort properly in the
			data item list.

	\returns an integer. -1 if $a > $b, 1 if $ < $b, 0 if they are equal.
*/
function DataItemCallback ( $a,
							$b
							)
{
	$ret = 0;
	
	if ( $a == "copy" )
		{
		$ret = -1;
		}
	elseif ( $b == "copy" )
		{
		$ret = 1;
		}
	elseif ( $a == "meeting_name" )
		{
		$ret = -1;
		}
	elseif ( $b == "meeting_name" )
		{
		$ret = 1;
		}
	else
		{
		$ret = strcmp ( $a, $b );
		}
	
	return $ret;
}

/*******************************************************************/
/** \brief	This returns HTML for a form to edit a single meeting.
	The editing itself is done via AJAX calls.

	\returns a string, containing the HTML for the form
*/
function DisplayMeetingForEdit ( $in_mtg_object )
{
	$ret = null;
	
	if ( $in_mtg_object instanceof c_comdef_meeting )
		{
		// The first thing we do is fetch the server settings and the language strings.
		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );

		$localized_strings = c_comdef_server::GetLocalStrings();
		
		$_id = $in_mtg_object->GetID();
		$meeting_id = 'meeting_'.$_id;
		
		// We access the meeting data directly from the object's array.
		$meeting_data = $in_mtg_object->GetMeetingData();
		$lang = $meeting_data['lang_enum'];
		$weekday_index = intval ( $meeting_data['weekday_tinyint'] ) -1;
		
		$ret = "<div class=\"c_comdef_meeting_edit_div";
		
		if ( $in_mtg_object->IsCopy() )
			{
			$ret .= " meeting_copy";
			}
		elseif ( !$in_mtg_object->IsPublished() )
			{
			$ret .= " meeting_unpub";
			}

		$ret .= "\" id=\"main_$meeting_id"."_meeting_div\">";
		$ret .= '<div class="c_comdef_edit_close_div top_close_div"><a href="javascript:CloseMeetingEditor()">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['close_window'] ).'</a></div>';
		$ret .= "<form action=\"#\" id=\"$meeting_id"."_form\" onsubmit=\"SubmitMeeting('$meeting_id','$meeting_id"."_submit_data_item');return false\">";
		
		// Display the "Published" checkbox.
		$title = null;
		$ret .= "<div class=\"checkbox_div centered_check\">";
			$ret .= '<input type="checkbox" id="'.$meeting_id.'_published" name="'.$meeting_id.'_published" value="1"';
				if ( (intval ( $meeting_data['published'] ) != 0) && !isset ($meeting_data['copy']) )
					{
					$ret .= ' checked="checked"';
					}
				
				if ( c_comdef_server::GetCurrentUserObj(true)->GetUserLevel() == _USER_LEVEL_EDITOR )
					{
					// Meeting List Editors are not allowed to work on published meetings.
					if ( intval ( $meeting_data['published'] ) != 0 )
						{
						return c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
						}
					
					$title = ' title="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['cant_publish_meetinglist_admin'] ).'"';
					$ret .= ' disabled="disabled"';
					}
				elseif ( !(isset ( $meeting_data['longitude'] ) && isset ( $meeting_data['latitude'] ) && ($meeting_data['longitude'] != 0.0) && ($meeting_data['latitude'] != 0.0)) )
					{
					$title = ' title="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['cant_publish_no_location'] ).'"';
					$ret .= ' disabled="disabled"';
					}
				elseif ( isset ($meeting_data['copy']) )
					{
					$title = ' title="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['cant_publish_duplicate'] ).'"';
					$ret .= ' disabled="disabled"';
					}
			$ret .= $title;
			$ret .= ' onchange="EnableMeetingChangeButton(\''.$meeting_id.'\', false)" />';
			$ret .= "<label class=\"checkbox_label\" for=\"$meeting_id"."_published\"";
			$ret .= $title;
			$ret .= ">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['published'] )."</label> ";
		$ret .= "</div>";

		// Display the weekday popup.			
		$ret .= '<div class="one_meeting_div">';
		$weekday = $localized_strings['weekdays'][$weekday_index];
		$value = $meeting_data['weekday_tinyint'];
		$ret .= "<div class=\"meeting_value_div\">";
		$ret .= "<label for=\"$meeting_id"."_weekday_tinyint\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['weekday'] ).$localized_strings['prompt_delimiter']."</label> ";
		$ret .= "<select id=\"$meeting_id"."_weekday_tinyint\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\">";
		for ( $i = 0; $i < 7; $i++ )
			{
			$ret .= "<option value=\"$i\"";
			if ( (intval ( $value ) -1) == $i )
				{
				$ret .= " selected=\"selected\"";
				}
			$ret .= ">".c_comdef_htmlspecialchars ( $localized_strings['weekdays'][$i] );
			if ( (intval ( $value ) -1) == $i )
				{
				$ret .= " *";
				}
			$ret .= "</option>";
			}
		$ret .= "</select>";
		$ret .= "</div>";
	
		// Display the Start Time Popups.
		$value = $meeting_data['start_time'];
		$ret .= "<div class=\"meeting_value_div\">";
		$time1 = strtotime ( $value );
		$ret .= "<label for=\"$meeting_id"."_start_time_hour\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['starts_at'] ).$localized_strings['prompt_delimiter']."</label> ";
		$h_val = intval ( date ( "G", $time1 ) );
		$m_val = intval ( date ( "i", $time1 ) );
		$ret .= "<select id=\"$meeting_id"."_start_time_hour\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\">";
		for ( $h = 0; $h < 24; $h++ )
			{
			$ret .= "<option value=\"$h\"";
			if ( $h == $h_val )
				{
				$ret .= " selected=\"selected\"";
				}
			if ( $h < 10 )
				{
				$h = "0$h";
				}
			$ret .= ">$h";
			if ( $h == $h_val )
				{
				$ret .= " *";
				}
			$ret .= "</option>";
			}
		$ret .= "</select>";
		$ret .= "<select id=\"$meeting_id"."_start_time_minute\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\">";
		for ( $m = 0; $m < 59; $m += 5 )
			{
			$ret .= "<option value=\"$m\"";
			if ( $m == $m_val )
				{
				$ret .= " selected=\"selected\"";
				}
			
			if ( $m < 10 )
				{
				$m = "0$m";
				}
			$ret .= ">$m";
			if ( $m == $m_val )
				{
				$ret .= " *";
				}
			$ret .= "</option>";
			}
		$ret .= "<option value=\"59\"";
		if ( 59 == $m_val )
			{
			$ret .= " selected=\"selected\"";
			}
		$ret .= ">59";
		if ( 59 == $m_val )
			{
			$ret .= " *";
			}
		$ret .= "</option>";
		$ret .= "</select>";
			
		$ret .= "</div>";
	
		$value = $meeting_data['duration_time'];
		
		// Display the Meeting Duration popups.
		$ret .= "<div class=\"meeting_value_div\">";
		$ret .= "<label for=\"$meeting_id"."_duration_time_hour\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['duration'] ).$localized_strings['prompt_delimiter']."</label> ";
		$time1 = strtotime ( $value );
		$h_val = intval ( date ( "G", $time1 ) );
		$m_val = intval ( date ( "i", $time1 ) );
		$ret .= "<select id=\"$meeting_id"."_duration_time_hour\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\">";
			for ( $h = 0; $h < 24; $h++ )
				{
				$ret .= "<option value=\"$h\"";
				if ( $h == $h_val )
					{
					$ret .= " selected=\"selected\"";
					}
				$ret .= ">$h";
				if ( $h == $h_val )
					{
					$ret .= " *";
					}
				$ret .= "</option>";
				}
		$ret .= "</select>";
		$ret .= "<select id=\"$meeting_id"."_duration_time_minute\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\">";
			for ( $m = 0; $m < 60; $m += 15 )
				{
				$ret .= "<option value=\"$m\"";
				if ( $m == $m_val )
					{
					$ret .= " selected=\"selected\"";
					}
				if ( $m < 10 )
					{
					$m = "0$m";
					}
				$ret .= ">$m";
				if ( $m == $m_val )
					{
					$ret .= " *";
					}
				$ret .= "</option>";
				}
		$ret .= "</select>";
		$ret .= "</div>";
		
		// Display the meeting ID (non-editable).
		$ret .= "<div class=\"meeting_value_div\">";
		$ret .= "<label>".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$localized_strings['prompt_delimiter']."</label>";
		$ret .= "&nbsp;";
		$ret .= c_comdef_htmlspecialchars ( str_replace ( "meeting_", "", $meeting_id ) );
		$ret .= "</div>";
	
		// Display the World ID.
		$ret .= "<div class=\"meeting_value_div\">";
		$ret .= "<label for=\"$meeting_id"."_worldid_mixed\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['world_id'] ).$localized_strings['prompt_delimiter']."</label>";
		$ret .= "&nbsp;";
		$ret .= "<input type=\"text\" id=\"$meeting_id"."_worldid_mixed\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\" onkeyup=\"EnableMeetingChangeButton('$meeting_id', false)\" value=\"";
		$ret .= c_comdef_htmlspecialchars ( $meeting_data['worldid_mixed'] );
		$ret .= "\" />&nbsp;<input class=\"reset_button\" type=\"button\" value=\"*\" onclick=\"document.getElementById('$meeting_id"."_worldid_mixed').value='".c_comdef_htmlspecialchars ( $meeting_data['worldid_mixed'] )."'\" />";
		$ret .= "</div>";
	
		// Display the Contact Email.
		$ret .= "<div class=\"meeting_value_div\">";
		$ret .= "<label title=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_contact_note'] )."\" for=\"$meeting_id"."_email_contact\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_contact'] ).$localized_strings['prompt_delimiter']."</label>";
		$ret .= "&nbsp;";
		$ret .= "<input title=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_contact_note'] )."\" type=\"text\" id=\"$meeting_id"."_email_contact\" size=\"40\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\" onkeyup=\"EnableMeetingChangeButton('$meeting_id', false)\" value=\"";
		$ret .= c_comdef_htmlspecialchars ( $meeting_data['email_contact'] );
		$ret .= "\" />&nbsp;<input title=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_contact_note'] )."\" class=\"reset_button\" type=\"button\" value=\"*\" onclick=\"document.getElementById('$meeting_id"."_email_contact').value='".c_comdef_htmlspecialchars ( $meeting_data['email_contact'] )."'\" />";
		$ret .= "</div>";
		
		// Display the Service Body selector.
		$bd_array = c_comdef_server::GetServer()->GetServiceBodyArray();
		if ( is_array ( $bd_array ) && count ( $bd_array ) )
			{
			$ret .= "<div class=\"meeting_value_div\">";
			$ret .= "<label for=\"$meeting_id"."_service_body_bigint\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['service_body'] ).$localized_strings['prompt_delimiter']."</label>";
			$ret .= "&nbsp;";
			$ret .= "<select id=\"$meeting_id"."_service_body_bigint\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\">";
			foreach ( $bd_array as &$sb )
				{
				$sb_id = $sb->GetID();
				$sb =& c_comdef_server::GetServiceBodyByIDObj ( $sb_id );
				if ( ($sb instanceof c_comdef_service_body)
					&& ((c_comdef_server::IsUserServerAdmin()) || $sb->IsUserInServiceBodyHierarchy()) )
					{
					$ret .= '<option value="'.c_comdef_htmlspecialchars ( $sb->GetID() ).'"';
					if ( intval ( $meeting_data['service_body_bigint'] ) == $sb->GetID() )
						{
						$ret .= ' selected="selected"';
						}
					$ret .= ">".c_comdef_htmlspecialchars ( $sb->GetLocalName() );
					if ( intval ( $meeting_data['service_body_bigint'] ) == $sb->GetID() )
						{
						$ret .= " *";
						}
					$ret .= "</option>";
					}
				}
			$ret .= "</select>";
			$ret .= "</div>";
			}
		
		// Display the meeting language selector.
		$value = $meeting_data['lang_enum'];
		$language1 = c_comdef_server::GetServerLangs();
		
		$language = $language1[$value];
		
		$ret .= "<div class=\"meeting_value_div\">";
		$ret .= "<label for=\"$meeting_id"."_lang_enum\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['language'] ).$localized_strings['prompt_delimiter']."</label>";
		$ret .= "&nbsp;";
		$ret .= "<select id=\"$meeting_id"."_lang_enum\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\">";
		foreach ( $language1 as $key => $val )
			{
			$ret .= "<option value=\"$key\"";
			if ( $value == $key )
				{
				$ret .= " selected=\"selected\"";
				}
			$ret .= ">".c_comdef_htmlspecialchars ( trim ( stripslashes ( $val ) ) );
			if ( $value == $key )
				{
				$ret .= " *";
				}
			$ret .= "</option>";
			}
		$ret .= "</select>";
		$ret .= "</div>";
	
		// Display the meeting location selector (longitude and latitude).
		$long = (isset ( $meeting_data['longitude'] ) ? $meeting_data['longitude'] : 0.0);
		$lat = (isset ( $meeting_data['latitude'] ) ? $meeting_data['latitude'] : 0.0);
		
		// 0,0 is considered an empty long/lat, so we center on the default center.
		if ( ($long == 0.0) && ($lat == 0.0 ) )
			{
			$long = $localized_strings['search_spec_map_center']['longitude'];
			$lat = $localized_strings['search_spec_map_center']['latitude'];
			}
		
		$ret .= "<div class=\"meeting_value_div\" id=\"$meeting_id"."_long_lat_div\">";
			$ret .= "<div class=\"meeting_value_div_inner\" id=\"$meeting_id"."_long_div_inner\">";
				$ret .= "<label>".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['long_label'] ).$localized_strings['prompt_delimiter']."</label>&nbsp;";
				$ret .= "<input type=\"text\" id=\"$meeting_id"."_longitude\" value=\"".c_comdef_htmlspecialchars ( $long )."\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\" onkeyup=\"EnableMeetingChangeButton('$meeting_id', false)\" />";
				$ret .= "&nbsp;<input class=\"reset_button\" type=\"button\" value=\"*\" onclick=\"document.getElementById('$meeting_id"."_longitude').value='".c_comdef_htmlspecialchars ( $long )."';document.getElementById('$meeting_id"."_latitude').value='".c_comdef_htmlspecialchars ( $lat )."';if(g_geocoder_browser_edit_meeting){RevealMap('$meeting_id')}\" />";
			$ret .= "</div>";
			$ret .= "<div class=\"meeting_value_div_inner\" id=\"$meeting_id"."_lat_div_inner\">";
				$ret .= "<label>".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['lat_label'] ).$localized_strings['prompt_delimiter']."</label>&nbsp;";
				$ret .= "<input type=\"text\" id=\"$meeting_id"."_latitude\" value=\"".c_comdef_htmlspecialchars ( $lat )."\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\" onkeyup=\"EnableMeetingChangeButton('$meeting_id', false)\" />";
			$ret .= "</div>";
			
			// The longitude and latitude are displayed in an anchor that triggers a JavaScript function that brings up a map.
			$ret .= "<div class=\"long_lat_link_div\" id=\"$meeting_id"."_long_lat_div_inner\">";
				$ret .= "<a id=\"$meeting_id"."_long_lat_div_a\" href=\"javascript:RevealMap('$meeting_id')\">";
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['set_loc'] );
				$ret .= "</a>";
			$ret .= "</div>";
		$ret .= "</div>";
	
		// Now, we display the various "optional" data fields. They can be removed (deleted), or added from a pre-designated "pool."
		$ret .= "<fieldset class=\"meeting_value_fieldset\" id=\"$meeting_id"."_field_container\">";
		$ret .= "<legend>".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['data_values'] )."</legend>";

		uksort ( $meeting_data, 'DataItemCallback' );
		
		$used_fields = array();
		foreach ( $meeting_data as $key => $value )
			{
			if ( null !== $value )
				{
				switch ( $key )
					{
					case "lang_enum":
					case "id_bigint":
					case "longitude":
					case "latitude":
					case "worldid_mixed":
					case "service_body_bigint":
					case "weekday_tinyint":
					case "start_time":
					case "duration_time":
					case "formats":
					case "email_contact":
					break;
					
					case "copy":
						$value['prompt'] = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['copy_prompt'];
						$value['value'] = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['copy'];
					
					default:
						if ( is_array ( $value ) && $value['prompt'] )
							{
							if ( $key != 'copy' )
								{
								$used_fields[$key] = $value;
								}
							$ret .= "<div id=\"$meeting_id"."_$key"."_deleted_div\" class=\"link_div\" style=\"display:none\">";
							$ret .= "<input type=\"hidden\" id=\"$meeting_id"."_$key"."_deleted_input\" name=\"$meeting_id"."_$key"."_deleted_input\" value=\"\" />";
							$ret .= c_comdef_htmlspecialchars ( $value['prompt'] ).c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['will_be_deleted'] )."<a href=\"javascript:UnDeleteMeetingDataItem('$meeting_id"."_$key"."')\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['follow_link_undel'] )."</a>";
							$ret .= "</div>";
							$ret .= "<fieldset class=\"meeting_value_fieldset\" id=\"$meeting_id"."_$key"."_fieldset\">";
							$ret .= "<legend>".c_comdef_htmlspecialchars ( $value['prompt'] )."</legend>";
		
							if ( $key == 'copy' )
								{
								$ret .= '<div class="duplicate_text_div">'.c_comdef_htmlspecialchars ( trim ( stripslashes ( $value['value'] ) ) )."</div>";
								}
							else
								{
								$ret .= "<div class=\"meeting_value_div\">";
								// We force the location strings to be visible, because they are combined into the location display for every meeting.
								// The only exception is if they are marked as not visible (for whatever reason) in the template.
								$vis_val = intval ( $value['visibility'] );
								
								$can_change_vis = $vis_val || !(($key == 'location_nation') || ($key == 'location_province') || ($key == 'location_sub_province') || ($key == 'location_municipality')
													|| ($key == 'location_city_subsection') || ($key == 'location_neighborhood') || ($key == 'location_street') || ($key == 'meeting_name')
													|| ($key == 'location_info') || ($key == 'location_postal_code_1') || ($key == 'location_postal_code_2') || ($key == 'location_text'));

								if ( $can_change_vis && c_comdef_server::IsUserServerAdmin() )	// Only server admins can override the visibility.
									{
									// Display the Visibility selector.
									$ret .= "<label for=\"$meeting_id"."_$key"."_visibility\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_prompt'] ).$localized_strings['prompt_delimiter']."</label>";
									$ret .= "&nbsp;";
									$ret .= "<select id=\"$meeting_id"."_$key"."_visibility\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\">";
										$ret .= '<option value="'._VISIBILITY_ALL_.'"';
										if ( !$value['visibility'] || !isset ( $value['visibility'] ) )
											{
											$ret .= ' selected="selected"';
											}
										$ret .= '>'.$localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_all'].'</option>';
										
										$ret .= '<option value="'._VISIBILITY_NONE_.'"';
										if ( isset ( $value['visibility'] ) && ( $value['visibility'] == _VISIBILITY_NONE_) )
											{
											$ret .= ' selected="selected"';
											}
										$ret .= '>'.$localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_admin'].'</option>';
										
										$ret .= '<option value="'._VISIBILITY_WEB_MOB_.'"';
										if ( isset ( $value['visibility'] ) && ( $value['visibility'] == _VISIBILITY_WEB_MOB_) )
											{
											$ret .= ' selected="selected"';
											}
										$ret .= '>'.$localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_web_mob'].'</option>';
// Commenting these out to reduce complexity										
// 										$ret .= '<option value="'._VISIBILITY_WEB_.'"';
// 										if ( isset ( $value['visibility'] ) && ( $value['visibility'] == _VISIBILITY_WEB_) )
// 											{
// 											$ret .= ' selected="selected"';
// 											}
// 										$ret .= '>'.$localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_web'].'</option>';
// 										$ret .= '<option value="'._VISIBILITY_MOB_.'"';
// 										if ( isset ( $value['visibility'] ) && ( $value['visibility'] == _VISIBILITY_MOB_) )
// 											{
// 											$ret .= ' selected="selected"';
// 											}
// 										$ret .= '>'.$localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_mob'].'</option>';

										$ret .= '<option value="'._VISIBILITY_PRINT_.'"';
										if ( isset ( $value['visibility'] ) && ( $value['visibility'] == _VISIBILITY_PRINT_) )
											{
											$ret .= ' selected="selected"';
											}
										$ret .= '>'.$localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_print'].'</option>';
									$ret .= "</select>";
									}
								elseif ( $can_change_vis )	// Otherwise, we simply report on what the visibility is.
									{
									$ret .= "<span class=\"value_prompt\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_prompt'] ).$localized_strings['prompt_delimiter']."</span>";
									
									switch ( intval ( $value['visibility'] ) )
										{
										case _VISIBILITY_NONE_:
											$val = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_admin'];
										break;
										
										case _VISIBILITY_WEB_MOB_:
											$val = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_web_mob'];
										break;

										case _VISIBILITY_WEB_:
											$val = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_web'];
										break;
										
										case _VISIBILITY_MOB_:
											$val = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_mob'];
										break;
										
										case _VISIBILITY_PRINT_:
											$val = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_print'];
										break;
										
										default:
											$val = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['visibility_value_all'];
										break;
										}
									
									$ret .= "<span class=\"value_value\">".c_comdef_htmlspecialchars ( $val )."</span>";
									}
								
								$ret .= "</div>";
								
								$ret .= "<textarea class=\"comdef_edit_field_textarea\" id=\"$meeting_id"."_$key\" cols=\"50\" rows=\"".($value['longdata'] ? "5" : "1")."\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\" onkeyup=\"EnableMeetingChangeButton('$meeting_id', false)\">".c_comdef_htmlspecialchars ( trim ( stripslashes ( $value['value'] ) ) )."</textarea>";
								}
							if ( $key != 'copy' )
								{
								$ret .= "&nbsp;<input class=\"reset_button\" type=\"button\" value=\"*\" onclick=\"document.getElementById('$meeting_id"."_$key').value='".c_comdef_htmlspecialchars ( trim ( stripslashes ( $value['value'] ) ) )."'\" />";
								}
							$ret .= "<div class=\"data_del_button_div\"><input type=\"button\" value=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['button_delete_dataitem'] )."\" onclick=\"DeleteMeetingDataItem ('$meeting_id"."_$key"."');EnableMeetingChangeButton('$meeting_id', false)\" /></div>";
							$ret .= "</fieldset>";
							}
					break;
					}
				}
			}
		// This is where new fields are inserted by the AJAX routines.
		$ret .= "<div id=\"$meeting_id"."_last_place_insert\"></div>";
		
		// This is where we get a list of the available "optional" fields to put in a popup for adding a new one.
		$data_obj1 = c_comdef_meeting::GetDataTableTemplate();
		$longdata_obj = c_comdef_meeting::GetLongDataTableTemplate();
		
		// We merge the two tables (data and longdata).
		if ( is_array ( $data_obj1 ) && count ( $data_obj1 ) && is_array ( $longdata_obj ) && count ( $longdata_obj ) )
			{
			$data_obj1 = array_merge ( $data_obj1, $longdata_obj );
			}
		
		// Sort them by their field keys, so we have a consistent order.
		ksort ( $data_obj1 );
		
		// Weed out the fields we already have, so only the unused fields are available. This will be an empty array if we have exhausted them.
		$data_obj = array_diff_key ( $data_obj1, $used_fields );

		// If there are fields that can be added, we show this div. Otherwise, we hide it.
		$ret .= "<div id=\"$meeting_id"."_add_new_div\" class=\"link_div\" style=\"display:";
		$ret .= ( is_array ( $data_obj ) && count ( $data_obj ) ) ? "inherit" : "none";
		$ret .= "\">";
		$ret .= "<a href=\"javascript:AddNewDataItem('$meeting_id');EnableMeetingChangeButton('$meeting_id', false)\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['follow_link_add'] )."</a>";
		$ret .= "</div>";
		$ret .= "<fieldset style=\"display:none\" class=\"meeting_value_fieldset\" id=\"$meeting_id"."_new_fieldset\">";
		$ret .= "<legend>";
		$ret .= "<select id=\"$meeting_id"."_new_key\" name=\"$meeting_id"."_new_key\">";
		$first = "";
		foreach ( $data_obj as &$do )
			{
			$key = $do['key'];
			$prompt = $do['field_prompt'];
			$ret .= "<option value=\"".c_comdef_htmlspecialchars ( $key )."\"";
			if ( !$first )
				{
				$ret .= " selected=\"selected\"";
				$first = $prompt;
				}
			$ret .= ">".c_comdef_htmlspecialchars ( $prompt )."</option>";
			}
		$ret .= "</select></legend>";
		
		$ret .= "<textarea id=\"$meeting_id"."_new_textarea\" cols=\"64\" rows=\"3\" onchange=\"EnableMeetingChangeButton('$meeting_id', false)\" onkeyup=\"EnableMeetingChangeButton('$meeting_id', false)\"></textarea>";
		$ret .= "<div class=\"data_button_div\">";
			$ret .= "<div class=\"data_button_div_left\">";
				$ret .= "<input type=\"button\" value=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['button_do_not_add'] )."\" onclick=\"DoNotAddDataItem ('$meeting_id')\" />";
			$ret .= "</div>";
			$ret .= "<div class=\"data_button_div_right\">";
				$ret .= "<img style=\"display:none\" id=\"$meeting_id"."_submit_data_item_throbber_img\" alt=\"ajax throbber\" class=\"bmlt_submit_throbber_img\" src=\"\" />";
				$ret .= "<input disabled=\"disabled\" id=\"$meeting_id"."_submit_data_item\" type=\"button\" value=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['button_submit_changes'] )."\" onclick=\"SubmitMeeting('$meeting_id','$meeting_id"."_submit_data_item')\" />";
			$ret .= "</div>";
			$ret .= "<div class=\"clear_both\"></div>";
		$ret .= "</div>";
		$ret .= "</fieldset>";
		$ret .= "</fieldset>";
		
		$ret .= '<fieldset id="formats_fieldset" class="formats_fieldset">';
			$ret .= '<legend class="format_sort_legend">';
				$ret .= '<label for="sort_formats_by">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['sort_select_label'] ) .'</label>';
				
				if ( isset ( $_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'] ) )
					{
					$prefs['format_sort'] = $_COOKIE[BMLT_COOKIE.'_meeting_edit_format_key'];
					}
				else
					{
					$prefs = array ( 'format_sort' => 'format_key' );
					}
				
				if ( ! isset ( $in_http_vars['comdef_format_sort_select'] ) || !$in_http_vars['comdef_format_sort_select'] )
					{
					$in_http_vars['comdef_format_sort_select'] = $prefs['format_sort'];
					}
				
				$ret .= '<select id="sort_formats_by" name="sort_formats_by" onchange="SortFormats(\''.intval($_id).'\')">';
					$ret .= '<option value="shared_id"';
					if ( $in_http_vars['comdef_format_sort_select'] == 'shared_id' )
						{
						$ret .= ' selected="selected"';
						}
					$ret .= '>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['sort_option_id'] ) .'</option>';
					$ret .= '<option value="format_key"';
					if ( $in_http_vars['comdef_format_sort_select'] == 'format_key' )
						{
						$ret .= ' selected="selected"';
						}
					$ret .= '>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['sort_option_key'] ) .'</option>';
					$ret .= '<option value="format_type"';
					if ( $in_http_vars['comdef_format_sort_select'] == 'format_type' )
						{
						$ret .= ' selected="selected"';
						}
					$ret .= '>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['sort_option_type'] ) .'</option>';
				$ret .= '</select>';
			$formats = $meeting_data['formats'];
			$def_formats = '';
			foreach ( $formats as &$format )
				{
				if ( $format instanceof c_comdef_format )
					{
					$def_formats .= (($def_formats != '') ? ',' : '').$format->GetSharedID();
					}
				}
			$ret .= '<input type="hidden" value="'.c_comdef_htmlspecialchars ( $def_formats ).'" id="def_formats_input" />';
			$ret .= '</legend>';
	
			// This is where we show all the format checkboxes.
			$ret .= "<div id=\"mtg_format_checkbox_div\" class=\"checkbox_div\"><div class=\"format_header\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_format'] ).$localized_strings['prompt_delimiter']."</div></div>";
		$ret .= "</fieldset>";
		
		$ret .= '<div class="edit_meeting_buttons_div">';
		// This is the "Delete Meeting" button.
		$ret .= "<div class=\"delete_div\"><input id=\"$meeting_id"."_delete\" type=\"button\" value=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['button_delete_meeting'] )."\" onclick=\"DeleteMeeting('$meeting_id','main_$meeting_id"."_meeting_div')\" /></div>";

		// This triggers an AJAX call, to submit the changes to the database.
		$ret .= "<div class=\"submit_div\">";
		$ret .= "<img style=\"display:none\" id=\"$meeting_id"."_submit_throbber_img\" alt=\"ajax throbber\" class=\"bmlt_submit_throbber_img\" src=\"\" />";
		$ret .= "<input disabled=\"disabled\" id=\"$meeting_id"."_submit\" type=\"button\" value=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['button_submit_changes'] )."\" onclick=\"SubmitMeeting('$meeting_id','$meeting_id"."_submit')\" />";
		$ret .= "</div>";
		$ret .= '</div>';
		$ret .= '<div style="clear:both"></div>';
		
		// List the changes that have been made to this meeting.

		$changes_obj = c_comdef_server::GetServer()->GetChangesFromIDAndType ( 'c_comdef_meeting', $_id );
		if ( $changes_obj instanceof c_comdef_changes )
			{
			$changes_objects = $changes_obj->GetChangesObjects();
			
			if ( is_array ( $changes_objects ) && count ( $changes_objects ) )
				{
				$meeting_id = $in_mtg_object->GetID();
				$ret .= "<dl id=\"meeting_changes_dl\" class=\"change_desc_dl_closed\"><a href=\"javascript:ToggleDL('meeting_changes_dl')\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['changes'] ).$localized_strings['prompt_delimiter']."</a>";
				$iter = 1;
				foreach ( $changes_objects as &$change )
					{
					if ( $change instanceof c_comdef_change )
						{
						$user_id = $change->GetUserID();
						$user = c_comdef_server::GetUserByIDObj ( $user_id );
						
						if ( $user instanceof c_comdef_user )
							{
							$change_id = $change->GetID();
							$desc = $change->DetailedChangeDescription();
							$ret .= "<dt class=\"change_desc_dt change_d_alt_$iter\">";
							$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['changed_by'] );
							$ret .= c_comdef_htmlspecialchars ( $user->GetLocalName() );
							$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['changed_on'] );
							$ret .= date ($change_date_format, $change->GetChangeDate());
							$ret .= "</dt>";
							$ret .= "<dd class=\"change_desc_dd change_d_alt_$iter\">".c_comdef_htmlspecialchars ( $desc['overall']['change_desc'] );
								if ( isset ( $desc['details'] ) && is_array ( $desc['details'] ) )
									{
									$ret .= '<dl class="change_detail_dl">';
									foreach ( $desc['details'] as $detail_string )
										{
										$ret .= "<dt class=\"change_desc_detail_dt\">$detail_string</dt>";
										}
									$ret .= '</dl>';
									}
							$ret .= "</dd>";
							
							if ( $change->GetBeforeObject() )
								{
								$a_rec = "javascript:RevertMeeting('$meeting_id','$change_id');";
								$ret .= "<dd class=\"change_desc_revert_dd change_d_alt_$iter\"><a href=\"$a_rec\" title=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['change_revert_title'] )."\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['change_revert'] )."</a></dd>";
								}
							
							if ( $iter == 1 )
								{
								$iter = 2;
								}
							else
								{
								$iter = 1;
								}
							}
						}
					}
				
				$ret .= "<dl>";
				}
			}
	
		$ret .= "</div>";
		$ret .= "</form>";
		$ret .= '<div class="c_comdef_edit_close_div bottom_close_div"><a href="javascript:CloseMeetingEditor()">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['close_window'] ).'</a></div>';
		$ret .= "</div>";
		}

	return $ret;
}
?>