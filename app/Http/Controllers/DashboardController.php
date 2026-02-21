<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'organizations' => $user->organizations()->latest()->get(),
            'children' => $user->children()->orderBy('last_name')->get(),
        ]);
    }
}
