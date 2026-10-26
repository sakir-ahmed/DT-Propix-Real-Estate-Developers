<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Documents\GenerateDocumentNumberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $customers = Customer::query()
            ->withCount('bookings')
            ->orderBy('name')
            ->limit(200)
            ->get()
            ->map(fn (Customer $customer): array => [
                'id' => $customer->id,
                'customer_code' => $customer->customer_code,
                'name' => $customer->name,
                'mobile' => $customer->mobile,
                'email' => $customer->email,
                'bookings_count' => $customer->bookings_count,
            ]);

        return Inertia::render('Admin/Customers/Index', ['customers' => $customers]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::query()->create([
            'branch_id' => $request->user()?->branch_id,
            'customer_code' => app(GenerateDocumentNumberAction::class)->handle('C'),
            'name' => $request->string('name')->toString(),
            'mobile' => normalize_bd_mobile($request->string('mobile')->toString()),
            'email' => $request->input('email'),
            'nid' => $request->input('nid'),
            'address' => $request->input('address'),
        ]);

        return redirect()
            ->route('admin.bookings.create', ['customer' => $customer->id])
            ->with('success', __('customers.created'));
    }
}
