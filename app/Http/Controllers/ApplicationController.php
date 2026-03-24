<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\FileType;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\SiteSettings;
use App\Models\User;
use App\Notifications\ApplicationSubmitted;
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

        $contest->load(['categories', 'ageGroups']);

        // Build age groups data for Alpine.js filtering
        $ageGroupsData = $contest->ageGroups->map(fn ($ag) => [
            'id'                  => $ag->id,
            'contest_category_id' => $ag->contest_category_id,
            'name'                => $ag->name,
            'min_age'             => $ag->min_age,
            'max_age'             => $ag->max_age,
        ])->values();

        return view('applications.create', compact('contest', 'children', 'alreadyAppliedIds', 'ageGroupsData'));
    }

    /**
     * GET /contests/{contest}/diploma-preview
     * Returns the diploma HTML template rendered as a sample (Образец).
     */
    public function diplomaPreview(Request $request, Contest $contest): View
    {
        $this->authorize('create', Application::class);

        $contest->load('organization');
        $authUser = $request->user();

        // Resolve whose name to show: the auth user or one of their children
        $requestedId = (int) $request->query('user_id', $authUser->id);
        $allowedIds  = $authUser->children()->pluck('id')->push($authUser->id);
        $user        = $allowedIds->contains($requestedId)
            ? User::find($requestedId) ?? $authUser
            : $authUser;

        $backgroundPath = null;
        if ($contest->diploma_background && Storage::disk('public')->exists($contest->diploma_background)) {
            $content        = Storage::disk('public')->get($contest->diploma_background);
            $backgroundPath = 'data:image/webp;base64,' . base64_encode($content);
        }

        $juryMembers = $contest->organization
            ->representatives()
            ->wherePivot('can_evaluate', true)
            ->get()
            ->map(fn ($u) => $u->full_name)
            ->all();

        $months = [
            1 => 'январь', 2 => 'февраль', 3 => 'март',
            4 => 'апрель', 5 => 'май', 6 => 'июнь',
            7 => 'июль', 8 => 'август', 9 => 'сентябрь',
            10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь',
        ];
        $now         = now();
        $awardedDate = $months[(int) $now->format('n')] . ' ' . $now->format('Y');

        $fontAriblk  = asset('fonts/ariblk.ttf');
        $fontCalibri = asset('fonts/calibri.ttf');

        return view('diplomas.template', [
            'participantLastName'        => $user->last_name,
            'participantFirstPatronymic' => trim(($user->first_name ?? '') . ' ' . ($user->patronymic ?? '')),
            'contestTitle'              => $contest->title,
            'orgName'                   => $contest->organization->name,
            'orgCity'                   => $contest->organization->city,
            'categoryName'              => null,
            'ageGroupName'              => null,
            'statusLabel'               => ApplicationStatus::Participant->diplomaLabel(),
            'teacherName'               => null,
            'awardedDate'               => $awardedDate,
            'diplomaNumber'             => '—',
            'qrCodeDataUri'             => null,
            'juryMembers'               => $juryMembers,
            'backgroundPath'            => $backgroundPath,
            'fontAriblk'                => $fontAriblk,
            'fontCalibri'               => $fontCalibri,
            'contactEmail'              => SiteSettings::get(SiteSettings::CONTACT_EMAIL, 'info@talant-centr.ru'),
            'participantInstitution'    => $user->organization,
            'participantGroup'          => $user->group,
            'isPreview'                 => true,
        ]);
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
            'contest_id'   => $contest->id,
            'category_id'  => $request->input('category_id') ?: null,
            'age_group_id' => $request->input('age_group_id') ?: null,
            'user_id'      => $submittedForUserId,
            'status'       => 'new',
            'teacher_name' => $request->input('teacher_name'),
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

        // Notify the applicant of successful submission
        $application->load('contest');
        $submittedForUser = User::find($submittedForUserId);
        if ($submittedForUser && $submittedForUser->email_notifications) {
            $submittedForUser->notify(new ApplicationSubmitted($application));
        }

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
