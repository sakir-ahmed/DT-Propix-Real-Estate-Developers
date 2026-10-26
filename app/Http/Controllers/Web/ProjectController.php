<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Project;
use App\Support\Website\JsonLd;
use App\Support\Website\ProjectPresenter;
use App\Support\Website\Seo;
use App\Support\Website\SiteContext;
use App\Support\Website\WebsiteCache;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends WebController
{
    /** @var array<string, array{min: float, max: float|null}> */
    private const array BUDGETS = [
        'under_50l' => ['min' => 0, 'max' => 5000000],
        '50l_1cr' => ['min' => 5000000, 'max' => 10000000],
        '1cr_2cr' => ['min' => 10000000, 'max' => 20000000],
        'above_2cr' => ['min' => 20000000, 'max' => null],
    ];

    public function __construct(
        private readonly WebsiteCache $cache,
        private readonly ProjectPresenter $presenter,
        private readonly SiteContext $site,
    ) {}

    public function index(Request $request): Response
    {
        $filters = [
            'status' => in_array($request->query('status'), Project::STATUSES, true) ? $request->query('status') : null,
            'type' => in_array($request->query('type'), Project::TYPES, true) ? $request->query('type') : null,
            'location' => is_string($request->query('location')) ? mb_substr($request->query('location'), 0, 80) : null,
            'budget' => array_key_exists((string) $request->query('budget'), self::BUDGETS) ? $request->query('budget') : null,
        ];
        $page = max(1, $request->integer('page', 1));
        $perPage = (int) config('propix.website.per_page', 12);

        $data = $this->cache->remember('projects.'.md5(json_encode([$filters, $page]) ?: ''), function () use ($filters, $page, $perPage): array {
            $cards = $this->filteredCards($filters);
            $paginator = new LengthAwarePaginator(
                $cards->forPage($page, $perPage)->values(),
                $cards->count(),
                $perPage,
                $page,
                ['path' => route('web.projects.index'), 'query' => array_filter($filters)],
            );

            return [
                'projects' => $paginator->toArray(),
                'locations' => Project::query()->published()->whereNotNull('location')->distinct()->orderBy('location')->pluck('location')->all(),
                'status_counts' => Project::query()->published()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->all(),
            ];
        });

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_projects'), 'href' => route('web.projects.index')],
        ]);

        $title = $filters['status'] !== null
            ? __('website.projects_title_status', ['status' => __('website.status_'.$filters['status'])])
            : __('website.projects_title');

        return Inertia::render('Web/Projects/Index', [
            ...$data,
            'filters' => $filters,
            'budgets' => array_keys(self::BUDGETS),
            'types' => Project::TYPES,
            'statuses' => Project::STATUSES,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make($title, __('website.projects_meta_description', ['company' => Seo::siteName()]), [
                'robots' => array_filter($filters) === [] ? 'index, follow' : 'noindex, follow',
                'json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)],
            ]),
        ]);
    }

    public function show(string $locale, Project $project): Response
    {
        abort_unless($project->is_published, 404);

        $data = $this->cache->remember('project.'.$project->id, function () use ($project): array {
            $project->load(['units.block', 'images']);

            $related = Project::query()->published()->with('units')
                ->whereKeyNot($project->id)
                ->orderByRaw('case when location = ? then 0 else 1 end', [$project->location])
                ->orderByRaw('case when status = ? then 0 else 1 end', [$project->status])
                ->orderBy('sort_order')
                ->limit(3)
                ->get()
                ->map(fn (Project $other): array => $this->presenter->card($other))
                ->all();

            return [
                'project' => $this->presenter->detail($project),
                'related' => $related,
            ];
        });

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_projects'), 'href' => route('web.projects.index')],
            ['label' => $project->name, 'href' => route('web.projects.show', $project->slug)],
        ]);

        $title = $project->translateColumn('meta_title') ?? __('website.project_title', [
            'name' => $project->name,
            'location' => $project->location ?? '',
        ]);
        $description = $project->translateColumn('meta_description')
            ?? $project->translateColumn('tagline')
            ?? $project->translateColumn('description')
            ?? $title;

        return Inertia::render('Web/Projects/Show', [
            ...$data,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make($title, $description, [
                'image' => $project->cover_image !== null ? url($project->cover_image) : null,
                'json_ld' => [
                    JsonLd::realEstateListing($project, route('web.projects.show', $project->slug), $this->site->profile(), $data['project']['starting_price']),
                    $this->breadcrumbJsonLd($breadcrumbs),
                ],
            ]),
        ]);
    }

    /**
     * @param  array{status: string|null, type: string|null, location: string|null, budget: string|null}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function filteredCards(array $filters): Collection
    {
        $query = Project::query()->published()->with('units')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByRaw("case status when 'ongoing' then 0 when 'upcoming' then 1 else 2 end")
            ->orderBy('name');

        if ($filters['status'] !== null) {
            $query->where('status', $filters['status']);
        }

        if ($filters['type'] !== null) {
            $query->where('type', $filters['type']);
        }

        if ($filters['location'] !== null) {
            $query->where('location', $filters['location']);
        }

        $cards = $query->get()->map(fn (Project $project): array => $this->presenter->card($project));

        if ($filters['budget'] !== null) {
            $range = self::BUDGETS[$filters['budget']];
            $cards = $cards->filter(function (array $card) use ($range): bool {
                if ($card['starting_price'] === null) {
                    return false;
                }

                $price = (float) $card['starting_price'];

                return $price >= $range['min'] && ($range['max'] === null || $price < $range['max']);
            });
        }

        return $cards->values();
    }
}
