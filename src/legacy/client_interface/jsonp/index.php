<?php
/***********************************************************************/
/**     \file   client_interface/jsonp/index.php

    \brief  This file is a very simple interface that is designed to return
    a JSONP string, in response to a search.
    In order to use this, you need to call: <ROOT SERVER BASE URI>/client_interface/jsonp/
    with the same parameters that you would send to an advanced search. The results
    will be returned as a JSONP file.

    This file can be called from other servers.

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

defined('BMLT_EXEC') or define('BMLT_EXEC', true); // This is a security verifier. Keeps files from being executed outside of the context

require_once(dirname(__FILE__).'/../csv/csv.php');

try {
    $server = c_comdef_server::MakeServer();
    $ret = null;

    if ($server instanceof c_comdef_server) {
        $_GET['json_data'] = true;
        $ret = parse_redirect($server);
        if (isset($_GET['compress_output']) || isset($_POST['compress_output'])) {
            if (zlib_get_coding_type() === false) {
                ob_start("ob_gzhandler");
            } else {
                ob_start();
            }
        } else {
            header('Content-Type:application/javascript; charset=UTF-8');
            ob_start();
        }

        echo $_GET['callback'] . '(';
        echo $ret;
        echo ');';
        ob_end_flush();
    } else {
        echo HandleNoServer();
    }
} catch (Exception $e) {
    echo HandleNoServer();
}
