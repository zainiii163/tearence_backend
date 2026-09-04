<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\MediaUrlHelper;
use App\Models\AffiliateCategory;
use App\Models\Affiliate;
use App\Models\BusinessAffiliateOffer;
use App\Models\UserAffiliatePost;
use App\Models\AffiliateUpsellPlan;
use App\Models\AffiliateApplication;
use App\Models\AffiliateHopClick;
use App\Models\AffiliateHopConversion;
use App\Models\AffiliatePayout;
use App\Support\CryptoRails;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Concerns\EnforcesListingPromoPayment;
use Illuminate\Validation\Rule;

class AffiliateController extends Controller
{
    use EnforcesListingPromoPayment;
    /**
     * Get all affiliate categories.
     */
    public function categories(): JsonResponse
    {
        $categories = AffiliateCategory::active()->ordered()->withCount([
            'businessAffiliateOffers as active_business_offers' => function ($query) {
                $query->active();
            },
            'userAffiliatePosts as active_user_posts' => function ($query) {
                $query->active();
            }
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get business affiliate offers with filters.
     */
    public function businessOffers(Request $request): JsonResponse
    {
        $query = BusinessAffiliateOffer::with(['user', 'affiliateCategory'])
            ->withCount([
                'applications as approved_promoters_count' => function ($q) {
                    $q->where('status', 'approved');
                },
                'applications as converting_promoters_count' => function ($q) {
                    $q->where('status', 'approved')->where('conversions_count', '>', 0);
                },
            ])
            ->withSum([
                'applications as promoters_earnings_sum' => function ($q) {
                    $q->where('status', 'approved');
                },
            ], 'earnings_total')
            ->withSum([
                'applications as promoters_conversions_sum' => function ($q) {
                    $q->where('status', 'approved');
                },
            ], 'conversions_count')
            ->withSum([
                'applications as promoters_clicks_sum' => function ($q) {
                    $q->where('status', 'approved');
                },
            ], 'clicks_count');

        // ClickBank marketplace mode: only live approved programs
        if ($request->boolean('marketplace')) {
            $query->active();
        }

        if ($request->category_id) {
            $query->where('affiliate_category_id', $request->category_id);
        }
        if ($request->country) {
            $query->where('country', $request->country);
        }
        if ($request->commission_type) {
            $query->where('commission_type', $request->commission_type);
        }
        if ($request->min_commission) {
            $query->where('commission_rate', '>=', $request->min_commission);
        }
        if ($request->max_commission) {
            $query->where('commission_rate', '<=', $request->max_commission);
        }
        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($w) use ($q) {
                $w->where('product_service_title', 'like', $q)
                    ->orWhere('business_name', 'like', $q)
                    ->orWhere('description', 'like', $q)
                    ->orWhere('tagline', 'like', $q);
            });
        }
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }
        if ($request->boolean('promoted')) {
            $query->where('is_promoted', true);
        }
        if ($request->boolean('sponsored')) {
            $query->where('is_sponsored', true);
        }
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('sale_price')
                ->whereNotNull('compare_at_price')
                ->whereColumn('compare_at_price', '>', 'sale_price');
        }
        if ($request->boolean('dropping_soon')) {
            $query->whereNotNull('drop_at')->where('drop_at', '>', now());
        }

        $sort = $request->sort ?? 'gravity';
        $order = strtolower((string) ($request->order ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sort === 'gravity') {
            $query->orderBy('converting_promoters_count', $order)->orderBy('clicks', 'desc');
        } elseif ($sort === 'commission_rate' || $sort === 'commission') {
            $query->orderBy('commission_rate', $order);
        } elseif (in_array($sort, ['created_at', 'views', 'clicks'], true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $offers = $query->paginate($request->per_page ?? 24);

        $offers->getCollection()->transform(function (BusinessAffiliateOffer $offer) {
            return $this->appendMarketplaceStats($offer);
        });

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * ClickBank-style marketplace metrics for one offer.
     */
    private function appendMarketplaceStats(BusinessAffiliateOffer $offer): BusinessAffiliateOffer
    {
        $clicks = (int) ($offer->promoters_clicks_sum ?? 0) ?: (int) ($offer->clicks ?? 0);
        $conversions = (int) ($offer->promoters_conversions_sum ?? 0);
        $earnings = (float) ($offer->promoters_earnings_sum ?? 0);
        $gravity = (int) ($offer->converting_promoters_count ?? 0);
        $avgSale = $conversions > 0 ? round($earnings / $conversions, 2) : 0.0;
        $epc = $clicks > 0 ? round($earnings / $clicks, 2) : 0.0;
        $initialPct = $offer->commission_type === 'percentage'
            ? (float) $offer->commission_rate
            : null;

        $shopping = $offer->shoppingActivity();
        $offer->setAttribute('shopping', $shopping);
        $offer->setAttribute('marketplace_stats', [
            'gravity' => $gravity,
            'avg_earnings_per_sale' => $avgSale,
            'avg_percent_per_sale' => $initialPct,
            'epc' => $epc,
            'conversion_rate' => $clicks > 0 ? round(($conversions / $clicks) * 100, 2) : 0,
            'approved_promoters' => (int) ($offer->approved_promoters_count ?? 0),
            'cookie_days' => (int) ($offer->cookie_duration ?: 30),
            'commission_label' => $offer->commission_type === 'fixed'
                ? '$' . number_format((float) $offer->commission_rate, 2)
                : rtrim(rtrim(number_format((float) $offer->commission_rate, 2), '0'), '.') . '%',
            'shopping' => $shopping,
        ]);

        return $offer;
    }

    /**
     * Get user affiliate posts with filters.
     */
    public function userPosts(Request $request): JsonResponse
    {
        $query = UserAffiliatePost::with(['user', 'affiliateCategory']);

        // Public hub: only live approved listings (user posts are always free)
        if ($request->boolean('marketplace')) {
            $query->active();
        } elseif ($request->boolean('mine') && Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->active();
        }
        
        // Filters only apply to the result set
        if ($request->category_id) {
            $query->where('affiliate_category_id', $request->category_id);
        }
        if ($request->country) {
            $query->where('country', $request->country);
        }
        if ($request->target_audience) {
            $query->where('target_audience', 'like', '%' . $request->target_audience . '%');
        }
        if ($request->filled('q') || $request->filled('search')) {
            $term = '%' . ($request->q ?: $request->search) . '%';
            $query->where(function ($w) use ($term) {
                $w->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('target_audience', 'like', $term);
            });
        }
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }
        if ($request->boolean('promoted')) {
            $query->where('is_promoted', true);
        }
        if ($request->boolean('sponsored')) {
            $query->where('is_sponsored', true);
        }

        // Sort
        $sort = $request->sort ?? 'created_at';
        $order = $request->order ?? 'desc';
        
        if (in_array($sort, ['created_at', 'views', 'clicks', 'shares'])) {
            $query->orderBy($sort, $order);
        }

        $posts = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Public hub map for the 3-part Affiliates architecture (Ads / Marketplace / Courses).
     */
    public function hubs(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 'ads',
                    'label' => 'Affiliate Ads',
                    'path' => '/affiliates',
                    'description' => 'Promoted affiliate link ads',
                ],
                [
                    'id' => 'marketplace',
                    'label' => 'Marketplace',
                    'path' => '/affiliates/marketplace',
                    'description' => 'Business programs to join and promote',
                ],
                [
                    'id' => 'courses',
                    'label' => 'Courses',
                    'path' => '/affiliates/courses',
                    'description' => 'Guides and education offers to get started',
                ],
            ],
        ]);
    }

    /**
     * Education / courses marketplace offers for the Courses hub.
     */
    public function courses(Request $request): JsonResponse
    {
        $educationCategoryIds = AffiliateCategory::query()
            ->where(function ($q) {
                $q->where('name', 'like', '%course%')
                    ->orWhere('name', 'like', '%education%')
                    ->orWhere('name', 'like', '%guide%')
                    ->orWhere('name', 'like', '%training%')
                    ->orWhere('slug', 'like', '%course%')
                    ->orWhere('slug', 'like', '%education%')
                    ->orWhere('slug', 'like', '%guide%')
                    ->orWhere('slug', 'like', '%training%');
            })
            ->pluck('id');

        $query = BusinessAffiliateOffer::with(['user', 'affiliateCategory'])
            ->active()
            ->where(function ($q) use ($educationCategoryIds) {
                if ($educationCategoryIds->isNotEmpty()) {
                    $q->whereIn('affiliate_category_id', $educationCategoryIds);
                }
                $q->orWhere('product_service_title', 'like', '%course%')
                    ->orWhere('product_service_title', 'like', '%guide%')
                    ->orWhere('product_service_title', 'like', '%training%')
                    ->orWhere('product_service_title', 'like', '%tutorial%')
                    ->orWhere('tagline', 'like', '%course%')
                    ->orWhere('tagline', 'like', '%guide%')
                    ->orWhere('description', 'like', '%affiliate marketing%')
                    ->orWhereHas('affiliateCategory', function ($c) {
                        $c->where('name', 'like', '%course%')
                            ->orWhere('name', 'like', '%education%')
                            ->orWhere('name', 'like', '%guide%')
                            ->orWhere('name', 'like', '%training%');
                    });
            });

        if ($request->filled('q')) {
            $term = '%' . $request->q . '%';
            $query->where(function ($w) use ($term) {
                $w->where('product_service_title', 'like', $term)
                    ->orWhere('business_name', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('tagline', 'like', $term);
            });
        }

        $offers = $query
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 24);

        $offers->getCollection()->transform(function (BusinessAffiliateOffer $offer) {
            return $this->appendMarketplaceStats($offer);
        });

        return response()->json([
            'success' => true,
            'data' => $offers,
            'meta' => [
                'hub' => 'courses',
                'education_category_ids' => $educationCategoryIds->values(),
            ],
        ]);
    }

    /**
     * Active Filament / paid affiliate link ads (affiliate_links) for the public hub.
     */
    public function affiliateLinks(Request $request): JsonResponse
    {
        $query = Affiliate::query()
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhere('status', 'active')
                    ->orWhereNull('status');
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }
        if ($request->filled('q') || $request->filled('search')) {
            $term = '%' . ($request->q ?: $request->search) . '%';
            $query->where(function ($w) use ($term) {
                $w->where('title', 'like', $term)
                    ->orWhere('link', 'like', $term);
            });
        }

        $sort = $request->sort ?? 'created_at';
        $order = $request->order ?? 'desc';
        if (in_array($sort, ['created_at', 'position', 'title', 'price'], true)) {
            $query->orderBy($sort, $order);
        } else {
            $query->orderByDesc('created_at');
        }

        $links = $query->paginate($request->per_page ?? 50);

        // Normalize to hub-friendly card fields
        $links->getCollection()->transform(function (Affiliate $row) {
            $rawImage = $row->getAttributes()['image_url'] ?? null;
            $imageUrl = null;
            if (is_string($rawImage) && $rawImage !== '') {
                $imageUrl = MediaUrlHelper::resolve($rawImage)
                    ?: MediaUrlHelper::rewriteLocalStorageUrl($rawImage)
                    ?: $rawImage;
            }

            return [
                'id' => $row->id,
                'title' => $row->title,
                'description' => $row->title,
                'affiliate_link' => $row->link,
                'tracking_link' => $row->link,
                'link' => $row->link,
                'image_url' => $imageUrl,
                'image' => $imageUrl,
                'position' => $row->position,
                'price' => $row->price,
                'commission_rate' => 0,
                'country' => '',
                'is_active' => (bool) $row->is_active,
                'status' => $row->status,
                'is_featured' => true,
                'is_promoted' => false,
                'is_sponsored' => false,
                'is_verified' => true,
                'views' => 0,
                'clicks' => 0,
                'content_source' => 'affiliate_links',
                'contentType' => 'link',
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'expires_at' => $row->expires_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $links,
        ]);
    }

    /**
     * Get a specific business affiliate offer.
     */
    public function businessOffer(string $id): JsonResponse
    {
        try {
            $offer = BusinessAffiliateOffer::with(['user', 'affiliateCategory'])
                ->withCount([
                    'applications as approved_promoters_count' => function ($q) {
                        $q->where('status', 'approved');
                    },
                    'applications as converting_promoters_count' => function ($q) {
                        $q->where('status', 'approved')->where('conversions_count', '>', 0);
                    },
                ])
                ->withSum([
                    'applications as promoters_earnings_sum' => function ($q) {
                        $q->where('status', 'approved');
                    },
                ], 'earnings_total')
                ->withSum([
                    'applications as promoters_conversions_sum' => function ($q) {
                        $q->where('status', 'approved');
                    },
                ], 'conversions_count')
                ->withSum([
                    'applications as promoters_clicks_sum' => function ($q) {
                        $q->where('status', 'approved');
                    },
                ], 'clicks_count')
                ->findOrFail($id);

            // Increment views without failing the response if analytics is unavailable
            try {
                $offer->incrementViews();
            } catch (\Throwable $e) {
                report($e);
            }

            $this->appendMarketplaceStats($offer);

            $payload = $offer->toArray();
            $payload['marketplace_stats'] = $offer->getAttribute('marketplace_stats');
            $payload['shopping'] = $offer->getAttribute('shopping');
            $payload['my_application'] = null;

            $userId = Auth::id();
            if ($userId) {
                $payload['my_application'] = AffiliateApplication::query()
                    ->where('business_affiliate_offer_id', $offer->id)
                    ->where('user_id', $userId)
                    ->first();
            }

            return response()->json([
                'success' => true,
                'data' => $payload,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found',
            ], 404);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load offer',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get a specific user affiliate post.
     */
    public function userPost(string $id): JsonResponse
    {
        $post = UserAffiliatePost::with(['user', 'affiliateCategory', 'analytics'])
            ->findOrFail($id);

        // Increment views
        $post->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    /**
     * Create a new business affiliate offer.
     */
    public function createBusinessOffer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'product_service_title' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'required|string',
            'affiliate_category_id' => 'required|exists:affiliate_categories,id',
            'country' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_rate' => 'required|numeric|min:0',
            'cookie_duration' => 'nullable|integer|min:1',
            'cookie_package_slug' => 'nullable|string|max:64',
            'allowed_traffic_types' => 'nullable|array',
            'allowed_traffic_types.*' => 'in:social_media,email,ppc,blogging,influencer,other',
            'restrictions' => 'nullable|string',
            'join_instructions' => 'nullable|string|max:5000',
            'tracking_link' => 'required|url',
            'promotional_assets' => 'nullable|array',
            'business_email' => 'required|email',
            'website_url' => 'nullable|url',
            'verification_document' => 'nullable|string',
            'sale_price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'discount_code' => 'nullable|string|max:64',
            'promotion_type' => 'nullable|in:none,percent_off,amount_off,sale,price_drop,product_drop',
            'promotion_label' => 'nullable|string|max:80',
            'drop_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $promo = app(\App\Services\PromoPricingService::class);
        $cookiePackage = null;
        if ($request->filled('cookie_package_slug')) {
            $cookiePackage = $promo->findBySlug((string) $request->cookie_package_slug, 'affiliates');
        }
        if (! $cookiePackage && $request->filled('cookie_duration')) {
            $cookiePackage = $promo->affiliateCookiePackages()
                ->firstWhere('duration_days', (int) $request->cookie_duration);
        }
        if (! $cookiePackage) {
            $cookiePackage = $promo->findBySlug('cookie_30', 'affiliates');
        }

        $cookieDays = (int) (
            $cookiePackage->duration_days
            ?? $request->input('cookie_duration')
            ?? 30
        );
        // Listing live window matches the purchased cookie package (promo offer)
        $listingDays = $cookieDays;

        $offer = BusinessAffiliateOffer::create([
            'user_id' => Auth::id(),
            'status' => 'approved',
            'is_active' => true,
            'payment_status' => ((float) ($cookiePackage->price_usd ?? 0) > 0) ? 'pending' : 'paid',
            'expires_at' => now()->addDays($listingDays),
            'affiliate_category_id' => $request->affiliate_category_id,
            'business_name' => $request->business_name,
            'product_service_title' => $request->product_service_title,
            'tagline' => $request->tagline,
            'description' => $request->description,
            'country' => $request->country,
            'region' => $request->region,
            'commission_type' => $request->commission_type,
            'commission_rate' => $request->commission_rate,
            'cookie_duration' => $cookieDays,
            'allowed_traffic_types' => $request->allowed_traffic_types,
            'restrictions' => $request->restrictions,
            'join_instructions' => $request->join_instructions,
            'tracking_link' => $request->tracking_link,
            'promotional_assets' => $request->promotional_assets,
            'business_email' => $request->business_email,
            'website_url' => $request->website_url,
            'verification_document' => $request->verification_document,
            'sale_price' => $request->sale_price,
            'compare_at_price' => $request->compare_at_price,
            'discount_code' => $request->discount_code,
            'promotion_type' => $request->input('promotion_type', 'none'),
            'promotion_label' => $request->promotion_label,
            'drop_at' => $request->drop_at,
            'price' => (float) ($cookiePackage->price_usd ?? 0),
        ]);

        $offer->load('affiliateCategory');
        $offer->makeVisible('postback_token');

        $promoAmount = (float) ($cookiePackage->price_usd ?? 0);
        if ($promoAmount >= 0.01) {
            return $this->paymentRequiredListingResponse(
                $offer,
                $promoAmount,
                'affiliate',
                'Pay the cookie / listing package to keep this offer live on Marketplace.'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Business affiliate offer created successfully',
            'data' => $offer,
            'promo' => $cookiePackage ? [
                'slug' => $cookiePackage->slug ?? null,
                'price_usd' => $promoAmount,
                'duration_days' => $cookieDays,
                'name' => $cookiePackage->name ?? null,
            ] : null,
        ], 201);
    }

    /**
     * Confirm cookie-package payment and keep the marketplace offer live.
     */
    public function completeCookiePayment(Request $request, string $id): JsonResponse
    {
        $offer = BusinessAffiliateOffer::where('user_id', Auth::id())->findOrFail($id);

        if ($offer->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Already paid.',
                'data' => $offer,
            ]);
        }

        $amount = (float) ($offer->price ?? 0);
        if ($amount < 0.01) {
            $offer->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Offer is live.',
                'data' => $offer->fresh(),
            ]);
        }

        $verified = $this->verifyPromoPayment($request, $amount, 'affiliate_offer', $offer->id);
        if ($verified instanceof JsonResponse) {
            return $verified;
        }

        $offer->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'payment_transaction_id' => $request->input('payment_id')
                ?: $request->input('transaction_id')
                ?: $offer->payment_transaction_id,
            'is_active' => true,
            'status' => 'approved',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed. Marketplace offer is live.',
            'data' => $offer->fresh()->load('affiliateCategory'),
        ]);
    }

    /**
     * Create a new user affiliate post.
     */
    public function createUserPost(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            // Clive: feed is image + title + link — description optional
            'description' => 'nullable|string',
            'affiliate_category_id' => 'nullable|exists:affiliate_categories,id',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'affiliate_link' => 'required|url',
            'image' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'hashtags.*' => 'string|max:50',
            'target_audience' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $title = (string) $request->title;
            $description = trim((string) ($request->description ?? ''));
            if ($description === '') {
                $description = $title;
            }

            $categoryId = $request->affiliate_category_id;
            if (! $categoryId) {
                $categoryId = \App\Models\AffiliateCategory::query()->value('id');
            }
            if (! $categoryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No affiliate category is available. Ask an admin to add one.',
                ], 422);
            }

            // Server-side listing state — ignore client status/payment fields
            $post = UserAffiliatePost::create([
                'user_id' => Auth::id(),
                'status' => 'approved',
                'is_active' => true,
                'payment_status' => 'paid',
                'expires_at' => now()->addDays(\App\Services\PromoPricingService::DEFAULT_FREE_DURATION_DAYS),
                'affiliate_category_id' => $categoryId,
                'title' => $title,
                'description' => $description,
                'country' => $request->country,
                'region' => $request->region,
                'affiliate_link' => $request->affiliate_link,
                'image' => $request->image ?: '',
                'hashtags' => $request->hashtags,
                'target_audience' => $request->target_audience,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create affiliate post',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'User affiliate post created successfully',
            'data' => $post->load('affiliateCategory'),
        ], 201);
    }

    /**
     * Apply to promote a business affiliate offer.
     */
    public function applyToPromote(Request $request, string $offerId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'message' => 'nullable|string|max:5000',
            'promotion_methods' => 'nullable|array',
            'promotion_methods.*' => 'string',
            'audience_details' => 'nullable|array',
            'website_url' => 'nullable|url|max:500',
            'social_media_links' => 'nullable|array',
            'social_media_links.*.platform' => 'nullable|string|max:50',
            'social_media_links.*.url' => 'required_with:social_media_links|url|max:500',
            'estimated_monthly_visitors' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $socialLinks = collect($request->input('social_media_links', []))
            ->filter(fn ($row) => is_array($row) && !empty($row['url']))
            ->values()
            ->all();
        $websiteUrl = $request->input('website_url');

        $offer = BusinessAffiliateOffer::findOrFail($offerId);

        if ((int) $offer->user_id === (int) Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot promote your own marketplace offer',
            ], 422);
        }

        if (! $offer->isCurrentlyActive()) {
            return response()->json([
                'success' => false,
                'message' => 'This offer is not live. It may be expired or inactive.',
            ], 422);
        }

        // Check if user already applied
        $existingApplication = AffiliateApplication::where('business_affiliate_offer_id', $offerId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingApplication) {
            if ($existingApplication->status === 'approved') {
                $existingApplication->ensureTrackingCode();
                $existingApplication->refresh();

                return response()->json([
                    'success' => true,
                    'message' => 'You already promote this offer — here is your tracking link',
                    'data' => $existingApplication->load('businessAffiliateOffer.affiliateCategory'),
                ]);
            }

            if ($existingApplication->status === 'pending') {
                // Instant hop-link marketplace: auto-approve pending joins
                $existingApplication->update(['status' => 'approved', 'joined_at' => now()]);
                $existingApplication->ensureTrackingCode();
                $existingApplication->refresh();

                return response()->json([
                    'success' => true,
                    'message' => 'You are now promoting this offer — hop link ready',
                    'data' => $existingApplication->load('businessAffiliateOffer.affiliateCategory'),
                ]);
            }

            // Rejected / withdrawn — allow re-apply and auto-approve for hop link
            $existingApplication->update([
                'message' => $request->message,
                'promotion_methods' => $request->promotion_methods,
                'audience_details' => $request->audience_details,
                'website_url' => $websiteUrl,
                'social_media_links' => $socialLinks,
                'estimated_monthly_visitors' => $request->estimated_monthly_visitors,
                'status' => 'approved',
                'rejection_reason' => null,
                'reviewed_at' => now(),
                'reviewed_by' => null,
                'approval_notes' => 'Auto-approved marketplace join',
                'joined_at' => now(),
            ]);
            $existingApplication->ensureTrackingCode();

            return response()->json([
                'success' => true,
                'message' => 'You are now promoting this offer — hop link ready',
                'data' => $existingApplication->fresh()->load('businessAffiliateOffer.affiliateCategory'),
            ]);
        }

        $application = AffiliateApplication::create([
            'business_affiliate_offer_id' => $offerId,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'promotion_methods' => $request->promotion_methods,
            'audience_details' => $request->audience_details,
            'website_url' => $websiteUrl,
            'social_media_links' => $socialLinks,
            'estimated_monthly_visitors' => $request->estimated_monthly_visitors,
            // ClickBank-style: approve immediately and mint hop link
            'status' => 'approved',
            'joined_at' => now(),
            'approval_notes' => 'Auto-approved marketplace join',
        ]);
        $application->ensureTrackingCode();

        // Increment applications count
        $offer->increment('applications');

        return response()->json([
            'success' => true,
            'message' => 'You are now promoting this offer — hop link ready',
            'data' => $application->fresh()->load('businessAffiliateOffer.affiliateCategory'),
        ], 201);
    }

    /**
     * Get available upsell plans.
     */
    public function upsellPlans(): JsonResponse
    {
        $plans = AffiliateUpsellPlan::active()->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    /**
     * Track click on affiliate link.
     */
    public function trackClick(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:business,user',
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->type === 'business') {
            $offer = BusinessAffiliateOffer::find($request->id);
            if ($offer) {
                $offer->incrementClicks();
            }
        } else {
            $post = UserAffiliatePost::find($request->id);
            if ($post) {
                $post->incrementClicks();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
        ]);
    }

    /**
     * Get user's affiliate applications (promotions).
     */
    public function myApplications(Request $request): JsonResponse
    {
        $applications = AffiliateApplication::where('user_id', Auth::id())
            ->with(['businessAffiliateOffer.affiliateCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    /**
     * Get user's business offers.
     */
    public function myBusinessOffers(Request $request): JsonResponse
    {
        $offers = BusinessAffiliateOffer::where('user_id', Auth::id())
            ->with(['affiliateCategory', 'applications'])
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->input('per_page', 20));

        $offers->getCollection()->transform(function (BusinessAffiliateOffer $offer) {
            $offer->ensurePostbackToken();
            $offer->makeVisible(['postback_token']);
            $offer->setAttribute(
                'postback_url',
                url('/api/v1/affiliates/conversions/postback')
            );

            return $offer;
        });

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Merchant: rotate postback token for one of their offers.
     */
    public function rotateOfferPostbackToken(string $offerId): JsonResponse
    {
        $offer = BusinessAffiliateOffer::where('user_id', Auth::id())->findOrFail($offerId);
        $token = $offer->rotatePostbackToken();
        $offer->makeVisible('postback_token');

        return response()->json([
            'success' => true,
            'message' => 'Postback token rotated',
            'data' => [
                'id' => $offer->id,
                'postback_token' => $token,
                'postback_url' => url('/api/v1/affiliates/conversions/postback'),
            ],
        ]);
    }

    /**
     * Get user's affiliate posts.
     */
    public function myUserPosts(Request $request): JsonResponse
    {
        $posts = UserAffiliatePost::where('user_id', Auth::id())
            ->with(['affiliateCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Business owner: list applicants for one of their offers.
     */
    public function offerApplications(Request $request, string $offerId): JsonResponse
    {
        $offer = BusinessAffiliateOffer::where('user_id', Auth::id())->findOrFail($offerId);

        $apps = AffiliateApplication::where('business_affiliate_offer_id', $offer->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 30));

        return response()->json([
            'success' => true,
            'data' => $apps,
        ]);
    }

    /**
     * Business owner: approve an applicant → mint hop link.
     */
    public function approveApplication(Request $request, string $applicationId): JsonResponse
    {
        $application = AffiliateApplication::with('businessAffiliateOffer')->findOrFail($applicationId);
        $offer = $application->businessAffiliateOffer;

        if (!$offer || (int) $offer->user_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $application->approve(Auth::id(), $request->input('notes'));
        $application->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Promoter approved — tracking link issued',
            'data' => $application,
        ]);
    }

    /**
     * Business owner: reject an applicant.
     */
    public function rejectApplication(Request $request, string $applicationId): JsonResponse
    {
        $application = AffiliateApplication::with('businessAffiliateOffer')->findOrFail($applicationId);
        $offer = $application->businessAffiliateOffer;

        if (!$offer || (int) $offer->user_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $application->reject(Auth::id(), $request->input('reason'));

        return response()->json([
            'success' => true,
            'message' => 'Application rejected',
            'data' => $application,
        ]);
    }

    /**
     * Record a conversion/sale attributed via hop cookie or tracking code (merchant callback).
     * Ahrefs model: unique link → cookie window → later purchase → commission ledger.
     *
     * Auth: JWT merchant (dashboard "Report sale"), or X-WWA-Affiliate-Secret matching
     * config('services.affiliate.postback_secret') / AFFILIATE_POSTBACK_SECRET for external IPN.
     */
    public function recordConversion(Request $request): JsonResponse
    {
        if (!$this->canRecordConversion($request)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'tracking_code' => 'nullable|string|max:64',
            'amount' => 'nullable|numeric|min:0',
            'order_id' => 'nullable|string|max:120',
            'offer_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $fromCookie = !$request->filled('tracking_code') && (bool) $request->cookie('wwa_aff');
        $code = $request->input('tracking_code') ?: $request->cookie('wwa_aff');
        if (!$code) {
            return response()->json(['success' => false, 'message' => 'No affiliate attribution found'], 422);
        }

        $application = AffiliateApplication::where('tracking_code', $code)
            ->where('status', 'approved')
            ->with('businessAffiliateOffer')
            ->first();

        if (!$application) {
            return response()->json(['success' => false, 'message' => 'Invalid tracking code'], 404);
        }

        $offer = $application->businessAffiliateOffer;
        if (!$offer) {
            return response()->json(['success' => false, 'message' => 'Offer missing for this affiliate link'], 404);
        }

        if ($request->filled('offer_id') && (int) $request->input('offer_id') !== (int) $offer->id) {
            return response()->json(['success' => false, 'message' => 'Tracking code does not match offer'], 422);
        }

        $usingPlatformSecret = $this->hasValidPostbackSecret($request);
        $usingOfferToken = $this->hasValidOfferPostbackToken($request, $offer);
        $isMerchantOwner = Auth::check() && (int) $offer->user_id === (int) Auth::id();

        // Merchant JWT may only report sales for their own offers
        if (Auth::check() && !$usingPlatformSecret && !$usingOfferToken) {
            if (!$isMerchantOwner) {
                return response()->json(['success' => false, 'message' => 'Unauthorized for this offer'], 403);
            }
        }

        if (!$usingPlatformSecret && !$usingOfferToken && !$isMerchantOwner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $cookieDays = max(1, (int) ($offer->cookie_duration ?: 30));
        $lastClick = AffiliateHopClick::query()
            ->where('affiliate_application_id', $application->id)
            ->orderByDesc('id')
            ->first();

        // Merchant dashboard "Report sale" can attest without a hop click.
        // External postbacks still require a hop within the cookie window.
        if (!$isMerchantOwner || $usingPlatformSecret || $usingOfferToken) {
            if (!$lastClick) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hop click found for this affiliate link — visitor must click the unique link first',
                ], 422);
            }

            if ($lastClick->created_at->lt(now()->subDays($cookieDays))) {
                return response()->json([
                    'success' => false,
                    'message' => "Cookie window expired ({$cookieDays} day(s)). Sale cannot be attributed.",
                    'data' => [
                        'cookie_duration_days' => $cookieDays,
                        'last_click_at' => $lastClick->created_at?->toIso8601String(),
                    ],
                ], 422);
            }
        }

        $orderId = $request->filled('order_id') ? trim((string) $request->input('order_id')) : null;
        if ($orderId === '') {
            $orderId = null;
        }

        if ($orderId !== null) {
            $dup = AffiliateHopConversion::query()
                ->where('business_affiliate_offer_id', $offer->id)
                ->where('order_id', $orderId)
                ->exists();
            if ($dup) {
                return response()->json([
                    'success' => false,
                    'message' => 'This order_id was already recorded for this offer',
                ], 422);
            }
        }

        $saleAmount = (float) ($request->input('amount') ?: 0);
        $commissionType = $offer->commission_type ?: 'percentage';
        $commissionRate = (float) $offer->commission_rate;
        $commission = 0.0;
        if ($commissionType === 'percentage') {
            $commission = round($saleAmount * ($commissionRate / 100), 2);
        } else {
            $commission = round($commissionRate, 2);
        }

        $conversion = null;
        DB::transaction(function () use (
            $application,
            $offer,
            $code,
            $orderId,
            $saleAmount,
            $commission,
            $commissionType,
            $commissionRate,
            $fromCookie,
            $lastClick,
            &$conversion
        ) {
            $conversion = AffiliateHopConversion::create([
                'affiliate_application_id' => $application->id,
                'business_affiliate_offer_id' => $offer->id,
                'tracking_code' => $code,
                'order_id' => $orderId,
                'sale_amount' => $saleAmount,
                'commission_amount' => $commission,
                'commission_type' => $commissionType,
                'commission_rate' => $commissionRate,
                'status' => 'confirmed',
                'attributed_via' => $fromCookie ? 'cookie' : ($lastClick ? 'code' : 'merchant_report'),
                'meta' => [
                    'last_click_id' => $lastClick?->id,
                    'last_click_at' => $lastClick?->created_at?->toIso8601String(),
                    'reported_by_user_id' => Auth::id(),
                ],
            ]);

            $application->increment('conversions_count');
            $application->increment('earnings_total', $commission);
        });

        return response()->json([
            'success' => true,
            'message' => 'Conversion recorded',
            'data' => [
                'tracking_code' => $code,
                'commission' => $commission,
                'sale_amount' => $saleAmount,
                'cookie_duration_days' => $cookieDays,
                'attributed_via' => $fromCookie ? 'cookie' : ($lastClick ? 'code' : 'merchant_report'),
                'conversion' => $conversion,
                'application' => $application->fresh(),
                'postback_hint' => 'POST /api/v1/affiliates/conversions/postback with X-WWA-Postback-Token (per offer) or X-WWA-Affiliate-Secret, plus tracking_code, amount, order_id',
            ],
        ]);
    }

    /**
     * Promoter: clicks, conversions, earnings + recent ledger rows + payouts.
     */
    public function myEarnings(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $apps = AffiliateApplication::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->with(['businessAffiliateOffer:id,product_service_title,business_name,cookie_duration,commission_type,commission_rate'])
            ->get();

        $appIds = $apps->pluck('id');

        $conversions = AffiliateHopConversion::query()
            ->whereIn('affiliate_application_id', $appIds)
            ->with([
                'offer:id,product_service_title,business_name,commission_type,commission_rate,cookie_duration',
                'application:id,tracking_code,user_id',
            ])
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $payouts = AffiliatePayout::query()
            ->forUser($userId)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $earnings = round((float) $apps->sum('earnings_total'), 2);
        $pendingPayouts = round((float) $payouts->whereIn('status', ['pending', 'processing'])->sum('amount'), 2);
        $paid = round((float) $payouts->where('status', 'paid')->sum('amount'), 2);
        $available = max(0, round($earnings - $pendingPayouts - $paid, 2));
        $salesVolume = round((float) $conversions->sum('sale_amount'), 2);

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'promoter',
                'who_is_paid' => 'promoter',
                'who_pays' => 'business',
                'totals' => [
                    'programs' => $apps->count(),
                    'clicks' => (int) $apps->sum('clicks_count'),
                    'conversions' => (int) $apps->sum('conversions_count'),
                    'sales_volume' => $salesVolume,
                    'earnings' => $earnings,
                    'pending' => $pendingPayouts,
                    'paid' => $paid,
                    'available' => $available,
                ],
                'applications' => $apps,
                'recent_conversions' => $conversions,
                'payouts' => $payouts,
            ],
        ]);
    }

    /**
     * Merchant (business): sales via promoter links + commissions owed to promoters.
     * Clive: business pays the % they offered per sale; promoter is who gets paid.
     */
    public function businessMoneySummary(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $offers = BusinessAffiliateOffer::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get([
                'id',
                'product_service_title',
                'business_name',
                'commission_type',
                'commission_rate',
                'status',
                'expires_at',
                'clicks',
                'views',
            ]);

        $offerIds = $offers->pluck('id');
        $conversions = $offerIds->isEmpty()
            ? collect()
            : AffiliateHopConversion::query()
                ->whereIn('business_affiliate_offer_id', $offerIds)
                ->with([
                    'offer:id,product_service_title,commission_type,commission_rate',
                    'application:id,user_id,tracking_code,status',
                ])
                ->orderByDesc('id')
                ->limit(100)
                ->get();

        $salesVolume = round((float) $conversions->sum('sale_amount'), 2);
        $commissionsOwed = round((float) $conversions->sum('commission_amount'), 2);

        $byOffer = $offers->map(function ($offer) use ($conversions) {
            $rows = $conversions->where('business_affiliate_offer_id', $offer->id);

            return [
                'offer_id' => $offer->id,
                'title' => $offer->product_service_title,
                'commission_type' => $offer->commission_type,
                'commission_rate' => (float) $offer->commission_rate,
                'status' => $offer->status,
                'expires_at' => $offer->expires_at,
                'sales_count' => $rows->count(),
                'sales_volume' => round((float) $rows->sum('sale_amount'), 2),
                'commissions_owed' => round((float) $rows->sum('commission_amount'), 2),
                'clicks' => (int) ($offer->clicks ?? 0),
                'views' => (int) ($offer->views ?? 0),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'role' => 'business',
                'who_pays' => 'business',
                'who_is_paid' => 'promoter',
                'explanation' => 'When a sale is attributed to a promoter hop link, you pay the commission % (or flat fee) you set on that offer.',
                'totals' => [
                    'offers' => $offers->count(),
                    'sales_count' => $conversions->count(),
                    'sales_volume' => $salesVolume,
                    'commissions_owed_to_promoters' => $commissionsOwed,
                    'your_net_after_commissions' => round(max(0, $salesVolume - $commissionsOwed), 2),
                ],
                'by_offer' => $byOffer,
                'recent_sales' => $conversions->take(40)->values(),
            ],
        ]);
    }

    /**
     * List payout history for the authenticated promoter.
     */
    public function myPayouts(Request $request): JsonResponse
    {
        $rows = AffiliatePayout::query()
            ->forUser(Auth::id())
            ->orderByDesc('id')
            ->paginate(min(50, max(1, (int) $request->get('per_page', 20))));

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * Request a payout from available affiliate balance.
     */
    public function requestPayout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:25',
            'method' => 'nullable|string|max:50',
            'payout_details' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'crypto_network' => 'nullable|string|in:trc20,erc20,polygon',
            'crypto_address' => 'nullable|string|max:191',
            'crypto_currency' => 'nullable|string|max:16',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = Auth::id();
        $amount = round((float) $request->input('amount'), 2);

        $earned = (float) AffiliateApplication::query()
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->sum('earnings_total');

        $reserved = (float) AffiliatePayout::query()
            ->forUser($userId)
            ->whereIn('status', ['pending', 'processing', 'paid'])
            ->sum('amount');

        $available = max(0, round($earned - $reserved, 2));

        if ($amount > $available) {
            return response()->json([
                'success' => false,
                'message' => 'Amount exceeds available balance',
                'data' => ['available' => $available],
            ], 422);
        }

        $method = strtolower((string) $request->input('method', 'paypal'));
        $cryptoNetwork = strtolower((string) $request->input('crypto_network', ''));
        $cryptoAddress = trim((string) $request->input('crypto_address', ''));
        $cryptoCurrency = $request->input('crypto_currency');

        if ($method === 'crypto') {
            $customer = Auth::user();
            if ($cryptoAddress === '' && $customer && ! empty($customer->crypto_wallet_address)) {
                $cryptoAddress = (string) $customer->crypto_wallet_address;
                $cryptoNetwork = $cryptoNetwork ?: (string) ($customer->crypto_network ?: 'trc20');
            }
            if ($cryptoNetwork === '') {
                $cryptoNetwork = 'trc20';
            }
            $check = CryptoRails::validateAddress($cryptoAddress, $cryptoNetwork);
            if (! $check['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $check['message'],
                ], 422);
            }
            $cryptoAddress = $check['address'];
            $meta = CryptoRails::network($cryptoNetwork);
            $cryptoCurrency = $cryptoCurrency ?: ($meta['currency'] ?? 'USDT');
        }

        $payout = AffiliatePayout::create([
            'user_id' => $userId,
            'amount' => $amount,
            'method' => $method,
            'payout_details' => $request->input('payout_details')
                ?: ($method === 'crypto' ? ($cryptoNetwork.' · '.$cryptoAddress) : null),
            'notes' => $request->input('notes'),
            'status' => 'pending',
            'reference' => 'AFF-' . strtoupper(Str::random(8)),
            'crypto_network' => $method === 'crypto' ? $cryptoNetwork : null,
            'crypto_address' => $method === 'crypto' ? $cryptoAddress : null,
            'crypto_currency' => $method === 'crypto' ? $cryptoCurrency : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payout request submitted',
            'data' => $payout,
        ], 201);
    }

    /**
     * Merchant: conversions for one of their offers.
     */
    public function offerConversions(Request $request, string $offerId): JsonResponse
    {
        $offer = BusinessAffiliateOffer::findOrFail($offerId);
        if ((int) $offer->user_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $rows = AffiliateHopConversion::query()
            ->where('business_affiliate_offer_id', $offer->id)
            ->with(['application:id,user_id,tracking_code,status'])
            ->orderByDesc('id')
            ->paginate(min(50, max(1, (int) $request->get('per_page', 20))));

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    private function hasValidPostbackSecret(Request $request): bool
    {
        $secret = config('services.affiliate.postback_secret')
            ?: env('AFFILIATE_POSTBACK_SECRET');
        if (!$secret) {
            return false;
        }
        $header = $request->header('X-WWA-Affiliate-Secret')
            ?: $request->header('X-Affiliate-Secret');

        return is_string($header) && hash_equals((string) $secret, $header);
    }

    private function offerPostbackTokenFromRequest(Request $request): ?string
    {
        $token = $request->header('X-WWA-Postback-Token')
            ?: $request->header('X-Affiliate-Postback-Token')
            ?: $request->input('postback_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    private function hasValidOfferPostbackToken(Request $request, BusinessAffiliateOffer $offer): bool
    {
        $token = $this->offerPostbackTokenFromRequest($request);
        if (!$token || empty($offer->postback_token)) {
            return false;
        }

        return hash_equals((string) $offer->postback_token, $token);
    }

    private function canRecordConversion(Request $request): bool
    {
        if ($this->hasValidPostbackSecret($request)) {
            return true;
        }

        if ($this->offerPostbackTokenFromRequest($request)) {
            return true;
        }

        return Auth::check();
    }

    /**
     * Search affiliate content.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'type' => 'nullable|in:all,business,user',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = $request->q;
        $type = $request->type ?? 'all';
        $results = [];

        if ($type === 'all' || $type === 'business') {
            $businessOffers = BusinessAffiliateOffer::active()
                ->with(['user', 'affiliateCategory'])
                ->where(function ($q) use ($query) {
                    $q->where('business_name', 'like', '%' . $query . '%')
                      ->orWhere('product_service_title', 'like', '%' . $query . '%')
                      ->orWhere('description', 'like', '%' . $query . '%')
                      ->orWhere('tagline', 'like', '%' . $query . '%');
                })
                ->limit(10)
                ->get();

            $results['business_offers'] = $businessOffers;
        }

        if ($type === 'all' || $type === 'user') {
            $userPosts = UserAffiliatePost::active()
                ->paid()
                ->with(['user', 'affiliateCategory'])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                      ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->limit(10)
                ->get();

            $results['user_posts'] = $userPosts;

            $links = Affiliate::query()
                ->where(function ($q) {
                    $q->where('is_active', true)
                        ->orWhere('status', 'active')
                        ->orWhereNull('status');
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                        ->orWhere('link', 'like', '%' . $query . '%');
                })
                ->limit(10)
                ->get()
                ->map(function (Affiliate $row) {
                    return [
                        'id' => $row->id,
                        'title' => $row->title,
                        'affiliate_link' => $row->link,
                        'tracking_link' => $row->link,
                        'link' => $row->link,
                        'position' => $row->position,
                        'is_featured' => true,
                        'contentType' => 'link',
                        'content_source' => 'affiliate_links',
                        'created_at' => $row->created_at,
                    ];
                });

            $results['affiliate_links'] = $links;
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Update a business affiliate offer.
     */
    public function updateBusinessOffer(Request $request, string $id): JsonResponse
    {
        $offer = BusinessAffiliateOffer::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'business_name' => 'sometimes|required|string|max:255',
            'product_service_title' => 'sometimes|required|string|max:255',
            'tagline' => 'nullable|string|max:80',
            'description' => 'sometimes|required|string',
            'affiliate_category_id' => 'sometimes|required|exists:affiliate_categories,id',
            'country' => 'sometimes|required|string|max:255',
            'region' => 'nullable|string|max:255',
            'commission_type' => 'sometimes|required|in:percentage,fixed',
            'commission_rate' => 'sometimes|required|numeric|min:0',
            'cookie_duration' => 'sometimes|required|integer|min:1',
            'allowed_traffic_types' => 'nullable|array',
            'allowed_traffic_types.*' => 'in:social_media,email,ppc,blogging,influencer,other',
            'restrictions' => 'nullable|string',
            'join_instructions' => 'nullable|string|max:5000',
            'tracking_link' => 'sometimes|required|url',
            'promotional_assets' => 'nullable|array',
            'business_email' => 'sometimes|required|email',
            'website_url' => 'nullable|url',
            'verification_document' => 'nullable|string',
            'sale_price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'discount_code' => 'nullable|string|max:64',
            'promotion_type' => 'nullable|in:none,percent_off,amount_off,sale,price_drop,product_drop',
            'promotion_label' => 'nullable|string|max:80',
            'drop_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $offer->update($request->only([
            'business_name', 'product_service_title', 'tagline', 'description',
            'affiliate_category_id', 'country', 'region', 'commission_type',
            'commission_rate', 'cookie_duration', 'allowed_traffic_types',
            'restrictions', 'join_instructions', 'tracking_link', 'promotional_assets',
            'business_email', 'website_url', 'verification_document',
            'sale_price', 'compare_at_price', 'discount_code', 'promotion_type',
            'promotion_label', 'drop_at',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Business affiliate offer updated successfully',
            'data' => $offer->load('affiliateCategory'),
        ]);
    }

    /**
     * Update a user affiliate post.
     */
    public function updateUserPost(Request $request, string $id): JsonResponse
    {
        $post = UserAffiliatePost::where('user_id', Auth::id())->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'affiliate_category_id' => 'nullable|exists:affiliate_categories,id',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'affiliate_link' => 'sometimes|required|url',
            'image' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'hashtags.*' => 'string|max:50',
            'target_audience' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $post->update($request->only([
            'title', 'description', 'affiliate_category_id', 'country',
            'region', 'affiliate_link', 'image', 'hashtags', 'target_audience'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'User affiliate post updated successfully',
            'data' => $post->load('affiliateCategory'),
        ]);
    }

    /**
     * Delete a business affiliate offer.
     */
    public function deleteBusinessOffer(string $id): JsonResponse
    {
        $offer = BusinessAffiliateOffer::where('user_id', Auth::id())->findOrFail($id);
        
        // Soft delete by updating status
        $offer->update([
            'is_active' => false,
            'status' => 'deleted'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Business affiliate offer deleted successfully',
        ]);
    }

    /**
     * Delete a user affiliate post.
     */
    public function deleteUserPost(string $id): JsonResponse
    {
        $post = UserAffiliatePost::where('user_id', Auth::id())->findOrFail($id);
        
        // Soft delete by updating status
        $post->update([
            'is_active' => false,
            'status' => 'deleted'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User affiliate post deleted successfully',
        ]);
    }

    /**
     * Upload affiliate image.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp,avif|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('file');
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::random(10) . '_' . time() . '.' . $extension;
            
            // Store file using Laravel Storage
            $path = $file->storeAs('affiliate_images', $fileName, 'public');
            
            // Public URL (rewrites localhost APP_URL to MEDIA_PUBLIC_URL / production host)
            $url = MediaUrlHelper::resolve($path);
            
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'id' => $path,
                    'filename' => $file->getClientOriginalName(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }
}
