<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\Customer;
use App\Models\Referral;
use App\Models\UserReferral;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReferralController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['validateCode', 'getReferralInfo']]);
    }

    /**
     * Get user's referral information
     */
    public function getMyReferral(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $customer_id = $user->customer_id;

        // Get or create referral for this user
        $referral = Referral::where('referrer_id', $customer_id)->first();
        
        if (!$referral) {
            $referral = Referral::create([
                'referrer_id' => $customer_id,
                'message' => 'Join me on this amazing platform!',
                'max_uses' => 50,
                'expires_at' => now()->addMonths(6),
            ]);
        }

        // Get referral statistics
        $stats = $referral->getStats();
        
        // Get recent referrals
        $recentReferrals = $referral->userReferrals()
            ->with(['referredUser', 'referrerUser'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'referral' => $referral,
                'stats' => $stats,
                'recent_referrals' => $recentReferrals,
            ]
        ]);
    }

    /**
     * Create a new referral
     */
    public function createReferral(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $customer_id = $user->customer_id;

        // Check if user already has a referral
        $existingReferral = Referral::where('referrer_id', $customer_id)->first();
        if ($existingReferral) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a referral code',
                'data' => $existingReferral
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:500',
            'max_uses' => 'nullable|integer|min:1|max:1000',
            'expires_at' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $referral = Referral::create([
                'referrer_id' => $customer_id,
                'message' => $request->message ?? 'Join me on this amazing platform!',
                'max_uses' => $request->max_uses ?? 50,
                'expires_at' => $request->expires_at ?? now()->addMonths(6),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Referral created successfully',
                'data' => $referral
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create referral: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update referral
     */
    public function updateReferral(Request $request, $referral_id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $referral = Referral::where('referral_id', $referral_id)
                           ->where('referrer_id', $user->customer_id)
                           ->first();

        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Referral not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:500',
            'max_uses' => 'nullable|integer|min:1|max:1000',
            'expires_at' => 'nullable|date|after:today',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        try {
            $referral->update($request->only([
                'message', 'max_uses', 'expires_at', 'is_active'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Referral updated successfully',
                'data' => $referral
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update referral: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate referral code (public endpoint)
     */
    public function validateCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $referral = Referral::findByCode($request->code);

        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code'
            ], 404);
        }

        if (!$referral->isValid()) {
            $reason = !$referral->is_active ? 'Referral is inactive' :
                     ($referral->expires_at && $referral->expires_at->isPast() ? 'Referral has expired' :
                     'Referral has reached maximum uses');

            return response()->json([
                'success' => false,
                'message' => $reason
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Valid referral code',
            'data' => [
                'referral_code' => $referral->referral_code,
                'referrer_name' => $referral->referrer->name ?? 'A friend',
                'message' => $referral->message,
                'remaining_uses' => $referral->remaining_uses,
                'expires_at' => $referral->expires_at,
            ]
        ]);
    }

    /**
     * Get referral info for registration page (public endpoint)
     */
    public function getReferralInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $referral = Referral::findByCode($request->code);

        if (!$referral || !$referral->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired referral code'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'referrer_name' => $referral->referrer->name ?? 'A friend',
                'message' => $referral->message,
                'benefits' => [
                    'You get a 20% discount on your first advert',
                    'Your friend gets a 10% discount on their next advert',
                ],
            ]
        ]);
    }

    /**
     * Get user's referral history and discounts
     */
    public function getMyReferralHistory(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $customer_id = $user->customer_id;

        // Get referrals I sent
        $sentReferrals = Referral::where('referrer_id', $customer_id)
            ->with(['userReferrals.referredUser'])
            ->first();

        // Get referrals I received (if any)
        $receivedReferral = UserReferral::where('referred_user_id', $customer_id)
            ->with(['referral', 'referrerUser'])
            ->first();

        // Get available discounts
        $availableDiscounts = [];

        if ($receivedReferral) {
            if (!$receivedReferral->referred_discount_used) {
                $availableDiscounts[] = [
                    'type' => 'referred_user',
                    'info' => $receivedReferral->getReferredDiscountInfo(),
                    'source' => 'Welcome discount from ' . ($receivedReferral->referrerUser->name ?? 'A friend'),
                ];
            }
        }

        if ($sentReferrals) {
            $availableReferrerDiscounts = $sentReferrals->userReferrals()
                ->withAvailableDiscount('referrer')
                ->get();

            foreach ($availableReferrerDiscounts as $userReferral) {
                $availableDiscounts[] = [
                    'type' => 'referrer',
                    'info' => $userReferral->getReferrerDiscountInfo(),
                    'source' => 'From referring ' . ($userReferral->referredUser->name ?? 'a friend'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sent_referrals' => $sentReferrals,
                'received_referral' => $receivedReferral,
                'available_discounts' => $availableDiscounts,
            ]
        ]);
    }

    /**
     * Share referral (generate share links)
     */
    public function shareReferral(Request $request, $referral_id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $referral = Referral::where('referral_id', $referral_id)
                           ->where('referrer_id', $user->customer_id)
                           ->first();

        if (!$referral) {
            return response()->json([
                'success' => false,
                'message' => 'Referral not found'
            ], 404);
        }

        $shareLinks = [
            'direct_link' => $referral->referral_link,
            'email' => 'mailto:?subject=Join me on this platform&body=Hi! I wanted to invite you to join this amazing platform. Use my referral code: ' . $referral->referral_code . ' Register here: ' . $referral->referral_link,
            'whatsapp' => 'https://wa.me/?text=Hi! I wanted to invite you to join this amazing platform. Use my referral code: ' . $referral->referral_code . ' Register here: ' . $referral->referral_link,
            'twitter' => 'https://twitter.com/intent/tweet?text=Hi! I wanted to invite you to join this amazing platform. Use my referral code: ' . $referral->referral_code . ' Register here: ' . $referral->referral_link,
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($referral->referral_link),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'referral' => $referral,
                'share_links' => $shareLinks,
                'share_text' => 'Hi! I wanted to invite you to join this amazing platform. Use my referral code: ' . $referral->referral_code . ' Register here: ' . $referral->referral_link,
            ]
        ]);
    }
}
