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
define('BMLT_EXEC', 1);
require_once(dirname(dirname(dirname(__FILE__))).'/server/config/get-config.php');

// We only do this if the capability has been enabled in the auto-config file.
if (isset($g_enable_semantic_admin) && ($g_enable_semantic_admin == true)) {
    require_once(dirname(dirname(dirname(__FILE__))).'/server/c_comdef_server.class.php');

    /***************************************************************************************************************
    ************************************************* MAIN CONTEXT *************************************************
    ***************************************************************************************************************/

    $http_vars = array_merge($_GET, $_POST);
    
    // Create an HTTP path to our XML file. We build it manually, in case this file is being used elsewhere, or we have a redirect in the domain.
    // We allow it to be used as HTTPS.
    $url_path = GetURLToMainServerDirectory().'local_server/server_admin/xml.php';
    $lang_enum = '';
    $login_call = false;    // We only allow login with the login call. That's to prevent users constantly sending cleartext login info.

    // We use a cookie to store the language pref.
    if (isset($_COOKIE) && isset($_COOKIE['bmlt_admin_lang_pref']) && $_COOKIE['bmlt_admin_lang_pref']) {
        $lang_enum = $_COOKIE['bmlt_admin_lang_pref'];
    }

    if (isset($http_vars['lang_enum']) && $http_vars['lang_enum']) {
        $lang_enum = $http_vars['lang_enum'];
    }

    $http_vars['lang_enum'] = $lang_enum;       // Quick and dirty way to ensure that this gets properly propagated.

    $expires = time() + (60 * 60 * 24 * 365);   // Expire in one year.
    setcookie('bmlt_admin_lang_pref', $lang_enum, $expires, '/');
    
    require_once(dirname(dirname(dirname(__FILE__))).'/server/shared/classes/comdef_utilityclasses.inc.php');
    require_once(dirname(dirname(__FILE__)).'/db_connect.php');
    
    DB_Connect_and_Upgrade();

    $server = c_comdef_server::MakeServer();
    
    if ($server instanceof c_comdef_server) {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        // See if we are logging in
        if (isset($http_vars['admin_action']) && (($http_vars['admin_action'] == 'logout') || ($http_vars['admin_action'] == 'login'))) {
            $login_call = true;
            // Belt and suspenders -nuke the stored login.
            $_SESSION[$admin_session_name] = null;
            unset($_SESSION[$admin_session_name]);

            if (isset($http_vars['admin_action']) && ($http_vars['admin_action'] == 'login')) {
                $login = $http_vars['c_comdef_admin_login'];
                $pw = $http_vars['c_comdef_admin_password'];
                
                if ($login && $pw) {
                    // If this is a valid login, we'll get an encrypted password back.
                    $enc_password = $server->GetEncryptedPW($login, trim($pw));

                    if (null != $enc_password) {    // If we got a password, we set up the session.
                        $_SESSION[$admin_session_name] = "$login\t$enc_password";
                        
                        // Check to make sure this is a kosher user.
                        $user_obj = $server->GetCurrentUserObj();
                        if (!($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED) || ($user_obj->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) || ($user_obj->GetID() == 1)) {
                            // We do not allow semantic access to Server Admin functions (because security)
                            unset($user_obj);    // Goodbye, Mr. Bond...
                            c_comdef_LogoutUser();
                            die('<h1>NOT AUTHORIZED</h1>');
                        }
                            
                        // If we are OK, we'll fall through.
                    } else // These seem redundant, but a basic security posture of mine is to immediatly kill execution upon discovering a security breach.
                        {
                        c_comdef_LogoutUser();
                        die('<h1>NOT AUTHORIZED</h1>');
                    }
                } else {
                    c_comdef_LogoutUser();
                    die('<h1>NOT AUTHORIZED</h1>');
                }
            } else // Logout gets a "bye".
                {
                c_comdef_LogoutUser();
                die('BYE');
            }
        }

        // If we are logged in, and this isn't the login call, then we get to play in the admin playground...
        if (!$login_call && isset($_SESSION[$admin_session_name])) {
            // Belt and suspenders. We just check one more time...
            $user_obj = $server->GetCurrentUserObj();
            if (!($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED) || ($user_obj->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) || ($user_obj->GetID() == 1)) {
                c_comdef_LogoutUser();
                die('<h1>NOT AUTHORIZED</h1>');
            } else // If everything is OK, then we actually include the class, instantiate the object, and process the request.
                {
                if (isset($http_vars['admin_action']) && $http_vars['admin_action']) {   // Must have an admin_action.
                    require_once(dirname(__FILE__).'/c_comdef_admin_xml_handler.class.php');
            
                    $handler = new c_comdef_admin_xml_handler($http_vars, $server);
                
                    if ($handler instanceof c_comdef_admin_xml_handler) {
                        $ret = $handler->process_commands();  // Do what you do so well...
                        
                        if (preg_match('|^<\?xml|', $ret)) {   // Only output an XML header is we are actually returning XML.
                            header('Content-Type:application/xml; charset=UTF-8');
                        }
                        
                        if (zlib_get_coding_type() === false) {
                            ob_start("ob_gzhandler");
                        } else {
                            ob_start();
                        }
                        echo ( $ret );
                        ob_end_flush();
                    } else {
                        $ret = '<h1>ERROR</h1>';
                    }
                
                    // Just making sure...
                    unset($handler);
                    unset($server);
                    unset($http_vars);
                } else {
                    die('<h1>BAD ADMIN ACTION</h1>');
                }
            }
        } elseif ($login_call && isset($_SESSION[$admin_session_name])) {  // Simple login just gets an "OK".
            ob_start();
            echo ( 'OK' );
            ob_end_flush();
        } else {
            c_comdef_LogoutUser();
            die('<h1>NOT AUTHORIZED</h1>');
        }
    } else {
        die('<h1>NO SERVER!</h1>');
    }
}
