<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->isKycVerified()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'KYC verification is required to access this feature.',
                    'kyc_status' => Auth::user()->kyc_status,
                    'kyc_rejection_reason' => Auth::user()->kyc_rejection_reason
                ], 403);
            }

            return redirect()->route('kyc.pending')
                ->with('error', 'KYC verification is required to access this feature.');
        }

        return $next($request);
    }
}
