<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobListing;
use App\Models\JobCategory;
use App\Models\JobApplication;
use App\Models\JobSavedListing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JobListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JobListing::with(['user', 'jobCategory'])
            ->active()
            ->withCount(['applications']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('job_category_id', $request->category_id);
        }

        // Country filter
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        // Work type filter
        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        // Remote filter
        if ($request->boolean('remote_only')) {
            $query->where('work_type', 'remote');
        }

        // Featured jobs
        if ($request->boolean('featured_only')) {
            $query->featured();
        }

        // Urgent jobs
        if ($request->boolean('urgent_only')) {
            $query->urgent();
        }

        // Sorting
        $sort = $request->get('sort', 'recent');
        switch ($sort) {
            case 'salary_high':
                $query->orderByRaw('CASE WHEN salary_range IS NOT NULL THEN 0 ELSE 1 END');
                break;
            case 'most_viewed':
                $query->orderBy('views_count', 'desc');
                break;
            case 'trending':
                $query->orderBy('applications_count', 'desc')
                       ->orderBy('views_count', 'desc');
                break;
            default:
                $query->orderBy('is_featured', 'desc')
                       ->orderBy('created_at', 'desc');
        }

        $jobs = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function show(JobListing $jobListing): JsonResponse
    {
        $jobListing->load(['user', 'jobCategory', 'applications' => function ($query) {
            $query->with('user')->latest();
        }]);

        // Increment views
        $jobListing->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $jobListing,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'job_category_id' => 'nullable|exists:job_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'skills_needed' => 'nullable|string',
            'company_name' => 'required|string|max:255',
            'company_website' => 'nullable|url',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'work_type' => ['required', Rule::in(['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote'])],
            'experience_level' => ['nullable', Rule::in(['entry_level', 'mid_level', 'senior_level', 'executive'])],
            'education_level' => ['nullable', Rule::in(['high_school', 'associate', 'bachelor', 'master', 'phd'])],
            'salary_range' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'benefits' => 'nullable|string',
            'application_method' => ['required', Rule::in(['email', 'website', 'platform'])],
            'application_email' => 'required_if:application_method,email|email',
            'application_url' => 'required_if:application_method,website|url',
            'is_urgent' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $path = $request->file('company_logo')->store('company-logos', 'public');
            $validated['company_logo'] = $path;
        }

        $validated['user_id'] = Auth::id();
        $validated['currency'] = $validated['currency'] ?? 'USD';

        $job = JobListing::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job posted successfully',
            'data' => $job->load(['jobCategory']),
        ], 201);
    }

    public function update(Request $request, JobListing $jobListing): JsonResponse
    {
        if ($jobListing->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'job_category_id' => 'nullable|exists:job_categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'skills_needed' => 'nullable|string',
            'company_name' => 'sometimes|required|string|max:255',
            'company_website' => 'nullable|url',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'nullable|string|max:100',
            'work_type' => ['sometimes', 'required', Rule::in(['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote'])],
            'experience_level' => ['nullable', Rule::in(['entry_level', 'mid_level', 'senior_level', 'executive'])],
            'education_level' => ['nullable', Rule::in(['high_school', 'associate', 'bachelor', 'master', 'phd'])],
            'salary_range' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'benefits' => 'nullable|string',
            'application_method' => ['sometimes', 'required', Rule::in(['email', 'website', 'platform'])],
            'application_email' => 'required_if:application_method,email|email',
            'application_url' => 'required_if:application_method,website|url',
            'is_urgent' => 'boolean',
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            // Delete old logo
            if ($jobListing->company_logo) {
                Storage::disk('public')->delete($jobListing->company_logo);
            }
            $path = $request->file('company_logo')->store('company-logos', 'public');
            $validated['company_logo'] = $path;
        }

        $jobListing->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $jobListing->load(['jobCategory']),
        ]);
    }

    public function destroy(JobListing $jobListing): JsonResponse
    {
        if ($jobListing->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Delete company logo
        if ($jobListing->company_logo) {
            Storage::disk('public')->delete($jobListing->company_logo);
        }

        $jobListing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully',
        ]);
    }

    public function myJobs(Request $request): JsonResponse
    {
        $jobs = JobListing::where('user_id', Auth::id())
            ->with(['jobCategory', 'applications'])
            ->withCount(['applications'])
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function saveJob(Request $request, JobListing $jobListing): JsonResponse
    {
        $userId = Auth::id();
        
        $saved = JobSavedListing::updateOrCreate(
            ['user_id' => $userId, 'job_listing_id' => $jobListing->id],
            []
        );

        return response()->json([
            'success' => true,
            'message' => $saved->wasRecentlyCreated ? 'Job saved successfully' : 'Job already saved',
            'data' => [
                'is_saved' => true,
            ],
        ]);
    }

    public function unsaveJob(Request $request, JobListing $jobListing): JsonResponse
    {
        $deleted = JobSavedListing::where('user_id', Auth::id())
            ->where('job_listing_id', $jobListing->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted ? 'Job removed from saved list' : 'Job was not in saved list',
            'data' => [
                'is_saved' => false,
            ],
        ]);
    }

    public function savedJobs(Request $request): JsonResponse
    {
        $jobs = JobSavedListing::where('user_id', Auth::id())
            ->with(['jobListing' => function ($query) {
                $query->with(['jobCategory', 'user']);
            }])
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = JobCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->withCount(['activeJobListings'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total_jobs' => JobListing::active()->count(),
            'featured_jobs' => JobListing::active()->featured()->count(),
            'urgent_jobs' => JobListing::active()->urgent()->count(),
            'total_categories' => JobCategory::where('is_active', true)->count(),
            'recent_jobs' => JobListing::active()->latest()->take(5)->get(['id', 'title', 'company_name', 'country', 'created_at']),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
