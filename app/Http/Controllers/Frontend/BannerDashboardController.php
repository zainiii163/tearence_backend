<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use App\Models\BannerCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BannerDashboardController extends Controller
{
    /**
     * Display the user's banner dashboard.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access your dashboard.');
        }

        $user = Auth::user();
        
        // Get user's banner statistics
        $totalBanners = BannerAd::where('user_id', $user->user_id)->count();
        $activeBanners = BannerAd::where('user_id', $user->user_id)->where('status', 'active')->count();
        $totalViews = BannerAd::where('user_id', $user->user_id)->sum('views_count');
        $totalClicks = BannerAd::where('user_id', $user->user_id)->sum('clicks_count');
        
        // Calculate average CTR
        $avgCtr = $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0;
        
        // Get recent banners
        $recentBanners = BannerAd::with('category')
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get promotion statistics
        $promotionStats = BannerAd::where('user_id', $user->user_id)
            ->selectRaw('promotion_tier, COUNT(*) as count')
            ->groupBy('promotion_tier')
            ->pluck('count', 'promotion_tier')
            ->toArray();

        return view('frontend.banners.dashboard', compact(
            'totalBanners',
            'activeBanners', 
            'totalViews',
            'totalClicks',
            'avgCtr',
            'recentBanners',
            'promotionStats'
        ));
    }

    /**
     * Display the user's banners list.
     */
    public function myBanners(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access your banners.');
        }

        $user = Auth::user();
        
        $query = BannerAd::with('category')
            ->where('user_id', $user->user_id);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('promotion_tier') && $request->promotion_tier !== 'all') {
            $query->where('promotion_tier', $request->promotion_tier);
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('banner_category_id', $request->category);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $banners = $query->paginate(10);
        $categories = BannerCategory::where('is_active', true)->orderBy('name')->get();

        return view('frontend.banners.my-banners', compact('banners', 'categories'));
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to create a banner.');
        }

        $categories = BannerCategory::where('is_active', true)->orderBy('name')->get();
        $countries = $this->getCountries();

        return view('frontend.banners.create', compact('categories', 'countries'));
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:500',
            'banner_category_id' => 'required|exists:banner_categories,id',
            'banner_type' => 'required|in:image,animated,html5,video',
            'banner_size' => 'required|in:728x90,300x250,160x600,970x250,468x60,1080x1080',
            'banner_image' => 'required|string|max:255',
            'destination_link' => 'required|url|max:500',
            'call_to_action' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'target_countries' => 'nullable|array',
            'target_audience' => 'nullable|array',
            'promotion_tier' => 'required|in:standard,promoted,featured,sponsored,network_boost',
            'terms_accepted' => 'required|accepted',
            'privacy_accepted' => 'required|accepted',
        ]);

        // Add user ID and generate slug
        $validated['user_id'] = $user->user_id;
        $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']) . '-' . time() . rand(1000, 9999);
        $validated['status'] = 'pending';
        $validated['is_active'] = false;

        // Convert arrays to JSON
        if (isset($validated['target_countries'])) {
            $validated['target_countries'] = json_encode($validated['target_countries']);
        }
        if (isset($validated['target_audience'])) {
            $validated['target_audience'] = json_encode($validated['target_audience']);
        }

        try {
            $banner = BannerAd::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Banner submitted successfully! It will be reviewed before going live.',
                'banner' => $banner
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to edit your banner.');
        }

        $user = Auth::user();
        $banner = BannerAd::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        $categories = BannerCategory::where('is_active', true)->orderBy('name')->get();
        $countries = $this->getCountries();

        return view('frontend.banners.edit', compact('banner', 'categories', 'countries'));
    }

    /**
     * Update the specified banner.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        $banner = BannerAd::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:500',
            'banner_category_id' => 'required|exists:banner_categories,id',
            'banner_type' => 'required|in:image,animated,html5,video',
            'banner_size' => 'required|in:728x90,300x250,160x600,970x250,468x60,1080x1080',
            'banner_image' => 'required|string|max:255',
            'destination_link' => 'required|url|max:500',
            'call_to_action' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'target_countries' => 'nullable|array',
            'target_audience' => 'nullable|array',
            'promotion_tier' => 'required|in:standard,promoted,featured,sponsored,network_boost',
        ]);

        // Convert arrays to JSON
        if (isset($validated['target_countries'])) {
            $validated['target_countries'] = json_encode($validated['target_countries']);
        }
        if (isset($validated['target_audience'])) {
            $validated['target_audience'] = json_encode($validated['target_audience']);
        }

        try {
            $banner->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Banner updated successfully!',
                'banner' => $banner
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified banner.
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        $banner = BannerAd::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        try {
            $banner->delete();

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show banner analytics.
     */
    public function analytics($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to view analytics.');
        }

        $user = Auth::user();
        $banner = BannerAd::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        // Get daily views for the last 30 days
        $dailyViews = DB::table('banner_analytics')
            ->where('banner_id', $id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get device breakdown (mock data for now)
        $deviceBreakdown = [
            'desktop' => 60,
            'mobile' => 35,
            'tablet' => 5
        ];

        return view('frontend.banners.analytics', compact('banner', 'dailyViews', 'deviceBreakdown'));
    }

    /**
     * Get list of countries for dropdown.
     */
    private function getCountries()
    {
        return [
            'USA' => 'United States',
            'Canada' => 'Canada',
            'UK' => 'United Kingdom',
            'Australia' => 'Australia',
            'Germany' => 'Germany',
            'France' => 'France',
            'Italy' => 'Italy',
            'Spain' => 'Spain',
            'Netherlands' => 'Netherlands',
            'Belgium' => 'Belgium',
            'Switzerland' => 'Switzerland',
            'Austria' => 'Austria',
            'Sweden' => 'Sweden',
            'Norway' => 'Norway',
            'Denmark' => 'Denmark',
            'Finland' => 'Finland',
            'Poland' => 'Poland',
            'Czech Republic' => 'Czech Republic',
            'Hungary' => 'Hungary',
            'Romania' => 'Romania',
            'Bulgaria' => 'Bulgaria',
            'Greece' => 'Greece',
            'Portugal' => 'Portugal',
            'Ireland' => 'Ireland',
            'Iceland' => 'Iceland',
            'Luxembourg' => 'Luxembourg',
            'Estonia' => 'Estonia',
            'Latvia' => 'Latvia',
            'Lithuania' => 'Lithuania',
            'Malta' => 'Malta',
            'Cyprus' => 'Cyprus',
            'Slovakia' => 'Slovakia',
            'Slovenia' => 'Slovenia',
            'Croatia' => 'Croatia',
            'Japan' => 'Japan',
            'South Korea' => 'South Korea',
            'China' => 'China',
            'India' => 'India',
            'Singapore' => 'Singapore',
            'Malaysia' => 'Malaysia',
            'Thailand' => 'Thailand',
            'Indonesia' => 'Indonesia',
            'Philippines' => 'Philippines',
            'Vietnam' => 'Vietnam',
            'New Zealand' => 'New Zealand',
            'South Africa' => 'South Africa',
            'Egypt' => 'Egypt',
            'Israel' => 'Israel',
            'Turkey' => 'Turkey',
            'UAE' => 'United Arab Emirates',
            'Saudi Arabia' => 'Saudi Arabia',
            'Brazil' => 'Brazil',
            'Argentina' => 'Argentina',
            'Chile' => 'Chile',
            'Colombia' => 'Colombia',
            'Peru' => 'Peru',
            'Mexico' => 'Mexico',
            'Russia' => 'Russia',
            'Ukraine' => 'Ukraine',
            'Belarus' => 'Belarus',
            'Kazakhstan' => 'Kazakhstan',
        ];
    }
}
