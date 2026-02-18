<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    private function validationRules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'organization' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        $parent = $request->user();

        User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'patronymic' => $validated['patronymic'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'organization' => $validated['organization'] ?? null,
            'city' => $validated['city'] ?? null,
            'group' => $validated['group'] ?? null,
            'email' => 'child.' . $parent->id . '.' . time() . '.' . mt_rand(100, 999) . '@talentcenter.local',
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

        $validated = $request->validate($this->validationRules());

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
}
