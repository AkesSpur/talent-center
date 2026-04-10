<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\ContestStatus;
use App\Http\Requests\EvaluateApplicationRequest;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Notifications\ApplicationEvaluated;
use App\Notifications\ContestFinalized;
use App\Services\ActionLogService;
use App\Services\DiplomaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function __construct(private readonly DiplomaService $diplomaService) {}

    /**
     * GET /organizations/{organization}/evaluate
     * Jury dashboard: list of contests in evaluation status for this org.
     */
    public function index(Request $request, Organization $organization): View
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->canInOrg('evaluate', $organization)) {
            abort(403);
        }

        $evaluationContests = $organization->contests()
            ->where(function ($query) {
                $query->where('status', ContestStatus::Evaluation->value)
                    ->orWhere(fn ($q) => $q->where('status', ContestStatus::Accepting->value)
                        ->where('is_permanent', true));
            })
            ->withCount([
                'applications',
                'applications as evaluated_count' => fn ($q) => $q->where('status', '!=', ApplicationStatus::New->value),
            ])
            ->get();

        $archivedContests = $organization->contests()
            ->where('status', ContestStatus::Archive->value)
            ->latest()
            ->take(5)
            ->get();

        return view('evaluation.index', compact('organization', 'evaluationContests', 'archivedContests'));
    }

    /**
     * GET /organizations/{organization}/contests/{contest}/evaluate
     * Show all applications grouped by category for evaluation.
     */
    public function show(Request $request, Organization $organization, Contest $contest): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->canInOrg('evaluate', $organization)) {
            abort(403);
        }

        $canAccess = $contest->isEvaluation()
            || $contest->isArchive()
            || ($contest->isPermanent() && $contest->isAccepting());

        if (! $canAccess) {
            return redirect()->route('evaluation.index', $organization)
                ->with('error', 'Конкурс не находится в стадии оценки.');
        }

        $contest->load(['categories', 'organization']);

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

        $canFinalize = $totalCount > 0
            && $evaluatedCount === $totalCount
            && $contest->isEvaluation();

        return view('evaluation.show', compact(
            'organization', 'contest', 'applications', 'totalCount', 'evaluatedCount', 'canFinalize'
        ));
    }

    /**
     * POST /organizations/{organization}/applications/{application}/evaluate
     * Assign a status (place or reject) to one application.
     */
    public function evaluate(
        EvaluateApplicationRequest $request,
        Organization $organization,
        Application $application
    ): RedirectResponse|JsonResponse {
        $this->authorize('update', $application);

        $contest = $application->contest;

        $canEvaluate = $contest->isEvaluation()
            || ($contest->isPermanent() && $contest->isAccepting());

        if (! $canEvaluate) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Оценка заявок закрыта — конкурс уже не находится в стадии оценки.'], 422);
            }

            return redirect()->back()
                ->with('error', 'Оценка заявок закрыта — конкурс уже не находится в стадии оценки.');
        }

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

        ActionLogService::log('application.evaluated', $application, [
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'by'         => $request->user()->id,
        ]);

        // For permanent contests: generate diploma immediately upon place assignment
        $eligibleStatuses = [
            ApplicationStatus::Participant,
            ApplicationStatus::Place1,
            ApplicationStatus::Place2,
            ApplicationStatus::Place3,
        ];

        if ($contest->isPermanent() && in_array($newStatus, $eligibleStatuses, true)) {
            $this->diplomaService->generateForApplication($application);
        }

        // Send notification to applicant
        $application->load('user', 'diploma');
        $application->user->notify(new ApplicationEvaluated($application));

        if ($request->expectsJson()) {
            return response()->json([
                'status'          => $newStatus->value,
                'statusLabel'     => $newStatus->label(),
                'statusColor'     => $newStatus->color(),
                'rejectionReason' => $application->rejection_reason,
                'diplomaUrl'      => $application->diploma
                    ? route('diplomas.download', $application->diploma)
                    : null,
            ]);
        }

        return redirect()->route('evaluation.show', [$organization, $contest])
            ->with('status', 'application-evaluated');
    }

    /**
     * POST /organizations/{organization}/contests/{contest}/finalize
     * Finalize evaluation: archive contest, generate diplomas, send notifications.
     */
    public function finalize(
        Request $request,
        Organization $organization,
        Contest $contest
    ): RedirectResponse {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->canInOrg('evaluate', $organization)) {
            abort(403);
        }

        if (! $contest->isEvaluation() && ! ($contest->isPermanent() && $contest->isAccepting())) {
            return redirect()->back()
                ->with('error', 'Конкурс не находится в стадии оценки.');
        }

        // Finalization gate: all applications must be evaluated
        $hasNew = $contest->applications()
            ->where('status', ApplicationStatus::New->value)
            ->exists();

        if ($hasNew) {
            return redirect()->back()
                ->with('error', 'Нельзя завершить оценку: есть необработанные заявки.');
        }

        // Archive the contest
        $contest->update(['status' => ContestStatus::Archive->value]);

        // Generate diplomas for all placed applications
        $diplomaCount = $this->diplomaService->generateForContest($contest);

        // Reload applications with diplomas for notifications
        $contest->applications()->with(['user', 'diploma'])->each(function (Application $app): void {
            if ($app->user->email_notifications) {
                $app->user->notify(new ContestFinalized($app));
            }
        });

        ActionLogService::log('contest.finalized', $contest, [
            'by'            => $user->id,
            'diplomas_made' => $diplomaCount,
        ]);

        return redirect()->route('evaluation.show', [$organization, $contest])
            ->with('status', 'contest-finalized');
    }
}
