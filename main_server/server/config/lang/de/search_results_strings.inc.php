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

	if ( !defined ( '__HTML_DISPLAY_CHARSET__' ) )
		{
	    define ( '__HTML_DISPLAY_CHARSET__', 'UTF-8' );
		}
		
	if ( !defined ( '__PROMPT_DELIMITER__' ) )
		{
		define ( "__PROMPT_DELIMITER__", ":" );	///< This is what is used to delimit a prompt from its value.
		}
	
	$comdef_map_radius_ranges = array ( 0.125, 0.25, 0.5, 1, 2, 5, 10, 15, 20, 35, 50 );
	
	$comdef_search_results_strings = array ('No_Results' => "Kein Meeting gefunden.",
											'main_info_radius_prompt' => 'Suche nach Meetings mit',
											'main_map_center_title' => 'Klicke auf diesen Marker um einen anderen Such-Radius zu w‰hlen, oder die Ergebnisse als Liste anzeigen zu lassen, oder das Such-Zentrum zu ‰ndern.',
											'main_map_list_link_title' => 'Such-Ergebnis als Liste.',
											'main_map_list_link_text' => 'Zeige das Such-Ergebnis als Liste',
											'url_title' => 'Folge diesem Link um ',
											'Filter_String' =>
												array (	'main_message' => 'Dieses Such-Ergebnis ist gefiltert.',
														'search_for' => 'Suchkriterium:',
														'formats' => 'Format: ',
														'weekdays' => 'Wochentag: ',
														'service_bodies' => 'Service Bodies: '
														),
											'Column_Headers' =>
												array ( 'weekday_tinyint' => 'Wochentag',
														'location_municipality' => 'Stadt',
														'meeting_name' => 'Meeting Name',
														'start_time' => 'Uhrzeit',
														'location' => 'Location',
														'formats' => 'Format',
														'gps' => 'GPS' ),
											'Column_Prompts' =>
												array ( 'weekday' => 'Sortiere die Ergebnisse nach Wochentage.',
														'town' => 'Sortiere die Ergebnisse nach St‰dten.',
														'time' => 'Sortiere die Ergebnisse nach Uhrzeit.',
														'rev' => ' (Suche in der umgekehrten Reihenfolge.)'),
											'Count_Tally' =>
												array ( 'to' => 'to',
														'of' => 'von',
														'suffix' => 'Meetings gefunden'),
											'edit_indicator' => 'Edit',
											'time_midnight' => 'Mitternacht',
											'time_noon' => 'Mittags',
											'page_up_char' => '>>',
											'page_down_char' => '<<',
											'page_up_prompt' => 'Gehe zur ¸bergeordneten Gruppe.',
											'page_down_prompt' => 'Gehe zur untergeordneten Gruppe.',
											'page_link_prompt' => 'Gehe zur Seite',
											'edit_meeting' => 'Meeting ‰ndern',
											'get_details_title' => 'Zeige mehr Details von diesem Meeting ',
											'meeting_link' => 'Zeige mehr Informationen ¸ber dieses Meeting.',
											'meeting_link_text' => 'Mehr Details',
											'no_meeting' => 'Kein Meeting gefunden',
											'Value_Prompts' =>
												array (	'id_bigint' => 'Meetings ID',
														'worldid_mixed' => 'World Services ID',
														'service_body' => 'Service Body',
														'service_bodies' => 'Service Bodies',
														'weekdays' => 'Wochentags',
														'weekday' => 'Meeting jeden',
														'start_time' => 'Meeting beginnt',
														'duration_time' => 'Meeting dauert',
														'location' => '÷rtlichkeit',
														'duration_time_hour' => 'Stunde',
														'duration_time_hours' => 'Stunden',
														'duration_time_minute' => 'Minute',
														'duration_time_minutes' => 'Minuten',
														'lang_enum' => 'Sprache',
														'formats' => 'Format',
														'distance' => 'Entfernung vom Zentrum',
														'generic' => 'NA Meeting',
														'close_title' => 'Fenster schlieﬂen',
														'close_text' => 'Fenster schlieﬂen',
														'map_alt' => 'Karte zum Meeting',
														'map' => 'Landkarte zeigen',
														'title_checkbox_unpub_meeting' => 'Dieses Meeting ist nicht Verˆffentlicht. Es wird bei der normalen Suche nicht angezeigt.',
														'title_checkbox_copy_meeting' => 'Dieses Meeting ist eine Kopie. Es ist nicht verˆffentlicht. Es wird bei der normalen Suche nicht angezeigt.'
														),
											'StaticMapsURL' => 'http://maps.google.de/staticmap?size=##WIDTH##x##HEIGHT##&amp;markers=##LAT##,##LONG##&amp;zoom=15&amp;key=##KEY##',
											'MapsURL' => 'http://maps.google.de/maps?q=##LAT##,##LONG##+(##NAME##)&amp;ll=##LAT##,##LONG##',
											'basic_html' => '&amp;output=html',
											'ServerMapsURL' => 'http://maps.google.de/maps/geo?q=##SEARCH_STRING##&output=xml&key=##KEY##',
											'Service_Body_Types' =>
												array (	'GR' => 'Gruppe',
														'AS' => 'Area Service',
														'RS' => 'Regional Service',
														'WS' => 'World Service',
														'MA' => 'Metro Area',
														'ZF' => 'Zonal Forum',
														'generic' => 'Service Body' ),
											'and' => 'and',
											'Radius_Display' =>
												array ( 'within' => ' mit einem Radius von ',
														'miles' => ' miles',
														'km' => ' Km'
														),
											'Search_Form' =>
												array (	'text_input_prompt' => 'Suche nach',
														'advanced_tooltips' => 'Um Informationen zu erhalten, den Cursor ¸ber den Link halten.',
														'select_radius_label' => 'Such-Radius',
														'auto_radius' => 'Auto',
														'specifier_map_disclose_title_vis' => 'Landkarte ausblenden.',
														'specifier_map_disclose_title_invis' => 'Landkarte anzeigen.',
														'specifier_string_submit_value' => 'Los',
														'specifier_string_basic_search_title' => 'Einfache Suche',
														'specifier_string_advanced_search_title' => 'Erweiterte Suche',
														'specifier_string_map_search_title_a' => 'Landkartensuche',
														'specifier_string_search_title_a' => 'Textsuche',
														'specifier_string_show_as_label' => 'Zeige Ergebnisse als',
														'specifier_string_show_as_list_option' => 'Liste',
														'specifier_string_show_as_map_option' => 'Landkarte',
														'specifier_string_return_as_csv_option' => 'CSV-Datei downloaden',
														'specifier_string_return_as_csv_naws_option' => 'CSV-Datei, für NAWS formattiert, downloaden',
														'specifier_string_booklet_option' => 'eine druckbare PDF-Datei downloaden, als Booklet formattiert',
														'specifier_string_list_option' => 'eine druckbare PDF-Datei downloaden, als Liste formattiert',
														'specifier_string_checkbox_label' => 'Dies ist ein Ort, Adresse oder PLZ',
														'specifier_string_checkbox_title' => 'If this is checked, the string will be looked up as an address or location (such as a town name).',
														'specifier_string_where_am_i_button' => 'Finde Meetings in meiner Umgebung',
														'address_lookup_fieldset' => 'Finde Meetings in der Nähe einer Adresse ',
														'address_lookup_label' => 'Adresse oder PLZ',
														'string_lookup_fieldset' => 'Finde Meeting anhand eines Suchtextes',
														'string_lookup_label' => 'Suchtext eingeben',
														'lookup_failed_alert' => 'Could not get the location.',
														'where_am_i_advanced_button' => 'Setze meine aktuelle Position',
														'small_weekdays' => 'Finde Meetings nach bestimmten Wochentagen'
														),
											'Contact_Form' =>
												array (	'contact_form_link_title' => 'Kontaktiere uns bzgl. dieses Meetings',
														'contact_form_main_title' => 'Sende eine Email bzgl. dieses Meetings',
														'contact_form_name' => 'Dein Name',
														'contact_form_no_name_text' => 'Es wurde kein Name angegeben',
														'contact_form_email' => 'Deine Email-Adresse',
														'contact_form_subject' => 'Betreff',
														'contact_form_no_subject_text' => 'Kein Betreff',
														'contact_form_message' => 'Deine Nachricht',
														'contact_form_sent_message' => 'Deine Nachricht wurde erfolgreich versendet.',
														'contact_form_failed_message' => 'Deine Nachricht wurde wegen eines Fehler nicht versendet.',
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
											'Root_Decl' => 'Die URI für diesen Server lautet ',
											'Root_Page_Title' => 'Basic Meeting List Toolbox Root Server',
											'hidden_value' => 'Cannot Display Data -Unauthorized'
											);
?>