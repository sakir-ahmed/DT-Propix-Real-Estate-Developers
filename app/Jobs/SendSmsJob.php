<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Jobs\Middleware\RestoreTenantContext;
use App\Services\SmsService;
use App\Support\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSmsJob implements ShouldQueue
{
    use Queueable;

    public ?int $tenantId;

    public function __construct(public string $mobile, public string $message, ?int $tenantId = null)
    {
        $this->tenantId = $tenantId ?? app(TenantContext::class)->id();
    }

    /**
     * @return list<RestoreTenantContext>
     */
    public function middleware(): array
    {
        return [new RestoreTenantContext($this->tenantId)];
    }

    public function handle(SmsService $sms): void
    {
        $sms->send($this->mobile, $this->message);
    }
}
