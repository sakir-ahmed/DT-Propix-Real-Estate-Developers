<?php

declare(strict_types=1);

namespace App\Support\Website;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Public website page data cached per language. Keys carry a version number;
 * purging bumps the version so every cached page expires at once without
 * needing a cache store that supports tags.
 */
class WebsiteCache
{
    public const string VERSION_KEY = 'website:version';

    /**
     * @template TValue
     *
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public function remember(string $key, Closure $callback, ?int $ttlSeconds = null): mixed
    {
        $ttl = $ttlSeconds ?? (int) config('propix.website.cache_seconds', 3600);

        return Cache::remember($this->key($key), $ttl, $callback);
    }

    public function purge(): void
    {
        Cache::forever(self::VERSION_KEY, $this->version() + 1);
    }

    public function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    public function key(string $key): string
    {
        return 'website:v'.$this->version().':'.app()->getLocale().':'.$key;
    }
}
