<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessAffiliateOffer;
use App\Models\FeaturedAdvert;
use App\Models\Listing;
use App\Models\PromotedAdvert;
use App\Models\SponsoredAdvert;
use App\Models\UserAffiliatePost;
use App\Services\PromoPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class PromoController extends Controller
{
    public function __construct(private PromoPricingService $promo)
    {
    }

    public function pricingPlans(Request $request): JsonResponse
    {
        $vertical = $request->query('vertical');
        $listingOnly = filter_var($request->query('listing_tiers', false), FILTER_VALIDATE_BOOLEAN);

        $plans = $this->promo->allActivePlans($vertical, $listingOnly)->map(function ($plan) {
            $days = (int) ($plan->duration_days ?? 30);
            $label = method_exists($plan, 'durationLabel')
                ? $plan->durationLabel()
                : ($days === 30 ? '1 month' : (($days % 7 === 0) ? (($days / 7) . ' weeks') : "{$days} days"));

            $price = (float) ($plan->price_usd ?? 0);

            return [
                'id' => $plan->tier ?? $plan->slug,
                'slug' => $plan->slug,
                'vertical' => $plan->vertical ?? 'all',
                'name' => $plan->name,
                'tier' => $plan->tier,
                'price' => $price,
                'price_usd' => $price,
                'price_label' => '$' . number_format($price, $price == floor($price) ? 0 : 2),
                'duration_days' => $days,
                'duration_label' => $label,
                'description' => $plan->description ?? null,
                'features' => $plan->features ?? [],
                'benefits' => $plan->features ?? [],
                'is_popular' => (bool) ($plan->is_popular ?? false),
                'popular' => (bool) ($plan->is_popular ?? false),
                'sort_order' => $plan->sort_order ?? 0,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $plans,
            'vertical' => $vertical,
            'default_free_duration_days' => PromoPricingService::DEFAULT_FREE_DURATION_DAYS,
            'matrix' => [
                'free' => ['days' => 3, 'usd' => 0],
                'paid' => ['days' => 7, 'usd' => 10],
                'promoted' => ['days' => 7, 'usd' => 20],
                'featured' => ['days' => 7, 'usd' => 30],
                'sponsored' => ['days' => 7, 'usd' => 40],
                'affiliate_cookies' => [
                    ['days' => 30, 'usd' => 20],
                    ['days' => 60, 'usd' => 30],
                    ['days' => 90, 'usd' => 40],
                ],
                'note' => 'Editable in Filament → Promo Pricing Plans. Values above are launch defaults only.',
            ],
        ]);
    }

    public function validateCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:64',
            'tier' => 'nullable|string|in:free,paid,promoted,featured,sponsored,cookie',
            'plan_slug' => 'nullable|string',
            'original_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $tier = $request->tier;
        $price = $request->original_price;

        if (!$price && $request->plan_slug) {
            $plan = $this->promo->findBySlug($request->plan_slug);
            if ($plan) {
                $price = (float) $plan->price_usd;
                $tier = $tier ?: $plan->tier;
            }
        }

        $result = $this->promo->validateCode($request->code, $tier, $price !== null ? (float) $price : null);

        return response()->json([
            'success' => (bool) ($result['valid'] ?? false),
            'message' => $result['message'] ?? '',
            'data' => $result,
        ], ($result['valid'] ?? false) ? 200 : 422);
    }

    /**
     * Extend live duration for a user's listing / promo advert.
     */
    public function extendDuration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:listing,featured,sponsored,promoted,affiliate_post,affiliate_offer',
            'id' => 'required',
            'plan_slug' => 'nullable|string',
            'duration_days' => 'nullable|integer|min:1|max:365',
            'promo_code' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::guard('api')->user() ?? Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $days = $request->duration_days;
        $plan = null;
        if ($request->plan_slug) {
            $plan = $this->promo->findBySlug($request->plan_slug);
            if ($plan) {
                $days = $days ?: (int) $plan->duration_days;
            }
        }
        $days = (int) ($days ?: PromoPricingService::DEFAULT_FREE_DURATION_DAYS);

        $type = $request->type;
        $id = $request->id;
        $record = null;

        try {
            switch ($type) {
                case 'listing':
                    $record = Listing::findOrFail($id);
                    $this->assertListingOwner($record, $user);
                    $record->end_date = now()->addDays($days)->toDateString();
                    if (Schema::hasColumn($record->getTable(), 'is_active')) {
                        $record->is_active = true;
                    }
                    $record->save();
                    break;

                case 'featured':
                    $record = FeaturedAdvert::findOrFail($id);
                    $this->assertCustomerOwner($record, $user);
                    $base = $record->expires_at && $record->expires_at->isFuture()
                        ? $record->expires_at
                        : now();
                    $record->expires_at = $base->copy()->addDays($days);
                    $record->is_active = true;
                    $record->save();
                    break;

                case 'sponsored':
                    $record = SponsoredAdvert::findOrFail($id);
                    $this->assertCustomerOwner($record, $user);
                    $base = ($record->expires_at && now()->lt($record->expires_at))
                        ? \Carbon\Carbon::parse($record->expires_at)
                        : now();
                    $record->expires_at = $base->addDays($days);
                    if (Schema::hasColumn($record->getTable(), 'is_active')) {
                        $record->is_active = true;
                    }
                    $record->save();
                    break;

                case 'promoted':
                    $record = PromotedAdvert::findOrFail($id);
                    $this->assertUserOwner($record, $user);
                    $base = ($record->expires_at && now()->lt($record->expires_at))
                        ? \Carbon\Carbon::parse($record->expires_at)
                        : now();
                    $record->expires_at = $base->addDays($days);
                    if (Schema::hasColumn($record->getTable(), 'is_active')) {
                        $record->is_active = true;
                    }
                    $record->save();
                    break;

                case 'affiliate_post':
                    $record = UserAffiliatePost::findOrFail($id);
                    $this->assertUserIdOwner($record, $user);
                    $base = ($record->expires_at && now()->lt($record->expires_at))
                        ? \Carbon\Carbon::parse($record->expires_at)
                        : now();
                    $record->expires_at = $base->addDays($days);
                    $record->is_active = true;
                    $record->save();
                    break;

                case 'affiliate_offer':
                    $record = BusinessAffiliateOffer::findOrFail($id);
                    $this->assertUserIdOwner($record, $user);
                    $base = ($record->expires_at && now()->lt($record->expires_at))
                        ? \Carbon\Carbon::parse($record->expires_at)
                        : now();
                    $record->expires_at = $base->addDays($days);
                    $record->is_active = true;
                    $record->save();
                    break;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Record not found'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }

        if ($request->promo_code) {
            $this->promo->redeemCode($request->promo_code);
        }

        return response()->json([
            'success' => true,
            'message' => "Duration extended by {$days} days",
            'data' => $record,
            'duration_days' => $days,
            'plan' => $plan,
        ]);
    }

    private function assertListingOwner($listing, $user): void
    {
        $uid = $user->user_id ?? $user->id ?? null;
        $cid = $user->customer_id ?? null;
        $ownerOk = ($cid && isset($listing->customer_id) && (int) $listing->customer_id === (int) $cid)
            || ($uid && isset($listing->user_id) && (int) $listing->user_id === (int) $uid);
        if (!$ownerOk) {
            throw new \RuntimeException('You do not own this listing');
        }
    }

    private function assertCustomerOwner($record, $user): void
    {
        $cid = $user->customer_id ?? null;
        if (!$cid || !isset($record->customer_id) || (int) $record->customer_id !== (int) $cid) {
            throw new \RuntimeException('You do not own this advert');
        }
    }

    private function assertUserOwner($record, $user): void
    {
        $uid = $user->user_id ?? $user->id ?? null;
        if (!$uid || !isset($record->user_id) || (int) $record->user_id !== (int) $uid) {
            throw new \RuntimeException('You do not own this advert');
        }
    }

    private function assertUserIdOwner($record, $user): void
    {
        $uid = $user->user_id ?? $user->id ?? null;
        if (!$uid || (int) $record->user_id !== (int) $uid) {
            throw new \RuntimeException('You do not own this post');
        }
    }
}
