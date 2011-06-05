/***********************************************************************/
/** 	\file	admin_meeting.js

	\brief	This file will be optimized and embedded in the HTML that is
	returned for a single meeting edit form. It will control the JavaScript
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

var	g_args = null;
var g_start_time = null;
var g_duration_time = null;
var g_formats = null;
var g_dirty = false;

/*******************************************************************/
/** \class	c_geocoder_browser_edit_meeting

	\brief	This is a special JavaScript Class that manages a Google Map.
*/
var g_geocoder_browser_edit_meeting = null;	/**< There is only one single instance of this map.*/

/** These are the various class data members. */
c_geocoder_browser_edit_meeting.prototype.point = null;			/**< The current GLatLng for the map marker */
c_geocoder_browser_edit_meeting.prototype.map = null;			/**< The Google Maps instance */
c_geocoder_browser_edit_meeting.prototype.marker = null;		/**< The marker instance */
c_geocoder_browser_edit_meeting.prototype.insert = null;
c_geocoder_browser_edit_meeting.prototype.display_id = null;
c_geocoder_browser_edit_meeting.prototype.point_only = null;

/*******************************************************************/
/** \brief	Constructor. Sets up the map and the various DOM elements.
*/
function c_geocoder_browser_edit_meeting ( in_display_id, in_lat, in_lng, in_point_only )
{
	/* Avoid memory leaks and bruised feelings. */
	if ( g_geocoder_browser_edit_meeting ) g_geocoder_browser_edit_meeting = null;
	
	/* We set the SINGLETON to us. */
	g_geocoder_browser_edit_meeting = this;

	if ( GBrowserIsCompatible() )
		{
		/* This should never happen. */
		if ( !in_lat ) in_lat = 40.83;
		if ( !in_lng ) in_lng = -72.9;
		
		this.display_id = in_display_id;
		g_geocoder_browser_edit_meeting.point = new GLatLng ( in_lat, in_lng );
	
		g_geocoder_browser_edit_meeting.map = new GMap2(document.getElementById("meeting_map"), {draggableCursor: "crosshair"} );
		if ( g_geocoder_browser_edit_meeting.map )
			{
			g_geocoder_browser_edit_meeting.map.addControl(new GLargeMapControl());
			g_geocoder_browser_edit_meeting.map.addControl(new GMapTypeControl());
			g_geocoder_browser_edit_meeting.map.setCenter(g_geocoder_browser_edit_meeting.point, 10);
			g_geocoder_browser_edit_meeting.map.enableGoogleBar ();

			g_geocoder_browser_edit_meeting.marker = new GMarker(g_geocoder_browser_edit_meeting.point, {draggable: true, title: "Drag to a New Location.", icon:gicon});
			GEvent.addListener(g_geocoder_browser_edit_meeting.marker, "dragend", g_geocoder_browser_edit_meeting.Dragend );
			GEvent.addListener(g_geocoder_browser_edit_meeting.map, "click", g_geocoder_browser_edit_meeting.MapClickCallback );
			g_geocoder_browser_edit_meeting.map.addOverlay(g_geocoder_browser_edit_meeting.marker);
			g_geocoder_browser_edit_meeting.point_only = in_point_only;
			g_geocoder_browser_edit_meeting.Dragend ();
			};
		};
};

/*******************************************************************/
/** \brief	
*/
function MarkerSetCallbackGM ( in_result	/**< A search result object array. */
							)
{
	if ( in_result[0].marker )
		{
		g_geocoder_browser_edit_meeting.map.checkResize();
		in_result[0].marker.hide();
		};
};

/*******************************************************************/
/** \brief	
*/
c_geocoder_browser_edit_meeting.prototype.Dragend = function (  )
{
	var id = g_geocoder_browser_edit_meeting.display_id;
	
	g_geocoder_browser_edit_meeting.point = g_geocoder_browser_edit_meeting.marker.getLatLng();
	if ( document.getElementById(id+'_longitude') != null )
		{
		var	oldlongval = document.getElementById(id+'_longitude').value;
		var	oldlatval = document.getElementById(id+'_latitude').value;
		var	longval = g_geocoder_browser_edit_meeting.point.lng();
		var	latval = g_geocoder_browser_edit_meeting.point.lat();
		document.getElementById(id+'_longitude').value = longval;
		document.getElementById(id+'_latitude').value = latval;
		var new_long = longval != oldlongval;
		var new_lat = latval != oldlatval;
		if (new_long) EnableMeetingChangeButton(id, false);
		if (new_lat) EnableMeetingChangeButton(id), false;
		};

	if ( document.getElementById(id+'_published') != null )
		{
		document.getElementById(id+'_published').disabled = false;
		};
};

/*******************************************************************/
/** \brief	Clicking in the map simulates a very fast drag.
*/
c_geocoder_browser_edit_meeting.prototype.MapClickCallback = function ( in_overlay, in_point )
{
	g_geocoder_browser_edit_meeting.marker.setLatLng (in_point );
	g_geocoder_browser_edit_meeting.Dragend();
};

/*******************************************************************/
/** \brief	When the "Find Marker" button is clicked, this function is called. It merely re-centers the map.
*/
c_geocoder_browser_edit_meeting.prototype.FindMarker = function ( )
{
	g_geocoder_browser_edit_meeting.map.panTo ( g_geocoder_browser_edit_meeting.point );
};

/*******************************************************************/
/** \brief	Simply hides the meeting editor.
*/
function CloseMeetingEditor( )
{
	var elem = document.getElementById ( 'c_comdef_search_results_edit_hidden_div' );
	
	if ( elem && (elem.style.display != 'none') )
		{
		if ( g_dirty )
			{
			if ( !confirm ( '##DIRTY_CONFIRM##' ) )
				{
				return;
				};
			};
		g_dirty = false;
		CloseMap();
		elem.innerHTML = '';
		elem.style.display = 'none';
		var new_button_input = document.getElementById ( 'new_meeting_button' );
		
		if ( new_button_input )
			{
			new_button_input.disabled = false;
			};
		};
};

/*******************************************************************/
/** \brief	This sorts through the editor, and extracts values to be
	sent down via the AJAX call. It recursively explores the DOM
	tree from the given node, and loads up the global g_args parameter.
*/
function GetRelevantMeetingNodes(	id,		/**< The meeting ID. */
									node,	/**< The root node for the crawl. */
									prefix	/**< A prefix for the element IDs that needs to be stripped. */
								)
{
	if ( !node )
		{
		node = document;
		if ( !prefix )
			{
			prefix = 'meeting';
			};
		id_int = parseInt(id.replace ( prefix+'_', '' ));
		g_args = new Array();
		if ( id_int > 0 )
			{
			g_args[0]='original_id='+id_int;
			};
		g_start_time = new Array();
		g_duration_time = new Array();
		g_formats = new Array();
		to_b_hidden = new Array();
		if ( (null != document.getElementById(id+'_new_fieldset')) && document.getElementById(id+'_new_fieldset').style.display.toString().match ('block') )
			{
			g_args[g_args.length] = document.getElementById(id+'_new_key').options[document.getElementById(id+'_new_key').selectedIndex].value+"="+encodeURIComponent(document.getElementById(id+'_new_textarea').value.toString());
			};
		};
	
	if ( node )
		{
		var temp = id+"_";
		if ( node.id && node.id.toString().match(temp) && node.type )
			{
			var nodename = node.id.toString().replace(temp,'');
			var nodeval = node.value ? node.value.toString() : '';
			/* Trim the values. */
			if ( nodeval )
			    {
			    nodeval = nodeval.replace(/^\s+/,'');
			    nodeval = nodeval.replace(/\s+$/,'');
			    };
			
			if ( (nodename != 'submit') && (nodename != 'submit_data_item') && (nodename != 'delete') && (nodename != 'new_key') && (nodename != 'new_textarea') )
				{
				if ( nodename.match ( '_deleted_input' ) )
					{
					if ( nodeval )
						{
						g_args[g_args.length] = nodename+"=1";
						};
					}
				else if ( nodename == 'published' )
					{
					g_args[g_args.length] = nodename+"="+(node.checked ? '1' : '0');
					}
				else if ( nodename == 'start_time_hour' )
					{
					g_start_time[0] = parseInt ( nodeval ).toString();
					}
				else if ( nodename == 'start_time_minute' )
					{
					g_start_time[1] = parseInt ( nodeval ).toString();
					}
				else if ( nodename == 'duration_time_hour' )
					{
					g_duration_time[0] = parseInt ( nodeval ).toString();
					}
				else if ( nodename == 'duration_time_minute' )
					{
					g_duration_time[1] = parseInt ( nodeval ).toString();
					}
				else if ( nodename.match('format_') )
					{
					if ( node.checked )
						{
						g_formats[g_formats.length] = parseInt ( nodename.replace('format_','') ).toString();
						};
					}
				else
					{
					g_args[g_args.length] = nodename+"="+encodeURIComponent(nodeval);
					};
				};
			};
		
		var next = node.firstChild;
		while (next)
			{
			GetRelevantMeetingNodes(id, next, 'meeting');
			next = next.nextSibling;
			};
		};
};

/*******************************************************************/
/** \brief	This simply opens and closes the display of the deleted
	meetings list.
*/
function ToggleDL(in_element	/**< The element to be toggled. */
				)
{
	var	elem = document.getElementById(in_element);
	
	if ( elem )
		{
		if ( elem.className !='change_desc_dl_closed' )
			{
			elem.className = 'change_desc_dl_closed';
			}
		else
			{
			elem.className = 'change_desc_dl_open';
			};
		};
};

/*******************************************************************/
/** \brief	Simply hides the Map div.
*/
function CloseMap()
{
	RevealMap();
	var	elem = document.getElementById('c_comdef_search_results_edit_hidden_div');
	
	if ( elem )
		{
		elem.style.display = 'block';
		};
};

/*******************************************************************/
/** \brief	This reveals and moves the map div, so that it is visible
	and centered on the point 
*/
function RevealMap(in_obj_id	/**< The ID of the element containing the map. */
					)
{
	var in_orig_id = null;
	var	elem = document.getElementById('geocoder_browser_edit_meeting_map_div');
	
	if ( elem )
		{
		var	elem2 = document.getElementById('c_comdef_search_results_edit_hidden_div');
		
		if ( elem2 )
			{
			elem2.style.display = 'none';
			};
		
		if ( g_geocoder_browser_edit_meeting )
			{
			in_orig_id = g_geocoder_browser_edit_meeting.display_id;
			delete g_geocoder_browser_edit_meeting;
			g_geocoder_browser_edit_meeting = null;
			elem.style.display = 'none';
			};
		
		if ( in_orig_id )
			{
			var elem2 = document.getElementById(in_orig_id+'_long_lat_div_a');
			elem2.href = 'javascript:RevealMap(\''+in_orig_id+'\')';
			};
		
		if ( in_obj_id )
			{
			var in_long = document.getElementById(in_obj_id+'_longitude').value;
			var in_lat = document.getElementById(in_obj_id+'_latitude').value;
			var obj = document.getElementById(in_obj_id+'_long_lat_div');
			elem.style.top = '2px';
			elem.style.left = '50%';
			elem.style.width = '612px';
			elem.style.height = '612px';
			elem.style.marginLeft = '-306px';
			elem.style.display = 'block';
			new c_geocoder_browser_edit_meeting ( in_obj_id, in_lat, in_long, true );
			};
		}
	else
		{
		alert ( 'ERROR: Cannot find geocoder_browser_edit_meeting_meeting' );
		};
};

/*******************************************************************/
/** \brief		
*/
function SubmitMeeting(	in_meeting_id,	/**< The ID of the meeting */
						in_obj_id
						)
{
	var	vars = GetRelevantMeetingNodes(in_meeting_id);
	var formatz = g_formats.join(',');
	var throbber_img = document.getElementById ( in_meeting_id+'_submit_throbber_img' );
	var button_input = document.getElementById ( in_meeting_id+'_submit' );
	
	if ( throbber_img && button_input )
		{
		throbber_img.src = '##IMAGE_DIR##'+'/ajax_throbber_linear.gif';	
		button_input.style.display = 'none';
		throbber_img.style.display = 'inline';
		};
	
	var throbber_img_2 = document.getElementById ( in_meeting_id+'_submit_data_item_throbber_img' );
	var button_input_2 = document.getElementById ( in_meeting_id+'_submit_data_item' );
	
	if ( throbber_img_2 && button_input_2 )
		{
		throbber_img_2.src = '##IMAGE_DIR##'+'/ajax_throbber_linear.gif';
		button_input_2.style.display = 'none';
		throbber_img_2.style.display = 'inline';
		};
	
	g_args[g_args.length] = 'formats='+formatz;
	g_args[g_args.length] = 'start_time='+g_start_time[0]+':'+g_start_time[1]+':00';
	g_args[g_args.length] = 'duration_time='+g_duration_time[0]+':'+g_duration_time[1]+':00';
	
	var uri = '##CHANGE_URI##?'+g_args.join('&');

	SimpleAJAXCall(uri,SubmitMeetingCallback,'GET',in_obj_id);
};

/*******************************************************************/
/** \brief		
*/
function SubmitMeetingCallback(	in_text,
								in_param
								)
{
    in_text = in_text.replace(/[\r\n]*/g, "");  // 'Orrible kludge to account for servers being naughty.
	eval ( "var json_obj = "+in_text+";" );

	var done_failed = (null != json_obj.error);
	
	if ( done_failed )
		{
		alert ( json_obj.report+' ('+json_obj.info+')' );
		if ( json_obj.type == 'email_format_bad' )
			{
			var elem = document.getElementById ( 'meeting_'+json_obj.id+'_email_contact' );
			var throbber_img = document.getElementById ( 'meeting_'+json_obj.id+'_submit_throbber_img' );
			var button_input = document.getElementById ( 'meeting_'+json_obj.id+'_submit' );
			var throbber_img_2 = document.getElementById ( 'meeting_'+json_obj.id+'_submit_data_item_throbber_img' );
			var button_input_2 = document.getElementById ( 'meeting_'+json_obj.id+'_submit_data_item' );

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

			if ( throbber_img_2 && button_input_2 )
				{
				throbber_img.src = '';	
				throbber_img.style.display = 'none';
				button_input.style.display = 'inline';
				};
			};
		}
	else
		{
		var meeting_id = json_obj.meeting_id;
		
		if ( meeting_id && in_param )
			{
			g_dirty = false;
			CloseMeetingEditor();
			var id = 'c_comdef_search_results_one_meeting_row_'+meeting_id;
			var line_elem = document.getElementById( id );

			if ( line_elem )
				{
				var	town_elem = document.getElementById ( 'c_comdef_search_results_town_'+meeting_id);
				
				if ( town_elem )
					{
					var	name_elem = document.getElementById ( 'c_comdef_search_results_meeting_list_a_'+meeting_id);
					
					if ( name_elem )
						{
						var	weekday_elem = document.getElementById ( 'c_comdef_search_results_weekday_'+meeting_id);
						
						if ( weekday_elem )
							{
							var	time_elem = document.getElementById ( 'c_comdef_search_results_time_'+meeting_id);
						
							if ( time_elem )
								{
								var	location_elem = document.getElementById ( 'c_comdef_search_results_location_'+meeting_id);
							
								if ( location_elem )
									{
									var	formats_elem = document.getElementById ( 'c_comdef_search_results_formats_'+meeting_id);
									};
								
								/* OK, we have everything we need to update this line. Let's make sure the data is good. */
								if ( formats_elem )
									{
									town_elem.innerHTML = json_obj.town_html;
									name_elem.innerHTML = json_obj.name_html;
									time_elem.innerHTML = json_obj.time_html;
									weekday_elem.innerHTML = json_obj.weekday_html;
									location_elem.innerHTML = json_obj.location_html;
									formats_elem.innerHTML = json_obj.format_html;
									if ( parseInt ( json_obj.meeting_published ) == 1 )
										{
										if ( line_elem.className.match(/.*?_alt1$/) )
											{
											line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_alt1';
											}
										else
											{
											if ( line_elem.className.match(/.*?_alt2$/) )
												{
												line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_alt2';
												};
											};
										}
									else
										{
										if ( line_elem.className.match(/.*?_alt1$/) )
											{
											line_elem.className = json_obj.copy ? 'c_comdef_search_results_one_meeting_row c_comdef_search_results_copy_alt1' : 'c_comdef_search_results_one_meeting_row c_comdef_search_results_unpub_alt1';
											}
										else
											{
											if ( line_elem.className.match(/.*?_alt2$/) )
												{
												line_elem.className = json_obj.copy ? 'c_comdef_search_results_one_meeting_row c_comdef_search_results_copy_alt2' : 'c_comdef_search_results_one_meeting_row c_comdef_search_results_unpub_alt2';
												};
											};
										};
									};
								};
							};
						};
					};
				};
			
			EditMeetingLink ( meeting_id );
			var elem = document.getElementById(in_param);
			elem.disabled = true;
			elem.blur();
			};
		};
};

/*******************************************************************/
/** \brief		
*/
function EnableMeetingChangeButton(	in_obj_id,
									in_disable
									)
{
	if ( typeof ( in_disable ) == 'undefined' )
		{
		in_disable = false;
		};
	var elem = document.getElementById(in_obj_id+'_submit');
	elem.disabled = (in_disable == false) ? false : true;
	elem = document.getElementById(in_obj_id+'_submit_data_item');
	elem.disabled = (in_disable == false) ? false : true;
	g_dirty = (in_disable == false) ? true : false;
};

/*******************************************************************/
/** \brief	
*/
function DeleteMeeting(	in_meeting_id	/**< The ID of the meeting */
						)
{
	in_meeting_id = parseInt(in_meeting_id.replace('meeting_',''));
	if(confirm('##DELETE_CONFIRM##'))
		{
		var row_to_delete = 'c_comdef_search_results_one_meeting_row_'+in_meeting_id;
		
		if ( !document.getElementById(row_to_delete) )
			{
			row_to_delete = null;
			};
		
		var uri = '##DELETE_URI##?meeting_id='+in_meeting_id;
		SimpleAJAXCall(uri,DeleteMeetingCallback,'POST',row_to_delete);
		};
};

/*******************************************************************/
/** \brief		
*/
function DeleteMeetingCallback(	in_text,
								in_param)
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
		g_dirty = false;
		CloseMeetingEditor();
		
		alert ( json_obj.report );

		if ( in_param )
			{
			var elem = document.getElementById(in_param);
			if ( elem )
				{
				var parent = elem.parentNode;
				
				if ( parent )
					{
					parent.removeChild ( elem );
					};
				};
			};
		};
};

/*******************************************************************/
/** \brief	
*/
function RevertMeeting(	in_meeting_id,	/**< This is an integer, with the meeting ID. */
						in_change_id	/**< This is an integer, with the change ID. */
						)
{
	if(confirm('##REVERT_CONFIRM##'))
		{
		SimpleAJAXCall('##REVERT_URI##?meeting_id='+in_meeting_id+'&change_id='+in_change_id,RevertMeetingCallback,'POST',in_meeting_id);
		};
};

/*******************************************************************/
/** \brief		
*/
function RevertMeetingCallback(in_text,
								in_meeting_id	/**< The ID of the meeting */
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
		var line_elem = document.getElementById( 'c_comdef_search_results_one_meeting_row_'+in_meeting_id );
		
		if ( line_elem )
			{
			if ( parseInt ( json_obj.meeting_published ) == 1 )
				{
				if ( line_elem.className.match(/.*?_alt1$/) )
					{
					line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_alt1';
					}
				else
					{
					if ( line_elem.className.match(/.*?_alt2$/) )
						{
						line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_alt2';
						};
					};
				}
			else
				{
				if ( line_elem.className.match(/.*?_alt1$/) )
					{
					line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_unpub_alt1';
					}
				else
					{
					if ( line_elem.className.match(/.*?_alt2$/) )
						{
						line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_unpub_alt2';
						};
					};
				};
			}
		EditMeetingLink ( in_meeting_id );
		};
};

/*******************************************************************/
/** \brief		
*/
function SortFormats ( in_meeting_id )
{
	var format_sort = document.getElementById ( 'sort_formats_by' );
	
	if ( format_sort )
		{
		var def_formats = document.getElementById ( 'def_formats_input' );
		
		if ( def_formats )
			{
			var	format_div = document.getElementById ( 'mtg_format_checkbox_div' );
			
			if ( format_div )
				{
				format_div.innerHTML = '<div class="admin_center_throbber_div"><img src="##IMAGE_DIR##/AdminAJAXThrobber.gif" class="admin_center_throbber" /></div>';	
				def_formats = def_formats.value;
				GetRelevantMeetingNodes ( 'meeting_'+in_meeting_id );
				var formatz = null;
				if ( g_formats )
					{
					formatz = g_formats.join(',');
					};
				if ( !formatz )
					{
					formatz = def_formats;
					};
				var uri = '##SORT_FORMAT_URI##?meeting_id='+in_meeting_id+'&sort_formats_by='+format_sort.value+'&formats='+formatz+'&def_formats='+def_formats;
				SimpleAJAXCall(uri, SortFormatsCallback, 'POST');
				};
			};
		};
};

/*******************************************************************/
/** \brief		
*/
function SortFormatsCallback( in_text )
{
	if ( in_text )
		{
		var	format_div = document.getElementById ( 'mtg_format_checkbox_div' );
		
		if ( format_div )
			{
			format_div.innerHTML = in_text;
			};
		};
};

/*******************************************************************/
/** \brief		
*/
function CheckboxSelectChanged ( in_action )
{
	var nodeList = document.getElementsByTagName ( 'input' );
	var meetingIDs = new Array();
	var	popup_menu = document.getElementById ( 'bmlt_list_search_admin_popup' );
	
	if ( nodeList )
		{
		for ( var c = 0; c < nodeList.length; c++ )
			{
			var node = nodeList[c];
			
			if ( node )
				{
				if ( (node.type == 'checkbox') && (node.value > 0) && node.checked )
					{
					meetingIDs[meetingIDs.length] = parseInt ( node.value );
					};
				};
			};
		
		if ( meetingIDs.length )
			{
			if ( in_action == 'apply_data_item' )
				{
				OpenDataItemEditor ( );
				}
			else
				{
				CloseDataItemEditor ( );
				if ( (in_action == 'duplicate') || (in_action == 'delete') || (in_action == 'delete_extreme_prejudice') )
					{
					var string_prompt = (in_action == 'duplicate') ? '##DUP_ACT_CONFIRM##' : ((in_action == 'delete') ? '##DEL_ACT_CONFIRM##' : '##DEL_ACT_CONFIRM_PERM##');
					
					if ( !confirm ( string_prompt ) )
						{
						if ( popup_menu )
							{
							popup_menu.selectedIndex = 0;
							};
						return;
						};
					};
			
				var popup_div = document.getElementById ( 'c_comdef_search_results_list_footer_inner' );
				var popup_throbber = document.getElementById ( 'bulk_submit_throbber_img' );
				
				if ( meetingIDs.length && popup_div && popup_throbber )
					{
					popup_throbber.src = '##IMAGE_DIR##'+'/ajax_throbber_linear_gray.gif';	
					popup_div.style.display = 'none';
					popup_throbber.style.display = 'inline';
					var ids = meetingIDs.join(',');
					var uri = '##CHECK_BULK_URI##?meeting_ids='+ids+'&action='+in_action;
					SimpleAJAXCall(uri, CheckboxSelectChangedCallback, 'POST');
					};
				};
			}
		else
			{
			alert ( '##CHECK-ONE##' );
			if ( popup_menu )
				{
				popup_menu.selectedIndex = 0;
				};
			};
		};
};

/*******************************************************************/
/** \brief		
*/
function CheckboxSelectChangedCallback ( in_text )
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
		var ids = json_obj.ids;
					
		if ( ((json_obj.message == 'duplicate') || (json_obj.message == 'apply_data_item')) && ids.length )
			{
			if ( json_obj.message == 'apply_data_item' )
				{
				alert ( json_obj.extra_data );
				};
			window.location.reload();
			};
		
		for ( var c = 0; c < ids.length; c++ )
			{
			var id = parseInt ( ids[c] );
			var line_elem = document.getElementById( 'c_comdef_search_results_one_meeting_row_'+id.toString() );
			
			if ( line_elem )
				{
				switch ( json_obj.message )
					{
					case	'publish':
						if ( line_elem.className.match(/.*?_alt1$/) )
							{
							line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_alt1';
							}
						else
							{
							if ( line_elem.className.match(/.*?_alt2$/) )
								{
								line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_alt2';
								};
							};
					break;
					
					case	'unpublish':
						if ( line_elem.className.match(/.*?_alt1$/) )
							{
							line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_unpub_alt1';
							}
						else
							{
							if ( line_elem.className.match(/.*?_alt2$/) )
								{
								line_elem.className = 'c_comdef_search_results_one_meeting_row c_comdef_search_results_unpub_alt2';
								};
							};
					break;
					
					case	'delete':
					case	'delete_extreme_prejudice':
						var parent = line_elem.parentNode;
						
						if ( parent )
							{
							parent.removeChild ( line_elem );
							};
					break;
					};
				}
			};
		
		var popup_div = document.getElementById ( 'c_comdef_search_results_list_footer_inner' );
		var popup_throbber = document.getElementById ( 'bulk_submit_throbber_img' );
		
		if ( popup_div && popup_throbber )
			{
			popup_throbber.src = '';
			popup_throbber.style.display = 'none';
			popup_div.style.display = 'block';
			}

		CancelDataItemEditor ( );
		};
};

/*******************************************************************/
/** \brief		
*/
function AddNewDataItem ( in_meeting_id	/**< The ID of the meeting */
						)
{
	var hideItem = document.getElementById(in_meeting_id+'_add_new_div');
	var showItem = document.getElementById(in_meeting_id+'_new_fieldset');
	
	hideItem.style.display = 'none';
	showItem.style.display = 'block';
};

/*******************************************************************/
/** \brief		
*/
function DoNotAddDataItem ( in_meeting_id	/**< The ID of the meeting */
						)
{
	var showItem = document.getElementById(in_meeting_id+'_add_new_div');
	var hideItem = document.getElementById(in_meeting_id+'_new_fieldset');
	
	hideItem.style.display = 'none';
	showItem.style.display = 'block';
};

/*******************************************************************/
/** \brief		
*/
function DeleteMeetingDataItem ( in_meeting_data_item_id
								)
{
	var main_elem = document.getElementById ( in_meeting_data_item_id+'_fieldset' );
	var del_elem = document.getElementById ( in_meeting_data_item_id+'_deleted_div' );
	var input_elem = document.getElementById ( in_meeting_data_item_id+'_deleted_input' );
	
	main_elem.style.display = 'none';
	del_elem.style.display = 'block';
	input_elem.value= '1';
};

/*******************************************************************/
/** \brief		
*/
function UnDeleteMeetingDataItem ( in_meeting_data_item_id
									)
{
	var main_elem = document.getElementById ( in_meeting_data_item_id+'_fieldset' );
	var del_elem = document.getElementById ( in_meeting_data_item_id+'_deleted_div' );
	var input_elem = document.getElementById ( in_meeting_data_item_id+'_deleted_input' );
	
	main_elem.style.display = 'block';
	del_elem.style.display = 'none';
	input_elem.value= '';
};

/*******************************************************************/
/** \brief		
*/
function OpenDataItemEditor ( )
{
	var dataItemEditor = document.getElementById('bmlt_list_data_item_div');
	
	if ( dataItemEditor )
		{
		dataItemEditor.style.display = 'block';
		document.getElementById('edit_data_item_value_textarea').select();
		};
};

/*******************************************************************/
/** \brief		
*/
function CloseDataItemEditor ( )
{
	var dataItemEditor = document.getElementById('bmlt_list_data_item_div');

	if ( dataItemEditor )
		{
		dataItemEditor.style.display = 'none';
		};
};

/*******************************************************************/
/** \brief		
*/
function CancelDataItemEditor ( )
{
	var	popup_menu = document.getElementById ( 'bmlt_list_search_admin_popup' );
	
	if ( popup_menu )
		{
		popup_menu.selectedIndex = 0;
		};
	CloseDataItemEditor ( );
};

/*******************************************************************/
/** \brief		
*/
function ApplyDataItemEditor ( )
{
	var override = document.getElementById ('edit_data_item_override_checkbox').checked;

	var confirmed = true;

	if ( !override || confirm('##APPLY-CONFIRM##') )
		{
		var nodeList = document.getElementsByTagName ( 'input' );
		var meetingIDs = new Array();
		var	popup_menu = document.getElementById ( 'bmlt_list_search_admin_popup' );
		
		if ( nodeList )
			{
			for ( var c = 0; c < nodeList.length; c++ )
				{
				var node = nodeList[c];
				
				if ( node )
					{
					if ( (node.type == 'checkbox') && (node.value > 0) && node.checked )
						{
						meetingIDs[meetingIDs.length] = parseInt ( node.value );
						};
					};
				};
			
			if ( meetingIDs.length )
				{
				var popup_div = document.getElementById ( 'c_comdef_search_results_list_footer_inner' );
				var popup_throbber = document.getElementById ( 'bulk_submit_throbber_img' );
				
				if ( meetingIDs.length && popup_div && popup_throbber )
					{
					var ids = meetingIDs.join(',');
					var key_string = encodeURI ( document.getElementById ( 'edit_data_item_new_key' ).value );
					var value_string = encodeURI ( document.getElementById ( 'edit_data_item_value_textarea' ).value );
					
					if ( !(value_string.replace(/^\s+|\s+$/g,"")) && override && !confirm('##APPLY-DELETE-CONFIRM##') )
						{
						confirmed = false;
						}
					else
						{
						if ( !(value_string.replace(/^\s+|\s+$/g,"")) && !override )
							{
							alert ('##APPLY-DELETE-OOPS##');
							confirmed = false;
 							};
						};
					
					if ( confirmed )
						{
						var uri = '##CHECK_BULK_URI##?meeting_ids='+ids+'&action=apply_data_item&key_string='+key_string+'&value_string='+value_string;
						if ( override )
							{
							uri += '&override=1';
							};
						popup_throbber.src = '##IMAGE_DIR##'+'/ajax_throbber_linear_gray.gif';	
						popup_div.style.display = 'none';
						popup_throbber.style.display = 'inline';
						SimpleAJAXCall(uri, CheckboxSelectChangedCallback, 'POST');
						}
					else
						{
						alert ( '##APPLY-CANCELED##' );
						};
					};
				}
			else
				{
				if ( popup_menu )
					{
					popup_menu.selectedIndex = 0;
					};
				};
			};
		}
	else
		{
		alert ( '##APPLY-CANCELED##' );
		};
};

/*******************************************************************/
/** \brief		
*/
function ToggleCheckBoxState ( )
{
	// Get all the checkboxes.
	var nodeList = document.getElementsByTagName ( 'input' );
	
	// Determine the state to which they will be set.
	var state = document.getElementById ( 'list_checkbox_0' ).checked;
	
	// Do it to it.
	for ( var c = 0; c < nodeList.length; c++ )
		{
		var node = nodeList[c];
		
		if ( node )
			{
			node.checked = state;
			};
		};
};
