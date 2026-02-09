<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Models\CustomerBusiness;
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
        $query = new CustomerBusiness();
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
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        $customer_id = $user->customer_id;

        // upload business_logo
        $fileName = $this->fileUpload->uploadFile($request->business_logo, $this->folder);

        try {
            DB::beginTransaction();

            $query = new CustomerBusiness();
            $query->slug = Str::slug($request->business_name);
            $query->customer_id = $customer_id;
            $query->business_name = $request->business_name;
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
            $query->status = 'active';
            $query->save();

            DB::commit();
            return $this->successResponse($query, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
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
        // $query->business_logo = $this->fileUpload->getFile($query->business_logo, $this->folder);

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
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $query = CustomerBusiness::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // upload business_logo
        $imageName = "";
        if ($request->business_logo) {
            if (Storage::disk($this->folder)->exists($query->business_logo)) {
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
            $query->status = 'active';
            $query->save();

            DB::commit();
            return $this->successResponse($query, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
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
}