<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Clive: signup stays frictionless; email verification is required before posting.
 * Controlled by EMAIL_VERIFICATION_REQUIRED env (default true).
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
            $emailVerified = ! empty($user->email_verified_at);
            if (! $emailVerified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please verify your email before posting. You can still browse and complete your profile.',
                    'code' => 'EMAIL_VERIFICATION_REQUIRED',
                    'verification_url' => config('verification.frontend_url', 'https://worldwideadverts.info') . '/verify-email',
                ], 403);
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
}
