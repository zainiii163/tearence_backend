<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use App\Models\CustomerStore;
use App\Models\StaffManagement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class StaffManagementController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get staff members for a business or store
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;
            $entityType = $request->get('entity_type'); // 'business' or 'store'
            $entityId = $request->get('entity_id');

            if (!$entityType || !$entityId) {
                return $this->errorResponse('Entity type and entity ID are required', Response::HTTP_BAD_REQUEST);
            }

            // Verify ownership
            if ($entityType === 'business') {
                $entity = CustomerBusiness::where('id', $entityId)
                    ->where('customer_id', $customer_id)
                    ->first();
            } else {
                $entity = CustomerStore::where('store_id', $entityId)
                    ->where('customer_id', $customer_id)
                    ->first();
            }

            if (!$entity) {
                return $this->errorResponse('Entity not found or access denied', Response::HTTP_NOT_FOUND);
            }

            $staffMembers = StaffManagement::where('customer_id', $customer_id)
                ->where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->with('staffMember')
                ->get();

            return $this->successResponse($staffMembers, 'Staff members retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add a staff member
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'staff_customer_id' => 'required|integer|exists:customer,customer_id',
                'entity_type' => 'required|in:business,store',
                'entity_id' => 'required|integer',
                'role' => 'required|in:admin,editor,viewer',
                'can_post_ads' => 'boolean',
                'can_edit_ads' => 'boolean',
                'can_delete_ads' => 'boolean',
                'can_manage_payments' => 'boolean',
                'can_view_analytics' => 'boolean',
                'can_manage_staff' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;

            // Verify ownership
            if ($request->entity_type === 'business') {
                $entity = CustomerBusiness::where('id', $request->entity_id)
                    ->where('customer_id', $customer_id)
                    ->first();
            } else {
                $entity = CustomerStore::where('store_id', $request->entity_id)
                    ->where('customer_id', $customer_id)
                    ->first();
            }

            if (!$entity) {
                return $this->errorResponse('Entity not found or access denied', Response::HTTP_NOT_FOUND);
            }

            // Check if staff member already exists
            $existing = StaffManagement::where('customer_id', $customer_id)
                ->where('staff_customer_id', $request->staff_customer_id)
                ->where('entity_type', $request->entity_type)
                ->where('entity_id', $request->entity_id)
                ->first();

            if ($existing) {
                return $this->errorResponse('Staff member already added', Response::HTTP_CONFLICT);
            }

            // Cannot add yourself as staff
            if ($request->staff_customer_id == $customer_id) {
                return $this->errorResponse('Cannot add yourself as staff member', Response::HTTP_BAD_REQUEST);
            }

            $staff = StaffManagement::create([
                'customer_id' => $customer_id,
                'staff_customer_id' => $request->staff_customer_id,
                'entity_type' => $request->entity_type,
                'entity_id' => $request->entity_id,
                'role' => $request->role,
                'can_post_ads' => $request->get('can_post_ads', false),
                'can_edit_ads' => $request->get('can_edit_ads', false),
                'can_delete_ads' => $request->get('can_delete_ads', false),
                'can_manage_payments' => $request->get('can_manage_payments', false),
                'can_view_analytics' => $request->get('can_view_analytics', false),
                'can_manage_staff' => $request->get('can_manage_staff', false),
                'invited_at' => now(),
            ]);

            return $this->successResponse($staff->load('staffMember'), 'Staff member added successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update staff member permissions
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'role' => 'sometimes|in:admin,editor,viewer',
                'can_post_ads' => 'boolean',
                'can_edit_ads' => 'boolean',
                'can_delete_ads' => 'boolean',
                'can_manage_payments' => 'boolean',
                'can_view_analytics' => 'boolean',
                'can_manage_staff' => 'boolean',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;
            $staff = StaffManagement::where('staff_id', $id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            $staff->update($request->only([
                'role',
                'can_post_ads',
                'can_edit_ads',
                'can_delete_ads',
                'can_manage_payments',
                'can_view_analytics',
                'can_manage_staff',
                'is_active',
            ]));

            return $this->successResponse($staff->load('staffMember'), 'Staff member updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove staff member
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;
            $staff = StaffManagement::where('staff_id', $id)
                ->where('customer_id', $customer_id)
                ->firstOrFail();

            $staff->delete();

            return $this->successResponse(null, 'Staff member removed successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get staff memberships for current user (where user is a staff member)
     */
    public function myStaffMemberships(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;

            $memberships = StaffManagement::where('staff_customer_id', $customer_id)
                ->where('is_active', true)
                ->with(['owner', 'business', 'store'])
                ->get();

            return $this->successResponse($memberships, 'Staff memberships retrieved successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search for users by email or phone
     */
    public function searchUsers(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'search' => 'required|string|min:3',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }

            $search = $request->search;
            $users = Customer::searchByEmailOrPhone($search);

            return $this->successResponse($users, 'Users found successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check if user exists and invite if not registered
     */
    public function checkAndInviteUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required_without:phone|email',
                'phone' => 'required_without:email|string',
                'entity_type' => 'required|in:business,store',
                'entity_id' => 'required|integer',
                'role' => 'required|in:admin,editor,viewer',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;

            // Verify ownership of entity
            if ($request->entity_type === 'business') {
                $entity = CustomerBusiness::where('id', $request->entity_id)
                    ->where('customer_id', $customer_id)
                    ->first();
            } else {
                $entity = CustomerStore::where('store_id', $request->entity_id)
                    ->where('customer_id', $customer_id)
                    ->first();
            }

            if (!$entity) {
                return $this->errorResponse('Entity not found or access denied', Response::HTTP_NOT_FOUND);
            }

            // Check if user exists
            $existingUser = Customer::findByEmailOrPhone($request->email, $request->phone);

            if ($existingUser) {
                // User exists, return their info for adding as staff
                return $this->successResponse([
                    'user_exists' => true,
                    'user' => [
                        'customer_id' => $existingUser->customer_id,
                        'name' => $existingUser->name,
                        'email' => $existingUser->email,
                        'phone_number' => $existingUser->phone_number,
                    ],
                    'message' => 'User is already registered. You can add them as staff now.'
                ], 'User found', Response::HTTP_OK);
            } else {
                // User doesn't exist, return invitation info
                $contactInfo = $request->email ?: $request->phone;
                
                return $this->successResponse([
                    'user_exists' => false,
                    'contact_info' => $contactInfo,
                    'message' => 'User is not registered. Please ask them to sign up first using this email/phone number.',
                    'signup_url' => url('/register') // Adjust to your frontend signup URL
                ], 'User not found - invitation needed', Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Add staff member with email/phone validation
     */
    public function addStaffMember(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required_without:customer_id|email',
                'phone' => 'required_without:customer_id|string',
                'customer_id' => 'required_without:email,phone|integer|exists:customer,customer_id',
                'entity_type' => 'required|in:business,store',
                'entity_id' => 'required|integer',
                'role' => 'required|in:admin,editor,viewer',
                'can_post_ads' => 'boolean',
                'can_edit_ads' => 'boolean',
                'can_delete_ads' => 'boolean',
                'can_manage_payments' => 'boolean',
                'can_view_analytics' => 'boolean',
                'can_manage_staff' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
            }

            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
            }
            $customer_id = $user->customer_id;

            // Verify ownership of entity
            if ($request->entity_type === 'business') {
                $entity = CustomerBusiness::where('id', $request->entity_id)
                    ->where('customer_id', $customer_id)
                    ->first();
            } else {
                $entity = CustomerStore::where('store_id', $request->entity_id)
                    ->where('customer_id', $customer_id)
                    ->first();
            }

            if (!$entity) {
                return $this->errorResponse('Entity not found or access denied', Response::HTTP_NOT_FOUND);
            }

            // Find user by customer_id, email, or phone
            $staffCustomer = null;
            if ($request->customer_id) {
                $staffCustomer = Customer::find($request->customer_id);
            } else {
                $staffCustomer = Customer::findByEmailOrPhone($request->email, $request->phone);
            }

            if (!$staffCustomer) {
                return $this->errorResponse('User not found. Please ask them to register first.', Response::HTTP_NOT_FOUND);
            }

            // Cannot add yourself as staff
            if ($staffCustomer->customer_id == $customer_id) {
                return $this->errorResponse('Cannot add yourself as staff member', Response::HTTP_BAD_REQUEST);
            }

            // Check if staff member already exists
            $existing = StaffManagement::where('customer_id', $customer_id)
                ->where('staff_customer_id', $staffCustomer->customer_id)
                ->where('entity_type', $request->entity_type)
                ->where('entity_id', $request->entity_id)
                ->first();

            if ($existing) {
                return $this->errorResponse('Staff member already added', Response::HTTP_CONFLICT);
            }

            $staff = StaffManagement::create([
                'customer_id' => $customer_id,
                'staff_customer_id' => $staffCustomer->customer_id,
                'entity_type' => $request->entity_type,
                'entity_id' => $request->entity_id,
                'role' => $request->role,
                'can_post_ads' => $request->get('can_post_ads', false),
                'can_edit_ads' => $request->get('can_edit_ads', false),
                'can_delete_ads' => $request->get('can_delete_ads', false),
                'can_manage_payments' => $request->get('can_manage_payments', false),
                'can_view_analytics' => $request->get('can_view_analytics', false),
                'can_manage_staff' => $request->get('can_manage_staff', false),
                'invited_at' => now(),
            ]);

            return $this->successResponse($staff->load('staffMember'), 'Staff member added successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
