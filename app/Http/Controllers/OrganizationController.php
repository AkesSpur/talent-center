<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Mail\JuryInvitation;
use App\Models\Organization;
use App\Models\User;
use App\Services\ActionLogService;
use App\Traits\HandlesImages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    use HandlesImages;
    public function index(Request $request): View
    {
        $organizations = $request->user()
            ->organizations()
            ->with(['contests' => fn ($q) => $q->where('status', 'evaluation')])
            ->latest()
            ->get();

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

        // Explicitly set offer_accepted from request (unchecked checkbox = not sent = false)
        $data['offer_accepted'] = $request->boolean('offer_accepted');

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

    /**
     * POST /organizations/{organization}/add-jury-member (AJAX)
     * Add a user as an org representative with can_evaluate permission.
     */
    public function addJuryMember(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        if (! $user->canInOrg('manage', $organization) && ! $user->isAdmin()) {
            return response()->json(['error' => 'Недостаточно прав.'], 403);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $newRep = User::where('email', $request->input('email'))->first();

        if (! $newRep) {
            return response()->json(['not_found' => true, 'email' => $request->input('email')], 422);
        }

        if ($organization->representatives()->where('user_id', $newRep->id)->exists()) {
            return response()->json([
                'id'             => $newRep->id,
                'name'           => $newRep->full_name,
                'already_member' => true,
            ]);
        }

        $organization->representatives()->attach($newRep->id, [
            'can_create'   => false,
            'can_manage'   => false,
            'can_evaluate' => true,
        ]);

        ActionLogService::log('representative.added', $organization, [
            'user_id'   => $newRep->id,
            'user_name' => $newRep->full_name,
            'source'    => 'jury_invite',
        ]);

        return response()->json([
            'id'             => $newRep->id,
            'name'           => $newRep->full_name,
            'already_member' => false,
        ]);
    }

    /**
     * POST /organizations/{organization}/send-jury-invitation (AJAX)
     * Send a registration invitation email to an unregistered email address.
     */
    public function sendJuryInvitation(Request $request, Organization $organization): JsonResponse
    {
        $user = $request->user();

        if (! $user->canInOrg('manage', $organization) && ! $user->isAdmin()) {
            return response()->json(['error' => 'Недостаточно прав.'], 403);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (User::where('email', $request->input('email'))->exists()) {
            return response()->json(['error' => 'Пользователь уже зарегистрирован. Попробуйте добавить снова.'], 422);
        }

        Mail::to($request->input('email'))->send(new JuryInvitation($organization, $user));

        ActionLogService::log('jury.invitation_sent', $organization, [
            'invited_email' => $request->input('email'),
            'invited_by'    => $user->id,
        ]);

        return response()->json(['sent' => true]);
    }
}
