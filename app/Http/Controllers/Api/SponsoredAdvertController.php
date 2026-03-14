<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SponsoredAdvert;
use App\Models\SponsoredCategory;
use App\Models\SponsoredAnalytic;
use App\Models\SavedAdvert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SponsoredAdvertController extends Controller
{
    /**
     * Display a listing of sponsored adverts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SponsoredAdvert::with(['category', 'user'])
            ->active();

        // Apply filters
        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->featured) {
            $query->featured();
        }

        if ($request->promoted) {
            $query->promoted();
        }

        if ($request->sponsored) {
            $query->sponsored();
        }

        // Apply sorting
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        
        if (in_array($sort, ['views', 'rating', 'price'])) {
            $query->orderBy($sort, $order);
        } else {
            $query->orderBy($sort, $order);
        }

        $adverts = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'meta' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
                'has_more' => $adverts->hasMorePages(),
            ],
        ]);
    }

    /**
     * Display the specified sponsored advert.
     */
    public function show($id): JsonResponse
    {
        $advert = SponsoredAdvert::with(['category', 'user'])
            ->active()
            ->findOrFail($id);

        // Track view
        $advert->trackEvent('view', [], Auth::id());

        return response()->json([
            'success' => true,
            'data' => $advert,
        ]);
    }

    /**
     * Store a newly created sponsored advert.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'category_id' => 'required|exists:sponsored_categories,id',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'video_url' => 'nullable|url',
            'seller_info' => 'nullable|array',
            'location' => 'nullable|array',
            'promotion_plan' => 'nullable|in:free,promoted,featured,sponsored',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending';

        $advert = SponsoredAdvert::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Advert created successfully. It will be reviewed within 24 hours.',
            'data' => $advert->load(['category', 'user']),
        ], 201);
    }

    /**
     * Update the specified sponsored advert.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($id);

        // Check if user owns this advert
        if ($advert->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|nullable|numeric|min:0',
            'currency' => 'sometimes|required|string|size:3',
            'category_id' => 'sometimes|required|exists:sponsored_categories,id',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'string',
            'video_url' => 'sometimes|nullable|url',
            'seller_info' => 'sometimes|nullable|array',
            'location' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $advert->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Advert updated successfully',
            'data' => $advert->fresh()->load(['category', 'user']),
        ]);
    }

    /**
     * Remove the specified sponsored advert.
     */
    public function destroy($id): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($id);

        // Check if user owns this advert
        if ($advert->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $advert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Advert deleted successfully',
        ]);
    }

    /**
     * Search sponsored adverts.
     */
    public function search(Request $request): JsonResponse
    {
        $query = SponsoredAdvert::with(['category', 'user'])
            ->active();

        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->keyword . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->keyword . '%');
            });
        }

        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->verified_only) {
            $query->whereHas('user', function ($q) {
                $q->where('verified', true);
            });
        }

        $adverts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'meta' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
            ],
        ]);
    }

    /**
     * Get featured sponsored adverts.
     */
    public function featured(Request $request): JsonResponse
    {
        $adverts = SponsoredAdvert::with(['category', 'user'])
            ->active()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 10))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    /**
     * Get adverts by category.
     */
    public function byCategory($slug): JsonResponse
    {
        $category = SponsoredCategory::where('slug', $slug)->firstOrFail();
        
        $adverts = SponsoredAdvert::with(['category', 'user'])
            ->active()
            ->where('category_id', $category->id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'meta' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
            ],
        ]);
    }

    /**
     * Get user's sponsored adverts.
     */
    public function userAdverts(Request $request): JsonResponse
    {
        $query = SponsoredAdvert::with(['category'])
            ->where('user_id', Auth::id());

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $adverts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $adverts->items(),
            'meta' => [
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
            ],
        ]);
    }

    /**
     * Save/unsave advert.
     */
    public function save($advertId): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($advertId);
        
        $savedAdvert = SavedAdvert::where('user_id', Auth::id())
            ->where('advert_id', $advertId)
            ->first();

        if ($savedAdvert) {
            $savedAdvert->delete();
            $saved = false;
        } else {
            SavedAdvert::create([
                'user_id' => Auth::id(),
                'advert_id' => $advertId,
            ]);
            $saved = true;
            
            // Track save event
            $advert->trackEvent('save', [], Auth::id());
        }

        $totalSaved = SavedAdvert::where('advert_id', $advertId)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'saved' => $saved,
                'total_saved' => $totalSaved,
            ],
        ]);
    }

    /**
     * Get saved adverts.
     */
    public function saved(Request $request): JsonResponse
    {
        $savedAdverts = SavedAdvert::with(['advert.category', 'advert.user'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $savedAdverts->items(),
            'meta' => [
                'current_page' => $savedAdverts->currentPage(),
                'last_page' => $savedAdverts->lastPage(),
                'per_page' => $savedAdverts->perPage(),
                'total' => $savedAdverts->total(),
            ],
        ]);
    }

    /**
     * Track analytics event.
     */
    public function track($advertId, Request $request): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($advertId);

        $validator = Validator::make($request->all(), [
            'event_type' => 'required|in:view,click,save,contact,share',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $advert->trackEvent(
            $request->event_type,
            $request->metadata ?? [],
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Event tracked successfully',
        ]);
    }

    /**
     * Get advert analytics.
     */
    public function analytics($advertId): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($advertId);

        // Check if user owns this advert
        if ($advert->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $analytics = $advert->analytics()
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->pluck('count', 'event_type');

        $dailyStats = $advert->analytics()
            ->selectRaw('DATE(created_at) as date, event_type, COUNT(*) as count')
            ->groupBy('date', 'event_type')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->groupBy('date');

        return response()->json([
            'success' => true,
            'data' => [
                'total_views' => $analytics['view'] ?? 0,
                'total_clicks' => $analytics['click'] ?? 0,
                'saves' => $analytics['save'] ?? 0,
                'contact_requests' => $analytics['contact'] ?? 0,
                'shares' => $analytics['share'] ?? 0,
                'daily_stats' => $dailyStats,
            ],
        ]);
    }

    /**
     * Get homepage statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'sponsored_ads' => number_format(SponsoredAdvert::active()->count()),
            'countries' => number_format(SponsoredAdvert::active()->distinct('country')->count('country')),
            'total_views' => number_format(SponsoredAdvert::sum('views')),
            'satisfaction' => '98%',
            'active_users' => '45.2K',
            'revenue' => '$125,430',
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get live activity feed.
     */
    public function activity(Request $request): JsonResponse
    {
        $activities = SponsoredAnalytic::with(['advert', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 20))
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->event_type,
                    'message' => $this->getActivityMessage($activity),
                    'timestamp' => $activity->created_at->toISOString(),
                    'icon' => $this->getActivityIcon($activity->event_type),
                    'user_id' => $activity->user_id,
                    'advert_id' => $activity->advert_id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    private function getActivityMessage($activity): string
    {
        $userName = $activity->user ? $activity->user->name : 'Someone';
        $advertTitle = $activity->advert ? $activity->advert->title : 'an advert';
        $country = $activity->advert->country ?? 'Unknown';

        switch ($activity->event_type) {
            case 'view':
                return "{$userName} viewed {$advertTitle} in {$country}";
            case 'save':
                return "{$userName} saved {$advertTitle}";
            case 'contact':
                return "{$userName} contacted seller about {$advertTitle}";
            case 'share':
                return "{$userName} shared {$advertTitle}";
            default:
                return "{$userName} interacted with {$advertTitle}";
        }
    }

    private function getActivityIcon($eventType): string
    {
        $icons = [
            'view' => '👁️',
            'click' => '👆',
            'save' => '❤️',
            'contact' => '💬',
            'share' => '🔗',
        ];

        return $icons[$eventType] ?? '📊';
    }
}
