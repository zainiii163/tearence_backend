<?php

namespace App\Http\Controllers\Concerns;

use App\Services\ListingPaymentGuard;
use App\Services\PromoPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shared create/confirm flow for paid listing promotions.
 * Free tiers go live immediately; paid tiers stay pending until payment is verified.
 */
trait EnforcesListingPromoPayment
{
    use VerifiesClientPayments;

    protected function promoPricing(): PromoPricingService
    {
        return app(PromoPricingService::class);
    }

    /**
     * Canonical promo matrix tier: free|paid|promoted|featured|sponsored|cookie
     */
    protected function resolveCanonicalPromoTier(?string $raw, string $context = 'listing'): string
    {
        $t = strtolower(trim((string) $raw));

        if ($t === '' || in_array($t, ['free', '0'], true)) {
            return 'free';
        }

        // Context-specific legacy keys
        if ($context === 'sponsored') {
            return match ($t) {
                'basic', 'standard' => 'paid',
                'plus', 'promoted' => 'promoted',
                'premium', 'featured' => 'featured',
                'sponsored', 'network', 'network_boost' => 'sponsored',
                default => ListingPaymentGuard::normalizeTier($t),
            };
        }

        if ($context === 'promoted') {
            return match ($t) {
                'promoted_basic', 'basic' => 'promoted',
                'promoted_plus', 'plus' => 'featured',
                'promoted_premium', 'premium', 'network_wide_boost' => 'sponsored',
                default => ListingPaymentGuard::normalizeTier($t),
            };
        }

        if ($context === 'banner') {
            return match ($t) {
                'standard', 'basic' => 'paid',
                'promoted' => 'promoted',
                'featured' => 'featured',
                'sponsored', 'network_boost' => 'sponsored',
                default => ListingPaymentGuard::normalizeTier($t),
            };
        }

        // Featured / buy-sell / generic
        if (in_array($t, ['basic', 'standard'], true)) {
            return 'free';
        }

        return ListingPaymentGuard::normalizeTier($t);
    }

    protected function resolvePromoAmountForTier(string $canonicalTier): float
    {
        if ($canonicalTier === 'free') {
            return 0.0;
        }

        $amount = (float) $this->promoPricing()->priceForTier($canonicalTier);

        if ($amount < 0.01) {
            // Paid tier must never resolve to $0 from a missing plan
            $amount = ListingPaymentGuard::defaultPaidPrice();
        }

        return ListingPaymentGuard::assertPaidAmount($amount, 'Promotion');
    }

    protected function resolvePromoDurationDays(string $canonicalTier): int
    {
        if ($canonicalTier === 'free') {
            return ListingPaymentGuard::freeDurationDays();
        }

        return (int) $this->promoPricing()->durationForTier($canonicalTier);
    }

    protected function requestHasPaymentReference(Request $request): bool
    {
        foreach (['payment_id', 'payment_reference', 'payment_transaction_id', 'transaction_id', 'payment_intent_id'] as $key) {
            $v = trim((string) $request->input($key, ''));
            if ($v !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify payment for a pending listing, or return JsonResponse on failure.
     *
     * @return array|JsonResponse
     */
    protected function verifyPromoPayment(
        Request $request,
        float $amount,
        string $purchaseType,
        string|int $purchaseId,
        string $currency = 'USD'
    ): array|JsonResponse {
        return $this->verifyClientPaymentOrFail(
            $request,
            $amount,
            $purchaseType,
            $purchaseId,
            $currency
        );
    }

    protected function paymentRequiredListingResponse(
        mixed $listing,
        float $amount,
        string $upsellType,
        ?string $message = null
    ): JsonResponse {
        if (is_object($listing)) {
            $id = $listing->id ?? (method_exists($listing, 'getKey') ? $listing->getKey() : null);
        } elseif (is_array($listing)) {
            $id = $listing['id'] ?? $listing['advert_id'] ?? $listing['listing_id'] ?? null;
        } else {
            $id = $listing;
        }

        return response()->json([
            'success' => true,
            'payment_required' => true,
            'requires_payment' => true,
            'amount' => round($amount, 2),
            'price' => round($amount, 2),
            'upsell_type' => $upsellType,
            'id' => $id,
            'listing_id' => $id,
            'advert_id' => $id,
            'data' => $listing,
            'message' => $message ?: 'Payment required to activate this paid promotion.',
        ], 201);
    }
}
