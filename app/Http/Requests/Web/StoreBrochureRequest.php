<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrochureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('mobile')) {
            $this->merge(['mobile' => preg_replace('/[\s\-()]+/', '', $this->string('mobile')->toString())]);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->where('is_published', true)],
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'consent' => ['accepted'],
            'submission_token' => ['required', 'string', 'max:64'],
            'company_website' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'string', 'max:255'],
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('website.form_name'),
            'mobile' => __('website.form_mobile'),
            'email' => __('website.form_email'),
            'consent' => __('website.form_consent_short'),
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     mobile: string,
     *     email: string|null,
     *     submission_token: string,
     *     honeypot: string|null,
     *     page: string|null,
     *     utm: array<string, string|null>
     * }
     */
    public function payload(): array
    {
        return [
            'name' => trim($this->string('name')->toString()),
            'mobile' => $this->string('mobile')->toString(),
            'email' => $this->input('email'),
            'submission_token' => $this->string('submission_token')->toString(),
            'honeypot' => $this->input('company_website'),
            'page' => $this->input('page'),
            'utm' => [
                'utm_source' => $this->input('utm_source'),
                'utm_medium' => $this->input('utm_medium'),
                'utm_campaign' => $this->input('utm_campaign'),
            ],
        ];
    }
}
