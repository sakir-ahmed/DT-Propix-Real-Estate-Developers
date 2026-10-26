<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEnquiryRequest extends FormRequest
{
    /** @var list<string> */
    public const array KINDS = ['enquiry', 'callback', 'site_visit', 'contact'];

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
            'kind' => ['required', Rule::in(self::KINDS)],
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->where('is_published', true)],
            'message' => ['nullable', 'string', 'max:2000'],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
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
            'project_id' => __('website.form_project'),
            'message' => __('website.form_message'),
            'preferred_date' => __('website.form_preferred_date'),
            'consent' => __('website.form_consent_short'),
        ];
    }

    /**
     * @return array{
     *     kind: string,
     *     name: string,
     *     mobile: string,
     *     email: string|null,
     *     project_id: int|null,
     *     message: string|null,
     *     preferred_date: string|null,
     *     submission_token: string,
     *     honeypot: string|null,
     *     page: string|null,
     *     utm: array<string, string|null>
     * }
     */
    public function payload(): array
    {
        return [
            'kind' => $this->string('kind')->toString(),
            'name' => trim($this->string('name')->toString()),
            'mobile' => $this->string('mobile')->toString(),
            'email' => $this->input('email'),
            'project_id' => $this->integer('project_id') ?: null,
            'message' => $this->input('message'),
            'preferred_date' => $this->input('preferred_date'),
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
