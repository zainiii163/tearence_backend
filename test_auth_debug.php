<?php

/*
|--------------------------------------------------------------------------
| API Test Route
|--------------------------------------------------------------------------
|
| This is a temporary test route to debug authentication issues
|
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Add this to your api.php file for testing
Route::get('/test-auth', function (Request $request) {
    return response()->json([
        'authenticated' => auth('api')->check(),
        'user' => auth('api')->user() ? [
            'id' => auth('api')->user()->customer_id,
            'email' => auth('api')->user()->email,
            'name' => auth('api')->user()->first_name . ' ' . auth('api')->user()->last_name,
        ] : null,
        'token_valid' => $request->bearerToken() ? 'Token present' : 'No token',
        'headers' => $request->headers->all(),
    ]);
})->middleware('api');

Route::get('/test-policy', function (Request $request) {
    $user = auth('api')->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }
    
    return response()->json([
        'can_create_vehicle' => $user->can('create', \App\Models\Vehicle::class),
        'user_type' => get_class($user),
        'is_authenticated' => $user->isAuthenticated(),
        'is_admin' => $user->isAdmin(),
    ]);
})->middleware('auth:api');
