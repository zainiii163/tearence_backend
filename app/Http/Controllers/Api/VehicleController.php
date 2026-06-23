<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\VehicleCollection;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleFavourite;
use App\Models\VehicleEnquiry;
use App\Models\User;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Requests\VehicleEnquiryRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VehicleController extends Controller
{
    public function index(Request $request): VehicleCollection
    {
        $query = Vehicle::with([
            'category', 
            'make', 
            'vehicleModel', 
            'user',
            'business'
        ])->published();

        // Filters
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('advert_type')) {
            $query->advertType($request->advert_type);
        }

        if ($request->has('make')) {
            $query->whereHas('make', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->make . '%');
            });
        }

        if ($request->has('model')) {
            $query->whereHas('vehicleModel', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->model . '%');
            });
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('min_year')) {
            $query->where('year', '>=', $request->min_year);
        }

        if ($request->has('max_year')) {
            $query->where('year', '<=', $request->max_year);
        }

        if ($request->has('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->has('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Featured/Promoted/Sponsored vehicles
        if ($request->has('featured')) {
            $query->featured();
        }

        if ($request->has('promoted')) {
            $query->promoted();
        }

        if ($request->has('sponsored')) {
            $query->sponsored();
        }

        if ($request->has('top_of_category')) {
            $query->topOfCategory();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('make', function($subQ) use ($search) {
                      $subQ->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('vehicleModel', function($subQ) use ($search) {
                      $subQ->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = [
            'created_at', 'price', 'year', 'mileage', 'views', 'saves', 'enquiries',
            'title', 'make', 'model'
        ];
        
        if (in_array($sortBy, $allowedSorts)) {
            if ($sortBy === 'make') {
                $query->orderByHas('make', 'name', $sortOrder);
            } elseif ($sortBy === 'model') {
                $query->orderByHas('vehicleModel', 'name', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        // Priority ordering for upgraded vehicles
        if ($request->has('with_priority') && $request->with_priority) {
            $query->orderByRaw('is_top_of_category DESC, is_sponsored DESC, is_featured DESC, is_promoted DESC');
        }

        $vehicles = $query->paginate($request->get('per_page', 12));

        return new VehicleCollection($vehicles);
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = auth('api')->user();
            $userId = $user ? ($user instanceof \App\Models\Customer ? $user->customer_id : $user->id) : null;
            
            if (!$userId) {
                return response()->json(['message' => 'Unauthorized - No authenticated user found'], 401);
            }

            $vehicleData = [
                'user_id' => $userId,
                'business_id' => $request->business_id,
                'category_id' => $request->category_id,
                'make_id' => $request->make_id,
                'model_id' => $request->model_id,
                'custom_model' => $request->custom_model,
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
                
                // Commercial vehicle fields
                'payload_capacity' => $request->payload_capacity,
                'axles' => $request->axles,
                'emission_class' => $request->emission_class,
                
                // Boat fields
                'length' => $request->length,
                'engine_type' => $request->engine_type,
                'capacity' => $request->capacity,
                'trailer_included' => $request->trailer_included ?? false,
                
                // Transport service fields
                'service_area' => $request->service_area,
                'operating_hours' => $request->operating_hours,
                'passenger_capacity' => $request->passenger_capacity,
                'luggage_capacity' => $request->luggage_capacity,
                'airport_pickup' => $request->airport_pickup ?? false,
                
                // Pricing
                'price' => $request->price,
                'price_type' => $request->price_type,
                'negotiable' => $request->negotiable ?? false,
                'deposit' => $request->deposit,
                
                // Media
                'video_link' => $request->video_link,
                
                // Location
                'country' => $request->country,
                'city' => $request->city,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'show_exact_location' => $request->show_exact_location ?? true,
                
                // Contact
                'contact_name' => $request->contact_name,
                'contact_phone' => $request->contact_phone,
                'contact_email' => $request->contact_email,
                'website' => $request->website,
                
                // Additional
                'features' => $request->features,
                'service_history' => $request->service_history,
                'mot_expiry' => $request->mot_expiry,
                'road_tax_status' => $request->road_tax_status,
                'previous_owners' => $request->previous_owners,
                
                'status' => 'approved',
                'is_active' => true,
            ];

            // Handle main image (file upload or pre-uploaded path)
            if ($request->hasFile('main_image')) {
                $image = $request->file('main_image');
                $path = $image->store('/', 'vehicles');
                $vehicleData['main_image'] = basename($path);
            } elseif (is_string($request->input('main_image')) && trim($request->input('main_image')) !== '') {
                $vehicleData['main_image'] = basename($request->input('main_image'));
            } else {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => ['main_image' => ['Main vehicle image is required.']],
                ], 422);
            }

            // Handle additional images
            if ($request->hasFile('additional_images')) {
                $additionalImages = [];
                foreach ($request->file('additional_images') as $image) {
                    $path = $image->store('/', 'vehicles');
                    $additionalImages[] = basename($path);
                }
                $vehicleData['additional_images'] = $additionalImages;
            }

            $vehicle = Vehicle::create($vehicleData);

            // Handle upgrades
            if ($request->has('upgrade_type')) {
                $this->processUpgrade($vehicle, $request->upgrade_type, $request->all());
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle created successfully',
                'data' => new VehicleResource($vehicle->load([
                    'category', 'make', 'vehicleModel', 'user', 'business'
                ]))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to create vehicle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        if (!$vehicle->isPublishedOnWebsite()) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        // Increment view count
        $vehicle->incrementViews();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle retrieved successfully',
            'data' => new VehicleResource($vehicle->load([
                'category', 'make', 'vehicleModel', 'user', 'business', 
                'favourites', 'analytics'
            ]))
        ], 200);
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $user = auth('api')->user();
        $userId = $user ? ($user instanceof \App\Models\Customer ? $user->customer_id : $user->id) : null;
        
        if (!$userId || $vehicle->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $vehicleData = $request->validated();

            // Handle main image
            if ($request->hasFile('main_image')) {
                // Delete old image
                if ($vehicle->main_image) {
                    Storage::disk('vehicles')->delete($vehicle->main_image);
                }

                $image = $request->file('main_image');
                $path = $image->store('/', 'vehicles');
                $vehicleData['main_image'] = basename($path);
            }

            // Handle additional images
            if ($request->hasFile('additional_images')) {
                $additionalImages = [];
                foreach ($request->file('additional_images') as $image) {
                    $path = $image->store('/', 'vehicles');
                    $additionalImages[] = basename($path);
                }
                $vehicleData['additional_images'] = $additionalImages;
            }

            $vehicle->update($vehicleData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated successfully',
                'data' => new VehicleResource($vehicle->load([
                    'category', 'make', 'vehicleModel', 'user', 'business'
                ]))
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to update vehicle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $user = auth('api')->user();
        $userId = $user ? ($user instanceof \App\Models\Customer ? $user->customer_id : $user->id) : null;
        
        if (!$userId || $vehicle->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete images
        if ($vehicle->main_image) {
            Storage::disk('vehicles')->delete($vehicle->main_image);
        }

        if ($vehicle->additional_images) {
            foreach ($vehicle->additional_images as $image) {
                Storage::disk('vehicles')->delete($image);
            }
        }

        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vehicle deleted successfully'
        ]);
    }

    public function getStats(): JsonResponse
    {
        $stats = [
            'total_vehicles' => Vehicle::published()->count(),
            'featured_vehicles' => Vehicle::published()->where('is_featured', true)->count(),
            'total_categories' => VehicleCategory::where('is_active', true)->count(),
            'total_makes' => VehicleMake::where('is_active', true)->count(),
            'recent_vehicles' => Vehicle::published()->orderBy('created_at', 'desc')->take(5)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function checkFavourite(Vehicle $vehicle): JsonResponse
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'data' => ['is_favourited' => false]
            ]);
        }
        
        $userId = $user instanceof \App\Models\Customer ? $user->customer_id : $user->id;
        $isFavourited = $vehicle->favourites()->where('user_id', $userId)->exists();
        
        return response()->json([
            'success' => true,
            'data' => ['is_favourited' => $isFavourited]
        ]);
    }

    public function saveVehicle(Vehicle $vehicle): JsonResponse
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $userId = $user instanceof \App\Models\Customer ? $user->customer_id : $user->id;
        $favourite = $vehicle->favourites()->where('user_id', $userId)->first();
        
        if ($favourite) {
            $favourite->delete();
            $vehicle->decrement('saves');
            
            return response()->json([
                'success' => true,
                'message' => 'Vehicle removed from saved',
                'data' => ['saved' => false]
            ]);
        } else {
            $vehicle->favourites()->create(['user_id' => $userId]);
            $vehicle->increment('saves');
            
            return response()->json([
                'success' => true,
                'message' => 'Vehicle saved successfully',
                'data' => ['saved' => true]
            ]);
        }
    }

    public function myVehicles(Request $request): VehicleCollection
    {
        $user = auth('api')->user();
        $userId = $user ? ($user instanceof \App\Models\Customer ? $user->customer_id : $user->id) : null;
        
        $vehicles = Vehicle::where('user_id', $userId)
            ->with(['category', 'make', 'vehicleModel', 'business'])
            ->paginate($request->get('per_page', 12));

        return new VehicleCollection($vehicles);
    }

    public function savedVehicles(Request $request): VehicleCollection
    {
        $user = auth('api')->user();
        $userId = $user ? ($user instanceof \App\Models\Customer ? $user->customer_id : $user->id) : null;
        
        $vehicles = Vehicle::whereHas('favourites', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['category', 'make', 'vehicleModel', 'user', 'business'])
          ->paginate($request->get('per_page', 12));

        return new VehicleCollection($vehicles);
    }

    public function toggleStatus(Vehicle $vehicle): JsonResponse
    {
        $user = auth('api')->user();
        $userId = $user ? ($user instanceof \App\Models\Customer ? $user->customer_id : $user->id) : null;
        
        if ($vehicle->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vehicle->is_active = !$vehicle->is_active;
        $vehicle->save();

        return response()->json([
            'message' => 'Vehicle status updated',
            'is_active' => $vehicle->is_active
        ]);
    }

    public function markAsSold(Vehicle $vehicle): JsonResponse
    {
        $user = auth('api')->user();
        $userId = $user ? ($user instanceof \App\Models\Customer ? $user->customer_id : $user->id) : null;
        
        if ($vehicle->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $vehicle->update([
            'status' => 'sold',
            'is_active' => false
        ]);

        return response()->json(['message' => 'Vehicle marked as sold']);
    }

    public function createEnquiry(VehicleEnquiryRequest $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $enquiry = $vehicle->enquiries()->create([
                'user_id' => auth('api')->id(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'message' => $request->message,
                'status' => 'pending'
            ]);

            $vehicle->incrementEnquiries();

            return response()->json([
                'message' => 'Enquiry sent successfully',
                'enquiry' => $enquiry
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send enquiry',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMakes(Request $request): JsonResponse
    {
        $makes = VehicleMake::active()
            ->when($request->has('search'), function($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->ordered()
            ->get();

        return response()->json($makes);
    }

    public function getModels(Request $request, $makeId): JsonResponse
    {
        $models = VehicleModel::where('make_id', $makeId)
            ->active()
            ->when($request->has('search'), function($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->ordered()
            ->get();

        return response()->json($models);
    }

    public function getCategories(Request $request): JsonResponse
    {
        $categories = VehicleCategory::active()
            ->ordered()
            ->withCount(['vehicles' => function($query) {
                $query->published();
            }])
            ->get();

        return response()->json($categories);
    }

    public function getFeaturedVehicles(Request $request): VehicleCollection
    {
        $vehicles = Vehicle::featured()
            ->active()
            ->with(['category', 'make', 'vehicleModel', 'user', 'business'])
            ->paginate($request->get('per_page', 12));

        return new VehicleCollection($vehicles);
    }

    public function getRecentVehicles(Request $request): VehicleCollection
    {
        $vehicles = Vehicle::active()
            ->orderBy('created_at', 'desc')
            ->with(['category', 'make', 'vehicleModel', 'user', 'business'])
            ->paginate($request->get('per_page', 12));

        return new VehicleCollection($vehicles);
    }

    public function getRelatedVehicles(Vehicle $vehicle, Request $request): VehicleCollection
    {
        $vehicles = Vehicle::active()
            ->where('id', '!=', $vehicle->id)
            ->where(function($query) use ($vehicle) {
                $query->where('category_id', $vehicle->category_id)
                      ->orWhere('make_id', $vehicle->make_id);
            })
            ->inRandomOrder()
            ->limit($request->get('limit', 6))
            ->with(['category', 'make', 'vehicleModel', 'user', 'business'])
            ->get();

        return new VehicleCollection($vehicles);
    }

    public function getPopularMakes(): JsonResponse
    {
        $popularMakes = VehicleMake::active()
            ->withCount(['vehicles' => function($query) {
                $query->published();
            }])
            ->orderBy('vehicles_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $popularMakes
        ]);
    }

    public function incrementViews(Vehicle $vehicle): JsonResponse
    {
        $vehicle->increment('views');
        
        return response()->json([
            'success' => true,
            'message' => 'View count incremented',
            'views' => $vehicle->fresh()->views
        ]);
    }

    public function incrementClicks(Vehicle $vehicle): JsonResponse
    {
        $vehicle->increment('clicks');
        
        return response()->json([
            'success' => true,
            'message' => 'Click count incremented',
            'clicks' => $vehicle->fresh()->clicks
        ]);
    }

    public function upgradeVehicle(Request $request, Vehicle $vehicle): JsonResponse
    {
        $user = auth('api')->user();
        
        if (!$user || $vehicle->user_id !== ($user instanceof \App\Models\Customer ? $user->customer_id : $user->id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $upgradeConfig = [
            'promoted' => ['is_promoted' => true],
            'featured' => ['is_featured' => true],
            'sponsored' => ['is_sponsored' => true],
            'top_of_category' => ['is_top_of_category' => true],
        ];

        $upgradeType = $request->input('upgrade_type');
        
        if (isset($upgradeConfig[$upgradeType])) {
            $vehicle->update($upgradeConfig[$upgradeType]);
            
            // Set expiry if pricing plan is selected
            $data = $request->all();
            if (isset($data['pricing_plan_id'])) {
                $vehicle->update([
                    'pricing_plan_id' => $data['pricing_plan_id'],
                    'expires_at' => now()->addDays($data['duration_days'] ?? 30),
                    'payment_status' => 'pending'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Vehicle upgraded successfully',
                'data' => new VehicleResource($vehicle->load([
                    'category', 'make', 'vehicleModel', 'user', 'business'
                ]))
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid upgrade type'
            ], 400);
        }
    }

    public function toggleFavourite(Vehicle $vehicle): JsonResponse
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $userId = $user instanceof \App\Models\Customer ? $user->customer_id : $user->id;
        
        $favourite = $vehicle->favourites()->where('user_id', $userId)->first();
        
        if ($favourite) {
            $favourite->delete();
            $vehicle->decrement('saves');
            
            return response()->json([
                'success' => true,
                'message' => 'Vehicle removed from favourites',
                'data' => ['saved' => false]
            ]);
        } else {
            $vehicle->favourites()->create(['user_id' => $userId]);
            $vehicle->increment('saves');
            
            return response()->json([
                'success' => true,
                'message' => 'Vehicle added to favourites',
                'data' => ['saved' => true]
            ]);
        }
    }
}
