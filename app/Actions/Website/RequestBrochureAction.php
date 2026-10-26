<?php

declare(strict_types=1);

namespace App\Actions\Website;

use App\Models\Project;
use Illuminate\Support\Facades\URL;

/**
 * A gated brochure download: the request becomes a lead, and the visitor gets
 * a short-lived signed link to the private PDF.
 */
class RequestBrochureAction
{
    public function __construct(private readonly SubmitEnquiryAction $submitEnquiry) {}

    /**
     * @param  array{
     *     name: string,
     *     mobile: string,
     *     email?: string|null,
     *     submission_token: string,
     *     honeypot?: string|null,
     *     page?: string|null,
     *     utm?: array<string, string|null>
     * }  $data
     * @return array{download_url: string|null, spam: bool}
     */
    public function handle(Project $project, array $data): array
    {
        $result = $this->submitEnquiry->handle([
            ...$data,
            'kind' => 'brochure',
            'project_id' => $project->id,
            'project_name' => $project->name,
        ]);

        if ($result['spam'] || $project->brochure_path === null) {
            return ['download_url' => null, 'spam' => $result['spam']];
        }

        $minutes = (int) config('propix.website.brochure_link_minutes', 30);

        return [
            'download_url' => URL::temporarySignedRoute('web.brochures.download', now()->addMinutes($minutes), ['project' => $project->id]),
            'spam' => false,
        ];
    }
}
