<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\TeamMember;
use App\Support\Website\Seo;
use App\Support\Website\WebsiteCache;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends WebController
{
    /** @var list<string> */
    private const array DEPARTMENT_ORDER = ['leadership', 'sales', 'engineering', 'accounts', 'customer-care'];

    public function __invoke(WebsiteCache $cache): Response
    {
        $groups = $cache->remember('team', function (): array {
            $members = TeamMember::query()->published()->orderBy('sort_order')->orderBy('name')->get();
            $departments = $members->pluck('department')->filter()->unique()
                ->sortBy(fn (string $department): int => array_search($department, self::DEPARTMENT_ORDER, true) ?: 99)
                ->values();

            return $departments->map(fn (string $department): array => [
                'key' => $department,
                'label' => __('website.department_'.$department),
                'members' => $members->where('department', $department)->map(fn (TeamMember $member): array => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'designation' => $member->translate('designation'),
                    'bio' => $member->translate('bio'),
                    'photo' => $member->photo,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'linkedin_url' => $member->linkedin_url,
                ])->values()->all(),
            ])->all();
        });

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_team'), 'href' => route('web.team')],
        ]);

        return Inertia::render('Web/Team', [
            'groups' => $groups,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(__('website.team_title'), __('website.team_meta_description', ['company' => Seo::siteName()]), [
                'json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)],
            ]),
        ]);
    }
}
