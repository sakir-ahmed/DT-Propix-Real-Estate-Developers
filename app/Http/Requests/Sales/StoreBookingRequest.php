<?php

declare(strict_types=1);

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('bookings.view');
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
            'template_id' => ['nullable', 'integer', 'exists:payment_plan_templates,id'],
        ];
    }

    /**
     * @return array{discount_amount: string, discount_reason: string|null, template_id: int|null, manual_schedule: null}
     */
    public function options(): array
    {
        return [
            'discount_amount' => (string) ($this->input('discount_amount') ?? 0),
            'discount_reason' => $this->input('discount_reason'),
            'template_id' => $this->integer('template_id') ?: null,
            'manual_schedule' => null,
        ];
    }
}
