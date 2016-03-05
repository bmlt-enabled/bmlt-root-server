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
*/

// Comment out for release version.
# define ( 'DEBUG', 1 );
require_once ( dirname ( __FILE__ ).'/bmlt_semantic.class.php' );
$uri = '';

if ( file_exists ( dirname ( dirname ( __FILE__ ) ).'/server/shared/classes/comdef_utilityclasses.inc.php' ) )
    {
    $port = $_SERVER['SERVER_PORT'] ;
    
    // IIS puts "off" in the HTTPS field, so we need to test for that.
    $https = (!empty ( $_SERVER['HTTPS'] ) && (($_SERVER['HTTPS'] !== 'off') || ($port == 443))); 
    $url_path = $_SERVER['SERVER_NAME'];
    $file_path = str_replace ( '\\', '/', dirname ( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) );
    $my_path = str_replace ( '\\', '/', dirname ( dirname ( $_SERVER['PHP_SELF'] ) ) );
    $subsequent_path = str_replace ( $file_path, '', $my_path );
    $url_path .= trim ( (($https && ($port != 443)) || ($port != 80)) ? ':'.$port : '', '/' );
    $url_path .= '/'.trim ( $subsequent_path, '/' );
    $uri = 'http'.($https ? 's' : '').'://'.$url_path;
    $_GET = array ( 'root_server' => $uri );
    }

$bmlt_semantic_instance = new bmlt_semantic ( $_GET );

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