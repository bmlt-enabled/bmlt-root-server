<?php
/**
        This file is part of the Basic Meeting List Toolbox (BMLT).

        Find out more at: http://bmlt.magshare.org

        BMLT is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as
        published by the Free Software Foundation, either version 3
        of the License, or (at your option) any later version.

        BMLT is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
        See the GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this code.  If not, see <http://www.gnu.org/licenses/>.

        Version: 1.2.0
*/

// Comment out for release version.
define('DEBUG', 1);
require_once(dirname(__FILE__).'/bmlt_semantic.class.php');
$uri = '';

// If we are inside the Root Server, we simply fetch the local Root Server automatically.
if (file_exists(dirname(dirname(__FILE__)).'/server/shared/classes/comdef_utilityclasses.inc.php') && !isset($_GET['ajaxCall'])) {
    $port = intval($_SERVER['SERVER_PORT']);
    
    // IIS puts "off" in the HTTPS field, so we need to test for that.
    $https = (!empty($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] !== 'off') || ($port == 443)));
    $url_path = $_SERVER['SERVER_NAME'];
    $file_path = str_replace('\\', '/', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
    $my_path = str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF'])));
    $subsequent_path = str_replace($file_path, '', $my_path);
    $url_path .= trim((($https && ($port != 443)) || (!$https && ($port != 80))) ? ':'.$port : '', '/');
    $url_path .= '/'.trim($subsequent_path, '/');
    $uri = 'http'.($https ? 's' : '').'://'.$url_path;
    $api_key = get_api_key($uri);
    $_GET = array ( 'root_server' => $uri, 'direct_workshop' => 1, 'google_api_key' => $api_key,  );
    
    if ($https) {
        $_GET['https'] = true;
    }
    
    if (($https && ($port != 443)) || (!$https && ($port != 80))) {
        $_GET['tcp_port'] = $port;
    }
}

$bmlt_semantic_instance = new bmlt_semantic($_GET);
    
/**************************************************************/
/** \brief  Query the server for its version.
            This requires that the _bmltRootServerURI data member be valid.

    \returns an integer that will be MMMmmmfff (M = Major Version, m = Minor Version, f = Fix Version).
*/
/**************************************************************/
function get_api_key($bmltRootServerURI)
{
    $ret = "";
    
    if ($bmltRootServerURI) {
        $error = null;
    
        $uri = $bmltRootServerURI.'/client_interface/xml/index.php?switcher=GetServerInfo';
        $xml = bmlt_semantic::call_curl($uri, $error);
        if (!$error && $xml) {
            $info_file = new DOMDocument;
            if ($info_file instanceof DOMDocument) {
                if (@$info_file->loadXML($xml)) {
                    $api_key = $info_file->getElementsByTagName("google_api_key");
                    $ret = $api_key->item(0)->nodeValue;
                }
            }
        }
    }
    
    return $ret;
}

ob_start();
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>BMLT Semantic Wizard</title>
    </head>
    <body>
        <?php echo $bmlt_semantic_instance->get_wizard_page_html(); ?>
        
    </body>
</html><?php ob_end_flush(); ?>