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
            die ( '<h2>NOT AUTHORIZED</h2>' );
            }
            
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
    }
    
    /********************************************************************************************************//**
    \brief
    \returns
    ************************************************************************************************************/
    function return_main_console_html()
    {
        $ret = '<div id="bmlt_admin_main_console" class="bmlt_admin_main_console_wrapper_div">';
            // We actually include the JS directly into the HTML. This gives us a lot more flexibility as to how we deploy and gatekeep this file.
            $ret .= '<script type="text/javascript">';
                $ret .= 'var g_ajax_callback_uri = \''.htmlspecialchars ( $this->my_ajax_uri ).'\';';
                if ( defined ( '__DEBUG_MODE__' ) )
                    {
                    $ret .= "\n";
                    }
                $ret .= 'var g_current_user_id = \''.htmlspecialchars ( $this->my_user->GetID() ).'\';';
                if ( defined ( '__DEBUG_MODE__' ) )
                    {
                    $ret .= "\n";
                    }
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
                        if ( defined ( '__DEBUG_MODE__' ) )
                            {
                            $ret .= "\n";
                            }
                        }
                $ret .= '];';
                if ( defined ( '__DEBUG_MODE__' ) )
                    {
                    $ret .= "\n";
                    }
                $ret .= file_get_contents ( dirname ( __FILE__ ).(defined('__DEBUG_MODE__') ? '/' : '/js_stripper.php?filename=' ).'server_admin_javascript.js' );
            
            if ( defined('__DEBUG_MODE__') )
                {
                $ret .= "\n";
                };
            $ret .= '</script>';
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
                        $ret .= '<h2>NOT AUTHORIZED</h2>';
                    break;
                    }
                }
            
        $ret .= '</div>';
        
        return  $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the meeting editor section of the console. Most user levels (not observers) have it.
    \returns The HTML and JavaScript for the "Edit Meetings" section.
    ************************************************************************************************************/
    function return_meeting_editor_panel ()
    {
        $ret = '<h2>NOT AUTHORIZED</h2>';
        
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
                    $ret .= '<div class="bmlt_admin_fader_div item_hidden" id="bmlt_admin_fader_meeting_editor_fail_div">';
                        $ret .= '<span class="failure_text_span">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_change_fader_failure_text'] ).'</span>';
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
            $ret .='<form class="bmlt_admin_meeting_editor_specifier_form" action="">';
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
                                $ret .= '<input checked="checked" type="checkbox"'.($c == 0 ? ' checked="checked"' : '').' id="bmlt_admin_meeting_search_weekday_checkbox_'.$c.'" onchange="admin_handler_object.handleWeekdayCheckBoxChanges('.$c.')" />';
                                $ret .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_weekday_checkbox_'.$c.'">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_names'][$c] ).'</label>';
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
            $ret .= '</form>';
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
        $ret = '<h2>NOT AUTHORIZED</h2>';
        
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
                        $ret .= '<input type="checkbox" checked="checked" id="bmlt_admin_meeting_search_service_body_checkbox_'.$id.'" onchange="admin_handler_object.handleServiceCheckBoxChanges('.$id.')" />';
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
        $ret = '<div id="bmlt_admin_meeting_editor_form_div" class="bmlt_admin_meeting_editor_form_div item_hidden">';
            $ret .= '<div class="bmlt_admin_meeting_editor_form_inner_div">';
                $ret .= $this->return_new_meeting_panel();
                $ret .= $this->return_meeting_results_panel();
            $ret .= '</div>';
        $ret .= '</div>';

        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs a panel for creating new meetings that goes above the results.
    \returns The HTML and JavaScript for the New Meetings section.
    ************************************************************************************************************/
    function return_new_meeting_panel ()
    {
        $ret = '<div id="bmlt_admin_meeting_editor_form_new_meetings_div" class="bmlt_admin_meeting_editor_form_new_meetings_div">';
            $ret .='<form class="bmlt_admin_meeting_editor_new_meetings_form" action="">';
                $ret .= '<div class="bmlt_admin_meeting_editor_form_new_meetings_inner_div">';
                $ret .= '</div>';
            $ret .= '</form>';
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
            $ret .='<form class="bmlt_admin_meeting_editor_results_form" action="">';
                $ret .= '<div class="bmlt_admin_meeting_editor_form_results_inner_div">';
                $ret .= '</div>';
            $ret .= '</form>';
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
            $ret .='<form class="bmlt_admin_user_account_edit_form" id="admin_account_mod_'.htmlspecialchars ( $this->my_user->GetID() ).'_form" action="">';
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
                        $ret .= '<span class="bmlt_admin_value_left">';
                            for ( $c = 0; $c < count ( $this->my_service_bodies ); $c++ )
                                {
                                $ret .= '<p>'.htmlspecialchars ( $this->my_service_bodies[$c]->GetLocalName() );
                                
                                if ( $c < (count ( $this->my_service_bodies ) - 1) )
                                    {
                                    $ret .= ',';
                                    }
                                $ret .= '</p>';
                                }
                        $ret .= '</span>';
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
                        $ret .= '<span class="bmlt_admin_value_left"><textarea name="bmlt_admin_user_description_textarea" id="bmlt_admin_user_description_textarea" class="bmlt_text_item" onkeyup="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);">'.htmlspecialchars ( $this->my_user->GetLocalDescription() ).'</textarea></span>';
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
                        $ret .= '<span id="bmlt_admin_account_change_ajax_button_throbber_span" class="bmlt_admin_value_left item_hidden"><img src="local_server/server_admin/style/images/button-throbber.gif" alt="AJAX Throbber" /></span>';
                        $ret .= '<div class="clear_both"></div>';
                    $ret .= '</div>';
                $ret .= '</div>';
            $ret .= '</form>';
        $ret .= '</div>';
        
        return  $ret;
    }
};
?>