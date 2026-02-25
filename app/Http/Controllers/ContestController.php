<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContestStatus;
use App\Http\Requests\StoreContestRequest;
use App\Http\Requests\UpdateContestRequest;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\PlatformCategory;
use App\Services\ActionLogService;
use App\Traits\HandlesImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ContestController extends Controller
{
    use HandlesImages;

    /**
     * GET /contests
     * Public listing — no auth required.
     */
    public function index(): View
    {
        $contests = Contest::with(['organization', 'categories', 'platformCategory'])
            ->where('status', ContestStatus::Accepting->value)
            ->latest()
            ->paginate(24);

        $platformCategories = PlatformCategory::active()->get();

        return view('contests.index', compact('contests', 'platformCategories'));
    }

    /**
     * GET /dashboard/contests
     * User's own created contests.
     */
    public function myIndex(Request $request): View
    {
        $contests = Contest::where('created_by', $request->user()->id)
            ->with(['organization', 'platformCategory', 'categories'])
            ->withCount('applications')
            ->latest()
            ->paginate(15);

        return view('dashboard.contests', compact('contests'));
    }

    /**
     * GET /contests/create
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $userOrgs = Organization::where('status', 'verified')
                ->with('representatives')
                ->orderBy('name')
                ->get();
        } else {
            $userOrgs = $user->organizations()
                ->where('status', 'verified')
                ->wherePivot('can_create', true)
                ->with('representatives')
                ->orderBy('name')
                ->get();
        }

        $orgsData = $userOrgs->map(fn (Organization $org) => [
            'id'   => $org->id,
            'name' => $org->name,
            'reps' => $org->representatives->map(fn ($rep) => [
                'id'   => $rep->id,
                'name' => $rep->full_name,
            ])->values()->toArray(),
        ])->values();

        $platformCategories = PlatformCategory::active()->get();
        $preselectedOrgId   = $request->integer('organization_id', 0);

        return view('contests.create', compact('orgsData', 'platformCategories', 'preselectedOrgId'));
    }

    /**
     * POST /contests
     */
    public function store(StoreContestRequest $request): RedirectResponse
    {
        $organization = Organization::findOrFail($request->input('organization_id'));

        // Manual org permission check (replaces org.permission middleware)
        if (! $request->user()->isAdmin()) {
            if (! $request->user()->canInOrg('create', $organization)) {
                abort(403);
            }
            if (! $organization->isVerified()) {
                abort(403, 'Организация должна быть верифицирована для создания конкурсов.');
            }
        }

        $data = array_merge(
            $request->safe()->except(['diploma_background', 'cover_image', 'categories', 'juries', 'organization_id']),
            [
                'organization_id' => $organization->id,
                'created_by'      => $request->user()->id,
                'status'          => ContestStatus::Draft->value,
            ]
        );

        if ($request->hasFile('diploma_background')) {
            $data['diploma_background'] = $this->storeImageAsWebp(
                $request->file('diploma_background'),
                'contests/backgrounds',
                1920,
                90
            );
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeImageAsWebp(
                $request->file('cover_image'),
                'contests/covers',
                1200,
                85
            );
        }

        $contest = Contest::create($data);

        $this->syncCategories($contest, $request->input('categories', []));
        $this->syncJuries($contest, $request->input('juries', []));

        // Determine and apply correct status based on dates
        $contest->status = $contest->determineCurrentStatus();
        $contest->save();

        ActionLogService::log('contest.created', $contest, [
            'organization_id' => $organization->id,
            'title'           => $contest->title,
            'status'          => $contest->status->value,
        ]);

        return redirect()->route('contests.show', $contest)
            ->with('status', 'contest-created');
    }

    /**
     * GET /contests/{contest}
     * Public — works for guests via Gate::allows().
     */
    public function show(Request $request, Contest $contest): View
    {
        abort_unless(Gate::allows('view', $contest), 403);

        $contest->load(['organization', 'categories', 'createdBy', 'juries', 'platformCategory']);

        $hasApplied = false;
        if ($request->user()) {
            $hasApplied = $contest->applications()
                ->where('user_id', $request->user()->id)
                ->exists();
        }

        return view('contests.show', compact('contest', 'hasApplied'));
    }

    /**
     * GET /contests/{contest}/edit
     */
    public function edit(Contest $contest): View
    {
        $this->authorize('update', $contest);

        $contest->load(['categories', 'juries', 'organization.representatives']);

        $org      = $contest->organization;
        $orgsData = collect([[
            'id'   => $org->id,
            'name' => $org->name,
            'reps' => $org->representatives->map(fn ($rep) => [
                'id'   => $rep->id,
                'name' => $rep->full_name,
            ])->values()->toArray(),
        ]]);

        $platformCategories = PlatformCategory::active()->get();
        $selectedJuryIds    = $contest->juries->pluck('id')->toArray();

        return view('contests.edit', compact('contest', 'orgsData', 'platformCategories', 'selectedJuryIds'));
    }

    /**
     * PUT /contests/{contest}
     */
    public function update(UpdateContestRequest $request, Contest $contest): RedirectResponse
    {
        $this->authorize('update', $contest);

        $data = $request->safe()->except([
            'diploma_background', 'delete_diploma_background',
            'cover_image', 'delete_cover_image',
            'categories', 'juries',
        ]);

        // Diploma background
        if ($request->boolean('delete_diploma_background') && $contest->diploma_background) {
            $this->deleteStoredImage($contest->diploma_background);
            $data['diploma_background'] = null;
        } elseif ($request->hasFile('diploma_background')) {
            if ($contest->diploma_background) {
                $this->deleteStoredImage($contest->diploma_background);
            }
            $data['diploma_background'] = $this->storeImageAsWebp(
                $request->file('diploma_background'),
                'contests/backgrounds',
                1920,
                90
            );
        }

        // Cover image
        if ($request->boolean('delete_cover_image') && $contest->cover_image) {
            $this->deleteStoredImage($contest->cover_image);
            $data['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            if ($contest->cover_image) {
                $this->deleteStoredImage($contest->cover_image);
            }
            $data['cover_image'] = $this->storeImageAsWebp(
                $request->file('cover_image'),
                'contests/covers',
                1200,
                85
            );
        }

        $contest->update($data);

        $this->syncCategories($contest, $request->input('categories', []));
        $this->syncJuries($contest, $request->input('juries', []));

        // Re-evaluate status after date changes (only if not cancelled)
        if (! $contest->isCancelled()) {
            $contest->refresh();
            $newStatus = $contest->determineCurrentStatus();
            if ($contest->status !== $newStatus) {
                $contest->status = $newStatus;
                $contest->save();
            }
        }

        ActionLogService::log('contest.updated', $contest, [
            'title' => $contest->title,
        ]);

        return redirect()->route('contests.show', $contest)
            ->with('status', 'contest-updated');
    }

    /**
     * POST /contests/{contest}/cancel
     */
    public function cancel(Request $request, Contest $contest): RedirectResponse
    {
        $this->authorize('cancel', $contest);

        $contest->update(['status' => ContestStatus::Cancelled->value]);

        ActionLogService::log('contest.cancelled', $contest, [
            'cancelled_by' => $request->user()->id,
        ]);

        return redirect()->route('contests.show', $contest)
            ->with('status', 'contest-cancelled');
    }

    /**
     * Sync contest categories: delete all existing, recreate from input array.
     *
     * @param  array<int, array{name: string, description?: string|null}>  $categories
     */
    private function syncCategories(Contest $contest, array $categories): void
    {
        $contest->categories()->delete();

        foreach ($categories as $cat) {
            if (! empty($cat['name'])) {
                $contest->categories()->create([
                    'name'        => $cat['name'],
                    'description' => $cat['description'] ?? null,
                ]);
            }
        }
    }

    /**
     * Sync contest jury members.
     *
     * @param  array<int, int>  $juryIds
     */
    private function syncJuries(Contest $contest, array $juryIds): void
    {
        $contest->juries()->sync($juryIds);
    }
}
