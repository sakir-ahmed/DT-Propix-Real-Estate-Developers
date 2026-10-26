<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Receipt;
use App\Services\BookingSummaryService;
use Inertia\Inertia;
use Inertia\Response;

class CollectionController extends Controller
{
    public function index(): Response
    {
        $summary = app(BookingSummaryService::class);

        $bookings = Booking::query()
            ->with(['customer:id,name,customer_code', 'unit:id,number'])
            ->where('status', 'confirmed')
            ->orderByDesc('booked_at')
            ->limit(100)
            ->get()
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'customer' => $booking->customer->name,
                'unit' => $booking->unit->number,
                'net_price' => $booking->net_price,
                'totals' => $summary->totals($booking),
            ]);

        $recentReceipts = Receipt::query()
            ->with(['booking:id,booking_no', 'customer:id,name'])
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->map(fn (Receipt $receipt): array => [
                'id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'booking_no' => $receipt->booking->booking_no,
                'customer' => $receipt->customer->name,
                'amount' => $receipt->amount,
                'method' => $receipt->method->value,
                'status' => $receipt->status,
                'received_at' => $receipt->received_at->toDateString(),
            ]);

        return Inertia::render('Admin/Collections/Index', [
            'bookings' => $bookings,
            'receipts' => $recentReceipts,
        ]);
    }
}
