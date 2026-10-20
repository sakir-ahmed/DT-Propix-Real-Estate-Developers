<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Tenant;
use App\Support\TenantContext;
use Dedoc\Scramble\Scramble;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::provider('eloquent', function ($app, array $config): EloquentUserProvider {
            return (new EloquentUserProvider($app['hash'], $config['model']))->withQuery(
                fn ($query) => $query->withoutGlobalScope('tenant')->withoutGlobalScope('branch'),
            );
        });

        Scramble::configure()->expose(
            ui: fn ($router, $action) => $router->get('api-docs', $action)->name('scramble.docs.ui'),
            document: fn ($router, $action) => $router->get('api-docs.json', $action)->name('scramble.docs.document'),
        );

        Activity::saving(function (Activity $activity): void {
            if ($activity->getAttribute('tenant_id') === null) {
                $activity->setAttribute('tenant_id', app(TenantContext::class)->id());
            }
        });

        Queue::createPayloadUsing(function (): array {
            return ['tenant_id' => app(TenantContext::class)->id()];
        });

        Event::listen(JobProcessing::class, function (JobProcessing $event): void {
            $tenantId = $event->job->payload()['tenant_id'] ?? null;
            $context = app(TenantContext::class);

            if (! is_numeric($tenantId)) {
                $context->set(null);

                return;
            }

            $context->set(Tenant::query()->find((int) $tenantId));
        });

        Event::listen([JobProcessed::class, JobFailed::class], function (): void {
            app(TenantContext::class)->set(null);
        });
    }
}
