<?php

namespace App\Services;

use App\Helpers\MediaUrlHelper;
use App\Models\AffiliateApplication;
use App\Models\BusinessAffiliateOffer;
use App\Models\CustomerBusiness;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BusinessDashboardStatsService
{
    /** @var array<string, array{tables: string[], title: string[], image: string[]}> */
    private const CATEGORY_TABLES = [
        'vehicles' => ['tables' => ['vehicle_adverts', 'vehicles'], 'title' => ['title', 'name', 'vehicle_name'], 'image' => ['main_image', 'image', 'thumbnail', 'cover_image']],
        'property' => ['tables' => ['property_adverts', 'properties'], 'title' => ['title', 'property_title', 'name'], 'image' => ['main_image', 'cover_image', 'image', 'thumbnail']],
        'jobs' => ['tables' => ['job_adverts', 'jobs'], 'title' => ['title', 'job_title', 'name'], 'image' => ['company_logo', 'image', 'logo']],
        'buy-sell' => ['tables' => ['buy_sell_adverts'], 'title' => ['title', 'name'], 'image' => ['main_image', 'image', 'thumbnail']],
        'services' => ['tables' => ['service_adverts', 'services'], 'title' => ['title', 'service_name', 'name'], 'image' => ['main_image', 'image', 'thumbnail']],
        'books' => ['tables' => ['book_adverts'], 'title' => ['title', 'book_title', 'name'], 'image' => ['cover_image', 'image', 'thumbnail']],
        'software' => ['tables' => ['software_adverts'], 'title' => ['title', 'name'], 'image' => ['image', 'logo', 'thumbnail']],
        'events' => ['tables' => ['event_adverts', 'events'], 'title' => ['title', 'event_name', 'name'], 'image' => ['main_image', 'image', 'cover_image']],
        'funding' => ['tables' => ['funding_campaigns'], 'title' => ['title', 'campaign_name', 'name'], 'image' => ['cover_image', 'image', 'banner_image']],
        'donations' => ['tables' => ['donation_campaigns', 'campaigns'], 'title' => ['title', 'campaign_name', 'name'], 'image' => ['cover_image', 'image']],
        'classifieds' => ['tables' => ['classified_adverts', 'classifieds'], 'title' => ['title', 'name'], 'image' => ['image', 'main_image']],
        'images' => ['tables' => ['image_adverts', 'stock_images'], 'title' => ['title', 'name'], 'image' => ['image', 'file_path', 'thumbnail']],
        'resorts' => ['tables' => ['resort_adverts', 'travel_adverts'], 'title' => ['title', 'name'], 'image' => ['main_image', 'cover_image', 'image']],
        'investment' => ['tables' => ['investment_adverts'], 'title' => ['title', 'name'], 'image' => ['image', 'cover_image']],
        'stores' => ['tables' => ['store_products', 'customer_store_products'], 'title' => ['title', 'name', 'product_name'], 'image' => ['image', 'main_image', 'thumbnail']],
        'adverts' => ['tables' => ['sponsored_adverts', 'featured_adverts'], 'title' => ['title', 'headline', 'name'], 'image' => ['image', 'banner_image', 'main_image']],
    ];

    public function build(string $category, ?int $customerId, ?int $userId): array
    {
        $category = strtolower(trim($category ?: 'business'));
        $stats = $this->emptyStats();
        $business = $customerId ? CustomerBusiness::where('customer_id', $customerId)->first() : null;

        $this->applyAffiliateStats($stats, $userId);
        $this->applyBusinessProfileStats($stats, $business);
        $this->applyCategoryListingCount($stats, $category, $customerId, $userId);

        $table = $this->resolveListingTable($category);
        $trends = $table ? $this->buildTrends($table, $customerId, $userId) : $this->emptyTrends();
        $recentListings = $table ? $this->fetchRecentListings($table, $category, $customerId, $userId) : [];
        $performance = $this->buildPerformance($stats, $business, $recentListings);

        if ($category === 'affiliate') {
            $stats['listings'] = $stats['offers'];
        }
        if ($category === 'stores') {
            $stats['products'] = $stats['listings'];
        }
        if (in_array($category, ['adverts', 'funding', 'donations'], true)) {
            $stats['campaigns'] = $stats['listings'];
        }

        return [
            'category' => $category,
            'stats' => $stats,
            'trends' => $trends,
            'performance' => $performance,
            'recent_listings' => $recentListings,
            'affiliate_summary' => [
                'offers' => (int) ($stats['offers'] ?? 0),
                'pending_applicants' => (int) ($stats['applicants'] ?? 0),
                'total_applications' => (int) ($stats['applications'] ?? 0),
            ],
            'updated_at' => now()->toIso8601String(),
        ];
    }

    private function emptyStats(): array
    {
        return [
            'listings' => 0,
            'orders' => 0,
            'views' => 0,
            'leads' => 0,
            'enquiries' => 0,
            'applications' => 0,
            'affiliates' => 0,
            'offers' => 0,
            'applicants' => 0,
            'hops' => 0,
            'products' => 0,
            'campaigns' => 0,
            'tickets' => 0,
            'sales' => 0,
            'rating' => null,
            'pledges' => 0,
            'goal' => 0,
            'visits' => 0,
            'donors' => 0,
            'raised' => 0,
            'replies' => 0,
            'bookings' => 0,
            'interest' => 0,
            'impressions' => 0,
            'clicks' => 0,
        ];
    }

    private function applyAffiliateStats(array &$stats, ?int $userId): void
    {
        if (!$userId || !Schema::hasTable('business_affiliate_offers')) {
            return;
        }

        $offerQuery = BusinessAffiliateOffer::where('user_id', $userId);
        $stats['offers'] = (clone $offerQuery)->count();
        $stats['affiliates'] = $stats['offers'];
        $stats['views'] = (int) (clone $offerQuery)->sum('views');
        $stats['clicks'] = (int) (clone $offerQuery)->sum('clicks');
        $stats['hops'] = $stats['clicks'];

        $offerIds = (clone $offerQuery)->pluck('id');
        if (Schema::hasTable('affiliate_applications') && $offerIds->isNotEmpty()) {
            $stats['applicants'] = AffiliateApplication::whereIn('business_affiliate_offer_id', $offerIds)
                ->where('status', 'pending')
                ->count();
            $stats['applications'] = AffiliateApplication::whereIn('business_affiliate_offer_id', $offerIds)->count();
            $stats['orders'] = AffiliateApplication::whereIn('business_affiliate_offer_id', $offerIds)
                ->where('status', 'approved')
                ->count();
        }
    }

    private function applyBusinessProfileStats(array &$stats, ?CustomerBusiness $business): void
    {
        if (!$business) {
            return;
        }

        $profile = is_array($business->category_profile) ? $business->category_profile : [];
        $stats['leads'] = max($stats['leads'], (int) ($profile['leads'] ?? 0));
        $stats['enquiries'] = max($stats['enquiries'], (int) ($profile['enquiries'] ?? $stats['leads']));
        if ($stats['views'] === 0) {
            $stats['views'] = (int) ($profile['views_30d'] ?? $business->views ?? 0);
        }
    }

    private function applyCategoryListingCount(array &$stats, string $category, ?int $customerId, ?int $userId): void
    {
        $table = $this->resolveListingTable($category);
        if (!$table) {
            return;
        }

        $count = $this->countForOwner($table, $customerId, $userId);
        if ($count > 0) {
            $stats['listings'] = $count;
        }
    }

    private function resolveListingTable(string $category): ?string
    {
        $config = self::CATEGORY_TABLES[$category] ?? null;
        if (!$config) {
            return null;
        }

        foreach ($config['tables'] as $table) {
            if (Schema::hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function countForOwner(string $table, ?int $customerId, ?int $userId): int
    {
        $q = DB::table($table);
        if ($customerId && Schema::hasColumn($table, 'customer_id')) {
            return (int) $q->where('customer_id', $customerId)->count();
        }
        if ($userId && Schema::hasColumn($table, 'user_id')) {
            return (int) $q->where('user_id', $userId)->count();
        }

        return 0;
    }

    private function ownerQuery(string $table, ?int $customerId, ?int $userId)
    {
        $q = DB::table($table);
        if ($customerId && Schema::hasColumn($table, 'customer_id')) {
            return $q->where('customer_id', $customerId);
        }
        if ($userId && Schema::hasColumn($table, 'user_id')) {
            return $q->where('user_id', $userId);
        }

        return null;
    }

    private function emptyTrends(): array
    {
        return collect(range(6, 0))->map(function (int $daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return [
                'date' => $date->toDateString(),
                'label' => $date->format('M j'),
                'listings' => 0,
                'views' => 0,
                'leads' => 0,
            ];
        })->values()->all();
    }

    private function buildTrends(string $table, ?int $customerId, ?int $userId): array
    {
        if (!Schema::hasColumn($table, 'created_at')) {
            return $this->emptyTrends();
        }

        $base = $this->ownerQuery($table, $customerId, $userId);
        if (!$base) {
            return $this->emptyTrends();
        }

        $from = Carbon::today()->subDays(6)->startOfDay();
        $rows = (clone $base)
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(6, 0))->map(function (int $daysAgo) use ($rows) {
            $date = Carbon::today()->subDays($daysAgo);
            $key = $date->toDateString();

            return [
                'date' => $key,
                'label' => $date->format('M j'),
                'listings' => (int) ($rows[$key] ?? 0),
                'views' => 0,
                'leads' => 0,
            ];
        })->values()->all();
    }

    private function fetchRecentListings(string $table, string $category, ?int $customerId, ?int $userId, int $limit = 8): array
    {
        $base = $this->ownerQuery($table, $customerId, $userId);
        if (!$base) {
            return [];
        }

        $config = self::CATEGORY_TABLES[$category] ?? ['title' => ['title', 'name'], 'image' => ['image', 'main_image']];
        $query = clone $base;
        if (Schema::hasColumn($table, 'created_at')) {
            $query->orderByDesc('created_at');
        } elseif (Schema::hasColumn($table, 'id')) {
            $query->orderByDesc('id');
        }

        $rows = $query->limit($limit)->get();
        $items = [];

        foreach ($rows as $row) {
            $arr = (array) $row;
            $title = $this->pickField($arr, $config['title']) ?: 'Listing #' . ($arr['id'] ?? '');
            $image = $this->resolveRowImage($arr, $config['image']);
            $status = $this->pickField($arr, ['status', 'listing_status', 'approval_status']) ?: 'active';

            $items[] = [
                'id' => $arr['id'] ?? null,
                'title' => $title,
                'image_url' => $image,
                'status' => $status,
                'created_at' => $arr['created_at'] ?? null,
                'views' => (int) ($arr['views'] ?? $arr['views_count'] ?? 0),
            ];
        }

        return $items;
    }

    private function pickField(array $row, array $candidates): ?string
    {
        foreach ($candidates as $key) {
            if (!empty($row[$key]) && is_scalar($row[$key])) {
                return (string) $row[$key];
            }
        }

        return null;
    }

    private function resolveRowImage(array $row, array $candidates): ?string
    {
        $direct = $this->pickField($row, $candidates);
        if ($direct) {
            return MediaUrlHelper::resolve($direct);
        }

        foreach (['attachments', 'images', 'gallery', 'media'] as $jsonCol) {
            if (empty($row[$jsonCol])) {
                continue;
            }
            $decoded = is_string($row[$jsonCol]) ? json_decode($row[$jsonCol], true) : $row[$jsonCol];
            if (!is_array($decoded)) {
                continue;
            }
            $first = $decoded[0] ?? null;
            if (is_string($first)) {
                return MediaUrlHelper::resolve($first);
            }
            if (is_array($first)) {
                $path = $first['url'] ?? $first['path'] ?? $first['file_path'] ?? $first['image_path'] ?? null;
                if ($path) {
                    return MediaUrlHelper::resolve($path);
                }
            }
        }

        return null;
    }

    private function buildPerformance(array $stats, ?CustomerBusiness $business, array $recentListings): array
    {
        $listings = max(1, (int) ($stats['listings'] ?? 0));
        $views = (int) ($stats['views'] ?? 0);
        $clicks = (int) ($stats['clicks'] ?? 0);
        $leads = (int) ($stats['leads'] ?? 0);
        $applications = (int) ($stats['applications'] ?? 0);
        $approved = (int) ($stats['orders'] ?? 0);
        $offers = max(1, (int) ($stats['offers'] ?? 0));

        $profileScore = 0;
        if ($business) {
            if (!empty($business->business_name)) {
                $profileScore += 25;
            }
            if (!empty($business->business_logo)) {
                $profileScore += 25;
            }
            if (!empty($business->business_description) || !empty($business->description)) {
                $profileScore += 25;
            }
            if (!empty($business->business_email) || !empty($business->phone)) {
                $profileScore += 25;
            }
        }

        $ctr = $views > 0 ? min(100, round(($clicks / $views) * 100, 1)) : 0;
        $leadRate = $views > 0 ? min(100, round(($leads / $views) * 100, 1)) : 0;
        $affiliateRate = $applications > 0 ? min(100, round(($approved / $applications) * 100, 1)) : 0;
        $activityRate = min(100, count($recentListings) * 12);

        return [
            [
                'key' => 'profile',
                'label' => 'Profile completeness',
                'value' => $profileScore,
                'max' => 100,
                'percent' => $profileScore,
            ],
            [
                'key' => 'listings',
                'label' => 'Active listings',
                'value' => (int) ($stats['listings'] ?? 0),
                'max' => max($listings, 10),
                'percent' => min(100, round(((int) ($stats['listings'] ?? 0) / max($listings, 10)) * 100)),
            ],
            [
                'key' => 'engagement',
                'label' => 'Click-through rate',
                'value' => $ctr,
                'max' => 100,
                'percent' => $ctr,
            ],
            [
                'key' => 'affiliates',
                'label' => 'Affiliate approval rate',
                'value' => $affiliateRate,
                'max' => 100,
                'percent' => $affiliateRate,
            ],
            [
                'key' => 'leads',
                'label' => 'Lead conversion',
                'value' => $leadRate,
                'max' => 100,
                'percent' => $leadRate,
            ],
            [
                'key' => 'offers',
                'label' => 'Affiliate offers live',
                'value' => (int) ($stats['offers'] ?? 0),
                'max' => max($offers, 5),
                'percent' => min(100, round(((int) ($stats['offers'] ?? 0) / max($offers, 5)) * 100)),
            ],
        ];
    }
}
