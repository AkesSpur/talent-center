<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function index(Request $request): View
    {
        $children = $request->user()->children()->orderBy('last_name')->get();

        return view('participants.index', compact('children'));
    }

    public function edit(Request $request, User $participant): View
    {
        if ($participant->parent_id !== $request->user()->id) {
            abort(403);
        }

        return view('participants.edit', compact('participant'));
    }

    private function validationRules(): array
    {
        return [
            'last_name'        => ['required', 'string', 'max:100'],
            'first_name'       => ['required', 'string', 'max:100'],
            'patronymic'       => ['nullable', 'string', 'max:100'],
            'birth_date'       => ['required', 'date'],
            'organization'     => ['nullable', 'string', 'max:255'],
            'city'             => ['nullable', 'string', 'max:255'],
            'group'            => ['nullable', 'string', 'max:255'],
            'parental_consent' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    private function isMinor(string $birthDate): bool
    {
        $dob = Carbon::parse($birthDate);
        return now() < $dob->copy()->addYears(18)->addDay();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules(), [
            'birth_date.required' => 'Дата рождения обязательна.',
        ]);

        $isMinor = $this->isMinor($validated['birth_date']);
        if ($isMinor && !$request->hasFile('parental_consent')) {
            return back()->withErrors(['parental_consent' => 'Для несовершеннолетнего участника необходимо прикрепить согласие родителей.'])->withInput();
        }

        $parent = $request->user();

        $participant = User::create([
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'patronymic'   => $validated['patronymic'] ?? null,
            'birth_date'   => $validated['birth_date'],
            'organization' => $validated['organization'] ?? null,
            'city'         => $validated['city'] ?? null,
            'group'        => $validated['group'] ?? null,
            'email'        => 'child.' . $parent->id . '.' . time() . '.' . mt_rand(100, 999) . '@talentcenter.local',
            'password'     => bcrypt(str()->random(32)),
            'role'         => 'participant',
            'parent_id'    => $parent->id,
        ]);

        if ($request->hasFile('parental_consent')) {
            $path = $request->file('parental_consent')->store('consents', 'public');
            $participant->update(['parental_consent_path' => $path]);
        }

        return redirect()->route('participants.index')
            ->with('status', 'participant-added');
    }

    public function update(Request $request, User $participant): RedirectResponse
    {
        if ($participant->parent_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate($this->validationRules(), [
            'birth_date.required' => 'Дата рождения обязательна.',
        ]);

        $isMinor = $this->isMinor($validated['birth_date']);
        if ($isMinor && !$participant->parental_consent_path && !$request->hasFile('parental_consent')) {
            return back()->withErrors(['parental_consent' => 'Для несовершеннолетнего участника необходимо прикрепить согласие родителей.'])->withInput();
        }

        $participant->update([
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'patronymic'   => $validated['patronymic'] ?? null,
            'birth_date'   => $validated['birth_date'],
            'organization' => $validated['organization'] ?? null,
            'city'         => $validated['city'] ?? null,
            'group'        => $validated['group'] ?? null,
        ]);

        if ($request->hasFile('parental_consent')) {
            if ($participant->parental_consent_path) {
                Storage::disk('public')->delete($participant->parental_consent_path);
            }
            $path = $request->file('parental_consent')->store('consents', 'public');
            $participant->update(['parental_consent_path' => $path]);
        }

        return redirect()->route('participants.index')
            ->with('status', 'participant-updated');
    }

    public function destroy(Request $request, User $participant): RedirectResponse
    {
        if ($participant->parent_id !== $request->user()->id) {
            abort(403);
        }

        $participant->delete();

        return redirect()->route('participants.index')
            ->with('status', 'participant-deleted');
    }
}
