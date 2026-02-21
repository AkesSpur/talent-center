<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\ActionLog;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'usersCount' => User::count(),
            'organizationsCount' => Organization::count(),
            'pendingOrgsCount' => Organization::where('status', OrganizationStatus::Pending)->count(),
            'verifiedOrgsCount' => Organization::where('status', OrganizationStatus::Verified)->count(),
            'contestsCount' => Contest::count(),
            'applicationsCount' => Application::count(),
            'recentLogs' => ActionLog::with('user')->latest()->limit(5)->get(),
        ]);
    }
}
