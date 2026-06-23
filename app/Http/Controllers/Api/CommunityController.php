<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityFollow;
use App\Models\Category;
use Illuminate\Http\Request;
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
        $community = Community::with(['category', 'creator', 'members.user'])
                              ->where('community_id', $id)
                              ->orWhere('slug', $id)
                              ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $community
        ]);
    }

    /**
     * Create a new community
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|uuid|exists:category,category_id',
            'cover_image' => 'nullable|string',
            'scope' => 'required|in:global,region,city',
            'region' => 'nullable|string|required_if:scope,region',
            'city' => 'nullable|string|required_if:scope,city',
            'strict_moderation' => 'boolean',
            'beginner_friendly' => 'boolean',
            'rules' => 'nullable|array',
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

        $community = Community::create([
            'community_id' => Str::uuid(),
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'cover_image' => $request->cover_image,
            'scope' => $request->scope,
            'region' => $request->region,
            'city' => $request->city,
            'strict_moderation' => $request->boolean('strict_moderation', false),
            'beginner_friendly' => $request->boolean('beginner_friendly', false),
            'rules' => $request->rules,
            'created_by' => auth()->id(),
        ]);

        // Add creator as admin member
        CommunityMember::create([
            'id' => Str::uuid(),
            'community_id' => $community->community_id,
            'user_id' => auth()->id(),
            'role' => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'data' => $community->load(['category', 'creator']),
            'message' => 'Community created successfully'
        ], 201);
    }

    /**
     * Update a community
     */
    public function update(Request $request, $id)
    {
        $community = Community::findOrFail($id);

        // Check if user is admin
        if (!$community->creator || $community->creator->user_id !== auth()->id()) {
            $member = CommunityMember::where('community_id', $community->community_id)
                                     ->where('user_id', auth()->id())
                                     ->where('role', 'admin')
                                     ->first();
            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this community'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|uuid|exists:category,category_id',
            'cover_image' => 'nullable|string',
            'scope' => 'sometimes|in:global,region,city',
            'region' => 'nullable|string',
            'city' => 'nullable|string',
            'strict_moderation' => 'boolean',
            'beginner_friendly' => 'boolean',
            'rules' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
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

        $community->update($request->only([
            'name', 'description', 'category_id', 'cover_image',
            'scope', 'region', 'city', 'strict_moderation',
            'beginner_friendly', 'rules'
        ]));

        return response()->json([
            'success' => true,
            'data' => $community->load(['category', 'creator']),
            'message' => 'Community updated successfully'
        ]);
    }

    /**
     * Delete a community
     */
    public function destroy($id)
    {
        $community = Community::findOrFail($id);

        // Check if user is admin
        if (!$community->creator || $community->creator->user_id !== auth()->id()) {
            $member = CommunityMember::where('community_id', $community->community_id)
                                     ->where('user_id', auth()->id())
                                     ->where('role', 'admin')
                                     ->first();
            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this community'
                ], 403);
            }
        }

        $community->delete();

        return response()->json([
            'success' => true,
            'message' => 'Community deleted successfully'
        ]);
    }

    /**
     * Join a community
     */
    public function join(Request $request, $id)
    {
        $community = Community::findOrFail($id);

        $existingMember = CommunityMember::where('community_id', $community->community_id)
                                         ->where('user_id', auth()->id())
                                         ->first();

        if ($existingMember) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this community'
            ], 400);
        }

        CommunityMember::create([
            'id' => Str::uuid(),
            'community_id' => $community->community_id,
            'user_id' => auth()->id(),
            'role' => 'member',
        ]);

        $community->incrementMembersCount();

        // Update user reputation
        $reputation = auth()->user()->getReputation();
        $reputation->incrementCommunitiesCount();

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the community'
        ]);
    }

    /**
     * Leave a community
     */
    public function leave($id)
    {
        $community = Community::findOrFail($id);

        $member = CommunityMember::where('community_id', $community->community_id)
                                 ->where('user_id', auth()->id())
                                 ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this community'
            ], 400);
        }

        if ($member->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admins cannot leave their own community'
            ], 400);
        }

        $member->delete();
        $community->decrementMembersCount();

        return response()->json([
            'success' => true,
            'message' => 'Successfully left the community'
        ]);
    }

    /**
     * Follow a community
     */
    public function follow($id)
    {
        $community = Community::findOrFail($id);

        $existingFollow = CommunityFollow::where('community_id', $community->community_id)
                                          ->where('user_id', auth()->id())
                                          ->first();

        if ($existingFollow) {
            return response()->json([
                'success' => false,
                'message' => 'You are already following this community'
            ], 400);
        }

        CommunityFollow::create([
            'id' => Str::uuid(),
            'community_id' => $community->community_id,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully followed the community'
        ]);
    }

    /**
     * Unfollow a community
     */
    public function unfollow($id)
    {
        $community = Community::findOrFail($id);

        $follow = CommunityFollow::where('community_id', $community->community_id)
                                  ->where('user_id', auth()->id())
                                  ->first();

        if (!$follow) {
            return response()->json([
                'success' => false,
                'message' => 'You are not following this community'
            ], 400);
        }

        $follow->delete();

        return response()->json([
            'success' => true,
            'message' => 'Successfully unfollowed the community'
        ]);
    }

    /**
     * Get community members
     */
    public function members($id)
    {
        $community = Community::findOrFail($id);

        $members = CommunityMember::where('community_id', $community->community_id)
                                  ->with('user')
                                  ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
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
        $communities = Community::byCategory($categoryId)
                                ->with(['category', 'creator'])
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $communities
        ]);
    }
}
