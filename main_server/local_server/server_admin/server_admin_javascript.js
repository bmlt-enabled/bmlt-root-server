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
/********************************************************************************************
*######################################### MAIN CODE #######################################*
********************************************************************************************/

function BMLT_Server_Admin ()
{
    /************************************************************************************//**
    *                                     DATA MEMBERS                                      *
    ****************************************************************************************/
    var m_account_panel_shown = null;           ///< This will be true if the "My Account" panel is exposed.
    var m_search_specifier_shown = null;        ///< This is true, if the meeting search specifier form is shown.
    var m_meeting_editor_panel_shown = null;    ///< This will be true if the "Edit Meetings" panel is exposed.
    var m_ajax_request_in_progress = null;      ///< This is any AJAX request currently under way.
    var m_success_fade_duration = null;         ///< Number of milliseconds for a success fader.
    var m_failure_fade_duration = null;         ///< Number of milliseconds for a failure fader.
    var m_search_results = null;                ///< This will contain any meeting search results.
    
    /************************************************************************************//**
    *                                       METHODS                                         *
    ****************************************************************************************/
    /************************************************************************************//**
    *                                   USED THROUGHOUT                                     *
    ****************************************************************************************/
    /************************************************************************************//**
    *   \brief When a text input (either <input> or <textarea> is initialized, we can set   *
    *          up a default text value that is displayed when the item is empty and not in  *
    *          focus. If we don't send in a specific value, then the current value of the   *
    *          text item is considered to be the default.                                   *
    ****************************************************************************************/
    this.handleTextInputLoad = function(    in_text_item,
                                            in_default_value
                                        )
    {
        if ( in_text_item )
            {
            in_text_item.original_value = in_text_item.value;
            
            if ( in_default_value )
                {
                in_text_item.defaultValue = in_default_value;
                }
            else
                {
                in_text_item.defaultValue = in_text_item.value;
                };
            
            in_text_item.value = in_text_item.original_value;

            if ( !in_text_item.value || (in_text_item.value == in_text_item.defaultValue) )
                {
                in_text_item.className = 'bmlt_text_item bmlt_text_item_dimmed';
                }
            else
                {
                in_text_item.className = 'bmlt_text_item';
                };
            };
    };
    
    /************************************************************************************//**
    *   \brief When a text item receives focus, we clear any default text.                  *
    ****************************************************************************************/
    this.handleTextInputFocus = function(    in_text_item
                                        )
    {
        if ( in_text_item )
            {
            if ( in_text_item.value == in_text_item.defaultValue )
                {
                in_text_item.value = '';
                };
            
            this.validateAccountGoButton();
            };
    };
    
    /************************************************************************************//**
    *   \brief When a text item loses focus, we restore any default text, if the item was   *
    *          left empty.                                                                  *
    ****************************************************************************************/
    this.handleTextInputBlur = function(    in_text_item
                                        )
    {
        if ( in_text_item )
            {
            if ( !in_text_item.value || (in_text_item.value == in_text_item.defaultValue) )
                {
                in_text_item.value = in_text_item.defaultValue;
                in_text_item.className = 'bmlt_text_item bmlt_text_item_dimmed';
                }
            else
                {
                in_text_item.className = 'bmlt_text_item';
                };
            
            this.validateAccountGoButton();
            };
    };
    
    /************************************************************************************//**
    *   \brief When a text item has its text changed, we check to see if it needs to have   *
    *          its classname changed to the default (usually won't make a difference, as    *
    *          the text item will be in focus, anyway).                                     *
    ****************************************************************************************/
    this.handleTextInputChange = function(  in_text_item
                                        )
    {
        if ( in_text_item )
            {
            if ( !in_text_item.value || (in_text_item.value == in_text_item.defaultValue) )
                {
                in_text_item.className = 'bmlt_text_item bmlt_text_item_dimmed';
                }
            else
                {
                in_text_item.className = 'bmlt_text_item';
                };
            
            this.validateAccountGoButton();
            };
    };
    
    /************************************************************************************//**
    *                                 MY ACCOUNT SECTION                                    *
    ****************************************************************************************/
    /************************************************************************************//**
    *   \brief  Toggles the visibility of the account info section.                         *
    ****************************************************************************************/
    this.toggleAccountInfo = function()
    {
        this.m_account_panel_shown = !this.m_account_panel_shown;
        
        var the_disclosure_div = document.getElementById ( 'bmlt_admin_user_account_disclosure_div' );
        var the_account_info_div = document.getElementById ( 'bmlt_admin_user_account_wrapper_div' );
        
        if ( this.m_account_panel_shown)
            {
            the_disclosure_div.className = 'bmlt_admin_user_account_disclosure_div bmlt_admin_user_account_disclosure_div_open';
            the_account_info_div.className = 'bmlt_admin_user_account_wrapper_div';
            }
        else
            {
            the_disclosure_div.className = 'bmlt_admin_user_account_disclosure_div bmlt_admin_user_account_disclosure_div_closed';
            the_account_info_div.className = 'bmlt_admin_user_account_wrapper_div bmlt_admin_user_account_wrapper_div_hidden';
            };
    };
    
    /************************************************************************************//**
    *   \brief This checks the values of the text items in the My Account section. If any   *
    *          of them are different from their default, we enable the GO button.           *
    ****************************************************************************************/
    this.validateAccountGoButton = function()
    {
        var email_field = document.getElementById ( 'bmlt_admin_user_email_input' );
        var password_field = document.getElementById ( 'bmlt_admin_user_account_password_input' );
        var ajax_button = document.getElementById ( 'bmlt_admin_account_change_ajax_button' );
        var description = document.getElementById ( 'bmlt_admin_user_description_textarea' );
        
        if (    (email_field.value != email_field.original_value)
            ||  (description.value != description.original_value)
            ||  (password_field.value && (password_field.value != password_field.defaultValue)) )
            {
            ajax_button.className = 'bmlt_admin_ajax_button';
            }
        else
            {
            ajax_button.className = 'bmlt_admin_ajax_button button_disabled';
            };
    };
    
    /************************************************************************************//**
    *   \brief This is called to initiate an AJAX process to change the account settings.   *
    ****************************************************************************************/
    this.handleAccountChange = function()
    {
        var email_field = document.getElementById ( 'bmlt_admin_user_email_input' );
        var password_field = document.getElementById ( 'bmlt_admin_user_account_password_input' );
        var description = document.getElementById ( 'bmlt_admin_user_description_textarea' );
        var affected_user_id = document.getElementById ( 'account_affected_user_id' );
        
        // We only do something if there is a difference.
        if (    (affected_user_id.value == g_current_user_id)   // Belt and suspenders...
            &&  ((email_field.value != email_field.original_value)
            ||  (description.value != description.original_value)
            ||  (password_field.value && (password_field.value != password_field.defaultValue))) )
            {
            this.setMyAccountThrobber ( true );
            var uri = g_ajax_callback_uri + '&target_user=' + encodeURIComponent ( g_current_user_id );
            if ( email_field.value != email_field.original_value )
                {
                uri += '&account_email_value=' + encodeURIComponent ( email_field.value );
                };
            
            if ( description.value != description.original_value )
                {
                uri += '&account_description_value=' + encodeURIComponent ( description.value );
                };
            
            if ( password_field.value && (password_field.value != password_field.defaultValue) )
                {
                uri += '&account_password_value=' + encodeURIComponent ( password_field.value );
                };
            
            if ( this.m_ajax_request_in_progress )
                {
                this.m_ajax_request_in_progress.abort();
                this.m_ajax_request_in_progress = null;
                };
            this.m_ajax_request_in_progress = BMLT_AjaxRequest ( uri, function(in_req,in_data) { admin_handler_object.handleAccountChangeAJAXCallback(in_req,in_data); }, 'post', this );
            };
    };
    
    /************************************************************************************//**
    *   \brief This is called to initiate an AJAX process to change the account settings.   *
    ****************************************************************************************/
    this.handleAccountChangeAJAXCallback = function(    in_http_request,
                                                        in_context
                                                    )
    {
        var email_field = document.getElementById ( 'bmlt_admin_user_email_input' );
        var password_field = document.getElementById ( 'bmlt_admin_user_account_password_input' );
        var description = document.getElementById ( 'bmlt_admin_user_description_textarea' );
        
        this.m_ajax_request_in_progress = null;
        if ( in_http_request.responseText )
            {
            eval ( 'var json_object = ' + in_http_request.responseText + ';' );
            }
            
        if ( json_object.ACCOUNT_CHANGED )
            {
            var success = true;
            
            if ( json_object.ACCOUNT_CHANGED.EMAIL_CHANGED == true )
                {
                email_field.original_value = email_field.value;
                }
            else if ( json_object.ACCOUNT_CHANGED.EMAIL_CHANGED == false )
                {
                success = false;
                };
                
            if ( json_object.ACCOUNT_CHANGED.DESCRIPTION_CHANGED == true )
                {
                description.original_value = description.value;
                }
            else if ( json_object.ACCOUNT_CHANGED.DESCRIPTION_CHANGED == false )
                {
                success = false;
                };
            
            password_field.value = '';
            this.validateAccountGoButton();
            
            if ( success )
                {
                BMLT_Admin_StartFader ( 'bmlt_admin_fader_account_success_div', this.m_success_fade_duration );
                }
            else
                {
                BMLT_Admin_StartFader ( 'bmlt_admin_fader_account_fail_div', this.m_failure_fade_duration );
                };
            }
        else
            {
            BMLT_Admin_StartFader ( 'bmlt_admin_fader_account_fail_div', this.m_failure_fade_duration );
            };
        
        this.handleTextInputBlur ( email_field );
        this.handleTextInputBlur ( password_field );
        this.handleTextInputBlur ( description );
        
        this.setMyAccountThrobber ( false );
    };
    
    /************************************************************************************//**
    *   \brief Displays or hides the AJAX Throbber for the My Account button.               *
    ****************************************************************************************/
    this.setMyAccountThrobber = function(   in_shown    ///< If true, the throbber is show. If false, it is hidden.
                                        )
    {
        var button_span = document.getElementById ( 'bmlt_admin_account_change_ajax_button_span' );
        var throbber_span = document.getElementById ( 'bmlt_admin_account_change_ajax_button_throbber_span' );
        
        throbber_span.className = 'bmlt_admin_value_left' + (in_shown ? '' : ' item_hidden');
        button_span.className = 'bmlt_admin_value_left' + (in_shown ? ' item_hidden' : '');
    };
    
    /************************************************************************************//**
    *                               MEETING EDITOR SECTION                                  *
    ****************************************************************************************/
    /************************************************************************************//**
    *   \brief  Toggles the visibility of the meeting editor section.                       *
    ****************************************************************************************/
    this.toggleMeetingEditor = function()
    {
        this.m_meeting_editor_panel_shown = !this.m_meeting_editor_panel_shown;
        
        var the_disclosure_div = document.getElementById ( 'bmlt_admin_meeting_editor_disclosure_div' );
        var the_editor_div = document.getElementById ( 'bmlt_admin_meeting_editor_wrapper_div' );
        
        if ( this.m_meeting_editor_panel_shown )
            {
            the_disclosure_div.className = 'bmlt_admin_meeting_editor_disclosure_div bmlt_admin_meeting_editor_disclosure_div_open';
            the_editor_div.className = 'bmlt_admin_meeting_editor_wrapper_div';
            }
        else
            {
            the_disclosure_div.className = 'bmlt_admin_meeting_editor_disclosure_div bmlt_admin_meeting_editor_disclosure_div_closed';
            the_editor_div.className = 'bmlt_admin_meeting_editor_wrapper_div bmlt_admin_meeting_editor_wrapper_div_hidden';
            };
    };
    
    /************************************************************************************//**
    *   \brief  This makes sure that the "All" checkbox syncs with the weekdays.            *
    ****************************************************************************************/
    this.handleWeekdayCheckBoxChanges = function(   in_checkbox_index ///< The checkbox that triggered the call.
                                                )
    {
        var all_checkbox = document.getElementById ( 'bmlt_admin_meeting_search_weekday_checkbox_0' );
        var weekday_checkboxes = new Array (document.getElementById ( 'bmlt_admin_meeting_search_weekday_checkbox_1' ),
                                            document.getElementById ( 'bmlt_admin_meeting_search_weekday_checkbox_2' ),
                                            document.getElementById ( 'bmlt_admin_meeting_search_weekday_checkbox_3' ),
                                            document.getElementById ( 'bmlt_admin_meeting_search_weekday_checkbox_4' ),
                                            document.getElementById ( 'bmlt_admin_meeting_search_weekday_checkbox_5' ),
                                            document.getElementById ( 'bmlt_admin_meeting_search_weekday_checkbox_6' ),
                                            document.getElementById ( 'bmlt_admin_meeting_search_weekday_checkbox_7' ) );
        
        if ( in_checkbox_index )
            {
            var weekday_selected = false;
            for ( var c = 0; c < 7; c++ )
                {
                if ( weekday_checkboxes[c].checked )
                    {
                    weekday_selected = true;
                    };
                };
            
            all_checkbox.checked = !weekday_selected;
            }
        else
            {
            all_checkbox.checked = true;
            
            for ( var c = 0; c < 7; c++ )
                {
                weekday_checkboxes[c].checked = false;
                };
            };
    };
    
    /************************************************************************************//**
    *   \brief  Displays the Search Specifier, and hides any search results.                *
    ****************************************************************************************/
    this.showSearchSpecifier = function()
    {
        this.m_search_specifier_shown = true;
        this.setSearchResultsVisibility();
    };
    
    /************************************************************************************//**
    *   \brief  Displays the Search Specifier, and hides any search results.                *
    ****************************************************************************************/
    this.showSearchResults = function()
    {
        // No search results, no visible results div.
        this.m_search_specifier_shown = (this.m_search_results ? false : true);
        this.setSearchResultsVisibility();
    };
    
    /************************************************************************************//**
    *   \brief  Displays the Search Results or specifier, dependent upon the switch.        *
    ****************************************************************************************/
    this.setSearchResultsVisibility = function()
    {
        var search_specifier_div = document.getElementById ( 'bmlt_admin_meeting_editor_form_specifier_div' );
        var search_results_div = document.getElementById ( 'bmlt_admin_meeting_editor_form_results_div' );
        
        if ( this.m_search_specifier_shown )
            {
            search_specifier_div.className = 'bmlt_admin_meeting_editor_form_specifier_div';
            search_results_div.className = 'bmlt_admin_meeting_editor_form_results_div item_hidden';
            }
        else
            {
            search_specifier_div.className = 'bmlt_admin_meeting_editor_form_specifier_div item_hidden';
            search_results_div.className = 'bmlt_admin_meeting_editor_form_results_div';
            };
    };

    /************************************************************************************//**
    *                                     CONSTRUCTOR                                       *
    ****************************************************************************************/
    this.m_account_panel_shown = false;
    this.m_search_specifier_shown = true;
    this.m_meeting_editor_panel_shown = false;
    this.m_success_fade_duration = 2000;        ///< 2 seconds for a success fader.
    this.m_failure_fade_duration = 5000;        ///< 5 seconds for a success fader.
};

var admin_handler_object = new BMLT_Server_Admin;

/********************************************************************************************
*###################################### AJAX HANDLER #######################################*
********************************************************************************************/

/****************************************************************************************//**
*   \brief A simple, generic AJAX request function.                                         *
*                                                                                           *
*   \returns a new XMLHTTPRequest object.                                                   *
********************************************************************************************/
    
function BMLT_AjaxRequest ( url,        ///< The URI to be called
                            callback,   ///< The success callback
                            method,     ///< The method ('get' or 'post')
                            extra_data  ///< If supplied, extra data to be delivered to the callback.
                            )
{
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
            function() {return new XMLHttpRequest()},
            function() {return new ActiveXObject("Msxml2.XMLHTTP")},
            function() {return new ActiveXObject("Msxml2.XMLHTTP")},
            function() {return new ActiveXObject("Microsoft.XMLHTTP")}
            ];
            
        var xmlhttp = false;
        
        for ( var i=0; i < XMLHttpArray.length; i++ )
            {
            try
                {
                xmlhttp = XMLHttpArray[i]();
                }
            catch(e)
                {
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
    if ( method == "POST" )
        {
        var rmatch = /^([^\?]*)\?(.*)$/.exec ( url );
        url = rmatch[1];
        sVars = rmatch[2];
        // This horrible, horrible kludge, is because Drupal insists on having its q parameter in the GET list only.
        var rmatch_kludge = /(q=admin\/settings\/bmlt)&?(.*)/.exec ( rmatch[2] );
        if ( rmatch_kludge && rmatch_kludge[1] )
            {
            url += '?'+rmatch_kludge[1];
            sVars = rmatch_kludge[2];
            };
        };
    if ( extra_data )
        {
        req.extra_data = extra_data;
        };
    req.open ( method, url, true );
	if ( method == "POST" )
        {
        req.setRequestHeader("Method", "POST "+url+" HTTP/1.1");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        };
    req.onreadystatechange = function ( )
        {
        if ( req.readyState != 4 ) return;
        if( req.status != 200 ) return;
        callback ( req, req.extra_data );
        req = null;
        };
    req.send ( sVars );
    
    return req;
};

/****************************************************************************************//**
*   \brief Starts the message "fader."                                                      *
*                                                                                           *
*   Simple fader, taken from here:                                                          *
*       http://www.switchonthecode.com/tutorials/javascript-tutorial-simple-fade-animation  *
********************************************************************************************/
function BMLT_Admin_StartFader( in_eid,         ///< The element ID to be faded.
                                in_fade_time    ///< The number of seconds to fade.
                                )
{
    var in_element = document.getElementById( in_eid );
    if ( in_element )
        {
        in_element.className = 'bmlt_admin_fader_div';
        in_element.FadeTimeTotal = in_fade_time;
        if ( in_element.FadeState == null )
            {
            if ( in_element.style.opacity == null 
                || in_element.style.opacity == '' 
                || in_element.style.opacity == '1' )
                {
                in_element.FadeState = 2;
                }
            else
                {
                in_element.FadeState = -2;
                };
            };
        
        if ( in_element.FadeState == 1 || in_element.FadeState == -1 )
            {
            in_element.FadeState = element.FadeState == 1 ? -1 : 1;
            in_element.FadeTimeLeft = in_element.FadeTimeTotal - in_element.FadeTimeLeft;
            }
        else
            {
            in_element.FadeState = in_element.FadeState == 2 ? -1 : 1;
            in_element.FadeTimeLeft = in_element.FadeTimeTotal;
            setTimeout ( "BMLT_Admin_animateFade(" + new Date().getTime() + ",'" + in_eid + "')", 33);
            };
            
        if ( in_element.FadeTimeLeft <= 0.0 )
            {
            in_element.className = 'bmlt_admin_fader_div item_hidden';
            };
        };
};

/****************************************************************************************//**
*   \brief Animates the fade.                                                               *
*                                                                                           *
*   Simple fader, taken from here:                                                          *
*       http://www.switchonthecode.com/tutorials/javascript-tutorial-simple-fade-animation  *
********************************************************************************************/
function BMLT_Admin_animateFade (   lastTick,       ///< The time of the last tick.
                                    in_eid          ///< The element ID
                                )
{  
    var in_element = document.getElementById( in_eid );
    if ( in_element )
        {
        var curTick = new Date().getTime();
        var elapsedTicks = curTick - lastTick;
    
        if ( in_element.FadeTimeLeft <= elapsedTicks )
            {
            in_element.style.opacity = in_element.FadeState == 1 ? '1' : '0';
            in_element.style.filter = 'alpha(opacity = ' + (in_element.FadeState == 1 ? '100' : '0') + ')';
            in_element.FadeState = in_element.FadeState == 1 ? 2 : -2;
            return;
            };
    
        in_element.FadeTimeLeft -= elapsedTicks;
    
        var newOpVal = in_element.FadeTimeLeft/in_element.FadeTimeTotal;
    
        if ( in_element.FadeState == 1 )
            {
            newOpVal = 1 - newOpVal;
            };
    
        in_element.style.opacity = newOpVal;
        in_element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
    
        setTimeout ( "BMLT_Admin_animateFade(" + curTick + ",'" + in_eid + "')", 33 );
        };
};

/********************************************************************************************
*###################################### THIRD-PARTY CODE ###################################*
********************************************************************************************/
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
        for (var o = []; m > 0; o[--m] = i);
        return o.join('');
    };

    var i = 0, a, f = arguments[i++], o = [], m, p, c, x, s = '';
    
    while (f)
        {
        if (m = /^[^\x25]+/.exec(f))
            {
            o.push(m[0]);
            }
        else if (m = /^\x25{2}/.exec(f))
            {
            o.push('%');
            }
        else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f))
            {
            if (((a = arguments[m[1] || i++]) == null) || (a == undefined))
                {
                throw('Too few arguments.');
                };
            
            if (/[^s]/.test(m[7]) && (typeof(a) != 'number'))
                {
                throw('Expecting number but found ' + typeof(a));
                };
            
            switch (m[7])
                {
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
            }
        else
            {
            throw('Huh ?!');
            };
        
        f = f.substring(m[0].length);
        };
    
    return o.join('');
};
