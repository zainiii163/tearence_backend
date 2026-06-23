<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\Community;
use App\Models\CommunityPostCommunity;
use App\Models\PostReaction;
use App\Models\SavedPost;
use App\Models\UserReputation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CommunityPostController extends Controller
{
    /**
     * Get community feed with filters
     */
    public function index(Request $request)
    {
        $query = CommunityPost::query();

        // Filter by post type
        if ($request->has('post_type')) {
            if ($request->post_type === 'ad_thread') {
                $query->adThreads();
            } elseif ($request->post_type === 'discussion_thread') {
                $query->discussionThreads();
            }
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by location
        if ($request->has('location')) {
            $query->byLocation($request->location);
        }

        // Filter by country
        if ($request->has('country')) {
            $query->byCountry($request->country);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        // Filter by community
        if ($request->has('community_id')) {
            $query->byCommunity($request->community_id);
        }

        // Filter by verification status
        if ($request->has('verified_only') && $request->boolean('verified_only')) {
            $query->verified();
        }

        // Hide flagged posts
        $query->notFlagged();

        // Sort options
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'trending':
                $query->trending();
                break;
            case 'top_rated':
                $query->topRated();
                break;
            case 'pinned':
                $query->pinned()->orderBy('created_at', 'desc');
                break;
            case 'featured':
                $query->featured()->orderBy('created_at', 'desc');
                break;
            case 'sponsored':
                $query->sponsored()->orderBy('created_at', 'desc');
                break;
            case 'newest':
            default:
                $query->newest();
                break;
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $posts = $query->with(['user', 'category', 'communities', 'primaryCommunity'])
                      ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Get "For You" feed (personalized)
     */
    public function forYou(Request $request)
    {
        $user = auth()->user();
        $userCategories = $user->communityPosts()
                              ->pluck('category_id')
                              ->unique()
                              ->toArray();

        $query = CommunityPost::query();

        if (!empty($userCategories)) {
            $query->whereIn('category_id', $userCategories);
        }

        $query->notFlagged()->trending();

        $posts = $query->with(['user', 'category', 'communities', 'primaryCommunity'])
                      ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Get "Following" feed
     */
    public function following(Request $request)
    {
        $user = auth()->user();
        $followedCommunityIds = $user->followedCommunities()->pluck('community_id')->toArray();

        $query = CommunityPost::query();

        if (!empty($followedCommunityIds)) {
            $query->whereHas('communities', function ($q) use ($followedCommunityIds) {
                $q->whereIn('community_id', $followedCommunityIds);
            });
        }

        $query->notFlagged()->newest();

        $posts = $query->with(['user', 'category', 'communities', 'primaryCommunity'])
                      ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Get "Local" feed
     */
    public function local(Request $request)
    {
        $user = auth()->user();
        $query = CommunityPost::query();

        if ($user->country) {
            $query->byCountry($user->country);
        }

        if ($user->city) {
            $query->byCity($user->city);
        }

        $query->notFlagged()->newest();

        $posts = $query->with(['user', 'category', 'communities', 'primaryCommunity'])
                      ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Get a single post
     */
    public function show($id)
    {
        $post = CommunityPost::with(['user', 'category', 'communities', 'primaryCommunity', 'comments.user'])
                            ->where('post_id', $id)
                            ->firstOrFail();

        // Increment view count
        $post->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * Create a new post
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_type' => 'required|in:ad_thread,discussion_thread',
            'advert_type' => 'nullable|required_if:post_type,ad_thread|in:buy_sell,property,vehicle,job,service,event,funding,resorts_travel,banner,sponsored,affiliate,book',
            'advert_id' => 'nullable|required_if:post_type,ad_thread|uuid',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'media' => 'nullable|array',
            'category_id' => 'nullable|uuid|exists:category,category_id',
            'location' => 'nullable|string',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'discussion_type' => 'nullable|in:general,question,review,advice,report',
            'tags' => 'nullable|array',
            'community_ids' => 'required|array|min:1',
            'community_ids.*' => 'uuid|exists:communities,community_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $post = CommunityPost::create([
            'post_id' => Str::uuid(),
            'user_id' => auth()->id(),
            'post_type' => $request->post_type,
            'advert_type' => $request->advert_type,
            'advert_id' => $request->advert_id,
            'title' => $request->title,
            'content' => $request->content,
            'cover_image' => $request->cover_image,
            'media' => $request->media,
            'category_id' => $request->category_id,
            'location' => $request->location,
            'country' => $request->country,
            'city' => $request->city,
            'discussion_type' => $request->discussion_type,
            'tags' => $request->tags,
        ]);

        // Attach to communities
        foreach ($request->community_ids as $index => $communityId) {
            CommunityPostCommunity::create([
                'id' => Str::uuid(),
                'community_id' => $communityId,
                'post_id' => $post->post_id,
                'is_primary' => $index === 0,
            ]);

            // Increment community posts count
            $community = Community::find($communityId);
            if ($community) {
                $community->incrementPostsCount();
                if ($request->post_type === 'ad_thread') {
                    $community->incrementActiveAdsCount();
                }
            }
        }

        // Update user reputation
        $reputation = auth()->user()->getReputation();
        $reputation->incrementPostsCount();
        $reputation->incrementReputationScore(5);

        return response()->json([
            'success' => true,
            'data' => $post->load(['user', 'category', 'communities', 'primaryCommunity']),
            'message' => 'Post created successfully'
        ], 201);
    }

    /**
     * Update a post
     */
    public function update(Request $request, $id)
    {
        $post = CommunityPost::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this post'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
            'cover_image' => 'nullable|string',
            'media' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $post->update($request->only(['title', 'content', 'cover_image', 'media', 'tags']));

        return response()->json([
            'success' => true,
            'data' => $post->load(['user', 'category', 'communities', 'primaryCommunity']),
            'message' => 'Post updated successfully'
        ]);
    }

    /**
     * Delete a post
     */
    public function destroy($id)
    {
        $post = CommunityPost::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this post'
            ], 403);
        }

        // Decrement community posts count
        foreach ($post->communities as $community) {
            $community->decrementPostsCount();
            if ($post->post_type === 'ad_thread') {
                $community->decrementActiveAdsCount();
            }
        }

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully'
        ]);
    }

    /**
     * React to a post
     */
    public function react(Request $request, $id)
    {
        $post = CommunityPost::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reaction_type' => 'required|in:like,love,laugh,helpful,disagree',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $existingReaction = PostReaction::where('post_id', $post->post_id)
                                        ->where('user_id', auth()->id())
                                        ->first();

        if ($existingReaction) {
            if ($existingReaction->reaction_type === $request->reaction_type) {
                // Remove reaction
                $existingReaction->delete();
                $post->decrementReactions();
                return response()->json([
                    'success' => true,
                    'message' => 'Reaction removed'
                ]);
            } else {
                // Update reaction
                $existingReaction->update(['reaction_type' => $request->reaction_type]);
                return response()->json([
                    'success' => true,
                    'message' => 'Reaction updated'
                ]);
            }
        }

        PostReaction::create([
            'id' => Str::uuid(),
            'post_id' => $post->post_id,
            'user_id' => auth()->id(),
            'reaction_type' => $request->reaction_type,
        ]);

        $post->incrementReactions();

        // Update reputation for helpful reactions
        if ($request->reaction_type === 'helpful') {
            $reputation = $post->user->getReputation();
            $reputation->incrementHelpfulCount();
            $reputation->incrementReputationScore(2);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reaction added'
        ]);
    }

    /**
     * Save a post
     */
    public function save($id)
    {
        $post = CommunityPost::findOrFail($id);

        $existingSave = SavedPost::where('post_id', $post->post_id)
                                  ->where('user_id', auth()->id())
                                  ->first();

        if ($existingSave) {
            $existingSave->delete();
            $post->decrementSaves();
            return response()->json([
                'success' => true,
                'message' => 'Post removed from saved'
            ]);
        }

        SavedPost::create([
            'id' => Str::uuid(),
            'user_id' => auth()->id(),
            'post_id' => $post->post_id,
        ]);

        $post->incrementSaves();

        return response()->json([
            'success' => true,
            'message' => 'Post saved successfully'
        ]);
    }

    /**
     * Get user's saved posts
     */
    public function saved(Request $request)
    {
        $savedPosts = auth()->user()->savedPosts()
                                     ->with('post.user', 'post.category', 'post.communities')
                                     ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $savedPosts
        ]);
    }

    /**
     * Get user's posts
     */
    public function myPosts(Request $request)
    {
        $posts = auth()->user()->communityPosts()
                               ->with(['category', 'communities', 'primaryCommunity'])
                               ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Pin a post (admin/moderator only)
     */
    public function pin($id)
    {
        $post = CommunityPost::findOrFail($id);

        // Check if user is moderator/admin in any of the post's communities
        $canPin = false;
        foreach ($post->communities as $community) {
            if (auth()->user()->isModeratorOf($community->community_id)) {
                $canPin = true;
                break;
            }
        }

        if (!$canPin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to pin this post'
            ], 403);
        }

        $post->update(['is_pinned' => !$post->is_pinned]);

        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => $post->is_pinned ? 'Post pinned' : 'Post unpinned'
        ]);
    }

    /**
     * Flag a post
     */
    public function flag(Request $request, $id)
    {
        $post = CommunityPost::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $post->update([
            'is_flagged' => true,
            'flag_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Post flagged for review'
        ]);
    }
}
