<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuySellPromotion;
use App\Models\BuySellItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BuySellPromotionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $promotions = BuySellPromotion::with(['item', 'item.user'])
            ->whereHas('item', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $promotions
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:buy_sell_items,id',
            'promotion_type' => 'required|in:promoted,featured,sponsored,network_boost',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $item = BuySellItem::findOrFail($request->item_id);

        if ($item->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $prices = [
            'promoted' => 29,
            'featured' => 49,
            'sponsored' => 99,
            'network_boost' => 199,
        ];

        $promotion = BuySellPromotion::create([
            'item_id' => $item->id,
            'promotion_type' => $request->promotion_type,
            'price' => $prices[$request->promotion_type],
            'currency' => 'USD',
            'status' => 'pending',
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Promotion created successfully',
            'data' => $promotion
        ], 201);
    }

    public function getPricingPlans(): JsonResponse
    {
        $plans = [
            [
                'type' => 'promoted',
                'name' => 'Promoted Listing',
                'price' => 29,
                'currency' => 'USD',
                'duration' => 30,
                'features' => [
                    'Highlighted in search results',
                    'Priority placement in category',
                    'Promoted badge',
                    'Basic analytics',
                ]
            ],
            [
                'type' => 'featured',
                'name' => 'Featured Item',
                'price' => 49,
                'currency' => 'USD',
                'duration' => 30,
                'features' => [
                    'Top placement in homepage carousel',
                    'All promoted features',
                    'Featured badge',
                    'Advanced analytics',
                    'Social media promotion',
                ]
            ],
            [
                'type' => 'sponsored',
                'name' => 'Sponsored Post',
                'price' => 99,
                'currency' => 'USD',
                'duration' => 30,
                'features' => [
                    'Premium placement across platform',
                    'All featured features',
                    'Sponsored badge',
                    'Premium analytics',
                    'Email newsletter inclusion',
                    'Priority support',
                ]
            ],
            [
                'type' => 'network_boost',
                'name' => 'Network Boost',
                'price' => 199,
                'currency' => 'USD',
                'duration' => 30,
                'features' => [
                    'Maximum visibility across network',
                    'All sponsored features',
                    'Network boost badge',
                    'Full analytics suite',
                    'Cross-platform promotion',
                    'Dedicated support',
                    'AI-powered recommendations',
                ]
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }
}
