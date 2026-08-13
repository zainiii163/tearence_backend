<?php

namespace App\Console\Commands;

use App\Helpers\PlatformFeeHelper;
use App\Models\BuySellPurchase;
use App\Models\ServiceOrder;
use App\Models\StoreOrder;
use App\Models\TemplatePurchase;
use App\Services\CategoryMoneyFlowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill Clive category money ledger from existing paid orders.
 */
class BackfillCategoryMoneyFlows extends Command
{
    protected $signature = 'money:backfill-category-flows';

    protected $description = 'Backfill category_money_flows from paid marketplace orders and fees';

    public function handle(CategoryMoneyFlowService $flow): int
    {
        if (! Schema::hasTable('category_money_flows')) {
            $this->error('Run migrations first: php artisan migrate');

            return self::FAILURE;
        }

        $count = 0;

        if (Schema::hasTable('store_orders')) {
            StoreOrder::query()->where('payment_status', 'paid')->orderBy('id')->chunkById(100, function ($rows) use ($flow, &$count) {
                foreach ($rows as $order) {
                    $flow->recordSaleSplit(
                        'stores',
                        (float) $order->amount,
                        (float) ($order->platform_fee ?? 0),
                        (float) ($order->seller_amount ?? 0),
                        'store_order',
                        $order->id,
                        $order->payment_id,
                        $order->buyer_id ? (int) $order->buyer_id : null,
                        $order->seller_id ? (int) $order->seller_id : null,
                        'USD',
                        'Backfill store order'
                    );
                    $count++;
                }
            });
        }

        if (Schema::hasTable('service_orders')) {
            ServiceOrder::query()->where('payment_status', 'paid')->orderBy('id')->chunkById(100, function ($rows) use ($flow, &$count) {
                foreach ($rows as $order) {
                    $flow->recordSaleSplit(
                        'services',
                        (float) $order->total_price,
                        (float) ($order->platform_fee ?? 0),
                        (float) ($order->seller_amount ?? 0),
                        'service_order',
                        $order->id,
                        $order->payment_id,
                        $order->buyer_id ? (int) $order->buyer_id : null,
                        $order->seller_id ? (int) $order->seller_id : null,
                        'USD',
                        'Backfill service order'
                    );
                    $count++;
                }
            });
        }

        if (Schema::hasTable('buy_sell_purchases')) {
            BuySellPurchase::query()->whereIn('payment_status', ['paid', 'completed'])->orderBy('id')->chunkById(100, function ($rows) use ($flow, &$count) {
                foreach ($rows as $purchase) {
                    $flow->recordSaleSplit(
                        'buy-sell',
                        (float) $purchase->price,
                        (float) ($purchase->platform_fee ?? 0),
                        (float) ($purchase->seller_amount ?? 0),
                        'buy_sell_purchase',
                        $purchase->id,
                        $purchase->payment_id ?? null,
                        null,
                        $purchase->seller_id ? (int) $purchase->seller_id : null,
                        'USD',
                        'Backfill buy-sell purchase'
                    );
                    $count++;
                }
            });
        }

        if (Schema::hasTable('template_purchases')) {
            TemplatePurchase::query()->whereIn('payment_status', ['paid', 'completed'])->orderBy('id')->chunkById(100, function ($rows) use ($flow, &$count) {
                foreach ($rows as $purchase) {
                    $gross = (float) ($purchase->price_paid ?? $purchase->price ?? 0);
                    $platform = (float) ($purchase->platform_fee ?? 0);
                    $seller = (float) ($purchase->seller_amount ?? 0);
                    if ($platform < 0.01 && $seller < 0.01 && $gross >= 0.01) {
                        $split = PlatformFeeHelper::split($gross);
                        $platform = $split['platform_fee'];
                        $seller = $split['seller_amount'];
                    }
                    $flow->recordSaleSplit(
                        'templates',
                        $gross,
                        $platform,
                        $seller,
                        'template_purchase',
                        $purchase->id,
                        $purchase->payment_id ?? null,
                        null,
                        $purchase->seller_id ? (int) $purchase->seller_id : null,
                        'USD',
                        'Backfill template purchase'
                    );
                    $count++;
                }
            });
        }

        $this->info("Backfilled/updated flows for {$count} source records.");

        return self::SUCCESS;
    }
}
