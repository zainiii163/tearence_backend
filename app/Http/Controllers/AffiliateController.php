<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Models\Affiliate;
use App\Models\AdPricingPlan;
use App\Models\RevenueTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AffiliateController extends APIController
{
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
        $this->folder = 'affiliates';
        $this->fileUpload = new FileUploadHelper();
    }

    /**
     * @OA\Get(
     *      path="/v1/affiliate",
     *      tags={"Affiliate"},
     *      summary="List affiliate",
     *      description="Get list affiliate",
     *      @OA\Parameter(
     *          name="id",
     *          description="Affiliate ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="position",
     *          description="Position",
     *          in="query",
     *          @OA\Schema(
     *              default="top",
     *              type="string",
     *              enum={"top","bottom"},
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
     *                 ref="#/components/schemas/AffiliateResource"
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
        $query = new Affiliate();
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($id = $request->get('id')) {
            $query = $query->where('id', $id);
        }

        if ($position = $request->get('position')) {
            if ($position == '') {
                $query = $query->where(function ($query) use ($position) {
                    $query = $query->where('position', $position);
                    $query = $query->orWhere('position', $position);
                });
            } else {
                $query = $query->where('position', $position);
            }
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

        // if ($query) {
        //     foreach ($query as $row) {
        //         $row->image_url = $this->fileUpload->getFile($row->image_url, $this->folder);
        //     }
        // }

        $result = [
            'items' => $query,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/affiliate/pricing-plans",
     *      tags={"Affiliate"},
     *      summary="Get affiliate pricing plans",
     *      description="Get available pricing plans for affiliate ads",
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *          )
     *      )
     *  )
     */
    public function getPricingPlans()
    {
        $plans = AdPricingPlan::active()
            ->byType('affiliate')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
        
        return $this->successResponse($plans, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1/affiliate/payment",
     *      tags={"Affiliate"},
     *      summary="Process affiliate payment",
     *      description="Process payment for affiliate ad",
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *          @OA\Property(property="pricing_plan_id", type="integer"),
     *          @OA\Property(property="payment_method", type="string", enum={"paypal", "stripe", "bank_transfer"}),
     *          @OA\Property(property="transaction_id", type="string")
     *        )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK"
     *      )
     *  )
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pricing_plan_id' => 'required|exists:ad_pricing_plans,id',
            'payment_method' => 'required|in:paypal,stripe,bank_transfer',
            'transaction_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $plan = AdPricingPlan::find($request->pricing_plan_id);
        
        try {
            // Check if user is authenticated
            if (!auth('api')->check()) {
                return $this->errorResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $user = auth('api')->user();
            if (!$user || !isset($user->customer_id)) {
                return $this->errorResponse('Invalid user or customer ID not found', Response::HTTP_UNAUTHORIZED);
            }

            DB::beginTransaction();

            // Create revenue tracking record
            $revenue = RevenueTracking::create([
                'customer_id' => $user->customer_id,
                'ad_type' => 'affiliate',
                'amount' => $plan->price,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'status' => 'paid',
                'description' => "Affiliate ad payment - {$plan->name}"
            ]);

            DB::commit();
            return $this->successResponse($revenue, 'Payment processed successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *      path="/v1/affiliate",
     *      tags={"Affiliate"},
     *      summary="Create affiliate",
     *      description="Create new affiliate",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *          @OA\Property(property="position", type="string"),
     *          @OA\Property(property="link", type="string"),
     *          @OA\Property(property="title", type="string"),
     *          @OA\Property(property="image_url", type="string"),
     *          @OA\Property(property="pricing_plan_id", type="integer"),
     *          @OA\Property(property="payment_transaction_id", type="string")
     *        )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Created",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/AffiliateResource"
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
     *  )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|string|max:10',
            'link' => 'required|string|max:200',
            'title' => 'required|string|max:200',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:512',
            'pricing_plan_id' => 'required|exists:ad_pricing_plans,id',
            'payment_transaction_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $plan = AdPricingPlan::find($request->pricing_plan_id);
        
        $imageName = "";
        if ($request->hasFile('image_url')) {
            $imageName = $this->fileUpload->uploadFile($request->image_url, $this->folder);
        }

        try {
            // Check if user is authenticated
            if (!auth('api')->check()) {
                return $this->errorResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $user = auth('api')->user();
            if (!$user || !isset($user->customer_id)) {
                return $this->errorResponse('Invalid user or customer ID not found', Response::HTTP_UNAUTHORIZED);
            }

            DB::beginTransaction();

            $affiliate = Affiliate::create([
                'position' => $request->position,
                'link' => $request->link,
                'title' => $request->title,
                'image_url' => $imageName,
                'status' => 'active',
                'price' => $plan->price,
                'payment_status' => 'paid',
                'payment_transaction_id' => $request->payment_transaction_id,
                'paid_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
                'is_active' => true
            ]);

            // Update revenue tracking with affiliate_id
            $revenue = RevenueTracking::where('transaction_id', $request->payment_transaction_id)
                ->where('customer_id', $user->customer_id)
                ->first();
            
            if ($revenue) {
                $revenue->update(['affiliate_id' => $affiliate->id]);
            }

            DB::commit();
            return $this->successResponse($affiliate, 'Affiliate created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/affiliate/{id}",
     *      tags={"Affiliate"},
     *      summary="Detail affiliate",
     *      description="Detail affiliate",
     *      @OA\Parameter(
     *          name="id",
     *          description="Affiliate ID",
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
     *                 ref="#/components/schemas/AffiliateResource"
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
        $query = Affiliate::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1/affiliate/{id}",
     *      tags={"Affiliate"},
     *      summary="Update affiliate",
     *      description="Update affiliate",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Affiliate ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *          @OA\Property(property="position", type="string", format="string"),
     *          @OA\Property(property="link", type="string", format="string"),
     *          @OA\Property(property="title", type="string", format="string"),
     *          @OA\Property(property="status", type="string", format="string"),
     *          @OA\Property(property="image_url", type="string", format="binary"),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/AffiliateResource"
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
            'position' => 'required',
            'link' => 'required',
            'title' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $query = Affiliate::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // upload thumbnail
        $imageName = "";
        if ($request->has('image_url')) {
            if (!is_null($query->image_url)) {
                Storage::disk($this->folder)->delete($query->image_url);
            }
            $imageName = $this->fileUpload->uploadFile($request->image_url, $this->folder);
        }

        try {
            DB::beginTransaction();

            $query->position = $request->position;
            $query->link = $request->link;
            $query->title = $request->title;
            $query->status = $request->status;
            if ($imageName != "") {
                $query->image_url = $imageName;
            }
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
     *     path="/v1/affiliate/{id}",
     *     summary="Delete affiliate",
     *     description="Delete a single store based on the ID",
     *     tags={"Affiliate"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="Affiliate ID",
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
     *                 ref="#/components/schemas/AffiliateResource"
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
        $query = Affiliate::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();

        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/affiliate/my-affiliate",
     *      tags={"Affiliate"},
     *      summary="Get my affiliate",
     *      description="Get my affiliate",
     *      security={
     *          {"bearerAuth": {}}
     *      },
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
     *                 ref="#/components/schemas/AffiliateResource"
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
    public function myAffiliate(Request $request)
    {
        // Check if user is authenticated
        if (!auth('api')->check()) {
            return $this->errorResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        $user = auth('api')->user();
        if (!$user || !isset($user->customer_id)) {
            return $this->errorResponse('Invalid user or customer ID not found', Response::HTTP_UNAUTHORIZED);
        }

        $customer_id = $user->customer_id;
        $query = new Affiliate();
        $query = $query->where('customer_id', $customer_id);

        $skip = $request->get('skip');
        $limit = $request->get('limit');

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
}
