<?php

declare(strict_types=1);

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobApplicationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'cover_letter' => ['nullable', 'string', 'max:3000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'consent' => ['accepted'],
            'company_website' => ['nullable', 'string', 'max:255'],
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
            'cover_letter' => __('website.careers_cover_letter'),
            'cv' => __('website.careers_cv'),
            'consent' => __('website.form_consent_short'),
        ];
    }

    /**
     * @return array{name: string, mobile: string, email: string|null, cover_letter: string|null}
     */
    public function payload(): array
    {
        return [
            'name' => trim($this->string('name')->toString()),
            'mobile' => $this->string('mobile')->toString(),
            'email' => $this->input('email'),
            'cover_letter' => $this->input('cover_letter'),
        ];
    }

    public function isSpam(): bool
    {
        $honeypot = $this->input('company_website');

        return is_string($honeypot) && trim($honeypot) !== '';
    }
}
