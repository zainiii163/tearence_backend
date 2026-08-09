<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use App\Models\BuySellSavedAdvert;
use App\Models\BuySellAdvertView;
use App\Models\BuySellAdvertReport;
use App\Models\BuySellPromotionPlan;
use App\Models\BuySellPurchase;
use App\Helpers\PlatformFeeHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        // Log first advert's images for debugging
        if ($adverts->count() > 0) {
            \Log::info('First advert images:', [
                'id' => $adverts->first()->id,
                'images' => $adverts->first()->images,
                'title' => $adverts->first()->title
            ]);
        }

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

        // Log advert data for debugging
        \Log::info('Showing advert:', [
            'id' => $advert->id,
            'images' => $advert->images,
            'brand' => $advert->brand,
            'model' => $advert->model,
            'color' => $advert->color,
            'dimensions' => $advert->dimensions,
        ]);

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
        // Handle category_id - accept UUID or slug for backward compatibility
        $categoryId = $request->category_id;
        if ($categoryId) {
            // Check if it's not already a valid UUID
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $categoryId)) {
                // Try to find category by slug
                $category = BuySellCategory::where('slug', $categoryId)->first();
                if ($category) {
                    $request->merge(['category_id' => $category->id]);
                }
            }
        }

        // Handle subcategory_id similarly
        $subcategoryId = $request->subcategory_id;
        if ($subcategoryId) {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $subcategoryId)) {
                $subcategory = BuySellCategory::where('slug', $subcategoryId)->first();
                if ($subcategory) {
                    $request->merge(['subcategory_id' => $subcategory->id]);
                }
            }
        }

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

        // Handle images array - ensure it's properly formatted
        if ($request->has('images')) {
            $images = $request->input('images');
            // If images is an associative array from FormData, convert to indexed array
            if (is_array($images)) {
                $data['images'] = array_values($images);
            }
        }

        // Log all specification fields for debugging
        \Log::info('Creating advert with data:', [
            'images' => $data['images'] ?? null,
            'brand' => $data['brand'] ?? null,
            'model' => $data['model'] ?? null,
            'color' => $data['color'] ?? null,
            'dimensions' => $data['dimensions'] ?? null,
            'weight' => $data['weight'] ?? null,
            'material' => $data['material'] ?? null,
            'usage_duration' => $data['usage_duration'] ?? null,
            'reason_for_selling' => $data['reason_for_selling'] ?? null,
        ]);

        $advert = BuySellAdvert::create($data);

        \Log::info('Advert created with images:', ['advert_id' => $advert->id, 'images' => $advert->images]);

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

        // Handle category_id - accept UUID or slug for backward compatibility
        $categoryId = $request->category_id;
        if ($categoryId) {
            // Check if it's not already a valid UUID
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $categoryId)) {
                // Try to find category by slug
                $category = BuySellCategory::where('slug', $categoryId)->first();
                if ($category) {
                    $request->merge(['category_id' => $category->id]);
                }
            }
        }

        // Handle subcategory_id similarly
        $subcategoryId = $request->subcategory_id;
        if ($subcategoryId) {
            if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $subcategoryId)) {
                $subcategory = BuySellCategory::where('slug', $subcategoryId)->first();
                if ($subcategory) {
                    $request->merge(['subcategory_id' => $subcategory->id]);
                }
            }
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
        try {
            $advert = BuySellAdvert::active()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'message' => 'required|string|min:10|max:1000',
                'contact_method' => 'required|in:email,phone,whatsapp',
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

            // Resolve seller account (JWT users are Customer records; user_id = customer_id)
            $sellerCustomer = null;
            if ($advert->user_id) {
                $sellerCustomer = \App\Models\Customer::find($advert->user_id);
            }
            if (! $sellerCustomer && $advert->seller_email) {
                $sellerCustomer = \App\Models\Customer::where('email', $advert->seller_email)->first();
            }

            $sellerEmail = $advert->seller_email ?: ($sellerCustomer->email ?? null);
            $sellerPhone = $advert->seller_phone ?: ($sellerCustomer->phone_number ?? null);
            $sellerName = $advert->seller_name;
            if (! $sellerName && $sellerCustomer) {
                $sellerName = trim(($sellerCustomer->first_name ?? '').' '.($sellerCustomer->last_name ?? '')) ?: 'Seller';
            }
            $sellerName = $sellerName ?: 'Seller';
            $sellerCustomerId = $sellerCustomer
                ? (int) $sellerCustomer->customer_id
                : ($advert->user_id ? (int) $advert->user_id : null);

            if (! $sellerEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller contact is not available for this listing.',
                ], 422);
            }

            $buyerId = Auth::guard('api')->id() ?: Auth::id();

            $contact = \App\Models\SellerContactMessage::create([
                'hub' => 'buysell',
                'listing_id' => (string) $advert->id,
                'seller_user_id' => $sellerCustomerId,
                'buyer_user_id' => $buyerId,
                'buyer_name' => $request->buyer_name,
                'buyer_email' => $request->buyer_email,
                'buyer_phone' => $request->buyer_phone,
                'contact_method' => $request->contact_method,
                'message' => $request->message,
                'status' => 'new',
            ]);

            if (\Illuminate\Support\Facades\Schema::hasColumn($advert->getTable(), 'contacts_count')) {
                $advert->increment('contacts_count');
            }

            $frontendBase = rtrim(
                env('FRONTEND_URL', env('APP_FRONTEND_URL', 'https://worldwideadverts.info')),
                '/'
            );
            $listingUrl = $frontendBase.'/item/'.$advert->id;

            $emailSent = false;
            try {
                \Illuminate\Support\Facades\Mail::to($sellerEmail, $sellerName)
                    ->send(new \App\Mail\SellerContactEnquiryMail(
                        sellerName: $sellerName,
                        listingTitle: (string) $advert->title,
                        buyerName: (string) $request->buyer_name,
                        buyerEmail: (string) $request->buyer_email,
                        buyerPhone: $request->buyer_phone,
                        contactMethod: (string) $request->contact_method,
                        enquiryMessage: (string) $request->message,
                        listingUrl: $listingUrl,
                    ));
                $emailSent = true;
            } catch (\Throwable $e) {
                \Log::warning('Seller contact email failed', [
                    'advert_id' => $advert->id,
                    'seller_email' => $sellerEmail,
                    'error' => $e->getMessage(),
                ]);
            }

            $notificationCreated = false;
            if ($sellerCustomerId) {
                try {
                    \App\Models\CustomerNotification::notify(
                        $sellerCustomerId,
                        \App\Models\CustomerNotification::TYPE_SELLER_ENQUIRY,
                        "{$request->buyer_name} sent an enquiry about \"{$advert->title}\": "
                            .mb_strimwidth((string) $request->message, 0, 140, '…'),
                        'New buyer enquiry',
                        [
                            'hub' => 'buysell',
                            'listing_id' => (string) $advert->id,
                            'listing_title' => $advert->title,
                            'contact_id' => $contact->id,
                            'buyer_name' => $request->buyer_name,
                            'buyer_email' => $request->buyer_email,
                            'buyer_phone' => $request->buyer_phone,
                            'contact_method' => $request->contact_method,
                            'url' => '/item/'.$advert->id,
                        ]
                    );
                    $notificationCreated = true;
                } catch (\Throwable $e) {
                    \Log::warning('Seller contact dashboard notification failed', [
                        'advert_id' => $advert->id,
                        'seller_customer_id' => $sellerCustomerId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Message sent to the seller',
                    'contact_id' => $contact->id,
                    'email_sent' => $emailSent,
                    'notification_created' => $notificationCreated,
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Listing not found',
            ], 404);
        } catch (\Throwable $e) {
            \Log::error('BuySell contactSeller failed', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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

    /**
     * Increment advert view count
     */
    public function view($id): JsonResponse
    {
        $advert = BuySellAdvert::active()->find($id);
        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        $advert->incrementView(
            Auth::id(),
            request()->ip(),
            request()->userAgent(),
            request()->referrer
        );

        return response()->json([
            'success' => true,
            'message' => 'View count incremented'
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

    public function activities(): JsonResponse
    {
        $activities = collect();
        $counter = 0;

        // Recent adverts posted
        $recentAdverts = BuySellAdvert::with(['category', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentAdverts as $advert) {
            $activities->push([
                'id' => 'posted_' . $advert->id . '_' . $counter++,
                'type' => 'item_posted',
                'user' => $advert->user ? $advert->user->name : 'Anonymous',
                'action' => 'posted a new item',
                'item' => $advert->title,
                'location' => $advert->city,
                'timestamp' => $advert->created_at,
            ]);
        }

        // Recently saved adverts
        $recentSaves = BuySellSavedAdvert::with(['advert', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentSaves as $save) {
            $activities->push([
                'id' => 'saved_' . $save->id . '_' . $counter++,
                'type' => 'item_saved',
                'user' => $save->user ? $save->user->name : 'Anonymous',
                'action' => 'saved an item',
                'item' => $save->advert->title,
                'location' => $save->advert->city,
                'timestamp' => $save->created_at,
            ]);
        }

        // Trending items (high views)
        $trendingItems = BuySellAdvert::with(['category', 'user'])
            ->where('views_count', '>', 10)
            ->orderBy('views_count', 'desc')
            ->limit(3)
            ->get();

        foreach ($trendingItems as $item) {
            $activities->push([
                'id' => 'trending_' . $item->id . '_' . $counter++,
                'type' => 'item_featured',
                'user' => $item->user ? $item->user->name : 'Anonymous',
                'action' => 'item is trending',
                'item' => $item->title,
                'location' => $item->city,
                'timestamp' => $item->updated_at,
            ]);
        }

        // Sort by timestamp and limit
        $activities = $activities->sortByDesc('timestamp')->take(10)->values();

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Buyer: list my Buy & Sell purchases.
     */
    public function myPurchases(Request $request): JsonResponse
    {
        if (! Schema::hasTable('buy_sell_purchases')) {
            return response()->json(['success' => true, 'data' => ['items' => []]]);
        }

        $buyerId = Auth::guard('api')->id() ?: Auth::id();
        $query = BuySellPurchase::with(['advert'])
            ->where('buyer_id', $buyerId)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $items = $query->paginate(min((int) $request->get('per_page', 20), 50));

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Seller: list purchases of my Buy & Sell listings.
     */
    public function mySales(Request $request): JsonResponse
    {
        if (! Schema::hasTable('buy_sell_purchases')) {
            return response()->json(['success' => true, 'data' => ['items' => []]]);
        }

        $sellerId = Auth::guard('api')->id() ?: Auth::id();
        $query = BuySellPurchase::with(['advert', 'buyer'])
            ->where('seller_id', $sellerId)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $items = $query->paginate(min((int) $request->get('per_page', 20), 50));

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Create a pending Buy & Sell purchase — PayPal confirm unlocks paid status.
     */
    public function purchase(Request $request, $id): JsonResponse
    {
        if (! Schema::hasTable('buy_sell_purchases')) {
            return response()->json([
                'success' => false,
                'message' => 'Run migrations: buy_sell_purchases table missing.',
            ], 503);
        }

        $advert = BuySellAdvert::active()->find($id);
        if (! $advert) {
            return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
        }

        $buyerId = Auth::guard('api')->id() ?: Auth::id();
        if ((int) $advert->user_id === (int) $buyerId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot buy your own listing',
            ], 400);
        }

        $price = (float) ($advert->price ?? 0);
        if ($price <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This listing is free or has no price — contact the seller instead.',
            ], 400);
        }

        $request->validate([
            'buyer_notes' => 'nullable|string|max:2000',
        ]);

        $existingPaid = BuySellPurchase::where('buysell_advert_id', $advert->id)
            ->where('buyer_id', $buyerId)
            ->where('payment_status', 'paid')
            ->latest('id')
            ->first();

        if ($existingPaid) {
            return response()->json([
                'success' => true,
                'message' => 'Already purchased.',
                'data' => [
                    'purchase_id' => $existingPaid->id,
                    'payment_status' => 'paid',
                    'amount' => (float) $existingPaid->price,
                    'title' => $existingPaid->title,
                ],
            ]);
        }

        $pending = BuySellPurchase::where('buysell_advert_id', $advert->id)
            ->where('buyer_id', $buyerId)
            ->where('payment_status', 'pending')
            ->latest('id')
            ->first();

        if ($pending) {
            return response()->json([
                'success' => true,
                'message' => 'Complete PayPal payment to finish your purchase.',
                'data' => [
                    'purchase_id' => $pending->id,
                    'payment_status' => 'pending',
                    'amount' => (float) $pending->price,
                    'currency' => $pending->currency ?: 'USD',
                    'title' => $pending->title,
                ],
            ]);
        }

        $fee = PlatformFeeHelper::split($price);

        $purchase = BuySellPurchase::create([
            'buysell_advert_id' => $advert->id,
            'buyer_id' => $buyerId,
            'seller_id' => $advert->user_id,
            'title' => $advert->title,
            'price' => $price,
            'currency' => $advert->currency ?: 'USD',
            'fee_percent' => $fee['fee_percent'],
            'platform_fee' => $fee['platform_fee'],
            'seller_amount' => $fee['seller_amount'],
            'payment_status' => 'pending',
            'buyer_notes' => $request->input('buyer_notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order created. Complete PayPal payment to buy this item.',
            'data' => [
                'purchase_id' => $purchase->id,
                'payment_status' => 'pending',
                'amount' => (float) $purchase->price,
                'currency' => $purchase->currency,
                'title' => $purchase->title,
                'platform_fee' => $purchase->platform_fee,
                'fee_percent' => $purchase->fee_percent,
            ],
        ], 201);
    }

    /**
     * Confirm PayPal/Stripe capture for a Buy & Sell purchase.
     */
    public function confirmPayment(Request $request, $purchaseId): JsonResponse
    {
        if (! Schema::hasTable('buy_sell_purchases')) {
            return response()->json(['success' => false, 'message' => 'Not available'], 503);
        }

        $purchase = BuySellPurchase::find($purchaseId);
        $buyerId = Auth::guard('api')->id() ?: Auth::id();

        if (! $purchase || (int) $purchase->buyer_id !== (int) $buyerId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($purchase->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Already paid.',
                'data' => [
                    'purchase_id' => $purchase->id,
                    'payment_status' => 'paid',
                    'amount' => (float) $purchase->price,
                    'title' => $purchase->title,
                ],
            ]);
        }

        $request->validate([
            'payment_id' => 'required|string|max:191',
            'payment_method' => 'required|in:paypal,stripe',
        ]);

        $purchase->markPaid($request->payment_method, $request->payment_id);

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed. The seller has been notified of your purchase.',
            'data' => [
                'purchase_id' => $purchase->id,
                'payment_status' => 'paid',
                'amount' => (float) $purchase->price,
                'title' => $purchase->title,
                'seller_amount' => $purchase->seller_amount,
                'platform_fee' => $purchase->platform_fee,
            ],
        ]);
    }
}
