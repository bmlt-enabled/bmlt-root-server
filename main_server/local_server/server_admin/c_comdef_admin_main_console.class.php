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
    var $my_formats;                    ///< The format objects that are available for meetings.
    var $my_data_field_templates;       ///< This holds the keys for all the possible data fields for this server.
    
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
        $this->my_ajax_uri = $_SERVER['PHP_SELF'].'?bmlt_ajax_callback=1';
        
        // We check this every chance that we get.
        if ( !$this->my_user || ($this->my_user->GetUserLevel() == _USER_LEVEL_DISABLED) )
            {
            die ( 'NOT AUTHORIZED' );
            }
        
        $this->my_formats = $this->my_server->GetFormatsArray();
            
        $service_bodies = $this->my_server->GetServiceBodyArray();
        $this->my_service_bodies = array();
        
        for ( $c = 0; $c < count ( $service_bodies ); $c++ )
            {
            $service_body = $service_bodies[$c];
            if ( $service_body->UserCanEdit() )
                {
                array_push ( $this->my_service_bodies, $service_body );
                }
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
            $ret .= '<script type="text/javascript" src="'.htmlspecialchars ( 'http://maps.google.com/maps/api/js?sensor=false' ).'"></script>';
            $ret .= '<script type="text/javascript" src="'.htmlspecialchars ( 'http://maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry' ).'"></script>';       
            $ret .= '<script type="text/javascript">';
                $ret .= 'var g_ajax_callback_uri = \''.htmlspecialchars ( $this->my_ajax_uri ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_current_user_id = \''.htmlspecialchars ( $this->my_user->GetID() ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_service_bodies_array = [';
                    for ( $c = 0; $c < count ( $this->my_service_bodies ); $c++ )
                        {
                        $ret .= '[';
                        $service_body = $this->my_service_bodies[$c];
                        $ret .= $service_body->GetID().',';
                        $ret .= $service_body->GetOwnerID().',';
                        $ret .= '\''.htmlspecialchars ( $service_body->GetLocalName() ).'\'';
                        $ret .=']';
                        if ( $c < (count ( $this->my_service_bodies ) - 1) )
                            {
                            $ret .= ',';
                            }
                        }
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_weekday_name_array = [';
                    for ( $c = 1; $c < 8; $c++ )
                        {
                        $ret .= '\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_names'][$c] ).'\'';
                        if ( $c < 8 )
                            {
                            $ret .= ',';
                            }
                        }
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_format_object_array = [';
                    $first = true;
                    foreach ( $this->my_formats[$this->my_server->GetLocalLang()] as $format )
                        {
                        if ( $format instanceof c_comdef_format )
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
                                $ret .= '"id":'.$format->GetSharedID();
                                $ret .= ',"key":"'.$format->GetKey().'"';
                                $ret .= ',"name":"'.$format->GetLocalName().'"';
                                $ret .= ',"description":"'.$format->GetLocalDescription().'"';
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
                                $ret .= "'".htmlspecialchars ( $key )."'";
                            break;
                            }
                        }
                $ret .= '];'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_closure_confirm_text = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_cancel_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_longitude = '.floatval ( $this->my_localized_strings['search_spec_map_center']['longitude'] ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_latitude = '.floatval ( $this->my_localized_strings['search_spec_map_center']['latitude'] ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_zoom = '.floatval ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_default_zoom'] ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_lookup_failed = \''.htmlspecialchars ( $this->my_localized_strings['search_spec_map_center']['meeting_lookup_failed'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_region_bias = \''.htmlspecialchars ( $this->my_localized_strings['region_bias'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_style_dir = \''.htmlspecialchars ( dirname ( $_SERVER['PHP_SELF'] ).'/local_server/server_admin/style' ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_Create_new_meeting_button_name = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_create_button_name'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_Save_meeting_button_name = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_save_buttonName'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_meeting_weekday = '.intVal ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_default_weekday'] ).';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_meeting_start_time = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_default_start_time'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_default_meeting_duration = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_default_duration'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_no_search_results_text = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_no_results_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_new_meeting_header_text = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_create_new_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_lookup_failed_not_enough_address_info = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_lookup_failed_not_enough_address_info'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_editor_result_count_format = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_result_count_format'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_editor_screen_delete_button_confirm = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_delete_button_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_editor_screen_delete_button_confirm_perm = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_delete_button_confirm_perm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_meeting_editor_already_editing_confirm = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_already_editing_confirm'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_history_header_format = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['history_header_format'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_history_no_history_available_text = \''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['history_no_history_available_text'] ).'\';'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
                $ret .= 'var g_time_values = [';
                    $ret .= '\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_am_label'] ).'\',';
                    $ret .= '\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_pm_label'] ).'\',';
                    $ret .= '\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_noon_label'] ).'\',';
                    $ret .= '\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_midnight_label'] ).'\'';
                $ret .= '];';
            $ret .= '</script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<script type="text/javascript" src="'.dirname ( $_SERVER['PHP_SELF'] ).'/local_server/server_admin'.(defined('__DEBUG_MODE__') ? '/' : '/js_stripper.php?filename=' ).'json2.js"></script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            $ret .= '<script type="text/javascript" src="'.dirname ( $_SERVER['PHP_SELF'] ).'/local_server/server_admin'.(defined('__DEBUG_MODE__') ? '/' : '/js_stripper.php?filename=' ).'server_admin_javascript.js"></script>'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
            // Belt and suspenders. Just make sure the user is legit.
            if ( ($this->my_user instanceof c_comdef_user) && ($this->my_user->GetUserLevel() != _USER_LEVEL_DISABLED) )
                {
                // Figure out which output will be sent, according to the user level.
                switch ( $this->my_user->GetUserLevel() )
                    {
                    case    _USER_LEVEL_SERVER_ADMIN:
                
                    case    _USER_LEVEL_SERVICE_BODY_ADMIN:
                
                    case    _USER_LEVEL_EDITOR:
                        $ret .= $this->return_meeting_editor_panel();
                        
                    case    _USER_LEVEL_OBSERVER:
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
    \brief This constructs the meeting editor section of the console. Most user levels (not observers) have it.
    \returns The HTML and JavaScript for the "Edit Meetings" section.
    ************************************************************************************************************/
    function return_meeting_editor_panel ()
    {
        $ret = 'NOT AUTHORIZED';
        
        if ( count ( $this->my_service_bodies ) )
            {
            $ret = '<div id="bmlt_admin_meeting_editor_disclosure_div" class="bmlt_admin_meeting_editor_disclosure_div bmlt_admin_meeting_editor_disclosure_div_closed">';
                $ret .= '<a class="bmlt_admin_meeting_editor_disclosure_a" href="javascript:admin_handler_object.toggleMeetingEditor()">';
                    $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_disclosure'] );
                $ret .= '</a>';
            $ret .= '</div>';
            $ret .= '<div id="bmlt_admin_meeting_editor_wrapper_div" class="bmlt_admin_meeting_editor_wrapper_div bmlt_admin_meeting_editor_wrapper_div_hidden">';
                $ret .= '<div class="bmlt_admin_meeting_editor_banner_div">';
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
            $ret .= '<a id="bmlt_admin_meeting_editor_tab_results_a" href="javascript:admin_handler_object.selectMeetingEditorTab()">';
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
                $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_text_specifier_input" id="bmlt_admin_text_specifier_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_text_input_default_text'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
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
                            $ret .= '<input checked="checked" type="checkbox" id="bmlt_admin_meeting_search_weekday_checkbox_'.$c.'" onclick="admin_handler_object.handleWeekdayCheckBoxChanges('.$c.')" onchange="admin_handler_object.handleWeekdayCheckBoxChanges('.$c.')" />'.(defined ( '__DEBUG_MODE__' ) ? "\n" : '');
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
                        $ret .= '<a id="bmlt_admin_meeting_search_ajax_button_a" href="javascript:admin_handler_object.searchForMeetings()" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_specifier_text'] ).'</a>';
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
    function populate_service_bodies (  $in_owner_id    ///< The ID of the "owner" Service body.
                                      )
    {
        $has_content = false;
        
        $ret = '<dl class="service_body_dl">';
        
        if ( defined ( '__DEBUG_MODE__' ) )
            {
            $ret .= "\n";
            }
        
        for ( $c = 0; $c < count ( $this->my_service_bodies ); $c++ )
            {
            $service_body = $this->my_service_bodies[$c];
            
            if ( $in_owner_id == $service_body->GetOwnerID() )
                {
                $id = $service_body->GetID();
                
                $has_content = true;
                
                $r = $this->populate_service_bodies($id);
                
                $ret .= '<dt class="service_body_dt'.($r != '' ? ' service_body_parent_dt' : '').'">';
                    $ret .= '<span class="single_checkbox_span">';
                        $ret .= '<input type="checkbox" checked="checked" id="bmlt_admin_meeting_search_service_body_checkbox_'.$id.'" onclick="admin_handler_object.handleServiceCheckBoxChanges('.$id.')" onchange="admin_handler_object.handleServiceCheckBoxChanges('.$id.')" />';
                        $ret .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_service_body_checkbox_'.$id.'">'.htmlspecialchars ( $service_body->GetLocalName() ).'</label>';
                    $ret .= '</span>';
                $ret .= '</dt>';
                
                if ( defined ( '__DEBUG_MODE__' ) )
                    {
                    $ret .= "\n";
                    }
                
                if ( $r != '' )
                    {
                    $ret .= '<dd class="bmlt_admin_service_body_child_dd">'.$r.'</dd>';
                    if ( defined ( '__DEBUG_MODE__' ) )
                        {
                        $ret .= "\n";
                        }
                    }
                }
            }
        
        if ( $has_content )
            {
            $ret .= '</dl>';
            if ( defined ( '__DEBUG_MODE__' ) )
                {
                $ret .= "\n";
                }
            }
        else
            {
            $ret = '';
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
                    $ret .= '<span id="bmlt_admin_meeting_ajax_button_span" class="bmlt_admin_meeting_editor_form_meeting_button_single_span"><a id="bmlt_admin_meeting_editor_form_meeting_'.$in_index.'button" href="javascript:admin_handler_object.createANewMeetingButtonHit(this)" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_create_button'] ).'</a></span>';
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
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(0,template)" id="bmlt_admin_meeting_editor_template_tab_item_basic_a" class="bmlt_admin_meeting_editor_tab_item_a_selected">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_basic_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(1,template)" id="bmlt_admin_meeting_editor_template_tab_item_location_a" class="bmlt_admin_meeting_editor_tab_item_a_unselected">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_location_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(2,template)" id="bmlt_admin_meeting_editor_template_tab_item_format_a" class="bmlt_admin_meeting_editor_tab_item_a_unselected">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_format_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(3,template)" id="bmlt_admin_meeting_editor_template_tab_item_other_a" class="bmlt_admin_meeting_editor_tab_item_a_unselected">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_other_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<a href="javascript:admin_handler_object.selectAnEditorTab(4,template)" id="bmlt_admin_meeting_editor_template_tab_item_history_a" class="bmlt_admin_meeting_editor_tab_item_a_unselected hide_in_new_meeting">';
                            $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_tab_bar_history_tab_text'] );
                        $ret .= '</a>';
                        $ret .= '<div class="clear_both"></div>';
                    $ret .= '</div>';
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
    \brief This constructs the "My Account" section of the console. All user levels will have this.
    \returns The HTML and JavaScript for the "My Account" section.
    ************************************************************************************************************/
    function return_meeting_editor_button_panel ()
    {
        $main_button_text = $this->my_localized_strings['comdef_server_admin_strings']['meeting_save_buttonName'];
        $ret = '<div class="bmlt_admin_meeting_editor_form_meeting_button_div">';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_meeting_button_left_span">';
                $ret .= '<a id="bmlt_admin_meeting_editor_form_meeting_template_save_button" href="javascript:admin_handler_object.saveMeeting(template)" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_save_buttonName'] ).'</a>';
                $ret .= '<span id="bmlt_admin_template_save_ajax_button_throbber_span" class="bmlt_admin_ajax_button_throbber_span item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                $ret .= '<span class="duplicate_checkbox_span hide_in_new_meeting">';
                    $ret .= '<input type="checkbox" id="bmlt_admin_meeting_template_duplicate_checkbox" />';
                    $ret .= '<label for="bmlt_admin_meeting_template_duplicate_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_saved_as_a_copy'] ).'</label>';
                $ret .= '</span>';
            $ret .= '</span>';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_middle_button_single_span bmlt_admin_delete_button_span hide_in_new_meeting">';
                $ret .= '<a id="bmlt_admin_meeting_editor_form_meeting_template_delete_button" href="javascript:admin_handler_object.deleteMeeting(template)" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_delete_button'] ).'</a>';
                $ret .= '<span id="bmlt_admin_template_delete_ajax_button_throbber_span" class="bmlt_admin_ajax_button_throbber_span item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                if ( $this->my_user->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN )
                    {
                    $ret .= '<span class="perm_checkbox_span">';
                        $ret .= '<input type="checkbox" id="bmlt_admin_meeting_template_delete_perm_checkbox" />';
                        $ret .= '<label for="bmlt_admin_meeting_template_delete_perm_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_delete_perm_checkbox'] ).'</label>';
                    $ret .= '</span>';
                    }
            $ret .= '</span>';
            $ret .= '<span class="bmlt_admin_meeting_editor_form_meeting_button_right_span"><a id="bmlt_admin_meeting_editor_form_meeting_template_cancel_button" href="javascript:admin_handler_object.cancelMeetingEdit(template)" class="bmlt_admin_ajax_button button">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_cancel_button'] ).'</a></span>';
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
                    $ret .= '<span class="bmlt_admin_value_left" id="bmlt_admin_meeting_id_template_display"></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_name_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_name_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_name_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.setItemValue(this, template, \'meeting_name\');" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_weekday_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left">';
                        $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_weekday_select" onchange="admin_handler_object.setItemValue(this, template, \'weekday_tinyint\')">';
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
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_cc_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_cc_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.setItemValue(this, template, \'world_id_mixed\');" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                if ( count ( $this->my_service_bodies ) > 1 )
                    {
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_sb_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left">';
                            $ret .= '<select id="bmlt_admin_single_meeting_editor_template_meeting_sb_select">';
                                $ret .= '<option value="0" disabled="disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_sb_default_value'] ).'</option>';
                                $ret .= '<option value="" disabled="disabled"></option>';
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
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_contact_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_contact_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.setItemValue(this, template, \'email_contact\');" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<span class="bmlt_admin_visibility_advice_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_visibility_advice'] ).'</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</div>';
            }
        else
            {
            die ( 'NOT AUTHORIZED' );
            };
        
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
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_longitude_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_longitude_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.setItemValue(this, template, \'longitude\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this, true);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_latitude_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_latitude_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_latitude_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.setItemValue(this, template, \'latitude\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this, true);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</div>';
            $ret .= '<div class="bmlt_admin_meeting_editor_address_div">';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_location_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_location_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_location_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.setItemValue(this, template, \'location_text\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_info_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_info_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_info_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.setItemValue(this, template, \'location_info\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_street_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_street_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_street_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.handleNewAddressInfo(template);admin_handler_object.setItemValue(this, template, \'location_street\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_neighborhood_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_neighborhood_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_neighborhood_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.handleNewAddressInfo(template);admin_handler_object.setItemValue(this, template, \'location_neighborhood\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_borough_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_borough_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_borough_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.handleNewAddressInfo(template);admin_handler_object.setItemValue(this, template, \'location_city_subsection\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_city_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_city_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_city_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.handleNewAddressInfo(template);admin_handler_object.setItemValue(this, template, \'location_municipality\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_county_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_county_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_county_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.handleNewAddressInfo(template);admin_handler_object.setItemValue(this, template, \'location_sub_province\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_state_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_state_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_state_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.handleNewAddressInfo(template);admin_handler_object.setItemValue(this, template, \'location_province\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_zip_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_zip_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_zip_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.handleNewAddressInfo(template);admin_handler_object.setItemValue(this, template, \'location_postal_code_1\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this, true);" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_nation_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input id="bmlt_admin_single_meeting_editor_template_meeting_nation_text_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_meeting_nation_prompt'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.handleNewAddressInfo(template);admin_handler_object.setItemValue(this, template, \'location_nation\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
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
                $ret .= '<div class="clear_both"></div>';
                foreach ( $this->my_formats[$this->my_server->GetLocalLang()] as $format )
                    {
                    if ( $format instanceof c_comdef_format )
                        {
                        $ret .= '<div class="bmlt_admin_meeting_one_format_div">';
                            $ret .= '<label class="left_label" for="bmlt_admin_meeting_template_format_'.$format->GetSharedID().'_checkbox">'.htmlspecialchars ( $format->GetKey() ).'</label>';
                            $ret .= '<span><input type="checkbox" value="'.$format->GetKey().'" id="bmlt_admin_meeting_template_format_'.$format->GetSharedID().'_checkbox" onchange="admin_handler_object.reactToFormatCheckbox(this, template)" onclick="admin_handler_object.reactToFormatCheckbox(this, template)" /></span>';
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
                                $ret .= '<input id="bmlt_admin_single_meeting_editor_template_meeting_'.htmlspecialchars ( $key ).'_text_input" type="text" onkeyup="admin_handler_object.handleTextInputChange(this, 0);admin_handler_object.setItemValue(this, template, \''.htmlspecialchars ( $key ).'\')" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this, true);" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_editor_screen_default_text_prompt'] ).'" />';
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
            $ret .= '<a class="bmlt_admin_user_account_disclosure_a" href="javascript:admin_handler_object.toggleAccountInfo()">';
                $ret .= htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_disclosure'] );
            $ret .= '</a>';
        $ret .= '</div>';
        $ret .= '<div id="bmlt_admin_user_account_wrapper_div" class="bmlt_admin_user_account_wrapper_div bmlt_admin_user_account_wrapper_div_hidden">';
            $ret .= '<div class="bmlt_admin_user_account_banner_div">';
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
                    $ret .= '<span class="bmlt_admin_value_left">'.htmlspecialchars ( $this->my_user->GetLocalName() ).'</span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_login_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left">'.htmlspecialchars ( $this->my_user->GetLogin() ).'</span>';
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
                            $ret .= '<p>'.htmlspecialchars ( $this->my_service_bodies[$c]->GetLocalName() );
                            
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
                    $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_email_input" id="bmlt_admin_user_email_input" type="text" value="'.htmlspecialchars ( $this->my_user->GetEmailAddress() ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_email_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['email_address_default_text'] ).'\');</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_description_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><textarea cols="40" rows="10" name="bmlt_admin_user_description_textarea" id="bmlt_admin_user_description_textarea" class="bmlt_text_item" onkeyup="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);">'.htmlspecialchars ( $this->my_user->GetLocalDescription() ).'</textarea></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_description_textarea\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_description_default_text'] ).'\');</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['change_password_label'] ).'</span>';
                    $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_account_password_input" id="bmlt_admin_user_account_password_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['change_password_default_text'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                    $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_account_password_input\'));</script>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">&nbsp;</span>';
                    $ret .= '<span id="bmlt_admin_account_change_ajax_button_span" class="bmlt_admin_value_left"><a id="bmlt_admin_account_change_ajax_button" href="javascript:admin_handler_object.handleAccountChange()" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_change_button_text'] ).'</a></span>';
                    $ret .= '<span id="bmlt_admin_account_change_ajax_button_throbber_span" class="bmlt_admin_value_left item_hidden"><img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" /></span>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</div>';
        $ret .= '</div>';
        
        return  $ret;
    }
};
?>