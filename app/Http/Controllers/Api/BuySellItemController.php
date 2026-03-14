<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuySellItem;
use App\Models\BuySellCategory;
use App\Models\BuySellImage;
use App\Models\BuySellVideo;
use App\Models\BuySellSeller;
use App\Models\BuySellEnquiry;
use App\Models\BuySellFavorite;
use App\Models\BuySellPromotion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BuySellItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BuySellItem::with(['category', 'images', 'primaryImage', 'user'])
            ->active();

        // Filters
        if ($request->category_id) {
            $query->byCategory($request->category_id);
        }

        if ($request->country) {
            $query->byCountry($request->country);
        }

        if ($request->item_type) {
            $query->byType($request->item_type);
        }

        if ($request->min_price) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->promotion_type) {
            $query->where('promotion_type', $request->promotion_type);
        }

        // Sorting
        $sort = $request->sort ?? 'newest';
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            case 'nearest':
                if ($request->latitude && $request->longitude) {
                    // Simplified distance calculation
                    $query->orderByRaw(
                        "(ABS(latitude - ?) + ABS(longitude - ?)) ASC",
                        [$request->latitude, $request->longitude]
                    );
                }
                break;
            default:
                $query->latest();
        }

        $items = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:buy_sell_categories,id',
            'title' => 'required|string|max:255',
            'item_type' => 'required|in:for_sale,for_swap,give_away',
            'condition' => 'required|in:new,like_new,good,fair,poor',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:100',
            'dimensions' => 'nullable|array',
            'weight' => 'nullable|numeric|min:0|max:10000',
            'description' => 'required|string',
            'key_features' => 'nullable|array',
            'usage_notes' => 'nullable|array',
            'price' => 'nullable|required_if:item_type,for_sale|numeric|min:0|max:999999.99',
            'currency' => 'string|size:3',
            'is_negotiable' => 'boolean',
            'country' => 'required|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_details' => 'nullable|string',
            'meta_data' => 'nullable|array',
            'seller' => 'nullable|array',
            'images' => 'nullable|array',
            'videos' => 'nullable|array',
            'promotion_type' => 'nullable|in:promoted,featured,sponsored,network_boost',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            $data['user_id'] = auth()->id();
            $data['status'] = 'draft'; // Will be active after admin approval

            $item = BuySellItem::create($data);

            // Handle images
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $index => $imageData) {
                    if (isset($imageData['base64']) && $imageData['base64']) {
                        $imagePath = $this->saveBase64Image($imageData['base64'], 'buy-sell/items');
                        BuySellImage::create([
                            'item_id' => $item->id,
                            'image_path' => $imagePath,
                            'sort_order' => $index,
                            'is_primary' => $index === 0,
                        ]);
                    }
                }
            }

            // Handle videos
            if ($request->has('videos') && is_array($request->videos)) {
                foreach ($request->videos as $videoData) {
                    if (isset($videoData['base64']) && $videoData['base64']) {
                        $videoPath = $this->saveBase64Video($videoData['base64'], 'buy-sell/videos');
                        BuySellVideo::create([
                            'item_id' => $item->id,
                            'video_path' => $videoPath,
                            'duration' => $videoData['duration'] ?? null,
                        ]);
                    }
                }
            }

            // Handle seller info
            if ($request->has('seller') && is_array($request->seller)) {
                BuySellSeller::create(array_merge($request->seller, [
                    'item_id' => $item->id,
                ]));
            }

            // Handle promotion
            if ($request->promotion_type && $request->promotion_type !== 'standard') {
                $this->createPromotion($item, $request->promotion_type);
            }

            DB::commit();

            $item->load(['category', 'images', 'videos', 'seller']);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'data' => $item
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(BuySellItem $item): JsonResponse
    {
        $item->load([
            'category',
            'images' => function ($query) {
                $query->orderBy('sort_order');
            },
            'videos',
            'seller',
            'user',
            'activePromotion',
            'reviews' => function ($query) {
                $query->where('status', 'approved')->latest()->limit(5);
            }
        ]);

        // Increment views
        $item->incrementViews();

        return response()->json([
            'success' => true,
            'data' => $item
        ]);
    }

    public function update(Request $request, BuySellItem $item): JsonResponse
    {
        if ($item->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'item_type' => 'in:for_sale,for_swap,give_away',
            'condition' => 'in:new,like_new,good,fair,poor',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:100',
            'dimensions' => 'nullable|array',
            'weight' => 'nullable|numeric|min:0|max:10000',
            'description' => 'string',
            'key_features' => 'nullable|array',
            'usage_notes' => 'nullable|array',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'currency' => 'string|size:3',
            'is_negotiable' => 'boolean',
            'country' => 'string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_details' => 'nullable|string',
            'meta_data' => 'nullable|array',
            'seller' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $item->update($validator->validated());

            // Update seller info if provided
            if ($request->has('seller') && is_array($request->seller)) {
                $item->seller()->updateOrCreate(
                    ['item_id' => $item->id],
                    $request->seller
                );
            }

            DB::commit();

            $item->load(['category', 'images', 'videos', 'seller']);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'data' => $item
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(BuySellItem $item): JsonResponse
    {
        if ($item->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully'
        ]);
    }

    public function toggleFavorite(BuySellItem $item): JsonResponse
    {
        $user = auth()->user();
        $favorite = $item->favorites()->where('user_id', $user->id)->first();

        if ($favorite) {
            $favorite->delete();
            $favorited = false;
        } else {
            $item->favorites()->create(['user_id' => $user->id]);
            $item->incrementSaves();
            $favorited = true;
        }

        return response()->json([
            'success' => true,
            'message' => $favorited ? 'Item added to favorites' : 'Item removed from favorites',
            'favorited' => $favorited
        ]);
    }

    public function contactSeller(Request $request, BuySellItem $item): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $enquiry = $item->enquiries()->create(array_merge($validator->validated(), [
            'user_id' => auth()->id(),
        ]));

        $item->incrementContacts();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $enquiry
        ]);
    }

    public function getFeaturedItems(Request $request): JsonResponse
    {
        $items = BuySellItem::with(['category', 'images', 'primaryImage'])
            ->promoted()
            ->active()
            ->limit($request->limit ?? 12)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function getMyItems(Request $request): JsonResponse
    {
        $items = BuySellItem::with(['category', 'images', 'primaryImage'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    private function saveBase64Image($base64, $directory): string
    {
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
        $filename = time() . '_' . Str::random(10) . '.jpg';
        $path = $directory . '/' . $filename;
        
        Storage::disk('public')->put($path, $imageData);
        
        return $path;
    }

    private function saveBase64Video($base64, $directory): string
    {
        $videoData = base64_decode(preg_replace('#^data:video/\w+;base64,#i', '', $base64));
        $filename = time() . '_' . Str::random(10) . '.mp4';
        $path = $directory . '/' . $filename;
        
        Storage::disk('public')->put($path, $videoData);
        
        return $path;
    }

    private function createPromotion(BuySellItem $item, string $type): void
    {
        $prices = [
            'promoted' => 29,
            'featured' => 49,
            'sponsored' => 99,
            'network_boost' => 199,
        ];

        BuySellPromotion::create([
            'item_id' => $item->id,
            'promotion_type' => $type,
            'price' => $prices[$type],
            'currency' => 'USD',
            'status' => 'pending',
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
    }
}
