<?php
/**
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

        Version: 1.2.0
*/

// Comment out for release version.
define('DEBUG', 1);
define('BMLT_EXEC', 1);
require_once(dirname(__FILE__).'/bmlt_semantic.class.php');
$uri = '';

// If we are inside the Root Server, we simply fetch the local Root Server automatically.
if (file_exists(dirname(dirname(__FILE__)).'/server/shared/classes/comdef_utilityclasses.inc.php') && !isset($_GET['ajaxCall'])) {
    require_once(dirname(dirname(__FILE__)).'/server/config/get-config.php');
    global $g_do_not_force_port;

    $from_proxy = array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER);
    if ($from_proxy) {
        $https = $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
        if (array_key_exists("HTTP_X_FORWARDED_PORT", $_SERVER)) {
            $port = intval($_SERVER['HTTP_X_FORWARDED_PORT']);
        } elseif ($https) {
            $port = 443;
        } else {
            $port = 80;
        }
    } else {
        $port = intval($_SERVER['SERVER_PORT']);
        $https = !empty($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off' || $port == 443) ? true : false;
    }

    $url_path = $_SERVER['SERVER_NAME'];
    $file_path = str_replace('\\', '/', dirname(dirname(dirname(dirname(dirname(__FILE__))))));
    $my_path = str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF'])));
    $subsequent_path = str_replace($file_path, '', $my_path);

    // See if we need to add an explicit port to the URI.
    if (!isset($g_do_not_force_port) || !$g_do_not_force_port) {
        if (!$https && ($port != 80)) {
            $url_path .= ":$port";
        } elseif ($https && ($port != 443)) {
            $url_path .= ":$port";
        }
    }

    $url_path .= '/'.trim($subsequent_path, '/').'/';
    $uri = 'http'.($https ? 's' : '').'://'.$url_path;
    $api_key = get_api_key($uri);
    $_GET = array ( 'root_server' => $uri, 'direct_workshop' => 1, 'google_api_key' => $api_key,  );
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