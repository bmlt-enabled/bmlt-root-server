<?php
/****************************************************************************************//**
* \file client_interface/xml/GetLangs.php                                                                       *
* \brief Returns an XML response, containing all the Language enumss and names.             *

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
$file_dir = str_replace('/client_interface/xml', '', dirname(__FILE__)).'/server/c_comdef_server.class.php';
require_once($file_dir);
$server = c_comdef_server::MakeServer();
$ret = null;

if ($server instanceof c_comdef_server) {
    $langs = $server->GetServerLangs();
    
    if ($langs) {
        // The caller can request compression. Not all clients can deal with compressed replies.
        if (isset($_GET['compress_xml']) || isset($_POST['compress_xml'])) {
            ob_start('ob_gzhandler');
        } else {
            header('Content-Type:application/xml; charset=UTF-8');
            ob_start();
        }
        echo '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>';

        $port = $_SERVER['SERVER_PORT'] ;
        // IIS puts "off" in the HTTPS field, so we need to test for that.
        $https = (!empty($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] !== 'off') || ($port == 443)));
        $server_path = $_SERVER['SERVER_NAME'];
        $my_path = dirname(dirname($_SERVER['SCRIPT_NAME'])).'/xsd/GetLangs.php';
        $server_path .= trim((($https && ($port != 443)) || (!$https && ($port != 80))) ? ':'.$port : '', '/');
        $xsd_uri = 'http'.($https ? 's' : '').'://'.$server_path.$my_path;
        
        echo "<languages xmlns=\"http://".$_SERVER['SERVER_NAME']."\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://".$_SERVER['SERVER_NAME']." $xsd_uri\">";

        foreach ($langs as $key_string => $name_string) {
            echo '<language key="'.htmlspecialchars($key_string).'"';
            if (!strcmp($key_string, $server->GetLocalLang())) {
                echo ' default="1"';
            }
            echo '>';
            echo htmlspecialchars($name_string);
            echo '</language>';
        }
        
        echo "</languages>";
        ob_end_flush();
    } else {
        echo ( 'No Languages' );
    }
} else {
    echo ( 'No Server' );
}
