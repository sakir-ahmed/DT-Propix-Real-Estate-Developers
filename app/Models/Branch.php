<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable(['name', 'code', 'address', 'is_head_office'])]
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory, LogsActivity;

    protected static function booted(): void
    {
        static::addGlobalScope('own-branch', function (Builder $builder): void {
            $guard = auth()->guard();

            if (! $guard->hasUser()) {
                return;
            }

            $user = $guard->user();

            if (! $user instanceof User || $user->is_super_admin) {
                return;
            }

            if ($user->canAccessAllBranches()) {
                return;
            }

            $builder->where($builder->getModel()->qualifyColumn('id'), $user->branch_id);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_head_office' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'address', 'is_head_office'])
            ->logOnlyDirty();
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
