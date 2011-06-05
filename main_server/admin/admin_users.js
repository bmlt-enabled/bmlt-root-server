/***********************************************************************/
/** 	\file	admin_users.js

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
/** \brief	This simply opens and closes the display of the user editor form.
*/
function ToggleUserEditDiv()
{
	var	elem = document.getElementById('edit_user_container_div');
	
	if ( elem )
		{
		if ( elem.className !='edit_user_div_closed' )
			{
			elem.className = 'edit_user_div_closed';
			}
		else
			{
			elem.className = 'edit_user_div_open';
			};
		};
	
	elem = document.getElementById('user_editor_list_div');
	
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
/** \brief	This simply opens and closes the display of one individual
	user.
*/
function ToggleOneUserEditDiv(	in_user_id	///< The numerical ID of the user.
								)
{
	var	elem = document.getElementById('edit_one_user_div_'+in_user_id);
	
	if ( elem )
		{
		if ( elem.className !='edit_one_user_div_closed' )
			{
			elem.className = 'edit_one_user_div_closed';
			}
		else
			{
			elem.className = 'edit_one_user_div_open';
			};
		};
	
	elem = document.getElementById('user_edit_'+in_user_id+'_fieldset');
	
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
/** \brief	This simply opens and closes the display of the user
	editor form.
*/
function EnableUserChangeButton(	in_user_id,	///< The ID of the User.
									in_disable	///< If true, disable the button (Default is false).
								)
{
	if ( typeof ( in_disable ) == 'undefined' )
		{
		in_disable = false;
		};
	
	var elem = document.getElementById('user_'+in_user_id+'_submit');
	elem.disabled = (in_disable == false) ? false : true;
}

/*******************************************************************/
/** \brief	Submits an AJAX call to change the User.
*/
function SubmitUser (	in_user_id,		///< The ID of the user.
						in_script_uri	///< Used to refresh for new Users
					)
{
	var args = new Array;
	var c = 0;

	// Prepare the argument list for the AJAX call.
	
	// This is the user login ID.
	field = document.getElementById ( 'user_login_string_'+in_user_id );
	
	if ( field )
		{
		args[c++] = 'login_string='+encodeURIComponent(field.value);
		};
	
	// This is a new user password (Cannot be empty).
	field = document.getElementById ( 'user_password_string_'+in_user_id );
	
	if ( in_user_id > 0 )
		{
		if ( field && field.value )
			{
			if ( field.value.length >= ##MIN_PW_LEN## )
				{
				args[c++] = 'password_string='+encodeURIComponent(field.value);
				field.value = null;
				}
			else
				{
				alert ( '##PW_TOO_SHORT##' );
				field.select();
				return;
				};
			};
		}
	else
		{
		if ( field.value.length >= ##MIN_PW_LEN## )
			{
			args[c++] = 'password_string='+encodeURIComponent(field.value);
			field.value = null;
			}
		else
			{
			alert ( '##PW_TOO_SHORT##' );
			field.select();
			return;
			};
		};
	
	// This is the user name.
	field = document.getElementById ( 'user_name_string_'+in_user_id );
	
	if ( field )
		{
		args[c++] = 'name_string='+encodeURIComponent(field.value);
		};
	
	// This is the user description.
	field = document.getElementById ( 'user_description_string_'+in_user_id );
	
	if ( field )
		{
		args[c++] = 'description_string='+encodeURIComponent(field.value);
		};
	
	// This is the user email.
	field = document.getElementById ( 'user_email_string_'+in_user_id );
	
	if ( field )
		{
		args[c++] = 'email_address_string='+encodeURIComponent(field.value);
		};
	
	// This is the user language.
	field = document.getElementById ( 'user_lang_'+in_user_id );
	
	if ( field )
		{
		args[c++] = 'lang_enum='+encodeURIComponent(field.value);
		};
	
	// This is the user level.
	field = document.getElementById ( 'user_level_'+in_user_id );
	
	if ( field )
		{
		args[c++] = 'user_level_tinyint='+encodeURIComponent(field.value);
		};
	
	var throbber_img = document.getElementById ( 'user_'+in_user_id+'_submit_throbber' );
	var button_input = document.getElementById ( 'user_'+in_user_id+'_submit' );

	if ( throbber_img && button_input )
		{
		throbber_img.src = '##IMAGE_DIR##'+((in_user_id>0) ? '/ajax_throbber_linear.gif' : '/ajax_throbber_linear_pink.gif');	
		button_input.style.display = 'none';
		throbber_img.style.display = 'inline';
		var uri = '##CHANGE_URI_USER##?original_id='+in_user_id+'&'+args.join('&');
		SimpleAJAXCall(uri,SubmitUserCallback,'POST', in_script_uri);
		};
};

/*******************************************************************/
/** \brief	This is the AJAX callback from setting the User.
	The processor will return a <a href="http://json.org">JSON</a> object,
	and this function will react accordingly.
*/
function SubmitUserCallback( in_text,		///< The text response from the call.
							in_script_uri	///< Used to refresh for new Users
							)
{
    in_text = in_text.replace(/[\r\n]*/g, "");  // 'Orrible kludge to account for servers being naughty.
	eval ( "var json_obj = "+in_text+";" );
	done_failed = (null != json_obj.error);
	
	if ( done_failed )
		{
		var throbber_img = document.getElementById ( 'user_'+json_obj.id+'_submit_throbber' );
		var button_input = document.getElementById ( 'user_'+json_obj.id+'_submit' );
					
		alert ( json_obj.report );
		if ( json_obj.type == 'dup_login' )
			{
			if ( typeof (json_obj.orig_login) != 'undefined' )
				{
				var field = document.getElementById ( 'user_login_string_'+json_obj.id );
				if ( field )
					{
					field.value = json_obj.orig_login;
					field.select();
					
					if ( throbber_img && button_input )
						{
						throbber_img.src = '';	
						button_input.style.display = 'inline';
						throbber_img.style.display = 'none';
						EnableUserChangeButton ( json_obj.id, true );
						};
					};
				};
			}
		else
			{
			if ( json_obj.type == 'email_format_bad' )
				{
				var field = document.getElementById ( 'user_email_string_'+json_obj.id );
				if ( field )
					{
					field.focus();
					if ( throbber_img && button_input )
						{
						throbber_img.src = '';	
						button_input.style.display = 'inline';
						throbber_img.style.display = 'none';
						EnableUserChangeButton ( json_obj.id, true );
						};
					};
				};
			};
		}
	else
		{
		if ( json_obj.new_user && in_script_uri )
			{
			window.location.href=in_script_uri;
			}
		else
			{
			var throbber_img = document.getElementById ( 'user_'+json_obj.id+'_submit_throbber' );
			var button_input = document.getElementById ( 'user_'+json_obj.id+'_submit' );
			var	a_rec = document.getElementById ( 'one_user_editor_'+json_obj.id+'_a' );
			var	l_rec = document.getElementById ( 'one_user_editor_'+json_obj.id+'_legend' );
			if ( a_rec && l_rec && throbber_img && button_input )
				{
				if ( json_obj.super_user )
					{
					a_rec.innerHTML = json_obj.name;
					};
				l_rec.innerHTML = '('+json_obj.id+') '+json_obj.name;
				throbber_img.src = '';	
				button_input.style.display = 'inline';
				throbber_img.style.display = 'none';
				EnableUserChangeButton ( json_obj.id, true );
				};
			};
		};
};

/*******************************************************************/
/** \brief	Submits an AJAX call to delete the User.
*/
function DeleteUser ( in_user_id	///< The ID of the user.
					)
{
	if ( confirm ( '##DELETE_CONFIRM_USER##' ) )
		{
		var uri = '##DELETE_URI_USER##?user_id='+in_user_id;
	
		SimpleAJAXCall(uri,DeleteUserCallback,'POST', null);
		};
};

/*******************************************************************/
/** \brief	Submits an AJAX call to delete the User.
*/
function DeleteUserCallback ( in_text	///< The text response from the call.
					)
{
    in_text = in_text.replace(/[\r\n]*/g, "");  // 'Orrible kludge to account for servers being naughty.
	eval ( "var json_obj = "+in_text+";" );
	done_failed = (null != json_obj.error);
	
	if ( done_failed )
		{
		alert ( json_obj.report );
		}
	else
		{
		var	user = document.getElementById ( 'edit_one_user_div_'+json_obj.id );
		
		if ( user )
			{
			alert ( json_obj.message );
			user.parentNode.removeChild ( user );
			};
		};
};