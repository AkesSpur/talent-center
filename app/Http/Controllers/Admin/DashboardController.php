<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'contestsCount' => Contest::count(),
            'applicationsCount' => Application::count(),
        ]);
    }
}
