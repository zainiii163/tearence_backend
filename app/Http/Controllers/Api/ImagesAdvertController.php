<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImagesAdvert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ImagesAdvertController extends Controller
{
    public function index(Request $request)
    {
        $query = ImagesAdvert::with(['user'])
            ->active()
            ->verified();

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            });
        }

        // Category filter
        if ($request->has('image_category')) {
            $query->byCategory($request->input('image_category'));
        }

        // License type filter
        if ($request->has('license_type')) {
            $query->byLicenseType($request->input('license_type'));
        }

        // Orientation filter
        if ($request->has('orientation')) {
            $query->byOrientation($request->input('orientation'));
        }

        // Color type filter
        if ($request->has('color_type')) {
            $query->byColorType($request->input('color_type'));
        }

        // Price filter
        if ($request->has('min_price') || $request->has('max_price')) {
            $query->byPriceRange(
                $request->input('min_price'),
                $request->input('max_price')
            );
        }

        // Minimum rating filter
        if ($request->has('min_rating')) {
            $query->byMinRating($request->input('min_rating'));
        }

        // Verified creator filter
        if ($request->has('verified_creator')) {
            $query->where('is_verified_creator', true);
        }

        // Promotion tier filter
        if ($request->has('promotion_tier')) {
            $query->byPromotionTier($request->input('promotion_tier'));
        }

        // Sort
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');
        
        switch ($sort) {
            case 'title':
                $query->orderBy('title', $order);
                break;
            case 'price':
                $query->orderBy('standard_price', $order);
                break;
            case 'downloads':
                $query->orderBy('downloads_count', $order);
                break;
            case 'rating':
                $query->orderBy('rating', $order);
                break;
            case 'views':
                $query->orderBy('views_count', $order);
                break;
            case 'promotion':
                $query->orderByRaw("FIELD(promotion_tier, 'network_wide', 'sponsored', 'featured', 'promoted', 'standard') {$order}");
                break;
            default:
                $query->orderBy('created_at', $order);
        }

        $images = $query->paginate($request->input('per_page', 24));

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    public function show($slug)
    {
        $image = ImagesAdvert::with(['user', 'verifier'])
            ->where('slug', $slug)
            ->active()
            ->verified()
            ->firstOrFail();

        // Increment view count
        $image->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $image,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'main_image' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'thumbnail' => 'nullable|string',
            'width' => 'nullable|integer|min:1',
            'height' => 'nullable|integer|min:1',
            'orientation' => 'required|in:landscape,portrait,square',
            'color_type' => 'required|in:color,black_white',
            'dominant_color' => 'nullable|string|max:20',
            'image_category' => 'required|in:business,people,nature,food,technology,real_estate,travel,abstract',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'license_type' => 'required|in:royalty_free,rights_managed,extended,editorial,exclusive',
            'standard_price' => 'required|numeric|min:0',
            'extended_price' => 'nullable|numeric|min:0',
            'exclusive_price' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'available_resolutions' => 'nullable|array',
            'available_formats' => 'nullable|array',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'business_name' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'social_links.*' => 'url',
            'has_model_release' => 'boolean',
            'model_release_document' => 'nullable|string',
            'has_property_release' => 'boolean',
            'property_release_document' => 'nullable|string',
            'agreed_to_terms' => 'required|accepted',
            'promotion_tier' => 'nullable|in:standard,promoted,featured,sponsored,network_wide',
            'is_verified_creator' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['verification_status'] = 'pending';
        $validated['currency'] = $validated['currency'] ?? 'GBP';
        $validated['has_model_release'] = $validated['has_model_release'] ?? false;
        $validated['has_property_release'] = $validated['has_property_release'] ?? false;
        $validated['is_verified_creator'] = $validated['is_verified_creator'] ?? false;
        $validated['promotion_tier'] = $validated['promotion_tier'] ?? 'standard';
        $validated['views_count'] = 0;
        $validated['downloads_count'] = 0;
        $validated['saves_count'] = 0;
        $validated['rating'] = 0;
        $validated['rating_count'] = 0;

        // Generate unique slug
        $validated['slug'] = $this->generateUniqueSlug($validated['title']);

        $image = ImagesAdvert::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully and pending admin verification',
            'data' => $image->load(['user']),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $image = ImagesAdvert::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'short_description' => 'sometimes|nullable|string|max:500',
            'main_image' => 'sometimes|string',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'string',
            'thumbnail' => 'sometimes|nullable|string',
            'width' => 'sometimes|nullable|integer|min:1',
            'height' => 'sometimes|nullable|integer|min:1',
            'orientation' => 'sometimes|in:landscape,portrait,square',
            'color_type' => 'sometimes|in:color,black_white',
            'dominant_color' => 'sometimes|nullable|string|max:20',
            'image_category' => 'sometimes|in:business,people,nature,food,technology,real_estate,travel,abstract',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'string|max:50',
            'license_type' => 'sometimes|in:standard,extended,editorial,exclusive',
            'standard_price' => 'sometimes|numeric|min:0',
            'extended_price' => 'sometimes|nullable|numeric|min:0',
            'exclusive_price' => 'sometimes|nullable|numeric|min:0',
            'currency' => 'sometimes|string|size:3',
            'available_resolutions' => 'sometimes|nullable|array',
            'available_formats' => 'sometimes|nullable|array',
            'contact_name' => 'sometimes|string|max:255',
            'contact_email' => 'sometimes|email|max:255',
            'contact_phone' => 'sometimes|nullable|string|max:20',
            'business_name' => 'sometimes|nullable|string|max:255',
            'website' => 'sometimes|nullable|url',
            'social_links' => 'sometimes|nullable|array',
            'social_links.*' => 'url',
            'has_model_release' => 'sometimes|boolean',
            'model_release_document' => 'sometimes|nullable|string',
            'has_property_release' => 'sometimes|boolean',
            'property_release_document' => 'sometimes|nullable|string',
            'promotion_tier' => 'sometimes|in:standard,promoted,featured,sponsored,network_wide',
            'is_verified_creator' => 'sometimes|boolean',
        ]);

        // Update slug if title changed
        if (isset($validated['title']) && $validated['title'] !== $image->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $image->id);
            // Reset verification status if title changes
            $validated['verification_status'] = 'pending';
        }

        $image->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
            'data' => $image->load(['user']),
        ]);
    }

    public function destroy($id)
    {
        $image = ImagesAdvert::where('user_id', Auth::id())->findOrFail($id);
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    public function myImages(Request $request)
    {
        $images = ImagesAdvert::with(['verifier'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 24));

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    public function featuredImages()
    {
        $images = ImagesAdvert::with(['user'])
            ->active()
            ->verified()
            ->featured()
            ->orderByRaw("FIELD(promotion_tier, 'network_wide', 'sponsored', 'featured')")
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    public function trendingImages()
    {
        $images = ImagesAdvert::with(['user'])
            ->active()
            ->verified()
            ->trending()
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    public function popularImages()
    {
        $images = ImagesAdvert::with(['user'])
            ->active()
            ->verified()
            ->popular()
            ->limit(12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }

    public function categories()
    {
        $categories = [
            'business' => [
                'name' => 'Business',
                'description' => 'Professional business imagery',
                'icon' => 'briefcase',
            ],
            'people' => [
                'name' => 'People',
                'description' => 'Portraits and lifestyle photos',
                'icon' => 'users',
            ],
            'nature' => [
                'name' => 'Nature',
                'description' => 'Landscapes and wildlife',
                'icon' => 'leaf',
            ],
            'food' => [
                'name' => 'Food',
                'description' => 'Culinary and food photography',
                'icon' => 'utensils',
            ],
            'technology' => [
                'name' => 'Technology',
                'description' => 'Tech and innovation imagery',
                'icon' => 'cpu',
            ],
            'real_estate' => [
                'name' => 'Real Estate',
                'description' => 'Property and architecture',
                'icon' => 'home',
            ],
            'travel' => [
                'name' => 'Travel',
                'description' => 'Destinations and tourism',
                'icon' => 'plane',
            ],
            'abstract' => [
                'name' => 'Abstract',
                'description' => 'Artistic and conceptual images',
                'icon' => 'image',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function licenseTypes()
    {
        $licenseTypes = [
            'royalty_free' => [
                'name' => 'Royalty Free License',
                'description' => 'For personal and commercial use with some restrictions',
                'price_multiplier' => 1,
                'features' => [
                    'Unlimited digital use',
                    'Up to 500,000 print copies',
                    'Social media use',
                    'Website use',
                ]
            ],
            'rights_managed' => [
                'name' => 'Rights Managed License',
                'description' => 'For specific use cases with exclusive rights',
                'price_multiplier' => 3,
                'features' => [
                    'Exclusive usage rights',
                    'Specific time period',
                    'Specific geographic region',
                    'Industry exclusivity',
                ]
            ],
            'extended' => [
                'name' => 'Extended License',
                'description' => 'For larger commercial projects and extended usage',
                'price_multiplier' => 4,
                'features' => [
                    'All Royalty Free features',
                    'Unlimited print copies',
                    'Merchandise use',
                    'Resale rights',
                    'Multi-user license',
                ]
            ],
            'editorial' => [
                'name' => 'Editorial License',
                'description' => 'For news, educational, and documentary use only',
                'price_multiplier' => 0.5,
                'features' => [
                    'News and editorial use',
                    'Educational materials',
                    'Documentaries',
                    'Non-commercial use only',
                ]
            ],
            'exclusive' => [
                'name' => 'Exclusive License',
                'description' => 'Full exclusive rights to the image',
                'price_multiplier' => 20,
                'features' => [
                    'All Extended features',
                    'Exclusive ownership',
                    'Image removed from marketplace',
                    'Full copyright transfer',
                    'Custom usage terms',
                ]
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $licenseTypes,
        ]);
    }

    public function promotionTiers()
    {
        $tiers = [
            'standard' => [
                'name' => 'Standard',
                'description' => 'Basic listing with standard visibility',
                'price' => 0,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Basic listing',
                    'Standard placement',
                    'Contact information'
                ]
            ],
            'promoted' => [
                'name' => 'Promoted',
                'description' => 'Enhanced visibility with highlighted listing',
                'price' => 9.99,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Highlighted listing',
                    'Appears above standard ads',
                    'Promoted badge',
                    '2× more visibility'
                ]
            ],
            'featured' => [
                'name' => 'Featured',
                'description' => 'Premium placement with maximum exposure',
                'price' => 24.99,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Top of category pages',
                    'Larger image card',
                    'Priority in search results',
                    'Featured badge',
                    '4× more visibility'
                ],
                'most_popular' => true
            ],
            'sponsored' => [
                'name' => 'Sponsored',
                'description' => 'Ultimate visibility across the platform',
                'price' => 49.99,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Homepage placement',
                    'Category top placement',
                    'Included in homepage slider',
                    'Sponsored badge',
                    'Maximum visibility'
                ]
            ],
            'network_wide' => [
                'name' => 'Network-Wide Boost',
                'description' => 'Complete network exposure for ultimate reach',
                'price' => 99.99,
                'currency' => 'GBP',
                'duration' => '30 days',
                'features' => [
                    'Appears across multiple pages',
                    'Homepage placement',
                    'Category pages',
                    'Email newsletters',
                    'Top Spotlight badge',
                    'Ultimate visibility'
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $tiers,
        ]);
    }

    public function statistics()
    {
        $totalImages = ImagesAdvert::active()->verified()->count();
        $totalViews = ImagesAdvert::active()->verified()->sum('views_count');
        $totalDownloads = ImagesAdvert::active()->verified()->sum('downloads_count');
        $verifiedCreators = ImagesAdvert::where('is_verified_creator', true)->distinct('user_id')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_images' => $totalImages,
                'total_views' => $totalViews,
                'total_downloads' => $totalDownloads,
                'verified_creators' => $verifiedCreators,
            ],
        ]);
    }

    public function verify($id)
    {
        $image = ImagesAdvert::findOrFail($id);
        $image->verification_status = 'verified';
        $image->verified_by = Auth::id();
        $image->verified_at = now();
        $image->save();

        return response()->json([
            'success' => true,
            'message' => 'Image verified successfully',
            'data' => $image,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $path = $request->file('image')->store('images', 'public');

        // Get image dimensions
        $imageInfo = getimagesize($request->file('image')->getPathname());
        $width = $imageInfo[0] ?? null;
        $height = $imageInfo[1] ?? null;

        // Determine orientation
        $orientation = 'landscape';
        if ($width && $height) {
            if ($width > $height) {
                $orientation = 'landscape';
            } elseif ($width < $height) {
                $orientation = 'portrait';
            } else {
                $orientation = 'square';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'path' => $path,
                'url' => asset('storage/' . $path),
                'width' => $width,
                'height' => $height,
                'orientation' => $orientation,
            ],
        ]);
    }

    public function uploadMultipleImages(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('images', 'public');
            
            $imageInfo = getimagesize($image->getPathname());
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            $orientation = 'landscape';
            if ($width && $height) {
                if ($width > $height) {
                    $orientation = 'landscape';
                } elseif ($width < $height) {
                    $orientation = 'portrait';
                } else {
                    $orientation = 'square';
                }
            }

            $uploadedImages[] = [
                'path' => $path,
                'url' => asset('storage/' . $path),
                'width' => $width,
                'height' => $height,
                'orientation' => $orientation,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $uploadedImages,
        ]);
    }

    public function incrementViews($id)
    {
        $image = ImagesAdvert::active()->verified()->findOrFail($id);
        $image->incrementViews();

        return response()->json([
            'success' => true,
            'message' => 'View count incremented',
            'data' => [
                'views_count' => $image->views_count,
            ],
        ]);
    }

    public function saveImage($id)
    {
        $image = ImagesAdvert::active()->verified()->findOrFail($id);
        $image->incrementSaves();

        return response()->json([
            'success' => true,
            'message' => 'Image saved to favorites',
            'data' => [
                'saves_count' => $image->saves_count,
            ],
        ]);
    }

    public function processPayment(Request $request, $id)
    {
        $request->validate([
            'license_type' => 'required|in:royalty_free,rights_managed,extended,editorial,exclusive',
        ]);

        $image = ImagesAdvert::active()->verified()->findOrFail($id);

        $licenseType = $request->input('license_type');
        $price = $image->{$licenseType . '_price'} ?? $image->standard_price;

        // Here you would integrate with your payment gateway
        // For now, we'll just simulate a successful payment
        
        $image->incrementDownloads();

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => [
                'image_id' => $image->id,
                'license_type' => $licenseType,
                'price' => $price,
                'currency' => $image->currency,
                'download_url' => $image->main_image_url,
            ],
        ]);
    }

    private function generateUniqueSlug($title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (ImagesAdvert::where('slug', $slug)
            ->when($excludeId, function ($query, $excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
