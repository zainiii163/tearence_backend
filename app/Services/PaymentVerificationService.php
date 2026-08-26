<?php

namespace App\Services;

use App\Models\VerifiedPaymentReference;
use App\Services\StripeClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;

/**
 * Defence layer for all marketplace payment confirms.
 * Never trust a client-supplied payment_id alone — verify with PayPal (or sandbox mock ledger).
 */
class PaymentVerificationService
{
    public const TOLERANCE = 0.02;

    /**
     * Verify payment_id against provider, amount, currency; enforce one-time use.
     *
     * @return array{payment_id:string,provider:string,amount:float,currency:string,status:string}
     *
     * @throws RuntimeException
     */
    public function assertVerifiedPayment(
        string $paymentId,
        float $expectedAmount,
        string $expectedCurrency = 'USD',
        ?string $purchaseType = null,
        string|int|null $purchaseId = null,
        ?int $userId = null
    ): array {
        $paymentId = trim($paymentId);
        if ($paymentId === '' || strlen($paymentId) < 4) {
            throw new RuntimeException('A valid payment reference is required.');
        }

        // Obvious fakes
        $lower = strtolower($paymentId);
        foreach (['paid', 'free', 'test', 'success', 'ok', 'true', '1', 'dummy', 'fake'] as $bad) {
            if ($lower === $bad) {
                throw new RuntimeException('Invalid payment reference.');
            }
        }

        if ($expectedAmount < 0.01) {
            throw new RuntimeException('Expected payment amount is invalid.');
        }

        $expectedCurrency = strtoupper($expectedCurrency ?: 'USD');

        $existing = VerifiedPaymentReference::where('payment_id', $paymentId)->first();
        if ($existing) {
            $samePurchase =
                (string) $existing->purchase_type === (string) $purchaseType
                && (string) $existing->purchase_id === (string) $purchaseId;
            if (! $samePurchase) {
                Log::warning('Payment defence: reused payment_id blocked', [
                    'payment_id' => $paymentId,
                    'existing_purchase' => [$existing->purchase_type, $existing->purchase_id],
                    'attempt' => [$purchaseType, $purchaseId],
                    'user_id' => $userId,
                ]);
                throw new RuntimeException('This payment has already been used.');
            }

            // Idempotent re-confirm for same purchase
            $this->assertAmountClose((float) $existing->amount, $expectedAmount, $expectedCurrency, (string) $existing->currency);

            return [
                'payment_id' => $paymentId,
                'provider' => $existing->provider,
                'amount' => (float) $existing->amount,
                'currency' => (string) $existing->currency,
                'status' => $existing->status,
            ];
        }

        $details = $this->fetchProviderPayment($paymentId);
        $status = strtoupper((string) ($details['status'] ?? ''));
        if (! in_array($status, ['COMPLETED', 'APPROVED'], true)) {
            throw new RuntimeException('Payment is not completed with the provider (status: '.($status ?: 'unknown').').');
        }

        $capturedAmount = (float) ($details['amount'] ?? 0);
        $capturedCurrency = strtoupper((string) ($details['currency'] ?? 'USD'));
        $this->assertAmountClose($capturedAmount, $expectedAmount, $expectedCurrency, $capturedCurrency);

        // If only APPROVED, require capture evidence in details or reject
        if ($status === 'APPROVED' && empty($details['captured'])) {
            throw new RuntimeException('Payment approved but not captured. Complete PayPal capture first.');
        }

        $provider = (string) ($details['provider'] ?? 'paypal');

        VerifiedPaymentReference::create([
            'payment_id' => $paymentId,
            'provider' => $provider,
            'status' => 'completed',
            'amount' => round($capturedAmount, 2),
            'currency' => $capturedCurrency,
            'purchase_type' => $purchaseType,
            'purchase_id' => $purchaseId !== null ? (string) $purchaseId : null,
            'user_id' => $userId,
            'meta' => [
                'raw_status' => $status,
                'verified_via' => $details['source'] ?? 'provider',
            ],
            'verified_at' => now(),
        ]);

        Log::info('Payment defence: verified', [
            'payment_id' => $paymentId,
            'amount' => $capturedAmount,
            'currency' => $capturedCurrency,
            'purchase_type' => $purchaseType,
            'purchase_id' => $purchaseId,
            'user_id' => $userId,
        ]);

        return [
            'payment_id' => $paymentId,
            'provider' => $provider,
            'amount' => $capturedAmount,
            'currency' => $capturedCurrency,
            'status' => 'completed',
        ];
    }

    /**
     * Remember expected amount when creating a PayPal order (server-side defence).
     */
    public function rememberPendingOrder(
        string $orderId,
        float $amount,
        string $currency = 'USD',
        array $meta = []
    ): void {
        Cache::put(
            $this->pendingKey($orderId),
            [
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => strtoupper($currency),
                'meta' => $meta,
                'created_at' => now()->toIso8601String(),
            ],
            now()->addHours(24)
        );
    }

    /**
     * After successful capture, store completed snapshot for confirm endpoints.
     */
    public function rememberCompletedOrder(string $orderId, array $details): void
    {
        Cache::put(
            $this->completedKey($orderId),
            $details,
            now()->addHours(48)
        );
    }

    public function getPendingOrder(string $orderId): ?array
    {
        $data = Cache::get($this->pendingKey($orderId));

        return is_array($data) ? $data : null;
    }

    private function assertAmountClose(
        float $actual,
        float $expected,
        string $expectedCurrency,
        string $actualCurrency
    ): void {
        if (strtoupper($actualCurrency) !== strtoupper($expectedCurrency)) {
            throw new RuntimeException(
                "Payment currency mismatch (paid {$actualCurrency}, expected {$expectedCurrency})."
            );
        }
        if (abs($actual - $expected) > self::TOLERANCE) {
            Log::warning('Payment defence: amount mismatch', [
                'actual' => $actual,
                'expected' => $expected,
            ]);
            throw new RuntimeException(
                'Payment amount does not match the order total. Contact support if charged incorrectly.'
            );
        }
    }

    /**
     * @return array{status:string,amount:float,currency:string,provider:string,source:string,captured?:bool}
     */
    private function fetchProviderPayment(string $paymentId): array
    {
        // Prefer completed capture cache (PayPal or crypto)
        $completed = Cache::get($this->completedKey($paymentId))
            ?: Cache::get('crypto_completed_order:'.$paymentId);
        if (is_array($completed) && ! empty($completed['status'])) {
            return [
                'status' => (string) $completed['status'],
                'amount' => (float) ($completed['amount'] ?? 0),
                'currency' => (string) ($completed['currency'] ?? 'USD'),
                'provider' => (string) ($completed['provider'] ?? $this->guessProvider($paymentId)),
                'source' => (string) ($completed['source'] ?? 'cache_completed'),
                'captured' => true,
            ];
        }

        // Crypto mock must be confirmed first
        if (str_starts_with($paymentId, 'CRYPTO-MOCK-')) {
            throw new RuntimeException('Crypto mock payment was not confirmed. Complete crypto checkout first.');
        }

        // Stripe mock must be confirmed first
        if (str_starts_with($paymentId, 'STRIPE-MOCK-')) {
            throw new RuntimeException('Stripe mock payment was not confirmed. Complete card checkout first.');
        }

        // Live Stripe PaymentIntent
        if (str_starts_with($paymentId, 'pi_')) {
            return $this->fetchStripePayment($paymentId);
        }

        // Live NOWPayments — refresh if still open
        if (str_starts_with($paymentId, 'NP-')) {
            return $this->fetchNowPaymentsPayment($paymentId);
        }

        // Pending create cache (mock not yet captured — reject)
        if (str_starts_with($paymentId, 'MOCK-')) {
            $mode = strtolower((string) config('paypal.mode', 'sandbox'));
            if ($mode !== 'sandbox') {
                throw new RuntimeException('Mock payments are not allowed in live mode.');
            }
            $pending = $this->getPendingOrder($paymentId) ?: Cache::get('paypal_mock_order:'.$paymentId);
            if (! is_array($pending)) {
                throw new RuntimeException('Mock payment not found or expired. Complete checkout again.');
            }
            // Mock must have been captured (completed cache). If only pending exists, fail.
            throw new RuntimeException('Mock payment was not captured. Complete PayPal capture first.');
        }

        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $response = $provider->showOrderDetails($paymentId);

            $status = (string) ($response['status'] ?? '');
            $unit = $response['purchase_units'][0] ?? [];
            $amountNode = $unit['amount']
                ?? ($unit['payments']['captures'][0]['amount'] ?? null)
                ?? [];
            $value = (float) ($amountNode['value'] ?? 0);
            $currency = (string) ($amountNode['currency_code'] ?? 'USD');

            // Prefer capture amount if present
            $captures = $unit['payments']['captures'] ?? [];
            if (is_array($captures) && count($captures) > 0) {
                $cap = $captures[0];
                $value = (float) ($cap['amount']['value'] ?? $value);
                $currency = (string) ($cap['amount']['currency_code'] ?? $currency);
                if (($cap['status'] ?? '') === 'COMPLETED') {
                    $status = 'COMPLETED';
                }
            }

            if ($value <= 0 && ($pending = $this->getPendingOrder($paymentId))) {
                $value = (float) $pending['amount'];
                $currency = (string) ($pending['currency'] ?? $currency);
            }

            return [
                'status' => $status,
                'amount' => $value,
                'currency' => $currency,
                'provider' => 'paypal',
                'source' => 'paypal_show_order',
                'captured' => strtoupper($status) === 'COMPLETED',
            ];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Payment defence: PayPal lookup failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Unable to verify payment with PayPal. Try again or contact support.');
        }
    }

    private function guessProvider(string $paymentId): string
    {
        if (str_starts_with($paymentId, 'CRYPTO-MOCK-')) {
            return 'crypto_mock';
        }
        if (str_starts_with($paymentId, 'STRIPE-MOCK-')) {
            return 'stripe_mock';
        }
        if (str_starts_with($paymentId, 'pi_')) {
            return 'stripe';
        }
        if (str_starts_with($paymentId, 'NP-')) {
            return 'nowpayments';
        }
        if (str_starts_with($paymentId, 'MOCK-')) {
            return 'mock';
        }

        return 'paypal';
    }

    /**
     * @return array{status:string,amount:float,currency:string,provider:string,source:string,captured?:bool}
     */
    private function fetchStripePayment(string $paymentId): array
    {
        try {
            $intent = app(StripeClient::class)->retrievePaymentIntent($paymentId);
            $statusRaw = (string) ($intent['status'] ?? '');
            $succeeded = $statusRaw === 'succeeded';
            $amountCents = (int) ($intent['amount_received'] ?? $intent['amount'] ?? 0);
            $amount = round($amountCents / 100, 2);
            $currency = strtoupper((string) ($intent['currency'] ?? 'USD'));

            if ($amount <= 0 && ($pending = $this->getPendingOrder($paymentId))) {
                $amount = (float) $pending['amount'];
                $currency = (string) ($pending['currency'] ?? $currency);
            }

            if ($succeeded) {
                $this->rememberCompletedOrder($paymentId, [
                    'status' => 'COMPLETED',
                    'amount' => $amount,
                    'currency' => $currency,
                    'provider' => 'stripe',
                    'source' => 'stripe_retrieve',
                    'captured' => true,
                ]);
            }

            return [
                'status' => $succeeded ? 'COMPLETED' : strtoupper($statusRaw ?: 'PENDING'),
                'amount' => $amount,
                'currency' => $currency,
                'provider' => 'stripe',
                'source' => 'stripe_retrieve',
                'captured' => $succeeded,
            ];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Payment defence: Stripe lookup failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Unable to verify card payment with Stripe. Try again or contact support.');
        }
    }

    /**
     * @return array{status:string,amount:float,currency:string,provider:string,source:string,captured?:bool}
     */
    private function fetchNowPaymentsPayment(string $ledgerId): array
    {
        $providerId = str_starts_with($ledgerId, 'NP-') ? substr($ledgerId, 3) : $ledgerId;
        try {
            $remote = app(NowPaymentsClient::class)->getPayment($providerId);
            $statusRaw = strtolower((string) ($remote['payment_status'] ?? ''));
            $finished = in_array($statusRaw, ['finished', 'confirmed'], true);
            $amount = (float) ($remote['price_amount'] ?? 0);
            $currency = strtoupper((string) ($remote['price_currency'] ?? 'USD'));
            if ($amount <= 0 && ($pending = $this->getPendingOrder($ledgerId))) {
                $amount = (float) $pending['amount'];
                $currency = (string) ($pending['currency'] ?? $currency);
            }

            if ($finished) {
                $this->rememberCompletedOrder($ledgerId, [
                    'status' => 'COMPLETED',
                    'amount' => $amount,
                    'currency' => $currency,
                    'provider' => 'nowpayments',
                    'source' => 'nowpayments_get_payment',
                    'captured' => true,
                ]);
            }

            return [
                'status' => $finished ? 'COMPLETED' : strtoupper($statusRaw ?: 'WAITING'),
                'amount' => $amount,
                'currency' => $currency,
                'provider' => 'nowpayments',
                'source' => 'nowpayments_get_payment',
                'captured' => $finished,
            ];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Payment defence: NOWPayments lookup failed', [
                'payment_id' => $ledgerId,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Unable to verify crypto payment. Wait for confirmation or contact support.');
        }
    }

    private function pendingKey(string $orderId): string
    {
        return 'paypal_pending_order:'.$orderId;
    }

    private function completedKey(string $orderId): string
    {
        return 'paypal_completed_order:'.$orderId;
    }
}
