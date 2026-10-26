<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Testimonial;
use App\Support\Website\Seo;
use App\Support\Website\WebsiteCache;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends WebController
{
    public function __invoke(WebsiteCache $cache): Response
    {
        $testimonials = $cache->remember('testimonials', fn (): array => Testimonial::query()->published()->with('project')->orderBy('sort_order')->get()
            ->map(fn (Testimonial $testimonial): array => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'designation' => $testimonial->translate('designation'),
                'quote' => $testimonial->translate('quote'),
                'rating' => $testimonial->rating,
                'photo' => $testimonial->photo,
                'project' => $testimonial->project?->name,
                'project_url' => $testimonial->project?->is_published ? route('web.projects.show', $testimonial->project->slug) : null,
            ])->all());

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_testimonials'), 'href' => route('web.testimonials')],
        ]);

        return Inertia::render('Web/Testimonials', [
            'testimonials' => $testimonials,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(__('website.testimonials_title'), __('website.testimonials_meta_description', ['company' => Seo::siteName()]), [
                'json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)],
            ]),
        ]);
    }
}
