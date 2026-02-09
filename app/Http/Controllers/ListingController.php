<?php

namespace App\Http\Controllers;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\APIController;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use App\Models\CustomerStore;
use App\Models\Listing;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ListingController extends APIController
{
    /**
     * Create a new ListingController instance.
     *
     * @return void
     */
    protected $folder;
    protected $fileUpload;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'index',
                'show',
                'featured',
                'new',
                'promoted',
                'ebay',
                'global',
            ]
        ]);
        $this->folder = 'listings';
        $this->fileUpload = new FileUploadHelper();
    }

    /**
     * @OA\Get(
     *      path="/v1/listing",
     *      tags={"Listing"},
     *      summary="List ads",
     *      description="Get ads list",
     *      @OA\Parameter(
     *          name="listing_id",
     *          description="Listing ID",
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
     *          name="category",
     *          description="Category",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="currencies",
     *          description="Currencies",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="min_price",
     *          description="Minimum Price",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="max_price",
     *          description="Maximum Price",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="title",
     *          description="Title",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Status",
     *          in="query",
     *          @OA\Schema(
     *              default="active",
     *              type="string",
     *              enum={"active","inactive"},
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
     *                 ref="#/components/schemas/ListingResource"
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
        $query = Listing::query();
        $query = $query->where('promo_expire_at', '>=', '2023-01-01 00:00:00');
        
        // Only show approved listings
        $query = $query->where('approval_status', 'approved');
        
        // Pagination parameters
        $page = (int)$request->get('page', 1);
        $perPage = (int)$request->get('per_page', 15);
        $skip = $request->get('skip');
        $limit = $request->get('limit');
        
        $categoryItems = [];
        $min_price = (int)$request->get('min_price');
        $max_price = (int)$request->get('max_price');

        // Basic filters
        if ($id = $request->get('listing_id')) {
            $query = $query->where('listing_id', $id);
        }

        if ($customer_id = $request->get('customer_id')) {
            $query = $query->where('customer_id', $customer_id);
        }

        // Category filtering with parent/child support
        if ($category = $request->get('category')) {
            $categoryData = Category::where('slug', $category)->first();
            if ($categoryData) {
                $parentCategories = Category::with('childs')->where('category_id', $categoryData->category_id)->get();
                if (count($parentCategories) > 0) {
                    foreach ($parentCategories as $parentCategory) {
                        if ($parentCategory->parent_id == NULL) { // as parent
                            if (count($parentCategory->childs) > 0) {
                                foreach ($parentCategory->childs as $childCategory) {
                                    $categoryItems[] = $childCategory->category_id;
                                }
                            } else {
                                $categoryItems[] = $parentCategory->category_id;
                            }
                        }
                    }
                }
                if (count($categoryItems) > 0) {
                    $query = $query->whereIn('category_id', $categoryItems);
                } else {
                    $query = $query->where('category_id', $categoryData->category_id);
                }
            }
        }

        // Category ID filter
        if ($category_id = $request->get('category_id')) {
            $query = $query->where('category_id', $category_id);
        }

        // Currency filter
        if ($currency = $request->get('currency')) {
            $currencies = explode(",", $currency);
            $currData = Currency::whereIn('code', $currencies)->get();
            if ($currData->count() > 0) {
                $query = $query->whereIn('currency_id', $currData->pluck('currency_id')->all());
            }
        }

        // Price range filter (for general listings)
        if ($min_price) {
            $query = $query->where(function ($q) use ($min_price) {
                $q->where('price', '>=', $min_price)
                  ->orWhere('salary_min', '>=', $min_price);
            });
        }

        if ($max_price) {
            $query = $query->where(function ($q) use ($max_price) {
                $q->where('price', '<=', $max_price)
                  ->orWhere('salary_max', '<=', $max_price);
            });
        }

        // Job-specific filters
        if ($job_type = $request->get('job_type')) {
            $jobTypes = is_array($job_type) ? $job_type : explode(',', $job_type);
            $query = $query->whereIn('job_type', $jobTypes);
        }

        if ($salary_min = $request->get('salary_min')) {
            $query = $query->where(function ($q) use ($salary_min) {
                $q->where('salary_min', '>=', $salary_min)
                  ->orWhere('salary_max', '>=', $salary_min);
            });
        }

        if ($salary_max = $request->get('salary_max')) {
            $query = $query->where(function ($q) use ($salary_max) {
                $q->where('salary_max', '<=', $salary_max)
                  ->orWhere('salary_min', '<=', $salary_max);
            });
        }

        // Location filter
        if ($location_id = $request->get('location_id')) {
            $locationIds = is_array($location_id) ? $location_id : explode(',', $location_id);
            $query = $query->whereIn('location_id', $locationIds);
        }

        // Status filter
        if ($status = $request->get('status')) {
            $query = $query->where('status', $status);
        } else {
            // Default to active if not specified
            $query = $query->where('status', 'active');
        }

        // Featured jobs filter
        if ($request->has('featured')) {
            $isFeatured = filter_var($request->get('featured'), FILTER_VALIDATE_BOOLEAN);
            if ($isFeatured) {
                $query = $query->where('is_featured', true)
                    ->where(function($q) {
                        $q->whereNull('featured_expires_at')
                          ->orWhere('featured_expires_at', '>', now());
                    });
            }
        }

        // Suggested jobs filter
        if ($request->has('suggested')) {
            $isSuggested = filter_var($request->get('suggested'), FILTER_VALIDATE_BOOLEAN);
            if ($isSuggested) {
                $query = $query->where('is_suggested', true)
                    ->where(function($q) {
                        $q->whereNull('suggested_expires_at')
                          ->orWhere('suggested_expires_at', '>', now());
                    });
            }
        }

        // Paid filter
        if ($request->has('paid')) {
            $isPaid = filter_var($request->get('paid'), FILTER_VALIDATE_BOOLEAN);
            if ($isPaid) {
                $query = $query->where('is_paid', true)
                    ->where(function($q) {
                        $q->whereNull('paid_expires_at')
                          ->orWhere('paid_expires_at', '>', now());
                    });
            }
        }

        // Promoted filter
        if ($request->has('promoted')) {
            $isPromoted = filter_var($request->get('promoted'), FILTER_VALIDATE_BOOLEAN);
            if ($isPromoted) {
                $query = $query->where('is_promoted', true)
                    ->where(function($q) {
                        $q->whereNull('promoted_expires_at')
                          ->orWhere('promoted_expires_at', '>', now());
                    });
            }
        }

        // Sponsored filter
        if ($request->has('sponsored')) {
            $isSponsored = filter_var($request->get('sponsored'), FILTER_VALIDATE_BOOLEAN);
            if ($isSponsored) {
                $query = $query->where('is_sponsored', true)
                    ->where(function($q) {
                        $q->whereNull('sponsored_expires_at')
                          ->orWhere('sponsored_expires_at', '>', now());
                    });
            }
        }

        // Business filter
        if ($request->has('business')) {
            $isBusiness = filter_var($request->get('business'), FILTER_VALIDATE_BOOLEAN);
            if ($isBusiness) {
                $query = $query->where('is_business', true)
                    ->where(function($q) {
                        $q->whereNull('business_expires_at')
                          ->orWhere('business_expires_at', '>', now());
                    });
            }
        }

        // Store filter
        if ($request->has('store')) {
            $isStore = filter_var($request->get('store'), FILTER_VALIDATE_BOOLEAN);
            if ($isStore) {
                $query = $query->where('is_store', true)
                    ->where(function($q) {
                        $q->whereNull('store_expires_at')
                          ->orWhere('store_expires_at', '>', now());
                    });
            }
        }

        // Search keyword (title and description)
        if ($keyword = $request->get('keyword') ?: $request->get('title')) {
            $query = $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        // Sorting - Map user-friendly sort names to actual database columns
        $sortParam = strtolower(trim($request->get('sort', 'newest')));
        $sortOrder = strtolower(trim($request->get('sort_type', 'desc')));
        
        // Map sort parameter to actual database column (normalized to lowercase)
        // NEVER use user input directly as column name for security and correctness
        $sortMapping = [
            'newest' => 'created_at',
            'oldest' => 'created_at',
            'salary_low' => 'salary_min',
            'salary_high' => 'salary_max',
            'relevance' => 'created_at', // Default to created_at for relevance
            'featured' => 'created_at',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
            'listing_id' => 'listing_id',
            'title' => 'title',
            'price' => 'price',
        ];
        
        // Always use mapped column, never use raw sort parameter
        // This prevents SQL errors from invalid column names like 'newest'
        $sortBy = isset($sortMapping[$sortParam]) ? $sortMapping[$sortParam] : 'created_at';
        
        // Adjust sort order for specific sort types
        if ($sortParam === 'oldest') {
            $sortOrder = 'asc';
        } elseif ($sortParam === 'salary_low') {
            $sortOrder = 'asc';
        } elseif ($sortParam === 'salary_high') {
            $sortOrder = 'desc';
        } elseif ($sortParam === 'newest') {
            $sortOrder = 'desc';
        }
        
        // Ensure sort order is valid
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
        
        // Handle featured jobs priority sorting
        if ($sortParam === 'featured' || !$request->has('sort') || $sortParam === 'relevance') {
            // Featured jobs first, then by date
            $query = $query->orderByRaw("CASE WHEN is_featured = 1 AND (featured_expires_at IS NULL OR featured_expires_at > NOW()) THEN 0 ELSE 1 END")
                          ->orderBy($sortBy, $sortOrder);
        } else {
            $query = $query->orderBy($sortBy, $sortOrder);
        }

        // Get total count before pagination
        $total = $query->count();

        // Pagination - support both old (skip/limit) and new (page/per_page) methods
        if ($skip !== null && $skip !== "") {
            // Legacy pagination
            $perPage = ($limit && $limit > 0) ? $limit : 15;
            $query = $query->skip($skip)->take($perPage);
        } else {
            // Modern pagination
            $perPage = max(1, min(100, $perPage)); // Limit between 1 and 100
            $query = $query->skip(($page - 1) * $perPage)->take($perPage);
        }

        // Eager load relationships for better performance
        $listings = $query->with(['category', 'location', 'customer', 'currency', 'package'])->get();

        // Calculate pagination metadata
        $lastPage = ceil($total / $perPage);
        $currentPage = $skip !== null && $skip !== "" ? floor($skip / $perPage) + 1 : $page;

        $result = [
            'items' => $listings,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $total > 0 ? (($currentPage - 1) * $perPage) + 1 : 0,
            'to' => min($currentPage * $perPage, $total),
        ];

        return $this->successResponse($result, 'Listings retrieved successfully', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     * path="/v1/listing",
     *   tags={"Listing"},
     *   summary="Create listing",
     *   description="Create a new listing",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="location_id", type="integer", format="integer"),
     *            @OA\Property(property="category_id", type="integer", format="integer"),
     *            @OA\Property(property="currency_id", type="integer", format="integer"),
     *            @OA\Property(property="package_id", type="integer", format="integer"),
     *            @OA\Property(property="title", type="string", format="string"),
     *            @OA\Property(property="description", type="string", format="string"),
     *            @OA\Property(property="price", type="integer", format="integer"),
     *            @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary")),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        $customer_id = $user->customer_id;
        $input = $request->all();

        // Validate request data
        $validator = Validator::make($input, [
            'location_id' => 'required',
            'category_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            'price' => 'nullable|numeric|min:0',
            // Job-specific fields
            'job_type' => 'nullable|in:full-time,part-time,contract,freelance,internship',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'apply_url' => 'nullable|url|max:255',
            'end_date' => 'nullable|date|after:today',
            // Venue-specific fields
            'venue_name' => 'nullable|string|max:255',
            'venue_type' => 'nullable|in:conference_hall,banquet_hall,outdoor,restaurant,hotel,stadium,theater,gallery,community_center,other',
            'capacity' => 'nullable|integer|min:1',
            'country' => 'nullable|string|max:100',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'facilities' => 'nullable|array',
            'facilities.*' => 'in:wifi,parking,projector,sound_system,catering,air_conditioning,wheelchair_accessible',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'venue_website' => 'nullable|url|max:255',
            // 'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate each image
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $ad = new Listing();
            $ad->location_id = $request->location_id;
            $ad->category_id = $request->category_id;
            $ad->currency_id = $request->currency_id;
            $ad->package_id = $request->package_id;
            $ad->title = $request->title;
            $ad->description = $request->description;
            // Handle price - set default to 0 if not provided or use salary_min for job listings
            $ad->price = $request->price ?? ($request->salary_min ?? 0);
            // Job-specific fields
            $ad->job_type = $request->job_type;
            $ad->salary_min = $request->salary_min;
            $ad->salary_max = $request->salary_max;
            $ad->apply_url = $request->apply_url;
            $ad->end_date = $request->end_date;
            
            // Venue-specific fields
            $ad->venue_name = $request->venue_name;
            $ad->venue_type = $request->venue_type;
            $ad->capacity = $request->capacity;
            $ad->country = $request->country;
            $ad->price_per_hour = $request->price_per_hour;
            $ad->price_per_day = $request->price_per_day;
            $ad->facilities = $request->facilities;
            $ad->contact_email = $request->contact_email;
            $ad->contact_phone = $request->contact_phone;
            $ad->venue_website = $request->venue_website;
            
            // Posting options
            $ad->is_paid = $request->boolean('is_paid', false);
            $ad->is_promoted = $request->boolean('is_promoted', false);
            $ad->is_sponsored = $request->boolean('is_sponsored', false);
            $ad->is_business = $request->boolean('is_business', false);
            $ad->is_store = $request->boolean('is_store', false);
            
            // Expiry dates for posting options
            if ($request->has('paid_expires_at')) {
                $ad->paid_expires_at = $request->paid_expires_at;
            }
            if ($request->has('promoted_expires_at')) {
                $ad->promoted_expires_at = $request->promoted_expires_at;
            }
            if ($request->has('sponsored_expires_at')) {
                $ad->sponsored_expires_at = $request->sponsored_expires_at;
            }
            if ($request->has('business_expires_at')) {
                $ad->business_expires_at = $request->business_expires_at;
            }
            if ($request->has('store_expires_at')) {
                $ad->store_expires_at = $request->store_expires_at;
            }
            
            $ad->created_at = date("Y-m-d H:i:s");
            $ad->customer_id = $customer_id;

            // Handle multiple base64 image uploads and save to listing_image table
            if ($request->has('images')) {
                // $sortOrder = 1;
                // Initialize an array to store the image names
                $imageNames = [];
                foreach ($request->images as $base64Image) {
                    // Decode the base64 string
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));

                    // Generate a unique image name
                    $imageName = time() . '-' . Str::random(10) . '.jpg'; // Assuming the image is JPEG, adapt as necessary

                    // Save the image to the 'listings' disk
                    Storage::disk($this->folder)->put($imageName, $image);

                    // Save image details to listing_image table
                    // DB::table('listing_image')->insert([
                    //     'listing_id' => $ad->listing_id,
                    //     'image_path' => $imageName, // Save relative image path
                    //     'sort_order' => $sortOrder++,
                    //     'created_at' => now(),
                    //     'updated_at' => now(),
                    // ]);
                    // Add the image name to the array
                    $imageNames[] = $imageName;
                }
                $ad->attachments = $imageNames;
            }
            $ad->save();

            // Process referral completion and apply discounts
            $customer = Customer::find($customer_id);
            $originalPrice = $ad->price;
            
            // Complete referral if this is user's first listing
            $completedReferral = ReferralService::completeReferral($customer);
            
            // Apply referral discount if available
            $discountInfo = ReferralService::applyReferralDiscount($ad, $originalPrice);
            
            // Update listing price if discount was applied
            if ($discountInfo['discount_applied']) {
                $ad->price = $discountInfo['final_price'];
                $ad->save();
            }


            DB::commit();
            
            $responseData = [
                'listing' => $ad,
            ];
            
            // Add discount info if applied
            if (isset($discountInfo) && $discountInfo['discount_applied']) {
                $responseData['discount_applied'] = $discountInfo;
            }
            
            // Add referral completion info
            if ($completedReferral) {
                $responseData['referral_completed'] = true;
                $responseData['referrer_discount_available'] = true;
            }
            
            return $this->successResponse($responseData, '', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *      path="/v1/listing/{slug}",
     *      tags={"Listing"},
     *      summary="Detail listing",
     *      description="Get ad detail by slug",
     *      @OA\Parameter(
     *          name="slug",
     *          description="Ad slug",
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
     *                 ref="#/components/schemas/ListingResource"
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
    public function show($slug)
    {
        $query = Listing::where('slug', $slug)
            ->where('approval_status', 'approved')
            ->first();
            
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }

        // customer store
        $is_has_store = false;
        $query->store = [];
        $customerStore = CustomerStore::where('customer_id', $query->customer_id)->first();
        if (!is_null($customerStore)) {
            $query->store = $customerStore;
            $is_has_store = true;
        }
        $query->is_has_store = $is_has_store;

        return $this->successResponse($query, '', Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     * path="/v1/listing/{id}",
     *   tags={"Listing"},
     *   summary="Update listing",
     *   description="Update an existing listing based on provided parameters",
     *   security={
     *      {"bearerAuth": {}}
     *   },
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       description="ID of the listing to update",
     *       @OA\Schema(
     *           type="integer"
     *       )
     *   ),
     *   @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="location_id", type="integer", format="integer", description="The location ID"),
     *            @OA\Property(property="category_id", type="integer", format="integer", description="The category ID"),
     *            @OA\Property(property="currency_id", type="integer", format="integer", description="The currency ID"),
     *            @OA\Property(property="package_id", type="integer", format="integer", description="The package ID"),
     *            @OA\Property(property="title", type="string", format="string", description="The listing title"),
     *            @OA\Property(property="description", type="string", format="string", description="The listing description"),
     *            @OA\Property(property="price", type="integer", format="integer", description="The listing price"),
     *            @OA\Property(property="status", type="string", format="string", description="The listing status"),
     *            @OA\Property(property="images", type="array", @OA\Items(type="string", format="binary", description="Base64 encoded image")),
     *        ),
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
    public function update(Request $request, $id)
    {
        $listing = Listing::find($id);

        if (!$listing) {
            return $this->errorResponse('Listing not found', Response::HTTP_NOT_FOUND);
        }

        // Validate the provided data
        // $validator = Validator::make($request->all(), [
        //     'location_id' => 'sometimes|required|integer',
        //     'category_id' => 'sometimes|required|integer',
        //     'currency_id' => 'sometimes|required|integer',
        //     'title' => 'sometimes|required|string',
        //     'description' => 'sometimes|required|string',
        //     'price' => 'sometimes|required|integer',
        //     'status' => 'sometimes|required|string',
        //     'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        // ]);

        // if ($validator->fails()) {
        //     return $this->errorResponse($validator->errors()->first(), Response::HTTP_BAD_REQUEST);
        // }

        try {
            DB::beginTransaction();

            // Update listing attributes based on provided input
            $listing->fill($request->only([
                'location_id',
                'category_id',
                'currency_id',
                'title',
                'description',
                'price',
                'status',
                'package_id',
                // Job-specific fields
                'job_type',
                'salary_min',
                'salary_max',
                'apply_url',
                'end_date',
                // Posting options
                'is_paid',
                'is_promoted',
                'is_sponsored',
                'is_business',
                'is_store',
                'paid_expires_at',
                'promoted_expires_at',
                'sponsored_expires_at',
                'business_expires_at',
                'store_expires_at',
            ]));
            
            // Ensure price is never null - use salary_min if price is null and it's a job listing
            if ($listing->price === null || $listing->price === '') {
                $listing->price = $listing->salary_min ?? ($request->salary_min ?? 0);
            }
            
            // $listing->slug = Str::slug($request->title ?? $listing->title); // If title is updated, slug should be updated
            // $listing->updated_at = now();

            // Handle image updates if any
            if ($request->has('images')) {
                // Initialize an array to store the image names
                $imageNames = [];
                // $sortOrder = 1;
                foreach ($request->images as $base64Image) {
                    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                    $imageName = time() . '-' . Str::random(10) . '.jpg'; // Adjust image extension as needed
                    Storage::disk($this->folder)->put($imageName, $image);
                    // Add the image name to the array
                    $imageNames[] = $imageName;
                    // Insert image data to listing_image table
                    // DB::table('listing_image')->insert([
                    //     'listing_id' => $listing->listing_id,
                    //     'image_path' => $imageName,
                    //     'sort_order' => $sortOrder++,
                    //     'created_at' => now(),
                    //     'updated_at' => now(),
                    // ]);
                }
                $listing->attachments = $imageNames;
            }
            $listing->save();

            DB::commit();
            return $this->successResponse($listing, 'Listing updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *  @OA\Delete(
     *     path="/v1/listing/{id}",
     *     summary="Delete listing",
     *     description="Delete a single article based on the ID",
     *     tags={"Listing"},
     *     security={
     *        {"bearerAuth": {}}
     *     },
     *     @OA\Parameter(
     *         description="ID of article to delete",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
        $query = Listing::where('id', $id)->first();
        if (is_null($query)) {
            return $this->errorResponse('Data not found.', Response::HTTP_NOT_FOUND);
        }
        $query->delete();

        return $this->successResponse($query, 'Data successfully deleted!', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1/listing/featured",
     *      tags={"Listing"},
     *      summary="Get featured ads",
     *      description="Get featured ads",
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
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
    public function featured(Request $request)
    {
        $query = new Listing();
        $query = $query->select('listing.*');
        $query = $query->leftjoin('listing_package', 'listing_package.package_id', '=', 'listing.package_id');
        $query = $query->where('listing.status', 'active');
        $query = $query->where('listing_package.promo_show_featured_area', 'yes');
        $query = $query->where('listing.promo_expire_at', '>=', '2023-01-01 00:00:00');

        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($skip == "") {
            $query = $query->get();
            $total = $query->count();
        } else {
            $perPage = ($skip == "") ? $query->count() : (
                $request->has('limit') ? $limit : 10
            );
            $total = $query->count();
            $query = $query->skip($skip)->take($perPage)->inRandomOrder()->get();
        }

        $result = [
            'items' => $query,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1/listing/new",
     *      tags={"Listing"},
     *      summary="Get new ads",
     *      description="Get new ads",
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
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
    public function new(Request $request)
    {
        $query = new Listing();
        $query = $query->select('listing.*');
        $query = $query->leftjoin('listing_package', 'listing_package.package_id', '=', 'listing.package_id');
        $query = $query->where('listing.status', 'active');
        $query = $query->orderBy('listing.promo_expire_at', 'desc');

        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($skip == "") {
            $query = $query->get();
            $total = $query->count();
        } else {
            $perPage = ($skip == "") ? $query->count() : (
                $request->has('limit') ? $limit : 10
            );
            $total = $query->count();
            $query = $query->skip($skip)->take($perPage)->inRandomOrder()->get();
        }

        $result = [
            'items' => $query,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1/listing/promoted",
     *      tags={"Listing"},
     *      summary="Get promoted ads",
     *      description="Get promoted ads",
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
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
    public function promoted(Request $request)
    {
        $query = new Listing();
        $query = $query->select('listing.*');
        $query = $query->leftjoin('listing_package', 'listing_package.package_id', '=', 'listing.package_id');
        $query = $query->where('listing.status', 'active');
        $query = $query->where('listing_package.promo_show_promoted_area', 'yes');
        $query = $query->where('listing.promo_expire_at', '>=', '2023-01-01 00:00:00');

        $skip = $request->get('skip');
        $limit = $request->get('limit');

        if ($skip == "") {
            $query = $query->get();
            $total = $query->count();
        } else {
            $perPage = ($skip == "") ? $query->count() : (
                $request->has('limit') ? $limit : 10
            );
            $total = $query->count();
            $query = $query->skip($skip)->take($perPage)->inRandomOrder()->get();
        }

        $result = [
            'items' => $query,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1/listing/ebay",
     *      tags={"Listing"},
     *      summary="Get ebay ads",
     *      description="Get ebay ads",
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
    public function ebay()
    {
        $ebay = json_decode(file_get_contents(storage_path() . "/json/ebay.json"), true);
        // echo "<pre>";
        // print_r($ebay["data"]);
        // die();

        $query = collect($ebay["data"]);

        $result = [
            'items' => $query->shuffle(),
            'total' => $query->count(),
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1/listing/{id}/my-listing",
     *      tags={"Listing"},
     *      summary="Get my listing",
     *      description="Get my listing",
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="Customer ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="title",
     *          description="Title",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Status",
     *          in="query",
     *          @OA\Schema(
     *              default="active",
     *              type="string",
     *              enum={"active","inactive"},
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
     *                 ref="#/components/schemas/ListingResource"
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
    public function myListing(Request $request)
    {
        $query = new Listing();
        
        // Get customer_id from authenticated user (route requires auth:api middleware)
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        $customer_id = $user->customer_id;
        
        $query = $query->where('customer_id', $customer_id);

        $skip = $request->get('skip');
        $limit = $request->get('limit') ?: $request->get('per_page'); // Support both limit and per_page

        if ($status = $request->get('status')) {
            $query = $query->where('status', $status);
        }

        if ($keyword = $request->get('title')) {
            $query = $query->where(function ($query) use ($keyword) {
                $query = $query->where('title', 'like', '%' . $keyword . '%');
            });
        }

        // Sorting - Map user-friendly sort names to actual database columns
        if ($sort = $request->get('sort')) {
            $sortParam = strtolower(trim($sort));
            $sortOrder = strtolower(trim($request->get('sort_type', 'asc')));
            
            // Map sort parameter to actual database column
            $sortMapping = [
                'newest' => 'created_at',
                'oldest' => 'created_at',
                'salary_low' => 'salary_min',
                'salary_high' => 'salary_max',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
                'listing_id' => 'listing_id',
                'title' => 'title',
                'price' => 'price',
            ];
            
            // Always use mapped column, never use raw sort parameter
            $sortBy = isset($sortMapping[$sortParam]) ? $sortMapping[$sortParam] : 'listing_id';
            
            // Adjust sort order for specific sort types
            if ($sortParam === 'oldest') {
                $sortOrder = 'asc';
            } elseif ($sortParam === 'salary_low') {
                $sortOrder = 'asc';
            } elseif ($sortParam === 'salary_high') {
                $sortOrder = 'desc';
            } elseif ($sortParam === 'newest') {
                $sortOrder = 'desc';
            }
            
            // Ensure sort order is valid
            $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';
            
            $query = $query->orderBy($sortBy, $sortOrder);
        } else {
            $query = $query->orderBy('listing_id', 'desc');
        }

        if ($skip == "") {
            $query = $query->get();
            $total = $query->count();
        } else {
            $perPage = ($skip == "") ? $query->count() : (
                ($limit && $limit > 0) ? $limit : 10
            );
            $total = $query->count();
            $query = $query->skip($skip)->take($perPage)->get();
        }

        $result = [
            'items' => $query,
            'total' => $total,
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1/listing/global",
     *      tags={"Listing"},
     *      summary="Get global ads",
     *      description="Get global ads",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(
     *            @OA\Property(property="keyword", type="string", format="string"),
     *            @OA\Property(property="category_id", type="number", format="number"),
     *            @OA\Property(property="latitude", type="string", format="string"),
     *            @OA\Property(property="longitude", type="string", format="string"),
     *        ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", format="string"),
     *              @OA\Property(property="message", type="string", format="string"),
     *              @OA\Property(property="data", type="object",
     *                 ref="#/components/schemas/ListingResource"
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
    public function global(Request $request)
    {
        $keyword = $request->keyword;
        $category_id = $request->category_id;

        // Initialize empty collections
        $store = collect();
        $business = collect();
        $banner = collect();
        $listing = Listing::query()
            ->select('listing.*')
            ->leftJoin('category', 'category.category_id', '=', 'listing.category_id')
            ->where('listing.status', 'active');

        // If keyword exists, search within listing, store, business, and banner
        if ($keyword) {
            $listing->where(function ($query) use ($keyword) {
                $query->where('listing.title', 'like', '%' . $keyword . '%')
                    ->orWhere('category.name', 'like', '%' . $keyword . '%');
            });

            $store = CustomerStore::where('store_name', 'like', '%' . $keyword . '%')->get();

            $business = CustomerBusiness::where(function ($query) use ($keyword) {
                $query->where('business_name', 'like', '%' . $keyword . '%')
                    ->orWhere('business_email', 'like', '%' . $keyword . '%')
                    ->orWhere('business_website', 'like', '%' . $keyword . '%');
            })->get();

            $banner = Banner::where('title', 'like', '%' . $keyword . '%')->get();

            $customer = Customer::where('first_name', 'like', '%' . $keyword . '%')
                ->orWhere('last_name', 'like', '%' . $keyword . '%')
                ->first();

            if ($customer) {
                $listing->orWhere('listing.customer_id', $customer->customer_id);
            }
        }

        // If category_id exists, filter listings by category
        if ($category_id && $category_id != 0) {
            $listing->where('listing.category_id', $category_id);
        }

        // Get the listing results (with pagination limits)
        $listing = $listing->inRandomOrder()->take(20)->get();

        // If no keyword was provided, or no results were found, return empty results
        if (!$keyword || ($listing->isEmpty() && $store->isEmpty() && $business->isEmpty() && $banner->isEmpty())) {
            $result = [
                'listing' => ['items' => [], 'total' => 0],
                'store' => ['items' => [], 'total' => 0],
                'business' => ['items' => [], 'total' => 0],
                'banner' => ['items' => [], 'total' => 0],
            ];
            return $this->successResponse($result, 'No results found', Response::HTTP_OK);
        }

        // Prepare final result structure
        $result = [
            'listing' => [
                'items' => $listing,
                'total' => $listing->count(),
            ],
            'store' => [
                'items' => $store,
                'total' => $store->count(),
            ],
            'business' => [
                'items' => $business,
                'total' => $business->count(),
            ],
            'banner' => [
                'items' => $banner,
                'total' => $banner->count(),
            ]
        ];

        return $this->successResponse($result, '', Response::HTTP_OK);
    }
}
