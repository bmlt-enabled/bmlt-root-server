/*
    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.
*/
/********************************************************************************************
*######################################### MAIN CODE #######################################*
********************************************************************************************/

function BMLT_Server_Admin()
{
    // #mark -
    // #mark ########## Class Declaration ##########
    // #mark -

    /************************************************************************************//**
    *                                     DATA MEMBERS                                      *
    ****************************************************************************************/
    var m_server_admin_panel_shown = null;      ///< This will be true if the "Server Administration" panel is exposed.
    var m_account_panel_shown = null;           ///< This will be true if the "My Account" panel is exposed.
    var m_search_specifier_shown = null;        ///< This is true, if the meeting search specifier form is shown.
    var m_meeting_editor_panel_shown = null;    ///< This will be true if the "Edit Meetings" panel is exposed.
    var m_service_body_editor_panel_shown = null;   ///< This is true, if the Service Body Editor is shown.
    var m_ajax_request_in_progress = null;      ///< This is any AJAX request currently under way.
    var m_success_fade_duration = null;         ///< Number of milliseconds for a success fader.
    var m_failure_fade_duration = null;         ///< Number of milliseconds for a failure fader.
    var m_search_results = null;                ///< This will contain any meeting search results.
    var m_meeting_results_container_div = null; ///< This will hold any search result display elements (allows easy disposal)
    var m_editing_window_open = null;           ///< If there is a meeting editor open, it is recorded here. There can only be one...
    var m_user_editor_panel_shown = null;       ///< Set to true, if the user editor is open.
    var m_warn_user_to_refresh = null;          ///< If this is true, then a warning alert will be shown to the user.
    var m_format_editor_table_rows = null;      ///< This is used to track the number of rows in the format editor table.

    /************************************************************************************//**
    *                                       METHODS                                         *
    ****************************************************************************************/

    // #mark -
    // #mark Affects All Sections
    // #mark -

    /************************************************************************************//**
    *   \brief If one of the upper sections has been edited, it can affect the Account,     *
    *          Meeting or Service Body sections. In this case, the user needs to log out,   *
    *          then back in again (or refresh the page, but signing out is easier to        *
    *          explain). This sets an orange fader in each affected section, and also sets  *
    *          the trigger for an alert that explains it.                                   *
    ****************************************************************************************/
    this.setWarningFaders = function () {
        if ( document.getElementById('bmlt_admin_fader_service_body_editor_warn_div') ) {
            document.getElementById('bmlt_admin_fader_service_body_editor_warn_div').className = 'bmlt_admin_fader_div';
        };

        if ( document.getElementById('bmlt_admin_fader_meeting_editor_warn_div') ) {
            document.getElementById('bmlt_admin_fader_meeting_editor_warn_div').className = 'bmlt_admin_fader_div';
        };

        if ( document.getElementById('bmlt_admin_fader_account_warn_div') ) {
            document.getElementById('bmlt_admin_fader_account_warn_div').className = 'bmlt_admin_fader_div';
        };

        this.m_warn_user_to_refresh = true;
    };

    /************************************************************************************//**
    *   \brief This displays that alert.                                                    *
    ****************************************************************************************/
    this.showWarningAlert = function () {
        if ( this.m_warn_user_to_refresh ) {  // Only if needed.
            alert(g_need_refresh_message_alert_text);
            this.m_warn_user_to_refresh = false;
        };
    };

    // #mark -
    // #mark Text Item Handlers
    // #mark -

    /************************************************************************************//**
    *   \brief When a text input (either <input> or <textarea> is initialized, we can set   *
    *          up a default text value that is displayed when the item is empty and not in  *
    *          focus. If we don't send in a specific value, then the current value of the   *
    *          text item is considered to be the default.                                   *
    ****************************************************************************************/
    this.handleTextInputLoad = function (
        in_text_item,
        in_default_value,
        in_small
    ) {
        if ( in_text_item ) {
            in_text_item.original_value = in_text_item.value;
            if ( !in_text_item.small ) {
                in_text_item.small = false;
            };

            if ( !in_text_item.tiny ) {
                in_text_item.tiny = false;
            };

            in_text_item.small = in_text_item.small || in_small;

            if ( in_default_value != null ) {
                in_text_item.defaultValue = in_default_value;
            } else {
                in_text_item.defaultValue = in_text_item.value;
            };

            in_text_item.value = in_text_item.original_value;

            if ( !in_text_item.value || (in_text_item.value == in_text_item.defaultValue) ) {
                in_text_item.value = in_text_item.defaultValue;
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : ''))) + ' bmlt_text_item_dimmed';
            } else {
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : '')));
            };
        };
    };

    /************************************************************************************//**
    *   \brief This just makes sure that the className is correct.                          *
    ****************************************************************************************/
    this.setTextItemClass = function (
        in_text_item,   ///< This is the text item to check.
        is_focused      ///< true, if the item is in focus
    ) {
        if ( in_text_item ) {
            if ( !is_focused && ((in_text_item.value == null) || (in_text_item.value == in_text_item.defaultValue)) ) {
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : ''))) + ' bmlt_text_item_dimmed';
            } else {
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : '')));
            };
        };
    };

    /************************************************************************************//**
    *   \brief When a text item receives focus, we clear any default text.                  *
    ****************************************************************************************/
    this.handleTextInputFocus = function (   in_text_item
                                        ) {
        if ( in_text_item ) {
            if ( in_text_item.value == in_text_item.defaultValue ) {
                in_text_item.value = '';
            };

            this.setTextItemClass(in_text_item, true);
            this.validateAccountGoButton();
        };
    };

    /************************************************************************************//**
    *   \brief When a text item loses focus, we restore any default text, if the item was   *
    *          left empty.                                                                  *
    ****************************************************************************************/
    this.handleTextInputBlur = function (    in_text_item
                                        ) {
        if ( in_text_item ) {
            if ( !in_text_item.value ) {
                in_text_item.value = in_text_item.defaultValue;
            };

            this.setTextItemClass(in_text_item, false);
            this.validateAccountGoButton();
        };
    };

    /************************************************************************************//**
    *   \brief This is called when executing a paste. It avoids leaving in the default text.*
    ****************************************************************************************/
    this.handleTextInputPaste = function (    in_text_item
                                        ) {
        if ( in_text_item ) {
            this.setTextItemClass(in_text_item, true);
            this.validateAccountGoButton();
        };
    };

    /************************************************************************************//**
    *   \brief When a text item has its text changed, we check to see if it needs to have   *
    *          its classname changed to the default (usually won't make a difference, as    *
    *          the text item will be in focus, anyway).                                     *
    ****************************************************************************************/
    this.handleTextInputChange = function (
        in_text_item,
        in_meeting_id
    ) {
        if ( in_text_item ) {
            this.validateAccountGoButton();
            this.validateMeetingEditorButton(in_meeting_id);
        };
    };

    /************************************************************************************//**
    *   \brief When a text item has its text changed, we check to see if it needs to have   *
    *          its classname changed to the default (usually won't make a difference, as    *
    *          the text item will be in focus, anyway).                                     *
    ****************************************************************************************/
    this.setItemValue = function (
        in_item,
        in_meeting_id,
        in_value_field
    ) {
        var eval_str = '';

        if ( (null == in_meeting_id) && (null != in_item.meeting_id) ) {
            in_meeting_id = in_item.meeting_id;
        };

        if ( !in_value_field ) {
            in_value_field = in_item.element;
        };

        var editor_object_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id.toString() + '_div';
        var editor_object = document.getElementById(editor_object_id);

        if ( (in_item.type == 'text') || (in_item.name == 'textarea') ) {
            var value = in_item.value.toString();

            value = value.replace(/'/g, "\\'");  // Make sure to escape apostrophes.

            if ( value && (value != in_item.defaultValue) ) {
                eval_str = 'editor_object.meeting_object.' + in_value_field + ' = \'' + value + '\';';
            } else {
                if ( 0 == in_meeting_id ) {
                    eval_str = 'delete editor_object.meeting_object.' + in_value_field + ';';
                } else {
                    eval_str = 'editor_object.meeting_object.' + in_value_field + ' = \'\';';
                };
            };
        };

        if ( eval_str ) {
            eval(eval_str);
        };

        if ( in_item ) {
            this.validateAccountGoButton();
            this.validateMeetingEditorButton(in_meeting_id);
            this.handleNewAddressInfo(in_meeting_id);
        };
    };

    // #mark -
    // #mark ########## Account Info Section ##########
    // #mark -

    /************************************************************************************//**
     *   \brief  Toggles the selected item of the Server Administration section.            *
     ****************************************************************************************/
    this.toggleServerAdminSelect = function () {
        var selectedValue = document.getElementById('bmlt_admin_server_admin_select').value;
        var updateWorldIdsDiv = document.getElementById('bmlt_admin_server_admin_update_world_ids_edit_form_inner_div');
        var nawsImportDiv = document.getElementById('bmlt_admin_server_admin_naws_import_edit_form_inner_div');
        if (selectedValue === "naws_import") {
            updateWorldIdsDiv.className = 'bmlt_admin_server_admin_update_world_ids_edit_form_inner_div item_hidden';
            nawsImportDiv.className = 'bmlt_admin_server_admin_naws_import_edit_form_inner_div';
        } else {
            updateWorldIdsDiv.className = 'bmlt_admin_server_admin_update_world_ids_edit_form_inner_div';
            nawsImportDiv.className = 'bmlt_admin_server_admin_naws_import_edit_form_inner_div item_hidden';
        }
    };

    /************************************************************************************//**
    *   \brief  Toggles the visibility of the Server Administration section.                *
    ****************************************************************************************/
    this.toggleServerAdmin = function () {
        this.m_server_admin_panel_shown = !this.m_server_admin_panel_shown;

        var the_disclosure_div = document.getElementById('bmlt_admin_server_admin_disclosure_div');
        var the_account_info_div = document.getElementById('bmlt_admin_server_admin_wrapper_div');

        if ( this.m_server_admin_panel_shown) {
            the_disclosure_div.className = 'bmlt_admin_server_admin_disclosure_div bmlt_admin_server_admin_disclosure_div_open';
            the_account_info_div.className = 'bmlt_admin_server_admin_wrapper_div';
        } else {
            the_disclosure_div.className = 'bmlt_admin_server_admin_disclosure_div bmlt_admin_server_admin_disclosure_div_closed';
            the_account_info_div.className = 'bmlt_admin_server_admin_wrapper_div bmlt_admin_server_admin_wrapper_div_hidden';
        }
    };

    /************************************************************************************//**
    *   \brief  Toggles the visibility of the account info section.                         *
    ****************************************************************************************/
    this.toggleAccountInfo = function () {
        this.m_account_panel_shown = !this.m_account_panel_shown;
        
        var the_disclosure_div = document.getElementById('bmlt_admin_user_account_disclosure_div');
        var the_account_info_div = document.getElementById('bmlt_admin_user_account_wrapper_div');
        
        if ( this.m_account_panel_shown) {
            var email_field = document.getElementById('bmlt_admin_user_email_input');
            var description_field = document.getElementById('bmlt_admin_user_description_textarea');
            var password_field = document.getElementById('bmlt_admin_user_account_password_input');
            
            this.handleTextInputBlur(email_field);
            this.handleTextInputBlur(description_field);
            this.handleTextInputBlur(password_field);
            
            the_disclosure_div.className = 'bmlt_admin_user_account_disclosure_div bmlt_admin_user_account_disclosure_div_open';
            the_account_info_div.className = 'bmlt_admin_user_account_wrapper_div';
            this.showWarningAlert();
        } else {
            the_disclosure_div.className = 'bmlt_admin_user_account_disclosure_div bmlt_admin_user_account_disclosure_div_closed';
            the_account_info_div.className = 'bmlt_admin_user_account_wrapper_div bmlt_admin_user_account_wrapper_div_hidden';
        };
    };
    
    /************************************************************************************//**
    *   \brief This checks the values of the text items in the My Account section. If any   *
    *          of them are different from their default, we enable the GO button.           *
    ****************************************************************************************/
    this.validateAccountGoButton = function () {
        var email_field = document.getElementById('bmlt_admin_user_email_input');
        var password_field = document.getElementById('bmlt_admin_user_account_password_input');
        var ajax_button = document.getElementById('bmlt_admin_account_change_ajax_button');
        var description = document.getElementById('bmlt_admin_user_description_textarea');
        var name_field = document.getElementById('bmlt_admin_user_name_input');
        var login_field = document.getElementById('bmlt_admin_user_login_input');
        
        if ( email_field && password_field && ajax_button && description ) {
            if (    (email_field.value != email_field.original_value && email_field.value != email_field.defaultValue)
                ||  (description.value != description.original_value && description.value != description.defaultValue)
                ||  (name_field && (name_field.value != name_field.original_value))
                ||  (login_field && (login_field.value != login_field.original_value))
                ||  (password_field.value && (password_field.value != password_field.defaultValue)) ) {
                ajax_button.className = 'bmlt_admin_ajax_button';
            } else {
                ajax_button.className = 'bmlt_admin_ajax_button button_disabled';
            };
        };
    };

    /************************************************************************************//**
    *   \brief This is called when the World ID update file input changes                   *
    ****************************************************************************************/
    this.handleWorldIDFileInputChange = function() {
        var file_input = document.getElementById('bmlt_admin_naws_spreadsheet_file_input');
        var save_button = document.getElementById('bmlt_admin_update_world_ids_ajax_button');

        if (file_input.files && file_input.files.length > 0) {
            save_button.className = 'bmlt_admin_ajax_button';
        } else {
            save_button.className = 'bmlt_admin_ajax_button button_disabled';
        }
    };

    /************************************************************************************//**
     *   \brief This is called when the NAWS Import file input changes                   *
     ****************************************************************************************/
    this.handleNAWSImportFileInputChange = function() {
        var file_input = document.getElementById('bmlt_admin_naws_import_file_input');
        var save_button = document.getElementById('bmlt_admin_naws_import_ajax_button');

        if (file_input.files && file_input.files.length > 0) {
            save_button.className = 'bmlt_admin_ajax_button';
        } else {
            save_button.className = 'bmlt_admin_ajax_button button_disabled';
        }
    };

    /************************************************************************************//**
    *   \brief This is called to initiate an AJAX process to update world IDs from file     *
    ****************************************************************************************/
    this.handleUpdateWorldIDsFromSpreadsheet = function() {
        var file_input = document.getElementById('bmlt_admin_naws_spreadsheet_file_input');
        var save_button = document.getElementById('bmlt_admin_update_world_ids_ajax_button');
        if (!file_input || !file_input.files || !file_input.files.length) {
            return;
        }

        if ( this.m_ajax_request_in_progress ) {
            this.m_ajax_request_in_progress.abort();
            this.m_ajax_request_in_progress = null;
        }

        this.m_ajax_request_in_progress = BMLT_AjaxRequest_FileUpload(
            g_ajax_callback_uri + '&do_update_world_ids=1',
            function(response) {admin_handler_object.handleUpdateWorldIDsFromSpreadsheetCallback(response);},
            file_input.files[0]
        );
        this.setUpdateWorldIDsThrobber(true);
        save_button.className = 'bmlt_admin_ajax_button button_disabled';
    };

    this.handleUpdateWorldIDsFromSpreadsheetCallback = function(response) {
        var file_input = document.getElementById('bmlt_admin_naws_spreadsheet_file_input');
        var save_button = document.getElementById('bmlt_admin_update_world_ids_ajax_button');
        this.setUpdateWorldIDsThrobber(false);
        file_input.value = '';
        save_button.className = 'bmlt_admin_ajax_button button_disabled';

        if (response && response.responseText) {
            if (response.responseText === 'NOT AUTHORIZED') {
                alert(g_AJAX_Auth_Failure);
                return;
            }

            eval('var result = ' + response.responseText + ';');
            if (!result) {
                return;
            }

            if (result.success) {
                var report = g_num_meetings_updated_text + result.report.updated.length.toString() + "\n\n";
                report += g_num_meetings_not_updated_text + result.report.not_updated.length.toString();
                if (result.report.not_found.length) {
                    report += "\n\n";
                    report += g_warning_text + ": " + result.report.not_found.length.toString() + " " + g_meetings_not_found_text + result.report.not_found.join(", ");
                }
                alert(report);
            } else {
                alert(g_errors_text + ":\n" + result.errors.join("\n"));
            }
        }
    };

    /************************************************************************************//**
     *   \brief This is called to initiate an AJAX process to update world IDs from file     *
     ****************************************************************************************/
    this.handleNAWSImport = function() {
        var file_input = document.getElementById('bmlt_admin_naws_import_file_input');
        var save_button = document.getElementById('bmlt_admin_naws_import_ajax_button');
        if (!file_input || !file_input.files || !file_input.files.length) {
            return;
        }
    
        if ( this.m_ajax_request_in_progress ) {
            this.m_ajax_request_in_progress.abort();
            this.m_ajax_request_in_progress = null;
        }
    
        this.m_ajax_request_in_progress = BMLT_AjaxRequest_FileUpload(
            g_ajax_callback_uri + '&do_naws_import=1',
            function(response) {admin_handler_object.handleNAWSImportCallback(response);},
            file_input.files[0]
        );
        this.setNAWSImportThrobber(true);
        save_button.className = 'bmlt_admin_ajax_button button_disabled';
    };

    this.handleNAWSImportCallback = function(response) {
        var file_input = document.getElementById('bmlt_admin_naws_import_file_input');
        var save_button = document.getElementById('bmlt_admin_naws_import_ajax_button');
        this.setNAWSImportThrobber(false);
        file_input.value = '';
        save_button.className = 'bmlt_admin_ajax_button button_disabled';

        if (response && response.responseText) {
            if (response.responseText === 'NOT AUTHORIZED') {
                alert(g_AJAX_Auth_Failure);
                return;
            }

            eval('var result = ' + response.responseText + ';');
            if (!result) {
                return;
            }

            if (result.success) {
                var report = g_service_bodies_created_text + result.report.num_service_bodies_created.toString() + "\n\n";
                report += g_users_created_text + result.report.num_users_created.toString() + "\n\n";
                report += g_meetings_created_text + result.report.num_meetings_created.toString() + "\n\n";
                report += g_server_admin_ui_refresh_ui_text;
                alert(report);
            } else {
                alert(g_errors_text + ": " + result.errors);
            }
        }
    };

    /************************************************************************************//**
    *   \brief Displays or hides the AJAX Throbber for the Update World IDs button          *
    ****************************************************************************************/
    this.setUpdateWorldIDsThrobber = function(visible) {
        var button_span = document.getElementById('bmlt_admin_update_world_ids_ajax_button_span');
        var throbber_span = document.getElementById('bmlt_admin_update_world_ids_ajax_button_throbber_span');
        throbber_span.className = 'bmlt_admin_value_left' + (visible ? '' : ' item_hidden');
        button_span.className = 'bmlt_admin_value_left' + (visible ? ' item_hidden' : '');
    };

    /************************************************************************************//**
     *   \brief Displays or hides the AJAX Throbber for the Update World IDs button          *
     ****************************************************************************************/
    this.setNAWSImportThrobber = function(visible) {
        var button_span = document.getElementById('bmlt_admin_naws_import_ajax_button_span');
        var throbber_span = document.getElementById('bmlt_admin_naws_import_ajax_button_throbber_span');
        throbber_span.className = 'bmlt_admin_value_left' + (visible ? '' : ' item_hidden');
        button_span.className = 'bmlt_admin_value_left' + (visible ? ' item_hidden' : '');
    };


    /************************************************************************************//**
    *   \brief This is called to initiate an AJAX process to change the account settings.   *
    ****************************************************************************************/
    this.handleAccountChange = function () {
        var email_field = document.getElementById('bmlt_admin_user_email_input');
        var password_field = document.getElementById('bmlt_admin_user_account_password_input');
        var description = document.getElementById('bmlt_admin_user_description_textarea');
        var affected_user_id = document.getElementById('account_affected_user_id');
        var name_field = document.getElementById('bmlt_admin_user_name_input');
        var login_field = document.getElementById('bmlt_admin_user_login_input');

        // We only do something if there is a difference.
        if (    (affected_user_id.value == g_current_user_id)   // Belt and suspenders...
            &&  ((email_field.value != email_field.original_value)
            ||  (description.value != description.original_value)
            ||  (name_field && (name_field.value != name_field.original_value))
            ||  (login_field && (login_field.value != login_field.original_value))
            ||  (password_field.value && (password_field.value != password_field.defaultValue)))
            ) {
            if ( g_min_pw_len && (password_field.value && (password_field.value != password_field.defaultValue)) && (password_field.value.length < g_min_pw_len) ) {
                alert(sprintf(g_min_password_length_string, g_min_pw_len));
            } else {
                this.setMyAccountThrobber(true);
                var uri = g_ajax_callback_uri + '&target_user=' + encodeURIComponent(g_current_user_id);
                if ( name_field && (name_field.value != name_field.original_value) ) {
                    uri += '&account_name_value=' + encodeURIComponent(name_field.value);
                };
            
                if ( login_field && (login_field.value != login_field.original_value) ) {
                    uri += '&account_login_value=' + encodeURIComponent(login_field.value);
                };
            
                if ( email_field.value != email_field.original_value ) {
                    uri += '&account_email_value=' + encodeURIComponent(email_field.value);
                };
            
                if ( description.value != description.original_value ) {
                    uri += '&account_description_value=' + encodeURIComponent(description.value);
                };
            
                if ( password_field.value && (password_field.value != password_field.defaultValue) ) {
                    uri += '&account_password_value=' + encodeURIComponent(password_field.value);
                };
            
                if ( this.m_ajax_request_in_progress ) {
                    this.m_ajax_request_in_progress.abort();
                    this.m_ajax_request_in_progress = null;
                };
            
                var salt = new Date();
                uri += '&salt=' + salt.getTime();
            
                this.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req) {
                    admin_handler_object.handleAccountChangeAJAXCallback(in_req); }, 'post');
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief This is called to initiate an AJAX process to change the account settings.   *
    ****************************************************************************************/
    this.handleAccountChangeAJAXCallback = function (in_http_request
                                                    ) {
        var email_field = document.getElementById('bmlt_admin_user_email_input');
        var password_field = document.getElementById('bmlt_admin_user_account_password_input');
        var description = document.getElementById('bmlt_admin_user_description_textarea');
        
        this.m_ajax_request_in_progress = null;
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
        };
            
        if ( json_object.ACCOUNT_CHANGED ) {
            var success = true;
            
            if ( json_object.ACCOUNT_CHANGED.EMAIL_CHANGED == true ) {
                email_field.original_value = email_field.value;
            } else if ( json_object.ACCOUNT_CHANGED.EMAIL_CHANGED == false ) {
                success = false;
            };

            if ( json_object.ACCOUNT_CHANGED.DESCRIPTION_CHANGED == true ) {
                description.original_value = description.value;
            } else if ( json_object.ACCOUNT_CHANGED.DESCRIPTION_CHANGED == false ) {
                success = false;
            };
            
            var reload = false;
            if ( json_object.ACCOUNT_CHANGED.PASSWORD_CHANGED == true ) {
                reload = true;
            } else if ( json_object.ACCOUNT_CHANGED.PASSWORD_CHANGED == false ) {
                success = false;
            };
            
            if ( json_object.ACCOUNT_CHANGED.PASSWORD_CHANGED == true ) {
                reload = true;
            } else if ( json_object.ACCOUNT_CHANGED.PASSWORD_CHANGED == false ) {
                success = false;
            };
            
            if ( reload ) {
                window.location.href = g_logout_uri;
            };
            
            password_field.value = '';
            this.validateAccountGoButton();
            
            if ( success ) {
                BMLT_Admin_StartFader('bmlt_admin_fader_account_success_div', this.m_success_fade_duration);
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_account_fail_div', this.m_failure_fade_duration);
            };
        } else {
            BMLT_Admin_StartFader('bmlt_admin_fader_account_fail_div', this.m_failure_fade_duration);
        };
        
        this.handleTextInputBlur(email_field);
        this.handleTextInputBlur(password_field);
        this.handleTextInputBlur(description);
        
        this.setMyAccountThrobber(false);
    };
    
    /************************************************************************************//**
    *   \brief Displays or hides the AJAX Throbber for the My Account button.               *
    ****************************************************************************************/
    this.setMyAccountThrobber = function (   in_shown    ///< If true, the throbber is show. If false, it is hidden.
                                        ) {
        var button_span = document.getElementById('bmlt_admin_account_change_ajax_button_span');
        var throbber_span = document.getElementById('bmlt_admin_account_change_ajax_button_throbber_span');
        
        throbber_span.className = 'bmlt_admin_value_left' + (in_shown ? '' : ' item_hidden');
        button_span.className = 'bmlt_admin_value_left' + (in_shown ? ' item_hidden' : '');
    };
        
    // #mark -
    // #mark ########## Meeting Editor Section ##########
    // #mark -

    /************************************************************************************//**
    *   \brief  This opens the editor to a particular meeting, as given by an ID.           *
    ****************************************************************************************/
    this.openMeetingForEditing = function ( in_meeting_id   ///< The ID of the meeting to be opened.
                                            ) {
        this.m_meeting_editor_panel_shown = false;
        this.toggleMeetingEditor();
        var uri = g_ajax_callback_uri + '&do_meeting_search=1&simple_other_fields=1&SearchStringAll=1&SearchString=' + parseInt(in_meeting_id);
        this.clearSearchResults();
        this.callRootServerForMeetingSearch(uri);
    };
        
    /************************************************************************************//**
    *   \brief  Toggles the visibility of the meeting editor section.                       *
    ****************************************************************************************/
    this.toggleMeetingEditor = function () {
        this.m_meeting_editor_panel_shown = !this.m_meeting_editor_panel_shown;
        
        var the_disclosure_div = document.getElementById('bmlt_admin_meeting_editor_disclosure_div');
        var the_editor_div = document.getElementById('bmlt_admin_meeting_editor_wrapper_div');
        
        if ( this.m_meeting_editor_panel_shown ) {
            the_disclosure_div.className = 'bmlt_admin_meeting_editor_disclosure_div bmlt_admin_meeting_editor_disclosure_div_open';
            the_editor_div.className = 'bmlt_admin_meeting_editor_wrapper_div';
            this.showWarningAlert();
        } else {
            the_disclosure_div.className = 'bmlt_admin_meeting_editor_disclosure_div bmlt_admin_meeting_editor_disclosure_div_closed';
            the_editor_div.className = 'bmlt_admin_meeting_editor_wrapper_div bmlt_admin_meeting_editor_wrapper_div_hidden';
        };
    };
    
    /************************************************************************************//**
    *   \brief  Returns an object with the meeting data for the meeting ID passed in.       *
    *   \returns a meeting object. Null if none found, or invalid ID.                       *
    ****************************************************************************************/
    this.getMeetingObjectById = function (
        in_meeting_id,  ///< The ID of the meeting to fetch
        in_as_a_copy    ///< If true, then the returned meeting object will be a clone (new object).
    ) {
        var ret = null;
        
        if ( in_meeting_id && this.m_search_results && this.m_search_results.length ) {
            for (var c = 0; c < this.m_search_results.length; c++) {
                if ( in_meeting_id == this.m_search_results[c].id_bigint ) {
                    var obj = this.m_search_results[c];
                    
                    if ( in_as_a_copy ) {
                        ret = JSON.parse(JSON.stringify(obj));
                    } else {
                        ret = obj;
                    };
                    break;
                };
            };
        };
        
        if ( !ret ) { // If we did not find the meeting, we create a placeholder for it.
            ret = new Object;
            ret.longitude = g_default_longitude;
            ret.latitude = g_default_latitude;
            ret.start_time = g_default_meeting_start_time;

            var dur = g_default_meeting_duration.split(':');
            dur[0] = parseInt(dur[0], 10);
            dur[1] = parseInt(dur[1], 10);
            ret.duration_time = sprintf('%02d:%02d:00', dur[0], dur[1]);

            ret.weekday_tinyint = g_default_meeting_weekday.toString();
            ret.id_bigint = 0;  // All new meetings are ID 0.
            ret.published = g_default_meeting_published;
            ret.service_body_bigint = g_service_bodies_array[0][0].toString();
            ret.formats = '';
            ret.format_shared_id_list = '';
        };
        
        ret.zoom = g_default_zoom;
        
        return ret;
    };
    
    // #mark -
    // #mark Search For Meetings Tab
    // #mark -
    
    /************************************************************************************//**
    *   \brief  Selecte the search specifier tab.                                           *
    ****************************************************************************************/
    this.selectSearchSpecifierTab = function () {
        var tab_specifier_element = document.getElementById('bmlt_admin_meeting_editor_tab_specifier_div');
        var tab_editor_element = document.getElementById('bmlt_admin_meeting_editor_tab_results_div');
        var tab_specifier_link = document.getElementById('bmlt_admin_meeting_editor_tab_specifier_a');
        var tab_editor_link = document.getElementById('bmlt_admin_meeting_editor_tab_results_a');
        var search_specifier_element = document.getElementById('bmlt_admin_meeting_editor_form_specifier_div');
        var meeting_editor_element = document.getElementById('bmlt_admin_meeting_editor_form_div');
        
        tab_specifier_element.className = 'bmlt_admin_tab_div_left bmlt_admin_tab_div_selected';
        tab_editor_element.className = 'bmlt_admin_tab_div_right bmlt_admin_tab_div_not_selected';
        
        tab_editor_link.setAttribute('href', '');
        tab_editor_link.href = 'javascript:admin_handler_object.selectMeetingEditorTab()';
        tab_specifier_link.removeAttribute("href");
        
        search_specifier_element.className = 'bmlt_admin_meeting_editor_form_specifier_div';
        meeting_editor_element.className = 'bmlt_admin_meeting_editor_form_div item_hidden';
    }
    
    /************************************************************************************//**
    *   \brief  This makes sure that the "All" checkbox syncs with the weekdays.            *
    ****************************************************************************************/
    this.handleWeekdayCheckBoxChanges = function (   in_checkbox_index ///< The checkbox that triggered the call.
                                                ) {
        var all_checkbox = document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_0');
        var weekday_checkboxes = new Array(
            document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_1'),
            document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_2'),
            document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_3'),
            document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_4'),
            document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_5'),
            document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_6'),
            document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_7')
        );
        
        if ( in_checkbox_index ) {
            var weekday_selected = true;
            for (var c = 0; c < 7; c++) {
                if ( !weekday_checkboxes[c].checked ) {
                    weekday_selected = false;
                };
            };
            
            all_checkbox.checked = weekday_selected;
        } else {
            for (var c = 0; c < 7; c++) {
                weekday_checkboxes[c].checked = all_checkbox.checked;
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief  This handles Service body checkboxes.                                       *
    ****************************************************************************************/
    this.handleServiceCheckBoxChanges = function (   in_service_body_id ///< The checkbox that triggered the call.
                                                ) {
        var the_checkbox = document.getElementById('bmlt_admin_meeting_search_service_body_checkbox_' + in_service_body_id);
        
        if ( the_checkbox ) {
            var my_children = this.getServiceBodyChildren(in_service_body_id);
            for (var c = 0; my_children && (c < my_children.length); c++) {
                var child_id = my_children[c][0];
                var child_checkbox = document.getElementById('bmlt_admin_meeting_search_service_body_checkbox_' + child_id);
                if ( child_checkbox ) {
                    child_checkbox.checked = the_checkbox.checked;
                    this.handleServiceCheckBoxChanges(child_id);
                };
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief  This returns the Service body ID of the given Service body ID.              *
    *   \returns an integer that is the ID of the parent Service body.                      *
    ****************************************************************************************/
    this.getServiceBodyParentID = function ( in_service_body_id
                                            ) {
        var the_object = null;
        
        for (var c = 0; c < g_service_bodies_array.length; c++) {
            if ( g_service_bodies_array[c][0] == in_service_body_id ) {
                the_object = g_service_bodies_array[c];
                break;
            };
        };
        
        return the_object[1];
    };
    
    /************************************************************************************//**
    *   \brief  Returns an array of Service body objects that are direct descendants of the *
    *           given Service body, as specified by ID.                                     *
    *   \returns an array of Service body objects.                                          *
    ****************************************************************************************/
    this.getServiceBodyChildren = function ( in_service_body_id
                                            ) {
        var ret_array = null;
        
        for (var c = 0; c < g_service_bodies_array.length; c++) {
            if ( this.getServiceBodyParentID(g_service_bodies_array[c][0]) == in_service_body_id ) {
                if ( ! ret_array ) {
                    ret_array = new Array();
                };
                
                ret_array[ret_array.length] = g_service_bodies_array[c];
            };
        };
        
        return ret_array;
    };
    
    /************************************************************************************//**
    *   \brief  Returns the name of the given Service body, by ID.                          *
    *   \returns a string.                                                                  *
    ****************************************************************************************/
    this.getServiceBodyName = function ( in_service_body_id
                                        ) {
        var the_object = null;
        
        for (var c = 0; c < g_service_bodies_array.length; c++) {
            if ( g_service_bodies_array[c][0] == in_service_body_id ) {
                the_object = g_service_bodies_array[c];
                break;
            };
        };
        
        return the_object[2];
    };
    
    /************************************************************************************//**
    *   \brief  Displays the Search Results or specifier, dependent upon the switch.        *
    ****************************************************************************************/
    this.setSearchResultsVisibility = function () {
        var search_specifier_div = document.getElementById('bmlt_admin_meeting_editor_form_specifier_div');
        var search_results_div = document.getElementById('bmlt_admin_meeting_editor_form_results_div');
        
        if ( this.m_search_specifier_shown ) {
            search_specifier_div.className = 'bmlt_admin_meeting_editor_form_specifier_div';
            search_results_div.className = 'bmlt_admin_meeting_editor_form_results_div item_hidden';
        } else {
            search_specifier_div.className = 'bmlt_admin_meeting_editor_form_specifier_div item_hidden';
            search_results_div.className = 'bmlt_admin_meeting_editor_form_results_div';
        };
    };
    
    // #mark -
    // #mark Do A Meeting Search
    // #mark -
    
    /************************************************************************************//**
    *   \brief  Reacts to the "Search For Meetings" button being hit.                       *
    ****************************************************************************************/
    this.searchForMeetings = function () {
        var button_span = document.getElementById('bmlt_admin_meeting_search_ajax_button_span');
        var throbber_span = document.getElementById('bmlt_admin_meeting_search_ajax_button_throbber_span');
        
        var uri = this.createSearchURI();

        button_span.className = 'bmlt_admin_value_left item_hidden';
        throbber_span.className = 'bmlt_admin_value_left';
        
        this.clearSearchResults();
        this.callRootServerForMeetingSearch(uri);
    };
    
    
    /************************************************************************************//**
    *   \brief Clears any previous search results.                                          *
    ****************************************************************************************/
    this.clearSearchResults = function () {
        var the_outer_container = document.getElementById('bmlt_admin_meeting_editor_form_results_inner_div');
        var the_main_results_display = document.getElementById('bmlt_admin_meeting_editor_form_results_div');
        
        if ( this.m_meeting_results_container_div ) { // Make sure we're starting from scratch.
            this.m_meeting_results_container_div.innerHTML = '';
            this.m_meeting_results_container_div = null;
        };
        
        the_outer_container.innerHTML = '';
        the_main_results_display.className = 'bmlt_admin_meeting_editor_form_results_div item_hidden"';
        
        this.m_search_results = null;
    };

    /************************************************************************************//**
    *   \brief This function constructs a URI to the root server that reflects the search   *
    *          parameters, as specified by the search specification section.                *
    *   \returns a string, containing the complete URI.                                     *
    ****************************************************************************************/
    this.createSearchURI = function () {
        var uri = g_ajax_callback_uri + '&do_meeting_search=1&sort_key=time&simple_other_fields=1';
        
        var search_string = document.getElementById('bmlt_admin_text_specifier_input').value;
        
        if ( search_string == document.getElementById('bmlt_admin_text_specifier_input').defaultValue ) {
            search_string = null;
        };
        
        var is_location = document.getElementById('bmlt_admin_meeting_search_text_is_a_location_checkbox').checked;
        var weekdays = new Array;
        
        if ( !document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_0').checked ) {
            for (var c = 1; c < 8; c++) {
                if ( document.getElementById('bmlt_admin_meeting_search_weekday_checkbox_' + c).checked ) {
                    weekdays[weekdays.length] = c;
                };
            };
        };
            
        var service_bodies = new Array();
        
        if ( g_service_bodies_array.length > 1 ) {
            for (var c = 0; c < g_service_bodies_array.length; c++) {
                var service_body_id = g_service_bodies_array[c][0];
                var sb_checkbox = document.getElementById('bmlt_admin_meeting_search_service_body_checkbox_' + service_body_id);
                if ( sb_checkbox && sb_checkbox.checked ) {
                    service_bodies[service_bodies.length] = service_body_id;
                };
            };
        } else {
            service_bodies[0] = g_service_bodies_array[0][0];
        };
        
        var starts_after = new Array();
        var starts_before = new Array();
        
        if ( document.getElementById('bmlt_admin_meeting_search_start_time_morn_checkbox').checked ) {
            starts_after = new Array(0, 0);
            starts_before = new Array(12, 0);
        } else if ( document.getElementById('bmlt_admin_meeting_search_start_time_aft_checkbox').checked ) {
            starts_after = new Array(11, 59);
            starts_before = new Array(18, 0);
        } else if ( document.getElementById('bmlt_admin_meeting_search_start_time_eve_checkbox').checked ) {
            starts_after = new Array(17, 59);
            starts_before = new Array(23, 59);
        };
        
        if ( search_string ) {
            uri += '&SearchStringAll=1&SearchString=' + encodeURIComponent(search_string) + (is_location ? '&StringSearchIsAnAddress=1' : '');
        };
        
        if ( weekdays.length ) {
            for (var c = 0; c < weekdays.length; c++) {
                uri += '&weekdays[]=' + parseInt(weekdays[c], 10);
            };
        };
        
        if ( service_bodies.length ) {
            for (var c = 0; c < service_bodies.length; c++) {
                uri += '&services[]=' + parseInt(service_bodies[c], 10);
            };
        };

        if ( starts_after.length ) {
            uri += '&StartsAfterH=' + starts_after[0];
            uri += '&StartsAfterM=' + starts_after[1];
        };
        
        if ( starts_before.length ) {
            uri += '&StartsBeforeH=' + starts_before[0];
            uri += '&StartsBeforeM=' + starts_before[1];
        };
        
        var pub_select = document.getElementById('bmlt_admin_single_meeting_editor_template_meeting_publish_search_select');
        
        if ( pub_select.value ) {
            uri += '&advanced_published=' + pub_select.value;
        };
        
        return uri;
    };
    
    /************************************************************************************//**
    *   \brief  Does an AJAX call for a JSON response, based on the given criteria and      *
    *           callback function.                                                          *
    *           The callback will be a function in the following format:                    *
    *               function ajax_callback ( in_json_obj )                                  *
    *           where "in_json_obj" is the response, converted to a JSON object.            *
    *           it will be null if the function failed.                                     *
    ****************************************************************************************/
    this.callRootServerForMeetingSearch = function (in_uri  ///< The URI to call (with all the parameters).
                                                    ) {
        if ( this.m_ajax_request_in_progress ) {
            this.m_ajax_request_in_progress.abort();
            this.m_ajax_request_in_progress = null;
        };
        
        var salt = new Date();
        in_uri += '&salt=' + salt.getTime();
        
        this.m_ajax_request_in_progress = BMLT_AjaxRequest(in_uri, function (in_req) {
            admin_handler_object.meetingSearchResultsCallback(in_req); }, 'post');
    };
    
    /************************************************************************************//**
    *   \brief This is the meeting search results callback.                                 *
    ****************************************************************************************/
    this.meetingSearchResultsCallback = function (  in_response_object  ///< The HTTPRequest response object.
                                                ) {
        var button_span = document.getElementById('bmlt_admin_meeting_search_ajax_button_span');
        var throbber_span = document.getElementById('bmlt_admin_meeting_search_ajax_button_throbber_span');
        var text_reply = in_response_object.responseText;
        
        throbber_span.className = 'bmlt_admin_value_left item_hidden';
        button_span.className = 'bmlt_admin_value_left';
    
        if ( text_reply ) {
            var json_builder = 'var response_object = ' + text_reply + ';';
        
            // This is how you create JSON objects.
            eval(json_builder);
        
            if ( response_object.length ) {
                this.processSearchResults(response_object);
                location.hash = '#bmlt_admin_meeting_editor_form_results_banner_div';
            } else {
                alert(g_no_search_results_text);
            };
        } else {
            alert(g_no_search_results_text);
        };
    };
    
    /************************************************************************************//**
    *   \brief This creates the meeting search results.                                     *
    ****************************************************************************************/
    this.processSearchResults = function ( in_search_results_json_object ///< The search results, as a JSON object.
                                        ) {
        this.m_search_results = in_search_results_json_object;
        
        for (var c = 0; c < this.m_search_results.length; c++) {
            if ( this.m_search_results[c] ) {
                // The reason we do this whacky stuff, is that the formats may not be in the same order from the server as we keep them, so this little dance re-orders them.
                var format_array = new Array;
                var main_formats = g_format_object_array;
                var mtg_formats = this.m_search_results[c].formats.split(',');
        
                for (var i = 0; i < main_formats.length; i++) {
                    for (var n = 0; n < mtg_formats.length; n++) {
                        if ( main_formats[i].key == mtg_formats[n] ) {
                            format_array[format_array.length] = mtg_formats[n];
                        };
                    };
                };
                
                this.m_search_results[c].formats = format_array.join(',');
                
                var dur = this.m_search_results[c].duration_time.split(':');
                dur[0] = parseInt(dur[0], 10);
                dur[1] = parseInt(dur[1], 10);
                this.m_search_results[c].duration_time = sprintf('%02d:%02d:00', dur[0], dur[1]);
            };
        };
        
        this.createMeetingList();
        
        this.selectMeetingEditorTab();
    };
    
    /************************************************************************************//**
    *   \brief This creates the DOM tree of the meeting list.
    ****************************************************************************************/
    this.createMeetingList = function () {
        this.m_search_results.sort(admin_handler_object.sortSearchResultsCallback);
        
        var the_outer_container = document.getElementById('bmlt_admin_meeting_editor_form_results_inner_div');
        var the_results_header = document.getElementById('bmlt_admin_meeting_editor_form_results_banner_div');
        var the_main_results_display = document.getElementById('bmlt_admin_meeting_editor_form_results_div');
        
        if ( this.m_meeting_results_container_div ) {
            this.m_meeting_results_container_div.innerHTML = '';
            this.m_meeting_results_container_div = null;
        };
        
        the_outer_container.innerHTML = '';
        the_main_results_display.className = 'bmlt_admin_meeting_editor_form_results_div item_hidden"';
        this.m_meeting_results_container_div = document.createElement('div');   // Create the container element.
        this.m_meeting_results_container_div.className = 'bmlt_admin_meeting_search_results_container_div';
        
        for (var c = 0; c < this.m_search_results.length; c++) {
            if ( this.m_search_results[c] ) {
                var outer_meeting_div = document.createElement('div');   // Create the container element.
                outer_meeting_div.meeting_id = this.m_search_results[c].id_bigint;
                outer_meeting_div.className = 'bmlt_admin_meeting_search_results_single_meeting_outer_container_' + ((this.m_search_results[c].published != '0') ? 'published' : 'unpublished') + '_div';
            
                var single_meeting_div = document.createElement('div');   // Create the container element.
                single_meeting_div.meeting_id = this.m_search_results[c].id_bigint;
                single_meeting_div.className = 'bmlt_admin_meeting_search_results_single_meeting_container_div' + ((c % 2) ? ' meeting_line_odd' : ' meeting_line_even');
                single_meeting_div.id = 'bmlt_admin_meeting_search_results_single_meeting_' + single_meeting_div.meeting_id +'_div';

                this.createOneMeetingNode(single_meeting_div, this.m_search_results[c]);
            
                outer_meeting_div.appendChild(single_meeting_div);
            
                this.m_meeting_results_container_div.appendChild(outer_meeting_div);
            };
        };
        
        var breaker_breaker_rubber_duck = document.createElement('div');   // This is a break element.
        breaker_breaker_rubber_duck.className = 'clear_both';
        this.m_meeting_results_container_div.appendChild(breaker_breaker_rubber_duck);
        
        the_outer_container.appendChild(this.m_meeting_results_container_div);
        the_main_results_display.className = 'bmlt_admin_meeting_editor_form_results_div"';
        
        the_results_header.innerHTML = (this.m_search_results.length > 1) ? sprintf(g_meeting_editor_result_count_format, this.m_search_results.length) : '';
    };
    
    /************************************************************************************//**
    *   \brief Sorts the search results by weekday, then time.
    ****************************************************************************************/
    this.sortSearchResultsCallback = function (
        in_object_a,
        in_object_b
    ) {
        if ( in_object_a && in_object_b ) {
            var a_start = in_object_a.start_time.toString().split(':');
            var b_start = in_object_b.start_time.toString().split(':');
            var a_time = (parseInt(in_object_a.weekday_tinyint, 10) * 100000) + (parseInt(a_start[0], 10) * 100) + parseInt(a_start[1], 10);
            var b_time = (parseInt(in_object_b.weekday_tinyint, 10) * 100000) + (parseInt(b_start[0], 10) * 100) + parseInt(b_start[1], 10);
            
            return (a_time > b_time) ? 1 : ((a_time < b_time) ? -1 : 0);
        } else if ( in_object_a && !in_object_b ) {
            return -1;
        } else if ( in_object_b && !in_object_a ) {
            return 1;
        };
        
        return 0;
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.createOneMeetingNode = function (
        in_single_meeting_div,  ///< The containing div element.
        in_meeting_object       ///< The meeting object.
    ) {
        var meeting_editorLink = document.createElement('a');
        meeting_editorLink.href = 'javascript:admin_handler_object.toggleMeetingSingleEditor(' + in_meeting_object.id_bigint + ', false)';
        meeting_editorLink.className = 'bmlt_admin_meeting_search_results_single_meeting_a';

        var element_span = document.createElement('span');   // Create the container element.
        element_span.className = 'bmlt_admin_meeting_search_results_single_meeting_weekday_span';
        var text_node = document.createTextNode(g_weekday_name_array[in_meeting_object.weekday_tinyint - 1]);
        element_span.appendChild(text_node);
        meeting_editorLink.appendChild(element_span);

        element_span = document.createElement('span');   // Create the container element.
        element_span.className = 'bmlt_admin_meeting_search_results_single_meeting_start_time_span';
        var start_time = in_meeting_object.start_time;
        var start_time = in_meeting_object.start_time.toString().split(':');
        var hours = 0;
        var minutes = 0;
        var midnight = false;
        var noon = false;
        var pm = false;

        if ( start_time && (start_time.length > 1) ) {
            var hours = parseInt(start_time[0], 10);
            var minutes = parseInt(start_time[1], 10);
            var midnight = false;
            var noon = false;
            var pm = false;
            
            if ( (hours == 23) && (minutes > 55) ) {
                midnight = true;
            } else {
                if ( (hours == 12) && (minutes == 0) ) {
                    noon = true;
                } else {
                    if ( hours > 23 ) {
                        hours = 23;
                    };
            
                    if ( hours < 0 ) {
                        hours = 0;
                    };

                    if ( minutes > 59 ) {
                        minutes = 59;
                    };
            
                    if ( minutes < 0 ) {
                        minutes = 0;
                    };

                    if ( minutes % g_default_minute_interval ) {
                        minutes += g_default_minute_interval;
                
                        if ( minutes >= 60 ) {
                            hours++;
                            minutes -= 60;
                        };
                    };
                };
            };
                
            if ( hours > 12 ) {
                hours -= 12;
                pm = true;
            } else if ( (hours == 12) && (minutes > 0) ) {
                pm = true;
            };
        };
        
        if ( midnight ) {
            start_time = g_time_values[3];
        } else if ( noon ) {
            start_time = g_time_values[2];
        } else {
            start_time = sprintf('%d:%02d %s', hours, minutes, (pm ? g_time_values[1] : g_time_values[0]));
        };
        
        text_node = document.createTextNode(start_time);
        element_span.appendChild(text_node);
        meeting_editorLink.appendChild(element_span);

        element_span = document.createElement('span');   // Create the container element.
        element_span.className = 'bmlt_admin_meeting_search_results_single_meeting_meeting_name_span';
        text_node = document.createTextNode(in_meeting_object.meeting_name + (in_meeting_object.location_street ? ', ' + in_meeting_object.location_street : '') + (in_meeting_object.location_city_subsection ? ', ' + in_meeting_object.location_city_subsection : '') + (in_meeting_object.location_municipality ? ', ' + in_meeting_object.location_municipality : '') + (in_meeting_object.location_province ? ', ' + in_meeting_object.location_province : '') + (in_meeting_object.location_sub_province ? ', (' + in_meeting_object.location_sub_province + ')' : '') + (in_meeting_object.location_postal_code_1 ? ', ' + in_meeting_object.location_postal_code_1 : ''));
        element_span.appendChild(text_node);
        meeting_editorLink.appendChild(element_span);

        in_single_meeting_div.appendChild(meeting_editorLink);
        var breaker_breaker_rubber_duck = document.createElement('div');   // This is a break element.
        breaker_breaker_rubber_duck.className = 'clear_both';
        in_single_meeting_div.appendChild(breaker_breaker_rubber_duck);
    };
    
    // #mark -
    // #mark Edit Meetings Tab
    // #mark -

    /************************************************************************************//**
    *   \brief  Selects the meeting editor tab.                                             *
    ****************************************************************************************/
    this.selectMeetingEditorTab = function () {
        var tab_specifier_element = document.getElementById('bmlt_admin_meeting_editor_tab_specifier_div');
        var tab_editor_element = document.getElementById('bmlt_admin_meeting_editor_tab_results_div');
        var tab_specifier_link = document.getElementById('bmlt_admin_meeting_editor_tab_specifier_a');
        var tab_editor_link = document.getElementById('bmlt_admin_meeting_editor_tab_results_a');
        var search_specifier_element = document.getElementById('bmlt_admin_meeting_editor_form_specifier_div');
        var meeting_editor_element = document.getElementById('bmlt_admin_meeting_editor_form_div');
        
        tab_specifier_element.className = 'bmlt_admin_tab_div_left bmlt_admin_tab_div_not_selected';
        tab_editor_element.className = 'bmlt_admin_tab_div_right bmlt_admin_tab_div_selected';
        
        tab_specifier_link.setAttribute('href', '');
        tab_specifier_link.href = 'javascript:admin_handler_object.selectSearchSpecifierTab()';
        tab_editor_link.removeAttribute("href");
        
        search_specifier_element.className = 'bmlt_admin_meeting_editor_form_specifier_div item_hidden';
        meeting_editor_element.className = 'bmlt_admin_meeting_editor_form_div';
    }
    
    /************************************************************************************//**
    *   \brief  Brings up a new meeting screen.                                             *
    ****************************************************************************************/
    this.toggleMeetingSingleEditor = function (
        in_meeting_id,
        in_no_confirm
    ) {
        var display_parent = document.getElementById('bmlt_admin_meeting_search_results_single_meeting_' + in_meeting_id + '_div');
    
        if ( display_parent ) {
            if ( !display_parent.meeting_editor_object ) {
                var proceed = true;
                if ( !in_no_confirm && (this.m_editing_window_open != null) && this.isMeetingDirty(this.m_editing_window_open.meeting_id) ) {
                    proceed = confirm(g_meeting_editor_already_editing_confirm);
                };
        
                if ( proceed ) {
                    if ( this.m_editing_window_open != null ) {
                        if ( this.m_editing_window_open.meeting_editor_object && (this.m_editing_window_open.meeting_editor_object.parentNode == this.m_editing_window_open) ) {
                            this.m_editing_window_open.removeChild(this.m_editing_window_open.meeting_editor_object);
                            this.m_editing_window_open.meeting_editor_object = null;
                        } else {
                            document.getElementById('bmlt_admin_meeting_editor_new_meeting_0_editor_display').className = 'item_hidden';
                            document.getElementById('bmlt_admin_meeting_editor_form_meeting_0_button').className = 'bmlt_admin_ajax_button button';
                        };
            
                        this.m_editing_window_open = null;
                    };
                        
                    display_parent.meeting_editor_object = document.createElement('div');   // Create the container element.
                    display_parent.meeting_editor_object.className = 'bmlt_admin_meeting_search_results_editor_container_div';
                    display_parent.appendChild(display_parent.meeting_editor_object);
                    display_parent.meeting_id = in_meeting_id;
                    
                    this.m_editing_window_open = display_parent;
                    this.createNewMeetingEditorScreen(display_parent.meeting_editor_object, in_meeting_id);
                    location.hash = '#bmlt_admin_meeting_search_results_single_meeting_' + parseInt(in_meeting_id, 10) + '_div';
                };
            } else {
                var proceed = true;
                if ( !in_no_confirm && (this.m_editing_window_open != null) && this.isMeetingDirty(this.m_editing_window_open.meeting_id) ) {
                    proceed = confirm(g_meeting_closure_confirm_text);
                };
        
                if ( proceed ) {
                    if ( display_parent.meeting_editor_object && (display_parent.meeting_editor_object.parentNode == display_parent) ) {
                        display_parent.removeChild(display_parent.meeting_editor_object);
                    };
                
                    this.m_editing_window_open.meeting_id = null;
                    display_parent.meeting_editor_object = null;
                };
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief  Brings up a new meeting screen.                                             *
    ****************************************************************************************/
    this.createANewMeetingButtonHit = function ( in_button_object
                                            ) {
        var display_parent = document.getElementById('bmlt_admin_meeting_editor_new_meeting_0_editor_display');
        var new_meeting_button = document.getElementById('bmlt_admin_meeting_editor_form_meeting_0_button');
        
        display_parent.innerHTML = '';
        
        var proceed = true;
        if ( (this.m_editing_window_open != null) && this.isMeetingDirty(this.m_editing_window_open.meeting_id) ) {
            proceed = confirm(g_meeting_editor_already_editing_confirm);
        };

        if ( proceed ) {
            if ( this.m_editing_window_open != null ) {
                this.cancelMeetingEdit(this.m_editing_window_open.meeting_id, true);
            };
                        
            this.createNewMeetingEditorScreen(display_parent, 0);
        
            new_meeting_button.className = 'bmlt_admin_ajax_button button item_hidden';
            display_parent.className = 'bmlt_admin_meeting_editor_meeting_editor_display';
            this.changeSaveMeetingButtonToCopy(0);
            
            this.m_editing_window_open = document.getElementById('bmlt_admin_single_meeting_editor_0_div');
            this.m_editing_window_open.meeting_id = 0;
        };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.changeSaveMeetingButtonToCopy = function ( in_meeting_id   ///< The BMLT ID of the meeting that is being edited.
                                                ) {
        var save_button = document.getElementById('bmlt_admin_meeting_editor_form_meeting_' + in_meeting_id +'_save_button');
        save_button.innerHTML = g_Create_new_meeting_button_name;
        this.validateMeetingEditorButton(in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.changeCopyMeetingButtonToSave = function ( in_meeting_id   ///< The BMLT ID of the meeting that is being edited.
                                                ) {
        var save_button = document.getElementById('bmlt_admin_meeting_editor_form_meeting_' + in_meeting_id +'_save_button');
        save_button.innerHTML = g_Save_meeting_button_name;
        this.validateMeetingEditorButton(in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.saveMeeting = function ( in_meeting_id
                                ) {
        if ( !in_meeting_id ) {
            in_meeting_id = 0;  // Just to make sure...
        };
            
        var save_button = document.getElementById('bmlt_admin_meeting_editor_form_meeting_' + in_meeting_id + '_save_button');
        
        if ( save_button.className == 'bmlt_admin_ajax_button button' ) {
            var root_element = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
            var new_meeting_id = in_meeting_id;
            var copy_checkbox = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_duplicate_checkbox');
            var throbber_span = document.getElementById('bmlt_admin_' + in_meeting_id + '_save_ajax_button_throbber_span');
            var save_a = document.getElementById('bmlt_admin_meeting_editor_form_meeting_' + in_meeting_id + '_save_button');
            var meeting_sent = false;

            if ( !copy_checkbox.checked && new_meeting_id && this.m_search_results && this.m_search_results.length ) {
                for (var c = 0; c < this.m_search_results.length; c++) {
                    if ( new_meeting_id == this.m_search_results[c].id_bigint ) {
                        save_a.className = 'item_hidden';
                        throbber_span.className = 'bmlt_admin_ajax_button_throbber_span';
                        if (g_auto_geocoding_enabled && root_element.original_address_line !== this.getAddressLine(in_meeting_id)) {
                            var sendMeetingToServer = this.sendMeetingToServer;
                            this.lookupLocation(in_meeting_id, function () {
                                sendMeetingToServer(in_meeting_id, false); });
                        } else {
                            this.sendMeetingToServer(in_meeting_id, false);
                        }
                        meeting_sent = true;
                        break;
                    }
                }
            }

            if ( !meeting_sent ) {
                save_a.className = 'item_hidden';
                throbber_span.className = 'bmlt_admin_ajax_button_throbber_span';
                if (g_auto_geocoding_enabled && root_element.original_address_line !== this.getAddressLine(in_meeting_id)) {
                    var sendMeetingToServer = this.sendMeetingToServer;
                    this.lookupLocation(in_meeting_id, function () {
                        sendMeetingToServer(in_meeting_id, true); });
                } else {
                    this.sendMeetingToServer(in_meeting_id, true);
                }
            }
        }
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.cancelMeetingEdit = function (
        in_meeting_id,
        in_no_confirm
    ) {
        if ( !this.isMeetingDirty(in_meeting_id) || (this.isMeetingDirty(in_meeting_id) && (in_no_confirm || confirm(g_meeting_closure_confirm_text))) ) {
            var parent_id = 'bmlt_admin_meeting_editor_new_meeting_' + in_meeting_id + '_editor_display';
            
            var display_parent = document.getElementById(parent_id);
            
            var editor = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
            
            if ( editor && editor.main_map ) {
                editor.main_map = null;
            };
            
            var map_div = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_map_div');
            
            if ( map_div ) {
                map_div.innerHTML = '';
            };
            
            if ( display_parent ) {
                if ( display_parent.m_ajax_request_in_progress ) {
                    display_parent.m_ajax_request_in_progress.abort();
                    display_parent.m_ajax_request_in_progress = null;
                };
                
                display_parent.innerHTML = '';
                display_parent.className = 'item_hidden';
            
                if ( in_meeting_id == 0 ) {
                    var new_meeting_button = document.getElementById('bmlt_admin_meeting_editor_form_meeting_0_button');
                    new_meeting_button.className = 'bmlt_admin_ajax_button button';
                };
                
                var mtg_window = document.getElementById('bmlt_admin_meeting_search_results_single_meeting_' + in_meeting_id + '_div');
                
                if ( mtg_window ) {
                    if ( mtg_window.meeting_editor_object && mtg_window.meeting_editor_object.parentNode && (mtg_window.meeting_editor_object.parentNode == mtg_window) ) {
                        mtg_window.removeChild(mtg_window.meeting_editor_object);
                    };
                
                    mtg_window.meeting_editor_object = null;
                };
                
                this.m_editing_window_open = null;
            } else {
                this.toggleMeetingSingleEditor(in_meeting_id, true);
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.deleteMeeting = function ( in_meeting_id
                                    ) {
        var confirm_str = g_meeting_editor_screen_delete_button_confirm;
        
        if ( confirm(confirm_str) ) {
            var root_element = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
            
            if ( root_element.m_ajax_request_in_progress ) {
                root_element.m_ajax_request_in_progress.abort();
                root_element.m_ajax_request_in_progress = null;
            };
        
            var uri = g_ajax_callback_uri + '&delete_meeting=' + in_meeting_id;

            var throbber_span = document.getElementById('bmlt_admin_' + in_meeting_id + '_delete_ajax_button_throbber_span');
            var delete_a = document.getElementById('bmlt_admin_meeting_editor_form_meeting_' + in_meeting_id + '_delete_button');
            delete_a.className = 'item_hidden';
            throbber_span.className = 'bmlt_admin_ajax_button_throbber_span';
            
            var salt = new Date();
            uri += '&salt=' + salt.getTime();
            
            root_element.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req) {
                admin_handler_object.handleDeleteMeetingAJAXCallback(in_req); }, 'get');
        };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.handleDeleteMeetingAJAXCallback = function (in_http_request
                                                    ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
        };
            
        if ( json_object ) {
            if ( json_object.success ) {
                for (var c = 0; c < this.m_search_results.length; c++) {
                    if ( this.m_search_results[c].id_bigint == json_object.report ) {
                        this.m_search_results[c] = null;
                        this.m_search_results.splice(c, 1);
                        break;
                    };
                };
                
                this.cancelMeetingEdit(json_object.report, true);
                this.createMeetingList();
                BMLT_Admin_StartFader('bmlt_admin_fader_meeting_editor_delete_success_div', this.m_success_fade_duration);
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_meeting_editor_delete_fail_div', this.m_failure_fade_duration);
            };
        } else {
            BMLT_Admin_StartFader('bmlt_admin_fader_meeting_editor_delete_fail_div', this.m_failure_fade_duration);
        };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.isMeetingDirty = function (in_meeting_id       ///< The BMLT ID of the meeting that will be dirtified.
                                    ) {
        var editor = document.getElementById('bmlt_admin_single_meeting_editor_' + parseInt(in_meeting_id, 10) + '_div');
        
        if ( editor && editor.meeting_object ) {
            var editor_object = JSON.stringify(editor.meeting_object);
            var original_object = JSON.stringify(this.getMeetingObjectById(in_meeting_id, false));
            return (editor_object != original_object);
        };
        
        return false;
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.validateMeetingEditorButton = function (in_meeting_id
                                                ) {
        var save_button = document.getElementById('bmlt_admin_meeting_editor_form_meeting_' + in_meeting_id + '_save_button');
        var dup_checkbox = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_duplicate_checkbox');
        
        if ( save_button ) {
            var enable = this.isMeetingDirty(in_meeting_id) || ((in_meeting_id > 0) && dup_checkbox && dup_checkbox.checked);
            if ( enable ) {
                save_button.className = 'bmlt_admin_ajax_button button';
            } else {
                save_button.className = 'bmlt_admin_ajax_button button_disabled';
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief This is called to initiate an AJAX process to change the account settings.   *
    ****************************************************************************************/
    this.sendMeetingToServer = function (
        in_meeting_id,
        is_new_meeting
    ) {
        var new_editor = document.getElementById('bmlt_admin_single_meeting_editor_' + parseInt(in_meeting_id, 10) + '_div');

        if ( is_new_meeting ) {
            new_editor.meeting_object.id_bigint = 0;
        };
        
        var serialized_meeting_object = JSON.stringify(new_editor.meeting_object);

        if ( is_new_meeting ) {
            new_editor.meeting_object.id_bigint = in_meeting_id;
        };
        
        var uri = g_ajax_callback_uri + '&set_meeting_change=' + encodeURIComponent(serialized_meeting_object);

        if ( new_editor.m_ajax_request_in_progress ) {
            new_editor.m_ajax_request_in_progress.abort();
            new_editor.m_ajax_request_in_progress = null;
        };
            
        var salt = new Date();
        uri += '&salt=' + salt.getTime();
        
        new_editor.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function ( in_req, in_orig_meeting_id ) {
            admin_handler_object.handleMeetingChangeAJAXCallback(in_req,in_orig_meeting_id); }, 'post', in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief This is called to initiate an AJAX process to change the account settings.   *
    ****************************************************************************************/
    this.handleMeetingChangeAJAXCallback = function (
        in_http_request,
        in_orig_meeting_id
    ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
        };

        if ( json_object ) {
            var meeting_changed = false;
            
            if ( json_object.error ) {
                alert(json_object.report);
            } else {
                if ( this.m_search_results ) {
                    for (var c = 0; c < this.m_search_results.length; c++) {
                        if ( this.m_search_results[c].id_bigint == json_object[0].id_bigint ) {
                            this.m_search_results[c] = json_object[0];
                            single_meeting_div_id = 'bmlt_admin_meeting_search_results_single_meeting_' + json_object[0].id_bigint +'_div';
                    
                            var single_meeting_div = document.getElementById(single_meeting_div_id);
                    
                            if ( single_meeting_div ) {
                                single_meeting_div.innerHTML = '';
                                this.createOneMeetingNode(single_meeting_div, this.m_search_results[c]);
                                meeting_changed = true;
                            };

                            break;
                        };
                    };
                };
            };
            
            if ( !meeting_changed ) {
                if ( !this.m_search_results ) {
                    this.m_search_results = new Array;
                };
                
                this.m_search_results[this.m_search_results.length] = json_object[0];
                BMLT_Admin_StartFader('bmlt_admin_fader_meeting_editor_add_success_div', this.m_success_fade_duration);
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_meeting_editor_success_div', this.m_success_fade_duration);
            };
            
            this.cancelMeetingEdit(in_orig_meeting_id, true);
            this.createMeetingList();
            if (json_object && json_object[0] && json_object[0].id_bigint) {
                this.toggleMeetingSingleEditor(json_object[0].id_bigint);
            }
        } else {
            if ( in_orig_meeting_id ) {
                BMLT_Admin_StartFader('bmlt_admin_fader_meeting_editor_fail_div', this.m_failure_fade_duration);
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_meeting_editor_add_fail_div', this.m_failure_fade_duration);
            };
        };
    };
    
    // #mark -
    // #mark Creating A New Meeting Editor
    // #mark -
    
    /************************************************************************************//**
    *   \brief  This creates a new meeting details editor screen.                           *
    *   \returns    A new DOM hierarchy with the initialized editor.                        *
    ****************************************************************************************/
    this.createNewMeetingEditorScreen = function (
        in_parent_element,  ///< The parent element of the new instance.
        in_meeting_id       ///< The BMLT ID of the meeting that will be edited. If null, then it is a new meeting.
    ) {
        // We first see if one already exists.
        var new_editor = document.getElementById('bmlt_admin_single_meeting_editor_' + parseInt(in_meeting_id, 10) + '_div');
    
        if ( !new_editor ) {
            var template_dom_list = document.getElementById('bmlt_admin_single_meeting_editor_template_div');
            
            var meeting_name_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_name_text_input';
            var meeting_cc_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_cc_text_input';
            var meeting_contact_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_contact_text_input';
            
            var meeting_location_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_location_text_input';
            var meeting_info_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_info_text_input';
            var meeting_street_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_street_text_input';
            var meeting_neighborhood_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_neighborhood_text_input';
            var meeting_borough_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_borough_text_input';
            var meeting_city_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_city_text_input';
            var meeting_county_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_county_text_input';
            var meeting_state_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_state_text_input';
            var meeting_zip_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_zip_text_input';
            var meeting_nation_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_nation_text_input';
            var meeting_longitude_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_longitude_text_input';
            var meeting_latitude_text_item_id = 'bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_latitude_text_input';
        
            if ( template_dom_list ) {    // This makes an exact copy of the template (including IDs, so we'll need to change those).
                new_editor = template_dom_list.cloneNode(true);
                // This function replaces all of the spots that say "template" with the given ID. That gives us unique IDs.
                BMLT_Admin_changeTemplateIDToUseThisID(new_editor, in_meeting_id);
                new_editor.meeting_object = this.getMeetingObjectById(in_meeting_id, true);
                new_editor.map_disclosed = false;
            
                new_editor.className = 'bmlt_admin_single_meeting_editor_div';
            
                in_parent_element.appendChild(new_editor);
                            
                this.handleTextInputLoad(document.getElementById(meeting_name_text_item_id));
                this.handleTextInputLoad(document.getElementById(meeting_cc_text_item_id), null, true);
                this.handleTextInputLoad(document.getElementById(meeting_contact_text_item_id));
                this.handleTextInputLoad(document.getElementById(meeting_location_text_item_id));
                this.handleTextInputLoad(document.getElementById(meeting_info_text_item_id));
                this.handleTextInputLoad(document.getElementById(meeting_street_text_item_id));
                this.handleTextInputLoad(document.getElementById(meeting_neighborhood_text_item_id));
                this.handleTextInputLoad(document.getElementById(meeting_borough_text_item_id));
                this.handleTextInputLoad(document.getElementById(meeting_city_text_item_id));
                this.handleTextInputLoad(document.getElementById(meeting_zip_text_item_id), null, true);
                this.handleTextInputLoad(document.getElementById(meeting_nation_text_item_id), null, true);
                this.handleTextInputLoad(document.getElementById(meeting_longitude_text_item_id), 0, true);
                this.handleTextInputLoad(document.getElementById(meeting_latitude_text_item_id), 0, true);

                var meeting_state_text_input = document.getElementById(meeting_state_text_item_id);
                if (meeting_state_text_input !== null) {
                    this.handleTextInputLoad(meeting_state_text_input, null, g_auto_geocoding_enabled && g_county_auto_geocoding_enabled);
                }

                var meeting_county_text_input = document.getElementById(meeting_county_text_item_id);
                if (meeting_county_text_input !== null) {
                    this.handleTextInputLoad(meeting_county_text_input, null, g_auto_geocoding_enabled && g_county_auto_geocoding_enabled);
                }
                
                var map_disclosure_a = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_map_disclosure_a');
                map_disclosure_a.href = 'javascript:admin_handler_object.toggleMeetingMapDisclosure(' + in_meeting_id + ')';
                
                var select_duplicate_checkbox = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_duplicate_checkbox');
                select_duplicate_checkbox.onchange = function () {
                    var save_button = document.getElementById('bmlt_admin_meeting_editor_form_meeting_' + in_meeting_id + '_save_button');
                    if ( this.checked ) {
                        save_button.innerHTML = g_Create_new_meeting_button_name;
                    } else {
                        save_button.innerHTML = g_Save_meeting_button_name;
                    };
                                                                    
                                                                    admin_handler_object.validateMeetingEditorButton(in_meeting_id);
                };
            };
        };
        
        this.populateMeetingEditorForm(new_editor);
        new_editor.original_address_line = this.getAddressLine(in_meeting_id);
        return new_editor;
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.populateMeetingEditorForm = function (  in_meeting_editor   ///< This is the meeting editor object that will be set up for this meeting.
                                                ) {
        var meeting_object = in_meeting_editor.meeting_object;
        var meeting_id = meeting_object.id_bigint;
        
        if ( !meeting_id ) {  // We add a header for the new meeting form.
            var template_header = document.getElementById('bmlt_admin_meeting_editor_' + meeting_id + '_meeting_header');
            template_header.innerHTML = g_new_meeting_header_text;
        };

        document.getElementById('bmlt_admin_meeting_id_' + meeting_id +'_display').innerHTML = meeting_id;

        var meeting_published_checkbox = document.getElementById('bmlt_admin_meeting_' + meeting_id + '_published_checkbox');
        
        var meeting_name_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_name_text_input');
        var meeting_cc_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_cc_text_input');
        var meeting_location_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_location_text_input');
        var meeting_info_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_info_text_input');
        var meeting_street_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_street_text_input');
        var meeting_neighborhood_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_neighborhood_text_input');
        var meeting_borough_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_borough_text_input');
        var meeting_city_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_city_text_input');
        var meeting_county_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_county_text_input');
        var meeting_county_select_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_county_select_input');
        var meeting_state_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_state_text_input');
        var meeting_state_select_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_state_select_input');
        var meeting_zip_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_zip_text_input');
        var meeting_nation_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_nation_text_input');
        var meeting_longitude_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_longitude_text_input');
        var meeting_latitude_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_latitude_text_input');
        var meeting_contact_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_contact_text_input');

        meeting_name_text_item.value = htmlspecialchars_decode(meeting_object.meeting_name ? meeting_object.meeting_name : meeting_name_text_item.value);
        this.setTextItemClass(meeting_name_text_item);
        meeting_name_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'meeting_name')};
        meeting_name_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_name_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_name_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'meeting_name'); }, 0);
        };
        meeting_name_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'meeting_name'); }, 0);
        };


        meeting_cc_text_item.value = htmlspecialchars_decode(meeting_object.worldid_mixed ? meeting_object.worldid_mixed : meeting_cc_text_item.value);
        this.setTextItemClass(meeting_cc_text_item);
        meeting_cc_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'worldid_mixed')};
        meeting_cc_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_cc_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_cc_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'worldid_mixed'); }, 0);
        };
        meeting_cc_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'worldid_mixed'); }, 0);
        };

        
        meeting_location_text_item.value = htmlspecialchars_decode(meeting_object.location_text ? meeting_object.location_text : meeting_location_text_item.value);
        this.setTextItemClass(meeting_location_text_item);
        meeting_location_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'location_text')};
        meeting_location_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_location_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_location_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_text'); }, 0);
        };
        meeting_location_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_text'); }, 0);
        };

        
        meeting_info_text_item.value = htmlspecialchars_decode(meeting_object.location_info ? meeting_object.location_info : meeting_info_text_item.value);
        this.setTextItemClass(meeting_info_text_item);
        meeting_info_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'location_info')};
        meeting_info_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_info_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_info_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_info'); }, 0);
        };
        meeting_info_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_info'); }, 0);
        };
        
        meeting_street_text_item.value = htmlspecialchars_decode(meeting_object.location_street ? meeting_object.location_street : meeting_street_text_item.value);
        this.setTextItemClass(meeting_street_text_item);
        meeting_street_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'location_street')};
        meeting_street_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_street_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_street_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_street'); }, 0);
        };
        meeting_street_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_street'); }, 0);
        };

        
        meeting_neighborhood_text_item.value = htmlspecialchars_decode(meeting_object.location_neighborhood ? meeting_object.location_neighborhood : meeting_neighborhood_text_item.value);
        this.setTextItemClass(meeting_neighborhood_text_item);
        meeting_neighborhood_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'location_neighborhood')};
        meeting_neighborhood_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_neighborhood_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_neighborhood_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_neighborhood'); }, 0);
        };
        meeting_neighborhood_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_neighborhood'); }, 0);
        };

        
        meeting_borough_text_item.value = htmlspecialchars_decode(meeting_object.location_city_subsection ? meeting_object.location_city_subsection : meeting_borough_text_item.value);
        this.setTextItemClass(meeting_borough_text_item);
        meeting_borough_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'location_city_subsection')};
        meeting_borough_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_borough_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_borough_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_city_subsection'); }, 0);
        };
        meeting_borough_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_city_subsection'); }, 0);
        };

        
        meeting_city_text_item.value = htmlspecialchars_decode(meeting_object.location_municipality ? meeting_object.location_municipality : meeting_city_text_item.value);
        this.setTextItemClass(meeting_city_text_item);
        meeting_city_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'location_municipality')};
        meeting_city_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_city_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_city_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_municipality'); }, 0);
        };
        meeting_city_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_municipality'); }, 0);
        };


        if (meeting_county_text_item !== null) {
            meeting_county_text_item.value = htmlspecialchars_decode(meeting_object.location_sub_province ? meeting_object.location_sub_province : meeting_county_text_item.value);
            this.setTextItemClass(meeting_county_text_item);
            meeting_county_text_item.onkeyup = function () {
                admin_handler_object.setItemValue(this, meeting_id, 'location_sub_province')};
            meeting_county_text_item.onfocus = function () {
                admin_handler_object.handleTextInputFocus(this)};
            meeting_county_text_item.onblur = function () {
                admin_handler_object.handleTextInputBlur(this)};
            meeting_county_text_item.onpaste = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.setItemValue(input, meeting_id, 'location_sub_province');
                }, 0);
            };
            meeting_county_text_item.oncut = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.setItemValue(input, meeting_id, 'location_sub_province');
                }, 0);
            };
        } else {
            if ( meeting_object.location_sub_province ) {
                for (var i = 0; i < meeting_county_select_item.options.length; i++) {
                    var option = meeting_county_select_item.options[i];
                    if ( option.value === meeting_object.location_sub_province ) {
                        meeting_county_select_item.selectedIndex = i;
                        break;
                    }
                }
            }
            meeting_county_select_item.onchange = function () {
                admin_handler_object.reactToMeetingCountySelect(meeting_id);
            }
        }


        if (meeting_state_text_item !== null) {
            meeting_state_text_item.value = htmlspecialchars_decode(meeting_object.location_province ? meeting_object.location_province : meeting_state_text_item.value);
            this.setTextItemClass(meeting_state_text_item);
            meeting_state_text_item.onkeyup = function () {
                admin_handler_object.setItemValue(this, meeting_id, 'location_province')};
            meeting_state_text_item.onfocus = function () {
                admin_handler_object.handleTextInputFocus(this)};
            meeting_state_text_item.onblur = function () {
                admin_handler_object.handleTextInputBlur(this)};
            meeting_state_text_item.onpaste = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.setItemValue(input, meeting_id, 'location_province');
                }, 0);
            };
            meeting_state_text_item.oncut = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.setItemValue(input, meeting_id, 'location_province');
                }, 0);
            };
        } else {
            if ( meeting_object.location_province ) {
                for (var i = 0; i < meeting_state_select_item.options.length; i++) {
                    var option = meeting_state_select_item.options[i];
                    if ( option.value === meeting_object.location_province ) {
                        meeting_state_select_item.selectedIndex = i;
                        break;
                    }
                }
            }
            meeting_state_select_item.onchange = function () {
                admin_handler_object.reactToMeetingStateSelect(meeting_id);}
        }

        
        meeting_zip_text_item.value = htmlspecialchars_decode(meeting_object.location_postal_code_1 ? meeting_object.location_postal_code_1 : meeting_zip_text_item.value);
        this.setTextItemClass(meeting_zip_text_item);
        meeting_zip_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'location_postal_code_1')};
        meeting_zip_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_zip_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_zip_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_postal_code_1'); }, 0);
        };
        meeting_zip_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_postal_code_1'); }, 0);
        };

        
        meeting_nation_text_item.value = htmlspecialchars_decode(meeting_object.location_nation ? meeting_object.location_nation : meeting_nation_text_item.value);
        this.setTextItemClass(meeting_nation_text_item);
        meeting_nation_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'location_nation')};
        meeting_nation_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_nation_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_nation_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_nation'); }, 0);
        };
        meeting_nation_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'location_nation'); }, 0);
        };

        
        meeting_longitude_text_item.value = htmlspecialchars_decode(meeting_object.longitude ? meeting_object.longitude : meeting_longitude_text_item.value);
        this.setTextItemClass(meeting_longitude_text_item);
        meeting_longitude_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'longitude')};
        meeting_longitude_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_longitude_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_longitude_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'longitude'); }, 0);
        };
        meeting_longitude_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'longitude'); }, 0);
        };

        
        meeting_latitude_text_item.value = htmlspecialchars_decode(meeting_object.latitude ? meeting_object.latitude : meeting_latitude_text_item.value);
        this.setTextItemClass(meeting_latitude_text_item);
        meeting_latitude_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'latitude')};
        meeting_latitude_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_latitude_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_latitude_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'latitude'); }, 0);
        };
        meeting_latitude_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'latitude'); }, 0);
        };

        
        meeting_contact_text_item.value = htmlspecialchars_decode(meeting_object.email_contact ? meeting_object.email_contact : meeting_contact_text_item.value);
        this.setTextItemClass(meeting_contact_text_item);
        meeting_contact_text_item.onkeyup = function () {
            admin_handler_object.setItemValue(this, meeting_id, 'email_contact')};
        meeting_contact_text_item.onfocus = function () {
            admin_handler_object.handleTextInputFocus(this)};
        meeting_contact_text_item.onblur = function () {
            admin_handler_object.handleTextInputBlur(this)};
        meeting_contact_text_item.onpaste = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'email_contact'); }, 0);
        };
        meeting_contact_text_item.oncut = function () {
            var input = this;
            setTimeout(function () {
                admin_handler_object.setItemValue(input, meeting_id, 'email_contact'); }, 0);
        };

        
        meeting_published_checkbox.checked = (meeting_object ? true : false);

        this.setFormatCheckboxes(meeting_object);
        this.setWeekday(meeting_object);
        this.setMeetingStartTime(meeting_object);
        this.setMeetingDuration(meeting_object);
        if (g_meeting_time_zones_enabled) {
            this.setMeetingTimeZone(meeting_object);
        }
        this.setServiceBody(meeting_object);
        this.setUpOtherTab(meeting_object);
        
        this.setPublished(meeting_object);
        this.handleNewAddressInfo(meeting_id);
        this.setFormatCheckboxHandlers(meeting_object);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setMeetingStartTime = function (in_meeting_object
                                        ) {
        var meeting_id = in_meeting_object.id_bigint;
        
        var time_hour_select = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_start_hour_select');
        var time_minute_select = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_start_minute_select');
        var time_am_radio = document.getElementById('bmlt_admin_' + meeting_id + '_time_am_radio');
        var time_pm_radio = document.getElementById('bmlt_admin_' + meeting_id + '_time_pm_radio');
        
        var start_time = in_meeting_object.start_time.toString().split(':');

        if ( start_time && (start_time.length > 1) ) {
            var hours = parseInt(start_time[0], 10);
            var minutes = parseInt(start_time[1], 10);
            var midnight = false;
            var noon = false;
            var pm = false;
            
            if ( (hours == 23) && (minutes > 55) ) {
                midnight = true;
            } else {
                if ( (hours == 12) && (minutes == 0) ) {
                    noon = true;
                } else {
                    if ( hours > 23 ) {
                        hours = 23;
                    };
            
                    if ( hours < 0 ) {
                        hours = 0;
                    };

                    if ( minutes > 59 ) {
                        minutes = 59;
                    };
            
                    if ( minutes < 0 ) {
                        minutes = 0;
                    };

                    if ( minutes % g_default_minute_interval ) {
                        minutes += g_default_minute_interval;
                
                        if ( minutes >= 60 ) {
                            hours++;
                            minutes -= 60;
                        };
                    };
                };
            };
                
            if ( hours > 12 ) {
                hours -= 12;
                pm = true;
            } else if ( (hours == 0) && (minutes > 0) ) {
                pm = false;
                hours = 12;
            } else if ( (hours == 12) && (minutes > 0) ) {
                pm = true;
            };
        };
        
        if ( midnight ) {
            BMLT_Admin_setSelectByValue(time_minute_select, 0);
            BMLT_Admin_setSelectByValue(time_hour_select, 0);
        } else if ( noon ) {
            BMLT_Admin_setSelectByValue(time_hour_select, 13);
            BMLT_Admin_setSelectByValue(time_minute_select, 0);
        } else {
            BMLT_Admin_setSelectByValue(time_hour_select, hours);
            BMLT_Admin_setSelectByValue(time_minute_select, minutes);
        };
        
        if ( pm ) {
            time_am_radio.checked = false;
            time_pm_radio.checked = true;
        } else {
            time_pm_radio.checked = false;
            time_am_radio.checked = true;
        };
        
        this.reactToTimeSelect(meeting_id);
        
        time_hour_select.onchange = function () {
            admin_handler_object.reactToTimeSelect(meeting_id); };
        time_minute_select.onchange = function () {
            admin_handler_object.reactToTimeSelect(meeting_id); };
        time_am_radio.onchange = function () {
            admin_handler_object.reactToTimeSelect(meeting_id); };
        time_pm_radio.onchange = function () {
            admin_handler_object.reactToTimeSelect(meeting_id); };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setMeetingDuration = function ( in_meeting_object
                                        ) {
        var meeting_id = in_meeting_object.id_bigint;
        
        var time_hour_select = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_duration_hour_select');
        var time_minute_select = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_duration_minute_select');
        
        var duration_time = in_meeting_object.duration_time.split(':');
        
        if ( duration_time && (duration_time.length > 1) ) {
            var hours = parseInt(duration_time[0], 10);
            var minutes = parseInt(duration_time[1], 10);
            var oe = false;
            
            if ( hours == 24 ) {
                oe = true;
            } else {
                if ( minutes % g_default_minute_interval ) {
                    minutes += g_default_minute_interval;
                
                    if ( minutes >= 60 ) {
                        hours++;
                        minutes -= 60;
                    };
                };
            
                if ( hours > 23 ) {
                    hours = 23;
                };
            
                if ( hours < 0 ) {
                    hours = 0;
                };
            };
        };
        
        if ( oe ) {
            BMLT_Admin_setSelectByValue(time_hour_select, 24);
            BMLT_Admin_setSelectByValue(time_minute_select, 0);
        } else {
            BMLT_Admin_setSelectByValue(time_hour_select, hours);
            BMLT_Admin_setSelectByValue(time_minute_select, minutes);
        };
        
        this.reactToDurationSelect(meeting_id);
        
        time_hour_select.onchange = function () {
            admin_handler_object.reactToDurationSelect(meeting_id); };
    };

    /************************************************************************************//**
     *   \brief
     ****************************************************************************************/
    this.setMeetingTimeZone = function (in_meeting_object) {
        var meeting_id = in_meeting_object.id_bigint;

        var time_zone_select = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_time_zone_select');

        BMLT_Admin_setSelectByStringValue(time_zone_select, in_meeting_object.time_zone);

        this.reactToTimeZoneSelect(meeting_id);

        time_zone_select.onchange = function () { admin_handler_object.reactToTimeZoneSelect(meeting_id); }
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setServiceBody = function (in_meeting_object
                                    ) {
        var meeting_id = in_meeting_object.id_bigint;
        
        var service_body_select = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_sb_select');
        
        if ( service_body_select ) {
            BMLT_Admin_setSelectByValue(service_body_select, in_meeting_object.service_body_bigint);
            
            this.reactToSBSelect(meeting_id);
        
            service_body_select.onchange = function () {
                admin_handler_object.reactToSBSelect(meeting_id); };
        };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setWeekday = function (in_meeting_object
                                ) {
        var meeting_id = in_meeting_object.id_bigint;
        
        var weekday_select = document.getElementById('bmlt_admin_single_meeting_editor_' + meeting_id + '_meeting_weekday_select');
        
        BMLT_Admin_setSelectByValue(weekday_select, in_meeting_object.weekday_tinyint);
            
        this.reactToWeekdaySelect(meeting_id);
        
        weekday_select.onchange = function () {
            admin_handler_object.reactToWeekdaySelect(meeting_id); };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setPublished = function (  in_meeting_object
                                ) {
        var meeting_id = in_meeting_object.id_bigint;
        
        var published_checkbox = document.getElementById('bmlt_admin_meeting_' + meeting_id + '_published_checkbox');
        
        published_checkbox.checked = in_meeting_object.published == '1';
        
        this.reactToPublishedCheck(meeting_id);
        
        published_checkbox.onchange = function () {
            admin_handler_object.reactToPublishedCheck(meeting_id); };
        published_checkbox.onclick = function () {
            admin_handler_object.reactToPublishedCheck(meeting_id); };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setFormatCheckboxHandlers = function (  in_meeting_object
                                                ) {
        var meeting_id = in_meeting_object.id_bigint;
        var main_formats = g_format_object_array;
        
        for (var c = 0; c < main_formats.length; c++) {
            var format_checkbox = document.getElementById('bmlt_admin_meeting_' + in_meeting_object.id_bigint + '_format_' + main_formats[c].id + '_checkbox');
            
            if ( format_checkbox ) {
                format_checkbox.onchange = function () {
                    admin_handler_object.reactToFormatCheckbox(format_checkbox, meeting_id); };
                format_checkbox.onclick = function () {
                    admin_handler_object.reactToFormatCheckbox(format_checkbox, meeting_id); };
            };
        };
    };
        
    // #mark -
    // #mark ########## Meeting Editor Internal Tabs ##########
    // #mark -

    /************************************************************************************//**
    *   \brief  Selects the meeting editor tab.                                             *
    ****************************************************************************************/
    this.selectAnEditorTab = function (
        in_tab_index,
        in_meeting_id
    ) {
        var tabs = new Array(
            document.getElementById('bmlt_admin_meeting_editor_' + in_meeting_id + '_tab_item_basic_a'),
            document.getElementById('bmlt_admin_meeting_editor_' + in_meeting_id + '_tab_item_location_a'),
            document.getElementById('bmlt_admin_meeting_editor_' + in_meeting_id + '_tab_item_format_a'),
            document.getElementById('bmlt_admin_meeting_editor_' + in_meeting_id + '_tab_item_other_a'),
            document.getElementById('bmlt_admin_meeting_editor_' + in_meeting_id + '_tab_item_history_a')
        );
        
        var sheets = new Array(
            document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_basic_sheet_div'),
            document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_location_sheet_div'),
            document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_format_sheet_div'),
            document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_other_sheet_div'),
            document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_history_sheet_div')
        );
        
        for (var c = 0; c < tabs.length; c++) {
            var tab_class = '';
            var sheet_class = '';
            
            if ( c == in_tab_index ) {
                tab_class = 'bmlt_admin_meeting_editor_tab_item_a_selected';
                sheet_class = 'bmlt_admin_meeting_option_sheet_div';
                if ( c == 4 ) {
                    this.openHistoryTab(in_meeting_id);
                };
            } else {
                tab_class = 'bmlt_admin_meeting_editor_tab_item_a_unselected';
                if ( c == 4 ) {
                    tab_class += ' hide_in_new_meeting';
                };
                sheet_class = 'bmlt_admin_meeting_option_sheet_div item_hidden';
            };
                
            tabs[c].className = tab_class;
            sheets[c].className = sheet_class;
        };
    };
        
    // #mark -
    // #mark Basic Tab
    // #mark -
        
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.respondToBasicTabSelection = function (in_meeting_id   ///< The BMLT ID of the meeting that is being edited.
                                                ) {
        this.selectAnEditorTab(0);
    };
        
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.reactToTimeSelect = function (  in_meeting_id   ///< The meeting ID
                                    ) {
        var time_hour_select = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_start_hour_select');
        var time_items = document.getElementById('bmlt_admin_' + in_meeting_id + '_time_span');
        var time_pm_radio = document.getElementById('bmlt_admin_' + in_meeting_id + '_time_pm_radio');
        var time_minute_select = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_start_minute_select');

        time_items.className = 'bmlt_admin_time_span' + (( (time_hour_select.options[time_hour_select.selectedIndex].value == 0) || (time_hour_select.options[time_hour_select.selectedIndex].value == 13) ) ? ' item_hidden' : '');
        
        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;
        
        var timeval = '';
        
        if ( time_hour_select.options[time_hour_select.selectedIndex].value == 0 ) {
            timeval = '23:59:00';
        } else if ( time_hour_select.options[time_hour_select.selectedIndex].value == 13 ) {
            timeval = '12:00:00';
        } else {
            var hour = parseInt(time_hour_select.options[time_hour_select.selectedIndex].value, 10);
            var minute = parseInt(time_minute_select.options[time_minute_select.selectedIndex].value, 10)
            
            if ( time_pm_radio.checked && (hour != 12) ) {
                hour += 12;
            } else if ( !time_pm_radio.checked && (hour == 12) ) {
                hour = 0;
            }
            
            timeval = sprintf('%02d:%02d:00', hour, minute);
        };
            
        the_meeting_object.start_time = timeval;
        this.validateMeetingEditorButton(in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.reactToDurationSelect = function (  in_meeting_id   ///< The meeting ID
                                    ) {
        var time_hour_select = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_duration_hour_select');
        var time_items = document.getElementById('bmlt_admin_' + in_meeting_id + '_duration_span');

        time_items.className = 'bmlt_admin_time_span' + ((time_hour_select.value == 24) ? ' item_hidden' : '');
        
        var time_minute_select = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_duration_minute_select');
        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;
        var timeval = '';
        
        if ( time_hour_select.options[time_hour_select.selectedIndex].value == 24 ) {
            timeval = '24:00:00';
        } else {
            timeval = sprintf('%02d:%02d:00', parseInt(time_hour_select.options[time_hour_select.selectedIndex].value, 10), parseInt(time_minute_select.options[time_minute_select.selectedIndex].value, 10));
        };
            
        the_meeting_object.duration_time = timeval;
        this.validateMeetingEditorButton(in_meeting_id);
    };

    this.reactToMeetingStateSelect = function (in_meeting_id) {
        var meeting_state_select_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_state_select_input');
        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;
        the_meeting_object.location_province = meeting_state_select_item.options[meeting_state_select_item.selectedIndex].value;
        this.handleNewAddressInfo(in_meeting_id);
        this.validateMeetingEditorButton(in_meeting_id);
    };

    this.reactToMeetingCountySelect = function (in_meeting_id) {
        var meeting_county_select_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_county_select_input');
        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;
        the_meeting_object.location_sub_province = meeting_county_select_item.options[meeting_county_select_item.selectedIndex].value;
        this.handleNewAddressInfo(in_meeting_id);
        this.validateMeetingEditorButton(in_meeting_id);
    };

    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.reactToSBSelect = function (in_meeting_id   ///< The meeting ID
                                    ) {
        var service_body_select = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_sb_select');
        
        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;
        
        the_meeting_object.service_body_bigint = service_body_select.value.toString();
        this.validateMeetingEditorButton(in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.reactToPublishedCheck = function (  in_meeting_id   ///< The meeting ID
                                            ) {
        var published_checkbox = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_published_checkbox');
        
        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;
        the_meeting_object.published = published_checkbox.checked ? '1' : '0';

        var unpublishedNoteDiv = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_unpublished_note_div');
        if (published_checkbox.checked) {
            unpublishedNoteDiv.className += ' item_hidden';
        } else {
            unpublishedNoteDiv.className = unpublishedNoteDiv.className.replace(" item_hidden", "");
        }
        this.validateMeetingEditorButton(in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.reactToWeekdaySelect = function (  in_meeting_id   ///< The meeting ID
                                            ) {
        var weekday_select = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_weekday_select');
        
        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;
        
        the_meeting_object.weekday_tinyint = weekday_select.value.toString();
        this.validateMeetingEditorButton(in_meeting_id);
    };

    /************************************************************************************//**
     *   \brief
     ****************************************************************************************/
    this.reactToTimeZoneSelect = function (  in_meeting_id   ///< The meeting ID
    ) {
        var time_zone_select = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_time_zone_select');

        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;

        the_meeting_object.time_zone = time_zone_select.value.toString();
        this.validateMeetingEditorButton(in_meeting_id);
    };

    // #mark -
    // #mark Location Tab
    // #mark -
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.handleNewAddressInfo = function ( in_meeting_id       ///< The BMLT ID of the meeting being edited.
                                        ) {
        var meeting_street_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_street_text_input');
        var meeting_borough_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_borough_text_input');
        var meeting_city_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_city_text_input');
        var meeting_state_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_state_text_input');
        var meeting_state_select_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_state_select_input');
        var meeting_zip_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_zip_text_input');
        var meeting_nation_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_nation_text_input');

        if ( meeting_street_text_item && meeting_borough_text_item && meeting_city_text_item && ( meeting_state_text_item || meeting_state_select_item ) && meeting_zip_text_item && meeting_nation_text_item ) {
            var street_text = meeting_street_text_item.value;
            var borough_text = meeting_borough_text_item.value;
            var city_text = meeting_city_text_item.value;
            var state_text = meeting_state_text_item ? meeting_state_text_item.value : meeting_state_select_item.options[meeting_state_select_item.selectedIndex].value;
            var zip_text = meeting_zip_text_item.value;
            var nation_text = meeting_nation_text_item.value;
        };
    };


    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.getAddressLine = function (in_meeting_id) {
        var meeting_street_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_street_text_input');
        var meeting_borough_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_borough_text_input');
        var meeting_city_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_city_text_input');
        var meeting_state_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_state_text_input');
        var meeting_state_select_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_state_select_input');
        var meeting_zip_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_zip_text_input');
        var meeting_nation_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_nation_text_input');

        var street_text = (meeting_street_text_item.value != meeting_street_text_item.defaultValue) ? meeting_street_text_item.value : '';
        var borough_text = (meeting_borough_text_item.value != meeting_borough_text_item.defaultValue) ? meeting_borough_text_item.value : '';
        var city_text = (meeting_city_text_item.value != meeting_city_text_item.defaultValue) ? meeting_city_text_item.value : '';
        var state_text = meeting_state_text_item ? ((meeting_state_text_item.value != meeting_state_text_item.defaultValue) ? meeting_state_text_item.value : '') : meeting_state_select_item.options[meeting_state_select_item.selectedIndex].value;
        var zip_text = (meeting_zip_text_item.value != meeting_zip_text_item.defaultValue) ? meeting_zip_text_item.value : '';
        var nation_text = (meeting_nation_text_item.value != meeting_nation_text_item.defaultValue) ? meeting_nation_text_item.value : '';
        
        if ( !nation_text ) {
            nation_text = g_region_bias;
        };
        
        // What we do here, is try to create a readable address line to be sent off for geocoding. We just try to clean it up as much as possible.
        var address_line = sprintf('%s,%s,%s,%s,%s,%s', street_text, borough_text, city_text, state_text, zip_text, nation_text);
        
        address_line = address_line.replace(/,+/g, ', ');
        address_line = address_line.replace(/^, /g, '');
        address_line = address_line.replace(/, $/g, '');
        return address_line;
    };

    this.lookupLocation = function (in_meeting_id, successCallback) {
        if (!g_google_api_key_is_good) {
            alert("The meeting couldn't be saved because there is a problem with the Google Maps API Key.");
            return;
        }

        var address_line = this.getAddressLine(in_meeting_id);
        var editor_object = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var the_meeting_object = editor_object.meeting_object;

        if ( address_line != ', ' ) {
            if ( !the_meeting_object.m_geocoder ) {
                the_meeting_object.m_geocoder = new google.maps.Geocoder;
            }

            the_meeting_object.m_geocoder.geocode(
                { 'address' : address_line },
                function ( in_geocode_response, status ) {
                    if (status !== 'OK') {
                        alert(g_meeting_lookup_failed);
                        return;
                    }
                    admin_handler_object.sGeoCallback(in_geocode_response, in_meeting_id);
                    if (successCallback) {
                        successCallback();
                    }
                }
            );
        } else {
            alert(g_meeting_lookup_failed_not_enough_address_info);
        }
    };
    /****************************************************************************************//**
    *   \brief This catches the AJAX response, and fills in the response form.                  *
    ********************************************************************************************/
    
    this.sGeoCallback = function (
        in_geocode_response,    ///< The JSON object.
        in_meeting_id           ///< The ID of the meeting.
    ) {
        var meeting_editor = document.getElementById('bmlt_admin_single_meeting_editor_' + parseInt(in_meeting_id, 10) + '_div');

        if ( meeting_editor ) {
            var the_meeting_object = meeting_editor.meeting_object;

            if ( in_geocode_response && in_geocode_response.length && (google.maps.OK == in_geocode_response[0].status) ) {
                var lng = in_geocode_response[0].geometry.location.lng();
                var lat = in_geocode_response[0].geometry.location.lat();
                
                delete  the_meeting_object.m_geocoder;
                
                var map_center = new google.maps.LatLng(lat, lng);
                this.setMeetingLongLat(map_center, in_meeting_id);
                if (meeting_editor.main_map) {
                    meeting_editor.main_map.panTo(map_center);
                    this.displayMainMarkerInMap(in_meeting_id);
                    google.maps.event.removeListener(the_meeting_object.m_geocoder);
                }

                if (g_auto_geocoding_enabled && g_county_auto_geocoding_enabled) {
                    var meeting_county_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_county_text_input');
                    if (meeting_county_text_item) {
                        for (var i = 0; i < in_geocode_response[0].address_components.length; i++) {
                            var component = in_geocode_response[0].address_components[i];
                            if (component.types && component.types[0] === "administrative_area_level_2") {
                                var county = component.long_name;
                                if (county.endsWith(" County")) {
                                    county = county.substring(0, county.length - 7);
                                }
                                meeting_county_text_item.value = county;
                                this.setItemValue(meeting_county_text_item, in_meeting_id, 'location_sub_province');
                                break;
                            }
                        }
                    }
                }

                if (g_auto_geocoding_enabled && g_county_auto_geocoding_enabled) {
                    var meeting_zip_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_zip_text_input');
                    if (meeting_zip_text_item) {
                        if (meeting_county_text_item) {
                            for (var i = 0; i < in_geocode_response[0].address_components.length; i++) {
                                var component = in_geocode_response[0].address_components[i];
                                if (component.types && component.types[0] === "postal_code") {
                                    var zipCode = component.long_name;
                                    meeting_zip_text_item.value = zipCode;
                                    this.setItemValue(meeting_zip_text_item, in_meeting_id, 'location_postal_code_1');
                                    break;
                                }
                            }
                        }
                    }
                }
                
                this.validateMeetingEditorButton(in_meeting_id);
            } else {
                alert(in_geocode_response[0].status.toString());
            };
        } else {
            alert(g_meeting_lookup_failed);
        };
    };
    
    /************************************************************************************//**
    *   \brief This toggles the map disclosure.                                             *
    ****************************************************************************************/
    this.toggleMeetingMapDisclosure = function ( in_meeting_id       ///< The meeting ID of the editor that gets this map.
                                                ) {
        var root_element = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var map_disclosure_div = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_map_disclosure_div');
        var map_div = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_map_div');
        var longlat_div = document.getElementById('bmlt_admin_single_location_' + in_meeting_id + '_long_lat_div');
        
        root_element.map_disclosed = !root_element.map_disclosed;

        map_disclosure_div.className = 'bmlt_admin_single_meeting_disclosure_map_div' + (root_element.map_disclosed ? '_open' : '_closed');
        map_div.className = 'bmlt_admin_single_meeting_map_div' + (root_element.map_disclosed ? '' : ' item_hidden');
        longlat_div.className = root_element.map_disclosed ? 'bmlt_admin_single_location_long_lat_div item_hidden' : 'bmlt_admin_single_location_long_lat_div';
        
        if ( root_element.map_disclosed && !root_element.main_map ) {
            root_element.main_map = this.createEditorMap(root_element, in_meeting_id);
        };
    };
    
    /************************************************************************************//**
    *   \brief This creates the map for the editor.                                         *
    *   \returns the map object.                                                            *
    ****************************************************************************************/
    this.createEditorMap = function (
        in_editor_parent,   ///< The main editor div object.
        in_meeting_id       ///< The meeting ID of the editor that gets this map.
    ) {
        var meeting_map_holder = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_inner_map_div');
        var map_center = new google.maps.LatLng(in_editor_parent.meeting_object.latitude, in_editor_parent.meeting_object.longitude);

        var myOptions = {
            'center': map_center,
            'zoom': in_editor_parent.meeting_object.zoom,
            'mapTypeId': google.maps.MapTypeId.ROADMAP,
            'mapTypeControlOptions': { 'style': google.maps.MapTypeControlStyle.DROPDOWN_MENU },
            'zoomControl': true,
            'mapTypeControl': true,
            'disableDoubleClickZoom' : true,
            'draggableCursor': "crosshair",
            'scaleControl' : true
        };

        myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.LARGE };

        in_editor_parent.m_main_map = new google.maps.Map(meeting_map_holder, myOptions);
    
        if ( in_editor_parent.m_main_map ) {
            in_editor_parent.m_main_map.setOptions({'scrollwheel': false});   // For some reason, it ignores setting this in the options.
            google.maps.event.addListener(in_editor_parent.m_main_map, 'click', function (in_event) {
                admin_handler_object.respondToMapClick(in_event, in_meeting_id); });
            in_editor_parent.m_main_map.initialcall = google.maps.event.addListener(in_editor_parent.m_main_map, 'tilesloaded', function (in_event) {
                admin_handler_object.tilesLoaded(in_meeting_id); });
            this.displayMainMarkerInMap(in_meeting_id);
        };
            
        return ( in_editor_parent.m_main_map );
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.tilesLoaded = function (in_meeting_id   ///< The meeting this map is associated with.
                                ) {
        var root_element = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');

        if ( root_element && root_element.main_map && root_element.m_main_map.initialcall ) {
            google.maps.event.removeListener(root_element.m_main_map.initialcall);
            root_element.m_main_map.initialcall = null;
        };
        
        this.displayMainMarkerInMap(in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.respondToMapClick = function (
        in_event,       ///< The Google Maps event
        in_meeting_id   ///< The meeting this map is associated with.
    ) {
        var root_element = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var map_center = in_event.latLng;
        root_element.main_map.panTo(map_center);
        this.setMeetingLongLat(map_center, in_meeting_id);
        this.displayMainMarkerInMap(in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.respondToMarkerDragEnd = function (
        in_event,       ///< The Google Maps event
        in_meeting_id   ///< The meeting this map is associated with.
    ) {
        var root_element = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var map_center = in_event.latLng;
        root_element.main_map.panTo(map_center);
        this.setMeetingLongLat(map_center, in_meeting_id);
    };

    /************************************************************************************//**
    *   \brief This displays the "Your Position" marker in the results map.                 *
    ****************************************************************************************/
    this.displayMainMarkerInMap = function (    in_meeting_id   ///< The meeting this map is associated with.
                                            ) {
        var root_element = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');

        if ( root_element && root_element.main_map ) {
            if ( root_element.main_map.main_marker ) {
                root_element.main_map.main_marker.setMap(null);
                root_element.main_map.main_marker = null;
            };
            
            m_icon_image = new google.maps.MarkerImage(g_style_dir + "/images/NACenterMarker.png", new google.maps.Size(21, 36), new google.maps.Point(0,0), new google.maps.Point(11, 36));
            m_icon_shadow = new google.maps.MarkerImage(g_style_dir + "/images/NACenterMarkerS.png", new google.maps.Size(43, 36), new google.maps.Point(0,0), new google.maps.Point(11, 36));

            root_element.main_map.main_marker = new google.maps.Marker({
                'position':     root_element.main_map.getCenter(),
                'map':          root_element.main_map,
                'icon':         m_icon_image,
                'shadow':       m_icon_shadow,
                'clickable':    false,
                'cursor':       'pointer',
                'draggable':    true
                                                                        });
            google.maps.event.addListener(root_element.main_map.main_marker, 'dragend', function (in_event) {
                admin_handler_object.respondToMarkerDragEnd(in_event, in_meeting_id); });
        };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setMeetingLongLat = function (
        in_longLat,
        in_meeting_id
    ) {
        var root_element = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div');
        var meeting_object = root_element.meeting_object;
        var meeting_longitude_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_longitude_text_input');
        var meeting_latitude_text_item = document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_meeting_latitude_text_input');
                
        var lng = (Math.round(100000000.0 * in_longLat.lng()) / 100000000.0);
        var lat = (Math.round(100000000.0 * in_longLat.lat()) / 100000000.0);
        
        meeting_object.longitude = lng;
        meeting_object.latitude = lat;
        
        meeting_longitude_text_item.value = htmlspecialchars_decode(meeting_object.longitude);
        meeting_latitude_text_item.value = htmlspecialchars_decode(meeting_object.latitude);
        
        this.setItemValue(meeting_longitude_text_item, in_meeting_id, 'longitude');
        this.setItemValue(meeting_latitude_text_item, in_meeting_id, 'latitude');
        
        this.validateMeetingEditorButton(in_meeting_id);
    };
        
    // #mark -
    // #mark Format Tab
    // #mark -
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setFormatCheckboxes = function (in_meeting_object
                                        ) {
        var format_keys_string = '';
        
        if (in_meeting_object) {
            if (in_meeting_object.format_shared_id_list) {
                format_keys_string = in_meeting_object.format_shared_id_list;
            };
        };
            
        var format_keys = new Array();
        
        // God, this is such an abomination.
        // If the format is one integer, or badly formed, then it might not split, in which case, we need to parse it.
        if (format_keys_string) {
            var splittee = format_keys_string.split(',');
            if (splittee && splittee.length) {
                    format_keys = splittee.map((v) => {return parseInt(v);});
            } else {
                format_keys[0] = parseInt(format_keys_string);
            };
        };
        
        var main_formats = g_format_object_array;
        for (var c = 0; c < main_formats.length; c++) {
            var format_checkbox = document.getElementById('bmlt_admin_meeting_' + in_meeting_object.id_bigint + '_format_' + main_formats[c].id + '_checkbox');
            
            if ( format_checkbox ) {
                for (var i = 0; i < format_keys.length; i++) {
                    if ( format_checkbox.value == format_keys[i] ) {
                        format_checkbox.checked = true;
                        break;
                    };
                };
            };
        };
        
        this.reactToFormatCheckbox(null, in_meeting_object.id_bigint);
        
        // The reason we do this whacky stuff, is that the formats may not be in the same order from the server as we keep them, so this little dance re-orders them.
        if ( in_meeting_object.id_bigint && this.m_search_results && this.m_search_results.length ) {
            for (var c = 0; c < this.m_search_results.length; c++) {
                if ( in_meeting_object.id_bigint == this.m_search_results[c].id_bigint ) {
                    this.m_search_results[c].formats = in_meeting_object.formats;
                    break;
                };
            };
        };
        
        this.validateMeetingEditorButton(in_meeting_object.id_bigint);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.reactToFormatCheckbox = function (
        in_checkbox_object,
        in_meeting_id
    ) {
        var format_array = new Array;
        var main_formats = g_format_object_array;
        
        for (var c = 0; c < main_formats.length; c++) {
            var format_checkbox = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_format_' + main_formats[c].id + '_checkbox');
            
            if ( format_checkbox && format_checkbox.checked ) {
                format_array[format_array.length] = parseInt(format_checkbox.value);
            };
        };

        format_array.sort(function(a,b) {return a-b;});

        document.getElementById('bmlt_admin_single_meeting_editor_' + in_meeting_id + '_div').meeting_object.format_shared_id_list = format_array.join(',');
        this.validateMeetingEditorButton(in_meeting_id);
    };
            
    // #mark -
    // #mark Other Tab
    // #mark -
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.setUpOtherTab = function ( in_meeting_object
                                    ) {
        for (var c = 0; c < g_other_field_ids.length; c++) {
            var element = g_other_field_ids[c];
            var val = in_meeting_object[element];
            var elementID = 'bmlt_admin_single_meeting_editor_' + in_meeting_object.id_bigint + '_meeting_' + element + '_text_input';
            var text_field = document.getElementById(elementID);
            
            var def_val = text_field.value;

            if ( val ) {
                var val_ar = val.toString().split("#@-@#");
                
                if ( val_ar && (val_ar.length == 2 ) ) {
                    val = val_ar[1];
                } else if ( val_ar && (val_ar.length == 3 ) ) {
                    val = val_ar[2];
                };
                    
                text_field.value = htmlspecialchars_decode(val);
            };
            
            this.setTextItemClass(text_field);
            text_field.meeting_id = in_meeting_object.id_bigint;
            text_field.element = element.toString();
            text_field.onkeyup = function () {
                admin_handler_object.setItemValue(this)};
            text_field.onfocus = function () {
                admin_handler_object.handleTextInputFocus(this)};
            text_field.onblur = function () {
                admin_handler_object.handleTextInputBlur(this)};
            text_field.onpaste = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.setItemValue(input); }, 0);
            };
            text_field.oncut = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.setItemValue(input); }, 0);
            };

            text_field = null;
        };
    };
    
    // #mark -
    // #mark History Tab
    // #mark -
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.openHistoryTab = function ( in_meeting_id
                                    ) {
        var option_sheet = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_history_sheet_div');
        var history_list = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_history_list_div');
        
        if ( !history_list ) {
            this.fetchMeetingHistory(in_meeting_id);
        };
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.fetchMeetingHistory = function ( in_meeting_id
                                        ) {
        var new_editor = document.getElementById('bmlt_admin_single_meeting_editor_' + parseInt(in_meeting_id, 10) + '_div');
        var uri = g_ajax_callback_uri + '&get_meeting_history=' + in_meeting_id;

        if ( new_editor.m_ajax_request_in_progress ) {
            new_editor.m_ajax_request_in_progress.abort();
            new_editor.m_ajax_request_in_progress = null;
        };
        
        var salt = new Date();
        uri += '&salt=' + salt.getTime();
        
        new_editor.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req,in_id) {
            admin_handler_object.fetchMeetingHistoryAJAXCallback(in_req,in_id); }, 'get', in_meeting_id);
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.fetchMeetingHistoryAJAXCallback = function (
        in_http_request,
        in_meeting_id
    ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
        };
            
        if ( json_object ) {
            var meeting_changed = false;
            
            if ( json_object.error ) {
                alert(json_object.report);
            } else {
                document.getElementById('bmlt_admin_history_ajax_button_' + in_meeting_id + '_throbber_div').className = 'bmlt_admin_history_ajax_button_throbber_div item_hidden';
                var option_sheet = document.getElementById('bmlt_admin_meeting_' + in_meeting_id + '_history_sheet_div');
                var history_list = document.createElement('div');
                history_list.id = 'bmlt_admin_meeting_' + in_meeting_id + '_history_list_div';
                history_list.className = 'bmlt_admin_meeting_history_list_div';
                
                if ( json_object.length ) {
                    for (var c = 0; c < json_object.length; c++) {
                        var history_item = document.createElement('div');
                        history_item.id = 'bmlt_admin_meeting_' + in_meeting_id + '_history_' + json_object[c].id + '_list_item_div';
                        history_item.className = 'bmlt_admin_meeting_history_list_item_div';
                    
                        var header_items = sprintf(g_history_header_format.toString().replace(/&gt;/g, '>').replace(/&lt;/g, '<'), json_object[c].date, json_object[c].user);
                        history_item.innerHTML = header_items;
                    
                        if ( json_object[c].description.length ) {
                            var item = document.createElement('div');
                            item.className = 'bmlt_admin_meeting_history_list_item_line_div bmlt_admin_meeting_history_list_item_description_div';
                            for (var i = 0; i < json_object[c].description.length; i++) {
                                var inner_item = document.createElement('div');
                                inner_item.className = 'bmlt_admin_meeting_history_list_item_description_line_div';
                                inner_item.appendChild(document.createTextNode(json_object[c].description[i].toString().replace(/&quot;/g, '"')));
                                item.appendChild(inner_item);
                            };
                            
                            history_item.appendChild(item);
                        };
                    
                        history_list.appendChild(history_item);
                    };
                } else {
                    history_list.innerHTML = g_history_no_history_available_text.toString().replace(/&gt;/g, '>').replace(/&lt;/g, '<');
                };
                
                option_sheet.appendChild(history_list);
            };
        };
    };
        
    // #mark -
    // #mark ########## Service Body Editor Section ##########
    // #mark -
        
    /************************************************************************************//**
    *   \brief  Toggles the visibility of the Service body editor section.                  *
    ****************************************************************************************/
    this.toggleServiceBodyEditor = function () {
        this.m_service_body_editor_panel_shown = !this.m_service_body_editor_panel_shown;
        
        var the_disclosure_div = document.getElementById('bmlt_admin_service_body_editor_disclosure_div');
        var the_editor_div = document.getElementById('bmlt_admin_service_body_editor_wrapper_div');
        
        if ( this.m_service_body_editor_panel_shown ) {
            the_disclosure_div.className = 'bmlt_admin_service_body_editor_disclosure_div bmlt_admin_service_body_editor_disclosure_div_open';
            the_editor_div.className = 'bmlt_admin_service_body_editor_wrapper_div';
            this.showWarningAlert();
        } else {
            the_disclosure_div.className = 'bmlt_admin_service_body_editor_disclosure_div bmlt_admin_service_body_editor_disclosure_div_closed';
            the_editor_div.className = 'bmlt_admin_service_body_editor_wrapper_div bmlt_admin_service_body_editor_wrapper_div_hidden';
        };
    };

    /************************************************************************************//**
    *   \brief  This sets up the Service Body Editor for the selected Service body.         *
    ****************************************************************************************/
    this.populateServiceBodyEditor = function () {
        var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
        var service_body_select = document.getElementById('bmlt_admin_single_service_body_editor_sb_select');
        
        var sb_id = 0;

        if ( main_service_body_editor.service_body_object && (this.isServiceBodyDirty() != null) ) {
            if ( !confirm(g_service_body_dirty_confirm_text) ) {
                service_body_select.selectedIndex = this.isServiceBodyDirty();
                return;
            };
        };
        
        var index = 0;
        var selected_service_body_object = null;
        
        if ( service_body_select ) {  // If we are able to switch between multiple Service bodies, we can do so here.
            index = service_body_select.selectedIndex;
            if ( service_body_select.options[index] ) {
                sb_id = service_body_select.options[index].value;
            } else {
                sb_id = 0;
            };
        } else {
            sb_id = g_editable_service_bodies_array[0][0];
        };
        
        var save_button_a = document.getElementById('bmlt_admin_service_body_editor_form_service_body_save_button');
        
        if ( sb_id == 0 ) {  // See if we will be creating a new one.
            save_button_a.innerHTML = g_service_body_create_button;
            main_service_body_editor.className = 'bmlt_admin_single_service_body_editor_div bmlt_admin_new_sb_editor';
            selected_service_body_object = this.makeANewSB();
        } else {
            var sb_object = null;
            
            for (var c = 0; c < g_service_bodies_array.length; c++) {
                if ( g_service_bodies_array[c][0] == sb_id ) {
                    sb_object = g_service_bodies_array[c];
                    break;
                };
            };
            
            save_button_a.innerHTML = g_service_body_save_button;
            main_service_body_editor.className = 'bmlt_admin_single_service_body_editor_div';
            // This makes a copy of the Service body object, so we can modify it.
            selected_service_body_object = JSON.parse(JSON.stringify(sb_object));
        };

        main_service_body_editor.service_body_object = selected_service_body_object;
        
        var id_display = document.getElementById('service_body_admin_id_display');
        id_display.innerHTML = selected_service_body_object[0].toString();
        
        this.setServiceBodyUser(selected_service_body_object);
            
        var name_text_input = document.getElementById('bmlt_admin_service_body_editor_sb_name_text_input');
        
        name_text_input.value = htmlspecialchars_decode(selected_service_body_object[2]);
        this.handleTextInputLoad(name_text_input, g_service_body_name_default_prompt_text);
        
        var description_textarea = document.getElementById('bmlt_admin_sb_description_textarea');
        
        description_textarea.value = htmlspecialchars_decode(selected_service_body_object[3]);
        this.handleTextInputLoad(description_textarea, g_service_body_description_default_prompt_text);

        var email_text_input = document.getElementById('bmlt_admin_service_body_editor_sb_email_text_input');
        
        email_text_input.value = htmlspecialchars_decode(selected_service_body_object[6]);
        this.handleTextInputLoad(email_text_input, g_service_body_email_default_prompt_text);

        var uri_text_input = document.getElementById('bmlt_admin_service_body_editor_sb_uri_text_input');
        
        uri_text_input.value = htmlspecialchars_decode(selected_service_body_object[7]);
        this.handleTextInputLoad(uri_text_input, g_service_body_uri_default_prompt_text);
        
        var world_id_text_input = document.getElementById('bmlt_admin_single_service_body_editor_world_cc_text_input');

        world_id_text_input.value = htmlspecialchars_decode(selected_service_body_object[12]);
        var naws_link_div = document.getElementById('service_body_editor_naws_link_div');
        var naws_link_a = document.getElementById('service_body_editor_naws_link_a');

        var helpline_text_input = document.getElementById('bmlt_admin_single_service_body_editor_helpline_text_input');
        
        helpline_text_input.value = htmlspecialchars_decode(selected_service_body_object[8]);
        this.handleTextInputLoad(helpline_text_input, g_service_body_helpline_default_prompt_text);
        
        if ( selected_service_body_object[12] ) { // We only show the NAWS dump link if we have a World ID.
            naws_link_div.className = 'naws_link_div';
            naws_link_a.href = 'client_interface/csv/?switcher=GetNAWSDump&sb_id=' + selected_service_body_object[0];
        } else {
            naws_link_div.className = 'item_hidden';
        };
        
        this.handleTextInputLoad(world_id_text_input, g_service_body_world_cc_default_prompt_text, true);
        
        var type_select = document.getElementById('bmlt_admin_single_service_body_editor_type_select');
        
        if ( type_select ) {
            var setIndex = 0;
    
            for (var c = 0; c < type_select.options.length; c ++) {
                if ( type_select.options[c].value == selected_service_body_object[9] ) {
                    setIndex = c;
                    break;
                };
            };
        
            type_select.selectedIndex = setIndex;
        };
        
        var service_body_parent_select = document.getElementById('bmlt_admin_single_service_body_editor_parent_select');

        if ( service_body_parent_select ) {
            for (var c = 0; c < service_body_parent_select.options.length; c++) {
                service_body_parent_select.options[c].selected = false;
                service_body_parent_select.options[c].disabled = false;
            };
        
            if ( document.getElementById('parent_popup_option_' + selected_service_body_object[0]) ) {
                document.getElementById('parent_popup_option_' + selected_service_body_object[0]).disabled = true;
            };
            
            if ( document.getElementById('parent_popup_option_' + selected_service_body_object[1]) ) {
                document.getElementById('parent_popup_option_' + selected_service_body_object[1]).selected = true;
            };
        };
        
        this.setServiceBodyEditorCheckboxes();
        
        this.validateServiceBodyEditorButtons();
        
        var perm_checkbox = document.getElementById('bmlt_admin_service_body_delete_perm_checkbox');
        if ( perm_checkbox ) {
            perm_checkbox.checked = false;
        };
            
    };

    /************************************************************************************//**
    *   \brief  This simply creates a brand-new, unsullied Service body object.             *
    *   \returns a new Service body object (which is really an array).                      *
    ****************************************************************************************/
    this.makeANewSB = function () {
        var new_service_body_object = new Array;
        new_service_body_object[0] = 0;
        new_service_body_object[1] = 0;
        new_service_body_object[2] = '';
        new_service_body_object[3] = '';
        new_service_body_object[4] = g_users[1][0];
        new_service_body_object[5] = '0';
        new_service_body_object[6] = '';
        new_service_body_object[7] = '';
        new_service_body_object[8] = '';
        new_service_body_object[9] = 'AS';
        new_service_body_object[10] = true;
        new_service_body_object[11] = true;
        new_service_body_object[12] = '';
        
        return new_service_body_object;
    };
    
    /************************************************************************************//**
    *   \brief  This handles display of the Service body user.                              *
    ****************************************************************************************/
    this.setServiceBodyUser = function ( in_selected_service_body    ///< The selected Service body array element (which is an array)
                                        ) {
        // One of these will be available -they are mutually exclusive.
        var user_single_display = document.getElementById('single_user_service_body_admin_span');
        var user_select_element = document.getElementById('bmlt_admin_single_service_body_editor_principal_user_select');
        var sb_primary_user = null;
        var user_index = 0; // This will be used to correlate the user to the position in the menu.
        
        // Get the user info that corresponds to this Service body's primary user.
        for (; user_index < g_users.length; user_index++) {
            if ( g_users[user_index][0] == in_selected_service_body[4] ) {
                sb_primary_user = g_users[user_index];
                break;
            };
        };
        
        if ( sb_primary_user ) {
            if ( user_single_display ) {
                user_single_display.innerHTML = sb_primary_user[2];
            } else if ( user_select_element ) {
                for (var c = 0; c < user_select_element.options.length; c++) {
                    if ( user_select_element.options[c].value == in_selected_service_body[4] ) {
                        user_select_element.selectedIndex = c;
                        break;
                    };
                };
            };
        };
    };

    /************************************************************************************//**
    *   \brief  This is called when a Service Body Editor checkbox is clicked.              *
    ****************************************************************************************/
    this.setServiceBodyEditorCheckboxes = function () {
        var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
        
        var users = main_service_body_editor.service_body_object[5].toString().split(',');
        
        if ( users && users.length && g_users && g_users.length ) {
            for (var c = 0; c < g_users.length; c++) {
                var checkbox = document.getElementById('service_body_admin_editor_user_' + g_users[c][0] +'_checkbox');
                if ( checkbox ) {
                    checkbox.checked = false;
                    
                    for (var i = 0; i < users.length; i++) {
                        if ( users[i] == g_users[c][0] ) {
                            checkbox.checked = true;
                            break;
                        };
                    };
                };
            };
        };
    };

    /************************************************************************************//**
    *   \brief  This is called when a Service Body Editor checkbox is clicked.              *
    ****************************************************************************************/
    this.serviceBodyUserChecboxHandler = function (
        in_user_id,         ///< The ID of the user.
        in_checkbox_object  ///< The Checkbox DOM object
    ) {
        var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
        
        var new_users = new Array;
        
        if ( g_users && g_users.length ) {
            for (var c = 0; c < g_users.length; c++) {
                var checkbox = document.getElementById('service_body_admin_editor_user_' + g_users[c][0] +'_checkbox');
                if ( checkbox && checkbox.checked ) {
                    new_users[new_users.length] = g_users[c][0];
                };
            };
        };
        
        if ( new_users.length ) {
            main_service_body_editor.service_body_object[5] = new_users.join(',').toString();
        } else {
            main_service_body_editor.service_body_object[5] = '0';
        };
        
        this.validateServiceBodyEditorButtons();
    };
    
    /************************************************************************************//**
    *   \brief  This sets the state of the Service body editor save button.                 *
    ****************************************************************************************/
    this.validateServiceBodyEditorButtons = function () {
        var save_button = document.getElementById('bmlt_admin_service_body_editor_form_service_body_save_button');
        var cancel_button = document.getElementById('bmlt_admin_service_body_editor_form_meeting_template_cancel_button');
        
        if ( this.isServiceBodyDirty() != null ) {
            save_button.className = 'bmlt_admin_ajax_button button';
            cancel_button.className = 'bmlt_admin_ajax_button button';
        } else {
            save_button.className = 'bmlt_admin_ajax_button button_disabled';
            cancel_button.className = 'bmlt_admin_ajax_button button_disabled';
        };
        
        var delete_span = document.getElementById('service_body_editor_delete_span');
        
        if ( delete_span ) {
            document.getElementById('bmlt_admin_meeting_editor_form_service_body_delete_button').className = 'bmlt_admin_ajax_button button';  // We set this in either case, as IE "sticks."
            if ( g_service_bodies_array.length > 1 ) {
                delete_span.className = 'bmlt_admin_meeting_editor_form_middle_button_single_span bmlt_admin_delete_button_span hide_in_new_service_body_admin';
            } else {
                delete_span.className = 'item_hidden';
            };
        };
    };

    /************************************************************************************//**
    *   \brief  See if changes have been made to the Service body.                          *
    *   \returns the service body index (if changed). Null, otherwise (Check for null)      *
    ****************************************************************************************/
    this.isServiceBodyDirty = function () {
        var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
        var edited_service_body_object = main_service_body_editor.service_body_object;
        var original_service_body = null;
        var index = 0;
        
        if ( edited_service_body_object[0] > 0 ) {
            for (; index < g_service_bodies_array.length; index++) {
                if ( g_service_bodies_array[index][0] == edited_service_body_object[0] ) {
                    original_service_body = g_service_bodies_array[index];
                    break;
                };
            };
        } else {
            original_service_body = this.makeANewSB();
        };
        
        var current = JSON.stringify(edited_service_body_object);
        var orig = JSON.stringify(original_service_body);
        var ret = ( (current != orig) ? index : null );
        
        return ret;
    };

    /************************************************************************************//**
    *   \brief  This goes through the Service body (except the checkboxes) and recalculates *
    *           the state of the edited object.                                             *
    ****************************************************************************************/
    this.recalculateServiceBody = function () {
        var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');

        var name_text_input = document.getElementById('bmlt_admin_service_body_editor_sb_name_text_input');
        
        if ( name_text_input && name_text_input.value && (name_text_input.value != name_text_input.defaultValue) ) {
            main_service_body_editor.service_body_object[2] = name_text_input.value;
        } else {
            main_service_body_editor.service_body_object[2] = '';
        };
        
        var description_textarea = document.getElementById('bmlt_admin_sb_description_textarea');
        
        if ( description_textarea && description_textarea.value && (description_textarea.value != description_textarea.defaultValue) ) {
            main_service_body_editor.service_body_object[3] = description_textarea.value;
        } else {
            main_service_body_editor.service_body_object[3] = '';
        };

        var email_text_input = document.getElementById('bmlt_admin_service_body_editor_sb_email_text_input');
        
        if ( email_text_input && email_text_input.value && (email_text_input.value != email_text_input.defaultValue) ) {
            main_service_body_editor.service_body_object[6] = email_text_input.value;
        } else {
            main_service_body_editor.service_body_object[6] = '';
        };

        var uri_text_input = document.getElementById('bmlt_admin_service_body_editor_sb_uri_text_input');
        
        if ( uri_text_input && uri_text_input.value && (uri_text_input.value != uri_text_input.defaultValue) ) {
            main_service_body_editor.service_body_object[7] = uri_text_input.value;
        } else {
            main_service_body_editor.service_body_object[7] = '';
        };

        var world_id_text_input = document.getElementById('bmlt_admin_single_service_body_editor_world_cc_text_input');
        
        if ( world_id_text_input && world_id_text_input.value && (world_id_text_input.value != world_id_text_input.defaultValue) ) {
            main_service_body_editor.service_body_object[12] = world_id_text_input.value;
        } else {
            main_service_body_editor.service_body_object[12] = '';
        };

        var helpline_text_input = document.getElementById('bmlt_admin_single_service_body_editor_helpline_text_input');
        
        if ( helpline_text_input && helpline_text_input.value && (helpline_text_input.value != helpline_text_input.defaultValue) ) {
            main_service_body_editor.service_body_object[8] = helpline_text_input.value;
        } else {
            main_service_body_editor.service_body_object[8] = '';
        };

        var user_select = document.getElementById('bmlt_admin_single_service_body_editor_principal_user_select');
        
        if ( user_select && user_select.options[user_select.selectedIndex].value ) {
            main_service_body_editor.service_body_object[4] = parseInt(user_select.options[user_select.selectedIndex].value, 10);
        };

        var type_select = document.getElementById('bmlt_admin_single_service_body_editor_type_select');
        
        if ( type_select && type_select.options[type_select.selectedIndex].value ) {
            main_service_body_editor.service_body_object[9] = type_select.options[type_select.selectedIndex].value;
        };
        
        var service_body_parent_select = document.getElementById('bmlt_admin_single_service_body_editor_parent_select');
        
        if ( service_body_parent_select ) {
            main_service_body_editor.service_body_object[1] = parseInt(service_body_parent_select.value, 10);
        };

        this.validateServiceBodyEditorButtons();
    };
    
    /************************************************************************************//**
    *   \brief
    ****************************************************************************************/
    this.handleTextInputServiceBodyChange = function (
        in_text_item,
        in_value_index
    ) {
        var val = '';
        
        if ( in_text_item ) {
            if ( !in_text_item.value || (in_text_item.value == in_text_item.defaultValue) ) {
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : ''))) + ' bmlt_text_item_dimmed';
            } else {
                val = in_text_item.value;
                in_text_item.className = 'bmlt_text_item' + (in_text_item.small ? '_small' : (in_text_item.med ? '_med' : (in_text_item.tiny ? '_tiny' : '')));
            };
        };
            
        var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
        main_service_body_editor.service_body_object[in_value_index] = val;
        
        this.recalculateServiceBody();
        this.validateServiceBodyEditorButtons();
    };
    
    /************************************************************************************//**
    *   \brief  This cancels the Service body editing session.                              *
    ****************************************************************************************/
    this.cancelServiceBodyEdit = function () {
        var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
        main_service_body_editor.service_body_object = null;
        this.populateServiceBodyEditor();
    };
    
    // These functions will trigger AJAX transactions.
    
    /************************************************************************************//**
    *   \brief  This saves the current Service body.                                        *
    ****************************************************************************************/
    this.saveServiceBody = function () {
        var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
        
        if ( main_service_body_editor.service_body_object[0] == 0 ) {
            this.createServiceBody();
        } else {
            if ( this.isServiceBodyDirty() != null ) {
                var button_object = document.getElementById('bmlt_admin_service_body_editor_form_service_body_save_button');
                var throbber_object = document.getElementById('bmlt_admin_service_body_save_ajax_button_throbber_span');

                var serialized_object = JSON.stringify(main_service_body_editor.service_body_object);
        
                var uri = g_ajax_callback_uri + '&set_service_body_change=' + encodeURIComponent(serialized_object);

                if ( main_service_body_editor.m_ajax_request_in_progress ) {
                    main_service_body_editor.m_ajax_request_in_progress.abort();
                    main_service_body_editor.m_ajax_request_in_progress = null;
                };
            
                var salt = new Date();
                uri += '&salt=' + salt.getTime();
            
                button_object.className = 'item_hidden';
                throbber_object.className = 'bmlt_admin_ajax_button_throbber_span';
            
                main_service_body_editor.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req) {
                    admin_handler_object.saveServiceBodyAJAXCallback(in_req); }, 'post');
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief  This is the AJAX callback handler for the save operation.                   *
    ****************************************************************************************/
    this.saveServiceBodyAJAXCallback = function (in_http_request ///< The HTTPRequest object
                                                ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
            
            if ( json_object ) {
                if ( !json_object.success ) {
                    alert(json_object.report);
                    BMLT_Admin_StartFader('bmlt_admin_fader_service_body_editor_fail_div', this.m_failure_fade_duration);
                } else {
                    for (var index = 0; index < g_service_bodies_array.length; index++) {
                        if ( g_service_bodies_array[index][0] == json_object.service_body[0] ) {
                            g_service_bodies_array[index] = json_object.service_body;
                            break;
                        };
                    };
                        
                    for (var index = 0; index < g_editable_service_bodies_array.length; index++) {
                        if ( g_editable_service_bodies_array[index][0] == json_object.service_body[0] ) {
                            g_editable_service_bodies_array[index] = json_object.service_body;
                            break;
                        };
                    };
                        
                    var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
                    main_service_body_editor.m_ajax_request_in_progress = null;
                    BMLT_Admin_StartFader('bmlt_admin_fader_service_body_editor_success_div', this.m_success_fade_duration);
                };
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_service_body_editor_fail_div', this.m_failure_fade_duration);
            };
        } else {
            BMLT_Admin_StartFader('bmlt_admin_fader_service_body_editor_fail_div', this.m_failure_fade_duration);
        };
        
        document.getElementById('bmlt_admin_service_body_save_ajax_button_throbber_span').className = 'item_hidden';
        this.validateServiceBodyEditorButtons();
    };
    
    /************************************************************************************//**
    *   \brief  This creates a new Service body.                                            *
    ****************************************************************************************/
    this.createServiceBody = function () {
        if ( this.isServiceBodyDirty() != null ) {
            var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
            var button_object = document.getElementById('bmlt_admin_service_body_editor_form_service_body_save_button');
            var throbber_object = document.getElementById('bmlt_admin_service_body_save_ajax_button_throbber_span');

            var serialized_object = JSON.stringify(main_service_body_editor.service_body_object);
        
            var uri = g_ajax_callback_uri + '&create_new_service_body=' + encodeURIComponent(serialized_object);

            if ( main_service_body_editor.m_ajax_request_in_progress ) {
                main_service_body_editor.m_ajax_request_in_progress.abort();
                main_service_body_editor.m_ajax_request_in_progress = null;
            };
            
            var salt = new Date();
            uri += '&salt=' + salt.getTime();
            
            button_object.className = 'item_hidden';
            throbber_object.className = 'bmlt_admin_ajax_button_throbber_span';
            
            main_service_body_editor.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req) {
                admin_handler_object.createServiceBodyAJAXCallback(in_req); }, 'post');
        };
    };
    
    /************************************************************************************//**
    *   \brief  This is the AJAX callback handler for the create operation.                 *
    ****************************************************************************************/
    this.createServiceBodyAJAXCallback = function (in_http_request ///< The HTTPRequest object
                                                ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
            
            if ( json_object ) {
                if ( !json_object.success ) {
                    alert(json_object.report);
                    BMLT_Admin_StartFader('bmlt_admin_fader_service_body_create_fail_div', this.m_failure_fade_duration);
                } else {
                    g_service_bodies_array[g_service_bodies_array.length] = json_object.service_body;
                    g_editable_service_bodies_array[g_editable_service_bodies_array.length] = json_object.service_body;
                    var service_body_select = document.getElementById('bmlt_admin_single_service_body_editor_sb_select');
                    var new_option = document.createElement('option');
                    new_option.value = htmlspecialchars_decode(json_object.service_body[0]);
                    new_option.text = htmlspecialchars_decode(json_object.service_body[2]);
                    
                    try {
                        // for IE earlier than version 8
                        service_body_select.add(new_option, service_body_select.options[service_body_select.options.length - 2]);
                    } catch (e) {
                        service_body_select.add(new_option, service_body_select.options.length - 2);
                    };
                    
                    service_body_select.selectedIndex = service_body_select.options.length - 3;
                    var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
                    main_service_body_editor.m_ajax_request_in_progress = null;
                    main_service_body_editor.service_body_object = null;
                    this.populateServiceBodyEditor();
                    BMLT_Admin_StartFader('bmlt_admin_fader_service_body_create_success_div', this.m_success_fade_duration);
                    document.getElementById('bmlt_admin_service_body_save_ajax_button_throbber_span').className = 'item_hidden';
                    this.setWarningFaders();
                };
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_service_body_create_fail_div', this.m_failure_fade_duration);
            };
        } else {
            BMLT_Admin_StartFader('bmlt_admin_fader_service_body_create_fail_div', this.m_failure_fade_duration);
        };
        
        document.getElementById('bmlt_admin_service_body_save_ajax_button_throbber_span').className = 'item_hidden';
        this.validateServiceBodyEditorButtons();
    };

    /************************************************************************************//**
    *   \brief  This deletes the current Service body.                                      *
    ****************************************************************************************/
    this.deleteServiceBody = function () {
        if ( g_service_bodies_array.length > 1 ) {
            var perm_check = document.getElementById('bmlt_admin_service_body_delete_perm_checkbox');
            var confirm_str = g_service_body_delete_button_confirm + (( perm_check && perm_check.checked ) ? ("\n" + g_service_body_delete_button_confirm_perm) : '');
        
            if ( confirm(confirm_str) ) {
                var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
            
                if ( main_service_body_editor.m_ajax_request_in_progress ) {
                    main_service_body_editor.m_ajax_request_in_progress.abort();
                    main_service_body_editor.m_ajax_request_in_progress = null;
                };
            
                var id = main_service_body_editor.service_body_object[0];
                var uri = g_ajax_callback_uri + '&delete_service_body=' + id + (( perm_check && perm_check.checked ) ? '&permanently=1' : '');

                var throbber_span = document.getElementById('bmlt_admin_service_body_delete_ajax_button_throbber_span').className = 'bmlt_admin_ajax_button_throbber_span';
                var delete_a = document.getElementById('bmlt_admin_meeting_editor_form_service_body_delete_button').className = 'item_hidden';
            
                var salt = new Date();
                uri += '&salt=' + salt.getTime();
            
                main_service_body_editor.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req) {
                    admin_handler_object.deleteServiceBodyAJAXCallback(in_req); }, 'get');
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief  This is the AJAX callback handler for the delete operation.                 *
    ****************************************************************************************/
    this.deleteServiceBodyAJAXCallback = function (  in_http_request ///< The HTTPRequest object
                                                ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
            
            if ( json_object ) {
                var main_service_body_editor = document.getElementById('bmlt_admin_single_service_body_editor_div');
                main_service_body_editor.service_body_object = null;
                    
                if ( !json_object.success ) {
                    alert(json_object.report);
                    BMLT_Admin_StartFader('bmlt_admin_fader_service_body_editor_delete_fail_div', this.m_failure_fade_duration);
                } else {
                    var service_body_select = document.getElementById('bmlt_admin_single_service_body_editor_sb_select');
                    service_body_select.selectedIndex = 0;
                    for (var index = 0; index < g_editable_service_bodies_array.length; index++) {
                        if ( g_editable_service_bodies_array[index][0] == parseInt(json_object.id, 10) ) {
                            service_body_select.remove(index);
                            g_editable_service_bodies_array.splice(index, 1);
                            break;
                        };
                    };
                        
                    for (var index = 0; index < g_service_bodies_array.length; index++) {
                        if ( g_service_bodies_array[index][0] == parseInt(json_object.id, 10) ) {
                            g_service_bodies_array.splice(index, 1);
                            break;
                        };
                    };
                    
                    // If we leave any orphans, we clean them up, here.
                    for (var index = 0; index < g_editable_service_bodies_array.length; index++) {
                        var whosMyDaddy = false;
                        
                        var parent_id = g_editable_service_bodies_array[index][1];
                        for (var c = 0; c < g_editable_service_bodies_array.length; c++) {
                            if ( g_editable_service_bodies_array[c][0] == parent_id ) {
                                whosMyDaddy = true;
                                break;
                            };
                        };
                        
                        if ( !whosMyDaddy ) {
                            g_editable_service_bodies_array[index][1] = 0;
                        };
                    };
                        
                    for (var index = 0; index < g_editable_service_bodies_array.length; index++) {
                        var whosMyDaddy = false;
                        
                        var parent_id = g_service_bodies_array[index][1];
                        for (var c = 0; c < g_service_bodies_array.length; c++) {
                            if ( g_service_bodies_array[c][0] == parent_id ) {
                                whosMyDaddy = true;
                                break;
                            };
                        };
                        
                        if ( !whosMyDaddy ) {
                            g_service_bodies_array[index][1] = 0;
                        };
                    };
                    
                    main_service_body_editor.m_ajax_request_in_progress = null;
                    document.getElementById('bmlt_admin_service_body_delete_ajax_button_throbber_span').className = 'item_hidden';
                    this.populateServiceBodyEditor();
                    BMLT_Admin_StartFader('bmlt_admin_fader_service_body_editor_delete_success_div', this.m_success_fade_duration);
                };
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_service_body_editor_delete_fail_div', this.m_failure_fade_duration);
            };
        } else {
            BMLT_Admin_StartFader('bmlt_admin_fader_service_body_editor_delete_fail_div', this.m_failure_fade_duration);
        };
        
        document.getElementById('bmlt_admin_service_body_delete_ajax_button_throbber_span').className = 'item_hidden';
        this.validateServiceBodyEditorButtons();
    };
        
    // #mark -
    // #mark ########## User Editor Section ##########
    // #mark -
    
    /************************************************************************************//**
    *   \brief  Toggles the visibility of the Service body editor section.                  *
    ****************************************************************************************/
    this.toggleUserEditor = function () {
        this.m_user_editor_panel_shown = !this.m_user_editor_panel_shown;
        
        var the_disclosure_div = document.getElementById('bmlt_admin_user_editor_disclosure_div');
        var the_editor_div = document.getElementById('bmlt_admin_user_editor_wrapper_div');
        
        if ( this.m_user_editor_panel_shown ) {
            the_disclosure_div.className = 'bmlt_admin_user_editor_disclosure_div bmlt_admin_user_editor_disclosure_div_open';
            the_editor_div.className = 'bmlt_admin_user_editor_wrapper_div';
        } else {
            the_disclosure_div.className = 'bmlt_admin_user_editor_disclosure_div bmlt_admin_user_editor_disclosure_div_closed';
            the_editor_div.className = 'bmlt_admin_user_editor_wrapper_div bmlt_admin_user_editor_wrapper_div_hidden';
        };
    };

    /************************************************************************************//**
    *   \brief  This sets up the Service Body Editor for the selected Service body.         *
    ****************************************************************************************/
    this.populateUserEditor = function () {
        var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
        var selected_user_select = document.getElementById('bmlt_admin_single_user_editor_user_select');
        var selected_user_id = selected_user_select.options[selected_user_select.options.selectedIndex].value;
        var selected_user_object = null;
        var save_button_a = document.getElementById('bmlt_admin_user_editor_form_user_save_button');
        
        for (var c = 0; c < g_users.length; c++) {
            if ( g_users[c][0] == selected_user_id ) {
                selected_user_object = JSON.parse(JSON.stringify(g_users[c]));    // This ensures the user is a separate object.
                break;
            };
        };
        
        if ( !main_user_editor_div.current_user_object
            || (main_user_editor_div.current_user_object && !this.isUserDirty())
            || (main_user_editor_div.current_user_object && this.isUserDirty() && confirm(g_user_dirty_confirm_text)) ) {
            this.last_selected_user_index = selected_user_select.options.selectedIndex;
            var password_label = document.getElementById('bmlt_admin_user_editor_password_label');
            if ( !selected_user_object ) {
                selected_user_object = this.makeANewUser();

                save_button_a.innerHTML = g_user_create_button;
                main_user_editor_div.className = 'bmlt_admin_single_user_editor_div bmlt_admin_new_user_editor';
                password_label.innerHTML = g_user_new_password_label;
            } else {
                save_button_a.innerHTML = g_user_save_button;
                main_user_editor_div.className = 'bmlt_admin_single_user_editor_div';
                password_label.innerHTML = g_user_password_label;
            };
        
            if ( selected_user_object ) {
                if ( selected_user_object[5] == 1 ) { // This should never be, but just in case...
                    main_user_editor_div.className = 'bmlt_admin_single_user_editor_div bmlt_admin_new_user_editor';
                };
                
                main_user_editor_div.current_user_object = selected_user_object;
            
                var id_text_item = document.getElementById('user_editor_id_display');
                id_text_item.innerHTML = selected_user_object[0];
            
                var login_field = document.getElementById('bmlt_admin_user_editor_login_input');
                login_field.value = htmlspecialchars_decode(selected_user_object[1]);
                this.handleTextInputBlur(login_field);
            
                var name_field = document.getElementById('bmlt_admin_user_editor_name_input');
                name_field.value = htmlspecialchars_decode(selected_user_object[2]);
                this.handleTextInputBlur(name_field);
            
                var description_textarea = document.getElementById('bmlt_admin_user_editor_description_textarea');
                description_textarea.value = htmlspecialchars_decode(selected_user_object[3]);
                this.handleTextInputBlur(description_textarea);
            
                var email_field = document.getElementById('bmlt_admin_user_editor_email_input');
                email_field.value = htmlspecialchars_decode(selected_user_object[4]);
                this.handleTextInputBlur(email_field);
            
                var password_field = document.getElementById('bmlt_admin_user_editor_password_input');
                password_field.value = htmlspecialchars_decode(selected_user_object[0] ? g_user_password_default_text : g_user_new_password_default_text);
                this.handleTextInputLoad(password_field);
            
                var user_level_popup_span = document.getElementById('user_editor_single_non_service_body_admin_display');
                var user_level_sa_span = document.getElementById('user_editor_single_service_body_admin_display');
            
                if ( selected_user_object[5] != 1 ) {
                    user_level_popup_span.className = 'bmlt_admin_value_left';
                    user_level_sa_span.className = 'item_hidden';
                    var user_level_popup_select = document.getElementById('bmlt_admin_single_user_editor_level_select');
                    user_level_popup_select.selectedIndex = (selected_user_object[5] == 2) ? 0 : ((selected_user_object[5] == 5) ? 1 : 3 );
                } else // This should never be, but just in case...
                    {
                    user_level_popup_span.className = 'item_hidden';
                    user_level_sa_span.className = 'bmlt_admin_value_left light_italic_display';
                };
            };

                var user_owner_id = parseInt(selected_user_object[7]);
                var user_owner_field = document.getElementById('bmlt_admin_single_user_editor_user_owner_select');
            for (var i = 0; i < user_owner_field.options.length; i++) {
                var option = user_owner_field.options[i];
                if ( user_owner_id === -1 && parseInt(option.value) === 1 ) {
                    user_owner_field.selectedIndex = i;
                    break;
                } else if ( parseInt(option.value) === user_owner_id ) {
                    user_owner_field.selectedIndex = i;
                    break;
                }
            }
        } else if ( this.last_selected_user_index !== undefined ) {
            selected_user_select.options.selectedIndex = this.last_selected_user_index;
        }
        
        var perm_checkbox = document.getElementById('bmlt_admin_user_delete_perm_checkbox');
        if ( perm_checkbox ) {
            perm_checkbox.checked = false;
        };

        this.validateUserEditorButtons();
    };

    /************************************************************************************//**
    *   \brief  Reads the current user editor state, and sets the main object accordingly.  *
    ****************************************************************************************/
    this.readUserEditorState = function () {
        var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
        
        var login_field = document.getElementById('bmlt_admin_user_editor_login_input');
        main_user_editor_div.current_user_object[1] = (login_field.value && (login_field.value != login_field.defaultValue)) ? login_field.value : '';
        
        var name_field = document.getElementById('bmlt_admin_user_editor_name_input');
        main_user_editor_div.current_user_object[2] = (name_field.value && (name_field.value != name_field.defaultValue)) ? name_field.value : '';
        
        var description_textarea = document.getElementById('bmlt_admin_user_editor_description_textarea');
        main_user_editor_div.current_user_object[3] = (description_textarea.value && (description_textarea.value != description_textarea.defaultValue)) ? description_textarea.value : '';
        
        var email_field = document.getElementById('bmlt_admin_user_editor_email_input');
        main_user_editor_div.current_user_object[4] = (email_field.value && (email_field.value != email_field.defaultValue)) ? email_field.value : '';
        
        var user_level_select = document.getElementById('bmlt_admin_single_user_editor_level_select');
        main_user_editor_div.current_user_object[5] = parseInt(user_level_select.options[user_level_select.selectedIndex].value, 10);
        
        var password_field = document.getElementById('bmlt_admin_user_editor_password_input');
        main_user_editor_div.current_user_object[6] = (password_field.value && (password_field.value != password_field.defaultValue)) ? password_field.value : '';

        var user_owner_select = document.getElementById('bmlt_admin_single_user_editor_user_owner_select');
        main_user_editor_div.current_user_object[7] = parseInt(user_owner_select.options[user_owner_select.selectedIndex].value, 10);

        this.validateUserEditorButtons();
    };

    /************************************************************************************//**
    *   \brief  Saves the user state.                                                       *
    ****************************************************************************************/
    this.saveUser = function () {
        var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
        
        var pw = main_user_editor_div.current_user_object[6];
        
        if ( g_min_pw_len && pw && (pw.length < g_min_pw_len) ) {
            alert(sprintf(g_min_password_length_string, g_min_pw_len));
        } else {
            if ( main_user_editor_div.current_user_object[0] == 0 ) {
                this.createUser();
            } else {
                if ( this.isUserDirty() ) {
                    var button_object = document.getElementById('bmlt_admin_user_editor_form_user_save_button');
                    var throbber_object = document.getElementById('bmlt_admin_user_save_ajax_button_throbber_span');

                    var serialized_object = JSON.stringify(main_user_editor_div.current_user_object);
        
                    var uri = g_ajax_callback_uri + '&set_user_change=' + encodeURIComponent(serialized_object);

                    if ( main_user_editor_div.m_ajax_request_in_progress ) {
                        main_user_editor_div.m_ajax_request_in_progress.abort();
                        main_user_editor_div.m_ajax_request_in_progress = null;
                    };
            
                    var salt = new Date();
                    uri += '&salt=' + salt.getTime();
            
                    button_object.className = 'item_hidden';
                    throbber_object.className = 'bmlt_admin_ajax_button_throbber_span';
            
                    main_user_editor_div.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req) {
                        admin_handler_object.saveUserAJAXCallback(in_req); }, 'post');
                };
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief  This is the AJAX callback handler for the save operation.                   *
    ****************************************************************************************/
    this.saveUserAJAXCallback = function (in_http_request ///< The HTTPRequest object
                                        ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
            
            if ( json_object ) {
                if ( !json_object.success ) {
                    alert(json_object.report);
                    BMLT_Admin_StartFader('bmlt_admin_fader_user_editor_fail_div', this.m_failure_fade_duration);
                } else {
                    for (var index = 0; index < g_users.length; index++) {
                        if ( g_users[index][0] == json_object.user[0] ) {
                            g_users[index] = json_object.user;
                            break;
                        };
                    };
                        
                    var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
                    main_user_editor_div.m_ajax_request_in_progress = null;
                    BMLT_Admin_StartFader('bmlt_admin_fader_user_editor_success_div', this.m_success_fade_duration);
                };
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_user_editor_fail_div', this.m_failure_fade_duration);
            };
        } else {
            BMLT_Admin_StartFader('bmlt_admin_fader_user_editor_fail_div', this.m_failure_fade_duration);
        };
        
        document.getElementById('bmlt_admin_user_save_ajax_button_throbber_span').className = 'item_hidden';
        this.validateUserEditorButtons();
    };
    
    /************************************************************************************//**
    *   \brief  This creates a new user.                                                    *
    ****************************************************************************************/
    this.createUser = function () {
        if ( this.isUserDirty() ) {
            var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
            
            if ( main_user_editor_div.current_user_object[6] ) {  // New users must have a password.
                var button_object = document.getElementById('bmlt_admin_user_editor_form_user_save_button');
                var throbber_object = document.getElementById('bmlt_admin_user_save_ajax_button_throbber_span');

                var serialized_object = JSON.stringify(main_user_editor_div.current_user_object);
        
                var uri = g_ajax_callback_uri + '&create_new_user=' + encodeURIComponent(serialized_object);

                if ( main_user_editor_div.m_ajax_request_in_progress ) {
                    main_user_editor_div.m_ajax_request_in_progress.abort();
                    main_user_editor_div.m_ajax_request_in_progress = null;
                };
            
                var salt = new Date();
                uri += '&salt=' + salt.getTime();
            
                button_object.className = 'item_hidden';
                throbber_object.className = 'bmlt_admin_ajax_button_throbber_span';
            
                main_user_editor_div.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req) {
                    admin_handler_object.createUserAJAXCallback(in_req); }, 'post');
            } else {
                alert(g_user_create_password_alert_text);
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief  This is the AJAX callback handler for the create operation.                 *
    ****************************************************************************************/
    this.createUserAJAXCallback = function (in_http_request ///< The HTTPRequest object
                                                ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
            
            if ( json_object ) {
                if ( !json_object.success ) {
                    alert(json_object.report);
                    BMLT_Admin_StartFader('bmlt_admin_fader_user_create_fail_div', this.m_failure_fade_duration);
                } else {
                    g_users[g_users.length] = json_object.user;
                    var user_select = document.getElementById('bmlt_admin_single_user_editor_user_select');
                    var new_option = document.createElement('option');
                    new_option.value = htmlspecialchars_decode(json_object.user[0]);
                    new_option.text = htmlspecialchars_decode(json_object.user[2]);
                    
                    try {
                        // for IE earlier than version 8
                        user_select.add(new_option, user_select.options[user_select.options.length - 2]);
                    } catch (e) {
                        user_select.add(new_option, user_select.options.length - 2);
                    };
                    
                    user_select.selectedIndex = user_select.options.length - 3;
                    var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
                    main_user_editor_div.m_ajax_request_in_progress = null;
                    main_user_editor_div.current_user_object = null;
                    this.populateUserEditor();
                    BMLT_Admin_StartFader('bmlt_admin_fader_user_create_success_div', this.m_success_fade_duration);
                    document.getElementById('bmlt_admin_user_save_ajax_button_throbber_span').className = 'item_hidden';
                    this.setWarningFaders();
                };
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_user_create_fail_div', this.m_failure_fade_duration);
            };
        } else {
            BMLT_Admin_StartFader('bmlt_admin_fader_user_create_fail_div', this.m_failure_fade_duration);
        };
        
        document.getElementById('bmlt_admin_user_save_ajax_button_throbber_span').className = 'item_hidden';
        this.validateUserEditorButtons();
    };

    /************************************************************************************//**
    *   \brief  Deletes the user.                                                           *
    ****************************************************************************************/
    this.deleteUser = function () {
        if ( g_users.length > 1 ) {
            var perm_check = document.getElementById('bmlt_admin_user_delete_perm_checkbox');
            var confirm_str = g_user_delete_button_confirm + (( perm_check && perm_check.checked ) ? ("\n" + g_user_delete_button_confirm_perm) : '');
        
            if ( confirm(confirm_str) ) {
                var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
            
                if ( main_user_editor_div.m_ajax_request_in_progress ) {
                    main_user_editor_div.m_ajax_request_in_progress.abort();
                    main_user_editor_div.m_ajax_request_in_progress = null;
                };
            
                var id = main_user_editor_div.current_user_object[0];
                var uri = g_ajax_callback_uri + '&delete_user=' + id + (( perm_check && perm_check.checked ) ? '&permanently=1' : '');

                document.getElementById('bmlt_admin_user_delete_ajax_button_throbber_span').className = 'bmlt_admin_ajax_button_throbber_span';
                document.getElementById('bmlt_admin_meeting_editor_form_user_delete_button').className = 'item_hidden';
            
                var salt = new Date();
                uri += '&salt=' + salt.getTime();
            
                main_user_editor_div.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req) {
                    admin_handler_object.deleteUserAJAXCallback(in_req); }, 'get');
            };
        };
    };
    
    /************************************************************************************//**
    *   \brief  This is the AJAX callback handler for the delete operation.                 *
    ****************************************************************************************/
    this.deleteUserAJAXCallback = function (  in_http_request ///< The HTTPRequest object
                                                ) {
        if ( in_http_request.responseText ) {
            if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                alert(g_AJAX_Auth_Failure);
            } else {
                eval('var json_object = ' + in_http_request.responseText + ';');
            };
            
            if ( json_object ) {
                var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
                main_user_editor_div.current_user_object = null;
                    
                if ( !json_object.success ) {
                    alert(json_object.report);
                    BMLT_Admin_StartFader('bmlt_admin_fader_user_editor_delete_fail_div', this.m_failure_fade_duration);
                } else {
                    var user_select = document.getElementById('bmlt_admin_single_user_editor_user_select');
                    user_select.selectedIndex = 0;
                    for (var index = 0; index < user_select.options.length; index++) {
                        if ( parseInt(user_select.options[index].value, 10) == parseInt(json_object.report, 10) ) {
                            user_select.remove(index);
                            break;
                        };
                    };
                        
                    for (var index = 0; index < g_users.length; index++) {
                        if ( parseInt(g_users[index][0], 10) == parseInt(json_object.report, 10) ) {
                            g_users.splice(index, 1);
                            break;
                        };
                    };
                    
                    main_user_editor_div.m_ajax_request_in_progress = null;
                    document.getElementById('bmlt_admin_user_delete_ajax_button_throbber_span').className = 'item_hidden';
                    this.populateUserEditor();
                    BMLT_Admin_StartFader('bmlt_admin_fader_user_editor_delete_success_div', this.m_success_fade_duration);
                    alert(g_user_meeting_editor_note);
                };
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_user_editor_delete_fail_div', this.m_failure_fade_duration);
            };
        } else {
            BMLT_Admin_StartFader('bmlt_admin_fader_user_editor_delete_fail_div', this.m_failure_fade_duration);
        };
        
        document.getElementById('bmlt_admin_user_delete_ajax_button_throbber_span').className = 'item_hidden';
        this.validateUserEditorButtons();
    };

    /************************************************************************************//**
    *   \brief  Resets the user to the original state.                                      *
    ****************************************************************************************/
    this.cancelUserEdit = function () {
        var main_user_editor_div = document.getElementById('bmlt_admin_single_user_editor_div');
        main_user_editor_div.current_user_object = null;
        this.populateUserEditor();
    };

    /************************************************************************************//**
    *   \brief  See if changes have been made to the User.                                  *
    *   \returns true, if the user has been changed.                                        *
    ****************************************************************************************/
    this.isUserDirty = function () {
        var edited_user_object = document.getElementById('bmlt_admin_single_user_editor_div').current_user_object;
        var original_user_object = null;
        var index = 0;
        
        if ( edited_user_object[0] > 0 ) {
            for (; index < g_users.length; index++) {
                if ( g_users[index][0] == edited_user_object[0] ) {
                    original_user_object = g_users[index];
                    break;
                };
            };
        } else {
            original_user_object = this.makeANewUser();
        };
        
        var current = JSON.stringify(edited_user_object);
        var orig = JSON.stringify(original_user_object);
        var ret = (current != orig);

        return ret;
    };

    /************************************************************************************//**
    *   \brief  This simply creates a brand-new, unsullied User object.                     *
    *   \returns a new user object (which is really an array).                              *
    ****************************************************************************************/
    this.makeANewUser = function () {
        var new_user_object = new Array;
        new_user_object[0] = 0;
        new_user_object[1] = '';
        new_user_object[2] = '';
        new_user_object[3] = '';
        new_user_object[4] = '';
        new_user_object[5] = 4;
        new_user_object[6] = '';
        new_user_object[7] = -1;
        
        return new_user_object;
    };
    
    /************************************************************************************//**
    *   \brief  This sets the state of the User editor save button.                         *
    ****************************************************************************************/
    this.validateUserEditorButtons = function () {
        var save_button = document.getElementById('bmlt_admin_user_editor_form_user_save_button');
        var cancel_button = document.getElementById('bmlt_admin_user_editor_form_user_editor_cancel_button');
        var delete_button = document.getElementById('bmlt_admin_meeting_editor_form_user_delete_button');
        
        if ( this.isUserDirty() ) {
            save_button.className = 'bmlt_admin_ajax_button button';
            cancel_button.className = 'bmlt_admin_ajax_button button';
        } else {
            save_button.className = 'bmlt_admin_ajax_button button_disabled';
            cancel_button.className = 'bmlt_admin_ajax_button button_disabled';
        };

        if ( g_is_server_admin ) {
            delete_button.className = 'bmlt_admin_ajax_button button';
        } else {
            delete_button.className = 'bmlt_admin_ajax_button button_disabled';
        }

    };
    
    // #mark -
    // #mark ########## Format Editor Section ##########
    // #mark -
        
    /************************************************************************************//**
    *   \brief  Toggles the visibility of the Format editor section.                        *
    ****************************************************************************************/
    this.toggleFormatEditor = function () {
        this.m_user_editor_panel_shown = !this.m_user_editor_panel_shown;
        
        var the_disclosure_div = document.getElementById('bmlt_admin_format_editor_disclosure_div');
        var the_editor_div = document.getElementById('bmlt_admin_format_editor_wrapper_div');
        
        if ( this.m_user_editor_panel_shown ) {
            the_disclosure_div.className = 'bmlt_admin_format_editor_disclosure_div bmlt_admin_format_editor_disclosure_div_open';
            the_editor_div.className = 'bmlt_admin_format_editor_wrapper_div';
        } else {
            the_disclosure_div.className = 'bmlt_admin_format_editor_disclosure_div bmlt_admin_format_editor_disclosure_div_closed';
            the_editor_div.className = 'bmlt_admin_format_editor_wrapper_div bmlt_admin_format_editor_wrapper_div_hidden';
        };
    };

    /************************************************************************************//**
    *   \brief  This sets up the Format Editor for the selected Format.                     *
    ****************************************************************************************/
    this.populateFormatEditor = function () {
        var format_table = document.getElementById('bmlt_admin_format_editor_table');
        
        for (var index = 0; index < g_formats_array.length; index++) {
            var format_group = g_formats_array[index];
            var format_id = format_group.id;
            var format_lang_group = JSON.parse(JSON.stringify(format_group.formats));  // This will always be a copy.

            this.createFormatRow(index, format_id, format_lang_group, format_table, 0);
        };
            
        var create_format_line_tr = format_table.insertRow(-1);
        create_format_line_tr.id = 'format_create_line_tr';
        
        var format_create_td = create_format_line_tr.insertCell(-1);
        format_create_td.id = 'format_create_td';
        format_create_td.className = 'format_create_td';
                    
        format_create_td.setAttribute('colspan', 6);
        format_create_td.colSpan = 6;
        var format_create_a = document.createElement('a');
        format_create_a.id = 'format_editor_create_a';
        format_create_a.className = 'bmlt_admin_ajax_button';
        format_create_a.appendChild(document.createTextNode(g_format_editor_create_format_button_text));
        format_create_a.href = 'javascript:admin_handler_object.createFormatOpen()';

        format_create_td.appendChild(format_create_a);
    };

    this.checkAllServiceBodies = function(button) {
        var desiredState = button.innerText == g_check_all_text;
        for (var i = 0; i < g_service_bodies_array.length; i++) {
            var serviceBodyId = g_service_bodies_array[i][0];
            var checkbox = document.getElementById("bmlt_admin_meeting_search_service_body_checkbox_" + serviceBodyId);
            checkbox.checked = desiredState;
        }
        button.innerText = desiredState ? g_uncheck_all_text : g_check_all_text;
    };

    /************************************************************************************//**
    *   \brief  This populates the format display list                                      *
    ****************************************************************************************/
    this.createFormatRow = function (
        in_index,               ///< The index, for styling the row.
        in_format_id,           ///< The shared ID for the format
        in_format_lang_group,   ///< The format objects
        in_container_table,     ///< The table that will contain this row.
        in_offset               ///< If the insertion needs to be offset, this is how much.
    ) {
        in_offset = parseInt(in_offset, 10); // Just to make sure.
        var format_line_tr = null;
        // The number of lines per format is the number of languages plus 2: one for NAWS code, and one for format_type
        var insertion_point = (g_formats_array.length + in_offset) * (g_langs.length + 2);
        
        if ( document.getElementById('format_create_line_tr') ) {
            format_line_tr = in_container_table.insertRow(insertion_point);
            insertion_point++;
        } else {
            format_line_tr = in_container_table.insertRow(-1);
            insertion_point = -1;
        };
            
        format_line_tr.id = 'format_editor_line_' + in_format_id + '_tr';
        
        var container_row = format_line_tr;
        var id_td = container_row.insertCell(-1);
        id_td.id = 'format_editor_id_' + in_format_id + '_td';
        id_td.className = 'format_editor_id_td';
        var node_text = (parseInt(in_format_id, 10) != 0) ? in_format_id.toString() : '';
        
        if ( node_text ) {
            id_td.appendChild(document.createTextNode(node_text));
        } else {
            id_td.innerHTML = '&nbsp;';
        };
        
        id_td.setAttribute('rowspan', g_langs.length + 1);
        id_td.rowSpan = g_langs.length;
        for (var c = 0; c < g_langs.length; c++) {
            var lang_key = g_langs[c];
            var format = (in_format_lang_group != null) ? (in_format_lang_group[lang_key] ? in_format_lang_group[lang_key] : '') : '';
            
            if ( !format ) {
                format = new Object;
                format.shared_id = in_format_id;
                format.lang_key = lang_key;
                format.lang_name = g_lang_names[lang_key];
                format.key = '';
                format.name = '';
                format.description = '';
                
                if ( !in_format_lang_group ) {
                    in_format_lang_group = new Object;
                };
                
                in_format_lang_group[lang_key] = format;
            };
            
            var unique_id =  in_format_id + '_' + lang_key;
            
            if ( c > 0 ) {
                container_row = in_container_table.insertRow(insertion_point);
                if ( insertion_point > 0 ) {
                    insertion_point++;
                };
                container_row.id = 'format_editor_' + lang_key + '_line_' + in_format_id + '_tr';
            };
            
            container_row.format_group_objects = in_format_lang_group;
            if ( !in_format_id ) {
                container_row.className = 'new_format_line';
            } else {
                container_row.className = 'format_editor_format_line_tr format_editor_format_line_' + ((in_index % 2) ? 'even' : 'odd') + '_tr';
            };
            
            var format_lang_td = container_row.insertCell(-1);
            format_lang_td.id = 'format_editor_lang_' + unique_id + '_td';
            format_lang_td.className = 'format_editor_lang_td';
            format_lang_td.appendChild(document.createTextNode(format.lang_name));
            
            var format_key_td = container_row.insertCell(-1);
            format_key_td.id = 'format_editor_key_' + in_format_id + '_td';
            format_key_td.className = 'format_editor_key_td';

            var format_key_input = document.createElement('input');
            format_key_input.type = 'text';
            format_key_input.format_object = format;
            format_key_input.data_member_name = 'key';
            format_key_input.tiny = true;
            format_key_input.value = htmlspecialchars_decode(format.key);
            format_key_input.defaultValue = '';
            format_key_input.id = 'bmlt_format_key_' + unique_id + '_text_item';
            format_key_input.onfocus= function () {
                admin_handler_object.handleTextInputFocus(this); };
            format_key_input.onblur= function () {
                admin_handler_object.handleTextInputBlur(this); };
            format_key_input.onkeyup = function () {
                admin_handler_object.handleFormatTextInput(this); };
            format_key_input.onpaste = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.handleFormatTextInput(input); }, 0);
            };
            format_key_input.oncut = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.handleFormatTextInput(input); }, 0);
            };


            format_key_td.appendChild(format_key_input);
            
            var format_name_td = container_row.insertCell(-1);
            format_name_td.id = 'format_editor_name_' + unique_id + '_td';
            format_name_td.className = 'format_editor_name_td';

            var format_name_input = document.createElement('input');
            format_name_input.type = 'text';
            format_name_input.format_object = format;
            format_name_input.data_member_name = 'name';
            format_name_input.small = true;
            format_name_input.value = htmlspecialchars_decode(format.name);
            format_name_input.defaultValue = '';
            format_name_input.id = 'bmlt_format_name_' + unique_id + '_text_item';
            format_name_input.onfocus= function () {
                admin_handler_object.handleTextInputFocus(this); };
            format_name_input.onblur= function () {
                admin_handler_object.handleTextInputBlur(this); };
            format_name_input.onkeyup = function () {
                admin_handler_object.handleFormatTextInput(this); };
            format_name_input.onpaste = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.handleFormatTextInput(input); }, 0);
            };
            format_name_input.oncut = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.handleFormatTextInput(input); }, 0);
            };

    
            format_name_td.appendChild(format_name_input);
            
            var format_description_td = container_row.insertCell(-1);
            format_description_td.id = 'format_editor_decription_' + unique_id + '_td';
            format_description_td.className = 'format_editor_description_td';

            var format_description_input = document.createElement('textarea');
            format_description_input.format_object = format;
            format_description_input.data_member_name = 'description';
            format_description_input.value = htmlspecialchars_decode(format.description);
            format_description_input.id = 'bmlt_format_description_' + unique_id + '_text_item';
            format_description_input.med = true;
            format_description_input.onfocus= function () {
                admin_handler_object.handleTextInputFocus(this); };
            format_description_input.onblur= function () {
                admin_handler_object.handleTextInputBlur(this); };
            format_description_input.onkeyup = function () {
                admin_handler_object.handleFormatTextInput(this); };
            format_description_input.onpaste = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.handleFormatTextInput(input); }, 0);
            };
            format_description_input.oncut = function () {
                var input = this;
                setTimeout(function () {
                    admin_handler_object.handleFormatTextInput(input); }, 0);
            };

            
            format_description_td.appendChild(format_description_input);
            
            if ( c == 0 ) {
                var format_buttons_td = container_row.insertCell(-1);
                format_buttons_td.id = 'format_editor_buttons_' + in_format_id + '_td';
                format_buttons_td.className = 'format_editor_buttons_td' + ((in_format_id == 0) ? ' bmlt_admin_new_format_editor_td' : '');
                format_buttons_td.setAttribute('rowspan', g_langs.length);
                format_buttons_td.rowSpan = g_langs.length;
            
                var format_change_div = document.createElement('div');
                format_change_div.id = 'format_editor_change_' + in_format_id + '_div';
                format_change_div.className = 'format_editor_change_div';
            
                var format_change_a = document.createElement('a');
                format_change_a.id = 'format_editor_change_' + in_format_id + '_a';
                format_change_a.format_group_objects = in_format_lang_group;
                format_change_a.className = 'bmlt_admin_ajax_button button_disabled';
                if ( in_format_id ) {
                    format_change_a.appendChild(document.createTextNode(g_format_editor_change_format_button_text));
                } else {
                    format_change_a.appendChild(document.createTextNode(g_format_editor_create_this_format_button_text));
                };
            
                format_change_a.href = 'javascript:admin_handler_object.saveFormat(' + in_format_id + ')';

                format_change_div.appendChild(format_change_a);
                
                var new_throbber_span = document.createElement('span');
                new_throbber_span.className = 'item_hidden';
                new_throbber_span.id = 'format_editor_change_' + in_format_id + '_throbber_span';
                
                var new_throbber_img = document.createElement('img');
                new_throbber_img.src = g_throbber_image_loc;
                new_throbber_img.setAttribute('alt', 'AJAX Throbber');

                new_throbber_span.appendChild(new_throbber_img);
                format_change_div.appendChild(new_throbber_span);
                format_buttons_td.appendChild(format_change_div);
                
                if ( g_formats_array.length > 1 ) {   // Can't delete the last format.
                    var format_delete_div = document.createElement('div');
                    format_delete_div.id = 'format_editor_delete_' + in_format_id + '_div';
                    format_delete_div.className = 'format_editor_delete_div hide_in_new_format_admin';
            
                    var format_delete_a = document.createElement('a');
                    format_delete_a.id = 'format_editor_delete_' + in_format_id + '_a';
                    format_change_a.format_group_objects = in_format_lang_group;
                    format_delete_a.className = 'bmlt_admin_ajax_button';
                    format_delete_a.appendChild(document.createTextNode(g_format_editor_delete_format_button_text));
                    format_delete_a.href = 'javascript:admin_handler_object.deleteFormat(' + in_format_id + ')';
            
                    format_delete_div.appendChild(format_delete_a);
                    var new_throbber_span = document.createElement('span');
                    new_throbber_span.className = 'item_hidden';
                    new_throbber_span.id = 'format_editor_delete_' + in_format_id + '_throbber_span';
                
                    var new_throbber_img = document.createElement('img');
                    new_throbber_img.src = g_throbber_image_loc;
                    new_throbber_img.setAttribute('alt', 'AJAX Throbber');

                    new_throbber_span.appendChild(new_throbber_img);
                    format_delete_div.appendChild(new_throbber_span);
                    format_buttons_td.appendChild(format_delete_div);
                };
            };
            
            this.handleTextInputLoad(format_key_input, '');
            this.handleTextInputLoad(format_name_input, g_format_editor_name_default_text);
            this.handleTextInputLoad(format_description_input, g_format_editor_description_default_text);
        };
            
        container_row = in_container_table.insertRow(insertion_point);
        // if we're not in create_format mode
        if ( insertion_point > 0 ) {
            insertion_point++;
        };
        container_row.id = 'format_editor_naws_id_' + in_format_id + '_tr';
        var row_className = 'format_editor_naws_id_tr format_editor_format_line_' + ((in_index % 2) ? 'even' : 'odd') + '_tr';
        
        if ( in_format_id == 0 ) {
            row_className = 'format_editor_naws_id_tr new_format_line';
        };
        
        container_row.className = row_className;

        var naws_td = container_row.insertCell(-1);
        naws_td.id = 'format_editor_naws_id_' + in_format_id + '_td';
        naws_td.className = 'format_editor_naws_id_td';
        naws_td.setAttribute('colspan', 6);
        
        var naws_menu_prompt = document.createElement('label');
        naws_menu_prompt.id = 'format_editor_naws_id_' + in_format_id + '_label';
        naws_menu_prompt.className = 'format_editor_naws_id_label';
        naws_menu_prompt.setAttribute('for', 'format_editor_naws_id_' + in_format_id + '_select');

        naws_menu_prompt.appendChild(document.createTextNode(g_naws_popup_prompt));
        naws_td.appendChild(naws_menu_prompt);

        naws_menu = document.createElement('select');
        naws_menu.id = 'format_editor_naws_id_' + in_format_id + '_select';
        naws_menu.className = 'format_editor_naws_id_select';
        naws_menu.format_group_objects = in_format_lang_group;
        naws_menu.shared_id = in_format_id;
        naws_menu.data_member_name = 'worldid_mixed';
        naws_menu.onchange = function () {
            admin_handler_object.handleFormatNAWSSelectInput(this); };
        
        for (var i = 0; i < g_naws_values.length; i++) {
            var val = g_naws_values[i].value ? htmlspecialchars_decode(g_naws_values[i].value) : '';
            if ( val ) {
                var key = g_naws_values[i].key ? g_naws_values[i].key: '';
                var opt = document.createElement('option');
                opt.value = htmlspecialchars_decode(key);
                opt.appendChild(document.createTextNode(htmlspecialchars_decode(val)));
            };
            
            naws_menu.appendChild(opt);
        };
        
        var key_match = false;
        
        var format_world_id_key = in_format_lang_group.en.worldid_mixed ? htmlspecialchars_decode(in_format_lang_group.en.worldid_mixed) : null;
        
        // Make sure the correct value is selected.
        if ( format_world_id_key ) {
            for (var i = 0; i < g_naws_values.length; i++) {
                var key = g_naws_values[i].key ? htmlspecialchars_decode(g_naws_values[i].key) : '';
                if ( key == format_world_id_key ) {
                    key_match = true;
                    naws_menu.selectedIndex = i;
                    break;
                };
            };
        };
                
        if ( !key_match ) {
            naws_menu.selectedIndex = 0;
        };
        
        naws_td.appendChild(naws_menu);
    
    /************************************************************************************//**
    *   \brief  Handle data input from the text items.                                      *
    ****************************************************************************************/
        this.handleFormatNAWSSelectInput = function ( in_select_input_object ///< This will contain the affected select input
                                            ) {
            var new_value = in_select_input_object.options[in_select_input_object.selectedIndex].value;
        
            for (var c = 0; c < g_langs.length; c++) {
                in_select_input_object.format_group_objects[g_langs[c]].worldid_mixed = new_value;
            };
        
            this.evaluateFormatState(in_select_input_object.shared_id);
        };
        /***
         * Begin Format-Type-Enum
         */
        container_row = in_container_table.insertRow(insertion_point);
        container_row.id = 'format_editor_formatType_' + in_format_id + '_tr';
        var row_className = 'format_editor_naws_id_tr format_editor_format_line_' + ((in_index % 2) ? 'even' : 'odd') + '_tr';
        
        if ( in_format_id == 0 ) {
            row_className = 'format_editor_naws_id_tr new_format_line';
        };
        
        container_row.className = row_className;

        var formatType_td = container_row.insertCell(-1);
        formatType_td.id = 'format_editor_formatType_' + in_format_id + '_td';
        formatType_td.className = 'format_editor_naws_id_td';
        formatType_td.setAttribute('colspan', 6);
        
        var formatType_menu_prompt = document.createElement('label');
        formatType_menu_prompt.id = 'format_editor_naws_id_' + in_format_id + '_label';
        formatType_menu_prompt.className = 'format_editor_naws_id_label';
        formatType_menu_prompt.setAttribute('for', 'format_editor_formatType_' + in_format_id + '_select');

        formatType_menu_prompt.appendChild(document.createTextNode(g_formatType_popup_prompt));
        formatType_td.appendChild(formatType_menu_prompt);

        formatType_menu = document.createElement('select');
        formatType_menu.id = 'format_editor_formatType_' + in_format_id + '_select';
        formatType_menu.className = 'format_editor_naws_id_select';
        formatType_menu.format_group_objects = in_format_lang_group;
        formatType_menu.shared_id = in_format_id;
        formatType_menu.data_member_name = 'format_type_enum';
        formatType_menu.onchange = function () {
            admin_handler_object.handleFormatTypeSelectInput(this); };
        
        for (var i = 0; i < g_formatType_values.length; i++) {
            var val = g_formatType_values[i].value ? htmlspecialchars_decode(g_formatType_values[i].value) : '';
            if ( val ) {
                var key = g_formatType_values[i].key ? g_formatType_values[i].key: '';
                var opt = document.createElement('option');
                opt.value = htmlspecialchars_decode(key);
                opt.appendChild(document.createTextNode(htmlspecialchars_decode(val)));
            };
            
            formatType_menu.appendChild(opt);
        };
        
        var key_match = false;
        var format_type_key = in_format_lang_group.en.type ? htmlspecialchars_decode(in_format_lang_group.en.type) : null;

        // Make sure the correct value is selected.
        if ( format_type_key ) {
            for (var i = 0; i < g_formatType_values.length; i++) {
                var key = g_formatType_values[i].key ? htmlspecialchars_decode(g_formatType_values[i].key) : '';
                if ( key == format_type_key ) {
                    key_match = true;
                    formatType_menu.selectedIndex = i;
                    break;
                };
            };
        };
                
        if ( !key_match ) {
            formatType_menu.selectedIndex = 0;
        };
        
        formatType_td.appendChild(formatType_menu);
    
    /************************************************************************************//**
    *   \brief  Handle data input from the text items.                                      *
    ****************************************************************************************/
        this.handleFormatTypeSelectInput = function ( in_select_input_object ///< This will contain the affected select input
                                            ) {
            var new_value = in_select_input_object.options[in_select_input_object.selectedIndex].value;
        
            for (var c = 0; c < g_langs.length; c++) {
                in_select_input_object.format_group_objects[g_langs[c]].type = new_value;
            };
        
            this.evaluateFormatState(in_select_input_object.shared_id);
        };
        /***************************************
         * End Format-Type-Enum
         ******************************************/
        /**********************************************/
 
    /************************************************************************************//**
    *   \brief  This goes through all the formats in the list, and ensures they have the    *
    *           proper styling to them.                                                     *
    ****************************************************************************************/
        this.restyleFormats = function () {
            for (var index = 0; index < g_formats_array.length; index++) {
                var format_group = g_formats_array[index];
                var format_id = format_group.id;
                for (var c = 0; c < g_langs.length; c++) {
                    var lang_key = g_langs[c];
                    var format_row = null;
                    var format_row_2 = document.getElementById('format_editor_naws_id_' + format_id + '_tr');
                    var format_row_3 = document.getElementById('format_editor_formatType_' + format_id + '_tr');
                    format_row_3.className = format_row_2.className = 'format_editor_naws_id_tr format_editor_format_line_' + ((index % 2) ? 'even' : 'odd') + '_tr';
                
                    if ( c == 0 ) {
                        format_row = document.getElementById('format_editor_line_' + format_id + '_tr');
                    } else {
                        format_row = document.getElementById('format_editor_' + lang_key + '_line_' + format_id + '_tr');
                    };
                    
                    if ( format_row ) {
                        format_row.className = 'format_editor_format_line_tr format_editor_format_line_' + ((index % 2) ? 'even' : 'odd') + '_tr';
                    };
                };
            };
        };

    /************************************************************************************//**
    *   \brief  Opens a new format editor row..                                             *
    ****************************************************************************************/
        this.createFormatOpen = function () {
            // There can only be one...
            var existing_new_format = document.getElementById('format_editor_line_0_tr');
            var create_button = document.getElementById('format_editor_create_a');
        
            if ( !existing_new_format ) {
                var table_element = document.getElementById('bmlt_admin_format_editor_table');
                this.createFormatRow(0, 0, null, table_element, 0);
                create_button.innerHTML = g_format_editor_cancel_create_format_button_text;
                create_button.href = 'javascript:admin_handler_object.cancelCreateNewFormat()';
            } else {
                create_button.innerHTML = g_format_editor_create_format_button_text;
                create_button.href = 'javascript:admin_handler_object.createFormatOpen()';
            };
        };
    
    /************************************************************************************//**
    *   \brief  Creates a new format editor row..                                           *
    ****************************************************************************************/
        this.cancelCreateNewFormat = function () {
            var existing_new_format = document.getElementById('format_editor_line_0_tr');
            var create_button = document.getElementById('format_editor_create_a');

            if ( document.getElementById('format_editor_line_0_tr') ) {
                existing_new_format.parentNode.removeChild(existing_new_format);

                for (var c = 1; c < g_langs.length; c++) {  // The format section is actually multiple lines; one for each language.
                    var lang_key = g_langs[c];
                    var container_row = document.getElementById('format_editor_' + lang_key + '_line_0_tr');
            
                    container_row.parentNode.removeChild(container_row);
                };
                
                var container_row = document.getElementById('format_editor_naws_id_0_tr');
                container_row.parentNode.removeChild(container_row);
                
                container_row = document.getElementById('format_editor_formatType_0_tr');
                container_row.parentNode.removeChild(container_row);
                
                create_button.innerHTML = g_format_editor_create_format_button_text;
                create_button.href = 'javascript:admin_handler_object.createFormatOpen()';
            };
        };

    /************************************************************************************//**
    *   \brief  Saves the changed format.                                                   *
    ****************************************************************************************/
        this.saveFormat = function (    in_format_id    ///< The shared ID of the format.
                                ) {
            var the_button = document.getElementById('format_editor_change_' + in_format_id + '_a');

            if ( this.isFormatDirty(in_format_id) ) {
                var edited_format_group = the_button.format_group_objects;    // We fetch the format from the button.
            
                var new_id = in_format_id;
            
                if ( !new_id ) {
                    var new_id = this.getNextFormatID();
                    for (var c = 0; c < g_langs.length; c++) {
                        edited_format_group[g_langs[c]].shared_id = new_id;
                    };
                };

                var json_to_send_to_server = JSON.stringify(edited_format_group);    // We will be sending as a JSON object.
                var throbber_span = document.getElementById('format_editor_change_' + in_format_id + '_throbber_span');
                the_button.className = 'item_hidden';
                throbber_span.className = 'bmlt_admin_general_ajax_button_throbber_div';
            
                var format_line_tr = document.getElementById('format_editor_line_' + in_format_id + '_tr');
            
                var uri = g_ajax_callback_uri + '&set_format_change=' + encodeURIComponent(json_to_send_to_server);

                if ( format_line_tr.m_ajax_request_in_progress ) {
                    format_line_tr.m_ajax_request_in_progress.abort();
                    format_line_tr.m_ajax_request_in_progress = null;
                };
        
                var salt = new Date();
                uri += '&salt=' + salt.getTime();

                format_line_tr.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req,id) {
                    admin_handler_object.saveFormatAJAXCallback(in_req,id); }, 'post',in_format_id);
            };
        };

    /************************************************************************************//**
    *   \brief  AJAX callback for the save operation.                                       *
    ****************************************************************************************/
        this.saveFormatAJAXCallback = function (
            in_http_request,    ///< The HTTPRequest object
            in_format_id
        ) {
            var the_button = document.getElementById('format_editor_change_' + in_format_id + '_a');
            var throbber_span = document.getElementById('format_editor_change_' + in_format_id + '_throbber_span');
            var edited_format_group = the_button.format_group_objects;    // We fetch the format from the button.
        
            throbber_span.className = 'item_hidden';
            the_button.className = 'bmlt_admin_ajax_button';
            if ( in_http_request.responseText ) {
                if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                    alert(g_AJAX_Auth_Failure);
                } else {
                    eval('var json_object = ' + in_http_request.responseText + ';');
                };
                if ( json_object ) {
                    if ( !json_object.success ) {
                        alert(json_object.report);
                        BMLT_Admin_StartFader('bmlt_admin_fader_format_editor_fail_div', this.m_failure_fade_duration);
                    } else {
                        edited_format_group = json_object.report;
                        var index = 0;
                        var handled = false;
                        while ( index <= g_formats_array.length ) {
                            if ( index == g_formats_array.length ) {
                                g_formats_array[index] = new Object;
                                g_formats_array[index].id = edited_format_group[g_langs[0]].shared_id;
                                this.cancelCreateNewFormat();
                                var format_table = document.getElementById('bmlt_admin_format_editor_table');
                                this.createFormatRow(index, g_formats_array[index].id, edited_format_group, format_table, -1);
                                handled = true;
                                break;
                            };
                        
                            if ( edited_format_group[g_langs[0]].shared_id == g_formats_array[index].id ) {
                                g_formats_array[index].formats = JSON.parse(JSON.stringify(edited_format_group));
                                BMLT_Admin_StartFader('bmlt_admin_fader_format_editor_success_div', this.m_success_fade_duration);
                            
                                if ( in_format_id ) {
                                    the_button.className += ' button_disabled';
                                };
                            
                                this.restyleFormats();
                                handled = true;
                                break;
                            };
                            
                            index++;
                        };
                    
                        // If we went through without interruption, then the job was not complete.
                        if ( !handled ) {
                            BMLT_Admin_StartFader('bmlt_admin_fader_format_editor_fail_div', this.m_failure_fade_duration);
                        };
                    };
                };
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_format_editor_fail_div', this.m_failure_fade_duration);
            };
        };
    
    /************************************************************************************//**
    *   \brief  Deletes the format.                                                         *
    ****************************************************************************************/
        this.deleteFormat = function (  in_format_id    ///< The shared ID of the format.
                                ) {
            if ( confirm(g_format_editor_delete_button_confirm) ) {
                var the_button = document.getElementById('format_editor_delete_' + in_format_id + '_a');
                var the_format_group = the_button.format_group_objects;   // We fetch the format from the button.
                var format_line_tr = document.getElementById('format_editor_line_' + in_format_id + '_tr');

                var throbber_span = document.getElementById('format_editor_delete_' + in_format_id + '_throbber_span');
                the_button.className = 'item_hidden';
                throbber_span.className = 'bmlt_admin_general_ajax_button_throbber_div';
            
                var uri = g_ajax_callback_uri + '&delete_format=' + in_format_id;

                if ( format_line_tr.m_ajax_request_in_progress ) {
                    format_line_tr.m_ajax_request_in_progress.abort();
                    format_line_tr.m_ajax_request_in_progress = null;
                };
    
                var salt = new Date();
                uri += '&salt=' + salt.getTime();

                format_line_tr.m_ajax_request_in_progress = BMLT_AjaxRequest(uri, function (in_req,id) {
                    admin_handler_object.deleteFormatAJAXCallback(in_req,id); }, 'post', in_format_id);
            };
        };

    /************************************************************************************//**
    *   \brief  AJAX callback for the save operation.                                       *
    ****************************************************************************************/
        this.deleteFormatAJAXCallback = function (
            in_http_request,    ///< The HTTPRequest object
            in_format_id        ///< The format being deleted.
        ) {
            var the_button = document.getElementById('format_editor_delete_' + in_format_id + '_a');
            var throbber_span = document.getElementById('format_editor_delete_' + in_format_id + '_throbber_span');
            throbber_span.className = 'item_hidden';
            the_button.className = 'bmlt_admin_ajax_button';

            if ( in_http_request.responseText ) {
                if ( in_http_request.responseText == 'NOT AUTHORIZED' ) {
                    alert(g_AJAX_Auth_Failure);
                } else {
                    eval('var json_object = ' + in_http_request.responseText + ';');
                };
            
                if ( json_object ) {
                    if ( !json_object.success ) {
                        alert(json_object.report);
                        BMLT_Admin_StartFader('bmlt_admin_fader_format_editor_delete_fail_div', this.m_failure_fade_duration);
                    } else {
                        var the_id = parseInt(json_object.report, 10);
                        var index = 0;
                        var handled = false;
                    
                        while ( index <= g_formats_array.length ) {
                            if ( parseInt(g_formats_array[index].id, 10) == the_id ) {
                                var container_row = document.getElementById('format_editor_line_' + the_id + '_tr');
        
                                container_row.parentNode.removeChild(container_row);

                                for (var c = 1; c < g_langs.length; c++) {  // The format section is actually multiple lines; one for each language.
                                    var lang_key = g_langs[c];
                                    container_row = document.getElementById('format_editor_' + lang_key + '_line_' + the_id + '_tr');
                                    container_row.parentNode.removeChild(container_row);
                                };
                                
                                container_row = document.getElementById('format_editor_naws_id_' + the_id + '_tr');
                                container_row.parentNode.removeChild(container_row);
                                container_row = document.getElementById('format_editor_formatType_' + the_id + '_tr');
                                container_row.parentNode.removeChild(container_row);
                                
                                this.setWarningFaders();
                                if ( g_formats_array.length > 1 ) {
                                    g_formats_array.splice(index, 1);
                                } else {
                                    g_formats_array = Array();
                                };
                                BMLT_Admin_StartFader('bmlt_admin_fader_format_editor_delete_success_div', this.m_success_fade_duration);
                                this.restyleFormats();
                                handled = true;
                                break;
                            };
                        
                            index++;
                        };
                    
                        if ( !handled ) {
                            BMLT_Admin_StartFader('bmlt_admin_fader_format_editor_delete_fail_div', this.m_failure_fade_duration);
                        };
                    };
                };
            } else {
                BMLT_Admin_StartFader('bmlt_admin_fader_format_editor_delete_fail_div', this.m_failure_fade_duration);
            };
        };
    
    /************************************************************************************//**
    *   \brief  Handle data input from the text items.                                      *
    ****************************************************************************************/
        this.handleFormatTextInput = function ( in_text_input_object ///< This will contain the affected text input
                                            ) {
            eval('in_text_input_object.format_object.' + in_text_input_object.data_member_name + ' = in_text_input_object.value;');
        
            this.evaluateFormatState(in_text_input_object.format_object.shared_id);
        };

    /************************************************************************************//**
    *   \brief  This function evaluates a changed format, and sets up the buttons.          *
    ****************************************************************************************/
        this.evaluateFormatState = function ( in_format_id  /// The ID of the format to check
                                            ) {
            var format_change_a = document.getElementById('format_editor_change_' + in_format_id + '_a');
        
            var className = 'bmlt_admin_ajax_button' + (this.isFormatDirty(in_format_id) ? '' : ' button_disabled');
        
            format_change_a.className = className;
        };

    /************************************************************************************//**
    *   \brief  This function Sees if the formats have been changed.                        *
    *   \returns a Boolean. TRUE, if the format has been changed.
    ****************************************************************************************/
        this.isFormatDirty = function ( in_format_id    /// The ID of the format to check
                                    ) {
            var format_change_a = document.getElementById('format_editor_change_' + in_format_id + '_a');
            var original_format_group_object = null;
            var edited_format_group_object = format_change_a.format_group_objects;  // The edited format group is attached to the change button.
        
            for (var index = 0; index < g_formats_array.length; index++) {
                var format_group = g_formats_array[index];
                if ( in_format_id == format_group.id ) {
                    original_format_group_object = format_group.formats;
                    break;
                };
            };
        
            // We now have the original (unmodified) format group. We now compare this group to the values of the edite format group.
            // We compare them the simple way, by stringifying them, and comparing the strings.
        
            var original_string = JSON.stringify(original_format_group_object);
            var new_string = JSON.stringify(edited_format_group_object);
        
            return ( original_string != new_string );
        };

    /************************************************************************************//**
    *   \brief  This will return a new format ID that comes after all the available ones.   *
    *   \returns an integer, containing the next format ID.                                 *
    ****************************************************************************************/
        this.getNextFormatID = function () {
            var ret = 0;
        
            for (var index = 0; index < g_formats_array.length; index++) {
                var format_group = g_formats_array[index];
                if ( parseInt(format_group.id, 10) > ret ) {
                    ret = parseInt(format_group.id, 10);
                };
            };
        
            return ( ret + 1 );
        };
    };
    
    // #mark -
    // #mark ########## Constructor ##########
    // #mark -

    /************************************************************************************//**
    *                                     CONSTRUCTOR                                       *
    ****************************************************************************************/
    this.m_server_admin_panel_shown = false;
    this.m_account_panel_shown = false;
    this.m_search_specifier_shown = true;
    this.m_meeting_editor_panel_shown = false;
    this.m_warn_user_to_refresh = false;
    this.m_success_fade_duration = 10000;        ///< 10 seconds for a success fader.
    this.m_failure_fade_duration = 10000;        ///< 10 seconds for a failure fader.
    this.m_format_editor_table_rows = 0;
};
    
// #mark -
// #mark ########## Global Functions ##########
// #mark -

var admin_handler_object = new BMLT_Server_Admin;

// #mark -
// #mark AJAX Handler
// #mark -

/****************************************************************************************//**
 *   \brief A simple, generic AJAX file upload function.                                     *
 *                                                                                           *
 *   \returns a new XMLHTTPRequest object.                                                   *
 ********************************************************************************************/
function BMLT_AjaxRequest_FileUpload(
    url,        ///< The URI to be called
    callback,   ///< The success callback
    file,       ///< The file object
    extra_data  ///< If supplied, extra data to be delivered to the callback.
) {
    /************************************************************************************//**
     *   \brief Create a generic XMLHTTPObject.                                              *
     *                                                                                       *
     *   This will account for the various flavors imposed by different browsers.            *
     *                                                                                       *
     *   \returns a new XMLHTTPRequest object.                                               *
     ****************************************************************************************/

    function createXMLHTTPObject()
    {
        var XMLHttpArray = [
            function () {
                return new XMLHttpRequest()},
            function () {
                return new ActiveXObject("Msxml2.XMLHTTP")},
            function () {
                return new ActiveXObject("Msxml2.XMLHTTP")},
            function () {
                return new ActiveXObject("Microsoft.XMLHTTP")}
        ];

        var xmlhttp = false;

        for (var i=0; i < XMLHttpArray.length; i++) {
            try {
                xmlhttp = XMLHttpArray[i]();
            } catch (e) {
                continue;
            };
            break;
        };

        return xmlhttp;
    };

    var req = createXMLHTTPObject();
    req.finalCallback = callback;

    if ( extra_data != null ) {
        req.extra_data = extra_data;
    }
    req.open("POST", url, true);
    req.onreadystatechange = function() {
        if ( req.readyState != 4 ) {
            return;
        }
        if ( req.status != 200 ) {
            return;
        }
        callback(req, req.extra_data);
        req = null;
    };

    var formData = new FormData();
    formData.append("thefile", file);
    req.send(formData);

    return req;
};

/****************************************************************************************//**
 *   \brief A simple, generic AJAX request function.                                         *
 *                                                                                           *
 *   \returns a new XMLHTTPRequest object.                                                   *
 ********************************************************************************************/
function BMLT_AjaxRequest(
    url,        ///< The URI to be called
    callback,   ///< The success callback
    method,     ///< The method ('get' or 'post')
    extra_data  ///< If supplied, extra data to be delivered to the callback.
) {
    /************************************************************************************//**
    *   \brief Create a generic XMLHTTPObject.                                              *
    *                                                                                       *
    *   This will account for the various flavors imposed by different browsers.            *
    *                                                                                       *
    *   \returns a new XMLHTTPRequest object.                                               *
    ****************************************************************************************/
    
    function createXMLHTTPObject()
    {
        var XMLHttpArray = [
            function () {
                return new XMLHttpRequest()},
            function () {
                return new ActiveXObject("Msxml2.XMLHTTP")},
            function () {
                return new ActiveXObject("Msxml2.XMLHTTP")},
            function () {
                return new ActiveXObject("Microsoft.XMLHTTP")}
            ];
            
        var xmlhttp = false;
        
        for (var i=0; i < XMLHttpArray.length; i++) {
            try {
                xmlhttp = XMLHttpArray[i]();
            } catch (e) {
                continue;
            };
            break;
        };
        
        return xmlhttp;
    };
    
    var req = createXMLHTTPObject();
    req.finalCallback = callback;
    var sVars = null;
    method = method.toString().toUpperCase();
    var drupal_kludge = '';
    
    // Split the URL up, if this is a POST.
    if ( method == "POST" ) {
        var rmatch = /^([^\?]*)\?(.*)$/.exec(url);
        url = rmatch[1];
        sVars = rmatch[2];
        // This horrible, horrible kludge, is because Drupal insists on having its q parameter in the GET list only.
        var rmatch_kludge = /(q=admin\/settings\/bmlt)&?(.*)/.exec(rmatch[2]);
        if ( rmatch_kludge && rmatch_kludge[1] ) {
            url += '?'+rmatch_kludge[1];
            sVars = rmatch_kludge[2];
        };
    };
    if ( extra_data != null ) {
        req.extra_data = extra_data;
    };
    req.open(method, url, true);
    if ( method == "POST" ) {
        req.setRequestHeader("Method", "POST "+url+" HTTP/1.1");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    };
    req.onreadystatechange = function ( ) {
        if ( req.readyState != 4 ) {
            return;
        }
        if ( req.status != 200 ) {
            return;
        }
        callback(req, req.extra_data);
        req = null;
    };
    req.send(sVars);
    
    return req;
};

// #mark -
// #mark Utility Functions
// #mark -

/****************************************************************************************//**
*   \brief Starts the message "fader."                                                      *
*                                                                                           *
*   Simple fader, taken from here:                                                          *
*       http://www.switchonthecode.com/tutorials/javascript-tutorial-simple-fade-animation  *
********************************************************************************************/
function BMLT_Admin_StartFader(
    in_eid,         ///< The element ID to be faded.
    in_fade_time    ///< The number of seconds to fade.
) {
    var in_element = document.getElementById(in_eid);
    if ( in_element ) {
        in_element.className = 'bmlt_admin_fader_div';
        in_element.FadeTimeTotal = in_fade_time;
        in_element.FadeTimeLeft = in_element.FadeTimeTotal;
        setTimeout("BMLT_Admin_animateFade(" + new Date().getTime() + ",'" + in_eid + "')", 33);
    };
};

/****************************************************************************************//**
*   \brief Animates the fade.                                                               *
*                                                                                           *
*   Simple fader, taken from here:                                                          *
*       http://www.switchonthecode.com/tutorials/javascript-tutorial-simple-fade-animation  *
********************************************************************************************/
function BMLT_Admin_animateFade(
    lastTick,       ///< The time of the last tick.
    in_eid          ///< The element ID
) {
    var in_element = document.getElementById(in_eid);
    if ( in_element ) {
        var curTick = new Date().getTime();
        var elapsedTicks = curTick - lastTick;
    
        if ( in_element.FadeTimeLeft <= elapsedTicks ) {
            in_element.className = 'bmlt_admin_fader_div item_hidden';
            in_element.FadeTimeTotal = null;
            in_element.FadeTimeLeft = null;
            in_element.FadeState = null;
            in_element.style.opacity = null;
            in_element.style.filter = null;
            return;
        };
    
        in_element.FadeTimeLeft -= elapsedTicks;
    
        var newOpVal = in_element.FadeTimeLeft/in_element.FadeTimeTotal;
    
        in_element.style.opacity = newOpVal;
        in_element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
    
        setTimeout("BMLT_Admin_animateFade(" + curTick + ",'" + in_eid + "')", 33);
    };
};

/****************************************************************************************//**
*   \brief This allows you to get objects within a DOM node hierarchy that have a certain   *
*          element name (type, such as 'div' or 'a'), and a className.                      *
*          This can be used to "drill into" a DOM hierarchy that doesn't have IDs.          *
*   \returns an array of DOM elements that meet the criteria.                               *
********************************************************************************************/
function BMLT_Admin_getChildElementsByClassName(
    in_container_element,   ///< The DOM node that contains the hierarchy
    in_element_type,        ///< The type of node that you are seeking.
    in_element_className    ///< The className for that element.
) {
    var starting_pool = in_container_element.getElementsByTagName(in_element_type);
    var ret = [];
    for (c = 0; c < starting_pool.length; c++) {
        if ( starting_pool[c].className == in_element_className ) {
            ret.append(starting_pool[c]);
        };
        
        var ret2 = BMLT_Admin_getChildElementsByClassName(starting_pool[c], in_element_type, in_element_className);
        
        if ( ret2 && ret2.length ) {
            ret = ret.concat(ret2);
        };
    };

    return ret;
};

/****************************************************************************************//**
*   \brief This allows you to search a particular DOM hierarchy for an element with an ID.  *
*          This is useful for changing Node IDs in the case of cloneNode().                 *
*   \returns a single DOM element, with the given ID.                                       *
********************************************************************************************/
function BMLT_Admin_getChildElementById(
    in_container_element,   ///< The DOM node that contains the hierarchy
    in_element_id           ///< The ID you are looking for.
) {
    var ret = null;
    
    if ( in_container_element && in_container_element.id == in_element_id ) { // Low-hanging fruit.
        ret = in_container_element;
    } else {
        // If we have kids, we check each of them for the ID.
        if ( in_container_element && in_container_element.childNodes && in_container_element.childNodes.length ) {
            for (var c = 0; c < in_container_element.childNodes.length; c++) {
                ret = BMLT_Admin_getChildElementsById(in_container_element.childNodes[c], in_element_id);
                
                if ( ret ) {
                    break;
                };
            };
        };
    };
    
    return ret;
};

/****************************************************************************************//**
*   \brief This replaces every instance of 'template' in a hierarchy's element ids with the *
*          the given ID.                                                                    *
********************************************************************************************/
function BMLT_Admin_changeTemplateIDToUseThisID(
    in_container_element,   ///< The DOM node that contains the hierarchy
    in_element_id           ///< The ID you are replacing with.
) {
    var ret = null;
    
    if ( in_container_element ) {
        if ( in_container_element.attributes && in_container_element.attributes.length ) {
            for (var c = 0; c < in_container_element.attributes.length; c++) {
                if ( in_container_element.attributes[c].nodeValue ) {
                    in_container_element.attributes[c].nodeValue = in_container_element.attributes[c].nodeValue.toString().replace(/template/g, in_element_id);
                };
            };
        };
        
        if ( in_container_element.id ) {
            in_container_element.id = in_container_element.id.replace(/template/g, in_element_id);
        };
    };
        
    // If we have kids, we check each of them for the ID.
    if ( in_container_element && in_container_element.childNodes && in_container_element.childNodes.length ) {
        for (var c = 0; c < in_container_element.childNodes.length; c++) {
            BMLT_Admin_changeTemplateIDToUseThisID(in_container_element.childNodes[c], in_element_id);
        };
    };
    
    return ret;
};

/************************************************************************************//**
 *   \brief
 ****************************************************************************************/
function BMLT_Admin_setSelectByStringValue(
    in_select_object,
    in_value
) {
    var setIndex = 0;

    for (var c = 0; c < in_select_object.options.length; c ++) {
        if (in_select_object.options[c].value == in_value) {
            setIndex = c;
            break;
        }
    }

    in_select_object.selectedIndex = setIndex;
}

/************************************************************************************//**
*   \brief
****************************************************************************************/
function BMLT_Admin_setSelectByValue(
    in_select_object,
    in_value
) {
    var setIndex = 0;
    
    for (var c = 0; c < in_select_object.options.length; c ++) {
        if ( parseInt(in_select_object.options[c].value, 10) == parseInt(in_value, 10) ) {
            setIndex = c;
            break;
        };
    };
        
    in_select_object.selectedIndex = setIndex;
};
  
/****************************************************************************************//**
*   \brief This just traps the enter key for the text entry.                                *
********************************************************************************************/
function BMLT_Admin_keyDown(event)
{
    if ( admin_handler_object.m_search_specifier_shown && (event.keyCode == 13) ) {
        admin_handler_object.searchForMeetings();
    };
};

// #mark -
// #mark ########## Third-Party Code ##########
// #mark -
function htmlspecialchars_decode(
    string,
    quote_style
) {
  // http://kevin.vanzonneveld.net
  // +   original by: Mirek Slugen
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Mateusz "loonquawl" Zalega
  // +      input by: ReverseSyntax
  // +      input by: Slawomir Kaniecki
  // +      input by: Scott Cariss
  // +      input by: Francois
  // +   bugfixed by: Onno Marsman
  // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +      input by: Ratheous
  // +      input by: Mailfaker (http://www.weedem.fr/)
  // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
  // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
  // *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
  // *     returns 1: '<p>this -> &quot;</p>'
  // *     example 2: htmlspecialchars_decode("&amp;quot;");
  // *     returns 2: '&quot;'
    var   optTemp = 0,
        i = 0,
        noquotes = false;

    if (typeof quote_style === 'undefined') {
        quote_style = 2;
    };
  
    string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
  
    if (quote_style === 0) {
        noquotes = true;
    };

    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            } else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            };
        };
    
        quote_style = optTemp;
    };

    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
      // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
    };

    if (!noquotes) {
        string = string.replace(/&quot;/g, '"');
    };

  // Put this in last place to avoid escape being double-decoded
    string = string.replace(/&amp;/g, '&');

    return string;
};

/**
sprintf() for JavaScript 0.6

Copyright (c) Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of sprintf() for JavaScript nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Alexandru Marasteanu BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


Changelog:
2007.04.03 - 0.1:
 - initial release
2007.09.11 - 0.2:
 - feature: added argument swapping
2007.09.17 - 0.3:
 - bug fix: no longer throws exception on empty paramenters (Hans Pufal)
2007.10.21 - 0.4:
 - unit test and patch (David Baird)
2010.05.09 - 0.5:
 - bug fix: 0 is now preceeded with a + sign
 - bug fix: the sign was not at the right position on padded results (Kamal Abdali)
 - switched from GPL to BSD license
2010.05.22 - 0.6:
 - reverted to 0.4 and fixed the bug regarding the sign of the number 0
 Note:
 Thanks to Raphael Pigulla <raph (at] n3rd [dot) org> (http://www.n3rd.org/)
 who warned me about a bug in 0.5, I discovered that the last update was
 a regress. I appologize for that.
**/

function sprintf()
{
    function str_repeat(i, m)
    {
        for (var o = []; m > 0; o[--m] = i) {
        }
        return o.join('');
    };

    var i = 0, a, f = arguments[i++], o = [], m, p, c, x, s = '';
    
    while (f) {
        if (m = /^[^\x25]+/.exec(f)) {
            o.push(m[0]);
        } else if (m = /^\x25{2}/.exec(f)) {
            o.push('%');
        } else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
            if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) {
                throw('Too few arguments.');
            };
            
            if (/[^s]/.test(m[7]) && (typeof(a) != 'number')) {
                throw('Expecting number but found ' + typeof(a));
            };
            
            switch (m[7]) {
                case 'b': a = a.toString(2); break;
                case 'c': a = String.fromCharCode(a); break;
                case 'd': a = parseInt(a,10); break;
                case 'e': a = m[6] ? a.toExponential(m[6]) : a.toExponential(); break;
                case 'f': a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a); break;
                case 'o': a = a.toString(8); break;
                case 's': a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a); break;
                case 'u': a = Math.abs(a); break;
                case 'x': a = a.toString(16); break;
                case 'X': a = a.toString(16).toUpperCase(); break;
            };
            
            a = (/[def]/.test(m[7]) && m[2] && a >= 0 ? '+'+ a : a);
            c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
            x = m[5] - String(a).length - s.length;
            p = m[5] ? str_repeat(c, x) : '';
            o.push(s + (m[4] ? a + p : p + a));
        } else {
            throw('Huh ?!');
        };
        
        f = f.substring(m[0].length);
    };
    
    return o.join('');
};


var g_google_api_key_is_good = false;
var showGoogleApiKeyError = function (message) {
    var errorElement = document.getElementById('google_maps_api_error_div');
    if (errorElement) {
        errorElement.className = errorElement.className.replace(/\bitem_hidden\b/g, "");
    }
    errorElement = document.getElementById('google_maps_api_error_a');
    if (errorElement) {
        errorElement.innerText = message;
    }
};

(function takeOverConsole() { // taken from http://tobyho.com/2012/07/27/taking-over-console-log/
    var console = window.console;
    if (!console) {
        return;
    }

    function intercept(method) {
        var original = console[method];
        console[method] = function() {
            // check message
            if (arguments[0].indexOf("Google Maps") !== -1) {
                var message = arguments[0];
                var idx = message.indexOf("\n");
                if (idx !== -1) {
                    message = message.substring(0, idx);
                }
                showGoogleApiKeyError(message);
            }

            if (original.apply) {
                // Do this for normal browsers
                original.apply(console, arguments);
            } else {
                // Do this for IE
                original(Array.prototype.slice.apply(arguments).join(' '));
            }
        }
    }
    var methods = ['error']; // only interested in the console.error method
    for (var i = 0; i < methods.length; i++) {
        intercept(methods[i]);
    }
}());

if (!g_google_api_key || !g_google_api_key.trim()) {
    showGoogleApiKeyError(g_maps_api_key_not_set);
} else {
    var geocoder = new google.maps.Geocoder;
    geocoder.geocode({'address':'27205'}, function (response, status) {
        g_google_api_key_is_good = status === 'OK';
        if (!g_google_api_key_is_good) {
            showGoogleApiKeyError(g_maps_api_key_warning);
        }
    });
}
