<?php

declare(strict_types=1);

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StoreReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('collections.record');
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'method' => ['required', 'in:cash,cheque,bank,mobile,card'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:100'],
            'received_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array{method: string, amount: string, reference: string|null, received_at: string|null, schedule_row_ids: null}
     */
    public function payload(): array
    {
        return [
            'method' => $this->string('method')->toString(),
            'amount' => (string) $this->input('amount'),
            'reference' => $this->input('reference'),
            'received_at' => $this->input('received_at'),
            'schedule_row_ids' => null,
        ];
    }
}
