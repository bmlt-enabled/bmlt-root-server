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
			include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
			$localized_strings = c_comdef_server::GetLocalStrings();
	
			// The first thing that we do is to get the original ID of the meeting (in case the ID is to be changed)
			if ( isset ( $_POST['original_id'] ) )
				{
				$orig_id = $_POST['original_id'];
				unset ( $_POST['original_id'] );
				}
			
			$cur_user =& c_comdef_server::GetCurrentUserObj(true);
			
			if ( $cur_user instanceof c_comdef_user )
				{
				$new_user = false;
				
				if ( $orig_id )
					{
					$user =& c_comdef_server::GetUserByIDObj ( $orig_id );
					}
				// Only server admins can create new users.
				elseif ( c_comdef_server::IsUserServerAdmin(null,true) )
					{
					$users_obj =& c_comdef_server::GetServerUsersObj();
					
					if ( $users_obj instanceof c_comdef_users )
						{
						// Fixed a bug pointed out by MG on 12/19/2010. The $lang_enum was useless. Replaced with null.
						$user = new c_comdef_user ($users_obj, 0, _USER_LEVEL_DISABLED, null, null, null, null, null, null);
						$users_obj->AddUser ( $user );
						}
					$new_user = true;
					}
				
				if ( $user instanceof c_comdef_user )
					{
					if ( $user->UserCanEdit() )
						{
						$new_pass = null;
						$new_login_string = null;

						$_POST['login_string'] = trim ( $_POST['login_string'] );
						$_POST['password_string'] = trim ( $_POST['password_string'] );
						$orig_login = $user->GetLogin();
						
						// We may need to change the session, if we are changing our login ID or password.
						$new_login = false;
						
						// Server admins can do a couple of things that regular users can't.
						if ( c_comdef_server::IsUserServerAdmin(null,true) )
							{
							// If we are not changing our own password, we can force-change any users' passwords.
							if ( $orig_id != $cur_user->GetID() )
								{
								if ( $_POST['login_string'] )
									{
									}
								
								if ( $_POST['password_string'] )
									{
									if ( strlen ( $_POST['password_string'] ) >= $min_pw_len )
										{
										$new_pass = $_POST['password_string'];
										unset ( $_POST['password_string'] );
										}
									else
										{
										$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['pw_too_short'] );
										$err_string = str_replace( '##MIN_PW_LEN##', intval ( $min_pw_len ), $err_string );
	                                    header ( 'Content-type: application/json' );
										die ("{'error':true,'type':'pw_too_short','id':'$orig_id','report':'$err_string'}");
										}
									}

								$user->SetUserLevel ( intval ( $_POST['user_level_tinyint'] ) );
								}
							
							// We can change a user's login ID (including our own)
							if ( $_POST['login_string'] && ($_POST['login_string'] != $user->GetLogin()) )
								{
								$can_change = true;
								
								$users_obj =& c_comdef_server::GetServerUsersObj();
								
								if ( $users_obj instanceof c_comdef_users )
									{
									$user_array_temp = $users_obj->GetUsersArray();
									
									// OK, we should have at least one user.
									if ( is_array ( $user_array_temp ) && count ( $user_array_temp ) )
										{
										foreach ( $user_array_temp as &$user_t )
											{
											if ( $user_t instanceof c_comdef_user )
												{
												// We can't have dupe logins.
												if ( $user_t->GetLogin() == $_POST['login_string'] )
													{
													$user_id = $orig_id;
													$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['dup_login'] );
	                                                header ( 'Content-type: application/json' );
													die ("{'error':true,'type':'dup_login','id':'$orig_id','report':'$err_string','orig_login':'$orig_login'}");
													}
												}
											}
										}
									else
										{
										$can_change = false;
										}
									}
								else
									{
									$can_change = false;
									}
								
								if ( $can_change )
									{
									$new_login_string = $_POST['login_string'];
									if ( $orig_id == $cur_user->GetID() )
										{
										$new_login = true;
										}
									}
								}
							}
						// If the password is still around, then we want to change our own password.
						// We have to be the user in order to change the password.
						if ( isset($_POST['password_string']) &&  $_POST['password_string'] && ($user->IsUser ($cur_user->GetLogin(), $cur_user->GetPassword()) ) )
							{
							if ( strlen ( $_POST['password_string'] ) >= $min_pw_len )
								{
								$new_pass = $_POST['password_string'];
								$new_login = true;
								}
							else
								{
								$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['pw_too_short'] );
								$err_string = str_replace( '##MIN_PW_LEN##', intval ( $min_pw_len ), $err_string );
	                            header ( 'Content-type: application/json' );
								die ("{'error':true,'type':'pw_too_short','id':'$orig_id','report':'$err_string'}");
								}
							}
						
						$user->SetLocalName ( $_POST['name_string'] );
						$user->SetLocalDescription ( stripslashes ( $_POST['description_string'] ) );
						$user->SetLocalLang ( $_POST['lang_enum'] );
							
						$value = trim ( $_POST['email_address_string'] );
						if ( $value )
							{
							if ( c_comdef_vet_email_address ( $value ) )
								{
								$user->SetEmailAddress ( $value );
								}
							else
								{
								$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Meeting']['email_format_bad'] );
	                            header ( 'Content-type: application/json' );
								die ( "{'error':true,'id':'$orig_id','type':'email_format_bad','report':'$err_string'}" );
								}
							}
						else
							{
							$user->SetEmailAddress ( '' );
							}
						
						$user->UpdateToDB(false, $new_login_string, $new_pass );

						if ( !$orig_id )
							{
							$orig_id = $user->GetID();
							}
						
						// If we changed our own login and password, we need to re-establish the session with the new credentials.
						if ( $new_login && ($user->GetID() == $cur_user->GetID()) )
							{
							$cur_user->RestoreFromDB();
							$login = $cur_user->GetLogin();
							$enc_password = $cur_user->GetPassword();
							$_SESSION[$admin_session_name] = "$login\t$enc_password";
							}
						
						$name = str_replace ( "'", "\\'", $user->GetLocalName() );
	                    header ( 'Content-type: application/json' );
						echo "{'success':true,'id':'$orig_id','new_user':".((true == $new_user) ? 'true' : 'false').",'name':'$name','super_user':".(c_comdef_server::IsUserServerAdmin(null,true) ? 'true' : 'false')."}";
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
					$orig_id = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['user_id'] ).$orig_id;
					$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['object_not_found'] );
	                header ( 'Content-type: application/json' );
					echo "{'error':true,'type':'object_not_found','report':'$err_string','info':'$orig_id'}";
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
			$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['server_not_instantiated'] );
	        header ( 'Content-type: application/json' );
			echo "{'error':true,'type':'server_not_instantiated','report':'$err_string'}";
			}
		}
	catch ( Exception $e )
		{
		$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Users']['object_not_changed'] );
	    header ( 'Content-type: application/json' );
		echo "{'error':true,'type':'object_not_changed','report':'$err_string'}";
		}
?>