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
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.
define('_LANG_COOKIE_NAME', 'bmlt_admin_lang_pref');

global  $http_vars;
$http_vars = array_merge($_GET, $_POST);
$lang_enum = '';

// We use a cookie to store the language pref.
if (isset($_COOKIE) && isset($_COOKIE[_LANG_COOKIE_NAME]) && $_COOKIE[_LANG_COOKIE_NAME]) {
    $lang_enum = $_COOKIE[_LANG_COOKIE_NAME];
}

if (isset($http_vars['lang_enum']) && $http_vars['lang_enum']) {
    $lang_enum = $http_vars['lang_enum'];
}

$http_vars['lang_enum'] = $lang_enum;       // Quick and dirty way to ensure that this gets properly propagated.

$file_loc = dirname(__FILE__).'/install_wizard/index.php';

require_once($file_loc);  // We test for the install wizard, first.

if (isset($g_enable_language_selector) && $g_enable_language_selector) {
    $expires = time() + (60 * 60 * 24 * 365);   // Expire in one year.
    setcookie(_LANG_COOKIE_NAME, $lang_enum, $expires, '/');
}

if (isset($http_vars ['bmlt_data_transfer'])) {  // This checks for the transfer wizard.
    require_once(dirname(__FILE__).'/server_admin/c_comdef_data_importer.class.php');
} elseif (isset($http_vars ['bmlt_ajax_callback'])) {
    require_once(dirname(__FILE__).'/server_admin/c_comdef_admin_ajax_handler.class.php');
} else {
    ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Script-Type" content="text/javascript" />
        <meta http-equiv="Content-Style-Type" content="text/css" />
    <?php
            require_once(dirname(dirname(__FILE__)).'/server/shared/classes/comdef_utilityclasses.inc.php');
            $config_file_path = dirname(dirname(__FILE__)).'/server/config/get-config.php';
            
    if (file_exists($config_file_path)) {
        include($config_file_path);
    }

            $url_path = GetURLToMainServerDirectory();
            $shortcut_icon = $url_path."local_server/server_admin/style/images/shortcut.png";
            $stylesheet = $url_path."local_server/server_admin/style".( defined('__DEBUG_MODE__') ? '/' : '/style_stripper.php?filename=' )."styles.css";
            
            require_once(dirname(dirname(__FILE__)).'/server/c_comdef_server.class.php');
            require_once(dirname(__FILE__).'/db_connect.php');

            DB_Connect_and_Upgrade();

            $server = c_comdef_server::MakeServer();
    ?>
        <link rel="stylesheet" href="<?php echo c_comdef_htmlspecialchars($stylesheet) ?>" />
        <link rel="icon" href="<?php echo c_comdef_htmlspecialchars($shortcut_icon) ?>" />
        <title>Basic Meeting List Toolbox Administration Console</title>
    </head>
    <body class="admin_body">
    <?php
    if ($server instanceof c_comdef_server) {
        // This throws up a tackle if someone wants to just barge in.
        require_once(dirname(__FILE__).'/server_admin/c_comdef_login.php');
            
        // We can only go past here is we are a logged-in user.
        $user_obj = $server->GetCurrentUserObj();
        if (($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED)) {
            echo '<div class="admin_page_wrapper">';
            // OK. If they make it in here, it means they are legit, so display the logged-in console.
            require_once(dirname(__FILE__).'/server_admin/main_console.php');
            echo '</div>';
        }
    } else {
        ?>
        <h1>ERROR: NO SERVER!</h1>
        <?php
    }
    ?>
    </body>
</html>
    <?php
}
?>