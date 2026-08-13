<?php

namespace App\Support;

/**
 * Maps payment purchase_types / marketplace domains → Clive category keys.
 */
class MarketplaceCategoryMap
{
    public const LABELS = [
        'jobs' => 'Jobs & Vacancies',
        'books' => 'Books',
        'vehicles' => 'Vehicles',
        'buy-sell' => 'Buy & Sell',
        'services' => 'Services',
        'stores' => 'Business & Stores',
        'property' => 'Property',
        'banners' => 'Banner Ads',
        'affiliates' => 'Affiliate Programs',
        'sponsored' => 'Sponsored Ads',
        'featured' => 'Featured Ads',
        'templates' => 'Business Templates',
        'tools' => 'Business Tools',
        'images' => 'Images',
        'donations' => 'Charities & Donations',
        'funding' => 'Funding',
        'events' => 'Events & Venues',
        'classifieds' => 'Classifieds',
        'other' => 'Other',
    ];

    public static function fromPurchaseType(string $purchaseType): string
    {
        $map = [
            'job_upsell' => 'jobs',
            'candidate_upsell' => 'jobs',
            'book_advert' => 'books',
            'book_listing' => 'books',
            'vehicle_advert' => 'vehicles',
            'buy_sell' => 'buy-sell',
            'service_order' => 'services',
            'store_order' => 'stores',
            'property_upsell' => 'property',
            'banner_pricing' => 'banners',
            'banner_ad' => 'banners',
            'listing_package' => 'buy-sell',
            'affiliate_pricing' => 'affiliates',
            'sponsored_advert' => 'sponsored',
            'business_template' => 'templates',
            'business_tool' => 'tools',
            'image_advert' => 'images',
            'donation' => 'donations',
            'funding_pledge' => 'funding',
        ];

        return $map[$purchaseType] ?? 'other';
    }

    public static function label(string $key): string
    {
        return self::LABELS[$key] ?? ucwords(str_replace('-', ' ', $key));
    }

    public static function allKeys(): array
    {
        return array_keys(self::LABELS);
    }
}
