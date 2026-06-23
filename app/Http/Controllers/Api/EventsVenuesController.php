<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventsVenuesAdvert;
use App\Models\EventsVenuesCategory;
use App\Models\EventsVenuesSave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventsVenuesController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advert_type' => 'nullable|in:event,venue',
            'search' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:events_venues_categories,id',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'event_category' => 'nullable|string',
            'venue_type' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'event_date_from' => 'nullable|date',
            'event_date_to' => 'nullable|date|after_or_equal:event_date_from',
            'capacity_min' => 'nullable|integer|min:1',
            'capacity_max' => 'nullable|integer|min:1',
            'family_friendly' => 'nullable|boolean',
            'indoor_outdoor' => 'nullable|boolean',
            'catering_available' => 'nullable|boolean',
            'parking_available' => 'nullable|boolean',
            'verified_only' => 'nullable|boolean',
            'promoted_only' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:created_at,title,price,capacity,views_count,event_date',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = EventsVenuesAdvert::active();

        // Filter by advert type (event or venue)
        if ($request->advert_type) {
            if ($request->advert_type === 'event') {
                $query->events();
            } else {
                $query->venues();
            }
        }

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        // Category filter
        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        // Location filters
        if ($request->country) {
            $query->byCountry($request->country);
        }

        if ($request->city) {
            $query->byCity($request->city);
        }

        // Event-specific filters
        if ($request->event_category) {
            $query->where('event_category', $request->event_category);
        }

        if ($request->event_date_from) {
            $query->where('event_date', '>=', $request->event_date_from);
        }

        if ($request->event_date_to) {
            $query->where('event_date', '<=', $request->event_date_to);
        }

        // Venue-specific filters
        if ($request->venue_type) {
            $query->where('venue_type', $request->venue_type);
        }

        if ($request->capacity_min) {
            $query->where('capacity', '>=', $request->capacity_min);
        }

        if ($request->capacity_max) {
            $query->where('capacity', '<=', $request->capacity_max);
        }

        // Price filter
        if ($request->min_price || $request->max_price) {
            if ($request->min_price) {
                $query->where('ticket_price', '>=', $request->min_price);
            }
            if ($request->max_price) {
                $query->where('ticket_price', '<=', $request->max_price);
            }
        }

        // Boolean filters
        if ($request->has('family_friendly') && $request->family_friendly !== null) {
            $query->where('family_friendly', $request->boolean('family_friendly'));
        }

        if ($request->has('indoor_outdoor') && $request->indoor_outdoor !== null) {
            $query->where('indoor_outdoor', $request->boolean('indoor_outdoor'));
        }

        if ($request->has('catering_available') && $request->catering_available !== null) {
            $query->where('catering_available', $request->boolean('catering_available'));
        }

        if ($request->has('parking_available') && $request->parking_available !== null) {
            $query->where('parking_available', $request->boolean('parking_available'));
        }

        if ($request->verified_only) {
            $query->verified();
        }

        if ($request->promoted_only) {
            $query->promoted();
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Prioritize sponsored/featured/promoted listings
        if (!$request->promoted_only) {
            $query->orderByRaw("FIELD(promotion_tier, 'sponsored', 'network_boost', 'featured', 'promoted', 'basic') ASC");
        }

        $perPage = $request->per_page ?? 12;
        $adverts = $query->paginate($perPage, ['*'], 'page', $request->page ?? 1);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $adverts->items(),
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
                'per_page' => $adverts->perPage(),
                'total' => $adverts->total(),
            ]
        ]);
    }

    public function show($slug)
    {
        $advert = EventsVenuesAdvert::active()->where('slug', $slug)->first();

        if (!$advert) {
            return response()->json([
                'success' => false,
                'message' => 'Advert not found'
            ], 404);
        }

        // Increment view count
        $advert->incrementViews();

        // Check if current user has saved this advert
        $isSaved = false;
        if (auth('api')->check()) {
            $isSaved = EventsVenuesSave::where('user_id', auth('api')->id())
                                      ->where('advert_id', $advert->id)
                                      ->exists();
        }

        $advertData = $advert->toArray();
        $advertData['is_saved'] = $isSaved;

        return response()->json([
            'success' => true,
            'data' => $advertData
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advert_type' => 'required|in:event,venue',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'tagline' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:events_venues_categories,id',
            
            // Event-specific fields
            'event_date' => 'required_if:advert_type,event|nullable|date',
            'event_time' => 'required_if:advert_type,event|nullable|date_format:H:i',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'event_end_time' => 'nullable|date_format:H:i',
            'venue_name' => 'nullable|string|max:255',
            'ticket_price' => 'nullable|numeric|min:0',
            'ticket_currency' => 'nullable|string|size:3',
            'free_event' => 'nullable|boolean',
            'event_category' => 'required_if:advert_type,event|nullable|string',
            
            // Venue-specific fields
            'venue_type' => 'required_if:advert_type,venue|nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'price_range' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            
            // Common fields
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Contact
            'contact_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            
            // Media
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video_url' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            
            // Additional
            'key_features' => 'nullable|array',
            'additional_notes' => 'nullable|string',
            'indoor_outdoor' => 'nullable|boolean',
            'family_friendly' => 'nullable|boolean',
            'catering_available' => 'nullable|boolean',
            'parking_available' => 'nullable|boolean',
            'accessible' => 'nullable|boolean',
            
            // Promotion
            'promotion_tier' => 'nullable|in:basic,promoted,featured,sponsored,network_boost',
            
            // Terms
            'terms_accepted' => 'required|boolean',
            'accurate_info' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = auth('api')->id();
        
        // Generate slug
        $data['slug'] = Str::slug($data['title']) . '-' . time();

        // Handle main image upload
        if ($request->hasFile('main_image')) {
            $image = $request->file('main_image');
            $imagePath = $image->store('events-venues', 'public');
            $data['main_image'] = $imagePath;
        }

        // Handle additional images upload
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('events-venues', 'public');
                $imagePaths[] = $path;
            }
            $data['images'] = $imagePaths;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoPath = $logo->store('events-venues/logos', 'public');
            $data['logo'] = $logoPath;
        }

        // Set default values
        $data['ticket_currency'] = $data['ticket_currency'] ?? 'USD';
        $data['free_event'] = $data['free_event'] ?? false;
        $data['indoor_outdoor'] = $data['indoor_outdoor'] ?? true; // Default to indoor
        $data['family_friendly'] = $data['family_friendly'] ?? false;
        $data['catering_available'] = $data['catering_available'] ?? false;
        $data['parking_available'] = $data['parking_available'] ?? false;
        $data['accessible'] = $data['accessible'] ?? false;
        $data['promotion_tier'] = $data['promotion_tier'] ?? 'basic';
        $data['promotion_price'] = 0;
        $data['is_verified'] = false;
        $data['status'] = 'active';
        $data['is_active'] = true;
        $data['views_count'] = 0;
        $data['saves_count'] = 0;
        $data['enquiries_count'] = 0;

        $advert = EventsVenuesAdvert::create($data);

        // Update category count if category exists
        if ($advert->category_id) {
            $advert->category->increment('adverts_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Advert created successfully',
            'data' => $advert->load(['category', 'user']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $advert = EventsVenuesAdvert::where('user_id', auth('api')->id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'advert_type' => 'in:event,venue',
            'title' => 'string|max:255',
            'description' => 'string',
            'short_description' => 'nullable|string|max:500',
            'tagline' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:events_venues_categories,id',
            
            'event_date' => 'nullable|date',
            'event_time' => 'nullable|date_format:H:i',
            'event_end_date' => 'nullable|date|after_or_equal:event_date',
            'event_end_time' => 'nullable|date_format:H:i',
            'venue_name' => 'nullable|string|max:255',
            'ticket_price' => 'nullable|numeric|min:0',
            'ticket_currency' => 'nullable|string|size:3',
            'free_event' => 'nullable|boolean',
            'event_category' => 'nullable|string',
            
            'venue_type' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'price_range' => 'nullable|string|max:100',
            'amenities' => 'nullable|array',
            
            'country' => 'string|max:100',
            'city' => 'string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            'contact_name' => 'string|max:255',
            'business_name' => 'nullable|string|max:255',
            'email' => 'email',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'video_url' => 'nullable|url',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            
            'key_features' => 'nullable|array',
            'additional_notes' => 'nullable|string',
            'indoor_outdoor' => 'nullable|boolean',
            'family_friendly' => 'nullable|boolean',
            'catering_available' => 'nullable|boolean',
            'parking_available' => 'nullable|boolean',
            'accessible' => 'nullable|boolean',
            
            'promotion_tier' => 'nullable|in:basic,promoted,featured,sponsored,network_boost',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();

        // Handle main image upload
        if ($request->hasFile('main_image')) {
            if ($advert->main_image) {
                Storage::disk('public')->delete($advert->main_image);
            }
            $image = $request->file('main_image');
            $imagePath = $image->store('events-venues', 'public');
            $data['main_image'] = $imagePath;
        }

        // Handle additional images upload
        if ($request->hasFile('images')) {
            if ($advert->images) {
                foreach ($advert->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('events-venues', 'public');
                $imagePaths[] = $path;
            }
            $data['images'] = $imagePaths;
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($advert->logo) {
                Storage::disk('public')->delete($advert->logo);
            }
            $logo = $request->file('logo');
            $logoPath = $logo->store('events-venues/logos', 'public');
            $data['logo'] = $logoPath;
        }

        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $advert->title) {
            $data['slug'] = Str::slug($data['title']) . '-' . time();
        }

        $advert->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Advert updated successfully',
            'data' => $advert->load(['category', 'user']),
        ]);
    }

    public function destroy($id)
    {
        $advert = EventsVenuesAdvert::where('user_id', auth('api')->id())->findOrFail($id);

        // Delete images
        if ($advert->main_image) {
            Storage::disk('public')->delete($advert->main_image);
        }

        if ($advert->images) {
            foreach ($advert->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        if ($advert->logo) {
            Storage::disk('public')->delete($advert->logo);
        }

        // Update category count
        if ($advert->category_id) {
            $advert->category->decrement('adverts_count');
        }

        $advert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Advert deleted successfully',
        ]);
    }

    public function myAdverts(Request $request)
    {
        $adverts = EventsVenuesAdvert::where('user_id', auth('api')->id())
                                    ->with(['category'])
                                    ->orderBy('created_at', 'desc')
                                    ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    public function save(Request $request, $id)
    {
        $advert = EventsVenuesAdvert::findOrFail($id);

        // Check if already saved
        $existingSave = EventsVenuesSave::where('user_id', auth('api')->id())
                                        ->where('advert_id', $id)
                                        ->first();

        if ($existingSave) {
            // Unsave
            $existingSave->delete();
            $advert->decrement('saves_count');

            return response()->json([
                'success' => true,
                'message' => 'Advert unsaved successfully',
                'saved' => false,
            ]);
        } else {
            // Save
            EventsVenuesSave::create([
                'user_id' => auth('api')->id(),
                'advert_id' => $id,
            ]);
            $advert->increment('saves_count');

            return response()->json([
                'success' => true,
                'message' => 'Advert saved successfully',
                'saved' => true,
            ]);
        }
    }

    public function savedAdverts(Request $request)
    {
        $saved = EventsVenuesSave::where('user_id', auth('api')->id())
                                ->with(['advert.category', 'advert.user'])
                                ->orderBy('created_at', 'desc')
                                ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $saved,
        ]);
    }

    public function featured()
    {
        $adverts = EventsVenuesAdvert::active()
                                    ->featured()
                                    ->with(['category', 'user'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get();

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    public function sponsored()
    {
        $adverts = EventsVenuesAdvert::active()
                                    ->sponsored()
                                    ->with(['category', 'user'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get();

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    public function categories()
    {
        $categories = EventsVenuesCategory::active()
                                          ->orderBy('sort_order')
                                          ->orderBy('name')
                                          ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_events' => EventsVenuesAdvert::active()->events()->count(),
            'total_venues' => EventsVenuesAdvert::active()->venues()->count(),
            'total_categories' => EventsVenuesCategory::active()->count(),
            'total_views' => EventsVenuesAdvert::active()->sum('views_count'),
            'total_saves' => EventsVenuesAdvert::active()->sum('saves_count'),
            'featured_count' => EventsVenuesAdvert::active()->featured()->count(),
            'sponsored_count' => EventsVenuesAdvert::active()->sponsored()->count(),
            'verified_count' => EventsVenuesAdvert::active()->verified()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function liveActivity()
    {
        $activities = collect();

        // Recent saves
        $recentSaves = EventsVenuesSave::with(['user', 'advert'])
                                       ->orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get();

        foreach ($recentSaves as $save) {
            $activities->push([
                'type' => 'save',
                'message' => "A user saved {$save->advert->title} in {$save->advert->city}",
                'timestamp' => $save->created_at,
                'data' => $save,
            ]);
        }

        // New adverts
        $newAdverts = EventsVenuesAdvert::active()
                                       ->with(['category', 'user'])
                                       ->orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get();

        foreach ($newAdverts as $advert) {
            $type = $advert->advert_type === 'event' ? 'event' : 'venue';
            $activities->push([
                'type' => $type,
                'message' => "New {$type} added: {$advert->title} in {$advert->city}",
                'timestamp' => $advert->created_at,
                'data' => $advert,
            ]);
        }

        // Highly viewed adverts
        $trending = EventsVenuesAdvert::active()
                                     ->where('views_count', '>', 5)
                                     ->orderBy('views_count', 'desc')
                                     ->limit(3)
                                     ->get();

        foreach ($trending as $advert) {
            $activities->push([
                'type' => 'trending',
                'message' => "A {$advert->advert_type} in {$advert->city} just got {$advert->views_count} views",
                'timestamp' => $advert->updated_at,
                'data' => $advert,
            ]);
        }

        // Sort by timestamp and limit
        $activities = $activities->sortByDesc('timestamp')->take(10)->values();

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $image = $request->file('image');
        $path = $image->store('events-venues', 'public');
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'success' => true,
            'data' => [
                'path' => $path,
                'url' => $url,
            ],
        ]);
    }

    public function promotionTiers()
    {
        $tiers = [
            [
                'name' => 'basic',
                'display_name' => 'Basic',
                'price' => 0,
                'duration_days' => 30,
                'features' => [
                    'Standard listing',
                    'Basic visibility',
                    '7-day validity',
                ],
            ],
            [
                'name' => 'promoted',
                'display_name' => 'Promoted',
                'price' => 29.99,
                'duration_days' => 30,
                'features' => [
                    'Highlighted listing',
                    'Above standard listings',
                    '30-day validity',
                    'Priority in search',
                ],
            ],
            [
                'name' => 'featured',
                'display_name' => 'Featured',
                'price' => 79.99,
                'duration_days' => 30,
                'features' => [
                    'Top of category',
                    'Larger card display',
                    'Email newsletter inclusion',
                    '30-day validity',
                    'Badge display',
                ],
            ],
            [
                'name' => 'sponsored',
                'display_name' => 'Sponsored',
                'price' => 149.99,
                'duration_days' => 30,
                'features' => [
                    'Homepage placement',
                    'Slider rotation',
                    'Social media promotion',
                    '30-day validity',
                    'Premium badge',
                    'Maximum visibility',
                ],
            ],
            [
                'name' => 'network_boost',
                'display_name' => 'Network Boost',
                'price' => 299.99,
                'duration_days' => 30,
                'features' => [
                    'Cross-page visibility',
                    'Newsletter feature',
                    'Social media spotlight',
                    '30-day validity',
                    'Exclusive badge',
                    'Analytics dashboard',
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $tiers,
        ]);
    }
}
