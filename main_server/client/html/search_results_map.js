/***********************************************************************/
/** \file	search_results_map.js

	\brief	This file will be optimized and embedded in the HTML that is
	returned for a meeting search map result.

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

var	main_lat = null;				/**< The latitude of the center point */
var	main_lng = null;				/**< The longitude of the center point */
var	main_map = null;				/**< The main map object. */
var main_overlay = null;			/**< The main circular overlay. */
var	main_marker = null;				/**< The center marker. */
var	main_radius = null;				/**< The radius, in miles. */
var	main_kbh = null;				/**< The keyboard handler. */
var	main_allocated_markers = null;	/**< This will hold all of the allocated markers. */
var g_last_callback_result = null;	/**< Contains the JSON object from the last search callback. */

/* extend Number object with methods for converting degrees/radians */

Number.prototype.toRad = function()
{
  return this * Math.PI / 180;
};

Number.prototype.toDeg = function()
{
  return this * 180 / Math.PI;
};

/*******************************************************************/
/** \brief	This fills the entire window with a map. The scale (zoom)
	is automatically determined, based upon the radius.
*/
function LoadMapMain ()
{
	elem = document.getElementById ('c_comdef_search_results_map_div');
	main_map = new GMap2(elem, { draggableCursor:'crosshair' } );
	
	if ( main_map )
		{
		var window_size = GetWindowSize ();
		main_map.enableScrollWheelZoom();
		main_map.enableContinuousZoom();
		
		if ( window_size.width < 640 )
			{
			main_map.addControl(new GSmallZoomControl3D());
			main_map.enablePinchToZoom();
			}
		else
			{
			main_map.addControl(new GMapTypeControl());
			main_map.addControl(new GLargeMapControl());
			main_map.addControl(new GScaleControl(), new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize ( 10, 30 )));
			main_map.addMapType ( G_PHYSICAL_MAP );
			};

		
		GEvent.addListener(main_map, "click", MapClicked);

		var zoom = ChooseZoom ( main_radius );
		main_map.setCenter ( new GLatLng ( main_lat, main_lng ), zoom );
		CreateCenterMarker ( main_lat, main_lng );
		main_map.checkResize();
		DragEnd();
		GEvent.addListener( main_map, "zoomend", ZoomEnd );
		};
};

/*******************************************************************/
/** \brief This is a callback that comes from the Google Bar being used
	to do a map search. What it does is hide the default search result
	marker (for the first result), and moves the center marker (and the
	search itself) to the location of the first result.
*/
function MarkerSetCallback ( in_result	/**< A search result object array. */
							)
{
	if ( in_result[0].marker )

		{
		main_map.checkResize();
		in_result[0].marker.hide();
		MapClicked ( null, new GLatLng(parseFloat(in_result[0].marker.getPoint().lat()), parseFloat(in_result[0].marker.getPoint().lng())) );
		}
};

/*******************************************************************/
/** \brief	This chooses a zoom level that should accommodate the
	the given radius.
	
	\returns an integer, denoting the chozen zoom level.
*/
function ChooseZoom ( in_radius		/**< A floating-point value. The radius of the circle, in miles. */
					)
{
	/* These are very general numbers. This is an extremely "kludgy" function, but it works. */
	
	var overlay_size = 226 * in_radius;
	var window_size = GetWindowSize ();
	
	if ( main_map )
		{
		var zoom = 7;
		if ( in_radius < 0.05 )
			{
			zoom = 19;
			}
		else
			{
			if ( in_radius < 0.125 )
				{
				zoom = 18;
				}
			else
				{
				if ( in_radius < 0.25 )
					{
					zoom = 17;
					}
				else
					{
					if ( in_radius < 0.5 )
						{
						zoom = 16;
						}
					else
						{
						if ( in_radius < .75 )
							{
							zoom = 15;
							}
						else
							{
							if ( in_radius <= 1 )
								{
								zoom = 14;
								}
							else
								{
								if ( in_radius < 2.8 )
									{
									zoom = 13;
									}
								else
									{
									if ( in_radius < 6 )
										{
										zoom = 12;
										}
									else
										{
										if ( in_radius < 11 )
											{
											zoom = 11;
											}
										else
											{
											if ( in_radius < 21 )
												{
												zoom = 10;
												}
											else
												{
												if ( in_radius < 41 )
													{
													zoom = 9;
													}
												else
													{
													if ( in_radius < 61 )
														{
														zoom = 8;
														};
													};
												};
											};
										};
									};
								};
							};
						};
					};
				};
			};
		};
	
	if ( window_size.width < 640 )
		{
		zoom--;
		};
	
	return zoom;
};

/*******************************************************************/
/** \brief	If the map is clicked, the main marker is moved there,
	and a new AJAX search is performed.
*/
function MapClicked (overlay,
					in_point
					)
{
	if( main_marker && in_point )
		{
		AbortAllAJAXHTTPRequests();
		ClearMarkers ();
		ClearCircularOverlay();
		main_marker.setPoint ( new GLatLng ( in_point.lat(), in_point.lng() ) );
		DragEnd ();
		};
};

/*******************************************************************/
/** \brief	This "clears the decks" before a drag is started.
*/
function DragStart ()
{
	AbortAllAJAXHTTPRequests();
	main_map.closeInfoWindow();
	ClearMarkers ();
	ClearCircularOverlay();
};

/*******************************************************************/
/** \brief	This redraws the circle, and starts a new AJAX search.
*/
function ZoomEnd ()
{
	main_map.closeInfoWindow();
	ClearMarkers ();
	ClearCircularOverlay();	
	DisplayMapSearchCallback ( g_last_callback_result, null, null, true );
};

/*******************************************************************/
/** \brief	This redraws the circle, and starts a new AJAX search.
*/
function DragEnd ()
{
	var point = new GLatLng ( main_marker.getPoint().lat(), main_marker.getPoint().lng() );
	
	if ( point )
		{
		CreateCenterMarkerHTML ('##FILTER_MESSAGE##');
		DisplayMapMeetings();
		};
};

/*******************************************************************/
/** \brief	This responds to the popup menu in the main marker window.
*/
function ChangeRadius ( in_radius	/**< A floating-point value. The radius of the circle, in miles. */
						)

{
	AbortAllAJAXHTTPRequests();
	ClearMarkers();
	g_last_callback_result = null;
	main_radius = in_radius;
	DragEnd ();
};

/*******************************************************************/
/** \brief	This creates the info window HTML for the main marker.
*/
function CreateCenterMarkerHTML ( filter_message )
{
	if ( main_marker )
		{
		var list_link = '##LIST_LINK##';
		var	radius_range = ##RADIUS_ARRAY##;
		var	radius_range_unit = '##RADIUS_ARRAY_UNIT_STRING##';
		var	marker_html = '<div class="marker_main_info_window_div">';
		
		marker_html += '<form action="#">';
		
		if ( filter_message )
			{
			marker_html += '<div class="marker_main_info_window_inner_div">'+filter_message+'</div>';
			};
		
		marker_html += '<div class="marker_main_info_window_inner_div">';
		marker_html += '<label for="marker_main_radius_select">##RADIUS_ARRAY_PROMPT##</label>';
		marker_html += '<select id="marker_main_radius_select" onchange="ChangeRadius(this.value)">';
		var first_val = radius_range[0];
		if ( main_radius  && (main_radius < first_val) )
			{
			if ( radius_range_unit == ' miles' )
				{
				text = main_radius+' mile';
				}
			else
				{
				text = main_radius+radius_range_unit;
				};
			
			marker_html += '<option value="'+main_radius+'" selected="selected">'+text+'</option>';
			}
		
		for ( var c = 0; c < radius_range.length; c++ )
			{
			var value = radius_range[c];
			var text = value;
			if ( text == 0.125 )
				{
				if ( radius_range_unit == ' miles' )
					{
					text = '1/8 mile';
					}
				else
				    {
                    text = '1/8' + radius_range_unit;
                    };
				}
			else
				if ( text == 0.25 )
					{
					if ( radius_range_unit == ' miles' )
						{
						text = '1/4 mile';
						}
					else
						{
						text = '1/4' + radius_range_unit;
						}
					}
				else
					if ( text == 0.5 )
						{
						if ( radius_range_unit == ' miles' )
							{
							text = '1/2 mile';
							}
						else
							{
							text = '1/2' + radius_range_unit;
							};
						}
					else
						if ( text == 1.0 )
							{
							if ( radius_range_unit == ' miles' )
								{
								text = '1 mile';
								}
							else
								{
								text = '1' + radius_range_unit;
								};
							}
						else
							{
							text += radius_range_unit;
							};
							
			marker_html += '<option value="'+value+'"';
			
			if ( main_radius == value )
				{
				marker_html += ' selected="selected"';
				}
			
			marker_html += '>'+text+'</option>';

			if ( main_radius > value )
				{
				var next_val = main_radius + 1;
				if ( c < (radius_range.length-1) ) next_val = radius_range[0];
				if ( main_radius < next_val )
					{
					text = main_radius+radius_range_unit;
					marker_html += '<option value="'+main_radius+'" selected="selected">'+text+'</option>';
					};
				};
			};
		
		marker_html += '</select>';
		marker_html += '</div>';

		if ( radius_range_unit == ' miles' )
			{
			my_radius = 'geo_width='+main_radius;
			}
		else
			{
			my_radius = 'geo_width_km='+main_radius;
			};
		marker_html += '<div class="marker_main_info_window_inner_div"><a class="c_comdef_map_list_link" href="'+list_link+'&amp;lat_val='+main_marker.getPoint().lat()+'&amp;long_val='+main_marker.getPoint().lng()+'&amp;'+my_radius+'" title="##LIST_LINK_TITLE##">##LIST_LINK_TEXT##</a></div>';
		marker_html += '</form>';
		marker_html += '</div>';
		main_marker.myHTML = marker_html;
		};
};

/*******************************************************************/
/** \brief	This creates the central marker on the map.
*/
function CreateCenterMarker (in_lat,	/**< A floating-point value. The latitude of the center of the map. */
							 in_lng		/**< A floating-point value. The longitude of the center of the map. */
							)
{
	var point = new GLatLng ( in_lat, in_lng );
	
	if ( point )
		{
		var center_icn = giconCenter;
		var filter_message = '##FILTER_MESSAGE##';
		if ( filter_message )
			{
			center_icn = giconCenterG;
			}
		var title_string = '##CENTER_TITLE##';
		main_marker = new GMarker(point, {draggable:true, icon:center_icn, title:title_string});
		if ( main_marker )
			{
			GEvent.addListener( main_marker, "dragstart", DragStart );
			GEvent.addListener( main_marker, "dragend", DragEnd );
			GEvent.addListener( main_marker, "click", function() { openMarkerInfoWindow(this); } );
			main_map.addOverlay( main_marker );
			CreateCenterMarkerHTML (filter_message);
			};
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
/** \brief	This creates a translucent circular overlay that represents
	the radius of the search.
*/
function CreateCircularOverlay ()
{
	if ( main_radius > 0 )
		{
		if ( main_marker )
			{
			ClearCircularOverlay ();
			point = new GLatLng ( main_marker.getPoint().lat(), main_marker.getPoint().lng() );
			
			if ( point )
				{
		        var	radius_range_unit = '##RADIUS_ARRAY_UNIT##';
				var size = (GetPixelsPerMile ( main_marker.getPoint() ) * main_radius) * 2.0;
				
				if ( radius_range_unit == 'km' )
				    {
				    size /= 1.609344;
				    }
				
				main_overlay = new EInsert ( point, "##IMAGE_DIR##/Circle.png", new GSize(size,size), main_map.getZoom() );
				main_map.addOverlay ( main_overlay );
				};
			};
		};
};

/*******************************************************************/
/** \brief	Clears all the current meeting markers.
*/
function ClearMarkers (	)
{
	main_map.closeInfoWindow();
	if ( main_allocated_markers )
		{
		for ( var c = 0; c < main_allocated_markers.length; c++ )
			{
			if ( main_allocated_markers[c] )
				{
				main_allocated_markers[c].hide();
				main_allocated_markers[c] = null;
				};
			};
		
		main_allocated_markers = null;
		};
};

/*******************************************************************/
/** \brief	This calls an AJAX function to fetch the search results
	and display them.
*/
function DisplayMapMeetings ()
{
	if ( main_radius > 0 )
		{
		if ( main_marker )
			{
			DisplayThrobber();
			var main_uri = '##MAIN_URI##';
			main_uri += '&long_val='+main_marker.getPoint().lng();
			main_uri += '&lat_val='+main_marker.getPoint().lat();
		    var	radius_range_unit = '##RADIUS_ARRAY_UNIT##';
            if ( radius_range_unit == 'km' )
                {
			    main_uri += '&geo_width_km='+main_radius;
                }
            else
                {
			    main_uri += '&geo_width='+main_radius;
                }
			main_uri = main_uri.replace(new RegExp ('\&amp;'),"&");
			main_uri = main_uri.replace(new RegExp ('^\/\/'),"/");
			SimpleAJAXCall ( main_uri, DisplayMapSearchCallback, 'GET', null, true );
			main_map.panTo ( main_marker.getPoint() );
			};
		
		};
};

/*******************************************************************/
/** \brief	This is the AJAX callback for the map search. The AJAX
	handler returns a JSON object with the basic meeting data.
	This creates a number of markers, each representing a meeting.
*/
function DisplayMapSearchCallback (	in_text,	/**< This is the text returned by the AJAX handler. It is a JSON object. */
									in_param_1,
									in_param_2,
									in_last_call
									)
{
	if ( in_last_call )
		{
		CreateCircularOverlay ();
		if ( in_text && (in_text != '0') )
			{
			g_last_callback_result = in_text;

            in_text = in_text.replace(/[\r\n]*/g, "");  // 'Orrible kludge to account for servers being naughty.
			eval ( 'var json_obj = '+in_text );
			if ( json_obj )
				{
				var	count_max = json_obj[0];
				
				if ( count_max )
					{
					var overlap_map = MapOverlappingMarkers ( json_obj );
	
					for ( var c = 0; c < overlap_map.length; c++ )
						{
						CreateMapMarker ( overlap_map[c] );
						};
					};
		
				main_map.panTo ( main_marker.getPoint() );
				};
			
			main_map.checkResize();
			};
	
		HideThrobber ();
		};
};

/*******************************************************************/
/** \brief	This creates a single meeting's marker on the map.
*/
function CreateMapMarker (	in_mtg_obj_array	/**< A meeting object array. */
							)
{
	if ( in_mtg_obj_array[0] )
		{
		var main_point = new GLatLng ( in_mtg_obj_array[0].lat, in_mtg_obj_array[0].lng );
		var	marker_html = '';
		var	tabs = new Array;
		var weekday = in_mtg_obj_array[0].weekday.value;
		var title_string = '';
		var weekday_ar = Array(weekday);
		
		for ( var c = 0; c < in_mtg_obj_array.length; c++ )
			{
			var in_mtg_obj = in_mtg_obj_array[c];
		
			var point = new GLatLng ( in_mtg_obj.lat, in_mtg_obj.lng );
			
			if ( point )
				{
				marker_html += '<div class="marker_info_window_div">';
				marker_html += '<div class="marker_line marker_line_name"><span class="marker_prompt">'+in_mtg_obj.name.prompt+'##MARKER_WINDOW_SEPARATOR##</span><span class="marker_value">'+in_mtg_obj.name.value+'</span></div>';
				marker_html += '<div class="marker_line"><span class="marker_prompt">'+in_mtg_obj.weekday.prompt+'##MARKER_WINDOW_SEPARATOR##</span><span class="marker_value">'+in_mtg_obj.weekday.value+'</span></div>';
				marker_html += '<div class="marker_line"><span class="marker_prompt">'+in_mtg_obj.time.prompt+'##MARKER_WINDOW_SEPARATOR##</span><span class="marker_value">'+in_mtg_obj.time.value+'</span></div>';
				marker_html += '<div class="marker_line"><span class="marker_prompt">'+in_mtg_obj.location.prompt+'##MARKER_WINDOW_SEPARATOR##</span><span class="marker_value">'+in_mtg_obj.location.value+'</span></div>';
				marker_html += '<div class="marker_line"><span class="marker_prompt">'+in_mtg_obj.town.prompt+'##MARKER_WINDOW_SEPARATOR##</span><span class="marker_value">'+in_mtg_obj.town.value+'</span></div>';

				if ( in_mtg_obj.formats.value )
					{
					marker_html += '<div class="marker_line"><span class="marker_prompt">'+in_mtg_obj.formats.prompt+'##MARKER_WINDOW_SEPARATOR##</span>';
					marker_html += '<span class="marker_value">'+in_mtg_obj.formats.value+'</span></div>';
					};
				marker_html += '<div class="marker_line"><a class="marker_more_info_a" title="'+in_mtg_obj.single_link.title+'" href="javascript:DisplayMeetingDetails('+in_mtg_obj.id+',\'##SINGLE_URI##\')">'+in_mtg_obj.single_link.text+'</a></div>';
				marker_html += '</div>';
				
				if ( in_mtg_obj_array.length == 1 )
					{
					title_string = weekday+': '+in_mtg_obj.name.value;
					}
				
				if ( (c == (in_mtg_obj_array.length -1)) || in_mtg_obj_array[c+1].weekday.value != weekday )
					{
					tabs[tabs.length] = new GInfoWindowTab ( weekday, marker_html );
					if ( c < in_mtg_obj_array.length -1 )
						{
						weekday = in_mtg_obj_array[c+1].weekday.value;
						weekday_ar[weekday_ar.length] = weekday;
						};
					
					meeting_cnt = 0;
					marker_html = '';
					};
				};
			};
		
		if ( in_mtg_obj_array.length > 1 )
			{
			title_string = weekday_ar.join(', ');
			}
		
		if ( !main_allocated_markers )
			{
			main_allocated_markers = new Array(new GMarker(main_point, {icon:((c>1)?g_multi_icon:gicon), title:title_string}));
			}
		else
			{
			main_allocated_markers[main_allocated_markers.length] = new GMarker(main_point, {icon:((c>1)?g_multi_icon:gicon), title:title_string});
			};
		
		main_allocated_markers[main_allocated_markers.length-1].myTabs = tabs;
		GEvent.addListener( main_allocated_markers[main_allocated_markers.length-1], "click", function() { openMarkerInfoWindow(this); } );
		main_map.addOverlay(main_allocated_markers[main_allocated_markers.length-1]);
		};
};

/*******************************************************************/
/** \brief	This returns an array, mapping out markers that overlap.

	\returns An array of arrays. Each array element is an array with
	n >= 1 elements, each of which is a meeting object. Each of the
	array elements corresponds to a single marker, and all the objects
	in that element's array will be covered by that one marker. The
	returned sub-arrays will be sorted in order of ascending weekday.
*/
function MapOverlappingMarkers (in_meeting_array	/**< An array of JSON objects that will be sorted. */
								)
{
	var tolerance = 8;	/* This is how many pixels we allow. */
	var tmp = new Array;
	
	for ( var c = 0; c < in_meeting_array.length-1; c++ )
		{
		tmp[c] = new Object;
		tmp[c].matched = false;
		tmp[c].matches = null;
		tmp[c].object = in_meeting_array[c+1];
		tmp[c].coords = main_map.fromLatLngToContainerPixel ( new GLatLng ( tmp[c].object.lat, tmp[c].object.lng ) );
		};
	
	for ( var c = 0; c < tmp.length; c++ )
		{
		if ( false == tmp[c].matched )
			{
			tmp[c].matched = true;
			tmp[c].matches = new Array;
			tmp[c].matches[0] = tmp[c].object;

			for ( var c2 = 0; c2 < tmp.length; c2++ )
				{
				if ( c2 != c )
					{
					var outer_coords = tmp[c].coords;
					var inner_coords = tmp[c2].coords;
					
					var xmin = outer_coords.x - tolerance;
					var xmax = outer_coords.x + tolerance;
					var ymin = outer_coords.y - tolerance;
					var ymax = outer_coords.y + tolerance;
					
					/* We have an overlap. */
					if ( (inner_coords.x >= xmin) && (inner_coords.x <= xmax) && (inner_coords.y >= ymin) && (inner_coords.y <= ymax) )
						{
						tmp[c].matches[tmp[c].matches.length] = tmp[c2].object;
						tmp[c2].matched = true;
						};
					};
				};
			};
		};

	var ret = new Array;
	
	for ( var c = 0; c < tmp.length; c++ )
		{
		if ( tmp[c].matches )
			{
			tmp[c].matches.sort ( function(a,b){return a.sortindex-b.sortindex});
			ret[ret.length] = tmp[c].matches;
			};
		};
	
	return ret;
};

/*******************************************************************/
/** \brief	This simply opens whatever window was assigned to a marker.
*/
function openMarkerInfoWindow ( in_marker )
{
	if ( in_marker.myHTML )
		{
		in_marker.openInfoWindowHtml(in_marker.myHTML);
		}
	else
		{
		in_marker.openInfoWindowTabs(in_marker.myTabs);
		}
};

/*******************************************************************/
/**
 * \brief Calculate destination point given start point lat/long (numeric degrees), 
 * bearing (numeric degrees) & distance (in m).
 *
 * from: Vincenty direct formula - T Vincenty, "Direct and Inverse Solutions of Geodesics on the 
 *			Ellipsoid with application of nested equations", Survey Review, vol XXII no 176, 1975
 *			www.ngs.noaa.gov/PUBS_LIB/inverse.pdf
 *
 *	\returns a GLatLng, containing the new point, as determined by distance and bearing.
 *
 * This came from here: www.movable-type.co.uk/scripts/latlong-vincenty-direct.html
 */
function destVincenty (	inGLatLng,	/**< The GLatLng for a point. */
						dist,		/**< The distance to be calculated (Defaults to 100 Km) */
						brng		/**< The bearing to the next point, in degrees (defaults to 90 degrees -parallel to Equator) */
						)
{
	if ( !dist )
		{
		dist = 100000;
		};
	
	if ( !brng )
		{
		brng = 90.0;
		};
		
	var lat1 = inGLatLng.lat();
	var lon1 = inGLatLng.lng();
	var a = 6378137, b = 6356752.3142,	f = 1/298.257223563;	/* WGS-84 ellipsiod */
	var s = dist;
	var alpha1 = brng/57.2957795131;
	var sinAlpha1 = Math.sin(alpha1), cosAlpha1 = Math.cos(alpha1);
	var tanU1 = (1-f) * Math.tan(lat1/57.2957795131);
	var cosU1 = 1 / Math.sqrt((1 + tanU1*tanU1)), sinU1 = tanU1*cosU1;
	var sigma1 = Math.atan2(tanU1, cosAlpha1);
	var sinAlpha = cosU1 * sinAlpha1;
	var cosSqAlpha = 1 - sinAlpha*sinAlpha;
	var uSq = cosSqAlpha * (a*a - b*b) / (b*b);
	var A = 1 + uSq/16384*(4096+uSq*(-768+uSq*(320-175*uSq)));
	var B = uSq/1024 * (256+uSq*(-128+uSq*(74-47*uSq)));
	var sigma = s / (b*A), sigmaP = 2*Math.PI;
	
	while (Math.abs(sigma-sigmaP) > 1e-12)
		{
		var cos2SigmaM = Math.cos(2*sigma1 + sigma);
		var sinSigma = Math.sin(sigma), cosSigma = Math.cos(sigma);
		var deltaSigma = B*sinSigma*(cos2SigmaM+B/4*(cosSigma*(-1+2*cos2SigmaM*cos2SigmaM)-B/6*cos2SigmaM*(-3+4*sinSigma*sinSigma)*(-3+4*cos2SigmaM*cos2SigmaM)));
		sigmaP = sigma;
		sigma = s / (b*A) + deltaSigma;
		};

	var tmp = sinU1*sinSigma - cosU1*cosSigma*cosAlpha1;
	var lat2 = Math.atan2(sinU1*cosSigma + cosU1*sinSigma*cosAlpha1, (1-f)*Math.sqrt(sinAlpha*sinAlpha + tmp*tmp));
	var lambda = Math.atan2(sinSigma*sinAlpha1, cosU1*cosSigma - sinU1*sinSigma*cosAlpha1);
	var C = f/16*cosSqAlpha*(4+f*(4-3*cosSqAlpha));
	var L = lambda - (1-C) * f * sinAlpha * (sigma + C*sinSigma*(cos2SigmaM+C*cosSigma*(-1+2*cos2SigmaM*cos2SigmaM)));

	var revAz = Math.atan2(sinAlpha, -tmp);	/** final bearing */

	return new GLatLng (lat2*57.2957795131, lon1+(L*57.2957795131));
};

/**
*/
function GetPixelsPerMile(	inPoint	/**< If this is not given, the map center is used. */
							)
{
	var ret = null;
	
	if ( main_map )
		{
		if ( !inPoint )
			{
			inPoint = main_map.getCenter();
			};
	
		if ( inPoint )
			{
			var pos1 = main_map.fromLatLngToDivPixel ( inPoint );
			var pos2 = main_map.fromLatLngToDivPixel ( destVincenty ( inPoint ) );
			
			var totalPixels = Math.abs ( pos1.x - pos2.x );	/* Total pixels for 100 Km */
			ret = totalPixels / 62.1371192;	/* Pixels per mile */
			};
		};

	return ret;
};