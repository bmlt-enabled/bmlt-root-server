<?php
/***********************************************************************/
/** \file   server_admin_strings.inc.php
 * \resumo As strings exibidas no console de administração do servidor (em português)
 * Este arquivo é parte da Ferramenta Básica de Lista de Reuniões (BMLT)
 * Encontre mais em: https://bmlt.app
 * BMLT é um software livre: você pode redistribuí-lo e/ou modificá-lo
 * sob os termos de MIT License.
 * BMLT é distribuído com a esperança de que seja útil,
 * mas SEM NENHUMA GARANTIA; sem sequer a garantia implícita de
 *     COMERCIALIZAÇÃO ou ADEQUAÇÃO PARA UMA FINALIDADE ESPECÍFICA. Veja o
 * MIT License para mais detalhes.
 * Você deve ter recebido uma cópia da Licença MIT junto com este código.
 * Se não, acesse <https://opensource.org/licenses/MIT>. */

defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Certifique-se que este arquivo esteja no contexto correto.

$comdef_server_admin_strings = array('server_admin_disclosure' => 'Administração do Servidor',
    'server_admin_naws_spreadsheet_label' => 'Planilha atualizada de IDs Mundiais:',
    'update_world_ids_button_text' => 'Atualizar IDs Mundiais de reunião',
    'update_world_ids_from_spreadsheet_dropdown_text' => 'Atualizar IDs Mundiais de reunião pela planilha do NAWS',
    'server_admin_error_no_world_ids_updated' => 'Nenhum ID mundial foi atualizado. Isso pode ser por seu usuário não ter permissão para essas reuniões',
    'server_admin_error_required_spreadsheet_column' => 'Coluna requerida no existe na planilha: ',
    'server_admin_error_bmlt_id_not_integer' => 'O ID BMLT provido não é do tipo integer: ',
    'server_admin_error_could_not_create_reader' => 'Não foi possível criar um leitor para o arquivo: ',
    'server_admin_error_no_files_uploaded' => 'Nenhum arquivo foi enviado.',
    'server_admin_error_service_bodies_already_exist' => 'Corpos de Serviço com os seguintes IDs Mundiais já existem: ',
    'server_admin_error_meetings_already_exist' => 'Reuniões com os seguintes IDs Mundiais já existem: ',
    'server_admin_ui_num_meetings_updated' => 'Numero de reuniões atualizadas: ',
    'server_admin_ui_num_meetings_not_updated' => 'Numero de reuniões que não precisam de atualização: ',
    'server_admin_ui_warning' => 'AVISO',
    'server_admin_ui_errors' => 'Erro(s)',
    'server_admin_ui_meetings_not_found' => 'reuniões foram encontradas na planilha que não existiam na base de dados. Isso pode acontecer quando uma reunião é apagada ou não publicada. Os IDs de reunião são: ',
    'server_admin_ui_service_bodies_created' => 'Corpos de serviço criados: ',
    'server_admin_ui_meetings_created' => 'Reuniões criadas: ',
    'server_admin_ui_users_created' => 'Usuários criados: ',
    'server_admin_ui_refresh_ui_text' => 'Saia e faça novo login para ver os novos corpos de serviço, usuarios, e reuniões.',
    'import_service_bodies_and_meetings_button_text' => 'Importar corpo de serviço e reuniões',
    'import_service_bodies_and_meetings_dropdown_text' => 'Importar corpo de serviço e reuniões da exportação do NAWS (NAWS Export)',
    'server_admin_naws_import_spreadsheet_label' => 'Planilha Importada do NAWS:',
    'server_admin_naws_import_initially_publish' => 'Initialize imported meetings to \'published\': ',
    'server_admin_naws_import_explanation' => 'Uncheck the box to initialize imported meetings to \'unpublished\'. (This is useful if many of the new meetings will need to be edited or deleted, and you don\'t want them showing up in the meantime.)',
    'account_disclosure' => 'Minha conta',
    'account_name_label' => 'Nome da minha conta:',
    'account_login_label' => 'Meu login:',
    'account_type_label' => 'Eu sou:',
    'account_type_1' => 'Administrador do Servidor',
    'account_type_2' => 'Administrador de corpo de serviço',
    'ServerMapsURL' => 'https://maps.googleapis.com/maps/api/geocode/xml?address=##SEARCH_STRING##&sensor=false',
    'account_type_4' => 'Pathetic Luser Who Shouldn\'t Even Have Access to This Page -The Author of the Software Pooched it BAD!',
    'account_type_5' => 'Observador',
    'change_password_label' => 'Mude minha senha para:',
    'change_password_default_text' => 'Deixe sem preencher se não pretende mudar a senha',
    'account_email_label' => 'Meu endereço de e-mail:',
    'email_address_default_text' => 'Coloque seu e-mail',
    'account_description_label' => 'Descrição:',
    'account_description_default_text' => 'Coloque uma Descrição',
    'account_change_button_text' => 'Mude meus dados da conta',
    'account_change_fader_success_text' => 'Os dados da conta foram alterados com sucesso',
    'account_change_fader_failure_text' => 'Os dados da conta NÃO foram alterados',
    'meeting_editor_disclosure' => 'Editor de Reuniões',
    'meeting_editor_already_editing_confirm' => 'Você está atualmente editando uma reunião. Você realmente quer PERDER todas as alterações?',
    'meeting_change_fader_success_text' => 'Os dados da reunião foram alterados com sucesso',
    'meeting_change_fader_failure_text' => 'Os dados da reunião não foram alterados',
    'meeting_change_fader_success_delete_text' => 'A reunião foi removida com sucesso',
    'meeting_change_fader_fail_delete_text' => 'A reunião não foi apagada',
    'meeting_change_fader_success_add_text' => 'A nova reunião foi criada com sucesso!',
    'meeting_change_fader_fail_add_text' => 'A nova reunião NÃO foi criada...',
    'meeting_text_input_label' => 'Busque pelo texto:',
    'access_service_body_label' => 'Eu tenho acesso a:',
    'meeting_text_input_default_text' => 'Digite o texto para buscar',
    'meeting_text_location_label' => 'Isso é um local ou CEP',
    'meeting_search_weekdays_label' => 'Busca por dia da semana selecionado:',
    'meeting_search_weekdays_names' => array('Todos', 'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'),
    'meeting_search_service_bodies_label' => 'Busca pelo corpo de serviço selecionado:',
    'meeting_search_start_time_label' => 'Busca pelo horário da reunião:',
    'meeting_search_start_time_all_label' => 'Qualquer horário',
    'meeting_search_start_time_morn_label' => 'Manhã',
    'meeting_search_start_time_aft_label' => 'Tarde',
    'meeting_search_start_time_eve_label' => 'Noite',
    'meeting_search_no_results_text' => 'Nenhuma reunião encontrada',
    'meeting_editor_tab_specifier_text' => 'Busca por reuniões',
    'meeting_editor_tab_editor_text' => 'Editar Reuniões',
    'meeting_editor_create_new_text' => 'Criar Nova Reunião',
    'meeting_editor_location_map_link' => 'Localização no Mapa',
    'meeting_editor_screen_match_ll_button' => 'Configure Longitude e Latitude do Endereço',
    'meeting_editor_screen_default_text_prompt' => 'Coloque um texto ou numero',
    'meeting_is_published' => 'Reunião está publicada',
    'meeting_unpublished_note' => 'Nota: Despublicar uam reunião indica que estátemporariamente fechada. Se ela está permanentemente fechada, por favor a delete.',
    'meeting_editor_screen_meeting_name_label' => 'Nome da Reunião:',
    'meeting_editor_screen_meeting_name_prompt' => 'Coloque o nome da reunião',
    'meeting_editor_screen_meeting_weekday_label' => 'Dia da Semana:',
    'meeting_editor_screen_meeting_start_label' => 'Horário de ínicio da Reunião:',
    'meeting_editor_screen_meeting_time_zone_label' => 'Meeting Time Zone:',
    'meeting_editor_screen_meeting_am_label' => 'AM',
    'meeting_editor_screen_meeting_pm_label' => 'PM',
    'meeting_editor_screen_meeting_noon_label' => 'Meio-Dia',
    'meeting_editor_screen_meeting_midnight_label' => 'Meia-Noite',
    'meeting_editor_screen_meeting_duration_label' => 'Duration:',
    'meeting_editor_screen_meeting_oe_label' => 'Aberta até o final',
    'meeting_editor_screen_meeting_cc_label' => 'Código do Comitê Mundial:',
    'meeting_editor_screen_meeting_cc_prompt' => 'Coloque o código do Comitê Mundial',
    'meeting_editor_screen_meeting_contact_label' => 'Contato por e-mail:',
    'meeting_editor_screen_meeting_contact_prompt' => 'Contato por e-mail apenas para essa reunião',
    'meeting_editor_screen_meeting_sb_label' => 'Corpo de Serviço:',
    'meeting_editor_screen_meeting_sb_default_value' => 'Nenhum Corpo de Serviço selecionado',
    'meeting_editor_screen_meeting_longitude_label' => 'Longitude:',
    'meeting_editor_screen_meeting_longitude_prompt' => 'Coloque a Longitude',
    'meeting_editor_screen_meeting_latitude_label' => 'Latitude:',
    'meeting_editor_screen_meeting_latitude_prompt' => 'Coloque a Latitude',
    'meeting_editor_screen_meeting_location_label' => 'Referência:',
    'meeting_editor_screen_meeting_location_prompt' => 'Coloque uma referência (exemplo: nome do prédio)',
    'meeting_editor_screen_meeting_info_label' => 'Informação Extra:',
    'meeting_editor_screen_meeting_info_prompt' => 'Coloque aqui informações como Carimbo para beneficiários da Justiça ou outras informaçẽos relevantes',
    'meeting_editor_screen_meeting_street_label' => 'Nome da Rua ou Avenida:',
    'meeting_editor_screen_meeting_street_prompt' => 'Coloque aqui o endereço da entrada do grupo',
    'meeting_editor_screen_meeting_neighborhood_label' => 'Bairro:',
    'meeting_editor_screen_meeting_neighborhood_prompt' => 'Coloque aqui o nome do Bairro',
    'meeting_editor_screen_meeting_borough_label' => 'região da cidade(por exemplo zona leste ou centro)',
    'meeting_editor_screen_meeting_borough_prompt' => 'Coloque aqui a região (não é obrigatório)',
    'meeting_editor_screen_meeting_city_label' => 'Nome da Cidade:',
    'meeting_editor_screen_meeting_city_prompt' => 'Nome da Cidade',
    'meeting_editor_screen_meeting_county_label' => 'Região do País:',
    'meeting_editor_screen_meeting_county_prompt' => 'Coloque aqui a região do país (exemplo Região Norte, Nordeste, Sudeste, etc)',
    'meeting_editor_screen_meeting_state_label' => 'Estado:',
    'meeting_editor_screen_meeting_state_prompt' => 'Coloque aqui o nome do Estado',
    'meeting_editor_screen_meeting_zip_label' => 'CEP:',
    'meeting_editor_screen_meeting_zip_prompt' => 'Coloque aqui o CEP sem hífen (exemplo 9876500)',
    'meeting_editor_screen_meeting_nation_label' => 'País:',
    'meeting_editor_screen_meeting_nation_prompt' => 'nome do país',
    'meeting_editor_screen_meeting_comments_label' => 'Comentários:',
    'meeting_editor_screen_meeting_train_lines_label' => 'Linhas de trêm ou metrô:',
    'meeting_editor_screen_meeting_bus_lines_label' => 'Linhas de ônibus:',
    'meeting_editor_screen_meeting_phone_meeting_number_label' => 'Phone Meeting Dial-in Number:',
    'meeting_editor_screen_meeting_phone_meeting_number_prompt' => 'Enter the dial-in number for a phone or virtual meeting',
    'meeting_editor_screen_meeting_virtual_meeting_link_label' => 'Virtual Meeting Link:',
    'meeting_editor_screen_meeting_virtual_meeting_link_prompt' => 'Enter the link for a virtual meeting',
    'meeting_editor_screen_meeting_virtual_meeting_additional_info_label' => 'Virtual Meeting Additional Information:',
    'meeting_editor_screen_meeting_virtual_meeting_additional_info_prompt' => 'Enter any additional information for joining the virtual meeting, including directly from the app. For example, if the meeting uses Zoom, "Zoom ID: 456 033 8613, Passcode: 1953" would be appropriate.',
    'meeting_editor_screen_meeting_venue_type' => 'Venue Type:',
    'meeting_editor_screen_meeting_venue_type_inperson' => 'In-Person',
    'meeting_editor_screen_meeting_venue_type_virtual' => 'Virtual',
    'meeting_editor_screen_meeting_venue_type_virtualTC' => 'Virtual (temporarily replaced an in-person)',
    'meeting_editor_screen_meeting_venue_type_hybrid' => 'Hybrid (both In-Person and Virtual)',
    'meeting_editor_screen_meeting_venue_type_validation' => 'You must select a venue type.',
    'meeting_editor_screen_meeting_virtual_info_missing' => 'Virtual or hybrid meetings must have a Virtual Meeting Link, a Phone Meeting Dial-in Number, or Virtual Meeting Additional Information',
    'meeting_editor_screen_meeting_location_warning' => 'Meeting should have a location (at least a city/town and state/province, or a zip/postal code).',
    'meeting_editor_screen_meeting_address_warning' => 'In-person or hybrid meetings should have a street address.',
    'meeting_editor_screen_meeting_url_validation' => 'Virtual Meeting Link is not a valid URL.',
    'meeting_editor_screen_meeting_url_or_phone_warning' => 'Virtual or hybrid meetings should have either a Virtual Meeting Link or a Phone Meeting Dial-in Number',
    'meeting_editor_screen_meeting_additional_warning' => 'Please also fill in Virtual Meeting Additional Information if there is a Virtual Meeting Link.',
    'meeting_editor_screen_meeting_validation_warning' => 'There are warnings.  Are you sure you want to continue?  If not, press \'cancel\' and go to the Location tab to address them.',
    'meeting_editor_screen_meeting_validation_failed' => 'Unable to save due to input errors.  Please go to the Location tab to address them, and then retry saving.  Errors: ',
    'meeting_editor_screen_meeting_validation_warnings' => 'Input warnings shown on the Location tab: ',
    'meeting_editor_screen_meeting_contact_name_1_label' => 'Nome para contato:',
    'meeting_editor_screen_meeting_contact_email_1_label' => 'E-mail de contato:',
    'meeting_editor_screen_meeting_contact_phone_1_label' => 'Telefone de contato:',
    'meeting_editor_screen_meeting_contact_name_2_label' => 'Nome para contato:',
    'meeting_editor_screen_meeting_contact_email_2_label' => 'E-mail de contato:',
    'meeting_editor_screen_meeting_contact_phone_2_label' => 'Telefone de contato:',
    'meeting_editor_screen_meeting_publish_search_prompt' => 'Busque por:',
    'meeting_editor_screen_meeting_publish_search_pub' => 'Apenas reuniões publicadas',
    'meeting_editor_screen_meeting_publish_search_unpub' => 'Apenas reuniões não publicadas',
    'meeting_editor_screen_meeting_visibility_advice' => 'Isso não aparecera na pesquisa .',
    'meeting_editor_screen_meeting_publish_search_all' => 'Todas as reuniões',
    'meeting_editor_screen_meeting_create_button' => 'Criar nova Reunião',
    'meeting_editor_screen_delete_button' => 'Apagar essa reunião',
    'meeting_editor_screen_delete_button_confirm' => 'Tem certeza que quer apagar essa reunião?',
    'meeting_editor_screen_cancel_button' => 'Cancelar',
    'logout' => 'SAIR',
    'meeting_editor_screen_cancel_confirm' => 'Tem certeza que vai cancelar essa edição de reunião? Todas as alterações serão perdidas?',
    'meeting_lookup_failed' => 'A localização do endereço falhou',
    'meeting_lookup_failed_not_enough_address_info' => 'Não há informações suficientes para buscar o endereço.',
    'meeting_create_button_name' => 'Salvar como uma nova reunião',
    'meeting_saved_as_a_copy' => 'Salvar uma CÓPIA dessa reunião (Criando como uma nova reunião)',
    'meeting_save_buttonName' => 'Salvar as alterações dessa reunião',
    'meeting_editor_tab_bar_basic_tab_text' => 'Básico',
    'meeting_editor_tab_bar_location_tab_text' => 'Localização',
    'meeting_editor_tab_bar_format_tab_text' => 'Formato',
    'meeting_editor_tab_bar_other_tab_text' => 'Outros',
    'meeting_editor_tab_bar_history_tab_text' => 'Histórico',
    'meeting_editor_result_count_format' => '%d Reuniões encontradas',
    'meeting_id_label' => 'ID da reunião:',
    'meeting_editor_default_zoom' => '13',
    'meeting_editor_default_weekday' => '2',
    'meeting_editor_default_start_time' => '20:30:00',
    'login_banner' => 'Ferramenta Básica de Lista de Reuniões BMLT',
    'login_underbanner' => 'Console de Administração do servidor',
    'login' => 'ID de acesso',
    'password' => 'Senha',
    'button' => 'ENTRAR',
    'cookie' => 'Ativar os cookies do navegador para administrar esse servidor.',
    'noscript' => 'Você não vai conseguir administrar esse servidor sem JavaScript.',
    'title' => 'Por favor fazer login para administrar o servidor.',
    'edit_Meeting_object_not_found' => 'ERRO: A Reunião não foi encontrada.',
    'edit_Meeting_object_not_changed' => 'ERRO: A Reunião não foi alterada.',
    'edit_Meeting_auth_failure' => 'Você não tem permissão para editar essa reunião.',
    'not_auth_1' => 'NÃO ALTORIZADO',
    'not_auth_2' => 'Você não tem permissão para editar esse servidor.',
    'not_auth_3' => 'Problema com usuário ou senha usado.',
    'email_format_bad' => 'Verifique se o e-mail está correto.',
    'history_header_format' => '<div class="bmlt_admin_meeting_history_list_item_line_div history_item_header_div"><span class="bmlt_admin_history_list_header_date_span">%s</span><span class="bmlt_admin_history_list_header_user_span">by %s</span></div>',
    'history_no_history_available_text' => '<h1 class="bmlt_admin_no_history_available_h1">No History Available For This Meeting</h1>',
    'service_body_editor_disclosure' => 'Administração de Corpo de Serviço',
    'service_body_change_fader_success_text' => 'As configurações de Corpo de Serviço foram alteradas com sucesso',
    'service_body_change_fader_fail_text' => 'Falha na configuração de Corpo de Serviço',
    'service_body_editor_screen_sb_id_label' => 'ID:',
    'service_body_editor_screen_sb_name_label' => 'Nome:',
    'service_body_name_default_prompt_text' => 'Coloque aqui o nome da estrutura de serviço',
    'service_body_parent_popup_label' => 'Estrutura de Serviço Responsável:',
    'service_body_parent_popup_no_parent_option' => 'Estrutura de Serviço Líder',
    'service_body_editor_screen_sb_admin_user_label' => 'Administrador Primário:',
    'service_body_editor_screen_sb_admin_description_label' => 'Descrição:',
    'service_body_description_default_prompt_text' => 'Coloque aqui a descrição da estrutura de serviço',
    'service_body_editor_screen_sb_admin_email_label' => 'Email de contato:',
    'service_body_email_default_prompt_text' => 'E-mail de contato da estrutura',
    'service_body_editor_screen_sb_admin_uri_label' => 'site da estrutura:',
    'service_body_uri_default_prompt_text' => 'Coloque aqui o site da estrutura de serviço',
    'service_body_editor_screen_sb_admin_full_editor_label' => 'Lista de editores da estrutura:',
    'service_body_editor_screen_sb_admin_full_editor_desc' => 'Esses usuários tem acesso completo a edição das reuniões da estrutura.',
    'service_body_editor_screen_sb_admin_editor_label' => 'Editores básicos da lista:',
    'service_body_editor_screen_sb_admin_editor_desc' => 'Esses usuários podem acessar somente reuniões não publicadas.',
    'service_body_editor_screen_sb_admin_observer_label' => 'Observadores:',
    'service_body_editor_screen_sb_admin_observer_desc' => 'Esses usuários só podem ver os dados, não edita-los.',
    'service_body_dirty_confirm_text' => 'Você alterou os dados dessa estrutura. Saindo perderá todas as mudanças. Tem certeza?',
    'service_body_save_button' => 'Salvar alterações',
    'service_body_create_button' => 'Criar esse corpo de serviço',
    'service_body_delete_button' => 'Apagar esse corpo de serviço',
    'service_body_delete_perm_checkbox' => 'Apagar esse corpo de serviço permanentemente.',
    'service_body_delete_button_confirm' => 'Tem certeza que quer apagar esse corpo de serviço? Tenha certeza de apagar as reuniões ou transferi-las para outro corpo de serviço antes de continuar.',
    'service_body_delete_button_confirm_perm' => 'Este corpo de serviço será apagado permanentemente!',
    'service_body_change_fader_create_success_text' => 'A estrutura de serviço foi criada com sucesso',
    'service_body_change_fader_create_fail_text' => 'Houve falha na criação da estrutura de serviço',
    'service_body_change_fader_delete_success_text' => 'A estrutura de serviço foi apagada',
    'service_body_change_fader_delete_fail_text' => 'Houve falha ao apagar a estrutura de serviço',
    'service_body_change_fader_fail_no_data_text' => 'A alteração da estrutura de serviço falhou, Por não haver dados fornecidos',
    'service_body_change_fader_fail_cant_find_sb_text' => 'Falha na alteração da estrutura de serviço, Estrutura de serviço não encontrada',
    'service_body_change_fader_fail_cant_update_text' => 'Falha na alteração da estrutura de serviço, Estrutura de serviço não atualizada',
    'service_body_change_fader_fail_bad_hierarchy' => 'Falha na alteração da estrutura de serviço',
    'service_body_cancel_button' => 'Restaurar ao original',
    'service_body_editor_type_label' => 'Tipo de Estrutura de Serviço:',
    'service_body_editor_type_c_comdef_service_body__GRP__' => 'Grupo',
    'service_body_editor_type_c_comdef_service_body__COP__' => 'Operador',
    'service_body_editor_type_c_comdef_service_body__ASC__' => 'CSA Comitê de Serviço de Área',
    'service_body_editor_type_c_comdef_service_body__RSC__' => 'CSR Comitê de Serviço da Região',
    'service_body_editor_type_c_comdef_service_body__WSC__' => 'Conferẽncia Mundial de Serviço',
    'service_body_editor_type_c_comdef_service_body__MAS__' => 'Área Metropolitana',
    'service_body_editor_type_c_comdef_service_body__ZFM__' => 'Forum Zonal',
    'service_body_editor_type_c_comdef_service_body__GSU__' => 'Grupo de Unidade de Serviço',
    'service_body_editor_type_c_comdef_service_body__LSU__' => 'Unidade de Serviço Local',
    'service_body_editor_screen_helpline_label' => 'Linha de Ajuda:',
    'service_body_editor_screen_helpline_prompt' => 'Coloque aqui o numero do Linha de Ajuda',
    'service_body_editor_uri_naws_format_text' => 'Busque as reuniões dessa estrutura de serviço em arquivo no formato compatível com o NAWS',
    'edit_Meeting_meeting_id' => 'ID da Reunião:',
    'service_body_editor_create_new_sb_option' => 'Criar uma nova estrutura de serviço',
    'service_body_editor_screen_world_cc_label' => 'Código do Comitê Mundial:',
    'service_body_editor_screen_world_cc_prompt' => 'Coloque aqui o código do Comitê Mundial',
    'user_editor_disclosure' => 'Usuário Administrador',
    'user_editor_create_new_user_option' => 'Criar um novo usuário',
    'user_editor_screen_sb_id_label' => 'ID:',
    'user_editor_account_login_label' => 'Login:',
    'user_editor_login_default_text' => 'nome de usuário',
    'user_editor_account_type_label' => 'O usuário é:',
    'user_editor_user_owner_label' => 'Pertence a: ',
    'user_editor_account_type_1' => 'Administrador do servidor',
    'user_editor_account_type_2' => 'Administrador da estrutura de serviço',
    'user_editor_account_type_3' => 'Editor de Estrutura de Serviço',
    'user_editor_account_type_5' => 'Observador',
    'user_editor_account_type_4' => 'Usuário desativado',
    'user_editor_account_name_label' => 'Nome de usuário:',
    'user_editor_name_default_text' => 'Coloque o usuário aqui',
    'user_editor_account_description_label' => 'Descrição:',
    'user_editor_description_default_text' => 'Coloque aqui a descrição do usuário',
    'user_editor_account_email_label' => 'Email:',
    'user_editor_email_default_text' => 'coloque aqui o e-mail',
    'user_change_fader_success_text' => 'Alteração do usuário realizada com sucesso',
    'user_change_fader_fail_text' => 'Falha na alteração do usuário',
    'user_change_fader_create_success_text' => 'Usuário criado com sucesso!',
    'user_change_fader_create_fail_text' => 'Falha na criação do usuário',
    'user_change_fader_create_fail_already_exists' => 'Você está tentando criar um usuário que já existe.',
    'user_change_fader_delete_success_text' => 'Usuário apagado com sucesso',
    'user_change_fader_delete_fail_text' => 'Falha ao apagar usuário',
    'user_save_button' => 'Salvar alterações para esse usuário',
    'user_create_button' => 'Criar novo usuário',
    'user_cancel_button' => 'Restaurar ao Original',
    'user_delete_button' => 'Apagar esse usuário',
    'user_delete_perm_checkbox' => 'Apagar permanentemente o usuário',
    'user_password_label' => 'Mudar Senha para:',
    'user_new_password_label' => 'Senha alterada:',
    'user_password_default_text' => 'Deixe em branco, a não ser que queira mudar a senha',
    'user_new_password_default_text' => 'Você deve configurar uma senha para o novo usuário',
    'user_dirty_confirm_text' => 'Você fez alterações para esse uusuário. Tem certeza que quer perder as alteraçẽos feitas?',
    'user_delete_button_confirm' => 'Tem certeza que vai apagar o usuário?',
    'user_delete_button_confirm_perm' => 'Esse usuário vai ser apagado permanentemente!',
    'user_create_password_alert_text' => 'Novos usuários devem ter uma senha. Você não digitou uma senha ainda.',
    'user_change_fader_fail_no_data_text' => 'Falha na alteração de usuário, Não há dados fornecidos',
    'user_change_fader_fail_cant_find_sb_text' => 'Falha na alteração do usuário, Usuario não existe',
    'user_change_fader_fail_cant_update_text' => 'Falha na alteração do usuário, Usuário não atualizado',
    'format_editor_disclosure' => 'Administração do Formato de Reunião',
    'format_change_fader_change_success_text' => 'Formato de Reunião atualizado',
    'format_change_fader_change_fail_text' => 'Falha na atualização de formato',
    'format_change_fader_create_success_text' => 'Formato de Reunião criado com sucesso',
    'format_change_fader_create_fail_text' => 'Falha na criação de Formato de Reunião',
    'format_change_fader_delete_success_text' => 'Formato apagado com sucesso',
    'format_change_fader_delete_fail_text' => 'Falha ao apagar formato',
    'format_change_fader_fail_no_data_text' => 'Falha na alteração de formato, falta de dados fornecidos',
    'format_change_fader_fail_cant_find_sb_text' => 'Falha na alteração de formato, formato não encontrado',
    'format_change_fader_fail_cant_update_text' => 'Falha na alteração de formato, formato não atualizado',
    'format_editor_name_default_text' => 'Coloque uma breve descrição',
    'format_editor_description_default_text' => 'Coloque aqui uma descrição mais detalhada',
    'format_editor_create_format_button_text' => 'Criar novo formato',
    'format_editor_cancel_create_format_button_text' => 'Cancelar',
    'format_editor_create_this_format_button_text' => 'Criar formato',
    'format_editor_change_format_button_text' => 'Alterar formato',
    'format_editor_delete_format_button_text' => 'Apagar formato',
    'format_editor_reset_format_button_text' => 'Restaurar ao Original',
    'need_refresh_message_fader_text' => 'Você deve atualizar essa pagina antes de fazer novas alterações (F5)',
    'need_refresh_message_alert_text' => 'Pelas alterações feitas na Administração do Servidor, Administração de Estrutura de Serviço, Administração de Usuário, ou Administração de Formato de Reunião, as informações demonstradas nessa sessão não são mais precisas, então essa pagina precisa ser ATUALIZADA. Para isso basta teclar a tecla de função F5 ou sair e logar novamente.',
    'format_editor_delete_button_confirm' => 'Tem certeza que deseja apagar esse Formato de Reunião?',
    'format_editor_delete_button_confirm_perm' => 'Esse formato será apagado permanentemente!',
    'min_password_length_string' => 'Senha muito curta! Ela tem que ter no mínimo %d caracteres!',
    'AJAX_Auth_Failure' => 'Falha de autorização para essa ação. Falha de configuração do servidor.',
    'Maps_API_Key_Warning' => 'Há um problema com a chave da API do Google Maps.',
    'Maps_API_Key_Not_Set' => 'A chave da API do Google Maps não foi configurada.',
    'Observer_Link_Text' => 'Meeting Browser',
    'Data_Transfer_Link_Text' => 'Importar Dados de Reuniões (AVISO: Isso vai sobreescrever os dados atuais!)',
    'MapsURL' => 'https://maps.google.com/maps?q=##LAT##,##LONG##+(##NAME##)&amp;ll=##LAT##,##LONG##',
    'hidden_value' => 'Não pode mostrar dados -não autorizado',
    'Value_Prompts' => array(
        'id_bigint' => 'Meeting ID',
        'worldid_mixed' => 'World Services ID',
        'service_body' => 'Estrutura de Serviço',
        'service_bodies' => 'Service Bodies',
        'weekdays' => 'Dia da Semana',
        'weekday' => 'Reunião acontece a cada',
        'start_time' => 'Horário de ínicio',
        'duration_time' => 'Duração da Reunião',
        'location' => 'Localidade',
        'duration_time_hour' => 'Hora',
        'duration_time_hours' => 'Horas',
        'duration_time_minute' => 'Minuto',
        'duration_time_minutes' => 'Minutos',
        'lang_enum' => 'Língua',
        'formats' => 'Formato',
        'distance' => 'Distancia do  Centro',
        'generic' => 'Reunião de NA',
        'close_title' => 'Fechar detalhes da Reunião',
        'close_text' => 'Fechar janela',
        'map_alt' => 'Mapa de reuniões',
        'map' => 'Clique aqui para abrir o mapa',
        'title_checkbox_unpub_meeting' => 'Essa Reunião não foi publicada. Não pode ser pesquisada.',
        'title_checkbox_copy_meeting' => 'Esta reunião é uma duplicata e também não foi publicada. Não pode ser encontrada numa pesquisa.'
    ),
    'world_format_codes_prompt' => 'Formato NAWS:',
    'world_format_codes' => array(
        '' => 'Nenhum',
        'ABERTA' => 'Aberta',
        'FECHADA' => 'Fechada',
        'CAD' => 'Acessivel à Cadeirante',
        'REC' => 'Recém-Chegados',
        'TB' => 'Texto Básico',
        'VEL' => 'Luz de Velas',
        'CON' => '12 Conceitos',
        'CBV' => 'Crianças bem vindas',
        'PART' => 'Partilha',
        'LGBTQ+' => 'LGBTQ+',
        'IP' => 'Estudo de IPs',
        'IR' => 'Estudo Isto Resulta',
        'SPH' => 'Estudo do Só Por Hoje',
        'VL' => 'Estudo Vivendo Limpo',
        'LIT' => 'Estudo de Literatura',
        'M' => 'Só Homens',
        'MED' => 'Meditação',
        'NF' => 'Proibido Fumar',
        'PR' => 'Perguntas e Respostas',
        'AR' => 'Acesso Restrito',
        'S-D' => 'Speaker/Discussion', // TODO translate
        'FUM' => 'Permitido Fumar',
        'TEM' => 'Temática',
        'PAS' => 'Passos',
        'EGP' => 'Estudo Guia de Passos',
        'TOP' => 'Tema',
        'TRAD' => 'Tradições',
        'VAR' => 'Formato Variável',
        'M' => 'Só Mulheres',
        'J' => 'Só Jovens',
        'LING' => 'Lingua Estrangeira',
        'GP' => 'Guia de Principios',
        'NC' => 'Não permite crianças',
        'FF' => 'Fechado em feriados',
        'VM' => 'Virtual', // TODO translate
        'HYBR' => 'Virtual and In-Person', // TODO translate
        'TC' => 'Facility Temporarily Closed' // TODO translate
    ),
    'format_type_prompt' => 'Format Type:',
    'format_type_codes' => array(
        '' => 'Nenhum',
        'FC1' => 'Formato de Reunião (Estudo de literatura, Temática, etc.)',
        'FC2' => 'Caracteristica do local (Acessivel a Cadeirante, Estacionamento limitado, etc.)',
        'FC3' => 'Necessidades e Restrições (Só Mulheres, LGTBQ+, Crianças não permitidas, etc.)',
        'O' => 'Atenção não membros (Abertas ou Fechadas)',
        'LANG' => 'Lingua',
        'ALERT' => 'Formato ()',
    ),

    'cookie_monster' => 'Este site usa cookies para ajudar na escolha da sua lingua padrão.',
    'main_prompts' => array(
        'id_bigint' => 'ID',
        'worldid_mixed' => 'ID Mundial',
        'shared_group_id_bigint' => 'Unused',
        'service_body_bigint' => 'ID Estrutura de Serviço',
        'weekday_tinyint' => 'Dia da Semana',
        'start_time' => 'Horário',
        'duration_time' => 'Duração',
        'time_zone' => 'Time Zone',
        'formats' => 'Formatos',
        'lang_enum' => 'Lingua',
        'longitude' => 'Longitude',
        'latitude' => 'Latitude',
        'published' => 'Publicada',
        'email_contact' => 'Contato Email',
    ),
    'check_all' => 'Marque Todas',
    'uncheck_all' => 'Desmarque Todas',
    'automatically_calculated_on_save' => 'Calcular automaticamente ao salvar.'
);

$email_contact_strings = array(
    'meeting_contact_form_subject_format' => "[MEETING LIST CONTACT] %s",
    'meeting_contact_message_format' => "%s\n--\nThis message concerns the meeting named \"%s\", which meets at %s, on %s.\nBrowser Link: %s\nEdit Link: %s\nIt was sent directly from the meeting list web server, and the sender is not aware of your email address.\nPlease be aware that replying will expose your email address.\nIf you use \"Reply All\", and there are multiple email recipients, you may expose other people's email addresses.\nPlease respect people's privacy and anonymity; including the original sender of this message."
);

$change_type_strings = array(
    '__THE_MEETING_WAS_CHANGED__' => 'Reunião Alterada.',
    '__THE_MEETING_WAS_CREATED__' => 'Reunião foi criada.',
    '__THE_MEETING_WAS_DELETED__' => 'Reunião foi apagada.',
    '__THE_MEETING_WAS_ROLLED_BACK__' => 'Restaurada a versão anterior da reunião.',

    '__THE_FORMAT_WAS_CHANGED__' => 'O Formato foi alterado.',
    '__THE_FORMAT_WAS_CREATED__' => 'O Formato foi criado.',
    '__THE_FORMAT_WAS_DELETED__' => 'O formato foi apagado.',
    '__THE_FORMAT_WAS_ROLLED_BACK__' => 'Restaurado o formato anterior da reunião.',

    '__THE_SERVICE_BODY_WAS_CHANGED__' => 'A estrutura de serviço foi alterada.',
    '__THE_SERVICE_BODY_WAS_CREATED__' => 'A estrutura de serviço foi criada.',
    '__THE_SERVICE_BODY_WAS_DELETED__' => 'A estrutura de serviço foi apagada.',
    '__THE_SERVICE_BODY_WAS_ROLLED_BACK__' => 'Restaurada a estrutura de serviço anterior.',

    '__THE_USER_WAS_CHANGED__' => 'O usuário foi alterado.',
    '__THE_USER_WAS_CREATED__' => 'O usuário foi criado.',
    '__THE_USER_WAS_DELETED__' => 'O usuário foi apagado.',
    '__THE_USER_WAS_ROLLED_BACK__' => 'Restaurado o usuário para versão anterior.',

    '__BY__' => 'por',
    '__FOR__' => 'para'
);

$detailed_change_strings = array(
    'was_changed_from' => 'mudou de',
    'to' => 'para',
    'was_changed' => 'foi alterado',
    'was_added_as' => 'adicionado como',
    'was_deleted' => 'foi apagado',
    'was_published' => 'A Reunião foi publicada',
    'was_unpublished' => 'A Reunião foi tirada do ar',
    'formats_prompt' => 'O Formato da Reunião',
    'duration_time' => 'Duração da Reunião',
    'start_time' => 'Inicio da Reunião',
    'longitude' => 'Longitude da Reunião',
    'latitude' => 'Latitude da Reunião',
    'sb_prompt' => 'A reunião alterou sua estrutura de serviço de',
    'id_bigint' => 'ID da reunião',
    'lang_enum' => 'Idioma da Reunião',
    'worldid_mixed' => 'The shared Group ID',
    'weekday_tinyint' => 'The day of the week on which the meeting gathers',
    'non_existent_service_body' => 'Estrutura de Serviço não existe mais',
);

defined('_END_CHANGE_REPORT') or define('_END_CHANGE_REPORT', '.');
