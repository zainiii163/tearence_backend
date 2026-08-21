<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CommunityAuthHelper;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityFollow;
use App\Models\Category;
use App\Models\CustomerBusiness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CommunityController extends Controller
{
    /**
     * Get all communities with filters
     */
    public function index(Request $request)
    {
        $query = Community::query();

        // Filter by category
        if ($request->has('category_id')) {
            $query->byCategory($request->category_id);
        }

        // Filter by scope
        if ($request->has('scope')) {
            $query->byScope($request->scope);
        }

        // Filter by region
        if ($request->has('region')) {
            $query->byRegion($request->region);
        }

        // Filter by city
        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        // Filter by verification status
        if ($request->has('verified')) {
            if ($request->boolean('verified')) {
                $query->verified();
            }
        }

        // Filter by featured status
        if ($request->has('featured')) {
            if ($request->boolean('featured')) {
                $query->featured();
            }
        }

        // Sort options
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'trending':
                $query->trending();
                break;
            case 'members':
                $query->orderBy('members_count', 'desc');
                break;
            case 'posts':
                $query->orderBy('posts_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $communities = $query->with(['category', 'creator'])
                            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $communities
        ]);
    }

    /**
     * Get trending communities
     */
    public function trending(Request $request)
    {
        $communities = Community::trending()
                                ->with(['category', 'creator'])
                                ->limit($request->get('limit', 10))
                                ->get();

        $userId = CommunityAuthHelper::usersUserId(null, false);
        if ($userId) {
            $joinedIds = CommunityMember::where('user_id', $userId)
                ->whereIn('community_id', $communities->pluck('community_id'))
                ->pluck('community_id')
                ->all();
            $communities->transform(function ($community) use ($joinedIds) {
                $community->is_joined = in_array($community->community_id, $joinedIds, true);
                return $community;
            });
        } else {
            $communities->transform(function ($community) {
                $community->is_joined = false;
                return $community;
            });
        }

        return response()->json([
            'success' => true,
            'data' => $communities
        ]);
    }

    /**
     * Get featured communities
     */
    public function featured(Request $request)
    {
        $communities = Community::featured()
                                ->with(['category', 'creator'])
                                ->limit($request->get('limit', 10))
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $communities
        ]);
    }

    /**
     * Get a single community
     */
    public function show($id)
    {
        $with = ['category', 'creator', 'members.user'];
        if (Schema::hasColumn('communities', 'business_id')) {
            $with[] = 'business';
        }

        $community = Community::with($with)
                              ->where(function ($q) use ($id) {
                                  $q->where('community_id', $id)->orWhere('slug', $id);
                              })
                              ->firstOrFail();

        $payload = $community->toArray();
        if ($community->relationLoaded('business') && $community->business) {
            $payload['business'] = [
                'id' => $community->business->id,
                'slug' => $community->business->slug,
                'business_name' => $community->business->business_name,
                'business_logo' => $community->business->business_logo,
                'href' => '/business/' . ($community->business->slug ?: $community->business->id),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $payload
        ]);
    }

    /**
     * Create a new community
     */
    public function store(Request $request)
    {
        // Social Hub modal may send location / omit scope
        if (!$request->filled('scope')) {
            $request->merge(['scope' => 'global']);
        }
        if (!$request->filled('city') && $request->filled('location')) {
            $request->merge(['city' => $request->input('location')]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:category,category_id',
            'cover_image' => 'nullable',
            'scope' => 'nullable|in:global,region,city',
            'region' => 'nullable|string|required_if:scope,region',
            'city' => 'nullable|string|required_if:scope,city',
            'location' => 'nullable|string|max:255',
            'strict_moderation' => 'boolean',
            'beginner_friendly' => 'boolean',
            'rules' => 'nullable',
            'is_private' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;

        while (Community::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->storeCommunityCover($request->file('cover_image'));
        } elseif (is_string($request->cover_image) && $request->cover_image !== '') {
            $coverPath = $request->cover_image;
        }

        $rules = $request->input('rules');
        if (is_string($rules) && trim($rules) !== '') {
            $rules = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $rules))));
        } elseif (!is_array($rules)) {
            $rules = null;
        }

        $userId = CommunityAuthHelper::usersUserId();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve a Social Hub user profile for this account.',
            ], 422);
        }
        $scope = $request->input('scope', 'global') ?: 'global';

        $community = Community::create([
            'community_id' => (string) Str::uuid(),
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'category_id' => $request->category_id ?: null,
            'cover_image' => $coverPath,
            'scope' => $scope,
            'region' => $request->region,
            'city' => $request->city,
            'strict_moderation' => $request->boolean('strict_moderation', false),
            'beginner_friendly' => $request->boolean('beginner_friendly', false),
            'rules' => $rules,
            'created_by' => $userId,
            'members_count' => 1,
        ]);

        // Add creator as admin member + auto-follow for Following feed
        CommunityMember::create([
            'id' => (string) Str::uuid(),
            'community_id' => $community->community_id,
            'user_id' => $userId,
            'role' => 'admin',
        ]);

        CommunityFollow::firstOrCreate(
            [
                'community_id' => $community->community_id,
                'user_id' => $userId,
            ],
            [
                'id' => (string) Str::uuid(),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $community->load(['category', 'creator']),
            'message' => 'Social Hub group created successfully'
        ], 201);
    }

    /**
     * Store community cover image file; returns storage path.
     */
    protected function storeCommunityCover($file): string
    {
        $folder = 'communities/covers';
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';

        if ($disk === 'public' && ! Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        try {
            $path = $folder.'/'.Str::uuid().'.webp';
            $image = \Intervention\Image\Facades\Image::make($file->getRealPath())
                ->orientate()
                ->resize(1600, 1600, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('webp', 82);
            Storage::disk($disk)->put($path, (string) $image);
            return $path;
        } catch (\Throwable $e) {
            return $file->store($folder, $disk);
        }
    }

    /**
     * Update a community
     */
    public function update(Request $request, $id)
    {
        $community = Community::where('community_id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        // Check if user is admin
        $actorId = CommunityAuthHelper::usersUserId(null, false);
        if (!$community->creator || (int) $community->creator->user_id !== (int) $actorId) {
            $member = CommunityMember::where('community_id', $community->community_id)
                                     ->where('user_id', $actorId)
                                     ->where('role', 'admin')
                                     ->first();
            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this Social Hub group'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:category,category_id',
            'cover_image' => 'nullable',
            'scope' => 'sometimes|in:global,region,city',
            'region' => 'nullable|string',
            'city' => 'nullable|string',
            'strict_moderation' => 'boolean',
            'beginner_friendly' => 'boolean',
            'rules' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $this->storeCommunityCover($request->file('cover_image'));
        } elseif (is_string($request->input('cover_image')) && $request->input('cover_image') !== '') {
            $coverPath = $request->input('cover_image');
        }

        if ($request->has('name')) {
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $counter = 1;

            while (Community::where('slug', $slug)->where('community_id', '!=', $community->community_id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $community->slug = $slug;
        }

        $payload = $request->only([
            'name', 'description', 'category_id',
            'scope', 'region', 'city', 'strict_moderation',
            'beginner_friendly', 'rules'
        ]);
        if ($coverPath !== null) {
            $payload['cover_image'] = $coverPath;
        }

        $community->update($payload);

        return response()->json([
            'success' => true,
            'data' => $community->load(['category', 'creator']),
            'message' => 'Social Hub group updated successfully'
        ]);
    }

    /**
     * Delete a community
     */
    public function destroy($id)
    {
        $community = Community::where('community_id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        // Check if user is admin
        $actorId = CommunityAuthHelper::usersUserId(null, false);
        if (!$community->creator || (int) $community->creator->user_id !== (int) $actorId) {
            $member = CommunityMember::where('community_id', $community->community_id)
                                     ->where('user_id', $actorId)
                                     ->where('role', 'admin')
                                     ->first();
            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this Social Hub group'
                ], 403);
            }
        }

        $community->delete();

        return response()->json([
            'success' => true,
            'message' => 'Social Hub group deleted successfully'
        ]);
    }

    /**
     * Join a community
     */
    public function join(Request $request, $id)
    {
        $community = Community::where('community_id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        $userId = CommunityAuthHelper::usersUserId();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve a Social Hub user profile for this account.',
            ], 422);
        }

        $existingMember = CommunityMember::where('community_id', $community->community_id)
                                         ->where('user_id', $userId)
                                         ->first();

        if ($existingMember) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this Social Hub group'
            ], 400);
        }

        CommunityMember::create([
            'id' => (string) Str::uuid(),
            'community_id' => $community->community_id,
            'user_id' => $userId,
            'role' => 'member',
        ]);

        $community->incrementMembersCount();

        // Auto-follow so Following feed includes joined groups
        CommunityFollow::firstOrCreate(
            [
                'community_id' => $community->community_id,
                'user_id' => $userId,
            ],
            [
                'id' => (string) Str::uuid(),
            ]
        );

        // Update user reputation
        $usersUser = CommunityAuthHelper::usersUser(null, false);
        if ($usersUser) {
            $reputation = $usersUser->getReputation();
            $reputation->incrementCommunitiesCount();
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the Social Hub group'
        ]);
    }

    /**
     * Leave a community
     */
    public function leave($id)
    {
        $community = Community::where('community_id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        $userId = CommunityAuthHelper::usersUserId(null, false);
        $member = CommunityMember::where('community_id', $community->community_id)
                                 ->where('user_id', $userId)
                                 ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this Social Hub group'
            ], 400);
        }

        if ($member->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admins cannot leave their own Social Hub group'
            ], 400);
        }

        $member->delete();
        $community->decrementMembersCount();

        return response()->json([
            'success' => true,
            'message' => 'Successfully left the Social Hub group'
        ]);
    }

    /**
     * Follow a community
     */
    public function follow($id)
    {
        $community = Community::where('community_id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        $userId = CommunityAuthHelper::usersUserId();
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Could not resolve a Social Hub user profile for this account.',
            ], 422);
        }

        $existingFollow = CommunityFollow::where('community_id', $community->community_id)
                                          ->where('user_id', $userId)
                                          ->first();

        if ($existingFollow) {
            return response()->json([
                'success' => false,
                'message' => 'You are already following this Social Hub group'
            ], 400);
        }

        CommunityFollow::create([
            'id' => Str::uuid(),
            'community_id' => $community->community_id,
            'user_id' => $userId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully followed the Social Hub group'
        ]);
    }

    /**
     * Unfollow a community
     */
    public function unfollow($id)
    {
        $community = Community::where('community_id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        $userId = CommunityAuthHelper::usersUserId(null, false);
        $follow = CommunityFollow::where('community_id', $community->community_id)
                                  ->where('user_id', $userId)
                                  ->first();

        if (!$follow) {
            return response()->json([
                'success' => false,
                'message' => 'You are not following this Social Hub group'
            ], 400);
        }

        $follow->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully unfollowed the Social Hub group'
        ]);
    }

    /**
     * Get community members
     */
    public function members($id)
    {
        $community = Community::where('community_id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        $members = CommunityMember::where('community_id', $community->community_id)
                                  ->with('user')
                                  ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    /**
     * Public: Social Hub page linked to a business profile.
     */
    public function forBusiness($businessId)
    {
        if (!Schema::hasColumn('communities', 'business_id')) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Business Social Hub link not migrated yet',
            ]);
        }

        $community = Community::with(['category', 'creator', 'business'])
            ->where('business_id', $businessId)
            ->first();

        if (!$community) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatBusinessCommunity($community),
        ]);
    }

    /**
     * Auth: create (or return) the Social Hub page for a business the user owns.
     */
    public function ensureForBusiness(Request $request, $businessId)
    {
        if (!Schema::hasColumn('communities', 'business_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Run migrations to enable business Social Hub pages',
            ], 503);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $business = CustomerBusiness::findOrFail($businessId);
        $customerId = $user->customer_id ?? $user->getKey();
        $isOwner = (int) $business->customer_id === (int) $customerId;
        $isAdmin = (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isBusinessAdmin') && $user->isBusinessAdmin())
            || (bool) ($user->is_super_admin ?? false)
            || in_array(strtolower((string) ($user->role ?? '')), ['admin', 'super_admin', 'superadmin'], true);

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Only the business owner can open their Social Hub page',
            ], 403);
        }

        $community = Community::where('business_id', $business->id)->first();
        if ($community) {
            return response()->json([
                'success' => true,
                'data' => $this->formatBusinessCommunity($community->load(['category', 'creator', 'business'])),
                'created' => false,
            ]);
        }

        $baseName = trim($business->business_name ?: 'Business') . ' — updates';
        $slug = Str::slug($baseName);
        $originalSlug = $slug ?: ('business-' . $business->id);
        $slug = $originalSlug;
        $counter = 1;
        while (Community::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        // communities.created_by / members / follows FK → users.user_id
        // Business owners authenticate as Customer, so resolve/create a matching User by email.
        $creatorUserId = CommunityAuthHelper::usersUserId($user, true);

        try {
            $community = Community::create([
                'community_id' => (string) Str::uuid(),
                'name' => $baseName,
                'slug' => $slug,
                'description' => $business->business_description
                    ?: ('Follow ' . ($business->business_name ?: 'this business') . ' for promotions, photos and updates.'),
                'cover_image' => $business->business_logo,
                'scope' => 'global',
                'city' => $business->city,
                'created_by' => $creatorUserId,
                'business_id' => $business->id,
                'members_count' => $creatorUserId ? 1 : 0,
                'beginner_friendly' => true,
                'rules' => [
                    'Be respectful',
                    'No spam',
                    'Share updates about this business only',
                ],
            ]);

            if ($creatorUserId) {
                CommunityMember::firstOrCreate(
                    [
                        'community_id' => $community->community_id,
                        'user_id' => $creatorUserId,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'role' => 'admin',
                    ]
                );

                CommunityFollow::firstOrCreate(
                    [
                        'community_id' => $community->community_id,
                        'user_id' => $creatorUserId,
                    ],
                    [
                        'id' => (string) Str::uuid(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Log::error('ensureForBusiness failed', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Could not create Social Hub page: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatBusinessCommunity($community->load(['category', 'creator', 'business'])),
            'created' => true,
            'message' => 'Business Social Hub page ready',
        ], 201);
    }

    /**
     * Admin / dashboard: list Social Hub pages linked to businesses.
     */
    public function businessPages(Request $request)
    {
        if (!Schema::hasColumn('communities', 'business_id')) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $user = auth()->user();
        $query = Community::with(['business', 'category', 'creator'])
            ->whereNotNull('business_id');

        $isAdmin = $user && (
            (method_exists($user, 'isAdmin') && $user->isAdmin())
            || (method_exists($user, 'isBusinessAdmin') && $user->isBusinessAdmin())
            || (bool) ($user->is_super_admin ?? false)
            || in_array(strtolower((string) ($user->role ?? '')), ['admin', 'super_admin', 'superadmin'], true)
        );
        if (!$isAdmin) {
            $customerId = $user->customer_id ?? $user->getKey();
            $ownedIds = CustomerBusiness::where('customer_id', $customerId)->pluck('id');
            $query->whereIn('business_id', $ownedIds);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('business', function ($bq) use ($search) {
                      $bq->where('business_name', 'like', '%' . $search . '%');
                  });
            });
        }

        $rows = $query->orderByDesc('updated_at')->paginate($request->get('per_page', 20));
        $rows->getCollection()->transform(fn ($c) => $this->formatBusinessCommunity($c));

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    protected function formatBusinessCommunity(Community $community): array
    {
        $payload = $community->toArray();
        if ($community->relationLoaded('business') && $community->business) {
            $payload['business'] = [
                'id' => $community->business->id,
                'slug' => $community->business->slug,
                'business_name' => $community->business->business_name,
                'business_logo' => $community->business->business_logo,
                'href' => '/business/' . ($community->business->id ?: $community->business->slug),
            ];
        }
        $payload['social_href'] = '/community/' . ($community->slug ?: $community->community_id);
        $payload['followers_count'] = $community->followers()->count();
        return $payload;
    }

    /**
     * Get user's communities
     */
    public function myCommunities()
    {
        $communities = auth()->user()->communities()
                                     ->with('category')
                                     ->get();

        return response()->json([
            'success' => true,
            'data' => $communities
        ]);
    }

    /**
     * Get communities by category
     */
    public function byCategory($categoryId)
    {
        // Social Hub forms sometimes request "all" categories (legacy)
        if ($categoryId === 'all' || $categoryId === '0' || $categoryId === '') {
            $categories = Category::query()
                ->orderBy('name')
                ->get(['category_id', 'name', 'slug', 'parent_id']);

            return response()->json([
                'success' => true,
                'data' => $categories,
                'type' => 'categories',
            ]);
        }

        $communities = Community::byCategory($categoryId)
                                ->with(['category', 'creator'])
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $communities
        ]);
    }
}
