<?php
/***********************************************************************/
/** \file	change_meeting_ajax.php

	\brief	This is a handler that is called from an AJAX call, and it is
	how a meeting's information is changed. The user session is checked to
	ensure that the user is authorized to edit the meeting.
	
	This echoes one of two JSON objects.
	
	If there is failure, then the JSON object will contain the following:
	
		- error		true
		- report	A string, containing the error report
		- info		A string, with an indicator of the meeting ID (i.e. 'Meeting 1401').
		
	If there is success, then the JSON object will contain the following:
		- meeting_id		This is an integer, containing the meeting ID.
		
		- new_data			You get one of these for each new data field added.
			- key			This is the key, used to match the data item in the data or longdata table.
			- field_prompt	This is the prompt, to be displayed.
			- value			This is the value of the new field.
		
		- deleted_data		You get one of these for each data field that was deleted.
			- key			This is the key, used to match the data item in the data or longdata table.
			- field_prompt	This is the prompt, to be displayed.

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
	require_once ( dirname ( __FILE__ ).'/../server/shared/Array2Json.php');
	require_once ( dirname ( __FILE__ ).'/../client/common_search.inc.php' );
	
	session_start();
	$server = c_comdef_server::MakeServer();
	if ( $server instanceof c_comdef_server )
		{
		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );

		$localized_strings = c_comdef_server::GetLocalStrings();

		// The first thing that we do is to get the original ID of the meeting (in case the ID is to be changed)
		if ( isset ( $_GET['original_id'] ) )
			{
			$orig_id = $_GET['original_id'];
			unset ( $_GET['original_id'] );
			}
		
		try
			{
			if ( $orig_id )
				{
				$meeting =& $server->GetOneMeeting($orig_id);
				}
			else
				{
				$data = array ( 'service_body_bigint' => intval ( $_GET['service_body_bigint'] ),
								'weekday_tinyint' => intval ( $_GET['weekday_tinyint'] ),
								'lang_enum' => $_GET['lang_enum'],
								'start_time' => $_GET['start_time'],
								'duration_time' => '00:00:00',
								'published' => false
								);
				$meeting = new c_comdef_meeting ( $server, $data );
				}
			
			if ( $meeting instanceof c_comdef_meeting )
				{
				// Security precaution: We check the session to make sure that the user is authorized for this meeting.
				if ( $meeting->UserCanEdit() )
					{
					$result_data = array ( 'meeting_id' => $orig_id );
					$data =& $meeting->GetMeetingData();

					// We prepare the "template" array. These are the data values for meeting 0 in the two tables.
					// We will use them to provide default visibility values. Only the server admin can override these.
					// This is where we get a list of the available "optional" fields to put in a popup for adding a new one.
					$template_data = c_comdef_meeting::GetDataTableTemplate();
					$template_longdata = c_comdef_meeting::GetLongDataTableTemplate();
					
					// We merge the two tables (data and longdata).
					if ( is_array ( $template_data ) && count ( $template_data ) && is_array ( $template_longdata ) && count ( $template_longdata ) )
						{
						$template_data = array_merge ( $template_data, $template_longdata );
						}
					
					foreach ( $_GET as $key => $value )
						{
						// Skip the visibility flags.
						if ( !preg_match ( '|_visibility$|', $key ) )
							{
							if ( isset ( $_GET[$key."_visibility"] ) && c_comdef_server::IsUserServerAdmin() )	// Only server admins can override the visibility.
								{
								$visibility = intval ( $_GET[$key."_visibility"] );
								}
							elseif ( isset ( $data[$key] ) && is_array ( $data[$key] ) )	// existing value
								{
								$visibility = intval ( $data[$key]['visibility'] );
								}
							else	// New field gets the template value.
								{
								$visibility = intval ( $template_data[$key]['visibility'] );
								}
							
							if ( $key == 'formats' )
								{
								$vals = array();
								$value = explode ( ",", $value );
								$lang = $_GET['lang_enum'];
								foreach ( $value as $id )
									{
									$vals[$id] = c_comdef_server::GetServer()->GetFormatsObj()->GetFormatBySharedIDCodeAndLanguage ( $id, $lang );
									}
								uksort ( $vals, array ( 'c_comdef_meeting','format_sorter_simple' ) );
								$value = $vals;
								}
							
							switch ( $key )
								{
								case	'distance_in_km':		// These are ignored.
								case	'distance_in_miles':
								break;
								
								// These are the "fixed" or "core" data values.
								case	'id_bigint':
								case	'worldid_mixed':
								case	'service_body_bigint':
								case	'start_time':
								case	'lang_enum':
								case	'duration_time':
								case	'formats':
								case	'longitude':
								case	'latitude':
								case	'latitude':
									$data[$key] = $value;
								break;
								
								case	'email_contact':
									$value = trim ( $value );
									if ( $value )
										{
										if ( c_comdef_vet_email_address ( $value ) )
											{
											$data[$key] = $value;
											}
										else
											{
											$info = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$orig_id;
											$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_format_bad'] );
	                                        header ( 'Content-type: application/json' );
											die ( "{'error':true,'type':'email_format_bad','report':'$err_string','id':'$orig_id',info:'$info'}" );
											}
										}
									else
										{
										$data[$key] = $value;
										}
								break;
								
								case	'copy':
									$data[$key]['field_prompt'] = $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['copy_prompt'];
								break;
								
								// We only accept a 1 or a 0.
								case	'published':
									// Meeting list editors can't publish meetings.
									if ( c_comdef_server::GetCurrentUserObj(true)->GetUserLevel() != _USER_LEVEL_EDITOR )
										{
										$data[$key] = (intval ( $value ) != 0) ? 1 : 0;
										}
								break;
	
								// This one is special. The editor sends in one less than it should be.
								case	'weekday_tinyint':
									$data[$key] = intval ( $value ) + 1;
								break;
								
								// These are the various "optional" fields.
								default:
									if ( isset ( $data[$key] ) )
										{
										$data[$key]['meetingid_bigint'] = $orig_id;
										$data[$key]['value'] = $value;
										$data[$key]['visibility'] = $visibility;
										}
									else
										{
										if ( !preg_match ( "/_deleted_input$/", $key ) )
											{
											if ( isset ( $_GET["new_visibility"] ) && c_comdef_server::IsUserServerAdmin() )	// Only server admins can override the visibility.
												{
												$visibility = intval ( $_GET["new_visibility"] );
												}
											$result_data['new_data']['key'] = $key;
											$result_data['new_data']['field_prompt'] = $template_data[$key]['field_prompt'];
											$result_data['new_data']['value'] = $value;
											$meeting->AddDataField ( $key, $template_data[$key]['field_prompt'], $value, null, $visibility );
											}
										}
								break;
								}
							}
						}
					
					foreach ( $_GET as $key => $value )
						{
						if ( preg_match ( "/_deleted_input$/", $key ) && ($value == "1") )
							{
							$key = preg_replace ( "/_deleted_input$/", "", $key );
							$result_data['deleted_data'][$key]['key'] = $key;
							$result_data['deleted_data'][$key]['prompt'] = $template_data[$key]['field_prompt'];
							$meeting->DeleteDataField ( $key );
							}
						}
					
					// You cannot publish duplicates.
					if ( isset ( $data['copy'] ) )
						{
						$meeting->SetPublished( 0 );
						}

					if ( $meeting->UpdateToDB() )
						{
						$result_data['meeting_published'] = $meeting->IsPublished();
						$result_data['copy'] = $meeting->IsCopy();
						$result_data['meeting_id'] = $meeting->GetID();
						$result_data['town_html'] = BuildTown ( $meeting );
						$result_data['name_html'] = c_comdef_htmlspecialchars ( trim ( stripslashes ( $meeting->GetMeetingDataValue('meeting_name') ) ) );
						$result_data['weekday_html'] = c_comdef_htmlspecialchars ( trim ( stripslashes ( $localized_strings['weekdays'][$meeting->GetMeetingDataValue('weekday_tinyint') - 1] ) ) );
						$result_data['time_html'] = BuildTime ( $meeting->GetMeetingDataValue('start_time') );
						$result_data['location_html'] = BuildLocation ( $meeting );
						$result_data['format_html'] = BuildFormats ( $meeting );
	                    header ( 'Content-type: application/json' );
						echo array2json ( json_prepare ( $result_data ) );
						}
					else
						{
						$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$orig_id;
						$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
	                    header ( 'Content-type: application/json' );
						echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'$orig_id'}";
						}
					}
				else
					{
					$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$orig_id;
					$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
	                header ( 'Content-type: application/json' );
					echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'$orig_id'}";
					}
				}
			else
				{
				$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$orig_id;
				$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['object_not_found'] );
	            header ( 'Content-type: application/json' );
				echo "{'error':true,'type':'object_not_found','report':'$err_string','info':'$orig_id'}";
				}
			}
		catch ( Exception $e )
			{
			$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$orig_id;
			$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['object_not_changed'] );
	        header ( 'Content-type: application/json' );
			echo "{'error':true,'type':'object_not_changed','report':'$err_string','info':'$orig_id'}";
			}
		}
	else
		{
		$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$orig_id;
		$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['object_not_changed'] );
	    header ( 'Content-type: application/json' );
		echo "{'error':true,'type':'object_not_changed','report':'$err_string','info':'$orig_id'}";
		}
?>