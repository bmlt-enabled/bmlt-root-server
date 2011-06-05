<?php
/*
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
	
	$id_bigint = $_POST['sb_id'];
	
	session_start();
	$server = c_comdef_server::MakeServer();
	if ( $server instanceof c_comdef_server )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();

		$lang_enum = c_comdef_server::GetServer()->GetLocalLang();
		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
		
		try
			{
			$server = c_comdef_server::MakeServer();
			$service_body =& c_comdef_server::GetServiceBodyByIDObj ( $id_bigint );
			if ( $service_body instanceof c_comdef_service_body )
				{
				if ( $service_body->UserCanEdit() )
					{
					$meetings = c_comdef_server::GetMeetingsForAServiceBody ( $id_bigint );
					
					// If we had any meetings assigned to us, we need to reassign them.
					if ( $meetings instanceof c_comdef_meetings )
						{
						$reassign = 0;	// We default to 0.
						
						// We start by seeing if an ID was passed in.
						if ( isset ( $_POST['sb_reassign'] ) )
							{
							$reassign = $_POST['sb_reassign'];
							}
						
						// If not, we go to our parent.
						if ( !$reassign )
							{
							$reassign = $service_body->GetOwnerID();
							}
						
						// If nothing there, we try our secondary parent.
						if ( !$reassign )
							{
							$reassign = $service_body->GetOwner2ID();
							}
						
						// If still nothing, we abort the deletion.
						if ( !$reassign )
							{
							$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['service_body_id'] ).$id_bigint;
							$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['no_reassignment_meetings'] );
							die ( "{'error':true,'type':'no_reassignment_meetings','report':'$err_string','info':'$orig_id'}" );
							}
						
						$meeting_array = $meetings->GetMeetingObjects();
						
						// Now, we check to see if we are authorized for every one of the meetings (We should be).
						if ( is_array ( $meeting_array ) && count ( $meeting_array ) )
							{
							foreach ( $meeting_array as $meeting )
								{
								if ( !$meeting->UserCanEdit() )
									{
									$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['service_body_id'] ).$id_bigint;
									$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['no_reassignment_meetings'] );
									die ( "{'error':true,'type':'not_allowed_reassign','report':'$err_string','info':'$orig_id'}" );
									}
								}
							
							// Hokay, we made it past that. We will reassign the meeting objects to whichever Service Body will take them.
							foreach ( $meeting_array as $meeting )
								{
								// We simply change each meeting's Service Body, and store the meeting.
								$meeting->SetServiceBodyID ( $reassign );
								// No need to check security again. That's done by the update routine.
								$meeting->UpdateToDB();
								}
							}
						}
	
					// Okay, now that the meetings are done, we look for Service Bodies that have this as their parent or secondary parent, and do them.
					// What we do here is reassign them to the same parent as we did the meetings. Since they were already a parent, there's no security risk.
					// It's OK for $reassign to be 0.
					$sb_array = $server->GetServiceBodyArray();
					
					if ( is_array ( $sb_array ) && count ( $sb_array ) )
						{
						foreach ( $sb_array as $sb )
							{
							// Have to have the right to edit it, first.
							if ( $sb->UserCanEdit () )
								{
								$needs_update = false;
								if ( $sb->GetOwnerID() == $id_bigint )
									{
									$sb->SetOwnerID ( $reassign );
									if ( $reassign && ($sb->GetOwner2ID() == $reassign) )
										{
										// If the secondary owner is also the new assignee, then we just clear it, to prevent redundancy.
										$sb->SetOwner2ID(0);
										}
		
									$needs_update = true;
									}
								elseif ( $sb->GetOwner2ID() == $id_bigint )
									{
									if ( $reassign && ($sb->GetOwnerID() == $reassign) )
										{
										// If the principal owner is the new assignee, then we just clear this owner, to prevent redundancy.
										$sb->SetOwner2ID ( 0 );
										}
									
									$needs_update = true;
									}
								
								if ( $needs_update )
									{
									$sb->UpdateToDB();
									}
								}
							}
						}
	
					// Now that the other Service Bodies and meetings are done, we can delete.
					$service_body->DeleteFromDB();						
					
	                header ( 'Content-type: application/json' );
					echo "{'success':true,'id':'$id_bigint'}";
					}
				else
					{
					$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['service_body_id'] ).$id_bigint;
					$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['auth_failure'] );
	                header ( 'Content-type: application/json' );
					echo ( "{'error':true,'type':'auth_failure','report':'$err_string','info':'$orig_id'}" );
					}
				}
			else
				{
				$err_string = json_prepare ( 'CANNOT CREATE SERVER' );
	            header ( 'Content-type: application/json' );
				echo ( "{'error':true,'type':'object_not_found','report':'$err_string'}" );
				}
			}
		catch ( Exception $e )
			{
			$err_string = json_prepare ( 'EXCEPTION THROWN' );
	        header ( 'Content-type: application/json' );
			echo ( "{'error':true,'type':'sql_err','report':'$err_string'}" );
			}
		}
?>