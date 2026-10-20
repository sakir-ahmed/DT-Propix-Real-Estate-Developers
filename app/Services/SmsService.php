<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $mobile, string $message): bool
    {
        $response = Http::asForm()
            ->timeout(10)
            ->post((string) config('services.bdbulksms.url'), [
                'to' => $mobile,
                'message' => $message,
                'token' => config('services.bdbulksms.token'),
            ]);

        $sent = $response->successful() && str_starts_with($response->body(), 'Ok');

        if ($sent) {
            Log::info('SMS sent.');
        } else {
            Log::error('SMS failed.', ['status' => $response->status()]);
        }

        return $sent;
    }
}
