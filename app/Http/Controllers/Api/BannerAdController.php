<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use App\Models\BannerCategory;
use App\Http\Resources\BannerAdResource;
use App\Http\Resources\BannerAdCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BannerAdController extends Controller
{
    /**
     * Display a listing of banner ads.
     */
    public function index(Request $request): BannerAdCollection
    {
        $query = BannerAd::with(['category', 'user']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // By default, only show active banners
            $query->active();
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->inCategory($request->category_id);
        }

        // Filter by country
        if ($request->has('country')) {
            $query->inCountry($request->country);
        }

        // Filter by promotion tier
        if ($request->has('promotion_tier')) {
            if ($request->promotion_tier === 'promoted') {
                $query->promoted();
            } elseif ($request->promotion_tier === 'featured') {
                $query->featured();
            } elseif ($request->promotion_tier === 'sponsored') {
                $query->sponsored();
            } elseif ($request->promotion_tier === 'network_boost') {
                $query->networkBoost();
            } else {
                $query->where('promotion_tier', $request->promotion_tier);
            }
        }

        // Filter by banner size
        if ($request->has('banner_size')) {
            $query->where('banner_size', $request->banner_size);
        }

        // Search by keyword
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'views':
                $query->orderBy('views_count', $sortOrder);
                break;
            case 'clicks':
                $query->orderBy('clicks_count', $sortOrder);
                break;
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'promotion_tier':
                $query->orderByRaw("FIELD(promotion_tier, 'network_boost', 'sponsored', 'featured', 'promoted', 'standard') {$sortOrder}");
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $limit = $request->get('limit', 20);
        $bannerAds = $query->paginate($limit);

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Get featured banner ads for carousel.
     */
    public function featured(Request $request): BannerAdCollection
    {
        $query = BannerAd::with(['category', 'user'])
            ->active()
            ->featured()
            ->where(function ($q) {
                $q->whereNull('promotion_end')
                  ->orWhere('promotion_end', '>=', now());
            });

        $limit = $request->get('limit', 10);
        $bannerAds = $query->orderBy('promotion_start', 'desc')->limit($limit)->get();

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Get most viewed banner ads.
     */
    public function mostViewed(Request $request): BannerAdCollection
    {
        $limit = $request->get('limit', 10);
        $bannerAds = BannerAd::with(['category', 'user'])
            ->active()
            ->mostViewed($limit)
            ->get();

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Get recently added banner ads.
     */
    public function recent(Request $request): BannerAdCollection
    {
        $limit = $request->get('limit', 10);
        $bannerAds = BannerAd::with(['category', 'user'])
            ->active()
            ->recent($limit)
            ->get();

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Store a newly created banner ad.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:500',
            'banner_type' => ['required', Rule::in(['image', 'animated', 'html5', 'video'])],
            'banner_size' => ['required', Rule::in(['728x90', '300x250', '160x600', '970x250', '468x60', '1080x1080'])],
            'banner_image' => 'required|string|max:255',
            'destination_link' => 'required|url|max:500',
            'call_to_action' => 'nullable|string|max:100',
            'key_selling_points' => 'nullable|string|max:1000',
            'offer_details' => 'nullable|string|max:1000',
            'validity_start' => 'nullable|date',
            'validity_end' => 'nullable|date|after_or_equal:validity_start',
            'banner_category_id' => 'required|exists:banner_categories,id',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'target_countries' => 'nullable|array',
            'target_countries.*' => 'string|max:100',
            'target_audience' => 'nullable|array',
            'target_audience.*' => 'string|max:255',
            'promotion_tier' => ['required', Rule::in(['standard', 'promoted', 'featured', 'sponsored', 'network_boost'])],
            'promotion_price' => 'required|numeric|min:0',
            'promotion_start' => 'nullable|date',
            'promotion_end' => 'nullable|date|after_or_equal:promotion_start',
            'is_verified_business' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $bannerAd = BannerAd::create($request->all());
            
            // If user is authenticated, associate the banner with them
            if (Auth::guard('api')->check()) {
                $bannerAd->user_id = Auth::guard('api')->id();
                $bannerAd->save();
            }

            // Update category banner count
            $bannerAd->category->updateActiveBannersCount();

            return response()->json([
                'success' => true,
                'message' => 'Banner ad created successfully',
                'data' => new BannerAdResource($bannerAd->load(['category', 'user']))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner ad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified banner ad.
     */
    public function show(string $slug): JsonResponse
    {
        $bannerAd = BannerAd::with(['category', 'user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view count
        $bannerAd->incrementViews();

        return response()->json([
            'success' => true,
            'data' => new BannerAdResource($bannerAd)
        ]);
    }

    /**
     * Update the specified banner ad.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $bannerAd = BannerAd::findOrFail($id);

        // Check if user owns this banner or is admin
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if ($bannerAd->user_id !== $user->user_id && !$user->hasPermission('manage_listings')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this banner ad'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:2000',
            'business_name' => 'sometimes|required|string|max:255',
            'contact_person' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'website_url' => 'sometimes|nullable|url|max:500',
            'banner_type' => ['sometimes', 'required', Rule::in(['image', 'animated', 'html5', 'video'])],
            'banner_size' => ['sometimes', 'required', Rule::in(['728x90', '300x250', '160x600', '970x250', '468x60', '1080x1080'])],
            'banner_image' => 'sometimes|required|string|max:255',
            'destination_link' => 'sometimes|required|url|max:500',
            'call_to_action' => 'sometimes|nullable|string|max:100',
            'key_selling_points' => 'sometimes|nullable|string|max:1000',
            'offer_details' => 'sometimes|nullable|string|max:1000',
            'validity_start' => 'sometimes|nullable|date',
            'validity_end' => 'sometimes|nullable|date|after_or_equal:validity_start',
            'banner_category_id' => 'sometimes|required|exists:banner_categories,id',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'target_countries' => 'sometimes|nullable|array',
            'target_countries.*' => 'string|max:100',
            'target_audience' => 'sometimes|nullable|array',
            'target_audience.*' => 'string|max:255',
            'promotion_tier' => ['sometimes', 'required', Rule::in(['standard', 'promoted', 'featured', 'sponsored', 'network_boost'])],
            'promotion_price' => 'sometimes|required|numeric|min:0',
            'promotion_start' => 'sometimes|nullable|date',
            'promotion_end' => 'sometimes|nullable|date|after_or_equal:promotion_start',
            'is_verified_business' => 'sometimes|boolean',
            'status' => ['sometimes', Rule::in(['draft', 'pending', 'active', 'rejected', 'expired'])],
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $bannerAd->update($request->all());

            // Update category banner count if category changed
            if ($request->has('banner_category_id') && $request->banner_category_id != $bannerAd->getOriginal('banner_category_id')) {
                $oldCategory = BannerCategory::find($bannerAd->getOriginal('banner_category_id'));
                $newCategory = BannerCategory::find($request->banner_category_id);
                
                if ($oldCategory) $oldCategory->updateActiveBannersCount();
                if ($newCategory) $newCategory->updateActiveBannersCount();
            }

            return response()->json([
                'success' => true,
                'message' => 'Banner ad updated successfully',
                'data' => new BannerAdResource($bannerAd->load(['category', 'user']))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner ad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified banner ad.
     */
    public function destroy(string $id): JsonResponse
    {
        $bannerAd = BannerAd::findOrFail($id);

        // Check if user owns this banner or is admin
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if ($bannerAd->user_id !== $user->user_id && !$user->hasPermission('manage_listings')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this banner ad'
                ], 403);
            }
        }

        try {
            $categoryId = $bannerAd->banner_category_id;
            $bannerAd->delete();

            // Update category banner count
            $category = BannerCategory::find($categoryId);
            if ($category) {
                $category->updateActiveBannersCount();
            }

            return response()->json([
                'success' => true,
                'message' => 'Banner ad deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner ad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track a click on a banner ad.
     */
    public function trackClick(string $slug): JsonResponse
    {
        $bannerAd = BannerAd::where('slug', $slug)->firstOrFail();
        
        $bannerAd->incrementClicks();

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
            'destination_link' => $bannerAd->destination_link
        ]);
    }

    /**
     * Get banner ads for the authenticated user.
     */
    public function myBanners(Request $request): BannerAdCollection
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $query = BannerAd::with(['category'])
            ->where('user_id', Auth::guard('api')->id());

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $limit = $request->get('limit', 20);
        $bannerAds = $query->paginate($limit);

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Get promotion options and pricing.
     */
    public function promotionOptions(): JsonResponse
    {
        $options = [
            [
                'tier' => 'promoted',
                'name' => 'Promoted Banner',
                'price' => 50.00,
                'currency' => 'GBP',
                'duration' => 30,
                'benefits' => [
                    'Highlighted banner',
                    'Appears above standard banners',
                    'Promoted badge',
                    '2× more visibility'
                ]
            ],
            [
                'tier' => 'featured',
                'name' => 'Featured Banner',
                'price' => 100.00,
                'currency' => 'GBP',
                'duration' => 30,
                'benefits' => [
                    'Top of category pages',
                    'Larger banner preview',
                    'Priority in search results',
                    'Included in weekly Featured Banners email',
                    'Featured badge',
                    '4× more visibility'
                ],
                'is_popular' => true
            ],
            [
                'tier' => 'sponsored',
                'name' => 'Sponsored Banner',
                'price' => 200.00,
                'currency' => 'GBP',
                'duration' => 30,
                'benefits' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    'Sponsored badge',
                    'Maximum visibility'
                ]
            ],
            [
                'tier' => 'network_boost',
                'name' => 'Network-Wide Boost',
                'price' => 500.00,
                'currency' => 'GBP',
                'duration' => 30,
                'benefits' => [
                    'Appears across multiple pages',
                    'Banner Ads page',
                    'Homepage',
                    'Category pages',
                    'Related search pages',
                    'Included in email newsletters',
                    'Included in push notifications',
                    'Top Spotlight badge',
                    'Ultimate visibility'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }
}
