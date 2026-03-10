<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SponsoredPricingPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SponsoredPricingPlanController extends Controller
{
    /**
     * Display a listing of sponsored pricing plans.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SponsoredPricingPlan::active();

        // Filter by tier
        if ($request->has('tier')) {
            $query->byTier($request->tier);
        }

        // Sort by price or sort order
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortOrder = $request->get('sort_order', 'asc');

        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } else {
            $query->orderBy('sort_order', 'asc')->orderBy('price', 'asc');
        }

        $plans = $query->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Display the specified sponsored pricing plan.
     */
    public function show($id): JsonResponse
    {
        $plan = SponsoredPricingPlan::active()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    /**
     * Get pricing plans by tier.
     */
    public function byTier($tier): JsonResponse
    {
        $plans = SponsoredPricingPlan::active()
            ->byTier($tier)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get featured pricing plans.
     */
    public function featured(): JsonResponse
    {
        $plans = SponsoredPricingPlan::active()
            ->featured()
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Get pricing comparison.
     */
    public function comparison(): JsonResponse
    {
        $plans = SponsoredPricingPlan::active()
            ->orderBy('sort_order', 'asc')
            ->get();

        // Group by tier for comparison
        $comparison = [];
        foreach ($plans as $plan) {
            $comparison[$plan->tier] = [
                'id' => $plan->plan_id,
                'name' => $plan->name,
                'tier' => $plan->tier,
                'price' => $plan->formatted_price,
                'duration' => $plan->duration_display,
                'features' => $plan->features ?? [],
                'visibility_settings' => $plan->visibility_settings ?? [],
                'badge_settings' => $plan->badge_settings ?? [],
                'placement_settings' => $plan->placement_settings ?? [],
                'promotion_settings' => $plan->promotion_settings ?? [],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    /**
     * Get recommended plan based on user input.
     */
    public function recommendation(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'advert_type' => 'required|string',
            'target_audience_size' => 'required|in:small,medium,large',
            'budget_range' => 'required|in:low,medium,high',
            'duration_preference' => 'required|in:short,medium,long',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Simple recommendation logic
        $recommendation = 'basic';
        
        if ($request->target_audience_size === 'large' || $request->budget_range === 'high') {
            $recommendation = 'premium';
        } elseif ($request->target_audience_size === 'medium' || $request->budget_range === 'medium') {
            $recommendation = 'plus';
        }

        $plan = SponsoredPricingPlan::active()
            ->byTier($recommendation)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'recommended_tier' => $recommendation,
                'recommended_plan' => $plan,
                'reasoning' => $this->getRecommendationReasoning($request, $recommendation),
            ],
        ]);
    }

    /**
     * Get recommendation reasoning.
     */
    private function getRecommendationReasoning($request, $recommendation): string
    {
        $reasons = [
            'basic' => 'Based on your requirements, the Basic plan offers great value for getting started with sponsored advertising.',
            'plus' => 'The Plus plan is recommended for your needs, providing enhanced visibility and features for growing your reach.',
            'premium' => 'For maximum impact and reach, the Premium plan will give you the best visibility across our platform.',
        ];

        return $reasons[$recommendation] ?? 'Our recommendation is based on your specific requirements and budget.';
    }
}
