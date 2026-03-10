<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use App\Models\BannerCategory;
use App\Http\Resources\BannerAdResource;
use App\Http\Resources\BannerAdCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BannerMarketplaceController extends Controller
{
    public function homepage(Request $request): JsonResponse
    {
        $featured = BannerAd::with(['category', 'user'])
            ->active()->featured()->limit(6)->get();
        
        $recent = BannerAd::with(['category', 'user'])
            ->active()->recent()->limit(6)->get();
        
        $categories = BannerCategory::withCount('activeBannerAds')
            ->where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'featured_banners' => BannerAdResource::collection($featured),
                'recent_banners' => BannerAdResource::collection($recent),
                'categories' => $categories
            ]
        ]);
    }

    public function carousel(Request $request): JsonResponse
    {
        $banners = BannerAd::with(['category', 'user'])
            ->active()
            ->featured()
            ->orderBy('promotion_start', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => BannerAdResource::collection($banners)
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        $categories = BannerCategory::withCount('activeBannerAds')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $totalBanners = BannerAd::active()->count();
        $totalViews = BannerAd::sum('views_count');
        $totalClicks = BannerAd::sum('clicks_count');
        
        $trendingCategories = BannerCategory::withCount('activeBannerAds')
            ->orderBy('active_banner_ads_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_banners' => $totalBanners,
                'total_views' => $totalViews,
                'total_clicks' => $totalClicks,
                'trending_categories' => $trendingCategories
            ]
        ]);
    }
}
