<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
        ]);

        $parent = $request->user();

        User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'patronymic' => $validated['patronymic'] ?? null,
            'email' => $this->generateChildEmail($parent, $validated['first_name'], $validated['last_name']),
            'password' => bcrypt(str()->random(32)),
            'role' => 'participant',
            'parent_id' => $parent->id,
        ]);

        return redirect()->route('profile.edit')
            ->with('status', 'participant-added')
            ->with('active_tab', 'participants');
    }

    public function update(Request $request, User $participant): RedirectResponse
    {
        if ($participant->parent_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
        ]);

        $participant->update($validated);

        return redirect()->route('profile.edit')
            ->with('status', 'participant-updated')
            ->with('active_tab', 'participants');
    }

    public function destroy(Request $request, User $participant): RedirectResponse
    {
        if ($participant->parent_id !== $request->user()->id) {
            abort(403);
        }

        $participant->delete();

        return redirect()->route('profile.edit')
            ->with('status', 'participant-deleted')
            ->with('active_tab', 'participants');
    }

    private function generateChildEmail(User $parent, string $firstName, string $lastName): string
    {
        return 'child.' . $parent->id . '.' . time() . '.' . mt_rand(100, 999) . '@talentcenter.local';
    }
}
