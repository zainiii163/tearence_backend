<?php

namespace App\Http\Controllers;

use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use App\Models\BuySellPromotionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BuySellController extends Controller
{
    public function index()
    {
        $categories = BuySellCategory::with(['children' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
        }])
        ->where('parent_id', null)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        $featuredAdverts = BuySellAdvert::with(['category', 'subcategory'])
            ->active()
            ->where('featured', true)
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        $recentAdverts = BuySellAdvert::with(['category', 'subcategory'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return view('buysell.index', compact('categories', 'featuredAdverts', 'recentAdverts'));
    }

    public function browse(Request $request)
    {
        $query = BuySellAdvert::with(['category', 'subcategory', 'user'])
            ->active();

        // Apply filters
        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        if ($request->subcategory) {
            $query->where('subcategory_id', $request->subcategory);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('description', 'LIKE', "%{$request->search}%")
                  ->orWhere('brand', 'LIKE', "%{$request->search}%")
                  ->orWhere('model', 'LIKE', "%{$request->search}%");
            });
        }

        if ($request->condition) {
            $query->where('condition', $request->condition);
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        // Sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        if (in_array($sortBy, ['created_at', 'price', 'views_count', 'title'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $adverts = $query->paginate(20);
        $categories = BuySellCategory::where('parent_id', null)->where('is_active', true)->get();

        return view('buysell.browse', compact('adverts', 'categories'));
    }

    public function show($id)
    {
        $advert = BuySellAdvert::with(['category', 'subcategory', 'user'])
            ->active()
            ->findOrFail($id);

        // Track view
        if (Auth::check()) {
            $advert->incrementView(Auth::id(), request()->ip(), request()->userAgent(), request()->header('referer'));
        } else {
            $advert->incrementView(null, request()->ip(), request()->userAgent(), request()->header('referer'));
        }

        // Get related adverts
        $relatedAdverts = BuySellAdvert::with(['category'])
            ->active()
            ->where('category_id', $advert->category_id)
            ->where('id', '!=', $advert->id)
            ->limit(6)
            ->get();

        return view('buysell.show', compact('advert', 'relatedAdverts'));
    }

    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        $stats = [
            'total_adverts' => BuySellAdvert::where('user_id', $user->id)->count(),
            'active_adverts' => BuySellAdvert::where('user_id', $user->id)->active()->count(),
            'total_views' => BuySellAdvert::where('user_id', $user->id)->sum('views_count'),
            'total_saves' => BuySellAdvert::where('user_id', $user->id)->sum('saves_count'),
        ];

        $recentAdverts = BuySellAdvert::where('user_id', $user->id)
            ->with(['category'])
            ->latest()
            ->limit(5)
            ->get();

        return view('buysell.dashboard', compact('stats', 'recentAdverts'));
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $categories = BuySellCategory::with(['children' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
        }])
        ->where('parent_id', null)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        return view('buysell.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10|max:5000',
            'category_id' => 'required|uuid|exists:buysell_categories,id',
            'subcategory_id' => 'nullable|uuid|exists:buysell_categories,id',
            'condition' => 'required|in:new,like_new,excellent,good,fair,poor',
            'price' => 'required|numeric|min:0|max:999999.99',
            'negotiable' => 'boolean',
            'currency' => 'string|size:3',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'dimensions' => 'nullable|string|max:200',
            'weight' => 'nullable|numeric|min:0',
            'material' => 'nullable|string|max:100',
            'usage_duration' => 'nullable|string|max:100',
            'reason_for_selling' => 'nullable|string|max:1000',
            'seller_name' => 'required|string|max:255',
            'seller_email' => 'required|email|max:255',
            'seller_phone' => 'nullable|string|max:50',
            'seller_website' => 'nullable|url|max:255',
            'verified_seller' => 'boolean',
            'show_phone' => 'boolean',
            'preferred_contact' => 'required|in:email,phone,website',
            'images' => 'array|max:15',
            'images.*' => 'url|max:500',
            'video_url' => 'nullable|url|max:500',
            'promotion_plan' => 'nullable|string|max:50',
        ]);

        $data = array_merge($validated, [
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $advert = BuySellAdvert::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Advert created successfully',
            'advert' => $advert->load(['category', 'subcategory'])
        ]);
    }

    public function edit($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $advert = BuySellAdvert::where('user_id', Auth::id())->findOrFail($id);
        
        $categories = BuySellCategory::with(['children' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
        }])
        ->where('parent_id', null)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

        return view('buysell.edit', compact('advert', 'categories'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $advert = BuySellAdvert::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|min:3|max:255',
            'description' => 'sometimes|string|min:10|max:5000',
            'category_id' => 'sometimes|uuid|exists:buysell_categories,id',
            'subcategory_id' => 'nullable|uuid|exists:buysell_categories,id',
            'condition' => 'sometimes|in:new,like_new,excellent,good,fair,poor',
            'price' => 'sometimes|numeric|min:0|max:999999.99',
            'negotiable' => 'boolean',
            'currency' => 'string|size:3',
            'country' => 'sometimes|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'dimensions' => 'nullable|string|max:200',
            'weight' => 'nullable|numeric|min:0',
            'material' => 'nullable|string|max:100',
            'usage_duration' => 'nullable|string|max:100',
            'reason_for_selling' => 'nullable|string|max:1000',
            'seller_name' => 'sometimes|string|max:255',
            'seller_email' => 'sometimes|email|max:255',
            'seller_phone' => 'nullable|string|max:50',
            'seller_website' => 'nullable|url|max:255',
            'verified_seller' => 'boolean',
            'show_phone' => 'boolean',
            'preferred_contact' => 'sometimes|in:email,phone,website',
            'images' => 'array|max:15',
            'images.*' => 'url|max:500',
            'video_url' => 'nullable|url|max:500',
            'status' => 'sometimes|in:active,inactive,expired',
        ]);

        $advert->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Advert updated successfully',
            'advert' => $advert->load(['category', 'subcategory'])
        ]);
    }

    public function myAdverts()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $adverts = BuySellAdvert::where('user_id', Auth::id())
            ->with(['category', 'subcategory'])
            ->latest()
            ->paginate(20);

        return view('buysell.my-adverts', compact('adverts'));
    }

    public function savedAdverts()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $savedAdverts = Auth::user()->buySellSavedAdverts()
            ->with(['advert.category', 'advert.subcategory'])
            ->latest()
            ->paginate(20);

        return view('buysell.saved-adverts', compact('savedAdverts'));
    }

    public function promotionPlans()
    {
        $plans = BuySellPromotionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('buysell.promotion-plans', compact('plans'));
    }
}
