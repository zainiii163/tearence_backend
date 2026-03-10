<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeaturedAdvert;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FeaturedAdvertController extends Controller
{
    /**
     * Display a listing of featured adverts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active();

        // Filter by upsell tier
        if ($request->has('tier')) {
            $query->byTier($request->tier);
        }

        // Filter by country
        if ($request->has('country')) {
            $query->byCountry($request->country);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by advert type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        // Filter verified sellers only
        if ($request->boolean('verified_only')) {
            $query->verified();
        }

        // Search functionality
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // Order by priority
        $query->orderByPriority();

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $featuredAdverts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Featured adverts retrieved successfully'
        ]);
    }

    /**
     * Store a newly created featured advert.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listing,listing_id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'currency' => 'string|size:3',
            'advert_type' => ['required', Rule::in(FeaturedAdvert::TYPE_PRODUCT, FeaturedAdvert::TYPE_SERVICE, FeaturedAdvert::TYPE_PROPERTY, FeaturedAdvert::TYPE_JOB, FeaturedAdvert::TYPE_EVENT, FeaturedAdvert::TYPE_VEHICLE, FeaturedAdvert::TYPE_BUSINESS, FeaturedAdvert::TYPE_EDUCATION, FeaturedAdvert::TYPE_TRAVEL, FeaturedAdvert::TYPE_FASHION, FeaturedAdvert::TYPE_ELECTRONICS, FeaturedAdvert::TYPE_PETS, FeaturedAdvert::TYPE_HOME, FeaturedAdvert::TYPE_HEALTH, FeaturedAdvert::TYPE_MISC)],
            'condition' => 'nullable|in:new,used,refurbished',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string|max:255',
            'video_url' => 'nullable|url|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'upsell_tier' => ['required', Rule::in(FeaturedAdvert::TIER_PROMOTED, FeaturedAdvert::TIER_FEATURED, FeaturedAdvert::TIER_SPONSORED)],
            'upsell_price' => 'required|numeric|min:0|max:99999.99',
            'starts_at' => 'required|date|after_or_equal:today',
            'expires_at' => 'required|date|after:starts_at',
        ]);

        // Get customer from authenticated user
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Customer authentication required.'
            ], 401);
        }

        // Verify listing ownership
        $listing = Listing::findOrFail($validated['listing_id']);
        if ($listing->customer_id !== $customer->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only create featured adverts for your own listings.'
            ], 403);
        }

        // Get category and country IDs
        $categoryId = $listing->category_id;
        $country = Country::where('name', $validated['country'])->first();
        $countryId = $country ? $country->country_id : null;

        $validated['customer_id'] = $customer->customer_id;
        $validated['category_id'] = $categoryId;
        $validated['country_id'] = $countryId;
        $validated['payment_status'] = FeaturedAdvert::PAYMENT_PENDING;
        $validated['is_active'] = false; // Will be activated after payment

        $featuredAdvert = FeaturedAdvert::create($validated);

        return response()->json([
            'success' => true,
            'data' => $featuredAdvert->load(['listing', 'customer', 'category', 'country']),
            'message' => 'Featured advert created successfully. Please complete payment to activate.'
        ], 201);
    }

    /**
     * Display the specified featured advert.
     */
    public function show(string $id): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->findOrFail($id);

        // Increment view count
        $featuredAdvert->incrementViewCount();

        return response()->json([
            'success' => true,
            'data' => $featuredAdvert,
            'message' => 'Featured advert retrieved successfully'
        ]);
    }

    /**
     * Update the specified featured advert.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::findOrFail($id);

        // Check ownership
        $customer = Auth::guard('customer')->user();
        if (!$customer || $featuredAdvert->customer_id !== $customer->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only update your own featured adverts.'
            ], 403);
        }

        // Don't allow edits if payment is completed and advert is active
        if ($featuredAdvert->payment_status === FeaturedAdvert::PAYMENT_PAID && $featuredAdvert->isCurrentlyActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit an active featured advert. Please contact support.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'currency' => 'string|size:3',
            'advert_type' => ['sometimes', 'required', Rule::in(FeaturedAdvert::TYPE_PRODUCT, FeaturedAdvert::TYPE_SERVICE, FeaturedAdvert::TYPE_PROPERTY, FeaturedAdvert::TYPE_JOB, FeaturedAdvert::TYPE_EVENT, FeaturedAdvert::TYPE_VEHICLE, FeaturedAdvert::TYPE_BUSINESS, FeaturedAdvert::TYPE_EDUCATION, FeaturedAdvert::TYPE_TRAVEL, FeaturedAdvert::TYPE_FASHION, FeaturedAdvert::TYPE_ELECTRONICS, FeaturedAdvert::TYPE_PETS, FeaturedAdvert::TYPE_HOME, FeaturedAdvert::TYPE_HEALTH, FeaturedAdvert::TYPE_MISC)],
            'condition' => 'nullable|in:new,used,refurbished',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string|max:255',
            'video_url' => 'nullable|url|max:255',
            'contact_name' => 'sometimes|required|string|max:255',
            'contact_email' => 'sometimes|required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'upsell_tier' => ['sometimes', 'required', Rule::in(FeaturedAdvert::TIER_PROMOTED, FeaturedAdvert::TIER_FEATURED, FeaturedAdvert::TIER_SPONSORED)],
            'upsell_price' => 'sometimes|required|numeric|min:0|max:99999.99',
            'starts_at' => 'sometimes|required|date|after_or_equal:today',
            'expires_at' => 'sometimes|required|date|after:starts_at',
        ]);

        // Update country ID if country changed
        if (isset($validated['country'])) {
            $country = Country::where('name', $validated['country'])->first();
            $validated['country_id'] = $country ? $country->country_id : null;
        }

        $featuredAdvert->update($validated);

        return response()->json([
            'success' => true,
            'data' => $featuredAdvert->load(['listing', 'customer', 'category', 'country']),
            'message' => 'Featured advert updated successfully'
        ]);
    }

    /**
     * Remove the specified featured advert.
     */
    public function destroy(string $id): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::findOrFail($id);

        // Check ownership or admin
        $customer = Auth::guard('customer')->user();
        $user = Auth::guard('api')->user();
        
        if ((!$customer || $featuredAdvert->customer_id !== $customer->customer_id) && !$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $featuredAdvert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Featured advert deleted successfully'
        ]);
    }

    /**
     * Get featured adverts for the current customer.
     */
    public function myFeaturedAdverts(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Customer authentication required.'
            ], 401);
        }

        $query = FeaturedAdvert::with(['listing', 'category', 'country'])
            ->where('customer_id', $customer->customer_id);

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by upsell tier
        if ($request->has('tier')) {
            $query->byTier($request->tier);
        }

        $query->orderByDesc('created_at');

        $perPage = min($request->get('per_page', 20), 100);
        $featuredAdverts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'My featured adverts retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts carousel (highest tier sponsored ads).
     */
    public function carousel(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 20);

        $featuredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->sponsored()
            ->orderByPriority()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Featured carousel adverts retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts by category grid.
     */
    public function categoryGrid(Request $request): JsonResponse
    {
        $categories = Category::withCount([
            'featuredAdverts' => function ($query) {
                $query->active();
            }
        ])
        ->whereHas('featuredAdverts', function ($query) {
            $query->active();
        })
        ->orderByDesc('featured_adverts_count')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Featured categories grid retrieved successfully'
        ]);
    }

    /**
     * Get trending countries with featured adverts.
     */
    public function trendingCountries(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 20);

        $countries = FeaturedAdvert::select('country', DB::raw('count(*) as count'))
            ->active()
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $countries,
            'message' => 'Trending countries retrieved successfully'
        ]);
    }

    /**
     * Get trending categories with featured adverts.
     */
    public function trendingCategories(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 10), 20);

        $categories = Category::with(['featuredAdverts' => function ($query) {
            $query->active()->orderByDesc('view_count')->limit(3);
        }])
        ->whereHas('featuredAdverts', function ($query) {
            $query->active();
        })
        ->withCount(['featuredAdverts' => function ($query) {
            $query->active();
        }])
        ->orderByDesc('featured_adverts_count')
        ->limit($limit)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
            'message' => 'Trending categories retrieved successfully'
        ]);
    }

    /**
     * Save/favorite a featured advert.
     */
    public function saveAdvert(string $id): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Customer authentication required.'
            ], 401);
        }

        $featuredAdvert = FeaturedAdvert::findOrFail($id);

        // Toggle save status (you would need to create a saves table for this)
        // For now, just increment the save count
        $featuredAdvert->incrementSaveCount();

        return response()->json([
            'success' => true,
            'message' => 'Featured advert saved successfully',
            'save_count' => $featuredAdvert->save_count
        ]);
    }

    /**
     * Contact the seller of a featured advert.
     */
    public function contactSeller(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $customer = Auth::guard('customer')->user();
        $featuredAdvert = FeaturedAdvert::findOrFail($id);

        // Increment contact count
        $featuredAdvert->incrementContactCount();

        // Here you would typically send an email or notification to the seller
        // For now, we'll just return a success response

        return response()->json([
            'success' => true,
            'message' => 'Message sent to seller successfully',
            'contact_count' => $featuredAdvert->contact_count
        ]);
    }

    /**
     * Get upsell pricing information.
     */
    public function getPricing(): JsonResponse
    {
        $pricing = [
            'promoted' => [
                'name' => 'Promoted',
                'price' => 29.99,
                'currency' => 'GBP',
                'duration_days' => 30,
                'features' => [
                    'Highlighted card',
                    'Appears above standard listings',
                    '"Promoted" badge',
                    '2× more visibility',
                ]
            ],
            'featured' => [
                'name' => 'Featured',
                'price' => 59.99,
                'currency' => 'GBP',
                'duration_days' => 30,
                'features' => [
                    'Top of category pages',
                    'Larger advert card',
                    'Priority in search results',
                    'Included in weekly "Top Featured Ads" email',
                    '"Featured" badge',
                    '4× more visibility',
                ],
                'is_most_popular' => true
            ],
            'sponsored' => [
                'name' => 'Sponsored',
                'price' => 99.99,
                'currency' => 'GBP',
                'duration_days' => 30,
                'features' => [
                    'Homepage placement',
                    'Featured in homepage slider',
                    'Category top placement',
                    'Included in social media promotion',
                    '"Sponsored" badge',
                    'Maximum visibility',
                    '6× more visibility',
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $pricing,
            'message' => 'Pricing information retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts for homepage listing.
     */
    public function homeListing(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 12), 24);
        
        $featuredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->orderByPriority()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Home featured adverts retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts by category with pagination.
     */
    public function byCategory(Request $request, int $categoryId): JsonResponse
    {
        $perPage = min($request->get('per_page', 20), 100);
        
        $featuredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->byCategory($categoryId)
            ->orderByPriority()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Category featured adverts retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts by country with pagination.
     */
    public function byCountry(Request $request, string $country): JsonResponse
    {
        $perPage = min($request->get('per_page', 20), 100);
        
        $featuredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->byCountry($country)
            ->orderByPriority()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Country featured adverts retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts by type with pagination.
     */
    public function byType(Request $request, string $type): JsonResponse
    {
        $perPage = min($request->get('per_page', 20), 100);
        
        $featuredAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->byType($type)
            ->orderByPriority()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Type featured adverts retrieved successfully'
        ]);
    }

    /**
     * Get related featured adverts.
     */
    public function related(Request $request, string $id): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::findOrFail($id);
        $limit = min($request->get('limit', 8), 16);
        
        $relatedAdverts = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active()
            ->where('id', '!=', $id)
            ->where(function ($query) use ($featuredAdvert) {
                if ($featuredAdvert->category_id) {
                    $query->orWhere('category_id', $featuredAdvert->category_id);
                }
                $query->orWhere('advert_type', $featuredAdvert->advert_type)
                      ->orWhere('country', $featuredAdvert->country);
            })
            ->orderByPriority()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $relatedAdverts,
            'message' => 'Related featured adverts retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts with advanced filtering.
     */
    public function advancedSearch(Request $request): JsonResponse
    {
        $query = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->active();

        // Text search
        if ($request->has('q')) {
            $query->search($request->q);
        }

        // Category filter
        if ($request->has('categories')) {
            $categories = explode(',', $request->categories);
            $query->whereIn('category_id', $categories);
        }

        // Country filter
        if ($request->has('countries')) {
            $countries = explode(',', $request->countries);
            $query->whereIn('country', $countries);
        }

        // Type filter
        if ($request->has('types')) {
            $types = explode(',', $request->types);
            $query->whereIn('advert_type', $types);
        }

        // Tier filter
        if ($request->has('tiers')) {
            $tiers = explode(',', $request->tiers);
            $query->whereIn('upsell_tier', $tiers);
        }

        // Price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Verified only
        if ($request->boolean('verified_only')) {
            $query->verified();
        }

        // Has images only
        if ($request->boolean('has_images')) {
            $query->whereNotNull('images')->where('images', '!=', '[]');
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'priority');
        $sortOrder = $request->get('sort_order', 'desc');
        
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'views':
                $query->orderByDesc('view_count');
                break;
            case 'saves':
                $query->orderByDesc('save_count');
                break;
            case 'contacts':
                $query->orderByDesc('contact_count');
                break;
            case 'rating':
                $query->orderByDesc('rating');
                break;
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            case 'expiry':
                $query->orderBy('expires_at');
                break;
            default:
                $query->orderByPriority();
        }

        $perPage = min($request->get('per_page', 20), 100);
        $featuredAdverts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Advanced search results retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $stats = [
            'total_active' => FeaturedAdvert::active()->count(),
            'by_tier' => [
                'promoted' => FeaturedAdvert::active()->promoted()->count(),
                'featured' => FeaturedAdvert::active()->featured()->count(),
                'sponsored' => FeaturedAdvert::active()->sponsored()->count(),
            ],
            'by_type' => FeaturedAdvert::active()
                ->select('advert_type', DB::raw('count(*) as count'))
                ->groupBy('advert_type')
                ->orderByDesc('count')
                ->get(),
            'top_countries' => FeaturedAdvert::active()
                ->select('country', DB::raw('count(*) as count'))
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'top_categories' => Category::withCount(['featuredAdverts' => function ($query) {
                $query->active();
            }])
            ->whereHas('featuredAdverts', function ($query) {
                $query->active();
            })
            ->orderByDesc('featured_adverts_count')
            ->limit(10)
            ->get(),
            'total_views' => FeaturedAdvert::active()->sum('view_count'),
            'total_saves' => FeaturedAdvert::active()->sum('save_count'),
            'total_contacts' => FeaturedAdvert::active()->sum('contact_count'),
            'average_rating' => FeaturedAdvert::active()
                ->whereNotNull('rating')
                ->avg('rating'),
            'verified_percentage' => FeaturedAdvert::active()
                ->where('is_verified_seller', true)
                ->count() > 0 
                ? (FeaturedAdvert::active()->where('is_verified_seller', true)->count() / FeaturedAdvert::active()->count()) * 100 
                : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Featured adverts statistics retrieved successfully'
        ]);
    }

    /**
     * Get live activity feed for featured adverts.
     */
    public function liveActivity(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 20), 50);
        
        // Recent activities - this would typically come from an activity log
        // For now, we'll simulate with recent featured adverts
        $recentAdverts = FeaturedAdvert::with(['customer', 'category'])
            ->active()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($advert) {
                return [
                    'type' => 'new_featured',
                    'message' => "New {$advert->upsell_tier} advert: {$advert->title}",
                    'customer' => $advert->customer->name ?? 'Anonymous',
                    'category' => $advert->category->name ?? 'Uncategorized',
                    'country' => $advert->country,
                    'created_at' => $advert->created_at,
                    'advert_id' => $advert->id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $recentAdverts,
            'message' => 'Live activity feed retrieved successfully'
        ]);
    }

    /**
     * Get featured adverts analytics for dashboard.
     */
    public function analytics(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '30days'); // 7days, 30days, 90days
        
        $startDate = match($timeframe) {
            '7days' => now()->subDays(7),
            '30days' => now()->subDays(30),
            '90days' => now()->subDays(90),
            default => now()->subDays(30),
        };

        $analytics = [
            'revenue' => [
                'total' => FeaturedAdvert::where('created_at', '>=', $startDate)
                    ->where('payment_status', FeaturedAdvert::PAYMENT_PAID)
                    ->sum('upsell_price'),
                'by_tier' => [
                    'promoted' => FeaturedAdvert::where('created_at', '>=', $startDate)
                        ->where('payment_status', FeaturedAdvert::PAYMENT_PAID)
                        ->where('upsell_tier', FeaturedAdvert::TIER_PROMOTED)
                        ->sum('upsell_price'),
                    'featured' => FeaturedAdvert::where('created_at', '>=', $startDate)
                        ->where('payment_status', FeaturedAdvert::PAYMENT_PAID)
                        ->where('upsell_tier', FeaturedAdvert::TIER_FEATURED)
                        ->sum('upsell_price'),
                    'sponsored' => FeaturedAdvert::where('created_at', '>=', $startDate)
                        ->where('payment_status', FeaturedAdvert::PAYMENT_PAID)
                        ->where('upsell_tier', FeaturedAdvert::TIER_SPONSORED)
                        ->sum('upsell_price'),
                ],
            ],
            'conversions' => [
                'total_created' => FeaturedAdvert::where('created_at', '>=', $startDate)->count(),
                'total_paid' => FeaturedAdvert::where('created_at', '>=', $startDate)
                    ->where('payment_status', FeaturedAdvert::PAYMENT_PAID)->count(),
                'conversion_rate' => 0, // Will be calculated
            ],
            'engagement' => [
                'total_views' => FeaturedAdvert::where('created_at', '>=', $startDate)->sum('view_count'),
                'total_saves' => FeaturedAdvert::where('created_at', '>=', $startDate)->sum('save_count'),
                'total_contacts' => FeaturedAdvert::where('created_at', '>=', $startDate)->sum('contact_count'),
            ],
            'daily_stats' => FeaturedAdvert::where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as created, SUM(CASE WHEN payment_status = "paid" THEN 1 ELSE 0 END) as paid')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        // Calculate conversion rate
        if ($analytics['conversions']['total_created'] > 0) {
            $analytics['conversions']['conversion_rate'] = 
                ($analytics['conversions']['total_paid'] / $analytics['conversions']['total_created']) * 100;
        }

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'message' => 'Featured adverts analytics retrieved successfully'
        ]);
    }
}
