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
defined( 'BMLT_EXEC' ) or die ( 'Cannot Execute Directly' );	// Makes sure that this file is in the correct context.
require_once ( dirname ( __FILE__ ).'/../../server/c_comdef_server.class.php');

/***********************************************************************************************************//**
    \class c_comdef_admin_main_console
    \brief Controls display of the main BMLT administration console.
***************************************************************************************************************/
class c_comdef_admin_ajax_handler
{
    var $my_localized_strings;          ///< This will contain the localized strings and whatnot for display.
    var $my_server;                     ///< This hold the server object.
    var $my_user;                       ///< This holds the instance of the logged-in user.
    var $my_ajax_uri;                   ///< This will be the URI for AJAX calls.
    var $my_http_vars;                  ///< Contains the HTTP vars sent in.
    
    /*******************************************************************************************************//**
    \brief
    ***********************************************************************************************************/
    function __construct (  $in_http_vars   ///< The HTTP transaction parameters
                        )
    {
        $this->my_http_vars = $in_http_vars;
        $this->my_localized_strings = c_comdef_server::GetLocalStrings();
        $this->my_server = c_comdef_server::MakeServer();
        $this->my_user = $this->my_server->GetCurrentUserObj();
        $this->my_ajax_uri = $_SERVER['PHP_SELF'].'?bmlt_ajax_callback=1';
        
        // We check this every chance that we get.
        if ( !$this->my_user || ($this->my_user->GetUserLevel() == _USER_LEVEL_DISABLED) )
            {
            die ( '<h2>NOT AUTHORIZED</h2>' );
            }
    }
    
    /*******************************************************************************************************//**
    \brief
    \returns
    ***********************************************************************************************************/
    function parse_ajax_call()
    {
        $returned_text = '';
        
        $account_changed = false;
        
        if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_password_value'] ) )
            {
            $this->my_user->SetNewPassword ( $this->my_http_vars['account_password_value'] );
            $success = $this->my_user->UpdateToDB ( false, null, true );
            $account_changed = true;
            if ( $ret )
                {
                $ret .= ',';
                }
            $ret .= '{\'PASSWORD_CHANGED\':'.($success ? 'true' : 'false').'}';
            }
        
        if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_email_value'] ) )
            {
            $this->my_user->SetEmailAddress ( $this->my_http_vars['account_email_value'] );
            $success = $this->my_user->UpdateToDB ( );
            $account_changed = true;
            if ( $ret )
                {
                $ret .= ',';
                }
            $ret .= '{\'EMAIL_CHANGED\':'.($success ? 'true' : 'false').'}';
            }
        
        if ( (intval ( $this->my_user->GetID() ) == intval ( $this->my_http_vars['target_user'] )) && isset ( $this->my_http_vars['account_description_value'] ) )
            {
            $this->my_user->SetLocalDescription ( $this->my_http_vars['account_description_value'] );
            $account_changed = true;
            $success = $this->my_user->UpdateToDB ( );
            if ( $ret )
                {
                $ret .= ',';
                }
            $ret .= '{\'DESCRIPTION_CHANGED\':'.($success ? 'true' : 'false').'}';
            }
        
        if ( $account_changed )
            {
            $returned_text .= '{\'ACCOUNT_CHANGED\':'.$ret.'}';
            }
        
        return  $returned_text;
    }
};

$handler = new c_comdef_admin_ajax_handler($http_vars);

$ret = 'ERROR';

if ( $handler instanceof c_comdef_admin_ajax_handler )
    {
    $ret = $handler->parse_ajax_call();
    }

echo $ret;
?>