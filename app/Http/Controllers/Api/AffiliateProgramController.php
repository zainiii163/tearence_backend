<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProgram;
use App\Models\AffiliateClick;
use App\Models\AffiliateConversion;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AffiliateProgramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AffiliateProgram::with(['user', 'category'])
            ->active()
            ->approved();

        // Filter by type
        if ($request->affiliate_type) {
            $query->byType($request->affiliate_type);
        }

        // Filter by network
        if ($request->affiliate_network) {
            $query->byNetwork($request->affiliate_network);
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', "%{$request->search}%")
                  ->orWhere('description', 'LIKE', "%{$request->search}%")
                  ->orWhere('program_name', 'LIKE', "%{$request->search}%");
            });
        }

        // Sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';

        switch ($sortBy) {
            case 'earnings':
                $query->orderBy('total_earnings', 'desc');
                break;
            case 'conversions':
                $query->orderBy('conversions_count', 'desc');
                break;
            case 'clicks':
                $query->orderBy('clicks_count', 'desc');
                break;
            case 'featured':
                $query->featured()->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        $programs = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }

    public function show(AffiliateProgram $program): JsonResponse
    {
        $program->load(['user', 'category', 'clicks' => function ($query) {
            $query->latest()->limit(10);
        }, 'conversions' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $program,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,category_id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'affiliate_type' => 'required|in:user_link,program_join,product_promotion',
            'program_name' => 'required|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'affiliate_network' => 'required|in:amazon,clickbank,shareasale,commission_junction,rakuten,independent,our_program',
            'product_category' => 'required|string|max:255',
            'promotion_method' => 'required|in:link_sharing,review,tutorial,social_media,email_marketing',
            'affiliate_link' => 'nullable|url',
        ]);

        $program = AffiliateProgram::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'affiliate_type' => $request->affiliate_type,
            'program_name' => $request->program_name,
            'commission_rate' => $request->commission_rate,
            'affiliate_network' => $request->affiliate_network,
            'product_category' => $request->product_category,
            'promotion_method' => $request->promotion_method,
            'affiliate_link' => $request->affiliate_link,
            'is_active' => true,
            'approved' => $request->affiliate_network === 'our_program' ? false : true, // Our program needs approval
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate program created successfully',
            'data' => $program,
        ], 201);
    }

    public function update(Request $request, AffiliateProgram $program): JsonResponse
    {
        if ($program->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'affiliate_link' => 'nullable|url',
        ]);

        $program->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Affiliate program updated successfully',
            'data' => $program,
        ]);
    }

    public function destroy(AffiliateProgram $program): JsonResponse
    {
        if ($program->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $program->delete();

        return response()->json([
            'success' => true,
            'message' => 'Affiliate program deleted successfully',
        ]);
    }

    public function myPrograms(Request $request): JsonResponse
    {
        $programs = AffiliateProgram::with(['category', 'clicks', 'conversions'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }

    public function toggleStatus(AffiliateProgram $program): JsonResponse
    {
        if ($program->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $program->is_active = !$program->is_active;
        $program->save();

        return response()->json([
            'success' => true,
            'message' => 'Program status updated successfully',
            'data' => $program,
        ]);
    }

    public function trackClick(AffiliateProgram $program, Request $request): JsonResponse
    {
        // Get user location data
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $referrer = $request->header('referer');

        // Create click record
        $click = AffiliateClick::create([
            'affiliate_program_id' => $program->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referrer' => $referrer,
            'country' => $request->country ?? 'Unknown',
            'city' => $request->city ?? 'Unknown',
            'clicked_at' => now(),
        ]);

        // Increment program clicks
        $program->incrementClicks();

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
            'data' => [
                'click_id' => $click->id,
                'affiliate_link' => $program->affiliate_link,
            ],
        ]);
    }

    public function recordConversion(Request $request): JsonResponse
    {
        $request->validate([
            'program_id' => 'required|exists:affiliate_programs,id',
            'click_id' => 'required|exists:affiliate_clicks,id',
            'amount' => 'required|numeric|min:0',
            'conversion_type' => 'required|in:sale,lead,signup,download,other',
            'product_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $program = AffiliateProgram::findOrFail($request->program_id);
        $click = AffiliateClick::findOrFail($request->click_id);

        // Calculate commission
        $commissionRate = $program->commission_rate ?? 0;
        $commissionAmount = ($request->amount * $commissionRate) / 100;

        DB::beginTransaction();
        try {
            $conversion = AffiliateConversion::create([
                'affiliate_program_id' => $program->id,
                'click_id' => $click->id,
                'amount' => $request->amount,
                'commission_rate' => $commissionRate,
                'commission_amount' => $commissionAmount,
                'conversion_type' => $request->conversion_type,
                'product_name' => $request->product_name,
                'customer_email' => $request->customer_email,
                'transaction_id' => $request->transaction_id,
                'converted_at' => now(),
                'status' => 'pending',
            ]);

            // Increment program conversions and earnings
            $program->incrementConversions();
            $program->addEarnings($commissionAmount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conversion recorded successfully',
                'data' => $conversion,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record conversion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProgramStats(): JsonResponse
    {
        $userId = Auth::id();

        $stats = [
            'total_programs' => AffiliateProgram::where('user_id', $userId)->count(),
            'active_programs' => AffiliateProgram::where('user_id', $userId)->active()->count(),
            'total_clicks' => AffiliateProgram::where('user_id', $userId)->sum('clicks_count'),
            'total_conversions' => AffiliateProgram::where('user_id', $userId)->sum('conversions_count'),
            'total_earnings' => AffiliateProgram::where('user_id', $userId)->sum('total_earnings'),
            'pending_conversions' => AffiliateConversion::whereHas('affiliateProgram', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->pending()->count(),
        ];

        // Calculate average conversion rate
        $totalClicks = $stats['total_clicks'];
        $totalConversions = $stats['total_conversions'];
        $stats['average_conversion_rate'] = $totalClicks > 0 ? ($totalConversions / $totalClicks) * 100 : 0;

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function getNetworks(): JsonResponse
    {
        $networks = [
            'amazon' => 'Amazon',
            'clickbank' => 'ClickBank',
            'shareasale' => 'ShareASale',
            'commission_junction' => 'Commission Junction',
            'rakuten' => 'Rakuten',
            'independent' => 'Independent Program',
            'our_program' => 'Our Referral Program',
        ];

        return response()->json([
            'success' => true,
            'data' => $networks,
        ]);
    }

    public function getFeaturedPrograms(): JsonResponse
    {
        $programs = AffiliateProgram::with(['user', 'category'])
            ->active()
            ->approved()
            ->featured()
            ->orderBy('total_earnings', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }

    public function joinOurProgram(Request $request): JsonResponse
    {
        $request->validate([
            'program_name' => 'required|string|max:255',
            'description' => 'required|string',
            'promotion_method' => 'required|in:link_sharing,review,tutorial,social_media,email_marketing',
            'product_category' => 'required|string|max:255',
        ]);

        // Create a program for our referral system
        $program = AffiliateProgram::create([
            'user_id' => Auth::id(),
            'category_id' => Category::where('name', 'Affiliate Programs')->first()->category_id,
            'title' => 'Join Our Referral Program',
            'description' => $request->description,
            'affiliate_type' => 'program_join',
            'program_name' => $request->program_name,
            'commission_rate' => 10.00, // Default 10% commission
            'affiliate_network' => 'our_program',
            'product_category' => $request->product_category,
            'promotion_method' => $request->promotion_method,
            'is_active' => true,
            'approved' => false, // Needs admin approval
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully. Pending approval.',
            'data' => $program,
        ], 201);
    }
}
