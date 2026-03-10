<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VehicleCategoryResource;
use App\Http\Resources\VehicleCategoryCollection;
use App\Models\VehicleCategory;
use App\Http\Requests\StoreVehicleCategoryRequest;
use App\Http\Requests\UpdateVehicleCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VehicleCategoryController extends Controller
{
    public function index(Request $request): VehicleCategoryCollection
    {
        $categories = VehicleCategory::where('is_active', true)
            ->withCount(['vehicles' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return new VehicleCategoryCollection($categories);
    }

    public function store(StoreVehicleCategoryRequest $request): JsonResponse
    {
        $category = VehicleCategory::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'icon' => $request->icon,
            'image' => $request->image,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'message' => 'Vehicle category created successfully',
            'category' => new VehicleCategoryResource($category)
        ], 201);
    }

    public function show(VehicleCategory $category): VehicleCategoryResource
    {
        $category->load(['vehicles' => function ($query) {
            $query->where('is_active', true)
                  ->with(['images'])
                  ->orderBy('created_at', 'desc')
                  ->take(12);
        }]);

        return new VehicleCategoryResource($category);
    }

    public function update(UpdateVehicleCategoryRequest $request, VehicleCategory $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json([
            'message' => 'Vehicle category updated successfully',
            'category' => new VehicleCategoryResource($category)
        ]);
    }

    public function destroy(VehicleCategory $category): JsonResponse
    {
        if ($category->vehicles()->exists()) {
            return response()->json([
                'message' => 'Cannot delete category with existing vehicles'
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Vehicle category deleted successfully']);
    }

    public function toggleStatus(VehicleCategory $category): JsonResponse
    {
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'message' => 'Category status updated',
            'is_active' => $category->is_active
        ]);
    }

    public function popularCategories(): VehicleCategoryCollection
    {
        $categories = VehicleCategory::where('is_active', true)
            ->withCount(['vehicles' => function ($query) {
                $query->where('is_active', true);
            }])
            ->having('vehicles_count', '>', 0)
            ->orderBy('vehicles_count', 'desc')
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        return new VehicleCategoryCollection($categories);
    }
}
