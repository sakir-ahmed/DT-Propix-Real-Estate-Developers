<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class TenantStorage
{
    public function __construct(private TenantContext $tenantContext) {}

    public function path(string $path = ''): string
    {
        return $this->tenantContext->requireTenant()->storagePath($path);
    }

    public function put(string $path, string $contents): string
    {
        $relative = $this->path($path);
        Storage::disk('private')->put($relative, $contents);

        return $relative;
    }

    public function exists(string $path): bool
    {
        return Storage::disk('private')->exists($this->path($path));
    }
}
