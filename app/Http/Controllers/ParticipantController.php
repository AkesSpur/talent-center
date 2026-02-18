<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'patronymic' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'organization' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:255'],
        ]);

        $child = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'patronymic' => $validated['patronymic'] ?? null,
            'email' => $this->generateChildEmail($request->user(), $validated['first_name'], $validated['last_name']),
            'password' => bcrypt(str()->random(32)),
            'role' => 'participant',
            'parent_id' => $request->user()->id,
        ]);

        return response()->json(['success' => true, 'participant' => $child], 201);
    }

    public function update(Request $request, User $participant): JsonResponse
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

        return response()->json(['success' => true, 'participant' => $participant->fresh()]);
    }

    public function destroy(Request $request, User $participant): JsonResponse
    {
        if ($participant->parent_id !== $request->user()->id) {
            abort(403);
        }

        $participant->delete();

        return response()->json(['success' => true]);
    }

    private function generateChildEmail(User $parent, string $firstName, string $lastName): string
    {
        $base = strtolower(transliterator_transliterate(
            'Any-Latin; Latin-ASCII',
            $firstName . '.' . $lastName
        ) ?: $firstName . '.' . $lastName);
        $base = preg_replace('/[^a-z0-9.]/', '', $base);

        return $base . '.child' . $parent->id . '.' . time() . '@talentcenter.local';
    }
}
