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
// This contains the PDO database access stuff.
require_once ( dirname ( __FILE__ ).'/../../server/classes/c_comdef_dbsingleton.class.php' );
?>
<div id="installer_wrapper" class="page_1_wrapper">
    <div id="bmlt_installer_tab_bar" class="bmlt_installer_tab_bar">
        <div id="bmlt_installer_tab_1" class="bmlt_installer_tab_1">
            <a href="javascript:g_installer_object.selectPage1()"><?php echo $comdef_install_wizard_strings['Database_Setup']; ?></a>
        </div>
        <div id="bmlt_installer_tab_2" class="bmlt_installer_tab_2">
            <a href="javascript:g_installer_object.selectPage2()"><?php echo $comdef_install_wizard_strings['Map_Setup']; ?></a>
        </div>
        <div id="bmlt_installer_tab_3" class="bmlt_installer_tab_3">
            <a href="javascript:g_installer_object.selectPage3()"><?php echo $comdef_install_wizard_strings['User_Setup']; ?></a>
        </div>
    </div>
    <div class="page_content">
        <div id="bmlt_installer_page_1" class="bmlt_installer_page_1">
            <div class="one_line_div">
                <div class="left_right_aligned_div bold_char"><?php echo $comdef_install_wizard_strings['Database_Name']; ?></div>
                <div class="right_left_aligned_div">
                    <input type="text" id="installer_db_name_input" value="<?php echo $prefs_array['dbName']; ?>" class="bmlt_text_item_small" />
                </div>
            </div>
            <div class="clear_both"></div>
        </div>
        <div id="bmlt_installer_page_2" class="bmlt_installer_page_2">
        </div>
        <div id="bmlt_installer_page_3" class="bmlt_installer_page_3">
        </div>
    </div>
    <script type="text/javascript">
        <?php
        ?>
        var g_installer_object = new BMLTInstaller;
        g_installer_object.handleTextInputLoad(document.getElementById('installer_db_name_input'),'<?php echo $comdef_install_wizard_strings['Database_Name_Default_Text']; ?>','small');
    </script>
</div>
