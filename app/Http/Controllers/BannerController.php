<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Models\Banner;
use App\Models\CustomerBusiness;
use App\Models\AdPricingPlan;
use App\Models\RevenueTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends APIController
{
    protected $folder;
    protected $fileUpload;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
                'scrape',
            ]
        ]);

        $this->folder = 'banner';
        $this->fileUpload = new FileUploadHelper();
    }

    /**
     * @OA\Get(
     *      path="/v1/banner",
     *      tags={"Banner"},
     *      summary="List banner",
     *      description="Get list banner",
     *      @OA\Parameter(
     *          name="user_id",
     *          description="User ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          description="Banner ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category",
     *          description="Category",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="size",
     *          description="Size",
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
     *                 ref="#/components/schemas/BannerResource"
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
        $query = new Banner();
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($id = $request->get('id')) {
            $query = $query->where('id', $id);
        }

        if ($request->filled('user_id')) {
            $user_id = $request->get('user_id');
            $query = $query->where('user_id', $user_id);
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
     * @OA\Get(
     *      path="/v1/banner/pricing-plans",
     *      tags={"Banner"},
     *      summary="Get banner pricing plans",
     *      description="Get available pricing plans for banner ads",
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
            ->byType('banner')
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
        
        return $this->successResponse($plans, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1/banner/payment",
     *      tags={"Banner"},
     *      summary="Process banner payment",
     *      description="Process payment for banner ad",
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
            if (!auth()->check()) {
                return $this->errorResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $user = auth()->user();
            if (!$user || !isset($user->customer_id)) {
                return $this->errorResponse('Invalid user or customer ID not found', Response::HTTP_UNAUTHORIZED);
            }

            DB::beginTransaction();

            // Create revenue tracking record
            $revenue = RevenueTracking::create([
                'customer_id' => $user->customer_id,
                'ad_type' => 'banner',
                'amount' => $plan->price,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'status' => 'paid',
                'description' => "Banner ad payment - {$plan->name}"
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
     *      path="/v1/banner",
     *      tags={"Banner"},
     *      summary="Create banner",
     *      description="Create new banner",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *          @OA\Property(property="title", type="string", format="string"),
     *          @OA\Property(property="url_link", type="string", format="string"),
     *          @OA\Property(property="img", type="string", format="binary"),
     *          @OA\Property(property="size_img", type="string", format="string"),
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
     *                 ref="#/components/schemas/BannerResource"
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
            'title' => 'required|string|max:100',
            'url_link' => 'required|string|max:100',
            'img' => 'required|image|mimes:jpeg,png,jpg,gif|max:512',
            'size_img' => 'nullable|string|max:50',
            'pricing_plan_id' => 'required|exists:ad_pricing_plans,id',
            'payment_transaction_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $plan = AdPricingPlan::find($request->pricing_plan_id);
        
        $imageName = "";
        if ($request->hasFile('img')) {
            $imageName = $this->fileUpload->uploadFile($request->img, $this->folder);
        }

        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                return $this->errorResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $user = auth()->user();
            if (!$user || !isset($user->customer_id)) {
                return $this->errorResponse('Invalid user or customer ID not found', Response::HTTP_UNAUTHORIZED);
            }

            DB::beginTransaction();

            $banner = Banner::create([
                'title' => $request->title,
                'url_link' => $request->url_link,
                'img' => $imageName,
                'size_img' => $request->size_img,
                'user_id' => $user->customer_id,
                'price' => $plan->price,
                'payment_status' => 'paid',
                'payment_transaction_id' => $request->payment_transaction_id,
                'paid_at' => now(),
                'expires_at' => now()->addDays($plan->duration_days),
                'is_active' => true
            ]);

            // Update revenue tracking with banner_id
            $revenue = RevenueTracking::where('transaction_id', $request->payment_transaction_id)
                ->where('customer_id', $user->customer_id)
                ->first();
            
            if ($revenue) {
                $revenue->update(['banner_id' => $banner->id]);
            }

            DB::commit();
            return $this->successResponse($banner, 'Banner created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/banner/{id}",
     *      tags={"Banner"},
     *      summary="Detail banner",
     *      description="Detail banner",
     *      @OA\Parameter(
     *          name="id",
     *          description="Banner ID",
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
     *                 ref="#/components/schemas/BannerResource"
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
        $query = Banner::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1/banner/{id}",
     *      tags={"Banner"},
     *      summary="Update banner",
     *      description="Update banner",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Banner ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *          @OA\Property(property="title", type="string", format="string"),
     *          @OA\Property(property="url_link", type="string", format="string"),
     *          @OA\Property(property="img", type="string", format="binary"),
     *          @OA\Property(property="size_img", type="string", format="string"),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BannerResource"
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
            'title' => 'required',
            'url_link' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $query = Banner::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // upload the img
        $imageName = "";
        if ($request->img) {
            if (Storage::disk($this->folder)->exists($query->img)) {
                Storage::disk($this->folder)->delete($query->img);
            }
            $imageName = $this->fileUpload->uploadFile($request->img, $this->folder);
        }

        try {
            // Check if user is authenticated
            if (!auth()->check()) {
                return $this->errorResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
            }

            $user = auth()->user();
            if (!$user || !isset($user->customer_id)) {
                return $this->errorResponse('Invalid user or customer ID not found', Response::HTTP_UNAUTHORIZED);
            }

            DB::beginTransaction();

            if ($imageName != "") {
                $query->img = $imageName;
            }

            $query->title = $request->title;
            $query->url_link = $request->url_link;
            $query->size_img = $request->size_img;
            $query->user_id = $user->customer_id;
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
     *     path="/v1/banner/{id}",
     *     summary="Delete banner",
     *     description="Delete a single data based on the ID",
     *     tags={"Banner"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="Banner ID",
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
     *                 ref="#/components/schemas/BannerResource"
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
        $query = Banner::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();
        
        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/banner/my-banner",
     *      tags={"Banner"},
     *      summary="Get my banner",
     *      description="Get my banner",
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
     *                 ref="#/components/schemas/BannerResource"
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
    public function myBanner(Request $request)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return $this->errorResponse('User not authenticated', Response::HTTP_UNAUTHORIZED);
        }

        $user = auth()->user();
        if (!$user || !isset($user->customer_id)) {
            return $this->errorResponse('Invalid user or customer ID not found', Response::HTTP_UNAUTHORIZED);
        }

        $user_id = $user->customer_id;
        $query = new Banner();
        $query = $query->where('user_id', $user_id);

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

    /**
     * @OA\Get(
     *      path="/v1/banner/{slug}",
     *      tags={"Banner"},
     *      summary="Get banner by slug",
     *      description="Get banner by slug",
     *      @OA\Parameter(
     *          name="slug",
     *          description="Business slug",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
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
     *                 ref="#/components/schemas/BannerResource"
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
    public function getBySlug(Request $request, $slug)
    {
        $business = CustomerBusiness::where('slug', $slug)->first();
        if (is_null($business)) {
            return $this->errorResponse('Business not found', Response::HTTP_NOT_FOUND);
        }
        $customer_id = $business->customer_id;

        $query = new Banner();
        $query = $query->where('user_id', $customer_id);

        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($status = $request->get('status')) {
            $query = $query->where('status', $status);
        }

        if ($keyword = $request->get('title')) {
            $query = $query->where(function($query) use ($keyword) {
                $query = $query->where('title', 'like', '%'.$keyword.'%');
            });
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
     * path="/v1/banner/upload",
     *   tags={"Banner"},
     *   summary="Upload banner",
     *   description="Upload banner",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *          @OA\Property(property="id", type="number", format="number"),
     *          @OA\Property(property="img", type="string", format="binary"),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BannerResource"
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
    public function upload(Request $request)
    {
        $query = Banner::find($request->id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // upload banner
        $fileName = "";
        if ($request->img) {
            $fileName = $this->fileUpload->uploadFile($request->img, $this->folder);
        }

        return $this->successResponse($fileName, '', Response::HTTP_CREATED);
    }
}
