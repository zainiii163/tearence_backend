<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Enforce paid-only marketplace policy: nothing free.
 */
class ListingPaymentGuard
{
    public const MIN_AMOUNT = 10.0;

    public static function assertPaidAmount(mixed $amount, string $label = 'Listing'): float
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;

        if ($value < self::MIN_AMOUNT) {
            throw new InvalidArgumentException(
                "{$label} requires a paid plan of at least \$" . number_format(self::MIN_AMOUNT, 2) . '. Free listings are not allowed.'
            );
        }

        return round($value, 2);
    }

    public static function isPaid(mixed $amount): bool
    {
        return is_numeric($amount) && (float) $amount >= self::MIN_AMOUNT;
    }

    /**
     * Normalize legacy free tier keys to the lowest paid tier.
     */
    public static function normalizeTier(?string $tier): string
    {
        $tier = strtolower(trim((string) $tier));

        if ($tier === '' || in_array($tier, ['basic', 'free', 'standard', '0'], true)) {
            return 'paid';
        }

        return $tier;
    }

    /** Default paid price when a free/standard tier was requested */
    public static function defaultPaidPrice(): float
    {
        return 29.0;
    }
}
