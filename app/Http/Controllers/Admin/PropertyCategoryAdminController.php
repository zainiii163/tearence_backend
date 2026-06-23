<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyCategoryAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of property categories
     */
    public function index(Request $request)
    {
        $query = PropertyCategory::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(20);

        return view('admin.property-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new property category
     */
    public function create()
    {
        return view('admin.property-categories.create');
    }

    /**
     * Store a newly created property category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_categories',
            'slug' => 'required|string|max:255|unique:property_categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'type' => 'required|in:residential,commercial,industrial,land,agricultural,luxury,investment',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        PropertyCategory::create($validated);

        return redirect()->route('admin.properties.categories.index')
            ->with('success', 'Property category created successfully.');
    }

    /**
     * Display the specified property category
     */
    public function show(PropertyCategory $category)
    {
        $category->load('properties');
        return view('admin.property-categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified property category
     */
    public function edit(PropertyCategory $category)
    {
        return view('admin.property-categories.edit', compact('category'));
    }

    /**
     * Update the specified property category
     */
    public function update(Request $request, PropertyCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:property_categories,name,' . $category->id,
            'slug' => 'required|string|max:255|unique:property_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'type' => 'required|in:residential,commercial,industrial,land,agricultural,luxury,investment',
            'active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $category->update($validated);

        return redirect()->route('admin.properties.categories.index')
            ->with('success', 'Property category updated successfully.');
    }

    /**
     * Remove the specified property category
     */
    public function destroy(PropertyCategory $category)
    {
        // Check if category has properties
        if ($category->properties()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated properties.');
        }

        $category->delete();

        return redirect()->route('admin.properties.categories.index')
            ->with('success', 'Property category deleted successfully.');
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request)
    {
        $categories = $request->input('categories', []);

        foreach ($categories as $index => $categoryId) {
            PropertyCategory::where('id', $categoryId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
