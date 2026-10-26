<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Page;
use App\Support\Website\Seo;
use App\Support\Website\WebsiteCache;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends WebController
{
    public function __construct(private readonly WebsiteCache $cache) {}

    public function show(string $locale, Page $page): Response
    {
        abort_unless($page->is_published, 404);

        return $this->render($page, route('web.pages.show', $page->slug));
    }

    public function privacy(): Response
    {
        return $this->render($this->bySlug('privacy'), route('web.privacy'));
    }

    public function terms(): Response
    {
        return $this->render($this->bySlug('terms'), route('web.terms'));
    }

    private function bySlug(string $slug): Page
    {
        return Page::query()->published()->where('slug', $slug)->firstOrFail();
    }

    private function render(Page $page, string $url): Response
    {
        $data = $this->cache->remember('page.'.$page->id, fn (): array => [
            'title' => $page->translate('title'),
            'excerpt' => $page->translate('excerpt'),
            'body' => $page->translate('body'),
            'template' => $page->template,
            'updated_at' => $page->updated_at?->toIso8601String(),
        ]);

        $breadcrumbs = $this->breadcrumbs([
            ['label' => (string) $data['title'], 'href' => $url],
        ]);

        return Inertia::render('Web/Page', [
            'page' => $data,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(
                $page->translate('meta_title') ?? (string) $data['title'],
                $page->translate('meta_description') ?? ($data['excerpt'] ?? (string) $data['title']),
                ['json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)]],
            ),
        ]);
    }
}
