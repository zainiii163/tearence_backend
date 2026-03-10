<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceMedia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Service::with(['user', 'serviceProvider', 'category', 'packages', 'addons', 'promotions', 'media'])
            ->active();

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        // Filter by country
        if ($request->country) {
            $query->byCountry($request->country);
        }

        // Filter by service type
        if ($request->service_type) {
            $query->byType($request->service_type);
        }

        // Filter by price range
        if ($request->min_price) {
            $query->where('starting_price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('starting_price', '<=', $request->max_price);
        }

        // Filter by verified providers only
        if ($request->verified_only) {
            $query->verified();
        }

        // Filter by promotion type
        if ($request->promotion_type) {
            if ($request->promotion_type === 'promoted') {
                $query->promoted();
            } elseif ($request->promotion_type === 'featured') {
                $query->featured();
            }
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';

        switch ($sortBy) {
            case 'rating':
                $query->orderBy('rating', $sortOrder);
                break;
            case 'price_low':
                $query->orderBy('starting_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('starting_price', 'desc');
                break;
            case 'views':
                $query->orderBy('views', $sortOrder);
                break;
            case 'enquiries':
                $query->orderBy('enquiries', $sortOrder);
                break;
            case 'featured':
                $query->featured()->orderBy('created_at', 'desc');
                break;
            case 'trending':
                $query->orderBy('views', 'desc')->orderBy('enquiries', 'desc');
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
        $service->load([
            'user', 
            'serviceProvider', 
            'category', 
            'packages' => function ($query) {
                $query->active()->ordered();
            }, 
            'addons' => function ($query) {
                $query->active()->ordered();
            },
            'media' => function ($query) {
                $query->ordered();
            }
        ]);

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
            'category_id' => 'required|exists:service_categories,id',
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'description' => 'required|string',
            'whats_included' => 'nullable|array',
            'whats_not_included' => 'nullable|array',
            'requirements' => 'nullable|string',
            'service_type' => 'required|in:freelance,local,business',
            'starting_price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'delivery_time' => 'nullable|integer|min:1',
            'availability' => 'nullable|array',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'service_area_radius' => 'nullable|integer|min:0',
            'languages' => 'nullable|array',
            'packages' => 'nullable|array',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.description' => 'required|string',
            'packages.*.price' => 'required|numeric|min:0',
            'packages.*.delivery_time' => 'required|integer|min:1',
            'packages.*.features' => 'nullable|array',
            'packages.*.revisions' => 'nullable|integer|min:0',
            'packages.*.sort_order' => 'nullable|integer|min:0',
            'addons' => 'nullable|array',
            'addons.*.title' => 'required|string|max:255',
            'addons.*.description' => 'nullable|string',
            'addons.*.price' => 'required|numeric|min:0',
            'addons.*.delivery_time' => 'nullable|integer|min:0',
            'addons.*.features' => 'nullable|array',
            'addons.*.sort_order' => 'nullable|integer|min:0',
            'promotion_type' => 'nullable|in:standard,promoted,featured,sponsored,network_boost',
        ]);

        // Create or update service provider
        $serviceProvider = ServiceProvider::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'country' => $request->country,
                'city' => $request->city,
                'is_verified' => false,
                'rating' => 0,
                'review_count' => 0,
            ]
        );

        $service = Service::create([
            'user_id' => Auth::id(),
            'service_provider_id' => $serviceProvider->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'tagline' => $request->tagline,
            'description' => $request->description,
            'whats_included' => $request->whats_included,
            'whats_not_included' => $request->whats_not_included,
            'requirements' => $request->requirements,
            'service_type' => $request->service_type,
            'starting_price' => $request->starting_price,
            'currency' => $request->currency,
            'delivery_time' => $request->delivery_time,
            'availability' => $request->availability,
            'country' => $request->country,
            'city' => $request->city,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'service_area_radius' => $request->service_area_radius,
            'languages' => $request->languages,
            'status' => 'draft',
            'promotion_type' => $request->promotion_type ?? 'standard',
            'is_verified' => false,
        ]);

        // Create packages if provided
        if ($request->packages) {
            foreach ($request->packages as $index => $packageData) {
                ServicePackage::create([
                    'service_id' => $service->id,
                    'name' => $packageData['name'],
                    'description' => $packageData['description'],
                    'price' => $packageData['price'],
                    'currency' => $request->currency,
                    'delivery_time' => $packageData['delivery_time'],
                    'features' => $packageData['features'] ?? [],
                    'revisions' => $packageData['revisions'] ?? 1,
                    'sort_order' => $packageData['sort_order'] ?? $index,
                    'is_active' => true,
                ]);
            }
        }

        // Create addons if provided
        if ($request->addons) {
            foreach ($request->addons as $index => $addonData) {
                ServiceAddon::create([
                    'service_id' => $service->id,
                    'title' => $addonData['title'],
                    'description' => $addonData['description'] ?? null,
                    'price' => $addonData['price'],
                    'currency' => $request->currency,
                    'delivery_time' => $addonData['delivery_time'] ?? null,
                    'features' => $addonData['features'] ?? [],
                    'sort_order' => $addonData['sort_order'] ?? $index,
                    'is_active' => true,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => $service->load(['category', 'packages', 'addons']),
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
            'tagline' => 'nullable|string|max:255',
            'description' => 'sometimes|required|string',
            'whats_included' => 'nullable|array',
            'whats_not_included' => 'nullable|array',
            'requirements' => 'nullable|string',
            'starting_price' => 'sometimes|required|numeric|min:0',
            'delivery_time' => 'nullable|integer|min:1',
            'availability' => 'nullable|array',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'service_area_radius' => 'nullable|integer|min:0',
            'languages' => 'nullable|array',
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
        $services = Service::with(['category', 'packages'])
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

        $newStatus = $service->status === 'active' ? 'paused' : 'active';
        $service->status = $newStatus;
        $service->save();

        return response()->json([
            'success' => true,
            'message' => "Service status updated to {$newStatus}",
            'data' => $service,
        ]);
    }

    public function uploadMedia(Request $request, Service $service): JsonResponse
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'required|in:image,video,document',
            'caption' => 'nullable|string|max:255',
            'is_thumbnail' => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('services/media', 'public');
            
            // If this is set as thumbnail, unset other thumbnails
            if ($request->is_thumbnail) {
                $service->media()->where('is_thumbnail', true)->update(['is_thumbnail' => false]);
            }
            
            $media = $service->media()->create([
                'type' => $request->type,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'caption' => $request->caption,
                'sort_order' => $service->media()->count() + 1,
                'is_thumbnail' => $request->is_thumbnail ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media uploaded successfully',
                'data' => $media,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file provided',
        ], 400);
    }

    public function getCategories(): JsonResponse
    {
        $categories = ServiceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->withCount('activeServices')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function getFeaturedServices(): JsonResponse
    {
        $services = Service::with(['user', 'serviceProvider', 'category', 'media'])
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

    public function getPopularServices(): JsonResponse
    {
        $services = Service::with(['user', 'serviceProvider', 'category', 'media'])
            ->active()
            ->orderBy('enquiries', 'desc')
            ->orderBy('rating', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function incrementEnquiries(Service $service): JsonResponse
    {
        $service->incrementEnquiries();

        return response()->json([
            'success' => true,
            'message' => 'Enquiry count incremented',
        ]);
    }

    public function getPromotionOptions(): JsonResponse
    {
        $options = [
            'promoted' => [
                'name' => 'Promoted Listing',
                'price' => 29.99,
                'duration_days' => 30,
                'benefits' => [
                    'Highlighted listing',
                    'Appears above standard services',
                    'Promoted badge',
                    '2× more visibility',
                ]
            ],
            'featured' => [
                'name' => 'Featured Listing',
                'price' => 49.99,
                'duration_days' => 30,
                'benefits' => [
                    'Top of category pages',
                    'Larger service card',
                    'Priority in search results',
                    'Included in weekly Featured Services email',
                    'Featured badge',
                ]
            ],
            'sponsored' => [
                'name' => 'Sponsored Listing',
                'price' => 79.99,
                'duration_days' => 30,
                'benefits' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    'Sponsored badge',
                ]
            ],
            'network_boost' => [
                'name' => 'Network-Wide Boost',
                'price' => 149.99,
                'duration_days' => 30,
                'benefits' => [
                    'Appears across multiple pages',
                    'Services page placement',
                    'Homepage placement',
                    'Category pages placement',
                    'Included in newsletters',
                    'Included in push notifications',
                    'Top Spotlight badge',
                ]
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $options,
        ]);
    }

    public function purchasePromotion(Request $request, Service $service): JsonResponse
    {
        $request->validate([
            'promotion_type' => 'required|in:promoted,featured,sponsored,network_boost',
        ]);

        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $options = $this->getPromotionOptions()->getData(true)->data;
        $promotionData = $options[$request->promotion_type] ?? null;

        if (!$promotionData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promotion type',
            ], 400);
        }

        // Update service promotion
        $service->update([
            'promotion_type' => $request->promotion_type,
            'promotion_expires_at' => now()->addDays($promotionData['duration_days']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Promotion purchased successfully',
            'data' => [
                'promotion_type' => $request->promotion_type,
                'expires_at' => $service->promotion_expires_at,
                'price' => $promotionData['price'],
            ],
        ]);
    }
}
