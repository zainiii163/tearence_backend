<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobSeeker;
use App\Models\JobUpsell;
use App\Models\JobPricingPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class JobUpsellController extends Controller
{
    /**
     * Get upsell pricing
     */
    public function pricing(): JsonResponse
    {
        $plans = JobPricingPlan::active()
                            ->orderBy('price')
                            ->get();

        $pricingData = [];
        
        foreach ($plans as $plan) {
            $pricingData[$plan->slug] = [
                'id' => $plan->slug,
                'name' => $plan->name,
                'price' => $plan->price,
                'currency' => $plan->currency,
                'period' => $plan->period,
                'features' => $plan->features,
                'recommended' => $plan->recommended,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $pricingData,
        ]);
    }

    /**
     * Create upsell
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'upsellable_type' => 'required|string|in:job_listing,job_seeker',
            'upsellable_id' => 'required|integer',
            'upsell_type' => 'required|string|in:promoted,featured,sponsored,network',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'duration_months' => 'nullable|integer|min:1|max:12',
        ]);

        // Get the pricing plan
        $plan = JobPricingPlan::where('slug', $request->upsell_type)->active()->first();
        if (!$plan) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Pricing plan not found',
                ],
            ], 404);
        }

        // Validate price matches plan
        if ($request->price != $plan->price) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Price does not match the plan price',
                ],
            ], 422);
        }

        // Check if user owns the upsellable item
        $upsellable = null;
        if ($request->upsellable_type === 'job_listing') {
            $upsellable = Job::where('id', $request->upsellable_id)
                           ->where('user_id', Auth::id())
                           ->first();
        } elseif ($request->upsellable_type === 'job_seeker') {
            $upsellable = JobSeeker::where('id', $request->upsellable_id)
                                 ->where('user_id', Auth::id())
                                 ->first();
        }

        if (!$upsellable) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Upsellable item not found or access denied',
                ],
            ], 404);
        }

        // Check for existing active upsell
        $existingUpsell = JobUpsell::where('upsellable_type', $request->upsellable_type)
                                  ->where('upsellable_id', $request->upsellable_id)
                                  ->active()
                                  ->first();

        if ($existingUpsell) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE_UPSELL',
                    'message' => 'An active upsell already exists for this item',
                ],
            ], 422);
        }

        $upsell = JobUpsell::create([
            'user_id' => Auth::id(),
            'pricing_plan_id' => $plan->id,
            'upsellable_type' => $request->upsellable_type,
            'upsellable_id' => $request->upsellable_id,
            'upsell_type' => $request->upsell_type,
            'price' => $request->price,
            'currency' => $request->currency ?? 'USD',
            'duration_months' => $request->duration_months ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Upsell created successfully',
            'data' => $upsell,
        ], 201);
    }

    /**
     * Activate upsell
     */
    public function activate($id): JsonResponse
    {
        $upsell = JobUpsell::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->first();

        if (!$upsell) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Upsell not found',
                ],
            ], 404);
        }

        if ($upsell->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'PAYMENT_REQUIRED',
                    'message' => 'Payment is required before activation',
                ],
            ], 422);
        }

        $upsell->activate();

        return response()->json([
            'success' => true,
            'message' => 'Upsell activated successfully',
            'data' => $upsell,
        ]);
    }

    /**
     * Cancel upsell
     */
    public function cancel($id): JsonResponse
    {
        $upsell = JobUpsell::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->first();

        if (!$upsell) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Upsell not found',
                ],
            ], 404);
        }

        $upsell->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Upsell cancelled successfully',
        ]);
    }

    /**
     * Get my upsells
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobUpsell::with(['pricingPlan', 'upsellable'])
                        ->where('user_id', Auth::id());

        // Filter by my_upsells flag
        if ($request->boolean('my_upsells')) {
            // This is the default behavior when authenticated
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->upsell_type) {
            $query->where('upsell_type', $request->upsell_type);
        }

        $upsells = $query->orderBy('created_at', 'desc')
                        ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $upsells->items(),
            'pagination' => [
                'current_page' => $upsells->currentPage(),
                'per_page' => $upsells->perPage(),
                'total' => $upsells->total(),
                'total_pages' => $upsells->lastPage(),
            ],
        ]);
    }

    /**
     * Get single upsell
     */
    public function show($id): JsonResponse
    {
        $upsell = JobUpsell::with(['pricingPlan', 'upsellable'])
                        ->where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$upsell) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Upsell not found',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $upsell,
        ]);
    }

    /**
     * Process payment for upsell
     */
    public function pay(Request $request, $id): JsonResponse
    {
        $upsell = JobUpsell::where('id', $id)
                         ->where('user_id', Auth::id())
                         ->first();

        if (!$upsell) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Upsell not found',
                ],
            ], 404);
        }

        if ($upsell->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ALREADY_PAID',
                    'message' => 'Upsell has already been paid',
                ],
            ], 422);
        }

        $request->validate([
            'payment_method' => 'required|string|in:stripe,paypal,bank_transfer',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        // In a real implementation, you would process the payment here
        // For now, we'll just mark it as paid
        $upsell->markAsPaid($request->transaction_id, $request->payment_method);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => $upsell,
        ]);
    }

    /**
     * Get upsell statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_upsells' => JobUpsell::where('user_id', Auth::id())->count(),
            'active_upsells' => JobUpsell::where('user_id', Auth::id())->active()->count(),
            'pending_upsells' => JobUpsell::where('user_id', Auth::id())->pending()->count(),
            'cancelled_upsells' => JobUpsell::where('user_id', Auth::id())->cancelled()->count(),
            'expired_upsells' => JobUpsell::where('user_id', Auth::id())->expired()->count(),
            'total_spent' => JobUpsell::where('user_id', Auth::id())->paid()->sum('price'),
            'upsells_by_type' => JobUpsell::where('user_id', Auth::id())
                                ->selectRaw('upsell_type, COUNT(*) as count')
                                ->groupBy('upsell_type')
                                ->get()
                                ->mapWithKeys(function ($item) {
                                    return [$item->upsell_type => $item->count];
                                }),
            'recent_upsells' => JobUpsell::where('user_id', Auth::id())
                                  ->with(['pricingPlan', 'upsellable'])
                                  ->orderBy('created_at', 'desc')
                                  ->limit(5)
                                  ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
