<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DisableInertiaSsr
{
    public function handle(Request $request, Closure $next): Response
    {
        config(['inertia.ssr.enabled' => false]);

        return $next($request);
    }
}
