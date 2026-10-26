<?php

declare(strict_types=1);

namespace App\Support\Website;

use App\Enums\UnitStatus;
use App\Models\Page;
use App\Models\Project;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Shared data for every public page: company profile and contact details,
 * navigation, footer links and headline numbers. Cached per language.
 */
class SiteContext
{
    public function __construct(private readonly WebsiteCache $cache) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->profile(),
            'locale' => app()->getLocale(),
            'alternates' => Seo::alternates(),
            'nav' => $this->navigation(),
            'footer' => $this->footer(),
            'stats' => $this->stats(),
            'project_options' => $this->projectOptions(),
            'submission_token' => (string) Str::uuid(),
            'year' => now()->year,
            'powered_by' => ['name' => 'Datatech BD Ltd', 'url' => 'https://datatechbd.com'],
        ];
    }

    /**
     * Company identity and contact details only (no navigation), for JSON-LD.
     *
     * @return array<string, mixed>
     */
    public function profile(): array
    {
        $mobile = (string) setting('company.mobile', '');
        $whatsapp = setting('website.whatsapp');
        $whatsappDigits = is_string($whatsapp) && $whatsapp !== '' ? preg_replace('/\D+/', '', $whatsapp) : null;

        return [
            'name' => Seo::siteName(),
            'tagline' => $this->localizedSetting('website.tagline'),
            'email' => setting('company.email'),
            'mobile' => $mobile !== '' ? format_bd_mobile($mobile) : null,
            'mobile_tel' => $mobile !== '' ? '+'.preg_replace('/\D+/', '', $mobile) : null,
            'whatsapp' => $whatsappDigits !== null && $whatsappDigits !== '' ? 'https://wa.me/'.$whatsappDigits : null,
            'address' => setting('company.address'),
            'office_hours' => $this->localizedSetting('website.office_hours'),
            'established_year' => setting('website.established_year'),
            'social' => [
                'facebook' => setting('website.facebook'),
                'youtube' => setting('website.youtube'),
                'linkedin' => setting('website.linkedin'),
            ],
            'map' => [
                'lat' => setting('website.map_lat'),
                'lng' => setting('website.map_lng'),
            ],
            'analytics_id' => setting('website.analytics_id'),
            'logo' => '/images/brand/logo-primary.png',
            'logo_reversed' => '/images/brand/logo-primary-reversed.png',
            'mark' => '/images/brand/logo-mark.svg',
        ];
    }

    /**
     * @return array{projects: int, completed: int, ongoing: int, upcoming: int, units_delivered: int, families: int, years: int|null}
     */
    public function stats(): array
    {
        return $this->cache->remember('stats', function (): array {
            $established = setting('website.established_year');
            $counts = Project::query()->published()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            return [
                'projects' => (int) $counts->sum(),
                'completed' => (int) ($counts['completed'] ?? 0),
                'ongoing' => (int) ($counts['ongoing'] ?? 0),
                'upcoming' => (int) ($counts['upcoming'] ?? 0),
                'units_delivered' => Unit::query()
                    ->whereHas('project', fn (Builder $query) => $query->where('is_published', true)->where('status', 'completed'))
                    ->count(),
                'families' => Unit::query()
                    ->whereHas('project', fn (Builder $query) => $query->where('is_published', true))
                    ->whereIn('status', [UnitStatus::Sold, UnitStatus::HandedOver])
                    ->count(),
                'years' => is_numeric($established) ? max(1, now()->year - (int) $established) : null,
            ];
        });
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    public function projectOptions(): array
    {
        return $this->cache->remember('project-options', fn (): array => Project::query()
            ->published()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Project $project): array => ['id' => $project->id, 'name' => $project->name])
            ->all());
    }

    private function localizedSetting(string $key): ?string
    {
        $value = setting($key);

        if (is_array($value)) {
            return Localizer::pick($value);
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @return list<array{key: string, label: string, href: string, active: bool}>
     */
    private function navigation(): array
    {
        $items = [
            ['key' => 'home', 'label' => __('website.nav_home'), 'route' => 'web.home', 'pattern' => 'web.home'],
            ['key' => 'projects', 'label' => __('website.nav_projects'), 'route' => 'web.projects.index', 'pattern' => 'web.projects.*'],
            ['key' => 'services', 'label' => __('website.nav_services'), 'route' => 'web.services.index', 'pattern' => 'web.services.*'],
            ['key' => 'about', 'label' => __('website.nav_about'), 'route' => 'web.about', 'pattern' => 'web.about'],
            ['key' => 'news', 'label' => __('website.nav_news'), 'route' => 'web.news.index', 'pattern' => 'web.news.*'],
            ['key' => 'careers', 'label' => __('website.nav_careers'), 'route' => 'web.careers.index', 'pattern' => 'web.careers.*'],
            ['key' => 'contact', 'label' => __('website.nav_contact'), 'route' => 'web.contact', 'pattern' => 'web.contact'],
        ];

        return array_map(fn (array $item): array => [
            'key' => $item['key'],
            'label' => $item['label'],
            'href' => route($item['route']),
            'active' => request()->routeIs($item['pattern']),
        ], $items);
    }

    /**
     * @return array<string, list<array{label: string, href: string}>>
     */
    private function footer(): array
    {
        $projects = $this->cache->remember('footer.projects', fn (): array => Project::query()
            ->published()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(5)
            ->get(['name', 'slug'])
            ->map(fn (Project $project): array => ['label' => $project->name, 'href' => route('web.projects.show', $project->slug)])
            ->all());

        $pages = $this->cache->remember('footer.pages', fn (): array => Page::query()
            ->published()
            ->where('show_in_footer', true)
            ->whereNotIn('slug', ['privacy', 'terms', 'about'])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Page $page): array => ['label' => (string) $page->translate('title'), 'href' => route('web.pages.show', $page->slug)])
            ->all());

        return [
            'company' => [
                ['label' => __('website.nav_about'), 'href' => route('web.about')],
                ['label' => __('website.nav_team'), 'href' => route('web.team')],
                ['label' => __('website.nav_testimonials'), 'href' => route('web.testimonials')],
                ['label' => __('website.nav_careers'), 'href' => route('web.careers.index')],
                ['label' => __('website.nav_contact'), 'href' => route('web.contact')],
            ],
            'explore' => [
                ['label' => __('website.nav_projects'), 'href' => route('web.projects.index')],
                ['label' => __('website.status_ongoing'), 'href' => route('web.projects.index', ['status' => 'ongoing'])],
                ['label' => __('website.status_upcoming'), 'href' => route('web.projects.index', ['status' => 'upcoming'])],
                ['label' => __('website.status_completed'), 'href' => route('web.projects.index', ['status' => 'completed'])],
                ['label' => __('website.nav_services'), 'href' => route('web.services.index')],
                ['label' => __('website.nav_news'), 'href' => route('web.news.index')],
                ['label' => __('website.nav_faq'), 'href' => route('web.faq')],
            ],
            'projects' => $projects,
            'legal' => [
                ['label' => __('website.nav_privacy'), 'href' => route('web.privacy')],
                ['label' => __('website.nav_terms'), 'href' => route('web.terms')],
                ...$pages,
            ],
        ];
    }
}
