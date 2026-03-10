<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SponsoredAdvert;
use App\Models\SponsoredAdvertInquiry;
use App\Models\SponsoredAdvertRating;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SponsoredAdvertController extends Controller
{
    /**
     * Display a listing of sponsored adverts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SponsoredAdvert::with(['category', 'creator'])
            ->active()
            ->currentlySponsored();

        // Filter by advert type
        if ($request->has('advert_type')) {
            $query->byType($request->advert_type);
        }

        // Filter by country
        if ($request->has('country')) {
            $query->byCountry($request->country);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        // Filter by sponsorship tier
        if ($request->has('tier')) {
            $query->byTier($request->tier);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Price range filter
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Search by keyword
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'views':
                $query->orderBy('views_count', $sortOrder);
                break;
            case 'rating':
                $query->orderBy('rating', $sortOrder);
                break;
            case 'saves':
                $query->orderBy('saves_count', $sortOrder);
                break;
            case 'tier':
                $query->orderByTier();
                if ($sortOrder === 'desc') {
                    $query->orderBy('sponsorship_tier', 'desc');
                }
                break;
            case 'popularity':
                $query->orderByPopularity();
                break;
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 12);
        $adverts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $adverts,
            'meta' => [
                'total' => $adverts->total(),
                'per_page' => $adverts->perPage(),
                'current_page' => $adverts->currentPage(),
                'last_page' => $adverts->lastPage(),
            ],
        ]);
    }

    /**
     * Display the specified sponsored advert.
     */
    public function show($slug): JsonResponse
    {
        $advert = SponsoredAdvert::with(['category', 'creator', 'ratings' => function ($query) {
            $query->approved()->latest();
        }])
            ->where('slug', $slug)
            ->active()
            ->currentlySponsored()
            ->firstOrFail();

        // Increment view count
        $advert->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $advert,
        ]);
    }

    /**
     * Store a newly created sponsored advert.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'required|string',
            'overview' => 'nullable|string',
            'key_features' => 'nullable|string',
            'what_makes_special' => 'nullable|string',
            'why_sponsored' => 'nullable|string',
            'additional_notes' => 'nullable|string',
            'advert_type' => 'required|string|in:Product,Service,Property,Job,Event,Vehicle,Business Opportunity,Miscellaneous',
            'category_id' => 'nullable|integer|exists:categories,category_id',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_precision' => 'nullable|in:exact,approximate',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'condition' => 'nullable|in:new,used,not_applicable',
            'main_image' => 'nullable|string',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'string',
            'video_link' => 'nullable|url',
            'seller_name' => 'required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'logo' => 'nullable|string',
            'verified_seller' => 'nullable|boolean',
            'sponsorship_tier' => 'required|in:basic,plus,premium',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'seo_meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Set sponsorship price based on tier
        $tierPrices = [
            'basic' => 29.99,
            'plus' => 59.99,
            'premium' => 99.99,
        ];

        $data = $request->all();
        $data['sponsorship_price'] = $tierPrices[$request->sponsorship_tier];
        $data['created_by'] = Auth::id();
        $data['currency'] = $data['currency'] ?? 'GBP';
        $data['location_precision'] = $data['location_precision'] ?? 'approximate';

        $advert = SponsoredAdvert::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert created successfully',
            'data' => $advert->load(['category', 'creator']),
        ], 201);
    }

    /**
     * Update the specified sponsored advert.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($id);

        // Check if user owns this advert or is admin
        if ($advert->created_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'sometimes|required|string',
            'advert_type' => 'sometimes|required|string|in:Product,Service,Property,Job,Event,Vehicle,Business Opportunity,Miscellaneous',
            'category_id' => 'nullable|integer|exists:categories,category_id',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'condition' => 'nullable|in:new,used,not_applicable',
            'main_image' => 'nullable|string',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'string',
            'video_link' => 'nullable|url',
            'seller_name' => 'sometimes|required|string|max:255',
            'business_name' => 'nullable|string|max:255',
            'phone' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|email|max:255',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'logo' => 'nullable|string',
            'verified_seller' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'seo_meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();
        $data['updated_by'] = Auth::id();

        $advert->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert updated successfully',
            'data' => $advert->fresh()->load(['category', 'creator']),
        ]);
    }

    /**
     * Remove the specified sponsored advert.
     */
    public function destroy($id): JsonResponse
    {
        $advert = SponsoredAdvert::findOrFail($id);

        // Check if user owns this advert or is admin
        if ($advert->created_by !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $advert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sponsored advert deleted successfully',
        ]);
    }

    /**
     * Get featured sponsored adverts for carousel.
     */
    public function featured(Request $request): JsonResponse
    {
        $adverts = SponsoredAdvert::with(['category', 'creator'])
            ->active()
            ->currentlySponsored()
            ->featured()
            ->orderByTier()
            ->orderBy('created_at', 'desc')
            ->limit($request->get('limit', 10))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    /**
     * Get sponsored adverts by category.
     */
    public function byCategory($categoryId): JsonResponse
    {
        $adverts = SponsoredAdvert::with(['category', 'creator'])
            ->active()
            ->currentlySponsored()
            ->where('category_id', $categoryId)
            ->orderByTier()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    /**
     * Get sponsored adverts by country.
     */
    public function byCountry($country): JsonResponse
    {
        $adverts = SponsoredAdvert::with(['category', 'creator'])
            ->active()
            ->currentlySponsored()
            ->byCountry($country)
            ->orderByTier()
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    /**
     * Get trending sponsored adverts.
     */
    public function trending(Request $request): JsonResponse
    {
        $adverts = SponsoredAdvert::with(['category', 'creator'])
            ->active()
            ->currentlySponsored()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderByPopularity()
            ->limit($request->get('limit', 20))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $adverts,
        ]);
    }

    /**
     * Submit an inquiry for a sponsored advert.
     */
    public function submitInquiry(Request $request, $id): JsonResponse
    {
        $advert = SponsoredAdvert::active()->currentlySponsored()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $inquiry = SponsoredAdvertInquiry::create([
            'sponsored_advert_id' => $advert->sponsored_advert_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Increment inquiry count
        $advert->incrementInquiries();

        return response()->json([
            'success' => true,
            'message' => 'Inquiry submitted successfully',
            'data' => $inquiry,
        ]);
    }

    /**
     * Submit a rating for a sponsored advert.
     */
    public function submitRating(Request $request, $id): JsonResponse
    {
        $advert = SponsoredAdvert::active()->currentlySponsored()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user already rated this advert
        $existingRating = SponsoredAdvertRating::where('sponsored_advert_id', $advert->sponsored_advert_id)
            ->where('email', $request->email)
            ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this advert',
            ], 422);
        }

        $rating = SponsoredAdvertRating::create([
            'sponsored_advert_id' => $advert->sponsored_advert_id,
            'user_id' => Auth::id(),
            'name' => $request->name,
            'email' => $request->email,
            'rating' => $request->rating,
            'review' => $request->review,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Update advert rating
        $advert->updateRating();

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully',
            'data' => $rating,
        ]);
    }

    /**
     * Get statistics for sponsored adverts.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_adverts' => SponsoredAdvert::count(),
            'active_adverts' => SponsoredAdvert::active()->count(),
            'currently_sponsored' => SponsoredAdvert::active()->currentlySponsored()->count(),
            'by_tier' => [
                'basic' => SponsoredAdvert::byTier('basic')->active()->currentlySponsored()->count(),
                'plus' => SponsoredAdvert::byTier('plus')->active()->currentlySponsored()->count(),
                'premium' => SponsoredAdvert::byTier('premium')->active()->currentlySponsored()->count(),
            ],
            'by_type' => SponsoredAdvert::selectRaw('advert_type, COUNT(*) as count')
                ->active()
                ->currentlySponsored()
                ->groupBy('advert_type')
                ->pluck('count', 'advert_type')
                ->toArray(),
            'total_views' => SponsoredAdvert::sum('views_count'),
            'total_saves' => SponsoredAdvert::sum('saves_count'),
            'total_inquiries' => SponsoredAdvert::sum('inquiries_count'),
            'average_rating' => SponsoredAdvert::where('rating', '>', 0)->avg('rating'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
