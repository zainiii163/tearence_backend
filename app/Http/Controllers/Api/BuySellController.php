<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use App\Models\BuySellSavedAdvert;
use App\Models\BuySellAdvertView;
use App\Models\BuySellAdvertReport;
use App\Models\BuySellPromotionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BuySellController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BuySellAdvert::with(['category', 'subcategory', 'user'])
            ->active();

        // Filters
        if ($request->category) {
            $query->byCategory($request->category);
        }

        if ($request->subcategory) {
            $query->where('subcategory_id', $request->subcategory);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->condition) {
            $query->where('condition', $request->condition);
        }

        if ($request->price_min || $request->price_max) {
            $query->byPriceRange($request->price_min, $request->price_max);
        }

        if ($request->country) {
            $query->byLocation($request->country, $request->city);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Status filters
        if ($request->featured) {
            $query->featured();
        }

        if ($request->promoted) {
            $query->promoted();
        }

        if ($request->sponsored) {
            $query->sponsored();
        }

        // Sorting
        $sortBy = $request->get('sortBy', 'created_at');
        $sortOrder = $request->get('sortOrder', 'desc');
        
        if (in_array($sortBy, ['created_at', 'price', 'views_count', 'title'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('limit', 20), 50);
        $page = $request->get('page', 1);

        $adverts = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $adverts->items(),
                'pagination' => [
                    'currentPage' => $adverts->currentPage(),
                    'totalPages' => $adverts->lastPage(),
                    'totalItems' => $adverts->total(),
                    'itemsPerPage' => $adverts->perPage(),
                    'hasNextPage' => $adverts->hasMorePages(),
                    'hasPrevPage' => $adverts->currentPage() > 1,
                ]
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $advert = BuySellAdvert::with(['category', 'subcategory', 'user'])
            ->active()
            ->findOrFail($id);

        // Track view
        $advert->incrementView(
            Auth::id(),
            request()->ip(),
            request()->userAgent(),
            request()->header('referer')
        );

        // Get related adverts
        $relatedAdverts = BuySellAdvert::with(['category'])
            ->active()
            ->where('category_id', $advert->category_id)
            ->where('id', '!=', $advert->id)
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'advert' => $advert,
                'related_adverts' => $relatedAdverts,
                'seller_profile' => [
                    'name' => $advert->seller_name,
                    'email' => $advert->seller_email,
                    'phone' => $advert->show_phone ? $advert->seller_phone : null,
                    'verified' => $advert->verified_seller,
                    'website' => $advert->seller_website,
                    'member_since' => $advert->created_at->format('Y-m-d'),
                ]
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10|max:5000',
            'category_id' => 'required|uuid|exists:buysell_categories,id',
            'subcategory_id' => 'nullable|uuid|exists:buysell_categories,id',
            'condition' => 'required|in:new,like_new,excellent,good,fair,poor',
            'price' => 'required|numeric|min:0|max:999999.99',
            'negotiable' => 'boolean',
            'currency' => 'string|size:3',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'dimensions' => 'nullable|string|max:200',
            'weight' => 'nullable|numeric|min:0',
            'material' => 'nullable|string|max:100',
            'usage_duration' => 'nullable|string|max:100',
            'reason_for_selling' => 'nullable|string|max:1000',
            'seller_name' => 'required|string|max:255',
            'seller_email' => 'required|email|max:255',
            'seller_phone' => 'nullable|string|max:50',
            'seller_website' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:500',
            'verified_seller' => 'boolean',
            'show_phone' => 'boolean',
            'preferred_contact' => 'required|in:email,phone,website',
            'images' => 'array|max:15',
            'images.*' => 'url|max:500',
            'video_url' => 'nullable|url|max:500',
            'promotion_plan' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();

        $advert = BuySellAdvert::create($data);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $advert->id,
                'message' => 'Advert created successfully',
                'advert' => $advert->load(['category', 'subcategory'])
            ]
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $advert = BuySellAdvert::findOrFail($id);

        if ($advert->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|min:3|max:255',
            'description' => 'sometimes|string|min:10|max:5000',
            'category_id' => 'sometimes|uuid|exists:buysell_categories,id',
            'subcategory_id' => 'nullable|uuid|exists:buysell_categories,id',
            'condition' => 'sometimes|in:new,like_new,excellent,good,fair,poor',
            'price' => 'sometimes|numeric|min:0|max:999999.99',
            'negotiable' => 'boolean',
            'currency' => 'string|size:3',
            'country' => 'sometimes|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'dimensions' => 'nullable|string|max:200',
            'weight' => 'nullable|numeric|min:0',
            'material' => 'nullable|string|max:100',
            'usage_duration' => 'nullable|string|max:100',
            'reason_for_selling' => 'nullable|string|max:1000',
            'seller_name' => 'sometimes|string|max:255',
            'seller_email' => 'sometimes|email|max:255',
            'seller_phone' => 'nullable|string|max:50',
            'seller_website' => 'nullable|url|max:255',
            'logo_url' => 'nullable|url|max:500',
            'verified_seller' => 'boolean',
            'show_phone' => 'boolean',
            'preferred_contact' => 'sometimes|in:email,phone,website',
            'images' => 'array|max:15',
            'images.*' => 'url|max:500',
            'video_url' => 'nullable|url|max:500',
            'status' => 'sometimes|in:active,inactive,expired',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $advert->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Advert updated successfully',
                'advert' => $advert->load(['category', 'subcategory'])
            ]
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $advert = BuySellAdvert::findOrFail($id);

        if ($advert->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $advert->deleted_by = Auth::id();
        $advert->save();
        $advert->delete();

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Advert deleted successfully'
            ]
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = BuySellCategory::with(['children' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
        }])
        ->where('parent_id', null)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    public function subcategories($categoryId): JsonResponse
    {
        $subcategories = BuySellCategory::where('parent_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }

    public function saveAdvert(Request $request, $id): JsonResponse
    {
        $advert = BuySellAdvert::active()->findOrFail($id);

        $isSaved = $advert->toggleSave(Auth::id());

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $isSaved ? 'Advert saved successfully' : 'Advert removed from saved',
                'is_saved' => $isSaved,
                'saves_count' => $advert->fresh()->saves_count
            ]
        ]);
    }

    public function savedAdverts(): JsonResponse
    {
        $savedAdverts = BuySellSavedAdvert::with(['advert.category', 'advert.subcategory'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $savedAdverts->items(),
                'pagination' => [
                    'currentPage' => $savedAdverts->currentPage(),
                    'totalPages' => $savedAdverts->lastPage(),
                    'totalItems' => $savedAdverts->total(),
                    'itemsPerPage' => $savedAdverts->perPage(),
                ]
            ]
        ]);
    }

    public function myAdverts(): JsonResponse
    {
        $adverts = BuySellAdvert::with(['category', 'subcategory'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $adverts->items(),
                'pagination' => [
                    'currentPage' => $adverts->currentPage(),
                    'totalPages' => $adverts->lastPage(),
                    'totalItems' => $adverts->total(),
                    'itemsPerPage' => $adverts->perPage(),
                ]
            ]
        ]);
    }

    public function contactSeller(Request $request, $id): JsonResponse
    {
        $advert = BuySellAdvert::active()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:10|max:1000',
            'contact_method' => 'required|in:email,phone',
            'buyer_name' => 'required|string|max:255',
            'buyer_email' => 'required|email|max:255',
            'buyer_phone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $advert->increment('contacts_count');

        // TODO: Send email notification to seller
        // TODO: Store contact message in database

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Message sent successfully'
            ]
        ]);
    }

    public function reportAdvert(Request $request, $id): JsonResponse
    {
        $advert = BuySellAdvert::active()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $report = BuySellAdvertReport::create([
            'advert_id' => $advert->id,
            'reporter_id' => Auth::id(),
            'reason' => $request->reason,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Advert reported successfully',
                'report_id' => $report->id
            ]
        ]);
    }

    public function promotionPlans(): JsonResponse
    {
        $plans = BuySellPromotionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    public function promoteAdvert(Request $request, $id): JsonResponse
    {
        $advert = BuySellAdvert::findOrFail($id);

        if ($advert->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|uuid|exists:buysell_promotion_plans,id',
            'payment_method' => 'required|string',
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $plan = BuySellPromotionPlan::findOrFail($request->plan_id);

        // TODO: Process payment
        // TODO: Update advert promotion status

        return response()->json([
            'success' => true,
            'data' => [
                'message' => 'Promotion purchased successfully',
                'promotion_end_date' => now()->addDays($plan->duration_days)
            ]
        ]);
    }

    public function searchSuggestions(Request $request): JsonResponse
    {
        $query = $request->get('q');
        
        if (strlen($query) < 3) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $categories = BuySellCategory::where('name', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->limit(5)
            ->get(['id', 'name', 'slug']);

        $suggestions = [];
        
        foreach ($categories as $category) {
            $suggestions[] = [
                'type' => 'category',
                'value' => $category->slug,
                'label' => $category->name,
            ];
        }

        // Add popular search terms
        $popularTerms = BuySellAdvert::selectRaw('LOWER(title) as title, COUNT(*) as count')
            ->where('title', 'LIKE', "%{$query}%")
            ->where('status', 'active')
            ->groupBy('title')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        foreach ($popularTerms as $term) {
            $suggestions[] = [
                'type' => 'suggestion',
                'value' => $term->title,
                'label' => $term->title,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    public function trending(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 5), 20);

        $trending = BuySellAdvert::with(['category'])
            ->active()
            ->orderBy('views_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trending
        ]);
    }

    public function recentlyViewed(): JsonResponse
    {
        $recentlyViewed = BuySellAdvertView::with(['advert.category'])
            ->where('user_id', Auth::id())
            ->latest('viewed_at')
            ->limit(10)
            ->get()
            ->pluck('advert');

        return response()->json([
            'success' => true,
            'data' => $recentlyViewed
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total_items' => BuySellAdvert::active()->count(),
            'active_users' => DB::table('users')->whereNotNull('email_verified_at')->count(),
            'countries' => BuySellAdvert::active()->distinct('country')->count('country'),
            'success_rate' => 98.5, // TODO: Calculate actual success rate
            'categories' => BuySellCategory::withCount(['activeAdverts'])
                ->where('parent_id', null)
                ->where('is_active', true)
                ->orderBy('active_adverts_count', 'desc')
                ->limit(10)
                ->get(['name', 'active_adverts_count as count'])
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
