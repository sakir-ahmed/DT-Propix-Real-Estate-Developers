<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use DateTimeInterface;
use Illuminate\Support\Facades\Cache;
use LogicException;

class TenantCache
{
    public function __construct(private TenantContext $tenantContext) {}

    public function key(string $key): string
    {
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw new LogicException('Cannot use tenant cache without a tenant context.');
        }

        return $tenant->cachePrefix().$key;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::get($this->key($key), $default);
    }

    public function put(string $key, mixed $value, DateTimeInterface|int $ttl): void
    {
        Cache::put($this->key($key), $value, $ttl);
    }

    public function forget(string $key): void
    {
        Cache::forget($this->key($key));
    }

    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function remember(string $key, DateTimeInterface|int $ttl, Closure $callback): mixed
    {
        return Cache::remember($this->key($key), $ttl, $callback);
    }
}
