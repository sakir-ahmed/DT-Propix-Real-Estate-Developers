<?php

declare(strict_types=1);

namespace App\Http\Requests\Crm;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('leads.manage');
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'source' => ['nullable', 'string', 'max:50'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array{name: string, mobile: string, source: string, project_id: int|null, budget: string|null, note: string|null}
     */
    public function payload(): array
    {
        return [
            'name' => $this->string('name')->toString(),
            'mobile' => $this->string('mobile')->toString(),
            'source' => $this->string('source')->toString() ?: 'manual',
            'project_id' => $this->integer('project_id') ?: null,
            'budget' => $this->input('budget') !== null ? (string) $this->input('budget') : null,
            'note' => $this->input('note'),
        ];
    }

    public function capturedBy(): ?User
    {
        return $this->user();
    }
}
