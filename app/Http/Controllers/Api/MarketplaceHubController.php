<?php

namespace App\Http\Controllers\Api;

use App\Helpers\MediaUrlHelper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Homepage marketplace hub tiles (Buy & Sell, Jobs, Property, …).
 */
class MarketplaceHubController extends Controller
{
    /**
     * Hub definitions: frontend slug → route + DB category slugs + listing source.
     *
     * @return array<int, array<string, mixed>>
     */
    private function hubDefinitions(): array
    {
        return [
            [
                'slug' => 'buy-sell',
                'name' => 'Buy & Sell',
                'description' => 'Post anything you want to sell or find items to purchase',
                'route' => '/buy-sell',
                'category_slugs' => ['buy-and-sell', 'buy-sell'],
                'count_table' => 'buysell_adverts',
                'image_table' => 'buysell_adverts',
                'image_column' => 'images',
                'image_json' => true,
            ],
            [
                'slug' => 'business',
                'name' => 'Business & Companies',
                'description' => 'Find business opportunities, company listings, and commercial services',
                'route' => '/business',
                'category_slugs' => ['business-and-stores', 'business'],
                'count_table' => null,
                'image_table' => null,
            ],
            [
                'slug' => 'services',
                'name' => 'Services and Solutions',
                'description' => 'Professional services, consulting, and business solutions',
                'route' => '/services',
                'category_slugs' => ['services'],
                'count_table' => 'services',
                'image_table' => null,
            ],
            [
                'slug' => 'property',
                'name' => 'Property and Solutions',
                'description' => 'Browse properties for sale, rent, and real estate investments',
                'route' => '/property',
                'category_slugs' => ['property'],
                'count_table' => 'properties',
                'image_table' => 'properties',
                'image_column' => 'cover_image',
            ],
            [
                'slug' => 'jobs',
                'name' => 'Jobs & Vacancies',
                'description' => 'Find remote and local jobs, or post openings for candidates',
                'route' => '/jobs',
                'category_slugs' => ['jobs-and-vacancies', 'jobs'],
                'count_table' => 'jobs',
                'image_table' => 'jobs',
                'image_column' => 'logo_url',
            ],
            [
                'slug' => 'software',
                'name' => 'Software & Code',
                'description' => 'Sell scripts, themes, plugins and apps',
                'route' => '/software',
                'category_slugs' => ['software', 'software-code'],
                'count_table' => null,
                'image_table' => null,
                'fallback_image' => '/img/banners/marketplace/banner-tech-electronics.png',
            ],
            [
                'slug' => 'events',
                'name' => 'Events & Venues',
                'description' => 'Explore events and venues — conferences, concerts, halls and more',
                'route' => '/events-venues',
                'category_slugs' => ['events', 'events-venues'],
                'count_table' => 'events_venues_adverts',
                'image_table' => 'events_venues_adverts',
                'image_column' => 'main_image',
                'fallback_image' => '/img/banners/marketplace/banner-events.png',
            ],
            [
                'slug' => 'sponsored',
                'name' => 'Sponsored Ads',
                'description' => 'Premium sponsored advertising placements',
                'route' => '/sponsored-adverts',
                'category_slugs' => ['sponsored-ads', 'sponsored'],
                'count_table' => 'sponsored_adverts',
                'image_table' => 'sponsored_adverts',
                'image_column' => 'main_image',
            ],
            [
                'slug' => 'promoted',
                'name' => 'Promoted Ads',
                'description' => 'Promoted content and advertising campaigns',
                'route' => '/promoted-adverts',
                'category_slugs' => ['promoted', 'promoted-ads'],
                'count_table' => 'promoted_adverts',
                'image_table' => 'promoted_adverts',
                'image_column' => 'main_image',
                'image_prefix' => 'promoted-adverts/',
            ],
            [
                'slug' => 'banner',
                'name' => 'Banner Ads',
                'description' => 'Display banner advertising solutions',
                'route' => '/banner-adverts',
                'category_slugs' => ['banner', 'banner-ads'],
                'count_table' => 'banner_ads',
                'image_table' => 'banner_ads',
                'image_column' => 'banner_image',
            ],
            [
                'slug' => 'featured',
                'name' => 'Featured Ads',
                'description' => 'Premium featured listings and highlighted content',
                'route' => '/featured',
                'category_slugs' => ['featured', 'featured-ads'],
                'count_table' => 'featured_adverts',
                'image_table' => 'featured_adverts',
                'image_column' => 'images',
                'image_json' => true,
                'fallback_image' => '/img/banners/marketplace/banner-real-estate.png',
            ],
            [
                'slug' => 'funding',
                'name' => 'Funding & Crowdfunding',
                'description' => 'Raise business funding via loan or share partnership campaigns',
                'route' => '/funding',
                'category_slugs' => ['funding'],
                'count_table' => 'funding_projects',
                'image_table' => 'funding_projects',
                'image_column' => 'cover_image',
            ],
            [
                'slug' => 'stores',
                'name' => 'Online Stores',
                'description' => 'Online stores and e-commerce marketplaces',
                'route' => '/stores',
                'category_slugs' => ['stores', 'online-stores'],
                'count_table' => null,
                'image_table' => null,
                'fallback_image' => '/img/banners/marketplace/banner-fashion-beauty.png',
            ],
            [
                'slug' => 'books',
                'name' => 'Books & Literature',
                'description' => 'Educational books, novels, audiobooks, and digital publications',
                'route' => '/books',
                'category_slugs' => ['books'],
                'count_table' => 'books',
                'image_table' => 'books',
                'image_column' => 'cover_image',
                'fallback_image' => '/img/banners/marketplace/banner-books-authors.png',
            ],
            [
                'slug' => 'vehicles',
                'name' => 'Vehicles & Transport',
                'description' => 'Cars, motorcycles, trucks, and transportation solutions',
                'route' => '/vehicles',
                'category_slugs' => ['vehicles'],
                'count_table' => 'vehicles',
                'image_table' => 'vehicles',
                'image_column' => 'main_image',
                'fallback_image' => '/img/banners/marketplace/banner-vehicles.png',
            ],
            [
                'slug' => 'donations',
                'name' => 'Charities and Donations',
                'description' => 'Humanitarian causes and charitable contributions',
                'route' => '/donations',
                'category_slugs' => ['charities-and-donations', 'donations'],
                'count_table' => null,
                'image_table' => null,
            ],
            [
                'slug' => 'images',
                'name' => 'Stock Images & Media',
                'description' => 'Buy and sell admin-verified images for commercial and personal use',
                'route' => '/images',
                'category_slugs' => ['images', 'stock-images'],
                'count_table' => 'images_adverts',
                'image_table' => 'images_adverts',
                'image_column' => 'main_image',
            ],
            [
                'slug' => 'classifieds',
                'name' => 'Classifieds',
                'description' => 'General classified advertisements and listings',
                'route' => '/classifieds-ads',
                'category_slugs' => ['classifieds'],
                'count_table' => null,
                'image_table' => null,
                'fallback_image' => '/img/banners/marketplace/banner-services.png',
            ],
            [
                'slug' => 'affiliate',
                'name' => 'Affiliate Hub',
                'description' => 'Affiliate marketing programs and partnership opportunities',
                'route' => '/affiliates',
                'category_slugs' => ['affiliate-programs', 'affiliate'],
                'count_table' => null,
                'image_table' => null,
            ],
            [
                'slug' => 'resorts',
                'name' => 'Resorts & Travel',
                'description' => 'Luxury resorts, vacation packages, and travel destinations',
                'route' => '/resorts-travel',
                'category_slugs' => ['hotel-resorts-travel', 'resorts'],
                'count_table' => 'resorts_travel',
                'image_table' => 'resorts_travel',
                'image_column' => 'main_image',
                'fallback_image' => '/img/banners/marketplace/banner-travel-resorts.png',
            ],
            [
                'slug' => 'investment',
                'name' => 'Businesses for Sale',
                'description' => 'Buy or sell online and physical businesses worldwide',
                'route' => '/businesses-for-sale',
                'category_slugs' => ['businesses-for-sale', 'investment'],
                'count_table' => null,
                'image_table' => null,
                'fallback_image' => '/img/banners/marketplace/banner-business-finance.png',
            ],
        ];
    }

    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->get()
            ->keyBy(fn ($c) => strtolower((string) $c->slug));

        $hubs = [];
        foreach ($this->hubDefinitions() as $def) {
            $dbCat = null;
            foreach ($def['category_slugs'] as $slug) {
                if (isset($categories[strtolower($slug)])) {
                    $dbCat = $categories[strtolower($slug)];
                    break;
                }
            }

            $images = $this->resolveHubImages($def, $dbCat);
            $listingCount = $this->resolveListingCount($def);

            $hubs[] = [
                'slug' => $def['slug'],
                'name' => $dbCat?->name ?: $def['name'],
                'description' => ($dbCat && filled($dbCat->description))
                    ? $dbCat->description
                    : $def['description'],
                'route' => $def['route'],
                'category_id' => $dbCat?->category_id,
                'icon' => $dbCat?->icon,
                'icon_color' => $dbCat?->icon_color,
                'image' => $dbCat?->image,
                'image_url' => $images[0] ?? null,
                'images' => $images,
                'listing_count' => $listingCount,
                'is_active' => $dbCat ? (bool) $dbCat->is_active : true,
                'sort_order' => $dbCat?->sort_order ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $hubs,
                'total' => count($hubs),
            ],
            'message' => 'Marketplace hubs retrieved successfully',
        ]);
    }

    /**
     * Collect rotating card images from recent posts in this hub.
     *
     * @return array<int, string>
     */
    private function resolveHubImages(array $def, ?Category $dbCat): array
    {
        $images = $this->sampleListingImages($def, 8);

        if ($dbCat) {
            foreach (['image', 'icon'] as $field) {
                $raw = $dbCat->{$field} ?? null;
                if ($this->looksLikeImagePath($raw)) {
                    $resolved = MediaUrlHelper::resolve($raw);
                    if ($resolved) {
                        $images[] = $resolved;
                    }
                }
            }
        }

        if (empty($images) && ! empty($def['fallback_image'])) {
            $images[] = $def['fallback_image'];
        }

        // Unique, preserve order
        $unique = [];
        foreach ($images as $url) {
            if (! is_string($url) || $url === '') {
                continue;
            }
            if (! in_array($url, $unique, true)) {
                $unique[] = $url;
            }
        }

        return array_values($unique);
    }

    /**
     * @return array<int, string>
     */
    private function sampleListingImages(array $def, int $limit = 8): array
    {
        $table = $def['image_table'] ?? null;
        $column = $def['image_column'] ?? null;
        if (! $table || ! $column || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return [];
        }

        $orderCol = Schema::hasColumn($table, 'updated_at')
            ? 'updated_at'
            : (Schema::hasColumn($table, 'created_at') ? 'created_at' : null);

        $query = DB::table($table)->whereNotNull($column)->where($column, '!=', '');
        if ($orderCol) {
            $query->orderByDesc($orderCol);
        }

        $rows = $query->limit(max($limit * 2, 12))->get([$column]);
        $urls = [];

        foreach ($rows as $row) {
            $raw = $row->{$column};
            $candidates = [];

            if (! empty($def['image_json'])) {
                $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                if (is_array($decoded)) {
                    foreach ($decoded as $item) {
                        if (is_string($item) && $item !== '') {
                            $candidates[] = $item;
                        } elseif (is_array($item)) {
                            $path = $item['url'] ?? $item['image_path'] ?? $item['path'] ?? null;
                            if (is_string($path) && $path !== '') {
                                $candidates[] = $path;
                            }
                        }
                    }
                }
            } elseif (is_string($raw) && $raw !== '') {
                $candidates[] = $raw;
            }

            foreach ($candidates as $candidate) {
                if (! empty($def['image_prefix']) && ! str_contains($candidate, '/') && ! str_starts_with($candidate, 'http')) {
                    $candidate = rtrim($def['image_prefix'], '/').'/'.ltrim($candidate, '/');
                }
                $resolved = MediaUrlHelper::resolve($candidate);
                if ($resolved && ! in_array($resolved, $urls, true)) {
                    $urls[] = $resolved;
                }
                if (count($urls) >= $limit) {
                    return $urls;
                }
            }
        }

        return $urls;
    }

    private function resolveListingCount(array $def): int
    {
        $table = $def['count_table'] ?? null;
        if (! $table || ! Schema::hasTable($table)) {
            return 0;
        }

        try {
            return (int) DB::table($table)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function looksLikeImagePath(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        // Icon name strings like "briefcase" are not images
        if (! str_contains($value, '/') && ! str_contains($value, '.') && ! str_starts_with($value, 'http')) {
            return false;
        }

        return (bool) preg_match('/\.(jpe?g|png|gif|webp|svg)(\?|$)/i', $value)
            || str_starts_with($value, 'http')
            || str_contains($value, 'storage/')
            || str_contains($value, 'categories/');
    }
}
