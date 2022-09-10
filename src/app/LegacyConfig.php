<?php

namespace App;

class LegacyConfig
{
    private static ?array $config = null;
    private static bool $configLoaded = false;

    public static function get(string $key = null, $default = null)
    {
        if (!self::$configLoaded) {
            self::loadConfig();
        }

        if (is_null(self::$config)) {
            return null;
        }

        if (is_null($key)) {
            return self::$config;
        }

        return self::$config[$key] ?? $default;
    }

    public static function set(string $key, $value)
    {
        // really should only be used in testing
        if (!self::$configLoaded) {
            self::loadConfig();
        }

        self::$config[$key] = $value;
    }

    public static function remove(string $key)
    {
        // really should only be used in testing
        if (!self::$configLoaded) {
            self::loadConfig();
        }

        unset(self::$config[$key]);
    }

    public static function reset()
    {
        // really should only be used in testing
        self::$config = null;
        self::$configLoaded = false;
    }

    private static function loadConfig()
    {
        $legacyConfigFile = base_path() . '/../auto-config.inc.php';
        if (file_exists($legacyConfigFile)) {
            defined('BMLT_EXEC') or define('BMLT_EXEC', 1);
            require($legacyConfigFile);
        }

        $config = [];

        if (isset($gkey)) {
            $config['google_api_key'] = $gkey;
        } elseif (isset($gKey)) {
            $config['google_api_key'] = $gKey;
        }

        if (isset($dbName)) {
            $config['db_database'] = $dbName;
        }

        if (isset($dbUser)) {
            $config['db_username'] = $dbUser;
        }

        if (isset($dbPassword)) {
            $config['db_password'] = $dbPassword;
        }

        if (isset($dbServer)) {
            $config['db_host'] = $dbServer;
        }

        if (isset($dbPrefix)) {
            $config['db_prefix'] = $dbPrefix;
        }

        if (isset($comdef_global_language)) {
            $config['language'] = $comdef_global_language;
        }

        $config['default_duration_time'] = $default_duration_time ?? '01:00:00';
        $config['region_bias'] = $region_bias ?? 'us';
        $config['distance_units'] = $comdef_distance_units ?? 'mi';
        $config['enable_semantic_admin'] = isset($g_enable_semantic_admin) && $g_enable_semantic_admin;
        $config['enable_email_contact'] = isset($g_enable_email_contact) && $g_enable_email_contact;
        $config['include_service_body_admin_on_emails'] = isset($include_service_body_admin_on_emails) && $include_service_body_admin_on_emails;
        $config['change_depth_for_meetings'] = $change_depth_for_meetings ?? 0;
        $config['meeting_states_and_provinces'] = $meeting_states_and_provinces ?? [];
        $config['meeting_counties_and_sub_provinces'] = $meeting_counties_and_sub_provinces ?? [];
        $config['meeting_time_zones_enabled'] = isset($meeting_time_zones_enabled) && $meeting_time_zones_enabled;


        self::$config = $config;
        self::$configLoaded = true;
    }
}
