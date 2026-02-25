<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\FileType;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Services\ActionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * GET /contests/{contest}/apply
     * Show application form. Only accessible if contest is accepting.
     */
    public function create(Request $request, Contest $contest): View|RedirectResponse
    {
        $this->authorize('create', Application::class);

        if (! $contest->isAccepting()) {
            return redirect()->route('contests.show', $contest)
                ->with('error', 'Приём заявок на этот конкурс закрыт.');
        }

        $user     = $request->user();
        $children = $user->children()->orderBy('last_name')->get();

        // Collect all user_ids in this "family" that have already applied
        $familyIds       = $children->pluck('id')->prepend($user->id);
        $alreadyAppliedIds = Application::where('contest_id', $contest->id)
            ->whereIn('user_id', $familyIds)
            ->pluck('user_id')
            ->toArray();

        // If every family member has already applied, block the form
        $availableIds = $familyIds->diff($alreadyAppliedIds);
        if ($availableIds->isEmpty()) {
            return redirect()->route('contests.show', $contest)
                ->with('warning', 'Все участники уже подали заявки на этот конкурс.');
        }

        $contest->load('categories');

        return view('applications.create', compact('contest', 'children', 'alreadyAppliedIds'));
    }

    /**
     * POST /contests/{contest}/apply
     */
    public function store(StoreApplicationRequest $request, Contest $contest): RedirectResponse
    {
        $this->authorize('create', Application::class);

        // Re-check contest is still accepting (race condition safety)
        if (! $contest->isAccepting()) {
            return redirect()->route('contests.show', $contest)
                ->with('error', 'Приём заявок на этот конкурс закрыт.');
        }

        $submittedForUserId = $request->filled('submitted_for_user_id')
            ? (int) $request->input('submitted_for_user_id')
            : $request->user()->id;

        // Duplicate guard per specific user_id
        $alreadyApplied = Application::where('contest_id', $contest->id)
            ->where('user_id', $submittedForUserId)
            ->exists();

        if ($alreadyApplied) {
            return redirect()->back()->withInput()
                ->with('error', 'Заявка от этого участника уже была подана на данный конкурс.');
        }

        $data = [
            'contest_id'  => $contest->id,
            'category_id' => $request->input('category_id') ?: null,
            'user_id'     => $submittedForUserId,
            'status'      => 'new',
        ];

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $mime     = $file->getMimeType() ?? '';
            $fileType = str_starts_with($mime, 'image/') ? FileType::Image : FileType::Document;
            $filename = Str::ulid() . '.' . $file->getClientOriginalExtension();

            Storage::disk('public')->putFileAs('applications', $file, $filename);

            $data['file_path'] = 'applications/' . $filename;
            $data['file_type'] = $fileType->value;
        } elseif ($request->filled('external_link')) {
            $data['external_link'] = $request->input('external_link');
            $data['file_type']     = FileType::Link->value;
        }

        $application = Application::create($data);

        ActionLogService::log('application.submitted', $application, [
            'contest_id' => $contest->id,
            'user_id'    => $submittedForUserId,
        ]);

        return redirect()->route('dashboard.applications')
            ->with('status', 'application-submitted');
    }

    /**
     * GET /dashboard/applications
     * Participant sees their own applications and those submitted on behalf of their children.
     */
    public function myIndex(Request $request): View
    {
        $user    = $request->user();
        $userIds = $user->children()->pluck('id')->prepend($user->id);

        $applications = Application::with(['contest.organization', 'category', 'user'])
            ->whereIn('user_id', $userIds)
            ->latest()
            ->paginate(20);

        return view('applications.index', compact('applications'));
    }

    /**
     * GET /organizations/{organization}/applications
     * Org reps with can_manage OR can_evaluate see all applications for this org's contests.
     */
    public function orgIndex(Request $request, Organization $organization): View
    {
        $user = $request->user();

        if (
            ! $user->isAdmin()
            && ! $user->canInOrg('manage', $organization)
            && ! $user->canInOrg('evaluate', $organization)
        ) {
            abort(403);
        }

        $query = Application::with(['contest', 'category', 'user'])
            ->whereHas('contest', fn ($q) => $q->where('organization_id', $organization->id));

        if ($request->filled('contest_id')) {
            $query->where('contest_id', $request->input('contest_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->latest()->paginate(30);

        $contests = Contest::where('organization_id', $organization->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('organizations.applications', compact('organization', 'applications', 'contests'));
    }
}
