<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyUpsellStoreRequest;
use App\Http\Resources\PropertyUpsellCollection;
use App\Http\Resources\PropertyUpsellResource;
use App\Models\Property;
use App\Models\PropertyUpsell;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PropertyUpsellController extends Controller
{
    public function index(Request $request): PropertyUpsellCollection
    {
        $upsells = PropertyUpsell::with(['property', 'user'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return new PropertyUpsellCollection($upsells);
    }

    public function store(PropertyUpsellStoreRequest $request): JsonResponse
    {
        try {
            $property = Property::findOrFail($request->property_id);

            // Check if user owns this property
            if ($property->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if there's already an active upsell of this type
            $existingUpsell = PropertyUpsell::where('property_id', $property->id)
                ->where('upsell_type', $request->upsell_type)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->first();

            if ($existingUpsell) {
                return response()->json([
                    'message' => 'An active upsell of this type already exists for this property'
                ], 422);
            }

            $pricing = PropertyUpsell::getPricing();
            $price = $pricing[$request->upsell_type][$request->duration_days] ?? 0;

            $upsell = PropertyUpsell::create([
                'property_id' => $property->id,
                'user_id' => Auth::id(),
                'upsell_type' => $request->upsell_type,
                'price' => $price,
                'currency' => 'USD',
                'duration_days' => $request->duration_days,
                'starts_at' => now(),
                'expires_at' => now()->addDays($request->duration_days),
                'payment_status' => 'pending',
                'status' => 'active',
            ]);

            // Update property upsell flags
            $property->update([
                $request->upsell_type => true,
                $request->upsell_type . '_until' => $upsell->expires_at,
            ]);

            return response()->json([
                'message' => 'Upsell created successfully',
                'upsell' => new PropertyUpsellResource($upsell->load(['property', 'user'])),
                'payment_required' => $price > 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create upsell',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(PropertyUpsell $upsell): PropertyUpsellResource
    {
        // Check if user owns this upsell
        if ($upsell->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new PropertyUpsellResource($upsell->load(['property', 'user']));
    }

    public function completePayment(PropertyUpsell $upsell, Request $request): JsonResponse
    {
        // Check if user owns this upsell
        if ($upsell->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'required|string',
        ]);

        try {
            $upsell->update([
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'paid_at' => now(),
            ]);

            return response()->json([
                'message' => 'Payment completed successfully',
                'upsell' => new PropertyUpsellResource($upsell->load(['property', 'user']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel(PropertyUpsell $upsell): JsonResponse
    {
        // Check if user owns this upsell
        if ($upsell->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $upsell->update([
                'status' => 'cancelled',
            ]);

            // Update property upsell flags
            $property = $upsell->property;
            $property->update([
                $upsell->upsell_type => false,
                $upsell->upsell_type . '_until' => null,
            ]);

            return response()->json(['message' => 'Upsell cancelled successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel upsell',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPropertyUpsells(Property $property): PropertyUpsellCollection
    {
        // Check if user owns this property
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $upsells = PropertyUpsell::with(['property', 'user'])
            ->where('property_id', $property->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return new PropertyUpsellCollection($upsells);
    }

    public function getUpsellOptions(): JsonResponse
    {
        $options = [
            'types' => PropertyUpsell::getUpsellTypes(),
            'pricing' => PropertyUpsell::getPricing(),
            'durations' => [
                7 => '7 Days',
                14 => '14 Days',
                30 => '30 Days',
            ],
            'benefits' => [
                'promoted' => [
                    'Highlighted card',
                    'Appears above standard listings',
                    '"Promoted" badge',
                ],
                'featured' => [
                    'Top of category',
                    'Larger card',
                    'Priority in search results',
                    'Included in weekly email blast',
                    '"Featured" badge',
                ],
                'sponsored' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Social media promotion',
                    '"Sponsored" badge',
                    'Maximum visibility',
                ],
            ],
        ];

        return response()->json($options);
    }

    public function getStats(): JsonResponse
    {
        $userId = Auth::id();

        $stats = [
            'total_upsells' => PropertyUpsell::where('user_id', $userId)->count(),
            'active_upsells' => PropertyUpsell::where('user_id', $userId)->active()->count(),
            'total_spent' => PropertyUpsell::where('user_id', $userId)
                ->where('payment_status', 'paid')
                ->sum('price'),
            'pending_payments' => PropertyUpsell::where('user_id', $userId)
                ->where('payment_status', 'pending')
                ->count(),
            'upsells_by_type' => PropertyUpsell::where('user_id', $userId)
                ->selectRaw('upsell_type, COUNT(*) as count')
                ->groupBy('upsell_type')
                ->pluck('count', 'upsell_type'),
        ];

        return response()->json($stats);
    }
}
