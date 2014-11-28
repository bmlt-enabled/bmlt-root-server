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
define ( 'BMLT_EXEC', 1 );
require_once ( dirname ( __FILE__ ).'/../../server/config/get-config.php' );

// We only do this if the capability has been enabled in the auto-config file.
if ( isset ( $g_enable_semantic_admin ) && ($g_enable_semantic_admin == TRUE) )
    {
    require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
    require_once ( dirname ( __FILE__ ).'/../../server/shared/Array2XML.php');
    require_once ( dirname ( __FILE__ ).'/../../client_interface/csv/search_results_csv.php' );

    /***************************************************************************************************************
    ************************************************* MAIN CONTEXT *************************************************
    ***************************************************************************************************************/

    global  $http_vars;
    $http_vars = array_merge ( $_GET, $_POST );
    $url_path = 'http'.((isset ( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS']) ? 's' : '').'://';
    $url_path .= $_SERVER['SERVER_NAME'];
    $url_path .= ((isset ( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] && ($_SERVER['SERVER_PORT'] != 443)) || ($_SERVER['SERVER_PORT'] != 80)) ? $_SERVER['SERVER_PORT'] : '';
    $url_path .= '/'.dirname ( $_SERVER['PHP_SELF'] );
    $url_path .= '/xml.php';
    $lang_enum = '';

    // We use a cookie to store the language pref.
    if ( isset ( $_COOKIE ) && isset ( $_COOKIE['bmlt_admin_lang_pref'] ) && $_COOKIE['bmlt_admin_lang_pref'] )
        {
        $lang_enum = $_COOKIE['bmlt_admin_lang_pref'];
        }

    if ( isset ( $http_vars['lang_enum'] ) && $http_vars['lang_enum'] )
        {
        $lang_enum = $http_vars['lang_enum'];
        }

    $http_vars['lang_enum'] = $lang_enum;       // Quick and dirty way to ensure that this gets properly propagated.

    $expires = time() + (60 * 60 * 24 * 365);   // Expire in one year.
    setcookie ( 'bmlt_admin_lang_pref', $lang_enum, $expires, '/' );
    
    require_once ( dirname ( __FILE__ ).'/../../server/shared/classes/comdef_utilityclasses.inc.php');
    require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');
    require_once ( dirname ( __FILE__ ).'/../db_connect.php');
    
    DB_Connect_and_Upgrade ( );

    $server = c_comdef_server::MakeServer();
    
    if ( $server instanceof c_comdef_server )
        {
        ob_start();
        
        if ( !isset ( $_SESSION ) )
            {
            session_start();
            }
        
        // See if we are logging in
        if (    (isset ( $_GET['admin_action'] ) && ($_GET['admin_action'] == 'logout') && ($_GET['admin_action'] == 'login'))  // We allow GET login.
            ||  (isset ( $_POST['admin_action'] ) && ($_POST['admin_action'] == 'logout') || ($_POST['admin_action'] == 'login'))
            )
            {
            // Belt and suspenders -nuke the stored login.
            $_SESSION[$admin_session_name] = null;
            unset ( $_SESSION[$admin_session_name] );

            if ( isset ( $_POST['admin_action'] ) && ($_POST['admin_action'] == 'login') )
                {
                $login = isset ( $_POST['c_comdef_admin_login'] ) ? $_POST['c_comdef_admin_login'] : (isset ( $_GET['c_comdef_admin_login'] ) ? $_GET['c_comdef_admin_login'] : null);
                $pw = isset ( $_POST['c_comdef_admin_password'] ) ? $_POST['c_comdef_admin_password'] : (isset ( $_GET['c_comdef_admin_password'] ) ? $_GET['c_comdef_admin_password'] : null);
                
                if ( $login && $pw )
                    {
                    // If this is a valid login, we'll get an encrypted password back.
                    $enc_password = $t_server->GetEncryptedPW ( $login, trim ( $pw ) );

                    if ( null != $enc_password )
                        {
                        $_SESSION[$admin_session_name] = "$login\t$enc_password";
                        }
                    else
                        {
                        // Otherwise, we just check to make sure this is a kosher user.
                        $user_obj = $t_server->GetCurrentUserObj();
                        if ( !($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED) )
                            {
                            echo ( '<h1>NOT AUTHORIZED</h1>' );
                            }
                        }
                    }
                else
                    {
                    echo ( '<h1>NOT AUTHORIZED</h1>' );
                    }
                }
            elseif ( (isset ( $_POST['admin_action'] ) && ($_POST['admin_action'] == 'logout')) || (isset ( $_GET['admin_action'] ) && ($_GET['admin_action'] == 'logout')) )
                {
                c_comdef_LogoutUser();
                }

            // Make sure these get wiped and deleted.
            $_POST['admin_action'] = null;
            $_POST['c_comdef_admin_login'] = null;
            $_POST['c_comdef_admin_password'] = null;
            $_GET['admin_action'] = null;
            $_GET['c_comdef_admin_login'] = null;
            $_GET['c_comdef_admin_password'] = null;
            
            unset ( $_POST['admin_action'] );
            unset ( $_POST['c_comdef_admin_login'] );
            unset ( $_POST['c_comdef_admin_password'] );
            unset ( $_GET['admin_action'] );
            unset ( $_GET['c_comdef_admin_login'] );
            unset ( $_GET['c_comdef_admin_password'] );
            }

        // If we are logged in, then we get to play in the admin playground...
        if ( isset ( $_SESSION[$admin_session_name] ) )
            {
            require_once ( dirname ( __FILE__ ).'/c_comdef_admin_xml_handler.class.php' );
            
            $handler = new c_comdef_admin_xml_handler ( $http_vars );
            
            if ( $handler )
                {
                }
            }
        else
            {
            echo ( '<h1>NOT AUTHORIZED</h1>' );
            }

        ob_end_flush();
        }
    else
        {
        die ( '<h1>NO SERVER!</h1>' );
        }
    }
?>