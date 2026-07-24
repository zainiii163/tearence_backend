<?php

namespace App\Helpers;

class PlatformFeeHelper
{
    public static function percent(): float
    {
        return (float) config('commerce.platform_fee_percent', 15);
    }

    /**
     * @return array{fee_percent: float, platform_fee: float, seller_amount: float}
     */
    public static function split(float|int|string $total): array
    {
        $total = round((float) $total, 2);
        $percent = self::percent();
        $platformFee = round($total * ($percent / 100), 2);
        $sellerAmount = round(max(0, $total - $platformFee), 2);

        return [
            'fee_percent' => $percent,
            'platform_fee' => $platformFee,
            'seller_amount' => $sellerAmount,
        ];
    }
}
