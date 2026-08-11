<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use App\Models\StaffManagement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BusinessController extends APIController
{
    protected $folder;
    protected $fileUpload;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
                'getBySlug',
                'detail',
            ]
        ]);

        $this->folder = 'business';
        $this->fileUpload = new FileUploadHelper();
    }

    /**
     * @OA\Get(
     *      path="/v1/business",
     *      tags={"Business"},
     *      summary="List business",
     *      description="Get list business",
     *      @OA\Parameter(
     *          name="id",
     *          description="Business ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="customer_id",
     *          description="Customer ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="skip",
     *          description="Skip",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          description="Limit",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          description="Sort by",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort_type",
     *          description="Sort type",
     *          in="query",
     *          @OA\Schema(
     *              default="asc",
     *              type="string",
     *              enum={"asc","desc"},
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function index(Request $request)
    {
        $query = CustomerBusiness::query()->where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        });
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($store_id = $request->get('store_id')) {
            $query = $query->where('store_id', $store_id);
        }

        if ($customer_id = $request->get('customer_id')) {
            $query = $query->where('customer_id', $customer_id);
        }

        if ($sort = $request->get('sort')) {
            $query = $query->orderBy($sort, $request->get('sort_type') ? $request->get('sort_type') : 'asc');
        } else {
            $query = $query->orderBy('id');
        }

        if ($skip == "") {
            $query = $query->get();
            $total = $query->count();
        } else {
            $perPage = ($skip == "") ? $query->count() : (
                $request->has('limit') ? $limit : 10
            );
            $total = $query->count();
            $query = $query->skip($skip)->take($perPage)->get();
        }

        // Convert business_logo to full URL for each business
        $query->transform(function ($business) {
            if ($business->business_logo) {
                $business->business_logo = $this->fileUpload->getFile($business->business_logo, $this->folder);
            }
            return $business;
        });

        $result = [
            'items' => $query,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     * path="/v1/business",
     *   tags={"Business"},
     *   summary="Create customer business",
     *   description="Create customer business",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="business_name", type="string", format="string"),
     *            @OA\Property(property="business_phone_number", type="string", format="string"),
     *            @OA\Property(property="business_address", type="string", format="string"),
     *            @OA\Property(property="business_email", type="string", format="string"),
     *            @OA\Property(property="business_logo", type="string", format="string"),
     *            @OA\Property(property="business_website", type="string", format="string"),
     *            @OA\Property(property="business_owner", type="string", format="string"),
     *            @OA\Property(property="personal_phone_number", type="string", format="string"),
     *            @OA\Property(property="personal_email", type="string", format="string"),
     *            @OA\Property(property="business_company_registration", type="string", format="string"),
     *            @OA\Property(property="business_company_name", type="string", format="string"),
     *            @OA\Property(property="business_company_no", type="string", format="string"),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *   ),
     *   @OA\Response(
     *       response=401,
     *       description="Unauthenticated",
     *       @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *       response=403,
     *       description="Forbidden",
     *       @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *       response=400,
     *       description="Bad Request",
     *       @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *)
     **/
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'business_name' => 'required',
            'business_phone_number' => 'required',
            'business_address' => 'required',
            'business_email' => 'required',
            'business_email' => 'email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        
        $customer_id = $user->customer_id ?? $user->getKey();
        
        if (!$customer_id) {
            return $this->errorResponse('Customer ID not found for user', Response::HTTP_BAD_REQUEST);
        }

        // upload business_logo if provided
        $fileName = null;
        if ($request->hasFile('business_logo') && $request->file('business_logo')->isValid()) {
            $fileName = $this->fileUpload->uploadFile($request->business_logo, $this->folder);
        }

        try {
            DB::beginTransaction();
            
            $query = new CustomerBusiness();
            $query->slug = Str::slug($request->business_name).'-'.Str::lower(Str::random(4));
            $query->customer_id = $customer_id;
            $query->business_name = $request->business_name;
            $query->business_description = $request->business_description;
            $query->business_phone_number = $request->business_phone_number;
            $query->business_address = $request->business_address;
            $query->business_email = $request->business_email;
            $query->business_logo = $fileName;
            $query->business_website = $request->business_website;
            $query->business_owner = $request->business_owner;
            $query->personal_phone_number = $request->personal_phone_number;
            $query->personal_email = $request->personal_email;
            $query->business_company_registration = $request->business_company_registration;
            $query->business_company_name = $request->business_company_name;
            $query->business_company_no = $request->business_company_no;
            $query->category_id = $request->category_id;
            $query->business_category_slug = $request->business_category_slug;
            $query->city = $request->city;
            $query->country = $request->country;
            $query->booking_url = $request->booking_url;
            if ($request->has('category_profile') || $request->has('profile')) {
                $profile = $request->input('category_profile', $request->input('profile'));
                if (is_string($profile)) {
                    $decoded = json_decode($profile, true);
                    $profile = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
                }
                $query->category_profile = is_array($profile) ? $profile : null;
            }
            $query->status = 'active';
            
            $query->save();

            DB::commit();
            return $this->successResponse($query, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Business creation error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/business/{id}",
     *      tags={"Business"},
     *      summary="Detail customer business",
     *      description="Detail customer business",
     *      @OA\Parameter(
     *          name="id",
     *          description="Business ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function show($id)
    {
        $query = CustomerBusiness::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        // Convert business_logo to full URL
        if ($query->business_logo) {
            $query->business_logo = $this->fileUpload->getFile($query->business_logo, $this->folder);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1/business/{id}",
     *      tags={"Business"},
     *      summary="Update customer business",
     *      description="Update customer business",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Business ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="business_name", type="string", format="string"),
     *            @OA\Property(property="business_phone_number", type="string", format="string"),
     *            @OA\Property(property="business_address", type="string", format="string"),
     *            @OA\Property(property="business_email", type="string", format="string"),
     *            @OA\Property(property="business_logo", type="string", format="string"),
     *            @OA\Property(property="business_website", type="string", format="string"),
     *            @OA\Property(property="business_owner", type="string", format="string"),
     *            @OA\Property(property="personal_phone_number", type="string", format="string"),
     *            @OA\Property(property="personal_email", type="string", format="string"),
     *            @OA\Property(property="business_company_registration", type="string", format="string"),
     *            @OA\Property(property="business_company_name", type="string", format="string"),
     *            @OA\Property(property="business_company_no", type="string", format="string"),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'business_name' => 'required',
            'business_phone_number' => 'required',
            'business_address' => 'required',
            'business_email' => 'required',
            'business_email' => 'email',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $query = CustomerBusiness::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // upload business_logo if provided
        $imageName = "";
        if ($request->hasFile('business_logo') && $request->file('business_logo')->isValid()) {
            if ($query->business_logo && Storage::disk($this->folder)->exists($query->business_logo)) {
                Storage::disk($this->folder)->delete($query->business_logo);
            }
            $imageName = $this->fileUpload->uploadFile($request->business_logo, $this->folder);
        }

        try {
            DB::beginTransaction();
            if ($imageName != "") {
                $query->business_logo = $imageName;
            }
            $query->business_name = $request->business_name;
            $query->business_description = $request->business_description;
            $query->business_phone_number = $request->business_phone_number;
            $query->business_address = $request->business_address;
            $query->business_email = $request->business_email;
            $query->business_website = $request->business_website;
            $query->business_owner = $request->business_owner;
            $query->personal_phone_number = $request->personal_phone_number;
            $query->personal_email = $request->personal_email;
            $query->business_company_registration = $request->business_company_registration;
            $query->business_company_name = $request->business_company_name;
            $query->business_company_no = $request->business_company_no;
            $query->category_id = $request->category_id ?? null;
            if ($request->filled('business_category_slug')) {
                $query->business_category_slug = $request->business_category_slug;
            }
            if ($request->has('city')) {
                $query->city = $request->city;
            }
            if ($request->has('country')) {
                $query->country = $request->country;
            }
            if ($request->has('booking_url')) {
                $query->booking_url = $request->booking_url;
            }
            if ($request->has('category_profile') || $request->has('profile')) {
                $profile = $request->input('category_profile', $request->input('profile'));
                if (is_string($profile)) {
                    $decoded = json_decode($profile, true);
                    $profile = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
                }
                $query->category_profile = is_array($profile) ? $profile : null;
            }
            $query->status = $request->input('status', 'active');
            $query->save();

            DB::commit();
            return $this->successResponse($query, '', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Business update error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  @OA\Delete(
     *     path="/v1/business/{id}",
     *     summary="Delete customer business",
     *     description="Delete a single store based on the ID",
     *     tags={"Business"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="Business ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     * )
     */
    public function destroy($id)
    {
        $query = CustomerBusiness::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();
        
        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/business/{customer_id}/detail",
     *      tags={"Business"},
     *      summary="Detail customer business",
     *      description="Detail customer business",
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function detail($customer_id)
    {
        $query = CustomerBusiness::where('customer_id', $customer_id)->first();
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    public function myBusiness()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        $customer_id = $user->customer_id;
        $business = (object)[];
        $businessExists = CustomerBusiness::where('customer_id', $customer_id);
        if ($businessExists->exists()) {
            $business = $businessExists->first();
            // Convert business_logo to full URL
            if ($business->business_logo) {
                $business->business_logo = $this->fileUpload->getFile($business->business_logo, $this->folder);
            }
        }
        
        return $this->successResponse($business, '', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/business/{slug}",
     *      tags={"Business"},
     *      summary="Get business by slug",
     *      description="Get business by slug",
     *      @OA\Parameter(
     *          name="slug",
     *          description="Business slug",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function getBySlug($slug)
    {
        $query = CustomerBusiness::where('slug', $slug)->first();
        if (is_null($query)) {
            return $this->errorResponse('Business not found', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * List business team members (StaffManagement wrapper for dashboard invite UI).
     */
    public function members($id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $business = CustomerBusiness::where('id', $id)->first();
        if (!$business) {
            return $this->errorResponse('Business not found', Response::HTTP_NOT_FOUND);
        }

        $isOwner = (int) $business->customer_id === (int) $user->customer_id;
        $staffRows = StaffManagement::where('entity_type', 'business')
            ->where('entity_id', $id)
            ->with('staffMember')
            ->get();

        $members = $staffRows->map(function ($row) {
            $person = $row->staffMember;
            return [
                'id' => $row->staff_id,
                'member_id' => $row->staff_id,
                'staff_id' => $row->staff_id,
                'customer_id' => $row->staff_customer_id,
                'email' => $person->email ?? null,
                'name' => trim(($person->first_name ?? '').' '.($person->last_name ?? '')),
                'role' => $row->role === 'admin' ? 'manager' : $row->role,
                'status' => $row->is_active ? 'active' : 'revoked',
            ];
        })->values();

        return $this->successResponse([
            'members' => $members,
            'available_roles' => ['admin', 'manager', 'editor', 'viewer'],
            'can_manage' => $isOwner,
            'current_role' => $isOwner ? 'owner' : 'member',
        ], '', Response::HTTP_OK);
    }

    /**
     * Invite / add a team member by email.
     */
    public function addMember(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $business = CustomerBusiness::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();
        if (!$business) {
            return $this->errorResponse('Business not found or access denied', Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'nullable|in:admin,manager,editor,viewer',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $staffCustomer = Customer::where('email', strtolower(trim($request->email)))->first();
        if (!$staffCustomer) {
            return $this->errorResponse('User not found. Ask them to register on Worldwide Adverts first.', Response::HTTP_NOT_FOUND);
        }
        if ((int) $staffCustomer->customer_id === (int) $user->customer_id) {
            return $this->errorResponse('Cannot add yourself as a team member', Response::HTTP_BAD_REQUEST);
        }

        $role = $request->input('role', 'editor');
        if ($role === 'manager' || $role === 'admin') {
            $role = 'admin';
        }
        if (!in_array($role, ['admin', 'editor', 'viewer'], true)) {
            $role = 'editor';
        }

        $existing = StaffManagement::where('customer_id', $user->customer_id)
            ->where('staff_customer_id', $staffCustomer->customer_id)
            ->where('entity_type', 'business')
            ->where('entity_id', $id)
            ->first();
        if ($existing) {
            return $this->errorResponse('Member already invited', Response::HTTP_CONFLICT);
        }

        $staff = StaffManagement::create([
            'customer_id' => $user->customer_id,
            'staff_customer_id' => $staffCustomer->customer_id,
            'entity_type' => 'business',
            'entity_id' => $id,
            'role' => $role,
            'can_post_ads' => in_array($role, ['admin', 'editor'], true),
            'can_edit_ads' => in_array($role, ['admin', 'editor'], true),
            'can_delete_ads' => $role === 'admin',
            'can_manage_payments' => $role === 'admin',
            'can_view_analytics' => true,
            'can_manage_staff' => $role === 'admin',
            'is_active' => true,
            'invited_at' => now(),
            'joined_at' => now(),
        ]);

        return $this->successResponse($staff->load('staffMember'), 'Member added', Response::HTTP_CREATED);
    }

    public function updateMember(Request $request, $id, $memberId)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $business = CustomerBusiness::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();
        if (!$business) {
            return $this->errorResponse('Business not found or access denied', Response::HTTP_FORBIDDEN);
        }

        $staff = StaffManagement::where('staff_id', $memberId)
            ->where('entity_type', 'business')
            ->where('entity_id', $id)
            ->first();
        if (!$staff) {
            return $this->errorResponse('Member not found', Response::HTTP_NOT_FOUND);
        }

        $role = $request->input('role', $staff->role);
        if ($role === 'manager' || $role === 'admin') {
            $role = 'admin';
        }
        if (!in_array($role, ['admin', 'editor', 'viewer'], true)) {
            $role = $staff->role;
        }

        $staff->role = $role;
        $staff->can_post_ads = in_array($role, ['admin', 'editor'], true);
        $staff->can_edit_ads = in_array($role, ['admin', 'editor'], true);
        $staff->can_delete_ads = $role === 'admin';
        $staff->can_manage_payments = $role === 'admin';
        $staff->can_manage_staff = $role === 'admin';
        $staff->save();

        return $this->successResponse($staff, 'Member updated', Response::HTTP_OK);
    }

    public function removeMember($id, $memberId)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $business = CustomerBusiness::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();
        if (!$business) {
            return $this->errorResponse('Business not found or access denied', Response::HTTP_FORBIDDEN);
        }

        $staff = StaffManagement::where('staff_id', $memberId)
            ->where('entity_type', 'business')
            ->where('entity_id', $id)
            ->first();
        if (!$staff) {
            return $this->errorResponse('Member not found', Response::HTTP_NOT_FOUND);
        }

        $staff->delete();

        return $this->successResponse(null, 'Member removed', Response::HTTP_OK);
    }
}