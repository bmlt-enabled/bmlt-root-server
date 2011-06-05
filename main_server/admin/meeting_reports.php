<?php
/***********************************************************************/
/** \file	meeting_reports.inc.php

	\brief	Displays a dropdown panel of the meeting reports for admins.

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
/** \brief	This returns the HTML for the meeting reports area of the admin control panel.
	
	\returns display-ready HTML for the dropdown panel.
*/
function DisplayMeetingReports ( $in_http_vars	///< The $_GET and $_POST variables, in an associative array.
							)
	{
	$ret = '';
	
	$cur_user =& c_comdef_server::GetCurrentUserObj();
	if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) && ($cur_user->GetUserLevel() != _USER_LEVEL_OBSERVER) )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();
	
		$ret = "<div id=\"meeting_reports_div_container_div_id\" class=\"meeting_reports_div_closed\"><a class=\"meeting_reports_a\" href=\"javascript:ToggleReportsDiv('meeting_')\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Reports']['meeting_reports_div_title'] ).$localized_strings['prompt_delimiter']."</a></div>";
		$ret .= '<div id="meeting_reports_div_id" class="meeting_reports_div" style="display:none">';
			$ret .= DisplayDeletedMeetingReports ( $in_http_vars );
			$ret .= DisplayChangedMeetingReports ( $in_http_vars );
		$ret .= '</div>';
		}
	
	return $ret;
	}

/*******************************************************************/
/** \brief	This returns the HTML for the meeting reports area of the admin control panel.
	
	\returns display-ready HTML for the dropdown panel.
*/
function DisplayChangedMeetingReports ( $in_http_vars	///< The $_GET and $_POST variables, in an associative array.
										)
	{
	$ret = '';
	
	$cur_user =& c_comdef_server::GetCurrentUserObj();
	if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) && ($cur_user->GetUserLevel() != _USER_LEVEL_OBSERVER) )
		{
		$changed_meeting_changes = GetChangedMeetings('comdef_change_type_delete');
		
		if ( is_array ( $changed_meeting_changes ) && count ( $changed_meeting_changes ) )
			{
			$changed_meeting_changes = array_merge_recursive ( $changed_meeting_changes, GetChangedMeetings('comdef_change_type_new') );
			}
		else
			{
			$changed_meeting_changes = GetChangedMeetings('comdef_change_type_new');
			}
		
		if ( is_array ( $changed_meeting_changes ) && count ( $changed_meeting_changes ) )
			{
			$changed_meeting_changes = array_merge_recursive ( $changed_meeting_changes, GetChangedMeetings('comdef_change_type_rollback') );
			}
		else
			{
			$changed_meeting_changes = GetChangedMeetings('comdef_change_type_rollback');
			}
		
		if ( is_array ( $changed_meeting_changes ) && count ( $changed_meeting_changes ) )
			{
			$changed_meeting_changes = array_merge_recursive ( $changed_meeting_changes, GetChangedMeetings('comdef_change_type_change') );
			}
		else
			{
			$changed_meeting_changes = GetChangedMeetings('comdef_change_type_change');
			}
		
		krsort ( $changed_meeting_changes );
	
		if ( is_array ( $changed_meeting_changes ) && count ( $changed_meeting_changes ) )
			{
			$localized_strings = c_comdef_server::GetLocalStrings();
			include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
			
			if ( count ( $changed_meeting_changes ) )
				{
				$ret = "<div id=\"changed_meeting_reports_div_container_div_id\" class=\"changed_meeting_reports_div_closed\"><a class=\"changed_meeting_reports_a\" href=\"javascript:ToggleReportsDiv('changed_meeting_')\">".intval ( count ( $changed_meeting_changes ) )." ".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Reports']['changed_meeting_reports_div_title'] ).$localized_strings['prompt_delimiter']."</a></div>";
				$ret .= '<div id="changed_meeting_reports_div_id" class="changed_meeting_reports_div" style="display:none">';
					$ret .= '<form class="edit_meeting_form" action="#" method="post">';
						$ret .= "<div id=\"changed_meeting_changes_div\" class=\"change_desc_div\">";
						foreach ( $changed_meeting_changes as &$change )
							{
							if ( $change instanceof c_comdef_change )
								{
								$change_id = $change->GetID();
								$user_id = $change->GetUserID();
								$user = c_comdef_server::GetUserByIDObj ( $user_id );
								$meeting_id = intval ( $change->GetBeforeObjectID() );
								if ( !$meeting_id )
									{
									$meeting_id = intval ( $change->GetAfterObjectID() );
									}
								if ( isset ( $change->meeting_name ) && $change->meeting_name )
									{
									$meeting_name = $change->meeting_name." (".$localized_strings['comdef_search_admin_strings']['Edit_Meetings']['id']."$meeting_id)";
									}
								else
									{
									$meeting_name = $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['id'].$meeting_id;
									}
								
								if ( $user instanceof c_comdef_user )
									{
									$desc = $change->DetailedChangeDescription();
									if ( $desc )
										{
										$ch = c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['the_meeting'] );
										$ch .= c_comdef_htmlspecialchars ( $meeting_name );
										switch ( $change->GetChangeType() )
											{
											case 'comdef_change_type_delete':
												$ch .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['deleted_by'] );
											break;
											
											case 'comdef_change_type_new':
												$ch .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['created_by'] );
											break;
											
											default:
												$ch .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['changed_by'] );
											break;
											}
										
										$ch .= c_comdef_htmlspecialchars ( $user->GetLocalName() );
										$ch .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['changed_on'] );
										$ch .= date ($change_date_format, $change->GetChangeDate());
										$ret .= "<div id=\"changed_desc_$change_id"."_reports_div_container_div_id\" class=\"";
										if ( ($change->GetChangeType() != 'comdef_change_type_delete') && c_comdef_server::DoesMeetingExist($meeting_id) )
											{
											$ret .= "changed_desc_reports_div_closed\">";
											$ret .= "<a class=\"changed_desc_reports_a\" href=\"javascript:ToggleDescDiv('$change_id')\">$ch</a>";
											$ret .= '<div id="changed_desc_'.$change_id.'_reports_div_id" class="detailed_change_div" style="display:none">';
												if ( isset ( $desc['details'] ) && is_array ( $desc['details'] ) )
													{
													foreach ( $desc['details'] as $detail_string )
														{
														$ret .= "<div class=\"individual_detail_line_div\">$detail_string</div>";
														}
													}
												
												// If there is a current version of this meeting, we allow the user to view it.
												if ( c_comdef_server::DoesMeetingExist($meeting_id) )
													{
													$ret .= "<div class=\"change_link\"><a class=\"visit_a\" href=\"javascript:VisitMeeting('$meeting_id')\" title=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['change_visit_title'] )."\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['change_visit'] )."</a></div>";
													}
												
												// Allow user to revert a meeting.
												if ( $change->GetBeforeObject() instanceof c_comdef_meeting )
													{
// This is commented out, because the UI is confusing. If the list is long, the new meeting is displayed far up the page. It's better for the admin to use the edit meeting functionality to revert.
//													$ret .= "<div class=\"change_link\"><a class=\"revert_a\" href=\"javascript:if(confirm('".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['change_revert_confirm'] )."'))RevertMeeting('$meeting_id','$change_id')\" title=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['change_revert_title'] )."\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['change_revert'] )."</a></div>";
													}
											$ret .= '</div>';
											}
										else
											{
											$ret .= "changed_desc_reports_no_link\">$ch";
											}
										$ret .= '</div>';
										}
									}
								}
							}
						$ret .= "</div>";
					$ret .= "</form>";
				$ret .= '</div>';
				}
			}
		}
	
	return $ret;
	}

/*******************************************************************/
/** \brief	This returns the HTML for the meeting reports area of the admin control panel.
	
	\returns display-ready HTML for the dropdown panel.
*/
function DisplayDeletedMeetingReports ( $in_http_vars	///< The $_GET and $_POST variables, in an associative array.
										)
	{
	$ret = '';
	
	$cur_user =& c_comdef_server::GetCurrentUserObj();
	if ( ($cur_user->GetUserLevel() != _USER_LEVEL_DISABLED) && ($cur_user->GetUserLevel() != _USER_LEVEL_OBSERVER) )
		{
		$del_changes = GetChangedMeetings('comdef_change_type_delete');
		
		if ( is_array ( $del_changes ) && count ( $del_changes ) )
			{
			set_time_limit ( intval ( count ( $del_changes ) / 10 ) );	// Prevents the script from timing out.
			
			$localized_strings = c_comdef_server::GetLocalStrings();
			include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
				
			$ret = "<div id=\"deleted_meeting_reports_div_container_div_id\" class=\"deleted_meeting_reports_div_closed\"><a class=\"deleted_meeting_reports_a\" href=\"javascript:ToggleReportsDiv('deleted_meeting_')\">".intval ( count ( $del_changes ) )." ".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Reports']['deleted_meeting_reports_div_title'] ).$localized_strings['prompt_delimiter']."</a></div>";
			$ret .= '<div id="deleted_meeting_reports_div_id" class="deleted_meeting_reports_div" style="display:none">';
				$ret .= '<form class="edit_meeting_form" action="#" method="post">';
					$ret .= "<div id=\"meeting_changes_undelete_div_id\" class=\"meeting_changes_undelete_div\">";
					$iter = 1;
					foreach ( $del_changes as &$change )
						{
						if ( $change instanceof c_comdef_change )
							{
							$change_id = $change->GetID();
							$user_id = $change->GetUserID();
							$user = c_comdef_server::GetUserByIDObj ( $user_id );
							$meeting_id = intval ( $change->GetBeforeObjectID() );
							
							if ( !c_comdef_server::DoesMeetingExist ( $meeting_id ) )
								{
								if ( isset ( $change->meeting_name ) && $change->meeting_name )
									{
									$meeting_name = $change->meeting_name." (".$localized_strings['comdef_search_admin_strings']['Edit_Meetings']['id']."$meeting_id)";
									}
								else
									{
									$meeting_name = $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['id'].$meeting_id;
									}
								
								$meeting_weekday = $localized_strings['weekdays'][$change->meeting_weekday];
								$meeting_start_time = date ( $time_format, $change->meeting_start_time );
								if ( $user instanceof c_comdef_user )
									{
									$desc = $change->DescribeChange();
									$ret .= "<div id=\"del_desc_dt_change_$change_id"."_$meeting_id\" class=\"deleted_desc_div change_d_alt_$iter\">";
									$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['the_meeting'] );
									$ret .= c_comdef_htmlspecialchars ( $meeting_name );
									$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['deleted_by'] );
									$ret .= c_comdef_htmlspecialchars ( $user->GetLocalName() );
									$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['changed_on'] );
									$ret .= date ($change_date_format, $change->GetChangeDate());
									$a_rec = "javascript:if(confirm('".rawurlencode ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['undelete_meeting_confirm'] )."'))UnDeleteMeeting('$meeting_id','$change_id')";
									$ret .= "<div id=\"del_desc_div_$change_id"."_$meeting_id\" class=\"change_desc_line_div change_d_alt_$iter\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['met_on'] ).c_comdef_htmlspecialchars ( $meeting_weekday ).c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['met_at'] ).c_comdef_htmlspecialchars ( $meeting_start_time )."</div>";
									$ret .= "<div id=\"del_desc_div_a_$change_id"."_$meeting_id\" class=\"change_desc_line_div change_d_alt_$iter\"><a href=\"$a_rec\" title=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['undelete_meeting_title'] )."\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meetings']['undelete_meeting'] )."</a></div>";
									if ( c_comdef_server::IsUserServerAdmin() )
										{
										$ret .= "<div id=\"del_desc_div_a2_$change_id"."_$meeting_id\" class=\"change_desc_line_div change_d_alt_$iter\"><a href=\"javascript:if(confirm('".rawurlencode ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['perm_delete_confirm'] )."'))PermDeleteMeeting($meeting_id,'".rawurlencode ( addslashes ( $meeting_name ) )."','del_desc_dt_change_$change_id"."_$meeting_id')\" title=\"".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['delete_extreme_prejudice_title'] )."\">".c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['delete_extreme_prejudice'] )."</a></div>";
										}
									if ( $iter == 1 )
										{
										$iter = 2;
										}
									else
										{
										$iter = 1;
										}
									$ret .= "</div>";
									}
								}
							}
						}
					$ret .= "</div>";
				$ret .= "</form>";
			$ret .= '</div>';
			}
		}
	
	return $ret;
	}
		
/*******************************************************************/
/** \brief	Returns an array of all the change objects that contain
	changed meetings that the current user has the authorization to edit.

	\returns an array of references to c_comdef_change objects, sorted
	by change date, with the last changed objects first in the array.
*/
function GetChangedMeetings( $in_change_type	/**< The change type.
														Can be:
															- 'comdef_change_type_new' - New object
															- 'comdef_change_type_delete' - Deleted the object
															- 'comdef_change_type_change' - Changed existing object
															- 'comdef_change_type_rollback' - Rolled existing object back to a previous version
												*/
							)
{
	$ret = null;
	
	// We start by getting all the meetings that have been deleted (Could be quite a few).
	$changes = c_comdef_server::GetServer()->GetChangesFromOTypeAndCType ( 'c_comdef_meeting', $in_change_type );

	if ( $changes instanceof c_comdef_changes )
		{
		$ret = array();
		$c_array =& $changes->GetChangesObjects();
		
		if ( is_array ( $c_array ) && count ( $c_array ) )
			{
			set_time_limit ( intval ( count ( $c_array ) / 10 ) );	// Prevents the script from timing out.
			foreach ( $c_array as &$change )
				{
				$b_obj = $change->GetBeforeObject();
				$a_obj = $change->GetAfterObject();
				if ( (!$b_obj && ($a_obj instanceof c_comdef_meeting) && $a_obj->UserCanEdit ()) || (!$a_obj && ($b_obj instanceof c_comdef_meeting) && $b_obj->UserCanEdit ()) || (($b_obj instanceof c_comdef_meeting) && $b_obj->UserCanEdit () && ($a_obj instanceof c_comdef_meeting) && $a_obj->UserCanEdit ()) )
					{
					if ( $in_change_type == 'comdef_change_type_delete' )
						{
						if ( !$b_obj || ($a_obj instanceof c_comdef_meeting) )
							{
							continue;
							}
						}
					elseif ( $in_change_type == 'comdef_change_type_new' )
						{
						if ( !$a_obj || ($b_obj instanceof c_comdef_meeting) )
							{
							continue;
							}
						}
					else
						{
						if ( !(($b_obj instanceof c_comdef_meeting ) && ($a_obj instanceof c_comdef_meeting ) && c_comdef_server::DoesMeetingExist( $a_obj->GetID() )) )
							{
							continue;
							}
						}
				
					if ( $b_obj instanceof c_comdef_meeting )
						{
						$value = $b_obj->GetMeetingDataValue ( 'meeting_name' );
	
						if ( $value )
							{
							$change->meeting_name = $value;
							}
						
						$value = $b_obj->GetMeetingDataValue ( 'weekday_tinyint' );
	
						$change->meeting_weekday = $value;
						
						$value = $b_obj->GetMeetingDataValue ( 'start_time' );
						}
					else
						{
						$value = $a_obj->GetMeetingDataValue ( 'meeting_name' );
	
						if ( $value )
							{
							$change->meeting_name = $value;
							}
						
						$value = $a_obj->GetMeetingDataValue ( 'weekday_tinyint' );
	
						$change->meeting_weekday = $value;
						
						$value = $a_obj->GetMeetingDataValue ( 'start_time' );
						}
					
					$change->meeting_start_time = strtotime ( $value );
					
					$ret[strval($change->GetChangeDate()).'_'.$in_change_type] = $change;
					}
				}
			}
		}
	
	krsort ( $ret );
	return $ret;
}
?>