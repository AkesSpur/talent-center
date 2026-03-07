<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\EvaluateApplicationRequest;
use App\Models\Application;
use App\Models\Contest;
use App\Notifications\ApplicationEvaluated;
use App\Services\ActionLogService;
use App\Services\DiplomaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function __construct(private readonly DiplomaService $diplomaService) {}

    /**
     * GET /admin/contests/{contest}/evaluate
     * Admin override view — shows all applications with editable forms regardless of contest status.
     */
    public function show(Contest $contest): View
    {
        $contest->load(['organization', 'categories']);

        $applications = $contest->applications()
            ->with(['user', 'category', 'evaluatedBy', 'diploma'])
            ->orderBy('category_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($app) => $app->category_id ?? 0);

        $totalCount     = $contest->applications()->count();
        $evaluatedCount = $contest->applications()
            ->where('status', '!=', ApplicationStatus::New->value)
            ->count();

        return view('admin.evaluation.show', compact(
            'contest', 'applications', 'totalCount', 'evaluatedCount'
        ));
    }

    /**
     * POST /admin/applications/{application}/evaluate
     * Admin override — works for any contest status including archive.
     */
    public function evaluate(EvaluateApplicationRequest $request, Application $application): RedirectResponse
    {
        $oldStatus = $application->status;
        $newStatus = ApplicationStatus::from($request->input('status'));

        $application->update([
            'status'           => $newStatus->value,
            'rejection_reason' => $newStatus === ApplicationStatus::Rejected
                ? $request->input('rejection_reason')
                : null,
            'evaluated_by'     => $request->user()->id,
            'evaluated_at'     => now(),
        ]);

        // Regenerate diploma if a place was assigned
        $placedStatuses = [
            ApplicationStatus::Participant,
            ApplicationStatus::Place1,
            ApplicationStatus::Place2,
            ApplicationStatus::Place3,
        ];

        if (in_array($newStatus, $placedStatuses, true)) {
            $this->diplomaService->generateForApplication($application);
        }

        // Reload for notification
        $application->load(['user', 'diploma', 'contest']);
        $application->user->notify(new ApplicationEvaluated($application));

        ActionLogService::log('application.admin_override', $application, [
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
        ]);

        return redirect()->route('admin.evaluation.show', $application->contest)
            ->with('status', 'application-evaluated');
    }

    /**
     * POST /admin/contests/{contest}/regenerate-diplomas
     * Re-trigger diploma generation for all eligible applications in a contest.
     */
    public function regenerateDiplomas(Request $request, Contest $contest): RedirectResponse
    {
        $count = $this->diplomaService->generateForContest($contest);

        ActionLogService::log('contest.diplomas_regenerated', $contest, [
            'count' => $count,
            'by'    => $request->user()->id,
        ]);

        return redirect()->route('admin.evaluation.show', $contest)
            ->with('status', 'diplomas-regenerated');
    }
}
