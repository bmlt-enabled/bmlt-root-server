<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://bmlt.magshare.org

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
require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2Json.php');
require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2XML.php');
require_once ( dirname ( __FILE__ ).'/../../client_interface/csv/search_results_csv.php' );
require_once ( dirname ( __FILE__ ).'/PhpJsonXmlArrayStringInterchanger.inc.php' );

/***********************************************************************************************************//**
    \class c_comdef_admin_main_console
    \brief Controls display of the main BMLT administration console.
***************************************************************************************************************/
class c_comdef_admin_ajax_handler
{
    var $my_localized_strings;          ///< This will contain the localized strings and whatnot for display.
    var $my_server;                     ///< This hold the server object.
    var $my_user;                       ///< This holds the instance of the logged-in user.
    var $my_http_vars;                  ///< Contains the HTTP vars sent in.
    
    /*******************************************************************************************************//**
    \brief
    ***********************************************************************************************************/
    function __construct (  $in_http_vars   ///< The HTTP transaction parameters
                        )
    {
        $this->my_http_vars = $in_http_vars;
        $this->my_localized_strings = c_comdef_server::GetLocalStrings();
        $this->my_server = c_comdef_server::MakeServer();
        $this->my_user = $this->my_server->GetCurrentUserObj();
        
        // We check this every chance that we get.
        if ( !$this->my_user || ($this->my_user->GetUserLevel() == _USER_LEVEL_DISABLED) )
            {
            die ( '<h2>NOT AUTHORIZED</h2>' );
            }
    }
    
    /*******************************************************************************************************//**
    \brief
    \returns
    ***********************************************************************************************************/
    function parse_ajax_call()
    {
        $returned_text = '';
        
        $account_changed = false;
        
        if ( isset ( $this->my_http_vars['set_service_body_change'] ) && $this->my_http_vars['set_service_body_change'] )
            {
            $this->HandleServiceBodyChange ( $this->my_http_vars['set_service_body_change'] );
            }
        else if ( isset ( $this->my_http_vars['delete_service_body'] ) && $this->my_http_vars['delete_service_body'] )
            {
            $this->HandleDeleteServiceBody ( $this->my_http_vars['delete_service_body'], isset ( $this->my_http_vars['permanently'] ) );
            }
        else if ( isset ( $this->my_http_vars['set_meeting_change'] ) && $this->my_http_vars['set_meeting_change'] )
            {
            $this->HandleMeetingUpdate ( $this->my_http_vars['set_meeting_change'] );
            }
        else if ( isset ( $this->my_http_vars['delete_meeting'] ) && $this->my_http_vars['delete_meeting'] )
            {
            $returned_text = $this->HandleDeleteMeeting ( $this->my_http_vars['delete_meeting'], isset ( $this->my_http_vars['permanently'] ) );
            }
        else if ( isset ( $this->my_http_vars['get_meeting_history'] ) && $this->my_http_vars['get_meeting_history'] )
            {
            $returned_text = $this->GetMeetingHistory ( $this->my_http_vars['get_meeting_history'] );
            }
        else if ( isset ( $this->my_http_vars['do_meeting_search'] ) )
            {
            $returned_text = $this->TranslateToJSON ( $this->GetSearchResults ( $this->my_http_vars ) );
            }
        else
            {
            if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_password_value'] ) )
                {
                $this->my_user->SetNewPassword ( $this->my_http_vars['account_password_value'] );
                $success = $this->my_user->UpdateToDB ( false, null, true );
                $account_changed = true;
                if ( $ret )
                    {
                    $ret .= ',';
                    }
                $ret .= '{\'PASSWORD_CHANGED\':'.($success ? 'true' : 'false').'}';
                }
        
            if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_email_value'] ) )
                {
                $this->my_user->SetEmailAddress ( $this->my_http_vars['account_email_value'] );
                $success = $this->my_user->UpdateToDB ( );
                $account_changed = true;
                if ( $ret )
                    {
                    $ret .= ',';
                    }
                $ret .= '{\'EMAIL_CHANGED\':'.($success ? 'true' : 'false').'}';
                }
        
            if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_description_value'] ) )
                {
                $this->my_user->SetLocalDescription ( $this->my_http_vars['account_description_value'] );
                $account_changed = true;
                $success = $this->my_user->UpdateToDB ( );
                if ( $ret )
                    {
                    $ret .= ',';
                    }
                $ret .= '{\'DESCRIPTION_CHANGED\':'.($success ? 'true' : 'false').'}';
                }
        
            if ( $account_changed )
                {
                $returned_text .= '{\'ACCOUNT_CHANGED\':'.$ret.'}';
                }
            }
        
        return  $returned_text;
    }

    /*******************************************************************/
    /**
        \brief	This handles updating an existing Service body.
    */	
    function HandleServiceBodyChange (  $in_service_body_data    ///< A JSON object, containing the new Service Body data.
                                        )
    {
        $json_tool = new PhpJsonXmlArrayStringInterchanger;
        
        $the_new_service_body = $json_tool->convertJsonToArray ( $in_service_body_data, true );
        
        if ( is_array ( $the_new_service_body ) && count ( $the_new_service_body ) )
            {
            $id = $the_new_service_body[0];
            $parent_service_body_id = $the_new_service_body[1];
            $name = $the_new_service_body[2];
            $description = $the_new_service_body[3];
            $main_user_id = $the_new_service_body[4];
            $editor_ids = explode ( ',', $the_new_service_body[5] );
            $email = $the_new_service_body[6];
            $uri = $the_new_service_body[7];
            $kml_uri = $the_new_service_body[8];
            $type = $the_new_service_body[9];
            
            $sb_to_change = $this->my_server->GetServiceBodyByIDObj ( $id );
            
            if ( $sb_to_change instanceof c_comdef_service_body )
                {
                $sb_to_change->SetOwnerID ( $parent_service_body_id );
                $sb_to_change->SetLocalName ( $name );
                $sb_to_change->SetLocalDescription ( $description );
                $sb_to_change->SetPrincipalUserID ( $main_user_id );
                $sb_to_change->SetEditors ( $editor_ids );
                $sb_to_change->SetContactEmail ( $email );
                $sb_to_change->SetURI ( $uri );
                $sb_to_change->SetKMLURI ( $kml_uri );
                $sb_to_change->SetSBType ( $type );
                
                if ( $sb_to_change->UpdateToDB() )
                    {
                    header ( 'Content-type: application/json' );
                    echo "{'success':true,'service_body':".array2json ( $the_new_service_body )."}";
                    }
                else
                    {
                    $err_string = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_cant_update_text'] );
                    header ( 'Content-type: application/json' );
                    echo "{'success':false,'report':'$err_string'}";
                    }
                }
            else
                {
                $err_string = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_cant_find_sb_text'] );
                header ( 'Content-type: application/json' );
                echo "{'success':false,'report':'$err_string'}";
                }
            }
        else
            {
            $err_string = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_no_data_text'] );
            header ( 'Content-type: application/json' );
            echo "{'success':false,'report':'$err_string'}";
            }
    }
    
    /*******************************************************************/
    /**
        \brief
    */	
    function GetMeetingHistory (    $in_meeting_id
                                )
    {
        $ret = '[';
        
        $changes = $this->my_server->GetChangesFromIDAndType ( 'c_comdef_meeting', $in_meeting_id );
    
        if ( $changes instanceof c_comdef_changes )
            {
            $obj_array =& $changes->GetChangesObjects();
        
            if ( is_array ( $obj_array ) && count ( $obj_array ) )
                {
                $first = true;
                
                foreach ( $obj_array as $change )
                    {
                    if ( !$first )
                        {
                        $ret .= ',';
                        }
                    else
                        {
                        $first = false;
                        }
                    
                    $ret .= '{';
                        $change_id = $change->GetID();
                        $user_name = json_prepare ( $this->my_server->GetUserByIDObj ( $change->GetUserID() )->GetLocalName() );
                        $change_description = json_prepare ( $change->DetailedChangeDescription() );
                        $change_date = json_prepare ( date ( 'g:i A, F j Y', $change->GetChangeDate() ) );
                        
                        $ret .= '"id":'.$change_id.',';
                        $ret .= '"user":"'.$user_name.'",';
                        $ret .= '"description":["'.implode ( '","', $change_description['details'] ).'"],';
                        $ret .= '"date":"'.$change_date.'"';
                        
                    $ret .= '}';
                    }
                }
            }
            
        $ret .= ']';
        
        return $ret;
    }
    
    /*******************************************************************/
    /**
        \brief
    */	
    function HandleDeleteMeeting (  $in_meeting_id,
                                    $in_delete_permanently = false
                                    )
    {
		try
			{
			$meeting =& $this->my_server->GetOneMeeting($in_meeting_id);
			
			if ( $meeting instanceof c_comdef_meeting )
				{
				if ( $meeting->UserCanEdit() )
					{
					if ( $meeting->DeleteFromDB() )
						{
						if ( $in_delete_permanently )
						    {
						    $this->DeleteMeetingChanges ( $in_meeting_id );
						    }
						
	                    header ( 'Content-type: application/json' );
						echo "{'success':true,'report':'$in_meeting_id'}";
						}
					else
						{
	                    header ( 'Content-type: application/json' );
						echo "{'success':false,'report':'$in_meeting_id'}";
						}
					}
				else
					{
                    header ( 'Content-type: application/json' );
                    echo "{'success':false,'report':'$in_meeting_id'}";
					}
				}
			else
				{
                header ( 'Content-type: application/json' );
                echo "{'success':false,'report':'$in_meeting_id'}";
				}
			}
		catch ( Exception $e )
			{
            header ( 'Content-type: application/json' );
            echo "{'success':false,'report':'$in_meeting_id'}";
			}
    }
    
    /*******************************************************************/
    /**
        \brief
    */	
    function HandleDeleteServiceBody (  $in_sb_id,
                                        $in_delete_permanently = false
                                    )
    {
        if ( c_comdef_server::IsUserServerAdmin(null,true) )
            {
            try
                {
                $service_body =& $this->my_server->GetServiceBodyByIDObj($in_sb_id);
            
                if ( $service_body instanceof c_comdef_service_body )
                    {
                    if ( $service_body->DeleteFromDB() )
                        {
                        if ( $in_delete_permanently )
                            {
                            $this->DeleteServiceBodyChanges ( $in_sb_id );
                            }
                    
                        header ( 'Content-type: application/json' );
                        echo "{'success':true,'report':'$in_sb_id'}";
                        }
                    else
                        {
                        header ( 'Content-type: application/json' );
                        echo "{'success':false,'report':'$in_sb_id'}";
                        }
                    }
                else
                    {
                    header ( 'Content-type: application/json' );
                    echo "{'success':false,'report':'$in_sb_id'}";
                    }
                }
            catch ( Exception $e )
                {
                header ( 'Content-type: application/json' );
                echo "{'success':false,'report':'$in_sb_id'}";
                }
            }
        else
            {
            echo 'NOT AUTHORIZED';
            }
    }

    /*******************************************************************/
    /**
    */
    function DeleteMeetingChanges (	$in_meeting_id
                                    )
    {
        if ( c_comdef_server::IsUserServerAdmin(null,true) )
            {
            $changes = $this->my_server->GetChangesFromIDAndType ( 'c_comdef_meeting', $in_meeting_id );
        
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
    }

    /*******************************************************************/
    /**
    */
    function DeleteServiceBodyChanges (	$in_sb_id
                                        )
    {
        if ( c_comdef_server::IsUserServerAdmin(null,true) )
            {
            $changes = $this->my_server->GetChangesFromIDAndType ( 'c_comdef_service_body', $in_sb_id );
        
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
    }

    /*******************************************************************/
    /**
        \brief	This handles updating an existing meeting, or adding a new one.
    */	
    function HandleMeetingUpdate (  $in_meeting_data    ///< A JSON object, containing the new meeting data.
                                )
    {
        $json_tool = new PhpJsonXmlArrayStringInterchanger;
        
        $the_new_meeting = $json_tool->convertJsonToArray ( $in_meeting_data, true );
        
        if ( is_array ( $the_new_meeting ) && count ( $the_new_meeting ) )
            {
            $this->SetMeetingDataValues ( $the_new_meeting );
            }
    }
    
    /*******************************************************************/
    /**
        \brief
    */	
    function SetMeetingDataValues (  $in_meeting_data    ///< A JSON object, containing the new meeting data.
                                )
    {
		try
            {
            if ( $in_meeting_data['id_bigint'] )
                {
                $meeting =& $this->my_server->GetOneMeeting($in_meeting_data['id_bigint']);
                }
            else
                {
                $data = array ( 'service_body_bigint' => intval ( $in_meeting_data['service_body_bigint'] ),
                                'weekday_tinyint' => intval ( $in_meeting_data['weekday_tinyint'] ),
                                'start_time' => $in_meeting_data['start_time'],
                                'lang_enum' => (isset ( $in_meeting_data['lang_enum'] ) && $in_meeting_data['lang_enum']) ? $in_meeting_data['lang_enum'] : $this->my_server->GetLocalLang()
                                );
                $meeting = new c_comdef_meeting ( $this->my_server, $data );
                }
            
            if ( $meeting instanceof c_comdef_meeting )
                {
                // Security precaution: We check the session to make sure that the user is authorized for this meeting.
                if ( $meeting->UserCanEdit() )
                    {
                    $result_data = array ( 'meeting_id' => $in_meeting_data['id_bigint'] );
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
                
                    foreach ( $in_meeting_data as $key => $value )
                        {
                        if ( $key == 'formats' )
                            {
                            $vals = array();
                            $value = explode ( ",", $value );
                            $lang = $this->my_server->GetLocalLang();
                            foreach ( $value as $fkey )
                                {
                                $object = c_comdef_server::GetServer()->GetFormatsObj()->GetFormatByKeyAndLanguage ( $fkey, $lang );
                                if ( $object )
                                    {
                                    $vals[$object->GetSharedID()] = $object;
                                    }
                                }
                            uksort ( $vals, array ( 'c_comdef_meeting','format_sorter_simple' ) );
                            $value = $vals;
                            }
                
                        switch ( $key )
                            {
                            case    'zoom':
                            case	'distance_in_km':		// These are ignored.
                            case	'distance_in_miles':
                            break;
                
                            // These are the "fixed" or "core" data values.
                            case	'worldid_mixed':
                            case	'start_time':
                            case	'lang_enum':
                            case	'duration_time':
                            case	'formats':
                                $data[$key] = $value;
                            break;
                            
                            case	'longitude':
                            case	'latitude':
                                $data[$key] = floatval ( $value );
                            break;
                
                            case	'id_bigint':
                            case	'service_body_bigint':
                            case	'weekday_tinyint':
                                $data[$key] = intval ( $value );
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
                                        $err_string = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['email_format_bad'] );
                                        header ( 'Content-type: application/json' );
                                        die ( "{'error':true,'type':'email_format_bad','report':'$err_string','id':'".$in_meeting_data['id_bigint']."'}" );
                                        }
                                    }
                                else
                                    {
                                    $data[$key] = $value;
                                    }
                            break;
                
                            // We only accept a 1 or a 0.
                            case	'published':
                                // Meeting list editors can't publish meetings.
                                if ( c_comdef_server::GetCurrentUserObj(true)->GetUserLevel() != _USER_LEVEL_EDITOR )
                                    {
                                    $data[$key] = $value ? 1 : 0;
                                    }
                            break;
                
                            // These are the various "optional" fields.
                            default:
                                if ( isset ( $data[$key] ) )
                                    {
                                    $data[$key]['meetingid_bigint'] = $in_meeting_data['id_bigint'];
                                    $data[$key]['value'] = $value;
                                    }
                                else
                                    {
                                    $result_data['new_data']['key'] = $key;
                                    $result_data['new_data']['field_prompt'] = $template_data[$key]['field_prompt'];
                                    $result_data['new_data']['value'] = $value;
                                    $meeting->AddDataField ( $key, $template_data[$key]['field_prompt'], $value, null, intval ( $template_data[$key]['visibility'] ) );
                                    }
                            break;
                            }
                        }
                    
                    if ( $meeting->UpdateToDB() )
                        {
                        header ( 'Content-type: application/json' );
                        echo $this->TranslateToJSON ( $this->GetSearchResults ( array ( 'meeting_ids' => array ( $meeting->GetID() ) ) ) );
                        }
                    else
                        {
                        $in_meeting_data['id_bigint'] = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_meeting_id'] ).$in_meeting_data['id_bigint'];
                        $err_string = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_auth_failure'] );
                        header ( 'Content-type: application/json' );
                        echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                        }
                    }
                else
                    {
                    $in_meeting_data['id_bigint'] = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_meeting_id'] ).$in_meeting_data['id_bigint'];
                    $err_string = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_auth_failure'] );
                    header ( 'Content-type: application/json' );
                    echo "{'error':true,'type':'auth_failure','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                    }
                }
            else
                {
                $in_meeting_data['id_bigint'] = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_meeting_id'] ).$in_meeting_data['id_bigint'];
                $err_string = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_object_not_found'] );
                header ( 'Content-type: application/json' );
                echo "{'error':true,'type':'object_not_found','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
                }
            }
        catch ( Exception $e )
            {
            $in_meeting_data['id_bigint'] = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_meeting_id'] ).$in_meeting_data['id_bigint'];
            $err_string = json_prepare ( $this->my_localized_strings['comdef_server_admin_strings']['edit_Meeting_object_not_changed'] );
            header ( 'Content-type: application/json' );
            echo "{'error':true,'type':'object_not_changed','report':'$err_string','info':'".$in_meeting_data['id_bigint']."'}";
            }
    }
    
    /*******************************************************************/
    /**
        \brief	This returns the search results, in whatever form was requested.
    
        \returns CSV data, with the first row a key header.
    */	
    function GetSearchResults ( 
                                $in_http_vars,	///< The HTTP GET and POST parameters.
                                &$formats_ar    ///< This will return the formats used in this search.
                                )
    {
        if ( !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] ) && isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') && isset ( $in_http_vars['advanced_radius'] ) && isset ( $in_http_vars['advanced_mapmode'] ) && $in_http_vars['advanced_mapmode'] && ( floatval ( $in_http_vars['advanced_radius'] != 0.0 ) ) && isset ( $in_http_vars['lat_val'] ) &&	 isset ( $in_http_vars['long_val'] ) && ( (floatval ( $in_http_vars['lat_val'] ) != 0.0) || (floatval ( $in_http_vars['long_val'] ) != 0.0) ) )
            {
            $in_http_vars['geo_width'] = $in_http_vars['advanced_radius'];
            }
        elseif ( !( isset ( $in_http_vars['geo_width'] ) && $in_http_vars['geo_width'] ) && isset ( $in_http_vars['bmlt_search_type'] ) && ($in_http_vars['bmlt_search_type'] == 'advanced') )
            {
            $in_http_vars['lat_val'] = null;
            $in_http_vars['long_val'] = null;
            }
        elseif ( !isset ( $in_http_vars['geo_loc'] ) || $in_http_vars['geo_loc'] != 'yes' )
            {
            if ( !isset( $in_http_vars['geo_width'] ) )
                {
                $in_http_vars['geo_width'] = 0;
                }
            }

        $geocode_results = null;
        $ignore_me = null;
        $meeting_objects = array();

        $result = DisplaySearchResultsCSV ( $in_http_vars, $ignore_me, $geocode_results, $meeting_objects );

        if ( is_array ( $meeting_objects ) && count ( $meeting_objects ) && is_array ( $formats_ar ) )
            {
            foreach ( $meeting_objects as $one_meeting )
                {
                $formats = $one_meeting->GetMeetingDataValue('formats');

                foreach ( $formats as $format )
                    {
                    if ( $format && ($format instanceof c_comdef_format) )
                        {
                        $format_shared_id = $format->GetSharedID();
                        $formats_ar[$format_shared_id] = $format;
                        }
                    }
                }
            }
    
        if ( isset ( $in_http_vars['data_field_key'] ) && $in_http_vars['data_field_key'] )
            {
            // At this point, we have everything in a CSV. We separate out just the field we want.
            $temp_keyed_array = array();
            $result = explode ( "\n", $result );
            $keys = array_shift ( $result );
            $keys = explode ( "\",\"", trim ( $keys, '"' ) );
            $the_keys = explode ( ',', $in_http_vars['data_field_key'] );
        
            $result2 = array();
            foreach ( $result as $row )
                {
                if ( $row )
                    {
                    $index = 0;
                    $row = explode ( '","', trim ( $row, '",' ) );
                    $row_columns = array();
                    foreach ( $row as $column )
                        {
                        if ( isset ( $column ) )
                            {
                            if ( in_array ( $keys[$index++], $the_keys ) )
                                {
                                array_push ( $row_columns, $column );
                                }
                            }
                        }
                    $result2[$row[0]] = '"'.implode ( '","', $row_columns ).'"';
                    }
                }

            $the_keys = array_intersect ( $keys, $the_keys );
            $result = '"'.implode ( '","', $the_keys )."\"\n".implode ( "\n", $result2 );
            }
    
        return $result;
    }

    /*******************************************************************/
    /**
        \brief Translates CSV to JSON.
    
        \returns a JSON string, with all the data in the CSV.
    */	
    function TranslateToJSON ( $in_csv_data ///< An array of CSV data, with the first element being the field names.
                            )
    {
        $temp_keyed_array = array();
        $in_csv_data = explode ( "\n", $in_csv_data );
        $keys = array_shift ( $in_csv_data );
        $keys = explode ( "\",\"", trim ( $keys, '"' ) );
    
        foreach ( $in_csv_data as $row )
            {
            if ( $row )
                {
                $line = null;
                $index = 0;
                $row = explode ( '","', trim ( $row, '",' ) );
                foreach ( $row as $column )
                    {
                    if ( isset ( $column ) )
                        {
                        $line[$keys[$index++]] = $column;
                        }
                    }
                array_push ( $temp_keyed_array, $line );
                }
            }
    
        $out_json_data = array2json ( $temp_keyed_array );

        return $out_json_data;
    }
};

$handler = new c_comdef_admin_ajax_handler($http_vars);

$ret = 'ERROR';

if ( $handler instanceof c_comdef_admin_ajax_handler )
    {
    $ret = $handler->parse_ajax_call();
    }

echo $ret;
?>