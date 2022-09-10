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
defined('BMLT_EXEC') or define('BMLT_EXEC', 1);
require_once(dirname(dirname(dirname(__FILE__))).'/server/config/get-config.php');

// We only do this if the capability has been enabled in the auto-config file.
if (isset($g_enable_semantic_admin) && ($g_enable_semantic_admin == true)) {
    require_once(dirname(dirname(dirname(__FILE__))).'/server/c_comdef_server.class.php');

    /***************************************************************************************************************
    ************************************************* MAIN CONTEXT *************************************************
    ***************************************************************************************************************/

    $http_vars = request()->input();

    // Create an HTTP path to our XML file. We build it manually, in case this file is being used elsewhere, or we have a redirect in the domain.
    // We allow it to be used as HTTPS.
    $url_path = GetURLToMainServerDirectory().'local_server/server_admin/json.php';
    $lang_enum = '';
    $login_call = false;    // We only allow login with the login call. That's to prevent users constantly sending cleartext login info.

    // We use a cookie to store the language pref.
    $lang_enum = request()->cookie('bmlt_admin_lang_pref', $lang_enum);

    if (isset($http_vars['lang_enum']) && $http_vars['lang_enum']) {
        $lang_enum = $http_vars['lang_enum'];
    }

    $http_vars['lang_enum'] = $lang_enum;       // Quick and dirty way to ensure that this gets properly propagated.

    if ($lang_enum) {
        cookie()->queue('bmlt_admin_lang_pref', $lang_enum, 60 * 24 * 365);
    }

    require_once(dirname(dirname(dirname(__FILE__))).'/server/shared/classes/comdef_utilityclasses.inc.php');
    require_once(dirname(dirname(dirname(__FILE__))).'/server/shared/Array2Json.php');
    require_once(dirname(dirname(__FILE__)).'/db_connect.php');

    DB_Connect_and_Upgrade();

    $server = c_comdef_server::MakeServer();

    if ($server instanceof c_comdef_server) {
        $user_obj = $server->GetCurrentUserObj();
        if (!($user_obj instanceof c_comdef_user) || ($user_obj->GetUserLevel() == _USER_LEVEL_DISABLED) || ($user_obj->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) || ($user_obj->GetID() == 1)) {
            c_comdef_LogoutUser();
            die('NOT AUTHORIZED');
        }

        if (isset($http_vars['admin_action']) && $http_vars['admin_action']) {   // Must have an admin_action.
            require_once(dirname(__FILE__).'/c_comdef_admin_xml_handler.class.php');

            $handler = new c_comdef_admin_xml_handler($http_vars, $server);

            if ($handler instanceof c_comdef_admin_xml_handler) {
                $ret = $handler->process_commands();
                $ret = simplexml_load_string($ret);
                $json = json_encode((Array)$ret, JSON_NUMERIC_CHECK);

                $pattern = '/\{\"\@attributes\"\:\{(.*?)\}\}/';    // Replace attribute objects with direct objects, to remove the extra layer.
                $replacement = '{\1}';
                do {
                    $old_json = $json;
                    $json = preg_replace($pattern, $replacement, $json);
                } while ($json && ($old_json != $json));

                $pattern = '/\"\@attributes\"\:\{(\"sequence_index\"\:(\d+?))\}\,/';
                $replacement = '';
                do {
                    $old_json = $json;
                    $json = preg_replace($pattern, $replacement, $json);
                } while ($json && ($old_json != $json));

                $pattern = '/\"row\"\:\{\"sequence_index\"\:(\d*?)\}\,/';    // Replace sequence index object, to remove the extra layer.
                do {
                    $old_json = $json;
                    $json = preg_replace($pattern, "", $json);
                } while ($json && ($old_json != $json));

                if (isset($json) && $json) {
                    header('Content-Type:application/json; charset=UTF-8');
                    if (zlib_get_coding_type() === false) {
                        ob_start("ob_gzhandler");
                    } else {
                        ob_start();
                    }
                    echo ( $json );
                    ob_end_flush();
                } else {
                    $ret = 'ERROR';
                }

                // Just making sure...
                unset($handler);
                unset($server);
                unset($http_vars);
            } else {
                $ret = 'ERROR';
            }
        } else {
            die('BAD ADMIN ACTION');
        }
    } else {
        die('NO SERVER!');
    }
}
