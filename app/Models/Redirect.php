<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PurgesWebsiteCache;
use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A permanent redirect for a changed slug or a retired URL on the public site.
 */
#[Fillable(['from_path', 'to_path', 'status_code', 'hits'])]
class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory, LogsActivity, PurgesWebsiteCache;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['from_path', 'to_path', 'status_code'])
            ->logOnlyDirty();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
        ];
    }

    public static function normalizePath(string $path): string
    {
        $parsed = parse_url($path, PHP_URL_PATH);
        $trimmed = trim(is_string($parsed) ? $parsed : $path, '/');

        return '/'.$trimmed;
    }
}
