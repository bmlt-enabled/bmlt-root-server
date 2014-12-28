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
/***********************************************************************************************************//**
    \class c_comdef_admin_xml_handler
    \brief Controls handling of the admin semantic interface.
    
            This class should not even be instantiated unless the user has been authorized, and an authorized seesion
            is in progress.
***************************************************************************************************************/
class c_comdef_admin_xml_handler
{
    var $http_vars;                     ///< This will hold the combined GET and POST parameters for this call.
    var $server;                        ///< The BMLT server model instance.
    var $my_localized_strings;          ///< An array of localized strings.
    var $handled_service_body_ids;      ///< This is used to ensure that we respect the hierarchy when doing a hierarchical Service body request.
    
    /********************************************************************************************************//**
    \brief The class constructor.
    ************************************************************************************************************/
    function __construct (  $in_http_vars,        ///< The combined GET and POST parameters.
                            $in_server            ///< The BMLT server instance.
                        )
    {
        $this->http_vars = $in_http_vars;
        $this->server = $in_server;
        $this->my_localized_strings = c_comdef_server::GetLocalStrings();
        $this->handled_service_body_ids = array();
    }
    
    /********************************************************************************************************//**
    \brief This is called to process the input and generate the output. It is the "heart" of the class.
    
    \returns XML to be returned.
    ************************************************************************************************************/
    function process_commands()
    {
        $ret = NULL;
        // We make sure that we are allowed to access this level of functionality.
        // This is "belt and suspenders." We will constantly check user credentials.
        if ( $this->basic_user_validation() )
            {
            if ( isset ( $this->http_vars['admin_action'] ) && trim ( $this->http_vars['admin_action'] ) )
                {
                switch ( strtolower ( trim ( $this->http_vars['admin_action'] ) ) )
                    {
                    case 'get_permissions':
                        $ret = $this->process_capabilities_request();
                    break;
                    
                    case 'get_service_body_info':
                        $ret = $this->process_service_bodies_info_request();
                    break;
                    
                    case 'get_format_info':
                        $ret = $this->process_format_info();
                    break;
                    
                    case 'get_field_templates':
                        $ret = $this->process_get_field_templates();
                    break;
                    
                    case 'get_meetings':
                        $ret = $this->process_meeting_search();
                    break;
                    
                    case 'get_changes':
                        $ret = $this->process_changes();
                    break;
                    
                    case 'modify_meeting':
                        if ( intval ( $this->http_vars['meeting_id'] ) )    // Make sure that we are referring to a meeting.
                            {
                            $ret = $this->process_meeting_modify();
                            }
                    break;
                    
                    case 'add_meeting':
                        $this->http_vars['meeting_id'] = 0;
                        unset ( $this->http_vars['meeting_id'] );    // Make sure that we have no meeting ID. This forces an add.
                        $ret = $this->process_meeting_modify();
                    break;
                    
                    case 'delete_meeting':
                        $ret = $this->process_meeting_delete();
                    break;
                    
                    default:
                        $ret = '<h1>BAD ADMIN ACTION</h1>';
                    break;
                    }
                }
            else
                {
                $ret = '<h1>BAD ADMIN ACTION</h1>';
                }
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This is a basic funtion that tests the current user, to see if they are basically valid.
    
    \returns TRUE, if the user is valid.
    ************************************************************************************************************/
    function basic_user_validation()
    {
        $ret = FALSE;
        
        $user_obj = $this->server->GetCurrentUserObj();
        // First, make sure the use is of the correct general type.
        if ( isset ( $user_obj ) && ($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) && ($user_obj->GetUserLevel() != _USER_LEVEL_SERVER_ADMIN) && ($user_obj->GetID() > 1) )
            {
            $ret = TRUE;
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to get change records.
    
    \returns the XML for the change data.
    ************************************************************************************************************/
    function process_changes()
    {
        $ret = '';
        
        // First, make sure the use is of the correct general type.
        if ( $this->basic_user_validation() )
            {
            $start_date = '';
            $end_date = '';
            $meeting_id = '';
            $user_id = '';
            $service_body_id = '';
            
            $ret = $this->TranslateCSVToXML ( $this->get_changes_as_csv ( $start_date, $end_date, $meeting_id, $user_id, $service_body_id ) );
            $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<changes xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/GetChanges.php\">$ret</changes>";
            }
        
        return $ret;
    }

/*******************************************************************/
/**
	\brief	This returns change records in CSV form (which we turn into XML).
	
	\returns CSV data, with the first row a key header.
*/	
function get_changes_as_csv (	
                                $in_start_date = null,	///< Optional. A start date (In PHP time() format). If supplied, then only changes on, or after this date will be returned.
                                $in_end_date = null,	///< Optional. An end date (In PHP time() format). If supplied, then only changes that occurred on, or before this date will be returned.
                                $in_meeting_id = null,	///< Optional. If supplied, an ID for a particular meeting. Only changes for that meeting will be returned.
                                $in_user_id = null,	    ///< Optional. If supplied, an ID for a particular user. Only changes made by that user will be returned.
                                $in_sb_id = null	    ///< Optional. If supplied, an ID for a particular Service body. Only changes for that Service body will be returned.
                                )
	{
	$ret = null;
	
	try
		{
		$change_objects = c_comdef_server::GetChangesFromIDAndType ( 'c_comdef_meeting', null, $in_start_date, $in_end_date );
		if ( $change_objects instanceof c_comdef_changes )
			{
			$obj_array = $change_objects->GetChangesObjects();
			
			if ( is_array ( $obj_array ) && count ( $obj_array ) )
				{
	            set_time_limit ( max ( 30, intval ( count ( $obj_array ) / 20 ) ) ); // Change requests can take a loooong time...
				$localized_strings = c_comdef_server::GetLocalStrings();
                require_once ( dirname ( dirname ( dirname ( __FILE__ ) ) ).'/server/config/get-config.php');
				$ret = '"date_int","date_string","change_type","meeting_id","meeting_name","user_id","user_name","service_body_id","service_body_name","meeting_exists","details"'."\n";
                
                // If they specify a Service body, we also look in "child" Service bodies, so we need to produce a flat array of IDs.
                if ( isset ( $in_sb_id ) && $in_sb_id )
                    {
                    global $bmlt_array_gather;
                    
                    $bmlt_array_gather = array();
                    
                    /************************************************//**
                    * This little internal function will simply fill    *
                    * the $bmlt_array_gather array with a linear set of *
                    * Service body IDs that can be used for a quick     *
                    * comparison, later on. It is a callback function.  *
                    ****************************************************/
                    function bmlt_at_at ( $in_value,
                                          $in_key
                                        )
                        {
                        global $bmlt_array_gather;
                        
                        if ( $in_value instanceof c_comdef_service_body )
                            {
                            array_push ( $bmlt_array_gather, $in_value->GetID() );
                            }
                        }
                    
                    array_walk_recursive ( c_comdef_server::GetServer()->GetNestedServiceBodyArray ( $in_sb_id ), bmlt_at_at );
                    
                    if ( is_array ( $bmlt_array_gather ) && count ( $bmlt_array_gather ) )
                        {
                        $in_sb_id = $bmlt_array_gather;
                        }
                    else
                        {
                        $in_sb_id = array ( $in_sb_id );
                        }
                    }
                
				foreach ( $obj_array as $change )
					{
					$change_type = $change->GetChangeType();
					$date_int = intval($change->GetChangeDate());
					$date_string = date ($change_date_format, $date_int );
				    
				    if ( $change instanceof c_comdef_change )
				        {
                        $b_obj = $change->GetBeforeObject();
                        $a_obj = $change->GetAfterObject();
                        $meeting_id = intval ( $change->GetBeforeObjectID() );
                        $sb_a = intval ( ($a_obj instanceof c_comdef_meeting) ? $a_obj->GetServiceBodyID() : 0 );
                        $sb_b = intval ( ($b_obj instanceof c_comdef_meeting) ? $b_obj->GetServiceBodyID() : 0 );
                        $sb_c = intval ( $change->GetServiceBodyID() );
                        
                        if ( !$meeting_id )
                            {
                            $meeting_id = intval ( $change->GetAfterObjectID() );
                            }
                        
                        if ( (intval ( $in_meeting_id ) && intval ( $in_meeting_id ) == intval ( $meeting_id )) || !intval ( $in_meeting_id ) )
                            {
                            $meeting_name = '';
                            $user_name = '';
					        
					        if ( !is_array ( $in_sb_id ) || !count ( $in_sb_id ) || in_array ( $sb_a, $in_sb_id ) || in_array ( $sb_b, $in_sb_id ) || in_array ( $sb_c, $in_sb_id ) )
					            {
					            $sb_id = (intval ( $sb_c ) ? $sb_c : (intval ( $sb_b ) ? $sb_b : $sb_a));
					            
                                // Using str_replace, because preg_replace is pretty expensive. However, I don't think this buys us much.
                                if ( $b_obj instanceof c_comdef_meeting )
                                    {
                                    $meeting_name = str_replace ( '"', "'", str_replace ( "\n", " ", str_replace ( "\r", " ", $b_obj->GetMeetingDataValue ( 'meeting_name' ))) );
                                    }
                                elseif ( $a_obj instanceof c_comdef_meeting )
                                    {
                                    $meeting_name = str_replace ( '"', "'", str_replace ( "\n", " ", str_replace ( "\r", " ", $a_obj->GetMeetingDataValue ( 'meeting_name' ))) );
                                    }
                                
                                $user_id = intval ( $change->GetUserID() );
                                
                                $user = c_comdef_server::GetUserByIDObj ( $user_id );
                                
                                if ( $user instanceof c_comdef_user )
                                    {
                                    $user_name = htmlspecialchars ( $user->GetLocalName() );
                                    }
            
                                $sb = c_comdef_server::GetServiceBodyByIDObj ( $sb_id );
                                
                                if ( $sb instanceof c_comdef_service_body )
                                    {
                                    $sb_name = htmlspecialchars ( $sb->GetLocalName() );
                                    }
                                
                                $meeting_exists = 0;
                                
                                if ( c_comdef_server::GetOneMeeting ( $meeting_id, true ) )
                                    {
                                    $meeting_exists = 1;
                                    }
            
                                $details = '';
                                $desc = $change->DetailedChangeDescription();
                                
                                if ( $desc && isset ( $desc['details'] ) && is_array ( $desc['details'] ) )
                                    {
                                    // We need to prevent double-quotes, as they are the string delimiters, so we replace them with single-quotes.
                                    $details = str_replace ( '"', "'", str_replace ( "\n", " ", str_replace ( "\r", " ", implode ( " ", $desc['details'] ))) );
                                    }
                                
                                $change_line = array();
            
                                if ( $date_int )
                                    {
                                    $change_line['date_int'] = $date_int;
                                    }
                                else
                                    {
                                    $change_line['date_int'] = 0;
                                    }
                                
                                if ( $date_string )
                                    {
                                    $change_line['date_string'] = $date_string;
                                    }
                                else
                                    {
                                    $change_line['date_string'] = '';
                                    }
                                
                                if ( $change_type )
                                    {
                                    $change_line['change_type'] = $change_type;
                                    }
                                else
                                    {
                                    $change_line['change_type'] = '';
                                    }
                                
                                if ( $meeting_id )
                                    {
                                    $change_line['meeting_id'] = $meeting_id;
                                    }
                                else
                                    {
                                    $change_line['meeting_id'] = 0;
                                    }
                                
                                if ( $meeting_name )
                                    {
                                    $change_line['meeting_name'] = $meeting_name;
                                    }
                                else
                                    {
                                    $change_line['meeting_name'] = '';
                                    }
                                
                                if ( $user_id )
                                    {
                                    $change_line['user_id'] = $user_id;
                                    }
                                else
                                    {
                                    $change_line['user_id'] = 0;
                                    }
                                
                                if ( $user_name )
                                    {
                                    $change_line['user_name'] = $user_name;
                                    }
                                else
                                    {
                                    $change_line['user_name'] = '';
                                    }
                                
                                if ( $sb_id )
                                    {
                                    $change_line['service_body_id'] = $sb_id;
                                    }
                                else
                                    {
                                    $change_line['service_body_id'] = '';
                                    }
                                
                                if ( $sb_name )
                                    {
                                    $change_line['service_body_name'] = $sb_name;
                                    }
                                else
                                    {
                                    $change_line['service_body_name'] = '';
                                    }
                                
                                $change_line['meeting_exists'] = $meeting_exists;
                                
                                if ( $details )
                                    {
                                    $change_line['details'] = $details;
                                    }
                                else
                                    {
                                    $change_line['details'] = '';
                                    }
                                
                                $ret .= '"'.implode ( '","', $change_line ).'"'."\n";
                                }
                            }
                        }
					}
				}
			}
		}
	catch ( Exception $e )
		{
		}

	return $ret;
	}
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to get all the available fields for adding/modifying meeting data.
    
    \returns the XML for the template data.
    ************************************************************************************************************/
    function process_get_field_templates()
    {
        $ret = '';
        
        // First, make sure the use is of the correct general type.
        if ( $this->basic_user_validation() )
            {
            // Get the template data from the database.
            $template_data = c_comdef_meeting::GetDataTableTemplate();
            $template_longdata = c_comdef_meeting::GetLongDataTableTemplate();

            // We merge the two tables (data and longdata).
            if ( is_array ( $template_data ) && count ( $template_data ) && is_array ( $template_longdata ) && count ( $template_longdata ) )
                {
                $template_data = array_merge ( $template_data, $template_longdata );
                }
            
            // $template_data now contains the templates for the meeting data. These are the available "slots" for new values. We need to convert to XML.
            
            foreach ( $template_data as $template )
                {
                $ret .= '<field_template>';
                    $ret .= '<key>'.c_comdef_htmlspecialchars ( $template['key'] ).'</key>';
                    $ret .= '<field_prompt>'.c_comdef_htmlspecialchars ( $template['field_prompt'] ).'</field_prompt>';
                    $ret .= '<lang_enum>'.c_comdef_htmlspecialchars ( $template['lang_enum'] ).'</lang_enum>';
                    $ret .= '<visibility>'.intval ( $template['visibility'] ).'</visibility>';
                    
                    if ( isset ( $template['data_string'] ) )
                        {
                        $ret .= '<data_string>'.c_comdef_htmlspecialchars ( $template['data_string'] ).'</data_string>';
                        }
                        
                    if ( isset ( $template['data_bigint'] ) )
                        {
                        $ret .= '<data_bigint>'.intval ( $template['data_bigint'] ).'</data_bigint>';
                        }
                        
                    if ( isset ( $template['data_double'] ) )
                        {
                        $ret .= '<data_double>'.intval ( $template['data_double'] ).'</data_double>';
                        }
                        
                    if ( isset ( $template['data_longtext'] ) )
                        {
                        $ret .= '<data_longtext>'.c_comdef_htmlspecialchars ( $template['data_longtext'] ).'</data_longtext>';
                        }
                        
                    if ( isset ( $template['data_blob'] ) )
                        {
                        $ret .= '<data_blob>'.c_comdef_htmlspecialchars ( base64_encode ( $template['data_blob'] ) ).'</data_blob>';
                        }
                $ret .= '</field_template>';
                }
            
            $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<field_templates xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/FieldTemplates.php\">$ret</field_templates>";
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
            
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to delete an existing meeting. $this->http_vars['meeting_id'] must be set to the meeting ID.
    
    \returns A very simple, 1-line XML Report.
    ************************************************************************************************************/
    function process_meeting_delete()
    {
        $ret = '<h1>ERROR</h1>';
        
        // First, make sure the use is of the correct general type.
        if ( $this->basic_user_validation() )
            {
            $meeting_obj = $this->server->GetOneMeeting ( intval ( $this->http_vars['meeting_id'] ) );
            if ( $meeting_obj instanceof c_comdef_meeting ) // Make sure we got a meeting.
                {
                if ( $meeting_obj->UserCanEdit ( $this->server->GetCurrentUserObj() ) )  // Make sure that we are allowed to edit this meeting.
                    {
                    $id = $meeting_obj->GetID();
                    $name = $meeting_obj->GetLocalName();
                    
                    $meeting_obj->DeleteFromDB();   // Delete the meeting.
                    $ret = '<deleted_meeting id="'.intval ( $id ).'" name="'.c_comdef_htmlspecialchars ( $name ).'"/>';
                    }
                else
                    {
                    $ret = '<h1>NOT AUTHORIZED</h1>';
                    }
                }
            else
                {
                $ret = '<h1>ERROR</h1>';
                }
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to modify fields in a meeting (or create a new meeting).
           This requires that the following HTTP parameters be set:
                - meeting_id This is an integer that is the BMLT ID of the meeting being modified (the user must have edit rights to this meeting). If this is 0 (or unset), then we will be creating a new meeting.
                             If we are creating a new meeting, the new meeting will start at 8:30PM on Sunday, unless new values for the 'weekday' and 'start_time' fields are provided.
                             Once the meeting is created, we can set any of its fields as given.
                - meeting_field This is a string, or array of string, with the field name in the meeting search response.
                - new_value This is a string, or array of string, with the new value for the field. If the meeting_field parameter is an array, then each value here needs to be specified to correspond with the field.
    
    \returns XML, containing the fields modified.
    ************************************************************************************************************/
    function process_meeting_modify()
    {
        $ret = NULL;
        
        $user_obj = $this->server->GetCurrentUserObj();
        // First, make sure the use is of the correct general type.
        if ( $this->basic_user_validation() )
            {
            // Get the meeting object, itself.
            
            if ( !intval ( $this->http_vars['meeting_id'] ) )  // Will we be creating a new meeting?
                {
                $service_bodies = $this->server->GetServiceBodyArray();
                $my_editable_service_bodies = array();
                
                // We cycle through all the Service bodies, and look for ones in which we have permissions.
                // We use the Service body IDs to key them in associative arrays.
                foreach ( $service_bodies as $service_body )
                    {
                    if ( ($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) && $service_body->UserCanEditMeetings() ) // We are a full Service body editor, with rights to edit the Service body itself (as well as all its meetings).
                        {
                        $my_editable_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                        }
                    }
                
                if ( isset ( $my_editable_service_bodies ) && is_array ( $my_editable_service_bodies ) && count ( $my_editable_service_bodies ) )
                    {
                    $service_body_id = 0;
                    
                    // If we are allowed to edit more than one Service body, then we are given a choice.
                    if ( count ( $my_editable_service_bodies ) > 1 )
                        {
                        $service_body_id = intval ( $this->http_vars['service_body_id'] );
                        }
                    else    // Otherwise, it is picked for us.
                        {
                        $service_body_id = $my_editable_service_bodies[0]->GetID();
                        }
                        
                    $weekday = 1;
                    $start_time = strtotime ( '22:30:00' );
                    $lang = c_comdef_server::GetServer()->GetLocalLang ();
                
                    if ( $service_body_id )
                        {
                        $service_body = c_comdef_server::GetServer()->GetServiceBodyByIDObj ( $service_body_id );

                        if ( $service_body instanceof c_comdef_service_body )
                            {
                            if ( $service_body->UserCanEditMeetings ( $user_obj ) )
                                {
                                $meeting_obj = c_comdef_server::AddNewMeeting ( $service_body_id, $weekday, $start_time, $lang );
                                $ret = '<new_meeting id="'.intval ( $meeting_obj->GetID() ).'"/>';
                                }
                            else
                                {
                                $ret = '<h1>NOT AUTHORIZED</h1>';
                                }
                            }
                        else
                            {
                            $ret = '<h1>ERROR</h1>';
                            }
                        }
                    else
                        {
                        $ret = '<h1>ERROR</h1>';
                        }
                    }
                else
                    {
                    $ret = '<h1>NOT AUTHORIZED</h1>';
                    }
                }
            else
                {
                $meeting_obj = $this->server->GetOneMeeting ( intval ( $this->http_vars['meeting_id'] ) );
                $ret = '<change_meeting id="'.intval ( $meeting_obj->GetID() ).'"/>';
                }
            
            if ( $meeting_obj instanceof c_comdef_meeting )
                {
                if ( $meeting_obj->UserCanEdit ( $user_obj ) )    // We next make sure that we are allowed to make changes to this meeting.
                    {
                    $keys = c_comdef_meeting::GetAllMeetingKeys();  // Get all the available keys. The one passed in needs to match one of these.

                    if ( in_array ( $this->http_vars['meeting_field'], $keys ) )
                        {
                        // In case we need to add a new field, we get the meeting data template.
                        $template_data = c_comdef_meeting::GetDataTableTemplate();
                        $template_longdata = c_comdef_meeting::GetLongDataTableTemplate();
            
                        // We merge the two tables (data and longdata).
                        if ( is_array ( $template_data ) && count ( $template_data ) && is_array ( $template_longdata ) && count ( $template_longdata ) )
                            {
                            $template_data = array_merge ( $template_data, $template_longdata );
                            }
                    
                        // If so, we take the field, and tweak its value.
                        $data &= $meeting_obj->GetMeetingData ( );  // Get the data array by reference.
                        if ( isset ( $data ) && is_array ( $data ) && count ( $data ) )
                            {
                            $meeting_fields = $this->http_vars['meeting_field'];
                            $new_values = $this->http_vars['new_value'];
                            
                            if ( !is_array ( $meeting_fields ) )
                                {
                                $meeting_fields = array ( $meeting_fields );
                                }
                            
                            if ( !is_array ( $new_values ) )
                                {
                                $new_values = array ( $new_values );
                                }
                            
                            if ( count ( $meeting_fields ) == count ( $new_values ) )
                                {
                                $index = 0;
                            
                                // We change each of the fields passed in to the new values passed in.
                                foreach ( $meeting_fields as $meeting_field )
                                    {
                                    if ( ($meeting_field != 'id_bigint') && ($meeting_field != 'service_body_bigint') )    // We can't change the meeting ID or Service body (those should be done on the main interface).
                                        {
                                        $field &= $data[$meeting_field];    // We get the field structure as a reference, so we will actually change the data in place.
                                        $value = $new_values[$index];
                        
                                        if ( isset ( $field ) && is_array ( $field ) && count ( $field ) ) // If we already have the field loaded, then we simply set its value to our provided one.
                                            {
                                            $old_value = $field['value'];
                                            if ( $old_value != $value )
                                                {
                                                $field['value'] = $value;
                                                }
                                            }
                                        else    // Otherwise, we have a relatively complex job. We have to create a new data object.
                                            {
                                            $meeting_obj->AddDataField ( $meeting_field, $template_data[$meeting_field]['field_prompt'], $value, null, intval ( $template_data[$meeting_field]['visibility'] ) );
                                            }
                                        
                                        $ret .= '<field>'.c_comdef_htmlspecialchars ( $this->http_vars['meeting_field'] ).'<old_value>'.c_comdef_htmlspecialchars ( $old_value ).'</old_value><new_value>'.c_comdef_htmlspecialchars ( $value ).'</new_value></field>';
                                        }
                                    }
                                        
                                $meeting_obj->UpdateToDB(); // Save the new data. After this, the meeting has been changed.
                                $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<change_response xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/ChangeResponse.php\">$ret</change_response>";
                                }
                            else
                                {
                                $ret = '<h1>ERROR</h1>';
                                }
                            }
                        else
                            {
                            $ret = '<h1>ERROR</h1>';
                            }
                        }
                    else
                        {
                        $ret = '<h1>ERROR</h1>';
                        }
                    }
                else
                    {
                    $ret = '<h1>NOT AUTHORIZED</h1>';
                    }
                }
            else
                {
                $ret = '<h1>ERROR</h1>';
                }
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
            
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to return meeting information from a search.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_meeting_search()
    {
        if ( !( isset ( $this->http_vars['geo_width'] ) && $this->http_vars['geo_width'] ) && isset ( $this->http_vars['bmlt_search_type'] ) && ($this->http_vars['bmlt_search_type'] == 'advanced') && isset ( $this->http_vars['advanced_radius'] ) && isset ( $this->http_vars['advanced_mapmode'] ) && $this->http_vars['advanced_mapmode'] && ( floatval ( $this->http_vars['advanced_radius'] != 0.0 ) ) && isset ( $this->http_vars['lat_val'] ) &&	 isset ( $this->http_vars['long_val'] ) && ( (floatval ( $this->http_vars['lat_val'] ) != 0.0) || (floatval ( $this->http_vars['long_val'] ) != 0.0) ) )
            {
            $this->http_vars['geo_width'] = $this->http_vars['advanced_radius'];
            }
        elseif ( !( isset ( $this->http_vars['geo_width'] ) && $this->http_vars['geo_width'] ) && isset ( $this->http_vars['bmlt_search_type'] ) && ($this->http_vars['bmlt_search_type'] == 'advanced') )
            {
            $this->http_vars['lat_val'] = null;
            $this->http_vars['long_val'] = null;
            }
        elseif ( !isset ( $this->http_vars['geo_loc'] ) || $this->http_vars['geo_loc'] != 'yes' )
            {
            if ( !isset( $this->http_vars['geo_width'] ) )
                {
                $this->http_vars['geo_width'] = 0;
                }
            }

        require_once ( dirname ( dirname ( dirname ( __FILE__ ) ) ).'/client_interface/csv/search_results_csv.php' );
    
        $geocode_results = null;
        $ignore_me = null;
        $meeting_objects = array();
        $formats_ar = array ();
        $result2 = DisplaySearchResultsCSV ( $this->http_vars, $ignore_me, $geocode_results, $meeting_objects );

        if ( is_array ( $meeting_objects ) && count ( $meeting_objects ) && is_array ( $formats_ar ) )
            {
            foreach ( $meeting_objects as $one_meeting )
                {
                $formats = $one_meeting->GetMeetingDataValue ( 'formats' );

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
    
        if ( isset ( $this->http_vars['data_field_key'] ) && $this->http_vars['data_field_key'] )
            {
            // At this point, we have everything in a CSV. We separate out just the field we want.
            $temp_keyed_array = array();
            $result = explode ( "\n", $result );
            $keys = array_shift ( $result );
            $keys = explode ( "\",\"", trim ( $keys, '"' ) );
            $the_keys = explode ( ',', $this->http_vars['data_field_key'] );
        
            $result = array();
            foreach ( $result2 as $row )
                {
                if ( $row )
                    {
                    $index = 0;
                    $row = explode ( '","', trim ( $row, '",' ) );
                    $row_columns = array();
                    foreach ( $row as $column )
                        {
                        if ( !$column )
                            {
                            $column = ' ';
                            }
                        if ( in_array ( $keys[$index++], $the_keys ) )
                            {
                            array_push ( $row_columns, $column );
                            }
                        }
                    $result[$row[0]] = '"'.implode ( '","', $row_columns ).'"';
                    }
                }

            $the_keys = array_intersect ( $keys, $the_keys );
            $result2 = '"'.implode ( '","', $the_keys )."\"\n".implode ( "\n", $result );
            }
        
        
        $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<meetings xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/GetSearchResults.php\">";
        $result .= $this->TranslateCSVToXML ( $result2 );
        if ( (isset ( $http_vars['get_used_formats'] ) || isset ( $http_vars['get_formats_only'] )) && $formats_ar && is_array ( $formats_ar ) && count ( $formats_ar ) )
            {
            if ( isset ( $http_vars['get_formats_only'] ) )
                {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<formats xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/GetFormats.php\">";
                }
            else
                {
                $result .= "<formats>";
                }
            $result3 = GetFormats ( $server, $langs, $formats_ar );
            $result .= TranslateToXML ( $result3 );
        
            $result .= "</formats>";
            }
    
        $result .= isset ( $http_vars['get_formats_only'] ) ? "" : "</meetings>";
    
        return $result;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to return format information.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_format_info()
    {
        $ret = '';
        
        if ( $this->basic_user_validation() )
            {
            $format_ids = array();
            
            // See if we are receiving a request for just specific formats, or if we are being asked for all of them.
            if ( isset ( $this->http_vars['format_id'] ) && intval ( trim ( $this->http_vars['format_id'] ) ) )
                {
                $format_ids[] = intval ( trim ( $this->http_vars['format_id'] ) );
                }
            elseif ( isset ( $this->http_vars['format_id'] ) && is_array ( $this->http_vars['format_id'] ) && count ( $this->http_vars['format_id'] ) )
                {
                foreach ( $this->http_vars['format_id'] as $format )
                    {
                    $format_ids[] = intval ( trim ( $format ) );
                    }
                }
            else
                {
                $format_ids = NULL;
                }
            
            $lang = $this->server->GetLocalLang();
            if ( isset ( $this->http_vars['lang'] ) && trim ( $this->http_vars['lang'] ) )
                {
                $lang = strtolower ( trim ( $this->http_vars['lang'] ) );
                }
            
            $returned_formats = array();    // We will be returning our formats in this.
            $format_objects = $this->server->GetFormatsObj()->GetFormatsByLanguage ( $lang ); // Get all the formats (not just the ones used, but ALL of them).
            
            // Filter for requested formats in the requested language.
            foreach ( $format_objects as $format )
                {
                if ( !$format_ids || in_array ( intval ( $format->GetSharedID() ), $format_ids ) )
                    {
                    $returned_formats[] = $format;
                    }
                }
            
            // At this point, we have a complete array of just the format[s] that will be returned. Time to make some XML...
            $index = 0;
            foreach ( $returned_formats as $format )
                {
                $ret .= '<row sequence_index="'.strval ( $index++ ).'">';
                    $ret.= '<key_string>'.c_comdef_htmlspecialchars ( $format->GetKey() ).'</key_string>';
                    $ret.= '<name_string>'.c_comdef_htmlspecialchars ( $format->GetLocalName() ).'</name_string>';
                    $ret.= '<description_string>'.c_comdef_htmlspecialchars ( $format->GetLocalDescription() ).'</description_string>';
                    $ret.= '<lang>'.c_comdef_htmlspecialchars ( $lang ).'</lang>';
                    $ret.= '<id>'.intval ( $format->GetSharedID() ).'</id>';
                    $world_id = trim ( $format->GetWorldID() );
                    if ( isset ( $world_id ) && $world_id )
                        {
                        $ret.= '<world_id>'.c_comdef_htmlspecialchars ( $world_id ).'</world_id>';
                        }
                $ret .= '</row>';
                }
            $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<formats xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/GetFormats.php\">$ret</formats>";
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
            
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to return Service Body information for multiple Service bodies.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_service_bodies_info_request()
    {
        $ret = '';
        
        if ( $this->basic_user_validation() )
            {
            $service_body_ids = array();
            
            // Look to see if the caller is asking for particular Service bodies.
            if ( isset ( $this->http_vars['sb_id'] ) && $this->http_vars['sb_id'] )
                {
                if ( !is_array ( $this->http_vars['sb_id'] ) )
                    {
                    $service_body_ids[] = intval ( trim ( $this->http_vars['sb_id'] ) );
                    }
                else
                    {
                    foreach ( $this->http_vars['sb_id'] as $id )
                        {
                        if ( intval ( trim ( $id ) ) )
                            {
                            $service_body_ids[] = intval ( trim ( $id ) );
                            }
                        }
                    }
                }
            
            // If we have a request for individual Service bodies, then we just return those ones.
            if ( isset ( $service_body_ids ) && is_array ( $service_body_ids ) && count ( $service_body_ids ) )
                {
                foreach ( $service_body_ids as $id )
                    {
                    $ret .= $this->process_service_body_info_request ( $id );
                    }
                }
            else    // If they are not looking for particular bodies, then we return the whole kit & kaboodle.
                {
                $service_bodies = $this->server->GetServiceBodyArray();
            
                foreach ( $service_bodies as $service_body )
                    {
                    if ( isset ( $this->http_vars['flat'] ) || !$service_body->GetOwnerIDObject() )    // We automatically include top-level Service bodies here, and let ones with parents sort themselves out.
                        {
                        $ret .= $this->process_service_body_info_request ( $service_body->GetID() );
                        }
                    }
                }
        
            $ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<service_bodies xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/HierServiceBodies.php\">$ret</service_bodies>";
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
            
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to return Service Body information.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_service_body_info_request (    $in_service_body_id ///< The ID of the Service body being requested.
                                                )
    {
        $ret = '';
        // Belt and suspenders. We need to make sure the user is authorized.
        if ( $this->basic_user_validation() )
            {
            if ( !in_array ( $in_service_body_id, $this->handled_service_body_ids ) )
                {
                $this->handled_service_body_ids[] = $in_service_body_id;
                $service_body = $this->server->GetServiceBodyByIDObj ( $in_service_body_id );
            
                if ( isset ( $service_body ) && ($service_body instanceof c_comdef_service_body) )
                    {
                    // Everyone gets the type, URI, name, description and parent Service body.
                    $name = $service_body->GetLocalName();
                    $description = $service_body->GetLocalDescription();
                    $uri = $service_body->GetURI();
                    $type = $service_body->GetSBType();

                    $parent_service_body_id = 0;
                    $parent_service_body_name = "";
                    $parent_service_body_type = "";

                    $parent_service_body = $service_body->GetOwnerIDObject();
                
                    if ( isset ( $parent_service_body ) && $parent_service_body )
                        {
                        $parent_service_body_id = intval ( $parent_service_body->GetID() );
                        $parent_service_body_name = $parent_service_body->GetLocalName();
                        $parent_service_body_type = $parent_service_body->GetSBType();
                        }
                
                    $principal_user = $service_body->GetPrincipalUserObj();
                    $principal_user_id = intval ( $principal_user->GetID() );
                
                    // Scan for our various editors.
                    $guest_editors = $this->server->GetUsersByLevelObj ( _USER_LEVEL_OBSERVER, TRUE );  // Observer or greater.
                    $service_body_editors = array();
                    $meeting_list_editors = array();
                    $observers = array();
                
                    foreach ( $guest_editors as $editor )
                        {
                        if ( $service_body->UserCanEdit ( $editor ) )   // We will have at least one of these, as the principal user needs to be listed.
                            {
                            array_push ( $service_body_editors, $editor );
                            }
                        elseif ( $service_body->UserCanEditMeetings ( $editor ) )
                            {
                            array_push ( $meeting_list_editors, $editor );
                            }
                        elseif ( $service_body->UserCanObserve ( $editor ) )
                            {
                            array_push ( $observers, $editor );
                            }
                        }
                
                    // Scan for direct descendant child Service bodies.
                    $children = array();
                    $service_bodies = $this->server->GetServiceBodyArray();
                
                    foreach ( $service_bodies as $child )
                        {
                        if ( $child->IsOwnedBy ( $in_service_body_id, TRUE ) )
                            {
                            $children[] = $child;
                            }
                        }
                
                    // We check to see which editors are mentioned in this Service body.
                    $guest_editors = $service_body->GetEditors();
                
                    // See if we have rights to edit this Service body. Just for the heck of it, we check the user level (not really necessary, but belt and suspenders).
                    $this_user_can_edit_the_body = ($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) && $service_body->UserCanEdit();
                
                    $contact_email = NULL;
                
                    // Service Body Admins (with permission for the body) get more info.
                    if ( $this_user_can_edit_the_body )
                        {
                        $contact_email = $service_body->GetContactEmail();
                        }
                    
                    // At this point, we have all the information we need to build the response XML.
                    $ret = '<service_body id="'.c_comdef_htmlspecialchars ( $in_service_body_id ).'" name="'.c_comdef_htmlspecialchars ( $name ).'" type="'.c_comdef_htmlspecialchars ( $type ).'">';
                        $ret .= '<service_body_type>'.c_comdef_htmlspecialchars ( $this->my_localized_strings['service_body_types'][$type] ).'</service_body_type>';
                        if ( isset ( $description ) && $description )
                            {
                            $ret .= '<description>'.c_comdef_htmlspecialchars ( $description ).'</description>';
                            }
                        if ( isset ( $uri ) && $uri )
                            {
                            $ret .= '<uri>'.c_comdef_htmlspecialchars ( $uri ).'</uri>';
                            }
                        if ( isset ( $parent_service_body ) && $parent_service_body )
                            {
                            $ret .= '<parent_service_body id="'.intval ( $parent_service_body_id ).'" type="'.c_comdef_htmlspecialchars ( $parent_service_body_type ).'">'.c_comdef_htmlspecialchars ( $parent_service_body_name ).'</parent_service_body>';
                            }
                        if ( $this_user_can_edit_the_body && isset ( $contact_email ) && $contact_email )
                            {
                            $ret .= '<contact_email>'.c_comdef_htmlspecialchars ( $contact_email ).'</contact_email>';
                            }
                    
                        $ret .= '<editors>';
                            if ( isset ( $service_body_editors ) && is_array ( $service_body_editors ) && count ( $service_body_editors ) )
                                {
                                // We will have at least one of these (the principal user).
                                // These are the users that can directly manipulate the Service body.
                                $ret .= '<service_body_editors>';
                                    foreach ( $service_body_editors as $editor )
                                        {
                                        $editor_id = intval ( $editor->GetID() );
                                        $ret .= '<editor id="'.$editor_id.'" type="'.( in_array ( $editor_id, $guest_editors ) ? 'direct' : (( $editor_id == $principal_user_id ) ? 'principal' : 'inherit')).'">'.c_comdef_htmlspecialchars ( $editor->GetLocalName() ).'</editor>';
                                        }
                                $ret .= '</service_body_editors>';
                                }
                        
                            // These are users that can't manipulate the Service body, but can edit meetings.
                            if ( isset ( $meeting_list_editors ) && is_array ( $meeting_list_editors ) && count ( $meeting_list_editors ) )
                                {
                                $ret .= '<meeting_list_editors>';
                                    foreach ( $meeting_list_editors as $editor )
                                        {
                                        $editor_id = intval ( $editor->GetID() );
                                        $ret .= '<editor id="'.$editor_id.'" type="'.( in_array ( $editor_id, $guest_editors ) ? 'direct' : (( $editor_id == $principal_user_id ) ? 'principal' : 'inherit' )).'">'.c_comdef_htmlspecialchars ( $editor->GetLocalName() ).'</editor>';
                                        }
                                $ret .= '</meeting_list_editors>';
                                }
                        
                            // These are users that can only see hidden fields in meetings.
                            if ( isset ( $observers ) && is_array ( $observers ) && count ( $observers ) )
                                {
                                $ret .= '<observers>';
                                    foreach ( $observers as $editor )
                                        {
                                        $editor_id = intval ( $editor->GetID() );
                                        $ret .= '<editor id="'.$editor_id.'" type="'.( in_array ( $editor_id, $guest_editors ) ? 'direct' : (( $editor_id == $principal_user_id ) ? 'principal' : 'inherit' )).'">'.c_comdef_htmlspecialchars ( $editor->GetLocalName() ).'</editor>';
                                        }
                                $ret .= '</observers>';
                                }
                        $ret .= '</editors>';
                
                        // If this is a hierarchical response, we embed the children as XML service_body elements. Otherwise, we list them as simple catalog elements.
                        if ( !isset ( $this->http_vars['flat'] ) && isset ( $children ) && is_array ( $children ) && count ( $children ) )
                            {
                            $ret .= "<service_bodies>";
                                foreach ( $children as $child )
                                    {
                                    $ret .= $this->process_service_body_info_request ( $child->GetID() );
                                    }
                            $ret .= "</service_bodies>";
                            }
                        elseif ( isset ( $children ) && is_array ( $children ) && count ( $children ) )
                            {
                            $ret .= '<children>';
                                foreach ( $children as $child )
                                    {
                                    $ret .= '<child_service_body id="'.intval ( $child->GetID() ).'" type="'.c_comdef_htmlspecialchars ( $child->GetSBType() ).'">'.c_comdef_htmlspecialchars ( $child->GetLocalName() ).'</child_service_body>';
                                    }
                            $ret .= '</children>';
                            }

                    $ret .= '</service_body>';
                    }
                }
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
            
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This fulfills a user request to report the rights for the logged-in user.
    
    \returns XML, containing the answer.
    ************************************************************************************************************/
    function process_capabilities_request()
    {
        $ret = '';
        $service_bodies = $this->server->GetServiceBodyArray();
        
        // We will fill these three arrays, depending on the users' rights for a given Service body.
        $my_meeting_observer_service_bodies = array();
        $my_meeting_editor_service_bodies = array();
        $my_editable_service_bodies = array();
        
        $user_obj = $this->server->GetCurrentUserObj();
        if ( $this->basic_user_validation() )
            {
            // We cycle through all the Service bodies, and look for ones in which we have permissions.
            // We use the Service body IDs to key them in associative arrays.
            foreach ( $service_bodies as $service_body )
                {
                if ( ($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) && $service_body->UserCanEdit() ) // We are a full Service body editor, with rights to edit the Service body itself (as well as all its meetings).
                    {
                    $my_editable_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                    }
                // Again, we keep checking credentials, over and over again.
                elseif ( (($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) || ($user_obj->GetUserLevel() == _USER_LEVEL_OBSERVER)) && $service_body->UserCanEditMeetings() ) // We are a "guest" editor, or an observer (depends on our user level).
                    {
                    if ( $user_obj->GetUserLevel() == _USER_LEVEL_OBSERVER )
                        {
                        $my_meeting_observer_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                        }
                    else
                        {
                        $my_meeting_editor_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                        }
                    }
                }
            // Now, we grant rights to Service bodies that are implicit from other rights (for example, a Service Body Admin can also observe and edit meetings).
            
            // A full Service Body Admin can edit meetings in that Service body.
            foreach ( $my_editable_service_bodies as $service_body )
                {
                $my_meeting_editor_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                }
            
            // An editor (whether an admin or a "guest") also has observe rights.
            foreach ( $my_meeting_editor_service_bodies as $service_body )
                {
                $my_meeting_observer_service_bodies['sb_'.$service_body->GetID()] = $service_body;
                }
            
            // At this point, we have 3 arrays (or fewer), filled with Service bodies that we have rights on. It is entirely possible that only one of them could be filled, and it may only have one member.
            
            // We start to construct the XML filler.
            foreach ( $service_bodies as $service_body )
                {
                // If we can observe, then we have at least one permission for this Service body.
                if ( isset ( $my_meeting_observer_service_bodies['sb_'.$service_body->GetID()] ) && $my_meeting_observer_service_bodies['sb_'.$service_body->GetID()] )
                    {
                    $ret .= '<service_body id="'.$service_body->GetID().'" name="'.c_comdef_htmlspecialchars ( $service_body->GetLocalName() ).'">';
                        $ret .= '<permission level="observer" />';
                        
                        if ( isset ( $my_meeting_editor_service_bodies['sb_'.$service_body->GetID()] ) && $my_meeting_editor_service_bodies['sb_'.$service_body->GetID()] )
                            {
                            $ret .= '<permission level="meeting_editor" />';
                            }
                        
                        if ( isset ( $my_editable_service_bodies['sb_'.$service_body->GetID()] ) && $my_editable_service_bodies['sb_'.$service_body->GetID()] )
                            {
                            $ret .= '<permission level="service_body_editor" />';
                            }
                    $ret .= '</service_body>';
                    }
                }
            // Create a proper XML wrapper for the response data.
			$ret = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<permissions xmlns=\"http://".c_comdef_htmlspecialchars ( $_SERVER['SERVER_NAME'] )."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"".GetURLToMainServerDirectory ( FALSE )."client_interface/xsd/AdminPermissions.php\">$ret</permissions>";
            // We now have XML that states the current user's permission levels in all Service bodies.
            }
        else
            {
            $ret = '<h1>NOT AUTHORIZED</h1>';
            }
        
        return $ret;
        }

    /*******************************************************************/
    /**
        \brief Translates CSV to XML.
    
        \returns an XML string, with all the data in the CSV.
    */	
    function TranslateCSVToXML (	$in_csv_data	///< An array of CSV data, with the first element being the field names.
                                )
        {
        require_once ( dirname ( dirname ( dirname ( __FILE__ ) ) ).'/server/shared/Array2XML.php');
        $temp_keyed_array = array();
        $in_csv_data = explode ( "\n", $in_csv_data );
        $keys = array_shift ( $in_csv_data );
        $keys = rtrim ( ltrim ( $keys, '"' ), '",' );
        $keys = preg_split ( '/","/', $keys );
    
        foreach ( $in_csv_data as $row )
            {
            if ( $row )
                {
                $line = null;
                $index = 0;
                $row_t = rtrim ( ltrim ( $row, '"' ), '",' );
                $row_t = preg_split ( '/","/', $row_t );
                foreach ( $row_t as $column )
                    {
                    if ( isset ( $column ) )
                        {
                        $line[$keys[$index++]] = trim ( $column );
                        }
                    }
                array_push ( $temp_keyed_array, $line );
                }
            }

        $out_xml_data = array2xml ( $temp_keyed_array, 'not_used', false );

        return $out_xml_data;
        }
};
?>