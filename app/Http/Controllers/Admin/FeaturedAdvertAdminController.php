<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedAdvert;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FeaturedAdvertAdminController extends Controller
{
    /**
     * Display a listing of featured adverts for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FeaturedAdvert::with(['listing', 'customer', 'category', 'country']);

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by upsell tier
        if ($request->has('upsell_tier')) {
            $query->where('upsell_tier', $request->upsell_tier);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter by advert type
        if ($request->has('advert_type')) {
            $query->where('advert_type', $request->advert_type);
        }

        // Search functionality
        if ($request->has('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'LIKE', "%{$term}%")
                  ->orWhere('description', 'LIKE', "%{$term}%")
                  ->orWhere('contact_name', 'LIKE', "%{$term}%")
                  ->orWhere('contact_email', 'LIKE', "%{$term}%")
                  ->orWhere('country', 'LIKE', "%{$term}%")
                  ->orWhere('city', 'LIKE', "%{$term}%");
            });
        }

        // Order by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $featuredAdverts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $featuredAdverts,
            'message' => 'Featured adverts retrieved successfully for admin'
        ]);
    }

    /**
     * Store a newly created featured advert (admin).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => 'required|exists:listing,listing_id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'currency' => 'string|size:3',
            'advert_type' => ['required', Rule::in(FeaturedAdvert::TYPE_PRODUCT, FeaturedAdvert::TYPE_SERVICE, FeaturedAdvert::TYPE_PROPERTY, FeaturedAdvert::TYPE_JOB, FeaturedAdvert::TYPE_EVENT, FeaturedAdvert::TYPE_VEHICLE, FeaturedAdvert::TYPE_BUSINESS, FeaturedAdvert::TYPE_EDUCATION, FeaturedAdvert::TYPE_TRAVEL, FeaturedAdvert::TYPE_FASHION, FeaturedAdvert::TYPE_ELECTRONICS, FeaturedAdvert::TYPE_PETS, FeaturedAdvert::TYPE_HOME, FeaturedAdvert::TYPE_HEALTH, FeaturedAdvert::TYPE_MISC)],
            'condition' => 'nullable|in:new,used,refurbished',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string|max:255',
            'video_url' => 'nullable|url|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'upsell_tier' => ['required', Rule::in(FeaturedAdvert::TIER_PROMOTED, FeaturedAdvert::TIER_FEATURED, FeaturedAdvert::TIER_SPONSORED)],
            'upsell_price' => 'required|numeric|min:0|max:99999.99',
            'payment_status' => ['required', Rule::in(FeaturedAdvert::PAYMENT_PENDING, FeaturedAdvert::PAYMENT_PAID, FeaturedAdvert::PAYMENT_FAILED)],
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'is_active' => 'boolean',
            'is_verified_seller' => 'boolean',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Get category and country IDs
        $listing = Listing::findOrFail($validated['listing_id']);
        $categoryId = $listing->category_id;
        $country = Country::where('name', $validated['country'])->first();
        $countryId = $country ? $country->country_id : null;

        $validated['customer_id'] = $listing->customer_id;
        $validated['category_id'] = $categoryId;
        $validated['country_id'] = $countryId;

        $featuredAdvert = FeaturedAdvert::create($validated);

        return response()->json([
            'success' => true,
            'data' => $featuredAdvert->load(['listing', 'customer', 'category', 'country']),
            'message' => 'Featured advert created successfully by admin'
        ], 201);
    }

    /**
     * Display the specified featured advert (admin).
     */
    public function show(string $id): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::with(['listing', 'customer', 'category', 'country'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $featuredAdvert,
            'message' => 'Featured advert retrieved successfully for admin'
        ]);
    }

    /**
     * Update the specified featured advert (admin).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'currency' => 'string|size:3',
            'advert_type' => ['sometimes', 'required', Rule::in(FeaturedAdvert::TYPE_PRODUCT, FeaturedAdvert::TYPE_SERVICE, FeaturedAdvert::TYPE_PROPERTY, FeaturedAdvert::TYPE_JOB, FeaturedAdvert::TYPE_EVENT, FeaturedAdvert::TYPE_VEHICLE, FeaturedAdvert::TYPE_BUSINESS, FeaturedAdvert::TYPE_EDUCATION, FeaturedAdvert::TYPE_TRAVEL, FeaturedAdvert::TYPE_FASHION, FeaturedAdvert::TYPE_ELECTRONICS, FeaturedAdvert::TYPE_PETS, FeaturedAdvert::TYPE_HOME, FeaturedAdvert::TYPE_HEALTH, FeaturedAdvert::TYPE_MISC)],
            'condition' => 'nullable|in:new,used,refurbished',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string|max:255',
            'video_url' => 'nullable|url|max:255',
            'contact_name' => 'sometimes|required|string|max:255',
            'contact_email' => 'sometimes|required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'upsell_tier' => ['sometimes', 'required', Rule::in(FeaturedAdvert::TIER_PROMOTED, FeaturedAdvert::TIER_FEATURED, FeaturedAdvert::TIER_SPONSORED)],
            'upsell_price' => 'sometimes|required|numeric|min:0|max:99999.99',
            'payment_status' => ['sometimes', 'required', Rule::in(FeaturedAdvert::PAYMENT_PENDING, FeaturedAdvert::PAYMENT_PAID, FeaturedAdvert::PAYMENT_FAILED)],
            'starts_at' => 'sometimes|required|date',
            'expires_at' => 'sometimes|required|date|after:starts_at',
            'is_active' => 'boolean',
            'is_verified_seller' => 'boolean',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // Update country ID if country changed
        if (isset($validated['country'])) {
            $country = Country::where('name', $validated['country'])->first();
            $validated['country_id'] = $country ? $country->country_id : null;
        }

        $featuredAdvert->update($validated);

        return response()->json([
            'success' => true,
            'data' => $featuredAdvert->load(['listing', 'customer', 'category', 'country']),
            'message' => 'Featured advert updated successfully by admin'
        ]);
    }

    /**
     * Remove the specified featured advert (admin).
     */
    public function destroy(string $id): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::findOrFail($id);
        $featuredAdvert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Featured advert deleted successfully by admin'
        ]);
    }

    /**
     * Bulk update featured adverts (admin).
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:featured_adverts,id',
            'updates' => 'required|array',
            'updates.payment_status' => 'nullable|in:pending,paid,failed',
            'updates.is_active' => 'nullable|boolean',
            'updates.is_verified_seller' => 'nullable|boolean',
            'updates.admin_notes' => 'nullable|string|max:1000',
        ]);

        $updated = FeaturedAdvert::whereIn('id', $validated['ids'])
            ->update($validated['updates']);

        return response()->json([
            'success' => true,
            'data' => ['updated_count' => $updated],
            'message' => "Successfully updated {$updated} featured adverts"
        ]);
    }

    /**
     * Get featured adverts statistics (admin).
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_featured_adverts' => FeaturedAdvert::count(),
            'total_active' => FeaturedAdvert::active()->count(),
            'total_pending_payment' => FeaturedAdvert::where('payment_status', 'pending')->count(),
            'total_paid' => FeaturedAdvert::where('payment_status', 'paid')->count(),
            'total_revenue' => FeaturedAdvert::where('payment_status', 'paid')->sum('upsell_price'),
            
            'by_tier' => [
                'promoted' => [
                    'total' => FeaturedAdvert::where('upsell_tier', 'promoted')->count(),
                    'active' => FeaturedAdvert::active()->promoted()->count(),
                    'revenue' => FeaturedAdvert::where('upsell_tier', 'promoted')->where('payment_status', 'paid')->sum('upsell_price'),
                ],
                'featured' => [
                    'total' => FeaturedAdvert::where('upsell_tier', 'featured')->count(),
                    'active' => FeaturedAdvert::active()->featured()->count(),
                    'revenue' => FeaturedAdvert::where('upsell_tier', 'featured')->where('payment_status', 'paid')->sum('upsell_price'),
                ],
                'sponsored' => [
                    'total' => FeaturedAdvert::where('upsell_tier', 'sponsored')->count(),
                    'active' => FeaturedAdvert::active()->sponsored()->count(),
                    'revenue' => FeaturedAdvert::where('upsell_tier', 'sponsored')->where('payment_status', 'paid')->sum('upsell_price'),
                ],
            ],
            
            'by_type' => FeaturedAdvert::select('advert_type', DB::raw('count(*) as count'))
                ->groupBy('advert_type')
                ->orderByDesc('count')
                ->get(),
            
            'by_country' => FeaturedAdvert::select('country', DB::raw('count(*) as count'))
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            
            'engagement' => [
                'total_views' => FeaturedAdvert::sum('view_count'),
                'total_saves' => FeaturedAdvert::sum('save_count'),
                'total_contacts' => FeaturedAdvert::sum('contact_count'),
                'average_rating' => FeaturedAdvert::whereNotNull('rating')->avg('rating'),
            ],
            
            'monthly_stats' => FeaturedAdvert::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as created, SUM(CASE WHEN payment_status = "paid" THEN upsell_price ELSE 0 END) as revenue')
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Featured adverts statistics retrieved successfully'
        ]);
    }

    /**
     * Approve featured advert (admin).
     */
    public function approve(string $id): JsonResponse
    {
        $featuredAdvert = FeaturedAdvert::findOrFail($id);
        
        $featuredAdvert->update([
            'payment_status' => FeaturedAdvert::PAYMENT_PAID,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => $featuredAdvert->fresh(),
            'message' => 'Featured advert approved and activated successfully'
        ]);
    }

    /**
     * Reject featured advert (admin).
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $featuredAdvert = FeaturedAdvert::findOrFail($id);
        
        $featuredAdvert->update([
            'payment_status' => FeaturedAdvert::PAYMENT_FAILED,
            'is_active' => false,
            'admin_notes' => $validated['reason'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $featuredAdvert->fresh(),
            'message' => 'Featured advert rejected successfully'
        ]);
    }

    /**
     * Export featured adverts (admin).
     */
    public function export(Request $request): JsonResponse
    {
        $query = FeaturedAdvert::with(['listing', 'customer', 'category', 'country']);

        // Apply filters similar to index method
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->has('upsell_tier')) {
            $query->where('upsell_tier', $request->upsell_tier);
        }
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        if ($request->has('advert_type')) {
            $query->where('advert_type', $request->advert_type);
        }

        $featuredAdverts = $query->get();

        // Transform data for export
        $exportData = $featuredAdverts->map(function ($advert) {
            return [
                'ID' => $advert->id,
                'Title' => $advert->title,
                'Type' => $advert->advert_type,
                'Tier' => $advert->upsell_tier,
                'Price' => $advert->formatted_price,
                'Payment Status' => $advert->payment_status,
                'Customer' => $advert->customer->name ?? 'N/A',
                'Country' => $advert->country,
                'City' => $advert->city,
                'Active' => $advert->is_active ? 'Yes' : 'No',
                'Verified Seller' => $advert->is_verified_seller ? 'Yes' : 'No',
                'Views' => $advert->view_count,
                'Saves' => $advert->save_count,
                'Contacts' => $advert->contact_count,
                'Starts At' => $advert->starts_at,
                'Expires At' => $advert->expires_at,
                'Created At' => $advert->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $exportData,
            'message' => 'Featured adverts data exported successfully'
        ]);
    }
}
