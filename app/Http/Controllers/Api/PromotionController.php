<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePromotion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public function tiers(): JsonResponse
    {
        $tiers = [
            [
                'id' => 'promoted',
                'name' => 'Promoted',
                'price' => 29.00,
                'duration_days' => [7, 30, 90, 365],
                'features' => [
                    'Highlighted listing',
                    'Appears above standard services',
                    '"Promoted" badge',
                    '2× more visibility'
                ],
                'is_popular' => false
            ],
            [
                'id' => 'featured',
                'name' => 'Featured',
                'price' => 59.00,
                'duration_days' => [7, 30, 90, 365],
                'features' => [
                    'Top of category pages',
                    'Larger service card',
                    'Priority in search results',
                    'Weekly "Featured Services" email',
                    '"Featured" badge'
                ],
                'is_popular' => true
            ],
            [
                'id' => 'sponsored',
                'name' => 'Sponsored',
                'price' => 99.00,
                'duration_days' => [7, 30, 90, 365],
                'features' => [
                    'Homepage placement',
                    'Category top placement',
                    'Homepage slider inclusion',
                    'Social media promotion',
                    '"Sponsored" badge'
                ],
                'is_popular' => false
            ],
            [
                'id' => 'network_boost',
                'name' => 'Network-Wide Boost',
                'price' => 199.00,
                'duration_days' => [7, 30, 90, 365],
                'features' => [
                    'Appears across all pages',
                    'Homepage, category & search',
                    'Newsletter inclusion',
                    'Push notifications',
                    '"Top Spotlight" badge'
                ],
                'is_popular' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $tiers,
        ]);
    }

    public function purchase(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'tier' => 'required|in:promoted,featured,sponsored,network_boost',
            'duration' => 'required|integer|in:7,30,90,365',
            'payment_method' => 'required|string',
        ]);

        $tiers = $this->tiers()->getData(true)['data'];
        $tierData = collect($tiers)->firstWhere('id', $request->tier);

        if (!$tierData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promotion tier',
            ], 400);
        }

        $service = \App\Models\Service::findOrFail($request->service_id);

        if ($service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $totalPrice = $tierData['price'] * ($request->duration / 30); // Calculate based on duration

        // Create promotion record
        $promotion = ServicePromotion::create([
            'service_id' => $request->service_id,
            'promotion_type' => $request->tier,
            'price' => $totalPrice,
            'currency' => 'USD',
            'duration_days' => $request->duration,
            'starts_at' => now(),
            'expires_at' => now()->addDays($request->duration),
            'status' => 'active',
            'benefits' => $tierData['features'],
        ]);

        // Update service promotion status
        $service->update([
            'promotion_type' => $request->tier,
            'promotion_expires_at' => $promotion->expires_at,
        ]);

        // Here you would integrate with actual payment processor
        // For now, we'll assume payment is successful

        return response()->json([
            'success' => true,
            'message' => 'Promotion purchased successfully',
            'data' => [
                'promotion' => $promotion,
                'service' => $service,
                'total_paid' => $totalPrice,
            ],
        ]);
    }

    public function calculateTotal(Request $request): JsonResponse
    {
        $request->validate([
            'tier' => 'required|in:promoted,featured,sponsored,network_boost',
            'duration' => 'required|integer|in:7,30,90,365',
        ]);

        $tiers = $this->tiers()->getData(true)['data'];
        $tierData = collect($tiers)->firstWhere('id', $request->tier);

        if (!$tierData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promotion tier',
            ], 400);
        }

        $basePrice = $tierData['price'];
        $total = $basePrice * ($request->duration / 30);

        return response()->json([
            'success' => true,
            'data' => [
                'base_price' => $basePrice,
                'duration' => $request->duration,
                'total' => round($total, 2),
                'currency' => 'USD',
            ],
        ]);
    }

    public function myPromotions(Request $request): JsonResponse
    {
        $promotions = ServicePromotion::with(['service'])
            ->whereHas('service', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $promotions,
        ]);
    }

    public function cancel(Request $request, $id): JsonResponse
    {
        $promotion = ServicePromotion::findOrFail($id);

        if ($promotion->service->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($promotion->status === 'expired') {
            return response()->json([
                'success' => false,
                'message' => 'Promotion already expired',
            ], 400);
        }

        $promotion->update([
            'status' => 'cancelled',
            'expires_at' => now(),
        ]);

        // Update service promotion status
        $promotion->service->update([
            'promotion_type' => 'standard',
            'promotion_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Promotion cancelled successfully',
        ]);
    }
}
