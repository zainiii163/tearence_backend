<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceReviewController extends Controller
{
    public function index(Request $request, $serviceId): JsonResponse
    {
        $service = Service::findOrFail($serviceId);
        
        $reviews = $service->approvedReviews()
            ->with(['buyer:id,name,profile_photo'])
            ->orderBy('created_at', $request->sort ?? 'desc')
            ->paginate($request->limit ?? 10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function store(Request $request, $serviceId): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'service_title' => 'nullable|string|max:255',
        ]);

        $service = Service::findOrFail($serviceId);
        
        // Check if user has already reviewed this service
        $existingReview = ServiceReview::where('service_id', $serviceId)
            ->where('buyer_id', Auth::id())
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this service',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $review = ServiceReview::create([
                'service_id' => $serviceId,
                'buyer_id' => Auth::id(),
                'provider_id' => $service->user_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'service_title' => $request->service_title,
                'status' => 'approved', // Auto-approve for now
            ]);

            // Update service rating
            $this->updateServiceRating($serviceId);

            // Log activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'service_id' => $serviceId,
                'activity_type' => 'review',
                'message' => 'New review submitted',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => $review->load(['buyer:id,name,profile_photo']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $reviewId): JsonResponse
    {
        $request->validate([
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'sometimes|required|string|max:1000',
        ]);

        $review = ServiceReview::findOrFail($reviewId);

        if ($review->buyer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $review->update($request->only(['rating', 'comment']));

        // Update service rating
        $this->updateServiceRating($review->service_id);

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review,
        ]);
    }

    public function destroy($reviewId): JsonResponse
    {
        $review = ServiceReview::findOrFail($reviewId);

        if ($review->buyer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $serviceId = $review->service_id;
        $review->delete();

        // Update service rating
        $this->updateServiceRating($serviceId);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    private function updateServiceRating($serviceId)
    {
        $service = Service::find($serviceId);
        if ($service) {
            $avgRating = ServiceReview::where('service_id', $serviceId)
                ->where('status', 'approved')
                ->avg('rating');
            
            $reviewCount = ServiceReview::where('service_id', $serviceId)
                ->where('status', 'approved')
                ->count();

            $service->update([
                'rating' => round($avgRating, 2),
                'review_count' => $reviewCount,
            ]);
        }
    }
}
