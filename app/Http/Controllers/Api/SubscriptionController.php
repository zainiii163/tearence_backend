<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\SubscriptionEntitlement;
use App\Services\PaymentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct(
        protected PaymentVerificationService $paymentVerification,
    ) {}

    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(fn ($plan) => [
                'id' => $plan->id,
                'slug' => $plan->slug,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => (float) $plan->price,
                'currency' => $plan->currency,
                'duration_days' => $plan->duration_days,
                'entitlements' => [
                    'paid_posts' => $plan->paid_posts,
                    'promoted_posts' => $plan->promoted_posts,
                    'featured_posts' => $plan->featured_posts,
                    'sponsored_posts' => $plan->sponsored_posts,
                ],
                'support_included' => $plan->support_included,
                'marketing_toolkit' => $plan->marketing_toolkit,
            ]);

        return response()->json(['plans' => $plans]);
    }

    public function status(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $subscription = UserSubscription::forUser($userId)
            ->active()
            ->with('plan', 'entitlements')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'active' => false,
                'subscription' => null,
            ]);
        }

        return response()->json([
            'active' => true,
            'subscription' => $this->formatSubscription($subscription),
        ]);
    }

    public function entitlements(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $subscription = UserSubscription::forUser($userId)
            ->active()
            ->with('entitlements')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'entitlements' => [],
                'has_active_subscription' => false,
            ]);
        }

        $entitlements = $subscription->entitlements->map(fn ($e) => [
            'type' => $e->entitlement_type,
            'total' => $e->total,
            'used' => $e->used,
            'remaining' => $e->remaining(),
            'period_start' => $e->period_start->toISOString(),
            'period_end' => $e->period_end->toISOString(),
        ]);

        return response()->json([
            'has_active_subscription' => true,
            'plan' => [
                'slug' => $subscription->plan->slug,
                'name' => $subscription->plan->name,
            ],
            'entitlements' => $entitlements,
        ]);
    }

    public function purchase(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'payment_id' => 'required|string',
            'payment_method' => 'required|string|in:stripe,paypal,crypto,mock',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        // Check for existing active subscription
        $existing = UserSubscription::forUser($user->id)
            ->active()
            ->where('plan_id', $plan->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You already have an active subscription for this plan.',
            ], 422);
        }

        // Verify payment
        $paymentVerified = $this->paymentVerification->assertVerifiedPayment(
            paymentId: $request->payment_id,
            expectedAmount: (float) $plan->price,
            expectedCurrency: $plan->currency,
            purchaseType: 'subscription',
            purchaseId: (string) $plan->id,
            userId: $user->id,
        );

        if (!$paymentVerified) {
            return response()->json([
                'message' => 'Payment verification failed.',
            ], 422);
        }

        $startsAt = now();
        $expiresAt = now()->addDays($plan->duration_days);

        return DB::transaction(function () use ($user, $plan, $startsAt, $expiresAt, $request) {
            // Cancel any existing active subscription for this user (different plan)
            UserSubscription::forUser($user->id)
                ->active()
                ->where('plan_id', '!=', $plan->id)
                ->update(['status' => 'expired']);

            // Create subscription
            $subscription = UserSubscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'payment_id' => $request->payment_id,
                'payment_method' => $request->payment_method,
                'amount_paid' => $plan->price,
                'currency' => $plan->currency,
            ]);

            // Create entitlements
            $entitlementTypes = [
                'paid_post' => $plan->paid_posts,
                'promoted_post' => $plan->promoted_posts,
                'featured_post' => $plan->featured_posts,
                'sponsored_post' => $plan->sponsored_posts,
            ];

            foreach ($entitlementTypes as $type => $total) {
                if ($total > 0) {
                    SubscriptionEntitlement::create([
                        'subscription_id' => $subscription->id,
                        'user_id' => $user->id,
                        'entitlement_type' => $type,
                        'total' => $total,
                        'used' => 0,
                        'period_start' => $startsAt,
                        'period_end' => $expiresAt,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Subscription activated successfully!',
                'subscription' => $this->formatSubscription($subscription->load('plan', 'entitlements')),
            ]);
        });
    }

    public function cancel(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $subscription = UserSubscription::forUser($userId)
            ->active()
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription found.',
            ], 404);
        }

        $subscription->cancel();

        return response()->json([
            'message' => 'Subscription cancelled. You can use remaining credits until ' . $subscription->expires_at->format('M d, Y') . '.',
            'subscription' => $this->formatSubscription($subscription->fresh('plan', 'entitlements')),
        ]);
    }

    public function checkEntitlement(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:paid_post,promoted_post,featured_post,sponsored_post',
        ]);

        $userId = $request->user()->id;

        $subscription = UserSubscription::forUser($userId)
            ->active()
            ->with('entitlements')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'has_entitlement' => false,
                'remaining' => 0,
                'message' => 'No active subscription. Subscribe to unlock post credits.',
            ]);
        }

        $entitlement = $subscription->entitlements
            ->firstWhere('entitlement_type', $request->type);

        if (!$entitlement || !$entitlement->hasRemaining()) {
            return response()->json([
                'has_entitlement' => false,
                'remaining' => 0,
                'message' => 'No remaining credits for this post type in your current billing period.',
                'upgrade_url' => '/dashboard?tab=subscriptions',
            ]);
        }

        return response()->json([
            'has_entitlement' => true,
            'remaining' => $entitlement->remaining(),
            'total' => $entitlement->total,
            'used' => $entitlement->used,
        ]);
    }

    public function consumeEntitlement(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:paid_post,promoted_post,featured_post,sponsored_post',
        ]);

        $userId = $request->user()->id;

        $subscription = UserSubscription::forUser($userId)
            ->active()
            ->with('entitlements')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'consumed' => false,
                'message' => 'No active subscription.',
            ], 422);
        }

        $entitlement = $subscription->entitlements
            ->firstWhere('entitlement_type', $request->type);

        if (!$entitlement || !$entitlement->hasRemaining()) {
            return response()->json([
                'consumed' => false,
                'remaining' => 0,
                'message' => 'No remaining credits for this post type.',
            ], 422);
        }

        $entitlement->consume();

        return response()->json([
            'consumed' => true,
            'remaining' => $entitlement->remaining(),
            'message' => 'Credit consumed. ' . $entitlement->remaining() . ' remaining.',
        ]);
    }

    private function formatSubscription(UserSubscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'plan' => [
                'slug' => $subscription->plan->slug,
                'name' => $subscription->plan->name,
                'price' => (float) $subscription->plan->price,
            ],
            'status' => $subscription->status,
            'starts_at' => $subscription->starts_at->toISOString(),
            'expires_at' => $subscription->expires_at->toISOString(),
            'cancelled_at' => $subscription->cancelled_at?->toISOString(),
            'days_remaining' => max(0, now()->diffInDays($subscription->expires_at, false)),
            'entitlements' => $subscription->entitlements->map(fn ($e) => [
                'type' => $e->entitlement_type,
                'total' => $e->total,
                'used' => $e->used,
                'remaining' => $e->remaining(),
            ]),
        ];
    }
}
