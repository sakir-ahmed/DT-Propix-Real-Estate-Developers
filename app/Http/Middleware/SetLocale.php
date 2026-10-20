<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $preferred = $user instanceof User ? $user->locale : null;
        $sessionLocale = $request->hasSession() ? $request->session()->get('locale') : null;
        $locale = $preferred
            ?? $sessionLocale
            ?? $request->getPreferredLanguage(['en', 'bn'])
            ?? config('app.locale');

        if (! in_array($locale, ['en', 'bn'], true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
