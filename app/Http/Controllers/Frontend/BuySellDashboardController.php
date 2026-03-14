<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use App\Models\BuySellPromotionPlan;
use App\Models\BuySellSavedAdvert;
use App\Models\BuySellAdvertView;
use App\Models\BuySellFavorite;
use App\Models\BuySellEnquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BuySellDashboardController extends Controller
{
    /**
     * Display the user's Buy & Sell dashboard.
     */
    public function dashboard(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access your dashboard.');
        }

        $user = Auth::user();
        
        // Get user's advert statistics
        $totalAdverts = BuySellAdvert::where('user_id', $user->user_id)->count();
        $activeAdverts = BuySellAdvert::where('user_id', $user->user_id)->where('is_active', true)->count();
        $soldAdverts = BuySellAdvert::where('user_id', $user->user_id)->where('status', 'sold')->count();
        $expiredAdverts = BuySellAdvert::where('user_id', $user->user_id)->where('status', 'expired')->count();
        
        // Get view and engagement statistics
        $totalViews = BuySellAdvertView::whereIn('advert_id', 
            BuySellAdvert::where('user_id', $user->user_id)->pluck('id')
        )->count();
        
        $totalFavorites = BuySellFavorite::whereIn('advert_id', 
            BuySellAdvert::where('user_id', $user->user_id)->pluck('id')
        )->count();
        
        $totalEnquiries = BuySellEnquiry::whereIn('advert_id', 
            BuySellAdvert::where('user_id', $user->user_id)->pluck('id')
        )->count();
        
        // Get recent adverts
        $recentAdverts = BuySellAdvert::with(['category', 'subcategory'])
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Get category statistics
        $categoryStats = BuySellAdvert::where('user_id', $user->user_id)
            ->join('buysell_categories', 'buysell_adverts.category_id', '=', 'buysell_categories.id')
            ->selectRaw('buysell_categories.name, COUNT(*) as count')
            ->groupBy('buysell_categories.name')
            ->pluck('count', 'name')
            ->toArray();

        // Get promotion statistics
        $promotionStats = BuySellAdvert::where('user_id', $user->user_id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('frontend.buysell.dashboard', compact(
            'totalAdverts',
            'activeAdverts', 
            'soldAdverts',
            'expiredAdverts',
            'totalViews',
            'totalFavorites',
            'totalEnquiries',
            'recentAdverts',
            'categoryStats',
            'promotionStats'
        ));
    }

    /**
     * Display the user's adverts list.
     */
    public function myAdverts(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access your adverts.');
        }

        $user = Auth::user();
        
        $query = BuySellAdvert::with(['category', 'subcategory'])
            ->where('user_id', $user->user_id);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category_id', $request->category);
        }

        if ($request->has('condition') && $request->condition !== 'all') {
            $query->where('condition', $request->condition);
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $adverts = $query->paginate(10);
        $categories = BuySellCategory::where('is_active', true)->whereNull('parent_id')->orderBy('name')->get();

        return view('frontend.buysell.my-adverts', compact('adverts', 'categories'));
    }

    /**
     * Show the form for creating a new advert.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to create an advert.');
        }

        $categories = BuySellCategory::with(['children' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        $promotionPlans = BuySellPromotionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $countries = $this->getCountries();

        return view('frontend.buysell.create', compact('categories', 'promotionPlans', 'countries'));
    }

    /**
     * Store a newly created advert.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'currency' => 'sometimes|string|max:3',
            'category_id' => 'required|exists:buysell_categories,id',
            'subcategory_id' => 'nullable|exists:buysell_categories,id',
            'condition' => 'required|in:new,like_new,good,fair,poor',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'preferred_contact' => 'nullable|in:phone,email,whatsapp',
            'promotion_plan_id' => 'nullable|exists:buysell_promotion_plans,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string',
            'terms_accepted' => 'required|accepted',
        ]);

        // Add user ID and generate slug
        $validated['user_id'] = $user->user_id;
        $validated['slug'] = Str::slug($validated['title']) . '-' . time() . rand(1000, 9999);
        $validated['status'] = 'active';
        $validated['is_active'] = true;
        $validated['expires_at'] = now()->addDays(90); // Default 90 days

        try {
            $advert = BuySellAdvert::create($validated);

            // Handle promotion if selected
            if (!empty($validated['promotion_plan_id'])) {
                $this->createPromotion($advert, $validated['promotion_plan_id']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Advert created successfully!',
                'advert' => $advert->load(['category', 'subcategory'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create advert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified advert.
     */
    public function edit($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to edit your advert.');
        }

        $user = Auth::user();
        $advert = BuySellAdvert::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        $categories = BuySellCategory::with(['children' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order');
        }])
        ->whereNull('parent_id')
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

        $promotionPlans = BuySellPromotionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $countries = $this->getCountries();

        return view('frontend.buysell.edit', compact('advert', 'categories', 'promotionPlans', 'countries'));
    }

    /**
     * Update the specified advert.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        $advert = BuySellAdvert::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'price' => 'required|numeric|min:0',
            'currency' => 'sometimes|string|max:3',
            'category_id' => 'required|exists:buysell_categories,id',
            'subcategory_id' => 'nullable|exists:buysell_categories,id',
            'condition' => 'required|in:new,like_new,good,fair,poor',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'preferred_contact' => 'nullable|in:phone,email,whatsapp',
            'images' => 'nullable|array|max:10',
            'images.*' => 'string',
        ]);

        try {
            $advert->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Advert updated successfully!',
                'advert' => $advert->load(['category', 'subcategory'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update advert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified advert.
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        $user = Auth::user();
        $advert = BuySellAdvert::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        try {
            $advert->delete();

            return response()->json([
                'success' => true,
                'message' => 'Advert deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete advert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show saved adverts.
     */
    public function savedAdverts(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to view saved adverts.');
        }

        $user = Auth::user();
        
        $savedAdverts = BuySellSavedAdvert::with(['advert.category', 'advert.subcategory', 'advert.user'])
            ->where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('frontend.buysell.saved-adverts', compact('savedAdverts'));
    }

    /**
     * Show advert analytics.
     */
    public function analytics($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to view analytics.');
        }

        $user = Auth::user();
        $advert = BuySellAdvert::where('id', $id)->where('user_id', $user->user_id)->firstOrFail();

        // Get daily views for the last 30 days
        $dailyViews = BuySellAdvertView::where('advert_id', $id)
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

        // Get location breakdown
        $locationBreakdown = BuySellAdvertView::where('advert_id', $id)
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return view('frontend.buysell.analytics', compact('advert', 'dailyViews', 'deviceBreakdown', 'locationBreakdown'));
    }

    /**
     * Create promotion for advert.
     */
    private function createPromotion($advert, $promotionPlanId)
    {
        $plan = BuySellPromotionPlan::findOrFail($promotionPlanId);
        
        $advert->promotions()->create([
            'plan_id' => $promotionPlanId,
            'price' => $plan->price,
            'starts_at' => now(),
            'expires_at' => now()->addDays($plan->duration_days),
            'is_active' => true,
        ]);

        // Update advert status based on promotion
        if ($plan->slug === 'featured') {
            $advert->featured = true;
        } elseif ($plan->slug === 'urgent') {
            $advert->urgent = true;
        } elseif ($plan->slug === 'promoted') {
            $advert->promoted = true;
        }
        
        $advert->expires_at = now()->addDays($plan->duration_days);
        $advert->save();
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
