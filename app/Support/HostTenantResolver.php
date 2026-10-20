<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant;

class HostTenantResolver
{
    public function isPlatformHost(string $host): bool
    {
        $domain = (string) config('propix.domain');

        return in_array($host, [$domain, 'www.'.$domain, 'localhost', '127.0.0.1'], true);
    }

    public function find(string $host): ?Tenant
    {
        $domain = (string) config('propix.domain');
        $suffix = '.'.$domain;

        if (str_ends_with($host, $suffix)) {
            $slug = substr($host, 0, -strlen($suffix));

            if ($slug === '' || str_contains($slug, '.')) {
                return null;
            }

            return Tenant::query()->where('slug', $slug)->first();
        }

        return Tenant::query()
            ->where('custom_domain', $host)
            ->whereNotNull('domain_verified_at')
            ->first();
    }
}
