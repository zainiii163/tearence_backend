<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FundingProject;
use App\Models\FundingUpsell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FundingUpsellController extends Controller
{
    public function getPlans()
    {
        $plans = [
            [
                'type' => 'promoted',
                'title' => 'Promoted Project',
                'price' => FundingUpsell::getPricing()['promoted'],
                'currency' => 'GBP',
                'duration_days' => 30,
                'features' => [
                    'Highlighted card in listings',
                    'Appears above standard listings',
                    '"Promoted" badge',
                    '2× more visibility',
                    'Basic analytics boost'
                ],
                'is_popular' => false
            ],
            [
                'type' => 'featured',
                'title' => 'Featured Project',
                'price' => FundingUpsell::getPricing()['featured'],
                'currency' => 'GBP',
                'duration_days' => 30,
                'features' => [
                    'Top of category pages',
                    'Larger card design',
                    'Priority in search results',
                    'Included in weekly "Top Projects" email',
                    '"Featured" badge',
                    'Advanced analytics',
                    'Social media promotion'
                ],
                'is_popular' => true
            ],
            [
                'type' => 'sponsored',
                'title' => 'Sponsored Project',
                'price' => FundingUpsell::getPricing()['sponsored'],
                'currency' => 'GBP',
                'duration_days' => 30,
                'features' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    '"Sponsored" badge',
                    'Maximum visibility',
                    'Premium analytics',
                    'Dedicated support',
                    'Newsletter feature'
                ],
                'is_popular' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    public function getComparison()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'headers' => ['Feature', 'Standard', 'Promoted', 'Featured', 'Sponsored'],
                'rows' => [
                    ['Basic Listing', '✓', '✓', '✓', '✓'],
                    ['Search Visibility', 'Standard', 'Enhanced', 'High', 'Maximum'],
                    ['Category Placement', 'Standard', 'Enhanced', 'Top', 'Premium'],
                    ['Homepage Placement', '✗', '✗', '✗', '✓'],
                    ['Special Badge', '✗', 'Promoted', 'Featured', 'Sponsored'],
                    ['Analytics', 'Basic', 'Enhanced', 'Advanced', 'Premium'],
                    ['Social Media Promotion', '✗', '✗', '✓', '✓'],
                    ['Newsletter Feature', '✗', '✗', '✗', '✓'],
                    ['Dedicated Support', '✗', '✗', '✗', '✓'],
                    ['Price', 'Free', '£29.99', '£79.99', '£199.99']
                ]
            ]
        ]);
    }

    public function getRecommendation(Request $request)
    {
        $projectId = $request->project_id;
        
        if (!$projectId) {
            return response()->json([
                'success' => false,
                'message' => 'Project ID is required'
            ], 422);
        }

        $project = FundingProject::where('customer_id', Auth::id())
                                ->findOrFail($projectId);

        // Simple recommendation logic based on project characteristics
        $recommendation = 'promoted'; // default

        if ($project->funding_goal >= 50000) {
            $recommendation = 'featured';
        }

        if ($project->funding_goal >= 100000 || $project->category === 'technology') {
            $recommendation = 'sponsored';
        }

        // Check if user has history with upsells
        $previousUpsells = FundingUpsell::where('customer_id', Auth::id())
                                       ->where('status', 'completed')
                                       ->count();

        if ($previousUpsells >= 3) {
            $recommendation = 'featured'; // Loyal customers get featured recommendation
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recommended_type' => $recommendation,
                'reason' => $this->getRecommendationReason($recommendation, $project),
                'project_funding_goal' => $project->funding_goal,
                'project_category' => $project->category
            ]
        ]);
    }

    public function purchaseUpsell(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'funding_project_id' => 'required|exists:funding_projects,id',
            'upsell_type' => 'required|in:promoted,featured,sponsored',
            'duration_days' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        
        // Verify project ownership
        $project = FundingProject::where('customer_id', Auth::id())
                                ->findOrFail($data['funding_project_id']);

        // Check for existing active upsell of same type
        $existingUpsell = FundingUpsell::where('funding_project_id', $project->id)
                                      ->where('upsell_type', $data['upsell_type'])
                                      ->where('status', 'active')
                                      ->first();

        if ($existingUpsell) {
            return response()->json([
                'success' => false,
                'message' => 'This project already has an active ' . $data['upsell_type'] . ' upsell'
            ], 409);
        }

        $data['customer_id'] = Auth::id();
        $data['price'] = FundingUpsell::getPricing()[$data['upsell_type']];
        $data['currency'] = 'GBP';
        $data['status'] = 'pending';
        $data['duration_days'] = $data['duration_days'] ?? 30;

        $upsell = FundingUpsell::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Upsell created successfully. Please complete payment.',
            'data' => $upsell->load('fundingProject')
        ], 201);
    }

    public function getMyUpsells(Request $request)
    {
        $upsells = FundingUpsell::where('customer_id', Auth::id())
                              ->with(['fundingProject'])
                              ->withCount(['fundingProject' => function($query) {
                                  $query->select('backers_count');
                              }])
                              ->latest()
                              ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $upsells
        ]);
    }

    public function getPostUpsells($projectId)
    {
        $project = FundingProject::findOrFail($projectId);
        
        $upsells = $project->upsells()
                          ->with('customer')
                          ->latest()
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $upsells
        ]);
    }

    public function cancelUpsell($id)
    {
        $upsell = FundingUpsell::where('customer_id', Auth::id())->findOrFail($id);

        if ($upsell->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Active upsells cannot be cancelled'
            ], 403);
        }

        if ($upsell->status === 'expired') {
            return response()->json([
                'success' => false,
                'message' => 'Upsell is already expired'
            ], 403);
        }

        $upsell->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Upsell cancelled successfully'
        ]);
    }

    public function getStats()
    {
        $customerId = Auth::id();
        
        $stats = [
            'total_spent' => FundingUpsell::where('customer_id', $customerId)
                                        ->where('status', 'completed')
                                        ->sum('price'),
            'active_upsells' => FundingUpsell::where('customer_id', $customerId)
                                            ->where('status', 'active')
                                            ->count(),
            'total_upsells' => FundingUpsell::where('customer_id', $customerId)
                                          ->count(),
            'upsell_breakdown' => [
                'promoted' => FundingUpsell::where('customer_id', $customerId)
                                          ->where('upsell_type', 'promoted')
                                          ->count(),
                'featured' => FundingUpsell::where('customer_id', $customerId)
                                          ->where('upsell_type', 'featured')
                                          ->count(),
                'sponsored' => FundingUpsell::where('customer_id', $customerId)
                                            ->where('upsell_type', 'sponsored')
                                            ->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    private function getRecommendationReason($type, $project)
    {
        $reasons = [
            'promoted' => 'Based on your project goals, a promoted listing will help you reach more potential backers.',
            'featured' => 'With your funding target of £' . number_format($project->funding_goal) . ', featured placement will maximize your visibility.',
            'sponsored' => 'For high-value projects like yours, sponsored placement ensures maximum exposure across all platforms.'
        ];

        return $reasons[$type] ?? $reasons['promoted'];
    }
}
