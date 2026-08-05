<?php

namespace App\Services;

use App\Models\PromoPricingPlan;
use App\Models\PromoRewardCode;
use Illuminate\Support\Collection;

class PromoPricingService
{
    /** Clive's matrix — used when DB empty or offline */
    public const FALLBACK_PLANS = [
        [
            'slug' => 'sponsored',
            'name' => 'Sponsored',
            'tier' => 'sponsored',
            'price_usd' => 100.00,
            'duration_days' => 30,
            'description' => 'Maximum visibility for 1 month',
            'features' => ['Homepage placement', 'Category top', 'Social promotion', 'Sponsored badge'],
            'sort_order' => 1,
        ],
        [
            'slug' => 'featured',
            'name' => 'Featured',
            'tier' => 'featured',
            'price_usd' => 30.00,
            'duration_days' => 14,
            'description' => 'Top of category for 2 weeks',
            'features' => ['Top of category', 'Featured badge', 'Priority search'],
            'sort_order' => 2,
        ],
        [
            'slug' => 'promoted',
            'name' => 'Promoted',
            'tier' => 'promoted',
            'price_usd' => 50.00,
            'duration_days' => 21,
            'description' => 'Highlighted promotion for 3 weeks',
            'features' => ['Highlighted card', 'Above standard', 'Promoted badge'],
            'sort_order' => 3,
        ],
        [
            'slug' => 'paid_1w',
            'name' => 'Paid Advert — 1 Week',
            'tier' => 'paid',
            'price_usd' => 10.00,
            'duration_days' => 7,
            'description' => 'Paid listing for 1 week',
            'features' => ['Search priority', 'Paid badge'],
            'sort_order' => 4,
        ],
        [
            'slug' => 'paid_2w',
            'name' => 'Paid Advert — 2 Weeks',
            'tier' => 'paid',
            'price_usd' => 15.00,
            'duration_days' => 14,
            'description' => 'Paid listing for 2 weeks',
            'features' => ['Search priority', 'Paid badge'],
            'sort_order' => 5,
        ],
        [
            'slug' => 'paid_4w',
            'name' => 'Paid Advert — 4 Weeks',
            'tier' => 'paid',
            'price_usd' => 20.00,
            'duration_days' => 28,
            'description' => 'Paid listing for 4 weeks',
            'features' => ['Search priority', 'Paid badge'],
            'sort_order' => 6,
        ],
    ];

    public const DEFAULT_FREE_DURATION_DAYS = 30;

    public function allActivePlans(): Collection
    {
        try {
            $plans = PromoPricingPlan::active()->orderBy('sort_order')->get();
            if ($plans->isNotEmpty()) {
                return $plans;
            }
        } catch (\Throwable $e) {
            // table may not exist yet
        }

        return collect(self::FALLBACK_PLANS)->map(fn ($p) => (object) $p);
    }

    public function findBySlug(string $slug): ?object
    {
        try {
            $plan = PromoPricingPlan::active()->where('slug', $slug)->first();
            if ($plan) {
                return $plan;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $fallback = collect(self::FALLBACK_PLANS)->firstWhere('slug', $slug);
        return $fallback ? (object) $fallback : null;
    }

    public function findByTier(string $tier): ?object
    {
        try {
            $plan = PromoPricingPlan::active()
                ->where('tier', $tier)
                ->orderBy('sort_order')
                ->first();
            if ($plan) {
                return $plan;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $fallback = collect(self::FALLBACK_PLANS)->firstWhere('tier', $tier);
        return $fallback ? (object) $fallback : null;
    }

    public function priceForTier(string $tier): float
    {
        $plan = $this->findByTier($tier);
        return $plan ? (float) $plan->price_usd : 0.0;
    }

    public function durationForTier(string $tier): int
    {
        $plan = $this->findByTier($tier);
        return $plan ? (int) $plan->duration_days : self::DEFAULT_FREE_DURATION_DAYS;
    }

    public function durationForSlug(string $slug): int
    {
        $plan = $this->findBySlug($slug);
        return $plan ? (int) $plan->duration_days : self::DEFAULT_FREE_DURATION_DAYS;
    }

    public function validateCode(string $code, ?string $tier = null, ?float $originalPrice = null): array
    {
        $reward = PromoRewardCode::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();

        if (!$reward || !$reward->isCurrentlyValid()) {
            return ['valid' => false, 'message' => 'Invalid or expired reward code'];
        }

        if (!$reward->appliesToTier($tier)) {
            return ['valid' => false, 'message' => 'This code does not apply to the selected promotion tier'];
        }

        $price = $originalPrice ?? ($tier ? $this->priceForTier($tier) : 0);
        $calc = $reward->calculateForPrice((float) $price);

        return [
            'valid' => true,
            'message' => 'Code applied successfully',
            'code' => $reward->code,
            'code_id' => $reward->id,
            'tier' => $tier,
            'original_price' => (float) $price,
            ...$calc,
        ];
    }

    public function redeemCode(string $code): ?PromoRewardCode
    {
        $reward = PromoRewardCode::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();
        if (!$reward || !$reward->isCurrentlyValid()) {
            return null;
        }
        $reward->incrementUses();
        return $reward;
    }
}
