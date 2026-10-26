<?php

declare(strict_types=1);

namespace App\Actions\Crm;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;

/**
 * Captures a lead from any source. A repeat enquiry by mobile adds an
 * activity to the existing lead instead of creating a duplicate.
 */
class CaptureLeadAction
{
    /**
     * @param  array{
     *     name: string,
     *     mobile: string,
     *     source?: string,
     *     campaign?: string|null,
     *     project_id?: int|null,
     *     budget?: string|null,
     *     note?: string|null,
     *     branch_id?: int|null,
     *     assignee_id?: int|null
     * }  $data
     * @return array{lead: Lead, created: bool}
     */
    public function handle(array $data, ?User $capturedBy = null): array
    {
        $mobile = normalize_bd_mobile($data['mobile']);
        $branchId = $data['branch_id'] ?? $capturedBy?->branch_id;

        // Duplicate detection spans branches: prefer this branch's lead, else any.
        $lead = Lead::query()
            ->withoutGlobalScope('branch')
            ->where('mobile', $mobile)
            ->orderByRaw('case when branch_id = ? then 0 else 1 end', [$branchId])
            ->first();

        if ($lead instanceof Lead) {
            LeadActivity::query()->create([
                'lead_id' => $lead->id,
                'user_id' => $capturedBy?->id,
                'type' => 'enquiry',
                'note' => $data['note'] ?? null,
            ]);

            return ['lead' => $lead, 'created' => false];
        }

        $lead = Lead::query()->create([
            'branch_id' => $data['branch_id'] ?? $capturedBy?->branch_id,
            'name' => $data['name'],
            'mobile' => $mobile,
            'source' => $data['source'] ?? 'manual',
            'campaign' => $data['campaign'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'budget' => $data['budget'] ?? null,
            'stage' => 'new',
            'assignee_id' => $data['assignee_id'] ?? $capturedBy?->id,
            'created_by' => $capturedBy?->id,
        ]);

        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $capturedBy?->id,
            'type' => 'created',
            'note' => $data['note'] ?? null,
        ]);

        return ['lead' => $lead, 'created' => true];
    }
}
