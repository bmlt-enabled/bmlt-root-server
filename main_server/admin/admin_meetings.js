/***********************************************************************/
/** 	\file	admin_meetings.js

	\brief	This file will be optimized and embedded in the HTML that is
	returned for the create meeting form. It will control the JavaScript
	and AJAX used for the form.

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

/*******************************************************************/
/** \brief	This simply opens and closes the display of the new
	meeting form.
*/
function ToggleNewMeetingDiv()
{
	var	elem = document.getElementById('new_meeting_container_div');
	
	if ( elem )
		{
		if ( elem.className !='new_meeting_div_closed' )
			{
			elem.className = 'new_meeting_div_closed';
			}
		else
			{
			elem.className = 'new_meeting_div_open';
			};
		};
	
	elem = document.getElementById('new_meeting_div');
	
	if ( elem )
		{
		if ( elem.style.display !='none' )
			{
			elem.style.display = 'none';
			}
		else
			{
			elem.style.display = 'block';
			};
		};
};

/*******************************************************************/
/** \brief		
*/
function CreateNewMeetingHandler ()
{
	var uri = '##CHANGE_URI##?';
	
	var elem = document.getElementById('meeting_new_weekday_tinyint');
	
	if ( elem )
		{
		uri += 'weekday_tinyint='+elem.value;
		elem = document.getElementById('meeting_new_start_time_hour');
		var elem2 = document.getElementById('meeting_new_start_time_minute');
		
		if ( elem && elem2 )
			{
			uri += '&start_time='+elem.value+':'+elem2.value+':00';
			elem = document.getElementById('meeting_new_lang_enum');
			
			if ( elem )
				{
				uri += '&lang_enum='+elem.value;
				elem = document.getElementById('meeting_new_service_body_bigint');
				
				if ( elem )
					{
					var throbber_img = document.getElementById ( 'create_submit_throbber_img' );
					var button_input = document.getElementById ( 'new_meeting_button' );
					if ( throbber_img && button_input )
						{
						uri += '&service_body_bigint='+elem.value;
						throbber_img.src = '##IMAGE_DIR##'+'/ajax_throbber_linear_pink.gif';	
						button_input.style.display = 'none';
						throbber_img.style.display = 'inline';
						SimpleAJAXCall(uri,CreateMeetingCallback,'GET');
						}
					};
				};
			};
		};
};

/*******************************************************************/
/** \brief		
*/
function CreateMeetingCallback(in_text)
{
    in_text = in_text.replace(/[\r\n]*/g, "");  // 'Orrible kludge to account for servers being naughty.
	eval ( "var json_obj = "+in_text+";" );
	var done_failed = (null != json_obj.error);
	
	if ( done_failed )
		{
		alert ( json_obj.report+' ('+json_obj.info+')' );
		}
	else
		{
		var throbber_img = document.getElementById ( 'create_submit_throbber_img' );
		var button_input = document.getElementById ( 'new_meeting_button' );
		if ( throbber_img && button_input )
			{
			throbber_img.src = '';	
			button_input.disabled = true;
			button_input.style.display = 'inline';
			throbber_img.style.display = 'none';
			EditMeetingLink ( json_obj.meeting_id );
			}
		};
};
