<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobSeeker;
use App\Models\JobAlert;
use App\Models\JobUpsell;
use App\Models\JobPricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JobsDashboardController extends Controller
{
    public function dashboard(): View
    {
        $user = Auth::user();
        
        // Get user's statistics
        $stats = [
            'total_jobs' => Job::where('user_id', $user->id)->count(),
            'active_jobs' => Job::where('user_id', $user->id)->active()->count(),
            'total_applications_received' => Job::where('user_id', $user->id)
                ->withCount('applications')
                ->get()
                ->sum('applications_count'),
            'total_applications_sent' => JobApplication::where('user_id', $user->id)->count(),
            'total_views' => Job::where('user_id', $user->id)->sum('views'),
            'saved_jobs' => $user->jobSaves()->count(),
        ];

        // Get recent jobs posted by user
        $recentJobs = Job::where('user_id', $user->id)
            ->with(['category', 'applications'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent applications sent by user
        $recentApplications = JobApplication::where('user_id', $user->id)
            ->with(['job.category'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get job seeker profile if exists
        $jobSeekerProfile = JobSeeker::where('user_id', $user->id)->first();

        // Get job alerts
        $jobAlerts = JobAlert::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get active upsells
        $activeUpsells = JobUpsell::where('user_id', $user->id)
            ->active()
            ->with(['pricingPlan', 'upsellable'])
            ->get();

        return view('jobs.dashboard', compact(
            'stats',
            'recentJobs',
            'recentApplications',
            'jobSeekerProfile',
            'jobAlerts',
            'activeUpsells'
        ));
    }

    public function myJobs(): View
    {
        $user = Auth::user();
        
        $jobs = Job::where('user_id', $user->id)
            ->with(['category', 'applications'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jobs.my-jobs', compact('jobs'));
    }

    public function createJob(): View
    {
        $categories = \App\Models\JobCategory::active()->orderBy('name')->get();
        $pricingPlans = JobPricingPlan::active()->orderBy('price')->get();
        
        return view('jobs.create', compact('categories', 'pricingPlans'));
    }

    public function editJob($id): View
    {
        $job = Job::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['category'])
            ->firstOrFail();

        $categories = \App\Models\JobCategory::active()->orderBy('name')->get();
        
        return view('jobs.edit', compact('job', 'categories'));
    }

    public function jobApplications(): View
    {
        $user = Auth::user();
        
        // Get applications for user's jobs
        $applications = JobApplication::whereHas('job', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['job.category', 'user'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('jobs.applications', compact('applications'));
    }

    public function myApplications(): View
    {
        $applications = JobApplication::where('user_id', Auth::id())
            ->with(['job.category', 'job.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jobs.my-applications', compact('applications'));
    }

    public function savedJobs(): View
    {
        $savedJobs = Auth::user()->jobSaves()
            ->with(['job.category', 'job.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jobs.saved', compact('savedJobs'));
    }

    public function jobSeekerProfile(): View
    {
        $profile = JobSeeker::where('user_id', Auth::id())->first();
        
        if (!$profile) {
            return view('jobs.seeker.create');
        }

        return view('jobs.seeker.edit', compact('profile'));
    }

    public function jobAlerts(): View
    {
        $alerts = JobAlert::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('jobs.alerts', compact('alerts'));
    }

    public function jobUpsells(): View
    {
        $upsells = JobUpsell::where('user_id', Auth::id())
            ->with(['pricingPlan', 'upsellable'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $pricingPlans = JobPricingPlan::active()->orderBy('price')->get();
        
        return view('jobs.upsells', compact('upsells', 'pricingPlans'));
    }

    public function analytics(): View
    {
        $user = Auth::user();
        
        // Job statistics
        $jobStats = [
            'total_views' => Job::where('user_id', $user->id)->sum('views'),
            'total_applications' => Job::where('user_id', $user->id)
                ->withCount('applications')
                ->get()
                ->sum('applications_count'),
            'total_saves' => Job::where('user_id', $user->id)->sum('saves_count'),
            'views_by_month' => Job::where('user_id', $user->id)
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
            'applications_by_status' => JobApplication::whereHas('job', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get(),
        ];

        // Job seeker statistics
        $seekerStats = [];
        $jobSeekerProfile = JobSeeker::where('user_id', $user->id)->first();
        
        if ($jobSeekerProfile) {
            $seekerStats = [
                'profile_views' => $jobSeekerProfile->views,
                'contact_count' => $jobSeekerProfile->contact_count,
                'applications_sent' => $jobSeekerProfile->applications()->count(),
                'applications_by_status' => $jobSeekerProfile->applications()
                    ->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->get(),
            ];
        }

        return view('jobs.analytics', compact('jobStats', 'seekerStats', 'jobSeekerProfile'));
    }

    public function browse(Request $request): View
    {
        $query = Job::with(['category', 'user'])
                    ->active()
                    ->notExpired();

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('description', 'LIKE', "%{$request->search}%")
                  ->orWhere('company_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('skills_needed', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->location) {
            $query->byLocation($request->location);
        }

        if ($request->work_type) {
            $query->byWorkType($request->work_type);
        }

        if ($request->experience_level) {
            $query->byExperienceLevel($request->experience_level);
        }

        if ($request->boolean('remote_only')) {
            $query->remote();
        }

        // Sort
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
            default:
                $query->orderBy('posted_at', 'desc');
        }

        $jobs = $query->paginate(10);
        $categories = \App\Models\JobCategory::active()->orderBy('name')->get();

        return view('jobs.browse', compact('jobs', 'categories'));
    }

    public function showJob($slug): View
    {
        $job = Job::with(['category', 'user', 'applications'])
                 ->where('slug', $slug)
                 ->active()
                 ->notExpired()
                 ->firstOrFail();

        // Track view
        \App\Models\JobView::trackView($job->id, Auth::id());

        // Check if saved by current user
        $isSaved = false;
        if (Auth::check()) {
            $isSaved = \App\Models\JobSave::isSaved($job->id, Auth::id());
        }

        // Check if applied by current user
        $hasApplied = false;
        if (Auth::check()) {
            $hasApplied = $job->hasAppliedByUser(Auth::id());
        }

        return view('jobs.show', compact('job', 'isSaved', 'hasApplied'));
    }

    public function seekers(Request $request): View
    {
        $query = JobSeeker::with(['user'])
                          ->active();

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('full_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('profession', 'LIKE', "%{$request->search}%")
                  ->orWhere('key_skills', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->profession) {
            $query->byProfession($request->profession);
        }

        if ($request->location) {
            $query->byLocation($request->location);
        }

        if ($request->experience_level) {
            $query->byExperience($request->experience_level);
        }

        if ($request->boolean('remote_available')) {
            $query->remote();
        }

        $seekers = $query->paginate(10);

        return view('jobs.seekers', compact('seekers'));
    }

    public function showSeeker($id): View
    {
        $seeker = JobSeeker::with(['user', 'applications.job.category'])
                          ->active()
                          ->findOrFail($id);

        // Increment views
        $seeker->incrementViews();

        return view('jobs.seeker-show', compact('seeker'));
    }
}
