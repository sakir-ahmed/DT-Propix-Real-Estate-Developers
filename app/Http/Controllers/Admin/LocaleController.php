<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['en', 'bn'], true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        $user = $request->user();

        if ($user instanceof User) {
            $user->update(['locale' => $locale]);
        }

        return back();
    }
}
