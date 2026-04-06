<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TBankService;
use Tests\TestCase;

class TBankServiceTest extends TestCase
{
    private TBankService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TBankService();
    }

    public function test_generate_token_sorts_keys_alphabetically_and_hashes(): void
    {
        // T-Bank spec: sort keys A-Z, concat values, append password, SHA-256.
        // We verify the token is stable (same input always produces same output)
        // and that the verifyWebhookToken round-trip confirms it.
        $params = [
            'TerminalKey' => '1698927993527DEMO',
            'Amount'      => 100,
            'OrderId'     => 'test-123',
            'Description' => 'Test payment',
        ];

        $token1 = $this->service->generateToken($params);
        $token2 = $this->service->generateToken($params);

        // Token must be deterministic
        $this->assertSame($token1, $token2);

        // Token must be a valid 64-char SHA-256 hex string
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token1);

        // Round-trip: verifyWebhookToken must accept the generated token
        $paramsWithToken = array_merge($params, ['Token' => $token1]);
        $this->assertTrue($this->service->verifyWebhookToken($paramsWithToken));
    }

    public function test_generate_token_excludes_token_receipt_and_data_keys(): void
    {
        $params = [
            'TerminalKey' => '1698927993527DEMO',
            'Amount'      => 500,
            'Token'       => 'should-be-excluded',
            'Receipt'     => 'should-be-excluded',
            'DATA'        => 'should-be-excluded',
        ];

        $withExclusions    = $this->service->generateToken($params);
        $withoutExclusions = $this->service->generateToken([
            'TerminalKey' => '1698927993527DEMO',
            'Amount'      => 500,
        ]);

        $this->assertSame($withoutExclusions, $withExclusions);
    }

    public function test_verify_webhook_token_returns_true_for_valid_signature(): void
    {
        $params = [
            'TerminalKey' => '1698927993527DEMO',
            'Amount'      => 50000,
            'OrderId'     => 'app-1-abcdef',
            'Status'      => 'CONFIRMED',
            'PaymentId'   => '999888777',
        ];

        $params['Token'] = $this->service->generateToken($params);

        $this->assertTrue($this->service->verifyWebhookToken($params));
    }

    public function test_verify_webhook_token_returns_false_for_tampered_data(): void
    {
        $params = [
            'TerminalKey' => '1698927993527DEMO',
            'Amount'      => 50000,
            'OrderId'     => 'app-1-abcdef',
            'Status'      => 'CONFIRMED',
            'PaymentId'   => '999888777',
        ];

        $params['Token'] = $this->service->generateToken($params);

        // Tamper with amount after signing
        $params['Amount'] = 99999;

        $this->assertFalse($this->service->verifyWebhookToken($params));
    }

    public function test_make_order_id_format(): void
    {
        $application = new \App\Models\Application(['id' => 42]);
        // Set the key manually since it's not persisted
        $application->id = 42;

        $orderId = $this->service->makeOrderId($application);

        $this->assertStringStartsWith('app-42-', $orderId);
        $this->assertMatchesRegularExpression('/^app-42-[A-Za-z0-9]{6}$/', $orderId);
    }
}
