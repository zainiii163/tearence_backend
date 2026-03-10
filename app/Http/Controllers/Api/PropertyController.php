<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyStoreRequest;
use App\Http\Requests\PropertyUpdateRequest;
use App\Http\Resources\PropertyCollection;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyAnalytics;
use App\Models\PropertySaved;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request): PropertyCollection
    {
        $query = Property::with(['user', 'category']);

        // Apply filters
        if ($request->filled('property_type')) {
            $query->byPropertyType($request->property_type);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('country')) {
            $query->byLocation($request->country, $request->city);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        if ($request->filled('min_bedrooms') || $request->filled('max_bedrooms')) {
            $query->bedrooms($request->min_bedrooms, $request->max_bedrooms);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('city', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('country', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Show only active and approved properties for public
        if (!$request->filled('include_all')) {
            $query->active();
        }

        // Priority ordering: sponsored > promoted > featured > regular
        $query->orderBy('sponsored', 'desc')
              ->orderBy('promoted', 'desc')
              ->orderBy('featured', 'desc')
              ->orderBy('created_at', 'desc');

        $properties = $query->paginate($request->get('per_page', 12));

        return new PropertyCollection($properties);
    }

    public function featured(Request $request): PropertyCollection
    {
        $properties = Property::with(['user', 'category'])
            ->active()
            ->featured()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return new PropertyCollection($properties);
    }

    public function promoted(Request $request): PropertyCollection
    {
        $properties = Property::with(['user', 'category'])
            ->active()
            ->promoted()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return new PropertyCollection($properties);
    }

    public function sponsored(Request $request): PropertyCollection
    {
        $properties = Property::with(['user', 'category'])
            ->active()
            ->sponsored()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return new PropertyCollection($properties);
    }

    public function show(Property $property): PropertyResource
    {
        // Track view
        PropertyAnalytics::create([
            'property_id' => $property->id,
            'event_type' => 'view',
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'country' => request()->header('CF-IPCountry') ?? null,
        ]);

        // Increment view count
        $property->increment('views_count');

        return new PropertyResource($property->load(['user', 'category', 'upsells']));
    }

    public function store(PropertyStoreRequest $request): PropertyResource
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('properties/cover', 'public');
            }

            // Handle additional images
            if ($request->hasFile('additional_images')) {
                $additionalImages = [];
                foreach ($request->file('additional_images') as $image) {
                    $additionalImages[] = $image->store('properties/additional', 'public');
                }
                $data['additional_images'] = $additionalImages;
            }

            // Handle seller logo upload
            if ($request->hasFile('seller_logo')) {
                $data['seller_logo'] = $request->file('seller_logo')->store('properties/logos', 'public');
            }

            // Set user ID
            $data['user_id'] = Auth::id();

            $property = Property::create($data);

            DB::commit();

            return new PropertyResource($property->load(['user', 'category']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(PropertyUpdateRequest $request, Property $property): PropertyResource
    {
        // Check if user owns this property
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                // Delete old image
                if ($property->cover_image) {
                    Storage::disk('public')->delete($property->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('properties/cover', 'public');
            }

            // Handle additional images
            if ($request->hasFile('additional_images')) {
                $additionalImages = [];
                foreach ($request->file('additional_images') as $image) {
                    $additionalImages[] = $image->store('properties/additional', 'public');
                }
                $data['additional_images'] = $additionalImages;
            }

            // Handle seller logo upload
            if ($request->hasFile('seller_logo')) {
                // Delete old logo
                if ($property->seller_logo) {
                    Storage::disk('public')->delete($property->seller_logo);
                }
                $data['seller_logo'] = $request->file('seller_logo')->store('properties/logos', 'public');
            }

            $property->update($data);

            DB::commit();

            return new PropertyResource($property->load(['user', 'category']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Property $property): JsonResponse
    {
        // Check if user owns this property
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Delete images
            if ($property->cover_image) {
                Storage::disk('public')->delete($property->cover_image);
            }

            if ($property->additional_images) {
                foreach ($property->additional_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            if ($property->seller_logo) {
                Storage::disk('public')->delete($property->seller_logo);
            }

            $property->delete();

            return response()->json(['message' => 'Property deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function myProperties(Request $request): PropertyCollection
    {
        $properties = Property::with(['user', 'category'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return new PropertyCollection($properties);
    }

    public function saveProperty(Property $property): JsonResponse
    {
        $user = Auth::user();

        $saved = $user->savedProperties()->where('property_id', $property->id)->first();

        if ($saved) {
            // Unsave
            $saved->delete();
            $property->decrement('saves_count');

            // Track analytics
            PropertyAnalytics::create([
                'property_id' => $property->id,
                'event_type' => 'save',
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['action' => 'unsave'],
            ]);

            return response()->json(['message' => 'Property removed from saved list']);
        } else {
            // Save
            $user->savedProperties()->create(['property_id' => $property->id]);
            $property->increment('saves_count');

            // Track analytics
            PropertyAnalytics::create([
                'property_id' => $property->id,
                'event_type' => 'save',
                'user_id' => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['action' => 'save'],
            ]);

            return response()->json(['message' => 'Property saved successfully']);
        }
    }

    public function savedProperties(Request $request): PropertyCollection
    {
        $properties = Auth::user()
            ->savedProperties()
            ->with('property.user', 'property.category')
            ->get()
            ->pluck('property');

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $properties->forPage($request->get('page', 1), $request->get('per_page', 12)),
            $properties->count(),
            $request->get('per_page', 12),
            $request->get('page', 1),
            ['path' => $request->url()]
        );

        return new PropertyCollection($paginated);
    }

    public function contactAgent(Property $property, Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'phone' => 'nullable|string|max:20',
        ]);

        // Track analytics
        PropertyAnalytics::create([
            'property_id' => $property->id,
            'event_type' => 'contact_agent',
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => [
                'message' => $request->message,
                'phone' => $request->phone,
            ],
        ]);

        // Increment inquiry count
        $property->increment('inquiries_count');

        // TODO: Send email notification to property owner
        // TODO: Send confirmation email to user

        return response()->json(['message' => 'Message sent to agent successfully']);
    }

    public function trackEvent(Property $property, Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|in:map_view,video_play,gallery_view,phone_click,share',
            'metadata' => 'nullable|array',
        ]);

        PropertyAnalytics::create([
            'property_id' => $property->id,
            'event_type' => $request->event_type,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $request->metadata,
        ]);

        return response()->json(['message' => 'Event tracked successfully']);
    }

    public function getPropertyTypes(): JsonResponse
    {
        return response()->json(Property::getPropertyTypes());
    }

    public function getCategories(): JsonResponse
    {
        return response()->json(Property::getCategories());
    }

    public function getCommercialTypes(): JsonResponse
    {
        return response()->json(Property::getCommercialTypes());
    }

    public function getLandTypes(): JsonResponse
    {
        return response()->json(Property::getLandTypes());
    }

    public function getPlanningPermissions(): JsonResponse
    {
        return response()->json(Property::getPlanningPermissions());
    }

    public function getViewTypes(): JsonResponse
    {
        return response()->json(Property::getViewTypes());
    }
}
