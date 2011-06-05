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
	
	try
		{
		session_start();
		$server = c_comdef_server::MakeServer();
		
		if ( $server instanceof c_comdef_server )
			{
			$localized_strings = c_comdef_server::GetLocalStrings();

			include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
			
			$cur_user =& c_comdef_server::GetCurrentUserObj(true);
			
			// Must be a server admin to delete a user.
			if ( ($cur_user instanceof c_comdef_user) || ($cur_user->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) )
				{
				$id_bigint = $_POST['user_id'];
				
				if ( $id_bigint )
					{
					$user_obj =& c_comdef_server::GetUserByIDObj ( $id_bigint );
					
					// Make sure that we have a legitimate user object.
					if ( $user_obj instanceof c_comdef_user )
						{
						// Can never be too sure, when it comes to security.
						if ( $user_obj->UserCanEdit() )
							{
							// The first thing that we need to do, is go through all the Service bodies, and remove the user ID from editors or principal users.
							$sb_array = $server->GetServiceBodyArray();
							
							if ( is_array ( $sb_array ) && count ( $sb_array ) )
								{
								foreach ( $sb_array as $sb )
									{
									// Have to have the right to edit it, first.
									// Since we shouldn't even be here unless we are a server admin, this should be a formality only.
									if ( $sb->UserCanEdit () )
										{
										$user_t = $sb->GetPrincipalUserID();
										
										// If the principal user is this user, we switch to the main Server Admin.
										if ( $user_t == $user_obj->GetID() )
											{
											$sb->SetPrincipalUserID ( 1 );
											}
										
										$editors = $sb->GetEditors();
										
										// We build up a new editor array, without this user.
										$new_editors = array();
										
										// Next, we remove the user ID from any editors.
										if ( is_array ( $editors ) && count ( $editors ) )
											{
											foreach ( $editors as $editor )
												{
												if ( $editor != $user_obj->GetID() )
													{
													array_push ( $new_editors, $editor );
													}
												}
											}
										
										$sb->SetEditors ( $new_editors );
										}
									}
								}
							
							$del_message = $localized_strings['comdef_search_admin_strings']['Edit_Users']['del_message'];
							$del_message = json_prepare ( str_replace ( '##USER##', $user_obj->GetLocalName().' ('.$user_obj->GetLogin().')', $del_message ) );
							// Okay, now we can delete the user.
							$user_obj->DeleteFromDB();

	                        header ( 'Content-type: application/json' );
							echo "{'success':true,'id':'$id_bigint','message':'$del_message'}";
							}
						else
							{
							$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['auth_failure'] );
	                        header ( 'Content-type: application/json' );
							echo "{'error':true,'type':'auth_failure','report':'$err_string'}";
							}
						}
					else
						{
						$id_bigint = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['user_id'] ).$id_bigint;
						$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['object_not_found'] );
	                    header ( 'Content-type: application/json' );
						echo "{'error':true,'type':'object_not_found','report':'$err_string','info':'$id_bigint'}";
						}
					}
				else
					{
					$id_bigint = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['user_id'] ).$id_bigint;
					$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['object_not_found'] );
	                header ( 'Content-type: application/json' );
					echo "{'error':true,'type':'object_not_found','report':'$err_string','info':'$id_bigint'}";
					}
				}
			else
				{
				$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['auth_failure'] );
	            header ( 'Content-type: application/json' );
				echo "{'error':true,'type':'auth_failure','report':'$err_string'}";
				}
			}
		else
			{
			$err_string = json_prepare ( 'CANNOT CREATE SERVER' );
	        header ( 'Content-type: application/json' );
			echo "{'error':true,'type':'server_not_instantiated','report':'$err_string'}";
			}
		}
	catch ( Exception $e )
		{
		$err_string = json_prepare ( 'EXCEPTION THROWN' );
	    header ( 'Content-type: application/json' );
		echo "{'error':true,'type':'object_not_changed','report':'$err_string'}";
		}
?>