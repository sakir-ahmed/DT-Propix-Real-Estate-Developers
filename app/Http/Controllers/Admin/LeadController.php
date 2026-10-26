<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Crm\CaptureLeadAction;
use App\Enums\LeadStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreLeadRequest;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\SiteVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(Request $request): Response
    {
        $stage = $request->string('stage')->toString();

        $leads = Lead::query()
            ->with(['project:id,name', 'assignee:id,name'])
            ->when($stage !== '' && $stage !== 'all', fn ($query) => $query->where('stage', $stage))
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn (Lead $lead): array => [
                'id' => $lead->id,
                'name' => $lead->name,
                'mobile' => $lead->mobile,
                'stage' => $lead->stage->value,
                'source' => $lead->source,
                'project' => $lead->project?->name,
                'assignee' => $lead->assignee?->name,
                'next_follow_up_at' => $lead->next_follow_up_at?->toDateTimeString(),
                'created_at' => $lead->created_at->toDateTimeString(),
            ]);

        return Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
            'stage' => $stage !== '' ? $stage : 'all',
            'stages' => array_map(fn (LeadStage $s): string => $s->value, LeadStage::pipeline()),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $result = app(CaptureLeadAction::class)->handle($request->payload(), $request->user());

        return redirect()
            ->route('admin.leads.index')
            ->with('success', $result['created'] ? __('leads.created') : __('leads.merged'));
    }

    public function show(Lead $lead): Response
    {
        $lead->load(['project:id,name', 'assignee:id,name']);

        return Inertia::render('Admin/Leads/Show', [
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'mobile' => $lead->mobile,
                'stage' => $lead->stage->value,
                'source' => $lead->source,
                'budget' => $lead->budget,
                'project' => $lead->project?->name,
                'assignee' => $lead->assignee?->name,
                'next_follow_up_at' => $lead->next_follow_up_at?->toDateTimeString(),
            ],
            'activities' => $lead->activities()
                ->limit(50)
                ->get()
                ->map(fn (LeadActivity $activity): array => [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'outcome' => $activity->outcome,
                    'note' => $activity->note,
                    'created_at' => $activity->created_at->toDateTimeString(),
                ])->values(),
            'visits' => $lead->siteVisits()
                ->orderByDesc('scheduled_at')
                ->limit(20)
                ->get()
                ->map(fn (SiteVisit $visit): array => [
                    'id' => $visit->id,
                    'scheduled_at' => $visit->scheduled_at->toDateTimeString(),
                    'completed' => $visit->isCompleted(),
                    'feedback' => $visit->feedback,
                ]),
        ]);
    }

    public function stage(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'stage' => ['required', 'in:'.implode(',', array_map(fn (LeadStage $s): string => $s->value, LeadStage::pipeline()))],
            'lost_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $to = LeadStage::from($validated['stage']);

        if ($to === LeadStage::Lost && blank($validated['lost_reason'] ?? null)) {
            return back()->withErrors(['lost_reason' => __('leads.lost_reason_required')]);
        }

        $lead->update([
            'stage' => $to,
            'lost_reason' => $to === LeadStage::Lost ? $validated['lost_reason'] : null,
        ]);

        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()?->id,
            'type' => 'stage',
            'note' => $to->value,
        ]);

        return back()->with('success', __('leads.stage_updated'));
    }

    public function activity(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:note,call,whatsapp,sms,outcome'],
            'outcome' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'follow_up_at' => ['nullable', 'date'],
        ]);

        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $request->user()?->id,
            'type' => $validated['type'],
            'outcome' => $validated['outcome'] ?? null,
            'note' => $validated['note'] ?? null,
            'follow_up_at' => $validated['follow_up_at'] ?? null,
        ]);

        $lead->update(['next_follow_up_at' => $validated['follow_up_at'] ?? null]);

        return back()->with('success', __('leads.activity_logged'));
    }

    public function visit(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'scheduled_at' => ['required', 'date'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'complete' => ['nullable', 'boolean'],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $visit = SiteVisit::query()->create([
            'lead_id' => $lead->id,
            'project_id' => $validated['project_id'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'salesperson_id' => $request->user()?->id,
        ]);

        if (($validated['complete'] ?? false) === true) {
            $visit->update([
                'completed_at' => now(),
                'feedback' => $validated['feedback'] ?? null,
            ]);
        }

        return back()->with('success', __('leads.visit_scheduled'));
    }

    public function completeVisit(Request $request, SiteVisit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $visit->update([
            'completed_at' => now(),
            'feedback' => $validated['feedback'] ?? $visit->feedback,
        ]);

        return back()->with('success', __('leads.visit_completed'));
    }
}
