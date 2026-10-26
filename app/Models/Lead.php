<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadStage;
use App\Models\Concerns\BelongsToBranch;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'branch_id', 'name', 'mobile', 'source', 'campaign', 'project_id',
    'budget', 'stage', 'lost_reason', 'assignee_id', 'next_follow_up_at', 'created_by',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use BelongsToBranch, HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'stage', 'assignee_id', 'next_follow_up_at'])
            ->logOnlyDirty();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stage' => LeadStage::class,
            'budget' => 'decimal:2',
            'next_follow_up_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<LeadActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('created_at');
    }

    /**
     * @return HasMany<SiteVisit, $this>
     */
    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class);
    }
}
