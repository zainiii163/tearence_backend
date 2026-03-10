<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\ServiceAddon;
use App\Models\ServicePromotion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // Dashboard Overview
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_services' => Service::count(),
            'active_services' => Service::where('status', 'active')->count(),
            'pending_services' => Service::where('status', 'pending')->count(),
            'total_categories' => ServiceCategory::where('is_active', true)->count(),
            'promoted_services' => Service::whereNotNull('promotion_type')->count(),
            'total_revenue' => ServicePromotion::sum('price'),
            'recent_services' => Service::with(['user', 'category'])
                ->latest()
                ->take(5)
                ->get(),
            'popular_categories' => ServiceCategory::withCount('activeServices')
                ->orderBy('active_services_count', 'desc')
                ->take(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    // Services Management
    public function index(Request $request): JsonResponse
    {
        $query = Service::with(['user', 'serviceProvider', 'category', 'packages', 'addons', 'promotions']);

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by promotion type
        if ($request->promotion_type) {
            $query->where('promotion_type', $request->promotion_type);
        }

        // Filter by service type
        if ($request->service_type) {
            $query->where('service_type', $request->service_type);
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $services = $query->paginate($request->per_page ?? 15);

        return response()->json($services);
    }

    public function show(Service $service): JsonResponse
    {
        $service->load(['user', 'serviceProvider', 'category', 'packages', 'addons', 'promotions', 'media']);
        
        return response()->json($service);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'required|string',
            'whats_included' => 'nullable|array',
            'whats_not_included' => 'nullable|array',
            'requirements' => 'nullable|string',
            'service_type' => ['required', Rule::in(['freelance', 'local', 'business'])],
            'starting_price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'delivery_time' => 'required|integer|min:1',
            'availability' => 'nullable|array',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'service_area_radius' => 'nullable|integer|min:1',
            'status' => ['required', Rule::in(['active', 'inactive', 'pending', 'suspended'])],
            'promotion_type' => ['nullable', Rule::in(['promoted', 'featured', 'sponsored', 'network_boost'])],
            'promotion_expires_at' => 'nullable|date|after:now',
            'is_verified' => 'boolean',
            'languages' => 'nullable|array',
        ]);

        $service->update($validated);

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service->fresh()
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ]);
    }

    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject', 'suspend', 'delete'])],
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id'
        ]);

        $services = Service::whereIn('id', $validated['service_ids']);

        switch ($validated['action']) {
            case 'approve':
                $services->update(['status' => 'active']);
                $message = 'Services approved successfully';
                break;
            case 'reject':
                $services->update(['status' => 'suspended']);
                $message = 'Services rejected successfully';
                break;
            case 'suspend':
                $services->update(['status' => 'suspended']);
                $message = 'Services suspended successfully';
                break;
            case 'delete':
                $services->delete();
                $message = 'Services deleted successfully';
                break;
        }

        return response()->json(['message' => $message]);
    }

    // Categories Management
    public function categoriesIndex(): JsonResponse
    {
        $categories = ServiceCategory::withCount('services')
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories);
    }

    public function categoriesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_categories',
            'slug' => 'nullable|string|max:255|unique:service_categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category = ServiceCategory::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    public function categoriesUpdate(Request $request, ServiceCategory $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:service_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category->fresh()
        ]);
    }

    public function categoriesDestroy(ServiceCategory $category): JsonResponse
    {
        if ($category->services()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with associated services'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }

    // Promotions Management
    public function promotionsIndex(Request $request): JsonResponse
    {
        $query = ServicePromotion::with(['service', 'service.user', 'service.category']);

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by promotion type
        if ($request->promotion_type) {
            $query->where('promotion_type', $request->promotion_type);
        }

        $promotions = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json($promotions);
    }

    public function promotionsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'promotion_type' => ['required', Rule::in(['promoted', 'featured', 'sponsored', 'network_boost'])],
            'duration_days' => 'required|integer|min:1|max:365',
            'notes' => 'nullable|string',
        ]);

        $pricing = ServicePromotion::getPromotionPricing()[$validated['promotion_type']];
        
        $promotion = ServicePromotion::create([
            'service_id' => $validated['service_id'],
            'promotion_type' => $validated['promotion_type'],
            'price' => $pricing['price'],
            'currency' => 'USD',
            'duration_days' => $validated['duration_days'],
            'starts_at' => now(),
            'expires_at' => now()->addDays($validated['duration_days']),
            'status' => 'active',
            'benefits' => $pricing['benefits'],
            'notes' => $validated['notes'],
        ]);

        // Update service promotion type
        $service = Service::find($validated['service_id']);
        $service->update([
            'promotion_type' => $validated['promotion_type'],
            'promotion_expires_at' => $promotion->expires_at,
        ]);

        return response()->json([
            'message' => 'Promotion created successfully',
            'promotion' => $promotion->fresh(['service'])
        ], 201);
    }

    public function promotionsUpdate(Request $request, ServicePromotion $promotion): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'expired', 'cancelled'])],
            'notes' => 'nullable|string',
        ]);

        $promotion->update($validated);

        // Update service promotion if cancelled
        if ($validated['status'] === 'cancelled') {
            $promotion->service->update([
                'promotion_type' => null,
                'promotion_expires_at' => null,
            ]);
        }

        return response()->json([
            'message' => 'Promotion updated successfully',
            'promotion' => $promotion->fresh()
        ]);
    }

    public function promotionsDestroy(ServicePromotion $promotion): JsonResponse
    {
        // Update service promotion
        $promotion->service->update([
            'promotion_type' => null,
            'promotion_expires_at' => null,
        ]);

        $promotion->delete();

        return response()->json([
            'message' => 'Promotion deleted successfully'
        ]);
    }

    // Analytics
    public function analytics(Request $request): JsonResponse
    {
        $period = $request->period ?? '30'; // days
        
        $analytics = [
            'services_over_time' => Service::where('created_at', '>=', now()->subDays($period))
                ->groupBy('date')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->get(),
            
            'services_by_category' => ServiceCategory::withCount('services')
                ->orderBy('services_count', 'desc')
                ->get(),
            
            'services_by_type' => Service::selectRaw('service_type, COUNT(*) as count')
                ->groupBy('service_type')
                ->get(),
            
            'promotion_revenue' => ServicePromotion::where('created_at', '>=', now()->subDays($period))
                ->selectRaw('promotion_type, SUM(price) as revenue, COUNT(*) as count')
                ->groupBy('promotion_type')
                ->get(),
            
            'top_services' => Service::withCount(['promotions'])
                ->orderBy('promotions_count', 'desc')
                ->take(10)
                ->get(['id', 'title', 'views', 'enquiries', 'rating']),
        ];

        return response()->json($analytics);
    }

    // Get promotion pricing for frontend
    public function promotionPricing(): JsonResponse
    {
        return response()->json(ServicePromotion::getPromotionPricing());
    }
}
