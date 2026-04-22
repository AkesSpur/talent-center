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
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $activeContests = Contest::with(['organization', 'platformCategory'])
            ->where('status', ContestStatus::Accepting)
            ->latest('applications_start_at')
            ->take(6)
            ->get();

        $stats = Cache::remember('homepage_stats', now()->endOfDay()->diffInSeconds(now()), function () {
            $format = function (int $n): string {
                if ($n < 100) {
                    $rounded = (int) round($n / 10) * 10;
                } else {
                    $rounded = (int) round($n / 100) * 100;
                }

                return number_format($rounded, 0, ',', ' ');
            };

            return [
                'contests'      => $format(Contest::count()),
                'organizations' => $format(Organization::where('status', OrganizationStatus::Verified)->count()),
                'participants'  => $format(User::where('role', UserRole::Participant)->count()),
                'applications'  => $format(Application::count()),
            ];
        });

        return view('welcome', compact('activeContests', 'stats'));
    }
}
