<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromotedAdvertCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PromotedAdvertCategoryController extends Controller
{
    /**
     * Display a listing of promoted advert categories.
     */
    public function index(): JsonResponse
    {
        $categories = PromotedAdvertCategory::active()
            ->ordered()
            ->withCount(['promotedAdverts' => function ($query) {
                $query->active();
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created promoted advert category.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = PromotedAdvertCategory::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified promoted advert category.
     */
    public function show(string $slug): JsonResponse
    {
        $category = PromotedAdvertCategory::where('slug', $slug)
            ->with(['promotedAdverts' => function ($query) {
                $query->active()->with(['category', 'user']);
            }])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Update the specified promoted advert category.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = PromotedAdvertCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified promoted advert category.
     */
    public function destroy(int $id): JsonResponse
    {
        $category = PromotedAdvertCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Get popular categories with most promoted adverts.
     */
    public function popular(): JsonResponse
    {
        $categories = PromotedAdvertCategory::active()
            ->withCount(['promotedAdverts' => function ($query) {
                $query->active();
            }])
            ->orderBy('promoted_adverts_count', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get promoted adverts in a specific category.
     */
    public function categoryAdverts(string $slug): JsonResponse
    {
        $category = PromotedAdvertCategory::where('slug', $slug)->firstOrFail();
        
        $adverts = $category->promotedAdverts()
            ->active()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'adverts' => $adverts,
            ],
        ]);
    }
}
