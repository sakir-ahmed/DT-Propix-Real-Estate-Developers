<?php

declare(strict_types=1);

namespace App\Console\Commands\Tenants;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ExportTenantCommand extends Command
{
    protected $signature = 'tenants:export {tenant : Tenant id or slug}';

    protected $description = 'Export one tenant as JSON plus its private files';

    public function handle(TenantContext $tenantContext): int
    {
        $argument = (string) $this->argument('tenant');

        $tenant = Tenant::query()
            ->where('slug', $argument)
            ->orWhere('id', $argument)
            ->first();

        if ($tenant === null) {
            $this->error('Tenant not found.');

            return self::FAILURE;
        }

        $tenantContext->set($tenant);

        $users = $tenant->users()
            ->get()
            ->map(function (User $user): array {
                $attributes = $user->attributesToArray();
                unset($attributes['password'], $attributes['remember_token']);

                return $attributes;
            })
            ->all();

        $payload = [
            'tenant' => $tenant->toArray(),
            'branches' => $tenant->branches()->get()->toArray(),
            'users' => $users,
        ];

        $stamp = now()->format('YmdHis');
        $relative = 'exports/'.$tenant->id.'/'.$stamp;
        Storage::disk('private')->makeDirectory($relative);
        Storage::disk('private')->put($relative.'/tenant.json', (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        $source = storage_path('app/private/'.$tenant->storagePath());
        $destination = storage_path('app/private/'.$relative.'/files');

        if (is_dir($source)) {
            File::copyDirectory($source, $destination);
        }

        $this->info(storage_path('app/private/'.$relative));

        return self::SUCCESS;
    }
}
