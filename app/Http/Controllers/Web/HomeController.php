<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Support\Website\JsonLd;
use App\Support\Website\ProjectPresenter;
use App\Support\Website\Seo;
use App\Support\Website\SiteContext;
use App\Support\Website\WebsiteCache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends WebController
{
    public function __invoke(WebsiteCache $cache, ProjectPresenter $presenter, SiteContext $site): Response
    {
        $data = $cache->remember('home', fn (): array => [
            'banners' => Banner::query()->published()->orderBy('sort_order')->get()
                ->map(fn (Banner $banner): array => [
                    'id' => $banner->id,
                    'title' => $banner->translate('title'),
                    'subtitle' => $banner->translate('subtitle'),
                    'image' => $banner->image,
                    'cta_label' => $banner->translate('cta_label'),
                    'cta_url' => $banner->cta_url,
                ])->all(),
            'featured' => Project::query()->published()->with('units')
                ->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('published_at')
                ->limit(6)->get()
                ->map(fn (Project $project): array => $presenter->card($project))->all(),
            'services' => Service::query()->published()->orderBy('sort_order')->limit(6)->get()
                ->map(fn (Service $service): array => [
                    'id' => $service->id,
                    'slug' => $service->slug,
                    'url' => route('web.services.show', $service->slug),
                    'title' => $service->translate('title'),
                    'summary' => $service->translate('summary'),
                    'icon' => $service->icon,
                ])->all(),
            'testimonials' => Testimonial::query()->published()->with('project')->orderBy('sort_order')->limit(6)->get()
                ->map(fn (Testimonial $testimonial): array => [
                    'id' => $testimonial->id,
                    'name' => $testimonial->name,
                    'designation' => $testimonial->translate('designation'),
                    'quote' => $testimonial->translate('quote'),
                    'rating' => $testimonial->rating,
                    'photo' => $testimonial->photo,
                    'project' => $testimonial->project?->name,
                ])->all(),
            'posts' => Post::query()->published()->latest('published_at')->limit(3)->get()
                ->map(fn (Post $post): array => $this->postCard($post))->all(),
            'faqs' => Faq::query()->published()->orderBy('sort_order')->limit(6)->get()
                ->map(fn (Faq $faq): array => [
                    'id' => $faq->id,
                    'question' => $faq->translate('question'),
                    'answer' => $faq->translate('answer'),
                ])->all(),
            'locations' => Project::query()->published()->whereNotNull('location')
                ->distinct()->orderBy('location')->pluck('location')->all(),
        ]);

        $profile = $site->profile();
        $tagline = $profile['tagline'] ?? __('website.home_default_tagline');
        $description = __('website.home_meta_description', ['company' => $profile['name']]);

        return Inertia::render('Web/Home', [
            ...$data,
            'seo' => Seo::make($profile['name'].' | '.$tagline, $description, [
                'suffix' => false,
                'json_ld' => [JsonLd::organization($profile), JsonLd::website($profile)],
            ]),
        ]);
    }
}
