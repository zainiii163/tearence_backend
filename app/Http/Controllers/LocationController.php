<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LocationController extends APIController
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
     *      path="/v1/location",
     *      tags={"Location"},
     *      summary="List ads location",
     *      description="Get ads list location",
     *      @OA\Parameter(
     *          name="location_id",
     *          description="Location ID",
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
     *                 ref="#/components/schemas/LocationResource"
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
        $query = new Location();
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($location_id = $request->get('location_id')) {
            $query = $query->where('location_id', $location_id);
        }

        if ($sort = $request->get('sort')) {
            $query = $query->orderBy($sort, $request->get('sort_type') ? $request->get('sort_type') : 'asc');
        } else {
            $query = $query->orderBy('favorite_id');
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
     * path="/v1/location",
     *   tags={"Location"},
     *   summary="Create listing location",
     *   description="Create a new listing location",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="country_id", type="integer", format="integer"),
     *            @OA\Property(property="zone_id", type="integer", format="integer"),
     *            @OA\Property(property="city", type="string", format="string"),
     *            @OA\Property(property="zip", type="string", format="string"),
     *            @OA\Property(property="latitude", type="string", format="string"),
     *            @OA\Property(property="longitude", type="string", format="string"),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/LocationResource"
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
            'customer_id',
            'listing_id',
        );
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'listing_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $location = new Location();
            $location->customer_id = $request->customer_id;
            $location->listing_id = $request->listing_id;
            $location->save();

            DB::commit();
            return $this->successResponse($location, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/location/{id}",
     *      tags={"Location"},
     *      summary="Detail listing location",
     *      description="Detail listing location",
     *      @OA\Parameter(
     *          name="id",
     *          description="Favorite ID",
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
     *                 ref="#/components/schemas/LocationResource"
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
        $query = Location::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1/location/{id}",
     *      tags={"Location"},
     *      summary="Update listing location",
     *      description="Update listing location",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Favorite ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="customer_id", type="integer", format="integer"),
     *              @OA\Property(property="listing_id", type="integer", format="integer"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/LocationResource"
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
        $input = $request->only(
            'customer_id',
            'listing_id',
        );
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'listing_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $location = Location::find($id);
        if (is_null($location)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();
            
            $location->customer_id = $request->customer_id;
            $location->listing_id = $request->listing_id;
            $location->save();

            DB::commit();
            return $this->successResponse($location, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  @OA\Delete(
     *     path="/v1/location/{id}",
     *     summary="Delete listing location",
     *     description="Delete a single article based on the ID",
     *     tags={"Location"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="Favorite ID",
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
     *                 ref="#/components/schemas/LocationResource"
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
        $query = Location::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();
        
        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }
}
