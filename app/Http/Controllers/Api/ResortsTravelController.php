<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResortsTravel;
use App\Models\ResortsTravelCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResortsTravelController extends Controller
{
    public function index(Request $request)
    {
        $query = ResortsTravel::with(['user', 'category'])
            ->active();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Advert type filter
        if ($request->has('advert_type')) {
            $query->byType($request->input('advert_type'));
        }

        // Accommodation type filter
        if ($request->has('accommodation_type')) {
            $query->byAccommodationType($request->input('accommodation_type'));
        }

        // Transport type filter
        if ($request->has('transport_type')) {
            $query->byTransportType($request->input('transport_type'));
        }

        // Experience type filter
        if ($request->has('experience_type')) {
            $query->byExperienceType($request->input('experience_type'));
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Location filter
        if ($request->has('country')) {
            $query->byLocation($request->input('country'), $request->input('city'));
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
                    $query->byAmenity($amenity);
                }
            }
        }

        // Verified filter
        if ($request->has('verified')) {
            $query->verified();
        }

        // Promotion tier filter
        if ($request->has('promotion_tier')) {
            $query->byPromotionTier($request->input('promotion_tier'));
        }

        // Availability filter
        if ($request->has('available_from')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('availability_start')
                  ->orWhere('availability_start', '<=', $request->input('available_from'));
            });
        }

        if ($request->has('available_to')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('availability_end')
                  ->orWhere('availability_end', '>=', $request->input('available_to'));
            });
        }

        // Sort
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        
        switch ($sort) {
            case 'title':
                $query->orderBy('title', $order);
                break;
            case 'price':
                $query->orderByRaw("CASE 
                    WHEN price_per_night IS NOT NULL THEN price_per_night
                    WHEN price_per_trip IS NOT NULL THEN price_per_trip
                    WHEN price_per_service IS NOT NULL THEN price_per_service
                    ELSE 999999
                END {$order}");
                break;
            case 'promotion':
                $query->orderByRaw("FIELD(promotion_tier, 'network_wide', 'sponsored', 'featured', 'promoted', 'standard') {$order}");
                break;
            case 'rating':
                // This would require a ratings system - placeholder for now
                $query->orderBy('created_at', $order);
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $adverts = $query->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    public function show($slug)
    {
        $advert = ResortsTravel::with(['user', 'category'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Increment view count if you want to track views
        // $advert->increment('views');

        return response()->json([
            'success' => true,
            'data' => $advert,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'advert_type' => 'required|in:accommodation,transport,experience',
            'accommodation_type' => 'nullable|required_if:advert_type,accommodation|in:resort,hotel,bnb,guest_house,holiday_home,villa,lodge',
            'transport_type' => 'nullable|required_if:advert_type,transport|in:airport_transfer,taxi_chauffeur,car_hire,shuttle_bus,tour_bus,boat_ferry,motorbike_scooter',
            'experience_type' => 'nullable|required_if:advert_type,experience|in:tours,excursions,adventure_packages,wellness_retreats',
            'category_id' => 'nullable|exists:resorts_travel_categories,id',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'price_per_night' => 'nullable|numeric|min:0',
            'price_per_trip' => 'nullable|numeric|min:0',
            'price_per_service' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'availability_start' => 'nullable|date|after_or_equal:today',
            'availability_end' => 'nullable|date|after_or_equal:availability_start',
            'room_types' => 'nullable|array',
            'room_types.*' => 'string',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'distance_to_city_centre' => 'nullable|integer|min:0',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'guest_capacity' => 'nullable|integer|min:1',
            'vehicle_type' => 'nullable|string|max:100',
            'passenger_capacity' => 'nullable|integer|min:1',
            'luggage_capacity' => 'nullable|integer|min:0',
            'service_area' => 'nullable|string',
            'operating_hours' => 'nullable|array',
            'airport_pickup' => 'boolean',
            'duration' => 'nullable|string|max:100',
            'group_size' => 'nullable|integer|min:1',
            'whats_included' => 'nullable|string',
            'what_to_bring' => 'nullable|string',
            'description' => 'required|string',
            'overview' => 'nullable|string',
            'key_features' => 'nullable|string',
            'why_travellers_love_this' => 'nullable|string',
            'nearby_attractions' => 'nullable|string',
            'additional_notes' => 'nullable|string',
            'contact_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'logo' => 'nullable|string',
            'verified_business' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'main_image' => 'nullable|string',
            'video_link' => 'nullable|url',
            'promotion_tier' => 'nullable|in:standard,promoted,featured,sponsored,network_wide',
            'is_approximate_location' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['promotion_tier'] = $validated['promotion_tier'] ?? 'standard';
        $validated['currency'] = $validated['currency'] ?? 'GBP';
        $validated['verified_business'] = $validated['verified_business'] ?? false;
        $validated['airport_pickup'] = $validated['airport_pickup'] ?? false;
        $validated['is_approximate_location'] = $validated['is_approximate_location'] ?? false;

        // Generate unique slug
        $validated['slug'] = $this->generateUniqueSlug($validated['title']);

        $advert = ResortsTravel::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resorts & Travel advert created successfully',
            'data' => $advert->load(['user', 'category']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $advert = ResortsTravel::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'tagline' => 'sometimes|nullable|string|max:255',
            'advert_type' => 'sometimes|in:accommodation,transport,experience',
            'accommodation_type' => 'sometimes|nullable|required_if:advert_type,accommodation|in:resort,hotel,bnb,guest_house,holiday_home,villa,lodge',
            'transport_type' => 'sometimes|nullable|required_if:advert_type,transport|in:airport_transfer,taxi_chauffeur,car_hire,shuttle_bus,tour_bus,boat_ferry,motorbike_scooter',
            'experience_type' => 'sometimes|nullable|required_if:advert_type,experience|in:tours,excursions,adventure_packages,wellness_retreats',
            'category_id' => 'sometimes|nullable|exists:resorts_travel_categories,id',
            'country' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'address' => 'sometimes|nullable|string',
            'latitude' => 'sometimes|nullable|numeric|between:-90,90',
            'longitude' => 'sometimes|nullable|numeric|between:-180,180',
            'price_per_night' => 'sometimes|nullable|numeric|min:0',
            'price_per_trip' => 'sometimes|nullable|numeric|min:0',
            'price_per_service' => 'sometimes|nullable|numeric|min:0',
            'currency' => 'sometimes|nullable|string|size:3',
            'availability_start' => 'sometimes|nullable|date|after_or_equal:today',
            'availability_end' => 'sometimes|nullable|date|after_or_equal:availability_start',
            'room_types' => 'sometimes|nullable|array',
            'room_types.*' => 'string',
            'amenities' => 'sometimes|nullable|array',
            'amenities.*' => 'string',
            'distance_to_city_centre' => 'sometimes|nullable|integer|min:0',
            'check_in_time' => 'sometimes|nullable|date_format:H:i',
            'check_out_time' => 'sometimes|nullable|date_format:H:i',
            'guest_capacity' => 'sometimes|nullable|integer|min:1',
            'vehicle_type' => 'sometimes|nullable|string|max:100',
            'passenger_capacity' => 'sometimes|nullable|integer|min:1',
            'luggage_capacity' => 'sometimes|nullable|integer|min:0',
            'service_area' => 'sometimes|nullable|string',
            'operating_hours' => 'sometimes|nullable|array',
            'airport_pickup' => 'sometimes|boolean',
            'duration' => 'sometimes|nullable|string|max:100',
            'group_size' => 'sometimes|nullable|integer|min:1',
            'whats_included' => 'sometimes|nullable|string',
            'what_to_bring' => 'sometimes|nullable|string',
            'description' => 'sometimes|string',
            'overview' => 'sometimes|nullable|string',
            'key_features' => 'sometimes|nullable|string',
            'why_travellers_love_this' => 'sometimes|nullable|string',
            'nearby_attractions' => 'sometimes|nullable|string',
            'additional_notes' => 'sometimes|nullable|string',
            'contact_name' => 'sometimes|string|max:255',
            'business_name' => 'sometimes|nullable|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'email' => 'sometimes|email',
            'website' => 'sometimes|nullable|url',
            'social_links' => 'sometimes|nullable|array',
            'social_links.*' => 'url',
            'logo' => 'sometimes|nullable|string',
            'verified_business' => 'sometimes|boolean',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'string',
            'main_image' => 'sometimes|nullable|string',
            'video_link' => 'sometimes|nullable|url',
            'promotion_tier' => 'sometimes|in:standard,promoted,featured,sponsored,network_wide',
            'is_approximate_location' => 'sometimes|boolean',
        ]);

        // Update slug if title changed
        if (isset($validated['title']) && $validated['title'] !== $advert->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $advert->id);
        }

        $advert->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Resorts & Travel advert updated successfully',
            'data' => $advert->load(['user', 'category']),
        ]);
    }

    public function destroy($id)
    {
        $advert = ResortsTravel::where('user_id', Auth::id())->findOrFail($id);
        $advert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Resorts & Travel advert deleted successfully',
        ]);
    }

    public function myAdverts(Request $request)
    {
        $adverts = ResortsTravel::with(['category'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    public function featuredAdverts()
    {
        $adverts = ResortsTravel::with(['user', 'category'])
            ->active()
            ->whereIn('promotion_tier', ['featured', 'sponsored', 'network_wide'])
            ->orderByRaw("FIELD(promotion_tier, 'network_wide', 'sponsored', 'featured')")
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    public function advertTypes()
    {
        $types = [
            'accommodation' => [
                'name' => 'Accommodation',
                'subtypes' => [
                    'resort' => 'Resort',
                    'hotel' => 'Hotel',
                    'bnb' => 'B&B',
                    'guest_house' => 'Guest House',
                    'holiday_home' => 'Holiday Home',
                    'villa' => 'Villa',
                    'lodge' => 'Lodge',
                ]
            ],
            'transport' => [
                'name' => 'Transport Services',
                'subtypes' => [
                    'airport_transfer' => 'Airport Transfer',
                    'taxi_chauffeur' => 'Taxi / Chauffeur',
                    'car_hire' => 'Car Hire',
                    'shuttle_bus' => 'Shuttle Bus',
                    'tour_bus' => 'Tour Bus',
                    'boat_ferry' => 'Boat / Ferry',
                    'motorbike_scooter' => 'Motorbike / Scooter Rental',
                ]
            ],
            'experience' => [
                'name' => 'Travel Experiences',
                'subtypes' => [
                    'tours' => 'Tours',
                    'excursions' => 'Excursions',
                    'adventure_packages' => 'Adventure Packages',
                    'wellness_retreats' => 'Wellness Retreats',
                ]
            ],
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
            'pool' => 'Swimming Pool',
            'parking' => 'Parking',
            'breakfast' => 'Breakfast Included',
            'air_conditioning' => 'Air Conditioning',
            'heating' => 'Heating',
            'kitchen' => 'Kitchen',
            'tv' => 'TV',
            'washing_machine' => 'Washing Machine',
            'elevator' => 'Elevator',
            'wheelchair_access' => 'Wheelchair Access',
            'pet_friendly' => 'Pet Friendly',
            'gym' => 'Gym/Fitness Center',
            'spa' => 'Spa/Wellness',
            'restaurant' => 'Restaurant',
            'bar' => 'Bar/Lounge',
            'room_service' => 'Room Service',
            'concierge' => 'Concierge Service',
            'business_center' => 'Business Center',
            'meeting_rooms' => 'Meeting Rooms',
            'airport_shuttle' => 'Airport Shuttle',
            'beach_access' => 'Beach Access',
            'golf_course' => 'Golf Course',
            'tennis_court' => 'Tennis Court',
            'kids_club' => 'Kids Club',
            'babysitting' => 'Babysitting Service',
            'laundry_service' => 'Laundry Service',
            'dry_cleaning' => 'Dry Cleaning',
            'currency_exchange' => 'Currency Exchange',
            'atm' => 'ATM on-site',
            'safety_deposit_box' => 'Safety Deposit Box',
            '24_hour_front_desk' => '24-Hour Front Desk',
            'multilingual_staff' => 'Multilingual Staff',
        ];

        return response()->json([
            'success' => true,
            'data' => $amenities,
        ]);
    }

    public function promotionTiers()
    {
        $tiers = [
            'standard' => [
                'name' => 'Standard',
                'description' => 'Basic listing with standard visibility',
                'price' => 0,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Basic listing',
                    'Standard placement',
                    'Contact information'
                ]
            ],
            'promoted' => [
                'name' => 'Promoted',
                'description' => 'Enhanced visibility with highlighted listing',
                'price' => 29.99,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Highlighted listing',
                    'Appears above standard ads',
                    'Promoted badge',
                    '2× more visibility'
                ]
            ],
            'featured' => [
                'name' => 'Featured',
                'description' => 'Premium placement with maximum exposure',
                'price' => 59.99,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Top of category pages',
                    'Larger advert card',
                    'Priority in search results',
                    'Included in weekly email',
                    'Featured badge',
                    '4× more visibility'
                ],
                'most_popular' => true
            ],
            'sponsored' => [
                'name' => 'Sponsored',
                'description' => 'Ultimate visibility across the platform',
                'price' => 99.99,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    'Sponsored badge',
                    'Maximum visibility'
                ]
            ],
            'network_wide' => [
                'name' => 'Network-Wide Boost',
                'description' => 'Complete network exposure for ultimate reach',
                'price' => 199.99,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Appears across multiple pages',
                    'Homepage placement',
                    'Category pages',
                    'Related search pages',
                    'Email newsletters',
                    'Push notifications',
                    'Top Spotlight badge',
                    'Ultimate visibility'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $tiers,
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
            $path = $image->store('resorts-travel', 'public');
            $uploadedImages[] = $path;
        }

        return response()->json([
            'success' => true,
            'data' => $uploadedImages,
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        $path = $request->file('logo')->store('resorts-travel/logos', 'public');

        return response()->json([
            'success' => true,
            'data' => $path,
        ]);
    }

    private function generateUniqueSlug($title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (ResortsTravel::where('slug', $slug)
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
