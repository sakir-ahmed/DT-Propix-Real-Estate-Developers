<?php

declare(strict_types=1);

namespace App\Support\Website;

use App\Models\JobPost;
use App\Models\Post;
use App\Models\Project;

/**
 * schema.org structured data for the public website.
 */
class JsonLd
{
    /**
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function organization(array $site): array
    {
        $sameAs = array_values(array_filter([
            $site['social']['facebook'] ?? null,
            $site['social']['youtube'] ?? null,
            $site['social']['linkedin'] ?? null,
        ], fn (mixed $value): bool => is_string($value) && $value !== ''));

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $site['name'],
            'url' => url('/'),
            'logo' => url($site['logo']),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $site['mobile_tel'],
                'contactType' => 'sales',
                'areaServed' => 'BD',
                'availableLanguage' => ['English', 'Bengali'],
            ],
        ];

        if (! empty($site['address'])) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $site['address'],
                'addressCountry' => 'BD',
            ];
        }

        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function website(array $site): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $site['name'],
            'url' => url('/'),
            'inLanguage' => ['en', 'bn'],
        ];
    }

    /**
     * @param  list<array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $items): array
    {
        $elements = [];

        foreach ($items as $index => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

    /**
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function article(Post $post, string $url, array $site): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->translate('title'),
            'description' => $post->translate('excerpt'),
            'url' => $url,
            'mainEntityOfPage' => $url,
            'image' => $post->cover_image !== null ? url($post->cover_image) : url($site['logo']),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'inLanguage' => app()->getLocale(),
            'author' => [
                '@type' => 'Organization',
                'name' => $site['name'],
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $site['name'],
                'logo' => ['@type' => 'ImageObject', 'url' => url($site['logo'])],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function jobPosting(JobPost $job, string $url, array $site): array
    {
        $types = [
            'full_time' => 'FULL_TIME',
            'part_time' => 'PART_TIME',
            'contract' => 'CONTRACTOR',
            'internship' => 'INTERN',
        ];

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'JobPosting',
            'title' => $job->translate('title'),
            'description' => $job->translate('description'),
            'url' => $url,
            'datePosted' => ($job->published_at ?? $job->created_at)?->toDateString(),
            'employmentType' => $types[$job->employment_type] ?? 'FULL_TIME',
            'hiringOrganization' => [
                '@type' => 'Organization',
                'name' => $site['name'],
                'sameAs' => url('/'),
                'logo' => url($site['logo']),
            ],
            'jobLocation' => [
                '@type' => 'Place',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $job->location,
                    'addressCountry' => 'BD',
                ],
            ],
        ];

        if ($job->deadline !== null) {
            $schema['validThrough'] = $job->deadline->endOfDay()->toIso8601String();
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $site
     * @return array<string, mixed>
     */
    public static function realEstateListing(Project $project, string $url, array $site, ?string $startingPrice): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'RealEstateListing',
            'name' => $project->name,
            'description' => $project->translateColumn('description') ?? $project->translateColumn('tagline') ?? $project->name,
            'url' => $url,
            'image' => $project->cover_image !== null ? url($project->cover_image) : url($site['logo']),
            'datePosted' => ($project->published_at ?? $project->created_at)?->toDateString(),
            'inLanguage' => app()->getLocale(),
            'provider' => [
                '@type' => 'Organization',
                'name' => $site['name'],
                'url' => url('/'),
            ],
        ];

        if ($project->address !== null || $project->location !== null) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $project->address,
                'addressLocality' => $project->location,
                'addressCountry' => 'BD',
            ];
        }

        if ($project->map_lat !== null && $project->map_lng !== null) {
            $schema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $project->map_lat,
                'longitude' => (float) $project->map_lng,
            ];
        }

        if ($startingPrice !== null) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => $startingPrice,
                'availability' => 'https://schema.org/InStock',
            ];
        }

        return $schema;
    }

    /**
     * @param  list<array{question: string, answer: string}>  $faqs
     * @return array<string, mixed>
     */
    public static function faqPage(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ], $faqs),
        ];
    }
}
