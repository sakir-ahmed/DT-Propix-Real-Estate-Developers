<?php

declare(strict_types=1);

namespace App\Support\Website;

/**
 * Resolves bilingual arrays into the current locale with a fallback.
 *
 * Two shapes are supported:
 *  - {"en": "text", "bn": "text"} becomes "text"
 *  - {"label_en": "...", "label_bn": "...", "icon": "bed"} becomes {"label": "...", "icon": "bed"}
 */
class Localizer
{
    public static function fallbackLocale(): string
    {
        $fallback = config('app.fallback_locale', 'en');

        return is_string($fallback) ? $fallback : 'en';
    }

    /**
     * @param  array<string, mixed>  $value
     */
    public static function pick(array $value, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $text = $value[$locale] ?? null;

        if (is_string($text) && $text !== '') {
            return $text;
        }

        $fallback = $value[self::fallbackLocale()] ?? null;

        return is_string($fallback) && $fallback !== '' ? $fallback : null;
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|string
     */
    public static function item(array $item, ?string $locale = null): array|string
    {
        $locale ??= app()->getLocale();

        if (array_key_exists('en', $item) || array_key_exists($locale, $item)) {
            return self::pick($item, $locale) ?? '';
        }

        $result = [];

        foreach ($item as $key => $value) {
            if (str_ends_with($key, '_en') || str_ends_with($key, '_bn')) {
                $base = substr($key, 0, -3);

                if (! array_key_exists($base, $result)) {
                    $result[$base] = $item[$base.'_'.$locale] ?? $item[$base.'_'.self::fallbackLocale()] ?? null;
                }

                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param  array<int, mixed>  $items
     * @return list<array<string, mixed>|string>
     */
    public static function list(array $items, ?string $locale = null): array
    {
        $result = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                $result[] = self::item($item, $locale);
            } elseif (is_string($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
