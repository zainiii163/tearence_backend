<?php

namespace App\Services;

use App\Models\CategoryMoneyFlow;
use App\Models\SellerMarketplacePayout;
use App\Support\MarketplaceCategoryMap;
use Illuminate\Support\Facades\Schema;

/**
 * Seller share of marketplace sales (books, buy-sell, images, services, templates…).
 * Buyer checkout lands on WWA; seller_amount is owed and withdrawable via crypto payout.
 */
class SellerMarketplaceEarningsService
{
    public function summary(int $userId): array
    {
        $earned = 0.0;
        $byCategory = [];
        $recent = [];

        if (Schema::hasTable('category_money_flows')) {
            $earned = (float) CategoryMoneyFlow::query()
                ->completed()
                ->where('bucket', CategoryMoneyFlow::BUCKET_SELLER)
                ->where('flow_subtype', 'seller_earning')
                ->where('payee_user_id', $userId)
                ->sum('seller_amount');

            $byCategory = CategoryMoneyFlow::query()
                ->completed()
                ->where('bucket', CategoryMoneyFlow::BUCKET_SELLER)
                ->where('flow_subtype', 'seller_earning')
                ->where('payee_user_id', $userId)
                ->selectRaw('category_key, SUM(seller_amount) as earned, COUNT(*) as sales')
                ->groupBy('category_key')
                ->get()
                ->map(fn ($row) => [
                    'category_key' => $row->category_key,
                    'label' => MarketplaceCategoryMap::label($row->category_key),
                    'earned' => round((float) $row->earned, 2),
                    'sales' => (int) $row->sales,
                ])
                ->values()
                ->all();

            $recent = CategoryMoneyFlow::query()
                ->completed()
                ->where('bucket', CategoryMoneyFlow::BUCKET_SELLER)
                ->where('flow_subtype', 'seller_earning')
                ->where('payee_user_id', $userId)
                ->orderByDesc('occurred_at')
                ->limit(40)
                ->get()
                ->map(fn (CategoryMoneyFlow $row) => [
                    'id' => $row->id,
                    'category_key' => $row->category_key,
                    'label' => MarketplaceCategoryMap::label($row->category_key),
                    'amount' => round((float) $row->seller_amount, 2),
                    'gross' => round((float) $row->gross_amount, 2),
                    'currency' => $row->currency,
                    'description' => $row->description,
                    'source_type' => $row->source_type,
                    'source_id' => $row->source_id,
                    'payment_id' => $row->payment_id,
                    'occurred_at' => optional($row->occurred_at)?->toIso8601String(),
                ])
                ->all();
        }

        $reserved = 0.0;
        $paidOut = 0.0;
        $payouts = [];

        if (Schema::hasTable('seller_marketplace_payouts')) {
            $reserved = (float) SellerMarketplacePayout::query()
                ->forUser($userId)
                ->whereIn('status', ['pending', 'processing', 'paid'])
                ->sum('amount');

            $paidOut = (float) SellerMarketplacePayout::query()
                ->forUser($userId)
                ->where('status', 'paid')
                ->sum('amount');

            $payouts = SellerMarketplacePayout::query()
                ->forUser($userId)
                ->orderByDesc('id')
                ->limit(30)
                ->get();
        }

        $available = max(0, round($earned - $reserved, 2));
        $feePercent = (float) config('commerce.platform_fee_percent', 15);

        return [
            'fee_percent' => $feePercent,
            'seller_percent' => round(100 - $feePercent, 2),
            'totals' => [
                'earned' => round($earned, 2),
                'reserved' => round($reserved, 2),
                'paid_out' => round($paidOut, 2),
                'available' => $available,
                'min_payout' => 25.0,
            ],
            'by_category' => $byCategory,
            'sales' => $recent,
            'payouts' => $payouts,
            'money_flow' => [
                'listing_fees' => '100% Worldwide Adverts (platform) — paid / promoted / featured / sponsored ads',
                'product_sales' => sprintf(
                    'Buyer pays WWA checkout → %.0f%% platform commission, %.0f%% credited to you as seller',
                    $feePercent,
                    100 - $feePercent
                ),
                'payouts' => 'Request crypto (USDT/USDC) payout from available balance; admin sends after approval',
            ],
        ];
    }
}
