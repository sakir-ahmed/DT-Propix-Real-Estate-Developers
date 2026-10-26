<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $mobile, public string $message) {}

    public function handle(SmsService $sms): void
    {
        $sms->send($this->mobile, $this->message);
    }
}
