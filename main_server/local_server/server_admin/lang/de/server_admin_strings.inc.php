<?php
/***********************************************************************/
/** \file   server_admin_strings.inc.php
 * \brief  The strings displayed in the search results for this language (German)
 *
 * This file is part of the Basic Meeting List Toolbox (BMLT).
 *
 * Find out more at: https://bmlt.app
 *
 * BMLT is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License.
 *
 * BMLT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * MIT License for more details.
 *
 * You should have received a copy of the MIT License along with this code.
 * If not, see <https://opensource.org/licenses/MIT>. */

defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

$comdef_server_admin_strings = array(
    'server_admin_disclosure' => 'Server Administration',
    'server_admin_naws_spreadsheet_label' => 'Updated World Committee Codes Spreadsheet',
    'update_world_ids_button_text' => 'Update World Committee Codes',
    'update_world_ids_from_spreadsheet_dropdown_text' => 'Update World Committee Codes (Group IDs from NAWS) from NAWS Spreadsheet',
    'server_admin_error_no_world_ids_updated' => 'No World IDs were updated. This could be because your user does not have permission to update the submitted meetings.',
    'server_admin_error_required_spreadsheet_column' => 'Required column does not exist in the spreadsheet: ',
    'server_admin_error_bmlt_id_not_integer' => 'The provided bmlt_id is not an integer: ',
    'server_admin_error_could_not_create_reader' => 'Could not create reader for file: ',
    'server_admin_error_no_files_uploaded' => 'No files were uploaded.',
    'server_admin_error_service_bodies_already_exist' => 'Service bodies with the following World IDs already exist: ',
    'server_admin_error_meetings_already_exist' => 'Meetings with the following World IDs already exist: ',
    'server_admin_ui_num_meetings_updated' => 'Number of meetings updated: ',
    'server_admin_ui_num_meetings_not_updated' => 'Number of meetings that did not need updating: ',
    'server_admin_ui_warning' => 'WARNING',
    'server_admin_ui_errors' => 'Error(s)',
    'server_admin_ui_meetings_not_found' => 'meetings were found in the spreadsheet that did not exist in the database. This can happen when a meeting is deleted or unpublished. The missing meeting IDs are: ',
    'server_admin_ui_service_bodies_created' => 'Service bodies created: ',
    'server_admin_ui_meetings_created' => 'Meetings created: ',
    'server_admin_ui_users_created' => 'Users created: ',
    'server_admin_ui_refresh_ui_text' => 'Sign out and then sign in again to see the new service bodies, users, and meetings.',
    'import_service_bodies_and_meetings_button_text' => 'Import Service Bodies and Meetings',
    'import_service_bodies_and_meetings_dropdown_text' => 'Import Service Bodies and Meetings from NAWS Export',
    'server_admin_naws_import_spreadsheet_label' => 'NAWS Import Spreadsheet:',
    'server_admin_naws_import_initially_publish' => 'Initialize imported meetings to \'published\': ',
    'server_admin_naws_import_explanation' => 'Uncheck the box to initialize imported meetings to \'unpublished\'. (This is useful if many of the new meetings will need to be edited or deleted, and you don\'t want them showing up in the meantime.)',
    'account_disclosure' => 'Mein Account',
    'account_name_label' => 'Mein Account Name:',
    'account_login_label' => 'Mein Login:',
    'account_type_label' => 'Ich bin ein:',
    'account_type_1' => 'Server Administrator',
    'account_type_2' => 'Service Body Administrator',
    'ServerMapsURL' => 'http://maps.googleapis.com/maps/api/geocode/xml?address=##SEARCH_STRING##&sensor=false',
    'account_type_4' => 'Pathetic Luser Who Shouldn\'t Even Have Access to This Page -The Author of the Software Pooched it BAD!',
    'account_type_5' => 'Service Body Observer',
    'change_password_label' => 'Mein Passwort ändern in:',
    'change_password_default_text' => 'Leave This Alone If You Don\'t Want To Change Your Password',
    'account_email_label' => 'Meine E-Mailadresse:',
    'email_address_default_text' => 'Trage eine E-Mailadresse ein',
    'account_description_label' => 'Meine Beschreibung:',
    'account_description_default_text' => 'Trage eine Beschreibung ein',
    'account_change_button_text' => 'Account Einstellungen ändern',
    'account_change_fader_success_text' => 'Die Account Information wurde erfolgreich geändert',
    'account_change_fader_failure_text' => 'Die Account Information wurde nicht geändert',
    'meeting_editor_disclosure' => 'Meetings bearbeiten',
    'meeting_editor_already_editing_confirm' => 'Du bearbeitest gerade ein anderes Meeting. Möchtest du alle Änderungen in diesem Meeting verlieren?',
    'meeting_change_fader_success_text' => 'Das Meeting wurde erfolgreich geändert',
    'meeting_change_fader_failure_text' => 'Das Meeting wurde nicht geändert',
    'meeting_change_fader_success_delete_text' => 'Das Meeting wurde erfolgreich gelöscht',
    'meeting_change_fader_fail_delete_text' => 'Das Meeting wurde nicht gelöscht',
    'meeting_change_fader_success_add_text' => 'Das neue Meeting wurde erfolgreich hinzugefügt',
    'meeting_change_fader_fail_add_text' => 'Das neue Meeting wurde nicht hinzugefügt',
    'meeting_text_input_label' => 'Suche nach Text:',
    'access_service_body_label' => 'Ich habe Zugriff zu:',
    'meeting_text_input_default_text' => 'Füge Suchtext ein',
    'meeting_text_location_label' => 'Dies ist ein Ort oder eine PLZ',
    'meeting_search_weekdays_label' => 'Suche nach ausgewählten Wochentagen:',
    'meeting_search_weekdays_names' => array('Alle', 'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'),
    'meeting_search_service_bodies_label' => 'Suche nach ausgewählten Service Bodies:',
    'meeting_search_start_time_label' => 'Suche nach Meetings-Anfangszeit:',
    'meeting_search_start_time_all_label' => 'Jede Zeit',
    'meeting_search_start_time_morn_label' => 'Morgen',
    'meeting_search_start_time_aft_label' => 'Nachmittag',
    'meeting_search_start_time_eve_label' => 'Abend',
    'meeting_search_no_results_text' => 'Keine Meetings gefunden',
    'meeting_editor_tab_specifier_text' => 'Suche nach Meetings',
    'meeting_editor_tab_editor_text' => 'Meetings bearbeiten oder erstellen',
    'meeting_editor_create_new_text' => 'Erstelle ein neues Meeting',
    'meeting_editor_location_map_link' => 'Ort auf Karte',
    'meeting_editor_screen_match_ll_button' => 'Setze Koordinaten der Karte auf diese Adresse',
    'meeting_editor_screen_default_text_prompt' => 'Trage einen Text oder eine Zahl ein',
    'meeting_is_published' => 'Meeting ist veröffentlicht',
    'meeting_unpublished_note' => 'Note: Unpublishing a meeting indicates a temporary closure. If this meeting has closed permanently, please delete it.',
    'meeting_editor_screen_meeting_name_label' => 'Meetings-Name:',
    'meeting_editor_screen_meeting_name_prompt' => 'Trage einen Meetings-Namen ein',
    'meeting_editor_screen_meeting_weekday_label' => 'Wochentag:',
    'meeting_editor_screen_meeting_start_label' => 'Meetings-Anfangszeit:',
    'meeting_editor_screen_meeting_time_zone_label' => 'Meeting Time Zone:',
    'meeting_editor_screen_meeting_am_label' => 'AM',
    'meeting_editor_screen_meeting_pm_label' => 'PM',
    'meeting_editor_screen_meeting_noon_label' => '12:00 Uhr',
    'meeting_editor_screen_meeting_midnight_label' => '24:00 Uhr',
    'meeting_editor_screen_meeting_duration_label' => 'Dauer:',
    'meeting_editor_screen_meeting_oe_label' => 'Ende offen',
    'meeting_editor_screen_meeting_cc_label' => 'World Service Committee Code:',
    'meeting_editor_screen_meeting_cc_advice' => 'Normally leave this field alone (see documentation).',  // TODO: translate
    'meeting_editor_screen_meeting_contact_label' => 'Meetings E-Mail Kontakt:',
    'meeting_editor_screen_meeting_contact_prompt' => 'Trage eine E-Mail for einen Kontakt nur für dieses Meeting ein',
    'meeting_editor_screen_meeting_sb_label' => 'Service Body:',
    'meeting_editor_screen_meeting_sb_default_value' => 'Kein Service Body ausgewählt',
    'meeting_editor_screen_meeting_longitude_label' => 'Longitude:',
    'meeting_editor_screen_meeting_longitude_prompt' => 'Trage einen Längengrad ein',
    'meeting_editor_screen_meeting_latitude_label' => 'Breitengrad:',
    'meeting_editor_screen_meeting_latitude_prompt' => 'Trage einen Breitengrad ein',
    'meeting_editor_screen_meeting_location_label' => 'Institution:',
    'meeting_editor_screen_meeting_location_prompt' => 'Trage einen Institutions-Namen ein (Wie einen Gebäudenamen)',
    'meeting_editor_screen_meeting_info_label' => 'Zusätzliche Informationen:',
    'meeting_editor_screen_meeting_info_prompt' => 'Trage zusätzliche Location Informationen ein',
    'meeting_editor_screen_meeting_street_label' => 'Straße:',
    'meeting_editor_screen_meeting_street_prompt' => 'Trage eine Straße ein',
    'meeting_editor_screen_meeting_neighborhood_label' => 'Nachbarschaft:',
    'meeting_editor_screen_meeting_neighborhood_prompt' => 'Trage eine Nachbarschaft ein (keinen Stadtteil oder Stadtbezirk)',
    'meeting_editor_screen_meeting_borough_label' => 'Stadtteil:',
    'meeting_editor_screen_meeting_borough_prompt' => 'Trage einen Stadtteil oder Stadtbezirk ein (keine Nachbarschaft)',
    'meeting_editor_screen_meeting_city_label' => 'Stadt:',
    'meeting_editor_screen_meeting_city_prompt' => 'Trage eine Stadt ein (Keine Nation oder Stadtbezirk)',
    'meeting_editor_screen_meeting_county_label' => 'Land:',
    'meeting_editor_screen_meeting_county_prompt' => 'Trage ein Land ein',
    'meeting_editor_screen_meeting_state_label' => 'Bundesstaat/Bundesland:',
    'meeting_editor_screen_meeting_state_prompt' => 'Trage ein Bundesstaat/Bundesland ein',
    'meeting_editor_screen_meeting_zip_label' => 'Postleitzahl:',
    'meeting_editor_screen_meeting_zip_prompt' => 'Trage eine Postleitzahl ein',
    'meeting_editor_screen_meeting_nation_label' => 'Nation:',
    'meeting_editor_screen_meeting_nation_prompt' => 'Trage eine Nation ein',
    'meeting_editor_screen_meeting_comments_label' => 'Comments:',
    'meeting_editor_screen_meeting_train_lines_label' => 'Train Lines:',
    'meeting_editor_screen_meeting_bus_lines_label' => 'Bus Lines:',
    'meeting_editor_screen_meeting_phone_meeting_number_label' => 'Phone Meeting Dial-in Number:',
    'meeting_editor_screen_meeting_phone_meeting_number_prompt' => 'Enter the dial-in number for a phone or virtual meeting',
    'meeting_editor_screen_meeting_virtual_meeting_link_label' => 'Virtual Meeting Link:',
    'meeting_editor_screen_meeting_virtual_meeting_link_prompt' => 'Enter the link for a virtual meeting',
    'meeting_editor_screen_meeting_virtual_meeting_additional_info_label' => 'Virtuelles Meeting - zusätzliche Informationen:',
    'meeting_editor_screen_meeting_virtual_meeting_additional_info_prompt' => 'Enter any additional information for joining the virtual meeting, including directly from the app. For example, if the meeting uses Zoom, "Zoom ID: 456 033 8613, Passcode: 1953" would be appropriate.',
    'meeting_editor_screen_meeting_venue_type' => 'Venue Type:',
    'meeting_editor_screen_meeting_venue_type_inperson' => 'In-Person',
    'meeting_editor_screen_meeting_venue_type_virtual' => 'Virtual',
    'meeting_editor_screen_meeting_venue_type_virtualTC' => 'Virtual (temporarily replacing an in-person)',
    'meeting_editor_screen_meeting_venue_type_hybrid' => 'Hybrid (both in-person and virtual)',
    'meeting_editor_screen_meeting_venue_type_validation' => 'You must select a venue type.',
    'meeting_editor_screen_meeting_virtual_info_missing' => 'Virtual or hybrid meetings must have a Virtual Meeting Link, a Phone Meeting Dial-in Number, or Virtual Meeting Additional Information',
    'meeting_editor_screen_meeting_location_warning' => 'Meeting should have a location (at least a city/town and state/province, or a zip/postal code).',
    'meeting_editor_screen_meeting_address_warning' => 'In-person or hybrid meetings should have a street address.',
    'meeting_editor_screen_meeting_url_validation' => 'Virtual Meeting Link is not a valid URL.',
    'meeting_editor_screen_meeting_url_or_phone_warning' => 'Virtual or hybrid meetings should have either a Virtual Meeting Link or a Phone Meeting Dial-in Number',
    'meeting_editor_screen_meeting_additional_warning' => 'Please also fill in Virtual Meeting Additional Information if there is a Virtual Meeting Link.',
    'meeting_editor_screen_meeting_validation_warning' => 'There are warnings.  Are you sure you want to save anyway?  If not, press \'cancel\' and go to the Location tab to see the warnings in place and address them.',
    'meeting_editor_screen_meeting_validation_failed' => 'Unable to save due to input errors.  Please go to the Location tab to address them, and then retry saving.  Errors: ',
    'meeting_editor_screen_meeting_validation_warnings' => 'Input warnings shown on the Location tab: ',
    'meeting_editor_screen_meeting_contact_name_1_label' => 'Contact 1 Name:',
    'meeting_editor_screen_meeting_contact_email_1_label' => 'Contact 1 Email:',
    'meeting_editor_screen_meeting_contact_phone_1_label' => 'Contact 1 Phone:',
    'meeting_editor_screen_meeting_contact_name_2_label' => 'Contact 2 Name:',
    'meeting_editor_screen_meeting_contact_email_2_label' => 'Contact 2 Email:',
    'meeting_editor_screen_meeting_contact_phone_2_label' => 'Contact 2 Phone:',
    'meeting_editor_screen_meeting_publish_search_prompt' => 'Suche nach:',
    'meeting_editor_screen_meeting_publish_search_pub' => 'nur veröfentlichte Meetings',
    'meeting_editor_screen_meeting_publish_search_unpub' => 'nur unveröfentlichte Meetings',
    'meeting_editor_screen_meeting_visibility_advice' => 'Dies wird in normalen Suchen nie angezeigt.',
    'meeting_editor_screen_meeting_publish_search_all' => 'Alle Meetings',
    'meeting_editor_screen_meeting_create_button' => 'Erstelle ein neues Meeting',
    'meeting_editor_screen_delete_button' => 'Lösche dieses Meeting',
    'meeting_editor_screen_delete_button_confirm' => 'Bist du sicher, dass du dieses Meeting löschen möchtest?',
    'meeting_editor_screen_cancel_button' => 'abbrechen',
    'logout' => 'Abmelden',
    'meeting_editor_screen_cancel_confirm' => 'Bist du sicher, dass du die Bearbeitung abbrechen möchtest und alle Änderungen verlieren möchtest?',
    'meeting_lookup_failed' => 'Die Suche nach der Adresse schlug fehl.',
    'meeting_lookup_failed_not_enough_address_info' => 'Dies ist keine gültige Adresse für eine Suche.',
    'meeting_create_button_name' => 'Speichere dies als ein neues Meeting',
    'meeting_saved_as_a_copy' => 'Speichere dies als eine Kopie (erstellt ein neues Meeting)',
    'meeting_save_buttonName' => 'Speichere die Änderungen an diesem Meeting',
    'meeting_editor_tab_bar_basic_tab_text' => 'Basic',
    'meeting_editor_tab_bar_location_tab_text' => 'Institution',
    'meeting_editor_tab_bar_format_tab_text' => 'Format',
    'meeting_editor_tab_bar_other_tab_text' => 'Sonstiges',
    'meeting_editor_tab_bar_history_tab_text' => 'History',
    'meeting_editor_result_count_format' => '%d Meetings gefunden',
    'meeting_id_label' => 'Meeting ID:',
    'meeting_editor_default_zoom' => '13',
    'meeting_editor_default_weekday' => '2',
    'meeting_editor_default_start_time' => '20:30:00',
    'login_banner' => 'Basic Meeting List Toolbox',
    'login_underbanner' => 'Root Server Administration Console',
    'login' => 'Login ID',
    'password' => 'Passwort',
    'button' => 'Log In',
    'cookie' => 'Man muss Cookies erlauben um diesen Server zu verwalten.',
    'noscript' => 'Man kann diese Seite nicht ohne JavaScript verwalten.',
    'title' => 'Bitte zum Verwalten des Servers einloggen.',
    'edit_Meeting_object_not_found' => 'ERROR: Dieses Meeting wurde nicht gefunden.',
    'edit_Meeting_object_not_changed' => 'ERROR: Dieses Meeting wurde nicht geändert.',
    'edit_Meeting_auth_failure' => 'Du hast keine Berechtigung, dieses Meeting zu bearbeiten.',
    'not_auth_1' => 'NOT AUTHORIZED',
    'not_auth_2' => 'Du hast keine Berechtigung, diesen Server zu verwalten.',
    'not_auth_3' => 'Es gab ein Problem mit Benutzernamen oder Passwort.',
    'email_format_bad' => 'Das Format der eingefügten E-Mailadresse ist nicht richtig.',
    'history_header_format' => '<div class="bmlt_admin_meeting_history_list_item_line_div history_item_header_div"><span class="bmlt_admin_history_list_header_date_span">%s</span><span class="bmlt_admin_history_list_header_user_span">by %s</span></div>',
    'history_no_history_available_text' => '<h1 class="bmlt_admin_no_history_available_h1">No History Available For This Meeting</h1>',
    'service_body_editor_disclosure' => 'Service Body Administration',
    'service_body_change_fader_success_text' => 'Der Service Body wurde erfolgreich geändert',
    'service_body_change_fader_fail_text' => 'Die Änderung des Service Body schlug fehl',
    'service_body_editor_screen_sb_id_label' => 'ID:',
    'service_body_editor_screen_sb_name_label' => 'Name:',
    'service_body_name_default_prompt_text' => 'Trage den Namen dieses Service Body ein',
    'service_body_parent_popup_label' => 'Service Body Parent:',
    'service_body_parent_popup_no_parent_option' => 'No Parent (Top-Level)',
    'service_body_editor_screen_sb_admin_user_label' => 'Primary Admin:',
    'service_body_editor_screen_sb_admin_description_label' => 'Beschreibung:',
    'service_body_description_default_prompt_text' => 'Trage eine Beschreibung dieses Service Body ein',
    'service_body_editor_screen_sb_admin_email_label' => 'Contact Email:',
    'service_body_email_default_prompt_text' => 'Trage eine Kontakt-E-Mailadresse für deisen Service Body ein',
    'service_body_editor_screen_sb_admin_uri_label' => 'Web Site URL:',
    'service_body_uri_default_prompt_text' => 'Trage eine Web Site URL für diesen Service Body ein',
    'service_body_editor_screen_sb_admin_full_editor_label' => 'Volle Meetingslisten Bearbeiter:',
    'service_body_editor_screen_sb_admin_full_editor_desc' => 'Diese Benutzer können alle Meetings in diesem Service Body bearbeiten.',
    'service_body_editor_screen_sb_admin_editor_label' => 'Basic Meeting List Bearbeiter:',
    'service_body_editor_screen_sb_admin_editor_desc' => 'Diese Benutzer können alle Meetings in diesem Service Body bearbeiten, aber nur, wenn sie unveröffentlicht sind.',
    'service_body_editor_screen_sb_admin_observer_label' => 'Beobachter:',
    'service_body_editor_screen_sb_admin_observer_desc' => 'Diese Benutzer können versteckte Informationen sehen (wie E-Mailadressen), aber können nichts bearbeiten.',
    'service_body_dirty_confirm_text' => 'Du hast den Service Body verändert. möchtest due diese Änderungen verlieren?',
    'service_body_save_button' => 'Speichere diese Änderungen am Service Body',
    'service_body_create_button' => 'Erstelle diesen Service Body',
    'service_body_delete_button' => 'Lösche diesen Service Body',
    'service_body_delete_perm_checkbox' => 'Lösche diesen Service Body dauerhaft',
    'service_body_delete_button_confirm' => 'Are you sure that you want to delete this Service body? Make sure that all meetings are either removed or transferred to another service body before performing this function.',
    'service_body_delete_button_confirm_perm' => 'Dieser Service Body wird dauerhaft gelöscht werden!',
    'service_body_change_fader_create_success_text' => 'Der Service Body wurde erfolgreich erstellt',
    'service_body_change_fader_create_fail_text' => 'Das Erstellen des Serice Body schlug fehl',
    'service_body_change_fader_delete_success_text' => 'Der Service Body wurde erfolgreich gelöscht',
    'service_body_change_fader_delete_fail_text' => 'Das Löschen des Serice Body schlug fehl',
    'service_body_change_fader_fail_no_data_text' => 'Das Ändern des Serice Body schlug fehl, weil keine Daten geliefert wurden',
    'service_body_change_fader_fail_cant_find_sb_text' => 'Das Ändern des Serice Body schlug fehl, weil der Service Body nicht gefunden wurde',
    'service_body_change_fader_fail_cant_update_text' => 'Das Ändern des Serice Body schlug fehl, weil der Service Body nicht upgedatet wurde',
    'service_body_change_fader_fail_bad_hierarchy' => 'Das Ändern des Serice Body schlug fehl, weil der gewählte Eigentümer Service Body unter diesem Service Body ist, und nicht benutzt werden kann',
    'service_body_cancel_button' => 'Zurücksetzen auf Ursprung',
    'service_body_editor_type_label' => 'Service Body Type:',
    'service_body_editor_type_c_comdef_service_body__GRP__' => 'Gruppe',
    'service_body_editor_type_c_comdef_service_body__COP__' => 'Co-Op',
    'service_body_editor_type_c_comdef_service_body__ASC__' => 'Gebiets Service Kommitee',
    'service_body_editor_type_c_comdef_service_body__RSC__' => 'Regionale Service Konferenz',
    'service_body_editor_type_c_comdef_service_body__WSC__' => 'World Service Conference',
    'service_body_editor_type_c_comdef_service_body__MAS__' => 'Metro Area',
    'service_body_editor_type_c_comdef_service_body__ZFM__' => 'Zonal Forum',
    'service_body_editor_type_c_comdef_service_body__GSU__' => 'Group Service Unit',
    'service_body_editor_type_c_comdef_service_body__LSU__' => 'Local Service Unit',
    'service_body_editor_screen_helpline_label' => 'Helpline:',
    'service_body_editor_screen_helpline_prompt' => 'Enter The Helpline Telephone Number',
    'service_body_editor_uri_naws_format_text' => 'Get The Meetings For This Service Body As A NAWS-Compatible File',
    'edit_Meeting_meeting_id' => 'Meeting ID:',
    'service_body_editor_create_new_sb_option' => 'Erstelle einen neuen Service Body',
    'service_body_editor_screen_world_cc_label' => 'World Service Committee Code:',
    'service_body_editor_screen_world_cc_prompt' => 'Trage einen Service Committee Code ein',
    'user_editor_disclosure' => 'Benutzerverwaltung',
    'user_editor_create_new_user_option' => 'CErstelle einen neuen Benutzer',
    'user_editor_screen_sb_id_label' => 'ID:',
    'user_editor_account_login_label' => 'Benutzer Login:',
    'user_editor_login_default_text' => 'Trage den Benutzer Login ein',
    'user_editor_account_type_label' => 'Benutzer ist ein:',
    'user_editor_user_owner_label' => 'Owned By: ', // TODO translate
    'user_editor_account_type_1' => 'Server Administrator',
    'user_editor_account_type_2' => 'Service Body Administrator',
    'user_editor_account_type_3' => 'Service Body Editor',
    'user_editor_account_type_5' => 'Service Body Observer',
    'user_editor_account_type_4' => 'Deaktivierter Benutzer',
    'user_editor_account_name_label' => 'Benutzername:',
    'user_editor_name_default_text' => 'Trage den Benutzernamen ein',
    'user_editor_account_description_label' => 'Beschreibung:',
    'user_editor_description_default_text' => 'Trage eine Benutzerbeschreibung ein',
    'user_editor_account_email_label' => 'E-Mailadresse:',
    'user_editor_email_default_text' => 'Trage die Benutzer-E-Mailadresse ein',
    'user_change_fader_success_text' => 'Der Benutzer wurde erfolgreich geändert',
    'user_change_fader_fail_text' => 'Das Ändern des Benutzers schlug fehl',
    'user_change_fader_create_success_text' => 'Der Benutzer wurde erfolgreich erstellt',
    'user_change_fader_create_fail_text' => 'Das Erstellen des Benutzers schlug fehl',
    'user_change_fader_delete_success_text' => 'Der Benutzer wurde erfolgreich gelöscht',
    'user_change_fader_delete_fail_text' => 'Das Löschen des Benutzers schlug fehl',
    'user_save_button' => 'Änderungen an diesem Benutzer speichern',
    'user_create_button' => 'Erstelle diesen neuen Benutzer',
    'user_cancel_button' => 'Zurücksetzen auf Ursprung',
    'user_delete_button' => 'Lösche diesen Benutzer',
    'user_delete_perm_checkbox' => 'Lösche diesen Benutzer dauerhaft',
    'user_password_label' => 'Ändere das Passwort zu:',
    'user_new_password_label' => 'Setze das Passwort zu:',
    'user_password_default_text' => 'Lass das frei, wenn du das Psswort nicht ändern willst',
    'user_new_password_default_text' => 'Du must ein Passwort für einen neuen Benutzer eintragen',
    'user_dirty_confirm_text' => 'Du hast den Benutzer verändert. möchtest due diese Änderungen verlieren?',
    'user_delete_button_confirm' => 'Bist du sicher, dass du diesen Benutzer löschen möchtest?',
    'user_delete_button_confirm_perm' => 'Dieser Benutzer wird dauerhaft gelöscht werden!',
    'user_create_password_alert_text' => 'Neue Benutzer brauchen ein Passwort. Du hast noch kein Passwort eingetragen.',
    'user_change_fader_fail_cant_find_sb_text' => 'Das Ändern des Benutzers  schlug fehl, weil der Benutzer nicht gefunden wurde',
    'user_change_fader_fail_cant_update_text' => 'Das Ändern des Benutzers schlug fehl, weil der Benutzer nicht upgedatet wurde',
    'format_editor_disclosure' => 'Format Verwaltung',
    'format_change_fader_change_success_text' => 'Das Format wurde erfolgreich geändert',
    'format_change_fader_change_fail_text' => 'Das Ändern des Formats schlug fehl',
    'format_change_fader_create_success_text' => 'Das Format wurde erfolgreich erstellt',
    'format_change_fader_create_fail_text' => 'Das Erstellen des Formats schlug fehl',
    'format_change_fader_delete_success_text' => 'Das Format wurde erfolgreich gelöscht',
    'format_change_fader_delete_fail_text' => 'Das Löschen des Formats schlug fehl',
    'format_change_fader_fail_no_data_text' => 'Das Ändern des Formats schlug fehl, weil keine Daten geliefert wurden',
    'format_change_fader_fail_cant_find_sb_text' => 'Das Ändern des Formatss  schlug fehl, weil das Format nicht gefunden wurde',
    'format_change_fader_fail_cant_update_text' => 'Das Ändern des Formats schlug fehl, weil deas Format nicht upgedatet wurde',
    'format_editor_name_default_text' => 'Trage eine kurze Beschreibung ein',
    'format_editor_description_default_text' => 'Trage eine detailiertere Beschreibung ein',
    'format_editor_create_format_button_text' => 'Erstelle ein neues Format',
    'format_editor_cancel_create_format_button_text' => 'abbrechen',
    'format_editor_create_this_format_button_text' => 'Erstelle dieses Format',
    'format_editor_change_format_button_text' => 'Ändere dieses Format',
    'format_editor_delete_format_button_text' => 'Lösche dieses Format',
    'format_editor_reset_format_button_text' => 'Zurücksetzen auf Ursprung',
    'need_refresh_message_fader_text' => 'Vor Benutzung dieses Bereiches sollte diese Seite neu geladen werden',
    'need_refresh_message_alert_text' => 'Weil Änderungen in der Service Body Verwaltung, Benutzerverwaltung oder Format Verwaltung vorgenommen wurden, ist die Information, die in diesem Bereich dargestellt wird, nicht mehr akkurat, also muss die Seite neu geladen werden. Der einfachste Weg dies zu tun ist sich abzumelden und sich wieder anzumelden.',
    'format_editor_delete_button_confirm' => 'Bist du sicher, dass du dieses Format löschen willst?',
    'format_editor_delete_button_confirm_perm' => 'Dieses Format wird dauerhaft gelöscht werden!',
    'format_editor_missing_key' => 'This format should have an entry for every language (at least a key).',   // TODO: translate
    'format_editor_reserved_key' => 'This key is reserved for a venue type format - please use something different.',       // TODO: translate
    'min_password_length_string' => 'Das Passwort ist zu kurz! Es muss mindestens %d zeichen betragen!',
    'AJAX_Auth_Failure' => 'Authorisation fehlgeschlagen für diese Operation. Es kann sein, dass ein Problem mit der Serverkonfiguration besteht.',
    'Maps_API_Key_Warning' => 'There is a problem with the Google Maps API Key.', // TODO translate
    'Observer_Link_Text' => 'Meeting Browser',
    'Data_Transfer_Link_Text' => 'Import Meeting Data (WARNING: Replaces Current Data!)',
    'MapsURL' => 'http://maps.google.com/maps?q=##LAT##,##LONG##+(##NAME##)&amp;ll=##LAT##,##LONG##',
    'hidden_value' => 'Cannot Display Data -Unauthorized',
    'Value_Prompts' => array(
        'id_bigint' => 'Meeting ID',
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
    'world_format_codes_prompt' => 'NAWS Format:',
    'world_format_codes' => array(
        '' => 'Keine',
        'OPEN' => 'Offen',
        'CLOSED' => 'Geschlossen',
        'WCHR' => 'Rollstuhlzugang',
        'BEG' => 'Neuankömmling/Newcomer',
        'BT' => 'Basic Text',
        'CAN' => 'Kerzenlicht',
        'CPT' => '12 Konzepte',
        'CW' => 'Kinder willkommen',
        'DISC' => 'Diskussion/Teilen',
        'GL' => 'Schwul/Lesbisch',
        'IP' => 'Thema Faltblätter',
        'IW' => 'Es funktioniert',
        'JFT' => 'Nur für Heute',
        'LC' => 'Thema Living Clean',
        'LIT' => 'Literaturmeeting',
        'M' => 'Männer',
        'MED' => 'Meditation',
        'NS' => 'Non-Smoking',
        'QA' => 'Frage & Antwort',
        'RA' => 'Eingeschränkter Zutritt',
        'S-D' => 'Sprecher / Diskussion',
        'SMOK' => 'Raucher',
        'SPK' => 'Sprecher',
        'STEP' => 'Schritte',
        'SWG' => 'Schritteleitfaden',
        'TOP' => 'Themenmeeting',
        'TRAD' => 'Traditionenmeeting',
        'VAR' => 'Format variatiiert',
        'W' => 'Frauen',
        'Y' => 'Junge Menschen',
        'LANG' => 'Fremdsprache',
        'GP' => 'Guiding Principles', // TODO translate
        'NC' => 'No Children', // TODO translate
        'CH' => 'Closed Holidays', // TODO translate
        'VM' => 'Virtual', // TODO translate
        'HYBR' => 'Virtual and In-person', // TODO translate
        'TC' => 'Facility Temporarily Closed' // TODO translate
    ),
    'format_type_prompt' => 'Format Type:',
    'format_type_codes' => array(
        '' => 'None',
        'FC1' => 'Meeting Format (Speaker, Book Study, etc.)',
        'FC2' => 'Location Code (Wheelchair Accessible, Limited Parking, etc.)',
        'FC3' => 'Common Needs and Restrictions (Mens Meeting, LGTBQ, No Children, etc.)',
        'O' => 'Attendance by non-addicts (Open, Closed)',
        'LANG' => 'Language',
        'ALERT' => 'Format should be especially prominent (Clean requirement, etc.)',
    ),

    'cookie_monster' => 'Diese Website benutzt ein Cookie, um Ihre bevorzugte Sprache zu speichern.',
    'main_prompts' => array(
        'id_bigint' => 'ID',
        'worldid_mixed' => 'World ID',
        'shared_group_id_bigint' => 'Unused',
        'service_body_bigint' => 'Service Body ID',
        'weekday_tinyint' => 'Wochentag',
        'start_time' => 'Start Zeit',
        'duration_time' => 'Dauer',
        'time_zone' => 'Time Zone',
        'formats' => 'Formate',
        'lang_enum' => 'Sprache',
        'longitude' => 'Längengrad',
        'latitude' => 'Breitengrad',
        'published' => 'veröffentlicht',
        'email_contact' => 'E-Mail Kontakt',
    ),
    'check_all' => 'Check All',
    'uncheck_all' => 'Uncheck All',
    'automatically_calculated_on_save' => 'Automatically calculated on save.'
);

$email_contact_strings = array(
    'meeting_contact_form_subject_format' => "[MEETING LIST CONTACT] %s",
    'meeting_contact_message_format' => "%s\n--\nDiese Nachricht betrifft das  Meeting namens\"%s\", dzss sich trifft um %s, immer %s.\nBrowser Link: %s\nEdit Link: %s\nEs wurde direkt vom Webserver der Meetingliste gesendet, und der Absender kennt Ihre E-Mail-Adresse nicht.\nBitte beachten Sie, dass Ihre E-Mail-Adresse beim Antworten angezeigt wird.\nWenn Sie \"Reply All\" verwenden, Wenn es mehrere E-Mail-Empfänger gibt, könnten Sie die E-Mail-Adressen anderer Personen anzeigen.\nBitte respektieren Sie die Privatsphäre und Anonymität der Menschen einschließlich des ursprünglichen Absenders dieser Nachricht."
);

$change_type_strings = array(
    '__THE_MEETING_WAS_CHANGED__' => 'Das Meeting wurde geändert.',
    '__THE_MEETING_WAS_CREATED__' => 'Das Meeting wurde erstellt.',
    '__THE_MEETING_WAS_DELETED__' => 'Das Meeting wurde gelöscht.',
    '__THE_MEETING_WAS_ROLLED_BACK__' => 'Das Meeting wurde auf die vorherige Version zurück gesetzt.',

    '__THE_FORMAT_WAS_CHANGED__' => 'Das Format wurde geändert.',
    '__THE_FORMAT_WAS_CREATED__' => 'Das Format wurde erstellt.',
    '__THE_FORMAT_WAS_DELETED__' => 'Das Format wurde gelöscht.',
    '__THE_FORMAT_WAS_ROLLED_BACK__' => 'Das Format wurde auf die vorherige Version zurück gesetzt.',

    '__THE_SERVICE_BODY_WAS_CHANGED__' => 'Der service body wurde geändert.',
    '__THE_SERVICE_BODY_WAS_CREATED__' => 'Der service body wurde erstellt.',
    '__THE_SERVICE_BODY_WAS_DELETED__' => 'Der service body wurde gelöscht.',
    '__THE_SERVICE_BODY_WAS_ROLLED_BACK__' => 'Der service body wurde auf die vorherige Version zurück gesetzt.',

    '__THE_USER_WAS_CHANGED__' => 'Der Benutzer  wurde geändert.',
    '__THE_USER_WAS_CREATED__' => 'Der Benutzer wurde erstellt.',
    '__THE_USER_WAS_DELETED__' => 'Der Benutzer wurde gelöscht.',
    '__THE_USER_WAS_ROLLED_BACK__' => 'Der Benutzer wurde auf die vorherige Version zurück gesetzt.',

    '__BY__' => 'by',
    '__FOR__' => 'for'
);

$detailed_change_strings = array(
    'was_changed_from' => 'was changed from',
    'to' => 'to',
    'was_changed' => 'wurde geändert',
    'was_added_as' => 'wurde hinzugefügt als',
    'was_deleted' => 'wurde gelöscht',
    'was_published' => 'Das Meeting wurde veröffentlicht',
    'was_unpublished' => 'Das Meeting wurde deaktiviert',
    'formats_prompt' => 'Das Meetings-Format',
    'duration_time' => 'Die Meetings-Dauer',
    'start_time' => 'Die Meetings Start-Zeit',
    'longitude' => 'Der Meetings-Längengrad',
    'latitude' => 'Der Meetings-Breitengrad',
    'sb_prompt' => 'Das Meeting änderte seinen Service Body von',
    'id_bigint' => 'Die Meetings-ID',
    'lang_enum' => 'Die Meetings-Sprache',
    'worldid_mixed' => 'Die gemeinsame Group ID',  // TODO: translate The World Committee Code
    'weekday_tinyint' => 'Der Tag der Woche, an dem das Meeting stattfindet',
    'non_existent_service_body' => 'Dieser Service Body existiert nicht mehr',
);

defined('_END_CHANGE_REPORT') or define('_END_CHANGE_REPORT', '.');
