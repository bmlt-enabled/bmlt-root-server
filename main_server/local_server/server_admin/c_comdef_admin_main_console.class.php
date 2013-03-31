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
require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2Json.php');

/************************************************************************************************************//**
    \class c_comdef_admin_main_console
    \brief Controls display of the main BMLT administration console.
****************************************************************************************************************/
class c_comdef_admin_main_console
{
    var $my_localized_strings;          ///< This will contain the localized strings and whatnot for display.
    var $my_server;                     ///< This hold the server object.
    var $my_user;                       ///< This holds the instance of the logged-in user.
    var $my_ajax_uri;                   ///< This will be the URI for AJAX calls.
    var $my_http_vars;                  ///< Contains the HTTP vars sent in.
    var $my_service_bodies;             ///< This will be an array that contains all the Service bodies this user can edit.
    var $my_users;                      ///< This will be an array of all the user objects.
    var $my_formats;                    ///< The format objects that are available for meetings.
    var $my_data_field_templates;       ///< This holds the keys for all the possible data fields for this server.
    var $my_editable_service_bodies;    ///< This will contain all the Service bodies that we can actually directly edit.
    var $my_all_service_bodies;         ///< This contains all Service bodies, cleaned for orphans.
    var $my_lang_ids;                   ///< Contains the enumerations for all the server langs.
    
    /********************************************************************************************************//**
    \brief
    ************************************************************************************************************/
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
            die ( 'NOT AUTHORIZED' );
            }

        $this->my_users = array_values ( $this->my_server->GetServerUsersObj()->GetUsersArray() );
        $this->my_ajax_uri = $_SERVER['PHP_SELF'].'?bmlt_ajax_callback=1';
        
        $this->my_formats = array();
        $langs = $this->my_server->GetServerLangs();
        $this->my_lang_ids = array_keys ( $langs );
        $server_format_array = $this->my_server->GetFormatsArray();
        $format_ids = array();
        
        // We will build an array of formats in the structure we'll need for our editor. We start by gathering all of the shared IDs.
        foreach ( $langs as $lang_key => $lang_name )
            {
            $the_format_object_array = $server_format_array[$lang_key];
            foreach ( $the_format_object_array as $format )
                {
                $format_ids[] = $format->GetSharedID();
                }
            }
            
        $format_ids = array_unique ( $format_ids, SORT_NUMERIC );
            
        // OK, we have a sorted array of unique format IDs. Now, we assign each one an array of format data per language.
        
        foreach ( $format_ids as $id )
            {
            $single_format = array();
            // Walk through the server languages...
            foreach ( $langs as $lang_key => $lang_name )
                {
                // Then through all the formats with data in each language...
                $the_format_object_array = $server_format_array[$lang_key];
                foreach ( $the_format_object_array as $format )
                    {
                    // If the format is available with data in this language, we add it to our ID.
                    if ( $format->GetSharedID() == $id )
                        {
                        $single_format[$lang_key]['shared_id'] = $id;
                        $single_format[$lang_key]['lang_key'] = $lang_key;
                        $single_format[$lang_key]['lang_name'] = $lang_name;
                        $single_format[$lang_key]['key'] = $format->GetKey();
                        $single_format[$lang_key]['name'] = $format->GetLocalName();
                        $single_format[$lang_key]['description'] = $format->GetLocalDescription();
                        $single_format[$lang_key]['type'] = $format->GetFormatType();
                        }
                    }
                }
            
            $this->my_formats[] = array ( 'id' => $id, 'formats' => $single_format );
            }
            
        $service_bodies = $this->my_server->GetServiceBodyArray();
        $this->my_service_bodies = array();
        $this->my_editable_service_bodies = array();
        $this->my_all_service_bodies = array();
        
        for ( $c = 0; $c < count ( $service_bodies ); $c++ )
            {
            $service_body = $service_bodies[$c];
            if ( $service_body->UserCanEditMeetings() )
                {
                array_push ( $this->my_service_bodies, $service_body );
                }
            
            if ( $service_body->UserCanEdit() )
                {
                array_push ( $this->my_editable_service_bodies, $service_body );
                }
            
            array_push ( $this->my_all_service_bodies, $service_body );
            }
        
        // We get all the available data fields, and create a local data member for their keys.
		$this->my_data_field_templates = c_comdef_meeting::GetDataTableTemplate();
		$longdata_obj = c_comdef_meeting::GetLongDataTableTemplate();
		
		// We merge the two tables (data and longdata).
		if ( is_array ( $this->my_data_field_templates ) && count ( $this->my_data_field_templates ) && is_array ( $longdata_obj ) && count ( $longdata_obj ) )
			{
			$this->my_data_field_templates = array_merge ( $this->my_data_field_templates, $longdata_obj );
			}
		
		// Sort them by their field keys, so we have a consistent order.
		ksort ( $this->my_data_field_templates, (SORT_NATURAL | SORT_FLAG_CASE) );
    }
    
    /********************************************************************************************************//**
    \brief
    \returns
    ************************************************************************************************************/
    function return_main_console_html()
    {
        $ret = '<div id="bmlt_admin_main_console" class="bmlt_admin_main_console_wrapper_div">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            // We actually include the JS directly into the HTML. This gives us a lot more flexibility as to how we deploy and gatekeep this file.
            $ret .= '<script type="text/javascript" src="'.self::js_html ( 'http://maps.google.com/maps/api/js?sensor=false' ).'"></script>';
            $ret .= '<script type="text/javascript" src="'.self::js_html ( 'http://maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry' ).'"></script>';       
            $ret .= '<script type="text/javascript">';
                $ret .= 'var g_ajax_callback_uri = \''.self::js_html ( $this->my_ajax_uri ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_current_user_id = \''.self::js_html ( $this->my_user->GetID() ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_formats_array = '.array2json ( $this->my_formats ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_langs = ["'.implode ( '","', $this->my_lang_ids ).'"];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_lang_names = '.array2json ( $this->my_server->GetServerLangs() ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_AJAX_Auth_Failure = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['AJAX_Auth_Failure'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_bodies_array = [';
                    for ( $c = 0; $c < count ( $this->my_service_bodies ); $c++ )
                        {
                        $service_body = $this->my_service_bodies[$c];
                        $ret .= '[';
            /* ID:0 */  $ret .= $service_body->GetID().',';
      /* Owner ID:1 */  $ret .= $service_body->GetOwnerID().',';
          /* Name:2 */  $ret .= '\''.self::js_html ( $service_body->GetLocalName() ).'\',';
   /* Description:3 */  $ret .= '\''.self::js_html ( $service_body->GetLocalDescription() ).'\',';
  /* Main User ID:4 */  $ret .= $service_body->GetPrincipalUserID().',';
    /* Editor IDs:5 */  $ret .= '\''.implode ( ',', $service_body->GetEditors() ).'\',';
 /* Contact Email:6 */  $ret .= '\''.self::js_html ( $service_body->GetContactEmail() ).'\',';
           /* URI:7 */  $ret .= '\''.self::js_html ( $service_body->GetURI() ).'\',';
       /* KML URI:8 */  $ret .= '\''.self::js_html ( $service_body->GetKMLURI() ).'\',';
       /* SB Type:9 */  $ret .= '\''.$service_body->GetSBType().'\',';
/* User Can Edit:10 */  $ret .= ($service_body->UserCanEdit() ? 'true' : 'false').',';
/* Edit Meetings:11 */  $ret .= ($service_body->UserCanEditMeetings() ? 'true' : 'false').',';
     /* World ID:12 */  $ret .= '\''.self::js_html ( $service_body->GetWorldID() ).'\'';
                        $ret .=']';
                        if ( $c < (count ( $this->my_service_bodies ) - 1) )
                            {
                            $ret .= ',';
                            }
                        }
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_editable_service_bodies_array = [';
                    for ( $c = 0; $c < count ( $this->my_editable_service_bodies ); $c++ )
                        {
                        $service_body = $this->my_editable_service_bodies[$c];
                        $ret .= '[';
            /* ID:0 */  $ret .= $service_body->GetID().',';
      /* Owner ID:1 */  $ret .= $service_body->GetOwnerID().',';
          /* Name:2 */  $ret .= '\''.self::js_html ( $service_body->GetLocalName() ).'\',';
   /* Description:3 */  $ret .= '\''.self::js_html ( $service_body->GetLocalDescription() ).'\',';
  /* Main User ID:4 */  $ret .= $service_body->GetPrincipalUserID().',';
    /* Editor IDs:5 */  $ret .= '\''.implode ( ',', $service_body->GetEditors() ).'\',';
 /* Contact Email:6 */  $ret .= '\''.self::js_html ( $service_body->GetContactEmail() ).'\',';
           /* URI:7 */  $ret .= '\''.self::js_html ( $service_body->GetURI() ).'\',';
       /* KML URI:8 */  $ret .= '\''.self::js_html ( $service_body->GetKMLURI() ).'\',';
       /* SB Type:9 */  $ret .= '\''.$service_body->GetSBType().'\',';
/* User Can Edit:10 */  $ret .= 'true,';
/* Edit Meetings:11 */  $ret .= 'true,';
     /* World ID:12 */  $ret .= '\''.self::js_html ( $service_body->GetWorldID() ).'\'';
                        $ret .=']';
                        if ( $c < (count ( $this->my_service_bodies ) - 1) )
                            {
                            $ret .= ',';
                            }
                        }
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_users = [';
                for ( $c = 0; $c < count ( $this->my_users ); $c++ )
                    {
                    $user = $this->my_users[$c];
                    $ret .= '[';
            /* ID:0 */  $ret .= $user->GetID().',';
         /* Login:1 */  $ret .= '\''.self::js_html ( ( ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) || ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) ) ? $user->GetLogin() : '' ).'\',';
          /* Name:2 */  $ret .= '\''.self::js_html ( $user->GetLocalName() ).'\',';
   /* Description:3 */  $ret .= '\''.self::js_html ( $user->GetLocalDescription() ).'\',';
         /* eMail:4 */  $ret .= '\''.self::js_html ( ( ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) || ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) || ($user->GetID() == $this->my_user->GetID()) ) ? $user->GetEmailAddress() : '' ).'\',';
    /* User Level:5 */  $ret .= $user->GetUserLevel().',';
     /*  Password:6 */  $ret .= '\'\''; // We do not give a password, but one can be sent in to change the current one, so we have a placeholder.
                    $ret .=']';
                    if ( $c < (count ( $this->my_users ) - 1) )
                        {
                        $ret .= ',';
                        }
                    }
                
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_levels = [';
                    $ret .= '[1,\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_1'] ).'\'],';
                    $ret .= '[2,\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_2'] ).'\'],';
                    $ret .= '[3,\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_3'] ).'\'],';
                    $ret .= '[4,\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_4'] ).'\'],';
                    $ret .= '[5,\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_5'] ).'\']';
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_weekday_name_array = [';
                    for ( $c = 1; $c < 8; $c++ )
                        {
                        $ret .= '\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_names'][$c] ).'\'';
                        if ( $c < 8 )
                            {
                            $ret .= ',';
                            }
                        }
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_object_array = [';
                    $first = true;
                    foreach ( $this->my_formats as $formats )
                        {
                        $format = $formats['formats'][$this->my_server->GetLocalLang()];
                        if ( $format )
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
                                $ret .= '"id":'.$format['shared_id'];
                                $ret .= ',"key":"'.$format['key'].'"';
                                $ret .= ',"name":"'.$format['name'].'"';
                                $ret .= ',"description":"'.$format['description'].'"';
                            $ret .= '}';
                            }
                        }
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_other_field_ids = [';
                    $first = true;
                    foreach ( $this->my_data_field_templates as $data_field )
                        {
                        $key = $data_field['key'];
                        switch ( $key )
                            {
                            case    'id_bigint':                // All of these are ignored, as they are taken care of in other option sheets.
                            case    'worldid_mixed':
                            case    'shared_group_id_bigint':
                            case    'service_body_bigint':
                            case    'weekday_tinyint':
                            case    'start_time':
                            case    'formats':
                            case    'lang_enum':
                            case    'longitude':
                            case    'latitude':
                            case    'email_contact':
                            case    'meeting_name':
                            case    'location_text':
                            case    'location_info':
                            case    'location_street':
                            case    'location_neighborhood':
                            case    'location_city_subsection':
                            case    'location_municipality':
                            case    'location_sub_province':
                            case    'location_province':
                            case    'location_postal_code_1':
                            case    'location_nation':
                            break;
                
                            default:    // We display these ones.
                                if ( !$first )
                                    {
                                    $ret .= ',';
                                    }
                                else
                                    {
                                    $first = false;
                                    }
                                $ret .= "'".self::js_html ( $key )."'";
                            break;
                            }
                        }
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_logout_uri = \''.self::js_html ( $_SERVER['PHP_SELF'].'?admin_action=logout' ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_closure_confirm_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_cancel_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_longitude = '.floatval ( $this->my_localized_strings['search_spec_map_center']['longitude'] ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_latitude = '.floatval ( $this->my_localized_strings['search_spec_map_center']['latitude'] ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_zoom = '.floatval ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_default_zoom'] ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_lookup_failed = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_lookup_failed'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_region_bias = \''.self::js_html ( $this->my_localized_strings['region_bias'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_style_dir = \''.self::js_html ( dirname ( $_SERVER['PHP_SELF'] ).'/local_server/server_admin/style' ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_Create_new_meeting_button_name = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_create_button_name'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_Save_meeting_button_name = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_save_buttonName'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_meeting_weekday = '.intVal ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_default_weekday'] ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_meeting_start_time = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_default_start_time'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_meeting_duration = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_default_duration'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_no_search_results_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_no_results_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_new_meeting_header_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_create_new_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_lookup_failed_not_enough_address_info = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_lookup_failed_not_enough_address_info'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_editor_result_count_format = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_result_count_format'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_editor_screen_delete_button_confirm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_delete_button_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_editor_screen_delete_button_confirm_perm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_delete_button_confirm_perm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_editor_already_editing_confirm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_already_editing_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_history_header_format = \''.str_replace ( '>', '&gt;', str_replace ( '<', '&lt;', $this->my_localized_strings['comdef_server_admin_strings']['history_header_format'] ) ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_history_no_history_available_text = \''.str_replace ( '>', '&gt;', str_replace ( '<', '&lt;', $this->my_localized_strings['comdef_server_admin_strings']['history_no_history_available_text'] ) ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_name_default_prompt_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_name_default_prompt_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_description_default_prompt_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_description_default_prompt_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_email_default_prompt_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_email_default_prompt_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_uri_default_prompt_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_uri_default_prompt_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_world_cc_default_prompt_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_world_cc_prompt'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_dirty_confirm_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_dirty_confirm_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_delete_button_confirm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_delete_button_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_delete_button_confirm_perm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_delete_button_confirm_perm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_save_button = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_save_button'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_body_create_button = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_create_button'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_save_button = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_save_button'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_create_button = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_create_button'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_password_default_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_password_default_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_new_password_default_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_new_password_default_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_password_label = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_password_label'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_new_password_label = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_new_password_label'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_dirty_confirm_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_dirty_confirm_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_delete_button_confirm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_delete_button_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_delete_button_confirm_perm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_delete_button_confirm_perm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_user_create_password_alert_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['user_create_password_alert_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_name_default_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_name_default_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_description_default_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_description_default_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_create_format_button_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_create_format_button_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_cancel_create_format_button_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_cancel_create_format_button_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_create_this_format_button_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_create_this_format_button_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_change_format_button_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_change_format_button_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_delete_format_button_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_delete_format_button_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_reset_format_button_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_reset_format_button_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_need_refresh_message_alert_text = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['need_refresh_message_alert_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_delete_button_confirm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_delete_button_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_editor_delete_button_confirm_perm = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_delete_button_confirm_perm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_throbber_image_loc = \'local_server/server_admin/style/images/ajax-throbber-white.gif\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_min_pw_len = '.$this->my_localized_strings['min_pw_len'].';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_min_password_length_string = \''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['min_password_length_string'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_time_values = [';
                    $ret .= '\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_am_label'] ).'\',';
                    $ret .= '\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_pm_label'] ).'\',';
                    $ret .= '\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_noon_label'] ).'\',';
                    $ret .= '\''.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_midnight_label'] ).'\'';
                $ret .= '];';
            $ret .= '</script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<script type="text/javascript" src="'.dirname ( $_SERVER['PHP_SELF'] ).'/local_server/server_admin'.(defined('__DEBUG_MODE__') ? '/' : '/js_stripper.php?filename=' ).'json2.js"></script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<script type="text/javascript" src="'.dirname ( $_SERVER['PHP_SELF'] ).'/local_server/server_admin'.(defined('__DEBUG_MODE__') ? '/' : '/js_stripper.php?filename=' ).'server_admin_javascript.js"></script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<noscript class="main_noscript">'.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['noscript'] ).'</noscript>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            // Belt and suspenders. Just make sure the user is legit.
            if ( ($this->my_user instanceof c_comdef_user) && ($this->my_user->GetUserLevel() != _USER_LEVEL_DISABLED) )
                {
                // Figure out which output will be sent, according to the user level.
                switch ( $this->my_user->GetUserLevel() )
                    {
                    case    _USER_LEVEL_SERVER_ADMIN:
                        $ret .= $this->return_format_editor_panel();
                        $ret .= $this->return_user_admin_panel();
                
                    case    _USER_LEVEL_SERVICE_BODY_ADMIN:
                        $ret .= $this->return_service_body_admin_panel();
                
                    case    _USER_LEVEL_EDITOR:
                        $ret .= $this->return_meeting_editor_panel();
                        
                    case    _USER_LEVEL_OBSERVER:
                        if ( $this->my_user->GetUserLevel() == _USER_LEVEL_OBSERVER )   // Observers get a link to the meeting search.
                            {
                            $ret .= '<div class="bmlt_admin_observer_link_div"><a href="client_interface/html" class="bmlt_admin_observer_link_a">'.self::js_html ( $this->my_localized_strings['comdef_server_admin_strings']['Observer_Link_Text'] ).'</a></div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                            }
                        $ret .= $this->return_user_account_settings_panel();
                    break;
                
                    default:
                        die ( 'USER NOT AUTHORIZED' );
                    break;
                    }
                }
            
        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        
        return  $ret;
    }
    
    /********************************************************************************************************//**
    \brief Does an HTML sub, and also "slashes" apostrophes.
    \returns "Cleaned" text
    ************************************************************************************************************/
    static function js_html(    $in_raw_html
                            )
    {
        return str_replace ( "'", "\'", htmlspecialchars ( $in_raw_html ) );
    }
    
    /********************************************************************************************************//**
    \brief This constructs the User editor panel. Only Server Admins get this one.
    \returns The HTML and JavaScript for the "Edit Users" section.
    ************************************************************************************************************/
    function return_format_editor_panel()
    {
        $ret = 'NOT AUTHORIZED TO EDIT USERS';
        
        if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
            {
            $ret = '<div id="bmlt_admin_format_editor_disclosure_div" class="bmlt_admin_format_editor_disclosure_div bmlt_admin_format_editor_disclosure_div_closed">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<a class="bmlt_admin_format_editor_disclosure_a" href="javascript:admin_handler_object.toggleFormatEditor();">';
                    $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['format_editor_disclosure'] );
                $ret .= '</a>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<div id="bmlt_admin_format_editor_wrapper_div" class="bmlt_admin_format_editor_wrapper_div bmlt_admin_format_editor_wrapper_div_hidden">';
                $ret .= '<div class="bmlt_admin_format_editor_banner_div">';
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_format_editor_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['format_change_fader_change_success_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_format_editor_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['format_change_fader_change_fail_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_format_create_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['format_change_fader_create_success_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_format_create_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['format_change_fader_create_fail_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_format_editor_delete_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['format_change_fader_delete_success_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_format_editor_delete_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['format_change_fader_delete_fail_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<div id="bmlt_admin_format_editor_inner_div" class="bmlt_admin_format_editor_inner_div"><table class="format_editor_table" id="bmlt_admin_format_editor_table" cellpadding="0" cellspacing="0" border="0"></table></div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<script type="text/javascript">admin_handler_object.populateFormatEditor()</script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the User editor panel. Only Server Admins get this one.
    \returns The HTML and JavaScript for the "Edit Users" section.
    ************************************************************************************************************/
    function return_user_admin_panel()
    {
        $ret = 'NOT AUTHORIZED TO EDIT USERS';
        
        if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
            {
            if ( count ( $this->my_users ) )
                {
                $ret = '<div id="bmlt_admin_user_editor_disclosure_div" class="bmlt_admin_user_editor_disclosure_div bmlt_admin_user_editor_disclosure_div_closed">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<a class="bmlt_admin_user_editor_disclosure_a" href="javascript:admin_handler_object.toggleUserEditor();">';
                        $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_disclosure'] );
                    $ret .= '</a>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<div id="bmlt_admin_user_editor_wrapper_div" class="bmlt_admin_user_editor_wrapper_div bmlt_admin_user_editor_wrapper_div_hidden">';
                    $ret .= '<div class="bmlt_admin_user_editor_banner_div">';
                        $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_user_editor_success_div">';
                            $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_success_text'] ).'</span>';
                        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                        $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_user_editor_fail_div">';
                            $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_fail_text'] ).'</span>';
                        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                        $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_user_create_success_div">';
                            $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_create_success_text'] ).'</span>';
                        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                        $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_user_create_fail_div">';
                            $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_create_fail_text'] ).'</span>';
                        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                        $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_user_editor_delete_success_div">';
                            $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_delete_success_text'] ).'</span>';
                        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                        $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_user_editor_delete_fail_div">';
                            $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_change_fader_delete_fail_text'] ).'</span>';
                        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= $this->return_single_user_editor_panel();
                $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<script type="text/javascript">admin_handler_object.populateUserEditor()</script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                }
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a window for the User administrator.
    \returns The HTML and JavaScript for the "User Administration" section.
    ************************************************************************************************************/
    function return_single_user_editor_panel ()
    {
        $ret = '<div id="bmlt_admin_single_user_editor_div" class="bmlt_admin_single_user_editor_div">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<fieldset id="bmlt_admin_single_user_editor_fieldset" class="bmlt_admin_single_user_editor_fieldset">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<legend id="bmlt_admin_single_user_editor_fieldset_legend" class="bmlt_admin_single_user_editor_fieldset_legend">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= $this->create_user_popup();
                $ret .= '</legend>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_screen_sb_id_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left light_italic_display" id="user_editor_id_display"></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left" id="user_editor_single_non_service_body_admin_display">';
                            $ret .= $this->create_user_level_popup();
                    $ret .= '</span>';
                    $ret .= '<span id="user_editor_single_service_body_admin_display" class="item_hidden">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_1'] ).'</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_login_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_editor_login_input" id="bmlt_admin_user_editor_login_input" type="text" value="" onkeyup="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onchange="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_editor_login_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_login_default_text'] ).'\', true);</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_name_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_editor_name_input" id="bmlt_admin_user_editor_name_input" type="text" value="" onkeyup="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onchange="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_editor_name_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_name_default_text'] ).'\');</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_description_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><textarea cols="40" rows="10" name="bmlt_admin_user_editor_description_textarea" id="bmlt_admin_user_editor_description_textarea" onkeyup="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onchange="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);"></textarea></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_editor_description_textarea\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_description_default_text'] ).'\');</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_email_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_editor_email_input" id="bmlt_admin_user_editor_email_input" type="text" value="" onkeyup="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onchange="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_editor_email_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_email_default_text'] ).'\');</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span id="bmlt_admin_user_editor_password_label" class="bmlt_admin_med_label_right"></span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_editor_password_input" id="bmlt_admin_user_editor_password_input" type="text" value="" onkeyup="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onchange="admin_handler_object.handleTextInputChange(this);admin_handler_object.readUserEditorState();" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= $this->return_user_editor_button_panel ();
            $ret .= '</fieldset>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        $ret .= '<script type="text/javascript">admin_handler_object.populateUserEditor()</script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This creates the HTML for a user selection popup menu.
    \returns The HTML and JavaScript for the popup menu (select element).
    ************************************************************************************************************/
    function create_user_popup ()
    {
        $ret = '<select id="bmlt_admin_single_user_editor_user_select" class="bmlt_admin_single_user_editor_user_select" onchange="admin_handler_object.populateUserEditor();">';
            $first = true;
            for ( $index = 0; $index  < count ( $this->my_users ); $index++ )
                {
                $user = $this->my_users[$index];
                if ( $user->GetID() != $this->my_user->GetID() )
                    {
                    $ret .= '<option value="'.$user->GetID().'"';
                    $ret .= '>'.htmlspecialchars ( $user->GetLocalName() ).'</option>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    }
                }
            $ret .= '<option value="" disabled="disabled"></option>';
            $ret .= '<option value="0" selected="selected">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_create_new_user_option'] ).'</option>';
        $ret .= '</select>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This creates the HTML for a user level popup menu.
    \returns The HTML and JavaScript for the popup menu (select element).
    ************************************************************************************************************/
    function create_user_level_popup ()
    {
        $ret = '<select id="bmlt_admin_single_user_editor_level_select" class="bmlt_admin_single_user_editor_level_select" onchange="admin_handler_object.readUserEditorState();">';
            $first = true;
            $ret .= '<option value="2">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_2'] ).'</option>';
//             $ret .= '<option value="3">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_3'] ).'</option>';
            $ret .= '<option value="5">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_5'] ).'</option>';
            $ret .= '<option value="" disabled="disabled"></option>';
            $ret .= '<option value="4">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_account_type_4'] ).'</option>';
        $ret .= '</select>';
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the User editor buttons as a div.
    \returns The HTML and JavaScript for the button panel.
    ************************************************************************************************************/
    function return_user_editor_button_panel ()
    {
        $ret = '<div class="bmlt_admin_user_editor_button_div">';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_meeting_button_left_span">';
                $ret .= '<a id="bmlt_admin_user_editor_form_user_save_button" href="javascript:admin_handler_object.saveUser();" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_save_button'] ).'</a>';
                $ret .= '<span id="bmlt_admin_user_save_ajax_button_throbber_span" class="bmlt_admin_ajax_button_throbber_span item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
            $ret .= '</span>';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_middle_button_single_span bmlt_admin_delete_button_span hide_in_new_user_admin">';
                $ret .= '<a id="bmlt_admin_meeting_editor_form_user_delete_button" href="javascript:admin_handler_object.deleteUser();" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_delete_button'] ).'</a>';
                $ret .= '<span id="bmlt_admin_user_delete_ajax_button_throbber_span" class="bmlt_admin_ajax_button_throbber_span item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                $ret .= '<span class="perm_checkbox_span">';
                    $ret .= '<input type="checkbox" id="bmlt_admin_user_delete_perm_checkbox" />';
                    $ret .= '<label for="bmlt_admin_user_delete_perm_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_delete_perm_checkbox'] ).'</label>';
                $ret .= '</span>';
            $ret .= '</span>';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_meeting_button_right_span">';
                $ret .= '<a id="bmlt_admin_user_editor_form_user_editor_cancel_button" href="javascript:admin_handler_object.cancelUserEdit();" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_cancel_button'] ).'</a>';
            $ret .= '</span>';
            $ret .= '<div class="clear_both"></div>';
        $ret .= '</div>';
                    
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the Service body editor panel. Only Server Admins and Service Body Admins get this one.
    \returns The HTML and JavaScript for the "Service Body Administration" section.
    ************************************************************************************************************/
    function return_service_body_admin_panel()
    {
        $ret = '';
        $full_editors = $this->get_full_editor_users();

        if ( count ( $full_editors ) )  // Have to have at least one Service body admin
            {
            $ret = '<div id="bmlt_admin_service_body_editor_disclosure_div" class="bmlt_admin_service_body_editor_disclosure_div bmlt_admin_service_body_editor_disclosure_div_closed">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<a class="bmlt_admin_service_body_editor_disclosure_a" href="javascript:admin_handler_object.toggleServiceBodyEditor();">';
                    $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_disclosure'] );
                $ret .= '</a>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<div id="bmlt_admin_service_body_editor_wrapper_div" class="bmlt_admin_service_body_editor_wrapper_div bmlt_admin_service_body_editor_wrapper_div_hidden">';
                $ret .= '<div class="bmlt_admin_service_body_editor_banner_div">';
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_service_body_editor_warn_div">';
                        $ret .= '<span class="warn_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['need_refresh_message_fader_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_service_body_editor_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_success_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_service_body_editor_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_fail_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_service_body_create_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_create_success_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_service_body_create_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_create_fail_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_service_body_editor_delete_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_delete_success_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_service_body_editor_delete_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_change_fader_delete_fail_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '</div>';
            
                $ret .= $this->return_single_service_body_editor_panel();
            $ret .= '</div>';
            $ret .= '<script type="text/javascript">admin_handler_object.populateServiceBodyEditor()</script>';
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a window for the Service Body administrator.
    \returns The HTML and JavaScript for the "Service Body Administration" section.
    ************************************************************************************************************/
    function return_single_service_body_editor_panel ()
    {
        $ret = '<div id="bmlt_admin_single_service_body_editor_div" class="bmlt_admin_single_service_body_editor_div">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<fieldset id="bmlt_admin_single_service_body_editor_fieldset" class="bmlt_admin_single_service_body_editor_fieldset">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<legend id="bmlt_admin_single_service_body_editor_fieldset_legend" class="bmlt_admin_single_service_body_editor_fieldset_legend">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');

                    if ( !($this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) && count ( $this->my_editable_service_bodies ) == 1 )
                        {
                        $ret .= '<span class="service_body_title_span">'.htmlspecialchars ( $this->my_editable_service_bodies[0]->GetLocalName() ).'</span>';
                        }
                    else
                        {
                        $ret .= $this->create_service_body_popup();
                        }
                    
                $ret .= '</legend>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_id_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left light_italic_display" id="service_body_admin_id_display"></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_user_label'] ).'</span>';

                    if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
                        {
                        $ret .= '<span class="bmlt_admin_value_left">';
                            $ret .= $this->create_service_body_user_popup();
                        $ret .= '</span>';
                        }
                    else
                        {
                        $ret .= '<span id="single_user_service_body_admin_span" class="bmlt_admin_value_left light_italic_display"></span>';
                        }
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
                    {
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_type_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left">';
                            $ret .= $this->create_service_body_type_popup();
                        $ret .= '</span>';
                        $ret .= '<div class="clear_both"></div>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_parent_popup_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left">';
                            $ret .= $this->create_service_body_parent_popup();
                        $ret .= '</span>';
                        $ret .= '<div class="clear_both"></div>';
                    $ret .= '</div>';
                    }
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_name_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_service_body_editor_sb_name_text_input" type="text" value="" onkeyup="admin_handler_object.handleTextInputServiceBodyChange(this, 2);" onchange="admin_handler_object.handleTextInputServiceBodyChange(this, 2);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';                
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_description_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><textarea cols="40" rows="10" name="bmlt_admin_sb_description_textarea" id="bmlt_admin_sb_description_textarea" class="bmlt_text_item" onkeyup="admin_handler_object.handleTextInputServiceBodyChange(this, 3);" onchange="admin_handler_object.handleTextInputServiceBodyChange(this, 3);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);"></textarea></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_email_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_service_body_editor_sb_email_text_input" type="text" value="" onkeyup="admin_handler_object.handleTextInputServiceBodyChange(this, 6);" onchange="admin_handler_object.handleTextInputServiceBodyChange(this, 6);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';                
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_uri_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_service_body_editor_sb_uri_text_input" type="text" value="" onkeyup="admin_handler_object.handleTextInputServiceBodyChange(this, 7);" onchange="admin_handler_object.handleTextInputServiceBodyChange(this, 7);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_world_cc_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_service_body_editor_world_cc_text_input" type="text" value="" onkeyup="admin_handler_object.handleTextInputServiceBodyChange(this, 12);" onchange="admin_handler_object.handleTextInputServiceBodyChange(this, 12);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                
                $full_editors = $this->get_full_editor_users();
                $basic_editors = $this->get_basic_editor_users();
                $observers = $this->get_observer_users();
                
                if ( count ( $full_editors ) )
                    {
                    $ret .= '<div id="service_body_admin_full_editor_list_div" class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_full_editor_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left light_italic_display">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_full_editor_desc'] ).'</span>';
                        $ret .= '<div class="clear_both"></div>';
                        
                        foreach ( $full_editors as $user )
                            {
                            $ret .= '<span class="bmlt_admin_med_label_right"><input type="checkbox" id="service_body_admin_editor_user_'.$user->GetID().'_checkbox" onchange="admin_handler_object.serviceBodyUserChecboxHandler('.$user->GetID().',this);" onclick="admin_handler_object.serviceBodyUserChecboxHandler('.$user->GetID().',this);" /></span>';
                            $ret .= '<label class="bmlt_admin_med_label_left" for="service_body_admin_editor_user_'.$user->GetID().'_checkbox">'.htmlspecialchars ( $user->GetLocalName() ).'</label>';
                            $ret .= '<div class="clear_both"></div>';
                            }
                        
                    $ret .= '</div>';
                    $ret .= '<div class="clear_both"></div>';
                    }
                
//                 if ( count ( $basic_editors ) )
//                     {
//                     $ret .= '<div id="service_body_admin_editor_list_div" class="bmlt_admin_one_line_in_a_form clear_both">';
//                         $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_editor_label'] ).'</span>';
//                         $ret .= '<span class="bmlt_admin_value_left light_italic_display">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_editor_desc'] ).'</span>';
//                         $ret .= '<div class="clear_both"></div>';
//                         
//                         foreach ( $basic_editors as $user )
//                             {
//                             $ret .= '<span class="bmlt_admin_med_label_right"><input type="checkbox" id="service_body_admin_editor_user_'.$user->GetID().'_checkbox" onchange="admin_handler_object.serviceBodyUserChecboxHandler('.$user->GetID().',this);" onclick="admin_handler_object.serviceBodyUserChecboxHandler('.$user->GetID().',this);" /></span>';
//                             $ret .= '<label class="bmlt_admin_med_label_left" for="service_body_admin_editor_user_'.$user->GetID().'_checkbox">'.htmlspecialchars ( $user->GetLocalName() ).'</label>';
//                             $ret .= '<div class="clear_both"></div>';
//                             }
//                     $ret .= '</div>';
//                     $ret .= '<div class="clear_both"></div>';
//                     }
                    
                if ( count ( $observers ) )
                    {
                    $ret .= '<div id="service_body_admin_observer_list_div" class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_observer_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left light_italic_display">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_screen_sb_admin_observer_desc'] ).'</span>';
                        $ret .= '<div class="clear_both"></div>';
                        
                        foreach ( $observers as $user )
                            {
                            $ret .= '<span class="bmlt_admin_med_label_right"><input type="checkbox" id="service_body_admin_editor_user_'.$user->GetID().'_checkbox" onchange="admin_handler_object.serviceBodyUserChecboxHandler('.$user->GetID().',this);" onclick="admin_handler_object.serviceBodyUserChecboxHandler('.$user->GetID().',this);" /></span>';
                            $ret .= '<label class="bmlt_admin_med_label_left" for="service_body_admin_editor_user_'.$user->GetID().'_checkbox">'.htmlspecialchars ( $user->GetLocalName() ).'</label>';
                            $ret .= '<div class="clear_both"></div>';
                            }
                    $ret .= '</div>';
                    $ret .= '<div class="clear_both"></div>';
                    }
                
                $ret .= '<div class="clear_both"></div>';
                $ret .= $this->return_service_body_editor_button_panel ();
            $ret .= '</fieldset>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This gets just the Service Body Admin Users, and returns their objects in an array.
    \returns An array with the user objects (instances of c_comdef_user)
    ************************************************************************************************************/
    function get_full_editor_users ()
    {
        $ret = array ();
        
        for ( $c = 0; $c < count ( $this->my_users ); $c++ )
            {
            $user = $this->my_users[$c];
            if ( $user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN )
                {
                array_push ( $ret, $user );
                }
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This gets just the Service Body Editor (Trainee) Users, and returns their objects in an array.
    \returns An array with the user objects (instances of c_comdef_user)
    ************************************************************************************************************/
    function get_basic_editor_users ()
    {
        $ret = array ();
        
        for ( $c = 0; $c < count ( $this->my_users ); $c++ )
            {
            $user = $this->my_users[$c];
            if ( $user->GetUserLevel() == _USER_LEVEL_EDITOR )
                {
                array_push ( $ret, $user );
                }
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This gets just the Observer Users, and returns their objects in an array.
    \returns An array with the user objects (instances of c_comdef_user)
    ************************************************************************************************************/
    function get_observer_users ()
    {
        $ret = array ();
        
        for ( $c = 0; $c < count ( $this->my_users ); $c++ )
            {
            $user = $this->my_users[$c];
            if ( $user->GetUserLevel() == _USER_LEVEL_OBSERVER )
                {
                array_push ( $ret, $user );
                }
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This creates the HTML for a Service body parent selection popup menu.
    \returns The HTML and JavaScript for the popup menu (select element).
    ************************************************************************************************************/
    function create_service_body_parent_popup ()
    {
        $ret = '<select id="bmlt_admin_single_service_body_editor_parent_select" class="bmlt_admin_single_service_body_editor_parent_select" onchange="admin_handler_object.recalculateServiceBody();">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');

            $ret .= '<option id="parent_popup_option_0" selected="selected" value="0">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_parent_popup_no_parent_option'] ).'</option>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            
            for ( $index = 0; $index  < count ( $this->my_editable_service_bodies ); $index++ )
                {
                $service_body = $this->my_editable_service_bodies[$index];
                $ret .= '<option id="parent_popup_option_'.$service_body->GetID().'" value="'.$service_body->GetID().'">'.htmlspecialchars ( $service_body->GetLocalName() ).'</option>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                }
        $ret .= '</select>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This creates the HTML for a Service body selection popup menu.
    \returns The HTML and JavaScript for the popup menu (select element).
    ************************************************************************************************************/
    function create_service_body_popup ()
    {
        $ret = '<select id="bmlt_admin_single_service_body_editor_sb_select" class="bmlt_admin_single_service_body_editor_sb_select" onchange="admin_handler_object.populateServiceBodyEditor();">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $first = true;

            for ( $index = 0; $index  < count ( $this->my_editable_service_bodies ); $index++ )
                {
                $service_body = $this->my_editable_service_bodies[$index];
                $ret .= '<option value="'.$service_body->GetID().'"';
                if ( $first )
                    {
                    $ret .= ' selected="selected"';
                    $first = false;
                    }
                $ret .= '>'.htmlspecialchars ( $service_body->GetLocalName() ).'</option>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                }
            
            // Service body admin adds a special one at the end for creating a new one.
            if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
                {
                if ( !$first )
                    {
                    $ret .= '<option value="" disabled="disabled"></option>';
                    }
                    
                $ret .= '<option value="0"';
                
                if ( $first )
                    {
                    $ret .= ' selected="selected"';
                    }
                    
                $ret .= '>'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_create_new_sb_option'] ).'</option>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                }
        $ret .= '</select>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This creates the HTML for a Service body selection popup menu.
    \returns The HTML and JavaScript for the popup menu (select element).
    ************************************************************************************************************/
    function create_service_body_type_popup ()
    {
        $ret = '<select id="bmlt_admin_single_service_body_editor_type_select" class="bmlt_admin_single_service_body_editor_type_select" onchange="admin_handler_object.recalculateServiceBody();">';
            $ret .= '<option value="GR">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_type_c_comdef_service_body__GRP__'] );
            $ret .= '</option>';
            $ret .= '<option value="AS">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_type_c_comdef_service_body__ASC__'] );
            $ret .= '</option>';
            $ret .= '<option value="RS">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_type_c_comdef_service_body__RSC__'] );
            $ret .= '</option>';
            $ret .= '<option value="WS">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_type_c_comdef_service_body__WSC__'] );
            $ret .= '</option>';
            $ret .= '<option value="MA">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_type_c_comdef_service_body__MAS__'] );
            $ret .= '</option>';
            $ret .= '<option value="ZF">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_type_c_comdef_service_body__ZFM__'] );
            $ret .= '</option>';
        $ret .= '</select>';
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This returns the user name for a given user ID.
    \returns a string, containing the name.
    ************************************************************************************************************/
    function get_user_name_from_id ($in_user_id  ///< The ID to look up.
                                    )
    {
        $ret = NULL;
        
        for ( $index = 0; $index  < count ( $this->my_users ); $index++ )
            {
            $user = $this->my_users[$index];
            if ( $user->GetID() == $in_user_id )
                {
                $ret = $user->GetLocalName();
                break;
                }
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This creates the HTML for a Service body selection popup menu.
    \returns The HTML and JavaScript for the popup menu (select element).
    ************************************************************************************************************/
    function create_service_body_user_popup ()
    {
        $ret = '<select id="bmlt_admin_single_service_body_editor_principal_user_select" class="bmlt_admin_single_service_body_editor_principal_user_select" onchange="admin_handler_object.recalculateServiceBody();">';

            for ( $index = 0; $index  < count ( $this->my_users ); $index++ )
                {
                $user = $this->my_users[$index];
                if ( $user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN )
                    {
                    $ret .= '<option value="'.$user->GetID().'">'.htmlspecialchars ( $user->GetLocalName() ).'</option>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    }
                }
        $ret .= '</select>';
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the Service body editor buttons as a div.
    \returns The HTML and JavaScript for the button panel.
    ************************************************************************************************************/
    function return_service_body_editor_button_panel ()
    {
        $ret = '<div class="naws_link_div" id="service_body_editor_naws_link_div">';
            $ret .= '<a id="service_body_editor_naws_link_a" href="javascript:admin_handler_object.getNawsDump();">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_editor_uri_naws_format_text'] );
            $ret .= '</a>';
        $ret .= '</div>';
        $ret .= '<div class="bmlt_admin_service_body_editor_button_div">';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_meeting_button_left_span">';
                $ret .= '<a id="bmlt_admin_service_body_editor_form_service_body_save_button" href="javascript:admin_handler_object.saveServiceBody();" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_save_button'] ).'</a>';
                $ret .= '<span id="bmlt_admin_service_body_save_ajax_button_throbber_span" class="bmlt_admin_ajax_button_throbber_span item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
            $ret .= '</span>';
            if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
                {
                $ret .= '<span id="service_body_editor_delete_span" class="bmlt_admin_meeting_editor_form_middle_button_single_span bmlt_admin_delete_button_span hide_in_new_service_body_admin">';
                    $ret .= '<a id="bmlt_admin_meeting_editor_form_service_body_delete_button" href="javascript:admin_handler_object.deleteServiceBody();" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_delete_button'] ).'</a>';
                    $ret .= '<span id="bmlt_admin_service_body_delete_ajax_button_throbber_span" class="bmlt_admin_ajax_button_throbber_span item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                    $ret .= '<span class="perm_checkbox_span">';
                        $ret .= '<input type="checkbox" id="bmlt_admin_service_body_delete_perm_checkbox" />';
                        $ret .= '<label for="bmlt_admin_service_body_delete_perm_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_delete_perm_checkbox'] ).'</label>';
                    $ret .= '</span>';
                $ret .= '</span>';
                }
            $ret .= '<span class="bmlt_admin_meeting_editor_form_meeting_button_right_span"><a id="bmlt_admin_service_body_editor_form_meeting_template_cancel_button" href="javascript:admin_handler_object.cancelServiceBodyEdit();" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['service_body_cancel_button'] ).'</a></span>';
            $ret .= '<div class="clear_both"></div>';
        $ret .= '</div>';
                    
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the meeting editor section of the console. Most user levels (not observers) have it.
    \returns The HTML and JavaScript for the "Edit Meetings" section.
    ************************************************************************************************************/
    function return_meeting_editor_panel ()
    {
        $ret = '';
        
        $can_edit = false;
        
        for ( $c = 0; $c < count ( $this->my_service_bodies ); $c++ )
            {
            if ( $this->my_service_bodies[$c]->UserCanEditMeetings() )
                {
                $can_edit = true;
                }
            }
            
        if ( $can_edit )
            {
            $ret = '<div id="bmlt_admin_meeting_editor_disclosure_div" class="bmlt_admin_meeting_editor_disclosure_div bmlt_admin_meeting_editor_disclosure_div_closed">';
                $ret .= '<a class="bmlt_admin_meeting_editor_disclosure_a" href="javascript:admin_handler_object.toggleMeetingEditor();">';
                    $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_disclosure'] );
                $ret .= '</a>';
            $ret .= '</div>';
            $ret .= '<div id="bmlt_admin_meeting_editor_wrapper_div" class="bmlt_admin_meeting_editor_wrapper_div bmlt_admin_meeting_editor_wrapper_div_hidden">';
                $ret .= '<div class="bmlt_admin_meeting_editor_banner_div">';
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_meeting_editor_warn_div">';
                        $ret .= '<span class="warn_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['need_refresh_message_fader_text'] ).'</span>';
                    $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_meeting_editor_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_change_fader_success_text'] ).'</span>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_meeting_editor_delete_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_change_fader_success_delete_text'] ).'</span>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_meeting_editor_add_success_div">';
                        $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_change_fader_success_add_text'] ).'</span>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_meeting_editor_delete_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_change_fader_fail_delete_text'] ).'</span>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_meeting_editor_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_change_fader_failure_text'] ).'</span>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_meeting_editor_add_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_change_fader_fail_add_text'] ).'</span>';
                    $ret .= '</div>';
                $ret .= '</div>';
                $ret .='<div class="bmlt_admin_meeting_editor_tab_div">';
                    $ret .= $this->return_meeting_editor_tab_div();
                $ret .= '</div>';
                $ret .='<div class="bmlt_admin_meeting_editor_inner_div">';
                    $ret .= $this->return_meeting_specification_panel();
                    $ret .= $this->return_meeting_editor_meetings_panel();
                $ret .= '</div>';
            
                $ret .= '<div class="clear_both"></div>';
            $ret .= '</div>';
            }
        
        return $ret;
    }

    /********************************************************************************************************//**
    \brief This constructs the tab div that allows the user to select between a search and results.
    \returns The HTML and JavaScript for the Meeting Editor Tabs
    ************************************************************************************************************/
    function return_meeting_editor_tab_div()
    {
        $ret ='<div id="bmlt_admin_meeting_editor_tab_specifier_div" class="bmlt_admin_tab_div_left bmlt_admin_tab_div_selected">';  // The link for the search specifier.
            $ret .= '<a id="bmlt_admin_meeting_editor_tab_specifier_a">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_specifier_text'] );
            $ret .= '</a>';
        $ret .= '</div>';
        $ret .='<div id="bmlt_admin_meeting_editor_tab_results_div" class="bmlt_admin_tab_div_right bmlt_admin_tab_div_not_selected">';   // The link for the results/editor.
            $ret .= '<a id="bmlt_admin_meeting_editor_tab_results_a" href="javascript:admin_handler_object.selectMeetingEditorTab();">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_editor_text'] );
            $ret .= '</a>';
        $ret .= '</div>';
        $ret .= '<div class="clear_both"></div>';
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the meeting search specification panel of the meeting editor.
    \returns The HTML and JavaScript for the Edit Meetings Search Specifier section.
    ************************************************************************************************************/
    function return_meeting_specification_panel ()
    {
        $ret = '<div id="bmlt_admin_meeting_editor_form_specifier_div" class="bmlt_admin_meeting_editor_form_specifier_div">';
            $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_text_input_label'] ).'</span>';
                $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_text_specifier_input" id="bmlt_admin_text_specifier_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_text_input_default_text'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onchange="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_text_specifier_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_text_input_default_text'] ).'\');</script>';
                $ret .= '<div class="clear_both"></div>';
            $ret .= '</div>';
            $ret .= '<div class="bmlt_admin_one_line_in_a_form_no_margin">';
                $ret .= '<span class="bmlt_admin_med_label_right"><input type="checkbox" id="bmlt_admin_meeting_search_text_is_a_location_checkbox" /></span>';
                $ret .= '<label class="bmlt_admin_med_label_left" for="bmlt_admin_meeting_search_text_is_a_location_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_text_location_label'] ).'</label>';
                $ret .= '<div class="clear_both"></div>';
            $ret .= '</div>';
            $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_label'] ).'</span>';
                $ret .= '<div class="bmlt_admin_value_left_div">';
                    for ( $c = 0; $c < 8; $c++ )
                        {
                        $ret .= '<span class="single_checkbox_span">';
                            $ret .= '<input checked="checked" type="checkbox" id="bmlt_admin_meeting_search_weekday_checkbox_'.$c.'" onclick="admin_handler_object.handleWeekdayCheckBoxChanges('.$c.');" onchange="admin_handler_object.handleWeekdayCheckBoxChanges('.$c.');" />'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                            $ret .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_weekday_checkbox_'.$c.'">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_names'][$c] ).'</label>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                        $ret .= '</span>';
                        }
                $ret .= '</div>';
                $ret .= '<div class="clear_both"></div>';
            $ret .= '</div>';
            $ret .= $this->return_meeting_start_time_selection_panel ();
            if ( count ( $this->my_service_bodies ) > 1 )
                {
                $ret .= $this->return_meeting_service_body_selection_panel ();
                }
            $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_publish_search_prompt'] ).'</span>';
                $ret .= '<div class="bmlt_admin_value_left_div">';
                    $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_publish_search_select">';
                        $ret .= '<option value ="-1">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_publish_search_unpub'] ).'</option>';
                        $ret .= '<option value ="0" selected="selected">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_publish_search_all'] ).'</option>';
                        $ret .= '<option value ="1">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_publish_search_pub'] ).'</option>';
                    $ret .= '</select>';
                $ret .= '</div>';
                $ret .= '<div class="clear_both"></div>';
            $ret .= '</div>';
            $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                $ret .= '<span class="bmlt_admin_med_label_right">&nbsp;</span>';
                    $ret .= '<span id="bmlt_admin_meeting_search_ajax_button_span" class="bmlt_admin_value_left">';
                        $ret .= '<a id="bmlt_admin_meeting_search_ajax_button_a" href="javascript:admin_handler_object.searchForMeetings();" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_specifier_text'] ).'</a>';
                    $ret .= '</span>';
                $ret .= '<span id="bmlt_admin_meeting_search_ajax_button_throbber_span" class="bmlt_admin_value_left item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                $ret .= '<div class="clear_both"></div>';
            $ret .= '</div>';
            $ret .= '<div class="clear_both"></div>';
        $ret .= '</div>';
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a panel that displays a choice of Service bodies for the user to choose.
    \returns The HTML and JavaScript for the Edit Meetings Search Specifier section.
    ************************************************************************************************************/
    function return_meeting_start_time_selection_panel ()
    {
        $ret = '<div class="bmlt_admin_one_line_in_a_form clear_both">';
            $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_start_time_label'] ).'</span>';
            $ret .= '<div class="bmlt_admin_value_left_div">';
                $ret .= '<span class="single_checkbox_span">';
                    $ret .= '<input type="radio" name="bmlt_admin_meeting_search_start_time_radiogroup" checked="checked" id="bmlt_admin_meeting_search_start_time_all_checkbox" />';
                    $ret .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_start_time_all_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_start_time_all_label'] ).'</label>';
                $ret .= '</span>';
                $ret .= '<span class="single_checkbox_span">';
                    $ret .= '<input type="radio" name="bmlt_admin_meeting_search_start_time_radiogroup" id="bmlt_admin_meeting_search_start_time_morn_checkbox" />';
                    $ret .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_start_time_morn_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_start_time_morn_label'] ).'</label>';
                $ret .= '</span>';
                $ret .= '<span class="single_checkbox_span">';
                    $ret .= '<input type="radio" name="bmlt_admin_meeting_search_start_time_radiogroup" id="bmlt_admin_meeting_search_start_time_aft_checkbox" />';
                    $ret .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_start_time_aft_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_start_time_aft_label'] ).'</label>';
                $ret .= '</span>';
                $ret .= '<span class="single_checkbox_span">';
                    $ret .= '<input type="radio" name="bmlt_admin_meeting_search_start_time_radiogroup" id="bmlt_admin_meeting_search_start_time_eve_checkbox" />';
                    $ret .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_start_time_eve_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_start_time_eve_label'] ).'</label>';
                $ret .= '</span>';
            $ret .= '</div>';
            $ret .= '<div class="clear_both"></div>';
        $ret .= '</div>';

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a panel that displays a choice of Service bodies for the user to choose.
    \returns The HTML and JavaScript for the Edit Meetings Search Specifier section.
    ************************************************************************************************************/
    function return_meeting_service_body_selection_panel ()
    {
        $ret = 'NOT AUTHORIZED';
        
        if ( count ( $this->my_service_bodies ) )
            {
            $ret = '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_service_bodies_label'] ).'</span>';
                $ret .= '<div id="bmlt_admin_meeting_editor_service_div" class="bmlt_admin_meeting_editor_service_div">';
                    $ret .= $this->populate_service_bodies(0);
                $ret .= '</div>';
                $ret .= '<div class="clear_both"></div>';
            $ret .= '</div>';
            }
        
        return $ret;
    }
        
    /************************************************************************************//**
    *	\brief Build the content for the Advanced Service Bodies section.                   *
    ****************************************************************************************/
    function populate_service_bodies (  $in_id    ///< The ID of the Service body.
                                      )
    {
        $service_body_content = '';
        $child_content = '';
        
        foreach ( $this->my_all_service_bodies as $service_body )
            {
            if ( $in_id == $service_body->GetID() )
                {
                if ( $service_body->UserCanEditMeetings() )
                    {
                    $service_body_content = '<span class="single_checkbox_span">';
                        $service_body_content .= '<input type="checkbox" checked="checked" id="bmlt_admin_meeting_search_service_body_checkbox_'.$in_id.'" onclick="admin_handler_object.handleServiceCheckBoxChanges('.$in_id.');" onchange="admin_handler_object.handleServiceCheckBoxChanges('.$in_id.');" />';
                        $service_body_content .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_service_body_checkbox_'.$in_id.'">'.htmlspecialchars ( $service_body->GetLocalName() ).'</label>';
                    $service_body_content .= '</span>';
                    }
                }
            else if ( $in_id == $service_body->GetOwnerID() )
                {
                $child_content .= $this->populate_service_bodies ( $service_body->GetID() );
                }
            }
        
        // At this point, we have the main Service body, as well as any child content.
        
        if ( $service_body_content )
            {
            $service_body_content = '<dt class="service_body_dt'.($child_content != '' ? ' service_body_parent_dt' : '').'">'.$service_body_content.'</dt>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            }
        
        if ( $child_content )
            {
            $child_content = '<dd class="bmlt_admin_service_body'.($service_body_content != '' ? '_child' : '').'_dd">'.$child_content.'</dd>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            }
        
        $ret = '';
        
        if ( $service_body_content || $child_content )
            {
            $ret = '<dl class="service_body_dl">'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '').$service_body_content.(defined ( '__DEBUG_MODE__' ) ? "\n" : '').$child_content.'</dl>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            }
            
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the combined new meetings/search results panel.
    \returns The HTML and JavaScript for the Edit Meetings Search Results section.
    ************************************************************************************************************/
    function return_meeting_editor_meetings_panel ()
    {
        if ( ($this->my_user->GetUserLevel() == _USER_LEVEL_EDITOR) || ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) || ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) )
            {
            $ret = '<div id="bmlt_admin_meeting_editor_form_div" class="bmlt_admin_meeting_editor_form_div item_hidden">';
                $ret .= '<div class="bmlt_admin_meeting_editor_form_inner_div">';
                    $ret .= $this->return_single_meeting_editor_template();
                    $ret .= $this->return_new_meeting_panel();
                    $ret .= $this->return_meeting_results_panel();
                $ret .= '</div>';
            $ret .= '</div>';
            }
        else
            {
            die ( 'THIS USER NOT AUTHORIZED TO EDIT MEETINGS' );
            }

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a panel for creating new meetings that goes above the results.
    \returns The HTML and JavaScript for the New Meetings section.
    ************************************************************************************************************/
    function return_new_meeting_panel ()
    {
        $ret = '<div id="bmlt_admin_meeting_editor_form_new_meetings_div" class="bmlt_admin_meeting_editor_form_new_meetings_div">';
            $ret .= '<div class="bmlt_admin_meeting_editor_form_meetings_inner_div">';
                $ret .= '<div class="bmlt_admin_meeting_editor_form_meeting_button_div">';
                    $ret .= '<span id="bmlt_admin_meeting_ajax_button_span" class="bmlt_admin_meeting_editor_form_meeting_button_single_span"><a id="bmlt_admin_meeting_editor_form_meeting_'.$in_index.'button" href="javascript:admin_handler_object.createANewMeetingButtonHit(this);" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_create_button'] ).'</a></span>';
                    $ret .= '<div class="clear_both"></div>';
                    $ret .= '<div id="bmlt_admin_meeting_editor_new_meeting_0_editor_display" class="bmlt_admin_meeting_editor_meeting_editor_display item_hidden"></div>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</div>';
        $ret .= '</div>';

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the meeting search results panel of the meeting editor.
    \returns The HTML and JavaScript for the Search Results section.
    ************************************************************************************************************/
    function return_meeting_results_panel ()
    {
        $ret = '<div id="bmlt_admin_meeting_editor_form_results_div" class="bmlt_admin_meeting_editor_form_results_div item_hidden">';
            $ret .= '<div id="bmlt_admin_meeting_editor_form_results_banner_div" class="bmlt_admin_meeting_editor_form_results_banner_div"></div>';
            $ret .= '<div id="bmlt_admin_meeting_editor_form_results_inner_div" class="bmlt_admin_meeting_editor_form_results_inner_div">';
            $ret .= '</div>';
        $ret .= '</div>';

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a template to be filled in for a single meeting that will be edited.
    \returns The HTML and JavaScript for the "Edit Meetings" section.
    ************************************************************************************************************/
    function return_single_meeting_editor_template()
    {
        $ret = '<div id="bmlt_admin_single_meeting_editor_template_div" class="bmlt_admin_single_meeting_editor_div item_hidden">';
            $ret .= '<div class="bmlt_admin_single_meeting_outer_div">';
                $ret .= '<div id="bmlt_admin_meeting_editor_template_meeting_header" class="bmlt_admin_meeting_editor_meeting_header"></div>';
                $ret .= '<div class="bmlt_admin_meeting_inner_div">';
                    $ret .= '<div class="bmlt_admin_meeting_editor_tab_bar">';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(0,template);" id="bmlt_admin_meeting_editor_template_tab_item_basic_a" class="bmlt_admin_meeting_editor_tab_item_a_selected">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_basic_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(1,template);" id="bmlt_admin_meeting_editor_template_tab_item_location_a" class="bmlt_admin_meeting_editor_tab_item_a_unselected">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_location_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(2,template);" id="bmlt_admin_meeting_editor_template_tab_item_format_a" class="bmlt_admin_meeting_editor_tab_item_a_unselected">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_format_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(3,template);" id="bmlt_admin_meeting_editor_template_tab_item_other_a" class="bmlt_admin_meeting_editor_tab_item_a_unselected">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_other_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(4,template);" id="bmlt_admin_meeting_editor_template_tab_item_history_a" class="bmlt_admin_meeting_editor_tab_item_a_unselected hide_in_new_meeting">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_history_tab_text'] );
                        $ret .= '</a>';
                    $ret .= '</div>';
                    $ret .= '<div class="clear_both"></div>';
                    $ret .= $this->return_single_meeting_basic_template();
                    $ret .= $this->return_single_meeting_location_template();
                    $ret .= $this->return_single_meeting_format_template();
                    $ret .= $this->return_single_meeting_other_template();
                    $ret .= $this->return_single_meeting_history_template();
                $ret .= '</div>';
                $ret .= $this->return_meeting_editor_button_panel();
            $ret .= '</div>';
        $ret .= '</div>';
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the meeting editor buttons as a div.
    \returns The HTML and JavaScript for the button panel.
    ************************************************************************************************************/
    function return_meeting_editor_button_panel ()
    {
        $ret = '<div class="bmlt_admin_meeting_editor_form_meeting_button_div">';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_meeting_button_left_span">';
                $ret .= '<a id="bmlt_admin_meeting_editor_form_meeting_template_save_button" href="javascript:admin_handler_object.saveMeeting(template);" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_save_buttonName'] ).'</a>';
                $ret .= '<span id="bmlt_admin_template_save_ajax_button_throbber_span" class="bmlt_admin_ajax_button_throbber_span item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                $ret .= '<span class="duplicate_checkbox_span hide_in_new_meeting">';
                    $ret .= '<input type="checkbox" id="bmlt_admin_meeting_template_duplicate_checkbox" />';
                    $ret .= '<label for="bmlt_admin_meeting_template_duplicate_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_saved_as_a_copy'] ).'</label>';
                $ret .= '</span>';
            $ret .= '</span>';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_middle_button_single_span bmlt_admin_delete_button_span hide_in_new_meeting">';
                $ret .= '<a id="bmlt_admin_meeting_editor_form_meeting_template_delete_button" href="javascript:admin_handler_object.deleteMeeting(template);" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_delete_button'] ).'</a>';
                $ret .= '<span id="bmlt_admin_template_delete_ajax_button_throbber_span" class="bmlt_admin_ajax_button_throbber_span item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
                    {
                    $ret .= '<span class="perm_checkbox_span">';
                        $ret .= '<input type="checkbox" id="bmlt_admin_meeting_template_delete_perm_checkbox" />';
                        $ret .= '<label for="bmlt_admin_meeting_template_delete_perm_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_delete_perm_checkbox'] ).'</label>';
                    $ret .= '</span>';
                    }
            $ret .= '</span>';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_meeting_button_right_span"><a id="bmlt_admin_meeting_editor_form_meeting_template_cancel_button" href="javascript:admin_handler_object.cancelMeetingEdit(template);" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_cancel_button'] ).'</a></span>';
            $ret .= '<div class="clear_both"></div>';
        $ret .= '</div>';
                    
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a template to be filled in for the basic options tab.
    \returns The HTML and JavaScript for the option sheet.
    ************************************************************************************************************/
    function return_single_meeting_basic_template()
    {
        if ( ($this->my_user->GetUserLevel() == _USER_LEVEL_EDITOR) || ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) || ($this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) )
            {
            $ret = '<div id="bmlt_admin_meeting_template_basic_sheet_div" class="bmlt_admin_meeting_option_sheet_div">';
        
                if ( $this->my_user->GetUserLevel() != _USER_LEVEL_EDITOR )
                    {
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right"><input type="checkbox" id="bmlt_admin_meeting_template_published_checkbox" /></span>';
                        $ret .= '<label class="bmlt_admin_med_label_left" for="bmlt_admin_meeting_template_published_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_is_published'] ).'</label>';
                        $ret .= '<div class="clear_both"></div>';
                    $ret .= '</div>';
                    }
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both hide_in_new_meeting">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_id_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left light_italic_display" id="bmlt_admin_meeting_id_template_display"></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_name_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_name_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_name_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_weekday_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left">';
                        $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_weekday_select">';
                            for ( $m = 1; $m < 8; $m++ )
                                {
                                $ret .= '<option value="'.$m.'">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_names'][$m] ).'</option>';
                                }
                        $ret .= '</select>';
                    $ret .= '</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_start_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left bmlt_admin_time_selector">';
                        $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_start_hour_select">';
                            $ret .= '<option value ="1">1</option>';
                            $ret .= '<option value ="2">2</option>';
                            $ret .= '<option value ="3">3</option>';
                            $ret .= '<option value ="4">4</option>';
                            $ret .= '<option value ="5">5</option>';
                            $ret .= '<option value ="6">6</option>';
                            $ret .= '<option value ="7">7</option>';
                            $ret .= '<option value ="8">8</option>';
                            $ret .= '<option value ="9">9</option>';
                            $ret .= '<option value ="10">10</option>';
                            $ret .= '<option value ="11">11</option>';
                            $ret .= '<option value ="12">12</option>';
                            $ret .= '<option value ="13">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_noon_label'] ).'</option>';
                            $ret .= '<option value ="0">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_midnight_label'] ).'</option>';
                        $ret .= '</select>';
                        $ret .= '<span id="bmlt_admin_template_time_span" class="bmlt_admin_time_span">:';
                            $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_start_minute_select">';
                                for ( $m = 0; $m < 60; $m += 5 )
                                    {
                                    $ret .= '<option value="'.$m.'">'.sprintf ( "%02d", $m ).'</option>';
                                    }
                            $ret .= '</select>';
                            $ret .= '<span class="bmlt_admin_am_pm_radiogroup">';
                                $ret .= '<input type="radio" name="bmlt_admin_template_time_ampm_radio" id="bmlt_admin_template_time_am_radio" /><label for="bmlt_admin_template_time_am_radio">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_am_label'] ).'</label>';
                                $ret .= '<input type="radio" name="bmlt_admin_template_time_ampm_radio" id="bmlt_admin_template_time_pm_radio" /><label for="bmlt_admin_template_time_pm_radio">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_pm_label'] ).'</label>';
                            $ret .= '</span>';
                        $ret .= '</span>';
                    $ret .= '</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_duration_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left bmlt_admin_time_selector">';
                        $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_duration_hour_select">';
                            for ( $m = 0; $m < 24; $m++ )
                                {
                                $ret .= '<option value="'.$m.'">'.$m.'</option>';
                                }
                            $ret .= '<option value ="24">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_oe_label'] ).'</option>';
                        $ret .= '</select>';
                        $ret .= '<span id="bmlt_admin_template_duration_span" class="bmlt_admin_time_span">:';
                            $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_duration_minute_select">';
                                for ( $m = 0; $m < 60; $m += 5 )
                                    {
                                    $ret .= '<option value="'.$m.'">'.sprintf ( "%02d", $m ).'</option>';
                                    }
                            $ret .= '</select>';
                        $ret .= '</span>';
                    $ret .= '</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_cc_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_cc_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_cc_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                if ( count ( $this->my_service_bodies ) > 1 )
                    {
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_sb_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left">';
                            $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_sb_select">';
                                for ( $m = 0; $m < count ( $this->my_service_bodies ); $m++ )
                                    {
                                    $ret .= '<option value="'.$this->my_service_bodies[$m]->GetID().'">'.htmlspecialchars ( $this->my_service_bodies[$m]->GetLocalName() ).'</option>';
                                    }
                            $ret .= '</select>';
                        $ret .= '</span>';
                    $ret .= '<div class="clear_both"></div>';
                    $ret .= '</div>';
                    }
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_contact_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_contact_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_contact_prompt'] ).'" /></span>';
                    $ret .= '<span class="bmlt_admin_visibility_advice_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_visibility_advice'] ).'</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</div>';
            }
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a template to be filled in for the location options tab.
    \returns The HTML and JavaScript for the option sheet.
    ************************************************************************************************************/
    function return_single_meeting_location_template()
    {
        $ret = '<div id="bmlt_admin_meeting_template_location_sheet_div" class="bmlt_admin_meeting_option_sheet_div item_hidden">';
            $ret .= '<div id="bmlt_admin_single_meeting_editor_template_map_disclosure_div" class="bmlt_admin_single_meeting_disclosure_map_div_closed">';
                $ret .= '<a class="bmlt_admin_single_meeting_editor_map_disclosure_a" id="bmlt_admin_single_meeting_editor_template_map_disclosure_a">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_location_map_link'] ).'</a>';
            $ret .= '</div>';
            $ret .= '<div id="bmlt_admin_single_meeting_editor_template_map_div" class="bmlt_admin_single_meeting_map_div item_hidden">';
                $ret .= '<div id="bmlt_admin_single_meeting_editor_template_inner_map_div" class="bmlt_admin_single_meeting_editor_inner_map_div"></div>';
                $ret .= '<div class="bmlt_admin_single_meeting_editor_map_button_bar_div">';
                    $ret .= '<a id="bmlt_admin_meeting_map_template_button_a" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_match_map_button'] ).'</a>';
                $ret .= '</div>';
            $ret .= '</div>';
            $ret .= '<div class="clear_both"></div>';
            $ret .= '<div id="bmlt_admin_single_location_template_long_lat_div" class="bmlt_admin_single_location_long_lat_div">';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_longitude_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_longitude_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_longitude_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_latitude_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_latitude_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_latitude_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</div>';
            $ret .= '<div class="bmlt_admin_meeting_editor_address_div">';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_location_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_location_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_location_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_info_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_info_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_info_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_street_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_street_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_street_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_neighborhood_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_neighborhood_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_neighborhood_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_borough_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_borough_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_borough_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_city_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_city_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_city_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_county_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_county_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_county_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_state_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_state_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_state_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_zip_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_zip_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_zip_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_nation_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_nation_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_nation_prompt'] ).'" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</div>';
        $ret .= '</div>';

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief 
    \returns The HTML and JavaScript for the option sheet.
    ************************************************************************************************************/
    function return_single_meeting_format_template()
    {
        $ret = '<div id="bmlt_admin_meeting_template_format_sheet_div" class="bmlt_admin_meeting_option_sheet_div item_hidden">';
            $ret .= '<div class="format_tab_inner_div">';
                $f_array = $this->my_server->GetFormatsArray();
                $f_array = $f_array[$this->my_server->GetLocalLang()];
                foreach ( $f_array as $format )
                    {
                    if ( $format instanceof c_comdef_format )
                        {
                        $ret .= '<div class="bmlt_admin_meeting_one_format_div">';
                            $ret .= '<label class="left_label" for="bmlt_admin_meeting_template_format_'.$format->GetSharedID().'_checkbox">'.htmlspecialchars ( $format->GetKey() ).'</label>';
                            $ret .= '<span><input type="checkbox" value="'.$format->GetKey().'" id="bmlt_admin_meeting_template_format_'.$format->GetSharedID().'_checkbox" onchange="admin_handler_object.reactToFormatCheckbox(this, template);" onclick="admin_handler_object.reactToFormatCheckbox(this, template);" /></span>';
                            $ret .= '<label class="right_label" for="bmlt_admin_meeting_template_format_'.$format->GetSharedID().'_checkbox">'.htmlspecialchars ( $format->GetLocalName() ).'</label>';
                        $ret .= '</div>';
                        }
                    }
                $ret .= '<div class="clear_both"></div>';
            $ret .= '</div>';
        $ret .= '</div>';

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief 
    \returns The HTML and JavaScript for the option sheet.
    ************************************************************************************************************/
    function return_single_meeting_other_template()
    {
        $ret = '<div id="bmlt_admin_meeting_template_other_sheet_div" class="bmlt_admin_meeting_option_sheet_div item_hidden">';
            foreach ( $this->my_data_field_templates as $data_field )
                {
                $key = $data_field['key'];
                $prompt = $data_field['field_prompt'];
                switch ( $key )
                    {
                    case    'id_bigint':                // All of these are ignored, as they are taken care of in other option sheets.
                    case    'worldid_mixed':
                    case    'shared_group_id_bigint':
                    case    'service_body_bigint':
                    case    'weekday_tinyint':
                    case    'start_time':
                    case    'formats':
                    case    'lang_enum':
                    case    'longitude':
                    case    'latitude':
                    case    'email_contact':
                    case    'meeting_name':
                    case    'location_text':
                    case    'location_info':
                    case    'location_street':
                    case    'location_neighborhood':
                    case    'location_city_subsection':
                    case    'location_municipality':
                    case    'location_sub_province':
                    case    'location_province':
                    case    'location_postal_code_1':
                    case    'location_nation':
                    break;
            
                    default:    // We display these ones.
                        $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                            $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $prompt ).'</span>';
                            $ret .= '<span class="bmlt_admin_value_left">';
                                $ret .= '<input id="bmlt_admin_single_meeting_editor_template_meeting_'.htmlspecialchars ( $key ).'_text_input" type="text" onkeyup="admin_handler_object.setItemValue(this, template, \''.htmlspecialchars ( $key ).'\');" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this, true);" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_default_text_prompt'] ).'" />';
                            $ret .= '</span>';
                            if ( $data_field['visibility'] == _VISIBILITY_NONE_ )
                                {
                                $ret .= '<span class="bmlt_admin_visibility_advice_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_visibility_advice'] ).'</span>';
                                }
                            $ret .= '<div class="clear_both"></div>';
                        $ret .= '</div>';
                    break;
                    }
                }
        $ret .= '</div>';

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief 
    \returns The HTML and JavaScript for the option sheet.
    ************************************************************************************************************/
    function return_single_meeting_history_template()
    {
        $ret = '<div id="bmlt_admin_meeting_template_history_sheet_div" class="bmlt_admin_meeting_option_sheet_div item_hidden">';
            $ret .= '<div id="bmlt_admin_history_ajax_button_template_throbber_div" class="bmlt_admin_history_ajax_button_throbber_div"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></div>';
        $ret .= '</div>';

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the "My Account" section of the console. All user levels will have this.
    \returns The HTML and JavaScript for the "My Account" section.
    ************************************************************************************************************/
    function return_user_account_settings_panel ()
    {
        $ret = '<div id="bmlt_admin_user_account_disclosure_div" class="bmlt_admin_user_account_disclosure_div bmlt_admin_user_account_disclosure_div_closed">';
            $ret .= '<a class="bmlt_admin_user_account_disclosure_a" href="javascript:admin_handler_object.toggleAccountInfo();">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_disclosure'] );
            $ret .= '</a>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_admin_user_account_wrapper_div" class="bmlt_admin_user_account_wrapper_div bmlt_admin_user_account_wrapper_div_hidden">';
            $ret .= '<div class="bmlt_admin_user_account_banner_div">';
                $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_account_warn_div">';
                    $ret .= '<span class="warn_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['need_refresh_message_fader_text'] ).'</span>';
                $ret .= '</div>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_account_success_div">';
                    $ret .= '<span class="success_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_change_fader_success_text'] ).'</span>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_account_fail_div">';
                    $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_change_fader_failure_text'] ).'</span>';
                $ret .= '</div>';
            $ret .= '</div>';
            $ret .= '<input type="hidden" id="account_affected_user_id" value="'.htmlspecialchars ( $this->my_user->GetID() ).'" />';
            $ret .= '<div class="bmlt_admin_user_account_edit_form_inner_div">';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_name_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left">';
                        if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
                            {
                            $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_name_input" id="bmlt_admin_user_name_input" type="text" value="'.htmlspecialchars ( $this->my_user->GetLocalName() ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onchange="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                            $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_name_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_name_default_text'] ).'\');</script>';
                            }
                        else
                            {
                            $ret .= htmlspecialchars ( $this->my_user->GetLocalName() );
                            }
                    $ret .= '</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_login_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left">';
                        if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
                            {
                            $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_login_input" id="bmlt_admin_user_login_input" type="text" value="'.htmlspecialchars ( $this->my_user->GetLogin() ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onchange="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                            $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_login_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['user_editor_login_default_text'] ).'\');</script>';
                            }
                        else
                            {
                            $ret .= htmlspecialchars ( $this->my_user->GetLogin() );
                            }
                    $ret .= '</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_type_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_type_'.$this->my_user->GetUserLevel()] ).'</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['access_service_body_label'] ).'</span>';
                    $ret .= '<div class="bmlt_admin_value_left">';
                        for ( $c = 0; $c < count ( $this->my_service_bodies ); $c++ )
                            {
                            $ret .= '<p';
                                if ( $this->my_service_bodies[$c]->UserCanEdit() )
                                    {
                                    $ret .= ' class="service_body_can_be_edited';
                                    if ( $this->my_service_bodies[$c]->GetPrincipalUserID() == $this->my_user->GetID() )
                                        {
                                        $ret .= ' principal_user_p';
                                        }
                                    $ret .= '"';
                                    }
                            $ret .= '>'.htmlspecialchars ( $this->my_service_bodies[$c]->GetLocalName() );
                            
                            if ( $c < (count ( $this->my_service_bodies ) - 1) )
                                {
                                $ret .= ',';
                                }
                            $ret .= '</p>';
                            }
                    $ret .= '</div>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_email_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_email_input" id="bmlt_admin_user_email_input" type="text" value="'.htmlspecialchars ( $this->my_user->GetEmailAddress() ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onchange="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_email_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['email_address_default_text'] ).'\');</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_description_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><textarea cols="40" rows="10" name="bmlt_admin_user_description_textarea" id="bmlt_admin_user_description_textarea" class="bmlt_text_item" onkeyup="admin_handler_object.handleTextInputChange(this);" onchange="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);">'.htmlspecialchars ( $this->my_user->GetLocalDescription() ).'</textarea></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_description_textarea\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_description_default_text'] ).'\');</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['change_password_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_account_password_input" id="bmlt_admin_user_account_password_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['change_password_default_text'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onchange="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_account_password_input\'));</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">&nbsp;</span>';
                    $ret .= '<span id="bmlt_admin_account_change_ajax_button_span" class="bmlt_admin_value_left"><a id="bmlt_admin_account_change_ajax_button" href="javascript:admin_handler_object.handleAccountChange();" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_change_button_text'] ).'</a></span>';
                    $ret .= '<span id="bmlt_admin_account_change_ajax_button_throbber_span" class="bmlt_admin_value_left item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</div>';
        $ret .= '</div>';
        
        return  $ret;
    }
};
?>