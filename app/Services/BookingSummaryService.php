<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\ScheduleRow;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Paid, due, overdue and upcoming figures per booking. Due amounts are always
 * derived from allocations, never typed (architecture.md section 14).
 */
class BookingSummaryService
{
    /**
     * @return list<array{id: int, seq: int, label: string, kind: string, due_date: string, amount: string, paid: string, due: string}>
     */
    public function scheduleWithPaid(Booking $booking): array
    {
        $paidByRow = $this->paidByRow($booking);

        return $booking->scheduleRows()
            ->orderBy('seq')
            ->get()
            ->map(fn (ScheduleRow $row): array => [
                'id' => $row->id,
                'seq' => $row->seq,
                'label' => $row->label,
                'kind' => $row->kind->value,
                'due_date' => $row->due_date->toDateString(),
                'amount' => $row->amount,
                'paid' => $paidByRow[$row->id] ?? '0.00',
                'due' => number_format(max(0, (float) $row->amount - (float) ($paidByRow[$row->id] ?? 0)), 2, '.', ''),
            ])
            ->all();
    }

    /**
     * @return array{overdue: string, upcoming: string, collected: string, advance: string}
     */
    public function totals(Booking $booking, ?Carbon $asOf = null): array
    {
        $asOf = ($asOf ?? now())->startOfDay();
        $paidByRow = $this->paidByRow($booking);

        $overdue = 0.0;
        $upcoming = 0.0;

        foreach ($booking->scheduleRows()->orderBy('seq')->get() as $row) {
            $due = (float) $row->amount - (float) ($paidByRow[$row->id] ?? 0);

            if ($due <= 0) {
                continue;
            }

            if ($row->due_date->startOfDay()->lessThanOrEqualTo($asOf)) {
                $overdue += $due;
            } else {
                $upcoming += $due;
            }
        }

        $collected = (float) $booking->receipts()->where('status', 'posted')->sum('amount');
        $allocated = (float) $this->allocatedSum($booking);
        $advance = max(0, $collected - $allocated);

        return [
            'overdue' => number_format($overdue, 2, '.', ''),
            'upcoming' => number_format($upcoming, 2, '.', ''),
            'collected' => number_format($collected, 2, '.', ''),
            'advance' => number_format($advance, 2, '.', ''),
        ];
    }

    private function allocatedSum(Booking $booking): string
    {
        $sum = DB::table('allocations')
            ->join('receipts', 'receipts.id', '=', 'allocations.receipt_id')
            ->where('receipts.booking_id', $booking->id)
            ->where('receipts.status', 'posted')
            ->sum('allocations.amount');

        return number_format((float) $sum, 2, '.', '');
    }

    /**
     * @return array<int, string>
     */
    private function paidByRow(Booking $booking): array
    {
        return DB::table('allocations')
            ->join('receipts', 'receipts.id', '=', 'allocations.receipt_id')
            ->where('receipts.booking_id', $booking->id)
            ->where('receipts.status', 'posted')
            ->groupBy('allocations.schedule_row_id')
            ->selectRaw('allocations.schedule_row_id, sum(allocations.amount) as paid')
            ->pluck('paid', 'schedule_row_id')
            ->map(fn ($paid): string => number_format((float) $paid, 2, '.', ''))
            ->all();
    }
}
