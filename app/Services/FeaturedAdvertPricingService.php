<?php

namespace App\Services;

use App\Models\FeaturedAdvert;

class FeaturedAdvertPricingService
{
    /**
     * Pricing configuration for different upsell tiers
     */
    private const PRICING_CONFIG = [
        FeaturedAdvert::TIER_PROMOTED => [
            'name' => 'Promoted',
            'price' => 29.99,
            'currency' => 'GBP',
            'duration_days' => 30,
            'priority_score' => 400,
            'features' => [
                'Highlighted card in search results',
                'Appears above standard listings',
                '"Promoted" badge on advert',
                '2× more visibility than standard ads',
                'Basic analytics dashboard',
            ],
            'benefits' => [
                'visibility_multiplier' => 2,
                'search_boost' => 400,
                'badge_color' => 'blue',
                'card_style' => 'highlighted',
            ]
        ],
        FeaturedAdvert::TIER_FEATURED => [
            'name' => 'Featured',
            'price' => 59.99,
            'currency' => 'GBP',
            'duration_days' => 30,
            'priority_score' => 600,
            'features' => [
                'Top placement in category pages',
                'Larger advert card with enhanced styling',
                'Priority placement in search results',
                'Included in weekly "Top Featured Ads" email newsletter',
                '"Featured" premium badge',
                '4× more visibility than standard ads',
                'Advanced analytics dashboard',
                'Social media promotion on our platforms',
            ],
            'benefits' => [
                'visibility_multiplier' => 4,
                'search_boost' => 600,
                'badge_color' => 'gold',
                'card_style' => 'premium',
                'newsletter_inclusion' => true,
                'social_promotion' => true,
            ],
            'is_most_popular' => true
        ],
        FeaturedAdvert::TIER_SPONSORED => [
            'name' => 'Sponsored',
            'price' => 99.99,
            'currency' => 'GBP',
            'duration_days' => 30,
            'priority_score' => 800,
            'features' => [
                'Premium homepage placement',
                'Featured in homepage carousel/slider',
                'Top placement in all category pages',
                'Priority #1 placement in search results',
                'Included in social media promotion across all platforms',
                '"Sponsored" exclusive premium badge',
                '6× more visibility than standard ads',
                'Comprehensive analytics dashboard',
                'Dedicated account manager support',
                'Priority customer support',
            ],
            'benefits' => [
                'visibility_multiplier' => 6,
                'search_boost' => 800,
                'badge_color' => 'purple',
                'card_style' => 'premium-plus',
                'homepage_placement' => true,
                'carousel_inclusion' => true,
                'newsletter_inclusion' => true,
                'social_promotion' => true,
                'account_manager' => true,
                'priority_support' => true,
            ]
        ],
    ];

    /**
     * Get pricing information for all tiers
     */
    public function getAllPricing(): array
    {
        return self::PRICING_CONFIG;
    }

    /**
     * Get pricing information for a specific tier
     */
    public function getPricingByTier(string $tier): ?array
    {
        return self::PRICING_CONFIG[$tier] ?? null;
    }

    /**
     * Calculate price for a specific tier with optional duration
     */
    public function calculatePrice(string $tier, int $durationDays = 30): float
    {
        $config = $this->getPricingByTier($tier);
        if (!$config) {
            return 0;
        }

        $basePrice = $config['price'];
        $baseDuration = $config['duration_days'];

        // Calculate pro-rated price for custom duration
        if ($durationDays !== $baseDuration) {
            $dailyRate = $basePrice / $baseDuration;
            return round($dailyRate * $durationDays, 2);
        }

        return $basePrice;
    }

    /**
     * Get discount pricing for bulk purchases
     */
    public function getBulkDiscount(int $quantity): array
    {
        $discounts = [
            1 => 0,      // No discount for single purchase
            3 => 0.10,   // 10% discount for 3+ months
            6 => 0.15,   // 15% discount for 6+ months
            12 => 0.25,  // 25% discount for 12+ months
        ];

        $discountRate = 0;
        foreach ($discounts as $minQuantity => $rate) {
            if ($quantity >= $minQuantity) {
                $discountRate = $rate;
            }
        }

        return [
            'discount_rate' => $discountRate,
            'discount_percentage' => $discountRate * 100,
            'bulk_pricing' => $this->calculateBulkPricing($discountRate),
        ];
    }

    /**
     * Calculate pricing with bulk discount applied
     */
    private function calculateBulkPricing(float $discountRate): array
    {
        $bulkPricing = [];
        
        foreach (self::PRICING_CONFIG as $tier => $config) {
            $originalPrice = $config['price'];
            $discountedPrice = $originalPrice * (1 - $discountRate);
            
            $bulkPricing[$tier] = [
                'original_price' => $originalPrice,
                'discounted_price' => round($discountedPrice, 2),
                'savings' => round($originalPrice - $discountedPrice, 2),
            ];
        }
        
        return $bulkPricing;
    }

    /**
     * Get comparison table for all tiers
     */
    public function getComparisonTable(): array
    {
        $features = [
            'search_boost' => 'Search Result Boost',
            'visibility_multiplier' => 'Visibility Multiplier',
            'badge_color' => 'Badge Color',
            'card_style' => 'Card Style',
            'homepage_placement' => 'Homepage Placement',
            'carousel_inclusion' => 'Carousel Inclusion',
            'newsletter_inclusion' => 'Newsletter Inclusion',
            'social_promotion' => 'Social Media Promotion',
            'account_manager' => 'Account Manager',
            'priority_support' => 'Priority Support',
        ];

        $comparison = [];
        
        foreach ($features as $key => $label) {
            $comparison[$key] = [
                'feature' => $label,
                'tiers' => []
            ];
            
            foreach (self::PRICING_CONFIG as $tier => $config) {
                $value = $config['benefits'][$key] ?? false;
                $comparison[$key]['tiers'][$tier] = $this->formatComparisonValue($key, $value);
            }
        }

        return $comparison;
    }

    /**
     * Format comparison values for display
     */
    private function formatComparisonValue(string $feature, $value): string
    {
        switch ($feature) {
            case 'search_boost':
                return is_numeric($value) ? "+{$value} points" : 'No';
            case 'visibility_multiplier':
                return is_numeric($value) ? "{$value}×" : '1×';
            case 'badge_color':
                return $value ? ucfirst($value) : 'None';
            case 'card_style':
                return $value ? ucfirst(str_replace('-', ' ', $value)) : 'Standard';
            default:
                return $value ? 'Yes' : 'No';
        }
    }

    /**
     * Get recommended tier based on user's needs
     */
    public function getRecommendation(array $needs): array
    {
        $scores = [];
        
        foreach (self::PRICING_CONFIG as $tier => $config) {
            $score = 0;
            
            // Score based on visibility needs
            if ($needs['max_visibility'] ?? false) {
                $score += $tier === FeaturedAdvert::TIER_SPONSORED ? 100 : 0;
            }
            
            // Score based on budget
            $budget = $needs['budget'] ?? 999999;
            if ($config['price'] <= $budget) {
                $score += 50;
            } else {
                $score -= 100;
            }
            
            // Score based on features needed
            if ($needs['homepage_placement'] ?? false) {
                $score += ($config['benefits']['homepage_placement'] ?? false) ? 30 : -20;
            }
            
            if ($needs['social_promotion'] ?? false) {
                $score += ($config['benefits']['social_promotion'] ?? false) ? 25 : -15;
            }
            
            if ($needs['analytics'] ?? false) {
                $score += 20; // All tiers have some analytics
            }
            
            // Bonus for most popular tier
            if ($config['is_most_popular'] ?? false) {
                $score += 10;
            }
            
            $scores[$tier] = $score;
        }
        
        // Get the tier with highest score
        $recommendedTier = array_keys($scores, max($scores))[0];
        
        return [
            'recommended_tier' => $recommendedTier,
            'reason' => $this->getRecommendationReason($recommendedTier, $needs),
            'alternative_tiers' => $this->getAlternativeRecommendations($recommendedTier, $scores),
        ];
    }

    /**
     * Get recommendation reason
     */
    private function getRecommendationReason(string $tier, array $needs): string
    {
        $config = self::PRICING_CONFIG[$tier];
        
        if ($tier === FeaturedAdvert::TIER_SPONSORED) {
            return "Perfect for maximum visibility and premium placement across all platforms.";
        }
        
        if ($tier === FeaturedAdvert::TIER_FEATURED) {
            return "Great balance of visibility and value, includes social media promotion.";
        }
        
        if ($tier === FeaturedAdvert::TIER_PROMOTED) {
            return "Affordable way to boost your advert's visibility above standard listings.";
        }
        
        return "Recommended based on your requirements.";
    }

    /**
     * Get alternative recommendations
     */
    private function getAlternativeRecommendations(string $recommendedTier, array $scores): array
    {
        $alternatives = [];
        arsort($scores);
        
        foreach ($scores as $tier => $score) {
            if ($tier !== $recommendedTier && $score > 0) {
                $alternatives[] = [
                    'tier' => $tier,
                    'reason' => $this->getAlternativeReason($tier),
                ];
            }
        }
        
        return array_slice($alternatives, 0, 2);
    }

    /**
     * Get alternative reason
     */
    private function getAlternativeReason(string $tier): string
    {
        $config = self::PRICING_CONFIG[$tier];
        
        if ($tier === FeaturedAdvert::TIER_PROMOTED) {
            return "Budget-friendly option with basic visibility boost.";
        }
        
        if ($tier === FeaturedAdvert::TIER_FEATURED) {
            return "Most popular choice with great features.";
        }
        
        if ($tier === FeaturedAdvert::TIER_SPONSORED) {
            return "Premium option for maximum exposure.";
        }
        
        return "Alternative option to consider.";
    }

    /**
     * Validate upsell tier
     */
    public function isValidTier(string $tier): bool
    {
        return array_key_exists($tier, self::PRICING_CONFIG);
    }

    /**
     * Get available tiers
     */
    public function getAvailableTiers(): array
    {
        return array_keys(self::PRICING_CONFIG);
    }
}
