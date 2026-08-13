<?php

namespace App\Services;

use App\Models\CategoryMoneyFlow;
use App\Support\MarketplaceCategoryMap;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Clive money-flow ledger: per category → Our money / Seller payouts / Other.
 */
class CategoryMoneyFlowService
{
    public function recordSaleSplit(
        string $categoryKey,
        float $gross,
        float $platformFee,
        float $sellerAmount,
        string $sourceType,
        string|int $sourceId,
        ?string $paymentId = null,
        ?int $payerUserId = null,
        ?int $payeeUserId = null,
        string $currency = 'USD',
        string $description = '',
        array $meta = []
    ): void {
        if ($platformFee > 0.009) {
            $this->record([
                'category_key' => $categoryKey,
                'bucket' => CategoryMoneyFlow::BUCKET_PLATFORM,
                'flow_subtype' => 'platform_fee',
                'gross_amount' => $gross,
                'platform_amount' => $platformFee,
                'seller_amount' => 0,
                'currency' => $currency,
                'payment_id' => $paymentId,
                'source_type' => $sourceType,
                'source_id' => (string) $sourceId,
                'payer_user_id' => $payerUserId,
                'payee_user_id' => null,
                'description' => $description ?: 'Platform fee on sale',
                'meta' => $meta,
            ]);
        }

        if ($sellerAmount > 0.009) {
            $this->record([
                'category_key' => $categoryKey,
                'bucket' => CategoryMoneyFlow::BUCKET_SELLER,
                'flow_subtype' => 'seller_earning',
                'gross_amount' => $gross,
                'platform_amount' => 0,
                'seller_amount' => $sellerAmount,
                'currency' => $currency,
                'payment_id' => $paymentId,
                'source_type' => $sourceType,
                'source_id' => (string) $sourceId,
                'payer_user_id' => $payerUserId,
                'payee_user_id' => $payeeUserId,
                'description' => $description ?: 'Seller share of sale',
                'meta' => $meta,
            ]);
        }
    }

    public function recordPlatformIncome(
        string $categoryKey,
        float $amount,
        string $subtype,
        string $sourceType,
        string|int $sourceId,
        ?string $paymentId = null,
        ?int $payerUserId = null,
        string $currency = 'USD',
        string $description = '',
        array $meta = []
    ): void {
        if ($amount < 0.01) {
            return;
        }

        $this->record([
            'category_key' => $categoryKey,
            'bucket' => CategoryMoneyFlow::BUCKET_PLATFORM,
            'flow_subtype' => $subtype,
            'gross_amount' => $amount,
            'platform_amount' => $amount,
            'seller_amount' => 0,
            'currency' => $currency,
            'payment_id' => $paymentId,
            'source_type' => $sourceType,
            'source_id' => (string) $sourceId,
            'payer_user_id' => $payerUserId,
            'payee_user_id' => null,
            'description' => $description ?: ucfirst(str_replace('_', ' ', $subtype)),
            'meta' => $meta,
        ]);
    }

    public function recordSellerPayout(
        string $categoryKey,
        float $amount,
        string $sourceType,
        string|int $sourceId,
        ?int $payeeUserId = null,
        ?string $paymentId = null,
        string $currency = 'USD',
        string $description = '',
        array $meta = []
    ): void {
        if ($amount < 0.01) {
            return;
        }

        $this->record([
            'category_key' => $categoryKey,
            'bucket' => CategoryMoneyFlow::BUCKET_SELLER,
            'flow_subtype' => 'payout_paid',
            'gross_amount' => $amount,
            'platform_amount' => 0,
            'seller_amount' => $amount,
            'currency' => $currency,
            'payment_id' => $paymentId,
            'source_type' => $sourceType,
            'source_id' => (string) $sourceId,
            'payer_user_id' => null,
            'payee_user_id' => $payeeUserId,
            'description' => $description ?: 'Payout to seller/affiliate',
            'meta' => $meta,
        ]);
    }

    public function recordOther(
        string $categoryKey,
        float $amount,
        string $subtype,
        string $sourceType,
        string|int $sourceId,
        ?string $paymentId = null,
        ?int $payerUserId = null,
        ?int $payeeUserId = null,
        string $currency = 'USD',
        string $description = '',
        array $meta = []
    ): void {
        if ($amount < 0.01) {
            return;
        }

        $this->record([
            'category_key' => $categoryKey,
            'bucket' => CategoryMoneyFlow::BUCKET_OTHER,
            'flow_subtype' => $subtype,
            'gross_amount' => $amount,
            'platform_amount' => 0,
            'seller_amount' => 0,
            'currency' => $currency,
            'payment_id' => $paymentId,
            'source_type' => $sourceType,
            'source_id' => (string) $sourceId,
            'payer_user_id' => $payerUserId,
            'payee_user_id' => $payeeUserId,
            'description' => $description ?: ucfirst(str_replace('_', ' ', $subtype)),
            'meta' => array_merge($meta, ['pass_through_amount' => $amount]),
        ]);
    }

    /**
     * Idempotent upsert by source + bucket + subtype.
     */
    public function record(array $data): ?CategoryMoneyFlow
    {
        if (! Schema::hasTable('category_money_flows')) {
            return null;
        }

        try {
            $attrs = [
                'source_type' => $data['source_type'] ?? null,
                'source_id' => isset($data['source_id']) ? (string) $data['source_id'] : null,
                'bucket' => $data['bucket'],
                'flow_subtype' => $data['flow_subtype'],
            ];

            $values = [
                'category_key' => $data['category_key'],
                'gross_amount' => round((float) ($data['gross_amount'] ?? 0), 2),
                'platform_amount' => round((float) ($data['platform_amount'] ?? 0), 2),
                'seller_amount' => round((float) ($data['seller_amount'] ?? 0), 2),
                'currency' => strtoupper($data['currency'] ?? 'USD'),
                'payment_id' => $data['payment_id'] ?? null,
                'payer_user_id' => $data['payer_user_id'] ?? null,
                'payee_user_id' => $data['payee_user_id'] ?? null,
                'status' => $data['status'] ?? 'completed',
                'description' => $data['description'] ?? null,
                'meta' => $data['meta'] ?? null,
                'occurred_at' => $data['occurred_at'] ?? now(),
            ];

            return CategoryMoneyFlow::updateOrCreate($attrs, $values);
        } catch (Throwable $e) {
            Log::warning('CategoryMoneyFlow record failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return null;
        }
    }

    /**
     * Super-admin summary: each category → our / seller / other.
     *
     * @return array{categories: array<int, array>, totals: array}
     */
    public function summarize(?string $from = null, ?string $to = null): array
    {
        if (! Schema::hasTable('category_money_flows')) {
            return ['categories' => [], 'totals' => $this->emptyTotals()];
        }

        $query = CategoryMoneyFlow::query()->completed();
        if ($from) {
            $query->where('occurred_at', '>=', $from);
        }
        if ($to) {
            $query->where('occurred_at', '<=', $to);
        }

        $rows = $query
            ->selectRaw('category_key, bucket, SUM(platform_amount) as platform_sum, SUM(seller_amount) as seller_sum, SUM(gross_amount) as gross_sum, COUNT(*) as txn_count')
            ->groupBy('category_key', 'bucket')
            ->get();

        $byCategory = [];
        foreach (MarketplaceCategoryMap::allKeys() as $key) {
            $byCategory[$key] = [
                'category_key' => $key,
                'label' => MarketplaceCategoryMap::label($key),
                'our_money' => 0.0,
                'seller_payouts' => 0.0,
                'other_monies' => 0.0,
                'gross' => 0.0,
                'transactions' => 0,
                'breakdown' => [
                    'products_fees_adverts_commissions' => 0.0,
                ],
            ];
        }

        foreach ($rows as $row) {
            $key = $row->category_key;
            if (! isset($byCategory[$key])) {
                $byCategory[$key] = [
                    'category_key' => $key,
                    'label' => MarketplaceCategoryMap::label($key),
                    'our_money' => 0.0,
                    'seller_payouts' => 0.0,
                    'other_monies' => 0.0,
                    'gross' => 0.0,
                    'transactions' => 0,
                    'breakdown' => [
                        'products_fees_adverts_commissions' => 0.0,
                    ],
                ];
            }

            $byCategory[$key]['transactions'] += (int) $row->txn_count;
            $byCategory[$key]['gross'] += (float) $row->gross_sum;

            if ($row->bucket === CategoryMoneyFlow::BUCKET_PLATFORM) {
                $amt = (float) $row->platform_sum;
                $byCategory[$key]['our_money'] += $amt;
                $byCategory[$key]['breakdown']['products_fees_adverts_commissions'] += $amt;
            } elseif ($row->bucket === CategoryMoneyFlow::BUCKET_SELLER) {
                $byCategory[$key]['seller_payouts'] += (float) $row->seller_sum;
            } elseif ($row->bucket === CategoryMoneyFlow::BUCKET_OTHER) {
                $byCategory[$key]['other_monies'] += (float) $row->gross_sum;
            }
        }

        // Subtype detail for platform bucket
        $subtypeRows = CategoryMoneyFlow::query()
            ->completed()
            ->where('bucket', CategoryMoneyFlow::BUCKET_PLATFORM)
            ->when($from, fn ($q) => $q->where('occurred_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('occurred_at', '<=', $to))
            ->selectRaw('category_key, flow_subtype, SUM(platform_amount) as amt')
            ->groupBy('category_key', 'flow_subtype')
            ->get();

        foreach ($subtypeRows as $row) {
            $key = $row->category_key;
            if (! isset($byCategory[$key])) {
                continue;
            }
            $byCategory[$key]['breakdown'][$row->flow_subtype] = round((float) $row->amt, 2);
        }

        $categories = array_values(array_map(function ($c) {
            $c['our_money'] = round($c['our_money'], 2);
            $c['seller_payouts'] = round($c['seller_payouts'], 2);
            $c['other_monies'] = round($c['other_monies'], 2);
            $c['gross'] = round($c['gross'], 2);
            foreach ($c['breakdown'] as $k => $v) {
                $c['breakdown'][$k] = round((float) $v, 2);
            }

            return $c;
        }, $byCategory));

        // Hide empty categories at the end (keep ones with activity first)
        usort($categories, function ($a, $b) {
            $aActive = ($a['our_money'] + $a['seller_payouts'] + $a['other_monies']) > 0 ? 0 : 1;
            $bActive = ($b['our_money'] + $b['seller_payouts'] + $b['other_monies']) > 0 ? 0 : 1;
            if ($aActive !== $bActive) {
                return $aActive <=> $bActive;
            }

            return strcmp($a['label'], $b['label']);
        });

        $totals = [
            'our_money' => round(array_sum(array_column($categories, 'our_money')), 2),
            'seller_payouts' => round(array_sum(array_column($categories, 'seller_payouts')), 2),
            'other_monies' => round(array_sum(array_column($categories, 'other_monies')), 2),
            'gross' => round(array_sum(array_column($categories, 'gross')), 2),
            'transactions' => (int) array_sum(array_column($categories, 'transactions')),
        ];

        return [
            'categories' => $categories,
            'totals' => $totals,
            'buckets' => [
                'platform' => 'Our money (products, fees, adverts & commissions)',
                'seller_payout' => 'Payouts to customers / sellers',
                'other' => 'Other monies (donations, funding, pass-through)',
            ],
        ];
    }

    private function emptyTotals(): array
    {
        return [
            'our_money' => 0.0,
            'seller_payouts' => 0.0,
            'other_monies' => 0.0,
            'gross' => 0.0,
            'transactions' => 0,
        ];
    }
}
