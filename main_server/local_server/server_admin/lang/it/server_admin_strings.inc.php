<?php
/***********************************************************************/
/** \file   server_admin_strings.inc.php
    \brief  The strings displayed in the server administration console (Italian)

    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.*/
    defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.
    
    $comdef_server_admin_strings = array (  'server_admin_disclosure'                               =>  'Server Administration',
                                            'server_admin_naws_spreadsheet_label'                   =>  'Updated World IDs Spreadsheet:',
                                            'update_world_ids_button_text'                          =>  'Update Meeting World IDs',
                                            'update_world_ids_from_spreadsheet_dropdown_text'       =>  'Update Meeting World IDs from NAWS Spreadsheet',
                                            'server_admin_error_no_world_ids_updated'               =>  'No World IDs were updated. This could be because your user does not have permission to update the submitted meetings.',
                                            'server_admin_error_required_spreadsheet_column'        =>  'Required column does not exist in the spreadsheet: ',
                                            'server_admin_error_bmlt_id_not_integer'                =>  'The provided bmlt_id is not an integer: ',
                                            'server_admin_error_could_not_create_reader'            =>  'Could not create reader for file: ',
                                            'server_admin_error_no_files_uploaded'                  =>  'No files were uploaded.',
                                            'server_admin_error_service_bodies_already_exist'       =>  'Service bodies with the following World IDs already exist: ',
                                            'server_admin_error_meetings_already_exist'             =>  'Meetings with the following World IDs already exist: ',
                                            'server_admin_ui_num_meetings_updated'                  =>  'Number of meetings updated: ',
                                            'server_admin_ui_num_meetings_not_updated'              =>  'Number of meetings that did not need updating: ',
                                            'server_admin_ui_warning'                               =>  'WARNING',
                                            'server_admin_ui_errors'                                =>  'Error(s)',
                                            'server_admin_ui_meetings_not_found'                    =>  'meetings were found in the spreadsheet that did not exist in the database. This can happen when a meeting is deleted or unpublished. The missing meeting IDs are: ',
                                            'server_admin_ui_service_bodies_created'                => 'Service bodies created: ',
                                            'server_admin_ui_meetings_created'                      => 'Meetings created: ',
                                            'server_admin_ui_users_created'                         => 'Users created: ',
                                            'server_admin_ui_refresh_ui_text'                       => 'Sign out and then sign in again to see the new service bodies, users, and meetings.',
                                            'import_service_bodies_and_meetings_button_text'        => 'Import Service Bodies and Meetings',
                                            'import_service_bodies_and_meetings_dropdown_text'      => 'Import Service Bodies and Meetings from NAWS Export',
                                            'server_admin_naws_import_spreadsheet_label'            => 'NAWS Import Spreadsheet:',
                                            'account_disclosure'                                    =>  'Il mio account',//'My Account',
                                            'account_name_label'                                    =>  'Nome del mio account:',//'My Account Name:',
                                            'account_login_label'                                   =>  'Il mio login:',//'My Login:',
                                            'account_type_label'                                    =>  'Io sono un:',//'I Am A:',
                                            'account_type_1'                                        =>  'Amministratore del server',//'Server Administrator',
                                            'account_type_2'                                        =>  'Amministratore della struttura di servizio',//'Service Body Administrator',
                                            'ServerMapsURL'                                         =>  'https://maps.googleapis.com/maps/api/geocode/xml?address=##SEARCH_STRING##&sensor=false',
                                            'account_type_4'                                        =>  'Patetico perdente che non dovrebbe neanche aver accesso a questa pagina - L\'Autore del software ha miseramente fallito!',//'Pathetic Luser Who Shouldn\'t Even Have Access to This Page -The Author of the Software Pooched it BAD!',
                                            'account_type_5'                                        =>  'Osservatore nella struttura di servizio',//'Service Body Observer',
                                            'change_password_label'                                 =>  'Cambia la mia password in:',//'Change My Password To:',
                                            'change_password_default_text'                          =>  'Ignoralo se non vuoi cambiare la tua password',//'Leave This Alone If You Don\'t Want To Change Your Password',
                                            'account_email_label'                                   =>  'Il mio indirizzo email:',//'My Email Address:',
                                            'email_address_default_text'                            =>  'Inserisci un indirizzo email',//'Enter An Email Address',
                                            'account_description_label'                             =>  'La mia descrizione:',//'My Description:',
                                            'account_description_default_text'                      =>  'Inserisci una descrizione',//'Enter A Description',
                                            'account_change_button_text'                            =>  'Modifica le impostazioni del mio account',//'Change My Account Settings',
                                            'account_change_fader_success_text'                     =>  'Le informazioni dell\'account sono state modificate con successo',//'The Account Information Was Successfully Changed',
                                            'account_change_fader_failure_text'                     =>  'Le informazioni dell\'account non sono state modificate',//'The Account Information Was Not Changed',
                                            'meeting_editor_disclosure'                             =>  'Editor delle riunioni',//'Meeting Editor',
                                            'meeting_editor_already_editing_confirm'                =>  'Stai modificando un\'altra riunione. Vuoi perdere tutte le modifiche fatte in quella riunione?',//'You are currently editing another meeting. Do you want to lose all changes in that meeting?',
                                            'meeting_change_fader_success_text'                     =>  'La riunione è stata modificata con successo',//'The Meeting Was Successfully Changed',
                                            'meeting_change_fader_failure_text'                     =>  'La riunione non è stata modificata',//'The Meeting Was Not Changed',
                                            'meeting_change_fader_success_delete_text'              =>  'La riunione è stata cancellata con successo',//'The Meeting Was Successfully Deleted',
                                            'meeting_change_fader_fail_delete_text'                 =>  'La riunione non è stata cancellata',//'The Meeting Was Not Deleted',
                                            'meeting_change_fader_success_add_text'                 =>  'La nuova riunione è stata aggiunta con successo',//'The New Meeting Was Successfully Added',
                                            'meeting_change_fader_fail_add_text'                    =>  'La nuova riunione non è stata aggiunta',//'The New Meeting Was Not Added',
                                            'meeting_text_input_label'                              =>  'Ricerca testuale:',//'Search For Text:',
                                            'access_service_body_label'                             =>  'Ho accesso a:',//'I Have Access to:',
                                            'meeting_text_input_default_text'                       =>  'Inserisci il testo da cercare',//'Enter Some Search Text',
                                            'meeting_text_location_label'                           =>  'Questa è una località o un codice di avviamento postale',//'This is a Location or PostCode',
                                            'meeting_search_weekdays_label'                         =>  'Cerca nei giorni selezionati:',//'Search For Selected Weekdays:',
                                            'meeting_search_weekdays_names'                         =>  array ( 'Tutti', 'Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato' ),//( 'All', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
                                            'meeting_search_service_bodies_label'                   =>  'Cerca nelle strutture di servizio selezionate:',//'Search In Selected Service Bodies:',
                                            'meeting_search_start_time_label'                       =>  'Cerca per orario di inizio riunione:',//'Search By Meeting Start Time:',
                                            'meeting_search_start_time_all_label'                   =>  'In ogni momento',//'Any Time',
                                            'meeting_search_start_time_morn_label'                  =>  'Mattina',//'Morning',
                                            'meeting_search_start_time_aft_label'                   =>  'Pomeriggio',//'Afternoon',
                                            'meeting_search_start_time_eve_label'                   =>  'Sera',//'Evening',
                                            'meeting_search_no_results_text'                        =>  'Nessuna riunione trovata',//'No Meetings Found',
                                            'meeting_editor_tab_specifier_text'                     =>  'Cerca per riunione',//'Search For Meetings',
                                            'meeting_editor_tab_editor_text'                        =>  'Modifica riunione',//'Edit Meetings',
                                            'meeting_editor_create_new_text'                        =>  'Crea una nuova riunione',//'Create A New Meeting',
                                            'meeting_editor_location_map_link'                      =>  'Mappa delle località',//'Location Map',
                                            'meeting_editor_screen_match_ll_button'                 =>  'Imposta longitudine e latitudine sull\'indirizzo',//'Set Longitude and Latitude to Address',
                                            'meeting_editor_screen_default_text_prompt'             =>  'Inserisci del testo o un numero',//'Enter Some Text or a Number',
                                            'meeting_is_published'                                  =>  'La riunione è pubblicata',//'Meeting is Published',
                                            'meeting_unpublished_note'                              =>  'Note: Unpublishing a meeting indicates a temporary closure. If this meeting has closed permanently, please delete it.',
                                            'meeting_editor_screen_meeting_name_label'              =>  'Nome della riunione:',//'Meeting Name:',
                                            'meeting_editor_screen_meeting_name_prompt'             =>  'Inserisci un nome per la riunione',//'Enter A Meeting Name',
                                            'meeting_editor_screen_meeting_weekday_label'           =>  'Giorno della settimana:',//'Weekday:',
                                            'meeting_editor_screen_meeting_start_label'             =>  'Orario di inizio della riunione:',//'Meeting Start Time:',
                                            'meeting_editor_screen_meeting_time_zone_label'         =>  'Meeting Time Zone:',//'Meeting Time Zone:`,
                                            'meeting_editor_screen_meeting_am_label'                =>  'AM',//'AM',
                                            'meeting_editor_screen_meeting_pm_label'                =>  'PM',//'PM',
                                            'meeting_editor_screen_meeting_noon_label'              =>  'Mezzogiorno',//'Noon',
                                            'meeting_editor_screen_meeting_midnight_label'          =>  'Mezzanotte',//Midnight',
                                            'meeting_editor_screen_meeting_duration_label'          =>  'Durata:',//'Duration:',
                                            'meeting_editor_screen_meeting_oe_label'                =>  'Senza orario di fine',//'Open-Ended',
                                            'meeting_editor_screen_meeting_cc_label'                =>  'Codice del comitato mondiale:',//'World Committee Code:',
                                            'meeting_editor_screen_meeting_cc_prompt'               =>  'Inserisci un codice per il comitato mondiale',//'Enter A World Committee Code',
                                            'meeting_editor_screen_meeting_contact_label'           =>  'Contatto email della riunione:',//'Meeting Email Contact:',
                                            'meeting_editor_screen_meeting_contact_prompt'          =>  'Inserisci un email di contatto specifica solo per questa riunione',//'Enter An Email for A Contact Specific Only to This Meeting',
                                            'meeting_editor_screen_meeting_sb_label'                =>  'Struttura di servizio:',//'Service Body:',
                                            'meeting_editor_screen_meeting_sb_default_value'        =>  'Nessuna struttura di servizio selezionata',//'No Service Body Selected',
                                            'meeting_editor_screen_meeting_longitude_label'         =>  'Longitudine:',//'Longitude:',
                                            'meeting_editor_screen_meeting_longitude_prompt'        =>  'Inserisci una longitudine',//'Enter A Longitude',
                                            'meeting_editor_screen_meeting_latitude_label'          =>  'Latitudine:',//'Latitude:',
                                            'meeting_editor_screen_meeting_latitude_prompt'         =>  'Inserisci una latitudine',//'Enter A Latitude',
                                            'meeting_editor_screen_meeting_location_label'          =>  'Luogo:',//'Location:',
                                            'meeting_editor_screen_meeting_location_prompt'         =>  'Inserisci un nome per il luogo della riunione (come il nome dell\'edificio)',//Enter A Location Name (Like a Building Name)',//Enter A Location Name (Like a Building Name)',
                                            'meeting_editor_screen_meeting_info_label'              =>  'Informazioni extra:',//'Extra Info:',
                                            'meeting_editor_screen_meeting_info_prompt'             =>  'Inserisci ogni altra informazione aggiuntiva sul luogo',//'Enter Any Additional Location Information',
                                            'meeting_editor_screen_meeting_street_label'            =>  'Indirizzo:',//'Street Address:',
                                            'meeting_editor_screen_meeting_street_prompt'           =>  'Inserisci un indirizzo',//'Enter A Street Address',
                                            'meeting_editor_screen_meeting_neighborhood_label'      =>  'Zona, rione:',//'Neighborhood:',
                                            'meeting_editor_screen_meeting_neighborhood_prompt'     =>  'Inserisci una zona o un rione (non un quartiere o altro distretto cittadino)',//'Enter A Neighborhood (Not Borough or City Subsection)',
                                            'meeting_editor_screen_meeting_borough_label'           =>  'Quartiere/Frazione:',//'Borough/City Subsection:',
                                            'meeting_editor_screen_meeting_borough_prompt'          =>  'Inserisci un quartiere o una frazione (non zona o rione)',//'Enter A Borough or City Subsection (Not Neighborhood)',
                                            'meeting_editor_screen_meeting_city_label'              =>  'Città:',//'City/Town:',
                                            'meeting_editor_screen_meeting_city_prompt'             =>  'Inserisci il nome della città (non di provincia o quartiere)',//'Enter A City or Town Name (Not County or Borough)',
                                            'meeting_editor_screen_meeting_county_label'            =>  'Provincia:',//'County/Sub-Province:',
                                            'meeting_editor_screen_meeting_county_prompt'           =>  'Inserisci il nome della provincia',//'Enter A County or Sub-Province Name',
                                            'meeting_editor_screen_meeting_state_label'             =>  'Regione:',//'State/Province:',
                                            'meeting_editor_screen_meeting_state_prompt'            =>  'Inserisci il nome della regione',//'Enter A State or Province Name',
                                            'meeting_editor_screen_meeting_zip_label'               =>  'Codice di avviamento postale (CAP):',//'Zip Code/Postal Code:',
                                            'meeting_editor_screen_meeting_zip_prompt'              =>  'Inserisci il CAP',//'Enter A Postal Code',
                                            'meeting_editor_screen_meeting_nation_label'            =>  'Nazione:',//'Nation:',
                                            'meeting_editor_screen_meeting_nation_prompt'           =>  'Inserisci il nome della nazione',//'Enter The Nation Name',
                                            'meeting_editor_screen_meeting_comments_label'          =>  'Comments:',
                                            'meeting_editor_screen_meeting_train_lines_label'       =>  'Train Lines:',
                                            'meeting_editor_screen_meeting_bus_lines_label'         =>  'Bus Lines:',
                                            'meeting_editor_screen_meeting_phone_meeting_number_label'      =>  'Phone Meeting Dial-in Number:',
                                            'meeting_editor_screen_meeting_phone_meeting_number_prompt'     =>  'Enter the dial-in number for a phone or virtual meeting',
                                            'meeting_editor_screen_meeting_virtual_meeting_link_label'      =>  'Virtual Meeting Link:',
                                            'meeting_editor_screen_meeting_virtual_meeting_link_prompt'     =>  'Enter the link for a virtual meeting',
                                            'meeting_editor_screen_meeting_virtual_meeting_additional_info_label'      =>  'Virtual Meeting Additional Information:',
                                            'meeting_editor_screen_meeting_virtual_meeting_additional_info_prompt'     =>  'Enter any additional information for joining the virtual meeting',
                                            'meeting_editor_screen_meeting_contact_name_1_label'    =>  'Contact 1 Name:',
                                            'meeting_editor_screen_meeting_contact_email_1_label'   =>  'Contact 1 Email:',
                                            'meeting_editor_screen_meeting_contact_phone_1_label'   =>  'Contact 1 Phone:',
                                            'meeting_editor_screen_meeting_contact_name_2_label'    =>  'Contact 2 Name:',
                                            'meeting_editor_screen_meeting_contact_email_2_label'   =>  'Contact 2 Email:',
                                            'meeting_editor_screen_meeting_contact_phone_2_label'   =>  'Contact 2 Phone:',
                                            'meeting_editor_screen_meeting_publish_search_prompt'   =>  'Cerca:',//'Look For:',
                                            'meeting_editor_screen_meeting_publish_search_pub'      =>  'Solo riunioni pubblicate',//'Published Meetings Only',
                                            'meeting_editor_screen_meeting_publish_search_unpub'    =>  'Solo riunioni non pubblicate',//'Unpublished Meetings Only',
                                            'meeting_editor_screen_meeting_visibility_advice'       =>  'Questo non è mai visualizzato nelle normali ricerche delle riunioni.',//'This is never displayed in normal meeting searches.',
                                            'meeting_editor_screen_meeting_publish_search_all'      =>  'Tutte le riunioni',//'All Meetings',
                                            'meeting_editor_screen_meeting_create_button'           =>  'Crea una nuova riunione',//'Create A New Meeting',
                                            'meeting_editor_screen_delete_button'                   =>  'Elimina questa riunione',//'Delete This Meeting',
                                            'meeting_editor_screen_delete_button_confirm'           =>  'Sei sicuro di voler eliminare questa riunione?',//'Are you sure that you want to delete this meeting?',
                                            'meeting_editor_screen_cancel_button'                   =>  'Cancella',//'Cancel',
                                            'logout'                                                =>  'Esci',//'Sign Out',
                                            'meeting_editor_screen_cancel_confirm'                  =>  'Sei sicuro di voler annullare la modifica di questa riunione e perdere tutte le variazioni apportate?',//'Are you sure that you want to cancel editing this meeting, and lose all changes?',
                                            'meeting_lookup_failed'                                 =>  'Ricerca dell\'indirizzo non riuscita.',//'The address lookup failed.',
                                            'meeting_lookup_failed_not_enough_address_info'         =>  'Nell\'indirizzo non ci sono elementi sufficienti per fare la ricerca.',//'There is not enough valid address information to do a lookup.',
                                            'meeting_create_button_name'                            =>  'Salva come nuova riunione',//'Save This As A New Meeting',
                                            'meeting_saved_as_a_copy'                               =>  'Salva questa riunione come copia (crea una nuova riunione)',//'Save This Meeting As A Copy (Creates A New Meeting)',
                                            'meeting_save_buttonName'                               =>  'Salva le modifiche a questa riunione',//'Save the Changes to This Meeting',
                                            'meeting_editor_tab_bar_basic_tab_text'                 =>  'Base',//'Basic',
                                            'meeting_editor_tab_bar_location_tab_text'              =>  'Località',//'Location',
                                            'meeting_editor_tab_bar_format_tab_text'                =>  'Formato',//'Format',
                                            'meeting_editor_tab_bar_other_tab_text'                 =>  'Altro',//'Other',
                                            'meeting_editor_tab_bar_history_tab_text'               =>  'Cronologia',//'History',
                                            'meeting_editor_result_count_format'                    =>  '%d riunioni trovate',//'%d Meetings Found',
                                            'meeting_id_label'                                      =>  'ID della riunione:',//'Meeting ID:',
                                            'meeting_editor_default_zoom'                           =>  '13',//'13',
                                            'meeting_editor_default_weekday'                        =>  '2',//'2',
                                            'meeting_editor_default_start_time'                     =>  '20:30:00',//'20:30:00',
                                            'login_banner'                                          =>  'Basic Meeting List Toolbox (BMLT)',//'Basic Meeting List Toolbox',
                                            'login_underbanner'                                     =>  'Console di amministrazione del Root Server',//'Root Server Administration Console',
                                            'login'                                                 =>  'ID di autenticazione',//'Login ID',
                                            'password'                                              =>  'Password',//'Password',
                                            'button'                                                =>  'Entra',//'Log In',
                                            'cookie'                                                =>  'Devi abilitare i cookie per amministrare questo server.',//'You must enable cookies in order to administer this server.',
                                            'noscript'                                              =>  'Non puoi amministrare questo sito senza JavaScript.',//'You cannot administer this site without JavaScript.',
                                            'title'                                                 =>  'Per favore, autenticati per amministrare il server.',//'Please log in to administer the server.',
                                            'edit_Meeting_object_not_found'                         =>  'ERRORE. La riunione non è stata trovata.',//'ERROR: The meeting was not found.',
                                            'edit_Meeting_object_not_changed'                       =>  'ERRORE. La riunione non è stata modificata.',//'ERROR: The meeting was not changed.',
                                            'edit_Meeting_auth_failure'                             =>  'Non sei autorizzato a modificare questa riunione.',//'You are not authorized to edit this meeting.',
                                            'not_auth_1'                                            =>  'NON AUTORIZZATO',//'NOT AUTHORIZED',
                                            'not_auth_2'                                            =>  'Non sei autorizzato ad amministrare questo server.',//'You are not authorized to administer this server.',
                                            'not_auth_3'                                            =>  'C\'è stato un problema con il nome utente o la password che hai inserito.',//'There was a problem with the user name or password that you entered.',
                                            'email_format_bad'                                      =>  'L\'indirizzo email che hai immesso non era formattato correttamente.',//'The email address that you entered was not formatted correctly.',
                                            'history_header_format'                                 =>  '<div class="bmlt_admin_meeting_history_list_item_line_div history_item_header_div"><span class="bmlt_admin_history_list_header_date_span">%s</span><span class="bmlt_admin_history_list_header_user_span">da %s</span></div>',
                                            'history_no_history_available_text'                     =>  '<h1 class="bmlt_admin_no_history_available_h1">Nessuna cronologia disponibile per questa riunione</h1>', /// No History Available For This Meeting
                                            'service_body_editor_disclosure'                        =>  'Amministrazione della struttura di servizio',//'Service Body Administration',
                                            'service_body_change_fader_success_text'                =>  'La struttura di servizio è stata modificata con successo',//'The Service Body Was Successfully Changed',
                                            'service_body_change_fader_fail_text'                   =>  'Modifica alla struttura di servizio non riuscita',//'The Service Body Change Failed',
                                            'service_body_editor_screen_sb_id_label'                =>  'ID:',//'ID:',
                                            'service_body_editor_screen_sb_name_label'              =>  'Nome:',//'Name:',
                                            'service_body_name_default_prompt_text'                 =>  'Inserisci il nome di questa struttura di servizio',//'Enter the Name of This Service Body',
                                            'service_body_parent_popup_label'                       =>  'Struttura di servizio genitore:',//'Service Body Parent:',
                                            'service_body_parent_popup_no_parent_option'            =>  'Nessun genitore (primo livello)',//'No Parent (Top-Level)',
                                            'service_body_editor_screen_sb_admin_user_label'        =>  'Amministratore primario:',//'Primary Admin:',
                                            'service_body_editor_screen_sb_admin_description_label' =>  'Descrizione:',//'Description:',
                                            'service_body_description_default_prompt_text'          =>  'Inserisci la descrizione di questa struttura di servizio',//'Enter A Description of This Service Body',
                                            'service_body_editor_screen_sb_admin_email_label'       =>  'Contatto email:',//'Contact Email:',
                                            'service_body_email_default_prompt_text'                =>  'Inserisci il contatto email di questa struttura di servizio',//'Enter A Contact Email Address for This Service Body',
                                            'service_body_editor_screen_sb_admin_uri_label'         =>  'Indirizzo (URL) del sito web:',//'Web Site URL:',
                                            'service_body_uri_default_prompt_text'                  =>  'Inserisci l\'indirizzo (URL) del sito web di questa struttura di servizio',//'Enter A Web Site URL for This Service Body',
                                            'service_body_editor_screen_sb_admin_full_editor_label' =>  'Lista completa delle riunioni:',//'Full Meeting List Editors:',
                                            'service_body_editor_screen_sb_admin_full_editor_desc'  =>  'Questi utenti possono modificare tutte le riunioni di questa struttura di servizio.',//'These Users Can Edit Any Meetings In This Service Body.',
                                            'service_body_editor_screen_sb_admin_editor_label'      =>  'Editor del BMLT:',//'Basic Meeting List Editors:',
                                            'service_body_editor_screen_sb_admin_editor_desc'       =>  'Questi utenti possono modificare tutte le riunioni di questa struttura di servizio, ma solo se non sono ancora state pubblicate.',//'These Users Can Edit Any Meetings In This Service Body, But Only If They Are Unpublished.',
                                            'service_body_editor_screen_sb_admin_observer_label'    =>  'Osservatori:',//'Observers:',
                                            'service_body_editor_screen_sb_admin_observer_desc'     =>  'Questi utenti possono vedere informazioni nascoste (come gli indirizzi email), ma non possono modificare niente.',//'These Users Can See Hidden Info (Like Email Addresses), But Cannot Edit Anything.',
                                            'service_body_dirty_confirm_text'                       =>  'Hai fatto delle modifiche a questa struttura di servizio. Vuoi perdere queste variazioni?',//'You have made changes to this Service Body. Do you want to lose your changes?',
                                            'service_body_save_button'                              =>  'Salva queste modifiche alla struttura di servizio',//'Save These Service Body Changes',
                                            'service_body_create_button'                            =>  'Crea questa struttura di servizio',//'Create This Service Body',
                                            'service_body_delete_button'                            =>  'Cancella questa struttura di servizio',//'Delete This Service Body',
                                            'service_body_delete_perm_checkbox'                     =>  'Cancella definitivamente questa struttura di servizio',//'Delete This Service Body Permanently',
                                            'service_body_delete_button_confirm'                    =>  'Sei sicuro di voler cancellare questa struttura di servizio? Make sure that all meetings are either removed or transferred to another service body before performing this function.',//'Are you sure that you want to delete this Service body?',
                                            'service_body_delete_button_confirm_perm'               =>  'Questa struttura di servizio sarà cancellata definitivamente!',//'This Service body will be deleted permanently!',
                                            'service_body_change_fader_create_success_text'         =>  'Struttura di servizio creata con successo',//'The Service Body Was Successfully Created',
                                            'service_body_change_fader_create_fail_text'            =>  'Creazione della struttura di servizio non riuscita',//'The Service Body Create Failed',
                                            'service_body_change_fader_delete_success_text'         =>  'Struttura di servizio cancellata con successo',//'The Service Body Was Successfully Deleted',
                                            'service_body_change_fader_delete_fail_text'            =>  'Creazione della struttura di servizio non riuscita',//'The Service Body Delete Failed',
                                            'service_body_change_fader_fail_no_data_text'           =>  'Modifica alla struttura di servizio non riuscita per mancanza di dati',//'The Service Body Change Failed, Because There Was No Data Supplied',
                                            'service_body_change_fader_fail_cant_find_sb_text'      =>  'La modifica alla struttura di servizio non è riuscita perché la struttura non è stata trovata',//'The Service Body Change Failed, Because The Service Body Was Not Found',
                                            'service_body_change_fader_fail_cant_update_text'       =>  'La modifica alla struttura di servizio non è riuscita perché la struttura non era aggiornata',//'The Service Body Change Failed, Because The Service Body Was Not Updated',
                                            'service_body_change_fader_fail_bad_hierarchy'          =>  'La modifica alla struttura di servizio non è riuscita perché il proprietario della struttura di servizio selezionato è sotto questa struttura di servizio e non può essere usato',//'The Service Body Change Failed, Because The Selected Owner Service Body Is Under This Service Body, And Cannot Be Used',
                                            'service_body_cancel_button'                            =>  'Ripristina l\'originale',//'Restore To Original',
                                            'service_body_editor_type_label'                        =>  'Tipo di struttura di servizio:',//'Service Body Type:',
                                            'service_body_editor_type_c_comdef_service_body__GRP__' =>  'Gruppo',//'Group',
                                            'service_body_editor_type_c_comdef_service_body__COP__' =>  'Co-Op',//'Co-Op',
                                            'service_body_editor_type_c_comdef_service_body__ASC__' =>  'Comitato di servizio d\'area',//'Area Service Committee',
                                            'service_body_editor_type_c_comdef_service_body__RSC__' =>  'Conferenza dei servizi di regione',//'Regional Service Conference',
                                            'service_body_editor_type_c_comdef_service_body__WSC__' =>  'Conferenza dei servizi mondiali',//'World Service Conference',
                                            'service_body_editor_type_c_comdef_service_body__MAS__' =>  'Area metropolitana',//'Metro Area',
                                            'service_body_editor_type_c_comdef_service_body__ZFM__' =>  'Forum zonale',//'Zonal Forum',
                                            'service_body_editor_screen_helpline_label'             =>  'Telefono:',
                                            'service_body_editor_screen_helpline_prompt'            =>  'Inserisci il numero di telefono',
                                            'service_body_editor_uri_naws_format_text'              =>  'Scarica le riunioni di questa struttura di servizio come file compatibile con i Servizi Mondiali di NA',//'Get The Meetings For This Service Body As A NAWS-Compatible File',
                                            'edit_Meeting_meeting_id'                               =>  'ID della riunione:',//'Meeting ID:',
                                            'service_body_editor_create_new_sb_option'              =>  'Crea una nuova struttura di servizio',//'Create A New Service Body',
                                            'service_body_editor_screen_world_cc_label'             =>  'Codice del comitato mondiale:',//'World Committee Code:',
                                            'service_body_editor_screen_world_cc_prompt'            =>  'Inserisci un codice per il comitato mondiale',//'Enter A World Committee Code',
                                            'user_editor_disclosure'                                =>  'Amministrazione dell\'utente',//'User Administration',
                                            'user_editor_create_new_user_option'                    =>  'Crea un nuovo utente',//'Create A New User',
                                            'user_editor_screen_sb_id_label'                        =>  'ID:',//'ID:',
                                            'user_editor_account_login_label'                       =>  'Autenticazione dell\'utente:',//'User Login:',
                                            'user_editor_login_default_text'                        =>  'Inserisci l\'autenticazione dell\'utente',//'Enter the User Login',
                                            'user_editor_account_type_label'                        =>  'L\'utente è un:',//'User Is A:',
                                            'user_editor_user_owner_label'                          =>  'Owned By: ', // TODO translate
                                            'user_editor_account_type_1'                            =>  'Amministratore del server',//'Server Administrator',
                                            'user_editor_account_type_2'                            =>  'Amministratore della struttura di servizio',//'Service Body Administrator',
                                            'user_editor_account_type_3'                            =>  'Editor nella struttura di servizio',//'Service Body Editor',
                                            'user_editor_account_type_5'                            =>  'Osservatore nella struttura di servizio',//'Service Body Observer',
                                            'user_editor_account_type_4'                            =>  'Disattiva utente',//'Disabled User',
                                            'user_editor_account_name_label'                        =>  'Nome utente:',//'User Name:',
                                            'user_editor_name_default_text'                         =>  'Inserisci il nome utente',//'Enter the User Name',
                                            'user_editor_account_description_label'                 =>  'Descrizione:',//'Description:',
                                            'user_editor_description_default_text'                  =>  'Inserisci la descrizione dell\'utente',//'Enter the User Description',
                                            'user_editor_account_email_label'                       =>  'Email:',//'Email:',
                                            'user_editor_email_default_text'                        =>  ' l\'email dell\'utente',//'Enter the User Email',
                                            'user_change_fader_success_text'                        =>  'L\'utente è stato modificato con successo',//'The User Was Successfully Changed',
                                            'user_change_fader_fail_text'                           =>  'La modifica all\'utente è fallita',//'The User Change Failed',
                                            'user_change_fader_create_success_text'                 =>  'L\'utente è stato creato con successo',//'The User Was Successfully Created',
                                            'user_change_fader_create_fail_text'                    =>  'La creazione dell\'utente è fallita',//'The User Create Failed',
                                            'user_change_fader_create_fail_already_exists'          =>  'Esiste già un profilo per l\'utente che stai cercando di creare.',//'A Login For The User That You Are Trying To Create Already Exists.',
                                            'user_change_fader_delete_success_text'                 =>  'L\'utente è stato eliminato con successo',//'The User Was Successfully Deleted',
                                            'user_change_fader_delete_fail_text'                    =>  'L\'eliminazione dell\'utente è fallita',//'The User Delete Failed',
                                            'user_save_button'                                      =>  'Salva le modifiche per questo utente',//'Save the Changes to This User',
                                            'user_create_button'                                    =>  'Crea nuovo utente',
                                            'user_cancel_button'                                    =>  'Ripristina l\'originale',
                                            'user_delete_button'                                    =>  'Cancella questo utente',
                                            'user_delete_perm_checkbox'                             =>  'Cancella definitivamente questo utente',
                                            'user_password_label'                                   =>  'Cambia password per:',
                                            'user_new_password_label'                               =>  'Imposta password per:',
                                            'user_password_default_text'                            =>  'Lascialo così a meno che non voglia cambiare la password', /// 'Leave This Alone, Unless You Want To Change The Password'
                                            'user_new_password_default_text'                        =>  'Devi inserire un password per il nuovo utente', /// 'You Must Enter A Password For A new User',
                                            'user_dirty_confirm_text'                               =>  'Hai fatto delle modifiche a questo utente. Vuoi perdere queste modifiche?', /// 'You have made changes to this User. Do you want to lose your changes?',
                                            'user_delete_button_confirm'                            =>  'Sei sicuro di voler cancellare questo utente?', /// 'Are you sure that you want to delete this user?',
                                            'user_delete_button_confirm_perm'                       =>  'Questo utente sarà cancellato definitivamente!', /// 'This user will be deleted permanently!',
                                            'user_create_password_alert_text'                       =>  'I nuovi utenti devono avere una password. Non hai fornito una password per questo utente.', /// 'New users must have a password. You have not supplied a password for this user.',
                                            'user_change_fader_fail_no_data_text'                   =>  'Modifica utente non riuscita per mancanza di dati', /// 'The User Change Failed, Because There Was No Data Supplied'
                                            'user_change_fader_fail_cant_find_sb_text'              =>  'La modifica dell\'utente non è riuscita perché l\'utente non è stato trovato', /// 'The User Change Failed, Because The User Was Not Found'
                                            'user_change_fader_fail_cant_update_text'               =>  'La modifica dell\'utente non è riuscita perché l\'utente non era aggiornato', /// 'The User Change Failed, Because The User Was Not Updated'
                                            'format_editor_disclosure'                              =>  'Formato amministrazione', /// 'Format Administration'
                                            'format_change_fader_change_success_text'               =>  'Formato cambiato con successo', /// 'The Format Was Successfully Changed'
                                            'format_change_fader_change_fail_text'                  =>  'Modifica al formato non riuscita', /// 'The Format Change Failed'
                                            'format_change_fader_create_success_text'               =>  'Formato creato con successo', /// 'The Format Was Successfully Created'
                                            'format_change_fader_create_fail_text'                  =>  'Creazione del formato non riuscita', /// 'The Format Create Failed'
                                            'format_change_fader_delete_success_text'               =>  'Il formato è stato cancellato con successo', /// 'The Format Was Successfully Deleted'
                                            'format_change_fader_delete_fail_text'                  =>  'Cancellazione del formato non riuscita', /// 'The Format Delete Failed'
                                            'format_change_fader_fail_no_data_text'                 =>  'Modifica del formato non riuscita per mancanza di dati', /// 'The Format Change Failed, Because There Was No Data Supplied'
                                            'format_change_fader_fail_cant_find_sb_text'            =>  'La modifica del formato non è riuscita perché il formato non è stato trovato', /// 'The Format Change Failed, Because The Format Was Not Found'
                                            'format_change_fader_fail_cant_update_text'             =>  'La modifica del formato non è riuscita perché il formato non era aggiornato', /// 'The Format Change Failed, Because The Format Was Not Updated'
                                            'format_editor_name_default_text'                       =>  'Inserisci una descrizione molto breve', /// 'Enter A Very Short Description'
                                            'format_editor_description_default_text'                =>  'Inserisci una descrizione più dettagliata', /// 'Enter A More Detailed Description'
                                            'format_editor_create_format_button_text'               =>  'Crea un nuovo formato', /// 'Create New Format'
                                            'format_editor_cancel_create_format_button_text'        =>  'Cancella', /// 'Cancel'
                                            'format_editor_create_this_format_button_text'          =>  'Crea questo formato', /// 'Create This Format'
                                            'format_editor_change_format_button_text'               =>  'Modifica questo formato', /// 'Change This Format'
                                            'format_editor_delete_format_button_text'               =>  'Cancella questo formato', /// 'Delete This Format'
                                            'format_editor_reset_format_button_text'                =>  'Ripristina l\'originale', /// 'Restore To Original'
                                            'need_refresh_message_fader_text'                       =>  'Dovresti aggiornare questa pagina prima di usare questa sezione', /// 'You Should Refresh This Page Before Using This Section'
                                            'need_refresh_message_alert_text'                       =>  'Siccome hai fatto delle modifiche all\'amministrazione di questa struttura di servizio, di questo utente o di questo formato, le informazioni visualizzate in questa sezione potrebbero non esser più accurate; per questo la pagina deve essere aggiornata. Il modo più facile di farlo è disconnettersi e rifare nuovamente l\'accesso.', /// 'Because you have made changes in the Service Body Administration, User Administration or Format Administration, the information displayed in this section may no longer be accurate, so the page needs to be refreshed. The easiest way to do this, is to Sign Out, then Log In again.'
                                            'format_editor_delete_button_confirm'                   =>  'Sei sicuro di voler cancellare questo formato?', /// 'Are you sure that you want to delete this format?'
                                            'format_editor_delete_button_confirm_perm'              =>  'Questo formato sarà cancellato definitivamente!', /// 'This format will be deleted permanently!'
                                            'min_password_length_string'                            =>  'La password è troppo corta! Deve essere lunga almeno %d caratteri!', /// 'The password is too short! It must be at least %d characters long!'
                                            'AJAX_Auth_Failure'                                     =>  'Autorizzazione fallita per questa operazione. Ci potrebbe essere un problema con la configurazione del server.', /// 'Authorization failed for this operation. There may be a problem with the server configuration.'
                                            'Maps_API_Key_Warning'                                  =>  'There is a problem with the Google Maps API Key.',
                                            'Observer_Link_Text'                                    =>  'Meeting Browser',
                                            'Data_Transfer_Link_Text'                               =>  'Importazione dati delle riunioni (ATTENZIONE: i dati correnti saranno sostituiti!)', /// 'Import Meeting Data (WARNING: Replaces Current Data!)'
                                            'MapsURL'                                               =>  'https://maps.google.com/maps?q=##LAT##,##LONG##+(##NAME##)&amp;ll=##LAT##,##LONG##',
                                            'hidden_value'                                          =>  'Non è possibile mostrare dati (manca l\'autorizzazione)', /// 'Cannot Display Data -Unauthorized'
                                            'Value_Prompts'                                         =>  array ( 'id_bigint' => 'ID riunione', /// 'Meeting ID'
                                                                                                                'worldid_mixed' => 'ID dei Servizi Mondiali di NA', /// 'World Services ID',
                                                                                                                'service_body' => 'Struttura di servizio', /// 'Service Body',
                                                                                                                'service_bodies' => 'Strutture di servizio', /// 'Service Bodies',
                                                                                                                'weekdays' => 'Giorni della settimana', /// 'Weekdays',
                                                                                                                'weekday' => 'La riunione si tiene ogni', /// 'Meeting Gathers Every',
                                                                                                                'start_time' => 'La riunione inizia alle ore', /// 'Meeting Starts at',
                                                                                                                'duration_time' => 'La riunione dura', /// 'Meeting Lasts',
                                                                                                                'location' => 'Località', /// 'Location',
                                                                                                                'duration_time_hour' => 'Ora', /// 'Hour',
                                                                                                                'duration_time_hours' => 'Ore', /// 'Hours',
                                                                                                                'duration_time_minute' => 'Minuto', /// 'Minute',
                                                                                                                'duration_time_minutes' => 'Minuti', /// 'Minutes',
                                                                                                                'lang_enum' => 'Lingua', /// 'Language',
                                                                                                                'formats' => 'Formato', /// 'Formats',
                                                                                                                'distance' => 'Distanza dal centro', /// 'Distance from Center',
                                                                                                                'generic' => 'Riunione NA', /// 'NA Meeting',
                                                                                                                'close_title' => 'Chiudi questa finestra di dettaglio della riunione', /// 'Close This Meeting Detail Window',
                                                                                                                'close_text' => 'Chiudi finestra', /// 'Close Window',
                                                                                                                'map_alt' => 'Mappa della riunione', /// 'Map to Meeting',
                                                                                                                'map' => 'Segui questo link per la mappa', /// 'Follow This Link for A Map',
                                                                                                                'title_checkbox_unpub_meeting' => 'Questa riunione non è pubblicata e non può essere vista nelle normali ricerche.', /// 'This meeting is unpublished. It cannot be seen by regular searches.',
                                                                                                                'title_checkbox_copy_meeting' => 'Questa riunione è il duplicato di un\'altra ed è anche non pubblicata; inoltre, non può essere vista nelle normali ricerche.' /// 'This meeting is a duplicate of another meeting. It is also unpublished. It cannot be seen by regular searches.'
                                                                                                            ),
                                            'world_format_codes_prompt'                             =>  'NAWS Format:',
                                            'world_format_codes'                                    =>  array (
                                                                                                            ''      =>  'Nessuno',//'None',
                                                                                                            'APERTA'=>  'Aperta',//'OPEN'//'Open',
                                                                                                            'CHIUSA'=>  'Chiusa',//'CLOSED'//'Closed',
                                                                                                            'DISAB' =>  'Accessibile ai disabili',//'WCHR'//'Wheelchair-Accessible',
                                                                                                            'PR'    =>  'Principianti/Nuovi venuti',//'BEG'//'Beginner/Newcomer',
                                                                                                            'TB'    =>  'Testo base',//'BT'//'Basic Text',
                                                                                                            'CAND'  =>  'Lume di candela',//'CAN'//'Candlelight',
                                                                                                            '12C'   =>  'Dodici Concetti',//'CPT'//'12 Concepts',
                                                                                                            'BAMB'  =>  'Ammessi i bambini',//'CW'//'Children Welcome',
                                                                                                            'DISC'  =>  'Discussione/Participazione',//'DISC'//'Discussion/Participation',
                                                                                                            'GL'    =>  'Gay/Lesbiche',//'GL'//'Gay/Lesbian',
                                                                                                            'IP'    =>  'Studio dei pamphlet',//'IP'//'IP Study',
                                                                                                            'FCP'   =>  'Studio di Funziona: come e perché',//'IW'  //'It Works Study',
                                                                                                            'SPO'   =>  'Studio del Solo per oggi',//'JFT'//'Just For Today Study',
                                                                                                            'VP'    =>  'Vivere puliti',//'LC'//'Living Clean',
                                                                                                            'LETT'  =>  'Studio della letteratura',//'LIT'//'Literature Study',
                                                                                                            'U'     =>  'Uomini',//'M'//'Men',
                                                                                                            'MEDIT' =>  'Meditazione',//'MED'//'Meditation',
                                                                                                            'DR'    =>  'Domande e risposte',//'QA'//'Questions & Answers',
                                                                                                            'AL'    =>  'Accesso limitato',//'RA'//'Restricted Access',
                                                                                                            'FUM'   =>  'Fumatori',//'SMOK'//'Smoking',
                                                                                                            'ORAT'  =>  'Oratore (sedia)',//'SPK'//'Speaker',
                                                                                                            '12P'   =>  'Passi',//'STEP'//'Step',
                                                                                                            'GLP'   =>  'Studio della Guida al lavoro sui passi',//'SWG'//'Step Working Guide Study',
                                                                                                            'ARG'   =>  'Argomento',//'TOP'//'Topic',
                                                                                                            '12T'   =>  'Tradizioni',//'TRAD'//'Tradition',
                                                                                                            'VAR'   =>  'Formato variabile',//'VAR'//'Format Varies',
                                                                                                            'D'     =>  'Donne',//'W'//'Women',
                                                                                                            'GIO'   =>  'Giovani',//'Y'//'Young People',
                                                                                                            'LAL'   =>  'Lingue alternate', //'LANG'//'Alternate Language'
                                                                                                            'GP'    =>  'Guiding Principles', // TODO translate
                                                                                                            'NC'    =>  'No Children', // TODO translate
                                                                                                            'CH'    =>  'Closed Holidays', // TODO translate
                                                                                                            'VM'    =>  'Virtual', // TODO translate
                                                                                                            'HYBR'  =>  'Virtual and In-Person', // TODO translate
                                                                                                            'TC'    =>  'Facility Temporarily Closed' // TODO translate
                                                                                                            ),
                                            'format_type_prompt'                                    =>  'Format Type:',    // TODO: Translate
                                            'format_type_codes'                                     =>  array (
                                                                                                            ''      =>  'None',    // TODO: Translate
                                                                                                            'FC1'  =>  'Meeting Format (Speaker, Book Study, etc.)',    // TODO: Translate
                                                                                                            'FC2'  =>  'Location Code (Wheelchair Accessible, Limited Parking, etc.)',    // TODO: Translate
                                                                                                            'FC3'  =>  'Common Needs and Restrictions (Mens Meeting, LGTBQ, No Children, etc.)',    // TODO: Translate
                                                                                                            'O'    =>  'Attendance by non-addicts (Open, Closed)',    // TODO: Translate
                                                                                                            'LANG' =>  'Language', // TRANSLATE
                                                                                                            'ALERT'=>  'Format should be especially prominent (Clean requirement, etc.)',// TODO: Translate
                                                                                                            ),
                                            'cookie_monster'                                        =>  'Questo sito usa dei cookie per conservare le impostazioni della tua lingua preferita.',//'This site uses a cookie to store your preferred language.',
                                            'main_prompts'                                          =>  array ( 'id_bigint' => 'ID',//'ID',
                                                                                                                'worldid_mixed' => 'ID mondiale',//'World ID',
                                                                                                                'shared_group_id_bigint' => 'Inutilizzato',//'Unused',
                                                                                                                'service_body_bigint' => 'ID della struttura di servizio',//'Service Body ID',
                                                                                                                'weekday_tinyint' => 'Giorno della settimana',//'Weekday',
                                                                                                                'start_time' => 'Ora d\'inizio',//'Start Time',
                                                                                                                'duration_time' => 'Durata',//'Duration',
                                                                                                                'time_zone' => 'Time Zone',
                                                                                                                'formats' => 'Formati',//Formats',
                                                                                                                'lang_enum' => 'Lingua',//'Language',
                                                                                                                'longitude' => 'Longitudine',//'Longitude',
                                                                                                                'latitude' => 'Latitudine',//'Latitude',
                                                                                                                'published' => 'Pubblicato',//'Published',
                                                                                                                'email_contact' => 'Contatto email',//'Email Contact',
                                                                                                                ),
                                            'check_all'                                             => 'Check All',
                                            'uncheck_all'                                           => 'Uncheck All',
                                            'automatically_calculated_on_save'                      => 'Automatically calculated on save.'
                                        );

    $email_contact_strings = array (
        'meeting_contact_form_subject_format'   =>  "[MEETING LIST CONTACT] %s",
        'meeting_contact_message_format'        =>  "%s\n--\nThis message concerns the meeting named \"%s\", which meets at %s, on %s.\nBrowser Link: %s\nEdit Link: %s\nIt was sent directly from the meeting list web server, and the sender is not aware of your email address.\nPlease be aware that replying will expose your email address.\nIf you use \"Reply All\", and there are multiple email recipients, you may expose other people's email addresses.\nPlease respect people's privacy and anonymity; including the original sender of this message."
    );
    
    $change_type_strings = array (
        '__THE_MEETING_WAS_CHANGED__' => 'La riunione è stata modificata.',//'The meeting was changed.', THEN: created, deleted rolled back...
        '__THE_MEETING_WAS_CREATED__' => 'La riunione è stata creata.',
        '__THE_MEETING_WAS_DELETED__' => 'La riunione è stata cancellata.',
        '__THE_MEETING_WAS_ROLLED_BACK__' => 'La riunione è stata riportata a una versione precedente.',
    
        '__THE_FORMAT_WAS_CHANGED__' => 'Il formato è stato modificato.',//'The format was changed.', THEN: created, deleted rolled back...
        '__THE_FORMAT_WAS_CREATED__' => 'Il formato è stato creato.',
        '__THE_FORMAT_WAS_DELETED__' => 'Il formato è stato cancellato.',
        '__THE_FORMAT_WAS_ROLLED_BACK__' => 'Il formato è stato riportato a una versione precedente.',
    
        '__THE_SERVICE_BODY_WAS_CHANGED__' => 'La struttura di servizio è stata modificata.',//'The service body was changed.', THEN: created, deleted rolled back...
        '__THE_SERVICE_BODY_WAS_CREATED__' => 'La struttura di servizio è stata creata.',
        '__THE_SERVICE_BODY_WAS_DELETED__' => 'La struttura di servizio è stata cancellata.',
        '__THE_SERVICE_BODY_WAS_ROLLED_BACK__' => 'La struttura di servizio è stata riportata a una versione precedente.',
    
        '__THE_USER_WAS_CHANGED__' => 'L\'utente è stato modificato.',//'The user was changed.', THEN: created, deleted rolled back...
        '__THE_USER_WAS_CREATED__' => 'L\'utente è stato creato.',
        '__THE_USER_WAS_DELETED__' => 'L\'utente è stato cancellato.',
        '__THE_USER_WAS_ROLLED_BACK__' => 'L\'utente è stato riportato a una versione precedente.',
    
        '__BY__' => 'da',//'by',
        '__FOR__' => 'per'//'for'
    );
    
    $detailed_change_strings = array (
        'was_changed_from' => 'è stato modificato da',//was changed from',
        'to' => 'a',//'to',
        'was_changed' => 'è stato modificato',//'was changed',
        'was_added_as' => 'è stato aggiunto come',//'was added as',
        'was_deleted' => 'è stato cancellato',//'was deleted',
        'was_published' => 'La riunione è stata pubblicata',//'The meeting was published',
        'was_unpublished' => 'La riunione non è più pubblicata',//'The meeting was unpublished',
        'formats_prompt' => 'Il formato della riunione',//'The meeting format',
        'duration_time' => 'La durata della riunione',//'The meeting duration',
        'start_time' => 'L\'ora di inizio della riunione',//'The meeting start time',
        'longitude' => 'La longitudine della riunione',
        'latitude' => 'La latitudine della riunione',
        'sb_prompt' => 'La riunione ha modificato la sua struttura di servizio da',//'The meeting changed its Service Body from',
        'id_bigint' => 'ID della riunione',//'The meeting ID',
        'lang_enum' => 'Lingua della riunione',//'The meeting language',
        'worldid_mixed' => 'ID del gruppo condiviso',//'The shared Group ID',
        'weekday_tinyint' => 'Il giorno della settimana in cui si tiene la riunione',//'The day of the week on which the meeting gathers',
        'non_existent_service_body' => 'La struttura di servizio non esiste più',//'Service Body No Longer Exists',
    );
    
    defined('_END_CHANGE_REPORT') or define('_END_CHANGE_REPORT', '.');
