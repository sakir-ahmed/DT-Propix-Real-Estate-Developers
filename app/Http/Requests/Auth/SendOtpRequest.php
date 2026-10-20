<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('mobile')) {
            $this->merge([
                'mobile' => normalize_bd_mobile($this->string('mobile')->toString()),
            ]);
        }
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^8801[3-9]\d{8}$/'],
        ];
    }
}
