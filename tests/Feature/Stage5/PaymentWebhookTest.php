<?php

declare(strict_types=1);

namespace Tests\Feature\Stage5;

use App\Enums\ApplicationStatus;
use App\Enums\PaymentStatus;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use App\Services\TBankService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private TBankService $tbank;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tbank = new TBankService();
    }

    private function makePaymentWithApplication(): array
    {
        $org     = Organization::factory()->create();
        $user    = User::factory()->create(['role' => 'participant']);
        $contest = Contest::factory()->create([
            'organization_id' => $org->id,
            'created_by'      => $user->id,
            'is_paid'         => true,
            'entry_fee'       => 500,
            'status'          => 'accepting',
        ]);
        $application = Application::factory()->create([
            'contest_id' => $contest->id,
            'user_id'    => $user->id,
            'status'     => 'payment_pending',
        ]);
        $orderId = 'app-' . $application->id . '-testid';
        $payment = Payment::factory()->create([
            'application_id' => $application->id,
            'contest_id'     => $contest->id,
            'user_id'        => $user->id,
            'tbank_order_id' => $orderId,
            'amount'         => 500,
            'status'         => 'accepted',
        ]);

        return compact('application', 'payment', 'orderId');
    }

    public function test_callback_returns_400_for_invalid_token(): void
    {
        $payload = [
            'TerminalKey' => config('tbank.terminal_key'),
            'OrderId'     => 'app-1-fake',
            'Status'      => 'CONFIRMED',
            'PaymentId'   => '12345',
            'Token'       => 'invalid-token',
        ];

        $response = $this->postJson(route('payments.callback'), $payload);

        $response->assertStatus(400);
        $response->assertJson(['ok' => false]);
    }

    public function test_confirmed_webhook_activates_application(): void
    {
        ['application' => $application, 'orderId' => $orderId] = $this->makePaymentWithApplication();

        $payload = [
            'TerminalKey' => config('tbank.terminal_key'),
            'OrderId'     => $orderId,
            'Status'      => 'CONFIRMED',
            'PaymentId'   => '987654321',
            'Amount'      => 50000,
        ];
        $payload['Token'] = $this->tbank->generateToken($payload);

        $response = $this->postJson(route('payments.callback'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('applications', [
            'id'     => $application->id,
            'status' => ApplicationStatus::New->value,
        ]);
    }

    public function test_confirmed_webhook_sets_payment_status_and_stores_receipt(): void
    {
        ['payment' => $payment, 'orderId' => $orderId] = $this->makePaymentWithApplication();

        $payload = [
            'TerminalKey' => config('tbank.terminal_key'),
            'OrderId'     => $orderId,
            'Status'      => 'CONFIRMED',
            'PaymentId'   => '111222333',
            'Amount'      => 50000,
        ];
        $payload['Token'] = $this->tbank->generateToken($payload);

        $this->postJson(route('payments.callback'), $payload);

        $payment->refresh();
        $this->assertSame(PaymentStatus::Accepted, $payment->status);
        $this->assertSame('111222333', $payment->tbank_payment_id);
        $this->assertNotNull($payment->receipt_data);
    }

    public function test_cancelled_webhook_does_not_change_application_status(): void
    {
        ['application' => $application, 'orderId' => $orderId] = $this->makePaymentWithApplication();

        $payload = [
            'TerminalKey' => config('tbank.terminal_key'),
            'OrderId'     => $orderId,
            'Status'      => 'CANCELED',
            'PaymentId'   => '000000000',
            'Amount'      => 50000,
        ];
        $payload['Token'] = $this->tbank->generateToken($payload);

        $response = $this->postJson(route('payments.callback'), $payload);

        $response->assertOk();
        $this->assertDatabaseHas('applications', [
            'id'     => $application->id,
            'status' => 'payment_pending',
        ]);
    }

    public function test_webhook_for_unknown_order_id_returns_ok_silently(): void
    {
        $payload = [
            'TerminalKey' => config('tbank.terminal_key'),
            'OrderId'     => 'app-9999-nonexistent',
            'Status'      => 'CONFIRMED',
            'PaymentId'   => '123',
            'Amount'      => 10000,
        ];
        $payload['Token'] = $this->tbank->generateToken($payload);

        $response = $this->postJson(route('payments.callback'), $payload);

        $response->assertOk();
        $response->assertJson(['ok' => true]);
    }

    public function test_callback_route_is_csrf_exempt(): void
    {
        // Posting without CSRF token should NOT get a 419
        $payload = [
            'TerminalKey' => config('tbank.terminal_key'),
            'OrderId'     => 'app-9999-csrftest',
            'Status'      => 'CONFIRMED',
            'PaymentId'   => '1',
            'Amount'      => 100,
            'Token'       => 'bad-but-we-only-care-about-csrf-here',
        ];

        $response = $this->post(route('payments.callback'), $payload);

        // Should be 400 (bad token) not 419 (CSRF)
        $this->assertNotSame(419, $response->getStatusCode());
    }
}
