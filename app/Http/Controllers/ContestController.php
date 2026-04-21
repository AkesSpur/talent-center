<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContestStatus;
use App\Http\Requests\StoreContestRequest;
use App\Http\Requests\UpdateContestRequest;
use App\Models\AgeGroup;
use App\Models\Contest;
use App\Models\ContestCategory;
use App\Models\ContestCover;
use App\Models\DiplomaBackground;
use App\Models\Organization;
use App\Models\PlatformCategory;
use App\Services\ActionLogService;
use App\Traits\HandlesImages;
use Illuminate\Database\QueryException;
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
    public function index(Request $request): View
    {
        $sort = $request->input('sort', 'newest');

        $query = Contest::with(['organization', 'categories', 'platformCategory'])
            ->where('status', ContestStatus::Accepting->value);

        if ($sort === 'deadline') {
            $query->orderByRaw('applications_end_at IS NULL, applications_end_at ASC');
        } else {
            $query->latest();
        }

        $contests = $query->paginate(24)->withQueryString();

        $platformCategories = PlatformCategory::active()->get();

        return view('contests.index', compact('contests', 'platformCategories', 'sort'));
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
            'id'              => $org->id,
            'name'            => $org->name,
            'canManage'       => $user->isAdmin() || $user->canInOrg('manage', $org),
            'can_host_paid'   => $org->canHostPaidContests(),
            'reps'            => $org->representatives->map(fn ($rep) => [
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

        $defaultApplicationLimit = (int) \App\Models\SiteSettings::get(\App\Models\SiteSettings::DEFAULT_APPLICATION_LIMIT, '50');

        return view('contests.create', compact('orgsData', 'platformCategories', 'preselectedOrgId', 'diplomaBackgrounds', 'contestCovers', 'defaultApplicationLimit'));
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

        $isPaid      = $request->boolean('is_paid');
        $isPermanent = $isPaid && $request->boolean('is_permanent');

        $data = array_merge(
            $request->safe()->except(['diploma_background', 'cover_image', 'categories', 'juries', 'organization_id', 'contest_age_groups', 'selected_diploma_background_path', 'selected_cover_path', 'is_permanent', 'is_paid', 'entry_fee', 'application_limit']),
            [
                'organization_id'   => $organization->id,
                'created_by'        => $request->user()->id,
                'status'            => ContestStatus::Draft->value,
                'is_permanent'      => $isPermanent,
                'is_paid'           => $isPaid,
                'entry_fee'         => $isPaid ? (int) $request->input('entry_fee') : null,
                'application_limit' => $isPermanent
                    ? 0
                    : ($isPaid
                        ? max(0, (int) $request->input('application_limit', 0))
                        : min(
                            (int) \App\Models\SiteSettings::get(\App\Models\SiteSettings::DEFAULT_APPLICATION_LIMIT, '50'),
                            max(1, (int) $request->input('application_limit', \App\Models\SiteSettings::get(\App\Models\SiteSettings::DEFAULT_APPLICATION_LIMIT, '50')))
                        )),
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

        $contest = $this->withDbRetry(3, function () use ($data, $request) {
            return DB::transaction(function () use ($data, $request) {
                $contest = Contest::create($data);

                $categoryModels = $this->syncCategories($contest, $request->input('categories', []));
                $this->syncAgeGroups($contest, $request->input('categories', []), $request->input('contest_age_groups', []), $categoryModels);
                $this->syncJuries($contest, $request->input('juries', []));

                $contest->status = $contest->determineCurrentStatus();
                $contest->save();

                return $contest;
            });
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

        $applicationsCount = $contest->applications()
            ->whereNotIn('status', ['payment_pending'])
            ->count();

        return view('contests.show', compact('contest', 'hasApplied', 'applicationsCount'));
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
            'id'            => $org->id,
            'name'          => $org->name,
            'canManage'     => $user->isAdmin() || $user->canInOrg('manage', $org),
            'can_host_paid' => $org->canHostPaidContests(),
            'reps'          => $org->representatives->map(fn ($rep) => [
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

        $defaultApplicationLimit = (int) \App\Models\SiteSettings::get(\App\Models\SiteSettings::DEFAULT_APPLICATION_LIMIT, '50');

        return view('contests.edit', compact('contest', 'orgsData', 'platformCategories', 'selectedJuryIds', 'diplomaBackgrounds', 'contestCovers', 'defaultApplicationLimit'));
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

        $isPaidUpdate      = $request->boolean('is_paid');
        $isPermanentUpdate = $isPaidUpdate && $request->boolean('is_permanent');
        $data['is_permanent'] = $isPermanentUpdate;
        if ($isPermanentUpdate) {
            $data['application_limit'] = 0;
        } elseif (! $isPaidUpdate) {
            $defaultLimit = (int) \App\Models\SiteSettings::get(\App\Models\SiteSettings::DEFAULT_APPLICATION_LIMIT, '50');
            $data['application_limit'] = min($defaultLimit, max(1, (int) ($data['application_limit'] ?? 1)));
        }
        $data['applications_start_at'] = Carbon::parse($data['applications_start_at'])->startOfDay();
        $data['applications_end_at']   = isset($data['applications_end_at'])
            ? Carbon::parse($data['applications_end_at'])->endOfDay()
            : null;
        $data['evaluation_end_at']     = isset($data['evaluation_end_at'])
            ? Carbon::parse($data['evaluation_end_at'])->endOfDay()
            : null;

        // Only delete a stored image if it is a contest-specific custom upload,
        // never if it points to a shared library asset (diploma-backgrounds/, etc.).
        $deleteIfOwned = function (?string $path): void {
            if ($path && str_starts_with($path, 'contests/')) {
                $this->deleteStoredImage($path);
            }
        };

        // Diploma background
        if ($request->boolean('delete_diploma_background') && $contest->diploma_background) {
            $deleteIfOwned($contest->diploma_background);
            $data['diploma_background'] = null;
        } elseif ($request->hasFile('diploma_background')) {
            $deleteIfOwned($contest->diploma_background);
            $data['diploma_background'] = $this->storeImageAsWebp(
                $request->file('diploma_background'),
                'contests/backgrounds',
                1920,
                90
            );
        } elseif ($request->filled('selected_diploma_background_path')) {
            $deleteIfOwned($contest->diploma_background);
            $data['diploma_background'] = $request->input('selected_diploma_background_path');
        }

        // Cover image
        if ($request->boolean('delete_cover_image') && $contest->cover_image) {
            $deleteIfOwned($contest->cover_image);
            $data['cover_image'] = null;
        } elseif ($request->hasFile('cover_image')) {
            $deleteIfOwned($contest->cover_image);
            $data['cover_image'] = $this->storeImageAsWebp(
                $request->file('cover_image'),
                'contests/covers',
                1200,
                85
            );
        } elseif ($request->filled('selected_cover_path')) {
            $data['cover_image'] = $request->input('selected_cover_path');
        }

        $this->withDbRetry(3, function () use ($contest, $data, $request) {
            DB::transaction(function () use ($contest, $data, $request) {
                $contest->update($data);

                $categoryModels = $this->syncCategories($contest, $request->input('categories', []));
                $this->syncAgeGroups($contest, $request->input('categories', []), $request->input('contest_age_groups', []), $categoryModels);
                $this->syncJuries($contest, $request->input('juries', []));

                if (! $contest->isCancelled()) {
                    $contest->refresh();
                    $newStatus = $contest->determineCurrentStatus();
                    if ($contest->status !== $newStatus) {
                        $contest->status = $newStatus;
                        $contest->save();
                    }
                }
            });
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
     *
     * Uses DB::unprepared() for DELETE statements to permanently avoid MySQL error 1615
     * ("Prepared statement needs to be re-prepared") on shared hosting (e.g. reg.ru).
     *
     * Root cause: the hosting provider runs ProxySQL or a similar connection pool in front
     * of MySQL. Even with PDO::ATTR_EMULATE_PREPARES => true (which prevents PHP from
     * sending PREPARE commands), the proxy re-converts queries to server-side prepared
     * statements internally. When MySQL's table_open_cache is exhausted or refreshed, it
     * invalidates those statements and returns 1615.
     *
     * DB::unprepared() sends a raw SQL string with NO parameter binding at any layer —
     * PHP, proxy, or MySQL — making 1615 structurally impossible for these statements.
     * The (int) cast on contest_id guarantees injection safety.
     *
     * @param  array<int, array{name: string, description?: string|null}>  $categories
     * @return array<int, ContestCategory>
     */
    private function syncCategories(Contest $contest, array $categories): array
    {
        DB::unprepared('DELETE FROM contest_categories WHERE contest_id = ' . (int) $contest->id);

        $created = [];
        foreach ($categories as $index => $cat) {
            if (! empty($cat['name'])) {
                $model = ContestCategory::create([
                    'contest_id'  => $contest->id,
                    'name'        => $cat['name'],
                    'description' => $cat['description'] ?? null,
                ]);
                $created[$index] = $model;
            }
        }

        return $created;
    }

    /**
     * Sync age groups. See syncCategories() for full explanation of why DB::unprepared()
     * is used instead of QueryBuilder for DELETE statements.
     */
    private function syncAgeGroups(Contest $contest, array $categoriesInput, array $contestAgeGroupsInput, array $categoryModels): void
    {
        // Delete contest-level age groups only (category-level ones cascade from category delete)
        DB::unprepared(
            'DELETE FROM age_groups WHERE contest_id = ' . (int) $contest->id .
            ' AND contest_category_id IS NULL'
        );

        // Create category-level age groups
        foreach ($categoriesInput as $index => $cat) {
            if (isset($categoryModels[$index]) && ! empty($cat['age_groups'])) {
                foreach ($cat['age_groups'] as $ag) {
                    if (! empty($ag['name'])) {
                        AgeGroup::create([
                            'contest_id'          => $contest->id,
                            'contest_category_id' => $categoryModels[$index]->id,
                            'name'                => $ag['name'],
                            'min_age'             => ($ag['min_age'] ?? null) ?: null,
                            'max_age'             => ($ag['max_age'] ?? null) ?: null,
                        ]);
                    }
                }
            }
        }

        // Create contest-level age groups
        foreach ($contestAgeGroupsInput as $ag) {
            if (! empty($ag['name'])) {
                AgeGroup::create([
                    'contest_id'          => $contest->id,
                    'contest_category_id' => null,
                    'name'                => $ag['name'],
                    'min_age'             => ($ag['min_age'] ?? null) ?: null,
                    'max_age'             => ($ag['max_age'] ?? null) ?: null,
                ]);
            }
        }
    }

    /**
     * Sync contest jury members using raw SQL to avoid 1615 on shared hosting.
     * See syncCategories() for the full explanation.
     *
     * @param  array<int, int>  $juryIds
     */
    private function syncJuries(Contest $contest, array $juryIds): void
    {
        $contestId = (int) $contest->id;

        // Delete existing jury assignments
        DB::unprepared("DELETE FROM contest_juries WHERE contest_id = {$contestId}");

        // Re-insert the requested jury members
        if (! empty($juryIds)) {
            $values = implode(', ', array_map(
                fn (int $userId) => "({$contestId}, {$userId})",
                array_map('intval', $juryIds)
            ));
            DB::unprepared("INSERT INTO contest_juries (contest_id, user_id) VALUES {$values}");
        }
    }

    /**
     * Execute a callback, retrying on MySQL error 1615 (prepared statement needs re-prepare).
     *
     * Error 1615 occurs on shared MySQL servers (e.g. reg.ru) when the server's internal
     * metadata/table-definition cache is flushed between a statement being compiled and
     * executed. PDO::ATTR_EMULATE_PREPARES reduces but does not eliminate this on all hosts.
     *
     * Strategy: catch 1615, force a full DB reconnect to get a fresh PDO handle,
     * wait briefly, then retry. The transaction is replayed from scratch each attempt,
     * so all or nothing semantics are preserved.
     */
    private function withDbRetry(int $maxAttempts, \Closure $callback): mixed
    {
        $attempt = 0;
        while (true) {
            try {
                return $callback();
            } catch (QueryException $e) {
                $attempt++;
                $is1615 = str_contains($e->getMessage(), '1615');

                if (! $is1615 || $attempt >= $maxAttempts) {
                    throw $e;
                }

                // Reconnect to get a fresh PDO handle before retrying
                DB::reconnect();
                usleep(100_000 * $attempt); // 100ms first retry, 200ms second
            }
        }
    }
}
