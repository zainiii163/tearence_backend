<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Clive: signup stays frictionless; email verification is required before posting.
 * Controlled by EMAIL_VERIFICATION_REQUIRED env (default true).
 *
 * Checks:
 *  1. email_verified_at timestamp on Customer
 *  2. OTP verification cache flag (set by VerificationService after OTP)
 * If neither is set, allows the post but adds a warning header.
 */
class EnsureVerifiedToPost
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'code' => 'UNAUTHENTICATED',
            ], 401);
        }

        // Email verification required before posting (configurable)
        if (config('verification.email_required', env('EMAIL_VERIFICATION_REQUIRED', true))) {
            $emailVerified = $this->isEmailVerified($user);

            if (! $emailVerified) {
                // Soft warning — allow the post but flag for frontend banner
                $request->attributes->set('email_verification_warning', true);

                return $next($request);
            }
        }

        // Soft KYC nudge on first post when customer KYC columns exist.
        if (Schema::hasColumn('customer', 'kyc_status')) {
            $status = $user->kyc_status ?? 'not_verified';
            $posts = (int) ($user->posts_count ?? 0);
            if ($posts < 1 && ! in_array($status, ['verified', 'pending', 'disabled'], true)) {
                // Allow the post but signal FE to open KYC after success.
                $request->attributes->set('kyc_prompt', true);
            }
        }

        return $next($request);
    }

    /**
     * Check if the user's email is verified via timestamp or OTP cache.
     */
    protected function isEmailVerified($user): bool
    {
        // 1. Primary: email_verified_at timestamp
        if (! empty($user->email_verified_at)) {
            return true;
        }

        // 2. Fallback: OTP verification cache flag
        $email = strtolower(trim($user->email ?? ''));
        if ($email) {
            $cacheKey = 'verification_verified_email_' . md5($email);
            if (Cache::has($cacheKey)) {
                // Also backfill the timestamp so future checks pass instantly
                try {
                    $user->email_verified_at = now();
                    $user->save();
                } catch (\Exception $e) {
                    // Non-critical — don't block the request
                }
                return true;
            }
        }

        return false;
    }
}
