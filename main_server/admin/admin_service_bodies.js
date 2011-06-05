/***********************************************************************/
/** 	\file	admin_service_bodies.js

	\brief	This file will be optimized and embedded in the HTML that is
	returned for the service body editor form. It will control the JavaScript
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
/** \brief	This simply opens and closes the display of the service
	body editor form.
*/
function ToggleNewServiceDiv()
{
	var	elem = document.getElementById('edit_service_container_div');
	
	if ( elem )
		{
		if ( elem.className !='edit_service_div_closed' )
			{
			elem.className = 'edit_service_div_closed';
			}
		else
			{
			elem.className = 'edit_service_div_open';
			};
		};
	
	elem = document.getElementById('service_body_editor_list_div');
	
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
/** \brief	This simply opens and closes the display of one single
	service body editor form.
*/
function ToggleOneSBEditDiv( in_sb_id	///< The numerical ID of the service body.
							)
{
	var	elem = document.getElementById('edit_one_div_sb_'+in_sb_id+'_a');
	
	if ( elem )
		{
		if ( elem.className !='edit_one_sb_a_closed' )
			{
			elem.className = 'edit_one_sb_a_closed';
			}
		else
			{
			elem.className = 'edit_one_sb_a_open';
			};
		};
	
	elem = document.getElementById('sb_'+in_sb_id+'_servicebodyeditor');
	
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
/** \brief	This simply opens and closes the display of the service
	body editor form.
*/
function EnableSBChangeButton(	in_sb_id,	///< The ID of the Service Body ("sb_XX").
								in_disable	///< If true, disable the button (Default is false).
							)
{
	if ( typeof ( in_disable ) == 'undefined' )
		{
		in_disable = false;
		};
	
	var elem = document.getElementById(in_sb_id+'_submit');
	elem.disabled = (in_disable == false) ? false : true;
}

/*******************************************************************/
/** \brief	Combs through the Service Body Editors checkboxes, and
	\returns an array for each one that is checked.
*/
function GetEditorArray (	in_sb_id,	///< The ID of the Service Body ("sb_XX").
							in_array	///< An array
							)
{
	var	ret = new Array();
	var	c = 0;
	
	// We parse out the checkboxes by first isolating their container.
	var	wrapper = document.getElementById ( in_sb_id+'_checkboxes' );
	
	if ( wrapper && wrapper.hasChildNodes() )
		{
		var n = wrapper.firstChild;
		
		do
			{
			// We are only interested in the div containing the checkbox.
			if ( (n.className == "sb_user_check_div") && n.hasChildNodes() )
				{
				var editor_cb_wrapper = n.firstChild;
				if ( editor_cb_wrapper )
					{
					var editor_cb = editor_cb_wrapper.firstChild;
					if ( editor_cb && editor_cb.checked && (editor_cb.value > 0) )
						{
						ret[c++] = editor_cb.value;
						};
					};
				};
			n = n.nextSibling;
			} while ( n );
		};
		
	return ret;
}

/*******************************************************************/
/** \brief	Submits an AJAX call to change the Service Body.
*/
function SubmitServiceBody(	in_sb_id,		///< The ID of the Service Body ("sb_XX").
							in_script_uri	///< Used to refresh for new Service Bodies
							)
{
	var in_button_id = in_sb_id+'_submit';
	var args = new Array;
	var c = 0;

	// Prepare the argument list for the AJAX call.
	var field = document.getElementById ( in_sb_id+'_original_id' );

	if ( field )
		{
		args[c++] = 'original_id='+parseInt(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_worldid_mixed' );
	
	if ( field )
		{
		args[c++] = 'worldid_mixed='+encodeURIComponent(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_name_string' );
	
	if ( field )
		{
		args[c++] = 'name_string='+encodeURIComponent(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_description_string' );
	
	if ( field )
		{
		args[c++] = 'description_string='+encodeURIComponent(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_lang_enum' );
	
	if ( field )
		{
		args[c++] = 'lang_enum='+encodeURIComponent(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_parent_bigint' );
	
	if ( field )
		{
		args[c++] = 'parent_bigint='+parseInt(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_parent_2_bigint' );
	
	if ( field )
		{
		args[c++] = 'parent_2_bigint='+parseInt(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_type' );
	
	if ( field )
		{
		args[c++] = 'type='+encodeURIComponent(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_principal_user_bigint' );
	
	if ( field )
		{
		args[c++] = 'principal_user_bigint='+parseInt(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_uri_string' );
	
	if ( field )
		{
		args[c++] = 'uri_string='+encodeURIComponent(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_kml_uri_string' );
	
	if ( field )
		{
		args[c++] = 'kml_uri_string='+encodeURIComponent(field.value);
		};
	
	field = document.getElementById ( in_sb_id+'_sb_meeting_email' );
	
	if ( field )
		{
		args[c++] = 'sb_meeting_email='+encodeURIComponent(field.value);
		};
	
	// See if we have any additional editors defined.
	var editors = GetEditorArray ( in_sb_id );

	args[c++] = 'editors_string='+editors.join('%2C');
	
	var throbber_img = document.getElementById ( in_button_id+'_throbber' );
	var button_input = document.getElementById ( in_button_id );
	if ( throbber_img && button_input )
		{
		throbber_img.src = '##IMAGE_DIR##'+((in_sb_id!='sb_0') ? '/ajax_throbber_linear.gif' : '/ajax_throbber_linear_pink.gif');	
		button_input.style.display = 'none';
		throbber_img.style.display = 'inline';
		var uri = '##CHANGE_URI_SB##?'+args.join('&');
		SimpleAJAXCall(uri,SubmitServiceBodyCallback,'POST', new Array(in_button_id,in_script_uri,in_sb_id));
		};
};

/*******************************************************************/
/** \brief	This is the AJAX callback from setting the Service Body.
	The processor will return a <a href="http://json.org">JSON</a> object,
	and this function will react accordingly.
*/
function SubmitServiceBodyCallback( in_text,	///< The text response from the call.
									in_array	///< The DOM OD of the submit button, and the script for a refresh.
									)
{
	var in_button_id = in_array[0];
	var in_script = in_array[1];
	var	in_sb_id = in_array[2];

	var throbber_img = document.getElementById ( in_button_id+'_throbber' );
	var button_input = document.getElementById ( in_button_id );

    in_text = in_text.replace(/[\r\n]*/g, "");  // 'Orrible kludge to account for servers being naughty.
	eval ( "var json_obj = "+in_text+";" );
	done_failed = (null != json_obj.error);
	
	if ( done_failed )
		{
		alert ( json_obj.report+' ('+json_obj.info+')' );
		if ( json_obj.type == 'email_format_bad' )
			{
			var elem = document.getElementById ( in_sb_id+'_sb_meeting_email' );

			if ( elem )
				{
				elem.focus();
				};

			if ( throbber_img && button_input )
				{
				throbber_img.src = '';	
				throbber_img.style.display = 'none';
				button_input.style.display = 'inline';
				};
			};
		}
	else
		{
		if ( throbber_img && button_input )
			{
			throbber_img.src = '';
			button_input.style.display = 'inline';
			throbber_img.style.display = 'none';
			if ( json_obj.new_sb && in_script )
				{
				window.location.href=in_script;
				}
			else
				{
				var name = json_obj.sb_name;
				var id = parseInt(json_obj.id);
				var a_id = 'edit_one_div_sb_'+id+'_a';
				var legend_id = 'sb_'+id+'_legend';

				var a_rec = document.getElementById ( a_id );
				var l_rec = document.getElementById ( legend_id );
				
				if ( a_rec && l_rec )
					{
					a_rec.innerHTML = name;
					l_rec.innerHTML = '('+id+') '+name;
					};
				
				EnableSBChangeButton ( 'sb_'+json_obj.id, true );
				};
			};
		};
};

/*******************************************************************/
/** \brief	Hides and/or shows any checkboxes for available admins.
*/
function ServiceBodyAdminChanged (  in_sb_id,       ///< The numeric ID of the Service Body.
                                    in_sb_user_id   ///< The ID of the selected admin.
							        )
{
    var current_node = document.getElementById(in_sb_id+'_editor_'+in_sb_user_id);  // The current node (will be disabled).
    var root_node = document.getElementById(in_sb_id+'_checkboxes');

    // Before we hide the current node, we make sure all the others are enabled.
    var next_node = root_node.firstChild;
    while ( next_node )
        {
        inner_node = next_node.firstChild.firstChild;
        if ( inner_node == current_node )
            {
            current_node.old_checked = current_node.checked;
            current_node.checked = false;
            current_node.disabled = true;
            }
        else
            {
            inner_node.disabled = false;
            if ( inner_node.old_checked )
                {
                inner_node.checked = inner_node.old_checked;
                }
            else
                {
                inner_node.checked = false;
                };
            };
        
        next_node = next_node.nextSibling;
        };
}

/*******************************************************************/
/** \brief	Submits an AJAX call to delete the Service Body.
*/
function DeleteServiceBody ( in_sb_id,		///< The numeric ID of the Service Body.
							in_script_uri	///< Used to refresh for deleted Service Bodies
							)
{
	if ( confirm ( '##SB_DELETE_CONFIRM##' ) )
		{
		var uri = '##DELETE_URI_SB##?sb_id='+in_sb_id;
		SimpleAJAXCall(uri,DeleteServiceBodyCallback,'POST', 'sb_'+in_sb_id+'_servicebodyeditor');
		};
};

/*******************************************************************/
/** \brief	This is the AJAX callback from deleting the Service Body.
	The processor will return a <a href="http://json.org">JSON</a> object,
	and this function will react accordingly.
*/
function DeleteServiceBodyCallback( in_text,	///< The text response from the call.
									in_sb_id	///< The ID of the Service Body Wrapper
									)
{
    in_text = in_text.replace(/[\r\n]*/g, "");  // 'Orrible kludge to account for servers being naughty.
	eval ( "var json_obj = "+in_text+";" );
	done_failed = (null != json_obj.error);
	
	if ( done_failed )
		{
		alert ( json_obj.report+' ('+json_obj.info+')' );
		}
	else
		{
		var	sb = document.getElementById ( in_sb_id );
		
		if ( sb )
			{
			alert ( '##SB_DELETE_MESSAGE##' );
			window.location.reload();
			};
		};
};
