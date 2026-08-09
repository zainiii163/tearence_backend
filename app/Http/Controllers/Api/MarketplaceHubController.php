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
 * Returns recent post images so each card can rotate real listing photos.
 */
class MarketplaceHubController extends Controller
{
    /**
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
                'image_sources' => [
                    ['table' => 'buysell_adverts', 'column' => 'images', 'json' => true],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'business',
                'name' => 'Business & Companies',
                'description' => 'Find business opportunities, company listings, and commercial services',
                'route' => '/business',
                'category_slugs' => ['business-and-stores', 'business'],
                'count_table' => 'business',
                'image_sources' => [
                    ['table' => 'business', 'column' => 'business_logo'],
                    ['table' => 'business', 'column' => 'logo'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'services',
                'name' => 'Services and Solutions',
                'description' => 'Professional services, consulting, and business solutions',
                'route' => '/services',
                'category_slugs' => ['services'],
                'count_table' => 'services',
                'image_sources' => [
                    ['table' => 'service_media', 'column' => 'file_path'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1504148455328-c376907d081c?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1581094794329-c8112a89af12?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'property',
                'name' => 'Property and Real Estate',
                'description' => 'Browse properties for sale, rent, and real estate investments',
                'route' => '/property',
                'category_slugs' => ['property'],
                'count_table' => 'properties',
                'image_sources' => [
                    ['table' => 'properties', 'column' => 'cover_image'],
                    ['table' => 'properties', 'column' => 'images', 'json' => true],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'jobs',
                'name' => 'Jobs & Vacancies',
                'description' => 'Find remote and local jobs, or post openings for candidates',
                'route' => '/jobs',
                'category_slugs' => ['jobs-and-vacancies', 'jobs'],
                'count_table' => 'jobs',
                'image_sources' => [
                    ['table' => 'jobs', 'column' => 'logo_url'],
                    ['table' => 'jobs', 'column' => 'company_logo'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'software',
                'name' => 'Software & Code',
                'description' => 'Sell scripts, themes, plugins and apps',
                'route' => '/software',
                'category_slugs' => ['software', 'software-code'],
                'count_table' => null,
                'image_sources' => [
                    ['table' => 'business_templates', 'column' => 'preview_image'],
                    ['table' => 'business_templates', 'column' => 'cover_image'],
                    ['table' => 'images_adverts', 'column' => 'main_image'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'events',
                'name' => 'Events & Venues',
                'description' => 'Explore events and venues — conferences, concerts, halls and more',
                'route' => '/events-venues',
                'category_slugs' => ['events', 'events-venues'],
                'count_table' => 'events_venues_adverts',
                'image_sources' => [
                    ['table' => 'events_venues_adverts', 'column' => 'main_image'],
                    ['table' => 'events_venues_adverts', 'column' => 'cover_image'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'sponsored',
                'name' => 'Sponsored Ads',
                'description' => 'Premium sponsored advertising placements',
                'route' => '/sponsored-adverts',
                'category_slugs' => ['sponsored-ads', 'sponsored'],
                'count_table' => 'sponsored_adverts',
                'image_sources' => [
                    ['table' => 'sponsored_adverts', 'column' => 'main_image'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'promoted',
                'name' => 'Promoted Ads',
                'description' => 'Promoted content and advertising campaigns',
                'route' => '/promoted-adverts',
                'category_slugs' => ['promoted', 'promoted-ads'],
                'count_table' => 'promoted_adverts',
                'image_sources' => [
                    ['table' => 'promoted_adverts', 'column' => 'main_image', 'prefix' => 'promoted-adverts/'],
                    ['table' => 'promoted_adverts', 'column' => 'image'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1533750349088-cd871a92f312?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'banner',
                'name' => 'Banner Ads',
                'description' => 'Display banner advertising solutions',
                'route' => '/banner-adverts',
                'category_slugs' => ['banner', 'banner-ads'],
                'count_table' => 'banner_ads',
                'image_sources' => [
                    ['table' => 'banner_ads', 'column' => 'banner_image'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1611162617474-5b21e879e113?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'featured',
                'name' => 'Featured / Personal Ads',
                'description' => 'Premium featured listings and highlighted content',
                'route' => '/featured',
                'category_slugs' => ['featured', 'featured-ads'],
                'count_table' => 'featured_adverts',
                'image_sources' => [
                    ['table' => 'featured_adverts', 'column' => 'images', 'json' => true],
                    ['table' => 'featured_adverts', 'column' => 'main_image'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'funding',
                'name' => 'Funding & Crowdfunding',
                'description' => 'Raise business funding via loan or share partnership campaigns',
                'route' => '/funding',
                'category_slugs' => ['funding'],
                'count_table' => 'funding_projects',
                'image_sources' => [
                    ['table' => 'funding_projects', 'column' => 'cover_image'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1633158829585-23ba8f7d8d0c?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'stores',
                'name' => 'Online Stores',
                'description' => 'Online stores and e-commerce marketplaces',
                'route' => '/stores',
                'category_slugs' => ['stores', 'online-stores'],
                'count_table' => 'customer_store',
                'image_sources' => [
                    ['table' => 'customer_store', 'column' => 'store_banner'],
                    ['table' => 'customer_store', 'column' => 'store_logo'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'books',
                'name' => 'Books & Literature',
                'description' => 'Educational books, novels, audiobooks, and digital publications',
                'route' => '/books',
                'category_slugs' => ['books'],
                'count_table' => 'books',
                'image_sources' => [
                    ['table' => 'books', 'column' => 'cover_image'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'vehicles',
                'name' => 'Vehicles & Transport',
                'description' => 'Cars, motorcycles, trucks, and transportation solutions',
                'route' => '/vehicles',
                'category_slugs' => ['vehicles'],
                'count_table' => 'vehicles',
                'image_sources' => [
                    ['table' => 'vehicles', 'column' => 'main_image'],
                    ['table' => 'vehicles', 'column' => 'images', 'json' => true],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'donations',
                'name' => 'Charities and Donations',
                'description' => 'Humanitarian causes and charitable contributions',
                'route' => '/donations',
                'category_slugs' => ['charities-and-donations', 'donations'],
                'count_table' => 'donations',
                'image_sources' => [
                    ['table' => 'donations', 'column' => 'cover_image'],
                    ['table' => 'donations', 'column' => 'images', 'json' => true],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'images',
                'name' => 'Stock Images & Media',
                'description' => 'Buy and sell admin-verified images for commercial and personal use',
                'route' => '/images',
                'category_slugs' => ['images', 'stock-images'],
                'count_table' => 'images_adverts',
                'image_sources' => [
                    ['table' => 'images_adverts', 'column' => 'main_image'],
                    ['table' => 'images_adverts', 'column' => 'thumbnail'],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'classifieds',
                'name' => 'Classifieds',
                'description' => 'General classified advertisements and listings',
                'route' => '/classifieds-ads',
                'category_slugs' => ['classifieds'],
                'count_table' => 'buysell_adverts',
                'image_sources' => [
                    ['table' => 'buysell_adverts', 'column' => 'images', 'json' => true],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1607082349566-187342175e2f?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'affiliate',
                'name' => 'Affiliate Hub',
                'description' => 'Affiliate marketing programs and partnership opportunities',
                'route' => '/affiliates',
                'category_slugs' => ['affiliate-programs', 'affiliate'],
                'count_table' => 'business_affiliate_offers',
                'image_sources' => [
                    ['table' => 'user_affiliate_posts', 'column' => 'image'],
                    ['table' => 'business_affiliate_offers', 'column' => 'promotional_assets', 'json' => true],
                    ['table' => 'affiliate_posts', 'column' => 'images', 'json' => true],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'resorts',
                'name' => 'Travel & Tourism',
                'description' => 'Luxury resorts, vacation packages, and travel destinations',
                'route' => '/resorts-travel',
                'category_slugs' => ['hotel-resorts-travel', 'resorts'],
                'count_table' => 'resorts_travel',
                'image_sources' => [
                    ['table' => 'resorts_travel', 'column' => 'main_image'],
                    ['table' => 'resorts_travel', 'column' => 'cover_image'],
                    ['table' => 'resorts_travel', 'column' => 'images', 'json' => true],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'investment',
                'name' => 'Businesses for Sale',
                'description' => 'Buy or sell online and physical businesses worldwide',
                'route' => '/businesses-for-sale',
                'category_slugs' => ['businesses-for-sale', 'investment'],
                'count_table' => 'sponsored_adverts',
                'image_sources' => [
                    ['table' => 'sponsored_adverts', 'column' => 'main_image', 'where' => ['advert_type' => 'business']],
                ],
                'fallback_images' => [
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=800&q=80',
                ],
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
     * Collect rotating card images from recent posts, then pad with curated fallbacks.
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
                    if ($resolved && $this->isUsableImageUrl($resolved)) {
                        $images[] = $resolved;
                    }
                }
            }
        }

        foreach ($def['fallback_images'] ?? [] as $fallback) {
            if (is_string($fallback) && $fallback !== '' && $this->isUsableImageUrl($fallback)) {
                $images[] = $fallback;
            }
        }

        $unique = [];
        foreach ($images as $url) {
            if (! is_string($url) || $url === '' || ! $this->isUsableImageUrl($url)) {
                continue;
            }
            if (! in_array($url, $unique, true)) {
                $unique[] = $url;
            }
            if (count($unique) >= 8) {
                break;
            }
        }

        return array_values($unique);
    }

    /**
     * @return array<int, string>
     */
    private function sampleListingImages(array $def, int $limit = 8): array
    {
        $sources = $def['image_sources'] ?? [];

        // Back-compat with older single-source keys
        if (empty($sources) && ! empty($def['image_table']) && ! empty($def['image_column'])) {
            $sources[] = [
                'table' => $def['image_table'],
                'column' => $def['image_column'],
                'json' => ! empty($def['image_json']),
                'prefix' => $def['image_prefix'] ?? null,
            ];
        }

        $urls = [];
        foreach ($sources as $source) {
            foreach ($this->sampleFromSource($source, $limit) as $url) {
                if (! in_array($url, $urls, true)) {
                    $urls[] = $url;
                }
                if (count($urls) >= $limit) {
                    return $urls;
                }
            }
        }

        return $urls;
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<int, string>
     */
    private function sampleFromSource(array $source, int $limit): array
    {
        $table = $source['table'] ?? null;
        $column = $source['column'] ?? null;
        if (! $table || ! $column || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return [];
        }

        $orderCol = Schema::hasColumn($table, 'updated_at')
            ? 'updated_at'
            : (Schema::hasColumn($table, 'created_at') ? 'created_at' : null);

        $query = DB::table($table)->whereNotNull($column)->where($column, '!=', '');

        foreach ($source['where'] ?? [] as $key => $value) {
            if (Schema::hasColumn($table, $key)) {
                $query->where($key, $value);
            }
        }

        if ($orderCol) {
            $query->orderByDesc($orderCol);
        }

        $rows = $query->limit(max($limit * 3, 16))->get([$column]);
        $urls = [];

        foreach ($rows as $row) {
            $raw = $row->{$column};
            $candidates = $this->extractCandidates($raw, ! empty($source['json']));

            foreach ($candidates as $candidate) {
                $prefix = $source['prefix'] ?? null;
                if ($prefix && ! str_contains($candidate, '/') && ! str_starts_with($candidate, 'http')) {
                    $candidate = rtrim($prefix, '/').'/'.ltrim($candidate, '/');
                }
                $resolved = MediaUrlHelper::resolve($candidate);
                if ($resolved && $this->isUsableImageUrl($resolved) && ! in_array($resolved, $urls, true)) {
                    $urls[] = $resolved;
                }
                if (count($urls) >= $limit) {
                    return $urls;
                }
            }
        }

        return $urls;
    }

    /**
     * @return array<int, string>
     */
    private function extractCandidates(mixed $raw, bool $asJson): array
    {
        $candidates = [];

        if ($asJson) {
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_string($item) && $item !== '') {
                        $candidates[] = $item;
                    } elseif (is_array($item)) {
                        $path = $item['url'] ?? $item['image_path'] ?? $item['path'] ?? $item['src'] ?? null;
                        if (is_string($path) && $path !== '') {
                            $candidates[] = $path;
                        }
                    }
                }
            }
        } elseif (is_string($raw) && $raw !== '') {
            $candidates[] = $raw;
        }

        return $candidates;
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

    private function isUsableImageUrl(string $url): bool
    {
        $lower = strtolower($url);
        if ($url === '' || str_contains($lower, 'example.com') || str_contains($lower, 'via.placeholder') || str_contains($lower, 'placehold.co')) {
            return false;
        }

        // Skip tiny icon names / non-paths
        if (! str_contains($url, '/') && ! str_contains($url, '.') && ! str_starts_with($url, 'http')) {
            return false;
        }

        return true;
    }

    private function looksLikeImagePath(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (! str_contains($value, '/') && ! str_contains($value, '.') && ! str_starts_with($value, 'http')) {
            return false;
        }

        return (bool) preg_match('/\.(jpe?g|png|gif|webp|svg)(\?|$)/i', $value)
            || str_starts_with($value, 'http')
            || str_contains($value, 'storage/')
            || str_contains($value, 'categories/');
    }
}
