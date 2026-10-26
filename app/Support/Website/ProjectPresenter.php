<?php

declare(strict_types=1);

namespace App\Support\Website;

use App\Actions\Inventory\PriceUnitAction;
use App\Enums\UnitStatus;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Unit;
use Illuminate\Support\Collection;

/**
 * Shapes projects and units for the public website. Only publishable data
 * leaves this class: prices follow the project's `show_prices` setting and
 * sold units appear only when `show_sold_units` is on.
 */
class ProjectPresenter
{
    public function __construct(private readonly PriceUnitAction $priceUnit) {}

    /**
     * @return array<string, mixed>
     */
    public function card(Project $project): array
    {
        $available = $project->units->filter(fn (Unit $unit): bool => $unit->status === UnitStatus::Available);

        return [
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'url' => route('web.projects.show', $project->slug),
            'type' => $project->type,
            'type_label' => __('website.type_'.$project->type),
            'status' => $project->status,
            'status_label' => __('website.status_'.$project->status),
            'location' => $project->location,
            'tagline' => $project->translateColumn('tagline'),
            'cover_image' => $project->cover_image,
            'is_featured' => $project->is_featured,
            'available_units' => $available->count(),
            'total_units' => $project->units->count(),
            'starting_price' => $project->show_prices ? $this->startingPrice($available) : null,
            'show_prices' => $project->show_prices,
            'bedrooms' => $this->range($project->units->pluck('bedrooms')),
            'sizes' => $this->range($project->units->pluck('size_sqft')->map(fn (mixed $size): int => (int) round((float) $size))),
            'handover_date' => $project->handover_date?->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(Project $project): array
    {
        $units = collect($this->publicUnits($project));
        $available = $units->filter(fn (array $unit): bool => $unit['status'] === 'available');

        return [
            ...$this->card($project),
            'address' => $project->address,
            'description' => $project->translateColumn('description'),
            'land_area' => $project->land_area,
            'floors_count' => $project->floors_count,
            'launch_date' => $project->launch_date?->toDateString(),
            'video_url' => $this->embedUrl($project->video_url),
            'has_brochure' => $project->brochure_path !== null,
            'map' => $project->map_lat !== null && $project->map_lng !== null
                ? ['lat' => (float) $project->map_lat, 'lng' => (float) $project->map_lng]
                : null,
            'amenities' => Localizer::list($project->amenities ?? []),
            'specifications' => Localizer::list($project->specifications ?? []),
            'highlights' => Localizer::list($project->highlights ?? []),
            'nearby' => Localizer::list($project->nearby ?? []),
            'gallery' => $this->images($project, 'gallery'),
            'floor_plans' => $this->images($project, 'floor_plan'),
            'units' => $units->values()->all(),
            'available_units' => $available->count(),
            'show_sold_units' => $project->show_sold_units,
            'unit_types' => $units->pluck('type')->unique()->values()->all(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function publicUnits(Project $project): array
    {
        $visible = [UnitStatus::Available];

        if ($project->show_sold_units) {
            $visible = [...$visible, UnitStatus::Booked, UnitStatus::Sold, UnitStatus::HandedOver];
        }

        return $project->units
            ->filter(fn (Unit $unit): bool => in_array($unit->status, $visible, true))
            ->sortBy('number')
            ->map(fn (Unit $unit): array => [
                'id' => $unit->id,
                'number' => $unit->number,
                'type' => $unit->type,
                'block' => $unit->block?->name,
                'size_sqft' => number_format((float) $unit->size_sqft, 0, '.', ''),
                'facing' => $unit->facing,
                'bedrooms' => $unit->bedrooms,
                'bathrooms' => $unit->bathrooms,
                'parking_count' => $unit->parking_count,
                'status' => $unit->status === UnitStatus::Available ? 'available' : 'sold',
                'price' => $project->show_prices && $unit->status === UnitStatus::Available
                    ? $this->priceUnit->handle($unit)['total']
                    : null,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Unit>  $units
     */
    public function startingPrice(Collection $units): ?string
    {
        if ($units->isEmpty()) {
            return null;
        }

        $totals = $units->map(fn (Unit $unit): float => (float) $this->priceUnit->handle($unit)['total']);

        return number_format((float) $totals->min(), 2, '.', '');
    }

    /**
     * @return list<array{path: string, alt: string, caption: string|null}>
     */
    private function images(Project $project, string $kind): array
    {
        return $project->images
            ->where('kind', $kind)
            ->map(fn (ProjectImage $image): array => [
                'path' => $image->path,
                'alt' => (string) $image->translate('alt'),
                'caption' => $image->translate('caption'),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, mixed>  $values
     * @return array{min: int, max: int}|null
     */
    private function range(Collection $values): ?array
    {
        $numbers = $values->filter(fn (mixed $value): bool => $value !== null)->map(fn (mixed $value): int => (int) $value);

        if ($numbers->isEmpty()) {
            return null;
        }

        return ['min' => (int) $numbers->min(), 'max' => (int) $numbers->max()];
    }

    private function embedUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $url, $matches) === 1) {
            return 'https://www.youtube-nocookie.com/embed/'.$matches[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches) === 1) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }
}
