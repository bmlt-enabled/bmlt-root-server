<?php

use App\LegacyConfig;

if (!function_exists('legacy_config')) {
    function legacy_config($key = null, $default = null)
    {
        return LegacyConfig::get($key, $default);
    }
}
