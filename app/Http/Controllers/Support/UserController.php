<?php

declare(strict_types=1);

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('support.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['organizations', 'children']);

        return view('support.users.show', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_blocked' => ['required', 'boolean'],
        ]);

        $blocked = (bool) $validated['is_blocked'];

        if ($user->is_blocked !== $blocked) {
            $user->update(['is_blocked' => $blocked]);
            ActionLogService::log('user.updated', $user, [
                'is_blocked' => ['from' => !$blocked, 'to' => $blocked],
            ]);
        }

        return redirect()->route('support.users.show', $user)
            ->with('status', 'user-updated');
    }
}
