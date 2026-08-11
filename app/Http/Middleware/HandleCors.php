<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = config('cors.allowed_origins', [
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'https://worldwideadverts.info',
            'https://www.worldwideadverts.info',
            'https://api.worldwideadverts.info',
        ]);

        $origin = $request->header('Origin');
        $originAllowed = $origin && in_array($origin, $allowedOrigins, true);

        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            $response = response('', $originAllowed || !$origin ? 204 : 403);

            if ($originAllowed) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers, X-Request-ID, Cache-Control, Pragma, Expires');
                $response->headers->set('Access-Control-Max-Age', '86400');
                $response->headers->set('Vary', 'Origin');
            }

            return $response;
        }

        $response = $next($request);

        // Only reflect allowed origins — never fall back to *
        if ($originAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, Access-Control-Request-Method, Access-Control-Request-Headers, X-Request-ID, Cache-Control, Pragma, Expires');
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Length, Content-Range, Authorization');
            $response->headers->set('Access-Control-Max-Age', '86400');
            $response->headers->set('Vary', 'Origin');
        }

        return $response;
    }
}
