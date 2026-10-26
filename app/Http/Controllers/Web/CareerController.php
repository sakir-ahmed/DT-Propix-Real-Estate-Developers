<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Website\ApplyForJobAction;
use App\Http\Requests\Web\StoreJobApplicationRequest;
use App\Models\JobPost;
use App\Support\Website\JsonLd;
use App\Support\Website\Seo;
use App\Support\Website\SiteContext;
use App\Support\Website\WebsiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class CareerController extends WebController
{
    public function __construct(private readonly WebsiteCache $cache, private readonly SiteContext $site) {}

    public function index(): Response
    {
        $jobs = $this->cache->remember('careers', fn (): array => JobPost::query()->open()
            ->orderBy('department')->orderByDesc('published_at')->get()
            ->map(fn (JobPost $job): array => $this->card($job))
            ->all());

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_careers'), 'href' => route('web.careers.index')],
        ]);

        return Inertia::render('Web/Careers/Index', [
            'jobs' => $jobs,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(__('website.careers_title'), __('website.careers_meta_description', ['company' => Seo::siteName()]), [
                'json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)],
            ]),
        ]);
    }

    public function show(string $locale, JobPost $jobPost): Response
    {
        abort_unless($jobPost->is_published, 404);

        $job = [
            ...$this->card($jobPost),
            'description' => $jobPost->translate('description'),
            'requirements' => $jobPost->translateList('requirements'),
            'benefits' => $jobPost->translateList('benefits'),
            'is_open' => $jobPost->isOpen(),
        ];

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_careers'), 'href' => route('web.careers.index')],
            ['label' => (string) $jobPost->translate('title'), 'href' => route('web.careers.show', $jobPost->slug)],
        ]);

        $jsonLd = [$this->breadcrumbJsonLd($breadcrumbs)];

        if ($jobPost->isOpen()) {
            array_unshift($jsonLd, JsonLd::jobPosting($jobPost, route('web.careers.show', $jobPost->slug), $this->site->profile()));
        }

        return Inertia::render('Web/Careers/Show', [
            'job' => $job,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(
                __('website.careers_job_title', ['title' => (string) $jobPost->translate('title'), 'company' => Seo::siteName()]),
                (string) $jobPost->translate('summary'),
                ['json_ld' => $jsonLd],
            ),
        ]);
    }

    public function apply(string $locale, StoreJobApplicationRequest $request, JobPost $jobPost, ApplyForJobAction $apply): RedirectResponse
    {
        abort_unless($jobPost->is_published, 404);

        if (! $jobPost->isOpen()) {
            return back()->with('error', __('website.careers_closed'));
        }

        if (! $request->isSpam()) {
            $cv = $request->file('cv');

            if ($cv instanceof UploadedFile) {
                $apply->handle($jobPost, $request->payload(), $cv);
            }
        }

        return back()->with('success', __('website.careers_applied'));
    }

    /**
     * @return array<string, mixed>
     */
    private function card(JobPost $job): array
    {
        return [
            'id' => $job->id,
            'slug' => $job->slug,
            'url' => route('web.careers.show', $job->slug),
            'title' => $job->translate('title'),
            'summary' => $job->translate('summary'),
            'department' => $job->department,
            'department_label' => __('website.department_'.$job->department),
            'location' => $job->location,
            'employment_type' => $job->employment_type,
            'employment_type_label' => __('website.employment_'.$job->employment_type),
            'experience' => $job->experience,
            'salary_range' => $job->salary_range,
            'vacancies' => $job->vacancies,
            'deadline' => $job->deadline?->toDateString(),
            'published_at' => ($job->published_at ?? $job->created_at)?->toIso8601String(),
        ];
    }
}
