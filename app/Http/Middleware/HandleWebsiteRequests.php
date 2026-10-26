<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Website\SiteContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Inertia\Middleware;

/**
 * Inertia middleware for the public website. Uses the SEO-aware root view and
 * shares the company profile, navigation and website translations.
 */
class HandleWebsiteRequests extends Middleware
{
    protected $rootView = 'website';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'locale' => fn (): string => app()->getLocale(),
            'translations' => fn (): array => $this->translations(),
            'site' => fn (): array => app(SiteContext::class)->toArray(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'download_url' => fn () => $request->session()->get('download_url'),
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function translations(): array
    {
        $locale = app()->getLocale();
        $lines = [];

        foreach (['website', 'ui'] as $group) {
            $translated = Lang::get($group, [], $locale);
            $lines[$group] = is_array($translated) ? $translated : [];
        }

        return $lines;
    }
}
