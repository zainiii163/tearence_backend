<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $query = new Customer();
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($email = $request->get('email')) {
            $query = $query->where(function($query) use ($email) {
                $query = $query->where('email', 'like', '%'.$email.'%');
            });
        }

        if ($name = $request->get('name')) {
            $query = $query->where(function($query) use ($name) {
                $query = $query->where('first_name', 'like', '%'.$name.'%');
                $query = $query->orWhere('last_name', 'like', '%'.$name.'%');
            });
        }

        if ($status = $request->get('status')) {
            $query = $query->where('status', $status);
        }

        if ($sort = $request->get('sort')) {
            $query = $query->orderBy($sort, $request->get('sort_type') ? $request->get('sort_type') : 'asc');
        } else {
            $query = $query->orderBy('category_id');
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

        $result = [
            'items' => $query,
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
        // $input = $request->all();
        $customer = Customer::find($id);
        if (is_null($customer)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
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
            $customer->first_name = $requestData['first_name'];
            $customer->last_name = $requestData['last_name'];
            $customer->phone = $requestData['phone'];
            $customer->gender = $requestData['gender'];
            $customer->currency_id = $requestData['currency_id'];
            $customer->birthday = $requestData['birthday'];
            $customer->address_street = $requestData['address_street'];
            $customer->address_house = $requestData['address_house'];
            $customer->email = $requestData['email'];
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
    public function destroy($id)
    {
        $query = Customer::where('id', $id)->first();
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();
        
        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
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
