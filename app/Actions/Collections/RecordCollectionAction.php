<?php

declare(strict_types=1);

namespace App\Actions\Collections;

use App\Actions\Documents\GenerateDocumentNumberAction;
use App\Enums\PaymentMethod;
use App\Models\Allocation;
use App\Models\Booking;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Records a collection against a booking: posts a numbered receipt, allocates
 * to the earliest due schedule rows (never above their due amount), and leaves
 * any remainder as an advance. Cheques stay provisional until cleared.
 */
class RecordCollectionAction
{
    public function __construct(private GenerateDocumentNumberAction $nextNumber) {}

    /**
     * @param  array{
     *     method: string,
     *     amount: string,
     *     reference?: string|null,
     *     received_at?: string|null,
     *     schedule_row_ids?: list<int>|null
     * }  $data
     * @return array{receipt: Receipt, advance: string}
     */
    public function handle(Booking $booking, User $receivedBy, array $data): array
    {
        return DB::transaction(function () use ($booking, $receivedBy, $data): array {
            $fresh = Booking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            if ($fresh->isCancelled()) {
                throw ValidationException::withMessages([
                    'booking' => __('bookings.cancelled'),
                ]);
            }

            $amount = round((float) $data['amount'], 2);

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => __('collections.amount_invalid'),
                ]);
            }

            $receipt = Receipt::query()->create([
                'receipt_no' => $this->nextNumber->handle('MR'),
                'booking_id' => $fresh->id,
                'branch_id' => $receivedBy->branch_id,
                'customer_id' => $fresh->customer_id,
                'received_by' => $receivedBy->id,
                'method' => PaymentMethod::from($data['method']),
                'reference' => $data['reference'] ?? null,
                'amount' => number_format($amount, 2, '.', ''),
                'received_at' => $data['received_at'] ?? now()->toDateString(),
                'status' => $data['method'] === PaymentMethod::Cheque->value ? 'provisional' : 'posted',
            ]);

            $advance = $this->allocate($receipt, $fresh, $amount, $data['schedule_row_ids'] ?? null);

            return ['receipt' => $receipt, 'advance' => $advance];
        });
    }

    /**
     * @param  list<int>|null  $chosenRowIds
     */
    private function allocate(Receipt $receipt, Booking $booking, float $amount, ?array $chosenRowIds): string
    {
        $rows = $booking->scheduleRows()
            ->orderBy('due_date')
            ->orderBy('seq')
            ->get();

        if ($chosenRowIds !== null && $chosenRowIds !== []) {
            $rows = $rows->filter(fn ($row): bool => in_array($row->id, $chosenRowIds, true))->values();
        }

        $remaining = $amount;

        foreach ($rows as $row) {
            if ($remaining <= 0) {
                break;
            }

            $paid = (float) $this->paidFor($booking, $row->id);
            $due = round((float) $row->amount - $paid, 2);

            if ($due <= 0) {
                continue;
            }

            $part = min($due, $remaining);

            Allocation::query()->create([
                'receipt_id' => $receipt->id,
                'schedule_row_id' => $row->id,
                'amount' => number_format($part, 2, '.', ''),
            ]);

            $remaining = round($remaining - $part, 2);
        }

        if ($remaining > 0 && $chosenRowIds !== null && $chosenRowIds !== []) {
            throw ValidationException::withMessages([
                'amount' => __('collections.allocation_exceeds_due'),
            ]);
        }

        return number_format($remaining, 2, '.', '');
    }

    private function paidFor(Booking $booking, int $rowId): string
    {
        $sum = Allocation::query()
            ->where('schedule_row_id', $rowId)
            ->whereIn('receipt_id', function ($query) use ($booking): void {
                $query->select('id')->from('receipts')
                    ->where('booking_id', $booking->id)
                    ->where('status', 'posted');
            })
            ->sum('amount');

        return number_format((float) $sum, 2, '.', '');
    }
}
