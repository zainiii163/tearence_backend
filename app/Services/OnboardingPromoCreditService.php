<?php

namespace App\Services;

use App\Models\CustomerPromoCredit;
use App\Models\PromoCreditUsage;
use App\Models\PromoRewardCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OnboardingPromoCreditService
{
    public const PLATFORMS = ['wwa', 'carservices', 'both'];

    public const TIERS = ['promoted', 'featured', 'sponsored'];

    public function normalizePlatform(?string $platform): string
    {
        $p = strtolower(trim((string) $platform));
        if (in_array($p, ['carservices', 'csl', 'car-services', 'carservicesltd'], true)) {
            return 'carservices';
        }
        if (in_array($p, ['wwa', 'worldwide', 'worldwideadverts'], true)) {
            return 'wwa';
        }

        return 'wwa';
    }

    public function normalizeTier(?string $tier): ?string
    {
        $t = strtolower(trim((string) $tier));
        if ($t === 'all') {
            return 'all';
        }
        if (in_array($t, self::TIERS, true)) {
            return $t;
        }

        return null;
    }

    /**
     * Validate an onboarding (free posts) code for a signup platform.
     *
     * @return array{valid: bool, message: string, code?: PromoRewardCode, grants?: array}
     */
    public function validateOnboardingCode(string $code, ?string $platform = 'wwa'): array
    {
        if (! Schema::hasTable('promo_reward_codes')) {
            return ['valid' => false, 'message' => 'Promo codes are not available yet'];
        }

        $platform = $this->normalizePlatform($platform);
        $reward = PromoRewardCode::whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])->first();

        if (! $reward || ! $reward->isCurrentlyValid()) {
            return ['valid' => false, 'message' => 'Invalid or expired promo code'];
        }

        $purpose = $reward->purpose ?? 'checkout';
        if (! in_array($purpose, ['onboarding', 'both'], true) && ($reward->type ?? '') !== 'free_posts') {
            return ['valid' => false, 'message' => 'This code is not valid for business signup'];
        }

        if (($reward->type ?? '') !== 'free_posts' && ($reward->grant_quantity ?? 0) < 1) {
            return ['valid' => false, 'message' => 'This code does not grant free posts'];
        }

        $codePlatform = strtolower((string) ($reward->platform ?? 'both'));
        if ($codePlatform !== 'both' && $codePlatform !== $platform) {
            $label = $codePlatform === 'carservices' ? 'Car Services Ltd' : 'Worldwide Adverts';

            return ['valid' => false, 'message' => "This code is only valid on {$label}"];
        }

        $grants = $this->resolveGrantRows($reward);
        if ($grants === []) {
            return ['valid' => false, 'message' => 'This code has no free post grants configured'];
        }

        return [
            'valid' => true,
            'message' => 'Promo code valid — free promotion credits will be added on signup',
            'code' => $reward->code,
            'code_id' => $reward->id,
            'platform' => $platform,
            'grants' => $grants,
            'description' => $reward->description,
        ];
    }

    /**
     * @return list<array{tier: string, quantity: int, duration_days: int}>
     */
    public function resolveGrantRows(PromoRewardCode $reward): array
    {
        $qty = max(1, (int) ($reward->grant_quantity ?: 1));
        $days = max(1, (int) ($reward->grant_duration_days ?: 7));
        $tier = $this->normalizeTier($reward->grant_tier);

        if ($tier === 'all' || $tier === null) {
            // "all" or missing tier → one credit per promo type
            return array_map(
                fn (string $t) => ['tier' => $t, 'quantity' => $qty, 'duration_days' => $days],
                self::TIERS
            );
        }

        return [['tier' => $tier, 'quantity' => $qty, 'duration_days' => $days]];
    }

    /**
     * Redeem onboarding code and create customer_promo_credits rows.
     *
     * @return array{ok: bool, message: string, credits?: list<CustomerPromoCredit>}
     */
    public function grantOnSignup(
        int $customerId,
        ?string $code,
        ?string $platform = 'wwa',
        ?int $businessId = null
    ): array {
        $code = trim((string) $code);
        if ($code === '') {
            return ['ok' => false, 'message' => 'No promo code provided'];
        }

        if (! Schema::hasTable('customer_promo_credits')) {
            return ['ok' => false, 'message' => 'Promo credits table missing — run migrations'];
        }

        $platform = $this->normalizePlatform($platform);
        $validated = $this->validateOnboardingCode($code, $platform);
        if (! ($validated['valid'] ?? false)) {
            return ['ok' => false, 'message' => $validated['message'] ?? 'Invalid code'];
        }

        /** @var PromoRewardCode $reward */
        $reward = PromoRewardCode::whereRaw('UPPER(code) = ?', [strtoupper($code)])->first();
        if (! $reward) {
            return ['ok' => false, 'message' => 'Invalid promo code'];
        }

        $maxPerUser = (int) ($reward->max_redemptions_per_user ?? 1);
        if ($maxPerUser > 0) {
            $prior = CustomerPromoCredit::where('customer_id', $customerId)
                ->where('promo_reward_code_id', $reward->id)
                ->count();
            if ($prior >= $maxPerUser) {
                return ['ok' => false, 'message' => 'You have already redeemed this promo code'];
            }
        }

        $created = [];
        DB::transaction(function () use ($reward, $customerId, $businessId, $platform, &$created) {
            foreach ($this->resolveGrantRows($reward) as $grant) {
                $created[] = CustomerPromoCredit::create([
                    'customer_id' => $customerId,
                    'business_id' => $businessId,
                    'promo_reward_code_id' => $reward->id,
                    'code_snapshot' => $reward->code,
                    'platform' => $platform,
                    'tier' => $grant['tier'],
                    'quantity_total' => $grant['quantity'],
                    'quantity_remaining' => $grant['quantity'],
                    'duration_days' => $grant['duration_days'],
                    'source' => 'onboarding',
                    'expires_at' => $reward->valid_until,
                ]);
            }
            $reward->incrementUses();
        });

        return [
            'ok' => true,
            'message' => 'Free promotion credits added to your account',
            'credits' => $created,
            'summary' => $this->summarizeCredits($created),
        ];
    }

    /**
     * @param  iterable<CustomerPromoCredit>  $credits
     * @return list<array{tier: string, quantity: int, duration_days: int}>
     */
    public function summarizeCredits(iterable $credits): array
    {
        $out = [];
        foreach ($credits as $c) {
            $out[] = [
                'id' => $c->id,
                'tier' => $c->tier,
                'quantity' => (int) $c->quantity_remaining,
                'duration_days' => (int) $c->duration_days,
                'platform' => $c->platform,
                'code' => $c->code_snapshot,
            ];
        }

        return $out;
    }

    public function listAvailableCredits(int $customerId): array
    {
        if (! Schema::hasTable('customer_promo_credits')) {
            return [];
        }

        $rows = CustomerPromoCredit::query()
            ->where('customer_id', $customerId)
            ->where('quantity_remaining', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('tier')
            ->orderBy('id')
            ->get();

        return $this->summarizeCredits($rows);
    }

    /**
     * Consume one credit for a listing activation.
     *
     * @return array{ok: bool, message: string, credit?: CustomerPromoCredit, duration_days?: int}
     */
    public function consumeCredit(
        int $customerId,
        string $tier,
        string $listingType,
        string|int $listingId
    ): array {
        $tier = $this->normalizeTier($tier);
        if (! $tier || $tier === 'all') {
            return ['ok' => false, 'message' => 'Invalid promotion tier for credit'];
        }

        if (! Schema::hasTable('customer_promo_credits')) {
            return ['ok' => false, 'message' => 'No promo credits available'];
        }

        return DB::transaction(function () use ($customerId, $tier, $listingType, $listingId) {
            $credit = CustomerPromoCredit::query()
                ->where('customer_id', $customerId)
                ->where('tier', $tier)
                ->where('quantity_remaining', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderBy('expires_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $credit) {
                return ['ok' => false, 'message' => "No free {$tier} post credits remaining"];
            }

            $credit->quantity_remaining = max(0, (int) $credit->quantity_remaining - 1);
            $credit->save();

            if (Schema::hasTable('promo_credit_usages')) {
                PromoCreditUsage::create([
                    'customer_promo_credit_id' => $credit->id,
                    'customer_id' => $customerId,
                    'listing_type' => $listingType,
                    'listing_id' => (string) $listingId,
                    'tier' => $tier,
                ]);
            }

            return [
                'ok' => true,
                'message' => "Used 1 free {$tier} post credit",
                'credit' => $credit,
                'duration_days' => (int) $credit->duration_days,
                'quantity_remaining' => (int) $credit->quantity_remaining,
            ];
        });
    }

    public function hasCredit(int $customerId, string $tier): bool
    {
        $tier = $this->normalizeTier($tier);
        if (! $tier || $tier === 'all' || ! Schema::hasTable('customer_promo_credits')) {
            return false;
        }

        return CustomerPromoCredit::query()
            ->where('customer_id', $customerId)
            ->where('tier', $tier)
            ->where('quantity_remaining', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
