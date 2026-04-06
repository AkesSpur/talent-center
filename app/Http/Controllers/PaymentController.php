<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\Payment;
use App\Services\ActionLogService;
use App\Services\TBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly TBankService $tbank) {}

    /**
     * POST /applications/{application}/pay
     * Initiates T-Bank payment for a payment_pending application.
     * Called from the "retry payment" link in "Мои заявки".
     */
    public function initiate(Request $request, Application $application): RedirectResponse
    {
        $user = $request->user();

        // Only the applicant or their parent can initiate payment
        if (
            $application->user_id !== $user->id
            && $application->user->parent_id !== $user->id
        ) {
            abort(403);
        }

        if ($application->status !== ApplicationStatus::PaymentPending) {
            return redirect()->route('dashboard.applications')
                ->with('error', 'Заявка не требует оплаты.');
        }

        try {
            // Generate a fresh orderId for each retry attempt
            $newOrderId = $this->tbank->makeOrderId($application);
            $result     = $this->tbank->initPayment($application, $newOrderId);

            
        } catch (\Throwable $e) {
            Log::error('T-Bank payment retry failed', [
                'application_id' => $application->id,
                'user_id'        => $user->id,
                'error'          => $e->getMessage(),
            ]);

            return redirect()->route('dashboard.applications')
                ->with('error', 'Не удалось инициировать оплату: ' . $e->getMessage());
        }

        // Update payment record with new order/payment IDs
        Payment::updateOrCreate(
            ['application_id' => $application->id],
            [
                'tbank_order_id'   => $result['OrderId'],
                'tbank_payment_id' => $result['PaymentId'],
                'status'           => PaymentStatus::Pending->value,
            ]
        );

        return redirect()->away($result['PaymentURL']);
    }

    /**
     * POST /payments/callback   (public, CSRF-excluded)
     * T-Bank calls this URL when payment status changes.
     */
    public function callback(Request $request): JsonResponse
    {
        $data = $request->all();

        if (! $this->tbank->verifyWebhookToken($data)) {
            return response()->json(['ok' => false, 'error' => 'Invalid token'], 400);
        }

        $tbankPaymentId = (string) ($data['PaymentId'] ?? '');
        $status         = (string) ($data['Status'] ?? '');
        $orderId        = (string) ($data['OrderId'] ?? '');

        $payment = Payment::where('tbank_order_id', $orderId)->first();

        if (! $payment) {
            return response()->json(['ok' => true]); // Unknown order — silently accept
        }

        if ($status === 'CONFIRMED') {
            // Payment successful — activate the application
            $payment->update([
                'tbank_payment_id' => $tbankPaymentId,
                'status'           => PaymentStatus::Accepted->value,
                'receipt_data'     => $data,
            ]);

            $payment->application->update(['status' => ApplicationStatus::New->value]);

            ActionLogService::log('payment.confirmed', $payment, [
                'contest_id'     => $payment->contest_id,
                'user_id'        => $payment->user_id,
                'amount'         => $payment->amount,
                'tbank_order_id' => $orderId,
            ]);
        }
        // CANCELED / DEADLINE_EXPIRED: application stays payment_pending, user can retry

        return response()->json(['ok' => true]);
    }

    /**
     * GET /payments/success
     * T-Bank redirects user here after successful payment.
     */
    public function success(Request $request): View|RedirectResponse
    {
        $orderId = $request->query('order');
        $payment = $orderId ? Payment::where('tbank_order_id', $orderId)->first() : null;

        if ($payment && $payment->status === PaymentStatus::Accepted) {
            return view('payments.success', compact('payment'));
        }

        // If webhook hasn't fired yet, redirect to applications — it'll appear as new soon
        return redirect()->route('dashboard.applications')
            ->with('status', 'application-submitted');
    }

    /**
     * GET /payments/fail
     * T-Bank redirects user here on payment failure/cancel.
     */
    public function fail(Request $request): View
    {
        $orderId = $request->query('order');
        $payment = $orderId ? Payment::with('application.contest')->where('tbank_order_id', $orderId)->first() : null;

        return view('payments.fail', compact('payment'));
    }
}
