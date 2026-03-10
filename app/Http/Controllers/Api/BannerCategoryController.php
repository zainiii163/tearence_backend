<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BannerCategory;
use App\Http\Resources\BannerCategoryResource;
use App\Http\Resources\BannerCategoryCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BannerCategoryController extends Controller
{
    /**
     * Display a listing of banner categories.
     */
    public function index(Request $request): BannerCategoryCollection
    {
        $query = BannerCategory::query();

        // Only show active categories by default
        if (!$request->has('include_inactive')) {
            $query->active();
        }

        // Order by sort order
        $query->ordered();

        // Filter with banner count
        if ($request->has('with_banner_count')) {
            $query->withCount('activeBannerAds');
        }

        // Get most popular categories
        if ($request->has('most_popular')) {
            $limit = $request->get('limit', 10);
            $categories = $query->mostPopular($limit)->get();
            return new BannerCategoryCollection($categories);
        }

        $categories = $query->get();
        return new BannerCategoryCollection($categories);
    }

    /**
     * Store a newly created banner category.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user has permission to manage categories
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if (!$user->hasPermission('manage_categories')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to create categories'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:banner_categories,name',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category = BannerCategory::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Banner category created successfully',
                'data' => new BannerCategoryResource($category)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified banner category.
     */
    public function show(string $slug): JsonResponse
    {
        $category = BannerCategory::where('slug', $slug)
            ->withCount('activeBannerAds')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => new BannerCategoryResource($category)
        ]);
    }

    /**
     * Get banner ads in a specific category.
     */
    public function bannerAds(string $slug, Request $request): JsonResponse
    {
        $category = BannerCategory::where('slug', $slug)->firstOrFail();
        
        $query = $category->activeBannerAds()->with(['user']);

        // Apply filters
        if ($request->has('promotion_tier')) {
            if ($request->promotion_tier === 'promoted') {
                $query->promoted();
            } elseif ($request->promotion_tier === 'featured') {
                $query->featured();
            } elseif ($request->promotion_tier === 'sponsored') {
                $query->sponsored();
            } elseif ($request->promotion_tier === 'network_boost') {
                $query->networkBoost();
            }
        }

        // Filter by country
        if ($request->has('country')) {
            $query->inCountry($request->country);
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
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $limit = $request->get('limit', 20);
        $bannerAds = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => new BannerCategoryResource($category),
                'banner_ads' => new \App\Http\Resources\BannerAdCollection($bannerAds)
            ]
        ]);
    }

    /**
     * Update the specified banner category.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Check if user has permission to manage categories
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if (!$user->hasPermission('manage_categories')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update categories'
                ], 403);
            }
        }

        $category = BannerCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:banner_categories,name,' . $id,
            'description' => 'sometimes|nullable|string|max:1000',
            'icon' => 'sometimes|nullable|string|max:255',
            'image' => 'sometimes|nullable|string|max:255',
            'color' => 'sometimes|nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $category->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Banner category updated successfully',
                'data' => new BannerCategoryResource($category)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified banner category.
     */
    public function destroy(string $id): JsonResponse
    {
        // Check if user has permission to manage categories
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if (!$user->hasPermission('manage_categories')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete categories'
                ], 403);
            }
        }

        $category = BannerCategory::findOrFail($id);

        // Check if category has banner ads
        if ($category->bannerAds()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category that contains banner ads'
            ], 422);
        }

        try {
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner category deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trending categories (most banner ads).
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $categories = BannerCategory::active()
            ->withCount('activeBannerAds')
            ->orderBy('active_banners_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => BannerCategoryResource::collection($categories)
        ]);
    }

    /**
     * Update banner counts for all categories.
     */
    public function updateBannerCounts(): JsonResponse
    {
        // This is typically called via cron job or admin action
        try {
            $categories = BannerCategory::all();
            
            foreach ($categories as $category) {
                $category->updateActiveBannersCount();
            }

            return response()->json([
                'success' => true,
                'message' => 'Banner counts updated successfully',
                'updated_count' => $categories->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner counts',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
