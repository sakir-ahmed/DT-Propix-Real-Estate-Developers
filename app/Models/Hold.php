<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['unit_id', 'held_by', 'token_amount', 'expires_at', 'released_at'])]
class Hold extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'token_amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function isActive(): bool
    {
        return $this->released_at === null && $this->expires_at->isFuture();
    }
}
