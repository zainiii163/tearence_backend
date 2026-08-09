<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;

/**
 * Server-side PayPal Orders v2 — required because client-side
 * actions.order.create / capture are deprecated and rejected by PayPal.
 */
class PayPalOrderController extends Controller
{
    private function provider(): PayPalClient
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();

        return $provider;
    }

    private function credentialsConfigured(): bool
    {
        $mode = config('paypal.mode', 'sandbox');
        $clientId = config("paypal.{$mode}.client_id");
        $secret = config("paypal.{$mode}.client_secret");

        return filled($clientId)
            && filled($secret)
            && ! str_starts_with((string) $clientId, 'xxxxx')
            && ! str_starts_with((string) $secret, 'xxxxxxx');
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

        if (! $this->credentialsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal is not configured on the server. Set PAYPAL_LIVE_CLIENT_SECRET (or sandbox credentials) in .env.',
            ], 503);
        }

        $currency = strtoupper($validated['currency'] ?? config('paypal.currency', 'USD'));
        $amount = number_format((float) $validated['amount'], 2, '.', '');
        $description = $validated['description']
            ?? 'Worldwide Adverts purchase';

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

        if (! $this->credentialsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'PayPal is not configured on the server. Set PAYPAL_LIVE_CLIENT_SECRET (or sandbox credentials) in .env.',
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

    /** Public client id for the JS SDK (no secret). */
    public function clientConfig(): JsonResponse
    {
        $mode = config('paypal.mode', 'sandbox');
        $clientId = config("paypal.{$mode}.client_id") ?: '';

        return response()->json([
            'success' => true,
            'data' => [
                'client_id' => $clientId,
                'mode' => $mode,
                'currency' => config('paypal.currency', 'USD'),
                'configured' => $this->credentialsConfigured(),
            ],
        ]);
    }
}
