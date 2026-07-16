<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceMedia;
use App\Models\ServiceAddon;
use App\Support\ServiceFormHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Service::with(['user', 'serviceProvider', 'category', 'packages', 'addons', 'promotions', 'media']);

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
                $query->where('promotion_type', $request->promotion_type);
            }

            if ($request->city) {
                $query->where('city', 'like', '%' . $request->city . '%');
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get services: ' . $e->getMessage(),
            ], 500);
        }
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

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $attributes = ServiceFormHelper::buildAttributes($request);

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

        $service = Service::create(array_merge($attributes, [
            'user_id' => Auth::id(),
            'service_provider_id' => $serviceProvider->id,
            'slug' => Str::slug($request->title) . '-' . time(),
            'is_verified' => false,
        ]));

        if ($request->packages) {
            $this->syncPackages($service, $request->packages, $request->currency);
        }

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
            'data' => $service->load(['category', 'packages', 'addons', 'media']),
        ], 201);
    }

    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $attributes = ServiceFormHelper::buildAttributes($request, isUpdate: true);
        $service->update($attributes);

        if ($request->has('packages')) {
            $this->syncPackages($service, $request->packages ?? [], $service->currency);
        }

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully',
            'data' => $service->fresh(['category', 'packages', 'addons', 'media']),
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
        $services = Service::with(['category', 'packages', 'media'])
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
            'file' => 'required_without:files|file|max:10240|mimes:jpeg,png,jpg,gif,webp',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:10240|mimes:jpeg,png,jpg,gif,webp',
            'type' => 'nullable|in:image,video,document',
            'caption' => 'nullable|string|max:255',
            'is_thumbnail' => 'boolean',
        ]);

        $files = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
        } elseif ($request->hasFile('file')) {
            $files = [$request->file('file')];
        }

        if ($files === []) {
            return response()->json([
                'success' => false,
                'message' => 'No file provided',
            ], 400);
        }

        $uploaded = [];
        $mediaType = $request->input('type', 'image');
        $markFirstAsThumbnail = $request->boolean('is_thumbnail')
            || ! ServiceMedia::where('service_id', $service->id)->where('is_thumbnail', true)->exists();

        foreach ($files as $index => $file) {
            $path = $file->store('services/media', 'public');

            $isThumbnail = $markFirstAsThumbnail && $index === 0;
            if ($isThumbnail) {
                ServiceMedia::where('service_id', $service->id)
                    ->where('is_thumbnail', true)
                    ->update(['is_thumbnail' => false]);
            }

            $uploaded[] = $service->media()->create([
                'type' => $mediaType,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'caption' => $request->caption,
                'sort_order' => ServiceMedia::where('service_id', $service->id)->count() + 1,
                'is_thumbnail' => $isThumbnail,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($uploaded) === 1 ? 'Media uploaded successfully' : count($uploaded) . ' files uploaded successfully',
            'data' => count($uploaded) === 1 ? $uploaded[0] : $uploaded,
        ]);
    }

    public function deleteMedia(Request $request, Service $service, ServiceMedia $media): JsonResponse
    {
        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ((int) $media->service_id !== (int) $service->id) {
            return response()->json([
                'success' => false,
                'message' => 'Media not found for this service',
            ], 404);
        }

        if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully',
        ]);
    }

    public function getCategories(): JsonResponse
    {
        $categories = ServiceCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ServiceCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'icon' => $category->icon,
                'sort_order' => $category->sort_order,
                'label' => $category->name,
            ]);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function getFormSchema(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ServiceFormHelper::formSchema(),
        ]);
    }

    private function syncPackages(Service $service, array $packages, string $currency): void
    {
        ServicePackage::where('service_id', $service->id)->delete();

        $sortOrder = 0;
        foreach (array_values($packages) as $packageData) {
            ServicePackage::create([
                'service_id' => $service->id,
                'name' => $packageData['name'],
                'description' => $packageData['description'],
                'price' => $packageData['price'],
                'currency' => $currency,
                'delivery_time' => $packageData['delivery_time'],
                'features' => ServiceFormHelper::normalizeListField($packageData['features'] ?? []) ?? [],
                'revisions' => $packageData['revisions'] ?? 1,
                'sort_order' => $packageData['sort_order'] ?? $sortOrder++,
                'is_active' => true,
            ]);
        }
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
