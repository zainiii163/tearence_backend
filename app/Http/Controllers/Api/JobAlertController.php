<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobAlert;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobAlertNotification;
use Illuminate\Validation\Rule;

class JobAlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JobAlert::where('user_id', Auth::id())
            ->with(['jobCategory']);

        $alerts = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'keywords' => 'nullable|string',
            'job_category_id' => 'nullable|exists:job_categories,id',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'work_type' => ['nullable', Rule::in(['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote'])],
            'salary_range' => 'nullable|string|max:100',
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;

        $alert = JobAlert::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job alert created successfully',
            'data' => $alert->load(['jobCategory']),
        ], 201);
    }

    public function show(JobAlert $jobAlert): JsonResponse
    {
        if ($jobAlert->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobAlert->load(['jobCategory']);

        return response()->json([
            'success' => true,
            'data' => $jobAlert,
        ]);
    }

    public function update(Request $request, JobAlert $jobAlert): JsonResponse
    {
        if ($jobAlert->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'keywords' => 'nullable|string',
            'job_category_id' => 'nullable|exists:job_categories,id',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'work_type' => ['nullable', Rule::in(['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote'])],
            'salary_range' => 'nullable|string|max:100',
            'frequency' => ['sometimes', 'required', Rule::in(['daily', 'weekly', 'monthly'])],
            'is_active' => 'boolean',
        ]);

        $jobAlert->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job alert updated successfully',
            'data' => $jobAlert->load(['jobCategory']),
        ]);
    }

    public function destroy(JobAlert $jobAlert): JsonResponse
    {
        if ($jobAlert->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $jobAlert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job alert deleted successfully',
        ]);
    }

    public function test(JobAlert $jobAlert): JsonResponse
    {
        if ($jobAlert->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $matchingJobs = $this->findMatchingJobs($jobAlert, 5);

        return response()->json([
            'success' => true,
            'message' => 'Test alert generated successfully',
            'data' => [
                'alert' => $jobAlert->load(['jobCategory']),
                'matching_jobs' => $matchingJobs,
                'total_matches' => $matchingJobs->count(),
            ],
        ]);
    }

    public function sendAlerts(): JsonResponse
    {
        $alerts = JobAlert::active()->get();
        $sentCount = 0;

        foreach ($alerts as $alert) {
            if ($alert->shouldSend()) {
                $matchingJobs = $this->findMatchingJobs($alert, 20);
                
                if ($matchingJobs->count() > 0) {
                    try {
                        Mail::to($alert->user->email)->send(new JobAlertNotification($alert, $matchingJobs));
                        $alert->markAsSent();
                        $sentCount++;
                    } catch (\Exception $e) {
                        // Log error but continue with other alerts
                        \Log::error('Failed to send job alert: ' . $e->getMessage());
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Job alerts processed. {$sentCount} alerts sent.",
            'data' => [
                'alerts_processed' => $alerts->count(),
                'alerts_sent' => $sentCount,
            ],
        ]);
    }

    private function findMatchingJobs(JobAlert $alert, int $limit = 20)
    {
        $query = JobListing::active()
            ->with(['jobCategory', 'user']);

        // Keyword search
        if ($alert->keywords) {
            $keywords = explode(',', $alert->keywords);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('title', 'like', '%' . trim($keyword) . '%')
                      ->orWhere('description', 'like', '%' . trim($keyword) . '%');
                }
            });
        }

        // Category filter
        if ($alert->job_category_id) {
            $query->where('job_category_id', $alert->job_category_id);
        }

        // Country filter
        if ($alert->country) {
            $query->where('country', $alert->country);
        }

        // City filter
        if ($alert->city) {
            $query->where('city', $alert->city);
        }

        // Work type filter
        if ($alert->work_type) {
            $query->where('work_type', $alert->work_type);
        }

        // Salary range filter (basic implementation)
        if ($alert->salary_range) {
            $query->whereNotNull('salary_range');
        }

        // Order by featured first, then by date
        $query->orderBy('is_featured', 'desc')
              ->orderBy('created_at', 'desc');

        return $query->limit($limit)->get();
    }

    public function stats(): JsonResponse
    {
        $userId = Auth::id();
        
        $stats = [
            'total_alerts' => JobAlert::where('user_id', $userId)->count(),
            'active_alerts' => JobAlert::where('user_id', $userId)->active()->count(),
            'daily_alerts' => JobAlert::where('user_id', $userId)->byFrequency('daily')->count(),
            'weekly_alerts' => JobAlert::where('user_id', $userId)->byFrequency('weekly')->count(),
            'monthly_alerts' => JobAlert::where('user_id', $userId)->byFrequency('monthly')->count(),
            'recent_alerts' => JobAlert::where('user_id', $userId)
                ->with(['jobCategory'])
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
