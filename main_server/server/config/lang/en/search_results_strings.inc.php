<?php
/***********************************************************************/
/** \file	search_results_strings.inc.php
	\brief	The strings displayed in the search results for this language (English)
    
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
    along with this code.  If not, see <http://www.gnu.org/licenses/>.*/
	defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.

	if ( !defined ( '__PROMPT_DELIMITER__' ) )
		{
		define ( "__PROMPT_DELIMITER__", ":" );	///< This is what is used to delimit a prompt from its value.
		}
	
	$comdef_map_radius_ranges = array ( 0.125, 0.25, 0.5, 1, 2, 5, 10, 15, 20, 35, 50 );
	
	$comdef_search_results_strings = array ('No_Results' => "No meetings found for this search.",
											'main_info_radius_prompt' => 'Search for meetings within',
											'main_map_center_title' => 'Click on this marker to choose a different search radius, or see the results as a list. You can also drag it around to change the search center.',
											'main_map_list_link_title' => 'Click on this link to view this search as a list.',
											'main_map_list_link_text' => 'View These Search Results as a List',
											'url_title' => 'Follow this link to go to ',
											'Filter_String' =>
												array (	'main_message' => 'This search is filtered. It only shows those meetings that match the search criteria.',
														'search_for' => 'Filter Criteria:',
														'formats' => 'Formats: ',
														'weekdays' => 'Weekdays: ',
														'service_bodies' => 'Service Bodies: '
														),
											'Column_Headers' =>
												array ( 'weekday_tinyint' => 'Weekday',
														'location_municipality' => 'Town',
														'meeting_name' => 'Meeting Name',
														'start_time' => 'Time',
														'location' => 'Location',
														'formats' => 'Format',
														'gps' => 'GPS' ),
											'Column_Prompts' =>
												array ( 'weekday' => 'Sort the Search by Weekday.',
														'town' => 'Sort the Search by Town.',
														'time' => 'Sort the Search by Time.',
														'rev' => ' (Search in the Opposite Order.)'),
											'Count_Tally' =>
												array ( 'to' => 'to',
														'of' => 'of',
														'suffix' => 'total meetings found'),
											'edit_indicator' => 'Edit',
											'time_midnight' => 'Midnight',
											'time_noon' => 'Noon',
											'page_up_char' => '>>',
											'page_down_char' => '<<',
											'page_up_prompt' => 'Go to the set of pages above this.',
											'page_down_prompt' => 'Go to the set of pages below this.',
											'page_link_prompt' => 'Go to page',
											'edit_meeting' => 'Edit meeting ',
											'get_details_title' => 'Get more details about meeting ',
											'meeting_link' => 'Get more information about this meeting.',
											'meeting_link_text' => 'More Details',
											'no_meeting' => 'No Meeting Found',
											'Value_Prompts' =>
												array (	'id_bigint' => 'Meeting ID',
														'worldid_mixed' => 'World Services ID',
														'service_body' => 'Service Body',
														'service_bodies' => 'Service Bodies',
														'weekdays' => 'Weekdays',
														'weekday' => 'Meeting Gathers Every',
														'start_time' => 'Meeting Starts at',
														'duration_time' => 'Meeting Lasts',
														'location' => 'Location',
														'duration_time_hour' => 'Hour',
														'duration_time_hours' => 'Hours',
														'duration_time_minute' => 'Minute',
														'duration_time_minutes' => 'Minutes',
														'lang_enum' => 'Language',
														'formats' => 'Formats',
														'distance' => 'Distance from Center',
														'generic' => 'NA Meeting',
														'close_title' => 'Close This Meeting Detail Window',
														'close_text' => 'Close Window',
														'map_alt' => 'Map to Meeting',
														'map' => 'Follow This Link for A Map',
														'title_checkbox_unpub_meeting' => 'This meeting is unpublished. It cannot be seen by regular searches.',
														'title_checkbox_copy_meeting' => 'This meeting is a duplicate of another meeting. It is also unpublished. It cannot be seen by regular searches.'
														),
											'StaticMapsURL' => 'http://maps.google.com/staticmap?size=##WIDTH##x##HEIGHT##&amp;markers=##LAT##,##LONG##&amp;zoom=15&amp;key=##KEY##',
											'MapsURL' => 'http://maps.google.com/maps?q=##LAT##,##LONG##+(##NAME##)&amp;ll=##LAT##,##LONG##',
											'basic_html' => '&amp;output=html',
											'ServerMapsURL' => 'http://maps.google.com/maps/geo?q=##SEARCH_STRING##&output=xml&key=##KEY##',
											'Service_Body_Types' =>
												array (	'GR' => 'Group',
														'AS' => 'Area Service',
														'RS' => 'Regional Service',
														'WS' => 'World Service',
														'MA' => 'Metro Area',
														'ZF' => 'Zonal Forum',
														'generic' => 'Service Body' ),
											'and' => 'and',
											'Radius_Display' =>
												array ( 'within' => ' within a radius of ',
														'miles' => ' miles',
														'km' => ' Km'
														),
											'Search_Form' =>
												array (	'text_input_prompt' => 'Search For',
														'advanced_tooltips' => 'Hold your cursor over one of these items to find out more.',
														'select_radius_label' => 'Search Radius',
														'auto_radius' => 'Auto',
														'specifier_map_disclose_title_vis' => 'Hide the Map.',
														'specifier_map_disclose_title_invis' => 'Display the Map.',
														'specifier_string_submit_value' => 'Go',
														'specifier_string_basic_search_title' => 'Basic Search',
														'specifier_string_advanced_search_title' => 'Advanced Search',
														'specifier_string_map_search_title_a' => 'Search by Map Instead of by Text',
														'specifier_string_search_title_a' => 'Search for Text Instead of by Map',
														'specifier_string_show_as_label' => 'Show Results as',
														'specifier_string_show_as_list_option' => 'a List',
														'specifier_string_show_as_map_option' => 'a Map',
														'specifier_string_return_as_csv_option' => 'a Comma-Separated Values (CSV) File Download',
														'specifier_string_return_as_csv_naws_option' => 'a Comma-Separated Values (CSV) File, Formatted for NAWS',
														'specifier_string_booklet_option' => 'a Printable PDF File Download, Formatted As a Booklet',
														'specifier_string_list_option' => 'a Printable PDF File Download, Formatted As a List',
														'specifier_string_checkbox_label' => 'This is a Location, Address or Postal (Zip) Code',
														'specifier_string_checkbox_title' => 'If this is checked, the string will be looked up as an address or location (such as a town name).',
														'specifier_string_where_am_i_button' => 'Find Meetings Near My Current Location',
														'address_lookup_fieldset' => 'Find Meetings Near An Address or Location',
														'address_lookup_label' => 'Address or Zip Code',
														'string_lookup_fieldset' => 'Find Meetings Containing a String',
														'string_lookup_label' => 'Enter String',
														'lookup_failed_alert' => 'Could not get the location.',
														'where_am_i_advanced_button' => 'Set My Current Location',
														'small_weekdays' => 'Find Meetings On Specific Weekdays'
														),
											'Contact_Form' =>
												array (	'contact_form_link_title' => 'Contact us about this Meeting',
														'contact_form_main_title' => 'Send An Email to Us about this Meeting',
														'contact_form_name' => 'Your Name',
														'contact_form_no_name_text' => 'No Name Given',
														'contact_form_email' => 'Your Email Address',
														'contact_form_subject' => 'Message Subject',
														'contact_form_no_subject_text' => 'No Subject',
														'contact_form_message' => 'Your Message',
														'contact_form_sent_message' => 'Your message was successfully sent.',
														'contact_form_failed_message' => 'Your message was not sent, due to an error.',
														'contact_form_spam_message' => 'Your message was not sent, because it appears to be spam. It may have too many links in the text, or the subject line has illegal characters in it.',
														'contact_form_need_email' => 'Your message was not sent, because your email is not valid.',
														'contact_form_need_message' => 'Your message was not sent, because its message body was empty.',
														'contact_form_send_button' => 'Send Message',
														'contact_form_cancel_button' => 'Cancel',
														'contact_form_OK_button' => 'OK',
														'contact_prefix' => '[FROM MEETING LIST]',
														'contact_body_text_preanble' => 'This message was sent by a user of the meeting list, and concerns the meeting named',
														'contact_body_text_preanble2' => 'You are being contacted, because you are listed as the principal Service Body Administrator for this meeting.'
														),
											'CheckBoxes' =>
												array (	'label_text' => 'With Checked Meetings',
														'default_option' => 'Select An Option',
														'publish' => 'Publish any Unpublished Meetings',
														'unpublish' => 'Unpublish any Published Meetings',
														'delete' => 'Delete These Meetings',
														'duplicate' => 'Make Copies of These Meetings',
														'apply_data_item' => 'Apply A Data Item To All These Meetings',
														'delete_extreme_prejudice' => 'Delete These Meetings Permanently'
													),
											'POI_Link_Title' => 'Download a GPS-Compatible POI (Point Of Interest) File, That Can Be Uploaded Onto A GPS Device.',
											'Map_Link_Title' => 'View The Search as a Map.',
											'Map_Link_of' => 'of ',
											'Main_Link_Title' => 'This will take you to a page with only this meeting displayed.',
											'Root_Decl' => 'The root URL for this server is ',
											'Root_Page_Title' => 'Basic Meeting List Toolbox Root Server',
											'hidden_value' => 'Cannot Display Data -Unauthorized'
											);
?>