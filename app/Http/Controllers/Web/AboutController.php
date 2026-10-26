<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Page;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Support\Website\Localizer;
use App\Support\Website\Seo;
use App\Support\Website\SiteContext;
use App\Support\Website\WebsiteCache;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends WebController
{
    public function __invoke(WebsiteCache $cache, SiteContext $site): Response
    {
        $data = $cache->remember('about', function (): array {
            $page = Page::query()->published()->where('slug', 'about')->first();
            $milestones = setting('website.milestones');

            return [
                'page' => $page instanceof Page ? [
                    'title' => $page->translate('title'),
                    'excerpt' => $page->translate('excerpt'),
                    'body' => $page->translate('body'),
                ] : null,
                'milestones' => is_array($milestones) ? Localizer::list($milestones) : [],
                'leadership' => TeamMember::query()->published()->where('department', 'leadership')->orderBy('sort_order')->limit(4)->get()
                    ->map(fn (TeamMember $member): array => [
                        'id' => $member->id,
                        'name' => $member->name,
                        'designation' => $member->translate('designation'),
                        'bio' => $member->translate('bio'),
                        'photo' => $member->photo,
                        'linkedin_url' => $member->linkedin_url,
                    ])->all(),
                'testimonials' => Testimonial::query()->published()->with('project')->orderBy('sort_order')->limit(3)->get()
                    ->map(fn (Testimonial $testimonial): array => [
                        'id' => $testimonial->id,
                        'name' => $testimonial->name,
                        'designation' => $testimonial->translate('designation'),
                        'quote' => $testimonial->translate('quote'),
                        'rating' => $testimonial->rating,
                        'project' => $testimonial->project?->name,
                    ])->all(),
            ];
        });

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_about'), 'href' => route('web.about')],
        ]);

        $profile = $site->profile();

        return Inertia::render('Web/About', [
            ...$data,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(
                $data['page']['title'] ?? __('website.about_title', ['company' => $profile['name']]),
                $data['page']['excerpt'] ?? __('website.about_meta_description', ['company' => $profile['name']]),
                ['json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)]],
            ),
        ]);
    }
}
