<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Model
 *
 * @property int|null $branch_id
 */
trait BelongsToBranch
{
    public static function bootBelongsToBranch(): void
    {
        static::addGlobalScope('branch', function (Builder $builder): void {
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

            if ($user->branch_id === null) {
                return;
            }

            $builder->where($builder->getModel()->qualifyColumn('branch_id'), $user->branch_id);
        });
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
