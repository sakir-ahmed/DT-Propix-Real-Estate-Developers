<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Website\SubmitEnquiryAction;
use App\Http\Requests\Web\StoreEnquiryRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class EnquiryController extends WebController
{
    /**
     * Enquiry, callback and site visit requests from any page. The reply is the
     * same whether the lead is new, merged or dropped as spam.
     */
    public function store(StoreEnquiryRequest $request, SubmitEnquiryAction $submit): RedirectResponse
    {
        $payload = $request->payload();
        $project = $payload['project_id'] !== null ? Project::query()->find($payload['project_id']) : null;

        $submit->handle([
            ...$payload,
            'project_name' => $project?->name,
        ]);

        return back()->with('success', __('website.form_success_'.$payload['kind']));
    }
}
