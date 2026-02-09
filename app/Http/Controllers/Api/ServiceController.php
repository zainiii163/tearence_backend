<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\ServiceOrder;
use App\Models\ServiceReview;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Service::with(['user', 'category', 'packages', 'reviews'])
            ->active();

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        // Filter by service category
        if ($request->service_category) {
            $query->byServiceCategory($request->service_category);
        }

        // Filter by price range
        if ($request->min_price) {
            $query->byPriceRange($request->min_price, $request->max_price);
        }

        // Filter by delivery time
        if ($request->delivery_time) {
            $query->byDeliveryTime($request->delivery_time);
        }

        // Filter by skill level
        if ($request->skill_level) {
            $query->where('skill_level', $request->skill_level);
        }

        // Filter by pricing model
        if ($request->pricing_model) {
            $query->where('pricing_model', $request->pricing_model);
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';

        switch ($sortBy) {
            case 'rating':
                $query->orderByRating($sortOrder);
                break;
            case 'orders':
                $query->orderByOrders($sortOrder);
                break;
            case 'price':
                $query->orderByPrice($sortOrder);
                break;
            case 'featured':
                $query->featured()->orderBy('created_at', 'desc');
                break;
            case 'verified':
                $query->verified()->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $services = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function show(Service $service): JsonResponse
    {
        $service->load(['user', 'category', 'packages', 'galleries' => function ($query) {
            $query->ordered();
        }, 'reviews' => function ($query) {
            $query->with('buyer')->latest();
        }]);

        // Increment views
        $service->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,category_id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'service_type' => 'required|in:freelance,consulting,digital_service,local_service,online_service',
            'pricing_model' => 'required|in:fixed_price,hourly_rate,package,quote_based',
            'base_price' => 'required|numeric|min:0',
            'delivery_time' => 'required|in:1_day,3_days,1_week,2_weeks,1_month,custom',
            'skill_level' => 'required|in:beginner,intermediate,expert,professional',
            'service_category' => 'required|in:design,writing,programming,marketing,business,video,audio,other',
            'portfolio_link' => 'nullable|url',
            'requirements' => 'nullable|array',
            'revisions_included' => 'nullable|integer|min:0',
            'extra_fast_delivery' => 'nullable|numeric|min:0',
            'packages' => 'nullable|array',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.description' => 'required|string',
            'packages.*.price' => 'required|numeric|min:0',
            'packages.*.delivery_time' => 'required|string',
            'packages.*.revisions' => 'nullable|integer|min:0',
            'packages.*.features' => 'nullable|array',
            'packages.*.is_popular' => 'boolean',
        ]);

        $service = Service::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'service_type' => $request->service_type,
            'pricing_model' => $request->pricing_model,
            'base_price' => $request->base_price,
            'delivery_time' => $request->delivery_time,
            'skill_level' => $request->skill_level,
            'service_category' => $request->service_category,
            'portfolio_link' => $request->portfolio_link,
            'requirements' => $request->requirements,
            'revisions_included' => $request->revisions_included ?? 0,
            'extra_fast_delivery' => $request->extra_fast_delivery,
            'is_active' => true,
        ]);

        // Create packages if provided
        if ($request->packages) {
            foreach ($request->packages as $packageData) {
                ServicePackage::create([
                    'service_id' => $service->id,
                    'name' => $packageData['name'],
                    'description' => $packageData['description'],
                    'price' => $packageData['price'],
                    'delivery_time' => $packageData['delivery_time'],
                    'revisions' => $packageData['revisions'] ?? 0,
                    'features' => $packageData['features'] ?? [],
                    'is_popular' => $packageData['is_popular'] ?? false,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => $service->load('packages'),
        ], 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'base_price' => 'sometimes|required|numeric|min:0',
            'delivery_time' => 'sometimes|required|in:1_day,3_days,1_week,2_weeks,1_month,custom',
            'skill_level' => 'sometimes|required|in:beginner,intermediate,expert,professional',
            'service_category' => 'sometimes|required|in:design,writing,programming,marketing,business,video,audio,other',
            'portfolio_link' => 'nullable|url',
            'requirements' => 'nullable|array',
            'revisions_included' => 'nullable|integer|min:0',
            'extra_fast_delivery' => 'nullable|numeric|min:0',
        ]);

        $service->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => $service,
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully',
        ]);
    }

    public function myServices(Request $request): JsonResponse
    {
        $services = Service::with(['category', 'packages', 'orders'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function toggleStatus(Service $service): JsonResponse
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $service->is_active = !$service->is_active;
        $service->save();

        return response()->json([
            'success' => true,
            'message' => 'Service status updated successfully',
            'data' => $service,
        ]);
    }

    public function uploadGallery(Request $request, Service $service): JsonResponse
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_video' => 'boolean',
            'video_url' => 'nullable|url',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services/gallery', 'public');
            
            $gallery = $service->galleries()->create([
                'image_path' => $path,
                'title' => $request->title,
                'description' => $request->description,
                'is_video' => $request->is_video ?? false,
                'video_url' => $request->video_url,
                'sort_order' => $service->galleries()->count() + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gallery item uploaded successfully',
                'data' => $gallery,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image provided',
        ], 400);
    }

    public function getServiceCategories(): JsonResponse
    {
        $categories = [
            'design' => 'Design & Creative',
            'writing' => 'Writing & Translation',
            'programming' => 'Programming & Tech',
            'marketing' => 'Marketing & Sales',
            'business' => 'Business & Finance',
            'video' => 'Video & Animation',
            'audio' => 'Audio & Music',
            'other' => 'Other',
        ];

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function getPopularServices(): JsonResponse
    {
        $services = Service::with(['user', 'category'])
            ->active()
            ->orderBy('orders_count', 'desc')
            ->orderBy('rating', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function getFeaturedServices(): JsonResponse
    {
        $services = Service::with(['user', 'category'])
            ->active()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }
}
