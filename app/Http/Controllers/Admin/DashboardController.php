<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Lead;
use App\Services\BookingSummaryService;
use App\Services\FunnelService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $from = $request->date('from')?->startOfDay();
        $to = $request->date('to')?->endOfDay();
        $funnel = app(FunnelService::class)->summary($from, $to);

        $todayFollowUps = Lead::query()
            ->whereNotNull('next_follow_up_at')
            ->where('next_follow_up_at', '<=', now()->endOfDay())
            ->whereNotIn('stage', ['booked', 'lost'])
            ->count();

        $overdueBookings = Booking::query()
            ->with(['customer:id,name', 'unit:id,number'])
            ->where('status', 'confirmed')
            ->limit(50)
            ->get()
            ->filter(fn (Booking $booking): bool => (float) app(BookingSummaryService::class)->totals($booking)['overdue'] > 0)
            ->take(10)
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'customer' => $booking->customer->name,
                'unit' => $booking->unit->number,
                'overdue' => app(BookingSummaryService::class)->totals($booking)['overdue'],
            ])
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'funnel' => $funnel,
            'period' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'todayFollowUps' => $todayFollowUps,
            'overdueBookings' => $overdueBookings,
        ]);
    }
}
