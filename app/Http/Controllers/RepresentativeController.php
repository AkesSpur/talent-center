<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Services\ActionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepresentativeController extends Controller
{
    public function index(Request $request, Organization $organization): View
    {
        $user = $request->user();

        if (! $user->canInOrg('manage', $organization) && ! $user->isAdmin() && ! $user->isSupport()) {
            abort(403);
        }

        $organization->load('representatives');

        return view('organizations.representatives', compact('organization'));
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $user = $request->user();

        if (! $user->canInOrg('manage', $organization) && ! $user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'can_create' => ['boolean'],
            'can_manage' => ['boolean'],
            'can_evaluate' => ['boolean'],
        ], [
            'email.exists' => 'Пользователь с таким email не найден в системе.',
        ]);

        $newRep = User::where('email', $validated['email'])->first();

        if ($organization->representatives()->where('user_id', $newRep->id)->exists()) {
            return back()->withErrors(['email' => 'Этот пользователь уже является представителем организации.']);
        }

        $organization->representatives()->attach($newRep->id, [
            'can_create' => $validated['can_create'] ?? false,
            'can_manage' => $validated['can_manage'] ?? false,
            'can_evaluate' => $validated['can_evaluate'] ?? false,
        ]);

        ActionLogService::log('representative.added', $organization, [
            'user_id' => $newRep->id,
            'user_name' => $newRep->full_name,
        ]);

        return redirect()->route('organizations.representatives.index', $organization)
            ->with('status', 'representative-added');
    }

    public function update(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->canInOrg('manage', $organization) && ! $currentUser->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'can_create' => ['boolean'],
            'can_manage' => ['boolean'],
            'can_evaluate' => ['boolean'],
        ]);

        $organization->representatives()->updateExistingPivot($user->id, [
            'can_create' => $validated['can_create'] ?? false,
            'can_manage' => $validated['can_manage'] ?? false,
            'can_evaluate' => $validated['can_evaluate'] ?? false,
        ]);

        ActionLogService::log('representative.updated', $organization, [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
        ]);

        return redirect()->route('organizations.representatives.index', $organization)
            ->with('status', 'representative-updated');
    }

    public function destroy(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $currentUser = $request->user();

        if (! $currentUser->canInOrg('manage', $organization) && ! $currentUser->isAdmin()) {
            abort(403);
        }

        // Prevent removing the org creator
        if ($organization->created_by === $user->id) {
            return back()->withErrors(['general' => 'Нельзя удалить создателя организации из представителей.']);
        }

        $organization->representatives()->detach($user->id);

        ActionLogService::log('representative.removed', $organization, [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
        ]);

        return redirect()->route('organizations.representatives.index', $organization)
            ->with('status', 'representative-removed');
    }
}
