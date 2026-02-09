<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get moderation dashboard data
     */
    public function getModerationDashboard()
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to access moderation dashboard'
            ], 403);
        }

        $stats = [
            'total_pending' => Listing::where('approval_status', 'pending')->count(),
            'total_approved' => Listing::where('approval_status', 'approved')->count(),
            'total_rejected' => Listing::where('approval_status', 'rejected')->count(),
            'total_harmful' => Listing::where('is_harmful', true)->count(),
            'total_posts' => Listing::count(),
            'admin_posts' => Listing::where('is_admin_post', true)->count(),
            'sponsored_posts' => Listing::where('is_sponsored', true)->count(),
            'promoted_posts' => Listing::where('is_promoted', true)->count(),
        ];

        // Recent pending posts
        $recentPending = Listing::with(['customer', 'category', 'location'])
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent harmful content
        $recentHarmful = Listing::with(['customer', 'category', 'location'])
            ->where('is_harmful', true)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Moderation dashboard data retrieved successfully',
            'data' => [
                'stats' => $stats,
                'recent_pending' => $recentPending,
                'recent_harmful' => $recentHarmful
            ]
        ]);
    }

    /**
     * Get all posts that need admin attention
     */
    public function getPostsNeedingAttention(Request $request)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to view posts needing attention'
            ], 403);
        }

        $query = Listing::with(['customer', 'category', 'location', 'currency'])
            ->where(function ($q) {
                $q->where('approval_status', 'pending')
                  ->orWhere('is_harmful', true)
                  ->orWhere('approval_status', 'rejected');
            });

        // Filter by type
        if ($type = $request->get('type')) {
            switch ($type) {
                case 'pending':
                    $query->where('approval_status', 'pending');
                    break;
                case 'harmful':
                    $query->where('is_harmful', true);
                    break;
                case 'rejected':
                    $query->where('approval_status', 'rejected');
                    break;
            }
        }

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $posts = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Posts needing attention retrieved successfully',
            'data' => $posts
        ]);
    }

    /**
     * Quick approve post with minimal options
     */
    public function quickApprove(Request $request, $postId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to approve posts'
            ], 403);
        }

        $listing = Listing::findOrFail($postId);
        
        if ($listing->approval_status !== 'pending') {
            return response()->json([
                'message' => 'This post is not pending approval'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $postType = $request->input('post_type', 'regular');
            $adminId = Auth::id();

            // Handle special admin posting
            if (Auth::user()->is_super_admin && $request->has('make_special')) {
                switch ($request->input('make_special')) {
                    case 'sponsored':
                        $listing->is_sponsored = true;
                        $listing->sponsored_expires_at = now()->addDays(30);
                        $postType = 'sponsored';
                        break;
                    case 'promoted':
                        $listing->is_promoted = true;
                        $listing->promoted_expires_at = now()->addDays(30);
                        $postType = 'promoted';
                        break;
                    case 'featured':
                        $listing->is_featured = true;
                        $listing->featured_expires_at = now()->addDays(30);
                        $postType = 'admin';
                        break;
                }
            }

            $listing->approve($adminId, $postType);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Post approved successfully',
                'data' => $listing->load(['customer', 'category', 'location'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick reject post with reason
     */
    public function quickReject(Request $request, $postId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to reject posts'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing = Listing::findOrFail($postId);
        
        if ($listing->approval_status !== 'pending') {
            return response()->json([
                'message' => 'This post is not pending approval'
            ], 400);
        }

        try {
            $listing->reject($request->reason);

            return response()->json([
                'status' => 'success',
                'message' => 'Post rejected successfully',
                'data' => $listing
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reject post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark post as harmful
     */
    public function markAsHarmful(Request $request, $postId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to moderate content'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $listing = Listing::findOrFail($postId);

        try {
            $listing->markAsHarmful($request->reason);

            return response()->json([
                'status' => 'success',
                'message' => 'Post marked as harmful and deactivated',
                'data' => $listing
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark post as harmful',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore harmful post
     */
    public function restoreHarmful($postId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to restore posts'
            ], 403);
        }

        $listing = Listing::findOrFail($postId);

        if (!$listing->is_harmful) {
            return response()->json([
                'message' => 'This post is not marked as harmful'
            ], 400);
        }

        try {
            $listing->update([
                'is_harmful' => false,
                'moderation_notes' => null,
                'status' => 'active',
                'approval_status' => 'pending', // Require re-approval
                'approved_by' => null,
                'approved_at' => null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Post restored successfully',
                'data' => $listing
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's post history for moderation
     */
    public function getUserPostHistory($userId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to view user post history'
            ], 403);
        }

        $user = User::findOrFail($userId);

        $posts = Listing::with(['category', 'location'])
            ->where('customer_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_posts' => Listing::where('customer_id', $userId)->count(),
            'approved_posts' => Listing::where('customer_id', $userId)->where('approval_status', 'approved')->count(),
            'pending_posts' => Listing::where('customer_id', $userId)->where('approval_status', 'pending')->count(),
            'rejected_posts' => Listing::where('customer_id', $userId)->where('approval_status', 'rejected')->count(),
            'harmful_posts' => Listing::where('customer_id', $userId)->where('is_harmful', true)->count(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'User post history retrieved successfully',
            'data' => [
                'user' => $user,
                'stats' => $stats,
                'posts' => $posts
            ]
        ]);
    }

    /**
     * Bulk actions on multiple posts
     */
    public function bulkAction(Request $request)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to perform bulk actions'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'post_ids' => 'required|array',
            'post_ids.*' => 'integer',
            'action' => 'required|in:approve,reject,delete,mark_harmful,restore',
            'reason' => 'required_if:action,reject,mark_harmful|string|max:500',
            'post_type' => 'required_if:action,approve|in:regular,sponsored,promoted,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $processedCount = 0;
            $action = $request->action;
            $adminId = Auth::id();

            foreach ($request->post_ids as $postId) {
                $listing = Listing::find($postId);
                if (!$listing) continue;

                switch ($action) {
                    case 'approve':
                        if ($listing->approval_status === 'pending') {
                            $listing->approve($adminId, $request->post_type);
                            $processedCount++;
                        }
                        break;
                    case 'reject':
                        if ($listing->approval_status === 'pending') {
                            $listing->reject($request->reason);
                            $processedCount++;
                        }
                        break;
                    case 'delete':
                        $listing->delete();
                        $processedCount++;
                        break;
                    case 'mark_harmful':
                        $listing->markAsHarmful($request->reason);
                        $processedCount++;
                        break;
                    case 'restore':
                        if ($listing->is_harmful) {
                            $listing->update([
                                'is_harmful' => false,
                                'moderation_notes' => null,
                                'status' => 'active',
                                'approval_status' => 'pending',
                                'approved_by' => null,
                                'approved_at' => null
                            ]);
                            $processedCount++;
                        }
                        break;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully processed {$processedCount} posts with action: {$action}",
                'data' => [
                    'processed_count' => $processedCount,
                    'action' => $action
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to perform bulk action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get moderation activity log
     */
    public function getModerationLog(Request $request)
    {
        // Check if user has permission to view analytics
        if (!Auth::user()->canViewAnalytics()) {
            return response()->json([
                'message' => 'You do not have permission to view moderation log'
            ], 403);
        }

        $query = Listing::with(['customer', 'category', 'approvedBy'])
            ->whereNotNull('approved_at')
            ->orWhere('is_harmful', true);

        // Filter by moderator
        if ($moderatorId = $request->get('moderator_id')) {
            $query->where('approved_by', $moderatorId);
        }

        // Filter by date range
        if ($startDate = $request->get('start_date')) {
            $query->whereDate('approved_at', '>=', $startDate);
        }
        if ($endDate = $request->get('end_date')) {
            $query->whereDate('approved_at', '<=', $endDate);
        }

        $activities = $query->orderBy('approved_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Moderation log retrieved successfully',
            'data' => $activities
        ]);
    }
}
