<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).

    Find out more at: https://bmlt.app

    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the MIT License.

    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    MIT License for more details.

    You should have received a copy of the MIT License along with this code.
    If not, see <https://opensource.org/licenses/MIT>.
*/
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

$basename = basename(realpath(dirname(__FILE__).'/../../'));
$realpath = realpath(dirname(__FILE__).'/../../../');

global  $default_lang;
$default_lang = $lang;
?>
<div id="installer_wrapper" class="page_1_wrapper">
    <div id="bmlt_installer_tab_bar" class="bmlt_installer_tab_bar">
        <div id="bmlt_installer_tab_1" class="bmlt_installer_tab_1">
            <a href="javascript:g_installer_object.selectPage1()"><?php echo htmlspecialchars($comdef_install_wizard_strings['Page_1_Tab']); ?></a>
        </div>
        <div id="bmlt_installer_tab_2" class="bmlt_installer_tab_2">
            <a href="javascript:g_installer_object.selectPage2()"><?php echo htmlspecialchars($comdef_install_wizard_strings['Page_2_Tab']); ?></a>
        </div>
        <div id="bmlt_installer_tab_3" class="bmlt_installer_tab_3">
            <a href="javascript:g_installer_object.selectPage3()"><?php echo htmlspecialchars($comdef_install_wizard_strings['Page_3_Tab']); ?></a>
        </div>
        <div id="bmlt_installer_tab_4" class="bmlt_installer_tab_4">
            <a href="javascript:g_installer_object.selectPage4()"><?php echo htmlspecialchars($comdef_install_wizard_strings['Page_4_Tab']); ?></a>
        </div>
    </div>
    <form action="" method="post">
        <div class="page_content">
            <?php
            if (version_compare(PHP_VERSION, '5.6.0', '>=')) {
                if (class_exists('PDO')) {
                    $drivers = PDO::getAvailableDrivers();
                        
                    $found = false;
                        
                    foreach ($drivers as $driver) {
                        if ($driver == 'mysql') {
                            $found = true;
                            break;
                        }
                    }
                            
                    if ($found) {
                        ?>
                <script type="text/javascript">
                    var g_maps_api_key_warning = '<?php echo htmlspecialchars($comdef_install_wizard_strings['Maps_API_Key_Warning']); ?>';
                    var g_maps_api_key_not_set = '<?php echo htmlspecialchars($comdef_install_wizard_strings['Maps_API_Key_Not_Set']); ?>';
                    var g_maps_api_key_valid = '<?php echo htmlspecialchars($comdef_install_wizard_strings['Maps_API_Key_Valid']); ?>';
                    var g_maps_api_key_click_here = '<?php echo htmlspecialchars($comdef_install_wizard_strings['Maps_API_Key_ClickHere']); ?>';
                </script>
                <script type="text/javascript" src="local_server/install_wizard/installer.js"></script>
                <script type="text/javascript">
                    var g_installer_object = new BMLTInstaller ( <?php echo array2json($prefs_array) ?> );
                </script>
                <div id="bmlt_installer_page_1" class="bmlt_installer_page_1">
                        <?php echo bmlt_create_next_prev_buttons(1) ?>
                    <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_1_Heading']; ?></h1>
                    <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_1_Text']; ?></h2>
                    <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_1_Initial_Intro']; ?></div>
                    <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_1_DB_Intro']; ?></div>
                    <div class="one_line_div">
                        <label class="left_right_aligned bold_char" for="installer_db_type_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['Database_Type']); ?></label>
                        <div class="right_left_aligned_div">
                        <?php echo bmlt_create_pdo_driver_select(); ?>
                        </div>
                    </div>
                    <div class="one_line_div">
                        <label class="left_right_aligned bold_char" for="installer_db_host_input"><?php echo htmlspecialchars($comdef_install_wizard_strings['Database_Host']); ?></label>
                        <div class="right_left_aligned_div">
                            <input type="text" id="installer_db_host_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars($prefs_array['dbServer']); ?>" class="bmlt_text_item_small" />
                        </div>
                        <div class="extra_text_div"><?php echo htmlspecialchars($comdef_install_wizard_strings['Database_Host_Additional_Text']); ?></div>
                    </div>
                    <div class="one_line_div">
                        <label class="left_right_aligned bold_char" for="installer_db_prefix_input"><?php echo htmlspecialchars($comdef_install_wizard_strings['Table_Prefix']); ?></label>
                        <div class="right_left_aligned_div">
                            <input type="text" id="installer_db_prefix_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars($prefs_array['dbPrefix']); ?>" class="bmlt_text_item_small" />
                        </div>
                        <div class="extra_text_div"><?php echo htmlspecialchars($comdef_install_wizard_strings['Table_Prefix_Additional_Text']); ?></div>
                    </div>
                    <div class="one_line_div">
                        <label class="left_right_aligned bold_char" for="installer_db_name_input"><?php echo $comdef_install_wizard_strings['Database_Name']; ?></label>
                        <div class="right_left_aligned_div">
                            <input type="text" id="installer_db_name_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo $prefs_array['dbName']; ?>" class="bmlt_text_item_small" />
                        </div>
                    </div>
                    <div class="one_line_div">
                        <label class="left_right_aligned bold_char" for="installer_db_user_input"><?php echo htmlspecialchars($comdef_install_wizard_strings['Database_User']); ?></label>
                        <div class="right_left_aligned_div">
                            <input type="text" id="installer_db_user_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars($prefs_array['dbUser']); ?>" class="bmlt_text_item_small" />
                        </div>
                    </div>
                    <div class="one_line_div">
                        <label class="left_right_aligned bold_char" for="installer_db_pw_input"><?php echo htmlspecialchars($comdef_install_wizard_strings['Database_PW']); ?></label>
                        <div class="right_left_aligned_div">
                            <input type="text" id="installer_db_pw_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars($prefs_array['dbPassword']); ?>" class="bmlt_text_item_small" />
                        </div>
                        <div class="extra_text_div"><?php echo htmlspecialchars($comdef_install_wizard_strings['Database_PW_Additional_Text']); ?></div>
                    </div>
                    <div class="one_line_div">
                        <div class="one_line_div centered_text">
                            <a class="bmlt_admin_ajax_button" href="javascript:g_installer_object.buttonTestForDatabaseSetup()"><?php echo htmlspecialchars($comdef_install_wizard_strings['Database_TestButton_Text']); ?></a>
                        </div>
                    </div>
                    <div class="clear_both"></div>
                        <?php echo bmlt_create_next_prev_buttons(1) ?>
                </div>
                <div id="bmlt_installer_page_2" class="bmlt_installer_page_2">
                        <?php echo bmlt_create_next_prev_buttons(2) ?>
                    <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_2_Heading']; ?></h1>
                    <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_2_Text']; ?></h2>
                    <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_2_API_key_Intro']; ?></div>
                    <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_2_API_key_2_Intro']; ?></div>
                    <div class="one_line_div">
                        <label class="left_right_aligned bold_char" for="api_text_entry"><?php echo htmlspecialchars($comdef_install_wizard_strings['Page_2_API_Key_Prompt']); ?></label>
                        <div class="right_left_aligned_div">
                            <input type="text" class="api_text_entry" id="api_text_entry" value="" />
                            <a class="bmlt_admin_ajax_button" href="javascript:g_installer_object.testMapsApiKey();"><?php echo htmlspecialchars($comdef_install_wizard_strings['Page_2_API_Key_Set_Button']); ?></a>
                        </div>
                    </div>
                    <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_2_Region_Bias_Intro']; ?></div>
                    <div class="one_line_div">
                        <label class="left_right_aligned bold_char" for="installer_region_bias_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['RegionBiasLabel']); ?></label>
                        <div class="right_left_aligned_div">
                        <?php echo bmlt_create_region_bias_select(); ?>
                        </div>
                    </div>
                    <div class="clear_both"></div>
                        <?php echo bmlt_create_next_prev_buttons(2) ?>
                </div>
                <div id="bmlt_installer_page_3" class="bmlt_installer_page_3">
                        <?php echo bmlt_create_next_prev_buttons(3) ?>
                    <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_3_Heading']; ?></h1>
                    <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_3_Text']; ?></h2>
                    <fieldset id="admin_login_stuff_fieldset">
                        <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_3_Server_Admin_Intro']; ?></div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_admin_login_input"><?php echo htmlspecialchars($comdef_install_wizard_strings['Admin_Login']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="text" id="installer_admin_login_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo $comdef_install_wizard_strings['ServerAdminDefaultLogin'] ?>" class="bmlt_text_item_med" />
                            </div>
                            <div class="extra_text_div"><?php echo htmlspecialchars($comdef_install_wizard_strings['Admin_Login_Additional_Text']); ?></div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_admin_password_input"><?php echo htmlspecialchars($comdef_install_wizard_strings['Admin_Password']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="text" id="installer_admin_password_input" onkeyup="g_installer_object.gatherInstallerState()" value="" class="bmlt_text_item_med" />
                            </div>
                            <div class="extra_text_div"><?php echo htmlspecialchars($comdef_install_wizard_strings['Admin_Password_Additional_Text']); ?></div>
                        </div>
                        <div class="one_line_div"><div id="admin_pw_warning_div" class="extra_text_div red_char"></div></div>
                        <div class="clear_both"></div>
                    </fieldset>
                    <div class="one_line_div"><div id="admin_pw_warning_div_2" class="item_hidden"><?php echo htmlspecialchars($comdef_install_wizard_strings['NoServerAdmin_Note_AlreadySet']); ?></div></div>
                    <fieldset id="admin_settings_fieldset">
                        <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_3_Misc_Intro']; ?></div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_title_input"><?php echo htmlspecialchars($comdef_install_wizard_strings['TitleTextLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="text" id="installer_title_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars($prefs_array['bmlt_title']); ?>" class="bmlt_text_item" />
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_banner_input"><?php echo htmlspecialchars($comdef_install_wizard_strings['BannerTextLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="text" id="installer_banner_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars($prefs_array['banner_text']); ?>" class="bmlt_text_item" />
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_lang_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['ServerLangLabel']); ?></label>
                            <div class="right_left_aligned_div"><?php echo bmlt_create_lang_select(); ?></div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="format_lang_names"><?php echo htmlspecialchars($comdef_install_wizard_strings['FormatLangNamesLabel']); ?></label>
                            <input class="text" id="format_lang_names" value="">
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_pw_length_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['PasswordLengthLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <select onchange="g_installer_object.gatherInstallerState()" id="installer_pw_length_select">
                                <?php
                                foreach ($comdef_install_wizard_strings['PW_LengthChices'] as $count) {
                                    echo '<option';
                                    if ($count == $prefs_array['min_pw_len']) {
                                        echo ' selected="selected"';
                                    }
                                        echo ">$count</option>";
                                }
                                ?>
                                </select>
                            </div>
                            <div class="extra_text_div"><?php echo htmlspecialchars($comdef_install_wizard_strings['PasswordLengthExtraText']); ?></div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="distance_units_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['DistanceUnitsLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <select onchange="g_installer_object.gatherInstallerState()" id="distance_units_select">
                                    <option value="mi"<?php
                                    if ($comdef_install_wizard_strings['DefaultDistanceUnits'] == 'mi') {
                                        echo ' selected="selected"';
                                    }
                                            echo '>'.htmlspecialchars($comdef_install_wizard_strings['DistanceUnitsMiles']);
                                    ?></option>
                                    <option value="km"<?php
                                    if ($comdef_install_wizard_strings['DefaultDistanceUnits'] == 'km') {
                                        echo ' selected="selected"';
                                    }
                                            
                                            echo '>'.htmlspecialchars($comdef_install_wizard_strings['DistanceUnitsKM']);
                                    ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="search_count_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['SearchDepthLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <select onchange="g_installer_object.gatherInstallerState()" id="search_count_select">
                                    <?php
                                    foreach ($comdef_install_wizard_strings['DistanceChoices'] as $count) {
                                        echo '<option';
                                        if ($count == $prefs_array['number_of_meetings_for_auto']) {
                                            echo ' selected="selected"';
                                        }
                                            echo ">$count</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="extra_text_div"><?php echo htmlspecialchars($comdef_install_wizard_strings['SearchDepthText']); ?></div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_history_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['HistoryDepthLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <select onchange="g_installer_object.gatherInstallerState()" id="installer_history_select">
                                    <?php
                                    foreach ($comdef_install_wizard_strings['HistoryChoices'] as $count) {
                                        echo '<option';
                                        if ($count == $prefs_array['change_depth_for_meetings']) {
                                            echo ' selected="selected"';
                                        }
                                            echo ">$count</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="extra_text_div"><?php echo htmlspecialchars($comdef_install_wizard_strings['HistoryDepthText']); ?></div>
                        </div>
                        <div class="clear_both"></div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_duration_hour_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['DurationLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <select onchange="g_installer_object.gatherInstallerState()" id="installer_duration_hour_select">
                                    <?php
                                    $default_duration = explode(':', $comdef_install_wizard_strings['DefaultDurationTime']);
                                    $default_duration[0] = intval($default_duration[0]);
                                    $default_duration[1] = intval($default_duration[1]);

                                    for ($hours = 0; $hours < 24; $hours++) {
                                        echo '<option value ="'.htmlspecialchars($hours).'"';
                                        if ($default_duration[0] == $hours) {
                                            echo ' selected="selected"';
                                        }
                                            echo '>'.htmlspecialchars($hours).'</option>';
                                    }
                                    ?>
                                </select>
                                <label class="extra_text_label" for="installer_duration_hour_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['DurationHourLabel']); ?></label>
                            </div>
                            <div class="right_left_aligned_div">
                                <select onchange="g_installer_object.gatherInstallerState()" id="installer_duration_minutes_select">
                                    <?php
                                    for ($minutes = 0; $minutes < 60; $minutes++) {
                                        echo '<option value ="'.htmlspecialchars($minutes).'"';
                                        if ($default_duration[1] == $minutes) {
                                            echo ' selected="selected"';
                                        }
                                        echo '>'.htmlspecialchars(sprintf("%02d", $minutes)).'</option>';
                                    }
                                    ?>
                                </select>
                                <label class="extra_text_label" for="installer_duration_minute_select"><?php echo htmlspecialchars($comdef_install_wizard_strings['DurationMinutesLabel']); ?></label>
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_admin_language_selector_checkbox"><?php echo htmlspecialchars($comdef_install_wizard_strings['LanguageSelectorEnableLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="checkbox" id="installer_admin_language_selector_checkbox" value="enable_language_selector" />
                            </div>
                            <div class="extra_text_div">
                                <?php echo htmlspecialchars($comdef_install_wizard_strings['LanguageSelectorEnableExtraText']); ?>
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="semantic_admin_checkbox"><?php echo htmlspecialchars($comdef_install_wizard_strings['SemanticAdminLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="checkbox" id="semantic_admin_checkbox" checked="checked" value="semantic_admin_checkbox_selector" />
                            </div>
                            <div class="extra_text_div">
                                <?php echo htmlspecialchars($comdef_install_wizard_strings['SemanticAdminExtraText']); ?>
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="default_closed_checkbox"><?php echo htmlspecialchars($comdef_install_wizard_strings['DefaultClosedStatus']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="checkbox" id="default_closed_checkbox" checked="checked" value="default_closed_checkbox_selector" />
                            </div>
                            <div class="extra_text_div">
                                <?php echo htmlspecialchars($comdef_install_wizard_strings['DefaultClosedStatusExtraText']); ?>
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_admin_email_contact_checkbox"><?php echo htmlspecialchars($comdef_install_wizard_strings['EmailContactEnableLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="checkbox" id="installer_admin_email_contact_checkbox" value="enable_email_contact_selector" onclick="reactToEmailCheckbox()" />
                            </div>
                            <div class="extra_text_div">
                                <?php echo htmlspecialchars($comdef_install_wizard_strings['EmailContactEnableExtraText']); ?>
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_admin_email_sba_contact_checkbox"><?php echo htmlspecialchars($comdef_install_wizard_strings['EmailContactAdminEnableLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="checkbox" disabled="disabled" id="installer_admin_email_sba_contact_checkbox" value="enable_email_sba_contact_selector" onclick="reactToEmailCheckbox()" />
                            </div>
                            <div class="extra_text_div">
                                <?php echo htmlspecialchars($comdef_install_wizard_strings['EmailContactAdminEnableExtraText']); ?>
                            </div>
                        </div>
                        <div class="one_line_div">
                            <label class="left_right_aligned bold_char" for="installer_admin_email_all_admins_checkbox"><?php echo htmlspecialchars($comdef_install_wizard_strings['EmailContactAllAdminEnableLabel']); ?></label>
                            <div class="right_left_aligned_div">
                                <input type="checkbox" disabled="disabled" id="installer_admin_email_all_admins_checkbox" value="enable_email_all_admins_contact_selector" />
                            </div>
                            <div class="extra_text_div">
                                <?php echo htmlspecialchars($comdef_install_wizard_strings['EmailContactAllAdminEnableExtraText']); ?>
                            </div>
                        </div>
                        <div class="clear_both"></div>
                    </fieldset>
                        <?php echo bmlt_create_next_prev_buttons(3) ?>
                </div>
                <div id="bmlt_installer_page_4" class="bmlt_installer_page_4">
                        <?php echo bmlt_create_next_prev_buttons(4) ?>
                    <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_4_Main_Intro']; ?></div>
                    <div id="database_install_stuff_div" class="item_hidden">
                        <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_4_Initialize_Root_Server_Heading']; ?></h1>
                        <div class="one_line_div centered_text">
                            NAWS Export Spreadsheet (Optional): <input name="bmlt_admin_naws_spreadsheet_file_input" id="bmlt_admin_naws_spreadsheet_file_input" type="file" />
                        </div>
                        <div class="one_line_div centered_text">
                            <a id="bmlt_installer_initialize_ajax_button" class="bmlt_admin_ajax_button" href="javascript:g_installer_object.initializeRootServer()"><?php echo htmlspecialchars($comdef_install_wizard_strings['Page_4_Initialize_Root_Server_Button']) ?></a>
                            <span id="bmlt_installer_initialize_ajax_button_throbber_span" class="item_hidden">
                                <img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" />
                            </span>
                        </div>
                    </div>
                    <div class="one_line_div"><div id="admin_db_items_warning" class="extra_text_div red_char"></div></div>
                    <div class="one_line_div"><div id="admin_google_api_key_warning" class="extra_text_div red_char"></div></div>
                    <div class="one_line_div"><div id="admin_server_admin_user_warning" class="extra_text_div red_char"></div></div>
                    <div id="result_code_div" class="item_hidden">
                        <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_4_Heading']; ?></h1>
                        <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_4_Text']; ?></h2>
                        <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_4_File_Intro']; ?></div>
                        <div class="explanatory_text_div"><?php echo $comdef_install_wizard_strings['Explanatory_Text_4_File_Extra']; ?></div>
                        <pre class="result_code_pre" id="file_text_pre"></pre>
                        <h2 class="page_heading_h2"><?php echo sprintf($comdef_install_wizard_strings['Page_4_PathInfo'], $realpath, $basename); ?></h2>
                        <pre class="result_code_pre"><?php echo "chmod 0644 $realpath/auto-config.inc.php"; ?></pre>
                        <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_4_Final']; ?></h2>
                    </div>
                    <div class="clear_both"></div>
                        <?php echo bmlt_create_next_prev_buttons(4) ?>
                </div><?php
                    } else {
                        $ret .= '<span class="installer_error_display">';
                        $ret .= ( is_array($drivers) && count($drivers) ) ? htmlspecialchars($comdef_install_wizard_strings['Database_Type_MySQL_Error']) : htmlspecialchars($comdef_install_wizard_strings['Database_Type_Error']);
                        $ret .= '</span></dt>';
                    }
                } else {
                    $ret .= '<span class="installer_error_display">'.htmlspecialchars($comdef_install_wizard_strings['Database_PDO_Error']).'</span></dt>';
                }
            } else {
                $ret .= '<span class="installer_error_display">'.htmlspecialchars($comdef_install_wizard_strings['Database_Version_Error']).'</span></dt>';
            }
            ?>
        </div>
    </form>
    <script type="text/javascript">
        g_pw_length_warning_text = '<?php echo htmlspecialchars($comdef_install_wizard_strings['NeedLongerPasswordNote']); ?>';
        g_db_init_no_pw_warning_text = '<?php echo htmlspecialchars($comdef_install_wizard_strings['NoDatabase_Note_PasswordIssue']); ?>';
        g_server_settings_click_here = '<?php echo htmlspecialchars($comdef_install_wizard_strings['NoDatabase_Note_ServerSettings_ClickHere']); ?>';
        g_db_init_db_set_warning_text = '<?php echo htmlspecialchars($comdef_install_wizard_strings['NoDatabase_Note_AlreadySet']); ?>';
        g_db_init_db_generic_db_error_text = '<?php echo htmlspecialchars($comdef_install_wizard_strings['NoDatabase_Note_GenericError']); ?>';
        g_db_init_db_click_here = '<?php echo htmlspecialchars($comdef_install_wizard_strings['NoDatabase_Note_ClickHere']); ?>';
        
        g_installer_object.m_ajax_uri = '<?php echo htmlspecialchars($_SERVER['PHP_SELF'].'?ajax_req='); ?>';

        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_name_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['Database_Name_Default_Text']); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_user_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['Database_User_Default_Text']); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_pw_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['Database_PW_Default_Text']); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_host_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['Database_Host_Default_Text']); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_prefix_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['Table_Prefix_Default_Text']); ?>','small');

        g_installer_object.handleTextInputLoad(document.getElementById('installer_admin_login_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['Admin_Login_Default_Text']); ?>','med');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_admin_password_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['Admin_Password_Default_Text']); ?>','med');

        g_installer_object.handleTextInputLoad(document.getElementById('installer_title_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['TitleTextDefaultText']); ?>');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_banner_input'),'<?php echo htmlspecialchars($comdef_install_wizard_strings['BannerTextDefaultText']); ?>');
    </script>
</div>

<?php
/*******************************************************************/
/** \brief Creates the HTML for the next and prev buttons.

    \returns a string, containing the element HTML.
*/
function bmlt_create_next_prev_buttons( $in_section  ///< The page we are in. An integer.
                                        )
{
    global  $comdef_install_wizard_strings;
    $ret = '<div class="next_prev_container_div">';
    if ($in_section > 1) {
        $ret .= '<div class="prev_button_div">';
            $ret .= '<a class="bmlt_admin_ajax_button" href="javascript:g_installer_object.selectPage'.strval($in_section - 1).'()">'.$comdef_install_wizard_strings['Prev_Button'].'</a>';
        $ret .= '</div>';
    }
    if ($in_section < 4) {
        $ret .= '<div class="next_button_div">';
            $ret .= '<a class="bmlt_admin_ajax_button" href="javascript:g_installer_object.selectPage'.strval($in_section + 1).'()">'.$comdef_install_wizard_strings['Next_Button'].'</a>';
        $ret .= '</div>';
    }
        $ret .= '<div class="clear_both"></div>';
    $ret .= '</div>';
    
    return $ret;
}

/*******************************************************************/
/** \brief Creates the select element for the Server default language.

    \returns a string, containing the select element HTML.
*/
function bmlt_create_lang_select()
{
    $ret = '';
    
    $basedir = dirname(dirname(__FILE__)).'/server_admin/lang/';
    
    $ret .= '<select onchange="g_installer_object.gatherInstallerState()" id="installer_lang_select">';
        $dh = opendir($basedir);
        $server_lang_names = array();
        
    if ($dh) {
        while (false !== ($enum = readdir($dh))) {
            $file_path = "$basedir$enum/name.txt";
            if (file_exists($file_path)) {
                $name = trim(file_get_contents($file_path));
                $server_lang_names[$enum] = $name;
            }
        }
                
        closedir($dh);
    }
        
        uksort($server_lang_names, 'ServerLangSortCallback');
        
    foreach ($server_lang_names as $enum => $name) {
        $ret .= '<option value="'.htmlspecialchars($enum).'">'.htmlspecialchars($name).'</option>';
    }
        
    $ret .= '</select>';
        
    return $ret;
}
    
/*******************************************************************/
/** \brief This is a callback to sort the server languages.
           The default server language will always be first, and
           the rest will be sorted alphabetically.
    \returns an integer. -1 if goes before b, 1 if otherwise, 0 if neither.
*/
function ServerLangSortCallback(
    $in_lang_a,
    $in_lang_b
) {
    global  $default_lang;
    
    $ret = 0;
    
    if ($in_lang_a == $default_lang) {
        $ret = -1;
    } elseif ($in_lang_b == $default_lang) {
        $ret = 1;
    } else {
        $ret = strncasecmp($in_lang_a, $in_lang_b, strlen($in_lang_a));
    }
        
    return $ret;
}

/*******************************************************************/
/** \brief Creates the select element for the Region bias.

    \returns a string, containing the select element HTML.
*/
function bmlt_create_region_bias_select()
{
    global  $prefs_array;
    $ret = '';
    
    $file_path = dirname(__FILE__).'/country_names_and_code_elements.txt';
    $cc_array = explode("\n", file_get_contents($file_path));
    
    $ret .= '<select onchange="g_installer_object.gatherInstallerState()" id="installer_region_bias_select">';
    foreach ($cc_array as $cc) {
        $cc_elem = explode("\t", trim($cc));
            
        if (isset($cc_elem) && is_array($cc_elem) && (count($cc_elem) == 2)) {
            $name = ucwords(strtolower(trim($cc_elem[0])));
            $code = strtolower(trim($cc_elem[1]));
            $ret .= '<option value="'.htmlspecialchars($code).'"';
            if (strtolower($prefs_array['region_bias']) == $code) {
                $ret .= ' selected="selected"';
            }
                $ret .= '>'.htmlspecialchars($name).'</option>';
        }
    }
    $ret .= '</select>';
        
    return $ret;
}

/*******************************************************************/
/** \brief Creates the select element for the PDO driver selector.

    \returns a string, containing the select element HTML.
*/
function bmlt_create_pdo_driver_select()
{
    global  $prefs_array;
    $ret = '';
    
    $ret .= '<select onchange="g_installer_object.gatherInstallerState()" id="installer_db_type_select">';
    $found = false;
    foreach (PDO::getAvailableDrivers() as $driver) {
        if ($driver == 'mysql') {   // Currently, we only support MySQL.
            $ret .= '<option value="'.htmlspecialchars($driver).'"';
//                 if ( $driver == $prefs_array['dbType'] )
//                     {
                    $ret .= ' selected="selected"';
//                     }
            $ret .= '>'.htmlspecialchars($driver).'</option>';
            $found = true;
        }
    }
    if ($found) {
        $ret .= '</select>';
    } else {
        $ret = '<h1>ERROR!</h1>';
    }
        
    return $ret;
}
?>
