<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TBankService
{
    private string $terminalKey;
    private string $password;
    private string $baseUrl;

    public function __construct()
    {
        $this->terminalKey = (string) config('tbank.terminal_key');
        $this->password    = (string) config('tbank.password');
        $this->baseUrl     = rtrim((string) config('tbank.base_url'), '/') . '/';
    }

    /**
     * Initialize a payment with T-Bank and return the payment page URL.
     * Called when participant submits a paid application or retries payment.
     *
     * @param  string|null  $orderId  Pass an existing orderId to re-use (e.g. retry flow).
     *                                When null, a new unique orderId is generated.
     * @return array{PaymentURL: string, PaymentId: string, OrderId: string}
     * @throws \RuntimeException if T-Bank returns an error
     */
    public function initPayment(Application $application, ?string $orderId = null): array
    {
        $contest = $application->contest;
        $amount  = $contest->entry_fee * 100; // T-Bank requires kopecks
        $orderId = $orderId ?? $this->makeOrderId($application);

        $params = [
            'TerminalKey'     => $this->terminalKey,
            'Amount'          => $amount,
            'OrderId'         => $orderId,
            'Description'     => 'Оргвзнос за участие в конкурсе «' . $contest->title . '»',
            'SuccessURL'      => route('payments.success', ['order' => $orderId]),
            'FailURL'         => route('payments.fail', ['order' => $orderId]),
            'NotificationURL' => route('payments.callback'),
        ];

        $params['Token'] = $this->generateToken($params);

        // Log outgoing request (exclude Token for security)
        Log::info('T-Bank Init request', [
            'url'      => $this->baseUrl . 'Init',
            'params'   => array_diff_key($params, ['Token' => '']),
        ]);

        $response = Http::post($this->baseUrl . 'Init', $params);
        $data     = $response->json() ?? [];


        Log::info('T-Bank Init response', [
            'http_status' => $response->status(),
            'body'        => $data,
        ]);

        if (! ($data['Success'] ?? false)) {
            $message   = $data['Message']   ?? $response->body();
            $errorCode = $data['ErrorCode'] ?? $response->status();
            throw new \RuntimeException("T-Bank Init failed: {$message} (ErrorCode: {$errorCode})");
        }

        return [
            'PaymentURL' => $data['PaymentURL'],
            'PaymentId'  => (string) $data['PaymentId'],
            'OrderId'    => $orderId,
        ];
    }

    /**
     * Verify that a webhook notification from T-Bank is authentic.
     * T-Bank signs notifications the same way we sign requests.
     */
    public function verifyWebhookToken(array $data): bool
    {
        $receivedToken = $data['Token'] ?? '';
        $expected      = $this->generateToken($data);

        return hash_equals($expected, $receivedToken);
    }

    /**
     * Generate the SHA-256 token required by T-Bank.
     * Algorithm: sort all params alphabetically by key (excluding Token, Receipt, DATA),
     * concatenate their values, append the terminal password, then SHA-256.
     */
    public function generateToken(array $params): string
    {
        $excluded = ['Token', 'Receipt', 'DATA'];

        $filtered = array_filter(
            $params,
            fn ($key) => ! in_array($key, $excluded, true),
            ARRAY_FILTER_USE_KEY
        );

        ksort($filtered);

        $values = implode('', $filtered);
        $values .= $this->password;

        return hash('sha256', $values);
    }

    /**
     * Generate the SHA-256 token required by T-Bank.
     * Algorithm: Add Password, exclude certain keys, sort alphabetically, concatenate, and hash.
     */
    // public function generateToken(array $params): string
    // {
    //     $excluded = ['Token', 'Receipt', 'DATA'];

    //     // 1. Filter out excluded keys
    //     $filtered = array_filter(
    //         $params,
    //         fn ($key) => ! in_array($key, $excluded, true),
    //         ARRAY_FILTER_USE_KEY
    //     );

    //     // 2. Add the Password to the array BEFORE sorting
    //     $filtered['Password'] = $this->password;

    //     // 3. Sort alphabetically by key
    //     ksort($filtered);

    //     // 4. Concatenate all values
    //     $values = implode('', $filtered);

    //     // 5. Return the SHA-256 hash
    //     return hash('sha256', $values);
    // }

    /**
     * Generate a unique order ID for a given application.
     * Format: app-{application_id}-{random_6_chars}
     */
    public function makeOrderId(Application $application): string
    {
        return 'app-' . $application->id . '-' . Str::random(6);
    }

    /**
     * Expose the resolved password for testing (allows tests to compute expected tokens
     * using the same source value, avoiding config resolution differences between
     * test bootstrap and service construction).
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
