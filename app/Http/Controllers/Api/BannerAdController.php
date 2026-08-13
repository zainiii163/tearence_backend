<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\EnforcesListingPromoPayment;
use App\Http\Controllers\Concerns\RecordsCategoryMoneyFlow;
use App\Http\Controllers\Concerns\VerifiesClientPayments;
use App\Models\BannerAd;
use App\Models\BannerCategory;
use App\Models\BannerPurchase;
use App\Http\Resources\BannerAdResource;
use App\Http\Resources\BannerAdCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BannerAdController extends Controller
{
    use VerifiesClientPayments;
    use EnforcesListingPromoPayment;
    use RecordsCategoryMoneyFlow;

    /**
     * Display a listing of banner ads.
     */
    public function index(Request $request): BannerAdCollection
    {
        $query = BannerAd::with(['category', 'user']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // By default, only show active banners
            $query->active();
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->inCategory($request->category_id);
        }

        // Filter by country
        if ($request->has('country')) {
            $query->inCountry($request->country);
        }

        // Filter by promotion tier
        if ($request->has('promotion_tier')) {
            if ($request->promotion_tier === 'promoted') {
                $query->promoted();
            } elseif ($request->promotion_tier === 'featured') {
                $query->featured();
            } elseif ($request->promotion_tier === 'sponsored') {
                $query->sponsored();
            } elseif ($request->promotion_tier === 'network_boost') {
                $query->networkBoost();
            } else {
                $query->where('promotion_tier', $request->promotion_tier);
            }
        }

        // Filter by banner size
        if ($request->has('banner_size')) {
            $query->where('banner_size', $request->banner_size);
        }

        // Search by keyword
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'ctr':
                $query->orderByRaw("CASE WHEN views_count > 0 THEN (clicks_count * 100.0 / views_count) ELSE 0 END {$sortOrder}");
                break;
            case 'views':
                $query->orderBy('views_count', $sortOrder);
                break;
            case 'clicks':
                $query->orderBy('clicks_count', $sortOrder);
                break;
            case 'title':
                $query->orderBy('title', $sortOrder);
                break;
            case 'promotion_tier':
                $query->orderByRaw("FIELD(promotion_tier, 'network_boost', 'sponsored', 'featured', 'promoted', 'standard') {$sortOrder}");
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $limit = $request->get('limit', 20);
        $bannerAds = $query->paginate($limit);

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Get featured banner ads for carousel.
     */
    public function featured(Request $request): BannerAdCollection
    {
        $query = BannerAd::with(['category', 'user'])
            ->active()
            ->featured()
            ->where(function ($q) {
                $q->whereNull('promotion_end')
                  ->orWhere('promotion_end', '>=', now());
            });

        $limit = $request->get('limit', 10);
        $bannerAds = $query->orderBy('promotion_start', 'desc')->limit($limit)->get();

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Get most viewed banner ads.
     */
    public function mostViewed(Request $request): BannerAdCollection
    {
        $limit = $request->get('limit', 10);
        $bannerAds = BannerAd::with(['category', 'user'])
            ->active()
            ->mostViewed($limit)
            ->get();

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Get recently added banner ads.
     */
    public function recent(Request $request): BannerAdCollection
    {
        $limit = $request->get('limit', 10);
        $bannerAds = BannerAd::with(['category', 'user'])
            ->active()
            ->recent($limit)
            ->get();

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Store a newly created banner ad.
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'business_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:500',
            'banner_type' => ['required', Rule::in(['image', 'animated', 'html5', 'video'])],
            'banner_size' => ['required', Rule::in(['728x90', '300x250', '160x600', '970x250', '468x60', '1080x1080'])],
            'destination_link' => 'required|url|max:500',
            'call_to_action' => 'nullable|string|max:100',
            'key_selling_points' => 'nullable|string|max:1000',
            'offer_details' => 'nullable|string|max:1000',
            'validity_start' => 'nullable|date',
            'validity_end' => 'nullable|date|after_or_equal:validity_start',
            'banner_category_id' => 'required|exists:banner_categories,id',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'target_countries' => 'nullable|array',
            'target_countries.*' => 'string|max:100',
            'target_audience' => 'nullable|array',
            'target_audience.*' => 'string|max:255',
            'promotion_tier' => ['required', Rule::in(['standard', 'promoted', 'featured', 'sponsored', 'network_boost'])],
            'promotion_price' => 'required|numeric|min:0',
            'promotion_start' => 'nullable|date',
            'promotion_end' => 'nullable|date|after_or_equal:promotion_start',
            'is_verified_business' => 'boolean',
        ];

        // Conditional validation for banner_image vs video based on banner_type
        if ($request->banner_type === 'video') {
            $rules['video'] = 'required|string|max:255';
        } else {
            $rules['banner_image'] = 'required|string|max:255';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $bannerData = $request->all();
            $tierKey = $this->resolveCanonicalPromoTier($bannerData['promotion_tier'] ?? 'standard', 'banner');
            $promoAmount = $this->resolvePromoAmountForTier($tierKey);
            $durationDays = $this->resolvePromoDurationDays($tierKey);

            $bannerData['promotion_price'] = $promoAmount;
            $bannerData['status'] = 'pending';
            $bannerData['is_active'] = false;
            if (empty($bannerData['promotion_start'])) {
                $bannerData['promotion_start'] = now();
            }
            if (empty($bannerData['promotion_end'])) {
                $bannerData['promotion_end'] = now()->addDays($durationDays);
            }

            $bannerAd = BannerAd::create($bannerData);

            // If user is authenticated, associate the banner with them
            if (Auth::guard('api')->check()) {
                $bannerAd->user_id = Auth::guard('api')->id();
                $bannerAd->save();
            }

            if ($this->requestHasPaymentReference($request)) {
                $verified = $this->verifyPromoPayment($request, $promoAmount, 'banner_advert', $bannerAd->id);
                if ($verified instanceof JsonResponse) {
                    return $verified;
                }
                $bannerAd->update([
                    'status' => 'active',
                    'is_active' => true,
                ]);
                $bannerAd->category?->updateActiveBannersCount();

                return response()->json([
                    'success' => true,
                    'payment_required' => false,
                    'message' => 'Banner ad created and activated',
                    'data' => new BannerAdResource($bannerAd->fresh()->load(['category', 'user'])),
                ], 201);
            }

            return $this->paymentRequiredListingResponse(
                $bannerAd->load(['category', 'user']),
                $promoAmount,
                'banner',
                'Payment required to activate this banner advert.'
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create banner ad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm payment and activate a pending banner listing.
     */
    public function completeListingPayment(Request $request, string $id): JsonResponse
    {
        $bannerAd = BannerAd::findOrFail($id);

        if (Auth::guard('api')->check()
            && (int) $bannerAd->user_id !== (int) Auth::guard('api')->id()
            && ! Auth::user()?->is_admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($bannerAd->status === 'active' && $bannerAd->is_active) {
            return response()->json([
                'success' => true,
                'message' => 'Already active.',
                'data' => new BannerAdResource($bannerAd),
            ]);
        }

        $tierKey = $this->resolveCanonicalPromoTier($bannerAd->promotion_tier, 'banner');
        $amount = (float) ($bannerAd->promotion_price ?: $this->resolvePromoAmountForTier($tierKey));

        $verified = $this->verifyPromoPayment($request, $amount, 'banner_advert', $bannerAd->id);
        if ($verified instanceof JsonResponse) {
            return $verified;
        }

        $bannerAd->update([
            'status' => 'active',
            'is_active' => true,
            'promotion_start' => now(),
            'promotion_end' => now()->addDays($this->resolvePromoDurationDays($tierKey)),
        ]);
        $bannerAd->category?->updateActiveBannersCount();

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed. Banner is now live.',
            'data' => new BannerAdResource($bannerAd->fresh()->load(['category', 'user'])),
        ]);
    }

    /**
     * Display the specified banner ad.
     */
    public function show(string $slug): JsonResponse
    {
        $bannerAd = BannerAd::with(['category', 'user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view count
        $bannerAd->incrementViews();

        return response()->json([
            'success' => true,
            'data' => new BannerAdResource($bannerAd)
        ]);
    }

    /**
     * Update the specified banner ad.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $bannerAd = BannerAd::findOrFail($id);

        // Check if user owns this banner or is admin
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if ($bannerAd->user_id !== $user->user_id && !$user->hasPermission('manage_listings')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to update this banner ad'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:2000',
            'business_name' => 'sometimes|required|string|max:255',
            'contact_person' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:50',
            'website_url' => 'sometimes|nullable|url|max:500',
            'banner_type' => ['sometimes', 'required', Rule::in(['image', 'animated', 'html5', 'video'])],
            'banner_size' => ['sometimes', 'required', Rule::in(['728x90', '300x250', '160x600', '970x250', '468x60', '1080x1080'])],
            'banner_image' => 'sometimes|required|string|max:255',
            'destination_link' => 'sometimes|required|url|max:500',
            'call_to_action' => 'sometimes|nullable|string|max:100',
            'key_selling_points' => 'sometimes|nullable|string|max:1000',
            'offer_details' => 'sometimes|nullable|string|max:1000',
            'validity_start' => 'sometimes|nullable|date',
            'validity_end' => 'sometimes|nullable|date|after_or_equal:validity_start',
            'banner_category_id' => 'sometimes|required|exists:banner_categories,id',
            'country' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'target_countries' => 'sometimes|nullable|array',
            'target_countries.*' => 'string|max:100',
            'target_audience' => 'sometimes|nullable|array',
            'target_audience.*' => 'string|max:255',
            'promotion_tier' => ['sometimes', 'required', Rule::in(['standard', 'promoted', 'featured', 'sponsored', 'network_boost'])],
            'promotion_price' => 'sometimes|required|numeric|min:0',
            'promotion_start' => 'sometimes|nullable|date',
            'promotion_end' => 'sometimes|nullable|date|after_or_equal:promotion_start',
            'is_verified_business' => 'sometimes|boolean',
            'status' => ['sometimes', Rule::in(['draft', 'pending', 'active', 'rejected', 'expired'])],
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $bannerAd->update($request->all());

            // Update category banner count if category changed
            if ($request->has('banner_category_id') && $request->banner_category_id != $bannerAd->getOriginal('banner_category_id')) {
                $oldCategory = BannerCategory::find($bannerAd->getOriginal('banner_category_id'));
                $newCategory = BannerCategory::find($request->banner_category_id);
                
                if ($oldCategory) $oldCategory->updateActiveBannersCount();
                if ($newCategory) $newCategory->updateActiveBannersCount();
            }

            return response()->json([
                'success' => true,
                'message' => 'Banner ad updated successfully',
                'data' => new BannerAdResource($bannerAd->load(['category', 'user']))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update banner ad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified banner ad.
     */
    public function destroy(string $id): JsonResponse
    {
        $bannerAd = BannerAd::findOrFail($id);

        // Check if user owns this banner or is admin
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();
            if ($bannerAd->user_id !== $user->user_id && !$user->hasPermission('manage_listings')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this banner ad'
                ], 403);
            }
        }

        try {
            $categoryId = $bannerAd->banner_category_id;
            $bannerAd->delete();

            // Update category banner count
            $category = BannerCategory::find($categoryId);
            if ($category) {
                $category->updateActiveBannersCount();
            }

            return response()->json([
                'success' => true,
                'message' => 'Banner ad deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete banner ad',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track a click on a banner ad.
     */
    public function trackClick(string $slug): JsonResponse
    {
        $bannerAd = BannerAd::where('slug', $slug)->first();

        if (!$bannerAd) {
            // Catalog / preview packs may not exist in DB yet — don't 404 the client.
            return response()->json([
                'success' => true,
                'message' => 'Banner not found; click ignored',
                'skipped' => true,
            ]);
        }

        $bannerAd->incrementClicks();

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
            'destination_link' => $bannerAd->destination_link
        ]);
    }

    /**
     * Get banner ads for the authenticated user.
     */
    public function myBanners(Request $request): BannerAdCollection
    {
        if (!Auth::guard('api')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $query = BannerAd::with(['category'])
            ->where('user_id', Auth::guard('api')->id());

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $limit = $request->get('limit', 20);
        $bannerAds = $query->paginate($limit);

        return new BannerAdCollection($bannerAds);
    }

    /**
     * Get promotion options and pricing.
     */
    public function promotionOptions(): JsonResponse
    {
        $options = [
            [
                'tier' => 'promoted',
                'name' => 'Promoted Banner',
                'price' => 50.00,
                'currency' => 'GBP',
                'duration' => 30,
                'benefits' => [
                    'Highlighted banner',
                    'Appears above standard banners',
                    'Promoted badge',
                    '2× more visibility'
                ]
            ],
            [
                'tier' => 'featured',
                'name' => 'Featured Banner',
                'price' => 100.00,
                'currency' => 'GBP',
                'duration' => 30,
                'benefits' => [
                    'Top of category pages',
                    'Larger banner preview',
                    'Priority in search results',
                    'Included in weekly Featured Banners email',
                    'Featured badge',
                    '4× more visibility'
                ],
                'is_popular' => true
            ],
            [
                'tier' => 'sponsored',
                'name' => 'Sponsored Banner',
                'price' => 200.00,
                'currency' => 'GBP',
                'duration' => 30,
                'benefits' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Included in social media promotion',
                    'Sponsored badge',
                    'Maximum visibility'
                ]
            ],
            [
                'tier' => 'network_boost',
                'name' => 'Network-Wide Boost',
                'price' => 500.00,
                'currency' => 'GBP',
                'duration' => 30,
                'benefits' => [
                    'Appears across multiple pages',
                    'Banner Ads page',
                    'Homepage',
                    'Category pages',
                    'Related search pages',
                    'Included in email newsletters',
                    'Included in push notifications',
                    'Top Spotlight badge',
                    'Ultimate visibility'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Public preview — watermarked when GD is available. Not a free download.
     */
    public function preview(int $id)
    {
        $banner = BannerAd::active()->findOrFail($id);
        $path = $this->resolveBannerFilePath($banner);
        if (!$path) {
            abort(404, 'Banner image not found');
        }

        if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            $bytes = @file_get_contents($path);
            if ($bytes !== false) {
                $src = @imagecreatefromstring($bytes);
                if ($src) {
                    $w = imagesx($src);
                    $h = imagesy($src);
                    imagealphablending($src, true);
                    imagesavealpha($src, true);
                    $overlay = imagecolorallocatealpha($src, 0, 0, 0, 70);
                    imagefilledrectangle($src, 0, (int) ($h * 0.42), $w, (int) ($h * 0.58), $overlay);
                    $white = imagecolorallocate($src, 255, 255, 255);
                    $label = 'PREVIEW — BUY TO DOWNLOAD';
                    $font = 5;
                    $tw = imagefontwidth($font) * strlen($label);
                    $th = imagefontheight($font);
                    imagestring($src, $font, (int) (($w - $tw) / 2), (int) (($h - $th) / 2), $label, $white);

                    ob_start();
                    imagejpeg($src, null, 72);
                    $out = ob_get_clean();
                    imagedestroy($src);

                    return response($out, 200, [
                        'Content-Type' => 'image/jpeg',
                        'Content-Disposition' => 'inline; filename="preview.jpg"',
                        'Cache-Control' => 'private, no-store, max-age=0',
                        'X-Content-Type-Options' => 'nosniff',
                    ]);
                }
            }
        }

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'image/jpeg',
            'Content-Disposition' => 'inline; filename="preview"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Start paid purchase (auth). Download unlocks only after confirmPayment.
     */
    public function purchase(Request $request, int $id): JsonResponse
    {
        if (!Schema::hasTable('banner_purchases')) {
            return response()->json([
                'success' => false,
                'message' => 'Run migrations: banner_purchases table missing.',
            ], 503);
        }

        $banner = BannerAd::active()->findOrFail($id);
        $customerId = Auth::id();
        $price = (float) ($banner->promotion_price ?: 29);
        if ($price < 10) {
            $price = 29;
        }

        $existing = BannerPurchase::where('customer_id', $customerId)
            ->where('banner_ad_id', $banner->id)
            ->where('payment_status', 'completed')
            ->where(function ($q) {
                $q->whereNull('download_token_expires_at')
                    ->orWhere('download_token_expires_at', '>', now());
            })
            ->latest('id')
            ->first();

        if ($existing && $existing->isDownloadValid()) {
            return response()->json([
                'success' => true,
                'message' => 'Already purchased.',
                'data' => [
                    'purchase_id' => $existing->id,
                    'payment_status' => 'completed',
                    'download_token' => $existing->download_token,
                    'download_url' => url('/api/v1/banner-ads/download/'.$existing->download_token),
                    'expires_at' => $existing->download_token_expires_at,
                    'amount' => (float) $existing->price_paid,
                ],
            ]);
        }

        $pending = BannerPurchase::where('customer_id', $customerId)
            ->where('banner_ad_id', $banner->id)
            ->where('payment_status', 'pending')
            ->latest('id')
            ->first();

        if (!$pending) {
            $pending = BannerPurchase::create([
                'customer_id' => $customerId,
                'banner_ad_id' => $banner->id,
                'banner_slug' => $banner->slug,
                'title' => $banner->title,
                'price_paid' => $price,
                'payment_status' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created. Complete PayPal payment to unlock your download.',
            'data' => [
                'purchase_id' => $pending->id,
                'payment_status' => 'pending',
                'amount' => (float) $pending->price_paid,
                'title' => $pending->title,
            ],
        ], 201);
    }

    /**
     * Confirm PayPal capture — unlocks forced file download (not browser open).
     */
    public function confirmPayment(Request $request, int $purchaseId): JsonResponse
    {
        if (!Schema::hasTable('banner_purchases')) {
            return response()->json(['success' => false, 'message' => 'Not available'], 503);
        }

        $purchase = BannerPurchase::find($purchaseId);
        if (!$purchase || (int) $purchase->customer_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($purchase->payment_status === 'completed' && $purchase->isDownloadValid()) {
            return response()->json([
                'success' => true,
                'message' => 'Already paid.',
                'data' => [
                    'purchase_id' => $purchase->id,
                    'payment_status' => 'completed',
                    'download_token' => $purchase->download_token,
                    'download_url' => url('/api/v1/banner-ads/download/'.$purchase->download_token),
                    'expires_at' => $purchase->download_token_expires_at,
                ],
            ]);
        }

        $request->validate([
            'payment_id' => 'required|string|max:191',
            'payment_method' => 'required|in:paypal,stripe,crypto',
        ]);

        $verified = $this->verifyClientPaymentOrFail(
            $request,
            (float) $purchase->price_paid,
            'banner_ad',
            $purchase->id
        );
        if ($verified instanceof JsonResponse) {
            return $verified;
        }

        $purchase->payment_id = $verified['payment_id'];
        $purchase->markCompleted($request->payment_method);

        $this->recordPlatformFeeMoneyFlow(
            'banner_ad',
            (float) $purchase->price_paid,
            'product',
            'banner_purchase',
            $purchase->id,
            $verified['payment_id'],
            Auth::id() ? (int) Auth::id() : null,
            'USD',
            'Banner ad product purchase'
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed. Your download is ready.',
            'data' => [
                'purchase_id' => $purchase->id,
                'payment_status' => 'completed',
                'download_token' => $purchase->download_token,
                'download_url' => url('/api/v1/banner-ads/download/'.$purchase->download_token),
                'expires_at' => $purchase->download_token_expires_at,
            ],
        ]);
    }

    /**
     * Paid download only — Content-Disposition: attachment so browsers save, not display.
     */
    public function download(string $token): BinaryFileResponse|JsonResponse
    {
        if (!Schema::hasTable('banner_purchases')) {
            return response()->json(['message' => 'Not available'], 404);
        }

        $purchase = BannerPurchase::where('download_token', $token)->first();
        if (!$purchase || !$purchase->isDownloadValid()) {
            return response()->json(['message' => 'Invalid or expired download token. Payment required.'], 401);
        }

        $banner = $purchase->banner ?: BannerAd::find($purchase->banner_ad_id);
        if (!$banner) {
            return response()->json(['message' => 'Banner not found'], 404);
        }

        $path = $this->resolveBannerFilePath($banner);
        if (!$path) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
        $filename = ($banner->slug ?: 'banner-'.$banner->id).'.'.$ext;

        return response()->download($path, $filename, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function resolveBannerFilePath(BannerAd $banner): ?string
    {
        $raw = (string) $banner->getRawOriginal('banner_image');
        if ($raw === '') {
            return null;
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            $raw = basename(parse_url($raw, PHP_URL_PATH) ?: $raw);
        }

        $name = basename($raw);
        $candidates = [
            storage_path('app/public/banner-images/'.$name),
            storage_path('app/public/'.$name),
            public_path('storage/banner-images/'.$name),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
