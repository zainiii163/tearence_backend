<?php

namespace App\Support;

/**
 * The single rule for whether a payment gateway may run in mock mode.
 *
 * Bug this closes — B1/S1. `STRIPE_MOCK` / `CRYPTO_MOCK` default to `"auto"`,
 * which resolved to *mock* whenever no real key was configured — including on
 * production, where keys are frequently absent. That made
 * `POST /stripe/payment-intents` followed by `.../confirm-mock` mint a valid
 * `VerifiedPaymentReference` on the live site, which `POST /listing` accepts,
 * so anyone could create paid listings for free.
 *
 * The fix is a hard rule enforced in one place: **production never mocks**,
 * whatever the flag says. Outside production the old behaviour stands — an
 * explicit true/false wins, and `auto` mocks only when real credentials are
 * missing.
 */
class PaymentMode
{
    /**
     * @param  mixed  $flag        The gateway's mock flag (bool, 0/1, or "auto").
     * @param  bool   $configured  Whether real credentials are present.
     */
    public static function useMock(mixed $flag, bool $configured): bool
    {
        // Hard rule: never mock in production, regardless of the flag. This is
        // what stops the free-listing exploit even if a deployment forgot to
        // set STRIPE_MOCK=false.
        if (self::mockDisabledHere()) {
            return false;
        }

        if ($flag === true || $flag === 1 || $flag === '1' || $flag === 'true') {
            return true;
        }
        if ($flag === false || $flag === 0 || $flag === '0' || $flag === 'false') {
            return false;
        }

        // "auto" / anything else: mock only when real credentials are missing.
        return ! $configured;
    }

    /**
     * True in environments where mock payment endpoints must be refused
     * outright — used to gate the `confirm-mock` routes as defence in depth.
     */
    public static function mockDisabledHere(): bool
    {
        return app()->environment('production');
    }
}
