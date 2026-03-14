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
        $job = Job::active()->notExpired()->find($jobId);

        if (!$job) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job not found or no longer accepting applications',
                ],
            ], 404);
        }

        // Check if user has already applied
        if ($job->hasAppliedByUser(Auth::id())) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE_APPLICATION',
                    'message' => 'You have already applied for this job',
                ],
            ], 422);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'cover_letter' => 'nullable|string|min:50',
            'cv_file' => 'nullable|string',
            'portfolio_links' => 'nullable|array',
            'portfolio_links.*' => 'url',
            'expected_salary' => 'nullable|string',
            'available_start_date' => 'nullable|date|after:today',
            'additional_notes' => 'nullable|string',
        ]);

        $application = JobApplication::create([
            'job_id' => $jobId,
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'location' => $request->location,
            'cover_letter' => $request->cover_letter,
            'cv_file_url' => $request->cv_file,
            'portfolio_links' => $request->portfolio_links,
            'expected_salary' => $request->expected_salary,
            'available_start_date' => $request->available_start_date,
            'additional_notes' => $request->additional_notes,
            'status' => 'submitted',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => [
                'application_id' => $application->id,
                'job_id' => $jobId,
                'status' => $application->status,
                'submitted_at' => $application->created_at,
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
