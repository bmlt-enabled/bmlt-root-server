<?php
/***********************************************************************/
/** \file	search_specification.php

	\brief	This file will output HTML to used as a basic search specification.
	It will display a string search (that can also be an address), and, if
	the browser can handle it, a map that can be clicked.

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

defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

/*******************************************************************/
/** \brief This function is called in order to construct the HTML
	for a search specification screen.
	
	\returns a string. The HTML for the search form.
*/
function DisplayMeetingSearchForm ( $in_http_vars ///< The $_GET and $_POST parameters, in an associative array.
									)
{
	$ret = '';
	
	if ( !isset ( $in_http_vars['script_name'] ) || !$in_http_vars['script_name'] )
		{
		$script_name = preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] );
		}
	else
		{
		$script_name = $in_http_vars['script_name'];
		}
	
	if ( !isset ( $in_http_vars['bmlt_root'] ) || !$in_http_vars['bmlt_root'] )
		{
		$in_http_vars['bmlt_root'] = 'http://'.$_SERVER['SERVER_NAME'].dirname ( $_SERVER['SCRIPT_NAME'] ).'/../';
		}

	$in_http_vars['script_name'] = $script_name;
	$action = $in_http_vars['script_name'];
	
	// This is the basic search manager class. It will do most of the work.
	require_once ( dirname ( __FILE__ ).'/../../client/c_comdef_meeting_search_manager.class.php' );
	$search_manager = new c_comdef_meeting_search_manager;
	
	$localized_strings = c_comdef_server::GetLocalStrings();

	$in_http_vars['bmlt_root'] = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root'] );
	if ( $in_http_vars['bmlt_root'] == '/' )
		{
		$in_http_vars['bmlt_root'] = '';
		}
	
	$location_of_images = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root']."themes/".$localized_strings['theme']."/html/images" );
	$location_of_throbber = "$location_of_images/Throbber.gif";
		
	$query = null;
	
	foreach ( $in_http_vars as $key => $value )
		{
		switch ( $key )
			{
			case	'bmlt_root':
			case	'disp_format':
			case	'geo_width':
			case	'lat_val':
			case	'long_val':
			case	'switcher':
			case	'supports_ajax':
			case	'do_search':
			case	'search_form':
			case	'single_meeting_id':
			case	'search_spec_map_center':
			break;
			
			default:
				if ( $query )
					{
					$query .= "&";
					}
					
				if ( is_array ( $value ) )
					{
					foreach ( $value as $v )
						{
						$query .= c_comdef_htmlspecialchars ( $key )."[]=".urlencode ( $v );
						}
					}
				elseif ( $value )
					{
					$query .= c_comdef_htmlspecialchars ( $key ). "=".urlencode ( $value );
					}
				else
					{
					$query .= c_comdef_htmlspecialchars ( $key );
					}
			break;
			}
		}
	
	if ( $query )
		{
		$query = "?$query&";
		}
	else
		{
		$query = '?';
		}

	$ret .= '<form class="c_comdef_search_specification_form" action="#" method="get" onsubmit="return SubmitHandler()">';
		$ret .= '<div id="c_comdef_search_specification_form_div" class="c_comdef_search_specification_form_div">';
			$ret .= '<fieldset class="where_am_i_fieldset" id="where_am_i_fieldset" style="display:none">';
				$ret .= '<legend>';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_where_am_i_button'] );
				$ret .= '</legend>';
				$ret .= '<input type="button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_submit_value'] ).'" onclick="WhereAmI()" />';
			$ret .= '</fieldset>';
			$ret .= '<fieldset class="address_lookup_fieldset">';
				$ret .= '<legend>';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['address_lookup_fieldset'] );
				$ret .= '</legend>';
				$ret .= '<label for="entered_address">';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['address_lookup_label'] ).__PROMPT_DELIMITER__;
				$ret .= '</label>';
				$ret .= '<input type="text" id="entered_address" class="entered_address" />';
				$ret .= '<input type="button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_submit_value'] ).'" onclick="AddressEntered()" onkeyup="last_field=this.id" onchange="last_field=this.id" />';
			$ret .= '</fieldset>';
			$ret .= '<fieldset class="string_lookup_fieldset">';
				$ret .= '<legend>';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['string_lookup_fieldset'] );
				$ret .= '</legend>';
				$ret .= '<label for="entered_string">';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['string_lookup_label'] ).__PROMPT_DELIMITER__;
				$ret .= '</label>';
				$ret .= '<input type="text" id="entered_string" class="entered_string" />';
				$ret .= '<input type="button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_submit_value'] ).'" onclick="StringEntered()" onkeyup="last_field=this.id" onchange="last_field=this.id" />';
			$ret .= '</fieldset>';
			$ret .= '<fieldset class="weekday_fieldset">';
				$ret .= '<legend>';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['small_weekdays'] );
				$ret .= '</legend>';
				
				for ( $i = 0; $i < 7; $i++ )
					{
					$weekday = $localized_strings['weekdays'][$i];
					$ret .= '<div class="bmlt_search_one_weekday_div">';
						$ret .= '<input id="weekday_check_'.c_comdef_htmlspecialchars($i).'" type="checkbox" value="'.c_comdef_htmlspecialchars($i+1).'" />';
						$ret .= '<label class="bmlt_spec_label_weekday" for="weekday_check_'.c_comdef_htmlspecialchars($i).'" class="bmlt_check_label">'.c_comdef_htmlspecialchars($weekday).'</label>';
					$ret .= '</div>';
					}
			$ret .= '</fieldset>';
		$ret .= '</div>';
	$ret .= '</form>';
	$ret .= '<div id="throbber_div" class="throbber_div" style="display:none"><img src="'.c_comdef_htmlspecialchars ( $location_of_throbber ).'" alt="throbber" /></div>';
	$script = file_get_contents ( dirname ( __FILE__ ).'/search_form.js' );
	$script = str_replace( "##SCRIPT_URL##", $script_name.$query.'small_screen=yes&supports_ajax=yes&do_search=yes&geo_width=-'.intval ( $localized_strings['number_of_meetings_for_auto'] ), $script );
	$script = str_replace( "##SCRIPT_URL_2##", $script_name.$query.'small_screen=yes&supports_ajax=yes&do_search=yes', $script );
	$script = str_replace( "##FAILED_LOOKUP##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['lookup_failed_alert'] ), $script );
	$ret .= '<script type="text/javascript">'.$script.'</script>';
	
	return optimizeReturn ( $ret );
}

?>
