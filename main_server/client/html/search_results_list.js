/***********************************************************************/
/**	\file	search_results_list.js

	\brief	This file will be optimized and embedded in the HTML that is
	returned for a meeting search list result and for a map result.

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
var gicon = new GIcon();

/// Safari requires that the map be loaded at the end of the page load, so these are semaphores.
var	map_lat = 0;
var map_lng = 0;

/** This is an icon for a single NA meeting (Default is a "blue balloon"). */
gicon.image = "##IMAGE_DIR##/NAMarker.png";
gicon.printImage = "##IMAGE_DIR##/NAMarker.gif";
gicon.mozPrintImage = "##IMAGE_DIR##/NAMarker.png";
gicon.iconSize = new GSize(23, 32);
gicon.shadow = "##IMAGE_DIR##/NAMarkerS.png";
gicon.shadowSize = new GSize(37, 32);
gicon.iconAnchor = new GPoint(12, 32);
gicon.infoWindowAnchor = new GPoint(12, 0);
gicon.transparent = "##IMAGE_DIR##/NAMarkerX.png";
gicon.imageMap = [11,0,18,2,23,8,23,15,11,32,0,14,0,7,4,0];

/** This is one for multiple meetings. */
var g_multi_icon = new GIcon ( gicon );
g_multi_icon.image = "##IMAGE_DIR##/NAMarkerG.png";

var giconCenter = new GIcon();

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

/** This one is displayed when we are viewing a filtered search. */
var giconCenterG = new GIcon ( giconCenter );
giconCenterG.image = "##IMAGE_DIR##/NACenterMarkerG.png";

/*******************************************************************/
/** \brief This function computes the current window scroll position.
	It is used to determine where to vertically orient the "throbber"
	and the single meeting "window" displays.
	
	\returns An object, with two data members: x (horiz) and y (vert),
	reflecting the current browser window's scroll position, in window
	coordinates (entire document, not just the displayed area).
*/
function GetScrollPos ()
{
	var x = 0;
	var y = 0;

	if( typeof( window.pageYOffset ) == 'number' )
		{
		x = window.pageXOffset;
		y = window.pageYOffset;
		}
	else if( document.documentElement && document.documentElement.scrollLeft )
		{
		x = document.documentElement.scrollLeft;
		y = document.documentElement.scrollTop;
		}
	else if( document.body && document.body.scrollLeft )
		{
		x = document.body.scrollLeft;
		y = document.body.scrollTop;
		};
	
	var position = { 'x' : x, 'y' : y };

	return position;
};

/*******************************************************************/
/** \brief	Gets the left side position, in absolute pixels, of a given
	DOM object.
	
	\returns An integer, the left side coordinates, in window
	coordinates (entire document, not just the displayed area).
*/
function findPosX ( obj	/**< A reference to a DOM object. The object for which we are getting the position. */
					)
{
	var curleft = 0;
	if ( obj.offsetParent )
		{
		while ( obj.offsetParent )
			{
			curleft += obj.offsetLeft;
			obj = obj.offsetParent;
			};
		}
	else if(obj.x) curleft += obj.x;
	
	return curleft;
};

/*******************************************************************/
/** \brief	Gets the top side position, in absolute pixels, of a given
	DOM object.
	
	\returns An integer, the top side coordinates, in window
	coordinates (entire document, not just the displayed area).
*/
function findPosY ( obj	/**< A reference to a DOM object. The object for which we are getting the position. */
					)
{
	var curtop = 0;
	if(obj.offsetParent)
		{
		while ( obj.offsetParent )
			{
			curtop += obj.offsetTop;
			obj = obj.offsetParent;
			};
		}
	else if(obj.y) curtop += obj.y;
	
	return curtop;
};

/*******************************************************************/
/** \brief Returns the outside dimensions of the current browser
	window, in pixels.
	
	\returns An object, containing two data members: 'height', for
	the window height in pixels, and 'width', for its width.
*/
function GetWindowSize ()
{
	var myWidth = 0;
	var myHeight = 0;
	
	if( typeof( window.innerWidth ) == 'number' )
		{
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
		}
	else if( document.documentElement && document.documentElement.clientWidth )
		{
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
		}
	else if( document.body && document.body.clientWidth )
		{
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
		};
	
	var mySize = { 'height' : myHeight, 'width' : myWidth };
	
	return mySize;
};

/*******************************************************************/
/** \brief Simply hides the "throbber" div.
*/
function HideThrobber ()
{
	var	elem = document.getElementById('c_comdef_search_results_single_ajax_throbber_div');
	if ( elem ) elem.style.display = 'none';
};

/*******************************************************************/
/** \brief Displays and centers the "throbber" div on the screen.
*/
function DisplayThrobber ()
{
	var	elem = document.getElementById('c_comdef_search_results_single_ajax_throbber_div');
	if ( elem )
		{
		elem.style.display = 'block';
		};
};

/*******************************************************************/
/** \brief	This function is called when the single meeting data is
	displayed. It fills the meeting's map square with a Google Map.
*/
function MakeMainMap ()
{
	var point = new GLatLng ( map_lat, map_lng );
	
	if ( point )
		{
		var elem_id = 'main_map_id_div';
		var elem = document.getElementById ( elem_id );
		if ( elem )
			{
			elem.className = 'main_map_div';
			var width = elem.offsetWidth;
			var height = elem.offsetHeight;
			var	mapSize = new GSize ( width, height );
			var map = new GMap2(elem, { size: mapSize });
			
			if ( map )
				{
				if ( width < 400 )
					{
					map.addControl(new GSmallZoomControl3D());
					}
				else
					{
					map.addControl(new GLargeMapControl());
					map.addControl(new GMapTypeControl());
					};
					
				map.enableScrollWheelZoom();
				map.enableContinuousZoom();
				
				map.setCenter(point, 15);
				var marker = new GMarker(point, {icon: giconCenter, clickable: false, draggable: false});
				map.addOverlay(marker);
				map.checkResize();
				map.panTo ( marker.getPoint() );
				};
			}
		};
};

/*******************************************************************/
/** \brief	This "closes" the single meeting details "window." It
	also sets the background to allow printing.
*/
function HideMeetingDetails ( )
{
	var div = document.getElementById ( 'bmlt_contact_us_form_div' );
	
	/*	This is a bit of funky UI. The big red X looks like it should close the
		contact form, so we make it do that. */
	if ( div && (div.style.display != 'none') )
		{
		div.style.display = 'none';
		}
	else
		{
		var	elem = document.getElementById('c_comdef_search_results_single_ajax_div');
		if ( elem )
			{
			elem.innerHTML = '';
			elem.style.display = 'none';
			if ( document.getElementById ( 'c_comdef_search_results_list_div' ) )
				{
				document.getElementById ( 'c_comdef_search_results_list_div' ).className = 'c_comdef_search_results_div';
				}
			else
				{
				if ( document.getElementById ( 'c_comdef_search_results_map_div' ) )
					{
					document.getElementById ( 'c_comdef_search_results_map_div' ).className = 'c_comdef_search_results_map_div';
					};
				};
			};
		};
};

/*******************************************************************/
/** \brief	This calls an AJAX function to fetch the details for a
	meeting and display it on the screen.
*/
function DisplayMeetingDetails (in_ID,	/**< An integer. The meeiting ID. */
								in_loc,	/**< A string. The URI of the AJAX handler file on the server. */
								in_id_call		/**< An integer with the meeting's ID. If provided, the edit call is made with the ID. */
								)
{
	DisplayThrobber();
	var	elem = document.getElementById('c_comdef_search_results_edit_hidden_div');
	
	if ( elem )
		{
		elem.style.display = 'none';
		};
	var uri = in_loc+"&single_meeting_id="+in_ID;
	uri = uri.replace(new RegExp ('^\/\/'),"/");
	SimpleAJAXCall ( uri, DisplayMeetingDetailsCallback, 'GET', in_id_call, true );
};

/*******************************************************************/
/** \brief	This is the AJAX callback for the single meeting detail display.
	It's where most of the work is done. The meeting HTML is contained
	in the text, but it is preceded by two floating point numbers, separated
	by '##$##'. These are the longitude and latitude of the meeting, and
	we need to parse them out first, in order to set the map. We then send
	the rest of the text to the div we have set aside for it, and we position
	that div so that it is at the top of the screen, no matter where we are.
	We also set the background to not print, so that printing will show only
	the meeting details.
*/
function DisplayMeetingDetailsCallback (in_text,	/**< This is the text returned by the AJAX handler. It is segregated into 3 parts, separated by '##$##'. */
										in_id		/**< An integer with the meeting's ID. If provided, the edit call is made with the ID. */
										)
{
	var	elem = document.getElementById('c_comdef_search_results_single_ajax_div');
	if ( elem )
		{
		var arr = in_text.split( '##$##' );
		map_lat = parseFloat ( arr[1] );
		map_lng = parseFloat ( arr[2] );
		var pos = GetScrollPos ( );
		elem.innerHTML = arr[3];
		elem.style.top = pos.y+'px';
		HideThrobber();
		elem.style.display = 'block';

		MakeMainMap ();
		if ( document.getElementById ( 'c_comdef_search_results_list_div' ) )
			{
			document.getElementById ( 'c_comdef_search_results_list_div' ).className = 'c_comdef_search_results_div no_print';
			}
		else
			{
			if ( document.getElementById ( 'c_comdef_search_results_map_div' ) )
				{
				document.getElementById ( 'c_comdef_search_results_map_div' ).className = 'c_comdef_search_results_map_div no_print';
				};
			if ( in_id )
				{
				EditMeetingLink ( in_id );
				}
			};
		};
};

/*******************************************************************/
/** \brief	This function sends the visitor to the edit page for the given meeting.
*/
function EditMeetingLink ( in_meeting_id	/**< An integer. The ID of the meeting to edit. */
						)
{
	DisplayThrobber();
	if ( CloseMap )
		{
		CloseMap();
		};
	
	var uri = '##EDIT_URI##?single_meeting_id='+in_meeting_id;

	HideMeetingDetails ( );
	SimpleAJAXCall ( uri, EditMeetingLinkCallback, 'GET', in_meeting_id, true );
};

/*******************************************************************/
/** \brief	This function sends the visitor to the edit page for the given meeting.
*/
function EditMeetingLinkCallback ( in_text,	/**< This is the HTML to display. */
									in_meeting_id	/**< The ID of the meeting */
								)
{
	if ( in_text )
		{
		var elem = document.getElementById ( 'c_comdef_search_results_edit_hidden_div' );

		if ( elem )
			{
			HideThrobber();
			elem.innerHTML = in_text;
			elem.style.display = 'block';
			SortFormats(in_meeting_id);
			}
		}
	else
		{
		alert ( 'ERROR: Cannot Edit' );
		};
};

/*******************************************************************/
/** \brief	This function displays the meeting contact form wait window (Blank, with throbber).
*/
function ContactUsWait ()
{
	var div = document.getElementById ( 'bmlt_contact_us_form_div' );
	div.innerHTML = '<div class="bmlt_contact_form_wait"><img class="bmlt_contact_form_generic_throbber" src="##IMAGE_DIR##/GenericThrobber.gif" /</div>';
	div.style.display = 'block';
}

/*******************************************************************/
/** \brief	This function displays the meeting contact form.
*/
function ContactUsLink ( in_meeting_id,			/**< An integer. The ID of the meeting to edit. */
						 in_single_meeting_uri	/**< A string. The URI of the single meeting on the root server. */
						)
{
	ContactUsWait ();
	
	var uri = '##CONTACT_URI##'+in_meeting_id+'&meeting_uri='+in_single_meeting_uri;
	SimpleAJAXCall ( uri, ContactUsLinkCallback, 'GET', null, true );
};

/*******************************************************************/
/** \brief	This function responds with the contact form, and fills in the div.
*/
function ContactUsLinkCallback ( in_text	/**< This is the HTML to display. */
								)
{
	var div = document.getElementById ( 'bmlt_contact_us_form_div' );
	
	if ( div )
		{
		if ( in_text )
			{
			var arr = in_text.split( '##$##' );
			
			if ( arr.length < 2 )
				{
				arr[1] = arr[0];
				arr[0] = 'bmlt_contact_form_name';
				};
			
			div.innerHTML = arr[1].toString().replace ( '/', '\/' );
			var focus_elem = document.getElementById(arr[0]);
			if ( focus_elem )
				{
				focus_elem.focus();
				};
			}
		else
			{
			div.style.display = 'none';
			};
		};
};

/*******************************************************************/
/** \brief	This function sends the contact us message.
*/
function SubmitContact ()
{
	var uri = '##CONTACT_URI##&submit_contact_form';

	var name_field = document.getElementById ( 'bmlt_contact_form_name' );
	var email_field = document.getElementById ( 'bmlt_contact_form_email' );
	var subject_field = document.getElementById ( 'bmlt_contact_form_subject' );
	var message_field = document.getElementById ( 'bmlt_contact_form_message' );
	var mtg_id_field = document.getElementById ( 'contact_us_meeting_id' );
	var sb_id_field = document.getElementById ( 'contact_us_service_body_id' );
	
	uri += '&bmlt_contact_form_name='+escape ( name_field.value );
	uri += '&bmlt_contact_form_email='+escape ( email_field.value );
	uri += '&bmlt_contact_form_subject='+escape ( subject_field.value );
	uri += '&bmlt_contact_form_message='+escape ( message_field.value );
	uri += '&meeting_id='+escape ( mtg_id_field.value );
	uri += '&service_body_id='+escape ( sb_id_field.value );
	
	/* These are the honeypot fields */
	uri += '&reply_header='+escape (document.getElementById ( 'bmlt_contact_reply_header' ).value );
	uri += '&extra_headers='+escape (document.getElementById ( 'bmlt_contact_extra_headers' ).value );
	uri += '&to_email_address='+escape (document.getElementById ( 'bmlt_contact_to_email_address' ).value );
	uri += '&cc_email_address='+escape (document.getElementById ( 'bmlt_contact_cc_email_address' ).value );
	uri += '&bc_email_address='+escape ( document.getElementById ( 'bmlt_contact_bc_email_address' ).value );

	ContactUsWait ();
	SimpleAJAXCall ( uri, ContactUsLinkCallback, 'GET', null, true );
};
