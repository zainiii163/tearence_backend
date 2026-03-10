<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::with(['category', 'user'])
                    ->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });

        // Search filters
        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%')
                  ->orWhere('description', 'like', '%' . $request->keyword . '%')
                  ->orWhere('company_name', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->category_id) {
            $query->where('job_category_id', $request->category_id);
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->work_type) {
            $query->where('work_type', $request->work_type);
        }

        if ($request->experience_level) {
            $query->where('experience_level', $request->experience_level);
        }

        if ($request->education_level) {
            $query->where('education_level', $request->education_level);
        }

        if ($request->has('is_remote') && $request->is_remote !== null) {
            $query->where('is_remote', $request->boolean('is_remote'));
        }

        if ($request->has('is_urgent') && $request->is_urgent !== null) {
            $query->where('is_urgent', $request->boolean('is_urgent'));
        }

        if ($request->salary_min) {
            $query->where('salary_min', '>=', $request->salary_min);
        }

        if ($request->salary_max) {
            $query->where('salary_max', '<=', $request->salary_max);
        }

        // Sorting
        $sort = $request->sort ?? 'recent';
        switch ($sort) {
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'salary_high':
                $query->orderBy('salary_max', 'desc');
                break;
            case 'salary_low':
                $query->orderBy('salary_min', 'asc');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
            case 'trending':
                $query->orderBy('views_count', 'desc')
                      ->orderBy('applications_count', 'desc');
                break;
            case 'closing_soon':
                $query->orderBy('expires_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Priority ordering for promoted jobs
        $query->orderByRaw('
            CASE 
                WHEN is_sponsored = 1 AND sponsored_until > NOW() THEN 1
                WHEN is_featured = 1 AND featured_until > NOW() THEN 2
                WHEN is_promoted = 1 AND promoted_until > NOW() THEN 3
                ELSE 4
            END
        ');

        $jobs = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function show($slug)
    {
        $job = Job::with(['category', 'user', 'applications'])
                 ->where('slug', $slug)
                 ->where('is_active', true)
                 ->firstOrFail();

        // Increment views
        $job->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $job,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_category_id' => 'required|exists:job_categories,id',
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'skills_needed' => 'nullable|string',
            'benefits' => 'nullable|string',
            'company_name' => 'required|string|max:200',
            'company_website' => 'nullable|url|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'contact_email' => 'required|email',
            'application_link' => 'nullable|url|max:500',
            'application_method' => 'required|in:email,link,platform',
            'work_type' => 'required|in:full_time,part_time,contract,temporary,internship,remote',
            'experience_level' => 'required|in:entry,junior,mid,senior,executive',
            'education_level' => 'nullable|in:high_school,diploma,bachelor,master,phd,none',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'salary_currency' => 'string|max:3',
            'salary_type' => 'nullable|in:hourly,monthly,yearly,project',
            'salary_negotiable' => 'boolean',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_name' => 'nullable|string|max:255',
            'is_remote' => 'boolean',
            'is_urgent' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = auth()->id();

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            $logo = $request->file('company_logo');
            $logoPath = $logo->store('company-logos', 'public');
            $data['company_logo'] = $logoPath;
        }

        // Set default values
        $data['salary_currency'] = $data['salary_currency'] ?? 'USD';
        $data['salary_negotiable'] = $data['salary_negotiable'] ?? false;
        $data['is_remote'] = $data['is_remote'] ?? false;
        $data['is_urgent'] = $data['is_urgent'] ?? false;
        $data['is_active'] = true;

        $job = Job::create($data);

        // Update category jobs count
        $job->category->increment('jobs_count');

        return response()->json([
            'success' => true,
            'message' => 'Job posted successfully',
            'data' => $job->load(['category', 'user']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $job = Job::where('user_id', auth()->id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'job_category_id' => 'exists:job_categories,id',
            'title' => 'string|max:200',
            'description' => 'string',
            'responsibilities' => 'nullable|string',
            'requirements' => 'nullable|string',
            'skills_needed' => 'nullable|string',
            'benefits' => 'nullable|string',
            'company_name' => 'string|max:200',
            'company_website' => 'nullable|url|max:255',
            'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'contact_email' => 'email',
            'application_link' => 'nullable|url|max:500',
            'application_method' => 'in:email,link,platform',
            'work_type' => 'in:full_time,part_time,contract,temporary,internship,remote',
            'experience_level' => 'in:entry,junior,mid,senior,executive',
            'education_level' => 'nullable|in:high_school,diploma,bachelor,master,phd,none',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0',
            'salary_currency' => 'string|max:3',
            'salary_type' => 'nullable|in:hourly,monthly,yearly,project',
            'salary_negotiable' => 'boolean',
            'country' => 'string|max:100',
            'city' => 'string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_name' => 'nullable|string|max:255',
            'is_remote' => 'boolean',
            'is_urgent' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            // Delete old logo
            if ($job->company_logo) {
                Storage::disk('public')->delete($job->company_logo);
            }
            
            $logo = $request->file('company_logo');
            $logoPath = $logo->store('company-logos', 'public');
            $data['company_logo'] = $logoPath;
        }

        $job->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Job updated successfully',
            'data' => $job->load(['category', 'user']),
        ]);
    }

    public function destroy($id)
    {
        $job = Job::where('user_id', auth()->id())->findOrFail($id);

        // Delete company logo
        if ($job->company_logo) {
            Storage::disk('public')->delete($job->company_logo);
        }

        // Update category jobs count
        $job->category->decrement('jobs_count');

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deleted successfully',
        ]);
    }

    public function myJobs(Request $request)
    {
        $jobs = Job::with(['category', 'applications'])
                   ->where('user_id', auth()->id())
                   ->orderBy('created_at', 'desc')
                   ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function apply(Request $request, $jobId)
    {
        $job = Job::findOrFail($jobId);

        // Check if user already applied
        $existingApplication = JobApplication::where('job_id', $jobId)
                                           ->where('user_id', auth()->id())
                                           ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied for this job',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'cover_letter' => 'required|string',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'portfolio_link' => 'nullable|url|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();
        $data['job_id'] = $jobId;
        $data['user_id'] = auth()->id();

        // Handle CV upload
        if ($request->hasFile('cv_file')) {
            $cv = $request->file('cv_file');
            $cvPath = $cv->store('cv-files', 'public');
            $data['cv_file'] = $cvPath;
        }

        $application = JobApplication::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => $application->load(['job', 'user']),
        ], 201);
    }

    public function myApplications(Request $request)
    {
        $applications = JobApplication::with(['job.category', 'job.user'])
                                      ->where('user_id', auth()->id())
                                      ->orderBy('created_at', 'desc')
                                      ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    public function jobApplications(Request $request, $jobId)
    {
        $job = Job::where('user_id', auth()->id())->findOrFail($jobId);

        $applications = $job->applications()
                           ->with(['user', 'jobSeeker'])
                           ->orderBy('created_at', 'desc')
                           ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    public function updateApplicationStatus(Request $request, $applicationId)
    {
        $application = JobApplication::findOrFail($applicationId);
        
        // Verify user owns the job
        if ($application->job->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:viewed,shortlisted,rejected,hired',
            'employer_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $application->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Application status updated successfully',
            'data' => $application->load(['job', 'user', 'jobSeeker']),
        ]);
    }

    public function featuredJobs()
    {
        $jobs = Job::with(['category', 'user'])
                   ->where('is_active', true)
                   ->where('is_featured', true)
                   ->where('featured_until', '>', now())
                   ->orderBy('featured_until', 'desc')
                   ->limit(10)
                   ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function sponsoredJobs()
    {
        $jobs = Job::with(['category', 'user'])
                   ->where('is_active', true)
                   ->where('is_sponsored', true)
                   ->where('sponsored_until', '>', now())
                   ->orderBy('sponsored_until', 'desc')
                   ->limit(10)
                   ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function urgentJobs()
    {
        $jobs = Job::with(['category', 'user'])
                   ->where('is_active', true)
                   ->where('is_urgent', true)
                   ->orderBy('created_at', 'desc')
                   ->limit(10)
                   ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function remoteJobs()
    {
        $jobs = Job::with(['category', 'user'])
                   ->where('is_active', true)
                   ->where('is_remote', true)
                   ->orderBy('created_at', 'desc')
                   ->limit(10)
                   ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function liveActivityFeed()
    {
        $activities = collect();

        // Recent applications
        $recentApplications = JobApplication::with(['job', 'user'])
                                           ->orderBy('created_at', 'desc')
                                           ->limit(5)
                                           ->get();

        foreach ($recentApplications as $application) {
            $activities->push([
                'type' => 'application',
                'message' => "A user from {$application->user->name} applied for {$application->job->title}",
                'timestamp' => $application->created_at,
                'data' => $application,
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
                'message' => "New vacancy added: {$job->title} in {$job->city}",
                'timestamp' => $job->created_at,
                'data' => $job,
            ]);
        }

        // Jobs with high views
        $trendingJobs = Job::with(['category', 'user'])
                          ->where('views_count', '>', 10)
                          ->orderBy('views_count', 'desc')
                          ->limit(3)
                          ->get();

        foreach ($trendingJobs as $job) {
            $activities->push([
                'type' => 'trending',
                'message' => "A job in {$job->city} just got {$job->views_count} views",
                'timestamp' => $job->updated_at,
                'data' => $job,
            ]);
        }

        // Sort by timestamp and limit
        $activities = $activities->sortByDesc('timestamp')->take(10)->values();

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function trendingJobs()
    {
        $jobs = Job::with(['category', 'user'])
                   ->where('is_active', true)
                   ->where('created_at', '>=', now()->subDays(7))
                   ->orderBy('views_count', 'desc')
                   ->orderBy('applications_count', 'desc')
                   ->limit(10)
                   ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs,
        ]);
    }

    public function statistics()
    {
        $user = auth()->user();
        
        $stats = [
            'total_jobs_posted' => Job::where('user_id', $user->id)->count(),
            'active_jobs' => Job::where('user_id', $user->id)->where('is_active', true)->count(),
            'total_applications' => JobApplication::whereHas('job', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            'pending_applications' => JobApplication::whereHas('job', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'pending')->count(),
            'total_views' => Job::where('user_id', $user->id)->sum('views_count'),
            'recent_activity' => JobApplication::whereHas('job', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
