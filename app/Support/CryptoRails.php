<?php

namespace App\Support;

class CryptoRails
{
    public const PROVIDER = 'nowpayments';

    public const NETWORKS = [
        'trc20' => [
            'id' => 'trc20',
            'currency' => 'USDT',
            'pay_currency' => 'usdttrc20',
            'label' => 'USDT → TRC20',
        ],
        'erc20' => [
            'id' => 'erc20',
            'currency' => 'USDT',
            'pay_currency' => 'usdterc20',
            'label' => 'USDT → ERC20',
        ],
        'polygon' => [
            'id' => 'polygon',
            'currency' => 'USDC',
            'pay_currency' => 'usdcmatic',
            'label' => 'USDC → Polygon',
        ],
    ];

    public const PAY_CURRENCY_NETWORK = [
        'usdttrc20' => 'TRC20',
        'usdterc20' => 'ERC20',
        'usdcmatic' => 'Polygon',
        'usdc' => 'USDC',
        'btc' => 'Bitcoin',
        'eth' => 'ERC20',
    ];

    public static function network(string $id): ?array
    {
        $key = strtolower(trim($id));

        return self::NETWORKS[$key] ?? null;
    }

    public static function payCurrencyForNetwork(string $networkId): string
    {
        return self::network($networkId)['pay_currency'] ?? 'usdttrc20';
    }

    public static function networkForPayCurrency(string $payCurrency): string
    {
        $key = strtolower(trim($payCurrency));

        return self::PAY_CURRENCY_NETWORK[$key] ?? strtoupper($key);
    }

    public static function validateAddress(string $address, string $networkId): array
    {
        $value = trim($address);
        if ($value === '') {
            return ['ok' => false, 'message' => 'Enter a wallet address.'];
        }

        $net = strtolower(trim($networkId));
        if ($net === 'trc20') {
            if (! preg_match('/^T[1-9A-HJ-NP-Za-km-z]{33}$/', $value)) {
                return ['ok' => false, 'message' => 'TRC20 addresses start with T and are 34 characters.'];
            }

            return ['ok' => true, 'address' => $value];
        }

        if (in_array($net, ['erc20', 'polygon', 'eth', 'usdc'], true)) {
            if (! preg_match('/^0x[a-fA-F0-9]{40}$/', $value)) {
                return ['ok' => false, 'message' => 'This network needs a 0x Ethereum-style address.'];
            }

            return ['ok' => true, 'address' => $value];
        }

        if (strlen($value) < 20) {
            return ['ok' => false, 'message' => 'Wallet address looks too short.'];
        }

        return ['ok' => true, 'address' => $value];
    }

    public static function isPaidStatus(string $status): bool
    {
        return in_array(strtolower($status), ['finished', 'confirmed', 'completed', 'paid', 'success'], true);
    }

    public static function extractTxHash(array $payload): ?string
    {
        foreach (['tx_hash', 'payin_hash', 'payout_hash', 'hash', 'transaction_hash', 'txid', 'outcome_hash', 'payment_hash'] as $key) {
            if (! empty($payload[$key]) && is_string($payload[$key])) {
                return trim($payload[$key]);
            }
        }

        return null;
    }
}
