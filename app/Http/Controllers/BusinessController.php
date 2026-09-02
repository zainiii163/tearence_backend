<?php

namespace App\Http\Controllers;

use App\Helpers\CommunityAuthHelper;
use App\Helpers\FileUploadHelper;
use App\Mail\BusinessStaffInviteMail;
use App\Models\AffiliateApplication;
use App\Models\BusinessAffiliateOffer;
use App\Models\BusinessStaffInvite;
use App\Models\Community;
use App\Models\CommunityFollow;
use App\Models\CommunityMember;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use App\Models\Job;
use App\Models\PromotedAdvert;
use App\Models\StaffManagement;
use App\Services\BusinessDashboardStatsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BusinessController extends APIController
{
    protected $folder;
    protected $fileUpload;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
                'listings',
                'getBySlug',
                'detail',
                'acceptStaffInvite',
            ]
        ]);

        $this->folder = 'business';
        $this->fileUpload = new FileUploadHelper();
    }

    /**
     * @OA\Get(
     *      path="/v1/business",
     *      tags={"Business"},
     *      summary="List business",
     *      description="Get list business",
     *      @OA\Parameter(
     *          name="id",
     *          description="Business ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="customer_id",
     *          description="Customer ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="skip",
     *          description="Skip",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          description="Limit",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          description="Sort by",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="sort_type",
     *          description="Sort type",
     *          in="query",
     *          @OA\Schema(
     *              default="asc",
     *              type="string",
     *              enum={"asc","desc"},
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function index(Request $request)
    {
        $query = CustomerBusiness::query()->where(function ($q) {
            $q->where('status', 'active')->orWhereNull('status');
        });
        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($store_id = $request->get('store_id')) {
            $query = $query->where('store_id', $store_id);
        }

        if ($customer_id = $request->get('customer_id')) {
            $query = $query->where('customer_id', $customer_id);
        }

        if ($sort = $request->get('sort')) {
            $query = $query->orderBy($sort, $request->get('sort_type') ? $request->get('sort_type') : 'asc');
        } else {
            $query = $query->orderBy('id');
        }

        if ($skip == "") {
            $query = $query->get();
            $total = $query->count();
        } else {
            $perPage = ($skip == "") ? $query->count() : (
                $request->has('limit') ? $limit : 10
            );
            $total = $query->count();
            $query = $query->skip($skip)->take($perPage)->get();
        }

        // Convert business_logo to full URL for each business
        $query->transform(function ($business) {
            if ($business->business_logo) {
                $business->business_logo = $this->fileUpload->getFile($business->business_logo, $this->folder);
            }
            return $business;
        });

        $result = [
            'items' => $query,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     * path="/v1/business",
     *   tags={"Business"},
     *   summary="Create customer business",
     *   description="Create customer business",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="business_name", type="string", format="string"),
     *            @OA\Property(property="business_phone_number", type="string", format="string"),
     *            @OA\Property(property="business_address", type="string", format="string"),
     *            @OA\Property(property="business_email", type="string", format="string"),
     *            @OA\Property(property="business_logo", type="string", format="string"),
     *            @OA\Property(property="business_website", type="string", format="string"),
     *            @OA\Property(property="business_owner", type="string", format="string"),
     *            @OA\Property(property="personal_phone_number", type="string", format="string"),
     *            @OA\Property(property="personal_email", type="string", format="string"),
     *            @OA\Property(property="business_company_registration", type="string", format="string"),
     *            @OA\Property(property="business_company_name", type="string", format="string"),
     *            @OA\Property(property="business_company_no", type="string", format="string"),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *   ),
     *   @OA\Response(
     *       response=401,
     *       description="Unauthenticated",
     *       @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *       response=403,
     *       description="Forbidden",
     *       @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *   @OA\Response(
     *       response=400,
     *       description="Bad Request",
     *       @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *   ),
     *)
     **/
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'business_name' => 'required',
            'business_phone_number' => 'required',
            'business_address' => 'required',
            'business_email' => 'required',
            'business_email' => 'email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        
        $customer_id = $user->customer_id ?? $user->getKey();
        
        if (!$customer_id) {
            return $this->errorResponse('Customer ID not found for user', Response::HTTP_BAD_REQUEST);
        }

        // upload business_logo if provided
        $fileName = null;
        if ($request->hasFile('business_logo') && $request->file('business_logo')->isValid()) {
            $fileName = $this->fileUpload->uploadFile($request->business_logo, $this->folder);
        }

        try {
            DB::beginTransaction();
            
            $query = new CustomerBusiness();
            $query->slug = Str::slug($request->business_name).'-'.Str::lower(Str::random(4));
            $query->customer_id = $customer_id;
            $query->business_name = $request->business_name;
            $query->business_description = $request->business_description;
            $query->business_phone_number = $request->business_phone_number;
            $query->business_address = $request->business_address;
            $query->business_email = $request->business_email;
            $query->business_logo = $fileName;
            $query->business_website = $request->business_website;
            $query->business_owner = $request->business_owner;
            $query->personal_phone_number = $request->personal_phone_number;
            $query->personal_email = $request->personal_email;
            $this->applyCompanyLegalFields($query, $request);
            $query->category_id = $request->category_id;
            $query->business_category_slug = $request->business_category_slug;
            $query->city = $request->city;
            $query->country = $request->country;
            if ($request->exists('postal_code')) {
                $query->postal_code = $request->postal_code;
            }
            $query->booking_url = $request->booking_url;
            if ($request->has('category_profile') || $request->has('profile')) {
                $query->category_profile = $this->mergeCategoryProfile(
                    null,
                    $request->input('category_profile', $request->input('profile'))
                );
                if (! empty($query->category_profile['booking_url']) && ! $request->filled('booking_url')) {
                    $query->booking_url = $query->category_profile['booking_url'];
                }
            }
            $query->status = 'active';
            
            $query->save();

            // Auto-create a Social Hub page for the business (Vehicles Hub social page)
            $this->ensureBusinessSocialPage($query, $user);

            DB::commit();
            return $this->successResponse($query, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Business creation error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Automatically create a Social Hub (community) page for a new business so
     * members can follow/like the business on the Vehicles Hub.
     */
    protected function ensureBusinessSocialPage(CustomerBusiness $business, $user)
    {
        if (!Schema::hasColumn('communities', 'business_id')) {
            return;
        }

        try {
            if (Community::where('business_id', $business->id)->exists()) {
                return;
            }

            $baseName = trim($business->business_name ?: 'Business') . ' — updates';
            $slug = Str::slug($baseName);
            $originalSlug = $slug ?: ('business-' . $business->id);
            $slug = $originalSlug;
            $counter = 1;
            while (Community::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $creatorUserId = CommunityAuthHelper::usersUserId($user, true);

            // Tag the business social page into the Vehicles Hub (community category)
            $vehiclesCategoryId = null;
            try {
                $vehiclesCategory = Category::where('slug', 'vehicles')->first();
                if ($vehiclesCategory) {
                    $vehiclesCategoryId = $vehiclesCategory->category_id;
                }
            } catch (\Throwable $e) {
                // category table may not exist on some environments - ignore
            }

            $community = Community::create([
                'community_id' => (string) Str::uuid(),
                'name' => $baseName,
                'slug' => $slug,
                'description' => $business->business_description
                    ?: ('Follow ' . ($business->business_name ?: 'this business') . ' for promotions, photos and updates.'),
                'cover_image' => $business->business_logo,
                'scope' => 'global',
                'city' => $business->city,
                'created_by' => $creatorUserId,
                'business_id' => $business->id,
                'category_id' => $vehiclesCategoryId,
                'members_count' => $creatorUserId ? 1 : 0,
                'beginner_friendly' => true,
                'rules' => [
                    'Be respectful',
                    'No spam',
                    'Share updates about this business only',
                ],
            ]);

            if ($creatorUserId) {
                CommunityMember::firstOrCreate(
                    ['community_id' => $community->community_id, 'user_id' => $creatorUserId],
                    ['id' => (string) Str::uuid(), 'role' => 'admin']
                );

                CommunityFollow::firstOrCreate(
                    ['community_id' => $community->community_id, 'user_id' => $creatorUserId],
                    ['id' => (string) Str::uuid()]
                );
            }
        } catch (\Throwable $e) {
            // Non-fatal: business was created; log the social-page failure
            \Log::warning('Auto business social page creation failed', [
                'business_id' => $business->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/business/{id}",
     *      tags={"Business"},
     *      summary="Detail customer business",
     *      description="Detail customer business",
     *      @OA\Parameter(
     *          name="id",
     *          description="Business ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function show($id)
    {
        // Accept numeric id or slug (FE links often use /business/{slug})
        $query = null;
        if (is_numeric($id)) {
            $query = CustomerBusiness::find($id);
        }
        if (!$query) {
            $query = CustomerBusiness::where('slug', $id)->first();
        }
        if (!$query) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        // Convert business_logo to full URL
        if ($query->business_logo) {
            $query->business_logo = $this->fileUpload->getFile($query->business_logo, $this->folder);
        }
        if ($query->cover_image) {
            try {
                $query->cover_image = $this->fileUpload->getFile($query->cover_image, $this->folder);
            } catch (\Throwable $e) {
                // keep relative path — frontend resolveStorageUrl can still handle it
            }
        }

        $careers = $this->careersForBusiness($query);
        $payload = $query->toArray();
        $payload['careers'] = $careers;
        $payload['jobs'] = $careers;

        // Attach live site reviews when table exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('site_reviews')) {
                $bizKey = (string) ($query->id);
                $reviews = \App\Models\SiteReview::approved()
                    ->forTarget('business', $bizKey)
                    ->orderByDesc('created_at')
                    ->limit(50)
                    ->get()
                    ->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'rating' => (int) $r->rating,
                            'comment' => $r->comment,
                            'author_name' => $r->author_name ?: 'Customer',
                            'created_at' => optional($r->created_at)->toIso8601String(),
                        ];
                    })
                    ->values()
                    ->all();
                $payload['reviews'] = $reviews;
                $payload['rating'] = count($reviews)
                    ? round(collect($reviews)->avg('rating'), 1)
                    : (float) ($payload['rating'] ?? data_get($payload, 'category_profile.average_rating', 0));
                $payload['reviews_count'] = count($reviews);
            }
        } catch (\Throwable $e) {
            // keep profile load resilient
        }

        return $this->successResponse($payload, '', Response::HTTP_OK);
    }

    /**
     * Public promotions / listings owned or branded by this business.
     */
    public function listings($id)
    {
        $business = null;
        if (is_numeric($id)) {
            $business = CustomerBusiness::find($id);
        }
        if (! $business) {
            $business = CustomerBusiness::where('slug', $id)->first();
        }
        if (! $business) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        $limit = (int) request('limit', 12);
        $limit = max(1, min($limit, 50));

        $items = [];
        if (Schema::hasTable('promoted_adverts')) {
            $q = PromotedAdvert::query()
                ->where(function ($inner) use ($business) {
                    $inner->where('business_name', $business->business_name);
                    if ($business->business_company_name) {
                        $inner->orWhere('business_name', $business->business_company_name);
                    }
                })
                ->where(function ($inner) {
                    $inner->where('is_active', true)->orWhere('status', 'active');
                })
                ->orderByDesc('is_featured')
                ->orderByDesc('id')
                ->limit($limit);

            $items = $q->get()->map(function ($ad) {
                return [
                    'id' => $ad->id,
                    'title' => $ad->title,
                    'slug' => $ad->slug,
                    'tagline' => $ad->tagline,
                    'description' => $ad->description,
                    'advert_type' => $ad->advert_type,
                    'category_name' => optional($ad->category)->name,
                    'image' => $ad->main_image,
                    'main_image' => $ad->main_image,
                    'price' => $ad->price,
                    'currency' => $ad->currency,
                    'website' => $ad->website,
                    'business_name' => $ad->business_name,
                ];
            })->values()->all();
        }

        return $this->successResponse(['items' => $items], '', Response::HTTP_OK);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function careersForBusiness(CustomerBusiness $business): array
    {
        $profile = is_array($business->category_profile) ? $business->category_profile : [];
        $fromProfile = [];
        if (! empty($profile['careers']) && is_array($profile['careers'])) {
            $fromProfile = $profile['careers'];
        }

        if (! Schema::hasTable('jobs') || ! $business->customer_id) {
            return array_values($fromProfile);
        }

        try {
            $jobs = Job::query()
                ->where(function ($q) use ($business) {
                    $q->where('user_id', $business->customer_id)
                        ->orWhere('company_name', $business->business_name);
                })
                ->where(function ($q) {
                    $q->where('is_active', true)->orWhere('status', 'active');
                })
                ->orderByDesc('id')
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            return array_values($fromProfile);
        }

        if ($jobs->isEmpty()) {
            return array_values($fromProfile);
        }

        return $jobs->map(function (Job $job) {
            $location = $job->location_name
                ?: collect([$job->city, $job->country])->filter()->implode(', ');

            return [
                'id' => $job->id,
                'title' => $job->title,
                'description' => $job->description,
                'location' => $location,
                'city' => $job->city,
                'country' => $job->country,
                'type' => $job->work_type,
                'work_type' => $job->work_type,
                'employment_type' => $job->work_type,
                'apply_url' => $job->application_link,
                'application_link' => $job->application_link,
            ];
        })->values()->all();
    }

    /**
     * @OA\Put(
     *      path="/v1/business/{id}",
     *      tags={"Business"},
     *      summary="Update customer business",
     *      description="Update customer business",
     *      security={
     *        {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Business ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="business_name", type="string", format="string"),
     *            @OA\Property(property="business_phone_number", type="string", format="string"),
     *            @OA\Property(property="business_address", type="string", format="string"),
     *            @OA\Property(property="business_email", type="string", format="string"),
     *            @OA\Property(property="business_logo", type="string", format="string"),
     *            @OA\Property(property="business_website", type="string", format="string"),
     *            @OA\Property(property="business_owner", type="string", format="string"),
     *            @OA\Property(property="personal_phone_number", type="string", format="string"),
     *            @OA\Property(property="personal_email", type="string", format="string"),
     *            @OA\Property(property="business_company_registration", type="string", format="string"),
     *            @OA\Property(property="business_company_name", type="string", format="string"),
     *            @OA\Property(property="business_company_no", type="string", format="string"),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'business_name' => 'required',
            'business_phone_number' => 'required',
            'business_address' => 'required',
            'business_email' => 'required',
            'business_email' => 'email',
        ]);
        
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $query = CustomerBusiness::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // upload business_logo if provided
        $imageName = "";
        if ($request->hasFile('business_logo') && $request->file('business_logo')->isValid()) {
            if ($query->business_logo && Storage::disk($this->folder)->exists($query->business_logo)) {
                Storage::disk($this->folder)->delete($query->business_logo);
            }
            $imageName = $this->fileUpload->uploadFile($request->business_logo, $this->folder);
        }

        try {
            DB::beginTransaction();
            if ($imageName != "") {
                $query->business_logo = $imageName;
            }
            $query->business_name = $request->business_name;
            $query->business_description = $request->business_description;
            $query->business_phone_number = $request->business_phone_number;
            $query->business_address = $request->business_address;
            $query->business_email = $request->business_email;
            $query->business_website = $request->business_website;
            $query->business_owner = $request->business_owner;
            $query->personal_phone_number = $request->personal_phone_number;
            $query->personal_email = $request->personal_email;
            $this->applyCompanyLegalFields($query, $request);
            $query->category_id = $request->category_id ?? $query->category_id;
            if ($request->filled('business_category_slug')) {
                $query->business_category_slug = $request->business_category_slug;
            }
            if ($request->has('city')) {
                $query->city = $request->city;
            }
            if ($request->has('country')) {
                $query->country = $request->country;
            }
            if ($request->has('booking_url')) {
                $query->booking_url = $request->booking_url;
            }
            if ($request->has('category_profile') || $request->has('profile')) {
                $query->category_profile = $this->mergeCategoryProfile(
                    $query->category_profile,
                    $request->input('category_profile', $request->input('profile'))
                );
                if (! empty($query->category_profile['booking_url']) && ! $request->filled('booking_url')) {
                    $query->booking_url = $query->category_profile['booking_url'];
                }
            }
            $query->status = $request->input('status', 'active');
            $query->save();

            DB::commit();
            return $this->successResponse($query, '', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Business update error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  @OA\Delete(
     *     path="/v1/business/{id}",
     *     summary="Delete customer business",
     *     description="Delete a single store based on the ID",
     *     tags={"Business"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="Business ID",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     * )
     */
    public function destroy($id)
    {
        $query = CustomerBusiness::find($id);
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();
        
        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/business/{customer_id}/detail",
     *      tags={"Business"},
     *      summary="Detail customer business",
     *      description="Detail customer business",
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function detail($customer_id)
    {
        $query = CustomerBusiness::where('customer_id', $customer_id)->first();
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    public function myBusiness()
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        $customer_id = $user->customer_id;
        $business = (object)[];
        $businessExists = CustomerBusiness::where('customer_id', $customer_id);
        if ($businessExists->exists()) {
            $business = $businessExists->first();
            // Convert business_logo to full URL
            if ($business->business_logo) {
                $business->business_logo = $this->fileUpload->getFile($business->business_logo, $this->folder);
            }
        }
        
        return $this->successResponse($business, '', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/business/{slug}",
     *      tags={"Business"},
     *      summary="Get business by slug",
     *      description="Get business by slug",
     *      @OA\Parameter(
     *          name="slug",
     *          description="Business slug",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/BusinessResource"
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResource")
     *      ),
     *  )
     */
    public function getBySlug($slug)
    {
        $query = CustomerBusiness::where('slug', $slug)->first();
        if (is_null($query)) {
            return $this->errorResponse('Business not found', Response::HTTP_NOT_FOUND);
        }

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * List business team members (StaffManagement wrapper for dashboard invite UI).
     */
    public function members($id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $business = CustomerBusiness::where('id', $id)->first();
        if (!$business) {
            return $this->errorResponse('Business not found', Response::HTTP_NOT_FOUND);
        }

        $isOwner = (int) $business->customer_id === (int) $user->customer_id;
        $staffRows = StaffManagement::where('entity_type', 'business')
            ->where('entity_id', $id)
            ->with('staffMember')
            ->get();

        $members = $staffRows->map(function ($row) {
            $person = $row->staffMember;
            return [
                'id' => $row->staff_id,
                'member_id' => $row->staff_id,
                'staff_id' => $row->staff_id,
                'customer_id' => $row->staff_customer_id,
                'email' => $person->email ?? null,
                'name' => trim(($person->first_name ?? '').' '.($person->last_name ?? '')),
                'role' => $row->role === 'admin' ? 'manager' : $row->role,
                'status' => $row->is_active ? 'active' : 'revoked',
            ];
        })->values();

        $pendingInvites = [];
        if (Schema::hasTable('business_staff_invites')) {
            $pendingInvites = BusinessStaffInvite::where('business_id', $id)
                ->where('status', 'pending')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->get()
                ->map(function ($invite) {
                    return [
                        'id' => 'invite-'.$invite->id,
                        'member_id' => null,
                        'invite_id' => $invite->id,
                        'email' => $invite->email,
                        'name' => $invite->email,
                        'role' => $invite->role,
                        'status' => 'pending',
                    ];
                })
                ->values()
                ->all();
        }

        return $this->successResponse([
            'members' => $members->concat($pendingInvites)->values(),
            'available_roles' => ['admin', 'manager', 'editor', 'viewer'],
            'can_manage' => $isOwner,
            'current_role' => $isOwner ? 'owner' : 'member',
            'pending_invites' => $pendingInvites,
        ], '', Response::HTTP_OK);
    }

    /**
     * Invite / add a team member by email.
     */
    public function addMember(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $business = CustomerBusiness::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();
        if (!$business) {
            return $this->errorResponse('Business not found or access denied', Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'nullable|in:admin,manager,editor,viewer',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        $email = strtolower(trim($request->email));
        $roleInput = $request->input('role', 'editor');
        $staffRole = in_array($roleInput, ['manager', 'admin'], true) ? 'admin' : $roleInput;
        if (!in_array($staffRole, ['admin', 'editor', 'viewer'], true)) {
            $staffRole = 'editor';
        }

        $staffCustomer = Customer::where('email', $email)->first();
        if (!$staffCustomer) {
            // Pending email invite for unregistered staff (Clive: invite members to manage page)
            return $this->createPendingStaffInvite($business, $user, $email, $roleInput ?: 'editor');
        }
        if ((int) $staffCustomer->customer_id === (int) $user->customer_id) {
            return $this->errorResponse('Cannot add yourself as a team member', Response::HTTP_BAD_REQUEST);
        }

        $existing = StaffManagement::where('customer_id', $user->customer_id)
            ->where('staff_customer_id', $staffCustomer->customer_id)
            ->where('entity_type', 'business')
            ->where('entity_id', $id)
            ->first();
        if ($existing) {
            return $this->errorResponse('Member already invited', Response::HTTP_CONFLICT);
        }

        $staff = StaffManagement::create([
            'customer_id' => $user->customer_id,
            'staff_customer_id' => $staffCustomer->customer_id,
            'entity_type' => 'business',
            'entity_id' => $id,
            'role' => $staffRole,
            'can_post_ads' => in_array($staffRole, ['admin', 'editor'], true),
            'can_edit_ads' => in_array($staffRole, ['admin', 'editor'], true),
            'can_delete_ads' => $staffRole === 'admin',
            'can_manage_payments' => $staffRole === 'admin',
            'can_view_analytics' => true,
            'can_manage_staff' => $staffRole === 'admin',
            'is_active' => true,
            'invited_at' => now(),
            'joined_at' => now(),
        ]);

        return $this->successResponse($staff->load('staffMember'), 'Member added', Response::HTTP_CREATED);
    }

    /**
     * Create pending invite + email for a user who has not registered yet.
     */
    protected function createPendingStaffInvite($business, $user, string $email, string $role)
    {
        if (!Schema::hasTable('business_staff_invites')) {
            return $this->errorResponse(
                'User not found. Ask them to register on Worldwide Adverts first.',
                Response::HTTP_NOT_FOUND
            );
        }

        $invite = BusinessStaffInvite::updateOrCreate(
            [
                'business_id' => $business->id,
                'email' => $email,
                'status' => 'pending',
            ],
            [
                'invited_by_customer_id' => $user->customer_id,
                'role' => $role,
                'token' => BusinessStaffInvite::mintToken(),
                'expires_at' => now()->addDays(14),
            ]
        );

        $frontend = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'https://worldwideadverts.uk')), '/');
        $signupUrl = $frontend.'/register?email='.urlencode($email).'&invite='.$invite->token;
        $acceptUrl = $frontend.'/my-business?invite='.$invite->token;
        $businessName = $business->business_name ?: $business->name ?: 'a business';
        $inviterName = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: 'A business owner';

        try {
            Mail::to($email)->send(new BusinessStaffInviteMail(
                $email,
                $businessName,
                $inviterName,
                $role,
                $signupUrl,
                $acceptUrl
            ));
        } catch (\Throwable $e) {
            // Invite still saved even if mail transport fails
        }

        return $this->successResponse([
            'invite' => $invite,
            'user_exists' => false,
            'signup_url' => $signupUrl,
            'accept_url' => $acceptUrl,
            'email_sent' => true,
        ], 'Invite sent. They must register with this email, then open the accept link.', Response::HTTP_CREATED);
    }

    /**
     * Accept a pending staff invite after the invitee has registered.
     */
    public function acceptStaffInvite(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }
        if (!Schema::hasTable('business_staff_invites')) {
            return $this->errorResponse('Invites not available', Response::HTTP_BAD_REQUEST);
        }

        $token = $request->input('token') ?: $request->query('token');
        if (!$token) {
            return $this->errorResponse('Invite token required', Response::HTTP_BAD_REQUEST);
        }

        $invite = BusinessStaffInvite::where('token', $token)->first();
        if (!$invite || !$invite->isPending()) {
            return $this->errorResponse('Invite not found or expired', Response::HTTP_NOT_FOUND);
        }

        $email = strtolower(trim((string) ($user->email ?? '')));
        if ($email !== strtolower($invite->email)) {
            return $this->errorResponse('Sign in with the invited email to accept.', Response::HTTP_FORBIDDEN);
        }

        $business = CustomerBusiness::find($invite->business_id);
        if (!$business) {
            return $this->errorResponse('Business not found', Response::HTTP_NOT_FOUND);
        }

        $staffRole = in_array($invite->role, ['manager', 'admin'], true) ? 'admin' : $invite->role;
        if (!in_array($staffRole, ['admin', 'editor', 'viewer'], true)) {
            $staffRole = 'editor';
        }

        $existing = StaffManagement::where('entity_type', 'business')
            ->where('entity_id', $business->id)
            ->where('staff_customer_id', $user->customer_id)
            ->first();

        if (!$existing) {
            StaffManagement::create([
                'customer_id' => $business->customer_id,
                'staff_customer_id' => $user->customer_id,
                'entity_type' => 'business',
                'entity_id' => $business->id,
                'role' => $staffRole,
                'can_post_ads' => in_array($staffRole, ['admin', 'editor'], true),
                'can_edit_ads' => in_array($staffRole, ['admin', 'editor'], true),
                'can_delete_ads' => $staffRole === 'admin',
                'can_manage_payments' => $staffRole === 'admin',
                'can_view_analytics' => true,
                'can_manage_staff' => $staffRole === 'admin',
                'is_active' => true,
                'invited_at' => $invite->created_at,
                'joined_at' => now(),
            ]);
        }

        $invite->update(['status' => 'accepted', 'accepted_at' => now()]);

        return $this->successResponse([
            'business_id' => $business->id,
            'role' => $invite->role,
        ], 'Invite accepted. You can manage this business page.', Response::HTTP_OK);
    }

    /**
     * Category-specific dashboard stats for the signed-in business owner.
     */
    public function dashboardStats(Request $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $category = strtolower(trim((string) $request->query('category', 'business')));
        $customerId = $user->customer_id;
        $userId = $user->user_id ?? $user->id ?? null;

        try {
            $payload = app(BusinessDashboardStatsService::class)->build($category, $customerId, $userId);
        } catch (\Throwable $e) {
            $payload = [
                'category' => $category,
                'stats' => [],
                'trends' => [],
                'performance' => [],
                'recent_listings' => [],
                'affiliate_summary' => ['offers' => 0, 'pending_applicants' => 0, 'total_applications' => 0],
                'updated_at' => now()->toIso8601String(),
            ];
        }

        return $this->successResponse($payload, '', Response::HTTP_OK);
    }

    public function updateMember(Request $request, $id, $memberId)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $business = CustomerBusiness::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();
        if (!$business) {
            return $this->errorResponse('Business not found or access denied', Response::HTTP_FORBIDDEN);
        }

        $staff = StaffManagement::where('staff_id', $memberId)
            ->where('entity_type', 'business')
            ->where('entity_id', $id)
            ->first();
        if (!$staff) {
            return $this->errorResponse('Member not found', Response::HTTP_NOT_FOUND);
        }

        $role = $request->input('role', $staff->role);
        if ($role === 'manager' || $role === 'admin') {
            $role = 'admin';
        }
        if (!in_array($role, ['admin', 'editor', 'viewer'], true)) {
            $role = $staff->role;
        }

        $staff->role = $role;
        $staff->can_post_ads = in_array($role, ['admin', 'editor'], true);
        $staff->can_edit_ads = in_array($role, ['admin', 'editor'], true);
        $staff->can_delete_ads = $role === 'admin';
        $staff->can_manage_payments = $role === 'admin';
        $staff->can_manage_staff = $role === 'admin';
        $staff->save();

        return $this->successResponse($staff, 'Member updated', Response::HTTP_OK);
    }

    public function removeMember($id, $memberId)
    {
        $user = auth('api')->user();
        if (!$user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $business = CustomerBusiness::where('id', $id)
            ->where('customer_id', $user->customer_id)
            ->first();
        if (!$business) {
            return $this->errorResponse('Business not found or access denied', Response::HTTP_FORBIDDEN);
        }

        $staff = StaffManagement::where('staff_id', $memberId)
            ->where('entity_type', 'business')
            ->where('entity_id', $id)
            ->first();
        if (!$staff) {
            return $this->errorResponse('Member not found', Response::HTTP_NOT_FOUND);
        }

        $staff->delete();

        return $this->successResponse(null, 'Member removed', Response::HTTP_OK);
    }

    /**
     * Save company legal details after signup (dashboard complete-profile form).
     */
    public function completeProfile(Request $request)
    {
        $user = auth('api')->user();
        if (! $user) {
            return $this->errorResponse('Unauthenticated', Response::HTTP_UNAUTHORIZED);
        }

        $customerId = $user->customer_id ?? $user->getKey();
        $business = CustomerBusiness::where('customer_id', $customerId)->first();

        if (! $business) {
            $business = new CustomerBusiness();
            $business->customer_id = $customerId;
            $business->status = 'active';
            $name = $request->input('business_name')
                ?: $request->input('business_company_name')
                ?: trim(($request->first_name ?? '').' '.($request->last_name ?? ''))
                ?: 'My business';
            $business->business_name = $name;
            $business->slug = Str::slug($name).'-'.Str::lower(Str::random(4));
            $business->business_email = $request->input('business_email') ?: ($user->email ?? '');
            $business->business_phone_number = $request->input('business_phone_number') ?: ($request->phone ?: '');
            $business->business_address = $request->input('business_address') ?: '';
        }

        $this->applyCompanyLegalFields($business, $request);

        if ($request->filled('business_name')) {
            $business->business_name = $request->business_name;
        }
        if ($request->filled('business_email')) {
            $business->business_email = $request->business_email;
        }
        if ($request->filled('phone') && ! $request->filled('business_phone_number')) {
            $business->business_phone_number = $request->phone;
        }
        if ($request->filled('website') && ! $request->filled('business_website')) {
            $business->business_website = $request->website;
        }
        if ($request->filled('company_registration_number') && ! $request->filled('business_company_no')) {
            $business->business_company_no = $request->company_registration_number;
            $business->business_company_registration = $request->company_registration_number;
        }
        if ($request->filled('city')) {
            $business->city = $request->city;
        }
        if ($request->filled('country')) {
            $business->country = $request->country;
        }
        if ($request->filled('business_address')) {
            $business->business_address = $request->business_address;
        }
        if ($request->filled('business_category_slug') || $request->filled('dashboard_category')) {
            $business->business_category_slug = $request->input('business_category_slug', $request->dashboard_category);
        }
        if ($request->filled('booking_url')) {
            $business->booking_url = $request->booking_url;
        }
        if ($request->has('category_profile')) {
            $business->category_profile = $this->mergeCategoryProfile(
                $business->category_profile,
                $request->input('category_profile')
            );
            if (! empty($business->category_profile['booking_url']) && ! $request->filled('booking_url')) {
                $business->booking_url = $business->category_profile['booking_url'];
            }
        }

        $business->save();

        return $this->successResponse($business, 'Business profile saved', Response::HTTP_OK);
    }

    /**
     * Merge category_profile JSON so partial form updates do not wipe careers, social_links, etc.
     * List-like keys from the request fully replace the previous list.
     */
    protected function mergeCategoryProfile($existing, $incoming): ?array
    {
        if (is_string($incoming)) {
            $decoded = json_decode($incoming, true);
            $incoming = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
        if (! is_array($incoming)) {
            return is_array($existing) ? $existing : null;
        }

        $base = is_array($existing) ? $existing : [];
        $merged = array_replace_recursive($base, $incoming);

        foreach (['social_links', 'booking_slots', 'services', 'careers', 'makes_serviced', 'highlights'] as $listKey) {
            if (array_key_exists($listKey, $incoming)) {
                $merged[$listKey] = $incoming[$listKey];
            }
        }

        return $merged;
    }

    /**
     * Company name, number, incorporation, VAT, DUNS, website, email, phone, address.
     */
    protected function applyCompanyLegalFields(CustomerBusiness $query, Request $request): void
    {
        $map = [
            'business_company_name' => 'business_company_name',
            'business_company_no' => 'business_company_no',
            'business_company_registration' => 'business_company_registration',
            'vat_number' => 'vat_number',
            'duns_number' => 'duns_number',
            'incorporation_date' => 'incorporation_date',
            'business_website' => 'business_website',
            'business_email' => 'business_email',
            'business_phone_number' => 'business_phone_number',
            'business_address' => 'business_address',
            'postal_code' => 'postal_code',
        ];

        foreach ($map as $requestKey => $column) {
            if ($request->exists($requestKey) && Schema::hasColumn('customer_business', $column)) {
                $value = $request->input($requestKey);
                $query->{$column} = $value === '' ? null : $value;
            }
        }
    }
}