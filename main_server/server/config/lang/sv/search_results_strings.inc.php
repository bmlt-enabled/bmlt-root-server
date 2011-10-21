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
	
	$comdef_search_results_strings = array ('No_Results' => "Din sökning gav inga träffar.",
											'main_info_radius_prompt' => 'Sök efter möten inom',
											'main_map_center_title' => 'Klicka på denna markör för att ändra din sökarea, eller se resultatet i listform. Du kan även flytta markören på kartan.',
											'main_map_list_link_title' => 'Klicka här för att få sökresultatet i listform',
											'main_map_list_link_text' => 'Se sökresultaten i en lista',
											'url_title' => 'Klicka på länken för att komma till ',
											'Filter_String' =>
												array (	'main_message' => 'Denna sökning är filtrerad. Endast träffar som stämmer mot filtreringsordet visas',
														'search_for' => 'Sök efter:',
														'formats' => 'Format: ',
														'weekdays' => 'Veckodag: ',
														'service_bodies' => 'serviceenhet: '
														),
											'Column_Headers' =>
												array ( 'weekday_tinyint' => 'Veckodag',
														'location_municipality' => 'Stad',
														'meeting_name' => 'Mötests namn',
														'start_time' => 'Tid',
														'location' => 'Plats',
														'formats' => 'Mötestyp',
														'gps' => 'GPS' ),
											'Column_Prompts' =>
												array ( 'weekday' => 'Sortera efter veckodag.',
														'town' => 'Sortera efter stad.',
														'time' => 'Sortera efter mötestid.',
														'rev' => ' (spegla sökresultatet.)'),
											'Count_Tally' =>
												array ( 'to' => 'till',
														'of' => 'av',
														'suffix' => 'möten funna.'),
											'edit_indicator' => 'Ändra',
											'time_midnight' => 'Midnatt',
											'time_noon' => 'Förmiddag',
											'page_up_char' => '>>',
											'page_down_char' => '<<',
											'page_up_prompt' => 'Nästa sidgrupp.',
											'page_down_prompt' => 'Föregående sidgrupp.',
											'page_link_prompt' => 'Gå till sidan',
											'edit_meeting' => 'Ändra mötet ',
											'get_details_title' => 'Mer detaljer om mötet ',
											'meeting_link' => 'Mer information om mötet.',
											'meeting_link_text' => 'Mera detaljer',
											'no_meeting' => 'Inga möten funna.',
											'Value_Prompts' =>
												array (	'id_bigint' => 'Mötes ID',
														'worldid_mixed' => 'World Services ID',
														'service_body' => 'serviceenhet',
														'service_bodies' => 'serviceenhetar',
														'weekdays' => 'Veckodag',
														'weekday' => 'Detta möte hålls varje',
														'start_time' => 'Mötet börjar',
														'duration_time' => 'Mötet håller på i',
														'location' => 'Plats',
														'duration_time_hour' => 'Timme',
														'duration_time_hours' => 'Timmar',
														'duration_time_minute' => 'Minut',
														'duration_time_minutes' => 'Minuter',
														'lang_enum' => 'Språk',
														'formats' => 'Mötestyp',
														'distance' => 'Avstånd från kartnål',
														'generic' => 'NA Möte',
														'close_title' => 'Stäng infosidan om mötet',
														'close_text' => 'stäng fönstret',
														'map_alt' => 'Karta till mötet',
														'map' => 'Klicka här för en karta',
														'title_checkbox_unpub_meeting' => 'Detta möte är opublicerat. Publicera för att göra synligt för alla.',
														'title_checkbox_copy_meeting' => 'Detta är en kopia på ett annat möte, dessutom är mötet opublicerat. '
														),
											'StaticMapsURL' => 'http://maps.google.com/staticmap?size=##WIDTH##x##HEIGHT##&amp;markers=##LAT##,##LONG##&amp;zoom=15&amp;key=##KEY##',
											'MapsURL' => 'http://maps.google.com/maps?q=##LAT##,##LONG##+(##NAME##)&amp;ll=##LAT##,##LONG##',
											'basic_html' => '&amp;output=html',
											'ServerMapsURL' => 'http://maps.google.com/maps/geo?q=##SEARCH_STRING##&output=xml&key=##KEY##',
											'Service_Body_Types' =>
												array (	'GR' => 'Group',
														'AS' => 'Destrikt',
														'RS' => 'Regional service',
														'WS' => 'Världs Service',
														'MA' => 'Metro Område',
														'ZF' => 'Zon forum',
														'generic' => 'serviceenhet' ),
											'and' => 'and',
											'Radius_Display' =>
												array ( 'within' => ' med en radie eav ',
														'miles' => ' miles',
														'km' => ' Km'
														),
											'Search_Form' =>
												array (	'text_input_prompt' => 'Sök efter',
														'advanced_tooltips' => 'Håll pekaren över en av dessa föremål för att få reda på mer',
														'select_radius_label' => 'Sökområde',
														'auto_radius' => 'Automatisk',
														'specifier_map_disclose_title_vis' => 'Dölj karta.',
														'specifier_map_disclose_title_invis' => 'Visa karta.',
														'specifier_string_submit_value' => 'OK',
														'specifier_string_basic_search_title' => 'Sök',
														'specifier_string_advanced_search_title' => 'Avancerad sökning',
														'specifier_string_map_search_title_a' => 'Sök via karta',
														'specifier_string_search_title_a' => 'Sök via text',
														'specifier_string_show_as_label' => 'Visa resultat som',
														'specifier_string_show_as_list_option' => 'lista',
														'specifier_string_show_as_map_option' => 'karta',
														'specifier_string_return_as_csv_option' => 'Ett dokument med kommaseparerade värden (CSV). Nedladdning',
														'specifier_string_return_as_csv_naws_option' => 'Ett dokument med kommaseparerade värden (CSV), Formaterad för NAWS. Nedladdning',
														'specifier_string_booklet_option' => 'En utskriftsvänlig PDF, formaterad som ett häfte. Nedladdning',
														'specifier_string_list_option' => 'En utskriftsvänlig PDF, formaterad som en lista. Nedladdning',
														'specifier_string_checkbox_label' => 'Detta är en plats, Adress eller postnummer',
														'specifier_string_checkbox_title' => 'Om denna är förbockad kommer sökningen ske i form av adress eller plats (tex en stad).',
														'specifier_string_where_am_i_button' => 'Hitta ett möte nära mig',
														'address_lookup_fieldset' => 'Hitta möten nära en adress eller en plats',
														'address_lookup_label' => 'Adress eller postnummer',
														'string_lookup_fieldset' => 'Sök möte med ett specifikt ord',
														'string_lookup_label' => 'fyll i ord',
														'lookup_failed_alert' => 'Kunde inte hitta området.',
														'where_am_i_advanced_button' => 'Sätt mitt nuvarande område',
														'small_weekdays' => 'Hitta möten speciella dagar'
														),
											'Contact_Form' =>
												array (	'contact_form_link_title' => 'Kontakta oss om detta möte',
														'contact_form_main_title' => 'Skicka ett mail om detta möte',
														'contact_form_name' => 'Ditt namn',
														'contact_form_no_name_text' => 'Skriv i namn',
														'contact_form_email' => 'Din adress',
														'contact_form_subject' => 'Meddelandets titel',
														'contact_form_no_subject_text' => 'Ingen titel',
														'contact_form_message' => 'Ditt meddelande',
														'contact_form_sent_message' => 'Ditt meddelande är nu skickat.',
														'contact_form_failed_message' => 'Ditt meddelande blev inte skickat. Nått gick snett.',
														'contact_form_spam_message' => 'Ditt meddelande blev inte skickat. Misstänkt spam. För många länkar och knepiga tecken är inte tillåtet.',
														'contact_form_need_email' => 'Ditt meddelande blev inte skickat. Din adress är felaktig.',
														'contact_form_need_message' => 'Ditt meddelande blev inte skickat. Själva meddelandet saknas.',
														'contact_form_send_button' => 'Skicka',
														'contact_form_cancel_button' => 'Ångra',
														'contact_form_OK_button' => 'OK',
														'contact_prefix' => '[FROM MEETING LIST]',
														'contact_body_text_preanble' => 'Detta meddelande var skickat från en medlem på listan, Meddelandet berör',
														'contact_body_text_preanble2' => 'Du blir kontaktad, eftersom du är listad som den huvudsakliga serviceenhetsadministratör för detta möte.'
														),
											'CheckBoxes' =>
												array (	'label_text' => 'Med valda möten',
														'default_option' => 'Välj ett alternativ',
														'publish' => 'Publicerade eller opublicerade',
														'unpublish' => 'Avpublicera alla publicerade möten',
														'delete' => 'Ta bort dessa möten',
														'duplicate' => 'Gör en kopia av dessa möten',
														'apply_data_item' => 'Välj en datatyp till dessa möten',
														'delete_extreme_prejudice' => 'Kassera dessa möten för gott?'
													),
											'POI_Link_Title' => 'Ladda ner en POI (Point Of Interest) fil som du sedan kan ladda in i din gps enhet',
											'Map_Link_Title' => 'Se sökningen som karta.',
											'Map_Link_of' => 'av ',
											'Main_Link_Title' => 'Visa endast detta möte.',
											'Root_Decl' => 'Adressen till denna server är ',
											'Root_Page_Title' => 'NA sveriges möteslista',
											'hidden_value' => 'Ingen tillgång'
											);
?>