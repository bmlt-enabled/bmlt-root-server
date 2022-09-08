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

        self::$config = $config;
        self::$configLoaded = true;
    }
}
