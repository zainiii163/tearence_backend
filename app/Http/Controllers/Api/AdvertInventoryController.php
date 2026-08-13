<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BannerAd;
use App\Models\BusinessAffiliateOffer;
use App\Models\FeaturedAdvert;
use App\Models\PromotedAdvert;
use App\Models\SponsoredAdvert;
use App\Models\UserAffiliatePost;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Clive: business/user advert inventory across formats + expiry.
 */
class AdvertInventoryController extends Controller
{
    public function mine(Request $request): JsonResponse
    {
        $user = Auth::user() ?? auth('api')->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $userId = $user->user_id ?? $user->id;
        $customerId = $user->customer_id ?? null;
        $items = [];

        if (Schema::hasTable('featured_adverts')) {
            $q = FeaturedAdvert::query()->orderByDesc('id')->limit(50);
            if ($customerId && Schema::hasColumn('featured_adverts', 'customer_id')) {
                $q->where('customer_id', $customerId);
            } elseif (Schema::hasColumn('featured_adverts', 'user_id')) {
                $q->where('user_id', $userId);
            }
            foreach ($q->get() as $row) {
                $items[] = $this->row(
                    'featured',
                    $row->id,
                    $row->title ?? 'Featured advert',
                    $row->payment_status ?? ($row->is_active ? 'active' : 'inactive'),
                    $row->expires_at,
                    $row->starts_at,
                    [
                        'edit_path' => '/dashboard?tab=featured',
                        'repost_formats' => ['free', 'paid', 'sponsored', 'promoted', 'banner', 'affiliate'],
                    ]
                );
            }
        }

        if (Schema::hasTable('sponsored_adverts')) {
            $q = SponsoredAdvert::query()->orderByDesc('id')->limit(50);
            if (Schema::hasColumn('sponsored_adverts', 'created_by')) {
                $q->where('created_by', $userId);
            } elseif (Schema::hasColumn('sponsored_adverts', 'user_id')) {
                $q->where('user_id', $userId);
            }
            foreach ($q->get() as $row) {
                $items[] = $this->row(
                    'sponsored',
                    $row->sponsored_advert_id ?? $row->id,
                    $row->title ?? 'Sponsored advert',
                    $row->payment_status ?? ($row->is_active ? 'active' : 'inactive'),
                    $row->sponsorship_end_date ?? $row->expires_at ?? null,
                    $row->sponsorship_start_date ?? $row->starts_at ?? null,
                    ['edit_path' => '/dashboard?tab=sponsored']
                );
            }
        }

        if (Schema::hasTable('promoted_adverts')) {
            $q = PromotedAdvert::query()->orderByDesc('id')->limit(50);
            if (Schema::hasColumn('promoted_adverts', 'user_id')) {
                $q->where('user_id', $userId);
            } elseif (Schema::hasColumn('promoted_adverts', 'created_by')) {
                $q->where('created_by', $userId);
            }
            foreach ($q->get() as $row) {
                $items[] = $this->row(
                    'promoted',
                    $row->id,
                    $row->title ?? 'Promoted advert',
                    $row->payment_status ?? ($row->is_active ? 'active' : 'inactive'),
                    $row->promotion_end ?? $row->expires_at ?? null,
                    $row->promotion_start ?? null,
                    ['edit_path' => '/dashboard?tab=promoted']
                );
            }
        }

        if (Schema::hasTable('banner_ads')) {
            $q = BannerAd::query()->orderByDesc('id')->limit(50);
            if (Schema::hasColumn('banner_ads', 'user_id')) {
                $q->where('user_id', $userId);
            } elseif (Schema::hasColumn('banner_ads', 'customer_id') && $customerId) {
                $q->where('customer_id', $customerId);
            }
            foreach ($q->get() as $row) {
                $items[] = $this->row(
                    'banner',
                    $row->id,
                    $row->title ?? 'Banner ad',
                    $row->payment_status ?? ($row->is_active ? 'active' : 'inactive'),
                    $row->promotion_end ?? $row->expires_at ?? null,
                    $row->promotion_start ?? null,
                    ['edit_path' => '/dashboard?tab=banners']
                );
            }
        }

        if (Schema::hasTable('business_affiliate_offers')) {
            foreach (
                BusinessAffiliateOffer::query()
                    ->where('user_id', $userId)
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get() as $row
            ) {
                $items[] = $this->row(
                    'affiliate',
                    $row->id,
                    $row->product_service_title ?? 'Affiliate offer',
                    $row->status ?? 'active',
                    $row->expires_at,
                    $row->created_at,
                    [
                        'edit_path' => '/dashboard?tab=affiliates&mode=selling',
                        'commission_rate' => $row->commission_rate,
                        'commission_type' => $row->commission_type,
                        'is_featured' => (bool) ($row->is_featured ?? false),
                        'is_sponsored' => (bool) ($row->is_sponsored ?? false),
                        'is_promoted' => (bool) ($row->is_promoted ?? false),
                    ]
                );
            }
        }

        if (Schema::hasTable('user_affiliate_posts')) {
            foreach (
                UserAffiliatePost::query()
                    ->where('user_id', $userId)
                    ->orderByDesc('id')
                    ->limit(30)
                    ->get() as $row
            ) {
                $items[] = $this->row(
                    'affiliate_post',
                    $row->id,
                    $row->title ?? 'Affiliate post',
                    $row->status ?? 'active',
                    $row->expires_at,
                    $row->created_at,
                    ['edit_path' => '/dashboard?tab=affiliates&mode=links']
                );
            }
        }

        usort($items, function ($a, $b) {
            $ae = $a['expires_at'] ? strtotime($a['expires_at']) : PHP_INT_MAX;
            $be = $b['expires_at'] ? strtotime($b['expires_at']) : PHP_INT_MAX;

            return $ae <=> $be;
        });

        $summary = [
            'total' => count($items),
            'active' => count(array_filter($items, fn ($i) => $i['status_label'] === 'active')),
            'expiring_soon' => count(array_filter($items, fn ($i) => ($i['days_remaining'] ?? null) !== null && $i['days_remaining'] <= 7 && $i['days_remaining'] >= 0)),
            'expired' => count(array_filter($items, fn ($i) => ($i['days_remaining'] ?? null) !== null && $i['days_remaining'] < 0)),
            'by_format' => [],
        ];
        foreach ($items as $item) {
            $f = $item['format'];
            $summary['by_format'][$f] = ($summary['by_format'][$f] ?? 0) + 1;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'items' => $items,
                'formats' => [
                    'free',
                    'paid',
                    'sponsored',
                    'featured',
                    'promoted',
                    'banner',
                    'affiliate',
                ],
            ],
        ]);
    }

    private function row(
        string $format,
        $id,
        string $title,
        ?string $status,
        $expiresAt,
        $startsAt,
        array $meta = []
    ): array {
        $expires = $expiresAt ? Carbon::parse($expiresAt) : null;
        $days = $expires ? (int) now()->diffInDays($expires, false) : null;
        $statusLabel = 'active';
        if ($expires && $expires->isPast()) {
            $statusLabel = 'expired';
        } elseif (in_array(strtolower((string) $status), ['pending', 'pending_payment', 'unpaid'], true)) {
            $statusLabel = 'pending';
        } elseif (in_array(strtolower((string) $status), ['inactive', 'disabled', 'rejected'], true)) {
            $statusLabel = 'inactive';
        } elseif ($status) {
            $statusLabel = strtolower($status) === 'paid' || strtolower($status) === 'approved' || strtolower($status) === 'completed'
                ? 'active'
                : strtolower($status);
        }

        return array_merge([
            'format' => $format,
            'id' => $id,
            'title' => $title,
            'payment_status' => $status,
            'status_label' => $statusLabel,
            'starts_at' => $startsAt ? Carbon::parse($startsAt)->toIso8601String() : null,
            'expires_at' => $expires ? $expires->toIso8601String() : null,
            'days_remaining' => $days,
            'source_key' => $format.':'.$id,
        ], $meta);
    }
}
