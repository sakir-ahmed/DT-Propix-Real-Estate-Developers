<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ExportInstallationCommand extends Command
{
    protected $signature = 'propix:export';

    protected $description = 'Export this installation\'s data as JSON plus its private files';

    public function handle(): int
    {
        $users = User::query()
            ->get()
            ->map(function (User $user): array {
                $attributes = $user->attributesToArray();
                unset($attributes['password'], $attributes['remember_token']);

                return $attributes;
            })
            ->all();

        $payload = [
            'branches' => Branch::query()->get()->toArray(),
            'users' => $users,
        ];

        $stamp = now()->format('YmdHis');
        $relative = 'exports/'.$stamp;
        Storage::disk('private')->makeDirectory($relative);
        Storage::disk('private')->put($relative.'/installation.json', (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        $source = storage_path('app/private');
        $destination = storage_path('app/private/'.$relative.'/files');

        foreach (['customers', 'documents'] as $folder) {
            $from = $source.'/'.$folder;

            if (is_dir($from)) {
                File::copyDirectory($from, $destination.'/'.$folder);
            }
        }

        $this->info(storage_path('app/private/'.$relative));

        return self::SUCCESS;
    }
}
