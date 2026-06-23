<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehiclesAdvertController extends Controller
{
    /**
     * Display a listing of vehicle adverts with filters.
     */
    public function index(Request $request)
    {
        $query = Vehicle::query()->published();

        // Filters - only apply if not empty
        if ($request->filled('vehicle_type')) {
            // vehicles table doesn't have vehicle_type, skip this filter
        }

        if ($request->filled('category')) {
            $query->where('advert_type', $request->category);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('make')) {
            // vehicles table uses make_id, skip this filter
        }

        if ($request->filled('model')) {
            // vehicles table uses model_id, skip this filter
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->filled('min_year')) {
            $query->where('year', '>=', $request->min_year);
        }

        if ($request->filled('max_year')) {
            $query->where('year', '<=', $request->max_year);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        if ($request->filled('body_type')) {
            $query->where('body_type', $request->body_type);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('tagline', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'views') {
            $query->orderBy('views', $sortOrder);
        } elseif ($sortBy === 'year') {
            $query->orderBy('year', $sortOrder);
        } elseif ($sortBy === 'mileage') {
            $query->orderBy('mileage', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 20);
        $vehicles = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $vehicles->items(),
            'meta' => [
                'current_page' => $vehicles->currentPage(),
                'per_page' => $vehicles->perPage(),
                'total' => $vehicles->total(),
                'last_page' => $vehicles->lastPage(),
            ],
        ]);
    }

    /**
     * Display featured vehicle adverts.
     */
    public function featured(Request $request)
    {
        $vehicles = Vehicle::published()
            ->where(function($query) {
                $query->where('is_featured', true)
                      ->orWhere('is_sponsored', true)
                      ->orWhere('is_promoted', true)
                      ->orWhere('is_top_of_category', true);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $vehicles->items(),
            'meta' => [
                'current_page' => $vehicles->currentPage(),
                'per_page' => $vehicles->perPage(),
                'total' => $vehicles->total(),
                'last_page' => $vehicles->lastPage(),
            ],
        ]);
    }

    /**
     * Display most viewed vehicle adverts.
     */
    public function mostViewed(Request $request)
    {
        $vehicles = Vehicle::published()
            ->orderBy('views', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $vehicles->items(),
            'meta' => [
                'current_page' => $vehicles->currentPage(),
                'per_page' => $vehicles->perPage(),
                'total' => $vehicles->total(),
                'last_page' => $vehicles->lastPage(),
            ],
        ]);
    }

    /**
     * Display recent vehicle adverts.
     */
    public function recent(Request $request)
    {
        $vehicles = Vehicle::published()
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $vehicles->items(),
            'meta' => [
                'current_page' => $vehicles->currentPage(),
                'per_page' => $vehicles->perPage(),
                'total' => $vehicles->total(),
                'last_page' => $vehicles->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified vehicle advert.
     */
    public function show($id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->published()
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle advert not found',
            ], 404);
        }

        // Increment view count
        $vehicle->increment('views');

        return response()->json([
            'success' => true,
            'data' => $vehicle,
        ]);
    }

    /**
     * Display vehicle by slug.
     */
    public function showBySlug($slug)
    {
        $vehicle = Vehicle::where('slug', $slug)
            ->published()
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle advert not found',
            ], 404);
        }

        // Increment view count
        $vehicle->increment('views');

        return response()->json([
            'success' => true,
            'data' => $vehicle,
        ]);
    }

    /**
     * Store a newly created vehicle advert.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:500'],
            'description' => ['required', 'string'],
            'advert_type' => ['required', 'in:sale,hire,lease,transport_service'],
            'condition' => ['required', 'in:new,used,excellent,good,fair'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'mileage' => ['nullable', 'integer'],
            'fuel_type' => ['nullable', 'string'],
            'transmission' => ['nullable', 'string'],
            'engine_size' => ['nullable', 'string'],
            'color' => ['nullable', 'string'],
            'doors' => ['nullable', 'integer'],
            'seats' => ['nullable', 'integer'],
            'body_type' => ['nullable', 'string'],
            'vin' => ['nullable', 'string'],
            'registration_number' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_type' => ['nullable', 'in:fixed,per_day,per_week,per_month,per_hour'],
            'negotiable' => ['boolean'],
            'deposit' => ['nullable', 'numeric'],
            'main_image' => ['nullable', 'string'],
            'additional_images' => ['nullable', 'array'],
            'video_link' => ['nullable', 'url'],
            'country' => ['required', 'string'],
            'city' => ['required', 'string'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'show_exact_location' => ['boolean'],
            'contact_name' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email'],
            'website' => ['nullable', 'url'],
            'features' => ['nullable', 'array'],
            'service_history' => ['nullable', 'string'],
            'mot_expiry' => ['nullable', 'string'],
            'road_tax_status' => ['nullable', 'string'],
            'previous_owners' => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vehicle = Vehicle::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id ?? 1,
            'make_id' => $request->make_id ?? 1,
            'model_id' => $request->model_id ?? 1,
            'title' => $request->title,
            'tagline' => $request->tagline,
            'description' => $request->description,
            'advert_type' => $request->advert_type,
            'condition' => $request->condition,
            'year' => $request->year,
            'mileage' => $request->mileage,
            'fuel_type' => $request->fuel_type,
            'transmission' => $request->transmission,
            'engine_size' => $request->engine_size,
            'color' => $request->color,
            'doors' => $request->doors,
            'seats' => $request->seats,
            'body_type' => $request->body_type,
            'vin' => $request->vin,
            'registration_number' => $request->registration_number,
            'price' => $request->price,
            'price_type' => $request->price_type ?? 'fixed',
            'negotiable' => $request->negotiable ?? false,
            'deposit' => $request->deposit,
            'main_image' => $request->main_image,
            'additional_images' => $request->additional_images,
            'video_link' => $request->video_link,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'show_exact_location' => $request->show_exact_location ?? true,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'website' => $request->website,
            'features' => $request->features,
            'service_history' => $request->service_history,
            'mot_expiry' => $request->mot_expiry,
            'road_tax_status' => $request->road_tax_status,
            'previous_owners' => $request->previous_owners,
            'status' => 'pending',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle advert created successfully',
            'data' => $vehicle,
        ], 201);
    }

    /**
     * Update the specified vehicle advert.
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle advert not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'vehicle_type' => ['in:car,van,motorbike,truck,bus,coach,electric_vehicle,classic_car,luxury_vehicle,caravan,motorhome,boat,jet_ski,agricultural,construction,other'],
            'category' => ['in:sale,hire,lease'],
            'make' => ['string', 'max:100'],
            'model' => ['string', 'max:100'],
            'year' => ['integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'condition' => ['in:new,used,certified_pre_owned,refurbished'],
            'fuel_type' => ['in:petrol,diesel,electric,hybrid,lpg,other'],
            'transmission' => ['in:automatic,manual,cvt,dual_clutch,other'],
            'body_type' => ['in:sedan,hatchback,suv,coupe,convertible,wagon,pickup,van,truck,bus,motorbike,other'],
            'price' => ['numeric', 'min:0'],
            'currency' => ['string', 'size:3'],
            'title' => ['string', 'max:255'],
            'description' => ['string'],
            'country' => ['string', 'max:100'],
            'city' => ['string', 'max:100'],
            'main_image' => ['string'],
            'images' => ['array'],
            'images.*' => ['string'],
            'contact_name' => ['string', 'max:255'],
            'phone_number' => ['string', 'max:20'],
            'email' => ['email'],
            'promotion_tier' => ['in:standard,promoted,featured,sponsored,top_of_category,network_boost'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Update slug if title changed
        if ($request->has('title') && $request->title !== $vehicle->title) {
            $vehicle->slug = $this->generateUniqueSlug($request->title);
        }

        // Update promotion price if tier changed
        if ($request->has('promotion_tier') && $request->promotion_tier !== $vehicle->promotion_tier) {
            $vehicle->promotion_price = $this->calculatePromotionPrice($request->promotion_tier);
        }

        $vehicle->update($request->except(['slug', 'promotion_price']));

        return response()->json([
            'success' => true,
            'message' => 'Vehicle advert updated successfully',
            'data' => $vehicle->fresh(),
        ]);
    }

    /**
     * Remove the specified vehicle advert.
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle advert not found',
            ], 404);
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle advert deleted successfully',
        ]);
    }

    /**
     * Get authenticated user's vehicle adverts.
     */
    public function myVehicles()
    {
        $vehicles = Vehicle::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $vehicles->items(),
            'meta' => [
                'current_page' => $vehicles->currentPage(),
                'per_page' => $vehicles->perPage(),
                'total' => $vehicles->total(),
                'last_page' => $vehicles->lastPage(),
            ],
        ]);
    }

    /**
     * Save/bookmark a vehicle advert.
     */
    public function saveVehicle($id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->published()
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle advert not found',
            ], 404);
        }

        $vehicle->increment('save_count');

        return response()->json([
            'success' => true,
            'message' => 'Vehicle advert saved successfully',
        ]);
    }

    /**
     * Track views for a vehicle advert.
     */
    public function trackViews($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle advert not found',
            ], 404);
        }

        $vehicle->increment('views');

        return response()->json([
            'success' => true,
            'message' => 'View tracked successfully',
            'views' => $vehicle->views,
        ]);
    }

    /**
     * Contact vehicle seller.
     */
    public function contactSeller(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string'],
            'message' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vehicle = Vehicle::where('id', $id)
            ->published()
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle advert not found',
            ], 404);
        }

        $vehicle->increment('contact_count');

        // Here you would send an email notification to the seller
        // For now, just return success

        return response()->json([
            'success' => true,
            'message' => 'Contact message sent successfully',
        ]);
    }

    /**
     * Process payment for vehicle advert promotion.
     */
    public function processPayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => ['required', 'string'],
            'transaction_id' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vehicle = Vehicle::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle advert not found',
            ], 404);
        }

        // Update promotion dates
        $vehicle->update([
            'promotion_start' => now(),
            'promotion_end' => now()->addDays(30),
            'status' => 'active',
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => $vehicle->fresh(),
        ]);
    }

    /**
     * Upload vehicle image.
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('vehicles', 'public');
            $url = Storage::url($path);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => $url,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file provided',
        ], 400);
    }

    /**
     * Get vehicle types from database.
     */
    public function getVehicleTypes()
    {
        // Return the allowed vehicle types from validation rules
        $vehicleTypes = [
            'car' => 'Cars',
            'van' => 'Vans',
            'motorbike' => 'Motorcycles',
            'truck' => 'Trucks',
            'bus' => 'Buses',
            'coach' => 'Coaches',
            'electric_vehicle' => 'Electric Vehicles',
            'classic_car' => 'Classic Cars',
            'luxury_vehicle' => 'Luxury Vehicles',
            'caravan' => 'Caravans',
            'motorhome' => 'Motorhomes',
            'boat' => 'Boats',
            'jet_ski' => 'Jet Skis',
            'agricultural' => 'Agricultural Vehicles',
            'construction' => 'Construction Vehicles',
            'other' => 'Other'
        ];
        
        return response()->json(['data' => $vehicleTypes]);
    }

    /**
     * Get vehicle categories from database (for form - array format).
     */
    public function getCategories()
    {
        $categories = \App\Models\VehicleCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json(['data' => $categories->toArray()]);
    }

    /**
     * Get vehicle categories for filters (object format).
     */
    public function getCategoriesForFilters()
    {
        $categories = \App\Models\VehicleCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['slug', 'name']);
        
        $formatted = [];
        foreach ($categories as $category) {
            $formatted[$category->slug] = $category->name;
        }
        
        return response()->json(['data' => $formatted]);
    }

    /**
     * Get promotion tiers from database.
     */
    public function getPromotionTiers()
    {
        $plans = \App\Models\VehiclePricingPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get(['slug', 'name', 'price', 'benefits']);
        
        $formatted = [];
        foreach ($plans as $plan) {
            $formatted[$plan->slug] = [
                'name' => $plan->name,
                'price' => (float) $plan->price,
                'benefits' => json_decode($plan->benefits, true) ?? [],
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Get vehicle makes.
     */
    public function getVehicleMakes()
    {
        $makes = \App\Models\VehicleMake::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
        
        return response()->json([
            'success' => true,
            'data' => $makes->toArray(),
        ]);
    }

    /**
     * Get vehicle models by make.
     */
    public function getVehicleModels($makeId)
    {
        $models = \App\Models\VehicleModel::where('make_id', $makeId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
        
        return response()->json([
            'success' => true,
            'data' => $models->toArray(),
        ]);
    }

    /**
     * Get platform statistics.
     */
    public function getStatistics()
    {
        $stats = [
            'total_vehicles' => Vehicle::published()->count(),
            'total_views' => Vehicle::published()->sum('views'),
            'total_saves' => Vehicle::published()->sum('saves'),
            'total_countries' => Vehicle::published()->distinct('country')->count('country'),
            'featured_vehicles' => Vehicle::published()
                ->where(function ($query) {
                    $query->where('is_featured', true)
                        ->orWhere('is_sponsored', true)
                        ->orWhere('is_promoted', true)
                        ->orWhere('is_top_of_category', true);
                })
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Generate unique slug from title.
     */
    private function generateUniqueSlug($title)
    {
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        $originalSlug = $slug;
        $counter = 1;

        while (Vehicle::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Calculate promotion price based on tier.
     */
    private function calculatePromotionPrice($tier)
    {
        $prices = [
            'standard' => 0,
            'promoted' => 10,
            'featured' => 25,
            'sponsored' => 50,
            'top_of_category' => 100,
            'network_boost' => 150,
        ];

        return $prices[$tier] ?? 0;
    }

    /**
     * Convert ['key' => 'Label'] association into [{id, name, label}, ...]
     */
    protected function mapToOptions(array $map): array
    {
        $out = [];
        foreach ($map as $id => $label) {
            $out[] = ['id' => $id, 'name' => $label, 'label' => $label];
        }
        return $out;
    }
}
