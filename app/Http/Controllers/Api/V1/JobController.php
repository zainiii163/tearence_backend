<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobPricingPlan;
use App\Models\JobSave;
use App\Models\JobView;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    // Public endpoints

    /**
     * Get all jobs with filtering and search
     */
    public function index(Request $request): JsonResponse
    {
        $query = Job::with(['category', 'user'])
                    ->active()
                    ->notExpired();

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('description', 'LIKE', "%{$request->search}%")
                  ->orWhere('company_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('skills_needed', 'LIKE', "%{$request->search}%");
            });
        }

        // Location filter
        if ($request->location) {
            $query->byLocation($request->location);
        }

        // Category filter
        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Work type filter
        if ($request->work_type) {
            $query->byWorkType($request->work_type);
        }

        // Salary range filter
        if ($request->salary_range) {
            $range = explode('-', $request->salary_range);
            if (count($range) === 2) {
                $query->bySalaryRange($range[0], $range[1]);
            } else {
                $query->bySalaryRange($range[0]);
            }
        }

        // Remote only filter
        if ($request->boolean('remote_only')) {
            $query->remote();
        }

        // Experience level filter
        if ($request->experience_level) {
            $query->byExperienceLevel($request->experience_level);
        }

        // Education level filter
        if ($request->education_level) {
            $query->where('education_level', $request->education_level);
        }

        // Country filter
        if ($request->country) {
            $query->where('country', $request->country);
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'most_recent';
        switch ($sortBy) {
            case 'salary_high_low':
                $query->orderByRaw("CAST(SUBSTRING_INDEX(salary_range, '-', 1) AS UNSIGNED) DESC");
                break;
            case 'salary_low_high':
                $query->orderByRaw("CAST(SUBSTRING_INDEX(salary_range, '-', 1) AS UNSIGNED) ASC");
                break;
            case 'most_viewed':
                $query->orderBy('views', 'desc');
                break;
            case 'trending':
                $query->orderBy('views', 'desc')
                      ->orderBy('applications_count', 'desc');
                break;
            default: // most_recent
                $query->orderBy('posted_at', 'desc');
        }

        // Promoted jobs first
        $query->orderByRaw("CASE WHEN promotion_type != 'basic' AND (promotion_expires_at IS NULL OR promotion_expires_at > NOW()) THEN 0 ELSE 1 END");

        $perPage = min($request->per_page ?? 20, 100);
        $jobs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'total_pages' => $jobs->lastPage(),
                'has_next' => $jobs->hasMorePages(),
                'has_prev' => $jobs->currentPage() > 1,
            ],
        ]);
    }

    /**
     * Get single job details
     */
    public function show($slug): JsonResponse
    {
        $job = Job::with(['category', 'user'])
                 ->where('slug', $slug)
                 ->active()
                 ->notExpired()
                 ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job not found',
                ],
            ], 404);
        }

        // Track view
        JobView::trackView($job->id, Auth::id());

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    /**
     * Get featured jobs
     */
    public function featured(Request $request): JsonResponse
    {
        $jobs = Job::with(['category', 'user'])
                  ->promoted()
                  ->active()
                  ->notExpired()
                  ->orderBy('posted_at', 'desc')
                  ->limit($request->limit ?? 10)
                  ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    /**
     * Get jobs by category
     */
    public function byCategory($slug): JsonResponse
    {
        $category = JobCategory::where('slug', $slug)->active()->first();
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Category not found',
                ],
            ], 404);
        }

        $jobs = Job::with(['category', 'user'])
                  ->where('category_id', $category->id)
                  ->active()
                  ->notExpired()
                  ->orderBy('posted_at', 'desc')
                  ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'category' => $category,
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'total_pages' => $jobs->lastPage(),
            ],
        ]);
    }

    /**
     * Get job categories
     */
    public function categories(): JsonResponse
    {
        $categories = JobCategory::active()
                                ->withCount(['activeJobs' => function ($query) {
                                    $query->active()->notExpired();
                                }])
                                ->orderBy('name')
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->slug,
                    'name' => $category->name,
                    'description' => $category->description,
                    'icon' => $category->icon,
                    'job_count' => $category->active_jobs_count,
                    'trending' => $category->trending,
                ];
            }),
        ]);
    }

    /**
     * Get pricing plans
     */
    public function pricingPlans(): JsonResponse
    {
        $plans = JobPricingPlan::active()
                            ->orderBy('price')
                            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans->map(function ($plan) {
                return [
                    'id' => $plan->slug,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'period' => $plan->period,
                    'features' => $plan->features,
                    'recommended' => $plan->recommended,
                ];
            }),
        ]);
    }

    /**
     * Get platform statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_jobs' => Job::active()->notExpired()->count(),
            'active_companies' => Job::active()->notExpired()->distinct('user_id')->count(),
            'total_applications' => \App\Models\JobApplication::count(),
            'success_rate' => 98, // This would be calculated from actual data
            'popular_categories' => JobCategory::withCount(['activeJobs' => function ($query) {
                $query->active()->notExpired();
            }])
            ->orderBy('active_jobs_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($category) {
                return [
                    'category' => $category->name,
                    'count' => $category->active_jobs_count,
                    'growth' => rand(5, 25), // This would be calculated from actual data
                ];
            }),
            'top_locations' => Job::active()->notExpired()
                                ->selectRaw('country, city, COUNT(*) as job_count')
                                ->groupBy('country', 'city')
                                ->orderBy('job_count', 'desc')
                                ->limit(5)
                                ->get(),
            'average_salary' => [
                'USD' => 75000,
                'EUR' => 65000,
                'GBP' => 55000,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Protected endpoints (require authentication)

    /**
     * Create new job posting
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'skills_needed' => 'nullable|string',
            'company_name' => 'required|string|max:255',
            'company_description' => 'nullable|string',
            'company_size' => 'nullable|string',
            'company_industry' => 'nullable|string',
            'company_founded' => 'nullable|string',
            'company_logo' => 'nullable|string',
            'company_website' => 'nullable|url',
            'company_social' => 'nullable|array',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'work_type' => 'required|string|in:Full-time,Part-time,Contract,Freelance,Internship,Temporary',
            'salary_range' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'experience_level' => 'required|string|in:entry,mid,senior,executive',
            'education_level' => 'nullable|string|in:high_school,associate,bachelor,master,doctorate',
            'remote_available' => 'boolean',
            'application_method' => 'required|string|in:email,website,phone,in_person',
            'application_email' => 'nullable|required_if:application_method,email|email',
            'application_phone' => 'nullable|required_if:application_method,phone|string',
            'application_website' => 'nullable|required_if:application_method,website|url',
            'application_instructions' => 'nullable|string',
            'verified_employer' => 'boolean',
            'terms_accepted' => 'accepted',
            'accurate_info' => 'accepted',
        ]);

        $job = Job::create(array_merge($request->all(), [
            'user_id' => Auth::id(),
            'status' => 'pending_review',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Job posted successfully',
            'data' => [
                'id' => $job->id,
                'title' => $job->title,
                'status' => $job->status,
                'posted_at' => $job->posted_at,
            ],
        ], 201);
    }

    /**
     * Update job posting
     */
    public function update(Request $request, $id): JsonResponse
    {
        $job = Job::where('id', $id)
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

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|min:50',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'skills_needed' => 'nullable|string',
            'company_name' => 'sometimes|required|string|max:255',
            'company_description' => 'nullable|string',
            'company_size' => 'nullable|string',
            'company_industry' => 'nullable|string',
            'company_founded' => 'nullable|string',
            'company_logo' => 'nullable|string',
            'company_website' => 'nullable|url',
            'company_social' => 'nullable|array',
            'country' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'state' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'work_type' => 'sometimes|required|string|in:Full-time,Part-time,Contract,Freelance,Internship,Temporary',
            'salary_range' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'experience_level' => 'sometimes|required|string|in:entry,mid,senior,executive',
            'education_level' => 'nullable|string|in:high_school,associate,bachelor,master,doctorate',
            'remote_available' => 'boolean',
            'application_method' => 'sometimes|required|string|in:email,website,phone,in_person',
            'application_email' => 'nullable|required_if:application_method,email|email',
            'application_phone' => 'nullable|required_if:application_method,phone|string',
            'application_website' => 'nullable|required_if:application_method,website|url',
            'application_instructions' => 'nullable|string',
        ]);

        $job->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $job,
        ]);
    }

    /**
     * Delete job posting
     */
    public function destroy($id): JsonResponse
    {
        $job = Job::where('id', $id)
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

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully',
        ]);
    }

    /**
     * Get my job postings
     */
    public function myJobs(Request $request): JsonResponse
    {
        $query = Job::with(['category', 'applications'])
                    ->where('user_id', Auth::id());

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $jobs = $query->orderBy('created_at', 'desc')
                      ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'total_pages' => $jobs->lastPage(),
            ],
        ]);
    }

    /**
     * Save/unsave a job
     */
    public function saveJob($id): JsonResponse
    {
        $job = Job::active()->notExpired()->find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job not found',
                ],
            ], 404);
        }

        $isSaved = JobSave::isSaved($id, Auth::id());

        if ($isSaved) {
            JobSave::unsaveJob($id, Auth::id());
            $message = 'Job removed from saved jobs';
        } else {
            JobSave::saveJob($id, Auth::id());
            $message = 'Job saved successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'saved' => !$isSaved,
        ]);
    }

    /**
     * Get saved jobs
     */
    public function savedJobs(Request $request): JsonResponse
    {
        $saves = JobSave::with(['job.category', 'job.user'])
                       ->where('user_id', Auth::id())
                       ->orderBy('created_at', 'desc')
                       ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $saves->getCollection()->map(function ($save) {
                return $save->job;
            }),
            'pagination' => [
                'current_page' => $saves->currentPage(),
                'per_page' => $saves->perPage(),
                'total' => $saves->total(),
                'total_pages' => $saves->lastPage(),
            ],
        ]);
    }
}
