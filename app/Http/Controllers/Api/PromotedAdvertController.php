<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromotedAdvert;
use App\Models\PromotedAdvertCategory;
use App\Models\PromotedAdvertFavorite;
use App\Models\PromotedAdvertAnalytic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PromotedAdvertController extends Controller
{
    /**
     * Display a listing of promoted adverts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PromotedAdvert::active()->with(['category', 'user']);

        // Filters
        if ($request->has('category')) {
            $query->inCategory($request->category);
        }

        if ($request->has('country')) {
            $query->inCountry($request->country);
        }

        if ($request->has('advert_type')) {
            $query->ofType($request->advert_type);
        }

        if ($request->has('promotion_tier')) {
            $query->byTier($request->promotion_tier);
        }

        if ($request->has('featured')) {
            $query->featured();
        }

        if ($request->has('currently_promoted')) {
            $query->currentlyPromoted();
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        match($sortBy) {
            'trending' => $query->orderBy('views_count', $sortOrder),
            'views' => $query->orderBy('views_count', $sortOrder),
            'saves' => $query->orderBy('saves_count', $sortOrder),
            'price' => $query->orderBy('price', $sortOrder),
            'title' => $query->orderBy('title', $sortOrder),
            'created_at' => $query->orderBy('created_at', $sortOrder),
            default => $query->orderBy('created_at', 'desc'),
        };

        $promotedAdverts = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $promotedAdverts,
        ]);
    }

    /**
     * Store a newly created promoted advert.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'required|string',
            'key_features' => 'nullable|array',
            'special_notes' => 'nullable|string',
            'advert_type' => 'required|in:product,service,property,vehicle,job,event,business,miscellaneous',
            'category_id' => 'nullable|exists:promoted_advert_categories,id',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_privacy' => 'in:exact,approximate',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'in:GBP,USD,EUR',
            'price_type' => 'in:fixed,negotiable,free',
            'condition' => 'nullable|in:new,used,not_applicable',
            'main_image' => 'required|string',
            'additional_images' => 'nullable|array|max:10',
            'video_link' => 'nullable|url',
            'seller_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'promotion_tier' => 'required|in:promoted_basic,promoted_plus,promoted_premium,network_wide_boost',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Set promotion price based on tier
        $promotionPrices = [
            'promoted_basic' => 29.99,
            'promoted_plus' => 59.99,
            'promoted_premium' => 99.99,
            'network_wide_boost' => 199.99,
        ];

        $data = $validator->validated();
        $data['promotion_price'] = $promotionPrices[$data['promotion_tier']];
        $data['user_id'] = Auth::id();
        $data['status'] = 'active';
        $data['is_active'] = true;
        $data['approved_at'] = now();

        $promotedAdvert = PromotedAdvert::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Promoted advert created successfully',
            'data' => $promotedAdvert->load(['category', 'user']),
        ], 201);
    }

    /**
     * Display the specified promoted advert.
     */
    public function show(string $slug): JsonResponse
    {
        $promotedAdvert = PromotedAdvert::with(['category', 'user', 'favorites'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment views
        $promotedAdvert->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $promotedAdvert,
        ]);
    }

    /**
     * Update the specified promoted advert.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $promotedAdvert = PromotedAdvert::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'sometimes|required|string',
            'key_features' => 'nullable|array',
            'special_notes' => 'nullable|string',
            'advert_type' => 'sometimes|required|in:product,service,property,vehicle,job,event,business,miscellaneous',
            'category_id' => 'nullable|exists:promoted_advert_categories,id',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_privacy' => 'in:exact,approximate',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'in:GBP,USD,EUR',
            'price_type' => 'in:fixed,negotiable,free',
            'condition' => 'nullable|in:new,used,not_applicable',
            'main_image' => 'sometimes|required|string',
            'additional_images' => 'nullable|array|max:10',
            'video_link' => 'nullable|url',
            'seller_name' => 'sometimes|required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'promotion_tier' => 'sometimes|required|in:promoted_basic,promoted_plus,promoted_premium,network_wide_boost',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Update promotion price if tier changed
        if (isset($data['promotion_tier'])) {
            $promotionPrices = [
                'promoted_basic' => 29.99,
                'promoted_plus' => 59.99,
                'promoted_premium' => 99.99,
                'network_wide_boost' => 199.99,
            ];
            $data['promotion_price'] = $promotionPrices[$data['promotion_tier']];
        }

        $promotedAdvert->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Promoted advert updated successfully',
            'data' => $promotedAdvert->load(['category', 'user']),
        ]);
    }

    /**
     * Remove the specified promoted advert.
     */
    public function destroy(int $id): JsonResponse
    {
        $promotedAdvert = PromotedAdvert::where('user_id', Auth::id())->findOrFail($id);
        $promotedAdvert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Promoted advert deleted successfully',
        ]);
    }

    /**
     * Get featured promoted adverts.
     */
    public function featured(): JsonResponse
    {
        $featuredAdverts = PromotedAdvert::active()
            ->featured()
            ->with(['category', 'user'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
        ]);
    }

    /**
     * Get most viewed promoted adverts.
     */
    public function mostViewed(): JsonResponse
    {
        $mostViewed = PromotedAdvert::active()
            ->mostViewed(12)
            ->with(['category', 'user'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mostViewed,
        ]);
    }

    /**
     * Get most saved promoted adverts.
     */
    public function mostSaved(): JsonResponse
    {
        $mostSaved = PromotedAdvert::active()
            ->mostSaved(12)
            ->with(['category', 'user'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mostSaved,
        ]);
    }

    /**
     * Get recent promoted adverts.
     */
    public function recent(): JsonResponse
    {
        $recent = PromotedAdvert::active()
            ->recent(12)
            ->with(['category', 'user'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recent,
        ]);
    }

    /**
     * Track click on promoted advert.
     */
    public function trackClick(string $slug): JsonResponse
    {
        $promotedAdvert = PromotedAdvert::where('slug', $slug)->firstOrFail();
        $promotedAdvert->incrementClicks();

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
        ]);
    }

    /**
     * Get promotion options with pricing.
     */
    public function promotionOptions(): JsonResponse
    {
        $options = [
            [
                'tier' => 'promoted_basic',
                'name' => 'Promoted Basic',
                'price' => 29.99,
                'currency' => 'GBP',
                'features' => [
                    'Highlighted listing',
                    'Appears above standard ads',
                    '"Promoted" badge',
                    '2× more visibility',
                ],
                'popular' => false,
            ],
            [
                'tier' => 'promoted_plus',
                'name' => 'Promoted Plus',
                'price' => 59.99,
                'currency' => 'GBP',
                'features' => [
                    'All Basic features',
                    'Top of category placement',
                    'Larger advert card',
                    'Priority in search results',
                    'Included in weekly "Promoted Highlights" email',
                ],
                'popular' => true,
            ],
            [
                'tier' => 'promoted_premium',
                'name' => 'Promoted Premium',
                'price' => 99.99,
                'currency' => 'GBP',
                'features' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    '"Premium Promoted" badge',
                    'Maximum visibility',
                ],
                'popular' => false,
            ],
            [
                'tier' => 'network_wide_boost',
                'name' => 'Network-Wide Boost',
                'price' => 199.99,
                'currency' => 'GBP',
                'features' => [
                    'Appears across multiple pages',
                    'Promoted Adverts Page',
                    'Homepage',
                    'Category pages',
                    'Related search pages',
                    'Included in email newsletters',
                    'Included in push notifications',
                    '"Top Spotlight" badge',
                ],
                'popular' => false,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $options,
        ]);
    }

    /**
     * Get user's promoted adverts.
     */
    public function myAdverts(): JsonResponse
    {
        $myAdverts = PromotedAdvert::where('user_id', Auth::id())
            ->with(['category'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $myAdverts,
        ]);
    }

    /**
     * Upload images for promoted advert.
     */
    public function uploadImages(Request $request): JsonResponse
    {
        // Handle both 'images' and 'images[]' field names
        $images = $request->file('images') ?: $request->file('images.');
        
        if (!$images) {
            return response()->json([
                'success' => false,
                'message' => 'No files found. Please select images to upload.',
                'errors' => [
                    'images' => ['The images field is required.']
                ],
            ], 422);
        }

        $validator = Validator::make(['images' => $images], [
            'images' => 'required|array|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [];
            
            foreach ($errors->all() as $error) {
                $errorMessages[] = $error;
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $errorMessages),
                'errors' => $errors,
            ], 422);
        }

        $uploadedImages = [];

        foreach ($images as $index => $image) {
            try {
                $filename = time() . '_' . $index . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('promoted-adverts', $filename, 'public');
                $uploadedImages[] = $filename;
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to upload image at index {$index}: " . $e->getMessage(),
                    'errors' => [
                        "images.{$index}" => [$e->getMessage()]
                    ],
                ], 422);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'data' => [
                'images' => $uploadedImages,
            ],
        ]);
    }

    /**
     * Upload logo for promoted advert.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $logo = $request->file('logo');
        $filename = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
        $path = $logo->storeAs('promoted-adverts/logos', $filename, 'public');

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'data' => [
                'logo' => $filename,
            ],
        ]);
    }

    /**
     * Toggle favorite status.
     */
    public function toggleFavorite(int $id): JsonResponse
    {
        $promotedAdvert = PromotedAdvert::findOrFail($id);
        $user = Auth::user();

        $favorite = PromotedAdvertFavorite::where('promoted_advert_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $promotedAdvert->decrement('saves_count');
            $favorited = false;
        } else {
            PromotedAdvertFavorite::create([
                'promoted_advert_id' => $id,
                'user_id' => $user->id,
            ]);
            $promotedAdvert->incrementSaves();
            $favorited = true;
        }

        return response()->json([
            'success' => true,
            'message' => $favorited ? 'Added to favorites' : 'Removed from favorites',
            'data' => [
                'favorited' => $favorited,
            ],
        ]);
    }

    /**
     * Get promoted adverts statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalActive = PromotedAdvert::active()->count();
            $totalViews = PromotedAdvert::active()->sum('views_count');
            $totalSaves = PromotedAdvert::active()->sum('saves_count');
            $totalClicks = PromotedAdvert::active()->sum('clicks_count');
            $totalCategories = PromotedAdvertCategory::active()->count();
            $totalCountries = PromotedAdvert::active()->distinct('country')->count('country');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_active_adverts' => $totalActive ?? 0,
                    'total_views' => $totalViews ?? 0,
                    'total_saves' => $totalSaves ?? 0,
                    'total_clicks' => $totalClicks ?? 0,
                    'total_categories' => $totalCategories ?? 0,
                    'total_countries' => $totalCountries ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_active_adverts' => 0,
                    'total_views' => 0,
                    'total_saves' => 0,
                    'total_clicks' => 0,
                    'total_categories' => 0,
                    'total_countries' => 0,
                ],
            ]);
        }
    }

    /**
     * Get live activity feed.
     */
    public function liveActivity(): JsonResponse
    {
        try {
            // Get recent analytics events
            $recentActivities = PromotedAdvertAnalytic::with(['promotedAdvert'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($analytic) {
                    $advert = $analytic->promotedAdvert;
                    return [
                        'id' => $analytic->id,
                        'event_type' => $analytic->event_type,
                        'country' => $analytic->country,
                        'city' => $analytic->city,
                        'advert_title' => $advert ? $advert->title : 'Unknown',
                        'advert_slug' => $advert ? $advert->slug : null,
                        'created_at' => $analytic->created_at ? $analytic->created_at->diffForHumans() : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $recentActivities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }

    /**
     * Get trending countries.
     */
    public function trendingCountries(): JsonResponse
    {
        $trendingCountries = PromotedAdvert::active()
            ->selectRaw('country, COUNT(*) as advert_count, SUM(views_count) as total_views')
            ->groupBy('country')
            ->orderBy('total_views', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trendingCountries,
        ]);
    }

    /**
     * Get trending categories.
     */
    public function trendingCategories(): JsonResponse
    {
        try {
            $trendingCategories = PromotedAdvertCategory::active()
                ->withCount(['promotedAdverts' => function ($query) {
                    $query->active();
                }])
                ->orderBy('promoted_adverts_count', 'desc')
                ->limit(12)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $trendingCategories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }
}
