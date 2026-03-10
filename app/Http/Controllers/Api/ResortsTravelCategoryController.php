<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResortsTravelCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResortsTravelCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ResortsTravelCategory::active();

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->input('type'));
        }

        $categories = $query->ordered()->get();

        // Load advert counts for each category
        $categories->each(function ($category) {
            $category->loadCount('activeAdverts');
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function show($slug)
    {
        $category = ResortsTravelCategory::with(['activeAdverts' => function ($query) {
            $query->with(['user'])->orderBy('created_at', 'desc')->limit(10);
        }])
        ->where('slug', $slug)
        ->active()
        ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:accommodation,transport,experience',
            'description' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:512',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('resorts-travel/categories/icons', 'public');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('resorts-travel/categories/images', 'public');
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = ResortsTravelCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = ResortsTravelCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:accommodation,transport,experience',
            'description' => 'sometimes|nullable|string',
            'icon' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:512',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'sometimes|nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = $request->file('icon')->store('resorts-travel/categories/icons', 'public');
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('resorts-travel/categories/images', 'public');
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = ResortsTravelCategory::findOrFail($id);

        // Check if category has adverts
        if ($category->adverts()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing adverts',
            ], 422);
        }

        // Delete associated files
        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    public function categoryTypes()
    {
        $types = [
            'accommodation' => 'Accommodation',
            'transport' => 'Transport Services',
            'experience' => 'Travel Experiences',
        ];

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function popularCategories()
    {
        $categories = ResortsTravelCategory::withCount(['activeAdverts' => function ($query) {
                $query->promoted();
            }])
            ->active()
            ->orderBy('active_adverts_count', 'desc')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function categoryAdverts($slug, Request $request)
    {
        $category = ResortsTravelCategory::where('slug', $slug)->active()->firstOrFail();

        $query = $category->activeAdverts()->with(['user']);

        // Apply the same filters as the main index method
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%");
            });
        }

        if ($request->has('min_price') || $request->has('max_price')) {
            $query->byPriceRange(
                $request->input('min_price'),
                $request->input('max_price')
            );
        }

        if ($request->has('verified')) {
            $query->verified();
        }

        // Sort
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        
        switch ($sort) {
            case 'title':
                $query->orderBy('title', $order);
                break;
            case 'price':
                $query->orderByRaw("CASE 
                    WHEN price_per_night IS NOT NULL THEN price_per_night
                    WHEN price_per_trip IS NOT NULL THEN price_per_trip
                    WHEN price_per_service IS NOT NULL THEN price_per_service
                    ELSE 999999
                END {$order}");
                break;
            case 'promotion':
                $query->orderByRaw("FIELD(promotion_tier, 'network_wide', 'sponsored', 'featured', 'promoted', 'standard') {$order}");
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $adverts = $query->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'adverts' => $adverts,
            ],
        ]);
    }
}
