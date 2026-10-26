<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() === null) {
            return $next($request);
        }

        $lastActivity = $request->session()->get('last_activity_at');
        $timeout = (int) setting('security.idle_timeout_minutes', 30) * 60;

        if (is_int($lastActivity) && (now()->getTimestamp() - $lastActivity) > $timeout) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login');
        }

        $request->session()->put('last_activity_at', now()->getTimestamp());

        return $next($request);
    }
}
