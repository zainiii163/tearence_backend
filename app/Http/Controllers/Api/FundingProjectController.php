<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundingProject;
use App\Models\FundingReward;
use App\Models\FundingUpdate;
use App\Models\FundingBacker;
use App\Models\FundingUpsell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FundingProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = FundingProject::with(['customer', 'rewards'])
                              ->active();

        // Filters
        if ($request->category) {
            $query->byCategory($request->category);
        }

        if ($request->country) {
            $query->byCountry($request->country);
        }

        if ($request->risk_level) {
            $query->where('risk_level', $request->risk_level);
        }

        if ($request->funding_model) {
            $query->where('funding_model', $request->funding_model);
        }

        if ($request->min_goal) {
            $query->where('funding_goal', '>=', $request->min_goal);
        }

        if ($request->max_goal) {
            $query->where('funding_goal', '<=', $request->max_goal);
        }

        // Sorting
        $sort = $request->sort ?? 'latest';
        switch ($sort) {
            case 'trending':
                $query->trending();
                break;
            case 'ending_soon':
                $query->endingSoon();
                break;
            case 'nearly_funded':
                $query->nearlyFunded();
                break;
            case 'most_funded':
                $query->orderBy('current_funded', 'desc');
                break;
            case 'most_backers':
                $query->orderBy('backers_count', 'desc');
                break;
            case 'featured':
                $query->featured();
                break;
            case 'promoted':
                $query->promoted();
                break;
            case 'sponsored':
                $query->sponsored();
                break;
            default:
                $query->latest('published_at');
        }

        $projects = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $projects,
            'meta' => [
                'categories' => FundingProject::getCategories(),
                'project_types' => FundingProject::getProjectTypes(),
                'funding_models' => FundingProject::getFundingModels(),
                'risk_levels' => FundingProject::getRiskLevels(),
            ]
        ]);
    }

    public function show($slug)
    {
        $project = FundingProject::with([
            'customer', 
            'rewards.active', 
            'updates.public' => function($query) {
                $query->latest();
            },
            'backers.public' => function($query) {
                $query->completed()->latest()->limit(10);
            }
        ])->where('slug', $slug)->firstOrFail();

        // Increment view count (you might want to add a views column)
        // $project->increment('views');

        return response()->json([
            'success' => true,
            'data' => $project
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'project_type' => 'required|in:personal,startup,community,creative',
            'category' => 'required|in:technology,creative_arts,community_social_impact,health_wellness,education,real_estate,environment,startups_business,other',
            'description' => 'required|string',
            'problem_solved' => 'required|string',
            'vision_mission' => 'required|string',
            'why_matters_now' => 'required|string',
            'funding_goal' => 'required|numeric|min:1',
            'minimum_contribution' => 'required|numeric|min:1',
            'funding_model' => 'required|in:donation,reward_based,equity,loan_based',
            'funding_deadline' => 'required|date|after:today',
            'risk_level' => 'required|in:low,medium,high',
            'country' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pitch_video_url' => 'nullable|url',
            'team_members' => 'nullable|array',
            'team_members.*.name' => 'required|string|max:255',
            'team_members.*.role' => 'required|string|max:255',
            'team_members.*.photo' => 'nullable|string',
            'use_of_funds' => 'nullable|array',
            'use_of_funds.*.item' => 'required|string|max:255',
            'use_of_funds.*.amount' => 'required|numeric|min:0',
            'milestones' => 'nullable|array',
            'milestones.*.title' => 'required|string|max:255',
            'milestones.*.date' => 'required|date',
            'milestones.*.description' => 'required|string',
            'social_links' => 'nullable|array',
            'revenue_model' => 'nullable|string',
            'forecasts' => 'nullable|string',
            'risk_disclosures' => 'nullable|string',
            'business_registration_number' => 'nullable|string|max:100',
            'website' => 'nullable|url',
            'rewards' => 'nullable|array',
            'rewards.*.title' => 'required|string|max:255',
            'rewards.*.description' => 'required|string',
            'rewards.*.minimum_contribution' => 'required|numeric|min:1',
            'rewards.*.limit' => 'nullable|integer|min:1',
            'rewards.*.estimated_delivery_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['customer_id'] = Auth::id();
        $data['slug'] = $this->generateUniqueSlug($data['title']);
        $data['status'] = 'draft';

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('funding/covers', 'public');
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $additionalImages[] = $image->store('funding/additional', 'public');
            }
            $data['additional_images'] = $additionalImages;
        }

        $project = FundingProject::create($data);

        // Create rewards if provided
        if (!empty($data['rewards'])) {
            foreach ($data['rewards'] as $rewardData) {
                $project->rewards()->create($rewardData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Funding project created successfully',
            'data' => $project->load('rewards')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = FundingProject::where('customer_id', Auth::id())->findOrFail($id);

        // Only allow updates if project is in draft or pending status
        if (!in_array($project->status, ['draft', 'pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Project cannot be edited in current status'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'project_type' => 'sometimes|required|in:personal,startup,community,creative',
            'category' => 'sometimes|required|in:technology,creative_arts,community_social_impact,health_wellness,education,real_estate,environment,startups_business,other',
            'description' => 'sometimes|required|string',
            'problem_solved' => 'sometimes|required|string',
            'vision_mission' => 'sometimes|required|string',
            'why_matters_now' => 'sometimes|required|string',
            'funding_goal' => 'sometimes|required|numeric|min:1',
            'minimum_contribution' => 'sometimes|required|numeric|min:1',
            'funding_model' => 'sometimes|required|in:donation,reward_based,equity,loan_based',
            'funding_deadline' => 'sometimes|required|date|after:today',
            'risk_level' => 'sometimes|required|in:low,medium,high',
            'country' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pitch_video_url' => 'nullable|url',
            'revenue_model' => 'nullable|string',
            'forecasts' => 'nullable|string',
            'risk_disclosures' => 'nullable|string',
            'business_registration_number' => 'nullable|string|max:100',
            'website' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            // Delete old image
            if ($project->cover_image) {
                Storage::disk('public')->delete($project->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('funding/covers', 'public');
        }

        $project->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Funding project updated successfully',
            'data' => $project
        ]);
    }

    public function destroy($id)
    {
        $project = FundingProject::where('customer_id', Auth::id())->findOrFail($id);

        // Only allow deletion if no backers or project is in draft
        if ($project->backers_count > 0 && !in_array($project->status, ['draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Project cannot be deleted as it has backers'
            ], 403);
        }

        // Delete associated files
        if ($project->cover_image) {
            Storage::disk('public')->delete($project->cover_image);
        }

        if ($project->additional_images) {
            foreach ($project->additional_images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Funding project deleted successfully'
        ]);
    }

    public function myProjects(Request $request)
    {
        $projects = FundingProject::where('customer_id', Auth::id())
                                ->with(['rewards', 'backers'])
                                ->withCount(['backers as completed_backers' => function($query) {
                                    $query->where('status', 'completed');
                                }])
                                ->latest()
                                ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function publish($id)
    {
        $project = FundingProject::where('customer_id', Auth::id())->findOrFail($id);

        if ($project->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft projects can be published'
            ], 403);
        }

        // Validate required fields
        if (!$project->cover_image || !$project->description || !$project->funding_goal) {
            return response()->json([
                'success' => false,
                'message' => 'Please complete all required fields before publishing'
            ], 422);
        }

        $project->update([
            'status' => 'pending',
            'published_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project submitted for approval',
            'data' => $project
        ]);
    }

    public function backProject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:' . FundingProject::find($id)->minimum_contribution,
            'funding_reward_id' => 'nullable|exists:funding_rewards,id',
            'message' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $project = FundingProject::findOrFail($id);

        if (!$project->is_funding_active) {
            return response()->json([
                'success' => false,
                'message' => 'Project is not currently accepting funding'
            ], 403);
        }

        $data = $validator->validated();
        $data['customer_id'] = Auth::id();
        $data['funding_project_id'] = $project->id;
        $data['status'] = 'pending';
        $data['is_anonymous'] = $data['is_anonymous'] ?? false;

        // Check if user has already backed this project
        $existingBacker = FundingBacker::where('funding_project_id', $project->id)
                                      ->where('customer_id', Auth::id())
                                      ->where('status', 'completed')
                                      ->first();

        if ($existingBacker) {
            return response()->json([
                'success' => false,
                'message' => 'You have already backed this project'
            ], 403);
        }

        // Validate reward availability
        if (!empty($data['funding_reward_id'])) {
            $reward = FundingReward::findOrFail($data['funding_reward_id']);
            if ($reward->is_sold_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reward is sold out'
                ], 403);
            }
            if ($data['amount'] < $reward->minimum_contribution) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount is below the minimum required for this reward'
                ], 422);
            }
        }

        $backer = FundingBacker::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Backing created successfully. Please complete payment.',
            'data' => $backer->load('fundingReward')
        ], 201);
    }

    public function getCategories()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => FundingProject::getCategories(),
                'project_types' => FundingProject::getProjectTypes(),
                'funding_models' => FundingProject::getFundingModels(),
                'risk_levels' => FundingProject::getRiskLevels(),
            ]
        ]);
    }

    public function getFeaturedProjects()
    {
        $projects = FundingProject::with(['customer', 'rewards'])
                                ->active()
                                ->featured()
                                ->latest('published_at')
                                ->limit(6)
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function getTrendingProjects()
    {
        $projects = FundingProject::with(['customer', 'rewards'])
                                ->active()
                                ->trending()
                                ->limit(10)
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function getEndingSoonProjects()
    {
        $projects = FundingProject::with(['customer', 'rewards'])
                                ->active()
                                ->endingSoon()
                                ->limit(10)
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    private function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (FundingProject::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
