<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\PlatformCategory;
use App\Services\ActionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContestController extends Controller
{
    /**
     * GET /admin/contests
     * All contests (all statuses, all orgs).
     */
    public function index(Request $request): View
    {
        $query = Contest::with(['organization', 'platformCategory'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('platform_category_id', $request->input('category'));
        }

        if ($request->filled('organization')) {
            $query->where('organization_id', $request->input('organization'));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        $contests           = $query->paginate(20)->withQueryString();
        $platformCategories = PlatformCategory::orderBy('name')->get();
        $organizations      = Organization::orderBy('name')->get(['id', 'name']);

        return view('admin.contests.index', compact('contests', 'platformCategories', 'organizations'));
    }

    /**
     * GET /admin/contests/{contest}/applications
     * All applications for a contest with inline participant stats.
     */
    public function applications(Request $request, Contest $contest): View
    {
        $contest->load(['organization', 'categories', 'platformCategory']);

        $query = Application::with(['user', 'category'])
            ->where('contest_id', $contest->id);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        $applications = $query->latest()->paginate(30)->withQueryString();

        return view('admin.contests.applications', compact('contest', 'applications'));
    }

    /**
     * DELETE /admin/contests/{contest}
     * Admin-only hard delete.
     */
    public function destroy(Contest $contest): RedirectResponse
    {
        ActionLogService::log('contest.deleted', null, [
            'title'           => $contest->title,
            'organization_id' => $contest->organization_id,
        ]);

        $contest->delete();

        return redirect()->route('admin.contests.index')
            ->with('status', 'contest-deleted');
    }
}
