<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CryptoPayment;
use App\Models\Customer;
use App\Services\CryptoPayoutService;
use App\Services\NowPaymentsClient;
use App\Services\PaymentVerificationService;
use App\Support\CryptoRails;
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

            $this->persistInvoice([
                'provider' => 'crypto_mock',
                'ledger_id' => $paymentId,
                'provider_invoice_id' => $paymentId,
                'user_id' => $meta['user_id'],
                'currency' => $fiat,
                'pay_currency' => $payCurrency,
                'network' => CryptoRails::networkForPayCurrency($payCurrency),
                'amount' => $amount,
                'pay_amount' => $amount,
                'pay_address' => 'TMockAddressDoNotSendRealFunds',
                'status' => 'waiting',
                'order_id' => $orderId,
                'upsell_type' => $meta['upsell_type'],
                'upsell_id' => $meta['upsell_id'],
                'mock' => true,
            ]);

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

            $this->persistInvoice([
                'provider' => CryptoRails::PROVIDER,
                'ledger_id' => $ledgerId,
                'provider_invoice_id' => $paymentId,
                'user_id' => $meta['user_id'],
                'currency' => $fiat,
                'pay_currency' => $created['pay_currency'] ?? $payCurrency,
                'network' => CryptoRails::networkForPayCurrency($created['pay_currency'] ?? $payCurrency),
                'amount' => $amount,
                'pay_amount' => $created['pay_amount'] ?? null,
                'pay_address' => $created['pay_address'] ?? null,
                'status' => (string) ($created['payment_status'] ?? 'waiting'),
                'order_id' => $orderId,
                'upsell_type' => $meta['upsell_type'],
                'upsell_id' => $meta['upsell_id'],
                'invoice_url' => $created['invoice_url'] ?? null,
                'mock' => false,
                'raw_provider_json' => $created,
            ]);

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
                'network' => CryptoRails::networkForPayCurrency($created['pay_currency'] ?? $payCurrency),
                'provider' => CryptoRails::PROVIDER,
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
                    'tx_hash' => CryptoRails::extractTxHash($remote),
                    'network' => CryptoRails::networkForPayCurrency($remote['pay_currency'] ?? ''),
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
     * Saved receiving wallet (affiliates / sellers). No custody.
     */
    public function getWallet(Request $request): JsonResponse
    {
        $customer = $request->user() ?: auth('api')->user();
        if (! $customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'crypto_wallet_address' => $customer->crypto_wallet_address,
                'crypto_network' => $customer->crypto_network,
                'crypto_wallet_verified_at' => $customer->crypto_wallet_verified_at,
            ],
        ]);
    }

    public function saveWallet(Request $request): JsonResponse
    {
        $customer = $request->user() ?: auth('api')->user();
        if (! $customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validated = $request->validate([
            'crypto_wallet_address' => 'required|string|max:191',
            'crypto_network' => 'required|string|in:trc20,erc20,polygon',
        ]);

        $check = CryptoRails::validateAddress(
            $validated['crypto_wallet_address'],
            $validated['crypto_network']
        );
        if (! $check['ok']) {
            return response()->json(['success' => false, 'message' => $check['message']], 422);
        }

        $customer->crypto_wallet_address = $check['address'];
        $customer->crypto_network = $validated['crypto_network'];
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Crypto wallet saved',
            'data' => [
                'crypto_wallet_address' => $customer->crypto_wallet_address,
                'crypto_network' => $customer->crypto_network,
                'crypto_wallet_verified_at' => $customer->crypto_wallet_verified_at,
            ],
        ]);
    }
    public function webhook(Request $request): JsonResponse
    {
        $secret = (string) config('crypto.nowpayments.ipn_secret');
        if ($secret === '') {
            Log::warning('Crypto IPN received but NOWPAYMENTS_IPN_SECRET is empty');
        } elseif ($secret !== '') {
            $sig = (string) $request->header('x-nowpayments-sig', '');
            $raw = $request->getContent();
            $sorted = $request->all();
            ksort($sorted);
            $expected = hash_hmac('sha512', json_encode($sorted, JSON_UNESCAPED_SLASHES), $secret);
            $alt = hash_hmac('sha512', $raw, $secret);
            if ($sig === '' || (! hash_equals($expected, $sig) && ! hash_equals($alt, $sig))) {
                Log::warning('Crypto IPN signature mismatch');

                return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
            }
        }

        $payload = $request->all();

        $isPayout = isset($payload['batch_withdrawal_id'])
            || isset($payload['withdrawal_id'])
            || (isset($payload['address']) && isset($payload['extra_id']) && ! isset($payload['payment_id']));

        if ($isPayout) {
            app(CryptoPayoutService::class)->applyProviderStatus($payload);

            return response()->json(['success' => true]);
        }

        $providerId = (string) ($payload['payment_id'] ?? '');
        $status = strtolower((string) ($payload['payment_status'] ?? ''));

        if ($providerId === '') {
            return response()->json(['success' => false, 'message' => 'Missing payment_id'], 422);
        }

        $row = CryptoPayment::query()
            ->where('provider_invoice_id', $providerId)
            ->orWhere('ledger_id', 'NP-'.$providerId)
            ->first();
        $ledgerId = $row?->ledger_id
            ?: (Cache::get('crypto_np_map:'.$providerId) ?: 'NP-'.$providerId);

        if ($row) {
            $row->raw_webhook_json = $payload;
            $row->status = $status ?: $row->status;
            $row->tx_hash = CryptoRails::extractTxHash($payload) ?: $row->tx_hash;
            $row->save();
        }

        if ($row && CryptoRails::isPaidStatus($row->status) && $row->completed_at) {
            return response()->json(['success' => true, 'idempotent' => true]);
        }

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
        $row = CryptoPayment::query()->where('ledger_id', $ledgerId)->first();
        if ($row && $row->completed_at) {
            return [
                'status' => 'COMPLETED',
                'amount' => (float) $row->amount,
                'currency' => $row->currency,
                'provider' => $row->provider,
                'source' => 'crypto_idempotent',
                'captured' => true,
                'tx_hash' => $row->tx_hash,
                'mock' => (bool) $row->mock,
            ];
        }

        $cached = Cache::get('crypto_invoice:'.$ledgerId) ?: [];
        $amount = (float) (
            $remote['price_amount']
            ?? $cached['amount']
            ?? $row?->amount
            ?? 0
        );
        $currency = strtoupper((string) (
            $remote['price_currency']
            ?? $cached['currency']
            ?? $row?->currency
            ?? 'USD'
        ));
        $txHash = CryptoRails::extractTxHash($remote);

        $details = [
            'status' => 'COMPLETED',
            'amount' => $amount,
            'currency' => $currency,
            'provider' => $mock ? 'crypto_mock' : 'nowpayments',
            'source' => $mock ? 'crypto_mock_confirm' : 'crypto_ipn',
            'captured' => true,
            'pay_currency' => $remote['pay_currency'] ?? ($cached['pay_currency'] ?? $row?->pay_currency),
            'pay_amount' => $remote['pay_amount'] ?? ($cached['pay_amount'] ?? $row?->pay_amount),
            'tx_hash' => $txHash,
            'network' => $row?->network,
            'mock' => $mock,
        ];

        app(PaymentVerificationService::class)->rememberCompletedOrder($ledgerId, $details);
        Cache::put('crypto_completed_order:'.$ledgerId, $details, now()->addHours(48));

        if (is_array($cached)) {
            $cached['status'] = 'finished';
            Cache::put('crypto_invoice:'.$ledgerId, $cached, now()->addDays(2));
        }

        CryptoPayment::query()->updateOrCreate(
            ['ledger_id' => $ledgerId],
            [
                'status' => 'finished',
                'tx_hash' => $txHash,
                'completed_at' => now(),
                'raw_webhook_json' => $remote,
            ]
        );

        return $details;
    }

    /**
     * @param  array<string,mixed>  $attrs
     */
    private function persistInvoice(array $attrs): void
    {
        try {
            CryptoPayment::query()->updateOrCreate(
                ['ledger_id' => $attrs['ledger_id']],
                $attrs
            );
        } catch (Throwable $e) {
            Log::warning('Could not persist crypto invoice', [
                'ledger_id' => $attrs['ledger_id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
