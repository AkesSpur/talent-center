<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContestStatus;
use App\Enums\OrganizationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $activeContests = Contest::with(['organization', 'platformCategory', 'categories'])
            ->where('status', ContestStatus::Accepting)
            ->latest('applications_start_at')
            ->take(6)
            ->get();

        $stats = [
            'contests'      => Contest::count(),
            'organizations' => Organization::where('status', OrganizationStatus::Verified)->count(),
            'participants'  => User::where('role', UserRole::Participant)->count(),
            'applications'  => Application::count(),
        ];

        return view('welcome', compact('activeContests', 'stats'));
    }
}
