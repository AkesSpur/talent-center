<?php

declare(strict_types=1);

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contest;
use App\Models\PlatformCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContestController extends Controller
{
    /**
     * GET /support/contests
     * All contests — read-only.
     */
    public function index(Request $request): View
    {
        $query = Contest::with(['organization', 'platformCategory'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('platform_category_id', $request->input('category'));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->input('search') . '%');
        }

        $contests           = $query->paginate(20)->withQueryString();
        $platformCategories = PlatformCategory::orderBy('name')->get();

        return view('support.contests.index', compact('contests', 'platformCategories'));
    }

    /**
     * GET /support/contests/{contest}
     * Contest detail with applications — read-only.
     */
    public function show(Request $request, Contest $contest): View
    {
        $contest->load(['organization', 'categories', 'platformCategory', 'juries', 'createdBy']);

        $query = Application::with(['user', 'category'])
            ->where('contest_id', $contest->id);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->latest()->paginate(30)->withQueryString();

        return view('support.contests.show', compact('contest', 'applications'));
    }
}
