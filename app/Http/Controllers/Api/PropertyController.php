<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyStoreRequest;
use App\Http\Requests\PropertyUpdateRequest;
use App\Http\Resources\PropertyCollection;
use App\Http\Resources\PropertyResource;
use App\Models\Customer;
use App\Models\Property;
use App\Models\PropertyAnalytic;
use App\Models\PropertyFavourite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;

class PropertyController extends Controller
{
    /**
     * JWT marketplace auth uses Customer (customer_id), not users.id.
     */
    private function authUserId(): ?int
    {
        $user = Auth::user() ?: auth('api')->user();
        if (! $user) {
            return null;
        }

        if ($user instanceof Customer) {
            return (int) $user->customer_id;
        }

        $id = $user->id ?? $user->user_id ?? null;

        return $id !== null ? (int) $id : null;
    }

    /** Continent / browse-region → country names used for marketplace drill-down. */
    protected array $continentCountries = [
        'europe' => [
            'Albania', 'Andorra', 'Austria', 'Belarus', 'Belgium', 'Bosnia and Herzegovina',
            'Bulgaria', 'Croatia', 'Cyprus', 'Czech Republic', 'Denmark', 'Estonia',
            'Finland', 'France', 'Germany', 'Greece', 'Hungary', 'Iceland', 'Ireland',
            'Italy', 'Kosovo', 'Latvia', 'Liechtenstein', 'Lithuania', 'Luxembourg',
            'Malta', 'Moldova', 'Monaco', 'Montenegro', 'Netherlands', 'North Macedonia',
            'Norway', 'Poland', 'Portugal', 'Romania', 'Russia', 'San Marino', 'Serbia',
            'Slovakia', 'Slovenia', 'Spain', 'Sweden', 'Switzerland', 'Ukraine',
            'United Kingdom', 'Vatican City', 'UK', 'England', 'Scotland', 'Wales',
        ],
        'north-america' => [
            'Antigua and Barbuda', 'Bahamas', 'Barbados', 'Belize', 'Canada', 'Costa Rica',
            'Cuba', 'Dominica', 'Dominican Republic', 'El Salvador', 'Grenada', 'Guatemala',
            'Haiti', 'Honduras', 'Jamaica', 'Mexico', 'Nicaragua', 'Panama',
            'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Vincent and the Grenadines',
            'Trinidad and Tobago', 'United States', 'USA', 'US',
        ],
        'asia' => [
            'Afghanistan', 'Armenia', 'Azerbaijan', 'Bangladesh', 'Bhutan', 'Brunei',
            'Cambodia', 'China', 'Georgia', 'India', 'Indonesia', 'Japan', 'Kazakhstan',
            'Kyrgyzstan', 'Laos', 'Malaysia', 'Maldives', 'Mongolia', 'Myanmar', 'Nepal',
            'North Korea', 'Pakistan', 'Philippines', 'Singapore', 'South Korea',
            'Sri Lanka', 'Taiwan', 'Tajikistan', 'Thailand', 'Timor-Leste', 'Turkmenistan',
            'Uzbekistan', 'Vietnam',
        ],
        'middle-east' => [
            'Bahrain', 'Egypt', 'Iran', 'Iraq', 'Israel', 'Jordan', 'Kuwait', 'Lebanon',
            'Oman', 'Palestine', 'Qatar', 'Saudi Arabia', 'Syria', 'Turkey',
            'United Arab Emirates', 'UAE', 'Yemen', 'Cyprus',
        ],
        'africa' => [
            'Algeria', 'Angola', 'Benin', 'Botswana', 'Burkina Faso', 'Burundi',
            'Cameroon', 'Cape Verde', 'Central African Republic', 'Chad', 'Comoros',
            'Congo', 'Djibouti', 'DR Congo', 'Egypt', 'Equatorial Guinea', 'Eritrea',
            'Eswatini', 'Ethiopia', 'Gabon', 'Gambia', 'Ghana', 'Guinea', 'Guinea-Bissau',
            'Ivory Coast', 'Kenya', 'Lesotho', 'Liberia', 'Libya', 'Madagascar', 'Malawi',
            'Mali', 'Mauritania', 'Mauritius', 'Morocco', 'Mozambique', 'Namibia', 'Niger',
            'Nigeria', 'Rwanda', 'Senegal', 'Seychelles', 'Sierra Leone', 'Somalia',
            'South Africa', 'South Sudan', 'Sudan', 'Tanzania', 'Togo', 'Tunisia',
            'Uganda', 'Zambia', 'Zimbabwe',
        ],
        'south-america' => [
            'Argentina', 'Bolivia', 'Brazil', 'Chile', 'Colombia', 'Ecuador', 'Guyana',
            'Paraguay', 'Peru', 'Suriname', 'Uruguay', 'Venezuela',
        ],
        'oceania' => [
            'Australia', 'Fiji', 'Kiribati', 'Marshall Islands', 'Micronesia', 'Nauru',
            'New Zealand', 'Palau', 'Papua New Guinea', 'Samoa', 'Solomon Islands',
            'Tonga', 'Tuvalu', 'Vanuatu',
        ],
    ];

    public function index(Request $request): PropertyCollection
    {
        try {
            $query = Property::with(['user']);

            // Apply filters
            if ($request->filled('property_type')) {
                $types = array_filter(array_map('trim', explode(',', $request->property_type)));
                if (count($types) === 1) {
                    $query->byPropertyType($types[0]);
                } elseif (count($types) > 1) {
                    $query->whereIn('property_type', $types);
                }
            }

            if ($request->filled('category')) {
                $query->byCategory($request->category);
            }

            if ($request->filled('country')) {
                $query->byLocation($request->country, $request->city);
            } elseif ($request->filled('continent')) {
                $this->applyContinentFilter($query, $request->continent);
            } elseif ($request->filled('location')) {
                $location = $request->location;
                $query->where(function ($q) use ($location) {
                    $q->where('country', 'LIKE', "%{$location}%")
                      ->orWhere('city', 'LIKE', "%{$location}%")
                      ->orWhere('region', 'LIKE', "%{$location}%");
                });
            } elseif ($request->filled('city')) {
                $query->byLocation(null, $request->city);
            }

            if ($request->filled('min_price') || $request->filled('max_price')) {
                $query->priceRange($request->min_price, $request->max_price);
            }

            if ($request->filled('min_bedrooms') || $request->filled('max_bedrooms')) {
                $query->bedrooms($request->min_bedrooms, $request->max_bedrooms);
            }

            if ($request->filled('min_bathrooms') || $request->filled('max_bathrooms')) {
                if ($request->filled('min_bathrooms')) {
                    $query->where('bathrooms', '>=', $request->min_bathrooms);
                }
                if ($request->filled('max_bathrooms')) {
                    $query->where('bathrooms', '<=', $request->max_bathrooms);
                }
            }

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('city', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('country', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Show only active and approved properties for public
            if (!$request->filled('include_all')) {
                $query->active();
            }

            // Priority ordering: sponsored > promoted > featured > regular
            $query->orderByRaw("
                CASE 
                    WHEN advert_type = 'sponsored' AND sponsored_until > NOW() THEN 1
                    WHEN advert_type = 'featured' AND featured_until > NOW() THEN 2
                    WHEN advert_type = 'promoted' AND promoted_until > NOW() THEN 3
                    ELSE 4
                END
            ")->orderBy('created_at', 'desc');

            $properties = $query->paginate($request->get('per_page', 12));

            return new PropertyCollection($properties);
        } catch (\Exception $e) {
            \Log::error('Property index error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json([
                'message' => 'Error fetching properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function featured(Request $request): PropertyCollection
    {
        try {
            $query = Property::with(['user'])
                ->active()
                ->featured();

            $country = $request->get('country');
            if ($request->boolean('local') && !$country) {
                $geo = $this->resolveIpCountry($request);
                $country = $geo['country'] ?? null;
            }

            if ($country) {
                $query->where('country', 'LIKE', '%' . $country . '%');
            }

            $properties = $query
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 12));

            // If local targeting returned nothing, fall back to global featured
            if ($country && $properties->total() === 0 && $request->boolean('local')) {
                $properties = Property::with(['user'])
                    ->active()
                    ->featured()
                    ->orderBy('created_at', 'desc')
                    ->paginate($request->get('per_page', 12));
            }

            return new PropertyCollection($properties);
        } catch (\Exception $e) {
            \Log::error('Property featured error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching featured properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resolve visitor country from IP (stevebauman/location).
     */
    public function geo(Request $request): JsonResponse
    {
        $geo = $this->resolveIpCountry($request);

        return response()->json([
            'success' => true,
            'country' => $geo['country'],
            'country_code' => $geo['country_code'],
            'city' => $geo['city'],
            'region' => $geo['region'],
            'ip' => $request->ip(),
        ]);
    }

    protected function resolveIpCountry(Request $request): array
    {
        $defaults = [
            'country' => null,
            'country_code' => null,
            'city' => null,
            'region' => null,
        ];

        try {
            $position = Location::get($request->ip());
            if (!$position) {
                return $defaults;
            }

            return [
                'country' => $position->countryName ?: null,
                'country_code' => $position->countryCode ?: null,
                'city' => $position->cityName ?: null,
                'region' => $position->regionName ?: null,
            ];
        } catch (\Throwable $e) {
            \Log::warning('Property geo lookup failed: ' . $e->getMessage());
            return $defaults;
        }
    }

    protected function applyContinentFilter($query, string $continentId): void
    {
        $key = strtolower(trim($continentId));
        $countries = $this->continentCountries[$key] ?? null;

        if (!$countries) {
            $query->where('region', 'LIKE', '%' . $continentId . '%');
            return;
        }

        $query->where(function ($q) use ($countries, $key) {
            $q->where(function ($inner) use ($countries) {
                foreach ($countries as $i => $country) {
                    if ($i === 0) {
                        $inner->where('country', 'LIKE', '%' . $country . '%');
                    } else {
                        $inner->orWhere('country', 'LIKE', '%' . $country . '%');
                    }
                }
            })->orWhere('region', 'LIKE', '%' . $key . '%');
        });
    }

    public function promoted(Request $request): PropertyCollection
    {
        try {
            $properties = Property::with(['user'])
                ->active()
                ->promoted()
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 12));

            return new PropertyCollection($properties);
        } catch (\Exception $e) {
            \Log::error('Property promoted error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching promoted properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sponsored(Request $request): PropertyCollection
    {
        try {
            $properties = Property::with(['user'])
                ->active()
                ->sponsored()
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 12));

            return new PropertyCollection($properties);
        } catch (\Exception $e) {
            \Log::error('Property sponsored error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching sponsored properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Property $property): PropertyResource
    {
        // Track view
        PropertyAnalytic::create([
            'property_id' => $property->id,
            'event_type' => 'view',
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Increment view count
        $property->increment('views');

        return new PropertyResource($property->load(['user']));
    }

    public function store(PropertyStoreRequest $request): PropertyResource|JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            if ($data === [] || $data === null) {
                $data = $request->except([
                    'cover_image',
                    'additional_images',
                    'seller_logo',
                    'terms_accepted',
                    'accuracy_confirmed',
                    'continent_id',
                ]);
            }

            // Frontend sends overview; DB column description is required historically
            if (empty($data['description'])) {
                $data['description'] = $data['overview']
                    ?? $data['key_features']
                    ?? $data['title']
                    ?? 'Property listing';
            }
            if (empty($data['overview']) && !empty($data['description'])) {
                $data['overview'] = $data['description'];
            }
            if (!isset($data['deposit_required']) && isset($data['deposit'])) {
                $data['deposit_required'] = $data['deposit'];
            }
            // DB enum does not include "sell"
            if (($data['category'] ?? '') === 'sell') {
                $data['category'] = 'buy';
            }

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('properties/cover', 'public');
            }

            if (empty($data['cover_image'])) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Cover image is required',
                    'errors' => ['cover_image' => ['Please upload a cover image.']],
                ], 422);
            }

            // Handle additional images
            if ($request->hasFile('additional_images')) {
                $additionalImages = [];
                foreach ($request->file('additional_images') as $image) {
                    $additionalImages[] = $image->store('properties/additional', 'public');
                }
                $data['additional_images'] = $additionalImages;
            }

            // Handle seller logo upload
            if ($request->hasFile('seller_logo')) {
                $data['seller_logo'] = $request->file('seller_logo')->store('properties/logos', 'public');
            }

            // JWT auth uses Customer — marketplace listings store customer_id in user_id
            $data['user_id'] = Auth::id();
            $data['active'] = array_key_exists('active', $data) ? (bool) $data['active'] : true;
            $data['approved'] = array_key_exists('approved', $data) ? (bool) $data['approved'] : true;
            $data['currency'] = $data['currency'] ?? 'USD';

            // Only persist fillable attributes
            $property = Property::create(collect($data)->only((new Property)->getFillable())->all());

            DB::commit();

            try {
                $property->load(['user']);
            } catch (\Throwable $e) {
                // user_id may be a customer_id (JWT) rather than users.user_id
            }

            return new PropertyResource($property);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(PropertyUpdateRequest $request, Property $property): PropertyResource|JsonResponse
    {
        // Check if user owns this property
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                // Delete old image
                if ($property->cover_image) {
                    Storage::disk('public')->delete($property->cover_image);
                }
                $data['cover_image'] = $request->file('cover_image')->store('properties/cover', 'public');
            }

            // Handle additional images
            if ($request->hasFile('additional_images')) {
                $additionalImages = [];
                foreach ($request->file('additional_images') as $image) {
                    $additionalImages[] = $image->store('properties/additional', 'public');
                }
                $data['additional_images'] = $additionalImages;
            }

            // Handle seller logo upload
            if ($request->hasFile('seller_logo')) {
                // Delete old logo
                if ($property->seller_logo) {
                    Storage::disk('public')->delete($property->seller_logo);
                }
                $data['seller_logo'] = $request->file('seller_logo')->store('properties/logos', 'public');
            }

            $property->update($data);

            DB::commit();

            return new PropertyResource($property->load(['user']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Property $property): JsonResponse
    {
        // Check if user owns this property
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Delete images
            if ($property->cover_image) {
                Storage::disk('public')->delete($property->cover_image);
            }

            if ($property->additional_images) {
                foreach ($property->additional_images as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            if ($property->seller_logo) {
                Storage::disk('public')->delete($property->seller_logo);
            }

            $property->delete();

            return response()->json(['message' => 'Property deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete property',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function myProperties(Request $request): PropertyCollection
    {
        $properties = Property::with(['user'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return new PropertyCollection($properties);
    }

    public function saveProperty(Property $property): JsonResponse
    {
        $userId = $this->authUserId();

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $saved = $property->favourites()->where('user_id', $userId)->first();

        if ($saved) {
            // Unsave
            $saved->delete();
            $property->decrement('saves');

            // Track analytics
            PropertyAnalytic::create([
                'property_id' => $property->id,
                'event_type' => 'save',
                'user_id' => $userId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'metadata' => ['action' => 'unsave'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Property removed from saved list',
                'saved' => false,
                'data' => ['saved' => false],
            ]);
        }

        // Save
        $property->favourites()->create(['user_id' => $userId]);
        $property->increment('saves');

        // Track analytics
        PropertyAnalytic::create([
            'property_id' => $property->id,
            'event_type' => 'save',
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => ['action' => 'save'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Property saved successfully',
            'saved' => true,
            'data' => ['saved' => true],
        ]);
    }

    public function savedProperties(Request $request): PropertyCollection
    {
        $userId = $this->authUserId();

        $properties = Property::query()
            ->whereHas('favourites', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['user'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 12));

        return new PropertyCollection($properties);
    }

    public function contactAgent(Property $property, Request $request): JsonResponse
    {
        // Track analytics
        PropertyAnalytic::create([
            'property_id' => $property->id,
            'event_type' => 'contact_agent',
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Increment inquiry count
        $property->increment('enquiries');

        $sellerCustomer = $property->user_id
            ? \App\Models\Customer::find($property->user_id)
            : null;

        // Return contact information
        $contactInfo = [
            'agent_name' => $property->seller_name
                ?? $property->agent_name
                ?? ($sellerCustomer
                    ? (trim(($sellerCustomer->first_name ?? '').' '.($sellerCustomer->last_name ?? '')) ?: 'Property Agent')
                    : 'Property Agent'),
            'agent_email' => $property->seller_email ?? $property->agent_email ?? ($sellerCustomer->email ?? null),
            'agent_phone' => $property->seller_phone ?? $property->agent_phone ?? ($sellerCustomer->phone_number ?? null),
            'agency_name' => $property->seller_company ?? $property->agency_name,
            'agency_phone' => $property->agency_phone,
            'agency_email' => $property->agency_email,
            'whatsapp' => $property->whatsapp_number ?? $property->seller_phone,
            'seller_name' => $property->seller_name,
            'seller_email' => $property->seller_email ?? ($sellerCustomer->email ?? null),
            'seller_phone' => $property->seller_phone ?? ($sellerCustomer->phone_number ?? null),
        ];

        if ($property->user) {
            $contactInfo['owner_email'] = $property->user->email;
            $contactInfo['owner_phone'] = $property->user->mobile_number ?? null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Contact information retrieved successfully',
            'data' => [
                'property_id' => $property->id,
                'property_title' => $property->title,
                'contact_info' => $contactInfo,
            ],
        ]);
    }

    /**
     * Send an enquiry message to the property seller (buy-sell pattern).
     */
    public function contactSeller(Request $request, Property $property): JsonResponse
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'message' => 'required|string|min:10|max:1000',
                'contact_method' => 'required|in:email,phone,whatsapp',
                'buyer_name' => 'required|string|max:255',
                'buyer_email' => 'required|email|max:255',
                'buyer_phone' => 'nullable|string|max:50',
                'type' => 'nullable|in:general,schedule_viewing,price_inquiry,financing,offer',
                'offer_amount' => 'nullable|numeric|min:1|max:999999999999',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $isOffer = $request->input('type') === 'offer' || $request->filled('offer_amount');
            $offerAmount = $request->filled('offer_amount') ? (float) $request->offer_amount : null;
            if ($isOffer && ! $offerAmount) {
                return response()->json([
                    'success' => false,
                    'errors' => ['offer_amount' => ['Please enter your offer amount.']],
                ], 422);
            }

            $currency = strtoupper((string) ($property->currency ?: 'USD'));
            $enquiryMessage = (string) $request->message;
            if ($offerAmount) {
                $formattedOffer = $currency.' '.number_format($offerAmount, 2, '.', ',');
                $enquiryMessage = "OFFER: {$formattedOffer}\n\n".$enquiryMessage;
            }
            // property_enquiries.type enum has no "offer" — map to price_inquiry
            $enquiryType = $isOffer ? 'price_inquiry' : $request->input('type', 'general');

            $sellerCustomer = null;
            if ($property->user_id) {
                $sellerCustomer = \App\Models\Customer::find($property->user_id);
            }
            if (! $sellerCustomer && $property->seller_email) {
                $sellerCustomer = \App\Models\Customer::where('email', $property->seller_email)->first();
            }

            $sellerEmail = $property->seller_email ?: ($sellerCustomer->email ?? null);
            $sellerName = $property->seller_name;
            if (! $sellerName && $sellerCustomer) {
                $sellerName = trim(($sellerCustomer->first_name ?? '').' '.($sellerCustomer->last_name ?? '')) ?: 'Seller';
            }
            $sellerName = $sellerName ?: 'Seller';
            $sellerCustomerId = $sellerCustomer
                ? (int) $sellerCustomer->customer_id
                : ($property->user_id ? (int) $property->user_id : null);

            if (! $sellerEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller contact is not available for this listing.',
                ], 422);
            }

            $buyerId = Auth::guard('api')->id() ?: Auth::id();

            $contact = \App\Models\SellerContactMessage::create([
                'hub' => 'property',
                'listing_id' => (string) $property->id,
                'seller_user_id' => $sellerCustomerId,
                'buyer_user_id' => $buyerId,
                'buyer_name' => $request->buyer_name,
                'buyer_email' => $request->buyer_email,
                'buyer_phone' => $request->buyer_phone,
                'contact_method' => $request->contact_method,
                'message' => $enquiryMessage,
                'status' => 'new',
            ]);

            try {
                \App\Models\PropertyEnquiry::create([
                    'property_id' => $property->id,
                    'user_id' => $buyerId,
                    'name' => $request->buyer_name,
                    'email' => $request->buyer_email,
                    'phone' => $request->buyer_phone,
                    'message' => $enquiryMessage,
                    'type' => $enquiryType,
                ]);
            } catch (\Throwable $e) {
                \Log::warning('PropertyEnquiry create failed', [
                    'property_id' => $property->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $property->increment('enquiries');

            PropertyAnalytic::create([
                'property_id' => $property->id,
                'event_type' => 'inquiry',
                'user_id' => $buyerId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => $offerAmount ? [
                    'kind' => 'offer',
                    'offer_amount' => $offerAmount,
                    'currency' => $currency,
                ] : null,
            ]);

            $frontendBase = rtrim(
                env('FRONTEND_URL', env('APP_FRONTEND_URL', 'https://worldwideadverts.info')),
                '/'
            );
            $listingUrl = $frontendBase.'/property/'.$property->id;

            $emailSent = false;
            try {
                \Illuminate\Support\Facades\Mail::to($sellerEmail, $sellerName)
                    ->send(new \App\Mail\SellerContactEnquiryMail(
                        sellerName: $sellerName,
                        listingTitle: (string) $property->title,
                        buyerName: (string) $request->buyer_name,
                        buyerEmail: (string) $request->buyer_email,
                        buyerPhone: $request->buyer_phone,
                        contactMethod: (string) $request->contact_method,
                        enquiryMessage: $enquiryMessage,
                        listingUrl: $listingUrl,
                    ));
                $emailSent = true;
            } catch (\Throwable $e) {
                \Log::warning('Property seller contact email failed', [
                    'property_id' => $property->id,
                    'seller_email' => $sellerEmail,
                    'error' => $e->getMessage(),
                ]);
            }

            $notificationCreated = false;
            if ($sellerCustomerId) {
                try {
                    $notifyTitle = $isOffer ? 'New property offer' : 'New property enquiry';
                    $notifyBody = $isOffer
                        ? "{$request->buyer_name} offered {$currency} ".number_format($offerAmount, 2, '.', ',')." on \"{$property->title}\""
                        : "{$request->buyer_name} sent an enquiry about \"{$property->title}\": "
                            .mb_strimwidth($enquiryMessage, 0, 140, '…');

                    \App\Models\CustomerNotification::notify(
                        $sellerCustomerId,
                        \App\Models\CustomerNotification::TYPE_SELLER_ENQUIRY,
                        $notifyBody,
                        $notifyTitle,
                        [
                            'hub' => 'property',
                            'listing_id' => (string) $property->id,
                            'listing_title' => $property->title,
                            'contact_id' => $contact->id,
                            'buyer_name' => $request->buyer_name,
                            'buyer_email' => $request->buyer_email,
                            'buyer_phone' => $request->buyer_phone,
                            'contact_method' => $request->contact_method,
                            'offer_amount' => $offerAmount,
                            'currency' => $offerAmount ? $currency : null,
                            'kind' => $isOffer ? 'offer' : 'enquiry',
                            'url' => '/property/'.$property->id,
                        ]
                    );
                    $notificationCreated = true;
                } catch (\Throwable $e) {
                    \Log::warning('Property seller dashboard notification failed', [
                        'property_id' => $property->id,
                        'seller_customer_id' => $sellerCustomerId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => $isOffer ? 'Offer sent to the seller' : 'Message sent to the seller',
                    'contact_id' => $contact->id,
                    'email_sent' => $emailSent,
                    'notification_created' => $notificationCreated,
                    'offer_amount' => $offerAmount,
                ],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Property contactSeller failed', [
                'property_id' => $property->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Seller inbox: enquiries for the authenticated user's properties.
     */
    public function myEnquiries(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $messages = \App\Models\SellerContactMessage::query()
            ->where('hub', 'property')
            ->where('seller_user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        $propertyIds = $messages->getCollection()
            ->pluck('listing_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $titles = Property::whereIn('id', $propertyIds)->pluck('title', 'id');

        $data = $messages->getCollection()->map(function ($msg) use ($titles) {
            return [
                'id' => $msg->id,
                'property_id' => $msg->listing_id,
                'property_title' => $titles[$msg->listing_id] ?? ('Property #'.$msg->listing_id),
                'buyer_name' => $msg->buyer_name,
                'buyer_email' => $msg->buyer_email,
                'buyer_phone' => $msg->buyer_phone,
                'contact_method' => $msg->contact_method,
                'message' => $msg->message,
                'status' => $msg->status,
                'created_at' => $msg->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    public function markEnquiryRead($id): JsonResponse
    {
        $userId = Auth::id();
        $msg = \App\Models\SellerContactMessage::where('hub', 'property')
            ->where('seller_user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $msg->update(['status' => 'read']);

        return response()->json([
            'success' => true,
            'message' => 'Enquiry marked as read',
            'data' => $msg,
        ]);
    }

    public function trackEvent(Property $property, Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|in:map_view,video_play,gallery_view,phone_click,share,inquiry',
            'metadata' => 'nullable|array',
        ]);

        PropertyAnalytic::create([
            'property_id' => $property->id,
            'event_type' => $request->event_type,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $request->metadata,
        ]);

        return response()->json(['message' => 'Event tracked successfully']);
    }

    public function getPropertyTypes(): JsonResponse
    {
        return response()->json(['data' => $this->mapToOptions(Property::getPropertyTypes())]);
    }

    public function getCategories(): JsonResponse
    {
        return response()->json(['data' => $this->mapToOptions(Property::getCategories())]);
    }

    public function getCommercialTypes(): JsonResponse
    {
        return response()->json(['data' => $this->mapToOptions(Property::getCommercialTypes())]);
    }

    public function getLandTypes(): JsonResponse
    {
        return response()->json(['data' => $this->mapToOptions(Property::getLandTypes())]);
    }

    public function getPlanningPermissions(): JsonResponse
    {
        return response()->json(['data' => $this->mapToOptions(Property::getPlanningPermissions())]);
    }

    public function getViewTypes(): JsonResponse
    {
        return response()->json(['data' => $this->mapToOptions(Property::getViewTypes())]);
    }

    /**
     * Convert ['key' => 'Label'] association into [{id, name, label}, ...]
     */
    protected function mapToOptions(array $map): array
    {
        $out = [];
        foreach ($map as $id => $label) {
            $out[] = ['id' => $id, 'name' => $label, 'label' => $label];
        }
        return $out;
    }
}
