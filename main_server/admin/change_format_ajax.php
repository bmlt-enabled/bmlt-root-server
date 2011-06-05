<?php
/***********************************************************************/
/** \file	change_format_ajax.php

	\brief	This is a handler that is called from an AJAX call, and it is
	how a format is changed. The user session is checked to ensure that
	the user is authorized to edit the format.
	
	This echoes one of two JSON objects.
	
	If there is failure, then the JSON object will contain the following:
	
		- error		true
		- report	A string, containing the error report
		- info		A string, with an indicator of the meeting ID (i.e. 'Meeting 1401').
		
	If there is success, then the JSON object will contain the following:
		- success	true
		- report	A string, indicating that the format was changed.

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
	
	session_start();
	$server = c_comdef_server::MakeServer();
	
	if ( $server instanceof c_comdef_server )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();

		$lang_enum = c_comdef_server::GetServer()->GetLocalLang();
		include ( dirname ( __FILE__ ).'/../server/config/auto-config.inc.php' );
	
		try
			{
			$is_new_format = false;
			if ( isset ( $_GET['new_lang'] ) && $_GET['new_lang'] )
				{
				$_GET['lang'] = $_GET['new_lang'];
				$format_obj = new c_comdef_format ( c_comdef_server::GetServer(),
													$_GET['shared_id'],
													$_GET['type'],
													$_GET['key'],
													isset ( $_GET['icon'] ) ? $_GET['icon'] : null,
													isset ( $_GET['world_id'] ) ? $_GET['world_id'] : null,
													$_GET['new_lang'],
													$_GET['name'],
													$_GET['description'] );
					if ( $format_obj->UserCanEdit() )
						{
						if ( !c_comdef_server::IsFormatKeyUnique ( $_GET['key'], $_GET['lang'] ) )
							{
							$format_obj = null;
							$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['format_key_not_unique'] );
	                        header ( 'Content-type: application/json' );
							echo "{'error':true,'type':'format_key_not_unique','report':'$err_string','info':'".$_GET['key']."'}";
							}
						$is_new_format = true;
						}
					else
						{
						$format_obj = null;
						}
				}
			else
				{
				$format_obj = c_comdef_server::GetOneFormat ( $_GET['shared_id'], $_GET['lang'] );
				if ( $format_obj instanceof c_comdef_format )
					{
					if ( $format_obj->UserCanEdit() )
						{
						if ( c_comdef_server::IsFormatKeyUnique ( $_GET['key'], $_GET['lang'] )
							|| ($_GET['key'] == $format_obj->GetKey()) )
							{
							$format_obj->SetKey ( $_GET['key'] );
							$format_obj->SetFormatType ( $_GET['type'] );
							$format_obj->SetLocalName ( $_GET['name'] );
							$format_obj->SetLocalDescription ( $_GET['description'] );
							}
						else
							{
							$format_obj = null;
							$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['format_key_not_unique'] );
	                        header ( 'Content-type: application/json' );
							echo "{'error':true,'type':'format_key_not_unique','report':'$err_string','info':'".$_GET['key']."'}";
							}
						}
					}
				}
			
			if ( $format_obj instanceof c_comdef_format )
				{
				if ( $format_obj->UserCanEdit() )
					{
					$format_obj->UpdateToDB();
	                header ( 'Content-type: application/json' );
					echo "{'success':true,'id':'".$_GET['shared_id']."','lang':'".$_GET['lang']."','new_format':".($is_new_format?'true':'false')."}";
					}
				}
			}
		catch ( Exception $e )
			{
			$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['object_not_changed'] );
	        header ( 'Content-type: application/json' );
			echo "{'error':true,'type':'object_not_changed','report':'$err_string'}";
			}
		}
	else
		{
		$err_string = json_prepare ( $localized_strings['comdef_search_admin_strings']['Edit_Formats']['object_not_changed'] );
	    header ( 'Content-type: application/json' );
		echo "{'error':true,'type':'object_not_changed','report':'$err_string'}";
		}
?>