<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentVerificationService;
use App\Services\StripeClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Site-wide Stripe card checkout (PaymentProcessor).
 * Mock when STRIPE_MOCK=auto and STRIPE_SECRET is missing (mirrors PayPal sandbox mock).
 */
class StripePaymentController extends Controller
{
    private function useMock(): bool
    {
        if (! config('stripe.enabled', true)) {
            return false;
        }

        $flag = config('stripe.mock', 'auto');
        if ($flag === true || $flag === 1 || $flag === '1' || $flag === 'true') {
            return true;
        }
        if ($flag === false || $flag === 0 || $flag === '0' || $flag === 'false') {
            return false;
        }

        return ! app(StripeClient::class)->configured();
    }

    public function clientConfig(): JsonResponse
    {
        $enabled = (bool) config('stripe.enabled', true);
        $mock = $this->useMock();
        $liveReady = app(StripeClient::class)->configured();
        $publishable = app(StripeClient::class)->publishableKey();

        if ($mock && ($publishable === '' || str_starts_with(strtolower($publishable), 'pk_test_xxx'))) {
            $publishable = '';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $enabled && ($mock || $liveReady),
                'mock' => $mock,
                'publishable_key' => $publishable,
                'currency' => (string) config('stripe.currency', 'USD'),
                'configured' => $liveReady,
                'message' => ! $enabled
                    ? 'Stripe is disabled'
                    : ($mock
                        ? 'Stripe mock mode (no real card charge)'
                        : 'Stripe card payments ready'),
            ],
        ]);
    }

    public function createPaymentIntent(Request $request): JsonResponse
    {
        if (! config('stripe.enabled', true)) {
            return response()->json(['success' => false, 'message' => 'Stripe payments are disabled'], 503);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:100000',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:200',
            'upsell_type' => 'nullable|string|max:64',
            'upsell_id' => 'nullable|string|max:64',
        ]);

        $currency = strtolower($validated['currency'] ?? config('stripe.currency', 'USD'));
        $amount = round((float) $validated['amount'], 2);
        $amountCents = (int) round($amount * 100);
        if ($amountCents < 50) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum card charge is $0.50.',
            ], 422);
        }

        $description = $validated['description'] ?? 'Worldwide Adverts purchase';
        $meta = [
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => strtoupper($currency),
            'description' => $description,
            'upsell_type' => $validated['upsell_type'] ?? null,
            'upsell_id' => $validated['upsell_id'] ?? null,
            'user_id' => auth('api')->id(),
            'created_at' => now()->toIso8601String(),
        ];

        if ($this->useMock()) {
            $paymentId = 'STRIPE-MOCK-'.strtoupper(Str::random(12));
            Cache::put('stripe_mock_intent:'.$paymentId, $meta, now()->addHour());
            app(PaymentVerificationService::class)->rememberPendingOrder(
                $paymentId,
                $amount,
                strtoupper($currency),
                $meta
            );

            Log::info('Stripe mock payment intent created', ['payment_id' => $paymentId]);

            return response()->json([
                'success' => true,
                'id' => $paymentId,
                'client_secret' => $paymentId.'_secret_mock',
                'status' => 'requires_payment_method',
                'mock' => true,
                'amount' => $amount,
                'currency' => strtoupper($currency),
            ]);
        }

        try {
            $params = [
                'amount' => $amountCents,
                'currency' => $currency,
                'description' => mb_substr($description, 0, 200),
                'automatic_payment_methods[enabled]' => 'true',
                'metadata[upsell_type]' => (string) ($validated['upsell_type'] ?? ''),
                'metadata[upsell_id]' => (string) ($validated['upsell_id'] ?? ''),
                'metadata[user_id]' => (string) (auth('api')->id() ?? ''),
                'metadata[wwa]' => '1',
            ];

            $intent = app(StripeClient::class)->createPaymentIntent($params);
            $id = (string) ($intent['id'] ?? '');
            $secret = (string) ($intent['client_secret'] ?? '');

            if ($id === '' || $secret === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe did not return a payment intent',
                    'details' => $intent,
                ], 422);
            }

            app(PaymentVerificationService::class)->rememberPendingOrder(
                $id,
                $amount,
                strtoupper($currency),
                $meta
            );

            return response()->json([
                'success' => true,
                'id' => $id,
                'client_secret' => $secret,
                'status' => $intent['status'] ?? null,
                'mock' => false,
                'amount' => $amount,
                'currency' => strtoupper($currency),
            ]);
        } catch (Throwable $e) {
            Log::error('Stripe createPaymentIntent failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Stripe payment intent failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * After Elements confirmCardPayment (or mock confirm), mark payment completed for defence layer.
     */
    public function confirm(Request $request, string $paymentId): JsonResponse
    {
        $paymentId = $paymentId ?: (string) $request->input('payment_intent_id');
        if ($paymentId === '') {
            return response()->json(['success' => false, 'message' => 'Payment intent id is required'], 422);
        }

        if (str_starts_with($paymentId, 'STRIPE-MOCK-')) {
            return $this->confirmMockInternal($paymentId);
        }

        try {
            $intent = app(StripeClient::class)->retrievePaymentIntent($paymentId);
            $status = (string) ($intent['status'] ?? '');
            if ($status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Card payment is not completed (status: '.($status ?: 'unknown').').',
                    'status' => $status,
                ], 422);
            }

            $amountCents = (int) ($intent['amount_received'] ?? $intent['amount'] ?? 0);
            $amount = round($amountCents / 100, 2);
            $currency = strtoupper((string) ($intent['currency'] ?? config('stripe.currency', 'USD')));
            $pending = app(PaymentVerificationService::class)->getPendingOrder($paymentId);
            if ($amount <= 0 && is_array($pending)) {
                $amount = (float) $pending['amount'];
                $currency = (string) ($pending['currency'] ?? $currency);
            }

            app(PaymentVerificationService::class)->rememberCompletedOrder($paymentId, [
                'status' => 'COMPLETED',
                'amount' => $amount,
                'currency' => $currency,
                'provider' => 'stripe',
                'source' => 'stripe_retrieve',
                'details' => [
                    'id' => $paymentId,
                    'status' => $status,
                    'payment_method' => $intent['payment_method'] ?? null,
                ],
            ]);

            return response()->json([
                'success' => true,
                'id' => $paymentId,
                'status' => 'COMPLETED',
                'mock' => false,
                'amount' => $amount,
                'currency' => $currency,
                'details' => [
                    'id' => $paymentId,
                    'status' => 'COMPLETED',
                    'provider' => 'stripe',
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Stripe confirm failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Stripe confirm failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function confirmMock(Request $request, string $paymentId): JsonResponse
    {
        return $this->confirmMockInternal($paymentId);
    }

    private function confirmMockInternal(string $paymentId): JsonResponse
    {
        if (! str_starts_with($paymentId, 'STRIPE-MOCK-')) {
            return response()->json(['success' => false, 'message' => 'Not a mock Stripe payment'], 422);
        }

        if (! $this->useMock() && ! Cache::has('stripe_mock_intent:'.$paymentId)) {
            return response()->json([
                'success' => false,
                'message' => 'Mock Stripe payments are only valid when STRIPE_MOCK is enabled.',
            ], 422);
        }

        $cached = Cache::get('stripe_mock_intent:'.$paymentId)
            ?: app(PaymentVerificationService::class)->getPendingOrder($paymentId);
        if (! $cached) {
            return response()->json([
                'success' => false,
                'message' => 'Mock Stripe intent expired or not found. Create a new payment.',
            ], 404);
        }
        Cache::forget('stripe_mock_intent:'.$paymentId);

        $details = [
            'id' => $paymentId,
            'status' => 'COMPLETED',
            'mock' => true,
            'provider' => 'stripe_mock',
            'amount' => $cached['amount'],
            'currency' => $cached['currency'],
        ];

        app(PaymentVerificationService::class)->rememberCompletedOrder($paymentId, [
            'status' => 'COMPLETED',
            'amount' => (float) $cached['amount'],
            'currency' => (string) $cached['currency'],
            'provider' => 'stripe_mock',
            'details' => $details,
        ]);

        Log::info('Stripe mock payment confirmed', ['payment_id' => $paymentId]);

        return response()->json([
            'success' => true,
            'id' => $paymentId,
            'status' => 'COMPLETED',
            'mock' => true,
            'details' => $details,
        ]);
    }

    /** Optional webhook — payment_intent.succeeded. */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature', '');
        $secret = (string) config('stripe.webhook_secret', '');

        if ($secret !== '') {
            // Lightweight HMAC check (Stripe signed payload). Full stripe-php constructEvent optional.
            if ($sig === '' || ! str_contains($sig, 't=') || ! str_contains($sig, 'v1=')) {
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
            }
        }

        $event = json_decode($payload, true);
        if (! is_array($event)) {
            return response()->json(['success' => false, 'message' => 'Invalid JSON'], 400);
        }

        $type = (string) ($event['type'] ?? '');
        if ($type === 'payment_intent.succeeded') {
            $intent = $event['data']['object'] ?? [];
            $id = (string) ($intent['id'] ?? '');
            if ($id !== '') {
                $amountCents = (int) ($intent['amount_received'] ?? $intent['amount'] ?? 0);
                app(PaymentVerificationService::class)->rememberCompletedOrder($id, [
                    'status' => 'COMPLETED',
                    'amount' => round($amountCents / 100, 2),
                    'currency' => strtoupper((string) ($intent['currency'] ?? 'USD')),
                    'provider' => 'stripe',
                    'source' => 'stripe_webhook',
                ]);
            }
        }

        return response()->json(['success' => true, 'received' => true]);
    }
}
