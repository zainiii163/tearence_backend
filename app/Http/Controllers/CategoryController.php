<?php

namespace App\Http\Controllers;

use App\Http\Controllers\APIController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends APIController
{
    /**
     * Create a new CategoryController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
                'tree'
            ]
        ]);
    }

    /**
     * @OA\Get(
     *      path="/v1/category",
     *      tags={"Category"},
     *      summary="List categories",
     *      description="Get categories list",
     *      @OA\Parameter(
     *          name="is_parent",
     *          description="Is parent",
     *          in="query",
     *          @OA\Schema(
     *              default="yes",
     *              type="string",
     *              enum={"yes","no"},
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="category_id",
     *          description="Category ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
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
        $query = new Category();
        $skip = $request->get('skip');
        $limit = $request->get('limit');
        $is_parent = $request->get('is_parent');

        if ($is_parent && $is_parent == "yes") {
            $query = $query->whereNull('parent_id');
        }

        if ($id = $request->get('category_id')) {
            $query = $query->where('category_id', $id);
        }

        if ($keyword = $request->get('name')) {
            $query = $query->where(function ($query) use ($keyword) {
                $query = $query->where('name', 'like', '%' . $keyword . '%');
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

        if ($query) {
            foreach ($query as $row) {
                $row->category;
                $row->package;
                $row->images;
            }
        }

        $result = [
            'items' => $query,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     * path="/v1/category",
     *   tags={"Category"},
     *   summary="Create category",
     *   description="Create a new category",
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
            'name',
            'icon',
            'sort_order',
            'status',
        );
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $category = new Category();
            $category->name = $request->name;
            $category->slug = Str::slug($request->name);
            $category->icon = $request->icon;
            $category->sort_order = $request->sort_order;
            $category->status = 'active';
            $category->save();

            DB::commit();
            return $this->successResponse($category, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/category/{slug}",
     *      tags={"Category"},
     *      summary="Detail category",
     *      description="Get category detail by slug",
     *      @OA\Parameter(
     *          name="slug",
     *          description="Category slug",
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
    public function show($slug)
    {
        $query = Category::where('slug', $slug)->first();
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1/category/{id}",
     *      tags={"Category"},
     *      summary="Update category",
     *      description="Update category",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Category ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="course_id", type="integer", format="integer"),
     *              @OA\Property(property="user_id", type="integer", format="integer"),
     *              @OA\Property(property="order_id", type="string", format="string"),
     *          ),
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
    public function update(Request $request, $id)
    {
        $input = $request->only(
            'name',
            'icon',
            'sort_order',
            'status',
        );
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $category = Category::find($id);
        if (is_null($category)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            $category = new Category();
            $category->name = $request->name;
            $category->slug = Str::slug($request->name);
            $category->icon = $request->icon;
            $category->sort_order = $request->sort_order;
            $category->status = 'active';
            $category->save();

            DB::commit();
            return $this->successResponse($category, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Articles  $categorys
     * @return \Illuminate\Http\Response
     */
    /**
     *  @OA\Delete(
     *     path="/v1/category/{id}",
     *     summary="Delete category",
     *     description="Delete a single category based on the ID",
     *     tags={"Category"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="ID of category to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
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
     * )
     */
    public function destroy($id)
    {
        $query = Category::where('id', $id)->first();
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();

        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/category/tree",
     *      tags={"Category"},
     *      summary="Treeview category",
     *      description="Get category treeview",
     *      @OA\Parameter(
     *          name="id",
     *          description="Id",
     *          in="query",
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
     *                  @OA\Property(property="items", type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          ref="#/components/schemas/CategoryParentResource"
     *                      ),
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
    public function tree(Request $request)
    {
        // Get the top-level categories
        $categories = Category::whereNull('parent_id');

        // Filter by id if provided
        if ($request->get('id')) {
            $id = $request->get('id');
            $categories = $categories->where('category_id', (int)$id);
        }

        $categories = $categories->get();

        // Convert the Eloquent collection to an array
        $categoriesArray = $categories->toArray();

        $nestedKeys = [];

        // Check if there are any categories
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $parent_id = $category['category_id'];

                // Find the parent category index in the array
                $index = array_search($parent_id, array_column($categoriesArray, 'category_id'));

                // If the parent category exists, add the children
                if (!is_null($parent_id) && $index !== false) {
                    $categoriesArray[$index]['children'][] = $category->childs;
                    $nestedKeys[] = $category['category_id'];
                }
            }
        }

        // Prepare the result
        $result = [
            'items' => $categoriesArray, // Use the modified array with nested children
            'total' => count($categoriesArray),
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/category/{id}/filters",
     *      tags={"Category"},
     *      summary="Get category filter configuration",
     *      description="Get filter configuration for a specific category",
     *      @OA\Parameter(
     *          name="id",
     *          description="Category ID or slug",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *      ),
     *  )
     */
    public function getFilters($id)
    {
        // Try to find by ID first, then by slug
        $category = Category::where('category_id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (!$category) {
            return $this->errorResponse('Category not found', Response::HTTP_NOT_FOUND);
        }

        // Get filter configuration from category or use defaults
        $filterConfig = $category->getDefaultFilterConfig();

        // Add category-specific information
        $result = [
            'category_id' => $category->category_id,
            'category_name' => $category->name,
            'category_slug' => $category->slug,
            'filter_config' => $filterConfig,
            // Add available posting options for this category
            'posting_options' => [
                'paid' => true,
                'featured' => true,
                'promoted' => true,
                'sponsored' => true,
                'business' => true,
                'store' => true,
            ],
        ];

        return $this->successResponse($result, 'Filter configuration retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get category-specific posting form configuration
     */
    public function getPostingForm($id)
    {
        // Try to find by ID first, then by slug
        $category = Category::where('category_id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (!$category) {
            return $this->errorResponse('Category not found', Response::HTTP_NOT_FOUND);
        }

        // Get category-specific form configuration
        $formConfig = $category->posting_form_config ?? [];

        // Default form fields based on category
        $defaultFields = [
            'title' => [
                'type' => 'text',
                'label' => 'Title',
                'required' => true,
                'max_length' => 255,
            ],
            'description' => [
                'type' => 'textarea',
                'label' => 'Description',
                'required' => true,
            ],
            'price' => [
                'type' => 'number',
                'label' => 'Price',
                'required' => false,
            ],
            'location_id' => [
                'type' => 'select',
                'label' => 'Location',
                'required' => true,
            ],
            'currency_id' => [
                'type' => 'select',
                'label' => 'Currency',
                'required' => false,
            ],
        ];

        // Category-specific fields
        $categoryFields = match($category->slug ?? '') {
            'jobs', 'job' => [
                'job_type' => [
                    'type' => 'select',
                    'label' => 'Job Type',
                    'options' => ['full-time', 'part-time', 'contract', 'freelance', 'internship'],
                    'required' => true,
                ],
                'salary_min' => [
                    'type' => 'number',
                    'label' => 'Minimum Salary',
                    'required' => false,
                ],
                'salary_max' => [
                    'type' => 'number',
                    'label' => 'Maximum Salary',
                    'required' => false,
                ],
                'experience_required' => [
                    'type' => 'select',
                    'label' => 'Experience Required',
                    'options' => ['entry', 'mid', 'senior', 'executive'],
                    'required' => false,
                ],
            ],
            'vehicles', 'vehicle' => [
                'make' => [
                    'type' => 'text',
                    'label' => 'Make',
                    'required' => true,
                ],
                'model' => [
                    'type' => 'text',
                    'label' => 'Model',
                    'required' => true,
                ],
                'year' => [
                    'type' => 'number',
                    'label' => 'Year',
                    'required' => true,
                ],
                'mileage' => [
                    'type' => 'number',
                    'label' => 'Mileage',
                    'required' => false,
                ],
            ],
            'properties', 'property', 'real-estate' => [
                'property_type' => [
                    'type' => 'select',
                    'label' => 'Property Type',
                    'options' => ['house', 'apartment', 'condo', 'land', 'commercial'],
                    'required' => true,
                ],
                'bedrooms' => [
                    'type' => 'number',
                    'label' => 'Bedrooms',
                    'required' => false,
                ],
                'bathrooms' => [
                    'type' => 'number',
                    'label' => 'Bathrooms',
                    'required' => false,
                ],
                'area' => [
                    'type' => 'number',
                    'label' => 'Area (sq ft)',
                    'required' => false,
                ],
            ],
            default => [],
        };

        // Merge default fields with category-specific fields
        $allFields = array_merge($defaultFields, $categoryFields, $formConfig);

        $result = [
            'category_id' => $category->category_id,
            'category_name' => $category->name,
            'category_slug' => $category->slug,
            'form_fields' => $allFields,
            'posting_options' => [
                'paid' => true,
                'featured' => true,
                'promoted' => true,
                'sponsored' => true,
                'business' => true,
                'store' => true,
            ],
        ];

        return $this->successResponse($result, 'Posting form configuration retrieved successfully', Response::HTTP_OK);
    }
}
