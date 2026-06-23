<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SponsoredAdvert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SponsoredAdvertController extends Controller
{
    /**
     * Get sponsored adverts with filtering and search
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'advert_type' => 'nullable|string|in:product,service,property,job,event,vehicle,business,other',
            'sponsorship_tier' => 'nullable|string|in:basic,plus,premium',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'verified_only' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:created_at,title,price,views_count,saves_count,rating',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = SponsoredAdvert::active()->withActivePromotion();

        // Apply filters
        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        if ($request->country) {
            $query->byCountry($request->country);
        }

        if ($request->city) {
            $query->byCity($request->city);
        }

        if ($request->advert_type) {
            $query->byAdvertType($request->advert_type);
        }

        if ($request->sponsorship_tier) {
            $query->byTier($request->sponsorship_tier);
        }

        if ($request->min_price || $request->max_price) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        if ($request->verified_only) {
            $query->verifiedSeller();
        }

        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        if ($sortBy === 'id') {
            $sortBy = 'sponsored_advert_id';
        }
        $query->orderBy($sortBy, $sortOrder);

        // Prioritize premium tier
        if (!$request->sponsorship_tier) {
            $query->orderByRaw("FIELD(sponsorship_tier, 'premium', 'plus', 'basic') ASC");
        }

        $perPage = $request->per_page ?? 12;
        $adverts = $query->orderBy('sponsored_advert_id', 'desc')->paginate($perPage, ['*'], 'page', $request->page ?? 1);

        // Transform data to match frontend expectations
        $baseUrl = env('APP_ENV') === 'production' ? 'https://api.worldwideadverts.info' : env('APP_URL', 'http://127.0.0.1:8000');
        
        $transformedAdverts = collect($adverts->items())->map(function ($advert) use ($baseUrl) {
            // Replace localhost URLs with production URLs
            $mainImage = $advert->main_image;
            if ($mainImage && str_contains($mainImage, '127.0.0.1:8000')) {
                $mainImage = str_replace('http://127.0.0.1:8000', $baseUrl, $mainImage);
            }
            
            return [
                'id' => $advert->sponsored_advert_id,
                'sponsored_advert_id' => $advert->sponsored_advert_id,
                'title' => $advert->title,
                'tagline' => $advert->tagline,
                'description' => $advert->description,
                'price' => $advert->price ? number_format($advert->price, 2) : '0.00',
                'currency' => $advert->currency,
                'country' => $advert->country,
                'city' => $advert->city,
                'main_image' => $mainImage,
                'image' => $mainImage,
                'views' => $advert->views_count,
                'views_count' => $advert->views_count,
                'category' => $advert->category_id, // Frontend expects category name, but we return ID for now
                'category_id' => $advert->category_id,
                'condition' => $advert->condition ?? 'Available',
                'sponsorship_tier' => $advert->sponsorship_tier,
                'seller' => [
                    'name' => $advert->seller_name,
                    'verified' => $advert->verified_seller ?? false,
                    'rating' => '4.5', // Default rating since not stored
                    'adsCount' => 0, // Default since not tracked
                    'reviews' => 0
                ],
                'badges' => $this->generateBadges($advert->sponsorship_tier, $advert->verified_seller),
                'slug' => $advert->slug,
                'created_at' => $advert->created_at,
                'updated_at' => $advert->updated_at
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $transformedAdverts,
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total()
            ]
        ]);
    }

    /**
     * Get single sponsored advert details by slug
     */
    public function show($slug)
    {
        $advert = SponsoredAdvert::active()->where('slug', $slug)->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsored advert not found'
            ], 404);
        }

        // Increment view count
        $advert->incrementViews();

        // Replace localhost URLs with production URLs
        $baseUrl = env('APP_ENV') === 'production' ? 'https://api.worldwideadverts.info' : env('APP_URL', 'http://127.0.0.1:8000');
        $advertData = $advert->toArray();
        
        if (isset($advertData['main_image']) && str_contains($advertData['main_image'], '127.0.0.1:8000')) {
            $advertData['main_image'] = str_replace('http://127.0.0.1:8000', $baseUrl, $advertData['main_image']);
        }
        
        if (isset($advertData['logo']) && str_contains($advertData['logo'], '127.0.0.1:8000')) {
            $advertData['logo'] = str_replace('http://127.0.0.1:8000', $baseUrl, $advertData['logo']);
        }
        
        if (isset($advertData['additional_images']) && is_array($advertData['additional_images'])) {
            $advertData['additional_images'] = array_map(function($img) use ($baseUrl) {
                return str_contains($img, '127.0.0.1:8000') ? str_replace('http://127.0.0.1:8000', $baseUrl, $img) : $img;
            }, $advertData['additional_images']);
        }

        return response()->json([
            'success' => true,
            'data' => $advertData
        ]);
    }

    /**
     * Create new sponsored advert
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advert_type' => 'required|string|in:product,service,property,job,event,vehicle,business,other',
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'required|string',
            'category_id' => 'nullable|integer',
            'condition' => 'nullable|string|in:new,used,not_applicable',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_precision' => 'nullable|string|in:exact,approximate',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_link' => 'nullable|url',
            'seller_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'verified_seller' => 'nullable|boolean',
            'sponsorship_tier' => 'required|string|in:basic,plus,premium',
            'sponsorship_price' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!auth('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $data = $request->except([
            'main_image', 'additional_images', 'logo', 'preferred_contact'
        ]);

        $data['created_by'] = auth('api')->id();
        $data['slug'] = SponsoredAdvert::createUniqueSlug($request->title);
        $data['currency'] = $data['currency'] ?? 'GBP';
        $data['verified_seller'] = $data['verified_seller'] ?? false;
        $data['is_active'] = true;

        // Map field names to match existing table
        if (isset($data['sponsored_tier'])) {
            $data['sponsorship_tier'] = $data['sponsored_tier'];
            unset($data['sponsored_tier']);
        }
        if (isset($data['promotion_price'])) {
            $data['sponsorship_price'] = $data['promotion_price'];
            unset($data['promotion_price']);
        }
        if (isset($data['contact_name'])) {
            $data['seller_name'] = $data['contact_name'];
            unset($data['contact_name']);
        }

        // Set promotion dates based on tier
        $tierDurations = [
            'basic' => 30,
            'plus' => 60,
            'premium' => 90
        ];
        $duration = $tierDurations[$data['sponsorship_tier']] ?? 30;
        $data['sponsorship_start_date'] = now();
        $data['sponsorship_end_date'] = now()->addDays($duration);

        // Debug: Log what we're receiving
        \Log::info('Image upload debug', [
            'has_main_image_file' => $request->hasFile('main_image'),
            'main_image_input' => $request->input('main_image'),
            'all_files' => $request->allFiles(),
            'content_type' => $request->header('Content-Type')
        ]);

        // Handle main image upload
        if ($request->hasFile('main_image')) {
            $image = $request->file('main_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/sponsored/main/' . auth('api')->id());
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $imageName);
            $data['main_image'] = asset('images/sponsored/main/' . auth('api')->id() . '/' . $imageName);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/sponsored/logos/' . auth('api')->id());
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $imageName);
            $data['logo'] = asset('images/sponsored/logos/' . auth('api')->id() . '/' . $imageName);
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/sponsored/images/' . auth('api')->id());
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $image->move($destinationPath, $imageName);
                $additionalImages[] = asset('images/sponsored/images/' . auth('api')->id() . '/' . $imageName);
            }
            $data['additional_images'] = $additionalImages;
        }

        $advert = SponsoredAdvert::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert created successfully',
            'data' => $advert
        ], 201);
    }

    /**
     * Update sponsored advert
     */
    public function update(Request $request, $id)
    {
        $advert = SponsoredAdvert::find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsored advert not found'
            ], 404);
        }

        if (!auth('api')->check() || $advert->created_by != auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'advert_type' => 'nullable|string|in:product,service,property,job,event,vehicle,business,other',
            'title' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'condition' => 'nullable|string|in:new,used,not_applicable',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_precision' => 'nullable|string|in:exact,approximate',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'video_link' => 'nullable|url',
            'seller_name' => 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'verified_seller' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sponsorship_tier' => 'nullable|string|in:basic,plus,premium',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except([
            'main_image', 'additional_images', 'logo'
        ]);

        // Update slug if title changed
        if ($request->has('title') && $request->title !== $advert->title) {
            $data['slug'] = SponsoredAdvert::createUniqueSlug($request->title);
        }

        // Handle main image upload
        if ($request->hasFile('main_image')) {
            $image = $request->file('main_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/sponsored/main/' . auth('api')->id());
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $imageName);
            $data['main_image'] = asset('images/sponsored/main/' . auth('api')->id() . '/' . $imageName);
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/sponsored/logos/' . auth('api')->id());
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $image->move($destinationPath, $imageName);
            $data['logo'] = asset('images/sponsored/logos/' . auth('api')->id() . '/' . $imageName);
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('images/sponsored/images/' . auth('api')->id());
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $image->move($destinationPath, $imageName);
                $additionalImages[] = asset('images/sponsored/images/' . auth('api')->id() . '/' . $imageName);
            }
            $data['additional_images'] = $additionalImages;
        }

        $advert->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert updated successfully',
            'data' => $advert->fresh()
        ]);
    }

    /**
     * Delete sponsored advert
     */
    public function destroy($id)
    {
        $advert = SponsoredAdvert::find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsored advert not found'
            ], 404);
        }

        if (!auth('api')->check() || $advert->created_by != auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $advert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert deleted successfully'
        ]);
    }

    /**
     * Get user's sponsored adverts
     */
    public function myAdverts()
    {
        if (!auth('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $adverts = SponsoredAdvert::with('category')
            ->where('created_by', auth('api')->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => collect($adverts->items())->map(fn ($advert) => $this->transformMyAdvert($advert))->values(),
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total()
            ]
        ]);
    }

    /**
     * Update sponsored advert visibility (active / paused)
     */
    public function updateStatus(Request $request, $id)
    {
        $advert = SponsoredAdvert::find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsored advert not found'
            ], 404);
        }

        if (!auth('api')->check() || $advert->created_by != auth('api')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,paused,pending',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $isActive = $request->has('is_active')
            ? (bool) $request->boolean('is_active')
            : $request->status === 'active';

        $advert->update(['is_active' => $isActive]);

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert status updated successfully',
            'data' => $this->transformMyAdvert($advert->fresh('category')),
        ]);
    }

    private function transformMyAdvert(SponsoredAdvert $advert): array
    {
        return [
            'id' => $advert->sponsored_advert_id,
            'sponsored_advert_id' => $advert->sponsored_advert_id,
            'title' => $advert->title,
            'slug' => $advert->slug,
            'description' => $advert->description,
            'price' => $advert->price,
            'currency' => $advert->currency,
            'category_id' => $advert->category_id,
            'category' => $advert->category ? [
                'id' => $advert->category->id,
                'name' => $advert->category->name,
            ] : null,
            'main_image' => $advert->main_image,
            'additional_images' => $advert->additional_images,
            'logo' => $advert->logo,
            'sponsorship_tier' => $advert->sponsorship_tier,
            'payment_status' => $advert->payment_status,
            'is_active' => (bool) $advert->is_active,
            'status' => $advert->status,
            'views_count' => $advert->views_count,
            'saves_count' => $advert->saves_count,
            'created_at' => $advert->created_at,
            'updated_at' => $advert->updated_at,
        ];
    }

    /**
     * Track view for sponsored advert
     */
    public function trackView($id)
    {
        $advert = SponsoredAdvert::find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsored advert not found'
            ], 404);
        }

        $advert->incrementViews();

        return response()->json([
            'success' => true,
            'message' => 'View tracked successfully'
        ]);
    }

    /**
     * Save/Unsave sponsored advert
     */
    public function saveAdvert($id)
    {
        if (!auth('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $advert = SponsoredAdvert::find($id);

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsored advert not found'
            ], 404);
        }

        $isSaved = $advert->toggleSave(auth('api')->id());

        return response()->json([
            'success' => true,
            'message' => $isSaved ? 'Advert saved successfully' : 'Advert unsaved successfully',
            'is_saved' => $isSaved
        ]);
    }

    /**
     * Get featured sponsored adverts
     */
    public function featured()
    {
        $adverts = SponsoredAdvert::active()
            ->withActivePromotion()
            ->premium()
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $adverts
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        $stats = [
            'sponsored_ads' => SponsoredAdvert::active()->withActivePromotion()->count(),
            'countries' => SponsoredAdvert::active()->distinct('country')->count(),
            'total_views' => SponsoredAdvert::active()->sum('views_count'),
            'satisfaction' => '98%', // Default satisfaction rate
            'total_active' => SponsoredAdvert::active()->withActivePromotion()->count(),
            'total_saves' => SponsoredAdvert::active()->sum('saves_count'),
            'premium_count' => SponsoredAdvert::active()->premium()->count(),
            'plus_count' => SponsoredAdvert::active()->plus()->count(),
            'basic_count' => SponsoredAdvert::active()->basic()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Generate badges based on sponsorship tier and verification status
     */
    private function generateBadges($tier, $verified)
    {
        $badges = [];

        // Add tier badge
        switch ($tier) {
            case 'premium':
                $badges[] = 'Sponsored Premium';
                break;
            case 'plus':
                $badges[] = 'Sponsored Plus';
                break;
            case 'basic':
                $badges[] = 'Sponsored Basic';
                break;
        }

        // Add verified badge
        if ($verified) {
            $badges[] = 'Verified';
        }

        return $badges;
    }

    /**
     * Get categories with counts
     */
    public function categories()
    {
        $categories = \App\Models\SponsoredCategory::all();

        // Get category counts from sponsored adverts
        $categoryCounts = SponsoredAdvert::active()
            ->withActivePromotion()
            ->selectRaw('category_id, COUNT(*) as count')
            ->groupBy('category_id')
            ->orderBy('count', 'desc')
            ->get()
            ->keyBy('category_id');

        // Map categories with their counts
        $result = [];
        foreach ($categories as $category) {
            $result[] = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon' => $category->icon,
                'color' => $category->color,
                'sponsored_adverts_count' => $categoryCounts->has($category->id) ? $categoryCounts->get($category->id)->count : 0
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Get trending categories
     */
    public function trendingCategories()
    {
        $categories = SponsoredAdvert::active()
            ->withActivePromotion()
            ->selectRaw('category_id, COUNT(*) as count, SUM(views_count) as total_views')
            ->groupBy('category_id')
            ->orderBy('total_views', 'desc')
            ->limit(10)
            ->get();

        $allCategories = \App\Models\SponsoredCategory::all()->keyBy('id');

        $result = [];
        foreach ($categories as $category) {
            $categoryData = $allCategories->get($category->category_id);
            if ($categoryData) {
                $result[] = [
                    'id' => $category->category_id,
                    'name' => $categoryData->name,
                    'slug' => $categoryData->slug,
                    'description' => $categoryData->description,
                    'icon' => $categoryData->icon,
                    'color' => $categoryData->color,
                    'sponsored_adverts_count' => $category->count,
                    'total_views' => $category->total_views
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Get pricing plans
     */
    public function pricingPlans()
    {
        $plans = [
            [
                'tier' => 'basic',
                'name' => 'Sponsored Basic',
                'price' => 29.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'benefits' => [
                    'Listed on Sponsored Adverts Page',
                    'Highlighted card',
                    'Sponsored badge',
                    '3x more visibility than standard ads'
                ]
            ],
            [
                'tier' => 'plus',
                'name' => 'Sponsored Plus',
                'price' => 79.99,
                'currency' => 'USD',
                'duration_days' => 60,
                'is_popular' => true,
                'benefits' => [
                    'All Basic features',
                    'Top of category placement',
                    'Larger advert card',
                    'Priority in search results',
                    'Included in weekly "Sponsored Highlights" email'
                ]
            ],
            [
                'tier' => 'premium',
                'name' => 'Sponsored Premium',
                'price' => 199.99,
                'currency' => 'USD',
                'duration_days' => 90,
                'is_vip' => true,
                'benefits' => [
                    'Homepage placement',
                    'Featured in homepage slider',
                    'Category top placement',
                    'Included in social media promotion',
                    'Premium Sponsored badge',
                    'Maximum visibility across the platform'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Upload image
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!auth('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $image = $request->file('image');
        $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs('sponsored/temp/' . auth('api')->id(), $imageName, 'public');

        return response()->json([
            'success' => true,
            'data' => [
                'path' => $imagePath,
                'url' => asset('storage/' . $imagePath)
            ]
        ]);
    }

    /**
     * Process payment for sponsored advert
     */
    public function processPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!auth('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $advert = SponsoredAdvert::where('sponsored_advert_id', $id)
            ->where('created_by', auth('api')->id())
            ->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Sponsored advert not found'
            ], 404);
        }

        // Update payment status
        $advert->update([
            'payment_status' => 'paid',
            'payment_transaction_id' => $request->transaction_id,
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => $advert->fresh()
        ]);
    }
}
