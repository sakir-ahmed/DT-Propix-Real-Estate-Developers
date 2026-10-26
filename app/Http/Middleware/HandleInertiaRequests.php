<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\MenuBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'locale' => fn (): string => app()->getLocale(),
            'translations' => fn (): array => $this->translations(),
            'auth' => [
                'user' => $user instanceof User ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'locale' => $user->locale,
                    'is_super_admin' => $user->is_super_admin,
                ] : null,
            ],
            'menu' => fn (): array => app(MenuBuilder::class)->for($user instanceof User ? $user : null),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function translations(): array
    {
        $locale = app()->getLocale();
        $groups = ['nav', 'auth', 'ui', 'projects', 'units', 'leads', 'customers', 'bookings', 'collections'];
        $lines = [];

        foreach ($groups as $group) {
            $translated = Lang::get($group, [], $locale);
            $lines[$group] = is_array($translated) ? $translated : [];
        }

        return $lines;
    }
}
