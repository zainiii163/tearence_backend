<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Check if user is authenticated and has customer relationship
        if (!$user || !$user->customer) {
            abort(403, 'Access denied. Customer account required.');
        }
        
        // Check if customer is active
        if (!$user->customer->is_active) {
            abort(403, 'Your customer account is not active.');
        }
        
        return $next($request);
    }
}
