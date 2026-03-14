<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundingProject;
use App\Models\FundingReward;
use App\Models\FundingUpsell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FundingProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = FundingProject::with(['user', 'rewards'])
                              ->where('is_active', true);

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->project_type) {
            $query->where('project_type', $request->project_type);
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->funding_model) {
            $query->where('funding_model', $request->funding_model);
        }

        $sort = $request->sort ?? 'latest';
        switch ($sort) {
            case 'trending':
                $query->orderBy('views_count', 'desc');
                break;
            case 'featured':
                $query->where('is_featured', true)->orderBy('created_at', 'desc');
                break;
            default:
                $query->latest('created_at');
        }

        $projects = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function show($id)
    {
        $project = FundingProject::with([
            'user',
            'rewards',
            'pledges' => function($query) {
                $query->where('status', 'completed')->where('is_anonymous', false)->latest()->limit(10);
            }
        ])->findOrFail($id);

        $project->increment('views_count');

        return response()->json(['success' => true, 'data' => $project]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'project_type' => 'required|in:personal,startup,community,creative',
            'category' => 'required|in:technology,creative_arts,community_social_impact,health_wellness,education,real_estate,environment,startups_business,other',
            'description' => 'required|string|min:50',
            'problem_solving' => 'required|string|min:50',
            'vision_mission' => 'required|string|min:50',
            'why_now' => 'nullable|string|min:20',
            'team_members' => 'nullable|array',
            'team_members.*.name' => 'required|string|max:255',
            'team_members.*.role' => 'required|string|max:255',
            'team_members.*.photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
            'funding_goal' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'minimum_contribution' => 'required|numeric|min:1',
            'funding_model' => 'required|in:donation,reward,equity,loan,hybrid',
            'use_of_funds' => 'nullable|array',
            'use_of_funds.*.item' => 'required|string|max:255',
            'use_of_funds.*.amount' => 'required|numeric|min:0',
            'milestones' => 'nullable|array',
            'milestones.*.milestone' => 'required|string|max:255',
            'milestones.*.expected_date' => 'required|date',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|in:facebook,twitter,linkedin,instagram,youtube,other',
            'social_links.*.url' => 'required|url|max:255',
            'pitch_video' => 'nullable|url|max:255',
            'identity_verification' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'business_registration_number' => 'nullable|string|max:255',
            'business_registration_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:5120',
            'funding_starts_at' => 'nullable|date',
            'funding_ends_at' => 'nullable|date|after:funding_starts_at',
            'rewards' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = Auth::id();
        $data['is_active'] = true;
        $data['funding_starts_at'] = $data['funding_starts_at'] ?? now();

        // Handle file uploads
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('funding/covers', 'public');
        }

        if ($request->hasFile('additional_images')) {
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $additionalImages[] = $image->store('funding/additional', 'public');
            }
            $data['additional_images'] = $additionalImages;
        }

        if ($request->hasFile('documents')) {
            $documents = [];
            foreach ($request->file('documents') as $document) {
                $documents[] = $document->store('funding/documents', 'public');
            }
            $data['documents'] = $documents;
        }

        if ($request->hasFile('identity_verification')) {
            $data['identity_verification'] = $request->file('identity_verification')->store('funding/verification', 'public');
        }

        if ($request->hasFile('business_registration_document')) {
            $data['business_registration_document'] = $request->file('business_registration_document')->store('funding/business', 'public');
        }

        // Handle team member photos
        if (!empty($data['team_members'])) {
            foreach ($data['team_members'] as &$member) {
                if (isset($member['photo']) && is_file($member['photo'])) {
                    $member['photo'] = $member['photo']->store('funding/team', 'public');
                }
            }
        }

        $project = FundingProject::create($data);

        // Create rewards if provided
        if (!empty($request->rewards)) {
            foreach ($request->rewards as $rewardData) {
                $rewardData['sort_order'] = $project->rewards()->count();
                $project->rewards()->create($rewardData);
            }
        }

        return response()->json([
            'success' => true, 
            'message' => 'Project created successfully', 
            'data' => $project->load(['rewards', 'upsells'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = FundingProject::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'tagline' => 'sometimes|string|max:80',
            'description' => 'sometimes|string|min:50',
            'problem_solving' => 'sometimes|string|min:50',
            'vision_mission' => 'sometimes|string|min:50',
            'why_now' => 'sometimes|string|min:20',
            'funding_goal' => 'sometimes|numeric|min:1',
            'minimum_contribution' => 'sometimes|numeric|min:1',
            'funding_model' => 'sometimes|in:donation,reward,equity,loan,hybrid',
            'use_of_funds' => 'sometimes|array',
            'use_of_funds.*.item' => 'required|string|max:255',
            'use_of_funds.*.amount' => 'required|numeric|min:0',
            'milestones' => 'sometimes|array',
            'milestones.*.milestone' => 'required|string|max:255',
            'milestones.*.expected_date' => 'required|date',
            'team_members' => 'sometimes|array',
            'team_members.*.name' => 'required|string|max:255',
            'team_members.*.role' => 'required|string|max:255',
            'social_links' => 'sometimes|array',
            'social_links.*.platform' => 'required|in:facebook,twitter,linkedin,instagram,youtube,other',
            'social_links.*.url' => 'required|url|max:255',
            'website' => 'sometimes|url|max:255',
            'pitch_video' => 'sometimes|url|max:255',
            'cover_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'additional_images' => 'sometimes|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'documents' => 'sometimes|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:5120',
            'funding_starts_at' => 'sometimes|date',
            'funding_ends_at' => 'sometimes|date|after:funding_starts_at',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Handle file uploads
        if ($request->hasFile('cover_image')) {
            // Delete old cover image
            if ($project->cover_image) {
                Storage::disk('public')->delete($project->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('funding/covers', 'public');
        }

        if ($request->hasFile('additional_images')) {
            // Delete old additional images
            if ($project->additional_images) {
                foreach ($project->additional_images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $additionalImages[] = $image->store('funding/additional', 'public');
            }
            $data['additional_images'] = $additionalImages;
        }

        if ($request->hasFile('documents')) {
            // Delete old documents
            if ($project->documents) {
                foreach ($project->documents as $oldDocument) {
                    Storage::disk('public')->delete($oldDocument);
                }
            }
            $documents = [];
            foreach ($request->file('documents') as $document) {
                $documents[] = $document->store('funding/documents', 'public');
            }
            $data['documents'] = $documents;
        }

        $project->update($data);

        return response()->json([
            'success' => true, 
            'message' => 'Project updated successfully', 
            'data' => $project->load(['rewards', 'upsells'])
        ]);
    }

    public function destroy($id)
    {
        $project = FundingProject::where('user_id', Auth::id())->findOrFail($id);

        if ($project->pledges()->where('status', 'completed')->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete with completed pledges'], 403);
        }

        if ($project->cover_image) {
            Storage::disk('public')->delete($project->cover_image);
        }

        $project->delete();

        return response()->json(['success' => true, 'message' => 'Project deleted']);
    }

    public function myProjects(Request $request)
    {
        $projects = FundingProject::where('user_id', Auth::id())->with(['rewards'])->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $projects]);
    }

    public function addReward(Request $request, $projectId)
    {
        $project = FundingProject::where('user_id', Auth::id())->findOrFail($projectId);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'minimum_contribution' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['sort_order'] = $project->rewards()->count();
        $reward = $project->rewards()->create($data);

        return response()->json(['success' => true, 'data' => $reward], 201);
    }

    public function purchaseUpsell(Request $request, $projectId)
    {
        $project = FundingProject::where('user_id', Auth::id())->findOrFail($projectId);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:promoted,featured,sponsored',
            'currency' => 'required|string|size:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        
        // Define pricing
        $prices = [
            'promoted' => 29.99,
            'featured' => 59.99,
            'sponsored' => 99.99,
        ];

        $data['funding_project_id'] = $projectId;
        $data['price'] = $prices[$data['type']];
        $data['status'] = 'pending';
        $data['user_id'] = Auth::id();

        // Check if user already has an active upsell of this type
        $existingUpsell = FundingUpsell::where('funding_project_id', $projectId)
                                     ->where('type', $data['type'])
                                     ->where('status', 'paid')
                                     ->where(function ($query) {
                                         $query->whereNull('expires_at')
                                               ->orWhere('expires_at', '>', now());
                                     })->first();

        if ($existingUpsell) {
            return response()->json([
                'success' => false, 
                'message' => 'You already have an active ' . $data['type'] . ' upsell for this project'
            ], 422);
        }

        $upsell = FundingUpsell::create($data);

        return response()->json([
            'success' => true, 
            'message' => 'Upsell purchase initiated', 
            'data' => $upsell
        ], 201);
    }

    public function getMetadata()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => [
                    'technology' => 'Technology',
                    'creative_arts' => 'Creative Arts',
                    'community_social_impact' => 'Community & Social Impact',
                    'health_wellness' => 'Health & Wellness',
                    'education' => 'Education',
                    'real_estate' => 'Real Estate',
                    'environment' => 'Environment',
                    'startups_business' => 'Startups & Business',
                    'other' => 'Other',
                ],
                'project_types' => [
                    'personal' => 'Personal Project',
                    'startup' => 'Startup / Business Project',
                    'community' => 'Community / Charity Project',
                    'creative' => 'Creative / Innovation Project',
                ],
                'funding_models' => [
                    'donation' => 'Donation',
                    'reward' => 'Reward-based',
                    'equity' => 'Equity (future)',
                    'loan' => 'Loan-based (future)',
                    'hybrid' => 'Hybrid',
                ],
                'currencies' => ['USD', 'GBP', 'EUR', 'AUD', 'CAD', 'INR'],
                'upsell_types' => [
                    'promoted' => [
                        'name' => 'Promoted Project',
                        'price' => 29.99,
                        'benefits' => [
                            'Highlighted card',
                            'Appears above standard listings',
                            '"Promoted" badge',
                            '2× more visibility'
                        ]
                    ],
                    'featured' => [
                        'name' => 'Featured Project',
                        'price' => 59.99,
                        'benefits' => [
                            'Top of category pages',
                            'Larger card design',
                            'Priority in search results',
                            'Included in weekly "Top Projects" email',
                            '"Featured" badge',
                            'Most Popular option'
                        ]
                    ],
                    'sponsored' => [
                        'name' => 'Sponsored Project',
                        'price' => 99.99,
                        'benefits' => [
                            'Homepage placement',
                            'Category top placement',
                            'Included in homepage slider',
                            'Included in social media promotion',
                            '"Sponsored" badge',
                            'Maximum visibility'
                        ]
                    ]
                ]
            ]
        ]);
    }
}
