<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Post;
use App\Support\Website\JsonLd;
use App\Support\Website\Seo;
use App\Support\Website\SiteContext;
use App\Support\Website\WebsiteCache;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends WebController
{
    public function __construct(private readonly WebsiteCache $cache, private readonly SiteContext $site) {}

    public function index(Request $request): Response
    {
        $category = in_array($request->query('category'), Post::CATEGORIES, true) ? $request->query('category') : null;
        $page = max(1, $request->integer('page', 1));

        $data = $this->cache->remember('news.'.($category ?? 'all').'.'.$page, function () use ($category, $page): array {
            $query = Post::query()->published()->latest('published_at');

            if ($category !== null) {
                $query->where('category', $category);
            }

            $posts = $query->paginate((int) config('propix.website.posts_per_page', 9), ['*'], 'page', $page)
                ->withPath(route('web.news.index'))
                ->appends(array_filter(['category' => $category]))
                ->through(fn (Post $post): array => $this->postCard($post));

            return [
                'posts' => $posts->toArray(),
                'category_counts' => Post::query()->published()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category')->all(),
            ];
        });

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_news'), 'href' => route('web.news.index')],
        ]);

        return Inertia::render('Web/News/Index', [
            ...$data,
            'category' => $category,
            'categories' => Post::CATEGORIES,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(
                $category !== null ? __('website.post_category_'.$category) : __('website.news_title'),
                __('website.news_meta_description', ['company' => Seo::siteName()]),
                [
                    'robots' => $category === null && $page === 1 ? 'index, follow' : 'noindex, follow',
                    'json_ld' => [$this->breadcrumbJsonLd($breadcrumbs)],
                ],
            ),
        ]);
    }

    public function show(string $locale, Post $post): Response
    {
        abort_unless($post->is_published, 404);

        $data = $this->cache->remember('post.'.$post->id, fn (): array => [
            'post' => [
                ...$this->postCard($post),
                'body' => $post->translate('body'),
                'tags' => $post->tags ?? [],
                'author' => $post->author?->name,
                'updated_at' => $post->updated_at?->toIso8601String(),
            ],
            'related' => Post::query()->published()->whereKeyNot($post->id)
                ->orderByRaw('case when category = ? then 0 else 1 end', [$post->category])
                ->latest('published_at')->limit(3)->get()
                ->map(fn (Post $other): array => $this->postCard($other))
                ->all(),
        ]);

        $breadcrumbs = $this->breadcrumbs([
            ['label' => __('website.nav_news'), 'href' => route('web.news.index')],
            ['label' => (string) $post->translate('title'), 'href' => route('web.news.show', $post->slug)],
        ]);

        return Inertia::render('Web/News/Show', [
            ...$data,
            'breadcrumbs' => $breadcrumbs,
            'seo' => Seo::make(
                $post->translate('meta_title') ?? (string) $post->translate('title'),
                $post->translate('meta_description') ?? (string) $post->translate('excerpt'),
                [
                    'type' => 'article',
                    'image' => $post->cover_image !== null ? url($post->cover_image) : null,
                    'published_time' => $post->published_at?->toIso8601String(),
                    'modified_time' => $post->updated_at?->toIso8601String(),
                    'json_ld' => [
                        JsonLd::article($post, route('web.news.show', $post->slug), $this->site->profile()),
                        $this->breadcrumbJsonLd($breadcrumbs),
                    ],
                ],
            ),
        ]);
    }
}
