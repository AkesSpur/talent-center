<?php

declare(strict_types=1);

namespace App\Http\Controllers\Support;

use App\Enums\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\ActionLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $organizations = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('support.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization): View
    {
        $organization->load(['representatives', 'createdBy', 'verifiedBy']);

        return view('support.organizations.show', compact('organization'));
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
}
