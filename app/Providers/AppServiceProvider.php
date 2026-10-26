<?php

declare(strict_types=1);

namespace App\Providers;

use Dedoc\Scramble\Scramble;
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
    }
}
