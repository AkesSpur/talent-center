<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * GET /admin/applications
     * All applications across all contests (all statuses, all users).
     */
    public function index(Request $request): View
    {
        $query = Application::with(['contest.organization', 'user', 'category'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('contest')) {
            $query->where('contest_id', $request->input('contest'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('last_name', 'like', '%' . $search . '%')
                  ->orWhere('first_name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $applications = $query->paginate(30)->withQueryString();
        $contests     = Contest::orderBy('title')->get(['id', 'title']);
        $statuses     = ApplicationStatus::cases();

        return view('admin.applications.index', compact('applications', 'contests', 'statuses'));
    }
}
