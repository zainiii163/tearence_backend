<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeaturedAdvert;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FeaturedAdvertBannerController extends Controller
{
    /**
     * Get featured adverts that can be displayed as banners.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->orderByPriority();

        // Filter by tier for banner display
        if ($request->has('tier')) {
            $query->byTier($request->tier);
        }

        // Limit results for banner display
        $limit = min($request->get('limit', 10), 20);
        $featuredAdverts = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Featured adverts for banner display retrieved successfully'
        ]);
    }

    /**
     * Get sponsored featured adverts for homepage banner slider.
     */
    public function homepageSlider(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 8), 12);
        
        $sponsoredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->sponsored()
            ->orderByPriority()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sponsoredAdverts,
            'message' => 'Homepage slider featured adverts retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts by category for category banners.
     */
    public function categoryBanners(Request $request, int $categoryId): JsonResponse
    {
        $limit = min($request->get('limit', 6), 10);
        
        $featuredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->byCategory($categoryId)
            ->orderByPriority()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Category featured adverts for banners retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts for country-specific banners.
     */
    public function countryBanners(Request $request, string $country): JsonResponse
    {
        $limit = min($request->get('limit', 6), 10);
        
        $featuredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->byCountry($country)
            ->orderByPriority()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Country featured adverts for banners retrieved successfully'
        ]);
    }

    /**
     * Create a banner from a featured advert.
     */
    public function createFromFeatured(Request $request, int $featuredAdvertId): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::findOrFail($featuredAdvertId);

        $validated = $request->validate([
            'banner_type' => 'required|string|in:featured_advert,sponsored_advert,promoted_advert',
            'position' => 'required|string|in:homepage,category,sidebar,footer',
            'duration_days' => 'required|integer|min:1|max:365',
        ]);

        // Create banner from featured advert
        $banner = Banner::create([
            'title' => $featuredAdvert->title,
            'description' => $featuredAdvert->description,
            'img' => $featuredAdvert->main_image,
            'url_link' => route('featured-adverts.show', $featuredAdvert->id),
            'banner_type' => $validated['banner_type'],
            'position' => $validated['position'],
            'price' => $featuredAdvert->upsell_price,
            'user_id' => $featuredAdvert->customer_id,
            'listing_id' => $featuredAdvert->listing_id,
            'expires_at' => now()->addDays($validated['duration_days']),
            'is_active' => true,
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $banner->load(['user', 'featuredAdverts']),
            'message' => 'Banner created from featured advert successfully'
        ], 201);
    }

    /**
     * Get banner analytics for featured adverts.
     */
    public function bannerAnalytics(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '30days'); // 7days, 30days, 90days
        
        $startDate = match($timeframe) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            default => now()->subDays(30),
        };

        $analytics = [
            'total_banner_views' => Banner::where('created_at', '>=', $startDate)
                ->where('banner_type', 'like', '%featured%')
                ->sum('views') ?? 0,
            
            'featured_advert_conversions' => FeaturedAdvert::where('created_at', '>=', $startDate)
                ->where('payment_status', 'paid')
                ->count(),
            
            'banner_types' => Banner::where('created_at', '>=', $startDate)
                ->where('banner_type', 'like', '%featured%')
                ->select('banner_type', DB::raw('count(*) as count'))
                ->groupBy('banner_type')
                ->get(),
            
            'top_performing_categories' => FeaturedAdvert::where('created_at', '>=', $startDate)
                ->where('payment_status', 'paid')
                ->with('category')
                ->select('category_id', DB::raw('count(*) as count'))
                ->groupBy('category_id')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'message' => 'Featured advert banner analytics retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts with banner integration data.
     */
    public function withBannerData(Request $request): JsonResponse
    {
        $featuredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->orderByPriority()
            ->get()
            ->map(function ($advert) {
                $advert->banner_data = [
                    'has_banner' => Banner::where('listing_id', $advert->listing_id)
                        ->where('banner_type', 'like', '%featured%')
                        ->active()
                        ->exists(),
                    'banner_clicks' => Banner::where('listing_id', $advert->listing_id)
                        ->where('banner_type', 'like', '%featured%')
                        ->sum('clicks') ?? 0,
                    'banner_views' => Banner::where('listing_id', $advert->listing_id)
                        ->where('banner_type', 'like', '%featured%')
                        ->sum('views') ?? 0,
                ];
                return $advert;
            });

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Featured adverts with banner data retrieved successfully'
        ]);
    }
}
