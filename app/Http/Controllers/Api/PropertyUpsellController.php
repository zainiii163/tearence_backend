<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\VerifiesClientPayments;
use App\Http\Requests\PropertyUpsellStoreRequest;
use App\Http\Resources\PropertyUpsellCollection;
use App\Http\Resources\PropertyUpsellResource;
use App\Models\PromoPricingPlan;
use App\Models\Property;
use App\Models\PropertyUpsell;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyUpsellController extends Controller
{
    use VerifiesClientPayments;

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

            if ((int) $property->user_id !== (int) Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $upsellType = $this->normalizeUpsellType($request->upsell_type);
            if (! $upsellType) {
                return response()->json(['message' => 'Invalid upsell type'], 422);
            }

            $existingUpsell = PropertyUpsell::where('property_id', $property->id)
                ->where('upsell_type', $upsellType)
                ->where('status', 'active')
                ->where('payment_status', 'paid')
                ->where('expires_at', '>', now())
                ->first();

            if ($existingUpsell) {
                return response()->json([
                    'message' => 'An active upsell of this type already exists for this property',
                ], 422);
            }

            $durationDays = (int) ($request->duration_days ?: 30);
            if (! in_array($durationDays, [7, 14, 30], true)) {
                $durationDays = 30;
            }

            $price = $this->resolvePrice($upsellType, $durationDays, $request->input('price'));

            $upsell = PropertyUpsell::create([
                'property_id' => $property->id,
                'user_id' => Auth::id(),
                'upsell_type' => $upsellType,
                'price' => $price,
                'currency' => $request->input('currency', 'USD'),
                'duration_days' => $durationDays,
                'starts_at' => now(),
                'expires_at' => now()->addDays($durationDays),
                'payment_status' => $price > 0 ? 'pending' : 'paid',
                'status' => $price > 0 ? 'pending' : 'active',
                'paid_at' => $price > 0 ? null : now(),
            ]);

            // Never activate promo flags until payment is confirmed (unless free)
            if ($price <= 0) {
                $this->activatePropertyPromotion($property, $upsell);
            }

            return response()->json([
                'message' => 'Upsell created successfully',
                'upsell' => new PropertyUpsellResource($upsell->load(['property', 'user'])),
                'payment_required' => $price > 0,
                'amount' => (float) $price,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create upsell',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse|PropertyUpsellResource
    {
        $upsell = PropertyUpsell::with(['property', 'user'])->findOrFail($id);

        if ((int) $upsell->user_id !== (int) Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new PropertyUpsellResource($upsell);
    }

    public function completePayment($id, Request $request): JsonResponse
    {
        $upsell = PropertyUpsell::with('property')->findOrFail($id);

        if ((int) $upsell->user_id !== (int) Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'required|string',
        ]);

        $verified = $this->verifyClientPaymentOrFail(
            $request,
            (float) $upsell->price,
            'property_upsell',
            $upsell->id,
            'USD',
            'transaction_id'
        );
        if ($verified instanceof JsonResponse) {
            return $verified;
        }

        try {
            $upsell->update([
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method,
                'transaction_id' => $verified['payment_id'],
                'paid_at' => now(),
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addDays((int) $upsell->duration_days),
            ]);

            $this->activatePropertyPromotion($upsell->property, $upsell->fresh());

            return response()->json([
                'message' => 'Payment completed successfully',
                'upsell' => new PropertyUpsellResource($upsell->fresh()->load(['property', 'user'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel($id): JsonResponse
    {
        $upsell = PropertyUpsell::with('property')->findOrFail($id);

        if ((int) $upsell->user_id !== (int) Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $upsell->update([
                'status' => 'cancelled',
            ]);

            $property = $upsell->property;
            if ($property && $property->advert_type === $upsell->upsell_type) {
                $untilField = $upsell->upsell_type.'_until';
                $property->update([
                    'advert_type' => 'basic',
                    $untilField => null,
                ]);
            }

            return response()->json(['message' => 'Upsell cancelled successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel upsell',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPropertyUpsells(Property $property): JsonResponse|PropertyUpsellCollection
    {
        if ((int) $property->user_id !== (int) Auth::id()) {
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

    protected function normalizeUpsellType(?string $type): ?string
    {
        $type = strtolower(trim((string) $type));
        $aliases = [
            'promoted' => 'promoted',
            'promote' => 'promoted',
            'featured' => 'featured',
            'feature' => 'featured',
            'sponsored' => 'sponsored',
            'sponsor' => 'sponsored',
        ];

        return $aliases[$type] ?? null;
    }

    protected function resolvePrice(string $upsellType, int $durationDays, $requestedPrice = null): float
    {
        if ($requestedPrice !== null && $requestedPrice !== '' && is_numeric($requestedPrice)) {
            return max(0, (float) $requestedPrice);
        }

        $plan = PromoPricingPlan::query()
            ->active()
            ->forVertical('property')
            ->where(function ($q) use ($upsellType) {
                $q->where('slug', $upsellType)->orWhere('tier', $upsellType);
            })
            ->orderBy('sort_order')
            ->first();

        if ($plan && $plan->price_usd !== null) {
            return (float) $plan->price_usd;
        }

        $pricing = PropertyUpsell::getPricing();

        return (float) ($pricing[$upsellType][$durationDays] ?? 0);
    }

    protected function activatePropertyPromotion(?Property $property, PropertyUpsell $upsell): void
    {
        if (! $property) {
            return;
        }

        $type = $upsell->upsell_type;
        $untilField = $type.'_until';

        $property->update([
            'advert_type' => $type,
            $untilField => $upsell->expires_at,
        ]);
    }
}
