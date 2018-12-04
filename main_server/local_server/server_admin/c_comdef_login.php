<?php
/***********************************************************************/
/** \file   c_comdef_login.php

    \brief  This presents a login dialog to the user, vets the login,
    and establishes a session.

    We track admin logins through the use of PHP sessions. This allows the
    login data to remain on the server. It also allows the login data to
    be carried into AJAX calls.

    We're pretty anal about checking user credentials. We don't allow any
    changes to happen to the DB unless the user has been vetted at the
    time the DB access is made, but we do a lot of checking along the way.

    If you include this file at the top of any file that does admin, it will
    check the session. If the session is not there, it will replace the
    output with a login form, and will continue along the way, once the user
    has logged in. If the session is set, it simply makes sure that the
    session reflects a user that has a system login (it does not check the
    user level), and stays out of the way.

    If the user authentication fails, then it does a PHP die(), and scrags
    the whole thing. This prevents execution of any code beyond the bare
    minimum necessary to authenticate.

    Cookies and JavaScript (and AJAX) are required to administer the server
    (but not to use it as a regular site visitor). This form checks to see
    if JavaScript is enabled, and if cookies are enabled.

    You should link to the c_comdef_login.css file for this form.

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
    along with this code.  If not, see <http://www.gnu.org/licenses/>.
*/
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

require_once(dirname(dirname(dirname(__FILE__)))."/server/c_comdef_server.class.php");
require_once(dirname(dirname(dirname(__FILE__)))."/server/shared/classes/comdef_utilityclasses.inc.php");

include(dirname(dirname(dirname(__FILE__))).'/server/config/get-config.php');

$t_server = c_comdef_server::MakeServer();  // We initialize the server.

$lang_enum = $t_server->GetServer()->GetLocalLang();

// We use a cookie to store the language pref.
if (isset($_COOKIE) && isset($_COOKIE['bmlt_admin_lang_pref']) && $_COOKIE['bmlt_admin_lang_pref']) {
    $bmlt_localization = $_COOKIE['bmlt_admin_lang_pref'];
}

if (isset($http_vars['lang_enum']) && $http_vars['lang_enum']) {
    $lang_enum = $http_vars['lang_enum'];
}

if (isset($g_enable_language_selector) && $g_enable_language_selector) {
    $expires = time() + (60 * 60 * 24 * 365);   // Expire in one year.
    setcookie('bmlt_admin_lang_pref', $lang_enum, $expires, '/');
}

if (!isset($_SESSION)) {
    session_start();
}

// See if we are logging in or out
if ((isset($_GET['admin_action']) && ($_GET['admin_action'] == 'logout'))    // No GET login.
    ||  (isset($_POST['admin_action']) && ($_POST['admin_action'] == 'logout'))
    ||  (isset($_POST['admin_action']) && ($_POST['admin_action'] == 'login'))   // Only POST login.
    ) {
    // Belt and suspenders -nuke the stored login.
    $_SESSION[$admin_session_name] = null;
    unset($_SESSION[$admin_session_name]);
    session_write_close();  // Close and reopen the session.
    session_start();

    if (isset($_POST['admin_action']) && ($_POST['admin_action'] == 'login')) {
        $login = isset($_POST['c_comdef_admin_login']) ? $_POST['c_comdef_admin_login'] : null;

        // If this is a valid login, we'll get an encrypted password back.
        $enc_password = isset($_POST['c_comdef_admin_password']) ? $t_server->GetEncryptedPW($login, trim($_POST['c_comdef_admin_password'])) : null;
        if (null != $enc_password) {
            $_SESSION[$admin_session_name] = "$login\t$enc_password";
            // If the login interrupted going somewhere else, we complete the journey.
            if (isset($_POST['attemptedurl']) && $_POST['attemptedurl'] && (0 >= strpos('logout', $_POST['attemptedurl']))) {
                ob_end_clean();
                header('Location: '.$_POST['attemptedurl']);
            }
        } else {
            // Otherwise, we just check to make sure this is a kosher user.
            $user_obj = $t_server->GetCurrentUserObj();
            if (!($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED)) {
                // Get the display strings.
                $localized_strings = c_comdef_server::GetLocalStrings();
    
                c_comdef_LogoutUser();

                // If the login is invalid, we terminate the whole kit and kaboodle, and inform the user they are persona non grata.
                die('<h2 class="c_comdef_not_auth_3">'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['not_auth_3']).'</h2>'.c_comdef_LoginForm($server).'</body></html>');
            }
        }
    } elseif ((isset($_POST['admin_action']) && ($_POST['admin_action'] == 'logout')) || (isset($_GET['admin_action']) && ($_GET['admin_action'] == 'logout'))) {
        c_comdef_LogoutUser();
    }

    // Make sure these get wiped and deleted.
    $_POST['admin_action'] = null;
    $_POST['c_comdef_admin_login'] = null;
    $_POST['c_comdef_admin_password'] = null;
    
    // Shouldn't have GET, but what the hell...
    $_GET['admin_action'] = null;
    $_GET['c_comdef_admin_login'] = null;
    $_GET['c_comdef_admin_password'] = null;
    
    // Belt and suspenders -we set them to naught, then unset them.
    unset($_POST['admin_action']);
    unset($_POST['c_comdef_admin_login']);
    unset($_POST['c_comdef_admin_password']);
    unset($_GET['admin_action']);
    unset($_GET['c_comdef_admin_login']);
    unset($_GET['c_comdef_admin_password']);
}

// See if a session has been started, or a login was attempted.
if (isset($_SESSION[$admin_session_name])) {
    // Get the display strings.
    $localized_strings = c_comdef_server::GetLocalStrings();

    // We double-check, and see if the user is valid.
    $user_obj = $t_server->GetCurrentUserObj();
    if (!($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED)) {
        // If the login is invalid, we terminate the whole kit and kaboodle, and inform the user they are persona non grata.
        die('<div class="c_comdef_not_auth_container_div"><div class="c_comdef_not_auth_div"><h1 class="c_comdef_not_auth_1">'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['not_auth_1']).'</h1><h2 class="c_comdef_not_auth_2">'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['not_auth_2']).'</h2></div></div></body></html>');
    }

    if (!isset($supress_header) || !$supress_header) {
        echo '<div class="bmlt_admin_logout_bar"><h4><a href="'.$_SERVER['PHP_SELF'].'?admin_action=logout">'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['logout']).'</a></h4>';
            $server_info = GetServerInfo();
            echo '<div class="server_version_display_div">'.htmlspecialchars($server_info['version']).'</div>';
        echo '</div>';
        echo '<div id="google_maps_api_error_div" class="bmlt_admin_google_api_key_error_bar item_hidden"><h4><a id="google_maps_api_error_a" href="https://bmlt.app/google-maps-api-keys-and-geolocation-issues/" target="_blank"></a></h4></div>';
    }
} else {
    echo c_comdef_LoginForm($t_server);
}

$t_server = null;

/***********************************************************************/
/** \brief Copied verbatim from here: http://stackoverflow.com/questions/6768793/get-the-full-url-in-php
\returns a string, with the full URI.
*/
function url_origin($s, $use_forwarded_host = false)
{
    $ssl = ( !empty($s['HTTPS']) && $s['HTTPS'] == 'on' ) ? true:false;
    $sp = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . ( ( $ssl ) ? 's' : '' );
    $port = $s['SERVER_PORT'];
    $port = ( (!$ssl && $port=='80') || ($ssl && $port=='443') ) ? '' : ':'.$port;
    $host = ( $use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST']) ) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
    $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
    return $protocol . '://' . $host;
}

/***********************************************************************/
/** \brief Copied verbatim from here: http://stackoverflow.com/questions/6768793/get-the-full-url-in-php
\returns a string, with the full URI.
*/
function full_url($s, $use_forwarded_host = false)
{
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}

/********************************************************************************************************//**
\brief This function parses the main server version from the XML file.
\returns a string, containing the version info and banner.
************************************************************************************************************/
function GetServerInfo()
{
    $ret = null;

    if (file_exists(dirname(dirname(dirname(__FILE__))).'/client_interface/serverInfo.xml')) {
        $info_file = new DOMDocument;
        if ($info_file instanceof DOMDocument) {
            if (@$info_file->load(dirname(dirname(dirname(__FILE__))).'/client_interface/serverInfo.xml')) {
                $has_info = $info_file->getElementsByTagName("bmltInfo");
        
                if (($has_info instanceof domnodelist) && $has_info->length) {
                    $ret['version'] = $has_info->item(0)->nodeValue;
                }
            }
        }
    }

    $config_file_path = dirname(dirname(dirname(__FILE__))).'/server/config/get-config.php';

    if (file_exists($config_file_path)) {
        include($config_file_path);
        $localized_strings = c_comdef_server::GetLocalStrings();
        if (isset($bmlt_title) && trim($bmlt_title)) {
            $ret['title'] = trim($bmlt_title);
        } else {
            $ret['title'] = $localized_strings['comdef_server_admin_strings']['login_banner'];
        }
        if (isset($banner_text) && trim($banner_text)) {
            $ret['banner_text'] = trim($banner_text);
        } else {
            $ret['banner_text'] = $localized_strings['comdef_server_admin_strings']['login_underbanner'];
        }
    }

    return $ret;
}
    
/*******************************************************************/
/** \brief  Returns HTML for the login form. If the user is not logged
    in, then they get the form. Otherwise, the login is processed, or
    the user is vetted.

    \returns a string, containing the form HTML.
*/
function c_comdef_LoginForm(    &$in_server ///< A reference to an instance of c_comdef_server
                                )
{
    include(dirname(dirname(dirname(__FILE__))).'/server/config/get-config.php');

    $http_vars = array_merge($_GET, $_POST);

    $localized_strings = c_comdef_server::GetLocalStrings();
    $server_info = GetServerInfo();

    global  $comdef_global_language;

    if (isset($http_vars) && is_array($http_vars) && count($http_vars) && isset($http_vars['lang_enum'])) {
        $lang_name = $http_vars['lang_enum'];
    
        if (file_exists(dirname(__FILE__)."/lang/".$lang_name."/name.txt")) {
            $comdef_global_language = $lang_name;
        }
    } elseif (isset($_SESSION) && is_array($_SESSION) && isset($_SESSION['lang_enum'])) {
        $lang_name = $_SESSION['lang_enum'];
    
        if (file_exists(dirname(__FILE__)."/lang/".$lang_name."/name.txt")) {
            $comdef_global_language = $lang_name;
        }
    }

    if (isset($_SESSION) && is_array($_SESSION)) {
        $_SESSION['lang_enum'] = $comdef_global_language;
    }

    $ret = '<div class="c_comdef_admin_login_form_container_div">';
        // If there is no JavaScript, then this message is displayed, and the form will not be revealed.
        $ret .= '<noscript><h1>'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['noscript']).'</h1></noscript>';
        $ret .= '<h1 class="login_form_main_banner_h1">'.c_comdef_htmlspecialchars($server_info['title']).'</h1>';
        $ret .= '<h2 class="login_form_secondary_banner_h2">'.c_comdef_htmlspecialchars($server_info['banner_text']).'</h2>';
        $ret .= '<form method="post" class="c_comdef_admin_login_form" id="c_comdef_admin_login_form" action="'.c_comdef_htmlspecialchars($_SERVER['SCRIPT_NAME']);
        $ret_temp = '';
    foreach ($http_vars as $key => $value) {
        switch ($key) {
            // Skip these.
            case 'c_comdef_admin_login':
            case 'c_comdef_admin_password':
            case 'admin_action':
            case 'login':
                break;
                
            default:
                // Arrays need to be concatenated strings.
                if (is_array($value)) {
                    $value = join(",", $value);
                }
                if ($ret_temp) {
                    $ret_temp .= '&amp;';
                } else {
                    $ret_temp = '?';
                }
                $ret_temp .= c_comdef_htmlspecialchars($key).'='.c_comdef_htmlspecialchars($value);
                break;
        }
    }
        $ret .= '">';   // Only the login will go through post.
            $ret .= '<input id="admin_action" type="hidden" name="admin_action" value="login" />';
            $attempted_url = full_url($_SERVER);
            
    if (!preg_match('|logout|', $attempted_url)) {
        $ret .= '<input id="attemptedurl" type="hidden" name="attemptedurl" value="'.c_comdef_htmlspecialchars($attempted_url).'" />';
    }
            
            $ret .= '<div style="display:none" id="c_comdef_admin_login_form_inner_container_div" class="c_comdef_admin_login_form_inner_container_div">';
                $ret .= '<div class="c_comdef_admin_login_form_line_div">';
                $ret .= '<div class="c_comdef_admin_login_form_prompt">'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['title']).'</div>';
                    $ret .= '<label for="c_comdef_admin_login">'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['login']).$localized_strings['prompt_delimiter'].'</label>';
                    $ret .= '<input id="c_comdef_admin_login" type="text" name="c_comdef_admin_login" value="" />';
                $ret .= '</div>';
                $ret .= '<div class="c_comdef_admin_login_form_line_div">';
                    $ret .= '<label for="c_comdef_admin_password">'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['password']).$localized_strings['prompt_delimiter'].'</label>';
                    $ret .= '<input type="password" id="c_comdef_admin_password" name="c_comdef_admin_password" value="" />';
                $ret .= '</div>';
    if (isset($g_enable_language_selector) && $g_enable_language_selector) {
        $ret .= '<div id="lang_enum_select_div" class="c_comdef_admin_login_form_line_div">';
            $ret .= '<select id="lang_enum_select" name="lang_enum">'.(defined('__DEBUG_MODE__') ? "\n" : '');
            $lang_array = c_comdef_server::GetServer()->GetServerLangs();
        foreach ($lang_array as $id => $name) {
            if ($id && $name) {
                $ret .= '<option value="'.c_comdef_htmlspecialchars($id).'"';
                if ($comdef_global_language == $id) {
                        $ret .= ' selected="selected"';
                }
                $ret.= '>'.c_comdef_htmlspecialchars($name).'</option>'.(defined('__DEBUG_MODE__') ? "\n" : '');
            }
        }
            $ret .= '</select>'.(defined('__DEBUG_MODE__') ? "\n" : '');
            $ret .= '<div id="cookie_notice_div" class="bmlt_cookie_notice_div">'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['cookie_monster']).'</div>';
                    $ret .= '</div>';
    }
                $ret .= '<div class="c_comdef_admin_login_form_submit_div">';
                    $ret .= '<input type="submit" value="'.c_comdef_htmlspecialchars($localized_strings['comdef_server_admin_strings']['button']).'" />';
                $ret .= '</div>';
            $ret .= '</div>';
        // This is how we check for JavaScript availability and enabled cookies (Cookies are required for sessions).
        // We reveal the form using JavaScript (It stays invisible if no JS), and we set a transitory cookie with JS to be read upon login.
        $ret .= '</form>
        <script type="text/javascript">
            document.getElementById(\'c_comdef_admin_login_form_inner_container_div\').style.display=\'block\';
            document.getElementById(\'c_comdef_admin_login\').focus();
            document.cookie=\'comdef_test=test\';
        </script>';
    $ret .= '</div>';

    return $ret;
}
