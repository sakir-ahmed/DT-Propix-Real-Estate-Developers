<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UnitStatus;
use App\Models\Concerns\PurgesWebsiteCache;
use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'project_id', 'block_id', 'number', 'type', 'size_sqft', 'facing',
    'bedrooms', 'bathrooms', 'parking_count', 'base_rate', 'floor_premium',
    'parking_price', 'utility_charges', 'other_charges', 'status',
])]
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory, LogsActivity, PurgesWebsiteCache;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['number', 'status', 'base_rate', 'size_sqft'])
            ->logOnlyDirty();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => UnitStatus::class,
            'size_sqft' => 'decimal:2',
            'base_rate' => 'decimal:2',
            'floor_premium' => 'decimal:2',
            'parking_price' => 'decimal:2',
            'utility_charges' => 'decimal:2',
            'other_charges' => 'decimal:2',
        ];
    }

    /**
     * @param  Builder<Unit>  $query
     * @return Builder<Unit>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', UnitStatus::Available);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<Block, $this>
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * @return HasMany<UnitStatusHistory, $this>
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(UnitStatusHistory::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
