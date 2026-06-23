<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobPricingPlan;
use App\Models\JobSave;
use App\Models\JobView;
use App\Support\JobSchema;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            $query->where('city', 'LIKE', "%{$request->location}%")
                  ->orWhere('country', 'LIKE', "%{$request->location}%");
        }

        // Category filter
        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Work type filter
        if ($request->work_type) {
            $query->where('work_type', $request->work_type);
        }

        // Remote only filter
        if ($request->boolean('remote_only')) {
            $query->where(JobSchema::column('remote'), true);
        }

        // Experience level filter
        if ($request->experience_level) {
            $query->where('experience_level', $request->experience_level);
        }

        // Country filter
        if ($request->country) {
            $query->where('country', $request->country);
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'newest';
        switch ($sortBy) {
            case 'salary_high_low':
                $query->orderBy('salary_max', 'desc');
                break;
            case 'salary_low_high':
                $query->orderBy('salary_min', 'asc');
                break;
            case 'most_viewed':
                $query->orderBy(JobSchema::column('views'), 'desc');
                break;
            case 'trending':
                $query->orderBy(JobSchema::column('views'), 'desc')
                      ->orderBy('applications_count', 'desc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
        }

        // Featured jobs first
        $query->orderByRaw("CASE WHEN is_featured = 1 AND (featured_until IS NULL OR featured_until > NOW()) THEN 0 ELSE 1 END");

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
    public function show($id): JsonResponse
    {
        $job = Job::with(['category', 'user'])
                 ->where('id', $id)
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
        try {
            JobView::trackView($job->id, Auth::id());
        } catch (\Exception $e) {
            // Ignore view tracking errors
        }

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    
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
                    'id' => $category->id,
                    'slug' => $category->slug,
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
        try {
            // Build a base active+not-expired query without relying on potentially-divergent scopes
            $baseQuery = function () {
                return Job::query()
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    });
            };

            // Detect category foreign key column dynamically (job_category_id or category_id)
            $jobCategoryFk = \Schema::hasColumn('jobs', 'job_category_id') ? 'job_category_id' : 'category_id';

            // Use raw DB query to avoid Eloquent accessors that may reference non-existent columns
            $popularCategories = \DB::table('job_categories')
                ->select('job_categories.id', 'job_categories.name')
                ->selectSub(function ($sub) use ($jobCategoryFk) {
                    $sub->from('jobs')
                        ->selectRaw('count(*)')
                        ->whereColumn('jobs.' . $jobCategoryFk, 'job_categories.id')
                        ->where('jobs.is_active', true)
                        ->where(function ($q) {
                            $q->whereNull('jobs.expires_at')->orWhere('jobs.expires_at', '>', now());
                        });
                }, 'jobs_count')
                ->orderByDesc('jobs_count')
                ->limit(5)
                ->get()
                ->map(function ($category) {
                    return [
                        'category' => $category->name,
                        'count' => (int) $category->jobs_count,
                        'growth' => rand(5, 25),
                    ];
                });

            $stats = [
                'total_jobs' => $baseQuery()->count(),
                'active_companies' => $baseQuery()->distinct('user_id')->count('user_id'),
                'total_applications' => \App\Models\JobApplication::count(),
                'success_rate' => 98,
                'popular_categories' => $popularCategories,
                'top_locations' => $baseQuery()
                    ->selectRaw('country, city, COUNT(*) as job_count')
                    ->groupBy('country', 'city')
                    ->orderByDesc('job_count')
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
        } catch (\Exception $e) {
            \Log::error('Jobs statistics error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics',
                'error' => $e->getMessage(),
                'file' => basename($e->getFile()) . ':' . $e->getLine(),
            ], 500);
        }
    }

    /**
     * Get live activity feed
     */
    public function activities(): JsonResponse
    {
        try {
            $activities = collect();

            // Recent applications
            $recentApplications = \App\Models\JobApplication::with(['job', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentApplications as $application) {
                $activities->push([
                    'type' => 'application',
                    'message' => "A user applied for {$application->job->title}",
                    'timestamp' => $application->created_at,
                ]);
            }

            // New jobs posted
            $newJobs = Job::with(['category', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($newJobs as $job) {
                $activities->push([
                    'type' => 'new_job',
                    'message' => "New vacancy: {$job->title} in {$job->city}",
                    'timestamp' => $job->created_at,
                ]);
            }

            // Sort by timestamp and limit
            $activities = $activities->sortByDesc('timestamp')->take(10)->values();

            return response()->json([
                'success' => true,
                'data' => $activities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching activities',
            ], 500);
        }
    }

    /**
     * Get trending searches
     */
    public function trendingSearches(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['term' => 'Software Engineer', 'count' => 12500],
                ['term' => 'Data Scientist', 'count' => 9800],
                ['term' => 'Product Manager', 'count' => 8500],
                ['term' => 'UX Designer', 'count' => 7200],
                ['term' => 'Full Stack Developer', 'count' => 15000],
            ],
        ]);
    }

    // Protected endpoints (require authentication)

    /**
     * Create new job posting
     */
    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'category_id' => $request->input('category_id', $request->input('job_category_id')),
        ]);

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:job_categories,id',
            'description' => 'required|string',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'benefits' => 'nullable|string',
            'skills_needed' => 'nullable|string|max:500',
            'company_name' => 'required|string|max:255',
            'company_description' => 'nullable|string',
            'company_size' => 'nullable|string|max:50',
            'company_industry' => 'nullable|string|max:255',
            'company_founded' => 'nullable|string|max:20',
            'company_logo' => 'nullable|string|max:500',
            'company_website' => 'nullable|url|max:500',
            'company_social' => 'nullable|array',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'work_type' => 'required|string|in:Full-time,Part-time,Contract,Freelance,Internship,Temporary,full_time,part_time,contract,temporary,internship,remote',
            'salary_range' => 'nullable|string|max:100',
            'currency' => 'nullable|string|size:3',
            'experience_level' => 'required|string|in:entry,junior,mid,senior,executive,entry_level,mid_level,senior_level',
            'education_level' => 'nullable|string|in:high_school,associate,bachelor,master,doctorate,diploma,phd,none',
            'remote_available' => 'boolean',
            'application_method' => 'required|string|in:email,website,phone,in_person,platform,link',
            'application_email' => 'nullable|required_if:application_method,email|email|max:255',
            'application_phone' => 'nullable|required_if:application_method,phone|string|max:50',
            'application_website' => 'nullable|required_if:application_method,website|url|max:500',
            'application_instructions' => 'nullable|string|max:1000',
            'verified_employer' => 'boolean',
            'terms_accepted' => 'nullable|accepted',
            'accurate_info' => 'nullable|accepted',
        ]);

        if (!Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            $salary = $this->parseSalaryRange($request->salary_range);
            $applicationEmail = $request->application_email ?: Auth::user()->email;

            $payload = $this->buildJobPayload($request, $salary, $applicationEmail);
            $job = Job::create($payload);

            if ($job->category) {
                $job->category->increment('jobs_count');
            }

            return response()->json([
                'success' => true,
                'message' => 'Job posted successfully',
                'data' => $job->load(['category', 'user']),
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('Job create failed', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create job posting',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error while saving job',
            ], 500);
        }
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

    /**
     * Upload job-related file (logo, profile photo, CV)
     */
    public function uploadFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:5120', // 5MB max
            'type' => 'required|string|in:company_logo,profile_photo,cv_file',
        ]);

        try {
            $file = $request->file('file');
            $type = $request->type;

            // Validate file type based on upload type
            $allowedMimes = $this->getAllowedMimesForJobType($type);
            if (!in_array($file->getMimeType(), $allowedMimes)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file type for this upload',
                ], 422);
            }

            // Generate unique filename
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

            // Determine storage path
            $storagePath = $this->getJobStoragePath($type);
            $filePath = $file->storeAs($storagePath, $fileName, 'public');

            // Return file URL
            $fileUrl = Storage::url($filePath);

            return response()->json([
                'success' => true,
                'data' => [
                    'file_url' => $fileUrl,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                ],
                'message' => 'File uploaded successfully',
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to upload file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get allowed MIME types for job upload types
     */
    private function getAllowedMimesForJobType(string $type): array
    {
        $mimeTypes = [
            'company_logo' => [
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/gif',
                'image/webp',
            ],
            'profile_photo' => [
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/gif',
                'image/webp',
            ],
            'cv_file' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
        ];

        return $mimeTypes[$type] ?? [];
    }

    /**
     * Get storage path for job uploads
     */
    private function getJobStoragePath(string $type): string
    {
        $paths = [
            'company_logo' => 'jobs/company-logos',
            'profile_photo' => 'jobs/profile-photos',
            'cv_file' => 'jobs/cv-files',
        ];

        return $paths[$type] ?? 'jobs/other';
    }

    private function mapWorkType(string $workType): string
    {
        return match ($workType) {
            'Full-time', 'full_time' => 'full_time',
            'Part-time', 'part_time' => 'part_time',
            'Contract', 'contract' => 'contract',
            'Freelance' => 'contract',
            'Internship', 'internship' => 'internship',
            'Temporary', 'temporary' => 'temporary',
            'remote' => 'remote',
            default => 'full_time',
        };
    }

    private function mapApplicationMethod(string $method): string
    {
        return match ($method) {
            'website' => 'link',
            'phone', 'in_person' => 'platform',
            default => in_array($method, ['email', 'link', 'platform'], true) ? $method : 'email',
        };
    }

    private function mapEducationLevel(?string $level): ?string
    {
        if (!$level) {
            return null;
        }

        return match ($level) {
            'associate' => 'diploma',
            'doctorate' => 'phd',
            default => $level,
        };
    }

    /**
     * @param  array{min: float|null, max: float|null}  $salary
     */
    private function buildJobPayload(Request $request, array $salary, string $applicationEmail): array
    {
        $cols = JobSchema::columns();
        $experienceLevel = match ($request->experience_level) {
            'entry_level' => 'entry',
            'mid_level' => 'mid',
            'senior_level' => 'senior',
            default => $request->experience_level,
        };

        $payload = [
            'user_id' => Auth::id(),
            $cols['category'] => (int) $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'responsibilities' => $request->responsibilities,
            'requirements' => $request->requirements,
            'benefits' => $request->benefits,
            'skills_needed' => $request->skills_needed,
            'company_name' => $request->company_name,
            'company_description' => $request->company_description,
            'company_size' => $request->company_size,
            'company_industry' => $request->company_industry,
            'company_founded' => $request->company_founded,
            $cols['logo'] => $request->company_logo,
            'company_website' => $request->company_website,
            'company_social' => $request->company_social,
            'country' => $request->country,
            'city' => $request->city,
            'state' => $request->state,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'work_type' => $this->mapWorkType($request->work_type),
            'salary_range' => $request->salary_range,
            'salary_min' => $salary['min'],
            'salary_max' => $salary['max'],
            'experience_level' => $experienceLevel,
            'education_level' => $this->mapEducationLevel($request->education_level),
            $cols['remote'] => $request->boolean('remote_available'),
            'application_method' => $this->mapApplicationMethod($request->application_method),
            $cols['email'] => $applicationEmail,
            'application_link' => $request->application_website,
            'application_phone' => $request->application_phone,
            'application_instructions' => $request->application_instructions,
            $cols['verified'] => $request->boolean('verified_employer'),
            'terms_accepted' => $request->boolean('terms_accepted'),
            'accurate_info' => $request->boolean('accurate_info'),
            'expires_at' => now()->addDays(30),
        ];

        if (\Schema::hasColumn('jobs', 'posted_at')) {
            $payload['posted_at'] = now();
        }

        if (\Schema::hasColumn('jobs', 'slug')) {
            $payload['slug'] = Str::slug($request->title) . '-' . time();
        }

        if (\Schema::hasColumn('jobs', 'salary_currency')) {
            $payload['salary_currency'] = $request->currency ?? 'USD';
        }

        if (\Schema::hasColumn('jobs', 'currency')) {
            $payload['currency'] = $request->currency ?? 'USD';
        }

        if (\Schema::hasColumn('jobs', 'is_active')) {
            $payload['is_active'] = true;
        }

        if (\Schema::hasColumn('jobs', 'status')) {
            $payload['status'] = 'active';
        }

        if (\Schema::hasColumn('jobs', 'application_website') && $request->application_website) {
            $payload['application_website'] = $request->application_website;
        }

        return $payload;
    }

    /**
     * @return array{min: float|null, max: float|null}
     */
    private function parseSalaryRange(?string $range): array
    {
        if (!$range) {
            return ['min' => null, 'max' => null];
        }

        if (preg_match('/(\d[\d,]*)\s*-\s*(\d[\d,]*)/', $range, $matches)) {
            return [
                'min' => (float) str_replace(',', '', $matches[1]),
                'max' => (float) str_replace(',', '', $matches[2]),
            ];
        }

        return ['min' => null, 'max' => null];
    }
}
