<?php

use App\LegacyConfig;

if (!function_exists('legacy_config')) {
    function legacy_config($key = null, $default = null)
    {
        return LegacyConfig::get($key, $default);
    }
}

if (!function_exists('ensure_array')) {
    function ensure_array($value, callable $callable): array
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return array_map($callable, $value);
    }
}

if (!function_exists('ensure_integer_array')) {
    function ensure_integer_array($value): array
    {
        return ensure_array($value, fn ($id) => intval($id));
    }
}

if (!function_exists('build_time_string')) {
    function build_time_string($hour, $minute): ?string
    {
        if (is_numeric($hour) || is_numeric($minute)) {
            $time = is_numeric($hour) ? str_pad(strval(min(23, max(0, intval($hour)))), 2, '0', STR_PAD_LEFT) : '00';
            $time = $time . ':' . (is_numeric($minute) ? str_pad(strval(min(59, max(0, intval($minute)))), 2, '0', STR_PAD_LEFT) : '00');
            $time = $time . ':00';
            return $time;
        }

        return null;
    }
}
