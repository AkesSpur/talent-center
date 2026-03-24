<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContestStatus;
use App\Http\Requests\StoreContestRequest;
use App\Http\Requests\UpdateContestRequest;
use App\Models\Contest;
use App\Models\ContestCover;
use App\Models\DiplomaBackground;
use App\Models\Organization;
use App\Models\PlatformCategory;
use App\Services\ActionLogService;
use App\Traits\HandlesImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'id'        => $org->id,
            'name'      => $org->name,
            'canManage' => $user->isAdmin() || $user->canInOrg('manage', $org),
            'reps'      => $org->representatives->map(fn ($rep) => [
                'id'   => $rep->id,
                'name' => $rep->full_name,
            ])->values()->toArray(),
        ])->values();

        $platformCategories = PlatformCategory::active()->get();
        $preselectedOrgId   = (int) old('organization_id', (string) $request->integer('organization_id', 0));

        $diplomaBackgrounds = DiplomaBackground::with('platformCategories')->get()->map(fn ($bg) => [
            'id'          => $bg->id,
            'name'        => $bg->name,
            'image_url'   => asset('storage/' . $bg->image_path),
            'image_path'  => $bg->image_path,
            'category_ids' => $bg->platformCategories->pluck('id')->toArray(),
        ])->values();

        $contestCovers = ContestCover::with('platformCategories')->get()->map(fn ($c) => [
            'id'           => $c->id,
            'name'         => $c->name,
            'image_url'    => asset('storage/' . $c->image_path),
            'image_path'   => $c->image_path,
            'category_ids' => $c->platformCategories->pluck('id')->toArray(),
        ])->values();

        return view('contests.create', compact('orgsData', 'platformCategories', 'preselectedOrgId', 'diplomaBackgrounds', 'contestCovers'));
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
            $request->safe()->except(['diploma_background', 'cover_image', 'categories', 'juries', 'organization_id', 'contest_age_groups', 'selected_diploma_background_path', 'selected_cover_path', 'is_permanent']),
            [
                'organization_id' => $organization->id,
                'created_by'      => $request->user()->id,
                'status'          => ContestStatus::Draft->value,
                'is_permanent'    => $request->boolean('is_permanent'),
            ]
        );

        $data['applications_start_at'] = Carbon::parse($data['applications_start_at'])->startOfDay();
        $data['applications_end_at']   = isset($data['applications_end_at'])
            ? Carbon::parse($data['applications_end_at'])->endOfDay()
            : null;
        $data['evaluation_end_at']     = isset($data['evaluation_end_at'])
            ? Carbon::parse($data['evaluation_end_at'])->endOfDay()
            : null;

        if ($request->hasFile('diploma_background')) {
            $data['diploma_background'] = $this->storeImageAsWebp(
                $request->file('diploma_background'),
                'contests/backgrounds',
                1920,
                90
            );
        } elseif ($request->filled('selected_diploma_background_path')) {
            $data['diploma_background'] = $request->input('selected_diploma_background_path');
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $this->storeImageAsWebp(
                $request->file('cover_image'),
                'contests/covers',
                1200,
                85
            );
        } elseif ($request->filled('selected_cover_path')) {
            $data['cover_image'] = $request->input('selected_cover_path');
        }

        $contest = DB::transaction(function () use ($data, $request) {
            $contest = Contest::create($data);

            $categoryModels = $this->syncCategories($contest, $request->input('categories', []));
            $this->syncAgeGroups($contest, $request->input('categories', []), $request->input('contest_age_groups', []), $categoryModels);
            $this->syncJuries($contest, $request->input('juries', []));

            // Determine and apply correct status based on dates
            $contest->status = $contest->determineCurrentStatus();
            $contest->save();

            return $contest;
        });

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

        $contest->load(['organization.createdBy', 'categories', 'createdBy', 'juries', 'platformCategory']);

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

        $contest->load(['categories.ageGroups', 'juries', 'organization.representatives', 'contestLevelAgeGroups']);

        $user     = request()->user();
        $org      = $contest->organization;
        $orgsData = collect([[
            'id'        => $org->id,
            'name'      => $org->name,
            'canManage' => $user->isAdmin() || $user->canInOrg('manage', $org),
            'reps'      => $org->representatives->map(fn ($rep) => [
                'id'   => $rep->id,
                'name' => $rep->full_name,
            ])->values()->toArray(),
        ]]);

        $platformCategories = PlatformCategory::active()->get();
        $selectedJuryIds    = $contest->juries->pluck('id')->toArray();

        $diplomaBackgrounds = DiplomaBackground::with('platformCategories')->get()->map(fn ($bg) => [
            'id'          => $bg->id,
            'name'        => $bg->name,
            'image_url'   => asset('storage/' . $bg->image_path),
            'image_path'  => $bg->image_path,
            'category_ids' => $bg->platformCategories->pluck('id')->toArray(),
        ])->values();

        $contestCovers = ContestCover::with('platformCategories')->get()->map(fn ($c) => [
            'id'           => $c->id,
            'name'         => $c->name,
            'image_url'    => asset('storage/' . $c->image_path),
            'image_path'   => $c->image_path,
            'category_ids' => $c->platformCategories->pluck('id')->toArray(),
        ])->values();

        return view('contests.edit', compact('contest', 'orgsData', 'platformCategories', 'selectedJuryIds', 'diplomaBackgrounds', 'contestCovers'));
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
            'categories', 'juries', 'contest_age_groups',
            'selected_diploma_background_path', 'selected_cover_path', 'is_permanent',
        ]);

        $data['is_permanent']          = $request->boolean('is_permanent');
        $data['applications_start_at'] = Carbon::parse($data['applications_start_at'])->startOfDay();
        $data['applications_end_at']   = isset($data['applications_end_at'])
            ? Carbon::parse($data['applications_end_at'])->endOfDay()
            : null;
        $data['evaluation_end_at']     = isset($data['evaluation_end_at'])
            ? Carbon::parse($data['evaluation_end_at'])->endOfDay()
            : null;

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
        } elseif ($request->filled('selected_diploma_background_path')) {
            if ($contest->diploma_background) {
                $this->deleteStoredImage($contest->diploma_background);
            }
            $data['diploma_background'] = $request->input('selected_diploma_background_path');
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
        } elseif ($request->filled('selected_cover_path')) {
            $data['cover_image'] = $request->input('selected_cover_path');
        }

        DB::transaction(function () use ($contest, $data, $request) {
            $contest->update($data);

            $categoryModels = $this->syncCategories($contest, $request->input('categories', []));
            $this->syncAgeGroups($contest, $request->input('categories', []), $request->input('contest_age_groups', []), $categoryModels);
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
        });

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
     * Returns an index-mapped array of created ContestCategory models.
     *
     * @param  array<int, array{name: string, description?: string|null}>  $categories
     * @return array<int, \App\Models\ContestCategory>
     */
    private function syncCategories(Contest $contest, array $categories): array
    {
        $contest->categories()->delete();

        $created = [];
        foreach ($categories as $index => $cat) {
            if (! empty($cat['name'])) {
                $created[$index] = $contest->categories()->create([
                    'name'        => $cat['name'],
                    'description' => $cat['description'] ?? null,
                ]);
            }
        }

        return $created;
    }

    /**
     * Sync age groups for a contest.
     * Category-level age groups are cascaded via syncCategories delete.
     * Contest-level age groups (no category) are handled separately.
     */
    private function syncAgeGroups(Contest $contest, array $categoriesInput, array $contestAgeGroupsInput, array $categoryModels): void
    {
        // Delete contest-level age groups (category-level ones cascade via category delete)
        $contest->contestLevelAgeGroups()->delete();

        // Create category-level age groups
        foreach ($categoriesInput as $index => $cat) {
            if (isset($categoryModels[$index]) && ! empty($cat['age_groups'])) {
                foreach ($cat['age_groups'] as $ag) {
                    if (! empty($ag['name'])) {
                        $contest->ageGroups()->create([
                            'contest_category_id' => $categoryModels[$index]->id,
                            'name'                => $ag['name'],
                            'min_age'             => ($ag['min_age'] ?? null) ?: null,
                            'max_age'             => ($ag['max_age'] ?? null) ?: null,
                        ]);
                    }
                }
            }
        }

        // Create contest-level age groups (when no categories)
        foreach ($contestAgeGroupsInput as $ag) {
            if (! empty($ag['name'])) {
                $contest->ageGroups()->create([
                    'contest_category_id' => null,
                    'name'                => $ag['name'],
                    'min_age'             => ($ag['min_age'] ?? null) ?: null,
                    'max_age'             => ($ag['max_age'] ?? null) ?: null,
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
