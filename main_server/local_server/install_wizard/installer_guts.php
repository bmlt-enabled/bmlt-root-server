<?php
/*
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://bmlt.magshare.org

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
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );    // Makes sure that this file is in the correct context.

$basename = basename ( realpath ( dirname ( __FILE__ ).'/../../' ) );
$realpath = realpath ( dirname ( __FILE__ ).'/../../../' );

global  $default_lang;
$default_lang = $lang;
?>
<div id="installer_wrapper" class="page_1_wrapper">
    <div id="bmlt_installer_tab_bar" class="bmlt_installer_tab_bar">
        <div id="bmlt_installer_tab_1" class="bmlt_installer_tab_1">
            <a href="javascript:g_installer_object.selectPage1()"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Page_1_Tab'] ); ?></a>
        </div>
        <div id="bmlt_installer_tab_2" class="bmlt_installer_tab_2">
            <a href="javascript:g_installer_object.selectPage2()"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Page_2_Tab'] ); ?></a>
        </div>
        <div id="bmlt_installer_tab_3" class="bmlt_installer_tab_3">
            <a href="javascript:g_installer_object.selectPage3()"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Page_3_Tab'] ); ?></a>
        </div>
        <div id="bmlt_installer_tab_4" class="bmlt_installer_tab_4">
            <a href="javascript:g_installer_object.selectPage4()"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Page_4_Tab'] ); ?></a>
        </div>
    </div>
    <div class="page_content">
        <?php
            if ( version_compare (PHP_VERSION,'5.1.0','>') )
                {
                if ( class_exists ( 'PDO' ) )
                    {
                    if ( count ( PDO::getAvailableDrivers() ) )
                        {
            ?>                <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
            <script type="text/javascript">
                var g_installer_object = new BMLTInstaller ( <?php echo array2json ( $prefs_array['search_spec_map_center'] ); ?> );
            </script>
            <div id="bmlt_installer_page_1" class="bmlt_installer_page_1">
                <?php echo bmlt_create_next_prev_buttons(1) ?>
                <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_1_Heading']; ?></h1>
                <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_1_Text']; ?></h2>
                <div class="one_line_div">
                    <div class="left_right_aligned_div bold_char"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_Type'] ); ?></div>
                    <div class="right_left_aligned_div">
                        <?php echo bmlt_create_pdo_driver_select(); ?>
                    </div>
                </div>
                <div class="one_line_div">
                    <div class="left_right_aligned_div bold_char"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_Host'] ); ?></div>
                    <div class="right_left_aligned_div">
                        <input type="text" id="installer_db_host_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars ( $prefs_array['dbServer'] ); ?>" class="bmlt_text_item_small" />
                    </div>
                    <div class="extra_text_div"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_Host_Additional_Text'] ); ?></div>
                </div>
                <div class="one_line_div">
                    <div class="left_right_aligned_div bold_char"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Table_Prefix'] ); ?></div>
                    <div class="right_left_aligned_div">
                        <input type="text" id="installer_db_prefix_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars ( $prefs_array['dbPrefix'] ); ?>" class="bmlt_text_item_small" />
                    </div>
                    <div class="extra_text_div"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Table_Prefix_Additional_Text'] ); ?></div>
                </div>
                <div class="one_line_div">
                    <div class="left_right_aligned_div bold_char"><?php echo $comdef_install_wizard_strings['Database_Name']; ?></div>
                    <div class="right_left_aligned_div">
                        <input type="text" id="installer_db_name_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo $prefs_array['dbName']; ?>" class="bmlt_text_item_small" />
                    </div>
                </div>
                <div class="one_line_div">
                    <div class="left_right_aligned_div bold_char"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_User'] ); ?></div>
                    <div class="right_left_aligned_div">
                        <input type="text" id="installer_db_user_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars ( $prefs_array['dbUser'] ); ?>" class="bmlt_text_item_small" />
                    </div>
                </div>
                <div class="one_line_div">
                    <div class="left_right_aligned_div bold_char"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_PW'] ); ?></div>
                    <div class="right_left_aligned_div">
                        <input type="text" id="installer_db_pw_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo htmlspecialchars ( $prefs_array['dbPassword'] ); ?>" class="bmlt_text_item_small" />
                    </div>
                </div>
                <?php echo bmlt_create_next_prev_buttons(1) ?>
            </div>
            <div id="bmlt_installer_page_2" class="bmlt_installer_page_2">
                <?php echo bmlt_create_next_prev_buttons(2) ?>
                <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_2_Heading']; ?></h1>
                <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_2_Text']; ?></h2>
                <div id="installer_map_display_div" class="installer_map_display_div"></div>
                <?php echo bmlt_create_next_prev_buttons(2) ?>
            </div>
            <div id="bmlt_installer_page_3" class="bmlt_installer_page_3">
                <?php echo bmlt_create_next_prev_buttons(3) ?>
                <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_3_Heading']; ?></h1>
                <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_3_Text']; ?></h2>
                <div id="admin_login_stuff_div">
                    <div class="one_line_div">
                        <div class="left_right_aligned_div bold_char"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Admin_Login'] ); ?></div>
                        <div class="right_left_aligned_div">
                            <input type="text" id="installer_admin_login_input" onkeyup="g_installer_object.gatherInstallerState()" value="<?php echo $comdef_install_wizard_strings['serveradmin'] ?>" class="bmlt_text_item_small" />
                        </div>
                        <div class="extra_text_div"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Admin_Login_Additional_Text'] ); ?></div>
                    </div>
                    <div class="one_line_div">
                        <div class="left_right_aligned_div bold_char"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Admin_Password'] ); ?></div>
                        <div class="right_left_aligned_div">
                            <input type="text" id="installer_admin_password_input" onkeyup="g_installer_object.gatherInstallerState()" value="" class="bmlt_text_item_small" />
                        </div>
                        <div class="extra_text_div"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Admin_Password_Additional_Text'] ); ?></div>
                    </div>
                </div>
                <div class="one_line_div">
                    <div class="left_right_aligned_div bold_char"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['ServerLangLabel'] ); ?></div>
                    <div class="right_left_aligned_div">
                        <?php echo bmlt_create_lang_select(); ?>
                    </div>
                </div>
                <?php echo bmlt_create_next_prev_buttons(3) ?>
            </div>
            <div id="bmlt_installer_page_4" class="bmlt_installer_page_4">
                <?php echo bmlt_create_next_prev_buttons(4) ?>
                <div id="database_install_stuff_div" class="item_hidden">
                    <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_4_DB_Setup_Heading']; ?></h1>
                    <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_4_DB_Setup_Text']; ?></h2>
                    <div class="one_line_div centered_text">
                        <a id="bmlt_installer_initialize_ajax_button" class="bmlt_admin_ajax_button" href="javascript:g_installer_object.setUpDatabase()"><?php echo htmlspecialchars ( $comdef_install_wizard_strings['Set_Up_Database'] ) ?></a>
                        <span id="bmlt_installer_initialize_ajax_button_throbber_span" class="item_hidden">
                            <img src="local_server/server_admin/style/images/ajax-throbber-white.gif" alt="AJAX Throbber" />
                        </span>
                    </div>
                </div>
                <h1 class="page_heading_h1"><?php echo $comdef_install_wizard_strings['Page_4_Heading']; ?></h1>
                <h2 class="page_heading_h2"><?php echo $comdef_install_wizard_strings['Page_4_Text']; ?></h2>
                <?php echo bmlt_create_next_prev_buttons(4) ?>
            </div><?php
                    }
                else
                    {
                    $ret .= '<span class="installer_error_display">'.htmlspecialchars ( $comdef_install_wizard_strings['Database_Type_Error'] ).'</span></dt>';
                    }
                }
            else
                {
                $ret .= '<span class="installer_error_display">'.htmlspecialchars ( $comdef_install_wizard_strings['Database_PDO_Error'] ).'</span></dt>';
                }
            }
        else
            {
            $ret .= '<span class="installer_error_display">'.htmlspecialchars ( $comdef_install_wizard_strings['Database_Version_Error'] ).'</span></dt>';
            }
        ?>
    </div>
    <script type="text/javascript">
        g_installer_object.m_top_dir_path = '<?php echo $realpath; ?>';
        g_installer_object.m_main_dir_basename = '<?php echo $basename; ?>';
        g_installer_object.m_ajax_uri = '<?php echo htmlspecialchars ( $_SERVER['PHP_SELF'].'?ajax_req=' ); ?>';

        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_name_input'),'<?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_Name_Default_Text'] ); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_user_input'),'<?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_User_Default_Text'] ); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_pw_input'),'<?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_PW_Default_Text'] ); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_host_input'),'<?php echo htmlspecialchars ( $comdef_install_wizard_strings['Database_Host_Default_Text'] ); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_prefix_input'),'<?php echo htmlspecialchars ( $comdef_install_wizard_strings['Table_Prefix_Default_Text'] ); ?>','small');

        g_installer_object.handleTextInputLoad(document.getElementById('installer_admin_login_input'),'<?php echo htmlspecialchars ( $comdef_install_wizard_strings['Admin_Login_Default_Text'] ); ?>','small');
        g_installer_object.handleTextInputLoad(document.getElementById('installer_admin_password_input'),'<?php echo htmlspecialchars ( $comdef_install_wizard_strings['Admin_Password_Default_Text'] ); ?>','med');
    </script>
</div>

<?php
function bmlt_create_next_prev_buttons( $in_section  ///< The page we are in. An integer.
                                        )
{
    global  $comdef_install_wizard_strings;
    $ret = '<div class="next_prev_container_div">';
        if ( $in_section > 1 )
            {
            $ret .= '<div class="prev_button_div">';
                $ret .= '<a class="bmlt_admin_ajax_button" href="javascript:g_installer_object.selectPage'.strval($in_section - 1).'()">'.$comdef_install_wizard_strings['Prev_Button'].'</a>';
            $ret .= '</div>';
            }
        if ( $in_section < 4 )
            {
            $ret .= '<div class="next_button_div">';
                $ret .= '<a class="bmlt_admin_ajax_button" href="javascript:g_installer_object.selectPage'.strval($in_section + 1).'()">'.$comdef_install_wizard_strings['Next_Button'].'</a>';
            $ret .= '</div>';
            }
        $ret .= '<div class="clear_both"></div>';
    $ret .= '</div>';
    
    return $ret;
}

function bmlt_create_lang_select()
{
    $ret = '';
    
    $basedir = dirname ( __FILE__ ).'/../server_admin/lang/';
    
    $ret .= '<select id="installer_lang_select">';
        $dh = opendir ( $basedir );
        $server_lang_names = array();
        
        if ( $dh )
            {
            while ( false !== ($enum = readdir( $dh )) )
                {
                $file_path = "$basedir$enum/name.txt";
                if ( file_exists ( $file_path ) )
                    {
                    $name = trim ( file_get_contents ( $file_path ) );
                    $ret .= '<option value="'.htmlspecialchars ( $enum ).'">'.htmlspecialchars ( $name ).'</option>';
                    }
                }
                
            closedir ( $dh );
            }
        
        uksort ( $server_lang_names, 'ServerLangSortCallback' );
    $ret .= '</select>';
        
    return $ret;
}
    
/*******************************************************************/
/** \brief This is a callback to sort the server languages.
           The default server language will always be first, and
           the rest will be sorted alphabetically.
    \returns an integer. -1 if goes before b, 1 if otherwise, 0 if neither.
*/
function ServerLangSortCallback( $in_lang_a,
                                 $in_lang_b
                                )
{
    global  $default_lang;
    
    $ret = 0;
    
    if ( $in_lang_a == $default_lang )
        {
        $ret = -1;
        }
    elseif ( $in_lang_b == $default_lang )
        {
        $ret = 1;
        }
    else
        {
        $ret = strncasecmp ( $in_lang_a, $in_lang_b );
        }
        
    return $ret;
}

function bmlt_create_pdo_driver_select()
{
    global  $prefs_array;
    $ret = '';
    
    $ret .= '<select id="installer_db_type_select">';
    foreach ( PDO::getAvailableDrivers() as $driver )
        {
        $ret .= '<option value="'.htmlspecialchars ( $driver ).'"';
            if ( $driver == $prefs_array['dbType'] )
                {
                $ret .= ' selected="selected"';
                }
        $ret .= '>'.htmlspecialchars ( $driver ).'</option>';
        }
    $ret .= '</select>';
        
    return $ret;
}
?>
