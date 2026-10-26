<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToBranch;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

#[Fillable([
    'booking_no', 'branch_id', 'unit_id', 'customer_id', 'lead_id', 'created_by',
    'status', 'price_snapshot', 'discount_amount', 'discount_reason', 'net_price',
    'agreement_signed_at', 'cancelled_at', 'cancellation_reason', 'active_unit_id',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use BelongsToBranch, HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['booking_no', 'status', 'net_price', 'discount_amount'])
            ->logOnlyDirty();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_snapshot' => 'array',
            'net_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'booked_at' => 'datetime',
            'agreement_signed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<ScheduleRow, $this>
     */
    public function scheduleRows(): HasMany
    {
        return $this->hasMany(ScheduleRow::class)->orderBy('seq');
    }

    /**
     * @return HasMany<Receipt, $this>
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class)->latest('received_at');
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null || $this->status === 'cancelled';
    }
}
