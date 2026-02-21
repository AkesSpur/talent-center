<?php

declare(strict_types=1);

namespace App\Http\Controllers\Support;

use App\Enums\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Organization;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('support.dashboard', [
            'pendingOrgsCount' => Organization::where('status', OrganizationStatus::Pending)->count(),
            'usersCount' => User::count(),
            'applicationsCount' => Application::count(),
            'pendingOrgs' => Organization::where('status', OrganizationStatus::Pending)
                ->with('createdBy')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
