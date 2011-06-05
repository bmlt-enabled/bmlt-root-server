/***********************************************************************/
/** \file	search_specifier_map.js

	\brief	This file will be optimized and embedded in the HTML that is
	returned for a meeting search map specification.

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

var	main_map = null;			/**< The main map object. */
var	main_lat = null;			/**< The latitude of the initial map center. */
var	main_lng = null;			/**< The longitude of the initial map center. */
var	main_zoom = null;			/**< The zoom of the initial map center. */
var main_marker = null;			/**< This is the marker that is placed in the Advanced map. */
var main_overlay = null;		/**< The main circular overlay for advanced map mode. */
var old_popup_display = false;	/**< This keeps track of popup visibility when switching between Advanced and Basic. */
var	disable_multi_zoom = false;	/**< If this is set to true, then the "multi-zoom" functionality, for zooming in before searching for meetings, will be disabled. Default is false.*/
var giconCenter = new GIcon();	/**< This will be the center icon in the advanced map. */
var g_geo = null;				/**< Android uses Google Gears, so we use this to keep an instance of Gears geolocator. */

/** This is an icon for the central marker (Default is a black "pin"). */
giconCenter.image = "##IMAGE_DIR##/NACenterMarker.png";
giconCenter.printImage = "##IMAGE_DIR##/NACenterMarker.gif";
giconCenter.mozPrintImage = "##IMAGE_DIR##/NACenterMarker.png";
giconCenter.iconSize = new GSize(21, 36);
giconCenter.shadow = "##IMAGE_DIR##/NACenterMarkerS.png";
giconCenter.shadowSize = new GSize(38, 36);
giconCenter.iconAnchor = new GPoint(10, 36);
giconCenter.infoWindowAnchor = new GPoint(10, 0);
giconCenter.transparent = "##IMAGE_DIR##/NACenterMarkerX.png";
giconCenter.imageMap = [10,35,0,11,0,4,4,0,15,0,20,3,20,12,10,35];

/*******************************************************************/
/** \brief	This creates the central marker on the map.
*/
function CreateCenterMarker ( in_point		/**< A floating-point value. The longitude of the center of the map. */
							)
{
	if ( in_point )
		{
		if ( main_marker )
			{
			main_map.removeOverlay ( main_marker );
			main_marker = null;
			};
	
		main_marker = new GMarker(in_point, {draggable:true, icon:giconCenter});
		
		if ( main_marker )
			{
			GEvent.addListener( main_marker, "dragend", DragEnd );
			GEvent.addListener( main_marker, "dragstart", DragStart );
			main_map.addOverlay( main_marker );
			};
		};
};

/*******************************************************************/
/** \brief	This makes the small map for the basic search. The map
	does not display any markers or overlays, and just allows the
	visitor to click anywhere. When they click, they will be either
	taken to the large, full-page map, or a list.
*/
function MakeSmallMap (	in_lat,		/**< A floating-point value. The latitude of the center of the map. */
						in_lng,		/**< A floating-point value. The longitude of the center of the map. */
						in_zoom,		/**< An integer. The zoom at which to display the map. */
						in_disable_multi_zoom	/**< If this is set to true, then the "multi-zoom" functionality, for zooming in before searching for meetings, will be disabled. Default is false.*/
						)
{
	if ( in_disable_multi_zoom )
		{
		disable_multi_zoom = true;
		}
	else
		{
		disable_multi_zoom = false;
		};
		
	if ( in_lat )
		{
		main_lat = in_lat;
		};
		
	if ( in_lng )
		{
		main_lng = in_lng;
		};
		
	if ( in_zoom )
		{
		main_zoom = in_zoom;
		};

	var	elem = document.getElementById('c_comdef_search_specification_map_div');
	
	if ( !main_map && (elem.style.display != 'none') )
		{
		var	cursor_type = (disable_multi_zoom || (in_zoom > 8)) ? 'crosshair' : 'pointer';
		
		main_map = new GMap2(elem, { draggableCursor:cursor_type });
		
		if ( main_map )
			{
			main_map.addControl(new GLargeMapControl());
			main_map.addControl(new GScaleControl(), new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize ( 10, 30 )));
			
			GEvent.addListener( main_map, "click", SmallMapClicked);
			
			main_map.setCenter ( new GLatLng ( main_lat, main_lng ), main_zoom );
		
			main_map.checkResize();
			GEvent.addListener( main_map, "zoomend", ZoomEnd );
			};
		};
};

/*******************************************************************/
/** \brief	This redraws the circle, and starts a new AJAX search.
*/
function DragEnd ()
{
	var point = new GLatLng ( main_marker.getPoint().lat(), main_marker.getPoint().lng() );
	
	if ( point )
		{
		SmallMapClicked ( main_marker, point );
		};
};

/*******************************************************************/
/** \brief	This redraws the circle, and starts a new AJAX search.
*/
function ZoomEnd ()
{
	var	new_center = main_map.getCenter();
	var	zoom = main_map.getZoom();
	var old_marker_set = main_marker != null;
	
	/* We have to do this, because we need to reset the cursor if the zoom is smaller/bigger than 9. */
	if ( main_marker )
		{
		main_map.removeOverlay ( main_marker );
		main_marker = null;
		};

	main_map = null;
	MakeSmallMap ( new_center.lat(), new_center.lng(), zoom, disable_multi_zoom );
	var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );
	
	if ( old_marker_set && bmlt_search_type && (bmlt_search_type.value == 'advanced') && (zoom > 8 ) )
		{
		SmallMapClicked ( null, new_center );
		};
	
	if ( !disable_multi_zoom && (main_map.getZoom() < 9) )
		{
		// If there is no marker, then we should have no saved centerpoint.
		document.getElementById ( 'advanced_radius' ).selectedIndex = 0;
		if ( main_marker )
			{
			main_map.removeOverlay ( main_marker );
			main_marker = null;
			};
		
		var bmlt_lat_val_hidden = document.getElementById ( 'lat_val_hidden' );
		var bmlt_long_val_hidden = document.getElementById ( 'long_val_hidden' );
		
		if ( bmlt_lat_val_hidden && bmlt_long_val_hidden )
			{
			bmlt_lat_val_hidden.value = null;
			bmlt_long_val_hidden.value = null;
			};
		};
};

/*******************************************************************/
/** \brief	This is called as a drag of the marker starts.
*/
function DragStart ( overlay, in_point )
{
	ClearCircularOverlay();
}

/*******************************************************************/
/** \brief	This is called when the visitor clicks inside the map,
	and will direct them to the main map.
*/
function SmallMapClicked ( overlay, in_point )
{
	var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );
	var advanced_mapmode = document.getElementById ( 'advanced_mapmode' );
	if ( disable_multi_zoom || (main_map.getZoom() > 8) )
		{
		/* We only display the bottom popup menu in Basic Search Mode. */
		if ( !bmlt_search_type || (bmlt_search_type && (bmlt_search_type.value != 'advanced')) )
			{
			var	elem = document.getElementById('result_type');
			if ( elem )
				{
				ShowThrobber();
				/* The "disp_format" parameter selects between a list and the map. */
				uri='##SCRIPT_URL##&long_val='+parseFloat(in_point.lng()).toString()+'&lat_val='+parseFloat(in_point.lat()).toString()+'&disp_format='+elem.value;
		
				window.location.href=uri;
				};
			}
		else
			{
			/* Just being anal-retentive */
			if ( advanced_mapmode )
				{
				advanced_mapmode.value = 1;
				};
			var bmlt_lat_val_hidden = document.getElementById ( 'lat_val_hidden' );
			var bmlt_long_val_hidden = document.getElementById ( 'long_val_hidden' );
			
			if ( bmlt_lat_val_hidden && bmlt_long_val_hidden )
				{
				bmlt_lat_val_hidden.value = in_point.lat();
				bmlt_long_val_hidden.value = in_point.lng();
				};
			
			main_map.panTo ( in_point );
			
			if ( !main_marker )
				{
				CreateCenterMarker ( in_point );
				}
			else
				{
				main_marker.setPoint ( in_point );
				};
			CreateCircularOverlay();
			};
		}
	else
		{
		if ( main_map.getZoom() < 5 )
			{
			main_map = null;
		
			MakeSmallMap ( in_point.lat(), in_point.lng(), 5, disable_multi_zoom );
			}
		else
			{
			if ( main_map.getZoom() < 9 )
				{
				main_map = null;
		
				MakeSmallMap ( in_point.lat(), in_point.lng(), 9, disable_multi_zoom );
				};
			};
		};
};

/*******************************************************************/
/** \brief	This function will toggle the basic search between the
	text input and the map.
	
	\returns a boolean. true if the map was visible before, false, otherwise.
*/
function ToggleMapVisibility( in_hide	///< True if the display is to be text.
							)
{
	var	elem = document.getElementById('c_comdef_search_specification_map_div');
	var	elem2 = document.getElementById('c_comdef_search_specification_map_vis_a');
	var	elem3 = document.getElementById('c_comdef_search_specification_map_check_div');
	var	elem4 = document.getElementById('c_comdef_search_specification_form_header_div');
	var	was_visible = (elem.style.display != 'none');
	var bmlt_lat_val_hidden = document.getElementById ( 'lat_val_hidden' );
	var bmlt_long_val_hidden = document.getElementById ( 'long_val_hidden' );
	var bmlt_long_val_hidden = document.getElementById ( 'geo_width' );
	var bmlt_search_radius_div = document.getElementById ( 'search_radius_div' );
	var advanced_mapmode = document.getElementById ( 'advanced_mapmode' );
	var result_type_advanced = document.getElementById ( 'result_type_advanced' );
	
	if ( elem && elem2 && elem3 && advanced_mapmode )
		{
		if ( !in_hide && (elem.style.display == 'none') )
			{
			document.getElementById('c_comdef_search_specification_search_string_line').style.display = 'none';
			elem.style.display = 'block';
			elem4.className = 'c_comdef_search_specification_form_header_div hidden_element_mode';
			elem2.title = '##SEARCHBYMAP_TITLE##';
			elem2.className = 'c_comdef_search_specification_map_vis_a';
			elem2.innerHTML = '##SEARCHBYSTRING##';
            if ( (main_lat || main_lng) && main_zoom )
                {
                MakeSmallMap ( main_lat, main_lng, main_zoom, disable_multi_zoom );
                };
			
			var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );
			
			/* We only display the bottom popup menu in Basic Search Mode. */
			if ( bmlt_search_type && (bmlt_search_type.value != 'advanced') )
				{
				elem3.style.display = 'block';
				if ( bmlt_search_radius_div )
					{
					bmlt_search_radius_div.style.display = 'none';
					};
				
				advanced_mapmode.value = '';
				}
			else
				{
				old_popup_display = 'block';
				if ( bmlt_lat_val_hidden && bmlt_long_val_hidden && main_marker )
					{
					bmlt_lat_val_hidden.value = main_marker.getLatLng().lat();
					bmlt_long_val_hidden.value = main_marker.getLatLng().lng();
					};
				
				bmlt_search_radius_div.style.display = elem.style.display;
				
				advanced_mapmode.value = 1;
				};
			}
		else
			{
			elem.style.display = 'none';
			elem3.style.display = 'none';
			elem2.title = '##SEARCHBYSTRING_TITLE##';
			elem2.className = 'c_comdef_search_specification_map_invis_a';
			elem2.innerHTML = '##SEARCHBYMAP##';
			elem4.className = 'c_comdef_search_specification_form_header_div';
			document.getElementById('c_comdef_search_specification_search_string_line').style.display = 'block';
			if ( document.getElementById('c_comdef_search_specification_search_string_line').style.display != 'none' )
				{
				document.getElementById('c_comdef_search_specification_search_string').focus();
				};
			old_popup_display = 'none';
			if ( bmlt_lat_val_hidden && bmlt_long_val_hidden )
				{
				bmlt_lat_val_hidden.value = null;
				bmlt_long_val_hidden.value = null;
				};
			if ( bmlt_search_radius_div )
				{
				bmlt_search_radius_div.style.display = 'none';
				};
			
			advanced_mapmode.value = '';
			};
		};
	
	if ( in_hide )
		{
		return was_visible;
		};
};

/*******************************************************************/
/** \brief	This function shows or hides the advanced search div.
*/
function DisplaySearchSpecification (	in_spec,	///< The type specifier. It can be 'advanced' or 'basic'.
										in_type		///< This is the type of display (can be 'map' or 'text'). If not specified, then whatever has been previously set is used.
									)
{
	/* Get all the various DOM elements we'll be using. */
	var basic_submit = document.getElementById ( 'c_comdef_search_specification_search_string_submit_basic' );
	var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );
	var bmlt_search_string = document.getElementById ( 'c_comdef_search_specification_search_string' );
	var bmlt_basic_type_popup = document.getElementById ( 'c_comdef_search_specification_map_check_div' );

	/* Collect the whole set. */
	if ( basic_submit && bmlt_search_type && bmlt_search_string && bmlt_basic_type_popup )
		{
		var advanced_search_mode = document.getElementById ( 'advanced_search_mode' );
		
		if ( advanced_search_mode )	// If this is the dynamic search display (This element is not present in the non-AJAX search).
			{
			var advanced_div = document.getElementById ( 'advanced_search_div' );
			var map_disc = document.getElementById ( 'c_comdef_search_specification_map_vis_a' );
			var	map_div = document.getElementById('c_comdef_search_specification_map_div');
			var bmlt_search_radius_div = document.getElementById ( 'search_radius_div' );
			var basic_tab = document.getElementById ( 'bmlt_spec_tab_basic_id' );
			var advanced_tab = document.getElementById ( 'bmlt_spec_tab_advanced_id' );
		
			if ( advanced_div && map_disc && map_div && bmlt_search_radius_div && basic_tab && advanced_tab )
				{
				// Some browsers (I before E before 7) reset the display, without resetting the variables. This should account for that.
				if ( (in_spec == 'advanced') && ((bmlt_search_type.value != 'advanced') || !advanced_search_mode.value || (advanced_div.style.display != 'block')) )
					{
					advanced_search_mode.value = 1;
					bmlt_search_type.value = 'advanced';
					advanced_div.style.display = 'block';
					basic_tab.className = 'bmlt_spec_tab';
					advanced_tab.className = 'bmlt_spec_tab_selected';
					basic_submit.style.display = 'none';
					old_popup_display = bmlt_basic_type_popup.style.display;
					bmlt_basic_type_popup.style.display = 'none';
					if ( main_marker && main_map )
						{
						ClearCircularOverlay();
						CreateCircularOverlay();
						main_marker.show();
						};
				
					var advanced_mapmode = document.getElementById ( 'advanced_mapmode' );
					var ss_line = document.getElementById('c_comdef_search_specification_search_string_line');
					
					if ( advanced_mapmode && ss_line )
						{
						advanced_mapmode.value = (bmlt_search_radius_div.style.display != 'none') ? 1 : '';
						
						if ( (in_type == 'text') && (ss_line.style.display != 'none') )
							{
							bmlt_search_string.focus();
							}
						else
							if ( in_type == 'map' && (map_div.style.display != 'block') )
								{
								ToggleMapVisibility ( );
								}
							else
								if ( in_type == 'text' )
									{
									ToggleMapVisibility ( true );
									bmlt_search_string.focus();
									}
							};
					
					bmlt_search_radius_div.style.display = map_div.style.display;
	
					advanced_search_mode.value = 1;

					if( typeof ( navigator ) == 'object' && typeof ( navigator.geolocation ) == 'object' )
						{
						document.getElementById ( 'where_am_i_advanced_fieldset' ).style.display='block';
						};
					}
				else if ( (in_spec != 'advanced') && ((bmlt_search_type.value == 'advanced') || advanced_search_mode.value || (advanced_div.style.display == 'block')) )
					{
					advanced_search_mode.value = '';
				
					if ( advanced_mapmode )
						{
						advanced_mapmode.value = '';
						};
					bmlt_search_type.value = 'basic';
					map_disc.style.display = 'block';
					advanced_div.style.display = 'none';
					advanced_tab.className = 'bmlt_spec_tab';
					basic_tab.className = 'bmlt_spec_tab_selected';
					basic_submit.style.display = 'inline';
					bmlt_basic_type_popup.style.display = old_popup_display;
					if ( main_marker && main_map )
						{
						main_marker.hide();
						ClearCircularOverlay();
						};
					};
				};
			
			if ( document.getElementById('c_comdef_search_specification_search_string_line').style.display != 'none' )
				{
				bmlt_search_string.focus();
				};
			};
		};
};

/*******************************************************************/
/** \brief	This creates a translucent circular overlay that represents
	the radius of the search.
*/
function CreateCircularOverlay ()
{
	var advanced_radius = document.getElementById ( 'advanced_radius' );

	var main_radius = parseFloat ( advanced_radius.value );
	if( main_overlay )
		{
		main_map.removeOverlay ( main_overlay );
		main_overlay = null;
		};
	
	if ( main_radius > 0 )
		{
		if ( main_marker )
			{
			point = new GLatLng ( main_marker.getPoint().lat(), main_marker.getPoint().lng() );
			
			if ( point )
				{
				var size = 226 * main_radius;
				main_overlay = new EInsert ( point, "##IMAGE_DIR##/Circle.png", new GSize(size,size), 13 );
				main_map.addOverlay ( main_overlay );
				}
			else
				{
				ClearCircularOverlay();
				};
			};
		}
	else
		{
		ClearCircularOverlay();
		};
};

/*******************************************************************/
/** \brief	This clears the circular overlay.
*/
function ClearCircularOverlay ()
{
	if ( main_overlay )
		{
		main_map.removeOverlay ( main_overlay );
		main_overlay.size = null;
		main_overlay = null;
		};
};

/*******************************************************************/
/** \brief	This function is called whenever one of the Service Body
	checkboxes changes value. If there are dependents, then they are
	all changed to match this one.
*/
function ServiceBodyCheckboxChanged ( in_dom_id	/**< The DOM ID of the checkbox. The IDs are written in a way that shows hierarchy. */
									)
{
	var elem = document.getElementById ( in_dom_id );
	var par_id = in_dom_id.replace ( /my_id_/, 'parent_' );
	
	if ( elem )
		{
		var allmychildren = GetDependentNodes ( par_id );
		for (var c = 0; c < allmychildren.length; c++ )
			{
			elem2 = document.getElementById ( allmychildren[c] );
			if ( elem2 )
				{
				if ( elem2.checked != elem.checked )
					{
					elem2.checked = elem.checked;
					};
				};
			};
		};
};

/*******************************************************************/
/** \brief	Traverses the DOM tree, and returns an array of all the
	elements with IDs that contain this element's ID.
	
	\returns an array of DOM node objects. These are ones that are directly
	or indirectly dependent upon this one. The array is flat.
*/
function GetDependentNodes (in_dom_id,		/**< The DOM ID that will be the basis of what we are looking for. The IDs are written in a way that shows hierarchy. */
							in_child_nodes	/**< The childnodes to check. */
							)
{
	var ret_array = new Array;
	if ( !in_child_nodes )
		{
		in_child_nodes = document.childNodes;
		};
	
	for (var c = 0; c < in_child_nodes.length; c++ )
		{
		var theNode = in_child_nodes[c];
		var nodeID = in_dom_id;
		
		if ( theNode.id )
			{
			var re = new RegExp ( '^'+nodeID+'_' );
			if ( theNode.id.toString().match ( re ) )
				{
				nodeID = theNode.id.toString();
				ret_array[ret_array.length] = nodeID;
				};
			};
		
		if ( theNode.childNodes && theNode.childNodes.length )
			{
			var node_children = GetDependentNodes ( nodeID, theNode.childNodes );
			if ( node_children && node_children.length )
				{
				ret_array = ret_array.concat ( node_children );
				};
			};
		};
	
	return ret_array;
};

/*******************************************************************/
/** \brief	Hides the main form, and shows the throbber. Call this when you submit.
*/
function ShowThrobber()
{
	document.getElementById ( 'bmlt_throbber_div' ).style.display='block';
};

/*******************************************************************/
/** \brief	Reverses the above.
*/
function HideThrobber()
{
	document.getElementById ( 'bmlt_search_spec_table' ).style.display='block';
	document.getElementById ( 'bmlt_throbber_div' ).style.display='none';
};

function WhereAmI_Fail_Final()
{
	var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );

	if ( !bmlt_search_type || (bmlt_search_type && (bmlt_search_type.value != 'advanced')) )
		{
		HideThrobber();
		};
	
	WhereAmI_Advanced_Fail_Final();
};

function WhereAmI_CallBack(in_position
							)
{
	if ( !in_position.coords )
		{
		var coords = {'longitude':in_position.longitude, 'latitude':in_position.latitude};
		
		in_position.coords = coords;
		};
	
	var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );

	if ( bmlt_search_type && (bmlt_search_type.value == 'advanced') )
		{
		var point = new GLatLng ( in_position.coords.latitude, in_position.coords.longitude );
		
		if ( point )
			{
			if ( main_marker )
				{
				main_map.removeOverlay ( main_marker );
				main_marker = null;
				};
			main_map.setZoom(10);	// Make sure the marker shows up.
			SmallMapClicked ( null, point );
			document.getElementById ( 'where_am_i_advanced_button' ).disabled = false;
			};
		}
	else
		{
		uri='##SCRIPT_URL##&long_val='+in_position.coords.longitude.toString()+'&lat_val='+in_position.coords.latitude.toString()+'&disp_format=map';
		window.location.href=uri;
		};
};

function WhereAmI()
{
	var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );

	if ( !bmlt_search_type || (bmlt_search_type && (bmlt_search_type.value != 'advanced')) )
		{
		ShowThrobber();
		}
	else
		{
		document.getElementById ( 'where_am_i_advanced_button' ).disabled = true;
		};
	
	if( typeof ( google ) == 'object' && typeof ( google.gears ) == 'object' )
		{
		if ( !g_geo )
			{
			g_geo = google.gears.factory.create('beta.geolocation');
			};
		
		g_geo.getCurrentPosition(WhereAmI_CallBack, WhereAmI_TryAgain_Google);
		}
	else
		{
		if( typeof ( navigator ) == 'object' && typeof ( navigator.geolocation ) == 'object' )
			{
			navigator.geolocation.getCurrentPosition(WhereAmI_CallBack, WhereAmI_TryAgain_w3c, {maximumAge:300000,timeout:0});
			}
		};
};

function WhereAmI_TryAgain_Google()
{
	var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );

	if ( bmlt_search_type && (bmlt_search_type.value == 'advanced') )
		{
		document.getElementById ( 'where_am_i_advanced_button' ).disabled = true;
		};
		
	g_geo.getCurrentPosition(WhereAmI_CallBack, WhereAmI_Fail_Final);
};

function WhereAmI_TryAgain_w3c()
{
	var bmlt_search_type = document.getElementById ( 'bmlt_search_type' );

	if ( bmlt_search_type && (bmlt_search_type.value == 'advanced') )
		{
		document.getElementById ( 'where_am_i_advanced_button' ).disabled = true;
		};
		
	navigator.geolocation.getCurrentPosition(WhereAmI_CallBack,WhereAmI_Fail_Final);
};

/* If the browser can handle the geolocation API, we show an extra fieldset in the map. */
if( (typeof ( navigator ) == 'object' && typeof ( navigator.geolocation ) == 'object') || (typeof ( google ) == 'object' && typeof ( google.gears ) == 'object') )
	{
	document.getElementById ( 'where_am_i_fieldset' ).style.display = 'block';
	};

// FF can sometimes hold onto values. This should make sure it lets go and lets God...
if ( document.getElementById ( 'advanced_search_mode' ) )
	{
	document.getElementById ( 'advanced_search_mode' ).value = 0;
	};

if ( document.getElementById ( 'advanced_mapmode' ) )
	{
	document.getElementById ( 'advanced_mapmode' ).value = 0;
	};
