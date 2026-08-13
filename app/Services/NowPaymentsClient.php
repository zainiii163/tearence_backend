<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Thin NOWPayments API client for site-wide crypto checkout.
 * @see https://documenter.getpostman.com/view/7907941/S1a32n38
 */
class NowPaymentsClient
{
    public function baseUrl(): string
    {
        $sandbox = (bool) config('crypto.nowpayments.use_sandbox');

        return rtrim(
            (string) ($sandbox
                ? config('crypto.nowpayments.sandbox_api_url')
                : config('crypto.nowpayments.api_url')),
            '/'
        );
    }

    public function apiKey(): string
    {
        return trim((string) config('crypto.nowpayments.api_key'));
    }

    public function configured(): bool
    {
        $key = $this->apiKey();
        if ($key === '') {
            return false;
        }
        $hay = strtolower($key);
        foreach (['xxxxx', 'your_', 'change_me', 'placeholder'] as $p) {
            if (str_contains($hay, $p)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create a payment (returns payment_id, pay_address, pay_amount, invoice_url-ish fields).
     *
     * @param  array{price_amount:float|string,price_currency:string,pay_currency:string,order_id?:string,order_description?:string,ipn_callback_url?:string}  $payload
     */
    public function createPayment(array $payload): array
    {
        return $this->request('POST', '/payment', $payload);
    }

    public function getPayment(string $paymentId): array
    {
        return $this->request('GET', '/payment/'.$paymentId);
    }

    /**
     * @return array<string,mixed>
     */
    private function request(string $method, string $path, array $body = []): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('NOWPayments API key is not configured.');
        }

        try {
            $http = Http::withHeaders([
                'x-api-key' => $this->apiKey(),
                'Accept' => 'application/json',
            ])->timeout(25);

            $url = $this->baseUrl().$path;
            $response = strtoupper($method) === 'GET'
                ? $http->get($url)
                : $http->post($url, $body);

            $json = $response->json() ?? [];
            if (! $response->successful()) {
                Log::warning('NOWPayments API error', [
                    'status' => $response->status(),
                    'body' => $json,
                    'path' => $path,
                ]);
                $message = $json['message'] ?? $json['error'] ?? 'NOWPayments request failed';
                throw new RuntimeException(is_string($message) ? $message : 'NOWPayments request failed');
            }

            return is_array($json) ? $json : [];
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('NOWPayments client exception', ['error' => $e->getMessage()]);
            throw new RuntimeException('Unable to reach crypto payment provider.');
        }
    }
}
