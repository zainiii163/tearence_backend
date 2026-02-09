<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all posts for a specific category (for admin management)
     */
    public function getCategoryPosts(Request $request, $categoryId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to manage category posts'
            ], 403);
        }

        $category = Category::findOrFail($categoryId);
        
        $query = Listing::with(['customer', 'location', 'currency'])
            ->where('category_id', $categoryId);

        // Filter by approval status
        if ($status = $request->get('status')) {
            $query->where('approval_status', $status);
        }

        // Filter by post type
        if ($postType = $request->get('post_type')) {
            $query->where('post_type', $postType);
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
            'message' => 'Category posts retrieved successfully',
            'data' => [
                'category' => $category,
                'posts' => $posts
            ]
        ]);
    }

    /**
     * Create a new post as admin for any category
     */
    public function createAdminPost(Request $request)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to create admin posts'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:category,category_id',
            'location_id' => 'required|exists:location,location_id',
            'currency_id' => 'nullable|exists:currency,currency_id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'post_type' => 'required|in:regular,sponsored,promoted,admin',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'status' => 'nullable|in:active,inactive',
            'featured_duration' => 'nullable|integer|min:1|max:365',
            'sponsored_duration' => 'nullable|integer|min:1|max:365',
            'promoted_duration' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $listing = new Listing();
            $listing->customer_id = Auth::id(); // Admin creates the post
            $listing->category_id = $request->category_id;
            $listing->location_id = $request->location_id;
            $listing->currency_id = $request->currency_id;
            $listing->title = $request->title;
            $listing->description = $request->description;
            $listing->price = $request->price ?? 0;
            $listing->status = $request->status ?? 'active';
            $listing->approval_status = 'approved'; // Admin posts are auto-approved
            $listing->approved_by = Auth::id();
            $listing->approved_at = now();
            $listing->post_type = $request->post_type;
            $listing->is_admin_post = true;
            
            // Set display name for admin posts - use generic admin identifier to protect identity
            $listing->display_name = 'Admin';

            // Handle post type features
            if ($request->post_type === 'sponsored' && $request->sponsored_duration) {
                $listing->is_sponsored = true;
                $listing->sponsored_expires_at = now()->addDays($request->sponsored_duration);
            }

            if ($request->post_type === 'promoted' && $request->promoted_duration) {
                $listing->is_promoted = true;
                $listing->promoted_expires_at = now()->addDays($request->promoted_duration);
            }

            if ($request->post_type === 'admin' || $request->featured_duration) {
                $listing->is_featured = true;
                $listing->featured_expires_at = now()->addDays($request->featured_duration ?? 30);
            }

            // Handle images
            if ($request->has('images')) {
                $listing->attachments = $request->images;
            }

            $listing->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Admin post created successfully',
                'data' => $listing->load(['category', 'location', 'customer'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create admin post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update any post (admin can edit all posts)
     */
    public function updatePost(Request $request, $postId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to edit posts'
            ], 403);
        }

        $listing = Listing::findOrFail($postId);

        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:category,category_id',
            'location_id' => 'nullable|exists:location,location_id',
            'currency_id' => 'nullable|exists:currency,currency_id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'post_type' => 'nullable|in:regular,sponsored,promoted,admin',
            'status' => 'nullable|in:active,inactive',
            'approval_status' => 'nullable|in:pending,approved,rejected',
            'images' => 'nullable|array',
            'images.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update listing fields
            $listing->fill($request->only([
                'category_id',
                'location_id',
                'currency_id',
                'title',
                'description',
                'price',
                'post_type',
                'status',
                'approval_status'
            ]));

            // Handle approval status changes
            if ($request->has('approval_status')) {
                if ($request->approval_status === 'approved') {
                    $listing->approved_by = Auth::id();
                    $listing->approved_at = now();
                    $listing->rejection_reason = null;
                } elseif ($request->approval_status === 'rejected') {
                    $listing->rejection_reason = $request->rejection_reason ?? 'Rejected by admin';
                }
            }

            // Handle images
            if ($request->has('images')) {
                $listing->attachments = $request->images;
            }

            // Mark as edited by admin
            $listing->is_admin_post = true;
            $listing->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Post updated successfully',
                'data' => $listing->load(['category', 'location', 'customer'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete any post (admin can delete all posts)
     */
    public function deletePost($postId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to delete posts'
            ], 403);
        }

        $listing = Listing::findOrFail($postId);

        try {
            $listing->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Post deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete post',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all pending posts across all categories
     */
    public function getPendingPosts(Request $request)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to view pending posts'
            ], 403);
        }

        $query = Listing::with(['customer', 'category', 'location', 'currency'])
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'asc');

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $posts = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Pending posts retrieved successfully',
            'data' => $posts
        ]);
    }

    /**
     * Bulk approve multiple posts
     */
    public function bulkApprove(Request $request)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to approve posts'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'post_ids' => 'required|array',
            'post_ids.*' => 'integer',
            'post_type' => 'nullable|in:regular,sponsored,promoted,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $approvedCount = 0;
            $postType = $request->post_type ?? 'regular';
            $adminId = Auth::id();

            foreach ($request->post_ids as $postId) {
                $listing = Listing::find($postId);
                if ($listing && $listing->approval_status === 'pending') {
                    $listing->approve($adminId, $postType);
                    $approvedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully approved {$approvedCount} posts",
                'data' => [
                    'approved_count' => $approvedCount,
                    'post_type' => $postType
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to approve posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk reject multiple posts
     */
    public function bulkReject(Request $request)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to reject posts'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'post_ids' => 'required|array',
            'post_ids.*' => 'integer',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $rejectedCount = 0;
            $reason = $request->reason;

            foreach ($request->post_ids as $postId) {
                $listing = Listing::find($postId);
                if ($listing && $listing->approval_status === 'pending') {
                    $listing->reject($reason);
                    $rejectedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully rejected {$rejectedCount} posts",
                'data' => [
                    'rejected_count' => $rejectedCount,
                    'reason' => $reason
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to reject posts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category statistics for admin dashboard
     */
    public function getCategoryStats()
    {
        // Check if user has permission to view analytics
        if (!Auth::user()->canViewAnalytics()) {
            return response()->json([
                'message' => 'You do not have permission to view statistics'
            ], 403);
        }

        $stats = Category::withCount(['listings' => function($query) {
                $query->where('approval_status', 'approved');
            }])
            ->withCount(['listings as pending_count' => function($query) {
                $query->where('approval_status', 'pending');
            }])
            ->withCount(['listings as rejected_count' => function($query) {
                $query->where('approval_status', 'rejected');
            }])
            ->get()
            ->map(function ($category) {
                return [
                    'category_id' => $category->category_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'approved_posts' => $category->listings_count,
                    'pending_posts' => $category->pending_count,
                    'rejected_posts' => $category->rejected_count,
                    'total_posts' => $category->listings_count + $category->pending_count + $category->rejected_count,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Category statistics retrieved successfully',
            'data' => $stats
        ]);
    }
}
