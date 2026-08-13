<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NowPaymentsClient;
use App\Services\PaymentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Site-wide crypto checkout (all products via PaymentProcessor).
 * Mock mode mirrors PayPal sandbox mock when NOWPAYMENTS_API_KEY is unset.
 */
class CryptoPaymentController extends Controller
{
    private function useMock(): bool
    {
        if (! config('crypto.enabled', true)) {
            return false;
        }

        $flag = config('crypto.mock', 'auto');
        if ($flag === true || $flag === 1 || $flag === '1' || $flag === 'true') {
            return true;
        }
        if ($flag === false || $flag === 0 || $flag === '0' || $flag === 'false') {
            return false;
        }

        return ! app(NowPaymentsClient::class)->configured();
    }

    public function clientConfig(): JsonResponse
    {
        $enabled = (bool) config('crypto.enabled', true);
        $mock = $this->useMock();
        $liveReady = app(NowPaymentsClient::class)->configured();

        return response()->json([
            'success' => true,
            'enabled' => $enabled && ($mock || $liveReady),
            'mock' => $mock,
            'provider' => (string) config('crypto.provider', 'nowpayments'),
            'currency' => (string) config('crypto.currency', 'USD'),
            'pay_currencies' => config('crypto.pay_currencies', []),
            'settle_currency' => (string) config('crypto.settle_currency', 'usdttrc20'),
            'message' => ! $enabled
                ? 'Crypto payments are disabled'
                : ($mock
                    ? 'Crypto mock mode (no real chain charge)'
                    : 'Crypto payments ready'),
        ]);
    }

    /**
     * Create a crypto invoice / payment for any site checkout amount.
     */
    public function createInvoice(Request $request): JsonResponse
    {
        if (! config('crypto.enabled', true)) {
            return response()->json(['success' => false, 'message' => 'Crypto payments are disabled'], 503);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:100000',
            'currency' => 'nullable|string|size:3',
            'pay_currency' => 'nullable|string|max:32',
            'description' => 'nullable|string|max:200',
            'upsell_type' => 'nullable|string|max:64',
            'upsell_id' => 'nullable|string|max:64',
        ]);

        $fiat = strtoupper($validated['currency'] ?? config('crypto.currency', 'USD'));
        $amount = number_format((float) $validated['amount'], 2, '.', '');
        $payCurrency = strtolower(
            $validated['pay_currency']
                ?? (string) config('crypto.settle_currency', 'usdttrc20')
        );
        $description = $validated['description'] ?? 'Worldwide Adverts purchase';
        $orderId = 'WWA-'.strtoupper(Str::random(10));

        $meta = [
            'amount' => $amount,
            'currency' => $fiat,
            'pay_currency' => $payCurrency,
            'description' => $description,
            'upsell_type' => $validated['upsell_type'] ?? null,
            'upsell_id' => $validated['upsell_id'] ?? null,
            'user_id' => auth('api')->id(),
            'order_id' => $orderId,
            'created_at' => now()->toIso8601String(),
        ];

        if ($this->useMock()) {
            $paymentId = 'CRYPTO-MOCK-'.strtoupper(Str::random(12));
            Cache::put('crypto_invoice:'.$paymentId, array_merge($meta, [
                'payment_id' => $paymentId,
                'status' => 'waiting',
                'mock' => true,
                'pay_address' => 'TMockAddressDoNotSendRealFunds'.Str::lower(Str::random(8)),
                'pay_amount' => $amount,
            ]), now()->addMinutes((int) config('crypto.invoice_ttl_minutes', 60)));

            app(PaymentVerificationService::class)->rememberPendingOrder(
                $paymentId,
                (float) $amount,
                $fiat,
                $meta
            );

            Log::info('Crypto mock invoice created', ['payment_id' => $paymentId]);

            return response()->json([
                'success' => true,
                'id' => $paymentId,
                'payment_id' => $paymentId,
                'status' => 'waiting',
                'mock' => true,
                'pay_currency' => $payCurrency,
                'pay_amount' => $amount,
                'price_amount' => $amount,
                'price_currency' => $fiat,
                'pay_address' => 'TMockAddressDoNotSendRealFunds',
                'invoice_url' => null,
                'order_id' => $orderId,
                'message' => 'Mock crypto invoice — use Confirm mock payment (no real transfer).',
            ]);
        }

        try {
            $client = app(NowPaymentsClient::class);
            $ipn = url('/api/v1/crypto/webhook');
            $created = $client->createPayment([
                'price_amount' => (float) $amount,
                'price_currency' => strtolower($fiat),
                'pay_currency' => $payCurrency,
                'order_id' => $orderId,
                'order_description' => mb_substr($description, 0, 200),
                'ipn_callback_url' => $ipn,
            ]);

            $paymentId = (string) ($created['payment_id'] ?? $created['id'] ?? '');
            if ($paymentId === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Crypto provider did not return a payment id',
                    'provider_response' => $created,
                ], 502);
            }

            // Normalize id we use in our ledger
            $ledgerId = 'NP-'.$paymentId;

            Cache::put('crypto_invoice:'.$ledgerId, array_merge($meta, [
                'payment_id' => $ledgerId,
                'provider_payment_id' => $paymentId,
                'status' => (string) ($created['payment_status'] ?? 'waiting'),
                'mock' => false,
                'pay_address' => $created['pay_address'] ?? null,
                'pay_amount' => $created['pay_amount'] ?? null,
                'invoice_url' => $created['invoice_url'] ?? null,
                'raw' => $created,
            ]), now()->addMinutes((int) config('crypto.invoice_ttl_minutes', 60)));

            // Also index by provider id for webhooks
            Cache::put('crypto_np_map:'.$paymentId, $ledgerId, now()->addDays(2));

            app(PaymentVerificationService::class)->rememberPendingOrder(
                $ledgerId,
                (float) $amount,
                $fiat,
                array_merge($meta, ['provider_payment_id' => $paymentId])
            );

            return response()->json([
                'success' => true,
                'id' => $ledgerId,
                'payment_id' => $ledgerId,
                'provider_payment_id' => $paymentId,
                'status' => $created['payment_status'] ?? 'waiting',
                'mock' => false,
                'pay_currency' => $created['pay_currency'] ?? $payCurrency,
                'pay_amount' => $created['pay_amount'] ?? null,
                'price_amount' => $amount,
                'price_currency' => $fiat,
                'pay_address' => $created['pay_address'] ?? null,
                'invoice_url' => $created['invoice_url'] ?? null,
                'order_id' => $orderId,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unable to create crypto invoice',
            ], 503);
        }
    }

    /**
     * Poll invoice status (FE after user pays / for mock confirm).
     */
    public function status(string $paymentId): JsonResponse
    {
        $paymentId = trim($paymentId);
        $cached = Cache::get('crypto_invoice:'.$paymentId);

        if (str_starts_with($paymentId, 'CRYPTO-MOCK-')) {
            $completed = Cache::get('paypal_completed_order:'.$paymentId)
                ?: Cache::get('crypto_completed_order:'.$paymentId);
            if (is_array($completed)) {
                return response()->json([
                    'success' => true,
                    'id' => $paymentId,
                    'status' => 'finished',
                    'payment_status' => 'finished',
                    'mock' => true,
                    'completed' => true,
                ]);
            }

            return response()->json([
                'success' => true,
                'id' => $paymentId,
                'status' => is_array($cached) ? ($cached['status'] ?? 'waiting') : 'waiting',
                'payment_status' => is_array($cached) ? ($cached['status'] ?? 'waiting') : 'waiting',
                'mock' => true,
                'completed' => false,
                'invoice' => $cached,
            ]);
        }

        // Live: refresh from provider when possible
        if (str_starts_with($paymentId, 'NP-') && app(NowPaymentsClient::class)->configured()) {
            $providerId = substr($paymentId, 3);
            try {
                $remote = app(NowPaymentsClient::class)->getPayment($providerId);
                $status = strtolower((string) ($remote['payment_status'] ?? ''));
                if (in_array($status, ['finished', 'confirmed', 'sending'], true)) {
                    $this->markCompleted($paymentId, $remote, false);
                }
                if (is_array($cached)) {
                    $cached['status'] = $status ?: ($cached['status'] ?? 'waiting');
                    $cached['raw'] = $remote;
                    Cache::put('crypto_invoice:'.$paymentId, $cached, now()->addHours(24));
                }

                return response()->json([
                    'success' => true,
                    'id' => $paymentId,
                    'status' => $status,
                    'payment_status' => $status,
                    'mock' => false,
                    'completed' => in_array($status, ['finished', 'confirmed'], true),
                    'invoice' => $cached,
                    'provider' => $remote,
                ]);
            } catch (Throwable $e) {
                // fall through to cache
            }
        }

        $completed = Cache::get('crypto_completed_order:'.$paymentId)
            ?: Cache::get('paypal_completed_order:'.$paymentId);

        return response()->json([
            'success' => (bool) $cached || (bool) $completed,
            'id' => $paymentId,
            'status' => is_array($completed) ? 'finished' : (is_array($cached) ? ($cached['status'] ?? 'waiting') : 'unknown'),
            'payment_status' => is_array($completed) ? 'finished' : (is_array($cached) ? ($cached['status'] ?? 'waiting') : 'unknown'),
            'completed' => is_array($completed),
            'invoice' => $cached,
        ], $cached || $completed ? 200 : 404);
    }

    /**
     * Mock-only: mark invoice paid so confirm-payment can verify like PayPal mock.
     */
    public function confirmMock(Request $request, string $paymentId): JsonResponse
    {
        if (! $this->useMock() || ! str_starts_with($paymentId, 'CRYPTO-MOCK-')) {
            return response()->json([
                'success' => false,
                'message' => 'Mock confirm is only available in crypto mock mode.',
            ], 403);
        }

        $cached = Cache::get('crypto_invoice:'.$paymentId);
        if (! is_array($cached)) {
            return response()->json(['success' => false, 'message' => 'Invoice not found or expired'], 404);
        }

        $details = $this->markCompleted($paymentId, [
            'payment_status' => 'finished',
            'price_amount' => $cached['amount'] ?? 0,
            'price_currency' => $cached['currency'] ?? 'USD',
            'pay_currency' => $cached['pay_currency'] ?? null,
            'pay_amount' => $cached['pay_amount'] ?? $cached['amount'] ?? 0,
        ], true);

        return response()->json([
            'success' => true,
            'id' => $paymentId,
            'status' => 'COMPLETED',
            'mock' => true,
            'details' => $details,
        ]);
    }

    /**
     * NOWPayments IPN webhook (no JWT).
     */
    public function webhook(Request $request): JsonResponse
    {
        $secret = (string) config('crypto.nowpayments.ipn_secret');
        if ($secret !== '') {
            $sig = (string) $request->header('x-nowpayments-sig', '');
            $raw = $request->getContent();
            $sorted = $request->all();
            ksort($sorted);
            $expected = hash_hmac('sha512', json_encode($sorted, JSON_UNESCAPED_SLASHES), $secret);
            // Accept either sorted JSON hmac or raw body hmac (provider variants)
            $alt = hash_hmac('sha512', $raw, $secret);
            if ($sig === '' || (! hash_equals($expected, $sig) && ! hash_equals($alt, $sig))) {
                Log::warning('Crypto IPN signature mismatch');
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
            }
        }

        $payload = $request->all();
        $providerId = (string) ($payload['payment_id'] ?? '');
        $status = strtolower((string) ($payload['payment_status'] ?? ''));

        if ($providerId === '') {
            return response()->json(['success' => false, 'message' => 'Missing payment_id'], 422);
        }

        $ledgerId = Cache::get('crypto_np_map:'.$providerId) ?: 'NP-'.$providerId;

        if (in_array($status, ['finished', 'confirmed'], true)) {
            $this->markCompleted($ledgerId, $payload, false);
            Log::info('Crypto IPN completed', ['payment_id' => $ledgerId, 'status' => $status]);
        } else {
            $cached = Cache::get('crypto_invoice:'.$ledgerId);
            if (is_array($cached)) {
                $cached['status'] = $status;
                $cached['raw'] = $payload;
                Cache::put('crypto_invoice:'.$ledgerId, $cached, now()->addDays(2));
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * @param  array<string,mixed>  $remote
     * @return array<string,mixed>
     */
    private function markCompleted(string $ledgerId, array $remote, bool $mock): array
    {
        $cached = Cache::get('crypto_invoice:'.$ledgerId) ?: [];
        $amount = (float) (
            $remote['price_amount']
            ?? $cached['amount']
            ?? 0
        );
        $currency = strtoupper((string) (
            $remote['price_currency']
            ?? $cached['currency']
            ?? 'USD'
        ));

        $details = [
            'status' => 'COMPLETED',
            'amount' => $amount,
            'currency' => $currency,
            'provider' => $mock ? 'crypto_mock' : 'nowpayments',
            'source' => $mock ? 'crypto_mock_confirm' : 'crypto_ipn',
            'captured' => true,
            'pay_currency' => $remote['pay_currency'] ?? ($cached['pay_currency'] ?? null),
            'pay_amount' => $remote['pay_amount'] ?? ($cached['pay_amount'] ?? null),
            'tx_hash' => $remote['outcome_hash'] ?? $remote['payment_hash'] ?? null,
            'mock' => $mock,
        ];

        // Share completed cache key namespace with PayPal verifier for one lookup path
        app(PaymentVerificationService::class)->rememberCompletedOrder($ledgerId, $details);
        Cache::put('crypto_completed_order:'.$ledgerId, $details, now()->addHours(48));

        if (is_array($cached)) {
            $cached['status'] = 'finished';
            Cache::put('crypto_invoice:'.$ledgerId, $cached, now()->addDays(2));
        }

        return $details;
    }
}
