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
//define ( '__DEBUG_MODE__', 1 ); // Uncomment to make the JavaScript easier to trace (and less efficient).

/*******************************************************************/
/** \brief This function is called in order to construct the HTML
	for a search specification screen.
	
	\returns a string. The HTML for the search form.
*/
function DisplayMeetingSearchForm ( $in_http_vars ///< The $_GET and $_POST parameters, in an associative array.
									)
{
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

	// These can be changed in the auto config.
	include ( dirname ( __FILE__ ).'/../../server/config/auto-config.inc.php' );
	$in_http_vars['bmlt_root'] = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root'] );
	if ( $in_http_vars['bmlt_root'] == '/' )
		{
		$in_http_vars['bmlt_root'] = '';
		}
	$location_of_images = preg_replace ( '#(^\/+)|(\/+$)#', '/', $in_http_vars['bmlt_root']."themes/".$localized_strings['theme']."/html/images" );
	$location_of_throbber = "$location_of_images/Throbber.gif";

	// The default configuration is to start with the string search.
	$map_disp = 'none';
	$text_disp = 'block';
	$map_title = $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_map_disclose_title_invis'];
	$map_text = $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_map_search_title_a'];
	$map_class = 'c_comdef_search_specification_map_invis_a';
	
	// If the browser is capable, and the basic configuration wants a map as default, we start with a map, instead.
	if ( !(isset ( $in_http_vars['start_view']) && ($in_http_vars['start_view'] == 'text')) && (isset ( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') && (($default_basic_search == 'map') || ($in_http_vars['start_view'] == 'map'))) )
		{
		$map_disp = 'block';
		$text_disp = 'none';
		$map_title = $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_map_disclose_title_vis'];
		$map_text = $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_search_title_a'];
		$map_class = 'c_comdef_search_specification_map_vis_a';
		}
	
	// I know, I know. Tables are the tools of Satan, yadda, yadda. This allows us to exert more control over the presentation. Tables are very robust. Since the system is designed to be embedded, we need as many hooks as possible.
	$ret = '<table id="bmlt_search_spec_table" class="bmlt_search_spec_table" cellpadding="0" cellspacing="0"><thead style="display:none"><tr><td>Search Specification Form</td></tr></thead><tbody class="bmlt_search_spec_tbody"><tr class="bmlt_search_spec_tr"><td class="bmlt_search_spec_td">';
	// This is the container for the whole kit and kaboodle.
	$ret .= '<div id="c_comdef_search_specification_container_div" class="c_comdef_search_specification_container_div">';
	// This contains everything, and allows a smaller box than 100%, as well as styling.
	$ret .= '<div id="c_comdef_search_specification_form_container_div" class="c_comdef_search_specification_form_container_div">';
	// This is the label, at the top of the form.
	$ret .= '<div id="c_comdef_search_specification_form_header_div" class="c_comdef_search_specification_form_header_div';

    if ( $map_disp == 'block' )
        {
        $ret .= ' hidden_element_mode';
        }
	    
	$ret .= '">';
	if ( isset ( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		$ret .= '<a class="bmlt_spec_tab_selected" id="bmlt_spec_tab_basic_id" href="javascript:DisplaySearchSpecification()">';
		}
	$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_basic_search_title'] );
	if ( isset ( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		$ret .= '</a>';
		$ret .= '<a class="bmlt_spec_tab" id="bmlt_spec_tab_advanced_id" href="javascript:DisplaySearchSpecification(\'advanced\')">';
		$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_advanced_search_title'] );
		$ret .= '</a>';
		}
		
	$ret .= '</div>';
	// The string search is contained in a form.
	$ret .= '<form class="c_comdef_search_specification_form" action="'.c_comdef_htmlspecialchars ( $action ).'" method="get" onsubmit="if(!document.getElementById(\'result_type_advanced\')||(document.getElementById(\'result_type_advanced\').value==\'force_list\'))ShowThrobber();return true">';
	// If the browser supports AJAX, we let the next handler know that.
	// This is the string search div.
	$ret .= '<div id="c_comdef_search_specification_string_div" class="c_comdef_search_specification_div">';
	if ( isset ( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		$ret .= '<input type="hidden" name="supports_ajax" value="yes" />';
		}
	// This tells the handler to execute a search.
	$ret .= '<input type="hidden" name="do_search" value="yes" />';
	// If the string search is hidden, it is done at this point.
	$ret .= '<div style="display:'.$text_disp.'" class="c_comdef_search_specification_one_line search_string_line" id="c_comdef_search_specification_search_string_line">';
	$ret .= '<label for="c_comdef_search_specification_search_string">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['text_input_prompt'] ).$localized_strings['prompt_delimiter'].'</label>';
	$ret .= '<input class="c_comdef_search_specification_search_string_text" type="text" name="SearchString" id="c_comdef_search_specification_search_string" value="" />';
	$ret .= '<input type="hidden" id="SearchStringAll_input" name="SearchStringAll" value="';
	$ret .= '1';
	$ret .= '" />';	// We will use all words for simple search.
	$ret .= '<input type="hidden" id="bmlt_search_type" name="bmlt_search_type" value="';
	$ret .= 'basic';
	$ret .= '" />';	// This indicates whether or not to pay attention to advanced search parameters.
	
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
			case	'script_name':
			case	'search_form':
			case	'single_meeting_id':
			case	'preset_service_bodies':
			break;
			
			default:
				if ( is_array ( $value ) )
					{
					$ret .= '<input type="hidden" name="'.c_comdef_htmlspecialchars ( $key ).'" value="'.c_comdef_htmlspecialchars ( join ( ",", $value ) ).'" />';
					}
				else
					{
					$ret .= '<input type="hidden" name="'.c_comdef_htmlspecialchars ( $key ).'" value="'.c_comdef_htmlspecialchars ( $value ).'" />';
					}
			break;
			}
		}

	$ret .= '<input type="submit" class="c_comdef_search_specification_search_string_submit" id="c_comdef_search_specification_search_string_submit_basic" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_submit_value'] ).'" />';
	$ret .= '<div class="search_check_div"><input type="checkbox" id="StringSearchIsAnAddress" name="StringSearchIsAnAddress" value="1" title="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_checkbox_title'] ).'" /><label for="StringSearchIsAnAddress">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_checkbox_label'] ).'</label></div>';
	$ret .= '</div>';
	$ret .= '</div>';
	
	// If the browser can handle it, we display a map, with a disclosure link.
	if ( isset ( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		// This contains the whole map section.
		$ret .= '<div id="c_comdef_search_specification_map_container_div" class="c_comdef_search_specification_map_container_div">';
		// This is the disclosure link.
		$ret .= '<a id="c_comdef_search_specification_map_vis_a" title="'.c_comdef_htmlspecialchars ( $map_title ).'" class="'.$map_class.'" href="javascript:ToggleMapVisibility()">'.c_comdef_htmlspecialchars ( $map_text ).'</a>';
		// This contains the map.
		$ret .= '<div id="c_comdef_search_specification_map_div" class="c_comdef_search_specification_map_div" style="display:'.$map_disp.'"></div>';
		// This contains the select that chooses the response format.
		$ret .= '<div id="c_comdef_search_specification_map_check_div" class="c_comdef_search_specification_map_check_div" style="display:'.$map_disp.'">';
		$ret .= '<label class="bmlt_result_type_label" for="result_type">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_show_as_label'] ).'</label>';
		$ret .= '<select id="result_type">';
		$ret .= '<option value="map" selected="selected">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_show_as_map_option'] ).'</option>';
		$ret .= '<option value="force_list">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_show_as_list_option'] ).'</option>';
		$ret .= '</select>';
		$ret .= '<fieldset class="where_am_i_fieldset" id="where_am_i_fieldset" style="display:none">';
			$ret .= '<legend>';
				$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_where_am_i_button'] );
			$ret .= '</legend>';
			$ret .= '<input type="button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_submit_value'] ).'" onclick="WhereAmI()" />';
		$ret .= '</fieldset>';
		$ret .= '</div>';
		// This is some JavaScript that is embedded, and controls the map and the search form. It is in here, in case the browser can't handle JavaScript.
		$pathname = dirname ( __FILE__ ).'/search_specifier_map.js';
		$script = file_get_contents ( $pathname );
		// This is how we do localization and customization. We replace tokens in the JavaScript with real time strings.
		$script = str_replace( "##SEARCHBYSTRING_TITLE##", $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_map_disclose_title_invis'], $script );
		$script = str_replace( "##SEARCHBYMAP_TITLE##", $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_map_disclose_title_vis'], $script );
		$script = str_replace( "##SEARCHBYSTRING##", $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_search_title_a'], $script );
		$script = str_replace( "##SEARCHBYMAP##", $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_map_search_title_a'], $script );
		$script = str_replace( '##IMAGE_DIR##', $location_of_images, $script );
		$script = str_replace( "##FAILED_LOOKUP##", c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['lookup_failed_alert'] ), $script );
		
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
				case	'preset_service_bodies':
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
			
		$script = str_replace( "##SCRIPT_URL##", $script_name.$query.'supports_ajax=yes&do_search=yes&geo_width=-'.intval ( $localized_strings['number_of_meetings_for_auto'] ), $script );
        if ( !defined ( '__DEBUG_MODE__' ) )
            {
            $script = preg_replace( "|\/\*.*?\*\/|s", "", $script );
            $script = preg_replace( '#http:\/\/#', "http:@@", $script );
            $script = preg_replace( '#\/\/.*?[\n\r]#', "", $script );
            $script = preg_replace( '#http:@@#', "http://", $script );
            $script = preg_replace( "|\t+|s", " ", $script );
            $script = preg_replace( "| +|s", " ", $script );
            $script = preg_replace( "|[\n\r]+|s", "", $script );
            }
        
		if ( isset ( $in_http_vars['search_spec_map_center'] ) && $in_http_vars['search_spec_map_center'] )
			{
			list ( $latval, $lngval, $zoom ) = explode ( ',', $in_http_vars['search_spec_map_center'] );
			if ( !isset ( $zoom ) || !$zoom )
				{
				$zoom = 9;
				}
			
			$localized_strings['search_spec_map_center']['latitude'] = floatval ( $latval );
			$localized_strings['search_spec_map_center']['longitude'] = floatval ( $lngval );
			$localized_strings['search_spec_map_center']['zoom'] = intval ( $zoom );
			}
		$ret .= '<script type="text/javascript">'.$script.'if ( document.getElementById(\'c_comdef_search_specification_search_string_line\').style.display!=\'none\'){document.getElementById(\'c_comdef_search_specification_search_string\').focus()};window.onload=function(){document.getElementById ( \'advanced_mapmode\' ).value=\'\';MakeSmallMap('.floatval ( $localized_strings['search_spec_map_center']['latitude'] ).','.floatval ( $localized_strings['search_spec_map_center']['longitude'] ).','.intval ( $localized_strings['search_spec_map_center']['zoom'] ).','.(($disable_zoom_in_clicks == true) ? 'true': 'false').');}</script>';
		$ret .= '</div>';
		}
	if ( isset ( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		$ret .= DisplayAdvancedSearchDiv ( $in_http_vars );
		}
	// This is the throbber that will be shown if we submit.
	$ret .= '<div id="bmlt_throbber_div" class="throbber_div" style="display:none"><img src="'.c_comdef_htmlspecialchars ( $location_of_throbber ).'" alt="throbber" /></div>';
	$ret .= '</form><div class="clear_both"></div>';
	$ret .= '</div>';
	if ( isset ( $in_http_vars['start_view'] ) && ( $in_http_vars['start_view'] == 'advanced' ) && isset ( $in_http_vars['supports_ajax'] ) && ($in_http_vars['supports_ajax'] == 'yes') )
		{
		$ret .= '<script type="text/javascript">';
		$ret .= "DisplaySearchSpecification('advanced','".htmlspecialchars ( $in_http_vars['start_view_adv'] )."');";			
		$ret .= '</script>';
		}
	$ret .= '</div></td></tr></tbody></table>';
	
	return $ret;
}

/*******************************************************************/
/** \brief This function outputs the XHTML for the advanced search
	div.
	
	\returns a string. The HTML for the search form.
*/
function DisplayAdvancedSearchDiv ( $in_http_vars ///< The $_GET and $_POST parameters, in an associative array.
									)
{
	$ret = '';
	require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php' );
	$server = c_comdef_server::MakeServer();
	
	if ( $server instanceof c_comdef_server )
		{
		$localized_strings = c_comdef_server::GetLocalStrings();

		$ret = '<div id="advanced_search_div" class="advanced_search_div" style="display:none">';
		$ret .= '<input type="hidden" name="advanced_search_mode" value="" id="advanced_search_mode" />';
		$ret .= '<input type="hidden" name="lat_val" value="" id="lat_val_hidden" />';
		$ret .= '<input type="hidden" name="long_val" value="" id="long_val_hidden" />';
//		$ret .= '<input type="hidden" name="zoom" value="" id="zoom_val_hidden" />';    // Commented out, because this was never used.
		$ret .= '<input type="hidden" name="advanced_mapmode" value="" id="advanced_mapmode" />';
		$ret .= '<input type="hidden" id="aggregate_sb_checks" name="aggregate_sb_checks" value="" />';

		$r_units = $localized_strings['dist_units'] == 'mi' ? $localized_strings['comdef_search_results_strings']['Radius_Display']['miles'] : $localized_strings['comdef_search_results_strings']['Radius_Display']['km'];
		$radius_array = array();
		foreach ( $localized_strings['comdef_map_radius_ranges'] as $radius )
			{
			if ( $radius > 0 )
				{
				array_push ( $radius_array, $radius );
				}
			}

		$select_html = '<label class="bmlt_advanced_radius_label" for="advanced_radius">'.$localized_strings['comdef_search_results_strings']['Search_Form']['select_radius_label'].$localized_strings['prompt_delimiter'].'</label>';
		$select_html .= ' <select class="bmlt_advanced_radius" id="advanced_radius" name="advanced_radius" onchange="ClearCircularOverlay();CreateCircularOverlay()">';
		$select_html .= '<option selected="selected" value="-'.$localized_strings['number_of_meetings_for_auto'].'">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['auto_radius'] ).'</option>';
		foreach ( $radius_array as $radius )
			{
			$text = $radius;
			if ( $text == 0.125 )
				{
				if ( $r_units == ' miles' )
					{
					$text = '1/8 mile';
					}
				else
					{
					$text = '1/8'.$r_units;
					}
				}
			else
				if ( $text == 0.25 )
					{
					if ( $r_units == ' miles' )
						{
						$text = '1/4 mile';
						}
                    else
                        {
                        $text = '1/4'.$r_units;
                        }
					}
				elseif ( $text == 0.5 )
					{
					if ( $r_units == ' miles' )
						{
						$text = '1/2 mile';
						}
                    else
                        {
                        $text = '1/2'.$r_units;
                        }
					}
				elseif ( $text == 1.0 )
					{
					if ( $r_units == ' miles' )
						{
						$text = '1 mile';
						}
                    else
                        {
                        $text = '1'.$r_units;
                        }
					}
				else
					{
					$text .= $r_units;
					}
						
			$select_html .= '<option value="'.c_comdef_htmlspecialchars ( $radius ).'">'.c_comdef_htmlspecialchars ( $text ).'</option>';
			}
			
			$select_html .= '</select>';
			
			$ret .= '<input type="submit" class="c_comdef_search_specification_search_string_submit_top" id="c_comdef_search_specification_search_string_submit_advanced_top" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_submit_value'] ).'" />';

			$ret .= '<div id="search_radius_div" class="search_radius_div" style="display:none">'.$select_html.'</div>';
			$ret .= '<fieldset class="one_line_advanced_spec_fieldset" id="where_am_i_advanced_fieldset" style="display:none">';
				$ret .= '<legend>';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_where_am_i_button'] );
				$ret .= '</legend>';
				$ret .= '<input type="button" id="where_am_i_advanced_button" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['where_am_i_advanced_button'] ).'" onclick="WhereAmI()" />';
			$ret .= '</fieldset>';
			$ret .= '<fieldset class="one_line_advanced_spec_fieldset">';
				$ret .= '<legend>';
					$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Value_Prompts']['weekdays'] );
				$ret .= '</legend>';
				
				for ( $i = 0; $i < 7; $i++ )
					{
					$weekday = $localized_strings['weekdays'][$i];
					$ret .= '<div class="bmlt_search_one_weekday_div">';
						$ret .= '<input name="advanced_weekdays[]" id="weekday_check_'.c_comdef_htmlspecialchars($i).'" type="checkbox" value="'.c_comdef_htmlspecialchars($i+1).'" />';
						$ret .= '<label class="bmlt_spec_label_weekday" for="weekday_check_'.c_comdef_htmlspecialchars($i).'" class="bmlt_check_label">'.c_comdef_htmlspecialchars($weekday).'</label>';
					$ret .= '</div>';
					}
			$ret .= '</fieldset>';

			$formats = $server->GetFormatsObj ();
			
			if ( $formats instanceof c_comdef_formats )
				{
				$format_array = $formats->GetFormatsByLanguage();
				
				if ( is_array ( $format_array ) && count ( $format_array ) )
					{
					$ret .= '<fieldset class="one_line_advanced_spec_fieldset">';
						$ret .= '<legend>';
							$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Value_Prompts']['formats'] );
						$ret .= '</legend>';
						
						foreach ( $format_array as &$format )
							{
							if ( $format instanceof c_comdef_format )
								{
								$id = $format->GetSharedID();
								$code = $format->GetKey();
								$description = $format->GetLocalDescription();
								$ret .= '<div class="bmlt_search_one_format_div">';
									$ret .= '<abbr title="'.c_comdef_htmlspecialchars($description).'"><input name="advanced_formats[]" id="format_check_'.c_comdef_htmlspecialchars($id).'" type="checkbox" value="'.c_comdef_htmlspecialchars($id).'" />';
									$ret .= '<label for="format_check_'.c_comdef_htmlspecialchars($id).'" class="bmlt_check_label">'.c_comdef_htmlspecialchars($code).'</label></abbr>';
								$ret .= '</div>';
								}
							}
						
						$ret .= '<div class="bmlt_hover_message">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['advanced_tooltips'] ).'</div>';
																			
					$ret .= '</fieldset>';
					}
				
				// Draw out all the Service Body checkboxes.
				$service_body_array = $server->GetServiceBodyArrayHierarchical();
				
				if ( is_array ( $service_body_array ) && count ( $service_body_array ) )
					{
					$service_body_array = $service_body_array['dependents'];
					
					$ret .= '<fieldset class="one_line_advanced_spec_fieldset">';
						$sb_array = array();
						if ( isset ( $in_http_vars['preset_service_bodies'] ) )
							{
							if ( is_array ( $in_http_vars['preset_service_bodies'] ) && count ( $in_http_vars['preset_service_bodies'] ) )
								{
								foreach ( $in_http_vars['preset_service_bodies'] as $sb_id )
									{
									array_push ( $sb_array, intval ( $sb_id ) );
									}
								}
							elseif ( intval ( $in_http_vars['preset_service_bodies'] ) )
								{
								$sb_array[0] = intval ( $in_http_vars['preset_service_bodies'] );
								}
							}

						$ret .= '<legend>';
							$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Value_Prompts']['service_bodies'] );
						$ret .= '</legend>';
						
						foreach ( $service_body_array as &$service_body_ar )
							{
							if ( isset ( $service_body_ar['object'] ) )
								{
								$service_body = $service_body_ar['object'];
								if ( $service_body instanceof c_comdef_service_body )
									{
									$id = $service_body->GetID();
// Commented out, because this KILLS performance.
//									$has_meetings = $server->GetMeetingsForAServiceBody ( $id );
									$has_meetings = true;
									if ( $has_meetings || isset ( $service_body_ar['dependents'] ) )
										{
										$dep = isset ( $service_body_ar['dependents'] ) ? $service_body_ar['dependents'] : null;
										$ret .= ReturnAServiceBodyDL ( $server, $service_body, null, $dep, $sb_array );
										}
									}
								}
							}
						$ret .= '<div class="bmlt_hover_message">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['advanced_tooltips'] ).'</div>';
					$ret .= '</fieldset>';
					}
				
				// These are the special administration options for logged-in users.
				if ( (c_comdef_server::GetCurrentUserObj() instanceof c_comdef_user) && is_array ( $localized_strings['comdef_search_admin_strings'] ) && count ( $localized_strings['comdef_search_admin_strings'] ) )
					{
					$ret .= '<fieldset class="one_line_advanced_spec_fieldset">';
						$ret .= '<legend>';
							$ret .= c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Spec']['admin_fieldset_name'] );
						$ret .= '</legend>';
						$ret .= '<div class="bmlt_oneline_advanced_value">';
							$ret .= '<label class="result_type_advanced_label" for="advanced_published">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Spec']['admin_select_published_label'] ).'</label>';
							$ret .= '<select class="result_type_advanced_select" name="advanced_published" id="advanced_published">';
								$ret .= '<option value="-1">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Spec']['admin_select_published_option_unpub'] ).'</option>';
								$ret .= '<option value="0" selected="selected">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Spec']['admin_select_published_option_anypub'] ).'</option>';
								$ret .= '<option value="1">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_admin_strings']['Admin_Spec']['admin_select_published_option_pub'] ).'</option>';
							$ret .= '</select>';
						$ret .= '</div>';
					$ret .= '</fieldset>';
					}
				
				// We only show a popup here if we are logged in, or we are a satellite, with PDF downloads enabled.
				$satellite = (isset ( $in_http_vars['satellite_standalone'] ) && ($in_http_vars['satellite_standalone'] != false)) || (isset ( $in_http_vars['satellite'] ) && ($in_http_vars['satellite'] != ''));

				include ( dirname ( __FILE__ ).'/../../server/config/auto-config.inc.php' );
				
				if ( (c_comdef_server::GetCurrentUserObj() instanceof c_comdef_user) || (isset ( $allow_pdf_downloads ) && $allow_pdf_downloads && $satellite) )
					{
					$ret .= '<fieldset class="one_line_advanced_spec_fieldset">';
					$ret .= '<label class="result_type_advanced_label" for="result_type_advanced">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_show_as_label'] ).'</label>';
					$ret .= '<select id="result_type_advanced" class="result_type_advanced_select" name="result_type_advanced">';
					$ret .= '<option value="force_list">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_show_as_list_option'] ).'</option>';
					
					if ( !(c_comdef_server::GetCurrentUserObj() instanceof c_comdef_user) )
						{
						$ret .= '<option value="booklet">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_booklet_option'] ).'</option>';
						$ret .= '<option value="listprint">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_list_option'] ).'</option>';
						}
					else
						{
						$ret .= '<option value="csv">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_return_as_csv_option'] ).'</option>';
						$ret .= '<option value="csv_naws">'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_return_as_csv_naws_option'] ).'</option>';
						}
					
					$ret .= '</select>';
					$ret .= '</fieldset>';
					}
				}
			$ret .= '<input type="submit" class="c_comdef_search_specification_search_string_submit" id="c_comdef_search_specification_search_string_submit_advanced" value="'.c_comdef_htmlspecialchars ( $localized_strings['comdef_search_results_strings']['Search_Form']['specifier_string_submit_value'] ).'" />';
	
		$ret .= '</div>';
		}
	
	return $ret;
}

/*******************************************************************/
/** \brief	This returns a <dl> with the Service Body Checkbox, as well
	as a recursive list of dependent <dl> elements.
	
	\returns a string. The HTML for the elements.
*/
function ReturnAServiceBodyDL ( &$server,
								&$in_service_body,
								$parent_id,
								&$dependents,
								$sb_array			///< This contains any "pre-checked" service body IDs.
								)
{
	$ret = '';
	$id = $in_service_body->GetID();
	$checked = (is_array ( $sb_array ) && count ( $sb_array ) && in_array ( intval ( $id ), $sb_array ));
	$dom_id = "my_id_$id";
	$my_parent_id = "parent_$id";
	if ( $parent_id )
		{
		$dom_id = $parent_id.'_'.$dom_id;
		$my_parent_id = $parent_id.'_'.$my_parent_id;
		}
	$name = $in_service_body->GetLocalName();
	$description = $in_service_body->GetLocalDescription();

	$localized_strings = c_comdef_server::GetLocalStrings();

	if ( !trim ( $description ) )
		{
		$description = $name;
		}
		
	$ret .= '<dl class="bmlt_search_one_service_body_dl">';
		$ret .= '<dt class="bmlt_search_one_service_body_dt">';
			$ret .= '<abbr title="'.c_comdef_htmlspecialchars($description).'"><input name="advanced_service_bodies[]" id="'.c_comdef_htmlspecialchars($dom_id).'" type="checkbox" value="'.c_comdef_htmlspecialchars($id).'" onchange="ServiceBodyCheckboxChanged(\''.c_comdef_htmlspecialchars($dom_id).'\')"';
			if ( $checked )
				{
				$ret .= ' checked="checked"';
				}
			$ret .= '/>';
			$ret .= '<label for="'.c_comdef_htmlspecialchars($dom_id).'" class="bmlt_check_label">'.c_comdef_htmlspecialchars($name).'</label></abbr>';
		$ret .= '</dt>';
	    
		if ( $localized_strings['show_sb_text'] )
		    {
		    $ret .= '<dd class="bmlt_search_one_service_body_dd">'.c_comdef_htmlspecialchars($description).'</dd>';
		    }
		
		if ( $dependents )
			{
			$ret .= '<dd class="bmlt_search_one_service_body_dd">';
			foreach ( $dependents as &$service_body_ar )
				{
				if ( isset ( $service_body_ar['object'] ) )
					{
					$service_body = $service_body_ar['object'];
					if ( $service_body instanceof c_comdef_service_body )
						{
						$id = $service_body->GetID();
// Commented out, because this KILLS performance.
//						$has_meetings = $server->GetMeetingsForAServiceBody ( $id );
						$has_meetings = true;
						if ( $has_meetings || isset ( $service_body_ar['dependents'] ) )
							{
							$dep = isset ( $service_body_ar['dependents'] ) ? $service_body_ar['dependents'] : null;
							$ret .= ReturnAServiceBodyDL ( $server, $service_body, $my_parent_id, $dep, $sb_array );
							}
						}
					}
				}
			$ret .= '</dd>';
			}
	$ret .= '</dl>';
	return $ret;
}
?>
