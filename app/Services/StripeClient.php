<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Thin Stripe REST client (PaymentIntents) — no stripe/stripe-php required.
 */
class StripeClient
{
    public function configured(): bool
    {
        $secret = (string) config('stripe.secret', '');
        if ($secret === '') {
            return false;
        }

        $hay = strtolower($secret);
        foreach (['xxxxx', 'your_', 'change_me', 'placeholder', 'sk_test_xxx'] as $p) {
            if (str_contains($hay, $p)) {
                return false;
            }
        }

        return str_starts_with($secret, 'sk_');
    }

    public function publishableKey(): string
    {
        return (string) config('stripe.publishable_key', '');
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    public function createPaymentIntent(array $params): array
    {
        return $this->request('POST', '/payment_intents', $params);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrievePaymentIntent(string $id): array
    {
        return $this->request('GET', '/payment_intents/'.rawurlencode($id));
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $params = []): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Stripe is not configured. Set STRIPE_SECRET in .env.');
        }

        $url = rtrim((string) config('stripe.api_base'), '/').$path;
        $secret = (string) config('stripe.secret');

        try {
            $pending = Http::withBasicAuth($secret, '')
                ->asForm()
                ->acceptJson()
                ->timeout(30);

            $response = strtoupper($method) === 'GET'
                ? $pending->get($url, $params)
                : $pending->post($url, $params);

            $json = $response->json() ?? [];

            if (! $response->successful()) {
                $message = $json['error']['message'] ?? ('Stripe HTTP '.$response->status());
                Log::warning('Stripe API error', [
                    'path' => $path,
                    'status' => $response->status(),
                    'error' => $json['error'] ?? null,
                ]);
                throw new RuntimeException($message);
            }

            return is_array($json) ? $json : [];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Stripe request failed', ['path' => $path, 'error' => $e->getMessage()]);
            throw new RuntimeException('Stripe request failed: '.$e->getMessage());
        }
    }
}
