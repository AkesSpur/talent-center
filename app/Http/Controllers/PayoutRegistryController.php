<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\PayoutRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutRegistryController extends Controller
{
    /**
     * GET /organizations/{organization}/payouts
     * Org reps with can_manage see the payout registry for this org.
     */
    public function orgIndex(Request $request, Organization $organization): View
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->canInOrg('manage', $organization)) {
            abort(403);
        }

        $registries = PayoutRegistry::with(['contest', 'receivedBy'])
            ->where('organization_id', $organization->id)
            ->latest()
            ->get();

        return view('organizations.payouts', compact('organization', 'registries'));
    }

    /**
     * POST /organizations/{organization}/payouts/{payoutRegistry}/confirm
     * Org rep confirms receipt of funds.
     */
    public function confirm(Request $request, Organization $organization, PayoutRegistry $payoutRegistry): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->canInOrg('manage', $organization)) {
            abort(403);
        }

        if ($payoutRegistry->organization_id !== $organization->id) {
            abort(404);
        }

        if ($payoutRegistry->payment_received) {
            return redirect()->route('organizations.payouts.index', $organization)
                ->with('warning', 'Получение выплаты уже подтверждено.');
        }

        $payoutRegistry->update([
            'payment_received'    => true,
            'payment_received_at' => now(),
            'payment_received_by' => $user->id,
        ]);

        return redirect()->route('organizations.payouts.index', $organization)
            ->with('status', 'payout-confirmed');
    }
}
