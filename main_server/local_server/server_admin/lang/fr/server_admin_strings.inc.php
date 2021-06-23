<?php
/***********************************************************************/
/** \file   server_admin_strings.inc.php
 * \brief  The strings displayed in the server administration console (French)
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

$comdef_server_admin_strings = array('server_admin_disclosure' => 'Server Administration',
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
    'account_disclosure' => 'Mon compte',
    'account_name_label' => 'Mon nom de compte:',
    'account_login_label' => 'Mon Login:',
    'account_type_label' => 'Je suis un:',
    'account_type_1' => 'Administrateur du Serveur',
    'account_type_2' => 'Administrateur de composante de structure de services',
    'ServerMapsURL' => 'https://maps.googleapis.com/maps/api/geocode/xml?address=##SEARCH_STRING##&sensor=false',
    'account_type_4' => 'Pathetic Luser Who Shouldn\'t Even Have Access to This Page -The Author of the Software Pooched it BAD!',
    'account_type_5' => 'Observateur',
    'change_password_label' => 'Changer mon mot de passe pour:',
    'change_password_default_text' => 'Laissez libre si vous ne voulez pas changer votre mot de passe',
    'account_email_label' => 'Mon adresse e-mail:',
    'email_address_default_text' => 'Saisissez une adresse e-mail',
    'account_description_label' => 'ma description:',
    'account_description_default_text' => 'Entrez une description',
    'account_change_button_text' => 'Modifier mes paramètres de compte',
    'account_change_fader_success_text' => 'L\'information du compte a été changé avec succès',
    'account_change_fader_failure_text' => 'L\'information du compte n\'a pas été modifié',
    'meeting_editor_disclosure' => 'Éditeur de réunion',
    'meeting_editor_already_editing_confirm' => 'Vous êtes en train d\'éditer une autre réunion. Voulez-vous perdre tous les changements de cette réunion?',
    'meeting_change_fader_success_text' => 'La réunion a été changé avec succès',
    'meeting_change_fader_failure_text' => 'La réunion n\'a pas été modifié',
    'meeting_change_fader_success_delete_text' => 'La réunion a été supprimé avec succès',
    'meeting_change_fader_fail_delete_text' => 'La réunion n\'a pas été supprimé',
    'meeting_change_fader_success_add_text' => 'La nouvelle réunion a été ajouté avec succès',
    'meeting_change_fader_fail_add_text' => 'La nouvelle réunion n\'a pas été ajouté',
    'meeting_text_input_label' => 'Recherche contextuelle:',
    'access_service_body_label' => 'J\'ai accès à:',
    'meeting_text_input_default_text' => 'Saisissez un mot-clé pour la recherche contextuelle',
    'meeting_text_location_label' => 'Il s\'agit d\'une Ville ou code postal',
    'meeting_search_weekdays_label' => 'Recherche par jour de semaine:',
    'meeting_search_weekdays_names' => array('Tous', 'Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'),
    'meeting_search_service_bodies_label' => 'Choisir parmi les comités:',
    'meeting_search_start_time_label' => 'Recherche par réunion Heure de début:',
    'meeting_search_start_time_all_label' => 'N\'importe quand',
    'meeting_search_start_time_morn_label' => 'Matin',
    'meeting_search_start_time_aft_label' => 'Après-midi',
    'meeting_search_start_time_eve_label' => 'Soir',
    'meeting_search_no_results_text' => 'Pas de réunions trouvés',
    'meeting_editor_tab_specifier_text' => 'Rechercher des Réunions',
    'meeting_editor_tab_editor_text' => 'Modifier Réunions',   // TODO: change to 'Edit Or Create Meetings'
    'meeting_editor_create_new_text' => 'Créer une nouvelle réunion',
    'meeting_editor_location_map_link' => 'Carte de localisation',
    'meeting_editor_screen_match_ll_button' => 'Optimiser la longitude et la latitude par rapport cette adresse',
    'meeting_editor_screen_default_text_prompt' => 'Entrez du texte ou un nombre',
    'meeting_is_published' => 'Réunion est publié',
    'meeting_unpublished_note' => 'Note: Unpublishing a meeting indicates a temporary closure. If this meeting has closed permanently, please delete it.',
    'meeting_editor_screen_meeting_name_label' => 'Nom de réunion:',
    'meeting_editor_screen_meeting_name_prompt' => 'Entrer un nom de réunion',
    'meeting_editor_screen_meeting_weekday_label' => 'jour de la semaine:',
    'meeting_editor_screen_meeting_start_label' => 'Réunion Heure de début:',
    'meeting_editor_screen_meeting_time_zone_label' => 'Meeting Time Zone:',
    'meeting_editor_screen_meeting_am_label' => 'AM',
    'meeting_editor_screen_meeting_pm_label' => 'PM',
    'meeting_editor_screen_meeting_noon_label' => 'Midi',
    'meeting_editor_screen_meeting_midnight_label' => 'Minuit',
    'meeting_editor_screen_meeting_duration_label' => 'Durée:',
    'meeting_editor_screen_meeting_oe_label' => 'À composition non limitée:',
    'meeting_editor_screen_meeting_cc_label' => 'Code Comité mondial:',
    'meeting_editor_screen_meeting_cc_advice' => 'Normally leave this field alone (see documentation).',  // TODO: translate
    'meeting_editor_screen_meeting_contact_label' => 'Contact E-mail de la réunion:',
    'meeting_editor_screen_meeting_contact_prompt' => 'Entrez un E-mail pour un contact spécifique uniquement de cette réunion',
    'meeting_editor_screen_meeting_sb_label' => 'composante de structure de service:',
    'meeting_editor_screen_meeting_sb_default_value' => 'Sans sélection de composante de structure de service',
    'meeting_editor_screen_meeting_longitude_label' => 'Longitude:',
    'meeting_editor_screen_meeting_longitude_prompt' => 'Entrer la Longitude',
    'meeting_editor_screen_meeting_latitude_label' => 'Latitude:',
    'meeting_editor_screen_meeting_latitude_prompt' => 'Entrer la Latitude',
    'meeting_editor_screen_meeting_location_label' => 'Emplacement:',
    'meeting_editor_screen_meeting_location_prompt' => 'Entrez le nom de l\'emplacement nom (comme un nom d\'édifice)',
    'meeting_editor_screen_meeting_info_label' => 'Détails:',
    'meeting_editor_screen_meeting_info_prompt' => 'Entrer infomations additionels de l\'emplacement',
    'meeting_editor_screen_meeting_street_label' => 'Adresse:',
    'meeting_editor_screen_meeting_street_prompt' => 'Entrer l\'adresse',
    'meeting_editor_screen_meeting_neighborhood_label' => 'Quartier:',
    'meeting_editor_screen_meeting_neighborhood_prompt' => 'Entrer le nom du quartier (Pas d\'arrondissement ni de secteur)',
    'meeting_editor_screen_meeting_borough_label' => 'L\'arrondissement de la ville ou secteur:',
    'meeting_editor_screen_meeting_borough_prompt' => 'Entrer un nom d\'arrondissement ou de secteur (Pas de quartier)',
    'meeting_editor_screen_meeting_city_label' => 'Ville / Municipalité:',
    'meeting_editor_screen_meeting_city_prompt' => 'Entrer le nom d\'une ville ou Municipalité (Pas de compté ni d\'arrondissement)',
    'meeting_editor_screen_meeting_county_label' => 'Compté / Région:',
    'meeting_editor_screen_meeting_county_prompt' => 'Entrer le nom de Compté ou de Région',
    'meeting_editor_screen_meeting_state_label' => 'Province:',
    'meeting_editor_screen_meeting_state_prompt' => 'Entrer le mom de la Province',
    'meeting_editor_screen_meeting_zip_label' => 'Code Postal:',
    'meeting_editor_screen_meeting_zip_prompt' => 'Entrer le Code Postal',
    'meeting_editor_screen_meeting_nation_label' => 'Pays:',
    'meeting_editor_screen_meeting_nation_prompt' => 'Entrer le nom de Pays',
    'meeting_editor_screen_meeting_comments_label' => 'Comments:',
    'meeting_editor_screen_meeting_train_lines_label' => 'Train Lines:',
    'meeting_editor_screen_meeting_bus_lines_label' => 'Bus Lines:',
    'meeting_editor_screen_meeting_phone_meeting_number_label' => 'Phone Meeting Dial-in Number:',
    'meeting_editor_screen_meeting_phone_meeting_number_prompt' => 'Enter the dial-in number for a phone or virtual meeting',
    'meeting_editor_screen_meeting_virtual_meeting_link_label' => 'Virtual Meeting Link:',
    'meeting_editor_screen_meeting_virtual_meeting_link_prompt' => 'Enter the link for a virtual meeting',
    'meeting_editor_screen_meeting_virtual_meeting_additional_info_label' => 'Virtual Meeting Additional Information:',
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
    'meeting_editor_screen_in_person_virtual_info_warning' => 'In-person meetings shouldn\'t have any virtual meeting information.',
    'meeting_editor_screen_meeting_virtual_location_info_warning' => 'Virtual meetings shouldn\'t have a location name or address.',
    'meeting_editor_screen_meeting_validation_warning' => 'There are warnings.  Are you sure you want to save anyway?  If not, press \'cancel\' and go to the Location tab to see the warnings in place and address them.',
    'meeting_editor_screen_meeting_validation_failed' => 'Unable to save due to input errors.  Please go to the Location tab to address them, and then retry saving.  Errors: ',
    'meeting_editor_screen_meeting_validation_warnings' => 'Input warnings shown on the Location tab: ',
    'meeting_editor_screen_meeting_contact_name_1_label' => 'Contact 1 Name:',
    'meeting_editor_screen_meeting_contact_email_1_label' => 'Contact 1 Email:',
    'meeting_editor_screen_meeting_contact_phone_1_label' => 'Contact 1 Phone:',
    'meeting_editor_screen_meeting_contact_name_2_label' => 'Contact 2 Name:',
    'meeting_editor_screen_meeting_contact_email_2_label' => 'Contact 2 Email:',
    'meeting_editor_screen_meeting_contact_phone_2_label' => 'Contact 2 Phone:',
    'meeting_editor_screen_meeting_publish_search_prompt' => 'Rechercher:',
    'meeting_editor_screen_meeting_publish_search_pub' => 'Réunions publiées seulement',
    'meeting_editor_screen_meeting_publish_search_unpub' => 'Réunions non-publiées seulement',
    'meeting_editor_screen_meeting_visibility_advice' => 'Ce n\'est jamais affiché dans les recherches de réunion normales.',
    'meeting_editor_screen_meeting_publish_search_all' => 'Toutes les réunions',
    'meeting_editor_screen_meeting_create_button' => 'Créer une nouvelle réunion',
    'meeting_editor_screen_delete_button' => 'Supprimer cette réunion',
    'meeting_editor_screen_delete_button_confirm' => 'Etes-vous sûr que vous voulez supprimer cette rencontre?',
    'meeting_editor_screen_cancel_button' => 'annuler',
    'logout' => 'Déconnexion',
    'meeting_editor_screen_cancel_confirm' => 'Etes-vous sûr que vous voulez annuler l\'édition de cette réunion, et perdre tous les changements?',
    'meeting_lookup_failed' => 'La recherche d\'adresse a échoué.',
    'meeting_lookup_failed_not_enough_address_info' => 'Il n\'y a pas assez d\'informations d\'adresse valide pour en faire une recherche.',
    'meeting_create_button_name' => 'L\'enregistrer comme une nouvelle réunion',
    'meeting_saved_as_a_copy' => 'Sauvegarder cette réunion comme une copie (crée une nouvelle réunion)',
    'meeting_save_buttonName' => 'Enregistrez les modifications à cette réunion',
    'meeting_editor_tab_bar_basic_tab_text' => 'de Base',
    'meeting_editor_tab_bar_location_tab_text' => 'Localisation',
    'meeting_editor_tab_bar_format_tab_text' => 'Format',
    'meeting_editor_tab_bar_other_tab_text' => 'Autre',
    'meeting_editor_tab_bar_history_tab_text' => 'Historique',
    'meeting_editor_result_count_format' => '%d de réunions trouvées',
    'meeting_id_label' => 'ID de réunions:',
    'meeting_editor_default_zoom' => '13',
    'meeting_editor_default_weekday' => '2',
    'meeting_editor_default_start_time' => '20:30:00',
    'login_banner' => 'Basic Meeting List Toolbox',
    'login_underbanner' => 'Console d\'Administration de serveur racine',
    'login' => 'ID de connexion',
    'password' => 'Mot de passe',
    'button' => 'Connexion',
    'cookie' => 'Vous devez activer les cookies dans le but d\'administrer ce serveur.',
    'noscript' => 'Vous ne pouvez pas administrer ce site sans JavaScript.',
    'title' => 'S\'il vous plaît vous identifier pour administrer le serveur.',
    'edit_Meeting_object_not_found' => 'ERREUR: La réunion n\'a pas été trouvé.',
    'edit_Meeting_object_not_changed' => 'ERREUR: La réunion n\'a pas été modifié.',
    'edit_Meeting_auth_failure' => 'Vous n\'êtes pas autorisé à modifier cette réunion.',
    'not_auth_1' => 'NON AUTHORISÉ',
    'not_auth_2' => 'Vous n\'êtes pas autorisé à administrer ce serveur.',
    'not_auth_3' => 'Il y avait un problème avec le nom d\'utilisateur ou mot de passe que vous avez entré.',
    'email_format_bad' => 'L\'adresse e-mail que vous avez entré n\'a pas été formaté correctement.',
    'history_header_format' => '<div class="bmlt_admin_meeting_history_list_item_line_div history_item_header_div"><span class="bmlt_admin_history_list_header_date_span">%s</span><span class="bmlt_admin_history_list_header_user_span">par %s</span></div>',
    'history_no_history_available_text' => '<h1 class="bmlt_admin_no_history_available_h1">Aucune Historique disponible pour cette réuniong</h1>',
    'service_body_editor_disclosure' => 'Administration du composante de structure de service',
    'service_body_change_fader_success_text' => 'Le composante de structure de service a été changé avec succès',
    'service_body_change_fader_fail_text' => 'Le changement du composante de structure de service a échoué',
    'service_body_editor_screen_sb_id_label' => 'ID:',
    'service_body_editor_screen_sb_name_label' => 'Nom:',
    'service_body_name_default_prompt_text' => 'Entrer le nom de ce composante de structure de service',
    'service_body_parent_popup_label' => 'composante de structure de service parent:',
    'service_body_parent_popup_no_parent_option' => 'Aucun Parent (Top-Niveau)',
    'service_body_editor_screen_sb_admin_user_label' => 'Administrateur principal:',
    'service_body_editor_screen_sb_admin_description_label' => 'Description:',
    'service_body_description_default_prompt_text' => 'Entrer une description de ce composante de structure de service',
    'service_body_editor_screen_sb_admin_email_label' => 'Contact E-mail:',
    'service_body_email_default_prompt_text' => 'Entrer un adresse de Contact E-mail pour le composante de structure de service',
    'service_body_editor_screen_sb_admin_uri_label' => 'URL du site Web:',
    'service_body_uri_default_prompt_text' => 'Entrer l\'URL du site Web pour ce composante de structure de service',
    'service_body_editor_screen_sb_admin_full_editor_label' => 'Éditeurs de liste complète de réunions:',
    'service_body_editor_screen_sb_admin_full_editor_desc' => 'Ces utilisateurs peuvent modifier toutes les réunions de ce composante de structure de service.',
    'service_body_editor_screen_sb_admin_editor_label' => 'Éditeurs de base de liste de réunions:',
    'service_body_editor_screen_sb_admin_editor_desc' => 'Ces utilisateurs peuvent modifier toutes les réunions de cet composante de structure de service, mais seulement si elles ne sont pas publiées.',
    'service_body_editor_screen_sb_admin_observer_label' => 'Observateurs:',
    'service_body_editor_screen_sb_admin_observer_desc' => 'Ces utilisateurs peuvent voir des informations cachés (comme les adresses courriel), mais ne peut rien modifier.',
    'service_body_dirty_confirm_text' => 'Vous avez apporté des modifications à cet composante de structure de service. Voulez-vous perdre vos modifications?',
    'service_body_save_button' => 'Sauvegarder les changements de ces composantes de structure de service',
    'service_body_create_button' => 'Créer une composante de structure de service',
    'service_body_delete_button' => 'Supprimer cette composante de structure de service',
    'service_body_delete_perm_checkbox' => 'Supprimer cette composante de structure de service Permanently',
    'service_body_delete_button_confirm' => 'Are you sure that you want to delete this composante de structure de service? Make sure that all meetings are either removed or transferred to another service body before performing this function.',
    'service_body_delete_button_confirm_perm' => 'Cette composante de structure de service sera supprimée de façon permanante!',
    'service_body_change_fader_create_success_text' => 'The composante de structure de service a été créée avec succès',
    'service_body_change_fader_create_fail_text' => 'La création de composante de structure de service a échouée',
    'service_body_change_fader_delete_success_text' => 'La composante de structure de service a été supprimée avec succès',
    'service_body_change_fader_delete_fail_text' => 'La suppression de composante de structure de service a échouée',
    'service_body_change_fader_fail_no_data_text' => 'La modification de la composante de structure de service a échouée, parce que il n\'y avait pas de données fournis',
    'service_body_change_fader_fail_cant_find_sb_text' => 'La modification de la composante de structure de service a échouée, parce que la Composante de la structure de service est introuvable',
    'service_body_change_fader_fail_cant_update_text' => 'La modification de la composante de structure de service a échouée, parce que la Composante de la structure de service n\'a pas été mise à jour',
    'service_body_change_fader_fail_bad_hierarchy' => 'La modification de la composante de structure de service a échouée, parce que le propriétaire de la structure Composante de service sélectionné est sous cette Composante de la structure de service et ne peut être utilisé',
    'service_body_cancel_button' => 'Restaurer l\'original',
    'service_body_editor_type_label' => 'Type de composante de structure de service',
    'service_body_editor_type_c_comdef_service_body__GRP__' => 'Groupe',
    'service_body_editor_type_c_comdef_service_body__COP__' => 'Co-Op',
    'service_body_editor_type_c_comdef_service_body__ASC__' => 'Comité de service local',
    'service_body_editor_type_c_comdef_service_body__RSC__' => 'Comité de Service Regional',
    'service_body_editor_type_c_comdef_service_body__WSC__' => 'Conférence Service mondial',
    'service_body_editor_type_c_comdef_service_body__MAS__' => 'Métro Local',
    'service_body_editor_type_c_comdef_service_body__ZFM__' => 'Forum Zonal',
    'service_body_editor_type_c_comdef_service_body__GSU__' => 'Group Service Unit',
    'service_body_editor_type_c_comdef_service_body__LSU__' => 'Local Service Unit',
    'service_body_editor_screen_helpline_label' => 'Helpline:',
    'service_body_editor_screen_helpline_prompt' => 'Enter The Helpline Telephone Number',
    'service_body_editor_uri_naws_format_text' => 'Obtenez les réunions pour cet composante de structure de service de services sous forme de fichier compatible SMNA',
    'edit_Meeting_meeting_id' => 'ID de réunion:',
    'service_body_editor_create_new_sb_option' => 'Créer une nouvelle composante de structure de service',
    'service_body_editor_screen_world_cc_label' => 'Code mondial de comité:',
    'service_body_editor_screen_world_cc_prompt' => 'Entrer un code mondial de Comité',
    'user_editor_disclosure' => 'Utilisateur d\'administration',
    'user_editor_create_new_user_option' => 'Créer un nouvel utilisateur',
    'user_editor_screen_sb_id_label' => 'ID:',
    'user_editor_account_login_label' => 'Login utlisateur:',
    'user_editor_login_default_text' => 'Entrer mon login',
    'user_editor_account_type_label' => 'Je suis un:',
    'user_editor_user_owner_label' => 'Owned By: ', // TODO translate
    'user_editor_account_type_1' => 'Administrateur du serveur',
    'user_editor_account_type_2' => 'Administrateur de la composante de structure de service ',
    'user_editor_account_type_3' => 'Editeur de la composante de structure de service',
    'user_editor_account_type_5' => 'Observateur de la composante de structure de service',
    'user_editor_account_type_4' => 'Désactiver utilisateur',
    'user_editor_account_name_label' => 'Nom d\'utilisateur:',
    'user_editor_name_default_text' => "Entrez le nom d\'utilisateur",
    'user_editor_account_description_label' => 'Description:',
    'user_editor_description_default_text' => "Entrez la description de l\'utilisateur",
    'user_editor_account_email_label' => 'E-mail:',
    'user_editor_email_default_text' => "Entrez le E-mail de l\'Utilisateur",
    'user_change_fader_success_text' => 'L\'utilisateur a été changé avec succès',
    'user_change_fader_fail_text' => 'Le changement de l\'utilisateur a échoué',
    'user_change_fader_create_success_text' => 'L\'utilisateur a été créé avec succès',
    'user_change_fader_create_fail_text' => 'Échec de création de l\'utilisateur',
    'user_change_fader_create_fail_already_exists' => 'Une connexion de l\'utilisateur que vous essayez de créer existe déjà.',
    'user_change_fader_delete_success_text' => 'L\'utilisateur a été supprimé avec succès',
    'user_change_fader_delete_fail_text' => 'Échec de suppression de l\'utilisateur',
    'user_save_button' => 'Enregistrez les modifications à cet utilisateur',
    'user_create_button' => 'Créer ce nouvel utilisateur',
    'user_cancel_button' => 'Restaurer la modification',
    'user_delete_button' => 'Supprimer cet utilisateur',
    'user_delete_perm_checkbox' => 'Supprimer cet utilisateur de façon permanente',
    'user_password_label' => 'Changer mot de passe pour:',
    'user_new_password_label' => 'Définir mot de passe à:',
    'user_password_default_text' => 'Ne rien changer, sauf si vous voulez changer le mot de passe',
    'user_new_password_default_text' => 'Vous devez entrer un mot de passe d\'un nouvel utilisateur',
    'user_dirty_confirm_text' => 'Vous avez apporté des modifications à l\'utilisateur. Voulez-vous perdre vos modifications?',
    'user_delete_button_confirm' => 'Etes-vous sûr que vous voulez supprimer cet utilisateur?',
    'user_delete_button_confirm_perm' => 'Cet utilisateur sera définitivement supprimé!',
    'user_create_password_alert_text' => 'Les nouveaux utilisateurs doivent avoir un mot de passe. Vous n\'avez pas fourni un mot de passe pour cet utilisateur.',
    'user_change_fader_fail_no_data_text' => 'Échec de suppression de l\'utilisateur, parce que il n\'y avait pas de données fournis',
    'user_change_fader_fail_cant_find_sb_text' => 'Échec de suppression de l\'utilisateur, parce que l\'utilisateur n\'a pas été trouvé',
    'user_change_fader_fail_cant_update_text' => 'Échec de suppression de l\'utilisateur, parce que l\'utilisateur n\'a pas été mise à jour',
    'format_editor_disclosure' => 'Administration de format',
    'format_change_fader_change_success_text' => 'Le format a été changé avec succès',
    'format_change_fader_change_fail_text' => 'Le Changement de format a échoué',
    'format_change_fader_create_success_text' => 'Le format a été créé avec succès',
    'format_change_fader_create_fail_text' => 'La création du format a échoué',
    'format_change_fader_delete_success_text' => 'Le format a été supprimé avec succès',
    'format_change_fader_delete_fail_text' => 'La suppréssion du format a échoué',
    'format_change_fader_fail_no_data_text' => 'Le changement du format a échoué, parce que il n\'y avait pas de données fournis',
    'format_change_fader_fail_cant_find_sb_text' => 'Le changement du format a échoué, parce que le format n\'a pas été trouvé',
    'format_change_fader_fail_cant_update_text' => 'Le changement du format a échoué, parce que le format n\'a pas été mise à jour',
    'format_editor_name_default_text' => 'Entrez une description très courte',
    'format_editor_description_default_text' => 'Entrez une description plus détaillée',
    'format_editor_create_format_button_text' => 'Créer un nouveau format',
    'format_editor_cancel_create_format_button_text' => 'Annuler',
    'format_editor_create_this_format_button_text' => 'Créer ce format',
    'format_editor_change_format_button_text' => 'Modifier ce format',
    'format_editor_delete_format_button_text' => 'Supprimer ce format',
    'format_editor_reset_format_button_text' => 'Restaurer la modification',
    'need_refresh_message_fader_text' => 'Vous devez actualiser cette page avant d\'utiliser cette section',
    'need_refresh_message_alert_text' => 'Parce que vous avez fait des changements dd l\'administration de la structure de composante de service, gestion des utilisateurs ou d\'administration de format, les informations affichées dans cette section peuvent ne plus être exactes même si la page doit être rafraîchie. La meilleure façon de le faire est de vous déconnecter, puis vous connecter à nouveau.',
    'format_editor_delete_button_confirm' => 'Etes-vous sûr que vous voulez supprimer ce format?',
    'format_editor_delete_button_confirm_perm' => 'Ce format sera définitivement supprimé!',
    'format_editor_reserved_key' => 'This key is reserved for a venue type format - please use something different.',       // TODO: translate
    'min_password_length_string' => 'Le mot de passe est trop court! Il doit être au moins contenir au moins %d caractères!',
    'AJAX_Auth_Failure' => 'Échec de l\'autorisation pour cette opération. Il peut y avoir un problème avec la configuration du serveur.',
    'Maps_API_Key_Warning' => 'There is a problem with the Google Maps API Key.',
    'Observer_Link_Text' => 'Meeting Browser',
    'Data_Transfer_Link_Text' => 'Import Meeting Data (WARNING: Replaces Current Data!)',
    'MapsURL' => 'https://maps.google.com/maps?q=##LAT##,##LONG##+(##NAME##)&amp;ll=##LAT##,##LONG##',
    'hidden_value' => 'Cannot Display Data -Unauthorized',
    'Value_Prompts' => array(
        'id_bigint' => 'ID de Réuniom',
        'worldid_mixed' => 'ID de Services Mondiaux',
        'service_body' => 'Composante de structure de service',
        'service_bodies' => 'Composantes de structure de service',
        'weekdays' => 'Jours de la semaine',
        'weekday' => 'Cette réunion a lieu à tous les',
        'start_time' => 'Laréunion débute à',
        'duration_time' => 'La réunion dure',
        'location' => 'Ville',
        'duration_time_hour' => 'Heure',
        'duration_time_hours' => 'Heures',
        'duration_time_minute' => 'Minute',
        'duration_time_minutes' => 'Minutes',
        'lang_enum' => 'Language',
        'formats' => 'Formats',
        'distance' => 'Distance du Centre',
        'generic' => 'Réunion NA',
        'close_title' => 'Fermez cette fenêtre Détail de Réunion',
        'close_text' => 'Fermer la fenêtre',
        'map_alt' => 'Carte de la réunion',
        'map' => 'Suivez ce lien pour la carte',
        'title_checkbox_unpub_meeting' => 'Cette rencontre n\'est pas publiée. Il ne peut pas être vu par des recherches régulières.',
        'title_checkbox_copy_meeting' => 'Cette réunion est une copie d\'une autre réunion. Il est également non publié. Elle ne peut être vue par recherches régulières.'
    ),
    'world_format_codes_prompt' => 'Format SMNA:',
    'world_format_codes' => array(
        '' => 'Aucun',
        'OPEN' => 'Ouvert',
        'CLOSED' => 'Fermée',
        'WCHR' => 'Fauteuil Roulant',
        'BEG' => 'Débutant/Nouveau',
        'BT' => 'Texte de base',
        'CAN' => 'À la Chandelle',
        'CPT' => '12 Concepts',
        'CW' => 'Enfants bienvenus',
        'DISC' => 'Discussion',
        'GL' => 'Gais/Lesbiennes',
        'IP' => 'Étude de pamphlet IP',
        'IW' => 'Étude du livre « Ça marche »',
        'JFT' => 'Étude du livre « Juste pour aujourd’hui »',
        'LC' => 'Étude du livre « Vivre abstinent »',
        'LIT' => 'Étude de littérature',
        'M' => 'Hommes',
        'MED' => 'Méditation',
        'NS' => 'Non-Smoking',
        'QA' => 'Questions et réponses',
        'RA' => 'Accès limité',
        'S-D' => 'Speaker/Discussion', // TODO translate
        'SMOK' => 'Fumeurs',
        'SPK' => 'Partage',
        'STEP' => 'Étapes',
        'SWG' => 'Étude du « Guide de travail sur les étapes »',
        'TOP' => 'Thématique',
        'TRAD' => 'Traditions',
        'VAR' => 'Formats variés',
        'W' => 'Femmes',
        'Y' => 'Jeunes',
        'LANG' => 'Alternate Language',
        'GP' => 'Guiding Principles', // TODO translate
        'NC' => 'No Children', // TODO translate
        'CH' => 'Closed Holidays', // TODO translate
        'VM' => 'Virtual', // TODO translate
        'HYBR' => 'Virtual and In-Person', // TODO translate
        'TC' => 'Facility Temporarily Closed' // TODO translate
    ),
    'format_type_prompt' => 'Format Type:',    // TODO: Translate
    'format_type_codes' => array(
        '' => 'None',    // TODO: Translate
        'FC1' => 'Meeting Format (Speaker, Book Study, etc.)',    // TODO: Translate
        'FC2' => 'Location Code (Wheelchair Accessible, Limited Parking, etc.)',    // TODO: Translate
        'FC3' => 'Common Needs and Restrictions (Mens Meeting, LGTBQ, No Children, etc.)',    // TODO: Translate
        'O' => 'Attendance by non-addicts (Open, Closed)',    // TODO: Translate
        'LANG' => 'Language', // TRANSLATE
        'ALERT' => 'Format should be especially prominent (Clean requirement, etc.)',// TODO: Translate
    ),
    'cookie_monster' => 'Ce site contient un cookie pour emmagasiner l\'information de votre langue de préférée.',
    'main_prompts' => array(
        'id_bigint' => 'ID',
        'worldid_mixed' => 'World ID',
        'shared_group_id_bigint' => 'Unused',
        'service_body_bigint' => 'Service Body ID',
        'weekday_tinyint' => 'Weekday',
        'start_time' => 'Start Time',
        'duration_time' => 'Duration',
        'time_zone' => 'Time Zone',
        'formats' => 'Formats',
        'lang_enum' => 'Language',
        'longitude' => 'Longitude',
        'latitude' => 'Latitude',
        'published' => 'Published',
        'email_contact' => 'Email Contact',
    ),
    'check_all' => 'Check All',
    'uncheck_all' => 'Uncheck All',
    'automatically_calculated_on_save' => 'Automatically calculated on save.'
);

$email_contact_strings = array(
    'meeting_contact_form_subject_format' => "[MEETING LIST CONTACT] %s",
    'meeting_contact_message_format' => "%s\n--\nThis message concerns the meeting named \"%s\", which meets at %s, on %s.\nBrowser Link: %s\nEdit Link: %s\nIt was sent directly from the meeting list web server, and the sender is not aware of your email address.\nPlease be aware that replying will expose your email address.\nIf you use \"Reply All\", and there are multiple email recipients, you may expose other people's email addresses.\nPlease respect people's privacy and anonymity; including the original sender of this message."
);

$change_type_strings = array(
    '__THE_MEETING_WAS_CHANGED__' => 'La réunion a été changé.',
    '__THE_MEETING_WAS_CREATED__' => 'La réunion a été créé.',
    '__THE_MEETING_WAS_DELETED__' => 'La réunion a été supprimée.',
    '__THE_MEETING_WAS_ROLLED_BACK__' => 'La réunion a été restaurée pour une version précédente.',

    '__THE_FORMAT_WAS_CHANGED__' => 'Le format a été changé.',
    '__THE_FORMAT_WAS_CREATED__' => 'Le format a été créé.',
    '__THE_FORMAT_WAS_DELETED__' => 'Le format a été supprimé.',
    '__THE_FORMAT_WAS_ROLLED_BACK__' => 'Le format a été restauré pour une version précédente.',

    '__THE_SERVICE_BODY_WAS_CHANGED__' => 'La composante de structure de service a été modifiée.',
    '__THE_SERVICE_BODY_WAS_CREATED__' => 'La composante de structure a été créée.',
    '__THE_SERVICE_BODY_WAS_DELETED__' => 'La composante de structure a été supprimée.',
    '__THE_SERVICE_BODY_WAS_ROLLED_BACK__' => 'La composante de structure a été restaurée pour une version précédente.',

    '__THE_USER_WAS_CHANGED__' => 'L\'Utilisateur a été changé.',
    '__THE_USER_WAS_CREATED__' => 'L\'Utilisateur a été créé.',
    '__THE_USER_WAS_DELETED__' => 'L\'Utilisateur a été supprimé.',
    '__THE_USER_WAS_ROLLED_BACK__' => 'L\'Utilisateur a été restauré pour une version précédente.',

    '__BY__' => 'par',
    '__FOR__' => 'pour'
);

$detailed_change_strings = array(
    'was_changed_from' => 'a été modifié à partir de',
    'to' => 'à',
    'was_changed' => 'a été changé',
    'was_added_as' => 'a été ajouté en tant que',
    'was_deleted' => 'a été supprimée',
    'was_published' => 'La réunion a été publiée',
    'was_unpublished' => 'La réunion n\'a pas été publiée',
    'formats_prompt' => 'Le format de la réunion',
    'duration_time' => 'La durée de la réunion',
    'start_time' => 'Début de la réuniob',
    'longitude' => 'Longitude de la réunion',
    'latitude' => 'TLatitude de la réunion',
    'sb_prompt' => 'La réunion a changé sa composante de structure de service de',
    'id_bigint' => 'ID de réunion',
    'lang_enum' => 'Langue de réunion',
    'worldid_mixed' => 'ID du groupe partagée',  // TODO: translate The World Committee Code
    'weekday_tinyint' => 'Le jour de la semaine où la réunion rassemble',
    'non_existent_service_body' => 'La composante de structure de service n\'esxise plus',
);

defined('_END_CHANGE_REPORT') or define('_END_CHANGE_REPORT', '.');
