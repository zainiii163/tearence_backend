<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function services(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:service_categories,id',
            'price_range.min' => 'nullable|numeric|min:0',
            'price_range.max' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:100',
            'service_type' => 'nullable|in:freelance,local,business',
            'delivery_time.max' => 'nullable|integer|min:1',
            'rating.min' => 'nullable|numeric|min:1|max:5',
            'verified_only' => 'boolean',
            'sort.field' => 'nullable|in:created_at,rating,starting_price,views,enquiries',
            'sort.order' => 'nullable|in:asc,desc',
            'pagination.page' => 'nullable|integer|min:1',
            'pagination.limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Service::with(['user', 'serviceProvider', 'category', 'media'])
            ->active();

        // Search query
        if ($request->query) {
            $query->search($request->query);
        }

        // Category filter
        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        // Price range filter
        if ($request->input('price_range.min')) {
            $query->where('starting_price', '>=', $request->input('price_range.min'));
        }
        if ($request->input('price_range.max')) {
            $query->where('starting_price', '<=', $request->input('price_range.max'));
        }

        // Location filter
        if ($request->location) {
            $query->where('country', 'LIKE', "%{$request->location}%")
                  ->orWhere('city', 'LIKE', "%{$request->location}%");
        }

        // Service type filter
        if ($request->service_type) {
            $query->byType($request->service_type);
        }

        // Delivery time filter
        if ($request->input('delivery_time.max')) {
            $query->where('delivery_time', '<=', $request->input('delivery_time.max'));
        }

        // Rating filter
        if ($request->input('rating.min')) {
            $query->where('rating', '>=', $request->input('rating.min'));
        }

        // Verified only filter
        if ($request->boolean('verified_only')) {
            $query->verified();
        }

        // Sorting
        $sortField = $request->input('sort.field', 'created_at');
        $sortOrder = $request->input('sort.order', 'desc');

        switch ($sortField) {
            case 'rating':
                $query->orderBy('rating', $sortOrder);
                break;
            case 'starting_price':
                $query->orderBy('starting_price', $sortOrder);
                break;
            case 'views':
                $query->orderBy('views', $sortOrder);
                break;
            case 'enquiries':
                $query->orderBy('enquiries', $sortOrder);
                break;
            default:
                $query->orderBy($sortField, $sortOrder);
        }

        // Pagination
        $page = $request->input('pagination.page', 1);
        $limit = $request->input('pagination.limit', 20);

        $services = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:50',
        ]);

        $query = $request->q;

        // Get service suggestions
        $services = Service::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orWhere('tagline', 'LIKE', "%{$query}%")
            ->active()
            ->limit(5)
            ->pluck('title');

        // Get category suggestions
        $categories = ServiceCategory::where('name', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->limit(5)
            ->pluck('name');

        // Get provider suggestions
        $providers = User::where('name', 'LIKE', "%{$query}%")
            ->whereHas('services')
            ->withCount('services')
            ->limit(5)
            ->get(['id', 'name'])
            ->map(function ($user) {
                return $user->name;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'services' => $services,
                'categories' => $categories,
                'providers' => $providers,
            ],
        ]);
    }

    public function popular(): JsonResponse
    {
        $popularServices = Service::with(['user', 'category', 'media'])
            ->active()
            ->orderBy('enquiries', 'desc')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        $popularCategories = ServiceCategory::withCount('activeServices')
            ->where('is_active', true)
            ->orderBy('active_services_count', 'desc')
            ->limit(10)
            ->get();

        $topProviders = User::withCount(['services' => function ($query) {
                $query->active();
            }])
            ->with('serviceProvider')
            ->having('services_count', '>', 0)
            ->orderBy('services_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'profile_photo']);

        return response()->json([
            'success' => true,
            'data' => [
                'popular_services' => $popularServices,
                'popular_categories' => $popularCategories,
                'top_providers' => $topProviders,
            ],
        ]);
    }

    public function trending(): JsonResponse
    {
        $trendingServices = Service::with(['user', 'category', 'media'])
            ->active()
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('views', 'desc')
            ->orderBy('enquiries', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trendingServices,
        ]);
    }
}
