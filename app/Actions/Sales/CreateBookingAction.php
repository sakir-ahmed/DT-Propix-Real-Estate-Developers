<?php

declare(strict_types=1);

namespace App\Actions\Sales;

use App\Actions\Documents\GenerateDocumentNumberAction;
use App\Actions\Inventory\ChangeUnitStatusAction;
use App\Actions\Inventory\PriceUnitAction;
use App\Enums\ScheduleRowKind;
use App\Enums\UnitStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Hold;
use App\Models\PaymentPlanTemplate;
use App\Models\ScheduleRow;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Books a unit for a customer inside one transaction:
 * row lock on the unit (no double booking), full price snapshot that later
 * price changes never alter, discount guard, and a payment schedule whose
 * rows must sum exactly to the net price.
 */
class CreateBookingAction
{
    public function __construct(
        private PriceUnitAction $priceUnit,
        private ChangeUnitStatusAction $changeStatus,
        private GenerateDocumentNumberAction $nextNumber,
    ) {}

    /**
     * @param  array{
     *     discount_amount?: string,
     *     discount_reason?: string|null,
     *     template_id?: int|null,
     *     manual_schedule?: list<array{label: string, kind?: string, due_date: string, amount: string}>|null
     * }  $options
     */
    public function handle(Unit $unit, Customer $customer, User $bookedBy, array $options = []): Booking
    {
        return DB::transaction(function () use ($unit, $customer, $bookedBy, $options): Booking {
            $locked = Unit::query()->whereKey($unit->id)->lockForUpdate()->firstOrFail();

            $activeHold = Hold::query()
                ->where('unit_id', $locked->id)
                ->whereNull('released_at')
                ->where('expires_at', '>', now())
                ->first();

            $bookable = $locked->status === UnitStatus::Available
                || ($locked->status === UnitStatus::OnHold && $activeHold !== null);

            if (! $bookable) {
                throw ValidationException::withMessages([
                    'unit' => __('units.not_available', ['status' => $locked->status->value]),
                ]);
            }

            $snapshot = $this->priceUnit->handle($locked);
            $gross = (float) $snapshot['total'];
            $discount = (float) ($options['discount_amount'] ?? 0);

            if ($discount > 0) {
                $this->assertDiscountAllowed($bookedBy, $discount, $gross, (string) ($options['discount_reason'] ?? ''));
            }

            if ($discount < 0 || $discount > $gross) {
                throw ValidationException::withMessages([
                    'discount_amount' => __('bookings.discount_invalid'),
                ]);
            }

            $net = $gross - $discount;

            $booking = Booking::query()->create([
                'booking_no' => $this->nextNumber->handle('BK'),
                'branch_id' => $bookedBy->branch_id,
                'active_unit_id' => $locked->id,
                'unit_id' => $locked->id,
                'customer_id' => $customer->id,
                'created_by' => $bookedBy->id,
                'status' => 'confirmed',
                'price_snapshot' => $snapshot,
                'discount_amount' => number_format($discount, 2, '.', ''),
                'discount_reason' => $options['discount_reason'] ?? null,
                'net_price' => number_format($net, 2, '.', ''),
            ]);

            $this->writeSchedule($booking, $net, $options);

            if ($activeHold !== null) {
                $activeHold->update(['released_at' => now()]);
            }

            $this->changeStatus->handle($locked, UnitStatus::Booked, $bookedBy, 'booking '.$booking->booking_no);

            return $booking->load(['scheduleRows', 'unit.project']);
        });
    }

    private function assertDiscountAllowed(User $user, float $discount, float $gross, string $reason): void
    {
        if (! $user->can('bookings.discount')) {
            throw ValidationException::withMessages([
                'discount_amount' => __('bookings.discount_not_allowed'),
            ]);
        }

        if ($reason === '') {
            throw ValidationException::withMessages([
                'discount_reason' => __('bookings.discount_reason_required'),
            ]);
        }

        $limit = (float) ($user->hasRole('owner') ? 100 : setting('sales.discount_percent_limit', 5));
        $percent = $gross > 0 ? ($discount / $gross) * 100 : 0;

        if ($percent > $limit) {
            throw ValidationException::withMessages([
                'discount_amount' => __('bookings.discount_over_limit', ['limit' => $limit]),
            ]);
        }
    }

    /**
     * @param  array{
     *     template_id?: int|null,
     *     manual_schedule?: list<array{label: string, kind?: string, due_date: string, amount: string}>|null
     * }  $options
     */
    private function writeSchedule(Booking $booking, float $net, array $options): void
    {
        $rows = $options['manual_schedule'] ?? null;

        if ($rows === null) {
            $templateId = $options['template_id'] ?? null;
            $template = $templateId !== null ? PaymentPlanTemplate::query()->find($templateId) : null;
            $template = $template ?? PaymentPlanTemplate::query()->orderBy('id')->first();

            if ($template === null) {
                throw ValidationException::withMessages([
                    'schedule' => __('bookings.template_missing'),
                ]);
            }

            $rows = collect($template->lines)
                ->map(fn (array $line): array => [
                    'label' => (string) $line['label'],
                    'kind' => (string) ($line['kind'] ?? ScheduleRowKind::Installment->value),
                    'due_date' => now()->addDays((int) ($line['due_days'] ?? 0))->toDateString(),
                    'amount' => number_format($net * ((float) $line['percent'] / 100), 2, '.', ''),
                ])
                ->all();
        }

        $sum = array_sum(array_map(fn (array $row): float => (float) $row['amount'], $rows));

        if (abs($sum - $net) > 0.01) {
            throw ValidationException::withMessages([
                'schedule' => __('bookings.schedule_mismatch', ['difference' => number_format(abs($sum - $net), 2, '.', '')]),
            ]);
        }

        foreach ($rows as $index => $row) {
            ScheduleRow::query()->create([
                'booking_id' => $booking->id,
                'version' => 1,
                'seq' => $index + 1,
                'label' => $row['label'],
                'kind' => $row['kind'] ?? ScheduleRowKind::Installment->value,
                'due_date' => $row['due_date'],
                'amount' => number_format((float) $row['amount'], 2, '.', ''),
            ]);
        }
    }
}
