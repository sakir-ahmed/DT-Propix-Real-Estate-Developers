<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    public function send(string $mobile, string $ip): bool
    {
        $mobile = normalize_bd_mobile($mobile);
        $limitKey = 'otp-send:'.$ip.'|'.$mobile;
        $maxSends = (int) config('propix.otp.max_send_per_hour');

        if (RateLimiter::tooManyAttempts($limitKey, $maxSends)) {
            return false;
        }

        RateLimiter::hit($limitKey, 3600);

        $code = (string) random_int(100000, 999999);

        Cache::put($this->key($mobile), [
            'hash' => Hash::make($code),
            'attempts' => 0,
        ], (int) config('propix.otp.ttl_seconds'));

        SendSmsJob::dispatch($mobile, __('auth.otp_message', ['code' => $code]));

        return true;
    }

    public function verify(string $mobile, string $code): bool
    {
        $mobile = normalize_bd_mobile($mobile);
        $key = $this->key($mobile);
        $payload = Cache::get($key);

        if (! is_array($payload) || ! isset($payload['hash'], $payload['attempts'])) {
            return false;
        }

        $maxAttempts = (int) config('propix.otp.max_verify_attempts');
        $attempts = (int) $payload['attempts'];

        if ($attempts >= $maxAttempts) {
            Cache::forget($key);

            return false;
        }

        if (! Hash::check($code, (string) $payload['hash'])) {
            $attempts++;

            if ($attempts >= $maxAttempts) {
                Cache::forget($key);
            } else {
                $payload['attempts'] = $attempts;
                Cache::put($key, $payload, (int) config('propix.otp.ttl_seconds'));
            }

            return false;
        }

        Cache::forget($key);

        return true;
    }

    private function key(string $mobile): string
    {
        return 'otp:'.$mobile;
    }
}
