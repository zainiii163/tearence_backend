<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Listing payment rules for the launch promo matrix.
 * Free ($0 / 3 days) is allowed; paid packages must be at least $10.
 */
class ListingPaymentGuard
{
    public const MIN_PAID_AMOUNT = 10.0;

    /** @deprecated use MIN_PAID_AMOUNT — kept for older call sites */
    public const MIN_AMOUNT = 10.0;

    public static function assertPaidAmount(mixed $amount, string $label = 'Listing'): float
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;

        // Free tier is valid
        if ($value < 0.01) {
            return 0.0;
        }

        if ($value < self::MIN_PAID_AMOUNT) {
            throw new InvalidArgumentException(
                "{$label} paid plans start at \$" . number_format(self::MIN_PAID_AMOUNT, 2) . '.'
            );
        }

        return round($value, 2);
    }

    public static function isPaid(mixed $amount): bool
    {
        return is_numeric($amount) && (float) $amount >= self::MIN_PAID_AMOUNT;
    }

    public static function isFree(mixed $amount): bool
    {
        return ! is_numeric($amount) || (float) $amount < 0.01;
    }

    /**
     * Normalize legacy keys; free remains free under launch promo.
     */
    public static function normalizeTier(?string $tier): string
    {
        $tier = strtolower(trim((string) $tier));

        if ($tier === '' || in_array($tier, ['basic', 'standard', '0'], true)) {
            return 'free';
        }

        return $tier;
    }

    public static function defaultPaidPrice(): float
    {
        return 10.0;
    }

    public static function freeDurationDays(): int
    {
        return (int) PromoPricingService::DEFAULT_FREE_DURATION_DAYS;
    }
}
