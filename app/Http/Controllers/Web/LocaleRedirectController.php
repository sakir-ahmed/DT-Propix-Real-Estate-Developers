<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Middleware\SetWebsiteLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The bare domain sends the visitor to the home page in their language:
 * the last language they chose, else the browser's preference, else English.
 */
class LocaleRedirectController extends WebController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $session = $request->hasSession() ? $request->session()->get('locale') : null;
        $locale = is_string($session) && in_array($session, SetWebsiteLocale::LOCALES, true)
            ? $session
            : ($request->getPreferredLanguage(SetWebsiteLocale::LOCALES) ?? 'en');

        return redirect()->route('web.home', ['locale' => $locale]);
    }
}
