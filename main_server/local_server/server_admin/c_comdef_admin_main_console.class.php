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
                $ret .= 'var g_current_user_id = \''.htmlspecialchars ( $this->my_user->GetID() ).'\';';
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
                $ret .='<div class="bmlt_admin_meeting_editor_form_inner_div" action="">';
                    $ret .= $this->return_meeting_specification_panel();
                    $ret .= $this->return_meeting_results_panel();
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            
                if ( count ( $this->my_service_bodies ) > 1 )
                    {
                    $ret .= $this->return_meeting_service_body_selection_panel ();
                    }
            $ret .= '</div>';
            }
        
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
            $ret = '<div id="bmlt_admin_meeting_editor_service_div" class="bmlt_admin_meeting_editor_service_div">';
            $ret .= '</div>';
            }
        
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
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right"><input type="checkbox" id="bmlt_admin_meeting_search_text_is_a_location_checkbox" /></span>';
                    $ret .= '<label class="bmlt_admin_med_label_left" for="bmlt_admin_meeting_search_text_is_a_location_checkbox">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_text_location_label'] ).'</label>';
                $ret .= '</div>';
                $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                    $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_label'] ).'</span>';
                    $ret .= '<div class="bmlt_admin_value_left_div">';
                        for ( $c = 0; $c < 8; $c++ )
                            {
                            $ret .= '<span class="single_checkbox_span">';
                                $ret .= '<input type="checkbox"'.($c == 0 ? ' checked="checked"' : '').' id="bmlt_admin_meeting_search_weekday_checkbox_'.$c.'" onchange="admin_handler_object.handleWeekdayCheckBoxChanges('.$c.')" />';
                                $ret .= '<label class="bmlt_admin_med_checkbox_label_left" for="bmlt_admin_meeting_search_weekday_checkbox_'.$c.'">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['meeting_search_weekdays_names'][$c] ).'</label>';
                            $ret .= '</span>';
                            }
                    $ret .= '</div>';
                $ret .= '</div>';
            $ret .= '</form>';
        $ret .= '</div>';
        
        return $ret;
    }
    
    /********************************************************************************************************//**
    \brief This constructs the meeting search results panel of the meeting editor.
    \returns The HTML and JavaScript for the Edit Meetings Search Results section.
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
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_login_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left">'.htmlspecialchars ( $this->my_user->GetLogin() ).'</span>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_type_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_type_'.$this->my_user->GetUserLevel()] ).'</span>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_email_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_email_input" id="bmlt_admin_user_email_input" type="text" value="'.htmlspecialchars ( $this->my_user->GetEmailAddress() ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                        $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_email_input\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['email_address_default_text'] ).'\');</script>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_description_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left"><textarea name="bmlt_admin_user_description_textarea" id="bmlt_admin_user_description_textarea" class="bmlt_text_item" onkeyup="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);">'.htmlspecialchars ( $this->my_user->GetLocalDescription() ).'</textarea></span>';
                        $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_description_textarea\'),\''.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_description_default_text'] ).'\');</script>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['change_password_label'] ).'</span>';
                        $ret .= '<span class="bmlt_admin_value_left"><input name="bmlt_admin_user_account_password_input" id="bmlt_admin_user_account_password_input" type="text" value="'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['change_password_default_text'] ).'" onkeyup="admin_handler_object.handleTextInputChange(this);" onfocus="admin_handler_object.handleTextInputFocus(this);" onblur="admin_handler_object.handleTextInputBlur(this);" /></span>';
                        $ret .= '<script type="text/javascript">admin_handler_object.handleTextInputLoad(document.getElementById(\'bmlt_admin_user_account_password_input\'));</script>';
                    $ret .= '</div>';
                    $ret .= '<div class="bmlt_admin_one_line_in_a_form clear_both">';
                        $ret .= '<span class="bmlt_admin_med_label_right">&nbsp;</span>';
                        $ret .= '<span id="bmlt_admin_account_change_ajax_button_span" class="bmlt_admin_value_left"><a id="bmlt_admin_account_change_ajax_button" href="javascript:admin_handler_object.handleAccountChange()" class="bmlt_admin_ajax_button button_disabled">'.htmlspecialchars ( $this->my_localized_strings['comdef_server_admin_strings']['account_change_button_text'] ).'</a></span>';
                        $ret .= '<span id="bmlt_admin_account_change_ajax_button_throbber_span" class="bmlt_admin_value_left item_hidden"><img src="local_server/server_admin/style/images/button-throbber.gif" alt="AJAX Throbber" /></span>';
                    $ret .= '</div>';
                    $ret .= '<div class="clear_both"></div>';
                $ret .= '</div>';
            $ret .= '</form>';
        $ret .= '</div>';
        
        return  $ret;
    }
};
?>