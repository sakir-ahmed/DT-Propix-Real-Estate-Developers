<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Services\OtpService;
use App\Support\TenantCache;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);
});

it('stores a hashed code and stops after five sends in an hour', function () {
    Bus::fake();
    $otp = app(OtpService::class);

    foreach (range(1, 5) as $attempt) {
        expect($otp->send('01710000001', '127.0.0.1'))->toBeTrue();
    }

    expect($otp->send('01710000001', '127.0.0.1'))->toBeFalse();

    $stored = app(TenantCache::class)->get('otp:8801710000001');
    expect($stored)->toBeArray()
        ->and($stored['hash'])->not->toBe('123456');
});

it('invalidates a code after five wrong attempts', function () {
    $cache = app(TenantCache::class);
    $cache->put('otp:8801710000001', [
        'hash' => Hash::make('123456'),
        'attempts' => 0,
    ], 300);

    $otp = app(OtpService::class);

    foreach (range(1, 5) as $attempt) {
        expect($otp->verify('8801710000001', '000000'))->toBeFalse();
    }

    expect($cache->get('otp:8801710000001'))->toBeNull()
        ->and($otp->verify('8801710000001', '123456'))->toBeFalse();
});

it('deletes the code after a successful verification', function () {
    $cache = app(TenantCache::class);
    $cache->put('otp:8801710000001', [
        'hash' => Hash::make('123456'),
        'attempts' => 0,
    ], 300);

    expect(app(OtpService::class)->verify('01710000001', '123456'))->toBeTrue()
        ->and($cache->get('otp:8801710000001'))->toBeNull();
});
