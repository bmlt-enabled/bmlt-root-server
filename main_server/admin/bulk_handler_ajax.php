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
	require_once ( dirname ( __FILE__ )."/../server/shared/classes/comdef_utilityclasses.inc.php" );
	require_once ( dirname ( __FILE__ ).'/../server/shared/Array2Json.php');
	
	$server = c_comdef_server::MakeServer();
	if ( $server instanceof c_comdef_server )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();

		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
		
		// Only certain folks are allowed to do this...
		if ( c_comdef_server::IsUserServerAdmin(null,true)
			|| (c_comdef_server::GetCurrentUserObj(true)->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) )
			{
			if ( isset ( $_POST['meeting_ids'] ) )
				{
				$meeting_ids = explode ( ",", $_POST['meeting_ids'] );
				}
			else
				{
				$meeting_ids = explode ( ",", $_GET['meeting_ids'] );
				}
			if ( isset ( $_POST['key_string'] ) )
				{
				$key_string = trim ( $_POST['key_string'] );
				}
			else
				{
				$key_string = trim ( $_GET['key_string'] );
				}
			if ( isset ( $_POST['value_string'] ) )
				{
				$value_string = trim ( $_POST['value_string'] );
				}
			else
				{
				$value_string = trim ( $_GET['value_string'] );
				}
			
			$override = false;
			
			if ( (isset ( $_POST['override'] ) && intval ($_POST['override']))
				|| (isset ( $_GET['override'] ) && intval ($_GET['override'])) )
				{
				$override = true;
				}
			$ret = '';
	
			// First, make sure that we have all the meetings. Otherwise, we fail right here.
			foreach ( $meeting_ids as $id )
				{
				if ( !($server->GetOneMeeting($id) instanceof c_comdef_meeting) )
					{
					$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['meeting_does_not_exist'] );
					$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
					break;
					}
				}
			
			if ( !$ret )
				{
				switch ( $_POST['action'] )
					{
					case	'publish':
						$ret = PublishMeetings ( $server, $localized_strings['comdef_search_admin_strings'], $meeting_ids );
					break;
					
					case	'unpublish':
						$ret = UnpublishMeetings ( $server, $localized_strings['comdef_search_admin_strings'], $meeting_ids );
					break;
					
					case	'duplicate':
						$ret = DuplicateMeetings ( $server, $localized_strings['comdef_search_admin_strings'], $meeting_ids );
					break;
					
					case	'delete':
						$ret = DeleteMeetings ( $server, $localized_strings['comdef_search_admin_strings'], $meeting_ids );
					break;
					
					case	'delete_extreme_prejudice':
						// Only server admins can delete permanently.
						if ( c_comdef_server::IsUserServerAdmin(null,true) )
							{
							$ret = DeleteMeetings ( $server, $localized_strings['comdef_search_admin_strings'], $meeting_ids, true );
							}
						else
							{
							$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
							$ret = "{'error':true,'type':'auth_failure','report':'$err_string''}";
							}
					break;
					
					case 'apply_data_item':
						// Only server admins can apply data items.
						if ( c_comdef_server::IsUserServerAdmin(null,true) )
							{
							$ret = ApplyDataItem ( $server, $localized_strings['comdef_search_admin_strings'], $meeting_ids, $key_string, $value_string, $override );
							}
						else
							{
							$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
							$ret = "{'error':true,'type':'auth_failure','report':'$err_string''}";
							}
					break;
					
					default:
						$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['unknown_bulk_operation'] );
						$ret = "{'error':true,'type':'unknown_bulk_operation','report':'$err_string''}";
					break;
					}
				}
			}
		else
			{
			$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['auth_failure'] );
			$ret = "{'error':true,'type':'auth_failure','report':'$err_string''}";
			}
		}
	else
		{
		$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['server_not_instantiated'] );
		$ret = "{'error':true,'type':'server_not_instantiated','report':'$err_string''}";
		}
	
	header ( 'Content-type: application/json' );
	echo $ret;

/*******************************************************************/
/**
*/
function PublishMeetings (	$server,
							$in_comdef_search_admin_strings,
							$in_meeting_ids
							)
{
	$ret = '';
	$changed_ids = array();
	
	foreach ( $in_meeting_ids as $id )
		{
		$meeting =& $server->GetOneMeeting($id);
		if ( $meeting instanceof c_comdef_meeting )
			{
			if ( $meeting->UserCanEdit() )
				{
				if ( !$meeting->IsPublished() && !$meeting->IsCopy() )	// Can't publish copies until the copy field has been deleted.
					{
					$meeting->SetPublished ( true );
					if ( !$meeting->UpdateToDB() )
						{
						$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
						$ret = "{'error':true,'type':'auth_failure','report':'$err_string','info':'$id'}";
						break;
						}
					else
						{
						array_push ( $changed_ids, $id );
						}
					}
				}
			else
				{
				$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
				$ret = "{'error':true,'type':'auth_failure','report':'$err_string''}";
				}
			}
		else	// Belt & suspenders
			{
			$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['meeting_does_not_exist'] );
			$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
			break;
			}
		}
	
	if ( !$ret )
		{
		$result['message'] = 'publish';
		$result['ids'] = $changed_ids;
		$ret = array2json ( json_prepare ( $result ) );
		}
	
	return $ret;
}
	
/*******************************************************************/
/**
*/
function UnpublishMeetings (	$server,
								$in_comdef_search_admin_strings,
								$in_meeting_ids
								)
{
	$ret = '';
	$changed_ids = array();
	
	foreach ( $in_meeting_ids as $id )
		{
		$meeting =& $server->GetOneMeeting($id);
		if ( $meeting instanceof c_comdef_meeting )
			{
			if ( $meeting->UserCanEdit() )
				{
				if ( $meeting->IsPublished() )
					{
					$meeting->SetPublished ( false );
					if ( !$meeting->UpdateToDB() )
						{
						$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
						$ret = "{'error':true,'type':'auth_failure','report':'$err_string','info':'$id'}";
						break;
						}
					else
						{
						array_push ( $changed_ids, $id );
						}
					}
				}
			else
				{
				$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
				$ret = "{'error':true,'type':'auth_failure','report':'$err_string''}";
				}
			}
		else	// Belt & suspenders
			{
			$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['meeting_does_not_exist'] );
			$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
			break;
			}
		}
	
	if ( !$ret )
		{
		$result['message'] = 'unpublish';
		$result['ids'] = $changed_ids;
		$ret = array2json ( json_prepare ( $result ) );
		}
	
	return $ret;
}

/*******************************************************************/
/**
*/
function DuplicateMeetings (	$server,
								$in_comdef_search_admin_strings,
								$in_meeting_ids
								)
{
	$ret = '';
	$changed_ids = array();
	
	foreach ( $in_meeting_ids as $id )
		{
		if ( intval ( $id ) > 0 )
			{
			$meeting =& $server->GetOneMeeting($id);
			if ( $meeting instanceof c_comdef_meeting )
				{
				if ( $meeting->UserCanEdit() )
					{
					if ( !$meeting->IsCopy() )	// Can't duplicate copies. Fail silently.
						{
						$meeting2 = $server->DuplicateMeetingObj ( $meeting );
						if ( !$meeting2->UpdateToDB() )
							{
							$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
							$ret = "{'error':true,'type':'auth_failure','report':'$err_string','info':'$id'}";
							break;
							}
						else
							{
							array_push ( $changed_ids, $meeting2->GetID() );
							}
						}
					}
				else
					{
					$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
					$ret = "{'error':true,'type':'auth_failure','report':'$err_string''}";
					}
				}
			else	// Belt & suspenders
				{
				$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['meeting_does_not_exist'] );
				$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
				break;
				}
			}
		else	// Belt & suspenders
			{
			$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['meeting_does_not_exist'] );
			$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
			break;
			}
		}

	if ( !$ret )
		{
		$result['message'] = 'duplicate';
		$result['ids'] = $changed_ids;
		$ret = array2json ( json_prepare ( $result ) );
		}
	
	return $ret;
}

/*******************************************************************/
/**
*/
function ApplyDataItem (	$server,
							$in_comdef_search_admin_strings,
							$in_meeting_ids,
							$in_key_string,
							$in_value_string,
							$override
							)
{
	$ret = '';
	$changed_ids = array();
	
	foreach ( $in_meeting_ids as $id )
		{
		if ( intval ( $id ) > 0 )
			{
			$meeting =& $server->GetOneMeeting($id);
			if ( $meeting instanceof c_comdef_meeting )
				{
				if ( $meeting->UserCanEdit() )
					{
					$values =& $meeting->GetMeetingData();
					
					if ( is_array ( $values ) && count ( $values ) )
						{
						$template_data = c_comdef_meeting::GetDataTableTemplate();
						$longdata_obj = c_comdef_meeting::GetLongDataTableTemplate();
						
						// We merge the two tables (data and longdata).
						if ( is_array ( $data_obj1 ) && count ( $data_obj1 ) && is_array ( $longdata_obj ) && count ( $longdata_obj ) )
							{
							$template_data = array_merge ( $data_obj1, $longdata_obj );
							}
						
						$success = false;

						if ( !trim($in_value_string) && $override )
							{
							$success = $meeting->DeleteDataField ( $in_key_string );
							}
						else
							{
							$success = $meeting->AddDataField ( $in_key_string, $template_data[$in_key_string]['field_prompt'], $in_value_string, null, $template_data[$in_key_string]['visibility'], $override );
							}
						
						if ( $success )
							{
							$meeting->UpdateToDB();
							array_push ( $changed_ids, $meeting->GetID() );
							}
						}
					}
				else
					{
					$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
					$ret = "{'error':true,'type':'auth_failure','report':'$err_string''}";
					}
				}
			else	// Belt & suspenders
				{
				$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['meeting_does_not_exist'] );
				$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
				break;
				}
			}
		else	// Belt & suspenders
			{
			$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['meeting_does_not_exist'] );
			$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
			break;
			}
		}

	if ( !$ret )
		{
		$result['message'] = 'apply_data_item';
		$result['ids'] = $changed_ids;
		$result['extra_data'] = $in_comdef_search_admin_strings['Edit_Meetings']['edit_data_item_value_apply_complete1'];
		$result['extra_data'] .= $in_key_string.$in_comdef_search_admin_strings['Edit_Meetings']['edit_data_item_value_apply_complete2'];
		$result['extra_data'] .= $in_value_string.$in_comdef_search_admin_strings['Edit_Meetings']['edit_data_item_value_apply_complete3'];
		$result['extra_data'] .= join ( ", ", $changed_ids );
		$ret = array2json ( json_prepare ( $result ) );
		}
	
	return $ret;
}

/*******************************************************************/
/**
*/
function DeleteMeetings (	$server,
							$in_comdef_search_admin_strings,
							$in_meeting_ids,
							$in_extreme = false
							)
{
	$ret = '';
	
	$changed_ids = array();
	
	foreach ( $in_meeting_ids as $id )
		{
		if ( intval ( $id ) > 0 )
			{
			$meeting =& $server->GetOneMeeting($id);
			if ( $meeting instanceof c_comdef_meeting )
				{
				if ( $meeting->UserCanEdit() )
					{
					if ( !$in_extreme )
						{
						if ( $meeting->DeleteFromDB() )
							{
							array_push ( $changed_ids, $id );
							}
						else
							{
							$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
							$ret = "{'error':true,'type':'auth_failure','report':'$err_string','info':'$id'}";
							}
						}
					else
						{
						if ( c_comdef_server::IsUserServerAdmin(null,true) && $meeting->DeleteFromDB_NoRecord() )
							{
							$changes = $server->GetChangesFromIDAndType ( 'c_comdef_meeting', $id );
							
							if ( $changes instanceof c_comdef_changes )
								{
								$obj_array =& $changes->GetChangesObjects();
								
								if ( is_array ( $obj_array ) && count ( $obj_array ) )
									{
									foreach ( $obj_array as $change )
										{
										$change->DeleteFromDB();
										}
									}
								}
							
							array_push ( $changed_ids, $id );
							}
						else
							{
							$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['auth_failure'] );
							$ret = "{'error':true,'type':'auth_failure','report':'$err_string','info':'$id'}";
							}
						}
					}
				}
			else
				{
				$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['meeting_does_not_exist'] );
				$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
				break;
				}
			}
		else	// Belt & suspenders
			{
			$err_string = json_prepare ( $in_comdef_search_admin_strings['Edit_Meeting']['meeting_does_not_exist'] );
			$ret = "{'error':true,'type':'meeting_does_not_exist','report':'$err_string','info':'$id''}";
			break;
			}
		}
	
	if ( !$ret )
		{
		$result['message'] = $in_extreme ? 'delete_extreme_prejudice' : 'delete';
		$result['ids'] = $changed_ids;
		$ret = array2json ( json_prepare ( $result ) );
		}
	
	return $ret;
}
?>