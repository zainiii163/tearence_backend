<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Models\ProviderFollower;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProviderController extends Controller
{
    public function show($id): JsonResponse
    {
        $provider = User::with([
            'serviceProvider',
            'services' => function ($query) {
                $query->active()->with(['category', 'media'])->limit(10);
            },
            'receivedReviews' => function ($query) {
                $query->where('status', 'approved')->with('service:id,title')->limit(10);
            }
        ])->findOrFail($id);

        // Get provider statistics
        $stats = [
            'total_services' => $provider->services()->count(),
            'active_services' => $provider->services()->active()->count(),
            'total_orders' => $provider->services()->sum('orders'),
            'total_earnings' => $provider->services()->sum('starting_price'), // Simplified
            'average_rating' => $provider->receivedReviews()->avg('rating') ?? 0,
            'total_reviews' => $provider->receivedReviews()->count(),
            'followers_count' => ProviderFollower::getFollowerCount($id),
            'is_following' => Auth::check() ? ProviderFollower::isFollowing($id, Auth::id()) : false,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => $provider,
                'stats' => $stats,
            ],
        ]);
    }

    public function services(Request $request, $id): JsonResponse
    {
        $provider = User::findOrFail($id);
        
        $services = $provider->services()
            ->active()
            ->with(['category', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function reviews(Request $request, $id): JsonResponse
    {
        $provider = User::findOrFail($id);
        
        $reviews = $provider->receivedReviews()
            ->where('status', 'approved')
            ->with(['buyer:id,name,profile_photo', 'service:id,title'])
            ->orderBy('created_at', $request->sort ?? 'desc')
            ->paginate($request->limit ?? 10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function follow($id): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        if ($id == Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot follow yourself',
            ], 422);
        }

        $follow = ProviderFollower::follow($id, Auth::id());

        if (!$follow) {
            return response()->json([
                'success' => false,
                'message' => 'Already following this provider',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Provider followed successfully',
            'followers_count' => ProviderFollower::getFollowerCount($id),
        ]);
    }

    public function unfollow($id): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $deleted = ProviderFollower::unfollow($id, Auth::id());

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Not following this provider',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Provider unfollowed successfully',
            'followers_count' => ProviderFollower::getFollowerCount($id),
        ]);
    }

    public function followers($id): JsonResponse
    {
        $provider = User::findOrFail($id);
        
        $followers = ProviderFollower::where('provider_id', $id)
            ->with('follower:id,name,profile_photo,email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $followers,
        ]);
    }

    public function following($id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $following = ProviderFollower::where('follower_id', $id)
            ->with('provider:id,name,profile_photo,email')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $following,
        ]);
    }
}
