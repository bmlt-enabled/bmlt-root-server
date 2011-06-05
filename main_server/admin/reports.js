/***********************************************************************/
/** 	\file	reports.js

	\brief	This file will be optimized and embedded in the HTML that is
	returned for the create meeting form. It will control the JavaScript
	and AJAX used for the reports display.

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
function ToggleReportsDiv( in_div_mod	/**< This allows you to re-use the same JS */
						)
{
	if ( !in_div_mod )
		{
		in_div_mod = '';
		};
	
	var	elem = document.getElementById ( in_div_mod+'reports_div_container_div_id' );
	if ( elem )
		{
		if ( elem.className != in_div_mod+'reports_div_closed' )
			{
			elem.className = in_div_mod+'reports_div_closed';
			}
		else
			{
			elem.className = in_div_mod+'reports_div_open';
			};
		};
	
	elem = document.getElementById ( in_div_mod+'reports_div_id' );
	
	if ( elem )
		{
		if ( elem.style.display !='none' )
			{
			elem.style.display = 'none';
			}
		else
			{
			// The history section needs to be filled via AJAX. Set the throbber going, and start the request.
			if ( (elem.id == 'reports_div_id') && (elem.innerHTML == '') )
				{
				elem.innerHTML = '<img src="##IMAGE_DIR##/ajax_throbber_linear_gray.gif" alt="Throbber" />';
				var uri = '##REPORTS_DISPLAY_URI##';
				SimpleAJAXCall(uri, FillReportsCallback, 'GET' );
				}
			
			elem.style.display = 'block';
			};
		};
};

/*******************************************************************/
/** \brief	This is the callback for the History section AJAX load.
*/
function FillReportsCallback (	in_text
								)
{
	var elem = document.getElementById ( 'reports_div_id' );
	elem.innerHTML = in_text;
};

/*******************************************************************/
/** \brief	This simply opens and closes the display of the new
	meeting form.
*/
function ToggleDescDiv( in_desc_id	/**< ID of the change */
						)
{
	if ( in_desc_id )
		{
		var	elem = document.getElementById ( 'changed_desc_'+in_desc_id+'_reports_div_container_div_id' );
		if ( elem )
			{
			if ( elem.className != 'changed_desc_reports_div_closed' )
				{
				elem.className = 'changed_desc_reports_div_closed';
				}
			else
				{
				elem.className = 'changed_desc_reports_div_open';
				};
			};
		
		elem = document.getElementById ( 'changed_desc_'+in_desc_id+'_reports_div_id' );
		
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
};

/*******************************************************************/
/** \brief	This is the call made to "undelete" a deleted meeting from
	the change record.
	
	Upon successful undeletion, the meeting is opened up for editing.
*/
function UnDeleteMeeting (	in_meeting_id,	/**< This is an integer, with the meeting ID. */
							in_change_id	/**< This is an integer, with the change ID. */
							)
{
	var uri = '##REVERT_URI##?meeting_id='+in_meeting_id+'&change_id='+in_change_id;
	SimpleAJAXCall(uri,UnDeleteMeetingCallback,'POST');
};

/*******************************************************************/
/** \brief		
*/
function UnDeleteMeetingCallback(in_text
								)
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
		var meeting_id = json_obj.meeting_id;
		var change_id = json_obj.change_id;
		var dt_id = 'del_desc_dt_change_'+change_id+'_'+meeting_id;
		var elem = document.getElementById ( dt_id );
		if ( elem )
			{
			var par = elem.parentNode;
			
			if ( par )
				{
				par.removeChild ( elem );
				DisplayMeetingDetails ( meeting_id, '##SINGLE_LOC##' );
				};
			};
		};
};

/*******************************************************************/
/** \brief		
*/
function VisitMeeting (in_meeting_id
						)
{
	DisplayMeetingDetails ( in_meeting_id, '##SINGLE_LOC##' );
};

/*******************************************************************/
/** \brief		
*/
function PermDeleteMeeting ( in_id, in_name, in_dt )
{
	if ( in_id )
		{
		var uri = '##PERM_DELETE_URI##?meeting_id='+in_id+'&meeting_name='+encodeURI(in_name)+'&dt_id='+encodeURI(in_dt);
		SimpleAJAXCall(uri, PermDeleteMeetingCallback, 'POST');
		};
};

/*******************************************************************/
/** \brief		
*/
function PermDeleteMeetingCallback ( in_text )
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
		var in_meeting_id = parseInt ( json_obj.id );
		var dt_id = json_obj.dt_id;
		var elem = document.getElementById ( dt_id );

		if ( elem )
			{
			var par = elem.parentNode;
			
			if ( par )
				{
				par.removeChild ( elem );
				alert ( json_obj.report );
				};
			};
		};
};
