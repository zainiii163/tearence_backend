<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobCategoryController extends Controller
{
    public function index()
    {
        $categories = JobCategory::where('is_active', true)
                                ->withCount('activeJobs')
                                ->orderBy('sort_order', 'asc')
                                ->orderBy('name', 'asc')
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function show($slug)
    {
        $category = JobCategory::where('slug', $slug)
                              ->withCount('activeJobs')
                              ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:job_categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $request->all();
        $data['slug'] = str()->slug($data['name']);
        $data['color'] = $data['color'] ?? '#3B82F6';
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = true;

        $category = JobCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = JobCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:100|unique:job_categories,name,' . $id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
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
            'data' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = JobCategory::findOrFail($id);

        // Check if category has jobs
        if ($category->jobs()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing jobs',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    public function popularCategories()
    {
        $categories = JobCategory::where('is_active', true)
                                ->withCount('activeJobs')
                                ->having('active_jobs_count', '>', 0)
                                ->orderBy('active_jobs_count', 'desc')
                                ->limit(12)
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function categoryWithJobs($slug)
    {
        $category = JobCategory::where('slug', $slug)
                              ->with(['activeJobs' => function ($query) {
                                  $query->orderBy('created_at', 'desc')
                                        ->limit(20);
                              }])
                              ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }
}
