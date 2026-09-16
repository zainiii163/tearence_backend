<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerController extends APIController
{
    /**
     * Create a new LessonController instance.
     *
     * @return void
     */
    protected $folder;
    protected $fileUpload;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
            ]
        ]);
        $this->folder = 'avatar';
        $this->fileUpload = new FileUploadHelper();
    }

    /**
     * B6/B15: only the account's owner (or an admin) may modify or delete it.
     *
     * The customer group runs behind `auth:api`, but nothing checked that the
     * `{id}` in the path belongs to the caller — so any signed-in user could
     * `PUT`/`DELETE` anyone else's account by id (an IDOR). This is the missing
     * ownership assertion.
     */
    private function ownsOrAdmin($id): bool
    {
        $authId = auth('api')->id();
        if ($authId !== null && (int) $authId === (int) $id) {
            return true;
        }

        $user = auth('api')->user();

        return $user !== null && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    /** True when the caller is an admin. */
    private function isAdminRequest(): bool
    {
        $user = auth('api')->user();

        return $user !== null && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/v1/customer",
     *      tags={"Customer"},
     *      summary="List customer",
     *      description="Get customer list",
     *      @OA\Parameter(
     *          name="email",
     *          description="Email address",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          description="Name",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Status (inactive, active)",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
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
     *                 ref="#/components/schemas/CategoryResource"
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
        $query = Customer::query();

        if ($email = $request->get('email')) {
            $query->where('email', 'like', '%'.$email.'%');
        }

        if ($name = $request->get('name')) {
            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', '%'.$name.'%')
                    ->orWhere('last_name', 'like', '%'.$name.'%');
            });
        }

        // React admin Users tab sends a single combined search term.
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%'.$search.'%')
                    ->orWhere('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%');
            });
        }

        // Only filter on optional columns when they actually exist (prevents
        // SQL 500s on databases that have not been migrated yet).
        if ($status = $request->get('status')) {
            if (Schema::hasColumn('customer', 'status')) {
                $query->where('status', $status);
            }
        }

        if ($role = $request->get('role')) {
            if (Schema::hasColumn('customer', 'role')) {
                $query->where('role', $role);
            }
        }

        $sort = $request->get('sort');
        if ($sort && Schema::hasColumn('customer', $sort)) {
            $query->orderBy($sort, $request->get('sort_type') ?: 'asc');
        } else {
            $query->orderBy('customer_id');
        }

        $total = $query->count();

        // Prefer page/per_page (admin Users tab), fall back to legacy skip/limit.
        $perPage = (int) $request->get('per_page', (int) ($request->get('limit') ?: 0));
        if ($perPage > 0) {
            $page = max(1, (int) $request->get('page', 1));
            $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
        } elseif (($skip = $request->get('skip')) !== null && $skip !== '') {
            $take = (int) ($request->get('limit') ?: 10);
            $items = $query->skip((int) $skip)->take($take > 0 ? $take : 10)->get();
        } else {
            $items = $query->get();
        }

        $result = [
            'items' => $items,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     * path="/v1/customer",
     *   tags={"Customer"},
     *   summary="Create customer",
     *   description="Create a new customer",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="author", type="integer", format="integer"),
     *            @OA\Property(property="title", type="string", format="string"),
     *            @OA\Property(property="content", type="string", format="string"),
     *            @OA\Property(property="media_type", type="string", format="string"),
     *            @OA\Property(property="media_url", type="string", format="string"),
     *            @OA\Property(property="status", type="string", format="string"),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/CategoryResource"
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
        $input = $request->only(
            'title',
            'content',
            'media_type',
            'media_url',
        );
        $validator = Validator::make($input, [
            'title' => 'required',
            'content' => 'required',
            'media_type' => 'required',
            'media_url' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        // upload image
        if ($request->media_type == 'image') {
            $imageName = $this->fileUpload->uploadFile($request->media_url, $this->folder);
        }

        try {
            DB::beginTransaction();

            $article = new Customer();
            $article->author = $request->author;
            $article->title = $request->title;
            $article->slug = Str::slug($request->title);
            $article->content = $request->content;
            $article->short_content = Str::limit(strip_tags($request->content), 50, '...');
            $article->media_type = $request->media_type;
            $article->media_url = $request->media_type == "image" ? $imageName : $request->media_url;
            $article->status = 'active';
            $article->save();

            DB::commit();
            return $this->successResponse($article, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *      path="/v1/customer/{id}",
     *      tags={"Customer"},
     *      summary="Detail customer",
     *      description="Get customer detail by ID",
     *      @OA\Parameter(
     *          name="id",
     *          description="Customer Id",
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
     *                 ref="#/components/schemas/CustomerResource"
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
        $query = Customer::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        // $query->attachment = $this->fileUpload->getFile($query->attachment, $this->folder);

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Put(
     *      path="/v1/customer/{id}",
     *      tags={"Customer"},
     *      summary="Update customer",
     *      description="Update customer",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Customer ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="first_name", type="string", format="string"),
     *              @OA\Property(property="last_name", type="string", format="string"),
     *              @OA\Property(property="email", type="string", format="string"),
     *              @OA\Property(property="phone", type="string", format="string"),
     *              @OA\Property(property="gender", type="string", format="string"),
     *              @OA\Property(property="birthday", type="string", format="string"),
     *              @OA\Property(property="country_id", type="integer", format="integer"),
     *              @OA\Property(property="zone_id", type="integer", format="integer"),
     *              @OA\Property(property="city", type="string", format="string"),
     *              @OA\Property(property="currency_id", type="integer", format="integer"),
     *              @OA\Property(property="zip", type="integer", format="integer"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/CustomerResource"
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
        // B6: reject cross-account edits before touching anything.
        if (! $this->ownsOrAdmin($id)) {
            return $this->errorResponse(
                'You do not have permission to modify this account.',
                Response::HTTP_FORBIDDEN
            );
        }

        // $input = $request->all();
        $customer = Customer::find($id);
        if (is_null($customer)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        if ($request->filled('crypto_wallet_address') && $request->filled('crypto_network') && ! $request->has('first_name')) {
            $network = strtolower((string) $request->input('crypto_network'));
            $check = \App\Support\CryptoRails::validateAddress(
                (string) $request->input('crypto_wallet_address'),
                $network
            );
            if (! $check['ok']) {
                return $this->errorResponse($check['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $customer->crypto_wallet_address = $check['address'];
            $customer->crypto_network = $network;
            $customer->save();

            return $this->successResponse($customer, 'Crypto wallet saved', Response::HTTP_OK);
        }

        $requestData = $request->all();

        // location
        if ($request->has('country_id')) {
            $customerLocation = Location::where('customer_id', $customer->customer_id)->first();
            if ($customerLocation != null) {
                $customerLocation->country_id = $request->country_id;
                $customerLocation->zone_id = $request->zone_id;
                $customerLocation->city = $request->city;
                $customerLocation->zip = $request->zip;
                $customerLocation->updated_at = date("Y-m-d H:i:s");
                $customerLocation->save();
            } else {
                $customerLocationNew = new Location();
                $customerLocationNew->country_id = $request->country_id;
                $customerLocationNew->customer_id = $customer->customer_id;
                $customerLocationNew->zone_id = $request->zone_id;
                $customerLocationNew->city = $request->city;
                $customerLocationNew->zip = $request->zip;
                $customerLocationNew->created_at = date("Y-m-d H:i:s");
                $customerLocationNew->save();
            }
        }
        // die(var_dump($requestData)); 

        try {
            DB::beginTransaction();

            // $customer->update($requestData);
            // Partial updates: only touch fields that were actually sent so
            // admin actions like PUT { role } or { status } do not wipe
            // profile data with nulls.
            $profileFields = [
                'first_name',
                'last_name',
                'phone',
                'gender',
                'currency_id',
                'birthday',
                'address_street',
                'address_house',
                'email',
            ];
            foreach ($profileFields as $field) {
                if ($request->has($field)) {
                    $customer->{$field} = $requestData[$field];
                }
            }

            // Admin management fields used by the Super Admin dashboard.
            if (Schema::hasColumn('customer', 'role') && $request->filled('role')) {
                $customer->role = strtolower((string) $request->input('role'));
            }
            if (Schema::hasColumn('customer', 'is_super_admin') && $request->has('is_super_admin')) {
                $customer->is_super_admin = (bool) $request->boolean('is_super_admin');
            }
            if (Schema::hasColumn('customer', 'status') && $request->filled('status')) {
                $customer->status = strtolower((string) $request->input('status'));
            }
            if (Schema::hasColumn('customer', 'user_type') && $request->filled('user_type')) {
                $customer->user_type = strtolower((string) $request->input('user_type'));
            }

            $customer->updated_at = date("Y-m-d H:i:s");
            $customer->save();

            DB::commit();
            return $this->successResponse($customer, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse(null, 'Data successfully updated!', Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    /**
     *  @OA\Delete(
     *     path="/v1/customer/{id}",
     *     summary="Delete customer",
     *     description="Delete a single customer based on the ID",
     *     tags={"Customer"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="ID of customer to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/CustomerResource"
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
    /**
     * A user requests deletion of their own account.
     *
     * This does NOT delete anything — deletion is admin-approved. It records
     * `deletion_requested_at` so the request shows up for an admin, and the
     * account stays fully usable until approval. Idempotent: requesting again
     * while one is pending is a no-op success.
     */
    public function requestDeletion($id)
    {
        if (! $this->ownsOrAdmin($id)) {
            return $this->errorResponse(
                'You do not have permission to modify this account.',
                Response::HTTP_FORBIDDEN
            );
        }

        $customer = Customer::find($id);
        if (is_null($customer)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        if (! $customer->hasPendingDeletionRequest()) {
            $customer->deletion_requested_at = now();
            $customer->save();
        }

        return $this->successResponse(
            ['deletion_requested_at' => $customer->deletion_requested_at],
            'Your account deletion request has been submitted for review.',
            Response::HTTP_OK
        );
    }

    /**
     * Admin approves a deletion request.
     *
     * The account is not physically removed — approval sets the soft-delete
     * `deleted_at` flag (the "deleted key"), so the row stays for records and
     * login answers "this account is deleted". This is what
     * `SuperAdminDashboard`'s delete-user action calls.
     */
    public function destroy($id)
    {
        // Only an admin may action a deletion; users can only *request* one.
        if (! $this->isAdminRequest()) {
            return $this->errorResponse(
                'Only an administrator can delete an account. Users may submit a deletion request.',
                Response::HTTP_FORBIDDEN
            );
        }

        // B15: the primary key is `customer_id`, not `id` — the old
        // `where('id', $id)` queried a non-existent column and 500'd. `find()`
        // uses the real PK.
        $customer = Customer::find($id);
        if (is_null($customer)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // Soft delete: sets `deleted_at` (the flag the system reads) without
        // removing the row.
        $customer->delete();

        return $this->successResponse(null, 'Account deletion approved.', Response::HTTP_OK);
    }

    /**
     * Admin rejects a pending deletion request — the account stays active.
     */
    public function rejectDeletion($id)
    {
        if (! $this->isAdminRequest()) {
            return $this->errorResponse(
                'Admin privileges required.',
                Response::HTTP_FORBIDDEN
            );
        }

        $customer = Customer::find($id);
        if (is_null($customer)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        $customer->deletion_requested_at = null;
        $customer->save();

        return $this->successResponse(null, 'Deletion request rejected.', Response::HTTP_OK);
    }

    /**
     * Admin list of accounts awaiting deletion approval.
     */
    public function deletionRequests()
    {
        if (! $this->isAdminRequest()) {
            return $this->errorResponse(
                'Admin privileges required.',
                Response::HTTP_FORBIDDEN
            );
        }

        $pending = Customer::whereNotNull('deletion_requested_at')
            ->orderBy('deletion_requested_at')
            ->get(['customer_id', 'first_name', 'last_name', 'email', 'deletion_requested_at']);

        return $this->successResponse($pending, 'Pending deletion requests.', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     * path="/v1/customer/upload-avatar/{id}",
     *   tags={"Customer"},
     *   summary="Upload customer avatar",
     *   description="Upload customer avatar",
     *      security={
     *        {"bearerAuth": {}}
     *     },
     *      @OA\Parameter(
     *          name="id",
     *          description="Customer ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="avatar", type="string", format="binary"),
     *          ),
     *     ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=403,
     *      description="Forbidden"
     *   )
     *)
     **/
    public function uploadAvatar(Request $request, $id)
    {
        // B6: an avatar belongs to one account; do not let others overwrite it.
        if (! $this->ownsOrAdmin($id)) {
            return $this->errorResponse(
                'You do not have permission to modify this account.',
                Response::HTTP_FORBIDDEN
            );
        }

        $input = $request->only('avatar');
        $validator = Validator::make($input, [
            'avatar' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $customer = Customer::where('customer_id', $id)->first();
        if (is_null($customer)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // upload avatar
        if (Storage::disk($this->folder)->exists($customer->avatar)) {
            Storage::disk($this->folder)->delete($customer->avatar);
        }
        $imageName = $this->fileUpload->uploadFile($request->avatar, $this->folder);

        try {
            DB::beginTransaction();
            $customer->avatar = $imageName;
            $customer->save();
            DB::commit();
            return $this->successResponse($customer->avatar, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
