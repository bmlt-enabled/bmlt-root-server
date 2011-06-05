/*
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

var g_last_field = null;
var g_geo = null;
	
function WhereAmI_TryAgain_Google()
{
	g_geo.getCurrentPosition(WhereAmI_CallBack, WhereAmI_Fail_Final);
};

function WhereAmI_TryAgain_w3c()
{
	navigator.geolocation.getCurrentPosition(WhereAmI_CallBack,WhereAmI_Fail_Final);
};

function WhereAmI_CallBack(in_position
							)
{
	var in_weekdays = ReadWeekdays();
	var advanced_stuff = '';
	if ( in_weekdays )
		{
		advanced_stuff = '&bmlt_search_type=advanced&advanced_search_mode=1';
		for ( var i = 0; i < in_weekdays.length; i++ )
			{
			advanced_stuff += '&advanced_weekdays[]='+in_weekdays[i];
			};
		};
	
	if ( !in_position.coords )
		{
		var coords = {'longitude':in_position.longitude, 'latitude':in_position.latitude};
		
		in_position.coords = coords;
		};
	
	uri='##SCRIPT_URL##'+advanced_stuff+'&long_val='+in_position.coords.longitude.toString()+'&lat_val='+in_position.coords.latitude.toString()+'&disp_format=force_list';
	window.location.href=uri;
};

function WhereAmI_Fail_Final()
{
	document.getElementById ( 'throbber_div' ).style.display='none';
	document.getElementById ( 'c_comdef_search_specification_form_div' ).style.display='block';
	alert ( '##FAILED_LOOKUP##' );
};

function WhereAmI()
{
	ShowThrobber();
	if( typeof ( navigator ) == 'object' && typeof ( navigator.geolocation ) == 'object' )
		{
		navigator.geolocation.getCurrentPosition(WhereAmI_CallBack,WhereAmI_TryAgain_w3c,{maximumAge:300000,timeout:0});
		}
	else
		{
		if( typeof ( google ) == 'object' && typeof ( google.gears ) == 'object' )
			{
			if ( !g_geo )
				{
				g_geo = google.gears.factory.create('beta.geolocation');
				};
			
			g_geo.getCurrentPosition(WhereAmI_CallBack, WhereAmI_TryAgain_Google);
			};
		}
};

function ShowThrobber()
{
	document.getElementById ( 'c_comdef_search_specification_form_div' ).style.display='none';
	document.getElementById ( 'throbber_div' ).style.display='block';
};

function ReadWeekdays()
{
	var in_weekdays = null;
	
	for ( var i = 0; i < 7; i++ )
		{
		if ( document.getElementById ( 'weekday_check_'+i ).checked )
			{
			if ( !in_weekdays )
				{
				in_weekdays = new Array();
				};
			
			in_weekdays[in_weekdays.length] = i+1;
			};
		};
	
	return in_weekdays;
};

function AddressEntered()
{
	var in_weekdays = ReadWeekdays();
	var advanced_stuff = 'basic';
	if ( in_weekdays )
		{
		advanced_stuff = 'advanced&advanced_search_mode=1';
		for ( var i = 0; i < in_weekdays.length; i++ )
			{
			advanced_stuff += '&advanced_weekdays[]='+in_weekdays[i];
			};
		};
	ShowThrobber();
	uri='##SCRIPT_URL_2##&StringSearchIsAnAddress=1&do_search=yes&disp_format=force_list&bmlt_search_type='+advanced_stuff+'&SearchString='+encodeURI(document.getElementById ( 'entered_address' ).value.toString());
	window.location.href=uri;
};

function StringEntered()
{
	var in_weekdays = ReadWeekdays();	
	var advanced_stuff = 'basic';
	if ( in_weekdays )
		{
		advanced_stuff = 'advanced&advanced_search_mode=1';
		for ( var i = 0; i < in_weekdays.length; i++ )
			{
			advanced_stuff += '&advanced_weekdays[]='+in_weekdays[i];
			};
		};
	ShowThrobber();
	uri='##SCRIPT_URL_2##&do_search=yes&disp_format=force_list&bmlt_search_type='+advanced_stuff+'&SearchString='+encodeURI(document.getElementById ( 'entered_string' ).value.toString());
	window.location.href=uri;
};

function SubmitHandler()
{
	if ( g_last_field = 'entered_address' )
		{
		AddressEntered();
		}
	else
		{
		if ( g_last_field = 'entered_string' )
			{
			StringEntered();
			}
		}
	
	return false;
};

if( (typeof ( navigator ) == 'object' && typeof ( navigator.geolocation ) == 'object') || (typeof ( google ) == 'object' && typeof ( google.gears ) == 'object') )
	{
	document.getElementById ( 'where_am_i_fieldset' ).style.display = 'block';
	};
		
