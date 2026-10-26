<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Collections\RecordCollectionAction;
use App\Actions\Inventory\ChangeUnitStatusAction;
use App\Actions\Inventory\PriceUnitAction;
use App\Actions\Sales\CreateBookingAction;
use App\Enums\UnitStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreBookingRequest;
use App\Http\Requests\Sales\StoreReceiptRequest;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\PaymentPlanTemplate;
use App\Models\Project;
use App\Models\Unit;
use App\Services\BookingSummaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(): Response
    {
        $bookings = Booking::query()
            ->with(['unit:id,number,project_id', 'unit.project:id,name', 'customer:id,name,customer_code'])
            ->orderByDesc('booked_at')
            ->limit(200)
            ->get()
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'customer' => $booking->customer->name,
                'project' => $booking->unit->project?->name,
                'unit' => $booking->unit->number,
                'net_price' => $booking->net_price,
                'status' => $booking->status,
                'agreement_signed' => $booking->agreement_signed_at !== null,
                'booked_at' => $booking->booked_at->toDateString(),
            ]);

        return Inertia::render('Admin/Bookings/Index', ['bookings' => $bookings]);
    }

    public function create(Request $request): Response
    {
        $canViewPrice = $request->user()?->can('units.view-price') ?? false;
        $customerId = $request->integer('customer');

        return Inertia::render('Admin/Bookings/Create', [
            'customers' => Customer::query()->orderBy('name')->get(['id', 'name', 'customer_code', 'mobile'])
                ->map(fn (Customer $c): array => ['id' => $c->id, 'label' => $c->name.' ('.$c->customer_code.')'])->all(),
            'selectedCustomerId' => $customerId ?: null,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name'])
                ->map(fn (Project $p): array => ['id' => $p->id, 'name' => $p->name])->all(),
            'templates' => PaymentPlanTemplate::query()->orderBy('name')->get(['id', 'name'])->all(),
            'canDiscount' => $request->user()?->can('bookings.discount') ?? false,
            'canViewPrice' => $canViewPrice,
        ]);
    }

    public function price(Request $request): array
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'integer', 'exists:units,id'],
        ]);

        abort_unless($request->user()?->can('units.view-price'), 403);

        return app(PriceUnitAction::class)->handle(Unit::query()->findOrFail($validated['unit_id']));
    }

    public function units(Request $request): array
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        return Unit::query()
            ->where('project_id', $validated['project_id'])
            ->available()
            ->orderBy('number')
            ->get(['id', 'number', 'size_sqft', 'base_rate'])
            ->map(fn (Unit $unit): array => [
                'id' => $unit->id,
                'number' => $unit->number,
                'size_sqft' => $unit->size_sqft,
                'rate' => $unit->base_rate,
                'price' => $request->user()?->can('units.view-price')
                    ? app(PriceUnitAction::class)->handle($unit)['total']
                    : null,
            ])
            ->all();
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = app(CreateBookingAction::class)->handle(
            Unit::query()->findOrFail($request->integer('unit_id')),
            Customer::query()->findOrFail($request->integer('customer_id')),
            $request->user(),
            $request->options(),
        );

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', __('bookings.created', ['number' => $booking->booking_no]));
    }

    public function show(Request $request, Booking $booking): Response
    {
        $booking->load(['unit.project', 'customer', 'receipts.receivedBy:id,name']);
        $summary = app(BookingSummaryService::class);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'booking_no' => $booking->booking_no,
                'status' => $booking->status,
                'net_price' => $booking->net_price,
                'discount_amount' => $booking->discount_amount,
                'price_snapshot' => $booking->price_snapshot,
                'agreement_signed' => $booking->agreement_signed_at !== null,
                'booked_at' => $booking->booked_at->toDateTimeString(),
                'unit' => $booking->unit->number,
                'project' => $booking->unit->project->name,
                'customer' => [
                    'id' => $booking->customer->id,
                    'name' => $booking->customer->name,
                    'customer_code' => $booking->customer->customer_code,
                    'mobile' => $booking->customer->mobile,
                ],
            ],
            'schedule' => $summary->scheduleWithPaid($booking),
            'totals' => $summary->totals($booking),
            'receipts' => $booking->receipts->map(fn ($r): array => [
                'id' => $r->id,
                'receipt_no' => $r->receipt_no,
                'amount' => $r->amount,
                'method' => $r->method->value,
                'status' => $r->status,
                'received_at' => $r->received_at->toDateString(),
                'received_by' => $r->receivedBy?->name,
            ])->all(),
            'canCollect' => $request->user()?->can('collections.record') ?? false,
        ]);
    }

    public function signAgreement(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($request->user()?->can('bookings.view'), 403);

        if ($booking->agreement_signed_at === null && ! $booking->isCancelled()) {
            $booking->update(['agreement_signed_at' => now()]);

            app(ChangeUnitStatusAction::class)->handle(
                $booking->unit,
                UnitStatus::Sold,
                $request->user(),
                'agreement '.$booking->booking_no,
            );
        }

        return back()->with('success', __('bookings.agreement_signed'));
    }

    public function collect(StoreReceiptRequest $request, Booking $booking): RedirectResponse
    {
        $result = app(RecordCollectionAction::class)->handle($booking, $request->user(), $request->payload());

        $message = __('collections.recorded', ['number' => $result['receipt']->receipt_no]);

        if ((float) $result['advance'] > 0) {
            $message .= ' '.__('collections.advance_created', ['amount' => $result['advance']]);
        }

        return back()->with('success', $message);
    }
}
