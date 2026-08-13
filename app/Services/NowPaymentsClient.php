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

    public function payoutsConfigured(): bool
    {
        $email = trim((string) config('crypto.nowpayments.email'));
        $password = (string) config('crypto.nowpayments.password');

        return $this->configured() && $email !== '' && $password !== '';
    }

    /**
     * JWT used by NOWPayments mass-payout endpoints.
     */
    public function authToken(): string
    {
        $email = trim((string) config('crypto.nowpayments.email'));
        $password = (string) config('crypto.nowpayments.password');
        if ($email === '' || $password === '') {
            throw new RuntimeException('NOWPayments payout email/password are not configured.');
        }

        $json = $this->request('POST', '/auth', [
            'email' => $email,
            'password' => $password,
        ]);

        $token = (string) ($json['token'] ?? '');
        if ($token === '') {
            throw new RuntimeException('NOWPayments did not return a payout auth token.');
        }

        return $token;
    }

    /**
     * @param  array<int, array<string,mixed>>  $withdrawals
     */
    public function createPayout(array $withdrawals, ?string $ipnCallbackUrl = null): array
    {
        $body = ['withdrawals' => array_values($withdrawals)];
        if ($ipnCallbackUrl) {
            $body['ipn_callback_url'] = $ipnCallbackUrl;
        }

        return $this->request('POST', '/payout', $body, true);
    }

    public function verifyPayout(string $batchId, string $code): array
    {
        return $this->request('POST', '/payout/'.$batchId.'/verify', [
            'verification_code' => $code,
        ], true);
    }

    public function getPayout(string $payoutId): array
    {
        return $this->request('GET', '/payout/'.$payoutId, [], true);
    }

    /**
     * @return array<string,mixed>
     */
    private function request(string $method, string $path, array $body = [], bool $withJwt = false): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('NOWPayments API key is not configured.');
        }

        try {
            $headers = [
                'x-api-key' => $this->apiKey(),
                'Accept' => 'application/json',
            ];
            if ($withJwt) {
                $headers['Authorization'] = 'Bearer '.$this->authToken();
            }

            $http = Http::withHeaders($headers)->timeout(25);

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
