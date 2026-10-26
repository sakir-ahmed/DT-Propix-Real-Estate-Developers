<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Installation-wide settings stored as key-value rows and served from a
 * forever cache. The Setting model clears that cache on every save or delete,
 * so a write made through Eloquent is visible on the next read.
 */
class Settings
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function set(string $key, mixed $value): Setting
    {
        return Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return Cache::rememberForever(Setting::CACHE_KEY, fn (): array => Setting::query()
            ->pluck('value', 'key')
            ->all());
    }

    /**
     * Call this after changing rows without model events, such as a bulk update.
     */
    public function flush(): void
    {
        Cache::forget(Setting::CACHE_KEY);
    }
}
