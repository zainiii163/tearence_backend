<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\JobAlert;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JobAlertController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of job alerts for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            
            // For User model, get user_id and find corresponding customer
            $user_id = $user->user_id;
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customer_id = $customer ? $customer->customer_id : $user_id;

            $query = JobAlert::where('customer_id', $customer_id)
                ->with(['location', 'category']);

            // Filter by active status
            if ($request->has('is_active')) {
                $isActive = filter_var($request->get('is_active'), FILTER_VALIDATE_BOOLEAN);
                $query->where('is_active', $isActive);
            }

            // Sort by created date
            $query->orderBy('created_at', 'desc');

            $alerts = $query->get();

            // Add matching jobs count for each alert
            $alerts->map(function($alert) {
                $alert->matching_jobs_count = $alert->findMatchingJobs(1)->count();
                return $alert;
            });

            return $this->successResponse($alerts, 'Job alerts retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created job alert.
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        
        // For User model, get user_id and find corresponding customer
        $user_id = $user->user_id;
        $customer = \App\Models\Customer::where('email', $user->email)->first();
        $customer_id = $customer ? $customer->customer_id : $user_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'location_id' => 'nullable|integer|exists:location,location_id',
            'category_id' => 'nullable|integer|exists:category,category_id',
            'job_type' => 'nullable|array',
            'job_type.*' => 'in:full-time,part-time,contract,freelance,internship',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'frequency' => 'nullable|in:instant,daily,weekly',
            'is_active' => 'nullable|boolean',
            'notification_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $alert = new JobAlert();
            $alert->customer_id = $customer_id;
            $alert->name = $request->name;
            $alert->keywords = $request->keywords ?? [];
            $alert->location_id = $request->location_id;
            $alert->category_id = $request->category_id;
            $alert->job_type = $request->job_type ?? [];
            $alert->salary_min = $request->salary_min;
            $alert->salary_max = $request->salary_max;
            $alert->frequency = $request->frequency ?? 'daily';
            $alert->is_active = $request->has('is_active') ? $request->is_active : true;
            $alert->notification_email = $request->notification_email;
            $alert->save();

            DB::commit();

            // Get matching jobs count
            $matchingJobs = $alert->findMatchingJobs(1);
            $alert->matching_jobs_count = $matchingJobs->count();

            return $this->successResponse($alert->load(['location', 'category']), 'Job alert created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified job alert.
     */
    public function show($id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            
            // For User model, get user_id and find corresponding customer
            $user_id = $user->user_id;
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customer_id = $customer ? $customer->customer_id : $user_id;

            $alert = JobAlert::where('job_alert_id', $id)
                ->where('customer_id', $customer_id)
                ->with(['location', 'category', 'customer'])
                ->firstOrFail();

            // Get matching jobs
            $matchingJobs = $alert->findMatchingJobs(50);
            $alert->matching_jobs = $matchingJobs;
            $alert->matching_jobs_count = $matchingJobs->count();

            return $this->successResponse($alert, 'Job alert retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified job alert.
     */
    public function update(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        
        // For User model, get user_id and find corresponding customer
        $user_id = $user->user_id;
        $customer = \App\Models\Customer::where('email', $user->email)->first();
        $customer_id = $customer ? $customer->customer_id : $user_id;

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'location_id' => 'nullable|integer|exists:location,location_id',
            'category_id' => 'nullable|integer|exists:category,category_id',
            'job_type' => 'nullable|array',
            'job_type.*' => 'in:full-time,part-time,contract,freelance,internship',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'frequency' => 'nullable|in:instant,daily,weekly',
            'is_active' => 'nullable|boolean',
            'notification_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $alert = JobAlert::where('job_alert_id', $id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            $alert->fill($request->only([
                'name',
                'keywords',
                'location_id',
                'category_id',
                'job_type',
                'salary_min',
                'salary_max',
                'frequency',
                'is_active',
                'notification_email',
            ]));

            $alert->save();

            DB::commit();

            // Get matching jobs count
            $matchingJobs = $alert->findMatchingJobs(1);
            $alert->matching_jobs_count = $matchingJobs->count();

            return $this->successResponse($alert->load(['location', 'category']), 'Job alert updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified job alert.
     */
    public function destroy($id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            
            // For User model, get user_id and find corresponding customer
            $user_id = $user->user_id;
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customer_id = $customer ? $customer->customer_id : $user_id;

            $alert = JobAlert::where('job_alert_id', $id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            $alert->delete();

            return $this->successResponse(null, 'Job alert deleted successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get matching jobs for a specific alert.
     */
    public function getMatchingJobs($id, Request $request)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            
            // For User model, get user_id and find corresponding customer
            $user_id = $user->user_id;
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customer_id = $customer ? $customer->customer_id : $user_id;

            $alert = JobAlert::where('job_alert_id', $id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            $limit = (int)$request->get('limit', 50);
            $limit = max(1, min(100, $limit)); // Limit between 1 and 100

            $matchingJobs = $alert->findMatchingJobs($limit);

            // Pagination info
            $total = $alert->findMatchingJobs(1000)->count(); // Get total count

            return $this->successResponse([
                'alert' => $alert->load(['location', 'category']),
                'matching_jobs' => $matchingJobs,
                'total_matching' => $total,
                'returned_count' => $matchingJobs->count(),
            ], 'Matching jobs retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Toggle alert active status.
     */
    public function toggleActive($id)
    {
        try {
            $user = auth('api')->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            
            // For User model, get user_id and find corresponding customer
            $user_id = $user->user_id;
            $customer = \App\Models\Customer::where('email', $user->email)->first();
            $customer_id = $customer ? $customer->customer_id : $user_id;

            $alert = JobAlert::where('job_alert_id', $id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            $alert->is_active = !$alert->is_active;
            $alert->save();

            return $this->successResponse($alert->load(['location', 'category']), 
                'Job alert ' . ($alert->is_active ? 'activated' : 'deactivated') . ' successfully', 
                Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Get all active alerts that need notification (for cron job).
     * This endpoint is for admin/internal use to process notifications.
     */
    public function getAlertsForNotification(Request $request)
    {
        try {
            // This could be protected with admin middleware in production
            $frequency = $request->get('frequency', 'daily');
            
            $alerts = JobAlert::where('is_active', true)
                ->where('frequency', $frequency)
                ->where(function($q) use ($frequency) {
                    $q->whereNull('last_notified_at');
                    
                    if ($frequency === 'daily') {
                        $q->orWhere('last_notified_at', '<', now()->subDay());
                    } elseif ($frequency === 'weekly') {
                        $q->orWhere('last_notified_at', '<', now()->subWeek());
                    }
                })
                ->with(['customer', 'location', 'category'])
                ->get();

            return $this->successResponse($alerts, 'Alerts ready for notification retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mark alert as notified (for cron job after sending notifications).
     */
    public function markAsNotified($id, Request $request)
    {
        try {
            $alert = JobAlert::findOrFail($id);
            
            $alert->last_notified_at = now();
            $alert->last_matched_count = $request->get('matched_count', 0);
            $alert->save();

            return $this->successResponse($alert, 'Alert marked as notified successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }
}

