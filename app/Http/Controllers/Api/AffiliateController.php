<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCategory;
use App\Models\BusinessAffiliateOffer;
use App\Models\UserAffiliatePost;
use App\Models\AffiliateUpsellPlan;
use App\Models\AffiliateApplication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AffiliateController extends Controller
{
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
            ->active();

        // Filters
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

        // Visibility filters
        if ($request->featured) {
            $query->featured();
        }

        if ($request->promoted) {
            $query->promoted();
        }

        if ($request->sponsored) {
            $query->sponsored();
        }

        // Sort
        $sort = $request->sort ?? 'created_at';
        $order = $request->order ?? 'desc';
        
        if (in_array($sort, ['created_at', 'views', 'clicks', 'commission_rate'])) {
            $query->orderBy($sort, $order);
        }

        $offers = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Get user affiliate posts with filters.
     */
    public function userPosts(Request $request): JsonResponse
    {
        $query = UserAffiliatePost::with(['user', 'affiliateCategory'])
            ->active();

        // Filters
        if ($request->category_id) {
            $query->where('affiliate_category_id', $request->category_id);
        }

        if ($request->country) {
            $query->where('country', $request->country);
        }

        if ($request->target_audience) {
            $query->where('target_audience', 'like', '%' . $request->target_audience . '%');
        }

        // Visibility filters
        if ($request->featured) {
            $query->featured();
        }

        if ($request->promoted) {
            $query->promoted();
        }

        if ($request->sponsored) {
            $query->sponsored();
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
     * Get a specific business affiliate offer.
     */
    public function businessOffer(string $id): JsonResponse
    {
        $offer = BusinessAffiliateOffer::with(['user', 'affiliateCategory', 'analytics'])
            ->findOrFail($id);

        // Increment views
        $offer->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $offer,
        ]);
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
            'cookie_duration' => 'required|integer|min:1',
            'allowed_traffic_types' => 'nullable|array',
            'allowed_traffic_types.*' => 'in:social_media,email,ppc,blogging,influencer,other',
            'restrictions' => 'nullable|string',
            'tracking_link' => 'required|url',
            'promotional_assets' => 'nullable|array',
            'business_email' => 'required|email',
            'website_url' => 'nullable|url',
            'verification_document' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $offer = BusinessAffiliateOffer::create([
            'user_id' => Auth::id(),
            'affiliate_category_id' => $request->affiliate_category_id,
            'business_name' => $request->business_name,
            'product_service_title' => $request->product_service_title,
            'tagline' => $request->tagline,
            'description' => $request->description,
            'country' => $request->country,
            'region' => $request->region,
            'commission_type' => $request->commission_type,
            'commission_rate' => $request->commission_rate,
            'cookie_duration' => $request->cookie_duration,
            'allowed_traffic_types' => $request->allowed_traffic_types,
            'restrictions' => $request->restrictions,
            'tracking_link' => $request->tracking_link,
            'promotional_assets' => $request->promotional_assets,
            'business_email' => $request->business_email,
            'website_url' => $request->website_url,
            'verification_document' => $request->verification_document,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Business affiliate offer created successfully',
            'data' => $offer->load('affiliateCategory'),
        ], 201);
    }

    /**
     * Create a new user affiliate post.
     */
    public function createUserPost(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'affiliate_category_id' => 'required|exists:affiliate_categories,id',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'affiliate_link' => 'required|url',
            'image' => 'required|string',
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

        $post = UserAffiliatePost::create([
            'user_id' => Auth::id(),
            'affiliate_category_id' => $request->affiliate_category_id,
            'title' => $request->title,
            'description' => $request->description,
            'country' => $request->country,
            'region' => $request->region,
            'affiliate_link' => $request->affiliate_link,
            'image' => $request->image,
            'hashtags' => $request->hashtags,
            'target_audience' => $request->target_audience,
        ]);

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
            'message' => 'nullable|string',
            'promotion_methods' => 'nullable|array',
            'promotion_methods.*' => 'string',
            'audience_details' => 'nullable|array',
            'website_url' => 'nullable|url',
            'social_media_links' => 'nullable|array',
            'estimated_monthly_visitors' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $offer = BusinessAffiliateOffer::findOrFail($offerId);

        // Check if user already applied
        $existingApplication = AffiliateApplication::where('business_affiliate_offer_id', $offerId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingApplication) {
            return response()->json([
                'success' => false,
                'message' => 'You have already applied to this offer',
            ], 422);
        }

        $application = AffiliateApplication::create([
            'business_affiliate_offer_id' => $offerId,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'promotion_methods' => $request->promotion_methods,
            'audience_details' => $request->audience_details,
            'website_url' => $request->website_url,
            'social_media_links' => $request->social_media_links,
            'estimated_monthly_visitors' => $request->estimated_monthly_visitors,
        ]);

        // Increment applications count
        $offer->increment('applications');

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => $application,
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
     * Get user's affiliate applications.
     */
    public function myApplications(): JsonResponse
    {
        $applications = AffiliateApplication::where('user_id', Auth::id())
            ->with(['businessAffiliateOffer.affiliateCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $applications,
        ]);
    }

    /**
     * Get user's business offers.
     */
    public function myBusinessOffers(): JsonResponse
    {
        $offers = BusinessAffiliateOffer::where('user_id', Auth::id())
            ->with(['affiliateCategory', 'applications'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }

    /**
     * Get user's affiliate posts.
     */
    public function myUserPosts(): JsonResponse
    {
        $posts = UserAffiliatePost::where('user_id', Auth::id())
            ->with(['affiliateCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
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
                ->with(['user', 'affiliateCategory'])
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                      ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->limit(10)
                ->get();

            $results['user_posts'] = $userPosts;
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
            'tracking_link' => 'sometimes|required|url',
            'promotional_assets' => 'nullable|array',
            'business_email' => 'sometimes|required|email',
            'website_url' => 'nullable|url',
            'verification_document' => 'nullable|string',
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
            'restrictions', 'tracking_link', 'promotional_assets',
            'business_email', 'website_url', 'verification_document'
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
            'description' => 'sometimes|required|string',
            'affiliate_category_id' => 'sometimes|required|exists:affiliate_categories,id',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'affiliate_link' => 'sometimes|required|url',
            'image' => 'sometimes|required|string',
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
            'file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            $fileUpload = new \App\Helpers\FileUploadHelper();
            $result = $fileUpload->uploadFile($file, 'affiliate_images');
            
            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'url' => $result['url'],
                    'id' => $result['id'] ?? null,
                    'filename' => $result['filename'] ?? $file->getClientOriginalName(),
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
