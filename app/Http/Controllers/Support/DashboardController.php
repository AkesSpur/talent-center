<?php

declare(strict_types=1);

namespace App\Http\Controllers\Support;

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
            'pendingOrgsCount' => Organization::where('status', 'pending')->count(),
            'usersCount' => User::count(),
            'applicationsCount' => Application::count(),
        ]);
    }
}
