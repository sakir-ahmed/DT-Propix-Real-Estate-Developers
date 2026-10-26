<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\PurgesWebsiteCache;
use Database\Factories\JobPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'slug', 'title', 'department', 'location', 'employment_type', 'experience', 'salary_range',
    'vacancies', 'summary', 'description', 'requirements', 'benefits', 'deadline',
    'is_published', 'published_at',
])]
class JobPost extends Model
{
    /** @use HasFactory<JobPostFactory> */
    use HasFactory, HasTranslations, LogsActivity, Publishable, PurgesWebsiteCache;

    /** @var list<string> */
    public const array EMPLOYMENT_TYPES = ['full_time', 'part_time', 'contract', 'internship'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['slug', 'department', 'is_published', 'deadline'])
            ->logOnlyDirty();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'summary' => 'array',
            'description' => 'array',
            'requirements' => 'array',
            'benefits' => 'array',
            'vacancies' => 'integer',
            'deadline' => 'date',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Published and not past the application deadline.
     *
     * @param  Builder<JobPost>  $query
     * @return Builder<JobPost>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->published()->where(function (Builder $query): void {
            $query->whereNull('deadline')->orWhereDate('deadline', '>=', now()->toDateString());
        });
    }

    public function isOpen(): bool
    {
        return $this->is_published && ($this->deadline === null || $this->deadline->endOfDay()->isFuture());
    }

    /**
     * @return HasMany<JobApplication, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
