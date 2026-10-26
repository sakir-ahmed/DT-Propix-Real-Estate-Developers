<?php

declare(strict_types=1);

use App\Services\OtpService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

it('stores a hashed code and stops after five sends in an hour', function () {
    Bus::fake();
    $otp = app(OtpService::class);
    $key = 'otp:'.normalize_bd_mobile('01710000001');

    foreach (range(1, 5) as $attempt) {
        expect($otp->send('01710000001', '127.0.0.1'))->toBeTrue();
    }

    expect($otp->send('01710000001', '127.0.0.1'))->toBeFalse();

    $stored = Cache::get($key);
    expect($stored)->toBeArray()
        ->and($stored['hash'])->not->toBe('123456');
});

it('invalidates a code after five wrong attempts', function () {
    $key = 'otp:'.normalize_bd_mobile('01710000001');

    Cache::put($key, [
        'hash' => Hash::make('123456'),
        'attempts' => 0,
    ], 300);

    $otp = app(OtpService::class);

    foreach (range(1, 5) as $attempt) {
        expect($otp->verify('01710000001', '000000'))->toBeFalse();
    }

    expect(Cache::get($key))->toBeNull()
        ->and($otp->verify('01710000001', '123456'))->toBeFalse();
});

it('deletes the code after a successful verification', function () {
    $key = 'otp:'.normalize_bd_mobile('01710000001');

    Cache::put($key, [
        'hash' => Hash::make('123456'),
        'attempts' => 0,
    ], 300);

    expect(app(OtpService::class)->verify('01710000001', '123456'))->toBeTrue()
        ->and(Cache::get($key))->toBeNull();
});
