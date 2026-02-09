<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Referral;
use App\Models\UserReferral;
use App\Models\Listing;

class ReferralService
{
    /**
     * Process referral during user registration
     */
    public static function processRegistrationReferral(Customer $customer, ?string $referralCode): ?UserReferral
    {
        if (!$referralCode) {
            return null;
        }

        $referral = Referral::findByCode($referralCode);
        
        if (!$referral || !$referral->isValid()) {
            return null;
        }

        // Don't allow self-referral
        if ($referral->referrer_id === $customer->customer_id) {
            return null;
        }

        // Check if user was already referred
        $existingReferral = UserReferral::where('referred_user_id', $customer->customer_id)
                                      ->first();
        if ($existingReferral) {
            return null;
        }

        try {
            // Increment referral usage
            if (!$referral->incrementUsage()) {
                return null;
            }

            // Create user referral record
            $userReferral = UserReferral::create([
                'referral_id' => $referral->referral_id,
                'referred_user_id' => $customer->customer_id,
                'referrer_user_id' => $referral->referrer_id,
                'status' => 'pending',
                'registered_at' => now(),
                'referred_discount_amount' => 20.00, // 20% discount for new user
                'referred_discount_type' => 'percentage',
                'referrer_discount_amount' => 10.00, // 10% discount for referrer
                'referrer_discount_type' => 'percentage',
            ]);

            $userReferral->markAsRegistered();

            return $userReferral;

        } catch (\Exception $e) {
            // Rollback usage increment if failed
            $referral->decrement('current_uses');
            return null;
        }
    }

    /**
     * Complete referral when user posts first listing
     */
    public static function completeReferral(Customer $customer): ?UserReferral
    {
        $userReferral = UserReferral::where('referred_user_id', $customer->customer_id)
                                  ->where('status', 'pending')
                                  ->first();

        if (!$userReferral) {
            return null;
        }

        $userReferral->markAsCompleted();
        return $userReferral;
    }

    /**
     * Apply referral discount to listing
     */
    public static function applyReferralDiscount(Listing $listing, float $originalPrice): array
    {
        $customer = $listing->customer;
        if (!$customer) {
            return [
                'original_price' => $originalPrice,
                'discount_amount' => 0,
                'final_price' => $originalPrice,
                'discount_applied' => false,
                'discount_source' => null,
            ];
        }

        // Check for referred user discount (welcome discount)
        $receivedReferral = UserReferral::where('referred_user_id', $customer->customer_id)
                                      ->where('referred_discount_used', false)
                                      ->first();

        if ($receivedReferral) {
            $discountInfo = $receivedReferral->getReferredDiscountInfo();
            $discountAmount = self::calculateDiscount($originalPrice, $discountInfo);
            
            if ($discountAmount > 0) {
                $receivedReferral->useReferredDiscount();
                
                return [
                    'original_price' => $originalPrice,
                    'discount_amount' => $discountAmount,
                    'final_price' => $originalPrice - $discountAmount,
                    'discount_applied' => true,
                    'discount_source' => 'welcome_referral',
                    'discount_info' => $discountInfo,
                ];
            }
        }

        // Check for referrer discount (from successful referrals)
        $availableReferrerDiscount = UserReferral::where('referrer_user_id', $customer->customer_id)
                                                ->where('referrer_discount_used', false)
                                                ->where('status', 'completed')
                                                ->first();

        if ($availableReferrerDiscount) {
            $discountInfo = $availableReferrerDiscount->getReferrerDiscountInfo();
            $discountAmount = self::calculateDiscount($originalPrice, $discountInfo);
            
            if ($discountAmount > 0) {
                $availableReferrerDiscount->useReferrerDiscount();
                
                return [
                    'original_price' => $originalPrice,
                    'discount_amount' => $discountAmount,
                    'final_price' => $originalPrice - $discountAmount,
                    'discount_applied' => true,
                    'discount_source' => 'referrer_reward',
                    'discount_info' => $discountInfo,
                ];
            }
        }

        return [
            'original_price' => $originalPrice,
            'discount_amount' => 0,
            'final_price' => $originalPrice,
            'discount_applied' => false,
            'discount_source' => null,
        ];
    }

    /**
     * Calculate discount amount based on discount info
     */
    private static function calculateDiscount(float $originalPrice, array $discountInfo): float
    {
        if ($discountInfo['type'] === 'percentage') {
            return $originalPrice * ($discountInfo['amount'] / 100);
        } else {
            return min($discountInfo['amount'], $originalPrice);
        }
    }

    /**
     * Get user's available discounts
     */
    public static function getAvailableDiscounts(Customer $customer): array
    {
        $discounts = [];

        // Welcome discount (if user was referred)
        $receivedReferral = UserReferral::where('referred_user_id', $customer->customer_id)
                                      ->where('referred_discount_used', false)
                                      ->first();

        if ($receivedReferral) {
            $discounts[] = [
                'type' => 'welcome',
                'info' => $receivedReferral->getReferredDiscountInfo(),
                'description' => 'Welcome discount for joining through a referral',
            ];
        }

        // Referrer rewards (from successful referrals)
        $referrerDiscounts = UserReferral::where('referrer_user_id', $customer->customer_id)
                                        ->where('referrer_discount_used', false)
                                        ->where('status', 'completed')
                                        ->get();

        foreach ($referrerDiscounts as $userReferral) {
            $discounts[] = [
                'type' => 'referrer_reward',
                'info' => $userReferral->getReferrerDiscountInfo(),
                'description' => 'Reward for successful referral',
                'referred_user' => $userReferral->referredUser->name ?? 'A friend',
            ];
        }

        return $discounts;
    }

    /**
     * Get referral statistics for a user
     */
    public static function getReferralStats(Customer $customer): array
    {
        $referral = $customer->referral;
        
        if (!$referral) {
            return [
                'has_referral' => false,
                'referral_code' => null,
                'referral_link' => null,
                'stats' => null,
            ];
        }

        return [
            'has_referral' => true,
            'referral_code' => $referral->referral_code,
            'referral_link' => $referral->referral_link,
            'stats' => $referral->getStats(),
        ];
    }
}
