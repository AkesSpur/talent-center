<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Diploma;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // Collect IDs: user + their children
        $userIds = $user->children()->pluck('id')->prepend($user->id);

        $recentApplications = Application::with(['contest.organization', 'user'])
            ->whereIn('user_id', $userIds)
            ->latest()
            ->take(3)
            ->get();

        $recentDiplomas = Diploma::with(['contest.organization', 'application.category', 'user'])
            ->whereIn('user_id', $userIds)
            ->where('is_preview', false)
            ->latest()
            ->take(3)
            ->get();

        return view('dashboard', [
            'organizations'      => $user->organizations()->latest()->get(),
            'children'           => $user->children()->orderBy('last_name')->get(),
            'recentApplications' => $recentApplications,
            'recentDiplomas'     => $recentDiplomas,
        ]);
    }
}
