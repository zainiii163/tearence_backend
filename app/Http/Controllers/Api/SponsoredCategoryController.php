<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SponsoredCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SponsoredCategoryController extends Controller
{
    /**
     * Display a listing of sponsored categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = SponsoredCategory::withCount('adverts')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'count' => $category->adverts_count,
                    'color' => $category->color,
                    'created_at' => $category->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Display the specified sponsored category.
     */
    public function show($slug): JsonResponse
    {
        $category = SponsoredCategory::where('slug', $slug)
            ->withCount('adverts')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'count' => $category->adverts_count,
                'color' => $category->color,
                'description' => $category->description,
                'created_at' => $category->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Store a newly created sponsored category.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:sponsored_categories',
            'slug' => 'required|string|max:100|unique:sponsored_categories',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category = SponsoredCategory::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Update the specified sponsored category.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $category = SponsoredCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:100|unique:sponsored_categories,name,' . $id,
            'slug' => 'sometimes|required|string|max:100|unique:sponsored_categories,slug,' . $id,
            'icon' => 'sometimes|nullable|string|max:50',
            'color' => 'sometimes|nullable|string|max:50',
            'description' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->fresh(),
        ]);
    }

    /**
     * Remove the specified sponsored category.
     */
    public function destroy($id): JsonResponse
    {
        $category = SponsoredCategory::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
