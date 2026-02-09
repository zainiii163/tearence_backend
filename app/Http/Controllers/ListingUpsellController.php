<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingUpsell;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ListingUpsellController extends Controller
{
    /**
     * Get available upsell options
     */
    public function getUpsellOptions(): JsonResponse
    {
        $options = [
            [
                'type' => ListingUpsell::TYPE_PRIORITY,
                'name' => 'Priority Placement',
                'description' => 'Your listing appears first in search results',
                'price' => 10.00,
                'duration_days' => 7,
                'priority_score' => 400,
            ],
            [
                'type' => ListingUpsell::TYPE_FEATURED,
                'name' => 'Featured Listing',
                'description' => 'Get a featured badge on your listing',
                'price' => 15.00,
                'duration_days' => 14,
                'priority_score' => 600,
            ],
            [
                'type' => ListingUpsell::TYPE_SPONSORED,
                'name' => 'Sponsored Listing',
                'description' => 'Top placement with sponsored badge',
                'price' => 25.00,
                'duration_days' => 21,
                'priority_score' => 800,
            ],
            [
                'type' => ListingUpsell::TYPE_PREMIUM,
                'name' => 'Premium Placement',
                'description' => 'Maximum visibility with premium placement',
                'price' => 50.00,
                'duration_days' => 30,
                'priority_score' => 1000,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Purchase an upsell for a listing
     */
    public function purchaseUpsell(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|integer|exists:listing,listing_id',
            'upsell_type' => 'required|string|in:' . implode(',', [
                ListingUpsell::TYPE_PRIORITY,
                ListingUpsell::TYPE_FEATURED,
                ListingUpsell::TYPE_SPONSORED,
                ListingUpsell::TYPE_PREMIUM
            ]),
            'duration_days' => 'required|integer|min:1|max:365',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing = Listing::findOrFail($request->listing_id);
        
        // Check if user owns the listing
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        if ($listing->customer_id !== $user->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only purchase upsells for your own listings'
            ], 403);
        }

        // Get pricing based on upsell type
        $pricing = $this->getUpsellPricing($request->upsell_type, $request->duration_days);
        
        // Create the upsell
        $upsell = ListingUpsell::create([
            'listing_id' => $listing->listing_id,
            'customer_id' => $listing->customer_id,
            'upsell_type' => $request->upsell_type,
            'price' => $pricing['price'],
            'duration_days' => $request->duration_days,
            'starts_at' => now(),
            'expires_at' => now()->addDays($request->duration_days),
            'status' => ListingUpsell::STATUS_ACTIVE,
            'payment_status' => ListingUpsell::PAYMENT_PENDING,
            'payment_details' => [
                'method' => $request->payment_method,
                'amount' => $pricing['price'],
                'currency' => 'USD',
            ],
        ]);

        // In a real implementation, you would process payment here
        // For now, we'll mark it as paid automatically
        $upsell->update(['payment_status' => ListingUpsell::PAYMENT_PAID]);

        return response()->json([
            'success' => true,
            'message' => 'Upsell purchased successfully',
            'data' => $upsell->load('listing')
        ]);
    }

    /**
     * Get user's active upsells
     */
    public function getUserUpsells(): JsonResponse
    {
        $customer = auth()->user()->customer;
        
        $upsells = ListingUpsell::where('customer_id', $customer->customer_id)
            ->with('listing')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $upsells
        ]);
    }

    /**
     * Get upsell statistics
     */
    public function getUpsellStats(): JsonResponse
    {
        $customer = auth()->user()->customer;
        
        $stats = [
            'total_spent' => ListingUpsell::where('customer_id', $customer->customer_id)
                ->where('payment_status', ListingUpsell::PAYMENT_PAID)
                ->sum('price'),
            'active_upsells' => ListingUpsell::where('customer_id', $customer->customer_id)
                ->active()
                ->count(),
            'total_upsells' => ListingUpsell::where('customer_id', $customer->customer_id)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Cancel an upsell
     */
    public function cancelUpsell(int $upsellId): JsonResponse
    {
        $upsell = ListingUpsell::findOrFail($upsellId);
        
        // Check if user owns the upsell
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        if ($upsell->customer_id !== $user->customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel your own upsells'
            ], 403);
        }

        // Only allow cancellation if it's not expired
        if ($upsell->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel expired upsell'
            ], 400);
        }

        $upsell->update(['status' => ListingUpsell::STATUS_CANCELLED]);

        return response()->json([
            'success' => true,
            'message' => 'Upsell cancelled successfully'
        ]);
    }

    /**
     * Get pricing for upsell type and duration
     */
    private function getUpsellPricing(string $type, int $days): array
    {
        $basePrices = [
            ListingUpsell::TYPE_PRIORITY => 10.00,
            ListingUpsell::TYPE_FEATURED => 15.00,
            ListingUpsell::TYPE_SPONSORED => 25.00,
            ListingUpsell::TYPE_PREMIUM => 50.00,
        ];

        $basePrice = $basePrices[$type] ?? 10.00;
        
        // Calculate price based on duration (bulk discount for longer periods)
        $multiplier = 1.0;
        if ($days >= 30) {
            $multiplier = 0.8; // 20% discount for 30+ days
        } elseif ($days >= 14) {
            $multiplier = 0.9; // 10% discount for 14+ days
        }

        return [
            'price' => $basePrice * $days * $multiplier,
            'base_price' => $basePrice,
            'multiplier' => $multiplier,
        ];
    }

    /**
     * Get search results with priority ordering
     */
    public function getSearchResults(Request $request): JsonResponse
    {
        $query = Listing::approved()->active();

        // Apply category filter if provided
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Apply location filter if provided
        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }

        // Apply price filters if provided
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply upsell type filter if provided
        if ($request->upsell_type) {
            $query->whereHas('activeUpsells', function($q) use ($request) {
                $q->where('upsell_type', $request->upsell_type);
            });
        }

        // Apply text search if provided
        if ($request->q) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->q . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->q . '%');
            });
        }

        // Apply priority ordering or custom sort
        if ($request->sort_by === 'priority') {
            $listings = $query->orderBySearchPriority()
                ->paginate($request->per_page ?? 20);
        } elseif ($request->sort_by === 'price_low') {
            $listings = $query->orderBy('price', 'asc')
                ->paginate($request->per_page ?? 20);
        } elseif ($request->sort_by === 'price_high') {
            $listings = $query->orderBy('price', 'desc')
                ->paginate($request->per_page ?? 20);
        } elseif ($request->sort_by === 'newest') {
            $listings = $query->orderByDesc('created_at')
                ->paginate($request->per_page ?? 20);
        } elseif ($request->sort_by === 'oldest') {
            $listings = $query->orderBy('created_at', 'asc')
                ->paginate($request->per_page ?? 20);
        } else {
            // Default to priority ordering
            $listings = $query->orderBySearchPriority()
                ->paginate($request->per_page ?? 20);
        }

        return response()->json([
            'success' => true,
            'data' => $listings
        ]);
    }
}
