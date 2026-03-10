<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class JobSeekerController extends Controller
{
    public function index(Request $request)
    {
        $query = JobSeeker::with(['user'])
                         ->where('is_active', true);

        // Search filters
        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%')
                  ->orWhere('bio', 'like', '%' . $request->keyword . '%')
                  ->orWhere('desired_role', 'like', '%' . $request->keyword . '%')
                  ->orWhereHas('user', function ($subQuery) use ($request) {
                      $subQuery->where('first_name', 'like', '%' . $request->keyword . '%')
                               ->orWhere('last_name', 'like', '%' . $request->keyword . '%');
                  });
            });
        }

        if ($request->experience_level) {
            $query->where('experience_level', $request->experience_level);
        }

        if ($request->education_level) {
            $query->where('education_level', $request->education_level);
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        if ($request->has('is_remote_available') && $request->is_remote_available !== null) {
            $query->where('is_remote_available', $request->boolean('is_remote_available'));
        }

        if ($request->has('willing_to_relocate') && $request->willing_to_relocate !== null) {
            $query->where('willing_to_relocate', $request->boolean('willing_to_relocate'));
        }

        if ($request->salary_min) {
            $query->where('salary_expectation_min', '>=', $request->salary_min);
        }

        if ($request->salary_max) {
            $query->where('salary_expectation_max', '<=', $request->salary_max);
        }

        if ($request->skills) {
            $skills = explode(',', $request->skills);
            $query->where(function ($q) use ($skills) {
                foreach ($skills as $skill) {
                    $q->orWhere('key_skills', 'like', '%' . trim($skill) . '%');
                }
            });
        }

        // Sorting
        $sort = $request->sort ?? 'recent';
        switch ($sort) {
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'experience':
                $query->orderBy('years_of_experience', 'desc');
                break;
            case 'views':
                $query->orderBy('views_count', 'desc');
                break;
            case 'contacts':
                $query->orderBy('profile_contacts_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        // Priority ordering for promoted profiles
        $query->orderByRaw('
            CASE 
                WHEN is_sponsored = 1 AND sponsored_until > NOW() THEN 1
                WHEN is_featured = 1 AND featured_until > NOW() THEN 2
                WHEN is_promoted = 1 AND promoted_until > NOW() THEN 3
                ELSE 4
            END
        ');

        $jobSeekers = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $jobSeekers,
        ]);
    }

    public function show($id)
    {
        $jobSeeker = JobSeeker::with(['user', 'applications'])
                            ->where('is_active', true)
                            ->findOrFail($id);

        // Increment views
        $jobSeeker->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $jobSeeker,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:200',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'portfolio_link' => 'nullable|url|max:500',
            'linkedin_url' => 'nullable|url|max:500',
            'github_url' => 'nullable|url|max:500',
            'website_url' => 'nullable|url|max:500',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,executive',
            'years_of_experience' => 'nullable|integer|min:0',
            'education_level' => 'nullable|in:high_school,diploma,bachelor,master,phd,none',
            'key_skills' => 'nullable|string',
            'desired_role' => 'nullable|string',
            'industries_interested' => 'nullable|string',
            'salary_expectation_min' => 'nullable|numeric|min:0',
            'salary_expectation_max' => 'nullable|numeric|min:0',
            'salary_currency' => 'string|max:3',
            'preferred_work_type' => 'nullable|in:full_time,part_time,contract,temporary,internship,remote,any',
            'is_remote_available' => 'boolean',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_name' => 'nullable|string|max:255',
            'willing_to_relocate' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user already has a profile
        $existingProfile = JobSeeker::where('user_id', auth()->id())->first();
        if ($existingProfile) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a job seeker profile',
            ], 422);
        }

        $data = $request->all();
        $data['user_id'] = auth()->id();

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');
            $photoPath = $photo->store('profile-photos', 'public');
            $data['profile_photo'] = $photoPath;
        }

        // Handle CV upload
        if ($request->hasFile('cv_file')) {
            $cv = $request->file('cv_file');
            $cvPath = $cv->store('cv-files', 'public');
            $data['cv_file'] = $cvPath;
        }

        // Set default values
        $data['salary_currency'] = $data['salary_currency'] ?? 'USD';
        $data['is_remote_available'] = $data['is_remote_available'] ?? true;
        $data['willing_to_relocate'] = $data['willing_to_relocate'] ?? false;
        $data['is_active'] = true;

        $jobSeeker = JobSeeker::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Job seeker profile created successfully',
            'data' => $jobSeeker->load(['user']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $jobSeeker = JobSeeker::where('user_id', auth()->id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:200',
            'bio' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'portfolio_link' => 'nullable|url|max:500',
            'linkedin_url' => 'nullable|url|max:500',
            'github_url' => 'nullable|url|max:500',
            'website_url' => 'nullable|url|max:500',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,executive',
            'years_of_experience' => 'nullable|integer|min:0',
            'education_level' => 'nullable|in:high_school,diploma,bachelor,master,phd,none',
            'key_skills' => 'nullable|string',
            'desired_role' => 'nullable|string',
            'industries_interested' => 'nullable|string',
            'salary_expectation_min' => 'nullable|numeric|min:0',
            'salary_expectation_max' => 'nullable|numeric|min:0',
            'salary_currency' => 'string|max:3',
            'preferred_work_type' => 'nullable|in:full_time,part_time,contract,temporary,internship,remote,any',
            'is_remote_available' => 'boolean',
            'country' => 'string|max:100',
            'city' => 'string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_name' => 'nullable|string|max:255',
            'willing_to_relocate' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($jobSeeker->profile_photo) {
                Storage::disk('public')->delete($jobSeeker->profile_photo);
            }
            
            $photo = $request->file('profile_photo');
            $photoPath = $photo->store('profile-photos', 'public');
            $data['profile_photo'] = $photoPath;
        }

        // Handle CV upload
        if ($request->hasFile('cv_file')) {
            // Delete old CV
            if ($jobSeeker->cv_file) {
                Storage::disk('public')->delete($jobSeeker->cv_file);
            }
            
            $cv = $request->file('cv_file');
            $cvPath = $cv->store('cv-files', 'public');
            $data['cv_file'] = $cvPath;
        }

        $jobSeeker->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $jobSeeker->load(['user']),
        ]);
    }

    public function destroy($id)
    {
        $jobSeeker = JobSeeker::where('user_id', auth()->id())->findOrFail($id);

        // Delete profile photo
        if ($jobSeeker->profile_photo) {
            Storage::disk('public')->delete($jobSeeker->profile_photo);
        }

        // Delete CV file
        if ($jobSeeker->cv_file) {
            Storage::disk('public')->delete($jobSeeker->cv_file);
        }

        $jobSeeker->delete();

        return response()->json([
            'success' => true,
            'message' => 'Profile deleted successfully',
        ]);
    }

    public function myProfile()
    {
        $jobSeeker = JobSeeker::with(['user', 'applications'])
                            ->where('user_id', auth()->id())
                            ->first();

        if (!$jobSeeker) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $jobSeeker,
        ]);
    }

    public function featuredProfiles()
    {
        $profiles = JobSeeker::with(['user'])
                           ->where('is_active', true)
                           ->where('is_featured', true)
                           ->where('featured_until', '>', now())
                           ->orderBy('featured_until', 'desc')
                           ->limit(10)
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $profiles,
        ]);
    }

    public function sponsoredProfiles()
    {
        $profiles = JobSeeker::with(['user'])
                           ->where('is_active', true)
                           ->where('is_sponsored', true)
                           ->where('sponsored_until', '>', now())
                           ->orderBy('sponsored_until', 'desc')
                           ->limit(10)
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $profiles,
        ]);
    }

    public function contactProfile(Request $request, $id)
    {
        $jobSeeker = JobSeeker::where('is_active', true)->findOrFail($id);

        // Increment contact count
        $jobSeeker->incrementProfileContacts();

        return response()->json([
            'success' => true,
            'message' => 'Profile contact recorded successfully',
            'data' => [
                'contact_info' => [
                    'email' => $jobSeeker->user->email,
                    'phone' => $jobSeeker->user->mobile_number,
                    'linkedin' => $jobSeeker->linkedin_url,
                    'portfolio' => $jobSeeker->portfolio_link,
                ]
            ],
        ]);
    }

    public function statistics()
    {
        $user = auth()->user();
        
        $profile = JobSeeker::where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found',
            ], 404);
        }

        $stats = [
            'total_views' => $profile->views_count,
            'total_contacts' => $profile->profile_contacts_count,
            'total_applications' => $profile->applications()->count(),
            'recent_views' => $profile->views_count, // This could be enhanced with tracking
            'recent_contacts' => $profile->profile_contacts_count, // This could be enhanced with tracking
            'profile_completion' => $this->calculateProfileCompletion($profile),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    private function calculateProfileCompletion($profile)
    {
        $fields = [
            'title' => $profile->title ? 10 : 0,
            'bio' => $profile->bio ? 15 : 0,
            'profile_photo' => $profile->profile_photo ? 10 : 0,
            'cv_file' => $profile->cv_file ? 15 : 0,
            'experience_level' => $profile->experience_level ? 10 : 0,
            'years_of_experience' => $profile->years_of_experience !== null ? 5 : 0,
            'education_level' => $profile->education_level ? 10 : 0,
            'key_skills' => $profile->key_skills ? 10 : 0,
            'desired_role' => $profile->desired_role ? 5 : 0,
            'portfolio_link' => $profile->portfolio_link ? 5 : 0,
            'linkedin_url' => $profile->linkedin_url ? 5 : 0,
        ];

        return array_sum($fields);
    }
}
