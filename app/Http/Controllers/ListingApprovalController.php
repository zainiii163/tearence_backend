<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ListingApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all pending listings for admin approval
     */
    public function pending(Request $request)
    {
        // Check if user has permission to approve listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to approve listings'
            ], 403);
        }

        $listings = Listing::with(['customer', 'category', 'location'])
            ->where('approval_status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Pending listings retrieved successfully',
            'data' => $listings
        ]);
    }

    /**
     * Approve a listing
     */
    public function approve(Request $request, $listingId)
    {
        // Check if user has permission to approve listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to approve listings'
            ], 403);
        }

        $listing = Listing::findOrFail($listingId);
        
        // Determine post type based on admin selection
        $postType = $request->input('post_type', 'regular');
        $adminId = Auth::id();

        // Handle special admin posting
        if (Auth::user()->is_super_admin) {
            switch ($postType) {
                case 'sponsored':
                    $listing->is_sponsored = true;
                    $listing->sponsored_expires_at = now()->addDays(30);
                    break;
                case 'promoted':
                    $listing->is_promoted = true;
                    $listing->promoted_expires_at = now()->addDays(30);
                    break;
                case 'admin':
                    $listing->is_admin_post = true;
                    break;
            }
        }

        $listing->approve($adminId, $postType);

        return response()->json([
            'status' => 'success',
            'message' => 'Listing approved successfully',
            'data' => $listing->load(['customer', 'category', 'location'])
        ]);
    }

    /**
     * Reject a listing
     */
    public function reject(Request $request, $listingId)
    {
        // Check if user has permission to approve listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to reject listings'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $listing = Listing::findOrFail($listingId);
        $listing->reject($request->reason);

        return response()->json([
            'status' => 'success',
            'message' => 'Listing rejected successfully',
            'data' => $listing
        ]);
    }

    /**
     * Mark listing as harmful
     */
    public function markHarmful(Request $request, $listingId)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to moderate content'
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $listing = Listing::findOrFail($listingId);
        $listing->markAsHarmful($request->reason);

        return response()->json([
            'status' => 'success',
            'message' => 'Listing marked as harmful and deactivated',
            'data' => $listing
        ]);
    }

    /**
     * Get harmful listings for review
     */
    public function harmful(Request $request)
    {
        // Check if user has permission to manage listings
        if (!Auth::user()->canManageListings()) {
            return response()->json([
                'message' => 'You do not have permission to view harmful content'
            ], 403);
        }

        $listings = Listing::with(['customer', 'category', 'location'])
            ->where('is_harmful', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'message' => 'Harmful listings retrieved successfully',
            'data' => $listings
        ]);
    }

    /**
     * Get approval statistics
     */
    public function statistics()
    {
        // Check if user has permission to view analytics
        if (!Auth::user()->canViewAnalytics()) {
            return response()->json([
                'message' => 'You do not have permission to view statistics'
            ], 403);
        }

        $stats = [
            'pending' => Listing::where('approval_status', 'pending')->count(),
            'approved' => Listing::where('approval_status', 'approved')->count(),
            'rejected' => Listing::where('approval_status', 'rejected')->count(),
            'harmful' => Listing::where('is_harmful', true)->count(),
            'total' => Listing::count(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Approval statistics retrieved successfully',
            'data' => $stats
        ]);
    }

    /**
     * Delete old ads manually
     */
    public function deleteOldAds(Request $request)
    {
        $request->validate([
            'days_old' => 'required|integer|min:1'
        ]);

        $daysOld = $request->input('days_old', 21);
        $cutoffDate = now()->subDays($daysOld);

        $deletedCount = Listing::where('created_at', '<', $cutoffDate)
            ->where('approval_status', '!=', 'harmful')
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Old ads deleted successfully',
            'data' => [
                'deleted_count' => $deletedCount,
                'days_old' => $daysOld
            ]
        ]);
    }

    /**
     * Detect harmful content using AI
     */
    public function detectHarmful(Request $request)
    {
        $harmfulKeywords = [
            'scam', 'fraud', 'illegal', 'drugs', 'weapons', 'fake', 'counterfeit',
            'stolen', 'prohibited', 'banned', 'illegal', 'criminal', 'money laundering'
        ];

        $suspiciousPatterns = [
            '/\b\d{4,}\b/', // Long numbers (potential account numbers)
            '/\b\d{3}-\d{3}-\d{4}\b/', // Phone numbers
            '/\b[A-Z]{2,}\d{6,}\b/', // Serial numbers
        ];

        $listings = Listing::where('approval_status', 'pending')
            ->orWhere('approval_status', 'approved')
            ->get();

        $harmfulAds = [];

        foreach ($listings as $listing) {
            $harmfulScore = 0;
            $harmfulReasons = [];

            // Check for harmful keywords
            $text = strtolower($listing->title . ' ' . $listing->description);
            foreach ($harmfulKeywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $harmfulScore += 20;
                    $harmfulReasons[] = "Contains prohibited keyword: {$keyword}";
                }
            }

            // Check for suspicious patterns
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $text)) {
                    $harmfulScore += 15;
                    $harmfulReasons[] = "Contains suspicious pattern";
                }
            }

            // Check for unrealistic pricing
            if ($listing->price > 0) {
                $avgPrice = Listing::where('category_id', $listing->category_id)
                    ->where('price', '>', 0)
                    ->avg('price');

                if ($avgPrice && $listing->price < ($avgPrice * 0.1)) {
                    $harmfulScore += 25;
                    $harmfulReasons[] = "Unrealistically low price";
                }
            }

            if ($harmfulScore >= 50) {
                $harmfulAds[] = [
                    'id' => $listing->listing_id,
                    'title' => $listing->title,
                    'description' => $listing->description,
                    'harmful_reason' => implode(', ', $harmfulReasons),
                    'harmful_score' => $harmfulScore
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Harmful ads detection completed',
            'data' => [
                'harmful_ads' => $harmfulAds,
                'total_detected' => count($harmfulAds)
            ]
        ]);
    }

    /**
     * Delete multiple harmful ads
     */
    public function deleteHarmful(Request $request)
    {
        $request->validate([
            'ad_ids' => 'required|array',
            'ad_ids.*' => 'integer'
        ]);

        $deletedIds = $request->input('ad_ids');
        $deletedCount = Listing::whereIn('listing_id', $deletedIds)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Harmful ads deleted successfully',
            'data' => [
                'deleted_count' => $deletedCount,
                'deleted_ids' => $deletedIds
            ]
        ]);
    }

    /**
     * Update ad poster role
     */
    public function updatePosterRole(Request $request, $adId)
    {
        $request->validate([
            'poster_role' => 'required|in:normal,sponsored,promoted,admin'
        ]);

        $listing = Listing::findOrFail($adId);
        
        // Only super admins can set special roles
        if ($request->input('poster_role') !== 'normal' && !Auth::user()->is_super_admin) {
            return response()->json([
                'message' => 'Only super admins can set special poster roles'
            ], 403);
        }

        $listing->update([
            'post_type' => $request->input('poster_role'),
            'is_admin_post' => $request->input('poster_role') === 'admin'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ad poster role updated successfully',
            'data' => [
                'id' => $listing->listing_id,
                'poster_role' => $listing->post_type,
                'updated_at' => $listing->updated_at
            ]
        ]);
    }

    /**
     * Repost ad with updated date
     */
    public function repostAd($adId)
    {
        $listing = Listing::findOrFail($adId);
        
        // Check if user owns this listing or is admin
        if ($listing->customer_id !== Auth::id() && !Auth::user()->is_super_admin) {
            return response()->json([
                'message' => 'You can only repost your own ads'
            ], 403);
        }

        $originalDate = $listing->created_at;
        
        $listing->update([
            'created_at' => now(),
            'last_reposted_at' => now(),
            'approval_status' => 'pending', // Require re-approval
            'approved_by' => null,
            'approved_at' => null
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ad reposted successfully with updated date',
            'data' => [
                'id' => $listing->listing_id,
                'created_at' => $listing->created_at,
                'reposted' => true,
                'previous_created_at' => $originalDate
            ]
        ]);
    }
}
