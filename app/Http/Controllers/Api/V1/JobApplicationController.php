<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    /**
     * Apply for a job
     */
    public function apply(Request $request, $jobId): JsonResponse
    {
        $job = Job::findPublic($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job not found or no longer accepting applications',
                ],
            ], 404);
        }

        $resolvedJobId = (int) $job->id;
        $userId = Auth::id();

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Check if user has already applied
        if ($job->hasAppliedByUser($userId)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE_APPLICATION',
                    'message' => 'You have already applied for this job',
                ],
            ], 422);
        }

        $validated = $request->validate([
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'cover_letter' => 'nullable|string|max:5000',
            'portfolio_link' => 'nullable|string|max:500',
            'cv_file' => 'nullable|string|max:500',
            'full_name' => 'nullable|string|max:255',
        ]);

        if (! empty($validated['portfolio_link']) && ! filter_var($validated['portfolio_link'], FILTER_VALIDATE_URL)) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio link must be a valid URL',
                'errors' => ['portfolio_link' => ['Portfolio link must be a valid URL']],
            ], 422);
        }

        $seeker = \App\Models\JobSeeker::query()
            ->where('user_id', $userId)
            ->first();

        $cvPath = $validated['cv_file'] ?? null;
        if (! $cvPath && $seeker && ! empty($seeker->cv_file)) {
            $cvPath = $seeker->cv_file;
        }

        // Prefer storage-relative path when a /storage URL is sent
        if (is_string($cvPath) && preg_match('#/storage/(.+)$#', $cvPath, $m)) {
            $cvPath = $m[1];
        }

        $application = JobApplication::create([
            'job_id' => $resolvedJobId,
            'user_id' => $userId,
            'job_seeker_id' => $seeker?->id,
            'cover_letter' => $validated['cover_letter'] ?? null,
            'cv_file' => $cvPath,
            'portfolio_link' => $validated['portfolio_link'] ?? null,
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? ($seeker->phone ?? null),
            'status' => 'pending',
            'applied_at' => now(),
        ]);

        try {
            $job->incrementApplications();
        } catch (\Throwable $e) {
            // applications_count column may be missing on older schemas
        }

        // Soft notification for employer (dashboard)
        try {
            if ($job->user_id) {
                $authUser = Auth::user();
                $applicantName = $validated['full_name']
                    ?? trim(($authUser->first_name ?? '').' '.($authUser->last_name ?? ''))
                    ?: ($authUser->email ?? 'A candidate');

                \App\Models\CustomerNotification::notify(
                    (int) $job->user_id,
                    \App\Models\CustomerNotification::TYPE_SELLER_ENQUIRY,
                    "{$applicantName} applied for \"{$job->title}\"",
                    'New job application',
                    [
                        'hub' => 'jobs',
                        'job_id' => $resolvedJobId,
                        'application_id' => $application->id,
                        'url' => '/jobs/'.$job->slug,
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Log::warning('Job application notification failed', [
                'job_id' => $resolvedJobId,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => [
                'application_id' => $application->id,
                'job_id' => $resolvedJobId,
                'status' => $application->status,
                'submitted_at' => $application->applied_at ?? $application->created_at,
                'has_seeker_profile' => (bool) $seeker,
            ],
        ], 201);
    }

    /**
     * Get applications for job (employer only)
     */
    public function getJobApplications(Request $request, $jobId): JsonResponse
    {
        $job = Job::where('id', $jobId)
                 ->where('user_id', Auth::id())
                 ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job not found or access denied',
                ],
            ], 404);
        }

        $query = $job->applications()->with(['user', 'jobSeeker']);

        // Status filter
        if ($request->status) {
            $query->byStatus($request->status);
        }

        $applications = $query->orderBy('created_at', 'desc')
                             ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $applications->items(),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
                'total_pages' => $applications->lastPage(),
            ],
        ]);
    }

    /**
     * Get all applications (employer)
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobApplication::with(['job', 'user', 'jobSeeker'])
                              ->whereHas('job', function ($q) {
                                  $q->where('user_id', Auth::id());
                              });

        // Job filter
        if ($request->job_id) {
            $query->where('job_id', $request->job_id);
        }

        // Status filter
        if ($request->status) {
            $query->byStatus($request->status);
        }

        $applications = $query->orderBy('created_at', 'desc')
                             ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $applications->items(),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
                'total_pages' => $applications->lastPage(),
            ],
        ]);
    }

    /**
     * Get application details
     */
    public function show($id): JsonResponse
    {
        $application = JobApplication::with(['job', 'user', 'jobSeeker'])
                                    ->find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Application not found',
                ],
            ], 404);
        }

        // Check if user owns the job or is the applicant
        if ($application->job->user_id !== Auth::id() && $application->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTHORIZATION_FAILED',
                    'message' => 'Access denied',
                ],
            ], 403);
        }

        // Mark as viewed if employer is viewing
        if ($application->job->user_id === Auth::id() && $application->isSubmitted()) {
            $application->markAsViewed();
        }

        return response()->json([
            'success' => true,
            'data' => $application,
        ]);
    }

    /**
     * Update application status (employer only)
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $application = JobApplication::with('job')->find($id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Application not found',
                ],
            ], 404);
        }

        // Check if user owns the job
        if ($application->job->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTHORIZATION_FAILED',
                    'message' => 'Access denied',
                ],
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:viewed,shortlisted,interview_scheduled,rejected,hired',
            'employer_notes' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'interview_date' => 'nullable|required_if:status,interview_scheduled|date|after:today',
            'interview_type' => 'nullable|required_if:status,interview_scheduled|string|in:phone,video,in_person',
            'interview_notes' => 'nullable|string',
        ]);

        $application->update([
            'status' => $request->status,
            'employer_notes' => $request->employer_notes,
            'next_steps' => $request->next_steps,
            'interview_date' => $request->interview_date,
            'interview_type' => $request->interview_type,
            'interview_notes' => $request->interview_notes,
            'status_updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully',
            'data' => $application,
        ]);
    }

    /**
     * Get application statistics (employer)
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_applications' => JobApplication::whereHas('job', function ($q) {
                $q->where('user_id', Auth::id());
            })->count(),
            'pending_applications' => JobApplication::whereHas('job', function ($q) {
                $q->where('user_id', Auth::id());
            })->pending()->count(),
            'viewed_applications' => JobApplication::whereHas('job', function ($q) {
                $q->where('user_id', Auth::id());
            })->viewed()->count(),
            'shortlisted_applications' => JobApplication::whereHas('job', function ($q) {
                $q->where('user_id', Auth::id());
            })->shortlisted()->count(),
            'rejected_applications' => JobApplication::whereHas('job', function ($q) {
                $q->where('user_id', Auth::id());
            })->rejected()->count(),
            'hired_applications' => JobApplication::whereHas('job', function ($q) {
                $q->where('user_id', Auth::id());
            })->hired()->count(),
            'applications_by_status' => JobApplication::whereHas('job', function ($q) {
                $q->where('user_id', Auth::id());
            })->selectRaw('status, COUNT(*) as count')
              ->groupBy('status')
              ->get()
              ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
              }),
            'recent_applications' => JobApplication::whereHas('job', function ($q) {
                $q->where('user_id', Auth::id());
            })->with('job')
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
     * Get my applications (job seeker)
     */
    public function myApplications(Request $request): JsonResponse
    {
        $query = JobApplication::with(['job.category', 'job.user'])
                              ->where('user_id', Auth::id());

        // Status filter
        if ($request->status) {
            $query->byStatus($request->status);
        }

        $applications = $query->orderBy('created_at', 'desc')
                             ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $applications->items(),
            'pagination' => [
                'current_page' => $applications->currentPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
                'total_pages' => $applications->lastPage(),
            ],
        ]);
    }

    /**
     * Withdraw application (job seeker)
     */
    public function withdraw($id): JsonResponse
    {
        $application = JobApplication::where('id', $id)
                                   ->where('user_id', Auth::id())
                                   ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Application not found or access denied',
                ],
            ], 404);
        }

        $application->update([
            'status' => 'withdrawn',
            'status_updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application withdrawn successfully',
        ]);
    }
}
