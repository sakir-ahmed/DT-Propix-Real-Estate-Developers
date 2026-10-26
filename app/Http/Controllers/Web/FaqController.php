<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Faq;
use App\Support\Website\JsonLd;
use App\Support\Website\Seo;
use App\Support\Website\WebsiteCache;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends WebController
{
    public function __invoke(WebsiteCache $cache): Response
    {
        $groups = $cache->remember('faq', function (): array {
            $faqs = Faq::query()->published()->orderBy('sort_order')->get();

            return collect(Faq::CATEGORIES)
                ->filter(fn (string $category): bool => $faqs->where('category', $category)->isNotEmpty())
                ->map(fn (string $category): array => [
                    'key' => $category,
                    'label' => __('website.faq_category_'.$category),
                    'items' => $faqs->where('category', $category)->map(fn (Faq $faq): array => [
                        'id' => $faq->id,
                        'question' => $faq->translate('question'),
                        'answer' => $faq->translate('answer'),
                    ])->values()->all(),
                ])
                ->values()
                ->all();
        });

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_faq'), 'href' => route('web.faq')],
        ]);

        $flat = [];

        foreach ($groups as $group) {
            foreach ($group['items'] as $item) {
                $flat[] = ['question' => (string) $item['question'], 'answer' => (string) $item['answer']];
            }
        }

        return Inertia::render('Web/Faq', [
            'groups' => $groups,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(__('website.faq_title'), __('website.faq_meta_description', ['company' => Seo::siteName()]), [
                'json_ld' => $flat === []
                    ? [$this->breadcrumbJsonLd($breadcrumbs)]
                    : [JsonLd::faqPage($flat), $this->breadcrumbJsonLd($breadcrumbs)],
            ]),
        ]);
    }
}
