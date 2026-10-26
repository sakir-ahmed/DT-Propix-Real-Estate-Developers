<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ScheduleRowKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['booking_id', 'version', 'seq', 'label', 'kind', 'due_date', 'amount'])]
class ScheduleRow extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'seq' => 'integer',
            'kind' => ScheduleRowKind::class,
            'due_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return HasMany<Allocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    public function paidAmount(): string
    {
        return $this->allocations()
            ->whereIn('receipt_id', function ($query): void {
                $query->select('id')->from('receipts')->where('status', 'posted');
            })->sum('amount');
    }
}
