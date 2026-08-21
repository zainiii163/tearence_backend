<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CommunityAuthHelper;
use App\Helpers\MediaUrlHelper;
use App\Models\CommunityPost;
use App\Models\Community;
use App\Models\CommunityPostCommunity;
use App\Models\CommunityPollVote;
use App\Models\PostReaction;
use App\Models\SavedPost;
use App\Models\UserReputation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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

        // Filter by community (id or slug)
        if ($request->filled('community_id') || $request->filled('community_slug')) {
            $query->byCommunity($request->input('community_id') ?: $request->input('community_slug'));
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
            case 'top-rated':
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

        $this->attachPollState($posts);

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Get "For You" feed (personalized) — soft-fails to trending feed
     */
    public function forYou(Request $request)
    {
        try {
            $user = CommunityAuthHelper::usersUser(null, false);
            $query = CommunityPost::query()->notFlagged();

            if ($user) {
                $userCategories = [];
                try {
                    $userCategories = $user->communityPosts()
                        ->whereNotNull('category_id')
                        ->pluck('category_id')
                        ->unique()
                        ->filter()
                        ->values()
                        ->toArray();
                } catch (\Throwable $e) {
                    // ignore preference errors
                }

                if (!empty($userCategories)) {
                    $query->whereIn('category_id', $userCategories);
                }
            }

            $sort = $request->get('sort', 'trending');
            if ($sort === 'newest') {
                $query->newest();
            } elseif (in_array($sort, ['top_rated', 'top-rated'], true)) {
                $query->topRated();
            } else {
                $query->trending();
            }

            $posts = $query->with(['user', 'category', 'communities'])
                ->paginate($request->get('per_page', 20));
            $this->attachPollState($posts);

            return response()->json([
                'success' => true,
                'data' => $posts,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Community forYou failed: '.$e->getMessage());

            $posts = CommunityPost::query()
                ->notFlagged()
                ->trending()
                ->with(['user', 'category', 'communities'])
                ->paginate($request->get('per_page', 20));
            $this->attachPollState($posts);

            return response()->json([
                'success' => true,
                'data' => $posts,
                'warning' => 'Fell back to trending feed',
            ]);
        }
    }

    /**
     * Get "Following" feed
     */
    public function following(Request $request)
    {
        try {
            $user = CommunityAuthHelper::usersUser(null, false);
            $query = CommunityPost::query()->notFlagged();

            $communityIds = [];
            if ($user) {
                try {
                    $followedIds = $user->followedCommunities()
                        ->pluck('communities.community_id')
                        ->filter()
                        ->values()
                        ->all();
                    if (empty($followedIds)) {
                        $followedIds = $user->followedCommunities()->pluck('community_id')->filter()->values()->all();
                    }

                    $joinedIds = [];
                    try {
                        $joinedIds = $user->communities()
                            ->pluck('communities.community_id')
                            ->filter()
                            ->values()
                            ->all();
                        if (empty($joinedIds)) {
                            $joinedIds = $user->communities()->pluck('community_id')->filter()->values()->all();
                        }
                    } catch (\Throwable $e) {
                        $joinedIds = [];
                    }

                    $communityIds = array_values(array_unique(array_merge($followedIds, $joinedIds)));
                } catch (\Throwable $e) {
                    $communityIds = [];
                }
            }

            // No follows/memberships → empty feed (not global dump)
            if (empty($communityIds)) {
                $empty = CommunityPost::query()
                    ->whereRaw('1 = 0')
                    ->paginate($request->get('per_page', 20));

                return response()->json([
                    'success' => true,
                    'data' => $empty,
                    'message' => 'Follow or join communities to see posts here.',
                ]);
            }

            $query->whereHas('communities', function ($q) use ($communityIds) {
                $q->whereIn('communities.community_id', $communityIds);
            });

            $query->newest();

            $posts = $query->with(['user', 'category', 'communities'])
                ->paginate($request->get('per_page', 20));
            $this->attachPollState($posts);

            return response()->json([
                'success' => true,
                'data' => $posts,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Community following failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Could not load following feed',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get "Local" feed
     */
    public function local(Request $request)
    {
        try {
            $user = auth()->user();
            $query = CommunityPost::query()->notFlagged();

            if ($user) {
                if (!empty($user->country)) {
                    $query->byCountry($user->country);
                }
                if (!empty($user->city)) {
                    $query->byCity($user->city);
                }
            }

            $query->newest();

            $posts = $query->with(['user', 'category', 'communities'])
                ->paginate($request->get('per_page', 20));
            $this->attachPollState($posts);

            return response()->json([
                'success' => true,
                'data' => $posts,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Community local failed: '.$e->getMessage());

            $posts = CommunityPost::query()
                ->notFlagged()
                ->newest()
                ->with(['user', 'category', 'communities'])
                ->paginate($request->get('per_page', 20));
            $this->attachPollState($posts);

            return response()->json([
                'success' => true,
                'data' => $posts,
                'warning' => 'Fell back to newest feed',
            ]);
        }
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
        $this->attachPollState($post);

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * Upload cover image, photo, or video for a community post.
     * Images are resized/WebP when possible; videos accepted up to 50MB.
     */
    public function uploadMedia(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:51200',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }
                    $mime = $value->getMimeType();
                    $okImage = str_starts_with((string) $mime, 'image/');
                    $okVideo = in_array($mime, ['video/mp4', 'video/webm', 'video/quicktime'], true);
                    if (! $okImage && ! $okVideo) {
                        $fail('File must be an image (jpeg/png/gif/webp) or video (mp4/webm/mov).');
                    }
                },
            ],
            'type' => 'nullable|in:cover,media',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $type = $request->get('type', 'media');
        $folder = $type === 'cover' ? 'community-posts/covers' : 'community-posts/media';
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

        if ($disk === 'public' && ! Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $file = $request->file('file');
        $mime = $file->getMimeType();
        $isVideo = str_starts_with((string) $mime, 'video/');
        $ext = $isVideo ? $file->getClientOriginalExtension() : 'webp';
        $fileName = Str::uuid().'.'.($ext ?: ($isVideo ? 'mp4' : 'webp'));
        $path = $folder.'/'.$fileName;

        if ($isVideo) {
            $stored = $file->storeAs($folder, $fileName, $disk);
            $path = $stored;
        } else {
            try {
                $image = \Intervention\Image\Facades\Image::make($file->getRealPath())
                    ->orientate()
                    ->resize(1600, 1600, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('webp', 82);
                Storage::disk($disk)->put($path, (string) $image);
            } catch (\Throwable $e) {
                $stored = $file->storeAs($folder, $file->hashName(), $disk);
                $path = $stored;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Media uploaded successfully',
            'data' => [
                'path' => $path,
                'url' => \App\Helpers\MediaUrlHelper::resolve($path),
                'media_type' => $isVideo ? 'video' : 'image',
            ],
        ], 201);
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
            'category_id' => 'nullable|integer|exists:category,category_id',
            'location' => 'nullable|string',
            'country' => 'nullable|string',
            'city' => 'nullable|string',
            'discussion_type' => 'nullable|in:general,question,review,advice,report,poll',
            'is_poll' => 'nullable|boolean',
            'poll_options' => 'nullable|array|min:2|max:6',
            'poll_options.*' => 'required|string|max:120',
            'poll_ends_at' => 'nullable|date|after:now',
            'tags' => 'nullable|array',
            'community_ids' => 'required|array|min:1',
            'community_ids.*' => 'uuid|exists:communities,community_id',
            'cover_image_file' => 'nullable|file|image|max:10240',
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $isPoll = $request->boolean('is_poll')
            || $request->discussion_type === 'poll'
            || $request->filled('poll_options');

        if ($isPoll) {
            $options = array_values(array_filter(array_map('trim', (array) $request->input('poll_options', []))));
            if (count($options) < 2) {
                return response()->json([
                    'success' => false,
                    'errors' => ['poll_options' => ['Polls need at least 2 options.']],
                ], 422);
            }
        }

        $coverImage = $request->cover_image;
        if ($request->hasFile('cover_image_file')) {
            $upload = $this->storeUploadedMedia($request->file('cover_image_file'), 'cover');
            $coverImage = $upload['path'];
        } elseif ($request->hasFile('cover_image')) {
            $upload = $this->storeUploadedMedia($request->file('cover_image'), 'cover');
            $coverImage = $upload['path'];
        }

        $media = is_array($request->media) ? $request->media : [];
        if ($request->hasFile('media_files')) {
            foreach ((array) $request->file('media_files') as $file) {
                if (!$file) {
                    continue;
                }
                $upload = $this->storeUploadedMedia($file, 'media');
                $media[] = [
                    'path' => $upload['path'],
                    'url' => $upload['url'],
                    'media_type' => $upload['media_type'],
                ];
            }
        }

        $pollOptions = null;
        if ($isPoll) {
            $pollOptions = collect($options)->values()->map(function ($text, $i) {
                return [
                    'id' => 'opt_'.($i + 1),
                    'text' => $text,
                    'votes' => 0,
                ];
            })->all();
        }

        $usersUserId = CommunityAuthHelper::usersUserId();
        if (!$usersUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve a Social Hub user profile for this account. Please ensure your email is set.',
            ], 422);
        }

        try {
            $post = CommunityPost::create([
                'post_id' => (string) Str::uuid(),
                'user_id' => $usersUserId,
                'post_type' => $request->post_type,
                'advert_type' => $request->advert_type,
                'advert_id' => $request->advert_id,
                'title' => $request->title,
                'content' => $request->content,
                'cover_image' => $coverImage,
                'media' => $media ?: null,
                'category_id' => $request->category_id,
                'location' => $request->location,
                'country' => $request->country,
                'city' => $request->city,
                'discussion_type' => $isPoll ? 'poll' : $request->discussion_type,
                'is_poll' => $isPoll,
                'poll_options' => $pollOptions,
                'poll_ends_at' => $isPoll ? $request->input('poll_ends_at') : null,
                'poll_votes_count' => 0,
                'tags' => $request->tags,
            ]);

            // Attach to communities (pivot `id` is auto-increment bigint)
            foreach ($request->community_ids as $index => $communityId) {
                $already = CommunityPostCommunity::where('community_id', $communityId)
                    ->where('post_id', $post->post_id)
                    ->exists();
                if (!$already) {
                    CommunityPostCommunity::create([
                        'community_id' => $communityId,
                        'post_id' => $post->post_id,
                        'is_primary' => $index === 0,
                    ]);
                }

                $community = Community::where(function ($q) use ($communityId) {
                    $q->where('community_id', $communityId)
                        ->orWhere('slug', $communityId);
                })->first();
                if ($community) {
                    $community->incrementPostsCount();
                    if ($request->post_type === 'ad_thread') {
                        $community->incrementActiveAdsCount();
                    }
                }
            }

            // Update customer posts_count (KYC first-post tracking) + users reputation
            $authUser = CommunityAuthHelper::authUser();
            if ($authUser && \Illuminate\Support\Facades\Schema::hasColumn('customer', 'posts_count')) {
                try {
                    $authUser->increment('posts_count');
                } catch (\Throwable $e) {
                    // ignore if actor is not a customer row
                }
            }
            $usersUser = CommunityAuthHelper::usersUser($authUser, false);
            if ($usersUser) {
                $reputation = $usersUser->getReputation();
                $reputation->incrementPostsCount();
                $reputation->incrementReputationScore(5);
            }

            $post = $post->load(['user', 'category', 'communities', 'primaryCommunity']);
            $post->withPollState($usersUserId);

            $payload = [
                'success' => true,
                'data' => $post,
                'message' => $isPoll ? 'Poll created successfully' : 'Post created successfully',
            ];
            if ($request->attributes->get('kyc_prompt')) {
                $payload['kyc_prompt'] = true;
                $payload['message'] .= '. Please complete KYC verification when prompted.';
            }

            return response()->json($payload, 201);
        } catch (\Throwable $e) {
            \Log::error('CommunityPost store failed', [
                'users_user_id' => $usersUserId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Could not create post: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vote on a poll post (one vote per user; can change option while open).
     */
    public function vote(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'option_id' => 'required|string|max:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $post = CommunityPost::where('post_id', $id)->firstOrFail();

        if (!$post->is_poll || !is_array($post->poll_options)) {
            return response()->json([
                'success' => false,
                'message' => 'This post is not a poll',
            ], 400);
        }

        if (!$post->isPollOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'This poll has ended',
            ], 400);
        }

        $optionId = (string) $request->input('option_id');
        $options = collect($post->poll_options)->values()->map(function ($opt, $i) {
            return [
                'id' => (string) ($opt['id'] ?? ('opt_'.($i + 1))),
                'text' => (string) ($opt['text'] ?? ''),
                'votes' => (int) ($opt['votes'] ?? 0),
            ];
        });

        if (!$options->firstWhere('id', $optionId)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid poll option',
            ], 422);
        }

        $userId = CommunityAuthHelper::usersUserId();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve a Social Hub user profile for this account.',
            ], 422);
        }
        $existing = CommunityPollVote::where('post_id', $post->post_id)
            ->where('user_id', $userId)
            ->first();

        if ($existing && $existing->option_id === $optionId) {
            $post->withPollState($userId);
            return response()->json([
                'success' => true,
                'message' => 'Already voted for this option',
                'data' => $post,
            ]);
        }

        if ($existing) {
            $options = $options->map(function ($opt) use ($existing) {
                if ($opt['id'] === $existing->option_id) {
                    $opt['votes'] = max(0, $opt['votes'] - 1);
                }
                return $opt;
            });
            $existing->update(['option_id' => $optionId]);
        } else {
            CommunityPollVote::create([
                'id' => (string) Str::uuid(),
                'post_id' => $post->post_id,
                'user_id' => $userId,
                'option_id' => $optionId,
            ]);
            $post->increment('poll_votes_count');
        }

        $options = $options->map(function ($opt) use ($optionId) {
            if ($opt['id'] === $optionId) {
                $opt['votes'] += 1;
            }
            return $opt;
        })->values()->all();

        $post->update([
            'poll_options' => $options,
            'poll_votes_count' => max(
                (int) $post->poll_votes_count,
                collect($options)->sum('votes')
            ),
        ]);

        $post->refresh()->withPollState($userId);

        return response()->json([
            'success' => true,
            'message' => 'Vote recorded',
            'data' => $post,
        ]);
    }

    /**
     * Attach poll state for authenticated viewer on a paginator/collection.
     */
    protected function attachPollState($posts): void
    {
        $userId = CommunityAuthHelper::usersUserId(null, false);
        if (method_exists($posts, 'getCollection')) {
            $posts->getCollection()->transform(function ($post) use ($userId) {
                if ($post instanceof CommunityPost) {
                    $post->withPollState($userId ? (int) $userId : null);
                }
                return $post;
            });
            return;
        }

        if ($posts instanceof CommunityPost) {
            $posts->withPollState($userId ? (int) $userId : null);
        }
    }

    /**
     * Update a post
     */
    public function update(Request $request, $id)
    {
        $post = CommunityPost::findOrFail($id);

        $usersUserId = CommunityAuthHelper::usersUserId(null, false);
        if (!$usersUserId || (int) $post->user_id !== (int) $usersUserId) {
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

        $usersUserId = CommunityAuthHelper::usersUserId(null, false);
        if (!$usersUserId || (int) $post->user_id !== (int) $usersUserId) {
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

        $usersUserId = CommunityAuthHelper::usersUserId();
        if (!$usersUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve a Social Hub user profile for this account.',
            ], 422);
        }

        $existingReaction = PostReaction::where('post_id', $post->post_id)
                                        ->where('user_id', $usersUserId)
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
            'user_id' => $usersUserId,
            'reaction_type' => $request->reaction_type,
        ]);

        $post->incrementReactions();

        // Update reputation for helpful reactions
        if ($request->reaction_type === 'helpful' && $post->user) {
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

        $usersUserId = CommunityAuthHelper::usersUserId();
        if (!$usersUserId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve a Social Hub user profile for this account.',
            ], 422);
        }

        $existingSave = SavedPost::where('post_id', $post->post_id)
                                  ->where('user_id', $usersUserId)
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
            'user_id' => $usersUserId,
            'post_id' => $post->post_id,
        ]);

        $post->incrementSaves();

        return response()->json([
            'success' => true,
            'message' => 'Post saved successfully'
        ]);
    }

    /**
     * Record a share (public or authenticated) and return share URL.
     */
    public function share(Request $request, $id)
    {
        $post = CommunityPost::findOrFail($id);
        $post->incrementShares();

        $shareUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://worldwideadverts.info')), '/')
            . '/communities?post=' . $post->post_id;

        return response()->json([
            'success' => true,
            'message' => 'Share recorded',
            'data' => [
                'post_id' => $post->post_id,
                'shares_count' => $post->fresh()->shares_count,
                'share_url' => $shareUrl,
                'title' => $post->title,
            ],
        ]);
    }

    /**
     * Get user's saved posts
     */
    public function saved(Request $request)
    {
        $usersUser = CommunityAuthHelper::usersUser(null, false);
        if (!$usersUser) {
            return response()->json([
                'success' => true,
                'data' => SavedPost::query()->whereRaw('1 = 0')->paginate($request->get('per_page', 20)),
            ]);
        }

        $savedPosts = $usersUser->savedPosts()
            ->with('post.user', 'post.category', 'post.communities')
            ->latest()
            ->paginate($request->get('per_page', 20));

        // Flatten to CommunityPost shape expected by Social Hub FE
        $savedPosts->getCollection()->transform(function ($saved) {
            $post = $saved->post;
            if (!$post) {
                return null;
            }
            $post->saved_at = $saved->created_at ?? $saved->saved_at ?? null;
            return $post;
        });
        $savedPosts->setCollection(
            $savedPosts->getCollection()->filter()->values()
        );
        $this->attachPollState($savedPosts);

        return response()->json([
            'success' => true,
            'data' => $savedPosts
        ]);
    }

    /**
     * Persist uploaded image/video for community posts.
     */
    protected function storeUploadedMedia($file, string $type = 'media'): array
    {
        $folder = $type === 'cover' ? 'community-posts/covers' : 'community-posts/media';
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

        if ($disk === 'public' && ! Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        $mime = $file->getMimeType();
        $isVideo = str_starts_with((string) $mime, 'video/');
        $ext = $isVideo ? ($file->getClientOriginalExtension() ?: 'mp4') : 'webp';
        $fileName = Str::uuid().'.'.$ext;
        $path = $folder.'/'.$fileName;

        if ($isVideo) {
            $path = $file->storeAs($folder, $fileName, $disk);
        } else {
            try {
                $image = \Intervention\Image\Facades\Image::make($file->getRealPath())
                    ->orientate()
                    ->resize(1600, 1600, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })
                    ->encode('webp', 82);
                Storage::disk($disk)->put($path, (string) $image);
            } catch (\Throwable $e) {
                $path = $file->storeAs($folder, $file->hashName(), $disk);
            }
        }

        return [
            'path' => $path,
            'url' => MediaUrlHelper::resolve($path),
            'media_type' => $isVideo ? 'video' : 'image',
        ];
    }

    /**
     * Get user's posts
     */
    public function myPosts(Request $request)
    {
        $usersUser = CommunityAuthHelper::usersUser(null, false);
        if (!$usersUser) {
            return response()->json([
                'success' => true,
                'data' => CommunityPost::query()->whereRaw('1 = 0')->paginate($request->get('per_page', 20)),
            ]);
        }

        $posts = $usersUser->communityPosts()
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
        $usersUser = CommunityAuthHelper::usersUser(null, false);
        $canPin = false;
        if ($usersUser) {
            foreach ($post->communities as $community) {
                if ($usersUser->isModeratorOf($community->community_id)) {
                    $canPin = true;
                    break;
                }
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
