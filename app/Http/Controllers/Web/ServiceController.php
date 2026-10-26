<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Service;
use App\Support\Website\Seo;
use App\Support\Website\WebsiteCache;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends WebController
{
    public function __construct(private readonly WebsiteCache $cache) {}

    public function index(): Response
    {
        $services = $this->cache->remember('services', fn (): array => Service::query()->published()->orderBy('sort_order')->get()
            ->map(fn (Service $service): array => $this->card($service))
            ->all());

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_services'), 'href' => route('web.services.index')],
        ]);

        return Inertia::render('Web/Services/Index', [
            'services' => $services,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(__('website.services_title'), __('website.services_meta_description', ['company' => Seo::siteName()]), [
                'json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)],
            ]),
        ]);
    }

    public function show(string $locale, Service $service): Response
    {
        abort_unless($service->is_published, 404);

        $data = $this->cache->remember('service.'.$service->id, fn (): array => [
            'service' => [
                ...$this->card($service),
                'body' => $service->translate('body'),
                'features' => $service->translateList('features'),
            ],
            'others' => Service::query()->published()->whereKeyNot($service->id)->orderBy('sort_order')->get()
                ->map(fn (Service $other): array => $this->card($other))
                ->all(),
        ]);

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_services'), 'href' => route('web.services.index')],
            ['label' => (string) $service->translate('title'), 'href' => route('web.services.show', $service->slug)],
        ]);

        return Inertia::render('Web/Services/Show', [
            ...$data,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make((string) $service->translate('title'), (string) $service->translate('summary'), [
                'json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)],
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function card(Service $service): array
    {
        return [
            'id' => $service->id,
            'slug' => $service->slug,
            'url' => route('web.services.show', $service->slug),
            'title' => $service->translate('title'),
            'summary' => $service->translate('summary'),
            'icon' => $service->icon,
        ];
    }
}
