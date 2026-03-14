<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobAlert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class JobAlertController extends Controller
{
    /**
     * Create job alert
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'keywords' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'work_type' => 'nullable|string|in:Full-time,Part-time,Contract,Freelance,Internship,Temporary',
            'salary_range' => 'nullable|string',
            'experience_level' => 'nullable|string|in:entry,mid,senior,executive',
            'education_level' => 'nullable|string|in:high_school,associate,bachelor,master,doctorate',
            'remote_only' => 'boolean',
            'frequency' => 'required|string|in:daily,weekly,monthly,instant',
        ]);

        $alert = JobAlert::create(array_merge($request->all(), [
            'user_id' => Auth::id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Job alert created successfully',
            'data' => $alert,
        ], 201);
    }

    /**
     * Get my job alerts
     */
    public function index(Request $request): JsonResponse
    {
        $alerts = JobAlert::where('user_id', Auth::id())
                         ->orderBy('created_at', 'desc')
                         ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $alerts->items(),
            'pagination' => [
                'current_page' => $alerts->currentPage(),
                'per_page' => $alerts->perPage(),
                'total' => $alerts->total(),
                'total_pages' => $alerts->lastPage(),
            ],
        ]);
    }

    /**
     * Get single job alert
     */
    public function show($id): JsonResponse
    {
        $alert = JobAlert::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job alert not found',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $alert,
        ]);
    }

    /**
     * Update job alert
     */
    public function update(Request $request, $id): JsonResponse
    {
        $alert = JobAlert::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job alert not found',
                ],
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'keywords' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'work_type' => 'nullable|string|in:Full-time,Part-time,Contract,Freelance,Internship,Temporary',
            'salary_range' => 'nullable|string',
            'experience_level' => 'nullable|string|in:entry,mid,senior,executive',
            'education_level' => 'nullable|string|in:high_school,associate,bachelor,master,doctorate',
            'remote_only' => 'boolean',
            'frequency' => 'sometimes|required|string|in:daily,weekly,monthly,instant',
            'active' => 'boolean',
        ]);

        $alert->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Job alert updated successfully',
            'data' => $alert,
        ]);
    }

    /**
     * Delete job alert
     */
    public function destroy($id): JsonResponse
    {
        $alert = JobAlert::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job alert not found',
                ],
            ], 404);
        }

        $alert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job alert deleted successfully',
        ]);
    }

    /**
     * Test job alert
     */
    public function test($id): JsonResponse
    {
        $alert = JobAlert::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job alert not found',
                ],
            ], 404);
        }

        $testResult = $alert->test();

        return response()->json([
            'success' => true,
            'message' => 'Test alert sent successfully',
            'data' => $testResult,
        ]);
    }

    /**
     * Get alert statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_alerts' => JobAlert::where('user_id', Auth::id())->count(),
            'active_alerts' => JobAlert::where('user_id', Auth::id())->active()->count(),
            'inactive_alerts' => JobAlert::where('user_id', Auth::id())->where('active', false)->count(),
            'alerts_by_frequency' => JobAlert::where('user_id', Auth::id())
                                    ->selectRaw('frequency, COUNT(*) as count')
                                    ->groupBy('frequency')
                                    ->get()
                                    ->mapWithKeys(function ($item) {
                                        return [$item->frequency => $item->count];
                                    }),
            'total_jobs_sent' => JobAlert::where('user_id', Auth::id())->sum('jobs_sent_count'),
            'recent_alerts' => JobAlert::where('user_id', Auth::id())
                                  ->orderBy('created_at', 'desc')
                                  ->limit(5)
                                  ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get matching jobs for an alert
     */
    public function matchingJobs($id, Request $request): JsonResponse
    {
        $alert = JobAlert::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job alert not found',
                ],
            ], 404);
        }

        $limit = min($request->limit ?? 20, 50);
        $jobs = $alert->findMatchingJobs($limit);

        return response()->json([
            'success' => true,
            'data' => $jobs,
            'alert' => [
                'id' => $alert->id,
                'name' => $alert->name,
                'matching_count' => $jobs->count(),
            ],
        ]);
    }
}
