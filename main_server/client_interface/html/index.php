<?php
define('ROOTPATH', __DIR__ . '/../..');
defined('BMLT_EXEC') or define('BMLT_EXEC', 1);?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
        $url = dirname(dirname(dirname($_SERVER['PHP_SELF']))) !== "/" ? dirname(dirname(dirname($_SERVER['PHP_SELF']))) : '';
        $shortcut_icon = "$url/local_server/server_admin/style/images/shortcut.png";
        $stylesheet = "$url/local_server/server_admin/style/styles.css?v=" . time();

        ?>
        <link rel="stylesheet" href="<?php echo $stylesheet ?>" />
        <link rel="icon" href="<?php echo $shortcut_icon ?>" />
        <link rel="stylesheet" type="text/css" href="node_modules/@bmlt-enabled/croutonjs/crouton.min.css" />
        <script type="text/javascript" src="node_modules/@bmlt-enabled/croutonjs/crouton.min.js"></script>
        <script type="text/javascript">
            // Full list of parameters: https://github.com/bmlt-enabled/crouton/blob/master/croutonjs/src/js/crouton-core.js#L13
            var crouton = new Crouton({
                custom_query: "&formats%5B%5D=-57&formats%5B%5D=-58&formats_comparison_operator=OR",
                recurse_service_bodies: true,
                root_server: "https://ctna.org/main_server",
                service_body: ["1"],
                template_path: "node_modules/@bmlt-enabled/croutonjs/templates",
                theme: "florida-nights",
                has_languages: "1",
                time_format: "H:mm (h:mma) z",
                // language: ""
            });
            crouton.render();
        </script>
    </head>
    <body>
        <div id="bmlt-tabs"></div>
    </body>
</html>
