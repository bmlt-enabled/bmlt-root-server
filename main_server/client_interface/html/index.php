<?php
define('ROOTPATH', __DIR__ . '/../..');
defined('BMLT_EXEC') or define('BMLT_EXEC', 1);
// define ( '_DEBUG_MODE_', 1 ); //Uncomment for easier JavaScript debugging.
require_once(dirname(__FILE__).'/bmlt-basic/bmlt_basic.class.php') ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"

    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>Browse the Root Server</title>
        <?php
            $url = dirname(dirname(dirname($_SERVER['PHP_SELF'])));
            
        if ($url == '/') {
            $url = '';
        }
            
            $shortcut_icon = "$url/local_server/server_admin/style/images/shortcut.png";
            $stylesheet = "$url/local_server/server_admin/style".( defined('__DEBUG_MODE__') ? '/' : '/style_stripper.php?filename=' )."styles.css";
        
            $basic_bmlt_object->output_head();
        ?>
        <link rel="stylesheet" href="<?php echo c_comdef_htmlspecialchars($stylesheet) ?>" />
        <link rel="icon" href="<?php echo c_comdef_htmlspecialchars($shortcut_icon) ?>" />
    </head>
    <body>
            <?php
            if ($server instanceof c_comdef_server) {
                // This throws up a tackle if someone wants to just barge in.
                require_once(dirname(dirname(dirname(__FILE__))).'/local_server/server_admin/c_comdef_login.php');
        
                // We can only go past here is we are a logged-in user.
                $user_obj = $server->GetCurrentUserObj();
                if (($user_obj instanceof c_comdef_user) && ($user_obj->GetUserLevel() != _USER_LEVEL_DISABLED)) {
                    echo '<div class="admin_page_wrapper">';
                    $basic_bmlt_object->output_body();
                    echo '</div>';
                }
            }
            ?>
    </body>
</html>
