<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundingProject;
use App\Models\FundingReward;
use App\Models\FundingUpsell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FundingProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = FundingProject::query()->where('is_active', true);

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
                $query->where('is_featured', true)->orderBy('published_at', 'desc');
                break;
            default:
                $query->latest('published_at');
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
        \Log::info('Funding project store request:', [
            'has_file' => $request->hasFile('cover_image'),
            'cover_image' => $request->file('cover_image'),
            'all_files' => $request->allFiles(),
            'all_data' => $request->except(['cover_image', 'additional_images', 'documents']),
        ]);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'project_type' => 'required|in:personal,startup,community,creative',
            'category' => 'required|in:technology,creative_arts,community_social_impact,health_wellness,education,real_estate,environment,startups_business,other',
            'description' => 'required|string|min:50',
            'problem_solving' => 'nullable|string|min:50',
            'vision_mission' => 'nullable|string|min:50',
            'why_now' => 'nullable|string|min:20',
            'team_members' => 'nullable|array',
            'team_members.*.name' => 'required|string|max:255',
            'team_members.*.role' => 'required|string|max:255',
            'team_members.*.photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
            'funding_goal' => 'required|numeric|min:1',
            'currency' => 'required|string|size:3',
            'minimum_contribution' => 'required|numeric|min:1',
            'funding_model' => 'required|in:donation,reward,equity,loan,hybrid',
            'use_of_funds' => 'nullable|string',
            'milestones' => 'nullable|string',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|string',
            'pitch_video' => 'nullable|url|max:255',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:5120',
            'funding_starts_at' => 'nullable|date',
            'funding_ends_at' => 'nullable|date|after:funding_starts_at',
            'rewards' => 'nullable|array',
        ], [
            'cover_image.required' => 'The cover image field is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Decode JSON array fields
        if (isset($data['use_of_funds']) && is_string($data['use_of_funds'])) {
            $data['use_of_funds'] = json_decode($data['use_of_funds'], true);
        }
        if (isset($data['milestones']) && is_string($data['milestones'])) {
            $data['milestones'] = json_decode($data['milestones'], true);
        }
        if (isset($data['social_links']) && is_string($data['social_links'])) {
            $data['social_links'] = json_decode($data['social_links'], true);
        }
        if (isset($data['team_members']) && is_string($data['team_members'])) {
            $data['team_members'] = json_decode($data['team_members'], true);
        }
        // Rewards are sent as arrays, not JSON strings, so no decoding needed

        $data['customer_id'] = Auth::id();
        $data['is_active'] = true;
        $data['is_verified'] = false;
        $data['status'] = 'active';
        $data['published_at'] = now();

        // Generate slug from title
        $data['slug'] = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']));
        $data['slug'] = preg_replace('/-+/', '-', $data['slug']);
        $data['slug'] = trim($data['slug'], '-');

        // Map field names to match database schema
        if (isset($data['problem_solving'])) {
            $data['problem_solved'] = $data['problem_solving'];
            unset($data['problem_solving']);
        }
        if (isset($data['why_now'])) {
            $data['why_matters_now'] = $data['why_now'];
            unset($data['why_now']);
        }
        if (isset($data['funding_ends_at'])) {
            $data['funding_deadline'] = $data['funding_ends_at'];
            unset($data['funding_ends_at']);
        }
        if (isset($data['pitch_video'])) {
            $data['pitch_video_url'] = $data['pitch_video'];
            unset($data['pitch_video']);
        }
        if (isset($data['documents'])) {
            $data['verification_documents'] = $data['documents'];
            unset($data['documents']);
        }

        // Handle file uploads - accept both file upload and string (already uploaded path)
        \Log::info('Cover image check:', [
            'hasFile' => $request->hasFile('cover_image'),
            'hasInput' => $request->has('cover_image'),
            'cover_image_value' => $request->input('cover_image'),
            'all_files' => $request->allFiles(),
            'all_data' => $request->all()
        ]);

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            \Log::info('Cover image file details:', [
                'originalName' => $file->getClientOriginalName(),
                'mimeType' => $file->getMimeType(),
                'size' => $file->getSize(),
                'isValid' => $file->isValid()
            ]);

            $data['cover_image'] = $file->store('funding/covers', 'public');
            \Log::info('Cover image uploaded successfully:', ['path' => $data['cover_image']]);
        } elseif (is_string($request->input('cover_image'))) {
            // Cover image is already a string path (uploaded separately or base64)
            $data['cover_image'] = $request->input('cover_image');
            \Log::info('Cover image provided as string:', ['value' => $data['cover_image']]);
        } else {
            \Log::warning('Cover image not found in request or invalid format');
        }

        if ($request->hasFile('additional_images')) {
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $additionalImages[] = $image->store('funding/additional', 'public');
            }
            $data['additional_images'] = json_encode($additionalImages);
        }

        if (isset($data['verification_documents']) && is_array($data['verification_documents'])) {
            $documents = [];
            foreach ($data['verification_documents'] as $document) {
                if (is_file($document)) {
                    $documents[] = $document->store('funding/documents', 'public');
                } else {
                    $documents[] = $document;
                }
            }
            $data['verification_documents'] = json_encode($documents);
        }

        if (isset($data['identity_verification']) && is_file($data['identity_verification'])) {
            $data['identity_verification'] = $data['identity_verification']->store('funding/verification', 'public');
        }

        // Handle team member photos
        if (!empty($data['team_members'])) {
            foreach ($data['team_members'] as &$member) {
                if (isset($member['photo']) && is_file($member['photo'])) {
                    $member['photo'] = $member['photo']->store('funding/team', 'public');
                }
            }
            $data['team_members'] = json_encode($data['team_members']);
        }

        // Handle JSON fields
        if (isset($data['use_of_funds'])) {
            $data['use_of_funds'] = json_encode($data['use_of_funds']);
        }
        if (isset($data['milestones'])) {
            $data['milestones'] = json_encode($data['milestones']);
        }
        if (isset($data['social_links'])) {
            $data['social_links'] = json_encode($data['social_links']);
        }

        $project = FundingProject::create($data);

        // Create rewards if provided
        if (!empty($request->rewards)) {
            foreach ($request->rewards as $rewardData) {
                $rewardData['funding_project_id'] = $project->id;
                // Set default estimated_delivery_date if not provided
                if (!isset($rewardData['estimated_delivery_date']) || empty($rewardData['estimated_delivery_date'])) {
                    $rewardData['estimated_delivery_date'] = now()->addMonths(3)->format('Y-m-d');
                }
                FundingReward::create($rewardData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => $project
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = FundingProject::where('customer_id', Auth::id())->findOrFail($id);

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
            'use_of_funds' => 'sometimes|string',
            'milestones' => 'sometimes|string',
            'team_members' => 'sometimes|string',
            'social_links' => 'sometimes|string',
            'website' => 'sometimes|url|max:255',
            'pitch_video' => 'sometimes|url|max:255',
            'cover_image' => 'sometimes',
            'additional_images' => 'sometimes|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'documents' => 'sometimes|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:5120',
            'funding_starts_at' => 'sometimes|date',
            'funding_ends_at' => 'sometimes|date|after:funding_starts_at',
            'rewards' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Decode JSON array fields
        if (isset($data['use_of_funds']) && is_string($data['use_of_funds'])) {
            $data['use_of_funds'] = json_decode($data['use_of_funds'], true);
        }
        if (isset($data['milestones']) && is_string($data['milestones'])) {
            $data['milestones'] = json_decode($data['milestones'], true);
        }
        if (isset($data['social_links']) && is_string($data['social_links'])) {
            $data['social_links'] = json_decode($data['social_links'], true);
        }
        if (isset($data['team_members']) && is_string($data['team_members'])) {
            $data['team_members'] = json_decode($data['team_members'], true);
        }
        // Rewards are sent as arrays, not JSON strings, so no decoding needed

        // Generate slug from title if title is being updated
        if (isset($data['title'])) {
            $data['slug'] = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title']));
            $data['slug'] = preg_replace('/-+/', '-', $data['slug']);
            $data['slug'] = trim($data['slug'], '-');
        }

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
        $project = FundingProject::where('customer_id', Auth::id())->findOrFail($id);

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
        $projects = FundingProject::where('customer_id', Auth::id())->with(['rewards'])->latest()->paginate(10);
        return response()->json(['success' => true, 'data' => $projects]);
    }

    public function addReward(Request $request, $projectId)
    {
        $project = FundingProject::where('customer_id', Auth::id())->findOrFail($projectId);

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
        $project = FundingProject::where('customer_id', Auth::id())->findOrFail($projectId);

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

    public function getFeaturedProjects()
    {
        $projects = FundingProject::query()
                                 ->where('is_active', true)
                                 ->where('is_featured', true)
                                 ->latest('published_at')
                                 ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function getTrendingProjects()
    {
        $projects = FundingProject::query()
                                 ->where('is_active', true)
                                 ->orderBy('views_count', 'desc')
                                 ->orderBy('current_funded', 'desc')
                                 ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function getEndingSoonProjects()
    {
        $projects = FundingProject::query()
                                 ->where('is_active', true)
                                 ->where('funding_deadline', '>', now())
                                 ->orderBy('funding_deadline', 'asc')
                                 ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
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

    public function getStatistics()
    {
        try {
            $stats = [
                'total_projects' => FundingProject::count(),
                'active_projects' => FundingProject::where('is_active', true)->count(),
                'total_funded' => FundingProject::sum('current_funded'),
                'total_funding_goal' => FundingProject::sum('funding_goal'),
                'successful_projects' => FundingProject::where('current_funded', '>=', DB::raw('funding_goal'))->count(),
                'total_backers' => FundingProject::sum('backers_count'),
                'countries' => FundingProject::distinct('country')->count(),
                'featured_projects' => FundingProject::where('is_featured', true)->count(),
                'promoted_projects' => FundingProject::where('is_promoted', true)->count(),
                'sponsored_projects' => FundingProject::where('is_sponsored', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    // Admin Methods
    public function adminDashboard()
    {
        try {
            $stats = [
                'total_projects' => FundingProject::count(),
                'active_projects' => FundingProject::where('is_active', true)->count(),
                'pending_projects' => FundingProject::where('is_active', false)->count(),
                'featured_projects' => FundingProject::where('is_featured', true)->count(),
                'promoted_projects' => FundingProject::where('is_promoted', true)->count(),
                'total_funding_goal' => FundingProject::sum('funding_goal'),
                'total_amount_raised' => FundingProject::sum('amount_raised'),
                'total_backers' => FundingProject::sum('backer_count'),
                'projects_this_month' => FundingProject::whereMonth('created_at', now()->month)->count(),
                'projects_today' => FundingProject::whereDate('created_at', today())->count(),
            ];

            $recentProjects = FundingProject::with(['user', 'rewards'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $popularCategories = FundingProject::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_projects' => $recentProjects,
                    'popular_categories' => $popularCategories,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load admin dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminIndex(Request $request)
    {
        try {
            $query = FundingProject::with(['user', 'rewards']);

            // Filters
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('project_type')) {
                $query->where('project_type', $request->project_type);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('tagline', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $projects = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch projects: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminShow($id)
    {
        try {
            $project = FundingProject::with(['user', 'rewards', 'pledges'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found: ' . $e->getMessage()
            ], 404);
        }
    }

    public function adminUpdate(Request $request, $id)
    {
        try {
            $project = FundingProject::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'tagline' => 'sometimes|nullable|string|max:80',
                'description' => 'sometimes|string',
                'category' => 'sometimes|in:technology,creative_arts,community_social_impact,health_wellness,education,real_estate,environment,startups_business,other',
                'project_type' => 'sometimes|in:personal,startup,community,creative',
                'funding_goal' => 'sometimes|numeric|min:1',
                'currency' => 'sometimes|in:USD,GBP,EUR,AUD,CAD,INR',
                'minimum_contribution' => 'sometimes|numeric|min:1',
                'funding_model' => 'sometimes|in:donation,reward,equity,loan,hybrid',
                'country' => 'sometimes|string|max:100',
                'city' => 'sometimes|string|max:100',
                'is_active' => 'sometimes|boolean',
                'is_featured' => 'sometimes|boolean',
                'is_promoted' => 'sometimes|boolean',
                'is_verified' => 'sometimes|boolean'
            ]);

            $project->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Project updated successfully',
                'data' => $project->load(['user', 'rewards'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminDestroy($id)
    {
        try {
            $project = FundingProject::findOrFail($id);
            $project->delete();

            return response()->json([
                'success' => true,
                'message' => 'Project deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminApprove($id)
    {
        try {
            $project = FundingProject::findOrFail($id);
            $project->update(['is_active' => true, 'is_verified' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Project approved successfully',
                'data' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminReject($id)
    {
        try {
            $project = FundingProject::findOrFail($id);
            $project->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Project rejected successfully',
                'data' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject project: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminToggleActive($id)
    {
        try {
            $project = FundingProject::findOrFail($id);
            $project->update(['is_active' => !$project->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Project status updated successfully',
                'data' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle project status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminReports(Request $request)
    {
        try {
            $period = $request->input('period', '30'); // days

            $startDate = Carbon::now()->subDays($period);

            $report = [
                'projects_created' => FundingProject::where('created_at', '>=', $startDate)->count(),
                'projects_by_category' => FundingProject::select('category', DB::raw('count(*) as count'))
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('category')
                    ->get(),
                'total_funding_goal' => FundingProject::where('created_at', '>=', $startDate)->sum('funding_goal'),
                'total_amount_raised' => FundingProject::where('created_at', '>=', $startDate)->sum('amount_raised'),
                'total_backers' => FundingProject::where('created_at', '>=', $startDate)->sum('backer_count'),
            ];

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reports: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload cover image for funding project.
     */
    public function uploadCoverImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $image = $request->file('image');

            // Generate unique filename
            $filename = 'cover_' . Str::uuid() . '.' . $image->getClientOriginalExtension();

            // Store the image
            $path = $image->storeAs('funding/covers', $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'Cover image uploaded successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'size' => $image->getSize()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload cover image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload additional images for funding project.
     */
    public function uploadAdditionalImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5MB max each
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedImages = [];

            foreach ($request->file('images') as $image) {
                $filename = 'additional_' . Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('funding/additional', $filename, 'public');

                $uploadedImages[] = [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'size' => $image->getSize()
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Additional images uploaded successfully',
                'data' => $uploadedImages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload additional images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function adminAnalytics(Request $request)
    {
        try {
            $analytics = [
                'growth_metrics' => $this->getGrowthMetrics(),
                'funding_metrics' => $this->getFundingMetrics(),
                'engagement_metrics' => $this->getEngagementMetrics(),
            ];

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function adminExport(Request $request)
    {
        try {
            $projects = FundingProject::with(['user', 'rewards'])
                ->get()
                ->map(function ($project) {
                    return [
                        'ID' => $project->id,
                        'Title' => $project->title,
                        'Category' => $project->category,
                        'Project Type' => $project->project_type,
                        'Funding Goal' => $project->funding_goal,
                        'Amount Raised' => $project->amount_raised,
                        'Backers' => $project->backer_count,
                        'Status' => $project->is_active ? 'Active' : 'Inactive',
                        'Featured' => $project->is_featured ? 'Yes' : 'No',
                        'Verified' => $project->is_verified ? 'Yes' : 'No',
                        'Created By' => $project->user ? $project->user->name : 'N/A',
                        'Created At' => $project->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $projects
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export projects: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods for analytics
    private function getGrowthMetrics(): array
    {
        $currentMonth = FundingProject::whereMonth('created_at', now()->month)->count();
        $lastMonth = FundingProject::whereMonth('created_at', now()->subMonth()->month)->count();
        
        return [
            'current_month_projects' => $currentMonth,
            'last_month_projects' => $lastMonth,
            'growth_rate' => $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0,
        ];
    }

    private function getFundingMetrics(): array
    {
        return [
            'total_funding_goal' => FundingProject::sum('funding_goal'),
            'total_amount_raised' => FundingProject::sum('amount_raised'),
            'funding_success_rate' => FundingProject::count() > 0 ? (FundingProject::where('amount_raised', '>=', DB::raw('funding_goal'))->count() / FundingProject::count()) * 100 : 0,
            'average_funding_goal' => FundingProject::avg('funding_goal'),
            'average_amount_raised' => FundingProject::avg('amount_raised'),
        ];
    }

    private function getEngagementMetrics(): array
    {
        return [
            'total_backers' => FundingProject::sum('backer_count'),
            'average_backers_per_project' => FundingProject::avg('backer_count'),
            'projects_with_rewards' => FundingProject::has('rewards')->count(),
            'average_rewards_per_project' => FundingProject::has('rewards')->count() > 0 ? FundingProject::withCount('rewards')->avg('rewards_count') : 0,
        ];
    }
}
