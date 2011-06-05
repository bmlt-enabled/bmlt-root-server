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
	
	session_start();
	$server = c_comdef_server::MakeServer();
	if ( $server instanceof c_comdef_server )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();
				
		$ret = '';
		// Only server admins can delete permanently.
		if ( c_comdef_server::IsUserServerAdmin(null,true) )
			{
			$ret = DeleteMeetingChanges ( $server, $localized_strings['comdef_search_admin_strings'], intval ( $_POST['meeting_id'] ), $_POST['dt_id'], $_POST['meeting_name'] );
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
function DeleteMeetingChanges (	$server,
								$in_comdef_search_admin_strings,
								$in_meeting_id,
								$in_dt_id,
								$in_name
								)
{
	$ret = '';

	if ( c_comdef_server::IsUserServerAdmin(null,true) )
		{
		$changes = $server->GetChangesFromIDAndType ( 'c_comdef_meeting', $in_meeting_id );
		
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
		}
	
	if ( !$ret )
		{
		$result['message'] = $in_extreme ? 'delete_extreme_prejudice' : 'delete';
		$result['id'] = $in_meeting_id;
		$result['dt_id'] = $in_dt_id;
		$result['report'] = $in_comdef_search_admin_strings['Edit_Meeting']['meeting_permanently_deleted'].'"'.$in_name.'"';
		$ret = array2json ( json_prepare ( $result ) );
		}
	
	return $ret;
}
?>