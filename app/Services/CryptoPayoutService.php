<?php

namespace App\Services;

use App\Models\AffiliatePayout;
use App\Models\SellerMarketplacePayout;
use App\Support\CryptoRails;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Sends approved crypto payouts (affiliates + marketplace sellers) through NOWPayments.
 * WWA ledger remains source of truth; provider only moves funds on-chain.
 */
class CryptoPayoutService
{
    public function approveAndSend(Model $payout): Model
    {
        if (! ($payout instanceof AffiliatePayout || $payout instanceof SellerMarketplacePayout)) {
            throw new RuntimeException('Unsupported payout model.');
        }

        if (strtolower((string) $payout->method) !== 'crypto') {
            throw new RuntimeException('This payout is not a crypto payout.');
        }
        if (in_array($payout->status, ['paid', 'rejected'], true)) {
            throw new RuntimeException('This payout can no longer be sent.');
        }

        $networkId = strtolower((string) ($payout->crypto_network ?: 'trc20'));
        $address = trim((string) $payout->crypto_address);
        $check = CryptoRails::validateAddress($address, $networkId);
        if (! $check['ok']) {
            throw new RuntimeException($check['message']);
        }

        $payCurrency = CryptoRails::payCurrencyForNetwork($networkId);
        $meta = CryptoRails::network($networkId) ?: CryptoRails::NETWORKS['trc20'];

        $mock = $this->useMock();
        if ($mock) {
            $payout->update([
                'status' => 'paid',
                'provider' => 'crypto_mock',
                'provider_payout_id' => 'PAYOUT-MOCK-'.strtoupper(Str::random(10)),
                'tx_hash' => '0x'.bin2hex(random_bytes(16)),
                'crypto_currency' => $meta['currency'],
                'crypto_address' => $check['address'],
                'crypto_network' => $networkId,
                'paid_at' => now(),
            ]);
            $fresh = $payout->fresh();
            $this->recordSellerLedgerPayout($fresh);

            return $fresh;
        }

        $client = app(NowPaymentsClient::class);
        if (! $client->payoutsConfigured()) {
            throw new RuntimeException(
                'NOWPayments payouts are not configured. Set NOWPAYMENTS_API_KEY, NOWPAYMENTS_EMAIL and NOWPAYMENTS_PASSWORD.'
            );
        }

        $ipn = url('/api/v1/crypto/webhook');
        $created = $client->createPayout([
            [
                'address' => $check['address'],
                'currency' => $payCurrency,
                'fiat_amount' => (float) $payout->amount,
                'fiat_currency' => 'usd',
                'extra_id' => (string) $payout->reference,
                'ipn_callback_url' => $ipn,
            ],
        ], $ipn);

        $batchId = (string) ($created['id'] ?? $created['batch_withdrawal_id'] ?? '');
        $withdrawal = $created['withdrawals'][0] ?? $created;
        $withdrawalId = (string) ($withdrawal['id'] ?? $batchId);
        $txHash = CryptoRails::extractTxHash(is_array($withdrawal) ? $withdrawal : []);

        $twoFa = trim((string) config('crypto.nowpayments.payout_2fa'));
        if ($twoFa !== '' && $batchId !== '') {
            try {
                $client->verifyPayout($batchId, $twoFa);
            } catch (Throwable $e) {
                Log::warning('NOWPayments payout 2FA verify failed', [
                    'payout_id' => $payout->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $remoteStatus = strtolower((string) ($withdrawal['status'] ?? $created['status'] ?? 'processing'));
        $paid = CryptoRails::isPaidStatus($remoteStatus) || $remoteStatus === 'finished';

        $payout->update([
            'status' => $paid ? 'paid' : 'processing',
            'provider' => CryptoRails::PROVIDER,
            'provider_payout_id' => $withdrawalId ?: $batchId,
            'tx_hash' => $txHash,
            'crypto_currency' => $meta['currency'],
            'crypto_address' => $check['address'],
            'crypto_network' => $networkId,
            'paid_at' => $paid ? now() : null,
            'raw_webhook_json' => $created,
        ]);

        $fresh = $payout->fresh();
        if ($paid) {
            $this->recordSellerLedgerPayout($fresh);
        }

        return $fresh;
    }

    /**
     * Apply payout IPN / status payload onto a matching ledger row.
     *
     * @param  array<string,mixed>  $payload
     */
    public function applyProviderStatus(array $payload): ?Model
    {
        $providerId = (string) (
            $payload['id']
            ?? $payload['withdrawal_id']
            ?? $payload['batch_withdrawal_id']
            ?? $payload['payout_id']
            ?? ''
        );
        $extraId = (string) ($payload['extra_id'] ?? $payload['order_id'] ?? '');

        $payout = $this->findPayout($providerId, $extraId);
        if (! $payout) {
            return null;
        }

        $status = strtolower((string) ($payload['status'] ?? $payload['payment_status'] ?? ''));
        $txHash = CryptoRails::extractTxHash($payload) ?: $payout->tx_hash;
        $paid = CryptoRails::isPaidStatus($status) || $status === 'finished';
        $rejected = in_array($status, ['rejected', 'failed', 'rejected_not_checked'], true);

        $payout->update([
            'status' => $paid ? 'paid' : ($rejected ? 'rejected' : 'processing'),
            'tx_hash' => $txHash,
            'provider_payout_id' => $payout->provider_payout_id ?: $providerId,
            'paid_at' => $paid ? ($payout->paid_at ?: now()) : $payout->paid_at,
            'raw_webhook_json' => $payload,
        ]);

        $fresh = $payout->fresh();
        if ($paid) {
            $this->recordSellerLedgerPayout($fresh);
        }

        return $fresh;
    }

    private function findPayout(string $providerId, string $extraId): ?Model
    {
        foreach ([AffiliatePayout::class, SellerMarketplacePayout::class] as $class) {
            $query = $class::query()->where('method', 'crypto');
            $payout = null;
            if ($providerId !== '') {
                $payout = (clone $query)->where('provider_payout_id', $providerId)->first();
            }
            if (! $payout && $extraId !== '') {
                $payout = (clone $query)->where('reference', $extraId)->first();
            }
            if ($payout) {
                return $payout;
            }
        }

        return null;
    }

    private function recordSellerLedgerPayout(Model $payout): void
    {
        if (! ($payout instanceof SellerMarketplacePayout)) {
            return;
        }
        if ($payout->status !== 'paid') {
            return;
        }

        try {
            app(CategoryMoneyFlowService::class)->recordSellerPayout(
                'other',
                (float) $payout->amount,
                'seller_marketplace_payout',
                $payout->id,
                (int) $payout->user_id,
                $payout->provider_payout_id ?: $payout->reference,
                $payout->currency ?: 'USD',
                'Seller marketplace payout paid'
            );
        } catch (Throwable $e) {
            Log::warning('Seller payout ledger write failed', ['error' => $e->getMessage()]);
        }
    }

    private function useMock(): bool
    {
        $flag = config('crypto.mock', 'auto');
        if ($flag === true || $flag === 1 || $flag === '1' || $flag === 'true') {
            return true;
        }
        if ($flag === false || $flag === 0 || $flag === '0' || $flag === 'false') {
            return false;
        }

        return ! app(NowPaymentsClient::class)->payoutsConfigured();
    }
}
