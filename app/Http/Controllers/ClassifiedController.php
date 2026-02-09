<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ClassifiedController extends APIController
{
    /**
     * @OA\Get(
     *      path="/v1/classified",
     *      tags={"Classified"},
     *      summary="Get list of classified",
     *      description="Get list of classified",
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
    public function index()
    {
        $categories = Category::select('category_id', 'name', 'slug', 'icon')->whereNull('parent_id')->orderBy('name', 'asc');
        // if ($request->get('id')) {
        //     $id = $request->get('id');
        //     $categories = $categories->where('category_id', (int)$id);
        // }
        $categories = $categories->get();
        $categoriesArray = $categories;
        if (count($categories) > 0) {
            foreach($categories as $category) {
                $parent_id = $category['category_id'];
                $index = array_search($parent_id, array_column($categoriesArray, 'category_id'));

                if (!is_null($parent_id) && $index != -1) {
                    // $categoriesArray[$index]['children'][] = $category->childs;
                    $categoriesArray[$index]['children'] = [];
                    if ($category->childs) {
                        foreach ($category->childs as $child) {
                            $categoriesArray[$index]['children'][] = [
                                'category_id' => $child->category_id,
                                'parent_id' => $child->parent_id,
                                'name' => $child->name,
                                'slug' => $child->slug,
                            ];
                        }
                    }
                }
            }
        }

        $result = [
            'items' => $categoriesArray,
            'total' => $categories->count(),
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/classified/{slug}",
     *      tags={"Classified"},
     *      summary="Get listing classified",
     *      description="Get listing classified",
     *      @OA\Parameter(
     *          name="slug",
     *          description="Category slug",
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
    public function show($slug, Request $request)
    {
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        $listing = new Listing();

        $category = Category::where('slug', $slug)->first();
        if (is_null($category)) {
            return $this->errorResponse('Category not found', Response::HTTP_NOT_FOUND);
        }
        // die(var_dump($category->category_id));

        $listing = $listing->where('category_id', $category->category_id);
        $listing = $listing->where('listing.status', 'active');
        $listing = $listing->where('listing.promo_expire_at', '>=', '2023-01-01 00:00:00');

        if ($skip == "") {
            $listing = $listing->get();
            $total = $listing->count();
        } else {
            $perPage = ($skip == "") ? $listing->count() : (
                $request->has('limit') ? $limit : 10
            );
            $total = $listing->count();
            $listing = $listing->skip($skip)->take($perPage)->get();
        }

        $result = [
            'items' => $listing,
            'total' => $total,
        ];
        
        return $this->successResponse($result, '', Response::HTTP_OK);
    }
}
