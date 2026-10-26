<?php

declare(strict_types=1);

namespace App\Actions\Website;

use App\Actions\Crm\CaptureLeadAction;
use App\Models\Branch;
use App\Models\Lead;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Every public form (enquiry, callback, site visit, brochure) lands here. It
 * drops honeypot submissions, is idempotent per submission token, records UTM
 * data, and hands the lead to CaptureLeadAction so a repeat enquiry by mobile
 * adds an activity instead of a duplicate. Callers always answer with the same
 * generic success message so the form never reveals whether a mobile exists.
 */
class SubmitEnquiryAction
{
    /** @var array<string, string> */
    private const array KIND_LABELS = [
        'enquiry' => 'Website enquiry',
        'callback' => 'Callback request',
        'site_visit' => 'Site visit request',
        'brochure' => 'Brochure request',
        'contact' => 'Contact form',
    ];

    public function __construct(private readonly CaptureLeadAction $captureLead) {}

    /**
     * @param  array{
     *     kind: string,
     *     name: string,
     *     mobile: string,
     *     email?: string|null,
     *     project_id?: int|null,
     *     project_name?: string|null,
     *     message?: string|null,
     *     preferred_date?: string|null,
     *     submission_token: string,
     *     honeypot?: string|null,
     *     page?: string|null,
     *     utm?: array<string, string|null>
     * }  $data
     * @return array{accepted: bool, duplicate: bool, spam: bool, lead: Lead|null}
     */
    public function handle(array $data): array
    {
        $honeypot = $data['honeypot'] ?? null;

        if (is_string($honeypot) && trim($honeypot) !== '') {
            Log::info('Website form dropped by honeypot.', ['kind' => $data['kind']]);

            return ['accepted' => true, 'duplicate' => false, 'spam' => true, 'lead' => null];
        }

        if (! $this->claimSubmissionToken($data['submission_token'])) {
            return ['accepted' => true, 'duplicate' => true, 'spam' => false, 'lead' => null];
        }

        $result = $this->captureLead->handle([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'source' => 'website',
            'campaign' => $this->campaign($data['utm'] ?? []),
            'project_id' => $data['project_id'] ?? null,
            'note' => $this->note($data),
            'branch_id' => $this->headOfficeId(),
        ]);

        Log::info('Website enquiry captured.', [
            'lead_id' => $result['lead']->id,
            'kind' => $data['kind'],
            'created' => $result['created'],
        ]);

        return ['accepted' => true, 'duplicate' => false, 'spam' => false, 'lead' => $result['lead']];
    }

    private function claimSubmissionToken(string $token): bool
    {
        $hours = (int) config('propix.website.submission_token_hours', 24);

        return Cache::add('website:submission:'.$token, true, now()->addHours($hours));
    }

    /**
     * @param  array<string, string|null>  $utm
     */
    private function campaign(array $utm): ?string
    {
        $campaign = $utm['utm_campaign'] ?? null;
        $source = $utm['utm_source'] ?? null;
        $value = is_string($campaign) && $campaign !== '' ? $campaign : $source;

        return is_string($value) && $value !== '' ? mb_substr($value, 0, 120) : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function note(array $data): string
    {
        $lines = [self::KIND_LABELS[$data['kind']] ?? 'Website form'];

        if (! empty($data['project_name'])) {
            $lines[] = 'Project: '.$data['project_name'];
        }

        if (! empty($data['preferred_date'])) {
            $lines[] = 'Preferred date: '.$data['preferred_date'];
        }

        if (! empty($data['email'])) {
            $lines[] = 'Email: '.$data['email'];
        }

        if (! empty($data['message'])) {
            $lines[] = 'Message: '.trim((string) $data['message']);
        }

        $utm = array_filter($data['utm'] ?? [], fn (mixed $value): bool => is_string($value) && $value !== '');

        if ($utm !== []) {
            $lines[] = 'UTM: '.implode(', ', array_map(fn (string $key, string $value): string => $key.'='.$value, array_keys($utm), $utm));
        }

        if (! empty($data['page'])) {
            $lines[] = 'Page: '.$data['page'];
        }

        return mb_substr(implode("\n", $lines), 0, 4000);
    }

    private function headOfficeId(): ?int
    {
        $id = Branch::query()->withoutGlobalScopes()->where('is_head_office', true)->value('id');

        return is_numeric($id) ? (int) $id : null;
    }
}
