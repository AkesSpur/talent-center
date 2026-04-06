<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    /**
     * GET /admin/payments
     * List all payments with optional filters.
     */
    public function index(Request $request): View
    {
        $query = Payment::with(['application.user', 'contest.organization', 'user'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('contest_id')) {
            $query->where('contest_id', $request->input('contest_id'));
        }

        if ($request->filled('organization_id')) {
            $query->whereHas('contest', fn ($q) => $q->where('organization_id', $request->input('organization_id')));
        }

        $payments      = $query->paginate(30)->withQueryString();
        $contests      = Contest::where('is_paid', true)->orderBy('title')->get(['id', 'title', 'organization_id']);
        $organizations = Organization::orderBy('name')->get(['id', 'name']);

        return view('admin.payments.index', compact('payments', 'contests', 'organizations'));
    }
}
