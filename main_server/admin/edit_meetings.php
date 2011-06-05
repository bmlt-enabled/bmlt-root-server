<?php
/***********************************************************************/
/** \file	edit_meetings.php

	\brief	Displays a form for adding and undeleting meetings.

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
/** \brief	This returns a form, used to create and undelete meetings.

	\returns a string, containing the HTML for the form
*/
function DisplayMeetingForm (
							$in_http_vars	///< An associative array that is a blend of the $_POST and $_GET arrays.
							)
{
	$ret = null;
	
	$server = c_comdef_server::MakeServer();

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

		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
		
		if ( !isset ( $in_http_vars['script_name'] ) || !$in_http_vars['script_name'] )
			{
			$script_name = preg_replace ( '#(^\/+)|(\/+$)#', '/', preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] ) );
			}
		else
			{
			$script_name = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['script_name'] );
			}
		
		$location_of_images = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root']."themes/".$localized_strings['theme']."/html/images" );
		$location_of_throbber = "$location_of_images/Throbber.gif";
		
		$cur_user =& c_comdef_server::GetCurrentUserObj();
		
		// Has to be associated with at least one Service body to undelete/create new meetings.
		if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) &&  ($cur_user->GetUserLevel() != _USER_LEVEL_OBSERVER) && (c_comdef_server::GetUserServiceBodies() || c_comdef_server::IsUserServerAdmin()) )
			{
			$ret .= '<div id="c_comdef_search_results_edit_hidden_div" style="display:none" class="c_comdef_search_results_edit_hidden_div no_print"></div>';
			$ret .= '<div id="geocoder_browser_edit_meeting_map_div" class="geocoder_browser_edit_meeting_hidden"><div id="goo_map_workplace_container" class="goo_map_workplace_container_div"><noscript><h1>'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['noscript_warning'] ).'</h1></noscript><div id="goo_map_workplace" class="map_work_div"><div id="meeting_map" class="map_square"></div><a href="javascript:CloseMap()">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['close_window'] ).'</a></div></div></div>';
			$ret .= "<div id=\"c_comdef_search_results_single_ajax_throbber_div\" class=\"c_comdef_search_results_single_ajax_throbber_div\" style=\"display:none\"><img id=\"c_comdef_search_results_single_ajax_throbber_img\" class=\"c_comdef_search_results_single_ajax_throbber_img\" alt=\"busy\" src=\"".c_comdef_htmlspecialchars ( $location_of_throbber )."\" /></div>";
			$ret .= "<div id=\"c_comdef_search_results_single_ajax_div\" class=\"c_comdef_search_results_single_ajax_div\" style=\"display:none\"></div>";
			$ret .= '<div class="edit_meeting_div">';
				$ret .= '<form class="edit_meeting_form" action="#" method="post">';
					$ret .= DisplayNewMeetingForm ( $in_http_vars );
				$ret .= '</form>';
			$ret .= '</div>';
			}
		}
	
	return $ret;
}
		
/*******************************************************************/
/** \brief	Return the HTML for a small form to add a new meeting.

	\returns a string, containing the form's HTML.
*/
function DisplayNewMeetingForm(
								$in_http_vars	///< An associative array that is a blend of the $_POST and $_GET arrays.
								)
{
	$localized_strings = c_comdef_server::GetLocalStrings();
		
	include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );

	$ret = "<div id=\"new_meeting_container_div\" class=\"new_meeting_div_closed\"><a href=\"javascript:ToggleNewMeetingDiv()\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['NewMeeting'] ).$localized_strings['prompt_delimiter']."</a>";
	ob_start();
	?>	<div id="new_meeting_div" class="new_meeting_div" style="display:none">
			<div class="new_meeting_left">
				<div class="meeting_value_div">
					<label for="meeting_new_weekday_tinyint"><?php echo c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['weekday'] ).$localized_strings['prompt_delimiter'] ?></label>
					<select id="meeting_new_weekday_tinyint"><?php
						for ( $i = 0; $i < 7; $i++ )
							{
							echo "<option value=\"$i\"";
							if ( 0 == $i )
								{
								echo " selected=\"selected\"";
								}
							echo ">".c_comdef_htmlspecialchars ( $localized_strings['weekdays'][$i] )."</option>";
							}
					?></select>
				</div>
				<div class="meeting_value_div">
					<label for="meeting_new_start_time_hour"><?php echo c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['starts_at'] ).$localized_strings['prompt_delimiter'] ?></label>
					<select id="meeting_new_start_time_hour">
						<option value="0" selected="selected">00</option>
						<option value="1">01</option>
						<option value="2">02</option>
						<option value="3">03</option>
						<option value="4">04</option>
						<option value="5">05</option>
						<option value="6">06</option>
						<option value="7">07</option>
						<option value="8">08</option>
						<option value="9">09</option>
						<option value="10">10</option>
						<option value="11">11</option>
						<option value="12">12</option>
						<option value="13">13</option>
						<option value="14">14</option>
						<option value="15">15</option>
						<option value="16">16</option>
						<option value="17">17</option>
						<option value="18">18</option>
						<option value="19">19</option>
						<option value="20">20</option>
						<option value="21">21</option>
						<option value="22">22</option>
						<option value="23">23</option>
					</select>
					<label for="meeting_new_start_time_minute" style="display:none"><?php echo c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['starts_at'] ).$localized_strings['prompt_delimiter'] ?></label>
					<select id="meeting_new_start_time_minute">
						<option value="0" selected="selected">00</option>
						<option value="15">5</option>
						<option value="15">10</option>
						<option value="15">15</option>
						<option value="30">20</option>
						<option value="30">25</option>
						<option value="30">30</option>
						<option value="30">35</option>
						<option value="45">40</option>
						<option value="45">45</option>
						<option value="45">50</option>
						<option value="45">55</option>
						<option value="59">59</option>
					</select>
				</div>
				<div class="meeting_value_div">
					<label for="meeting_new_service_body_bigint"><?php echo c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['service_body'] ).$localized_strings['prompt_delimiter'] ?></label>
					<select id="meeting_new_service_body_bigint"><?php
						$bd_array = c_comdef_server::GetServer()->GetAllServiceIDs();
						foreach ( $bd_array as $key => $sb_id )
							{
							$sb = c_comdef_server::GetServiceBodyByIDObj ( $sb_id );
							
							if ( ($sb instanceof c_comdef_service_body) && ($sb->IsUserInServiceBodyHierarchy() || (c_comdef_server::IsUserServerAdmin())) )
								{
								echo "<option value=\"$sb_id\"";
								if ( $comdef_native_service_body == $sb_id )
									{
									echo ' selected="selected"';
									}
								echo ">".c_comdef_htmlspecialchars ( $key )."</option>";
								}
							}
					?></select>
				</div>
				<div class="meeting_value_div">
					<label for="meeting_new_lang_enum"><?php echo c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['language'] ).$localized_strings['prompt_delimiter'] ?></label>
					<select id="meeting_new_lang_enum"><?php
						$lang = c_comdef_server::GetServer()->GetLocalLang();
						$language1 = c_comdef_server::GetServerLangs();
						foreach ( $language1 as $key => $val )
							{
							echo "<option value=\"$key\"";
							if ( $lang == $key )
								{
								echo " selected=\"selected\"";
								}
							echo ">".c_comdef_htmlspecialchars ( trim ( $val ) );
							echo "</option>";
							}
					?></select>
				</div>
			</div>
			<div class="new_meeting_right">
				<img style="display:none" id="create_submit_throbber_img" alt="ajax throbber" class="bmlt_submit_throbber_pink_img" src="placeholder" />
				<input class="new_meeting_button" id="new_meeting_button" type="button" value="<?php echo c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['NewMeeting'] ) ?>" onclick="CreateNewMeetingHandler()" />
			</div>
			<div class="new_meeting_breaker"></div>
		</div>
	</div><?php
	$ret .= ob_get_contents();
	ob_end_clean();
	
	return $ret;
}
		
/*******************************************************************/
/** \brief	Returns an array of all the change objects that contain
	deleted meetings that the current user has the authorization to edit.

	\returns an array of references to c_comdef_change objects, sorted
	by change date, with the last changed objects first in the array.
*/
function GetDeletedMeetings()
{
	$ret = null;
	
	// We start by getting all the meetings that have been deleted (Could be quite a few).
	$changes = c_comdef_server::GetChangesFromOTypeAndCType ( 'c_comdef_meeting', 'comdef_change_type_delete' );

	if ( $changes instanceof c_comdef_changes )
		{
		$ret = array();
		$c_array =& $changes->GetChangesObjects();
		
		if ( is_array ( $c_array ) && count ( $c_array ) )
			{
			foreach ( $c_array as &$change )
				{
				$b_obj = $change->GetBeforeObject();
				if ( $b_obj instanceof c_comdef_meeting )
					{
					if ( $b_obj->UserCanEdit () )
						{
						if ( !c_comdef_server::GetOneMeeting ( $b_obj->GetID() ) )
							{
							$block = false;
							// There can only be one...
							foreach ( $ret as &$change_obj )
								{
								if ( $change_obj instanceof c_comdef_change )
									{
									if ( $change_obj->GetBeforeObjectID() == $b_obj->GetID() )
										{
										$block = true;
										}
									}
								}
							if ( !$block )
								{
								$value = $b_obj->GetMeetingDataValue ( 'meeting_name' );

								if ( $value )
									{
									$change->meeting_name = $value;
									}
								
								$value = $b_obj->GetMeetingDataValue ( 'weekday_tinyint' );

								$change->meeting_weekday = $value;
								
								$value = $b_obj->GetMeetingDataValue ( 'start_time' );

								$change->meeting_start_time = strtotime ( $value );
								
								array_push ( $ret, $change );
								}
							}
						}
					}
				}
			}
		}
	
	return $ret;
}
?>