<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookPurchase;
use App\Models\CustomerNotification;
use App\Models\Listing;
use App\Models\ListingFavorite;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Clive-facing dashboard insights: sold, favourites, messages stub, ending promos, notifications.
 */
class UserInsightsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function customerId(): int
    {
        $user = auth('api')->user();
        return (int) ($user->customer_id ?? $user->id);
    }

    public function __invoke(Request $request)
    {
        $customerId = $this->customerId();
        $now = now();
        $in7 = now()->addDays(7);

        $soldItems = 0;
        $soldRevenue = 0.0;
        $platformFees = 0.0;

        if (Schema::hasTable('service_orders')) {
            $completed = ServiceOrder::query()
                ->where('seller_id', $customerId)
                ->where('status', 'completed');
            $soldItems += (clone $completed)->count();
            $soldRevenue += (float) (clone $completed)->sum('seller_amount')
                ?: (float) (clone $completed)->sum('total_price');
            $platformFees += (float) (clone $completed)->sum('platform_fee');
        }

        if (Schema::hasTable('book_purchases')) {
            $booksSold = BookPurchase::query()
                ->whereHas('listing', fn ($q) => $q->where('customer_id', $customerId))
                ->where('payment_status', 'completed');
            $soldItems += (clone $booksSold)->count();
            $soldRevenue += (float) (clone $booksSold)->sum('seller_amount')
                ?: (float) (clone $booksSold)->sum('price_paid');
            $platformFees += (float) (clone $booksSold)->sum('platform_fee');
        }

        $favouritesSaved = 0;
        if (Schema::hasTable('listing_favorites')) {
            $favouritesSaved = ListingFavorite::where('customer_id', $customerId)->count();
        } elseif (Schema::hasTable('listing_favourite')) {
            $favouritesSaved = \DB::table('listing_favourite')->where('customer_id', $customerId)->count();
        }

        $favouritesReceived = (int) Listing::where('customer_id', $customerId)->sum('saves');

        $endingPromotions = collect();
        try {
            $endingPromotions = Listing::where('customer_id', $customerId)
                ->where(function ($q) use ($now, $in7) {
                    $q->where(function ($q2) use ($now, $in7) {
                        $q2->whereNotNull('featured_expires_at')
                            ->whereBetween('featured_expires_at', [$now, $in7]);
                    })->orWhere(function ($q2) use ($now, $in7) {
                        $q2->whereNotNull('promoted_expires_at')
                            ->whereBetween('promoted_expires_at', [$now, $in7]);
                    })->orWhere(function ($q2) use ($now, $in7) {
                        $q2->whereNotNull('end_date')
                            ->whereBetween('end_date', [$now, $in7]);
                    });
                })
                ->orderByDesc('updated_at')
                ->limit(20)
                ->get(['listing_id', 'title', 'featured_expires_at', 'promoted_expires_at', 'end_date', 'is_featured']);
        } catch (\Throwable $e) {
            $endingPromotions = collect();
        }

        $unreadNotifications = 0;
        $recentNotifications = collect();
        try {
            $unreadNotifications = CustomerNotification::unreadCount($customerId);
            $recentNotifications = CustomerNotification::where('customer_id', $customerId)
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        } catch (\Throwable $e) {
            // table may not be migrated yet
        }

        $isBusiness = (auth('api')->user()->user_type ?? 'basic') === 'business';

        return response()->json([
            'success' => true,
            'data' => [
                'account_type' => $isBusiness ? 'business' : 'personal',
                'platform_fee_percent' => (float) config('commerce.platform_fee_percent', 15),
                'sales' => [
                    'sold_items' => $soldItems,
                    'seller_revenue' => round($soldRevenue, 2),
                    'platform_fees_paid' => round($platformFees, 2),
                ],
                'favourites' => [
                    'saved_by_you' => $favouritesSaved,
                    'received_on_listings' => $favouritesReceived,
                ],
                'messages' => [
                    'unread' => 0,
                    'note' => 'Chat inbox available at /messages',
                ],
                'ending_promotions' => $endingPromotions,
                'notifications' => [
                    'unread' => $unreadNotifications,
                    'recent' => $recentNotifications,
                ],
                'two_factor_enabled' => !empty(auth('api')->user()->two_factor_confirmed_at),
            ],
        ]);
    }
}
