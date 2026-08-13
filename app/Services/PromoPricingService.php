<?php

namespace App\Services;

use App\Models\PromoPricingPlan;
use App\Models\PromoRewardCode;
use Illuminate\Support\Collection;

class PromoPricingService
{
    /**
     * Launch promotional matrix (editable in Filament → Promo Pricing Plans).
     * Free 3d · Paid $10/1w · Promoted $20/1w · Featured $30/1w · Sponsored $40/1w
     */
    public const FALLBACK_PLANS = [
        [
            'slug' => 'free',
            'vertical' => 'all',
            'name' => 'Free Ad',
            'tier' => 'free',
            'price_usd' => 0.00,
            'duration_days' => 3,
            'description' => 'Basic listing — runs for 3 days',
            'features' => ['Standard search listing', '3 days live', 'Free badge'],
            'is_popular' => false,
            'sort_order' => 0,
        ],
        [
            'slug' => 'paid',
            'vertical' => 'all',
            'name' => 'Paid Advert',
            'tier' => 'paid',
            'price_usd' => 10.00,
            'duration_days' => 7,
            'description' => 'Paid listing for 1 week',
            'features' => ['Search priority', 'Paid badge', '1 week live'],
            'is_popular' => false,
            'sort_order' => 1,
        ],
        [
            'slug' => 'promoted',
            'vertical' => 'all',
            'name' => 'Promoted Ad',
            'tier' => 'promoted',
            'price_usd' => 20.00,
            'duration_days' => 7,
            'description' => 'Highlighted promotion for 1 week',
            'features' => ['Highlighted card', 'Above standard', 'Promoted badge', '1 week live'],
            'is_popular' => true,
            'sort_order' => 2,
        ],
        [
            'slug' => 'featured',
            'vertical' => 'all',
            'name' => 'Featured Ad',
            'tier' => 'featured',
            'price_usd' => 30.00,
            'duration_days' => 7,
            'description' => 'Top of category for 1 week',
            'features' => ['Top of category', 'Featured badge', 'Priority search', '1 week live'],
            'is_popular' => false,
            'sort_order' => 3,
        ],
        [
            'slug' => 'sponsored',
            'vertical' => 'all',
            'name' => 'Sponsored Ad',
            'tier' => 'sponsored',
            'price_usd' => 40.00,
            'duration_days' => 7,
            'description' => 'Maximum visibility for 1 week',
            'features' => ['Homepage placement', 'Category top', 'Sponsored badge', '1 week live'],
            'is_popular' => false,
            'sort_order' => 4,
        ],
        // Affiliate site-advertising cookie / hop-link packages
        [
            'slug' => 'cookie_30',
            'vertical' => 'affiliates',
            'name' => 'Affiliate Cookie — 30 Days',
            'tier' => 'cookie',
            'price_usd' => 20.00,
            'duration_days' => 30,
            'description' => 'Promotional affiliate hop / cookie window — 30 days',
            'features' => ['30-day cookie', 'Hop links on WWA', 'Marketplace listing'],
            'is_popular' => true,
            'sort_order' => 10,
        ],
        [
            'slug' => 'cookie_60',
            'vertical' => 'affiliates',
            'name' => 'Affiliate Cookie — 60 Days',
            'tier' => 'cookie',
            'price_usd' => 30.00,
            'duration_days' => 60,
            'description' => 'Promotional affiliate hop / cookie window — 60 days',
            'features' => ['60-day cookie', 'Hop links on WWA', 'Marketplace listing'],
            'is_popular' => false,
            'sort_order' => 11,
        ],
        [
            'slug' => 'cookie_90',
            'vertical' => 'affiliates',
            'name' => 'Affiliate Cookie — 90 Days',
            'tier' => 'cookie',
            'price_usd' => 40.00,
            'duration_days' => 90,
            'description' => 'Promotional affiliate hop / cookie window — 90 days',
            'features' => ['90-day cookie', 'Hop links on WWA', 'Marketplace listing'],
            'is_popular' => false,
            'sort_order' => 12,
        ],
    ];

    /**
     * Per-vertical listing tiers (same launch promo — Filament can override per marketplace).
     */
    public const LISTING_VERTICAL_DEFAULTS = [
        'free' => [
            'name' => 'Free Ad',
            'tier' => 'free',
            'price_usd' => 0.00,
            'duration_days' => 3,
            'description' => 'Basic listing — runs for 3 days',
            'features' => ['Standard search listing', '3 days live', 'Free badge'],
            'is_popular' => false,
            'sort_order' => 0,
        ],
        'paid' => [
            'name' => 'Paid Advert',
            'tier' => 'paid',
            'price_usd' => 10.00,
            'duration_days' => 7,
            'description' => 'Paid listing for 1 week',
            'features' => ['Search priority', 'Paid badge', '1 week live'],
            'is_popular' => false,
            'sort_order' => 1,
        ],
        'promoted' => [
            'name' => 'Promoted Ad',
            'tier' => 'promoted',
            'price_usd' => 20.00,
            'duration_days' => 7,
            'description' => 'Highlighted promotion for 1 week',
            'features' => ['Highlighted card', 'Above standard', 'Promoted badge', '1 week live'],
            'is_popular' => true,
            'sort_order' => 2,
        ],
        'featured' => [
            'name' => 'Featured Ad',
            'tier' => 'featured',
            'price_usd' => 30.00,
            'duration_days' => 7,
            'description' => 'Top of category for 1 week',
            'features' => ['Top of category', 'Featured badge', 'Priority search', '1 week live'],
            'is_popular' => false,
            'sort_order' => 3,
        ],
        'sponsored' => [
            'name' => 'Sponsored Ad',
            'tier' => 'sponsored',
            'price_usd' => 40.00,
            'duration_days' => 7,
            'description' => 'Maximum visibility for 1 week',
            'features' => ['Homepage placement', 'Category top', 'Sponsored badge', '1 week live'],
            'is_popular' => false,
            'sort_order' => 4,
        ],
    ];

    public const LISTING_VERTICALS = [
        'property', 'buysell', 'services', 'jobs', 'events', 'vehicles', 'books', 'funding', 'resorts', 'images', 'affiliates', 'banners',
    ];

    /** Free ads run this many days unless a Filament “free” plan overrides */
    public const DEFAULT_FREE_DURATION_DAYS = 3;

    public const LISTING_TIER_KEYS = ['free', 'paid', 'promoted', 'featured', 'sponsored'];


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
                $plans = $plans->filter(
                    fn ($p) => in_array($p->tier, self::LISTING_TIER_KEYS, true)
                )->values();
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
        if ($vertical === 'affiliates' && ! $listingTiersOnly) {
            $cookie = collect(self::FALLBACK_PLANS)
                ->filter(fn ($p) => ($p['tier'] ?? '') === 'cookie')
                ->map(fn ($p) => (object) $p)
                ->values();
            if ($cookie->isNotEmpty()) {
                return $cookie;
            }
        }

        if ($vertical && in_array($vertical, self::LISTING_VERTICALS, true)) {
            $plans = collect(self::LISTING_VERTICAL_DEFAULTS)->map(function ($p, $slug) use ($vertical) {
                return (object) array_merge($p, [
                    'slug' => $slug,
                    'vertical' => $vertical,
                ]);
            })->values();

            if ($listingTiersOnly) {
                $plans = $plans->filter(
                    fn ($p) => in_array($p->tier, self::LISTING_TIER_KEYS, true)
                )->values();
            }

            return $plans;
        }

        $plans = collect(self::FALLBACK_PLANS)->map(fn ($p) => (object) $p);
        if ($listingTiersOnly) {
            $plans = $plans->filter(
                fn ($p) => in_array($p->tier, self::LISTING_TIER_KEYS, true)
            )->values();
        } elseif ($vertical === 'affiliates') {
            $plans = $plans->filter(fn ($p) => ($p->tier ?? '') === 'cookie')->values();
        }

        return $plans;
    }

    /** Affiliate promotional cookie packages (30/60/90). */
    public function affiliateCookiePackages(): Collection
    {
        return $this->allActivePlans('affiliates', false)
            ->filter(fn ($p) => ($p->tier ?? '') === 'cookie')
            ->values();
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
