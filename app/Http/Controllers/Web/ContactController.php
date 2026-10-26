<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Branch;
use App\Support\Website\JsonLd;
use App\Support\Website\Seo;
use App\Support\Website\SiteContext;
use App\Support\Website\WebsiteCache;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends WebController
{
    public function show(WebsiteCache $cache, SiteContext $site): Response
    {
        $branches = $cache->remember('branches', fn (): array => Branch::query()->withoutGlobalScopes()
            ->orderByDesc('is_head_office')->orderBy('name')->get()
            ->map(fn (Branch $branch): array => [
                'id' => $branch->id,
                'name' => $branch->name,
                'address' => $branch->address,
                'is_head_office' => $branch->is_head_office,
            ])->all());

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_contact'), 'href' => route('web.contact')],
        ]);

        $profile = $site->profile();

        return Inertia::render('Web/Contact', [
            'branches' => $branches,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(__('website.contact_title'), __('website.contact_meta_description', ['company' => $profile['name']]), [
                'json_ld' => [JsonLd::organization($profile), $this->breadcrumbJsonLd($breadcrumbs)],
            ]),
        ]);
    }
}
