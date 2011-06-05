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

	session_start();
	try
		{
		$server = c_comdef_server::MakeServer();
		
		if ( $server instanceof c_comdef_server )
			{
			include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
			$localized_strings = c_comdef_server::GetLocalStrings();

			// The first thing that we do is to get the original ID of the meeting (in case the ID is to be changed)
			if ( isset ( $_POST['original_id'] ) )
				{
				$orig_id = $_POST['original_id'];
				unset ( $_POST['original_id'] );
				}
			
			$new_sb = false;
			
			if ( $orig_id )
				{
				$service_body =& c_comdef_server::GetServiceBodyByIDObj ( $orig_id );
				}
			else
				{
				$service_body = new c_comdef_service_body;
				$new_sb = true;
				}
			
			if ( !isset ( $_POST['parent_bigint'] ) || !($server->GetServiceBodyByIDObj(intval ( $_POST['parent_bigint'] )) instanceof c_comdef_service_body) )
				{
				$_POST['parent_bigint'] = 0;
				}
			
			if ( !isset ( $_POST['parent_2_bigint'] ) || !($server->GetServiceBodyByIDObj(intval ( $_POST['parent_2_bigint'] )) instanceof c_comdef_service_body) )
				{
				$_POST['parent_2_bigint'] = 0;
				}
			
			if ( $service_body instanceof c_comdef_service_body )
				{
				$service_body->SetLocalName ( $_POST['name_string'] );
				$service_body->SetLocalDescription ( stripslashes ( $_POST['description_string'] ) );
				$service_body->SetWorldID ( $_POST['worldid_mixed'] );
				$service_body->SetLocalLang ( $_POST['lang_enum'] );
				// Security measure: Make sure the user exists.
				if ( (isset ( $_POST['principal_user_bigint'] ) && !$_POST['principal_user_bigint']) || ($server->GetUserByIDObj($_POST['principal_user_bigint']) instanceof c_comdef_user) )
					{
					$service_body->SetPrincipalUserID ( $_POST['principal_user_bigint'] );
					}
				$service_body->SetEditors ( explode( ",", $_POST['editors_string'] ) );
				$service_body->SetKMLURI ( $_POST['kml_uri_string'] );
				$service_body->SetURI ( $_POST['uri_string'] );
				$service_body->SetOwnerID ( $_POST['parent_bigint'] );
				$service_body->SetOwner2ID ( $_POST['parent_2_bigint'] );
				$service_body->SetSBType ( $_POST['type'] );
							
				$value = trim ( $_POST['sb_meeting_email'] );
				if ( $value )
					{
					if ( c_comdef_vet_email_address ( $value ) )
						{
						$service_body->SetContactEmail ( $value );
						}
					else
						{
						$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['service_body_id'] ).$orig_id;
						$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_format_bad'] );
	                    header ( 'Content-type: application/json' );
						die ( "{'error':true,'type':'email_format_bad','report':'$err_string','info':'$orig_id'}" );
						}
					}
				else
					{
					$service_body->SetContactEmail ( '' );
					}

				$name = str_replace ( "'", "\\'", $service_body->GetLocalName() );

				$service_body->UpdateToDB();
				
	            header ( 'Content-type: application/json' );
				echo "{'success':true,'id':'$orig_id','lang':'".$_POST['lang_enum']."','new_sb':".((true == $new_sb) ? 'true' : 'false').",'sb_name' : '$name'}";
				}
			else
				{
				$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['service_body_id'] ).$orig_id;
				$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['object_not_found'] );
	            header ( 'Content-type: application/json' );
				echo "{'error':true,'type':'object_not_found','report':'$err_string','info':'$orig_id'}";
				}
			}
		else
			{
			$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['service_body_id'] ).$orig_id;
			$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['server_not_instantiated'] );
	        header ( 'Content-type: application/json' );
			echo "{'error':true,'type':'server_not_instantiated','report':'$err_string','info':'$orig_id'}";
			}
		}
	catch ( Exception $e )
		{
		$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['service_body_id'] ).$orig_id;
		$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Service_Bodies']['object_not_changed'] );
	    header ( 'Content-type: application/json' );
		echo "{'error':true,'type':'object_not_changed','report':'$err_string','info':'$orig_id'}";
		}
?>