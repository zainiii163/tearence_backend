<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;

/**
 * Server-side PayPal Orders v2 — required because client-side
 * actions.order.create / capture are deprecated and rejected by PayPal.
 *
 * Sandbox: set PAYPAL_MODE=sandbox + sandbox client id/secret.
 * Local QA without keys: PAYPAL_SANDBOX_MOCK=true (or auto when sandbox keys missing).
 */
class PayPalOrderController extends Controller
{
    private function mode(): string
    {
        $mode = strtolower((string) config('paypal.mode', 'sandbox'));

        return in_array($mode, ['sandbox', 'live'], true) ? $mode : 'sandbox';
    }

    private function credentialsConfigured(): bool
    {
        $mode = $this->mode();
        $clientId = (string) config("paypal.{$mode}.client_id");
        $secret = (string) config("paypal.{$mode}.client_secret");

        if ($clientId === '' || $secret === '') {
            return false;
        }

        $placeholders = ['xxxxx', 'xxxxxxx', 'your_', 'change_me', 'placeholder'];
        $hay = strtolower($clientId.' '.$secret);
        foreach ($placeholders as $p) {
            if (str_contains($hay, $p)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mock is allowed only in sandbox mode.
     * auto = mock when sandbox credentials are not configured.
     */
    private function useMock(): bool
    {
        if ($this->mode() !== 'sandbox') {
            return false;
        }

        $flag = config('paypal.sandbox_mock');
        if ($flag === true || $flag === 1 || $flag === '1' || $flag === 'true') {
            return true;
        }
        if ($flag === false || $flag === 0 || $flag === '0' || $flag === 'false') {
            return false;
        }

        // "auto" / anything else → mock when real sandbox keys are missing
        return ! $this->credentialsConfigured();
    }

    private function provider(): PayPalClient
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        return $provider;
    }

    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:100000',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:127',
            'upsell_type' => 'nullable|string|max:64',
            'upsell_id' => 'nullable|string|max:64',
        ]);

        $currency = strtoupper($validated['currency'] ?? config('paypal.currency', 'USD'));
        $amount = number_format((float) $validated['amount'], 2, '.', '');
        $description = $validated['description'] ?? 'Worldwide Adverts purchase';

        if ($this->useMock()) {
            $orderId = 'MOCK-'.strtoupper(Str::random(12));
            Cache::put('paypal_mock_order:'.$orderId, [
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                'upsell_type' => $validated['upsell_type'] ?? null,
                'upsell_id' => $validated['upsell_id'] ?? null,
                'user_id' => auth('api')->id(),
                'created_at' => now()->toIso8601String(),
            ], now()->addHour());

            Log::info('PayPal sandbox mock order created', ['order_id' => $orderId]);

            return response()->json([
                'success' => true,
                'id' => $orderId,
                'status' => 'CREATED',
                'mock' => true,
                'mode' => 'sandbox',
            ]);
        }

        if (! $this->credentialsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal sandbox is not configured. Set PAYPAL_SANDBOX_CLIENT_ID and PAYPAL_SANDBOX_CLIENT_SECRET, or PAYPAL_SANDBOX_MOCK=true.',
            ], 503);
        }

        try {
            $provider = $this->provider();
            $unit = [
                'amount' => [
                    'currency_code' => $currency,
                    'value' => $amount,
                ],
                'description' => mb_substr($description, 0, 127),
            ];
            $customId = trim(($validated['upsell_type'] ?? '').':'.($validated['upsell_id'] ?? ''), ':');
            if ($customId !== '') {
                $unit['custom_id'] = mb_substr($customId, 0, 127);
            }

            $response = $provider->createOrder([
                'intent' => 'CAPTURE',
                'purchase_units' => [$unit],
            ]);

            if (! empty($response['id'])) {
                return response()->json([
                    'success' => true,
                    'id' => $response['id'],
                    'status' => $response['status'] ?? null,
                    'mock' => false,
                    'mode' => $this->mode(),
                ]);
            }

            Log::warning('PayPal createOrder failed', ['response' => $response]);

            return response()->json([
                'success' => false,
                'message' => $response['message']
                    ?? $response['error']['message']
                    ?? 'Unable to create PayPal order',
                'details' => $response,
            ], 422);
        } catch (Throwable $e) {
            Log::error('PayPal createOrder exception', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PayPal order creation failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function capture(Request $request, string $orderId): JsonResponse
    {
        $request->validate([
            'order_id' => 'nullable|string|max:64',
        ]);

        $orderId = $orderId ?: (string) $request->input('order_id');
        if ($orderId === '') {
            return response()->json([
                'success' => false,
                'message' => 'PayPal order id is required',
            ], 422);
        }

        if (str_starts_with($orderId, 'MOCK-')) {
            if ($this->mode() !== 'sandbox') {
                return response()->json([
                    'success' => false,
                    'message' => 'Mock PayPal orders are only valid in sandbox mode.',
                ], 422);
            }

            $cached = Cache::pull('paypal_mock_order:'.$orderId);
            if (! $cached) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mock order expired or not found. Create a new payment.',
                ], 404);
            }

            $details = [
                'id' => $orderId,
                'status' => 'COMPLETED',
                'mock' => true,
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $cached['currency'],
                        'value' => $cached['amount'],
                    ],
                    'description' => $cached['description'],
                    'custom_id' => trim(($cached['upsell_type'] ?? '').':'.($cached['upsell_id'] ?? ''), ':'),
                ]],
                'payer' => [
                    'email_address' => 'sandbox-buyer@worldwideadverts.test',
                    'name' => ['given_name' => 'Sandbox', 'surname' => 'Buyer'],
                ],
            ];

            Log::info('PayPal sandbox mock order captured', ['order_id' => $orderId]);

            return response()->json([
                'success' => true,
                'id' => $orderId,
                'status' => 'COMPLETED',
                'mock' => true,
                'mode' => 'sandbox',
                'details' => $details,
            ]);
        }

        if (! $this->credentialsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal is not configured on the server. Set sandbox or live credentials in .env.',
            ], 503);
        }

        try {
            $provider = $this->provider();
            $response = $provider->capturePaymentOrder($orderId);

            $status = $response['status'] ?? null;
            if ($status === 'COMPLETED' || ! empty($response['id'])) {
                return response()->json([
                    'success' => true,
                    'id' => $response['id'] ?? $orderId,
                    'status' => $status,
                    'mock' => false,
                    'mode' => $this->mode(),
                    'details' => $response,
                ]);
            }

            Log::warning('PayPal capture failed', [
                'order_id' => $orderId,
                'response' => $response,
            ]);

            return response()->json([
                'success' => false,
                'message' => $response['message']
                    ?? $response['error']['message']
                    ?? 'Unable to capture PayPal payment',
                'details' => $response,
            ], 422);
        } catch (Throwable $e) {
            Log::error('PayPal capture exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'PayPal capture failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /** Public client id + mode for the JS SDK (no secret). */
    public function clientConfig(): JsonResponse
    {
        $mode = $this->mode();
        $configured = $this->credentialsConfigured();
        $mock = $this->useMock();
        $clientId = (string) (config("paypal.{$mode}.client_id") ?: '');

        if ($mock && (! $configured || str_starts_with(strtolower($clientId), 'xxxxx'))) {
            // PayPal JS still needs a client-id string; "sb" is the public sandbox demo id
            $clientId = 'sb';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'client_id' => $clientId,
                'mode' => $mode,
                'currency' => config('paypal.currency', 'USD'),
                'configured' => $configured,
                'mock' => $mock,
                'sandbox' => $mode === 'sandbox',
            ],
        ]);
    }
}
