<?php
define('ROOTPATH', __DIR__ . '/../..');
defined('BMLT_EXEC') or define('BMLT_EXEC', 1);
require_once('../../local_server/server_admin/c_comdef_admin_main_console.class.php');
$console_object = new c_comdef_admin_main_console($_REQUEST);
$user_obj = $console_object->my_server->GetCurrentUserObj();
if ($user_obj instanceof c_comdef_user && $user_obj->GetUserLevel() != _USER_LEVEL_DISABLED) {
    $service_body_ids = [];
    $service_body_set = [];
    if ($user_obj->GetUserLevel() == _USER_LEVEL_OBSERVER) {
        $service_body_set = $console_object->my_observable_service_bodies;
    } else if ($user_obj->GetUserLevel() == _USER_LEVEL_SERVICE_BODY_ADMIN) {
        $service_body_set = $console_object->my_editable_service_bodies;
    } else if ($user_obj->GetUserLevel() == _USER_LEVEL_SERVER_ADMIN) {
        $service_body_set = $console_object->my_all_service_bodies;
    }

    foreach ($service_body_set as $service_body_id) {
        array_push($service_body_ids, intval($service_body_id->GetID()));
    }

    function getBCP47TagForISO631Language($code)
    {
        $default = 'en-US';

        $iso630tobcp47 = [
            'de' => 'de-DE',
            'dk' => 'da-DK',
            'es' => 'es-US',
            'fa' => 'fa-IR',
            'fr' => 'fr-CA',
            'it' => 'it-IT',
            'pl' => 'pl-PL',
            'pt' => 'pt-BR',
            'sv' => 'sv-SE'
        ];

        return array_key_exists($code, $iso630tobcp47) ? $iso630tobcp47[$code] : $default;
    }?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
        $url = dirname(dirname(dirname($_SERVER['PHP_SELF']))) !== "/" ? dirname(dirname(dirname($_SERVER['PHP_SELF']))) : '';
    ?>
        <link rel="icon" href="<?php echo sprintf("%s/local_server/server_admin/style/images/shortcut.png", $url) ?>" />
        <link rel="stylesheet" href="<?php echo sprintf("%s/local_server/server_admin/style/styles.css", $url) ?>" />
        <link rel="stylesheet" type="text/css" href="node_modules/@bmlt-enabled/croutonjs/crouton.min.css" />
        <script type="text/javascript" src="node_modules/@bmlt-enabled/croutonjs/crouton.min.js"></script>
        <script type="text/javascript">
            // Full list of parameters: https://github.com/bmlt-enabled/crouton/blob/master/croutonjs/src/js/crouton-core.js#L13
            var crouton = new Crouton({
                recurse_service_bodies: true,
                root_server: "<?php echo str_replace('client_interface/html/', '', GetURLToMainServerDirectory()) ?>",
                service_body: <?php echo json_encode($service_body_ids) ?>,
                template_path: "node_modules/@bmlt-enabled/croutonjs/templates",
                theme: "florida-nights",
                has_languages: "1",
                time_format: "H:mm (h:mma) z",
                google_api_key: "<?php echo $console_object->my_server::GetLocalStrings()["google_api_key"] ?>",
                show_map: true,
                language: "<?php echo getBCP47TagForISO631Language($_COOKIE["bmlt_admin_lang_pref"]) ?>"
            });
            crouton.render();
        </script>
    </head>
    <body>
        <div id="bmlt-tabs"></div>
    </body>
    <?php
}
?>
</html>
