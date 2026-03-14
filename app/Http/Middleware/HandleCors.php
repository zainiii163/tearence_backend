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
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        // Get allowed origins based on environment
        $allowedOrigins = [
            'http://localhost:3000',
            'http://127.0.0.1:3000', 
            'http://localhost:8000', 
            'http://127.0.0.1:8000',
            'https://worldwideadverts.info',
            'https://www.worldwideadverts.info',
            'https://api.worldwideadverts.info'
        ];

        $origin = $request->header('Origin');
        
        // Set CORS headers
        if ($origin && in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }
        
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', '*');
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Length,Content-Range');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
