<?php

namespace App\Services;

use App\Models\PromoPricingPlan;
use App\Models\PromoRewardCode;
use Illuminate\Support\Collection;

class PromoPricingService
{
    /** Global Clive matrix — used when DB empty or offline */
    public const FALLBACK_PLANS = [
        [
            'slug' => 'sponsored',
            'vertical' => 'all',
            'name' => 'Sponsored',
            'tier' => 'sponsored',
            'price_usd' => 100.00,
            'duration_days' => 30,
            'description' => 'Maximum visibility for 1 month',
            'features' => ['Homepage placement', 'Category top', 'Social promotion', 'Sponsored badge'],
            'is_popular' => false,
            'sort_order' => 1,
        ],
        [
            'slug' => 'featured',
            'vertical' => 'all',
            'name' => 'Featured',
            'tier' => 'featured',
            'price_usd' => 30.00,
            'duration_days' => 14,
            'description' => 'Top of category for 2 weeks',
            'features' => ['Top of category', 'Featured badge', 'Priority search'],
            'is_popular' => true,
            'sort_order' => 2,
        ],
        [
            'slug' => 'promoted',
            'vertical' => 'all',
            'name' => 'Promoted',
            'tier' => 'promoted',
            'price_usd' => 50.00,
            'duration_days' => 21,
            'description' => 'Highlighted promotion for 3 weeks',
            'features' => ['Highlighted card', 'Above standard', 'Promoted badge'],
            'is_popular' => false,
            'sort_order' => 3,
        ],
        [
            'slug' => 'paid_1w',
            'vertical' => 'all',
            'name' => 'Paid Advert — 1 Week',
            'tier' => 'paid',
            'price_usd' => 10.00,
            'duration_days' => 7,
            'description' => 'Paid listing for 1 week',
            'features' => ['Search priority', 'Paid badge'],
            'is_popular' => false,
            'sort_order' => 4,
        ],
        [
            'slug' => 'paid_2w',
            'vertical' => 'all',
            'name' => 'Paid Advert — 2 Weeks',
            'tier' => 'paid',
            'price_usd' => 15.00,
            'duration_days' => 14,
            'description' => 'Paid listing for 2 weeks',
            'features' => ['Search priority', 'Paid badge'],
            'is_popular' => false,
            'sort_order' => 5,
        ],
        [
            'slug' => 'paid_4w',
            'vertical' => 'all',
            'name' => 'Paid Advert — 4 Weeks',
            'tier' => 'paid',
            'price_usd' => 20.00,
            'duration_days' => 28,
            'description' => 'Paid listing for 4 weeks',
            'features' => ['Search priority', 'Paid badge'],
            'is_popular' => false,
            'sort_order' => 6,
        ],
    ];

    /**
     * Classic listing upsell matrix used on property / services / jobs forms historically.
     * Seeded per-vertical so Filament can edit them independently.
     */
    public const LISTING_VERTICAL_DEFAULTS = [
        'promoted' => [
            'name' => 'Promoted Listing',
            'tier' => 'promoted',
            'price_usd' => 29.00,
            'duration_days' => 60,
            'description' => 'Enhanced visibility with promoted badge',
            'features' => ['Enhanced visibility', 'Promoted badge', 'Highlighted card', '60 days active'],
            'is_popular' => false,
            'sort_order' => 1,
        ],
        'featured' => [
            'name' => 'Featured Listing',
            'tier' => 'featured',
            'price_usd' => 79.00,
            'duration_days' => 90,
            'description' => 'Top of category with larger card',
            'features' => ['Top of category', 'Larger display card', '90 days active', 'Weekly email feature'],
            'is_popular' => true,
            'sort_order' => 2,
        ],
        'sponsored' => [
            'name' => 'Sponsored Listing',
            'tier' => 'sponsored',
            'price_usd' => 199.00,
            'duration_days' => 180,
            'description' => 'Homepage placement and social promotion',
            'features' => ['Homepage placement', 'Homepage slider', '180 days active', 'Social media promotion'],
            'is_popular' => false,
            'sort_order' => 3,
        ],
    ];

    public const LISTING_VERTICALS = [
        'property', 'buysell', 'services', 'jobs', 'events', 'vehicles', 'books', 'funding', 'resorts', 'images',
    ];

    public const DEFAULT_FREE_DURATION_DAYS = 30;

    public function allActivePlans(?string $vertical = null, bool $listingTiersOnly = false): Collection
    {
        $vertical = $vertical ?: null;

        try {
            $query = PromoPricingPlan::active()->orderBy('sort_order');
            if ($vertical) {
                $query->forVertical($vertical);
            }
            $plans = $query->get();

            // Prefer vertical-specific over "all" for same tier/slug
            if ($vertical && $plans->isNotEmpty()) {
                $plans = $this->preferVerticalPlans($plans, $vertical);
            }

            if ($listingTiersOnly) {
                $plans = $plans->filter(fn ($p) => in_array($p->tier, ['promoted', 'featured', 'sponsored'], true))->values();
            }

            if ($plans->isNotEmpty()) {
                return $plans;
            }
        } catch (\Throwable $e) {
            // table may not exist yet
        }

        return $this->fallbackPlans($vertical, $listingTiersOnly);
    }

    protected function preferVerticalPlans(Collection $plans, string $vertical): Collection
    {
        $byTier = [];
        foreach ($plans as $plan) {
            $key = $plan->tier ?: $plan->slug;
            $existing = $byTier[$key] ?? null;
            if (! $existing) {
                $byTier[$key] = $plan;
                continue;
            }
            // Prefer exact vertical match over "all"
            if (($plan->vertical ?? 'all') === $vertical && ($existing->vertical ?? 'all') === 'all') {
                $byTier[$key] = $plan;
            }
        }

        return collect(array_values($byTier))->sortBy('sort_order')->values();
    }

    protected function fallbackPlans(?string $vertical, bool $listingTiersOnly): Collection
    {
        if ($vertical && in_array($vertical, self::LISTING_VERTICALS, true)) {
            $plans = collect(self::LISTING_VERTICAL_DEFAULTS)->map(function ($p, $slug) use ($vertical) {
                return (object) array_merge($p, [
                    'slug' => $slug,
                    'vertical' => $vertical,
                ]);
            })->values();

            return $listingTiersOnly ? $plans : $plans;
        }

        $plans = collect(self::FALLBACK_PLANS)->map(fn ($p) => (object) $p);
        if ($listingTiersOnly) {
            $plans = $plans->filter(fn ($p) => in_array($p->tier, ['promoted', 'featured', 'sponsored'], true))->values();
        }

        return $plans;
    }

    public function findBySlug(string $slug, ?string $vertical = null): ?object
    {
        try {
            $query = PromoPricingPlan::active()->where('slug', $slug);
            if ($vertical) {
                $query->forVertical($vertical);
            }
            $plan = $query->orderByRaw("CASE WHEN vertical = ? THEN 0 ELSE 1 END", [$vertical ?: 'all'])->first();
            if ($plan) {
                return $plan;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        $fallback = $this->fallbackPlans($vertical, false)->firstWhere('slug', $slug);
        return $fallback ?: null;
    }

    public function findByTier(string $tier, ?string $vertical = null): ?object
    {
        try {
            $query = PromoPricingPlan::active()->where('tier', $tier);
            if ($vertical) {
                $query->forVertical($vertical);
            }
            $plan = $query->orderByRaw("CASE WHEN vertical = ? THEN 0 ELSE 1 END", [$vertical ?: 'all'])
                ->orderBy('sort_order')
                ->first();
            if ($plan) {
                return $plan;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return $this->fallbackPlans($vertical, false)->firstWhere('tier', $tier);
    }

    public function priceForTier(string $tier, ?string $vertical = null): float
    {
        $plan = $this->findByTier($tier, $vertical);
        return $plan ? (float) $plan->price_usd : 0.0;
    }

    public function durationForTier(string $tier, ?string $vertical = null): int
    {
        $plan = $this->findByTier($tier, $vertical);
        return $plan ? (int) $plan->duration_days : self::DEFAULT_FREE_DURATION_DAYS;
    }

    public function durationForSlug(string $slug, ?string $vertical = null): int
    {
        $plan = $this->findBySlug($slug, $vertical);
        return $plan ? (int) $plan->duration_days : self::DEFAULT_FREE_DURATION_DAYS;
    }

    public function validateCode(string $code, ?string $tier = null, ?float $originalPrice = null): array
    {
        $reward = PromoRewardCode::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();

        if (! $reward || ! $reward->isCurrentlyValid()) {
            return ['valid' => false, 'message' => 'Invalid or expired reward code'];
        }

        if (! $reward->appliesToTier($tier)) {
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
        if (! $reward || ! $reward->isCurrentlyValid()) {
            return null;
        }
        $reward->incrementUses();
        return $reward;
    }
}
