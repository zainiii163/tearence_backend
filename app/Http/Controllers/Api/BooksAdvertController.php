<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookAdvert;
use App\Models\BookSave;
use App\Models\BookView;
use App\Models\PricingPlan;
use App\Models\BookPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BooksAdvertController extends Controller
{
    /**
     * Get books with filtering and search
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string',
            'genre' => 'nullable|string',
            'country' => 'nullable|string',
            'format' => 'nullable|string|in:paperback,hardcover,ebook,audiobook',
            'book_type' => 'nullable|string',
            'language' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'verified_only' => 'nullable|boolean',
            'promoted_only' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:created_at,title,price,views_count,saves_count,rating',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = BookAdvert::active();

        // Apply filters
        if ($request->search) {
            $query->search($request->search);
        }

        if ($request->genre) {
            $query->byGenre($request->genre);
        }

        if ($request->country) {
            $query->byCountry($request->country);
        }

        if ($request->format) {
            $query->byFormat($request->format);
        }

        if ($request->book_type) {
            $query->byBookType($request->book_type);
        }

        if ($request->language) {
            $query->byLanguage($request->language);
        }

        if ($request->min_price || $request->max_price) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        if ($request->verified_only) {
            $query->verifiedAuthor();
        }

        if ($request->promoted_only) {
            $query->promoted();
        }

        // Apply sorting
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Prioritize promoted books
        if (!$request->promoted_only) {
            $query->orderByRaw("FIELD(advert_type, 'sponsored', 'featured', 'promoted', 'basic') ASC");
        }

        $perPage = $request->per_page ?? 12;
        $books = $query->paginate($perPage, ['*'], 'page', $request->page ?? 1);

        // Get available filters
        $filters = $this->getAvailableFilters();

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $books->items(),
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
                'filters' => $filters
            ]
        ]);
    }

    /**
     * Get single book details by slug
     */
    public function show($slug)
    {
        $book = BookAdvert::active()->where('slug', $slug)->first();

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }

        // Increment view count
        $book->incrementViews();

        // Check if current user has saved this book
        $isSaved = false;
        if (auth('user')->check()) {
            $isSaved = $book->isSavedByUser(auth('user')->id());
        }

        $bookData = $book->toArray();
        $bookData['is_saved'] = $isSaved;

        return response()->json([
            'success' => true,
            'data' => $bookData
        ]);
    }

    /**
     * Create new book advert
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_type' => 'required|string',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'author_name' => 'required|string|max:255',
            'author_bio' => 'nullable|string',
            'author_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'author_social_links' => 'nullable|array',
            'author_social_links.*' => 'url',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'isbn' => 'nullable|string|max:20',
            'pages' => 'nullable|integer|min:1',
            'language' => 'required|string|max:10',
            'genre' => 'required|string|max:100',
            'format' => 'required|string|in:paperback,hardcover,ebook,audiobook',
            'price' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'age_range' => 'nullable|string|max:20',
            'series_name' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:100',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'trailer_video_url' => 'nullable|url',
            'sample_files' => 'nullable|array',
            'sample_files.*' => 'file|mimes:pdf,mp3,m4a,wav|max:10240',
            'purchase_links' => 'nullable|array',
            'purchase_links.*.platform' => 'required|string',
            'purchase_links.*.url' => 'required|url',
            'country' => 'required|string|max:100',
            'location_address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'upsell_tier' => 'nullable|integer|in:1,2,3,4',
            'agreed_to_terms' => 'required|boolean',
            'verified_author' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!auth('user')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $data = $request->except([
            'cover_image', 'additional_images', 'sample_files', 'author_photo'
        ]);
        
        $data['user_id'] = auth('user')->id();
        $data['slug'] = BookAdvert::createUniqueSlug($request->title);
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['verified_author'] = $data['verified_author'] ?? false;
        $data['upsell_tier'] = $data['upsell_tier'] ?? 1;

        // Map upsell_tier to advert_type
        $advertTypeMap = [1 => 'basic', 2 => 'promoted', 3 => 'featured', 4 => 'sponsored'];
        $data['advert_type'] = $advertTypeMap[$data['upsell_tier']] ?? 'basic';

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('books/covers/' . auth('user')->id(), $imageName, 'public');
            $data['cover_image_url'] = asset('storage/' . $imagePath);
        }

        // Handle author photo upload
        if ($request->hasFile('author_photo')) {
            $image = $request->file('author_photo');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('books/authors', $imageName, 'public');
            $data['author_photo_url'] = asset('storage/' . $imagePath);
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('books/images/' . auth('user')->id(), $imageName, 'public');
                $additionalImages[] = asset('storage/' . $imagePath);
            }
            $data['additional_images'] = $additionalImages;
        }

        // Handle sample files
        if ($request->hasFile('sample_files')) {
            $sampleFiles = [];
            foreach ($request->file('sample_files') as $file) {
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('books/samples/' . auth('user')->id(), $fileName, 'public');
                $sampleFiles[] = asset('storage/' . $filePath);
            }
            $data['sample_files'] = $sampleFiles;
        }

        $book = BookAdvert::create($data);

        // Set promotion expiry if not basic
        if ($data['upsell_tier'] > 1) {
            $durationDays = [2 => 30, 3 => 60, 4 => 90][$data['upsell_tier']] ?? 30;
            $book->update(['promoted_until' => now()->addDays($durationDays)]);
        }

        $responseData = [
            'id' => $book->id,
            'slug' => $book->slug,
            'payment_required' => $data['upsell_tier'] > 1,
            'amount' => $this->getUpsellPrice($data['upsell_tier'])
        ];

        return response()->json([
            'success' => true,
            'message' => 'Book created successfully',
            'data' => $responseData
        ], 201);
    }

    /**
     * Update book advert
     */
    public function update(Request $request, $id)
    {
        $book = BookAdvert::where('user_id', auth('user')->id())->find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found or unauthorized'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'book_type' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'author_name' => 'nullable|string|max:255',
            'author_bio' => 'nullable|string',
            'author_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'author_social_links' => 'nullable|array',
            'author_social_links.*' => 'url',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'isbn' => 'nullable|string|max:20',
            'pages' => 'nullable|integer|min:1',
            'language' => 'nullable|string|max:10',
            'genre' => 'nullable|string|max:100',
            'format' => 'nullable|string|in:paperback,hardcover,ebook,audiobook',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'age_range' => 'nullable|string|max:20',
            'series_name' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'trailer_video_url' => 'nullable|url',
            'sample_files' => 'nullable|array',
            'sample_files.*' => 'file|mimes:pdf,mp3,m4a,wav|max:10240',
            'purchase_links' => 'nullable|array',
            'purchase_links.*.platform' => 'required|string',
            'purchase_links.*.url' => 'required|url',
            'country' => 'nullable|string|max:100',
            'location_address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except([
            'cover_image', 'additional_images', 'sample_files', 'author_photo'
        ]);

        // Handle file uploads similar to store method
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('books/covers/' . auth('user')->id(), $imageName, 'public');
            $data['cover_image_url'] = asset('storage/' . $imagePath);
        }

        if ($request->hasFile('author_photo')) {
            $image = $request->file('author_photo');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('books/authors', $imageName, 'public');
            $data['author_photo_url'] = asset('storage/' . $imagePath);
        }

        // Handle additional images and sample files similarly
        if ($request->hasFile('additional_images')) {
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('books/images/' . auth('user')->id(), $imageName, 'public');
                $additionalImages[] = asset('storage/' . $imagePath);
            }
            $data['additional_images'] = $additionalImages;
        }

        if ($request->hasFile('sample_files')) {
            $sampleFiles = [];
            foreach ($request->file('sample_files') as $file) {
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('books/samples/' . auth('user')->id(), $fileName, 'public');
                $sampleFiles[] = asset('storage/' . $filePath);
            }
            $data['sample_files'] = $sampleFiles;
        }

        $book->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Book advert updated successfully',
            'data' => $book
        ]);
    }

    /**
     * Delete book advert
     */
    public function destroy($id)
    {
        $book = BookAdvert::where('user_id', auth('user')->id())->find($id);

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found or unauthorized'
            ], 404);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book advert deleted successfully'
        ]);
    }

    /**
     * Save/bookmark a book
     */
    public function save($id)
    {
        if (!auth('user')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $book = BookAdvert::active()->find($id);
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }

        $userId = auth('user')->id();
        $isSaved = $book->toggleSave($userId);

        return response()->json([
            'success' => true,
            'message' => $isSaved ? 'Book saved successfully' : 'Book removed from saved items',
            'data' => [
                'is_saved' => $isSaved,
                'saves_count' => $book->saves_count
            ]
        ]);
    }

    /**
     * Increment book view count
     */
    public function view($id)
    {
        $book = BookAdvert::active()->find($id);
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }

        $book->incrementViews();

        return response()->json([
            'success' => true,
            'message' => 'View count incremented'
        ]);
    }

    /**
     * Get featured books
     */
    public function featured(Request $request)
    {
        $perPage = $request->per_page ?? 12;
        $page = $request->page ?? 1;

        $books = BookAdvert::active()
            ->featured()
            ->withActivePromotion()
            ->orderBy('advert_type', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $books->items()
        ]);
    }

    /**
     * Get user's books
     */
    public function myBooks(Request $request)
    {
        if (!auth('user')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $perPage = $request->per_page ?? 12;
        $page = $request->page ?? 1;

        $books = BookAdvert::where('user_id', auth('user')->id())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $books->items()
        ]);
    }

    /**
     * Get books by genre
     */
    public function byGenre($genre, Request $request)
    {
        $perPage = $request->per_page ?? 12;
        $page = $request->page ?? 1;

        $books = BookAdvert::active()
            ->byGenre($genre)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $books->items()
        ]);
    }

    /**
     * Get pricing plans
     */
    public function pricingPlans()
    {
        $plans = [
            [
                'id' => 1,
                'name' => 'Basic Listing',
                'price' => 0,
                'features' => [
                    'Standard visibility',
                    '7 days listing',
                    'Basic support'
                ],
                'recommended' => false
            ],
            [
                'id' => 2,
                'name' => 'Promoted',
                'price' => 29,
                'features' => [
                    'Enhanced visibility',
                    '30 days listing',
                    'Priority support',
                    'Promoted badge'
                ],
                'recommended' => false
            ],
            [
                'id' => 3,
                'name' => 'Featured',
                'price' => 79,
                'features' => [
                    'Premium placement',
                    '60 days listing',
                    'Featured badge',
                    'Analytics access'
                ],
                'recommended' => true
            ],
            [
                'id' => 4,
                'name' => 'Sponsored',
                'price' => 149,
                'features' => [
                    'Homepage placement',
                    '90 days listing',
                    'Sponsored badge',
                    'Advanced analytics',
                    'Social media promotion'
                ],
                'recommended' => false
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Process payment for book promotion
     */
    public function payment(Request $request, $id)
    {
        if (!auth('user')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|integer|in:1,2,3,4',
            'payment_method' => 'required|string',
            'payment_token' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $book = BookAdvert::where('user_id', auth('user')->id())->find($id);
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found or unauthorized'
            ], 404);
        }

        // Get pricing plan
        $plan = $this->getPricingPlan($request->plan_id);
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid pricing plan'
            ], 400);
        }

        // Create payment record
        $payment = BookPayment::create([
            'book_id' => $book->id,
            'user_id' => auth('user')->id(),
            'plan_id' => $request->plan_id,
            'amount' => $plan['price'],
            'currency' => 'USD',
            'payment_method' => $request->payment_method,
            'payment_id' => $request->payment_token,
            'status' => 'completed',
            'paid_at' => now(),
            'expires_at' => now()->addDays($plan['duration_days'] ?? 30)
        ]);

        // Update book promotion status
        $advertTypeMap = [1 => 'basic', 2 => 'promoted', 3 => 'featured', 4 => 'sponsored'];
        $book->update([
            'advert_type' => $advertTypeMap[$request->plan_id],
            'upsell_tier' => $request->plan_id,
            'promoted_until' => $payment->expires_at,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => [
                'payment_id' => $payment->id,
                'status' => 'completed',
                'expires_at' => $payment->expires_at
            ]
        ]);
    }

    /**
     * Get platform statistics
     */
    public function statistics()
    {
        $stats = [
            'totalBooks' => BookAdvert::count(),
            'totalAuthors' => BookAdvert::distinct('user_id')->count('user_id'),
            'totalViews' => BookAdvert::sum('views_count'),
            'totalSaves' => BookAdvert::sum('saves_count'),
            'activeCountries' => BookAdvert::distinct('country')->count('country'),
            'topGenres' => BookAdvert::selectRaw('genre as name, COUNT(*) as count')
                ->whereNotNull('genre')
                ->groupBy('genre')
                ->orderBy('count', 'desc')
                ->take(10)
                ->get(),
            'trendingBooks' => BookAdvert::active()
                ->orderBy('views_count', 'desc')
                ->take(10)
                ->get(['title', 'views_count'])
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get available filters
     */
    private function getAvailableFilters(): array
    {
        return [
            'genres' => BookAdvert::active()->whereNotNull('genre')->distinct()->pluck('genre'),
            'formats' => ['paperback', 'hardcover', 'ebook', 'audiobook'],
            'book_types' => ['fiction', 'non-fiction', 'children', 'academic', 'poetry', 'business', 'self-help'],
            'languages' => BookAdvert::active()->distinct()->pluck('language'),
            'countries' => BookAdvert::active()->distinct()->pluck('country')
        ];
    }

    /**
     * Get upsell price by tier
     */
    private function getUpsellPrice($tier): float
    {
        $prices = [1 => 0, 2 => 29, 3 => 79, 4 => 149];
        return $prices[$tier] ?? 0;
    }

    /**
     * Get pricing plan by ID
     */
    private function getPricingPlan($planId): ?array
    {
        $plans = [
            1 => ['price' => 0, 'duration_days' => 7],
            2 => ['price' => 29, 'duration_days' => 30],
            3 => ['price' => 79, 'duration_days' => 60],
            4 => ['price' => 149, 'duration_days' => 90]
        ];

        return $plans[$planId] ?? null;
    }
}
