<?php
/**
 * One-shot Filament nav cleanup: regroup, rename, hide duplicates.
 * Run: php scripts/cleanup_filament_nav.php
 */

$resourcesDir = __DIR__ . '/../app/Filament/Resources';

$updates = [
    // Hide duplicates / legacy
    'AdvertisementResource.php' => [
        'shouldRegisterNavigation' => 'false',
        'navigationGroup' => "'Content Management'",
        'navigationLabel' => "'Advertisements (Legacy)'",
    ],
    'BookResource.php' => [
        'shouldRegisterNavigation' => 'false',
        'navigationLabel' => "'Books (Legacy)'",
    ],
    'JobListingResource.php' => [
        'shouldRegisterNavigation' => 'false',
        'navigationLabel' => "'Job Listings (Legacy)'",
    ],
    'ProjectResource.php' => [
        'shouldRegisterNavigation' => 'false',
        'navigationLabel' => "'Projects (Legacy)'",
    ],
    'PromotionPlanResource.php' => [
        'shouldRegisterNavigation' => 'false',
        'navigationLabel' => "'Promotion Plans (Legacy)'",
    ],
    'BuySellItemResource.php' => [
        'shouldRegisterNavigation' => 'false',
        'navigationLabel' => "'Buy & Sell Items (Legacy)'",
    ],
    'CandidateProfileResource.php' => [
        'shouldRegisterNavigation' => 'false',
        'navigationGroup' => "'Jobs & Vacancies'",
        'navigationLabel' => "'Candidate Profiles (Legacy)'",
    ],

    // Affiliates — move Filament link ads into Affiliates Hub
    'AffiliateResource.php' => [
        'navigationGroup' => "'Affiliates Hub'",
        'navigationLabel' => "'Affiliate Link Ads'",
        'navigationSort' => '0',
        'navigationIcon' => "'heroicon-o-link'",
    ],
    'AffiliateCategoryResource.php' => [
        'navigationLabel' => "'Categories'",
        'navigationSort' => '1',
    ],
    'BusinessAffiliateOfferResource.php' => [
        'navigationLabel' => "'Business Offers'",
        'navigationSort' => '2',
    ],
    'UserAffiliatePostResource.php' => [
        'navigationLabel' => "'User Posts'",
        'navigationSort' => '3',
    ],
    'AffiliateApplicationResource.php' => [
        'navigationLabel' => "'Applications'",
        'navigationSort' => '4',
    ],
    'AffiliateUpsellPlanResource.php' => [
        'navigationLabel' => "'Upsell Plans'",
        'navigationSort' => '5',
    ],

    // Marketing & Ads — collapse overlapping ad groups
    'SponsoredAdvertResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Sponsored Adverts'",
        'navigationSort' => '1',
        'navigationIcon' => "'heroicon-o-sparkles'",
    ],
    'SponsoredCategoryResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Sponsored Categories'",
        'navigationSort' => '2',
    ],
    'SponsoredPricingPlanResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Sponsored Pricing'",
        'navigationSort' => '3',
    ],
    'FeaturedAdvertResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Featured Adverts'",
        'navigationSort' => '4',
    ],
    'PromotedAdvertResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Promoted Adverts'",
        'navigationSort' => '5',
    ],
    'PromotedAdvertCategoryResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Promoted Categories'",
        'navigationSort' => '6',
    ],
    'AdManagementResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Ad Management'",
        'navigationSort' => '7',
    ],
    'PromoPricingPlanResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Promo Pricing Plans'",
        'navigationSort' => '8',
    ],
    'PromoRewardCodeResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Reward Codes'",
        'navigationSort' => '9',
    ],
    'BannerAdResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Banner Ads'",
        'navigationSort' => '10',
    ],
    'BannerCategoryResource.php' => [
        'navigationGroup' => "'Marketing & Ads'",
        'navigationLabel' => "'Banner Categories'",
        'navigationSort' => '11',
    ],

    // Moderation / listings
    'AdModerationResource.php' => [
        'navigationGroup' => "'Moderation'",
        'navigationLabel' => "'Ad Moderation'",
        'navigationSort' => '1',
    ],
    'ListingResource.php' => [
        'navigationGroup' => "'Moderation'",
        'navigationLabel' => "'All Listings'",
        'navigationSort' => '2',
    ],

    // Content
    'BlogResource.php' => [
        'navigationGroup' => "'Content'",
        'navigationLabel' => "'Blogs'",
        'navigationSort' => '1',
    ],
    'PackageResource.php' => [
        'navigationGroup' => "'Commerce'",
        'navigationLabel' => "'Packages'",
        'navigationSort' => '1',
    ],

    // Marketplace verticals under clearer groups
    'BookAdvertResource.php' => [
        'navigationGroup' => "'Marketplace'",
        'navigationLabel' => "'Books Adverts'",
        'navigationSort' => '1',
    ],
    'PricingPlanResource.php' => [
        'navigationGroup' => "'Marketplace'",
        'navigationLabel' => "'Book Pricing Plans'",
        'navigationSort' => '2',
    ],
    'ImagesAdvertResource.php' => [
        'navigationGroup' => "'Marketplace'",
        'navigationLabel' => "'Images & Media'",
        'navigationSort' => '3',
    ],
    'ResortsTravelResource.php' => [
        'navigationGroup' => "'Marketplace'",
        'navigationLabel' => "'Resorts & Travel'",
        'navigationSort' => '4',
    ],
    'ResortsTravelCategoryResource.php' => [
        'navigationGroup' => "'Marketplace'",
        'navigationLabel' => "'Travel Categories'",
        'navigationSort' => '5',
    ],

    // Jobs
    'JobAlertResource.php' => [
        'navigationGroup' => "'Jobs & Vacancies'",
        'navigationLabel' => "'Job Alerts'",
    ],
    'JobUpsellResource.php' => [
        'navigationGroup' => "'Jobs & Vacancies'",
        'navigationLabel' => "'Job Upsells'",
        'navigationSort' => '20',
    ],
    'CandidateUpsellResource.php' => [
        'navigationGroup' => "'Jobs & Vacancies'",
        'navigationLabel' => "'Candidate Upsells'",
        'navigationSort' => '21',
    ],

    // Users / customers
    'CustomerResource.php' => [
        'navigationGroup' => "'User Management'",
        'navigationLabel' => "'Customers'",
        'navigationSort' => '4',
    ],
    'CustomerStoreResource.php' => [
        'navigationGroup' => "'User Management'",
        'navigationLabel' => "'Customer Stores'",
        'navigationSort' => '5',
    ],
    'CustomerBusinessResource.php' => [
        'navigationGroup' => "'User Management'",
        'navigationLabel' => "'Businesses'",
        'navigationSort' => '6',
    ],
    'CategoryResource.php' => [
        'navigationGroup' => "'Settings'",
        'navigationLabel' => "'Global Categories'",
        'navigationSort' => '10',
    ],

    // Localization → Settings
    'CurrencyResource.php' => [
        'navigationGroup' => "'Settings'",
        'navigationLabel' => "'Currencies'",
        'navigationSort' => '20',
    ],
    'LanguageResource.php' => [
        'navigationGroup' => "'Settings'",
        'navigationLabel' => "'Languages'",
        'navigationSort' => '21',
    ],
    'CountryResource.php' => [
        'navigationGroup' => "'Settings'",
        'navigationLabel' => "'Countries'",
        'navigationSort' => '22',
    ],
    'ZoneResource.php' => [
        'navigationGroup' => "'Settings'",
        'navigationLabel' => "'Zones'",
        'navigationSort' => '23',
    ],

    // Commerce
    'AdPricingPlanResource.php' => [
        'navigationGroup' => "'Commerce'",
        'navigationLabel' => "'Ad Pricing Plans'",
        'navigationSort' => '2',
    ],
    'ListingUpsellResource.php' => [
        'navigationGroup' => "'Commerce'",
        'navigationLabel' => "'Listing Upsells'",
        'navigationSort' => '3',
    ],
    'RevenueTrackingResource.php' => [
        'navigationGroup' => "'Commerce'",
        'navigationLabel' => "'Revenue Tracking'",
        'navigationSort' => '4',
    ],

    // Buy & Sell labels
    'BuySellAdvertResource.php' => [
        'navigationLabel' => "'Adverts'",
        'navigationSort' => '1',
    ],
    'BuySellCategoryResource.php' => [
        'navigationLabel' => "'Categories'",
        'navigationSort' => '2',
    ],
    'BuySellPromotionPlanResource.php' => [
        'navigationLabel' => "'Promotion Plans'",
        'navigationSort' => '3',
    ],
    'BuySellPromotionResource.php' => [
        'navigationLabel' => "'Promotions'",
        'navigationSort' => '4',
    ],

    // Vehicles labels
    'VehicleResource.php' => [
        'navigationLabel' => "'Vehicles'",
        'navigationSort' => '1',
    ],
    'VehicleCategoryResource.php' => [
        'navigationLabel' => "'Categories'",
        'navigationSort' => '2',
    ],
    'VehicleMakeResource.php' => [
        'navigationLabel' => "'Makes'",
        'navigationSort' => '3',
    ],
    'VehicleModelResource.php' => [
        'navigationLabel' => "'Models'",
        'navigationSort' => '4',
    ],
    'VehicleEnquiryResource.php' => [
        'navigationLabel' => "'Enquiries'",
        'navigationSort' => '5',
    ],
];

function setOrReplaceProperty(string $content, string $prop, string $value, string $type): string
{
    $pattern = '/protected\s+static\s+(?:\?' . preg_quote($type, '/') . '\s+|bool\s+)\$' . preg_quote($prop, '/') . '\s*=\s*[^;]+;/';
    $replacement = $type === 'bool'
        ? "protected static bool \${$prop} = {$value};"
        : ($type === 'int'
            ? "protected static ?int \${$prop} = {$value};"
            : "protected static ?string \${$prop} = {$value};");

    if (preg_match($pattern, $content)) {
        return preg_replace($pattern, $replacement, $content, 1);
    }

    // Insert after class opening / model property
    if (preg_match('/(class\s+\w+\s+extends\s+Resource\s*\{)/', $content, $m, PREG_OFFSET_CAPTURE)) {
        $pos = $m[0][1] + strlen($m[0][0]);
        return substr($content, 0, $pos) . "\n\n    {$replacement}" . substr($content, $pos);
    }

    return $content;
}

$changed = 0;
foreach ($updates as $file => $props) {
    $path = $resourcesDir . DIRECTORY_SEPARATOR . $file;
    if (!is_file($path)) {
        echo "SKIP missing: {$file}\n";
        continue;
    }
    $content = file_get_contents($path);
    $original = $content;

    foreach ($props as $prop => $value) {
        $type = match ($prop) {
            'shouldRegisterNavigation' => 'bool',
            'navigationSort' => 'int',
            default => 'string',
        };
        $content = setOrReplaceProperty($content, $prop, $value, $type);
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "UPDATED {$file}\n";
        $changed++;
    } else {
        echo "NOCHANGE {$file}\n";
    }
}

echo "Done. Updated {$changed} files.\n";
