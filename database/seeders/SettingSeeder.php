<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Installation defaults for the settings table. Each key is created once and
 * never overwritten, so re-seeding keeps the values the owner has changed.
 */
class SettingSeeder extends Seeder
{
    /** @var array<string, mixed> */
    public const array DEFAULTS = [
        'company.name' => 'Company',
        'company.email' => 'info@company.test',
        'company.mobile' => '8801710000000',
        'company.address' => null,
        'security.idle_timeout_minutes' => 30,
        'sales.hold_hours' => 48,
        'sales.discount_percent_limit' => 5,
        'accounting.revenue_recognition' => 'collection',
        'accounting.late_charges_enabled' => false,
    ];

    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
