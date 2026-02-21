<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\ActionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Organization::with('createdBy');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('inn', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'blocked') {
                $query->where('is_blocked', true);
            } else {
                $query->where('status', $status)->where('is_blocked', false);
            }
        }

        $totalCount = Organization::count();
        $organizations = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.organizations.index', compact('organizations', 'totalCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'inn'           => ['required', 'string', 'max:12', 'unique:organizations,inn'],
            'ogrn'          => ['nullable', 'string', 'max:15'],
            'legal_address' => ['nullable', 'string', 'max:500'],
            'website'       => ['nullable', 'url', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'status'        => ['required', 'in:pending,verified'],
        ], [
            'inn.unique'           => 'Организация с таким ИНН уже существует.',
            'inn.required'         => 'Укажите ИНН.',
            'name.required'        => 'Укажите название организации.',
            'contact_email.required' => 'Укажите контактный email.',
            'website.url'          => 'Укажите корректный URL сайта.',
        ]);

        $org = Organization::create([
            ...$validated,
            'created_by'  => $request->user()->id,
            'verified_by' => $validated['status'] === 'verified' ? $request->user()->id : null,
            'verified_at' => $validated['status'] === 'verified' ? now() : null,
        ]);

        ActionLogService::log('organization.created', $org);

        return redirect()->route('admin.organizations.index')
            ->with('status', 'organization-created');
    }

    public function show(Organization $organization): View
    {
        $organization->load(['representatives', 'createdBy', 'verifiedBy']);

        return view('admin.organizations.show', compact('organization'));
    }

    public function edit(Organization $organization): View
    {
        $organization->load(['representatives', 'createdBy']);

        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'delete_avatar' => ['nullable', 'boolean'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'inn'           => ['required', 'string', 'max:12'],
            'ogrn'          => ['nullable', 'string', 'max:15'],
            'legal_address' => ['nullable', 'string', 'max:500'],
            'website'       => ['nullable', 'url', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $data = collect($validated)->except(['avatar', 'delete_avatar'])->toArray();

        if ($request->boolean('delete_avatar') && $organization->avatar_path) {
            Storage::disk('public')->delete($organization->avatar_path);
            $data['avatar_path'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($organization->avatar_path) {
                Storage::disk('public')->delete($organization->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('organizations/avatars', 'public');
        }

        $changes = [];
        foreach ($data as $field => $value) {
            if ($organization->{$field} !== $value) {
                $changes[$field] = ['from' => $organization->{$field}, 'to' => $value];
            }
        }

        $organization->update($data);

        if (! empty($changes)) {
            ActionLogService::log('organization.updated', $organization, $changes);
        }

        return redirect()->route('admin.organizations.show', $organization)
            ->with('status', 'organization-updated');
    }

    public function verify(Request $request, Organization $organization): RedirectResponse
    {
        if ($organization->isVerified()) {
            return back()->with('status', 'already-verified');
        }

        $organization->update([
            'status' => OrganizationStatus::Verified,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        ActionLogService::log('organization.verified', $organization);

        return back()->with('status', 'organization-verified');
    }

    public function toggleBlock(Request $request, Organization $organization): RedirectResponse
    {
        $blocked = ! $organization->is_blocked;
        $organization->update(['is_blocked' => $blocked]);

        ActionLogService::log(
            $blocked ? 'organization.blocked' : 'organization.unblocked',
            $organization
        );

        return redirect()->route('admin.organizations.show', $organization)
            ->with('status', $blocked ? 'organization-blocked' : 'organization-unblocked');
    }

    public function addRepresentative(Request $request, Organization $organization): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
                'can_create' => ['boolean'],
                'can_manage' => ['boolean'],
                'can_evaluate' => ['boolean'],
            ], [
                'email.exists' => 'Пользователь с таким email не найден в системе.',
            ]);
        } catch (ValidationException $e) {
            return back()->withInput()->with('error', $e->validator->errors()->first());
        }

        $newRep = User::where('email', $validated['email'])->first();

        if ($organization->representatives()->where('user_id', $newRep->id)->exists()) {
            return back()->withInput()->with('error', 'Этот пользователь уже является представителем организации.');
        }

        $organization->representatives()->attach($newRep->id, [
            'can_create' => $validated['can_create'] ?? false,
            'can_manage' => $validated['can_manage'] ?? false,
            'can_evaluate' => $validated['can_evaluate'] ?? false,
        ]);

        ActionLogService::log('representative.added', $organization, [
            'user_id' => $newRep->id,
            'user_name' => $newRep->full_name,
            'added_by' => 'admin',
        ]);

        return redirect()->route('admin.organizations.show', $organization)
            ->with('status', 'representative-added');
    }

    public function updateRepresentative(Request $request, Organization $organization, User $user): RedirectResponse
    {
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
            'updated_by' => 'admin',
        ]);

        return redirect()->route('admin.organizations.show', $organization)
            ->with('status', 'representative-updated');
    }

    public function removeRepresentative(Request $request, Organization $organization, User $user): RedirectResponse
    {
        if ($organization->created_by === $user->id) {
            return back()->with('error', 'Нельзя удалить создателя организации из представителей.');
        }

        $organization->representatives()->detach($user->id);

        ActionLogService::log('representative.removed', $organization, [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'removed_by' => 'admin',
        ]);

        return redirect()->route('admin.organizations.show', $organization)
            ->with('status', 'representative-removed');
    }
}
