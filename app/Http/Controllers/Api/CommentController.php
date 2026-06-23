<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\UserReputation;
use App\Models\CommunityPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    /**
     * Get comments for a post
     */
    public function index(Request $request, $postId)
    {
        $post = CommunityPost::findOrFail($postId);

        $query = Comment::where('post_id', $post->post_id);

        // Filter by comment type
        if ($request->has('comment_type')) {
            $query->byType($request->comment_type);
        }

        // Show only top-level comments by default
        if (!$request->has('include_replies') || !$request->boolean('include_replies')) {
            $query->topLevel();
        }

        // Hide flagged/hidden comments
        $query->notFlagged()->notHidden();

        $comments = $query->with(['user', 'replies.user'])
                         ->orderBy('created_at', 'desc')
                         ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    /**
     * Get a single comment
     */
    public function show($id)
    {
        $comment = Comment::with(['user', 'post', 'parent', 'replies.user'])
                         ->where('comment_id', $id)
                         ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $comment
        ]);
    }

    /**
     * Create a new comment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|uuid|exists:community_posts,post_id',
            'parent_id' => 'nullable|uuid|exists:comments,comment_id',
            'content' => 'required|string',
            'comment_type' => 'nullable|in:question,review,tip,report_experience,general',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $post = CommunityPost::findOrFail($request->post_id);

        $comment = Comment::create([
            'comment_id' => Str::uuid(),
            'post_id' => $request->post_id,
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'content' => $request->content,
            'comment_type' => $request->comment_type ?? 'general',
        ]);

        // Increment post comments count
        $post->incrementComments();

        // If it's a reply, increment parent's replies count
        if ($request->parent_id) {
            $parent = Comment::find($request->parent_id);
            if ($parent) {
                $parent->incrementReplies();
            }
        }

        // Update user reputation
        $reputation = auth()->user()->getReputation();
        $reputation->incrementCommentsCount();
        $reputation->incrementReputationScore(2);

        return response()->json([
            'success' => true,
            'data' => $comment->load(['user', 'post']),
            'message' => 'Comment created successfully'
        ], 201);
    }

    /**
     * Update a comment
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this comment'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'comment_type' => 'nullable|in:question,review,tip,report_experience,general',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $comment->update($request->only(['content', 'comment_type']));

        return response()->json([
            'success' => true,
            'data' => $comment->load(['user', 'post']),
            'message' => 'Comment updated successfully'
        ]);
    }

    /**
     * Delete a comment
     */
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this comment'
            ], 403);
        }

        // Decrement post comments count
        $post = $comment->post;
        if ($post) {
            $post->decrementComments();
        }

        // If it's a reply, decrement parent's replies count
        if ($comment->parent_id) {
            $parent = Comment::find($comment->parent_id);
            if ($parent) {
                $parent->decrementReplies();
            }
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully'
        ]);
    }

    /**
     * React to a comment
     */
    public function react(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reaction_type' => 'required|in:like,love,laugh,helpful,disagree',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $existingReaction = CommentReaction::where('comment_id', $comment->comment_id)
                                            ->where('user_id', auth()->id())
                                            ->first();

        if ($existingReaction) {
            if ($existingReaction->reaction_type === $request->reaction_type) {
                // Remove reaction
                $existingReaction->delete();
                $comment->decrementReactions();
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

        CommentReaction::create([
            'id' => Str::uuid(),
            'comment_id' => $comment->comment_id,
            'user_id' => auth()->id(),
            'reaction_type' => $request->reaction_type,
        ]);

        $comment->incrementReactions();

        // Update reputation for helpful reactions
        if ($request->reaction_type === 'helpful') {
            $reputation = $comment->user->getReputation();
            $reputation->incrementHelpfulCount();
            $reputation->incrementReputationScore(2);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reaction added'
        ]);
    }

    /**
     * Get replies to a comment
     */
    public function replies($id)
    {
        $comment = Comment::findOrFail($id);

        $replies = $comment->replies()
                          ->with('user')
                          ->notFlagged()
                          ->notHidden()
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $replies
        ]);
    }

    /**
     * Flag a comment
     */
    public function flag(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $comment->update([
            'is_flagged' => true,
            'flag_reason' => $request->reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment flagged for review'
        ]);
    }

    /**
     * Hide a comment (moderator only)
     */
    public function hide($id)
    {
        $comment = Comment::findOrFail($id);

        // Check if user is moderator/admin in the post's community
        $canHide = false;
        $post = $comment->post;
        if ($post) {
            foreach ($post->communities as $community) {
                if (auth()->user()->isModeratorOf($community->community_id)) {
                    $canHide = true;
                    break;
                }
            }
        }

        if (!$canHide) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to hide this comment'
            ], 403);
        }

        $comment->update(['is_hidden' => !$comment->is_hidden]);

        return response()->json([
            'success' => true,
            'data' => $comment,
            'message' => $comment->is_hidden ? 'Comment hidden' : 'Comment visible'
        ]);
    }
}
