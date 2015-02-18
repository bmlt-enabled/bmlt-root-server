<?php 
// Comment out for release version.
define ( 'DEBUG', 1 );
require_once ( dirname ( __FILE__ ).'/bmlt_semantic.class.php' );
$bmlt_semantic_instance = new bmlt_semantic ( 'http://bmlt.newyorkna.org/main_server', array_merge_recursive ( $_GET, $_POST ) );
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