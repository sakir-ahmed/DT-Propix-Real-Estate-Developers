<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\Website\WebsiteCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Any change to this model invalidates the public website cache, so an edit,
 * a publish, or a unit status or price change is visible within a minute.
 *
 * @mixin Model
 */
trait PurgesWebsiteCache
{
    public static function bootPurgesWebsiteCache(): void
    {
        $purge = static function (): void {
            app(WebsiteCache::class)->purge();
        };

        static::saved($purge);
        static::deleted($purge);
    }
}
