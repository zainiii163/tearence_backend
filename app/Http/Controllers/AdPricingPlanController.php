<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\AdPricingPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdPricingPlanController extends APIController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * @OA\Get(
     *      path="/v1/ad-pricing-plans",
     *      tags={"Ad Pricing Plans"},
     *      summary="Get all ad pricing plans",
     *      description="Get list of all ad pricing plans",
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="items", type="array",
     *                      @OA\Items(type="object")
     *                  ),
     *                  @OA\Property(property="total", type="integer", format="integer"),
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
    public function index()
    {
        $plans = AdPricingPlan::active()
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        $result = [
            'items' => $plans,
            'total' => $plans->count(),
        ];

        return $this->successResponse($result, 'Ad pricing plans retrieved successfully', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1/ad-pricing-plans",
     *      tags={"Ad Pricing Plans"},
     *      summary="Create ad pricing plan",
     *      description="Create new ad pricing plan",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *          @OA\Property(property="name", type="string", format="string"),
     *          @OA\Property(property="ad_type", type="string", enum={"banner", "affiliate", "classified"}),
     *          @OA\Property(property="price", type="number", format="float"),
     *          @OA\Property(property="duration_days", type="integer"),
     *          @OA\Property(property="description", type="string", format="string"),
     *          @OA\Property(property="is_active", type="boolean"),
     *          @OA\Property(property="is_featured", type="boolean"),
     *          @OA\Property(property="sort_order", type="integer"),
     *        )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Created",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/AdPricingPlanResource"
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
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'ad_type' => 'required|in:banner,affiliate,classified',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $plan = AdPricingPlan::create([
            'name' => $request->name,
            'ad_type' => $request->ad_type,
            'price' => $request->price,
            'duration_days' => $request->duration_days,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'is_featured' => $request->is_featured ?? false,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return $this->successResponse($plan, 'Ad pricing plan created successfully', Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *      path="/v1/ad-pricing-plans/{id}",
     *      tags={"Ad Pricing Plans"},
     *      summary="Update ad pricing plan",
     *      description="Update existing ad pricing plan",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Ad Pricing Plan ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *          @OA\Property(property="name", type="string", format="string"),
     *          @OA\Property(property="price", type="number", format="float"),
     *          @OA\Property(property="duration_days", type="integer"),
     *          @OA\Property(property="description", type="string", format="string"),
     *          @OA\Property(property="is_active", type="boolean"),
     *          @OA\Property(property="is_featured", type="boolean"),
     *          @OA\Property(property="sort_order", type="integer"),
     *        )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/AdPricingPlanResource"
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
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function update(Request $request, $id)
    {
        $plan = AdPricingPlan::find($id);
        if (!$plan) {
            return $this->errorResponse('Ad pricing plan not found', Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'name' => 'sometimes|string|max:100',
            'ad_type' => 'sometimes|in:banner,affiliate,classified',
            'price' => 'sometimes|numeric|min:0',
            'duration_days' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $plan->update($request->all());

        return $this->successResponse($plan, 'Ad pricing plan updated successfully', Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/v1/ad-pricing-plans/{id}",
     *      tags={"Ad Pricing Plans"},
     *      summary="Delete ad pricing plan",
     *      description="Delete ad pricing plan",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Ad Pricing Plan ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object"),
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
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function destroy($id)
    {
        $plan = AdPricingPlan::find($id);
        if (!$plan) {
            return $this->errorResponse('Ad pricing plan not found', Response::HTTP_NOT_FOUND);
        }

        $plan->delete();

        return $this->successResponse(null, 'Ad pricing plan deleted successfully', Response::HTTP_OK);
    }
}
