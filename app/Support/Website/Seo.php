<?php

declare(strict_types=1);

namespace App\Support\Website;

use App\Http\Middleware\SetWebsiteLocale;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

/**
 * Builds the per-page SEO payload rendered server-side in the website root view:
 * title, description, canonical URL, hreflang alternates, OpenGraph and JSON-LD.
 */
class Seo
{
    /**
     * @param  array{
     *     image?: string|null,
     *     type?: string,
     *     robots?: string,
     *     suffix?: bool,
     *     json_ld?: list<array<string, mixed>>,
     *     published_time?: string|null,
     *     modified_time?: string|null
     * }  $options
     * @return array<string, mixed>
     */
    public static function make(string $title, string $description, array $options = []): array
    {
        $siteName = self::siteName();
        $alternates = self::alternates();
        $locale = app()->getLocale();
        $suffix = $options['suffix'] ?? true;

        return [
            'title' => $suffix && $title !== $siteName ? $title.' | '.$siteName : $title,
            'description' => Str::limit(trim(strip_tags($description)), 300, ''),
            'canonical' => $alternates[$locale] ?? url()->current(),
            'alternates' => $alternates,
            'image' => $options['image'] ?? url('/images/brand/logo-512.png'),
            'type' => $options['type'] ?? 'website',
            'robots' => $options['robots'] ?? 'index, follow',
            'site_name' => $siteName,
            'locale' => $locale === 'bn' ? 'bn_BD' : 'en_US',
            'published_time' => $options['published_time'] ?? null,
            'modified_time' => $options['modified_time'] ?? null,
            'json_ld' => $options['json_ld'] ?? [],
        ];
    }

    public static function siteName(): string
    {
        $name = setting('company.name', config('app.name', 'DT Propix'));

        return is_string($name) && $name !== '' ? $name : 'DT Propix';
    }

    /**
     * The current page in every supported language, used for canonical and hreflang.
     *
     * @return array<string, string>
     */
    public static function alternates(): array
    {
        $route = request()->route();

        if (! $route instanceof Route || $route->getName() === null) {
            return [];
        }

        $parameters = $route->originalParameters();
        $query = array_intersect_key(request()->query(), ['page' => true]);
        $alternates = [];

        foreach (SetWebsiteLocale::LOCALES as $locale) {
            $alternates[$locale] = route($route->getName(), array_merge($parameters, $query, ['locale' => $locale]));
        }

        return $alternates;
    }
}
