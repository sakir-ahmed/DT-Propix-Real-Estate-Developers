<?php

declare(strict_types=1);

use App\Support\Settings;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return app(Settings::class)->get($key, $default);
    }
}

if (! function_exists('normalize_bd_mobile')) {
    function normalize_bd_mobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?? '';

        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '88'.$digits;
        }

        if (str_starts_with($digits, '1') && strlen($digits) === 10) {
            return '880'.$digits;
        }

        return $digits;
    }
}

if (! function_exists('format_bd_mobile')) {
    /**
     * Formats a stored mobile (8801XXXXXXXXX) for display as +880 1XXX-XXXXXX.
     */
    function format_bd_mobile(string $mobile): string
    {
        $digits = normalize_bd_mobile($mobile);

        if (preg_match('/^880(1\d{3})(\d{6})$/', $digits, $matches) === 1) {
            return '+880 '.$matches[1].'-'.$matches[2];
        }

        return $mobile;
    }
}
