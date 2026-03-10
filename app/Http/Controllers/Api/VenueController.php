<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $query = Venue::with(['user', 'events'])
            ->active();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Venue type filter
        if ($request->has('venue_type')) {
            $query->byType($request->input('venue_type'));
        }

        // Location filter
        if ($request->has('country')) {
            $query->byLocation($request->input('country'), $request->input('city'));
        }

        // Capacity filter
        if ($request->has('min_capacity') || $request->has('max_capacity')) {
            $query->byCapacity(
                $request->input('min_capacity'),
                $request->input('max_capacity')
            );
        }

        // Price filter
        if ($request->has('min_price') || $request->has('max_price')) {
            $query->byPriceRange(
                $request->input('min_price'),
                $request->input('max_price')
            );
        }

        // Amenities filter
        if ($request->has('amenities')) {
            $amenities = $request->input('amenities');
            if (is_array($amenities)) {
                foreach ($amenities as $amenity) {
                    $query->whereJsonContains('amenities', $amenity);
                }
            }
        }

        // Features filter
        if ($request->has('indoor')) {
            $query->where('indoor', $request->boolean('indoor'));
        }
        if ($request->has('outdoor')) {
            $query->where('outdoor', $request->boolean('outdoor'));
        }
        if ($request->has('catering_available')) {
            $query->where('catering_available', $request->boolean('catering_available'));
        }
        if ($request->has('parking_available')) {
            $query->where('parking_available', $request->boolean('parking_available'));
        }
        if ($request->has('accessibility')) {
            $query->where('accessibility', $request->boolean('accessibility'));
        }

        // Promotion tier filter
        if ($request->has('promotion_tier')) {
            $query->byPromotionTier($request->input('promotion_tier'));
        }

        // Sort
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        
        switch ($sort) {
            case 'name':
                $query->orderBy('name', $order);
                break;
            case 'capacity':
                $query->orderBy('capacity', $order);
                break;
            case 'price':
                $query->orderBy('min_price', $order);
                break;
            case 'promotion':
                $query->orderByRaw("FIELD(promotion_tier, 'spotlight', 'sponsored', 'featured', 'promoted', 'standard') {$order}");
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $venues = $query->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    public function show($slug)
    {
        $venue = Venue::with(['user', 'events' => function ($query) {
            $query->active()->upcoming()->orderBy('date_time', 'asc')->limit(5);
        }])
        ->where('slug', $slug)
        ->active()
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $venue,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue_type' => 'required|in:wedding_hall,conference_centre,party_hall,outdoor_space,hotel_banquet,bar_restaurant,meeting_room,exhibition_space,sports_venue,other',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'indoor' => 'boolean',
            'outdoor' => 'boolean',
            'catering_available' => 'boolean',
            'parking_available' => 'boolean',
            'accessibility' => 'boolean',
            'opening_hours' => 'nullable|array',
            'booking_link' => 'nullable|url',
            'contact_email' => 'required|email',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'floor_plan' => 'nullable|string',
            'video_link' => 'nullable|url',
            'promotion_tier' => 'nullable|in:standard,promoted,featured,sponsored,spotlight',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['promotion_tier'] = $validated['promotion_tier'] ?? 'standard';

        // Set default boolean values
        $validated['indoor'] = $validated['indoor'] ?? true;
        $validated['outdoor'] = $validated['outdoor'] ?? false;
        $validated['catering_available'] = $validated['catering_available'] ?? false;
        $validated['parking_available'] = $validated['parking_available'] ?? false;
        $validated['accessibility'] = $validated['accessibility'] ?? false;

        $venue = Venue::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Venue created successfully',
            'data' => $venue->load(['user', 'events']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $venue = Venue::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'venue_type' => 'sometimes|in:wedding_hall,conference_centre,party_hall,outdoor_space,hotel_banquet,bar_restaurant,meeting_room,exhibition_space,sports_venue,other',
            'country' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'capacity' => 'sometimes|integer|min:1',
            'min_price' => 'sometimes|nullable|numeric|min:0',
            'max_price' => 'sometimes|nullable|numeric|min:0',
            'description' => 'sometimes|string',
            'amenities' => 'sometimes|nullable|array',
            'amenities.*' => 'string',
            'indoor' => 'sometimes|boolean',
            'outdoor' => 'sometimes|boolean',
            'catering_available' => 'sometimes|boolean',
            'parking_available' => 'sometimes|boolean',
            'accessibility' => 'sometimes|boolean',
            'opening_hours' => 'sometimes|nullable|array',
            'booking_link' => 'sometimes|nullable|url',
            'contact_email' => 'sometimes|email',
            'social_links' => 'sometimes|nullable|array',
            'social_links.*' => 'url',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'string',
            'floor_plan' => 'sometimes|nullable|string',
            'video_link' => 'sometimes|nullable|url',
            'promotion_tier' => 'sometimes|in:standard,promoted,featured,sponsored,spotlight',
        ]);

        $venue->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Venue updated successfully',
            'data' => $venue->load(['user', 'events']),
        ]);
    }

    public function destroy($id)
    {
        $venue = Venue::where('user_id', Auth::id())->findOrFail($id);
        $venue->delete();

        return response()->json([
            'success' => true,
            'message' => 'Venue deleted successfully',
        ]);
    }

    public function myVenues(Request $request)
    {
        $venues = Venue::with(['events'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    public function featuredVenues()
    {
        $venues = Venue::with(['user'])
            ->active()
            ->whereIn('promotion_tier', ['featured', 'sponsored', 'spotlight'])
            ->orderByRaw("FIELD(promotion_tier, 'spotlight', 'sponsored', 'featured')")
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $venues,
        ]);
    }

    public function venueTypes()
    {
        $types = [
            'wedding_hall' => 'Wedding Venues',
            'conference_centre' => 'Conference Centres',
            'party_hall' => 'Party Halls',
            'outdoor_space' => 'Outdoor Spaces',
            'hotel_banquet' => 'Hotels & Banquet Rooms',
            'bar_restaurant' => 'Bars & Restaurants',
            'meeting_room' => 'Meeting Rooms',
            'exhibition_space' => 'Exhibition Spaces',
            'sports_venue' => 'Sports Venues',
            'other' => 'Other',
        ];

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function amenities()
    {
        $amenities = [
            'wi_fi' => 'Wi-Fi',
            'parking' => 'Parking',
            'catering' => 'Catering',
            'av_equipment' => 'AV Equipment',
            'air_conditioning' => 'Air Conditioning',
            'heating' => 'Heating',
            'sound_system' => 'Sound System',
            'lighting' => 'Lighting',
            'stage' => 'Stage',
            'dance_floor' => 'Dance Floor',
            'bar' => 'Bar',
            'kitchen' => 'Kitchen',
            'restrooms' => 'Restrooms',
            'wheelchair_access' => 'Wheelchair Access',
            'elevator' => 'Elevator',
            'security' => 'Security',
        ];

        return response()->json([
            'success' => true,
            'data' => $amenities,
        ]);
    }

    public function uploadImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('venues', 'public');
            $uploadedImages[] = Storage::url($path);
        }

        return response()->json([
            'success' => true,
            'data' => $uploadedImages,
        ]);
    }

    public function uploadFloorPlan(Request $request)
    {
        $request->validate([
            'floor_plan' => 'required|image|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $path = $request->file('floor_plan')->store('venue-floor-plans', 'public');

        return response()->json([
            'success' => true,
            'data' => Storage::url($path),
        ]);
    }
}
