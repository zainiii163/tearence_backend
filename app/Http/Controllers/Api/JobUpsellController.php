<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobUpsell;
use App\Models\JobListing;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class JobUpsellController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JobUpsell::with(['user', 'upsellable']);

        // Filter by type
        if ($request->filled('upsell_type')) {
            $query->where('upsell_type', $request->upsell_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get upsells for current user
        if ($request->boolean('my_upsells')) {
            $query->where('user_id', Auth::id());
        }

        $upsells = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $upsells,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'upsellable_type' => ['required', Rule::in(['job_listing', 'job_seeker'])],
            'upsellable_id' => 'required|integer',
            'upsell_type' => ['required', Rule::in(['promoted', 'featured', 'sponsored', 'network_wide'])],
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'payment_id' => 'nullable|string',
            'payment_details' => 'nullable|array',
        ]);

        // Validate ownership
        $modelClass = $validated['upsellable_type'] === 'job_listing' ? JobListing::class : JobSeeker::class;
        $model = $modelClass::find($validated['upsellable_id']);

        if (!$model || $model->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized or item not found',
            ], 403);
        }

        // Check for existing active upsell of same type
        $existingUpsell = JobUpsell::where('upsellable_type', $validated['upsellable_type'])
            ->where('upsellable_id', $validated['upsellable_id'])
            ->where('upsell_type', $validated['upsell_type'])
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingUpsell) {
            return response()->json([
                'success' => false,
                'message' => 'An active upsell of this type already exists',
            ], 422);
        }

        $validated['user_id'] = Auth::id();
        $validated['currency'] = $validated['currency'] ?? 'USD';
        $validated['status'] = 'pending';

        $upsell = JobUpsell::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Upsell created successfully',
            'data' => $upsell->load(['user', 'upsellable']),
        ], 201);
    }

    public function show(JobUpsell $jobUpsell): JsonResponse
    {
        if ($jobUpsell->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobUpsell->load(['user', 'upsellable']);

        return response()->json([
            'success' => true,
            'data' => $jobUpsell,
        ]);
    }

    public function activate(JobUpsell $jobUpsell): JsonResponse
    {
        if ($jobUpsell->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        if ($jobUpsell->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Upsell cannot be activated',
            ], 422);
        }

        $jobUpsell->status = 'active';
        $jobUpsell->starts_at = now();
        
        // Set expiration based on type
        $durationDays = match ($jobUpsell->upsell_type) {
            'promoted' => 7,
            'featured' => 14,
            'sponsored' => 21,
            'network_wide' => 30,
            default => 7,
        };
        
        $jobUpsell->expires_at = now()->addDays($durationDays);
        $jobUpsell->save();

        return response()->json([
            'success' => true,
            'message' => 'Upsell activated successfully',
            'data' => $jobUpsell->load(['user', 'upsellable']),
        ]);
    }

    public function cancel(JobUpsell $jobUpsell): JsonResponse
    {
        if ($jobUpsell->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobUpsell->status = 'cancelled';
        $jobUpsell->save();

        return response()->json([
            'success' => true,
            'message' => 'Upsell cancelled successfully',
            'data' => $jobUpsell,
        ]);
    }

    public function pricing(): JsonResponse
    {
        $pricing = [
            'promoted' => [
                'name' => 'Promoted Listing',
                'price' => 29.99,
                'currency' => 'USD',
                'duration' => 7,
                'features' => [
                    'Highlighted listing',
                    'Appears above standard posts',
                    '"Promoted" badge',
                    '2× more visibility',
                ],
            ],
            'featured' => [
                'name' => 'Featured Listing',
                'price' => 79.99,
                'currency' => 'USD',
                'duration' => 14,
                'features' => [
                    'Top of category pages',
                    'Larger listing card',
                    'Priority in search results',
                    'Included in weekly "Featured Jobs" email',
                    '"Featured" badge',
                ],
                'is_popular' => true,
            ],
            'sponsored' => [
                'name' => 'Sponsored Listing',
                'price' => 149.99,
                'currency' => 'USD',
                'duration' => 21,
                'features' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    '"Sponsored" badge',
                    'Maximum visibility',
                ],
            ],
            'network_wide' => [
                'name' => 'Network-Wide Boost',
                'price' => 299.99,
                'currency' => 'USD',
                'duration' => 30,
                'features' => [
                    'Appears across multiple pages',
                    'Jobs page',
                    'Homepage',
                    'Category pages',
                    'Related search pages',
                    'Included in email newsletters',
                    'Included in push notifications',
                    '"Top Spotlight" badge',
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $pricing,
        ]);
    }

    public function stats(): JsonResponse
    {
        $userId = Auth::id();
        
        $stats = [
            'total_upsells' => JobUpsell::where('user_id', $userId)->count(),
            'active_upsells' => JobUpsell::where('user_id', $userId)->active()->count(),
            'pending_upsells' => JobUpsell::where('user_id', $userId)->where('status', 'pending')->count(),
            'expired_upsells' => JobUpsell::where('user_id', $userId)->where('status', 'expired')->count(),
            'total_spent' => JobUpsell::where('user_id', $userId)
                ->where('status', '!=', 'cancelled')
                ->sum('price'),
            'recent_upsells' => JobUpsell::where('user_id', $userId)
                ->with(['upsellable'])
                ->latest()
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
