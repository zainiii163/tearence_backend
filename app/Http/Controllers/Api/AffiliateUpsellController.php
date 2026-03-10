<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\APIController;
use App\Models\AffiliatePost;
use App\Models\AffiliatePostUpsell;
use App\Models\AffiliateUpsellPlan;
use App\Models\RevenueTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AffiliateUpsellController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all available upsell plans.
     */
    public function getPlans(Request $request)
    {
        $plans = AffiliateUpsellPlan::active()
            ->ordered()
            ->get();

        return $this->successResponse($plans, 'Upsell plans retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get comparison of upsell plans.
     */
    public function getComparison(Request $request)
    {
        $plans = AffiliateUpsellPlan::active()
            ->ordered()
            ->get();

        // Transform plans for comparison view
        $comparison = $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price' => $plan->formatted_price,
                'duration' => $plan->duration_description,
                'benefits' => $plan->benefits_array,
                'badge' => $plan->badge_name,
                'visibility_level' => $this->getVisibilityLevel($plan->slug),
                'placement' => $this->getPlacementDescription($plan),
                'has_badge' => true,
                'email_inclusion' => $plan->weekly_email_blast,
                'social_promotion' => $plan->social_media_promotion,
            ];
        });

        return $this->successResponse($comparison, 'Upsell plans comparison retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Purchase upsell for an affiliate post.
     */
    public function purchaseUpsell(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'affiliate_post_id' => 'required|exists:affiliate_posts,id',
            'upsell_plan_id' => 'required|exists:affiliate_upsell_plans,id',
            'payment_method' => 'required|in:paypal,stripe,bank_transfer',
            'transaction_id' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $user = auth('api')->user();
            if (!$user || !isset($user->customer_id)) {
                return $this->errorResponse('User not authenticated or customer ID not found', Response::HTTP_UNAUTHORIZED);
            }

            // Get affiliate post and verify ownership
            $post = AffiliatePost::where('id', $request->affiliate_post_id)
                ->where('customer_id', $user->customer_id)
                ->first();

            if (!$post) {
                return $this->errorResponse('Affiliate post not found or unauthorized', Response::HTTP_NOT_FOUND);
            }

            // Get upsell plan
            $upsellPlan = AffiliateUpsellPlan::find($request->upsell_plan_id);
            if (!$upsellPlan || !$upsellPlan->is_active) {
                return $this->errorResponse('Upsell plan not found or inactive', Response::HTTP_NOT_FOUND);
            }

            // Check if there's already an active upsell for this post
            $existingActiveUpsell = AffiliatePostUpsell::where('affiliate_post_id', $post->id)
                ->active()
                ->first();

            if ($existingActiveUpsell) {
                return $this->errorResponse('This post already has an active upsell. Please wait for it to expire or contact support.', Response::HTTP_CONFLICT);
            }

            // Create revenue tracking record
            $revenue = RevenueTracking::create([
                'customer_id' => $user->customer_id,
                'ad_type' => 'affiliate_post_upsell',
                'amount' => $upsellPlan->price,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'status' => 'paid',
                'description' => "Affiliate post upsell - {$upsellPlan->name} for post: {$post->title}"
            ]);

            // Create post upsell record
            $postUpsell = AffiliatePostUpsell::create([
                'affiliate_post_id' => $post->id,
                'upsell_plan_id' => $upsellPlan->id,
                'customer_id' => $user->customer_id,
                'amount_paid' => $upsellPlan->price,
                'currency' => $upsellPlan->currency,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'payment_status' => 'paid',
                'is_active' => true,
            ]);

            // Update post's upsell tier
            $post->update(['upsell_tier' => $upsellPlan->slug]);

            // Update revenue tracking with post upsell ID
            $revenue->update(['affiliate_post_upsell_id' => $postUpsell->id]);

            DB::commit();

            // Load relationships for response
            $postUpsell->load(['affiliatePost', 'upsellPlan', 'customer']);

            return $this->successResponse($postUpsell, 'Upsell purchased successfully', Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get my upsell purchases.
     */
    public function getMyUpsells(Request $request)
    {
        $user = auth('api')->user();
        if (!$user || !isset($user->customer_id)) {
            return $this->errorResponse('User not authenticated or customer ID not found', Response::HTTP_UNAUTHORIZED);
        }

        $query = AffiliatePostUpsell::with(['affiliatePost', 'upsellPlan'])
            ->where('customer_id', $user->customer_id);

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Order by creation date
        $query->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);

        $total = $query->count();
        $upsells = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $this->successResponse([
            'items' => $upsells,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ], 'My upsells retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get upsells for a specific post.
     */
    public function getPostUpsells($postId, Request $request)
    {
        $user = auth('api')->user();
        if (!$user || !isset($user->customer_id)) {
            return $this->errorResponse('User not authenticated or customer ID not found', Response::HTTP_UNAUTHORIZED);
        }

        // Verify post ownership
        $post = AffiliatePost::where('id', $postId)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$post) {
            return $this->errorResponse('Affiliate post not found or unauthorized', Response::HTTP_NOT_FOUND);
        }

        $upsells = AffiliatePostUpsell::with(['upsellPlan'])
            ->where('affiliate_post_id', $postId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($upsells, 'Post upsells retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Cancel an active upsell.
     */
    public function cancelUpsell($id, Request $request)
    {
        $user = auth('api')->user();
        if (!$user || !isset($user->customer_id)) {
            return $this->errorResponse('User not authenticated or customer ID not found', Response::HTTP_UNAUTHORIZED);
        }

        $upsell = AffiliatePostUpsell::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();

        if (!$upsell) {
            return $this->errorResponse('Upsell not found or unauthorized', Response::HTTP_NOT_FOUND);
        }

        if (!$upsell->is_active) {
            return $this->errorResponse('Upsell is already inactive', Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            // Deactivate the upsell
            $upsell->update([
                'is_active' => false,
                'ends_at' => now(),
            ]);

            // Reset post to standard tier
            $upsell->affiliatePost->update(['upsell_tier' => 'standard']);

            DB::commit();

            return $this->successResponse($upsell, 'Upsell cancelled successfully', Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get upsell statistics.
     */
    public function getStats(Request $request)
    {
        $user = auth('api')->user();
        if (!$user || !isset($user->customer_id)) {
            return $this->errorResponse('User not authenticated or customer ID not found', Response::HTTP_UNAUTHORIZED);
        }

        $totalUpsells = AffiliatePostUpsell::where('customer_id', $user->customer_id)->count();
        $activeUpsells = AffiliatePostUpsell::where('customer_id', $user->customer_id)->active()->count();
        $totalSpent = AffiliatePostUpsell::where('customer_id', $user->customer_id)
            ->paid()
            ->sum('amount_paid');

        $expiringSoon = AffiliatePostUpsell::where('customer_id', $user->customer_id)
            ->active()
            ->where('ends_at', '<=', now()->addDays(7))
            ->count();

        $upsellsByPlan = AffiliatePostUpsell::with('upsellPlan')
            ->where('customer_id', $user->customer_id)
            ->paid()
            ->get()
            ->groupBy('upsellPlan.name')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_spent' => $group->sum('amount_paid'),
                ];
            });

        return $this->successResponse([
            'total_upsells' => $totalUpsells,
            'active_upsells' => $activeUpsells,
            'total_spent' => number_format($totalSpent, 2),
            'expiring_soon' => $expiringSoon,
            'upsells_by_plan' => $upsellsByPlan,
        ], 'Upsell statistics retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get recommendation for upsell tier.
     */
    public function getRecommendation(Request $request)
    {
        // This could be enhanced with AI/ML in the future
        // For now, return a simple recommendation based on business logic
        $recommendation = [
            'recommended_tier' => 'featured',
            'reason' => 'Most users choose Featured for the best results with optimal visibility and cost-effectiveness.',
            'conversion_boost' => '20-40%',
            'roi_estimate' => '3x-5x',
        ];

        return $this->successResponse($recommendation, 'Upsell recommendation retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get visibility level for upsell plan.
     */
    private function getVisibilityLevel($slug)
    {
        $levels = [
            'standard' => 'Basic',
            'promoted' => 'Enhanced',
            'featured' => 'Premium',
            'sponsored' => 'Maximum',
        ];

        return $levels[$slug] ?? 'Basic';
    }

    /**
     * Get placement description for upsell plan.
     */
    private function getPlacementDescription($plan)
    {
        $placements = [];

        if ($plan->homepage_placement) {
            $placements[] = 'Homepage';
        }

        if ($plan->top_of_category) {
            $placements[] = 'Top of Category';
        }

        if ($plan->category_top_placement) {
            $placements[] = 'Category Top';
        }

        if ($plan->homepage_slider) {
            $placements[] = 'Homepage Slider';
        }

        if (empty($placements)) {
            return 'Standard Placement';
        }

        return implode(', ', $placements);
    }
}
