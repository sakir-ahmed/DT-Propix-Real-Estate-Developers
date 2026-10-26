<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Support\Settings;
use Database\Seeders\SettingSeeder;

it('seeds every default once and keeps a value the owner changed', function () {
    $this->seed(SettingSeeder::class);
    app(Settings::class)->set('sales.hold_hours', 72);

    $this->seed(SettingSeeder::class);

    expect(Setting::query()->count())->toBe(count(SettingSeeder::DEFAULTS))
        ->and(setting('sales.hold_hours'))->toBe(72)
        ->and(setting('security.idle_timeout_minutes'))->toBe(30)
        ->and(setting('sales.discount_percent_limit'))->toBe(5)
        ->and(setting('accounting.revenue_recognition'))->toBe('collection')
        ->and(setting('accounting.late_charges_enabled'))->toBeFalse();
});

it('returns the stored value with its original type', function (mixed $value) {
    app(Settings::class)->set('demo.key', $value);

    expect(setting('demo.key'))->toBe($value);
})->with([
    'integer' => [48],
    'boolean' => [false],
    'string' => ['collection'],
    'list' => [['a', 'b']],
]);

it('falls back to the default when the value is null or the key is missing', function () {
    app(Settings::class)->set('company.address', null);

    expect(setting('company.address', 'Dhaka'))->toBe('Dhaka')
        ->and(setting('missing.key', 'fallback'))->toBe('fallback')
        ->and(setting('missing.key'))->toBeNull();
});

it('serves the new value right after a row is saved through the model', function () {
    $setting = Setting::factory()->create(['key' => 'sales.hold_hours', 'value' => 48]);
    expect(setting('sales.hold_hours'))->toBe(48);

    $setting->update(['value' => 24]);

    expect(setting('sales.hold_hours'))->toBe(24);
});

it('forgets a deleted row on the next read', function () {
    $setting = Setting::factory()->create(['key' => 'sales.hold_hours', 'value' => 48]);
    expect(setting('sales.hold_hours'))->toBe(48);

    $setting->delete();

    expect(setting('sales.hold_hours', 72))->toBe(72);
});
