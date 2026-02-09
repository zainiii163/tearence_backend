<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\CandidateProfile;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CandidateProfileController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
            ]
        ]);
    }

    /**
     * Display a listing of candidate profiles.
     */
    public function index(Request $request)
    {
        try {
            $query = CandidateProfile::with(['customer', 'location']);

            // Filter by visibility (only show public profiles for non-authenticated users)
            if (!auth()->check()) {
                $query->where('visibility', 'public');
            }

            // Filter by featured
            if ($request->has('featured') && $request->featured == '1') {
                $query->where('is_featured', true)
                      ->where(function($q) {
                          $q->whereNull('featured_expires_at')
                            ->orWhere('featured_expires_at', '>', now());
                      });
            }

            // Filter by location
            if ($request->has('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            // Search by skills
            if ($request->has('skills')) {
                $skills = is_array($request->skills) ? $request->skills : explode(',', $request->skills);
                $query->where(function($q) use ($skills) {
                    foreach ($skills as $skill) {
                        $q->orWhereJsonContains('skills', trim($skill));
                    }
                });
            }

            // Search by headline or summary
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('headline', 'like', "%{$search}%")
                      ->orWhere('summary', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Featured profiles first
            if ($sortBy === 'featured') {
                $query->orderByRaw('CASE WHEN is_featured = 1 AND (featured_expires_at IS NULL OR featured_expires_at > NOW()) THEN 0 ELSE 1 END')
                      ->orderBy('created_at', 'desc');
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $profiles = $query->paginate($perPage);

            return $this->successResponse($profiles, 'Candidate profiles retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified candidate profile.
     */
    public function show($id)
    {
        try {
            $profile = CandidateProfile::with(['customer', 'location', 'upsells'])
                ->findOrFail($id);

            // Check visibility
            $user = auth()->user();
            if ($profile->visibility === 'private' && (!$user || $user->customer_id !== $profile->customer_id)) {
                return $this->errorResponse('Profile is private', Response::HTTP_FORBIDDEN);
            }

            return $this->successResponse($profile, 'Candidate profile retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created candidate profile.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        $customer_id = $user->customer_id;

        $validator = Validator::make($request->all(), [
            'headline' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'cv_url' => 'nullable|url|max:255',
            'location_id' => 'nullable|integer|exists:location,location_id',
            'visibility' => 'nullable|in:public,private',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            // Check if profile already exists
            $existingProfile = CandidateProfile::where('customer_id', $customer_id)->first();
            
            if ($existingProfile) {
                return $this->errorResponse('Profile already exists. Use update endpoint.', Response::HTTP_BAD_REQUEST);
            }

            $profile = new CandidateProfile();
            $profile->customer_id = $customer_id;
            $profile->headline = $request->headline;
            $profile->summary = $request->summary;
            $profile->skills = $request->skills ?? [];
            $profile->cv_url = $request->cv_url;
            $profile->location_id = $request->location_id;
            $profile->visibility = $request->visibility ?? 'public';
            $profile->save();

            DB::commit();
            return $this->successResponse($profile->load(['customer', 'location']), 'Candidate profile created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified candidate profile.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        $customer_id = $user->customer_id;

        $validator = Validator::make($request->all(), [
            'headline' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'cv_url' => 'nullable|url|max:255',
            'location_id' => 'nullable|integer|exists:location,location_id',
            'visibility' => 'nullable|in:public,private',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $profile = CandidateProfile::where('candidate_profile_id', $id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            $profile->fill($request->only([
                'headline',
                'summary',
                'skills',
                'cv_url',
                'location_id',
                'visibility',
            ]));
            $profile->save();

            DB::commit();
            return $this->successResponse($profile->load(['customer', 'location']), 'Candidate profile updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified candidate profile.
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;

            $profile = CandidateProfile::where('candidate_profile_id', $id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            $profile->delete();

            return $this->successResponse(null, 'Candidate profile deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get current user's candidate profile.
     */
    public function myProfile(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;
            $profile = CandidateProfile::with(['customer', 'location', 'upsells'])
                ->where('customer_id', $customer_id)
                ->first();

            if (!$profile) {
                return $this->errorResponse('Profile not found', Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse($profile, 'Profile retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

