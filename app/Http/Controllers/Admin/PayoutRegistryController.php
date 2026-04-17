<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PayoutRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PayoutRegistryController extends Controller
{
    /**
     * GET /admin/payout-registries
     */
    public function index(Request $request): View
    {
        $query = PayoutRegistry::with(['organization', 'contest', 'receivedBy'])
            ->latest();

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->input('organization_id'));
        }

        // "Только неоплаченные": default ON unless form was submitted with checkbox unchecked
        $filterSubmitted = $request->has('filter_submitted');
        $unpaidOnly      = ! $filterSubmitted || $request->boolean('unpaid_only');
        if ($unpaidOnly) {
            $query->whereRaw('payout_amount > COALESCE(transfer_amount, 0)');
        }

        $registries    = $query->paginate(20)->withQueryString();
        $organizations = Organization::orderBy('name')->get(['id', 'name']);

        return view('admin.payout-registries.index', compact('registries', 'organizations', 'unpaidOnly'));
    }

    /**
     * POST /admin/payout-registries
     * Create a payout registry entry for a contest.
     * Auto-calculates payout_amount: floor(total_accepted_fees * 0.6) - 2000
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'contest_id' => ['required', 'exists:contests,id'],
        ]);

        $contest = Contest::findOrFail($request->input('contest_id'));

        // Prevent duplicates
        if (PayoutRegistry::where('contest_id', $contest->id)->exists()) {
            return redirect()->route('admin.payout-registries.index')
                ->with('error', 'Реестр выплат для этого конкурса уже создан.');
        }

        $totalFees = Payment::where('contest_id', $contest->id)
            ->where('status', PaymentStatus::Accepted->value)
            ->sum('amount');

        $payoutAmount = max(0, (int) floor($totalFees * 0.6) - 2000);

        PayoutRegistry::create([
            'organization_id' => $contest->organization_id,
            'contest_id'      => $contest->id,
            'payout_amount'   => $payoutAmount,
        ]);

        // Mark all accepted payments for this contest as distributed
        Payment::where('contest_id', $contest->id)
            ->where('status', PaymentStatus::Accepted->value)
            ->update(['status' => PaymentStatus::Distributed->value]);

        return redirect()->route('admin.payout-registries.index')
            ->with('status', 'payout-registry-created');
    }

    /**
     * PUT /admin/payout-registries/{payoutRegistry}
     * Update transfer details (accountant fills these in).
     */
    public function update(Request $request, PayoutRegistry $payoutRegistry): RedirectResponse
    {
        $validated = $request->validate([
            'transfer_amount'          => ['nullable', 'integer', 'min:0'],
            'transfer_document_date'   => ['nullable', 'date'],
            'transfer_document_number' => ['nullable', 'string', 'max:100'],
        ]);

        $payoutRegistry->update($validated);

        return redirect()->route('admin.payout-registries.index')
            ->with('status', 'payout-registry-updated');
    }

    /**
     * GET /admin/payout-registries/{payoutRegistry}/requisites
     * Download a .txt file with org bank details + contest info for the accountant.
     */
    public function requisites(PayoutRegistry $payoutRegistry): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $payoutRegistry->load(['organization', 'contest']);
        $org = $payoutRegistry->organization;

        $lines = [
            'РЕКВИЗИТЫ ОРГАНИЗАЦИИ',
            str_repeat('-', 40),
            'Наименование:      ' . ($org->name ?? '—'),
            'ИНН:               ' . ($org->inn ?? '—'),
            'КПП:               ' . ($org->kpp ?? '—'),
            'БИК:               ' . ($org->bank_bik ?? '—'),
            'Расчётный счёт:    ' . ($org->bank_account ?? '—'),
            'Корр. счёт:        ' . ($org->correspondent_account ?? '—'),
            'Банк:              ' . ($org->bank_name ?? '—'),
            '',
            str_repeat('-', 40),
            'НАЗНАЧЕНИЕ ВЫПЛАТЫ',
            str_repeat('-', 40),
            'Конкурс:           ' . ($payoutRegistry->contest->title ?? '—'),
            'Сумма к выплате:   ' . number_format($payoutRegistry->payout_amount, 0, '.', ' ') . ' руб.',
        ];

        $content  = implode("\r\n", $lines);
        $filename = 'requisites-' . $payoutRegistry->id . '.txt';

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * POST /admin/payout-registries/{payoutRegistry}/document
     * Upload the transfer confirmation PDF.
     */
    public function uploadDocument(Request $request, PayoutRegistry $payoutRegistry): RedirectResponse
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        // Delete old document if exists
        if ($payoutRegistry->transfer_document_path) {
            Storage::disk('public')->delete($payoutRegistry->transfer_document_path);
        }

        $file     = $request->file('document');
        $filename = 'payout-docs/' . Str::ulid() . '.pdf';
        $file->storeAs('', $filename, 'public');

        $payoutRegistry->update(['transfer_document_path' => $filename]);

        return redirect()->route('admin.payout-registries.index')
            ->with('status', 'payout-document-uploaded');
    }
}
