<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ListingPackageController extends APIController
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
     * @OA\Get(
     *      path="/v1/listing-package",
     *      tags={"ListingPackage"},
     *      summary="List ads package",
     *      description="Get ads list package",
     *      @OA\Parameter(
     *          name="package_id",
     *          description="Package ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="title",
     *          description="Title",
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
     *                 ref="#/components/schemas/PackageResource"
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
        $query = new Package();
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($id = $request->get('package_id')) {
            $query = $query->where('package_id', $id);
        }

        if ($keyword = $request->get('title')) {
            $query = $query->where(function($query) use ($keyword) {
                $query = $query->where('title', 'like', '%'.$keyword.'%');
            });
        }

        if ($sort = $request->get('sort')) {
            $query = $query->orderBy($sort, $request->get('sort_type') ? $request->get('sort_type') : 'asc');
        } else {
            $query = $query->orderBy('package_id');
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
     * path="/v1/listing-package",
     *   tags={"ListingPackage"},
     *   summary="Create listing package",
     *   description="Create a new listing package",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="title", type="string", format="string"),
     *            @OA\Property(property="price", type="integer", format="integer"),
     *            @OA\Property(property="listing_days", type="integer", format="integer"),
     *            @OA\Property(property="promo_days", type="integer", format="integer"),
     *            @OA\Property(property="promo_show_promoted_area", type="string", format="string"),
     *            @OA\Property(property="promo_show_featured_area", type="string", format="string"),
     *            @OA\Property(property="promo_show_at_top", type="string", format="string"),
     *            @OA\Property(property="promo_sign", type="string", format="string"),
     *            @OA\Property(property="recommended_sign", type="string", format="string"),
     *            @OA\Property(property="auto_renewal", type="integer", format="integer"),
     *            @OA\Property(property="pictures", type="integer", format="integer"),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/PackageResource"
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
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required',
            'listing_days' => 'required',
            'promo_days' => 'required',
            'promo_show_promoted_area' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $package = new Package();
            $package->title = $request->title;
            $package->price = $request->price;
            $package->listing_days = $request->listing_days;
            $package->promo_days = $request->promo_days;
            $package->promo_show_promoted_area = $request->promo_show_promoted_area;
            $package->promo_show_featured_area = $request->promo_show_featured_area;
            $package->promo_show_at_top = $request->promo_show_at_top;
            $package->promo_sign = $request->promo_sign;
            $package->recommended_sign = $request->recommended_sign;
            $package->auto_renewal = $request->auto_renewal;
            $package->pictures = $request->pictures;
            $package->save();

            DB::commit();
            return $this->successResponse($package, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/listing-package/{id}",
     *      tags={"ListingPackage"},
     *      summary="Detail listing package",
     *      description="Detail listing package",
     *      @OA\Parameter(
     *          name="id",
     *          description="Package ID",
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
     *                 ref="#/components/schemas/PackageResource"
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
        $query = Package::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1/listing-package/{id}",
     *      tags={"ListingPackage"},
     *      summary="Update listing package",
     *      description="Update listing package",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Package ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *            @OA\Property(property="title", type="string", format="string"),
     *            @OA\Property(property="price", type="integer", format="integer"),
     *            @OA\Property(property="listing_days", type="integer", format="integer"),
     *            @OA\Property(property="promo_days", type="integer", format="integer"),
     *            @OA\Property(property="promo_show_promoted_area", type="string", format="string"),
     *            @OA\Property(property="promo_show_featured_area", type="string", format="string"),
     *            @OA\Property(property="promo_show_at_top", type="string", format="string"),
     *            @OA\Property(property="promo_sign", type="string", format="string"),
     *            @OA\Property(property="recommended_sign", type="string", format="string"),
     *            @OA\Property(property="auto_renewal", type="integer", format="integer"),
     *            @OA\Property(property="pictures", type="integer", format="integer"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/PackageResource"
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
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required',
            'listing_days' => 'required',
            'promo_days' => 'required',
            'promo_show_promoted_area' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $package = Package::find($id);
        if (is_null($package)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            $package->title = $request->title;
            $package->price = $request->price;
            $package->listing_days = $request->listing_days;
            $package->promo_days = $request->promo_days;
            $package->promo_show_promoted_area = $request->promo_show_promoted_area;
            $package->promo_show_featured_area = $request->promo_show_featured_area;
            $package->promo_show_at_top = $request->promo_show_at_top;
            $package->promo_sign = $request->promo_sign;
            $package->recommended_sign = $request->recommended_sign;
            $package->auto_renewal = $request->auto_renewal;
            $package->pictures = $request->pictures;
            $package->save();

            DB::commit();
            return $this->successResponse($package, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  @OA\Delete(
     *     path="/v1/listing-package/{id}",
     *     summary="Delete listing",
     *     description="Delete a single article based on the ID",
     *     tags={"ListingPackage"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="Package ID",
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
     *                 ref="#/components/schemas/PackageResource"
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
        $query = Package::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();
        
        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }
}
