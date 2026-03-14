<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JobApplicationController extends Controller
{
    public function store(Request $request, JobListing $jobListing): JsonResponse
    {
        // Check if user has already applied
        $existingApplication = JobApplication::where('job_listing_id', $jobListing->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job',
            ], 422);
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'cover_letter' => 'nullable|string',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        // Handle CV file upload
        if ($request->hasFile('cv_file')) {
            $path = $request->file('cv_file')->store('job-applications/cv', 'public');
            $validated['cv_file'] = $path;
        }

        $validated['job_listing_id'] = $jobListing->id;
        $validated['user_id'] = Auth::id();
        $validated['applied_at'] = now();

        $application = JobApplication::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => $application->load(['jobListing', 'user']),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = JobApplication::with(['jobListing', 'user']);

        // Filter by job listing
        if ($request->filled('job_listing_id')) {
            $query->where('job_listing_id', $request->job_listing_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Get applications for jobs posted by current user
        if ($request->boolean('my_jobs')) {
            $query->whereHas('jobListing', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        // Get applications by current user
        if ($request->boolean('my_applications')) {
            $query->where('user_id', Auth::id());
        }

        $applications = $query->latest('applied_at')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    public function show(JobApplication $jobApplication): JsonResponse
    {
        // Check if user owns the application or the job listing
        if ($jobApplication->user_id !== Auth::id() && 
            $jobApplication->jobListing->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Mark as viewed if employer is viewing
        if ($jobApplication->jobListing->user_id === Auth::id() && 
            $jobApplication->status === 'pending') {
            $jobApplication->markAsViewed();
        }

        $jobApplication->load(['jobListing', 'user']);

        return response()->json([
            'success' => true,
            'data' => $jobApplication,
        ]);
    }

    public function updateStatus(Request $request, JobApplication $jobApplication): JsonResponse
    {
        // Only job listing owner can update status
        if ($jobApplication->jobListing->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['viewed', 'shortlisted', 'rejected', 'hired'])],
            'employer_notes' => 'nullable|string',
        ]);

        $jobApplication->update($validated);

        // Update status based on method
        switch ($validated['status']) {
            case 'viewed':
                $jobApplication->markAsViewed();
                break;
            case 'shortlisted':
                $jobApplication->markAsShortlisted();
                break;
            case 'rejected':
                $jobApplication->markAsRejected();
                break;
            case 'hired':
                $jobApplication->markAsHired();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully',
            'data' => $jobApplication->load(['jobListing', 'user']),
        ]);
    }

    public function destroy(JobApplication $jobApplication): JsonResponse
    {
        // Only job listing owner can delete application
        if ($jobApplication->jobListing->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Delete CV file
        if ($jobApplication->cv_file) {
            Storage::disk('public')->delete($jobApplication->cv_file);
        }

        $jobApplication->delete();

        return response()->json([
            'success' => true,
            'message' => 'Application deleted successfully',
        ]);
    }

    public function stats(): JsonResponse
    {
        $userId = Auth::id();
        
        // For employers
        $employerStats = [
            'total_applications' => JobApplication::whereHas('jobListing', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'pending_applications' => JobApplication::whereHas('jobListing', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'pending')->count(),
            'shortlisted_applications' => JobApplication::whereHas('jobListing', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'shortlisted')->count(),
            'recent_applications' => JobApplication::whereHas('jobListing', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->with(['jobListing', 'user'])->latest('applied_at')->take(5)->get(),
        ];

        // For job seekers
        $jobSeekerStats = [
            'total_applications' => JobApplication::where('user_id', $userId)->count(),
            'pending_applications' => JobApplication::where('user_id', $userId)->where('status', 'pending')->count(),
            'viewed_applications' => JobApplication::where('user_id', $userId)->where('status', 'viewed')->count(),
            'shortlisted_applications' => JobApplication::where('user_id', $userId)->where('status', 'shortlisted')->count(),
            'recent_applications' => JobApplication::where('user_id', $userId)
                ->with(['jobListing'])
                ->latest('applied_at')
                ->take(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'employer' => $employerStats,
                'job_seeker' => $jobSeekerStats,
            ],
        ]);
    }
}
