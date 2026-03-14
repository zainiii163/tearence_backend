<?php

namespace App\Http\Controllers;

use App\Models\SponsoredAdvert;
use App\Models\SavedAdvert;
use App\Models\SponsoredAnalytic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SponsoredDashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function dashboard(): JsonResponse
    {
        $user = Auth::user();
        
        $stats = [
            'total_adverts' => SponsoredAdvert::where('user_id', $user->id)->count(),
            'active_adverts' => SponsoredAdvert::where('user_id', $user->id)->where('status', 'active')->count(),
            'total_views' => SponsoredAdvert::where('user_id', $user->id)->sum('views'),
            'total_saves' => SavedAdvert::whereHas('advert', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
        ];

        $recentAdverts = SponsoredAdvert::with(['category'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $savedAdverts = SavedAdvert::with(['advert.category'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_adverts' => $recentAdverts,
                'saved_adverts' => $savedAdverts,
            ],
        ]);
    }

    /**
     * Get user's sponsored adverts.
     */
    public function myAdverts(Request $request): JsonResponse
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
     * Show create advert form.
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'form_type' => 'create',
                'categories' => \App\Models\SponsoredCategory::orderBy('name')->get(),
            ],
        ]);
    }

    /**
     * Show edit advert form.
     */
    public function edit($id): JsonResponse
    {
        $advert = SponsoredAdvert::with(['category'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'form_type' => 'edit',
                'advert' => $advert,
                'categories' => \App\Models\SponsoredCategory::orderBy('name')->get(),
            ],
        ]);
    }

    /**
     * Store new advert.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
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
            'data' => $advert->load(['category']),
        ], 201);
    }

    /**
     * Update advert.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $advert = SponsoredAdvert::where('user_id', Auth::id())
            ->findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
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
            'data' => $advert->fresh()->load(['category']),
        ]);
    }

    /**
     * Get analytics for user's adverts.
     */
    public function analytics(): JsonResponse
    {
        $user = Auth::user();
        
        $adverts = SponsoredAdvert::where('user_id', $user->id)->get();
        
        $totalViews = $adverts->sum('views');
        $totalSaves = SavedAdvert::whereHas('advert', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        
        $analyticsData = [];
        foreach ($adverts as $advert) {
            $analyticsData[] = [
                'advert' => $advert,
                'views' => $advert->analytics()->where('event_type', 'view')->count(),
                'saves' => $advert->saves()->count(),
                'contacts' => $advert->analytics()->where('event_type', 'contact')->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_views' => $totalViews,
                'total_saves' => $totalSaves,
                'adverts_analytics' => $analyticsData,
            ],
        ]);
    }

    /**
     * Get saved adverts.
     */
    public function savedAdverts(Request $request): JsonResponse
    {
        $savedAdverts = SavedAdvert::with(['advert.category'])
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
     * Delete advert.
     */
    public function destroy($id): JsonResponse
    {
        $advert = SponsoredAdvert::where('user_id', Auth::id())
            ->findOrFail($id);

        $advert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Advert deleted successfully',
        ]);
    }
}
