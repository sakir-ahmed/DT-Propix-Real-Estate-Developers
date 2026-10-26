<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\Website\JsonLd;

/**
 * Shared helpers for public website controllers: breadcrumbs and card shapes
 * that several pages reuse.
 */
abstract class WebController extends Controller
{
    /**
     * @param  list<array{label: string, href: string}>  $trail
     * @return list<array{label: string, href: string}>
     */
    protected function breadcrumbs(array $trail): array
    {
        return [['label' => __('website.nav_home'), 'href' => route('web.home')], ...$trail];
    }

    /**
     * @param  list<array{label: string, href: string}>  $breadcrumbs
     * @return array<string, mixed>
     */
    protected function breadcrumbJsonLd(array $breadcrumbs): array
    {
        return JsonLd::breadcrumbs(array_map(
            fn (array $item): array => ['name' => $item['label'], 'url' => $item['href']],
            $breadcrumbs,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    protected function postCard(Post $post): array
    {
        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'url' => route('web.news.show', $post->slug),
            'title' => $post->translate('title'),
            'excerpt' => $post->translate('excerpt'),
            'category' => $post->category,
            'category_label' => __('website.post_category_'.$post->category),
            'cover_image' => $post->cover_image,
            'reading_minutes' => $post->reading_minutes,
            'published_at' => $post->published_at?->toIso8601String(),
        ];
    }
}
