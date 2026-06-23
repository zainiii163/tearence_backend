<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use App\Models\BannerCategory;
use App\Http\Resources\BannerAdResource;
use App\Http\Resources\BannerAdCollection;
use App\Http\Resources\BannerCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BannerAdminController extends Controller
{
    /**
     * Display the banner admin dashboard.
     */
    public function dashboard()
    {
        $stats = [
            'total_banners' => BannerAd::count(),
            'active_banners' => BannerAd::where('status', 'active')->count(),
            'pending_banners' => BannerAd::where('status', 'pending')->count(),
            'total_views' => BannerAd::sum('views_count'),
            'total_clicks' => BannerAd::sum('clicks_count'),
            'avg_ctr' => BannerAd::where('views_count', '>', 0)->avg(DB::raw('(clicks_count * 100.0 / views_count)')) ?: 0,
            'featured_banners' => BannerAd::where('promotion_tier', 'featured')->count(),
            'sponsored_banners' => BannerAd::where('promotion_tier', 'sponsored')->count(),
            'revenue' => BannerAd::sum('promotion_price'),
        ];

        $recentBanners = BannerAd::with('category')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $topPerforming = BannerAd::with('category')
            ->where('views_count', '>', 0)
            ->orderByRaw('(clicks_count * 100.0 / views_count) DESC')
            ->limit(5)
            ->get();

        return view('admin.banner.dashboard', compact('stats', 'recentBanners', 'topPerforming'));
    }

    /**
     * Display a listing of banner ads.
     */
    public function index(Request $request)
    {
        $query = BannerAd::with(['category', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by promotion tier
        if ($request->has('promotion_tier') && $request->promotion_tier !== 'all') {
            $query->where('promotion_tier', $request->promotion_tier);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('banner_category_id', $request->category_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // Date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $banners = $query->paginate(20);
        $categories = BannerCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.banner.index', compact('banners', 'categories'));
    }

    /**
     * Show the form for creating a new banner ad.
     */
    public function create()
    {
        $categories = BannerCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.banner.create', compact('categories'));
    }

    /**
     * Store a newly created banner ad.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website_url' => 'nullable|url|max:500',
            'banner_type' => 'required|in:image,animated,html5,video',
            'banner_size' => 'required|string',
            'banner_image' => 'required|string|max:500',
            'destination_link' => 'required|url|max:500',
            'call_to_action' => 'nullable|string|max:100',
            'key_selling_points' => 'nullable|string',
            'offer_details' => 'nullable|string',
            'validity_start' => 'nullable|date',
            'validity_end' => 'nullable|date|after_or_equal:validity_start',
            'banner_category_id' => 'required|exists:banner_categories,id',
            'country' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'target_countries' => 'nullable|array',
            'target_audience' => 'nullable|array',
            'promotion_tier' => 'required|in:standard,promoted,featured,sponsored,network_boost',
            'promotion_price' => 'required|numeric|min:0',
            'promotion_start' => 'nullable|date',
            'promotion_end' => 'nullable|date|after_or_equal:promotion_start',
            'is_verified_business' => 'boolean',
            'status' => 'required|in:draft,pending,active,rejected,expired',
            'is_active' => 'boolean',
        ]);

        // Generate slug
        $validated['slug'] = Str::slug($validated['title']);
        $validated['user_id'] = Auth::id();

        // Handle array fields
        if (isset($validated['target_countries'])) {
            $validated['target_countries'] = json_encode($validated['target_countries']);
        }
        if (isset($validated['target_audience'])) {
            $validated['target_audience'] = json_encode($validated['target_audience']);
        }

        BannerAd::create($validated);

        return redirect()->route('admin.banner.index')
            ->with('success', 'Banner ad created successfully.');
    }

    /**
     * Display the specified banner ad.
     */
    public function show($id)
    {
        $banner = BannerAd::with(['category', 'user'])->findOrFail($id);
        
        // Calculate analytics
        $ctr = $banner->views_count > 0 ? ($banner->clicks_count / $banner->views_count) * 100 : 0;
        
        return view('admin.banner.show', compact('banner', 'ctr'));
    }

    /**
     * Show the form for editing the specified banner ad.
     */
    public function edit($id)
    {
        $banner = BannerAd::findOrFail($id);
        $categories = BannerCategory::where('is_active', true)->orderBy('name')->get();
        
        // Decode JSON fields
        if ($banner->target_countries) {
            $banner->target_countries = json_decode($banner->target_countries, true);
        }
        if ($banner->target_audience) {
            $banner->target_audience = json_decode($banner->target_audience, true);
        }
        
        return view('admin.banner.edit', compact('banner', 'categories'));
    }

    /**
     * Update the specified banner ad.
     */
    public function update(Request $request, $id)
    {
        $banner = BannerAd::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website_url' => 'nullable|url|max:500',
            'banner_type' => 'required|in:image,animated,html5,video',
            'banner_size' => 'required|string',
            'banner_image' => 'required|string|max:500',
            'destination_link' => 'required|url|max:500',
            'call_to_action' => 'nullable|string|max:100',
            'key_selling_points' => 'nullable|string',
            'offer_details' => 'nullable|string',
            'validity_start' => 'nullable|date',
            'validity_end' => 'nullable|date|after_or_equal:validity_start',
            'banner_category_id' => 'required|exists:banner_categories,id',
            'country' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'target_countries' => 'nullable|array',
            'target_audience' => 'nullable|array',
            'promotion_tier' => 'required|in:standard,promoted,featured,sponsored,network_boost',
            'promotion_price' => 'required|numeric|min:0',
            'promotion_start' => 'nullable|date',
            'promotion_end' => 'nullable|date|after_or_equal:promotion_start',
            'is_verified_business' => 'boolean',
            'status' => 'required|in:draft,pending,active,rejected,expired',
            'is_active' => 'boolean',
        ]);

        // Update slug if title changed
        if ($validated['title'] !== $banner->title) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Handle array fields
        if (isset($validated['target_countries'])) {
            $validated['target_countries'] = json_encode($validated['target_countries']);
        }
        if (isset($validated['target_audience'])) {
            $validated['target_audience'] = json_encode($validated['target_audience']);
        }

        $banner->update($validated);

        return redirect()->route('admin.banner.index')
            ->with('success', 'Banner ad updated successfully.');
    }

    /**
     * Remove the specified banner ad.
     */
    public function destroy($id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->delete();

        return redirect()->route('admin.banner.index')
            ->with('success', 'Banner ad deleted successfully.');
    }

    /**
     * Approve the specified banner ad.
     */
    public function approve($id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Banner ad approved successfully.');
    }

    /**
     * Reject the specified banner ad.
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $banner = BannerAd::findOrFail($id);
        $banner->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()
            ->with('success', 'Banner ad rejected successfully.');
    }

    /**
     * Toggle the active status of the specified banner ad.
     */
    public function toggleActive($id)
    {
        $banner = BannerAd::findOrFail($id);
        $banner->update([
            'is_active' => !$banner->is_active,
        ]);

        return redirect()->back()
            ->with('success', 'Banner ad status updated successfully.');
    }

    /**
     * Bulk approve banner ads.
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'banner_ids' => 'required|array',
            'banner_ids.*' => 'exists:banner_ads,id',
        ]);

        BannerAd::whereIn('id', $validated['banner_ids'])->update([
            'status' => 'active',
            'approved_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Banner ads approved successfully.');
    }

    /**
     * Bulk reject banner ads.
     */
    public function bulkReject(Request $request)
    {
        $validated = $request->validate([
            'banner_ids' => 'required|array',
            'banner_ids.*' => 'exists:banner_ads,id',
            'rejection_reason' => 'required|string|max:1000',
        ]);

        BannerAd::whereIn('id', $validated['banner_ids'])->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->back()
            ->with('success', 'Banner ads rejected successfully.');
    }

    /**
     * Display banner analytics.
     */
    public function analytics()
    {
        // Analytics data
        $analytics = [
            'daily_views' => BannerAd::selectRaw('DATE(created_at) as date, SUM(views_count) as views')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'daily_clicks' => BannerAd::selectRaw('DATE(created_at) as date, SUM(clicks_count) as clicks')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'promotion_performance' => BannerAd::selectRaw('promotion_tier, COUNT(*) as count, AVG(views_count) as avg_views, AVG(clicks_count) as avg_clicks')
                ->groupBy('promotion_tier')
                ->get(),
            
            'category_performance' => BannerAd::with('category')
                ->selectRaw('banner_category_id, COUNT(*) as count, AVG(views_count) as avg_views, AVG(clicks_count) as avg_clicks')
                ->groupBy('banner_category_id')
                ->get(),
        ];

        return view('admin.banner.analytics', compact('analytics'));
    }

    /**
     * Export banner ads.
     */
    public function export(Request $request)
    {
        $query = BannerAd::with(['category', 'user']);

        // Apply filters (same as index method)
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->has('promotion_tier') && $request->promotion_tier !== 'all') {
            $query->where('promotion_tier', $request->promotion_tier);
        }
        if ($request->has('category_id') && $request->category_id) {
            $query->where('banner_category_id', $request->category_id);
        }

        $banners = $query->get();

        $csvData = [];
        $csvData[] = ['ID', 'Title', 'Business Name', 'Email', 'Category', 'Status', 'Promotion Tier', 'Views', 'Clicks', 'CTR', 'Created At'];

        foreach ($banners as $banner) {
            $ctr = $banner->views_count > 0 ? ($banner->clicks_count / $banner->views_count) * 100 : 0;
            $csvData[] = [
                $banner->id,
                $banner->title,
                $banner->business_name,
                $banner->email,
                $banner->category ? $banner->category->name : 'N/A',
                $banner->status,
                $banner->promotion_tier,
                $banner->views_count,
                $banner->clicks_count,
                round($ctr, 2) . '%',
                $banner->created_at->format('Y-m-d H:i:s')
            ];
        }

        $filename = 'banner_ads_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, 200, $headers);
    }

    /**
     * Display system health information.
     */
    public function systemHealth()
    {
        $health = [
            'total_banners' => BannerAd::count(),
            'active_banners' => BannerAd::where('status', 'active')->count(),
            'pending_approval' => BannerAd::where('status', 'pending')->count(),
            'expired_banners' => BannerAd::where('status', 'expired')->count(),
            'categories' => BannerCategory::count(),
            'storage_usage' => $this->getStorageUsage(),
            'recent_activity' => BannerAd::orderBy('updated_at', 'desc')->limit(10)->get(),
        ];

        return view('admin.banner.health', compact('health'));
    }

    /**
     * Get storage usage statistics.
     */
    private function getStorageUsage()
    {
        $bannerPath = public_path('storage/banner-images');
        $logoPath = public_path('storage/business-logos');
        
        $bannerSize = is_dir($bannerPath) ? $this->getDirectorySize($bannerPath) : 0;
        $logoSize = is_dir($logoPath) ? $this->getDirectorySize($logoPath) : 0;
        
        return [
            'banner_images' => $bannerSize,
            'business_logos' => $logoSize,
            'total' => $bannerSize + $logoSize,
        ];
    }

    /**
     * Calculate directory size.
     */
    private function getDirectorySize($dir)
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->getDirectorySize($each);
        }
        return $size;
    }
}
