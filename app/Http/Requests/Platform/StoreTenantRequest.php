<?php

declare(strict_types=1);

namespace App\Http\Requests\Platform;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->is_super_admin;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('owner_mobile')) {
            $this->merge([
                'owner_mobile' => normalize_bd_mobile($this->string('owner_mobile')->toString()),
            ]);
        }

        if ($this->filled('slug')) {
            $this->merge([
                'slug' => strtolower($this->string('slug')->toString()),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var array<string, mixed> $plans */
        $plans = config('propix.plans');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:60', Rule::unique('tenants', 'slug')],
            'plan' => ['required', 'string', Rule::in(array_keys($plans))],
            'trial_ends_at' => ['nullable', 'date'],
            'message_credits' => ['nullable', 'integer', 'min:0'],
            'limit_overrides' => ['nullable', 'array'],
            'limit_overrides.projects' => ['nullable', 'integer', 'min:0'],
            'limit_overrides.units' => ['nullable', 'integer', 'min:0'],
            'limit_overrides.users' => ['nullable', 'integer', 'min:0'],
            'limit_overrides.storage_mb' => ['nullable', 'integer', 'min:0'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_username' => ['required', 'string', 'alpha_dash', 'max:60'],
            'owner_email' => ['required', 'email', 'max:255'],
            'owner_mobile' => ['required', 'string', 'regex:/^8801[3-9]\d{8}$/'],
            'owner_password' => ['required', 'string', 'min:8'],
        ];
    }
}
