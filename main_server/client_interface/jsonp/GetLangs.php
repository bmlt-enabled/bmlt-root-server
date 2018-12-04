<?php
/****************************************************************************************//**
* \file client_interface/jsonp/GetLangs.php                                                                     *
* \brief Returns a JSON response, containing all the Language enumss and names.             *

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
********************************************************************************************/

defined('BMLT_EXEC') or define('BMLT_EXEC', true); // This is a security verifier. Keeps files from being executed outside of the context
$file_dir = str_replace('/client_interface/jsonp', '', dirname(__FILE__)).'/server/c_comdef_server.class.php';
require_once($file_dir);
$server = c_comdef_server::MakeServer();
$ret = null;

if ($server instanceof c_comdef_server) {
    $langs = $server->GetServerLangs();
    
    if ($langs) {
        // The caller can request compression. Not all clients can deal with compressed replies.
        if (isset($_GET['compress_json']) || isset($_POST['compress_json'])) {
            ob_start('ob_gzhandler');
        } else {
            header('Content-Type:application/json; charset=UTF-8');
            ob_start();
        }

        echo $_GET['callback'] . '({"languages":[';

        $first = true;
        
        foreach ($langs as $key_string => $name_string) {
            if (!$first) {
                echo ",";
            } else {
                $first = false;
            }
            
            echo '{"key":'.json_encode($key_string);
            echo ',"name":'.json_encode($name_string);
            if (!strcmp($key_string, $server->GetLocalLang())) {
                echo ',"default":true';
            }
            echo '}';
        }
        
        echo "]});";
        ob_end_flush();
    } else {
        echo ( 'No Languages' );
    }
} else {
    echo ( 'No Server' );
}
