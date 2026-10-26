<?php

declare(strict_types=1);

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Scramble::configure()->expose(
            ui: fn ($router, $action) => $router->get('api-docs', $action)->name('scramble.docs.ui'),
            document: fn ($router, $action) => $router->get('api-docs.json', $action)->name('scramble.docs.document'),
        );

        RateLimiter::for('website-forms', fn (Request $request): Limit => Limit::perMinute(
            (int) config('propix.website.form_rate_limit_per_minute', 5),
        )->by((string) $request->ip()));
    }
}
