/***********************************************************************/
/** 	\file	admin_formats.js

	\brief	This file will be optimized and embedded in the HTML that is
	returned for the formats edit form. It will control the JavaScript
	and AJAX used for the editor form.

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
/** \brief	This simply opens and closes the display of the format
	editor area
*/
function ToggleFormatsDiv()
{
	var	elem = document.getElementById('formats_container_div');
	
	if ( elem )
		{
		if ( elem.className !='formats_div_closed' )
			{
			elem.className = 'formats_div_closed';
			}
		else
			{
			elem.className = 'formats_div_open';
			};
		};
	
	elem = document.getElementById('edit_formats_div');
	
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
/** \brief	This is called to set the browser state of the "change"
	button for the given format to enabled, allowing the user to
	execute an AJAX call to set the changed data.
*/
function EnableFormatChangeButton(	in_format_id	///< An integer. The ID of the format.
									)
{
	var elem = document.getElementById('format_ch_button_'+in_format_id);
	
	if ( elem && elem.disabled )
		{
		elem.disabled = false;
		};
}

/*******************************************************************/
/** \brief	This is the opposite of the above. It blurs the control,
	so an enabled control is not selected.
*/
function DisableFormatChangeButton(	in_format_id	///< An integer. The ID of the format.
									)
{
	var elem = document.getElementById('format_ch_button_'+in_format_id);

	if ( elem && !elem.disabled )
		{
		elem.disabled = true;
		elem.blur();
		};
}

/*******************************************************************/
/** \brief	This gathers the current state of the format, and sends
	it to the server in an AJAX call.
*/
function ChangeFormat(	in_format_id,	///< An integer. The ID of the format.
						in_lang_enum_enum	///< A string. The enum of the format language.
						)
{
	var uri = '##CHANGE_URI_FORMAT##?shared_id='+in_format_id+'&lang='+in_lang_enum_enum;
		
	var elem = document.getElementById('format_type_'+in_format_id);
	uri += '&type='+escape(elem.value);
	
	var	format_key = null;
	var	format_name = null;

	elem = document.getElementById('format_key_'+in_format_id);
	
	if ( elem )
		{
		format_key = elem.value;
		};
	
	elem = document.getElementById('format_name_'+in_format_id);
	
	if ( elem )
		{
		format_name = elem.value;
		};
	
	if ( format_key && format_name )
		{
		uri += '&key='+escape(format_key);
		uri += '&name='+escape(format_name);
		
		elem = document.getElementById('format_description_'+in_format_id);
		uri += '&description='+escape(elem.value);
		SimpleAJAXCall(uri, ChangeFormatCallback, 'GET');
		}
	else
		{
		alert ( '##NO_BLANK##' );
		};

};

/*******************************************************************/
/** \brief	This is the AJAX callback for the format change. It
	disables the "Change" button upon successful completion.
	
	The input is a JSON object from the server. It may contain a
	failure report. If so, an alert is shown with the report, and
	the button is not disabled.
*/
function ChangeFormatCallback(in_text	///< A JSON object from the server.
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
		DisableFormatChangeButton ( json_obj.id );
		};
};

/*******************************************************************/
/** \brief	This deletes the given format in an AJAX call.
*/
function DeleteFormat(	in_format_id,		///< An integer. The ID of the format.
						in_lang_enum_enum,	///< A string. The enum of the format language.
						script_uri			///< The URI of the script for a refresh (if a new format).
						)
{
	var uri = '##DELETE_URI_FORMAT##?shared_id='+in_format_id+'&lang='+in_lang_enum_enum;
	
	if ( confirm ( '##DELETE_CONFIRM_FORMAT##' ) )
		{
		SimpleAJAXCall(uri,DeleteFormatCallback,'GET',script_uri);
		};
};

/*******************************************************************/
/** \brief	This is the AJAX callback for the format delete.
	
	The input is a JSON object from the server. It may contain a
	failure report. If so, an alert is shown with the report.
	If successful, the line is removed from the form, and a report
	alert is displayed.
*/
function DeleteFormatCallback(in_text,	///< A JSON object from the server.
							script_uri	///< The URI of the script for a refresh (if a new format).
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
		var lang_popup = document.getElementById ( 'edit_formats_lang_enum' );
		var sort_popup = document.getElementById ( 'comdef_format_sort_select' );
		script_uri +='open_formats&lang_enum='+lang_popup.value+'&comdef_format_sort_select='+sort_popup.value;
		window.location.href=script_uri;
		};
};

/*******************************************************************/
/** \brief	This copies a format from one language to another.
	If the original format is blank, the copy is not made.
*/
function CopyFormat(	in_format_id,		///< An integer. The ID of the format.
						in_lang_enum_enum,	///< A string. The enum of the source format language.
						in_new_lang_enum,	///< A string. The enum of the new format language.
						script_uri			///< The URI of the script for a refresh (if a new format).
					)
{
	var uri = '##CHANGE_URI_FORMAT##?shared_id='+in_format_id;
	if ( in_lang_enum_enum ) uri += '&lang='+in_lang_enum_enum;
	uri += '&new_lang='+in_new_lang_enum;
	
	var elem = document.getElementById('format_type_'+in_format_id);
	uri += '&type='+escape(elem.value);
	
	var	format_key = null;
	var	format_name = null;

	elem = document.getElementById('format_key_'+in_format_id);
	
	if ( elem )
		{
		format_key = elem.value;
		};
	
	elem = document.getElementById('format_name_'+in_format_id);
	
	if ( elem )
		{
		format_name = elem.value;
		};
	
	if ( format_key && format_name )
		{
		uri += '&key='+escape(format_key);
		uri += '&name='+escape(format_name);
		
		elem = document.getElementById('format_description_'+in_format_id);
		uri += '&description='+escape(elem.value);
		SimpleAJAXCall(uri,CopyFormatCallback,'GET',script_uri);
		}
	else
		{
		alert ( '##NO_BLANK##' );
		};
};

/*******************************************************************/
/** \brief	This is the AJAX callback for the copy. If the copy is
	successful, the fieldset of the old format is replaced with a
	new fieldset in the new language.
*/
function CopyFormatCallback(in_text,	///< A JSON object from the server.
							script_uri	///< The URI of the script for a refresh (if a new format).
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
		if ( json_obj.new_format && script_uri )
			{
			var lang_popup = document.getElementById ( 'edit_formats_lang_enum' );
			var sort_popup = document.getElementById ( 'comdef_format_sort_select' );
			script_uri +='open_formats&lang_enum='+lang_popup.value+'&comdef_format_sort_select='+sort_popup.value;
			window.location.href=script_uri;
			}
		else
			{
			var container = document.getElementById('edit_one_format_fieldset_'+json_obj.id);
			container.className = 'edit_one_format_fieldset native_lang_format_fieldset';
			
			var copy_button = document.getElementById('format_copy_button_'+json_obj.id);
			var button_div = copy_button.parentNode;
			
			button_div.removeChild ( copy_button );
			
			var legend = document.getElementById ( 'edit_one_format_legend_'+json_obj.id );
			legend.innerHTML = json_obj.id;
			
			var oc_but_html = '<input type="button" id="format_del_button_'+json_obj.id+'" class="format_delete_button" value="##DEL_BUTTON##" onclick="DeleteFormat('+json_obj.id+',\''+json_obj.lang+'\')" />';
			var new_delete_button_div = document.createElement ( "div" );
			if ( new_delete_button_div )
				{
				new_delete_button_div.className = 'edit_format_button_div';
				new_delete_button_div.innerHTML = oc_but_html;
				button_div.appendChild ( new_delete_button_div );
				
				var occ_but_html = '<input type="button" disabled="disabled" id="format_ch_button_'+json_obj.id+'" class="format_change_button" value="##CH_BUTTON##" onclick="ChangeFormat('+json_obj.id+',\''+json_obj.lang+'\')" />';
				var new_ch_button_div = document.createElement ( "div" );
				if ( new_ch_button_div )
					{
					new_ch_button_div.className = 'edit_format_button_div';
					new_ch_button_div.innerHTML = occ_but_html;
					button_div.appendChild ( new_ch_button_div );
					
					CreateNewFieldset ( parseInt ( json_obj.id ) + 1, json_obj.lang );
					};
				};
			};
		};
};

/*******************************************************************/
/** \brief	This creates a new format editor fieldset dynamically.
*/
function CreateNewFieldset (in_format_id,		///< An integer. The ID of the format.
							in_lang_enum_enum	///< A string. The enum of the format language.
							)
{
	var container = document.getElementById ( 'c_comdef_edit_format_format_list_div' );
	
	if ( container )
		{
		var new_fieldset = document.createElement ( "fieldset" );
		if ( new_fieldset )
			{
			new_fieldset.id = 'edit_one_format_fieldset_'+in_format_id;
			new_fieldset.className = 'edit_one_format_fieldset new_format_fieldset';
			
			var new_legend = document.createElement ( "legend" );
			if ( new_legend )
				{
				new_legend.id = 'edit_one_format_legend_'+in_format_id;
				new_legend.innerHTML = in_format_id+' (##NEW_FORMAT##)';
				new_fieldset.appendChild ( new_legend );
				
				var new_fields_div = document.createElement ( "div" );
				
				if ( new_fields_div )
					{
					new_fields_div.className = 'edit_format_fields_div';
					
					var new_type_container_div = document.createElement ( "div" );
					
					if ( new_type_container_div )
						{
						new_type_container_div.className = 'edit_format_value_div';
						new_type_container_div.id = 'edit_format_value_div_format_'+in_format_id;
						
						var orig_label = document.getElementById ( 'format_type_label_'+(in_format_id-1) );
						
						if ( orig_label )
							{
							var new_label = orig_label.cloneNode ( true );
							
							if ( new_label )
								{
								new_label.id = 'format_type_label_'+in_format_id;
								new_label.htmlFor = 'format_type_'+in_format_id;
									
								new_type_container_div.appendChild ( new_label );
								
								var orig_selector = document.getElementById ( 'format_type_'+(in_format_id-1) );
								
								if ( orig_selector )
									{
									var new_type_selector = orig_selector.cloneNode(true);
									if ( new_type_selector )
										{
										new_type_selector.selectedIndex = 0;
										new_type_selector.onChange = null;
										new_type_selector.id = 'format_type_'+in_format_id;
										};
									
									new_type_container_div.appendChild ( new_type_selector );
									};
								};
							};
						
						new_fields_div.appendChild ( new_type_container_div );
						};
					
					var new_container_div = document.createElement ( "div" );
					
					if ( new_container_div )
						{
						new_container_div.className = 'edit_format_value_div';
						new_container_div.id = 'edit_format_value_div_key_'+in_format_id;
						
						var orig_label = document.getElementById ( 'format_key_label_'+(in_format_id-1) );
						
						if ( orig_label )
							{
							var new_label = orig_label.cloneNode ( true );
							
							if ( new_label )
								{
								new_label.id = 'format_key_label_'+in_format_id;
								new_label.htmlFor = 'format_key_'+in_format_id;
									
								new_container_div.appendChild ( new_label );
								};
							};
					
						var new_text = document.createElement ( "input" );
						
						if ( new_text )
							{
							new_text.type = 'text';
							new_text.className = 'edit_one_format_key';
							new_text.size = 5;
							new_text.id = 'format_key_'+in_format_id;
							new_text.name = 'format_key_'+in_format_id;

							new_container_div.appendChild ( new_text );
							};
						
						new_fields_div.appendChild ( new_container_div );
						};
					
					var new_container_div = document.createElement ( "div" );
					
					if ( new_container_div )
						{
						new_container_div.className = 'edit_format_value_div';
						new_container_div.id = 'edit_format_value_div_name_'+in_format_id;
						
						var orig_label = document.getElementById ( 'format_name_label_'+(in_format_id-1) );
						
						if ( orig_label )
							{
							var new_label = orig_label.cloneNode ( true );
							
							if ( new_label )
								{
								new_label.id = 'format_name_label_'+in_format_id;
								new_label.htmlFor = 'format_name_'+in_format_id;
									
								new_container_div.appendChild ( new_label );
								};
							};
					
						var new_text = document.createElement ( "input" );
						
						if ( new_text )
							{
							new_text.type = 'text';
							new_text.className = 'edit_one_format_name';
							new_text.size = 32;
							new_text.id = 'format_name_'+in_format_id;
							new_text.name = 'format_name_'+in_format_id;

							new_container_div.appendChild ( new_text );
							};
						
						new_fields_div.appendChild ( new_container_div );
						};
					
					var new_container_div = document.createElement ( "div" );
					
					if ( new_container_div )
						{
						new_container_div.className = 'edit_format_value_div';
						new_container_div.id = 'edit_format_value_div_description_'+in_format_id;
						
						var orig_label = document.getElementById ( 'format_description_label_'+(in_format_id-1) );
						
						if ( orig_label )
							{
							var new_label = orig_label.cloneNode ( true );
							
							if ( new_label )
								{
								new_label.id = 'format_description_label_'+in_format_id;
								new_label.htmlFor = 'format_description_'+in_format_id;
									
								new_container_div.appendChild ( new_label );
								};
							};
					
						var new_text = document.createElement ( "textarea" );
						
						if ( new_text )
							{
							new_text.className = 'edit_one_format_description_textarea';
							new_text.cols = 64;
							new_text.rows = 2;
							new_text.id = 'format_description_'+in_format_id;
							new_text.name = 'format_description_'+in_format_id;

							new_container_div.appendChild ( new_text );
							};
						
						new_fields_div.appendChild ( new_container_div );
						};
					
					new_fieldset.appendChild ( new_fields_div );
					};
					
				var new_buttons_div = document.createElement ( "div" );
				
				if ( new_buttons_div )
					{
					new_buttons_div.className = 'edit_format_buttons_div';
					
					var new_add_button_div = document.createElement ( "div" );
					if ( new_add_button_div )
						{
						new_add_button_div.className = 'edit_format_button_div';
						var occ_but_html = '<input type="button" id="format_copy_button_'+in_format_id+'" class="format_copy_button" value="##ADD_BUTTON##" onclick="CopyFormat('+in_format_id+',null,\''+in_lang_enum+'\')" />';
						new_add_button_div.innerHTML = occ_but_html;
						new_buttons_div.appendChild ( new_add_button_div );
						};
					
					new_fieldset.appendChild ( new_buttons_div );
					};
				};
			
			container.appendChild ( new_fieldset );
			};
		};
};

/*******************************************************************/
/** \brief	This sorts the formats, and redisplays them.
*/
function ReSortFormats ( script_uri )
{
	var lang_popup = document.getElementById ( 'edit_formats_lang_enum' );
	var sort_popup = document.getElementById ( 'comdef_format_sort_select' );
	if ( lang_popup && sort_popup )
		{
		window.location.href=script_uri+'open_formats&lang_enum='+lang_popup.value+'&comdef_format_sort_select='+sort_popup.value;
		};
};
