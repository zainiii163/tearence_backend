<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\APIController;
use App\Models\AffiliatePost;
use App\Models\AffiliatePostUpsell;
use App\Models\AffiliateUpsellPlan;
use App\Models\RevenueTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AffiliatePostController extends APIController
{
    protected $fileUpload;
    protected $folder = 'affiliate_posts';

    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
                'getByCategory',
                'getFeatured',
                'getSponsored',
                'getPromoted'
            ]
        ]);
        $this->fileUpload = new FileUploadHelper();
    }

    /**
     * Display a listing of affiliate posts.
     */
    public function index(Request $request)
    {
        $query = AffiliatePost::with(['customer', 'category', 'activeUpsell.upsellPlan']);

        // Filter by post type
        if ($request->has('post_type')) {
            $query->where('post_type', $request->post_type);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by country/region
        if ($request->has('country_region')) {
            $query->where('country_region', $request->country_region);
        }

        // Filter by upsell tier
        if ($request->has('upsell_tier')) {
            $query->where('upsell_tier', $request->upsell_tier);
        }

        // Only show active and approved posts
        $query->active();

        // Search functionality
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('tagline', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('business_name', 'like', "%{$searchTerm}%");
            });
        }

        // Order by upsell priority and creation date
        $query->orderByUpsellPriority();

        // Pagination
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);

        $total = $query->count();
        $posts = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $this->successResponse([
            'items' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ], 'Affiliate posts retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Store a newly created affiliate post.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_type' => 'required|in:business,promoter',
            'title' => 'required|string|max:200',
            'tagline' => 'nullable|string|max:80',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:ea_categories,id',
            'country_region' => 'nullable|string|max:100',
            
            // Business-specific fields
            'business_name' => 'required_if:post_type,business|string|max:200',
            'commission_rate' => 'required_if:post_type,business|string|max:50',
            'cookie_duration' => 'nullable|integer|min:1',
            'allowed_traffic_types' => 'nullable|array',
            'allowed_traffic_types.*' => 'in:social,email,ppc,blogging,influencer,other',
            'restrictions' => 'nullable|string',
            'affiliate_link' => 'required_if:post_type,business|url|max:500',
            'business_email' => 'required_if:post_type,business|email|max:200',
            'website_url' => 'nullable|url|max:500',
            'verification_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            
            // Promoter-specific fields
            'target_audience' => 'nullable|string|max:100',
            'hashtags' => 'nullable|array',
            'hashtags.*' => 'string|max:50',
            
            // Common fields
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'promotional_assets' => 'nullable|array|max:10',
            'promotional_assets.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,mp4|max:10240',
            
            // Upsell selection
            'upsell_plan_id' => 'nullable|exists:ea_affiliate_upsell_plans,id',
            'payment_method' => 'required_if:upsell_plan_id,paypal,stripe,bank_transfer',
            'transaction_id' => 'required_if:upsell_plan_id|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            // Handle file uploads
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $images[] = $this->fileUpload->uploadFile($image, $this->folder);
                }
            }

            $promotionalAssets = [];
            if ($request->hasFile('promotional_assets')) {
                foreach ($request->file('promotional_assets') as $asset) {
                    $promotionalAssets[] = $this->fileUpload->uploadFile($asset, $this->folder . '/assets');
                }
            }

            $verificationDocument = null;
            if ($request->hasFile('verification_document')) {
                $verificationDocument = $this->fileUpload->uploadFile($request->file('verification_document'), $this->folder . '/verification');
            }

            // Get authenticated user
            $user = auth('api')->user();
            if (!$user || !isset($user->customer_id)) {
                return $this->errorResponse('User not authenticated or customer ID not found', Response::HTTP_UNAUTHORIZED);
            }

            // Determine upsell tier
            $upsellTier = 'standard';
            if ($request->has('upsell_plan_id')) {
                $upsellPlan = AffiliateUpsellPlan::find($request->upsell_plan_id);
                if ($upsellPlan) {
                    $upsellTier = $upsellPlan->slug;
                }
            }

            // Create affiliate post
            $post = AffiliatePost::create([
                'post_type' => $request->post_type,
                'title' => $request->title,
                'tagline' => $request->tagline,
                'description' => $request->description,
                'business_name' => $request->business_name,
                'commission_rate' => $request->commission_rate,
                'cookie_duration' => $request->cookie_duration,
                'allowed_traffic_types' => $request->allowed_traffic_types,
                'restrictions' => $request->restrictions,
                'affiliate_link' => $request->affiliate_link,
                'business_email' => $request->business_email,
                'website_url' => $request->website_url,
                'verification_document' => $verificationDocument,
                'target_audience' => $request->target_audience,
                'hashtags' => $request->hashtags,
                'country_region' => $request->country_region,
                'images' => $images,
                'promotional_assets' => $promotionalAssets,
                'customer_id' => $user->customer_id,
                'category_id' => $request->category_id,
                'upsell_tier' => $upsellTier,
                'status' => 'pending',
                'is_active' => false,
            ]);

            // Handle upsell payment if selected
            if ($request->has('upsell_plan_id') && $request->upsell_plan_id) {
                $upsellPlan = AffiliateUpsellPlan::find($request->upsell_plan_id);
                
                if ($upsellPlan) {
                    // Create revenue tracking record
                    $revenue = RevenueTracking::create([
                        'customer_id' => $user->customer_id,
                        'ad_type' => 'affiliate_post',
                        'amount' => $upsellPlan->price,
                        'payment_method' => $request->payment_method,
                        'transaction_id' => $request->transaction_id,
                        'status' => 'paid',
                        'description' => "Affiliate post upsell - {$upsellPlan->name}"
                    ]);

                    // Create post upsell record
                    $postUpsell = AffiliatePostUpsell::create([
                        'affiliate_post_id' => $post->id,
                        'upsell_plan_id' => $upsellPlan->id,
                        'customer_id' => $user->customer_id,
                        'amount_paid' => $upsellPlan->price,
                        'currency' => $upsellPlan->currency,
                        'payment_method' => $request->payment_method,
                        'transaction_id' => $request->transaction_id,
                        'payment_status' => 'paid',
                        'is_active' => true,
                    ]);

                    // Update revenue tracking with post upsell ID
                    $revenue->update(['affiliate_post_upsell_id' => $postUpsell->id]);
                }
            }

            DB::commit();

            // Load relationships for response
            $post->load(['customer', 'category', 'activeUpsell.upsellPlan']);

            return $this->successResponse($post, 'Affiliate post created successfully', Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified affiliate post.
     */
    public function show($id)
    {
        $post = AffiliatePost::with(['customer', 'category', 'activeUpsell.upsellPlan'])
            ->where('id', $id)
            ->active()
            ->first();

        if (!$post) {
            return $this->errorResponse('Affiliate post not found', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($post, 'Affiliate post retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Update the specified affiliate post.
     */
    public function update(Request $request, $id)
    {
        $post = AffiliatePost::where('id', $id)
            ->where('customer_id', auth('api')->user()->customer_id)
            ->first();

        if (!$post) {
            return $this->errorResponse('Affiliate post not found or unauthorized', Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:200',
            'tagline' => 'nullable|string|max:80',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:ea_categories,id',
            'country_region' => 'nullable|string|max:100',
            
            // Business-specific fields
            'business_name' => 'sometimes|required_if:post_type,business|string|max:200',
            'commission_rate' => 'sometimes|required_if:post_type,business|string|max:50',
            'cookie_duration' => 'nullable|integer|min:1',
            'allowed_traffic_types' => 'nullable|array',
            'allowed_traffic_types.*' => 'in:social,email,ppc,blogging,influencer,other',
            'restrictions' => 'nullable|string',
            'affiliate_link' => 'sometimes|required_if:post_type,business|url|max:500',
            'business_email' => 'sometimes|required_if:post_type,business|email|max:200',
            'website_url' => 'nullable|url|max:500',
            
            // Promoter-specific fields
            'target_audience' => 'nullable|string|max:100',
            'hashtags' => 'nullable|array',
            'hashtags.*' => 'string|max:50',
            
            // Common fields
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            // Handle file uploads
            $images = $post->images ?? [];
            if ($request->hasFile('images')) {
                // Delete old images
                foreach ($images as $oldImage) {
                    Storage::disk($this->folder)->delete($oldImage);
                }
                
                // Upload new images
                $images = [];
                foreach ($request->file('images') as $image) {
                    $images[] = $this->fileUpload->uploadFile($image, $this->folder);
                }
            }

            // Update post
            $post->update([
                'title' => $request->title ?? $post->title,
                'tagline' => $request->tagline ?? $post->tagline,
                'description' => $request->description ?? $post->description,
                'business_name' => $request->business_name ?? $post->business_name,
                'commission_rate' => $request->commission_rate ?? $post->commission_rate,
                'cookie_duration' => $request->cookie_duration ?? $post->cookie_duration,
                'allowed_traffic_types' => $request->allowed_traffic_types ?? $post->allowed_traffic_types,
                'restrictions' => $request->restrictions ?? $post->restrictions,
                'affiliate_link' => $request->affiliate_link ?? $post->affiliate_link,
                'business_email' => $request->business_email ?? $post->business_email,
                'website_url' => $request->website_url ?? $post->website_url,
                'target_audience' => $request->target_audience ?? $post->target_audience,
                'hashtags' => $request->hashtags ?? $post->hashtags,
                'country_region' => $request->country_region ?? $post->country_region,
                'images' => $images,
                'category_id' => $request->category_id ?? $post->category_id,
            ]);

            DB::commit();

            // Load relationships for response
            $post->load(['customer', 'category', 'activeUpsell.upsellPlan']);

            return $this->successResponse($post, 'Affiliate post updated successfully', Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified affiliate post.
     */
    public function destroy($id)
    {
        $post = AffiliatePost::where('id', $id)
            ->where('customer_id', auth('api')->user()->customer_id)
            ->first();

        if (!$post) {
            return $this->errorResponse('Affiliate post not found or unauthorized', Response::HTTP_NOT_FOUND);
        }

        try {
            DB::beginTransaction();

            // Delete associated files
            if ($post->images) {
                foreach ($post->images as $image) {
                    Storage::disk($this->folder)->delete($image);
                }
            }

            if ($post->promotional_assets) {
                foreach ($post->promotional_assets as $asset) {
                    Storage::disk($this->folder . '/assets')->delete($asset);
                }
            }

            if ($post->verification_document) {
                Storage::disk($this->folder . '/verification')->delete($post->verification_document);
            }

            // Delete the post (cascade will handle upsells)
            $post->delete();

            DB::commit();

            return $this->successResponse(null, 'Affiliate post deleted successfully', Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get my affiliate posts.
     */
    public function myPosts(Request $request)
    {
        $user = auth('api')->user();
        if (!$user || !isset($user->customer_id)) {
            return $this->errorResponse('User not authenticated or customer ID not found', Response::HTTP_UNAUTHORIZED);
        }

        $query = AffiliatePost::with(['customer', 'category', 'activeUpsell.upsellPlan'])
            ->where('customer_id', $user->customer_id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by post type
        if ($request->has('post_type')) {
            $query->where('post_type', $request->post_type);
        }

        // Order by creation date
        $query->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);

        $total = $query->count();
        $posts = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $this->successResponse([
            'items' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ], 'My affiliate posts retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get posts by category.
     */
    public function getByCategory($categoryId, Request $request)
    {
        $query = AffiliatePost::with(['customer', 'category', 'activeUpsell.upsellPlan'])
            ->where('category_id', $categoryId)
            ->active();

        // Order by upsell priority
        $query->orderByUpsellPriority();

        // Pagination
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);

        $total = $query->count();
        $posts = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $this->successResponse([
            'items' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ], 'Category affiliate posts retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get featured posts.
     */
    public function getFeatured(Request $request)
    {
        $query = AffiliatePost::with(['customer', 'category', 'activeUpsell.upsellPlan'])
            ->active()
            ->where(function ($q) {
                $q->where('upsell_tier', 'featured')
                  ->orWhereHas('activeUpsell', function ($subQuery) {
                      $subQuery->whereHas('upsellPlan', function ($planQuery) {
                          $planQuery->where('slug', 'featured');
                      });
                  });
            });

        // Order by upsell priority
        $query->orderByUpsellPriority();

        // Pagination
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);

        $total = $query->count();
        $posts = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $this->successResponse([
            'items' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ], 'Featured affiliate posts retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get sponsored posts.
     */
    public function getSponsored(Request $request)
    {
        $query = AffiliatePost::with(['customer', 'category', 'activeUpsell.upsellPlan'])
            ->active()
            ->where(function ($q) {
                $q->where('upsell_tier', 'sponsored')
                  ->orWhereHas('activeUpsell', function ($subQuery) {
                      $subQuery->whereHas('upsellPlan', function ($planQuery) {
                          $planQuery->where('slug', 'sponsored');
                      });
                  });
            });

        // Order by upsell priority
        $query->orderByUpsellPriority();

        // Pagination
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);

        $total = $query->count();
        $posts = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $this->successResponse([
            'items' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ], 'Sponsored affiliate posts retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Get promoted posts.
     */
    public function getPromoted(Request $request)
    {
        $query = AffiliatePost::with(['customer', 'category', 'activeUpsell.upsellPlan'])
            ->active()
            ->where(function ($q) {
                $q->where('upsell_tier', 'promoted')
                  ->orWhereHas('activeUpsell', function ($subQuery) {
                      $subQuery->whereHas('upsellPlan', function ($planQuery) {
                          $planQuery->where('slug', 'promoted');
                      });
                  });
            });

        // Order by upsell priority
        $query->orderByUpsellPriority();

        // Pagination
        $perPage = $request->get('per_page', 12);
        $page = $request->get('page', 1);

        $total = $query->count();
        $posts = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return $this->successResponse([
            'items' => $posts,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
        ], 'Promoted affiliate posts retrieved successfully', Response::HTTP_OK);
    }
}
