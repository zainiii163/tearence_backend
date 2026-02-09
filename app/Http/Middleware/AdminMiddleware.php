<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Check if user has admin role (adjust based on your user structure)
        // Option 1: Check role field
        if (isset($user->role) && $user->role === 'admin') {
            return $next($request);
        }

        // Option 2: Check is_admin field
        if (isset($user->is_admin) && $user->is_admin == 1) {
            return $next($request);
        }

        // Option 3: Check email for super admin (temporary solution)
        $adminEmails = [
            'admin@example.com',
            'superadmin@example.com',
            'test@example.com', // For testing
        ];

        if (in_array($user->email, $adminEmails)) {
            return $next($request);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Access denied. Admin privileges required.'
        ], 403);
    }
}
