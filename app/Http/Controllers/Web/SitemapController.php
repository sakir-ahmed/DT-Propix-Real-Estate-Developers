<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Middleware\SetWebsiteLocale;
use App\Models\JobPost;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Support\Website\WebsiteCache;
use Carbon\CarbonInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

/**
 * XML sitemap with every public page in both languages and hreflang links.
 */
class SitemapController extends WebController
{
    public function __invoke(WebsiteCache $cache): Response
    {
        $xml = $cache->remember('sitemap', fn (): string => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function build(): string
    {
        $entries = [
            $this->entry('web.home', [], null, 'daily', '1.0'),
            $this->entry('web.projects.index', [], null, 'daily', '0.9'),
            $this->entry('web.services.index', [], null, 'monthly', '0.7'),
            $this->entry('web.about', [], null, 'monthly', '0.7'),
            $this->entry('web.news.index', [], null, 'weekly', '0.7'),
            $this->entry('web.careers.index', [], null, 'weekly', '0.6'),
            $this->entry('web.team', [], null, 'monthly', '0.5'),
            $this->entry('web.testimonials', [], null, 'monthly', '0.5'),
            $this->entry('web.faq', [], null, 'monthly', '0.6'),
            $this->entry('web.contact', [], null, 'monthly', '0.7'),
        ];

        foreach (Project::query()->published()->orderBy('id')->get(['slug', 'updated_at']) as $project) {
            $entries[] = $this->entry('web.projects.show', ['project' => $project->slug], $project->updated_at, 'weekly', '0.9');
        }

        foreach (Service::query()->published()->orderBy('id')->get(['slug', 'updated_at']) as $service) {
            $entries[] = $this->entry('web.services.show', ['service' => $service->slug], $service->updated_at, 'monthly', '0.6');
        }

        foreach (Post::query()->published()->orderBy('id')->get(['slug', 'updated_at']) as $post) {
            $entries[] = $this->entry('web.news.show', ['post' => $post->slug], $post->updated_at, 'monthly', '0.6');
        }

        foreach (JobPost::query()->open()->orderBy('id')->get(['slug', 'updated_at']) as $job) {
            $entries[] = $this->entry('web.careers.show', ['jobPost' => $job->slug], $job->updated_at, 'weekly', '0.5');
        }

        foreach (Page::query()->published()->orderBy('id')->get(['slug', 'updated_at']) as $page) {
            $route = match ($page->slug) {
                'privacy' => 'web.privacy',
                'terms' => 'web.terms',
                'about' => null,
                default => 'web.pages.show',
            };

            if ($route === null) {
                continue;
            }

            $entries[] = $this->entry($route, $route === 'web.pages.show' ? ['page' => $page->slug] : [], $page->updated_at, 'yearly', '0.3');
        }

        $body = implode('', $entries);

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">'
            .$body
            .'</urlset>';
    }

    /**
     * @param  array<string, string>  $parameters
     */
    private function entry(string $route, array $parameters, ?CarbonInterface $lastModified, string $frequency, string $priority): string
    {
        $urls = [];

        foreach (SetWebsiteLocale::LOCALES as $locale) {
            $urls[$locale] = URL::route($route, [...$parameters, 'locale' => $locale]);
        }

        $alternates = '';

        foreach ($urls as $locale => $url) {
            $alternates .= '<xhtml:link rel="alternate" hreflang="'.$locale.'" href="'.e($url).'"/>';
        }

        $alternates .= '<xhtml:link rel="alternate" hreflang="x-default" href="'.e($urls['en']).'"/>';

        $xml = '';

        foreach ($urls as $url) {
            $xml .= '<url><loc>'.e($url).'</loc>'
                .($lastModified !== null ? '<lastmod>'.$lastModified->toDateString().'</lastmod>' : '')
                .'<changefreq>'.$frequency.'</changefreq>'
                .'<priority>'.$priority.'</priority>'
                .$alternates
                .'</url>';
        }

        return $xml;
    }
}
