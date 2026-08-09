<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Models\CustomerStore;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StoreController extends APIController
{
    protected $folder;
    protected $fileUpload;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
                'showBySlug',
                'categories',
                'myAds',
                'detail',
            ]
        ]);
        $this->folder = 'store';
        $this->fileUpload = new FileUploadHelper();
    }

    /**
     * @OA\Get(
     *      path="/v1/store",
     *      tags={"Store"},
     *      summary="List ads store",
     *      description="Get ads list store",
     *      @OA\Parameter(
     *          name="store_id",
     *          description="Store ID",
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
     *                 ref="#/components/schemas/StoreResource"
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
        $query = CustomerStore::query()->where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        });

        if ($store_id = $request->get('store_id')) {
            $query->where('store_id', $store_id);
        }

        if ($customer_id = $request->get('customer_id')) {
            $query->where('customer_id', $customer_id);
        }

        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('category') && $request->get('category') !== 'all') {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('store_address', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $query->where('store_address', 'like', '%'.$request->get('location').'%');
        }

        $sort = $request->get('sort', 'store_id');
        $sortType = $request->get('sort_type', 'desc');
        $allowedSort = ['store_id', 'store_name', 'created_at', 'company_name', 'is_featured'];
        if (!in_array($sort, $allowedSort, true)) {
            $sort = 'store_id';
        }
        $query->orderBy($sort, $sortType === 'asc' ? 'asc' : 'desc');

        $skip = (int) $request->get('skip', 0);
        $limit = (int) $request->get('limit', 12);
        if ($limit < 1) {
            $limit = 12;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $total = (clone $query)->count();
        $items = $query->skip($skip)->take($limit)->get();
        $lastPage = max(1, (int) ceil($total / $limit));

        return $this->successResponse([
            'items' => $items,
            'total' => $total,
            'last_page' => $lastPage,
            'per_page' => $limit,
            'current_page' => (int) floor($skip / $limit) + 1,
            'filters' => [
                'categories' => CustomerStore::query()
                    ->whereNotNull('category')
                    ->where('category', '!=', '')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category'),
            ],
        ], '', Response::HTTP_OK);
    }

    /**
     * Distinct store categories for browse grid.
     */
    public function categories()
    {
        $defaults = [
            'fashion', 'electronics', 'home', 'food', 'beauty', 'sports', 'services', 'other',
        ];

        $fromDb = CustomerStore::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $rows = [];
        foreach (array_unique(array_merge($defaults, array_keys($fromDb))) as $cat) {
            $rows[] = [
                'id' => $cat,
                'slug' => $cat,
                'name' => ucwords(str_replace(['-', '_'], ' ', $cat)),
                'count' => (int) ($fromDb[$cat] ?? 0),
            ];
        }

        usort($rows, fn ($a, $b) => $b['count'] <=> $a['count'] ?: strcmp($a['name'], $b['name']));

        return $this->successResponse($rows, '', Response::HTTP_OK);
    }

    /**
     * Public store detail by slug.
     */
    public function showBySlug($slug)
    {
        $query = CustomerStore::where('slug', $slug)->first();
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        $productsCount = Listing::where('customer_id', $query->customer_id)->count();
        $payload = $query->toArray();
        $payload['products_count'] = $productsCount;
        $payload['listings_count'] = $productsCount;

        return $this->successResponse($payload, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     * path="/v1/store",
     *   tags={"Store"},
     *   summary="Create customer store",
     *   description="Create customer store",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="store_name", type="string", format="string"),
     *            @OA\Property(property="company_name", type="string", format="string"),
     *            @OA\Property(property="company_no", type="string", format="string"),
     *            @OA\Property(property="vat", type="string", format="string"),
     *            @OA\Property(property="store_address", type="string", format="string"),
     *            @OA\Property(property="store_logo", type="string", format="binary"),
     *            @OA\Property(property="store_banner", type="string", format="binary"),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/StoreResource"
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
            'store_name' => 'required',
            'company_name' => 'nullable|string|max:255',
            'company_no' => 'nullable|string|max:100',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        
        // For User model, we need to find the corresponding customer
        $customer = \App\Models\Customer::where('email', $user->email)->first();
        $customer_id = $customer ? $customer->customer_id : $user->user_id;

        // upload store logo & banner
        $fileLogo = "";
        $fileBanner = "";
        if ($request->store_logo) {
            $fileLogo = $this->fileUpload->uploadFile($request->store_logo, $this->folder);
        }
        if ($request->store_banner) {
            $fileBanner = $this->fileUpload->uploadFile($request->store_banner, $this->folder);
        }

        try {
            DB::beginTransaction();

            $query = new CustomerStore();
            $query->slug = Str::slug($request->store_name);
            $query->customer_id = $customer_id;
            $query->store_name = $request->store_name;
            $query->company_name = $request->company_name;
            $query->company_no = $request->company_no;
            $query->vat = $request->vat;
            $query->store_address = $request->store_address;
            $query->category = $request->input('category');
            $query->description = $request->input('description');
            $query->phone = $request->input('phone');
            $query->email = $request->input('email');
            $query->website = $request->input('website');
            $query->is_featured = (bool) $request->boolean('is_featured');
            $query->status = $request->input('status', 'active');
            $query->created_at = date("Y-m-d H:i:s");
            if ($fileLogo != "") {
                $query->store_logo = $fileLogo;
            }
            if ($fileBanner != "") {
                $query->store_banner = $fileBanner;
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
     * @OA\Get(
     *      path="/v1/store/{id}",
     *      tags={"Store"},
     *      summary="Detail customer store",
     *      description="Detail customer store",
     *      @OA\Parameter(
     *          name="id",
     *          description="Store ID",
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
     *                 ref="#/components/schemas/StoreResource"
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
        $query = CustomerStore::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1/store/{id}",
     *      tags={"Store"},
     *      summary="Update customer store",
     *      description="Update customer store",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="store_id",
     *          description="Store ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="store_name", type="string", format="string"),
     *            @OA\Property(property="company_name", type="string", format="string"),
     *            @OA\Property(property="company_no", type="string", format="string"),
     *            @OA\Property(property="vat", type="string", format="string"),
     *            @OA\Property(property="store_address", type="string", format="string"),
     *            @OA\Property(property="store_logo", type="string", format="binary"),
     *            @OA\Property(property="store_banner", type="string", format="binary"),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/StoreResource"
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
            'store_name' => 'required',
            'company_name' => 'nullable|string|max:255',
            'company_no' => 'nullable|string|max:100',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $query = CustomerStore::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // upload the store logo & banner
        $logoName = "";
        if ($request->store_logo) {
            if (Storage::disk($this->folder)->exists($query->store_logo)) {
                Storage::disk($this->folder)->delete($query->store_logo);
            }
            $logoName = $this->fileUpload->uploadFile($request->store_logo, $this->folder);
        }

        $bannerName = "";
        if ($request->store_banner) {
            if (Storage::disk($this->folder)->exists($query->store_banner)) {
                Storage::disk($this->folder)->delete($query->store_banner);
            }
            $bannerName = $this->fileUpload->uploadFile($request->store_banner, $this->folder);
        }

        try {
            DB::beginTransaction();

            if ($logoName != "") {
                $query->store_logo = $logoName;
            }
            if ($bannerName != "") {
                $query->store_banner = $bannerName;
            }
            $query->store_name = $request->store_name;
            $query->company_name = $request->company_name;
            $query->company_no = $request->company_no;
            $query->vat = $request->vat;
            $query->store_address = $request->store_address;
            if ($request->has('category')) {
                $query->category = $request->input('category');
            }
            if ($request->has('description')) {
                $query->description = $request->input('description');
            }
            if ($request->has('phone')) {
                $query->phone = $request->input('phone');
            }
            if ($request->has('email')) {
                $query->email = $request->input('email');
            }
            if ($request->has('website')) {
                $query->website = $request->input('website');
            }
            if ($request->has('is_featured')) {
                $query->is_featured = (bool) $request->boolean('is_featured');
            }
            $query->status = $request->input('status', 'active');
            $query->updated_at = date("Y-m-d H:i:s");
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
     *     path="/v1/store/{id}",
     *     summary="Delete customer store",
     *     description="Delete a single store based on the ID",
     *     tags={"Store"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="Store ID",
     *         in="path",
     *         name="store_id",
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
     *                 ref="#/components/schemas/StoreResource"
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
        $query = CustomerStore::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();
        
        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/store/my-store",
     *      tags={"Store"},
     *      summary="Get my store",
     *      description="Get my store",
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
    public function myStore()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        
        // For User model, we need to find the corresponding customer
        $customer = \App\Models\Customer::where('email', $user->email)->first();
        $customer_id = $customer ? $customer->customer_id : $user->user_id;
        
        $store = (object)[];
        $storeExists = CustomerStore::where('customer_id', $customer_id);
        if ($storeExists->exists()) {
            $store = $storeExists->first();
        }
        
        return $this->successResponse($store, '', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/store/{id}/my-ads",
     *      tags={"Store"},
     *      summary="Get my ads",
     *      description="Get my ads based on store",
     *      @OA\Parameter(
     *          name="id",
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
     *                 ref="#/components/schemas/ListingResource"
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
    public function myAds($id, Request $request)
    {
        $query = new Listing();
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($id) {
            $query = $query->where('customer_id', $id);
        }

        if ($sort = $request->get('sort')) {
            $query = $query->orderBy($sort, $request->get('sort_type') ? $request->get('sort_type') : 'asc');
        } else {
            $query = $query->orderBy('listing_id');
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
     *      path="/v1/store/{customer_id}/detail",
     *      tags={"Store"},
     *      summary="Detail customer store",
     *      description="Detail customer store",
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/StoreResource"
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
        $query = CustomerStore::where('customer_id', $customer_id)->first();
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }
}