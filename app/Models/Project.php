<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\PurgesWebsiteCache;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'name', 'slug', 'type', 'status', 'location', 'map_lat', 'map_lng',
    'launch_date', 'handover_date', 'description_en', 'description_bn',
    'is_published', 'published_at', 'is_featured', 'show_prices', 'show_sold_units', 'sort_order',
    'tagline_en', 'tagline_bn', 'address', 'land_area', 'floors_count',
    'cover_image', 'video_url', 'brochure_path', 'amenities', 'specifications', 'highlights', 'nearby',
    'meta_title_en', 'meta_title_bn', 'meta_description_en', 'meta_description_bn',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasTranslations, LogsActivity, Publishable, PurgesWebsiteCache;

    /** @var list<string> */
    public const array TYPES = ['apartment', 'commercial', 'plot', 'mixed'];

    /** @var list<string> */
    public const array STATUSES = ['ongoing', 'upcoming', 'completed'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'status', 'location', 'is_published', 'show_prices', 'show_sold_units'])
            ->logOnlyDirty();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'map_lat' => 'decimal:7',
            'map_lng' => 'decimal:7',
            'launch_date' => 'date',
            'handover_date' => 'date',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'show_prices' => 'boolean',
            'show_sold_units' => 'boolean',
            'floors_count' => 'integer',
            'amenities' => 'array',
            'specifications' => 'array',
            'highlights' => 'array',
            'nearby' => 'array',
        ];
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * @return HasMany<Block, $this>
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class);
    }

    /**
     * @return HasMany<Unit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * @return HasMany<ProjectImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<Testimonial, $this>
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }
}
