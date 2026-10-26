<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Lead;
use App\Models\Receipt;
use App\Models\SiteVisit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The funnel numbers with the fixed definitions from prd.md section 5.
 * Every screen and report must use these so they always agree.
 */
class FunnelService
{
    /**
     * @return array{
     *     leads: int, site_visits: int, bookings: int,
     *     sales: string, collection: string, outstanding: string
     * }
     */
    public function summary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from?->startOfDay();
        $to = $to?->endOfDay();

        $leads = Lead::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->count();

        $visits = SiteVisit::query()
            ->whereNotNull('completed_at')
            ->when($from, fn ($q) => $q->where('completed_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('completed_at', '<=', $to))
            ->count();

        $bookings = Booking::query()
            ->where('status', 'confirmed')
            ->when($from, fn ($q) => $q->where('booked_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('booked_at', '<=', $to))
            ->count();

        $sales = Booking::query()
            ->whereNotNull('agreement_signed_at')
            ->when($from, fn ($q) => $q->where('agreement_signed_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('agreement_signed_at', '<=', $to))
            ->sum('net_price');

        $collection = Receipt::query()
            ->where('status', 'posted')
            ->when($from, fn ($q) => $q->where('received_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('received_at', '<=', $to))
            ->sum('amount');

        $outstanding = $this->outstanding();

        return [
            'leads' => $leads,
            'site_visits' => $visits,
            'bookings' => $bookings,
            'sales' => number_format((float) $sales, 2, '.', ''),
            'collection' => number_format((float) $collection, 2, '.', ''),
            'outstanding' => $outstanding,
        ];
    }

    /**
     * Overdue plus due today, at the as-of date (dashboard definition).
     */
    public function outstanding(?Carbon $asOf = null): string
    {
        $asOf = ($asOf ?? now())->startOfDay()->endOfDay();

        $due = DB::table('schedule_rows')
            ->join('bookings', 'bookings.id', '=', 'schedule_rows.booking_id')
            ->where('bookings.status', 'confirmed')
            ->where('schedule_rows.due_date', '<=', $asOf)
            ->sum('schedule_rows.amount');

        $paid = DB::table('allocations')
            ->join('receipts', 'receipts.id', '=', 'allocations.receipt_id')
            ->join('schedule_rows', 'schedule_rows.id', '=', 'allocations.schedule_row_id')
            ->where('receipts.status', 'posted')
            ->where('schedule_rows.due_date', '<=', $asOf)
            ->sum('allocations.amount');

        return number_format(max(0, (float) $due - (float) $paid), 2, '.', '');
    }
}
