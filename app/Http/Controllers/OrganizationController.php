<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Services\ActionLogService;
use App\Traits\HandlesImages;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    use HandlesImages;
    public function index(Request $request): View
    {
        $organizations = $request->user()->organizations()->latest()->get();

        return view('organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        $this->authorize('create', Organization::class);

        return view('organizations.create');
    }

    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $data = array_merge($request->safe()->except('avatar'), [
            'status'     => 'pending',
            'created_by' => $request->user()->id,
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $this->storeImageAsWebp($request->file('avatar'), 'organizations/avatars');
        }

        $org = Organization::create($data);

        $org->representatives()->attach($request->user()->id, [
            'can_create'   => true,
            'can_manage'   => true,
            'can_evaluate' => true,
        ]);

        ActionLogService::log('organization.created', $org);

        return redirect()->route('organizations.show', $org)
            ->with('status', 'organization-created');
    }

    public function show(Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->load(['representatives', 'createdBy', 'verifiedBy', 'contests']);

        return view('organizations.show', compact('organization'));
    }

    public function edit(Organization $organization): View
    {
        $this->authorize('update', $organization);

        return view('organizations.edit', compact('organization'));
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $data = $request->safe()->except(['avatar', 'delete_avatar']);

        if ($request->boolean('delete_avatar') && $organization->avatar_path) {
            $this->deleteStoredImage($organization->avatar_path);
            $data['avatar_path'] = null;
        } elseif ($request->hasFile('avatar')) {
            $this->deleteStoredImage($organization->avatar_path);
            $data['avatar_path'] = $this->storeImageAsWebp($request->file('avatar'), 'organizations/avatars');
        }

        $organization->update($data);

        ActionLogService::log('organization.updated', $organization);

        return redirect()->route('organizations.show', $organization)
            ->with('status', 'organization-updated');
    }
}
