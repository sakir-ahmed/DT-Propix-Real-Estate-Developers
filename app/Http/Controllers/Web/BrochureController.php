<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Website\RequestBrochureAction;
use App\Http\Requests\Web\StoreBrochureRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BrochureController extends WebController
{
    public function store(StoreBrochureRequest $request, RequestBrochureAction $requestBrochure): RedirectResponse
    {
        $payload = $request->payload();
        $project = Project::query()->published()->findOrFail($request->integer('project_id'));

        $result = $requestBrochure->handle($project, $payload);

        return back()
            ->with('success', __('website.form_success_brochure'))
            ->with('download_url', $result['download_url']);
    }

    /**
     * Signed, short-lived download of a gated brochure from the private disk.
     */
    public function download(Project $project): StreamedResponse
    {
        abort_unless($project->is_published && $project->brochure_path !== null, 404);
        abort_unless(Storage::disk('local')->exists($project->brochure_path), 404);

        return Storage::disk('local')->download($project->brochure_path, $project->slug.'-brochure.pdf');
    }
}
