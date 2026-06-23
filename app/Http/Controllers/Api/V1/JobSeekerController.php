<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class JobSeekerController extends Controller
{
    // Public endpoints

    /**
     * Get all job seekers (public)
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobSeeker::with(['user'])
                          ->active();

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('desired_role', 'LIKE', "%{$request->search}%")
                  ->orWhere('key_skills', 'LIKE', "%{$request->search}%")
                  ->orWhere('bio', 'LIKE', "%{$request->search}%");
            });
        }

        // Profession filter
        if ($request->profession) {
            $query->byProfession($request->profession);
        }

        // Location filter
        if ($request->location) {
            $query->byLocation($request->location);
        }

        // Experience level filter
        if ($request->experience_level) {
            $query->byExperience($request->experience_level);
        }

        // Skills filter
        if ($request->skills) {
            $skills = is_array($request->skills) ? $request->skills : explode(',', $request->skills);
            $query->where(function ($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhere('key_skills', 'LIKE', "%{$skill}%");
                }
            });
        }

        // Remote availability filter
        if ($request->boolean('remote_available')) {
            $query->remote();
        }

        // Order by created_at
        $query->orderBy('created_at', 'desc');

        $perPage = min($request->per_page ?? 20, 100);
        $seekers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $seekers->items(),
            'pagination' => [
                'current_page' => $seekers->currentPage(),
                'per_page' => $seekers->perPage(),
                'total' => $seekers->total(),
                'total_pages' => $seekers->lastPage(),
                'has_next' => $seekers->hasMorePages(),
                'has_prev' => $seekers->currentPage() > 1,
            ],
        ]);
    }

    /**
     * Get single job seeker details (public)
     */
    public function show($id): JsonResponse
    {
        $seeker = JobSeeker::with(['user'])
                          ->active()
                          ->find($id);

        if (!$seeker) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Job seeker profile not found',
                ],
            ], 404);
        }

        // Increment views
        $seeker->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $seeker,
        ]);
    }

    /**
     * Get job seeker statistics (public)
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_seekers' => JobSeeker::active()->count(),
            'remote_available' => JobSeeker::active()->remote()->count(),
            'popular_professions' => JobSeeker::active()
                                    ->selectRaw('profession, COUNT(*) as count')
                                    ->groupBy('profession')
                                    ->orderBy('count', 'desc')
                                    ->limit(5)
                                    ->get(),
            'experience_distribution' => JobSeeker::active()
                                        ->selectRaw('years_of_experience, COUNT(*) as count')
                                        ->groupBy('years_of_experience')
                                        ->orderBy('count', 'desc')
                                        ->get(),
            'top_locations' => JobSeeker::active()
                                ->selectRaw('country, city, COUNT(*) as count')
                                ->groupBy('country', 'city')
                                ->orderBy('count', 'desc')
                                ->limit(5)
                                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    // Protected endpoints (require authentication)

    /**
     * Create job seeker profile
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|string|max:255',
            'cv_file' => 'nullable|string|max:255',
            'portfolio_link' => 'nullable|string|max:500',
            'linkedin_url' => 'nullable|string|max:500',
            'github_url' => 'nullable|string|max:500',
            'website_url' => 'nullable|string|max:500',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,executive',
            'years_of_experience' => 'nullable|integer',
            'education_level' => 'nullable|in:high_school,diploma,bachelor,master,phd,none',
            'key_skills' => 'nullable|string',
            'desired_role' => 'nullable|string',
            'industries_interested' => 'nullable|string',
            'salary_expectation_min' => 'nullable|numeric',
            'salary_expectation_max' => 'nullable|numeric',
            'salary_currency' => 'nullable|string|size:3',
            'preferred_work_type' => 'nullable|in:full_time,part_time,contract,temporary,internship,remote,any',
            'is_remote_available' => 'boolean',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            'willing_to_relocate' => 'boolean',
        ]);

        // Check if user already has a profile
        $existingProfile = JobSeeker::where('user_id', Auth::id())->first();
        if ($existingProfile) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DUPLICATE_PROFILE',
                    'message' => 'You already have a job seeker profile',
                ],
            ], 422);
        }

        $seeker = JobSeeker::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'bio' => $request->bio,
            'profile_photo' => $request->profile_photo,
            'cv_file' => $request->cv_file,
            'portfolio_link' => $request->portfolio_link,
            'linkedin_url' => $request->linkedin_url,
            'github_url' => $request->github_url,
            'website_url' => $request->website_url,
            'experience_level' => $request->experience_level,
            'years_of_experience' => $request->years_of_experience,
            'education_level' => $request->education_level,
            'key_skills' => $request->key_skills,
            'desired_role' => $request->desired_role,
            'industries_interested' => $request->industries_interested,
            'salary_expectation_min' => $request->salary_expectation_min,
            'salary_expectation_max' => $request->salary_expectation_max,
            'salary_currency' => $request->salary_currency ?? 'USD',
            'preferred_work_type' => $request->preferred_work_type,
            'is_remote_available' => $request->boolean('is_remote_available'),
            'country' => $request->country,
            'city' => $request->city,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_name' => $request->location_name,
            'willing_to_relocate' => $request->boolean('willing_to_relocate'),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Job seeker profile created successfully',
            'data' => $seeker->load(['user']),
        ], 201);
    }

    /**
     * Update job seeker profile
     */
    public function update(Request $request, $id): JsonResponse
    {
        $seeker = JobSeeker::where('id', $id)
                          ->where('user_id', Auth::id())
                          ->first();

        if (!$seeker) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Profile not found or access denied',
                ],
            ], 404);
        }

        $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'profession' => 'sometimes|required|string|max:255',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|string',
            'country' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
            'state' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'years_of_experience' => 'sometimes|required|string|in:0-1,1-3,3-5,5-10,10+',
            'key_skills' => 'nullable|string',
            'education_level' => 'nullable|string|in:high_school,associate,bachelor,master,doctorate',
            'education_details' => 'nullable|string',
            'experience_summary' => 'nullable|string',
            'desired_role' => 'nullable|string|max:255',
            'salary_expectation' => 'nullable|string',
            'work_type_preference' => 'nullable|string|in:Full-time,Part-time,Contract,Freelance',
            'remote_availability' => 'boolean',
            'preferred_locations' => 'nullable|array',
            'preferred_industries' => 'nullable|array',
            'portfolio_link' => 'nullable|url',
            'linkedin_link' => 'nullable|url',
            'github_link' => 'nullable|url',
            'cv_file' => 'nullable|string',
            'additional_links' => 'nullable|array',
        ]);

        $seeker->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $seeker,
        ]);
    }

    /**
     * Delete job seeker profile
     */
    public function destroy($id): JsonResponse
    {
        $seeker = JobSeeker::where('id', $id)
                          ->where('user_id', Auth::id())
                          ->first();

        if (!$seeker) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Profile not found or access denied',
                ],
            ], 404);
        }

        $seeker->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile deleted successfully',
        ]);
    }

    /**
     * Get my job seeker profile
     */
    public function myProfile(): JsonResponse
    {
        $seeker = JobSeeker::with(['applications.job.category'])
                          ->where('user_id', Auth::id())
                          ->first();

        if (!$seeker) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Profile not found',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $seeker,
        ]);
    }

    /**
     * Get my applications (as job seeker)
     */
    public function myApplications(Request $request): JsonResponse
    {
        $seeker = JobSeeker::where('user_id', Auth::id())->first();
        
        if (!$seeker) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Profile not found',
                ],
            ], 404);
        }

        $applications = $seeker->applications()
                              ->with(['job.category', 'job.user'])
                              ->orderBy('created_at', 'desc')
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
     * Get profile statistics
     */
    public function myStatistics(): JsonResponse
    {
        $seeker = JobSeeker::where('user_id', Auth::id())->first();
        
        if (!$seeker) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'Profile not found',
                ],
            ], 404);
        }

        $stats = [
            'profile_views' => $seeker->views,
            'contact_count' => $seeker->contact_count,
            'total_applications' => $seeker->applications()->count(),
            'pending_applications' => $seeker->applications()->where('status', 'submitted')->count(),
            'viewed_applications' => $seeker->applications()->where('status', 'viewed')->count(),
            'shortlisted_applications' => $seeker->applications()->where('status', 'shortlisted')->count(),
            'rejected_applications' => $seeker->applications()->where('status', 'rejected')->count(),
            'hired_applications' => $seeker->applications()->where('status', 'hired')->count(),
            'recent_applications' => $seeker->applications()
                                         ->with('job.category')
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
     * Contact job seeker profile
     */
    public function contactProfile(Request $request, $id): JsonResponse
    {
        $seeker = JobSeeker::with(['user'])
                          ->where('is_active', true)
                          ->findOrFail($id);

        // Increment contact count if column exists
        if (Schema::hasColumn('job_seekers', 'profile_contacts_count')) {
            $seeker->increment('profile_contacts_count');
        }

        $contactInfo = [
            'linkedin' => $seeker->linkedin_url,
            'portfolio' => $seeker->portfolio_link,
            'github' => $seeker->github_url,
            'website' => $seeker->website_url,
        ];

        // Add user contact info if user exists
        if ($seeker->user) {
            $contactInfo['email'] = $seeker->user->email;
            $contactInfo['phone'] = $seeker->user->mobile_number ?? null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile contact recorded successfully',
            'data' => [
                'seeker_id' => $seeker->id,
                'seeker_name' => $seeker->user->name ?? 'Job Seeker',
                'contact_info' => $contactInfo,
            ],
        ]);
    }
}
