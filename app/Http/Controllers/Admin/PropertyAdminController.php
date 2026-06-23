<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PropertiesExport;

class PropertyAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display property dashboard with analytics
     */
    public function dashboard()
    {
        $stats = [
            'total_properties' => Property::count(),
            'active_properties' => Property::where('active', true)->count(),
            'pending_approval' => Property::where('approved', false)->count(),
            'total_views' => Property::sum('views'),
            'total_enquiries' => Property::sum('enquiries'),
            'featured_properties' => Property::where('advert_type', 'featured')->count(),
            'promoted_properties' => Property::where('advert_type', 'promoted')->count(),
            'sponsored_properties' => Property::where('advert_type', 'sponsored')->count(),
        ];

        $recentProperties = Property::with(['user', 'category'])
            ->latest()
            ->take(5)
            ->get();

        $popularProperties = Property::with(['user', 'category'])
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        return view('admin.properties.dashboard', compact('stats', 'recentProperties', 'popularProperties'));
    }

    /**
     * Display a listing of properties
     */
    public function index(Request $request)
    {
        $query = Property::with(['user', 'category']);

        // Filters
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('advert_type')) {
            $query->where('advert_type', $request->advert_type);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->filled('approved')) {
            $query->where('approved', $request->boolean('approved'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $properties = $query->latest()->paginate(20);

        return view('admin.properties.index', compact('properties'));
    }

    /**
     * Show the form for creating a new property
     */
    public function create()
    {
        $categories = PropertyCategory::where('active', true)->get();
        return view('admin.properties.create', compact('categories'));
    }

    /**
     * Store a newly created property
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'category' => 'required|in:buy,rent,lease,auction,invest',
            'property_type' => 'required|in:residential,commercial,industrial,land,agricultural,luxury,short_term_rental,investment,new_development',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,EUR,GBP,AED,SAR',
            'negotiable' => 'boolean',
            'deposit' => 'nullable|numeric|min:0',
            'service_charges' => 'nullable|numeric|min:0',
            'maintenance_fees' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'specifications' => 'nullable|array',
            'amenities' => 'nullable|array',
            'location_highlights' => 'nullable|array',
            'transport_links' => 'nullable|array',
            'seller_name' => 'required|string|max:255',
            'seller_company' => 'nullable|string|max:255',
            'seller_phone' => 'required|string|max:255',
            'seller_email' => 'required|email|max:255',
            'seller_website' => 'nullable|url|max:255',
            'verified_agent' => 'boolean',
            'active' => 'boolean',
            'approved' => 'boolean',
            'advert_type' => 'required|in:standard,promoted,featured,sponsored',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'seller_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        // Handle file uploads
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('properties/cover', 'public');
        }

        if ($request->hasFile('additional_images')) {
            $images = [];
            foreach ($request->file('additional_images') as $image) {
                $images[] = $image->store('properties/additional', 'public');
            }
            $validated['additional_images'] = json_encode($images);
        }

        if ($request->hasFile('seller_logo')) {
            $validated['seller_logo'] = $request->file('seller_logo')->store('properties/logos', 'public');
        }

        // Convert arrays to JSON
        $validated['specifications'] = $validated['specifications'] ? json_encode($validated['specifications']) : null;
        $validated['amenities'] = $validated['amenities'] ? json_encode($validated['amenities']) : null;
        $validated['location_highlights'] = $validated['location_highlights'] ? json_encode($validated['location_highlights']) : null;
        $validated['transport_links'] = $validated['transport_links'] ? json_encode($validated['transport_links']) : null;

        Property::create($validated);

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property created successfully.');
    }

    /**
     * Display the specified property
     */
    public function show(Property $property)
    {
        $property->load(['user', 'category', 'enquiries', 'favourites']);
        return view('admin.properties.show', compact('property'));
    }

    /**
     * Show the form for editing the specified property
     */
    public function edit(Property $property)
    {
        $categories = PropertyCategory::where('active', true)->get();
        return view('admin.properties.edit', compact('property', 'categories'));
    }

    /**
     * Update the specified property
     */
    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'category' => 'required|in:buy,rent,lease,auction,invest',
            'property_type' => 'required|in:residential,commercial,industrial,land,agricultural,luxury,short_term_rental,investment,new_development',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:USD,EUR,GBP,AED,SAR',
            'negotiable' => 'boolean',
            'deposit' => 'nullable|numeric|min:0',
            'service_charges' => 'nullable|numeric|min:0',
            'maintenance_fees' => 'nullable|numeric|min:0',
            'description' => 'required|string',
            'specifications' => 'nullable|array',
            'amenities' => 'nullable|array',
            'location_highlights' => 'nullable|array',
            'transport_links' => 'nullable|array',
            'seller_name' => 'required|string|max:255',
            'seller_company' => 'nullable|string|max:255',
            'seller_phone' => 'required|string|max:255',
            'seller_email' => 'required|email|max:255',
            'seller_website' => 'nullable|url|max:255',
            'verified_agent' => 'boolean',
            'active' => 'boolean',
            'approved' => 'boolean',
            'advert_type' => 'required|in:standard,promoted,featured,sponsored',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'seller_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
        ]);

        // Handle file uploads
        if ($request->hasFile('cover_image')) {
            // Delete old image
            if ($property->cover_image) {
                Storage::disk('public')->delete($property->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('properties/cover', 'public');
        }

        if ($request->hasFile('additional_images')) {
            // Delete old images
            if ($property->additional_images) {
                $oldImages = json_decode($property->additional_images, true);
                foreach ($oldImages as $image) {
                    Storage::disk('public')->delete($image);
                }
            }
            
            $images = [];
            foreach ($request->file('additional_images') as $image) {
                $images[] = $image->store('properties/additional', 'public');
            }
            $validated['additional_images'] = json_encode($images);
        }

        if ($request->hasFile('seller_logo')) {
            // Delete old logo
            if ($property->seller_logo) {
                Storage::disk('public')->delete($property->seller_logo);
            }
            $validated['seller_logo'] = $request->file('seller_logo')->store('properties/logos', 'public');
        }

        // Convert arrays to JSON
        $validated['specifications'] = $validated['specifications'] ? json_encode($validated['specifications']) : null;
        $validated['amenities'] = $validated['amenities'] ? json_encode($validated['amenities']) : null;
        $validated['location_highlights'] = $validated['location_highlights'] ? json_encode($validated['location_highlights']) : null;
        $validated['transport_links'] = $validated['transport_links'] ? json_encode($validated['transport_links']) : null;

        $property->update($validated);

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified property
     */
    public function destroy(Property $property)
    {
        // Delete associated files
        if ($property->cover_image) {
            Storage::disk('public')->delete($property->cover_image);
        }

        if ($property->additional_images) {
            $images = json_decode($property->additional_images, true);
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        if ($property->seller_logo) {
            Storage::disk('public')->delete($property->seller_logo);
        }

        $property->delete();

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property deleted successfully.');
    }

    /**
     * Approve a property
     */
    public function approve(Property $property)
    {
        $property->update(['approved' => true]);

        return back()->with('success', 'Property approved successfully.');
    }

    /**
     * Reject a property
     */
    public function reject(Property $property)
    {
        $property->update(['approved' => false]);

        return back()->with('success', 'Property rejected successfully.');
    }

    /**
     * Toggle property active status
     */
    public function toggleActive(Property $property)
    {
        $property->update(['active' => !$property->active]);

        return back()->with('success', 'Property status updated successfully.');
    }

    /**
     * Bulk approve properties
     */
    public function bulkApprove(Request $request)
    {
        $propertyIds = $request->input('property_ids', []);
        
        Property::whereIn('id', $propertyIds)->update(['approved' => true]);

        return back()->with('success', 'Properties approved successfully.');
    }

    /**
     * Bulk reject properties
     */
    public function bulkReject(Request $request)
    {
        $propertyIds = $request->input('property_ids', []);
        
        Property::whereIn('id', $propertyIds)->update(['approved' => false]);

        return back()->with('success', 'Properties rejected successfully.');
    }

    /**
     * Bulk update properties
     */
    public function bulkUpdate(Request $request)
    {
        $propertyIds = $request->input('property_ids', []);
        $updates = $request->except('property_ids', '_token');

        Property::whereIn('id', $propertyIds)->update($updates);

        return back()->with('success', 'Properties updated successfully.');
    }

    /**
     * Export properties to Excel
     */
    public function export()
    {
        return Excel::download(new PropertiesExport, 'properties.xlsx');
    }

    /**
     * Display property analytics
     */
    public function analytics()
    {
        $analytics = [
            'properties_by_type' => Property::select('property_type', DB::raw('count(*) as count'))
                ->groupBy('property_type')
                ->get(),
            
            'properties_by_category' => Property::select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get(),
            
            'properties_by_country' => Property::select('country', DB::raw('count(*) as count'))
                ->groupBy('country')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            
            'monthly_trends' => Property::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('count(*) as count')
                )
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),
        ];

        return view('admin.properties.analytics', compact('analytics'));
    }

    /**
     * Display property reports
     */
    public function reports()
    {
        $reports = [
            'most_viewed' => Property::orderBy('views', 'desc')->limit(10)->get(),
            'most_enquired' => Property::orderBy('enquiries', 'desc')->limit(10)->get(),
            'most_saved' => Property::orderBy('saves', 'desc')->limit(10)->get(),
            'recently_added' => Property::latest()->limit(10)->get(),
        ];

        return view('admin.properties.reports', compact('reports'));
    }

    /**
     * Analytics overview
     */
    public function analyticsOverview()
    {
        return response()->json([
            'total_properties' => Property::count(),
            'active_properties' => Property::where('active', true)->count(),
            'pending_approval' => Property::where('approved', false)->count(),
            'total_views' => Property::sum('views'),
            'total_enquiries' => Property::sum('enquiries'),
        ]);
    }

    /**
     * Popular properties
     */
    public function popularProperties()
    {
        $properties = Property::with(['user', 'category'])
            ->orderBy('views', 'desc')
            ->limit(20)
            ->get();

        return response()->json($properties);
    }

    /**
     * Property trends
     */
    public function propertyTrends()
    {
        $trends = Property::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return response()->json($trends);
    }

    /**
     * Search analytics
     */
    public function searchAnalytics()
    {
        // This would typically integrate with a search analytics service
        return response()->json([
            'popular_searches' => [],
            'search_trends' => [],
            'search_conversion' => [],
        ]);
    }
}
