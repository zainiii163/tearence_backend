<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerBusiness;
use App\Models\SiteReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * Polymorphic ratings/reviews used across WWA marketplaces.
 *
 * GET  /site-reviews/{type}/{id}
 * POST /site-reviews/{type}/{id}  (jwt)
 */
class SiteReviewController extends Controller
{
    /** @var array<string, string> */
    public const TYPES = [
        'business' => 'business',
        'service' => 'service',
        'resort' => 'resort',
        'resorts-travel' => 'resort',
        'book' => 'book',
        'store' => 'store',
        'property' => 'property',
        'vehicle' => 'vehicle',
        'buy-sell' => 'buy-sell',
        'event' => 'event',
        'venue' => 'venue',
        'sponsored' => 'sponsored',
        'featured' => 'featured',
        'image' => 'image',
        'job' => 'job',
        'vehicle' => 'vehicle',
    ];

    public function index(Request $request, string $type, string $id): JsonResponse
    {
        if (! Schema::hasTable('site_reviews')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'average_rating' => 0,
                    'reviews_count' => 0,
                ],
            ]);
        }

        $resolved = $this->resolveType($type);
        if (! $resolved) {
            return response()->json(['success' => false, 'message' => 'Invalid review type'], 422);
        }

        $reviews = SiteReview::approved()
            ->forTarget($resolved, $id)
            ->orderByDesc('created_at')
            ->limit(min(100, (int) $request->get('limit', 50)))
            ->get()
            ->map(fn (SiteReview $r) => $this->serialize($r));

        $stats = $this->stats($resolved, $id);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $reviews,
                'average_rating' => $stats['average_rating'],
                'reviews_count' => $stats['reviews_count'],
            ],
        ]);
    }

    public function store(Request $request, string $type, string $id): JsonResponse
    {
        if (! Schema::hasTable('site_reviews')) {
            return response()->json([
                'success' => false,
                'message' => 'Reviews are not available yet. Please run migrations.',
            ], 503);
        }

        $resolved = $this->resolveType($type);
        if (! $resolved) {
            return response()->json(['success' => false, 'message' => 'Invalid review type'], 422);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:2000',
            'author_name' => 'nullable|string|max:120',
        ]);

        $user = Auth::user();
        $customerId = $user->customer_id ?? $user->getKey();
        if (! $customerId) {
            return response()->json(['success' => false, 'message' => 'Sign in to leave a review'], 401);
        }

        $authorName = $request->input('author_name')
            ?: trim(($user->first_name ?? '').' '.($user->last_name ?? ''))
            ?: ($user->name ?? 'Customer');

        $existing = SiteReview::forTarget($resolved, $id)
            ->where('customer_id', $customerId)
            ->first();

        if ($existing) {
            $existing->update([
                'rating' => (int) $request->input('rating'),
                'comment' => $request->input('comment'),
                'author_name' => $authorName,
                'status' => 'approved',
            ]);
            $review = $existing->fresh();
            $message = 'Review updated';
        } else {
            $review = SiteReview::create([
                'reviewable_type' => $resolved,
                'reviewable_id' => (string) $id,
                'customer_id' => $customerId,
                'author_name' => $authorName,
                'rating' => (int) $request->input('rating'),
                'comment' => $request->input('comment'),
                'status' => 'approved',
            ]);
            $message = 'Review submitted';
        }

        $this->syncParentStats($resolved, $id);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $this->serialize($review),
            'meta' => $this->stats($resolved, $id),
        ], $existing ? 200 : 201);
    }

    /**
     * Convenience wrappers used by BusinessService client.
     */
    public function businessIndex(Request $request, $businessId): JsonResponse
    {
        return $this->index($request, 'business', (string) $businessId);
    }

    public function businessStore(Request $request, $businessId): JsonResponse
    {
        return $this->store($request, 'business', (string) $businessId);
    }

    protected function resolveType(string $type): ?string
    {
        $key = strtolower(trim($type));

        return self::TYPES[$key] ?? null;
    }

    protected function stats(string $type, string $id): array
    {
        $q = SiteReview::approved()->forTarget($type, $id);
        $count = (clone $q)->count();
        $avg = $count ? round((float) (clone $q)->avg('rating'), 1) : 0;

        return [
            'average_rating' => $avg,
            'reviews_count' => $count,
        ];
    }

    protected function serialize(SiteReview $review): array
    {
        return [
            'id' => $review->id,
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'author_name' => $review->author_name ?: 'Customer',
            'customer_id' => $review->customer_id,
            'created_at' => optional($review->created_at)->toIso8601String(),
            'status' => $review->status,
        ];
    }

    protected function syncParentStats(string $type, string $id): void
    {
        $stats = $this->stats($type, $id);

        if ($type === 'business') {
            $business = is_numeric($id)
                ? CustomerBusiness::find($id)
                : CustomerBusiness::where('slug', $id)->first();
            if (! $business) {
                return;
            }
            $profile = is_array($business->category_profile) ? $business->category_profile : [];
            $profile['average_rating'] = $stats['average_rating'];
            $profile['reviews_count'] = $stats['reviews_count'];
            $business->category_profile = $profile;
            if (Schema::hasColumn($business->getTable(), 'rating')) {
                $business->rating = $stats['average_rating'];
            }
            if (Schema::hasColumn($business->getTable(), 'reviews_count')) {
                $business->reviews_count = $stats['reviews_count'];
            }
            $business->save();
        }
    }
}
