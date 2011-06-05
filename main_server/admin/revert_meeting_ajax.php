<?php
/***********************************************************************/
/** \file	revert_meeting_ajax.php

	\brief	This is a handler that is called from an AJAX call, and it is
	how a meeting is reverted to a previous edition. The user session is
	checked to ensure that the user is authorized to edit the meeting.
	
	This echoes one of two JSON objects.
	
	If there is failure, then the JSON object will contain the following:
	
		- error		true
		- report	A string, containing the error report
		- info		A string, with an indicator of the meeting ID (i.e. 'Meeting 1401').
		
	If there is success, then the JSON object will contain the following:
		- success	true
		- report	A string, indicating that the meeting was reverted.

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
	require_once ( dirname ( __FILE__ )."/../server/shared/classes/comdef_utilityclasses.inc.php" );
	
	$server = c_comdef_server::MakeServer();
	session_start();
	
	if ( $server instanceof c_comdef_server )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();
		
		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
	
		try
			{
			$meeting_id = $_POST['meeting_id'];
			$change_id = $_POST['change_id'];
			$meeting =& $server->GetOneMeeting($meeting_id);
			
			if ( !($meeting instanceof c_comdef_meeting) )
				{
				$change = c_comdef_server::GetOneChange ( $change_id );
				
				if ( $change instanceof c_comdef_change )
					{
					$meeting = $change->GetBeforeObject();
					}
				}
			
			if ( $meeting instanceof c_comdef_meeting )
				{
				if ( $meeting->UserCanEdit() )
					{
					// List the changes that have been made to this meeting.
					$changes_obj = c_comdef_server::GetServer()->GetChangesFromIDAndType ( 'c_comdef_meeting', $meeting_id );
					if ( $changes_obj instanceof c_comdef_changes )
						{
						$changes_objects = $changes_obj->GetChangesObjects();
						
						if ( is_array ( $changes_objects ) && count ( $changes_objects ) )
							{
							foreach ( $changes_objects as &$change )
								{
								if ( ($change instanceof c_comdef_change) && ($change->GetID() == $change_id) )
									{
									if ( !((c_comdef_server::GetCurrentUserObj(true)->GetUserLevel() == _USER_LEVEL_EDITOR) && ($change->GetBeforeObject()->IsPublished())) )
										{
										if ( $change->Rollback() )
											{
											$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_reverted'] ).$meeting_id;
											
											$meeting =& $server->GetOneMeeting($meeting_id);
											
											$published = intval ( $meeting->IsPublished() ) != 0 ? 1 : 0;
											
	                                        header ( 'Content-type: application/json' );
											echo "{'success':true,'report':'$orig_id','meeting_published':$published,'meeting_id':$meeting_id,'change_id':$change_id}";
											}
										else
											{
											$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$meeting_id;
											$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['revert_failure'] );
	                                        header ( 'Content-type: application/json' );
											echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'$orig_id'}";
											}
										}
									else
										{
										$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$meeting_id;
										$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
	                                    header ( 'Content-type: application/json' );
										echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'$orig_id'}";
										}
									break;
									}
								}
							}
						}
					}
				else
					{
					$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$meeting_id;
					$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
	                header ( 'Content-type: application/json' );
					echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'$orig_id'}";
					}
				}
			else
				{
				$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$meeting_id;
				$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['object_not_found'] );
	            header ( 'Content-type: application/json' );
				echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'$orig_id'}";
				}
			}
		catch ( Exception $e )
			{
			$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_id'] ).$meeting_id;
			$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['object_not_changed'] );
	        header ( 'Content-type: application/json' );
			echo "{'error':true,'type':'object_not_changed','report':'$err_string','info':'$orig_id'}";
			}
		}
	else
		{
		$err_string = json_prepare ( 'CANNOT CREATE SERVER OBJECT' );
	    header ( 'Content-type: application/json' );
		echo "{'error':true,'type':'object_not_changed','report':'$err_string','info':'$orig_id'}";
		}
?>