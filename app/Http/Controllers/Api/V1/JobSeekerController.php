<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
                $q->where('full_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('profession', 'LIKE', "%{$request->search}%")
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

        // Promoted seekers first
        $query->orderByRaw("CASE WHEN promotion_type != 'basic' AND (promotion_expires_at IS NULL OR promotion_expires_at > NOW()) THEN 0 ELSE 1 END")
              ->orderBy('created_at', 'desc');

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
            'full_name' => 'required|string|max:255',
            'profession' => 'required|string|max:255',
            'bio' => 'nullable|string|min:50',
            'profile_photo' => 'nullable|string',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'years_of_experience' => 'required|string|in:0-1,1-3,3-5,5-10,10+',
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
            'terms_accepted' => 'accepted',
            'accurate_info' => 'accepted',
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

        $seeker = JobSeeker::create(array_merge($request->all(), [
            'user_id' => Auth::id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Job seeker profile created successfully',
            'data' => [
                'id' => $seeker->id,
                'full_name' => $seeker->full_name,
                'profession' => $seeker->profession,
                'status' => $seeker->status,
                'created_at' => $seeker->created_at,
            ],
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
            'bio' => 'nullable|string|min:50',
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
}
