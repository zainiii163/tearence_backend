<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\VerifiesClientPayments;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\BookAdvertPurchase;
use App\Models\AdPricingPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookAdvertController extends Controller
{
    use VerifiesClientPayments;

    /**
     * Display a listing of books with filters and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::with(['user', 'author', 'upsells'])
            ->active()
            ->orderBy('created_at', 'desc');

        // Search by title or author
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by genre
        if ($request->filled('genre')) {
            $query->byGenre($request->input('genre'));
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->byCountry($request->input('country'));
        }

        // Filter by book type
        if ($request->filled('book_type')) {
            $query->where('book_type', $request->input('book_type'));
        }

        // Filter by format
        if ($request->filled('format')) {
            $query->where('format', $request->input('format'));
        }

        // Filter by language
        if ($request->filled('language')) {
            $query->where('language', $request->input('language'));
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Verified authors only
        if ($request->boolean('verified_only')) {
            $query->where('verified_author', true);
        }

        // Promoted books
        if ($request->boolean('promoted_only')) {
            $query->promoted();
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSorts = ['created_at', 'title', 'price', 'views_count', 'saves_count', 'rating'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->input('per_page', 12);
        $books = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $books,
            'filters' => [
                'genres' => Book::select('genre')->distinct()->whereNotNull('genre')->pluck('genre'),
                'countries' => Book::select('country')->distinct()->pluck('country'),
                'book_types' => Book::select('book_type')->distinct()->pluck('book_type'),
                'formats' => ['paperback', 'hardcover', 'ebook', 'audiobook'],
            ]
        ]);
    }

    /**
     * Store a newly created book.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['slug'] = Str::slug($data['title']) . '-' . time();
            $data['user_id'] = Auth::id();

            // Handle upsell selection
            $upsellPrice = 0;
            $advertType = 'standard';
            
            if ($request->filled('upsell_tier')) {
                $pricingPlan = AdPricingPlan::find($request->input('upsell_tier'));
                if ($pricingPlan) {
                    $upsellPrice = $pricingPlan->price;
                    $advertType = $pricingPlan->tier_type;
                    $data['pricing_plan_id'] = $pricingPlan->id;
                    $data['upsell_price'] = $upsellPrice;
                    $data['advert_type'] = $advertType;
                    $data['payment_status'] = 'pending';
                }
            }

            // Upsell maps to advert_type enum on books table (no is_promoted columns)
            if (!isset($data['advert_type'])) {
                $data['advert_type'] = $advertType;
            }

            // Handle file uploads
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
            }

            if ($request->hasFile('author_photo')) {
                $data['author_photo'] = $request->file('author_photo')->store('books/authors', 'public');
            }

            // Handle multiple images
            if ($request->hasFile('additional_images')) {
                $images = [];
                foreach ($request->file('additional_images') as $image) {
                    $images[] = $image->store('books/additional', 'public');
                }
                $data['additional_images'] = $images;
            }

            // Handle sample files
            if ($request->hasFile('sample_files')) {
                $files = [];
                foreach ($request->file('sample_files') as $file) {
                    $files[] = [
                        'path' => $file->store('books/samples', 'public'),
                        'name' => $file->getClientOriginalName(),
                        'type' => $file->getClientOriginalExtension()
                    ];
                }
                $data['sample_files'] = $files;
            }

            $data = self::filterBookTableAttributes($data);

            if (!isset($data['status'])) {
                $data['status'] = 'active';
            }

            $book = Book::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book created successfully!',
                'data' => $book->load(['user', 'author']),
                'payment_required' => $upsellPrice > 0,
                'payment_amount' => $upsellPrice
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create book: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified book.
     */
    public function show($slug): JsonResponse
    {
        $book = Book::with(['user', 'author', 'upsells'])
            ->where('slug', $slug)
            ->first();

        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found'
            ], 404);
        }

        // Increment view count
        $book->incrementViews();

        $payload = $book->toArray();
        $payload['is_purchased'] = false;
        $payload['purchase'] = null;

        $viewerId = null;
        try {
            if (request()->bearerToken()) {
                $viewerId = \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate()?->getAuthIdentifier();
            }
        } catch (\Throwable $e) {
            $viewerId = null;
        }

        if ($viewerId && Schema::hasTable('book_advert_purchases')) {
            $owned = BookAdvertPurchase::where('customer_id', $viewerId)
                ->where('book_id', $book->id)
                ->where('payment_status', 'completed')
                ->latest('id')
                ->first();
            if ($owned) {
                $payload['is_purchased'] = true;
                $payload['purchase'] = [
                    'purchase_id' => $owned->id,
                    'format' => $owned->format,
                    'download_token' => $owned->isDownloadValid() ? $owned->download_token : null,
                    'download_url' => $owned->isDownloadValid()
                        ? url('/api/v1/books-adverts/purchases/download/'.$owned->download_token)
                        : null,
                    'expires_at' => $owned->download_token_expires_at,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $payload
        ]);
    }

    /**
     * Update the specified book.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        if ($book->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Update slug if title changed
            if (isset($data['title']) && $data['title'] !== $book->title) {
                $data['slug'] = Str::slug($data['title']) . '-' . time();
            }

            // Handle file uploads
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
            }

            if ($request->hasFile('author_photo')) {
                $data['author_photo'] = $request->file('author_photo')->store('books/authors', 'public');
            }

            if ($request->hasFile('additional_images')) {
                $images = [];
                foreach ($request->file('additional_images') as $image) {
                    $images[] = $image->store('books/additional', 'public');
                }
                $data['additional_images'] = $images;
            }

            if ($request->hasFile('sample_files')) {
                $files = [];
                foreach ($request->file('sample_files') as $file) {
                    $files[] = [
                        'path' => $file->store('books/samples', 'public'),
                        'name' => $file->getClientOriginalName(),
                        'type' => $file->getClientOriginalExtension()
                    ];
                }
                $data['sample_files'] = $files;
            }

            $data = self::filterBookTableAttributes($data);

            $book->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book updated successfully!',
                'data' => $book->fresh()->load(['user', 'author'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update book: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified book.
     */
    public function destroy(Book $book): JsonResponse
    {
        if ($book->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully!'
        ]);
    }

    /**
     * Get user's books.
     */
    public function myBooks(): JsonResponse
    {
        $books = Book::where('user_id', Auth::id())
            ->with(['author', 'upsells'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    /**
     * Track a book view (public).
     */
    public function trackViews(Book $book): JsonResponse
    {
        $book->incrementViews();

        return response()->json([
            'success' => true,
            'message' => 'View tracked successfully',
            'data' => [
                'views_count' => $book->fresh()->views_count,
            ],
        ]);
    }

    /**
     * Save/bookmark a book.
     */
    public function saveBook(Book $book): JsonResponse
    {
        $user = Auth::user();
        $userId = $user->user_id ?? $user->id;

        if ($book->saves()->where('user_id', $userId)->exists()) {
            $book->saves()->where('user_id', $userId)->delete();
            $saved = false;
        } else {
            $book->saves()->create([
                'user_id' => $userId,
                'saved_at' => now(),
            ]);
            $saved = true;
        }

        $book->incrementSaves();

        return response()->json([
            'success' => true,
            'message' => $saved ? 'Book saved successfully!' : 'Book removed from saves',
            'saved' => $saved,
            'saves_count' => $book->saves_count
        ]);
    }

    /**
     * Get pricing plans for upsell tiers.
     */
    public function getPricingPlans(): JsonResponse
    {
        $plans = AdPricingPlan::where('advert_type', 'books')
            ->where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Get featured books for homepage.
     */
    public function getFeaturedBooks(): JsonResponse
    {
        $books = Book::with(['user', 'author'])
            ->active()
            ->promoted()
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        if ($books->isEmpty()) {
            $books = Book::with(['user', 'author'])
                ->active()
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $books
        ]);
    }

    /**
     * Get books by genre.
     */
    public function getBooksByGenre($genre): JsonResponse
    {
        $books = Book::with(['user', 'author'])
            ->active()
            ->byGenre($genre)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $books,
            'genre' => $genre
        ]);
    }

    /**
     * Start book purchase (reader buys the listing). PayPal confirm unlocks order.
     */
    public function purchase(Request $request, $id): JsonResponse
    {
        if (!Schema::hasTable('book_advert_purchases')) {
            return response()->json([
                'success' => false,
                'message' => 'Run migrations: book_advert_purchases table missing.',
            ], 503);
        }

        $book = Book::active()->findOrFail($id);
        $customerId = Auth::id();

        if ((int) $book->user_id === (int) $customerId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot purchase your own book listing.',
            ], 422);
        }

        $format = $request->input('format', $book->format);
        $price = (float) ($book->price ?? 0);
        $currency = strtoupper($book->currency ?: 'USD');

        $existing = BookAdvertPurchase::where('customer_id', $customerId)
            ->where('book_id', $book->id)
            ->where('payment_status', 'completed')
            ->latest('id')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Already purchased.',
                'data' => [
                    'purchase_id' => $existing->id,
                    'payment_status' => 'completed',
                    'amount' => (float) $existing->price_paid,
                    'currency' => $existing->currency,
                    'format' => $existing->format,
                    'fulfillment' => in_array($existing->format, ['ebook', 'audiobook'], true) ? 'digital' : 'seller',
                    'download_token' => $existing->isDownloadValid() ? $existing->download_token : null,
                    'download_url' => $existing->isDownloadValid()
                        ? url('/api/v1/books-adverts/purchases/download/'.$existing->download_token)
                        : null,
                    'seller_email' => $book->user?->email,
                ],
            ]);
        }

        // Free books — complete immediately
        if ($price <= 0) {
            $purchase = BookAdvertPurchase::create([
                'customer_id' => $customerId,
                'book_id' => $book->id,
                'book_slug' => $book->slug,
                'title' => $book->title,
                'format' => $format,
                'price_paid' => 0,
                'currency' => $currency,
                'payment_status' => 'completed',
                'payment_method' => 'free',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Book claimed successfully.',
                'data' => [
                    'purchase_id' => $purchase->id,
                    'payment_status' => 'completed',
                    'amount' => 0,
                    'currency' => $currency,
                    'format' => $format,
                    'fulfillment' => in_array($format, ['ebook', 'audiobook'], true) ? 'digital' : 'seller',
                    'download_token' => $purchase->download_token,
                    'download_url' => url('/api/v1/books-adverts/purchases/download/'.$purchase->download_token),
                    'seller_email' => $book->user?->email,
                ],
            ]);
        }

        $pending = BookAdvertPurchase::where('customer_id', $customerId)
            ->where('book_id', $book->id)
            ->where('payment_status', 'pending')
            ->latest('id')
            ->first();

        if (!$pending) {
            $pending = BookAdvertPurchase::create([
                'customer_id' => $customerId,
                'book_id' => $book->id,
                'book_slug' => $book->slug,
                'title' => $book->title,
                'format' => $format,
                'price_paid' => $price,
                'currency' => $currency,
                'payment_status' => 'pending',
            ]);
        } else {
            $pending->update([
                'format' => $format,
                'price_paid' => $price,
                'currency' => $currency,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order created. Complete PayPal payment to finish your purchase.',
            'data' => [
                'purchase_id' => $pending->id,
                'payment_status' => 'pending',
                'amount' => (float) $pending->price_paid,
                'currency' => $pending->currency,
                'title' => $pending->title,
                'format' => $pending->format,
                'fulfillment' => in_array($pending->format, ['ebook', 'audiobook'], true) ? 'digital' : 'seller',
            ],
        ], 201);
    }

    /**
     * Confirm PayPal payment for a book purchase.
     */
    public function confirmPurchasePayment(Request $request, int $purchaseId): JsonResponse
    {
        if (!Schema::hasTable('book_advert_purchases')) {
            return response()->json(['success' => false, 'message' => 'Not available'], 503);
        }

        $purchase = BookAdvertPurchase::find($purchaseId);
        if (!$purchase || (int) $purchase->customer_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $book = Book::with('user')->find($purchase->book_id);

        if ($purchase->payment_status === 'completed') {
            return response()->json([
                'success' => true,
                'message' => 'Already paid.',
                'data' => [
                    'purchase_id' => $purchase->id,
                    'payment_status' => 'completed',
                    'format' => $purchase->format,
                    'fulfillment' => in_array($purchase->format, ['ebook', 'audiobook'], true) ? 'digital' : 'seller',
                    'download_token' => $purchase->isDownloadValid() ? $purchase->download_token : null,
                    'download_url' => $purchase->isDownloadValid()
                        ? url('/api/v1/books-adverts/purchases/download/'.$purchase->download_token)
                        : null,
                    'seller_email' => $book?->user?->email,
                    'seller_name' => $book?->author_name,
                ],
            ]);
        }

        $request->validate([
            'payment_id' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $verified = $this->verifyClientPaymentOrFail(
            $request,
            (float) $purchase->price_paid,
            'book_advert',
            $purchase->id
        );
        if ($verified instanceof JsonResponse) {
            return $verified;
        }

        $purchase->payment_id = $verified['payment_id'];
        $purchase->markCompleted($request->input('payment_method', 'paypal'));

        return response()->json([
            'success' => true,
            'message' => 'Payment complete. Your book order is confirmed.',
            'data' => [
                'purchase_id' => $purchase->id,
                'payment_status' => 'completed',
                'format' => $purchase->format,
                'fulfillment' => in_array($purchase->format, ['ebook', 'audiobook'], true) ? 'digital' : 'seller',
                'download_token' => $purchase->download_token,
                'download_url' => url('/api/v1/books-adverts/purchases/download/'.$purchase->download_token),
                'seller_email' => $book?->user?->email,
                'seller_name' => $book?->author_name,
                'amount' => (float) $purchase->price_paid,
                'currency' => $purchase->currency,
            ],
        ]);
    }

    /**
     * Download digital book content after purchase (sample/ebook files).
     */
    public function downloadPurchase(string $token)
    {
        if (!Schema::hasTable('book_advert_purchases')) {
            abort(404);
        }

        $purchase = BookAdvertPurchase::where('download_token', $token)->first();
        if (!$purchase || !$purchase->isDownloadValid()) {
            return response()->json(['success' => false, 'message' => 'Download unavailable or expired'], 403);
        }

        $book = Book::find($purchase->book_id);
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Book not found'], 404);
        }

        $purchase->increment('download_attempts');

        $samples = is_array($book->sample_files) ? $book->sample_files : [];
        if (!empty($samples)) {
            $first = $samples[0];
            $path = is_array($first) ? ($first['path'] ?? null) : $first;
            if ($path && Storage::disk('public')->exists($path)) {
                $name = is_array($first) ? ($first['name'] ?? basename($path)) : basename($path);
                return Storage::disk('public')->download($path, $name);
            }
        }

        // Receipt / order confirmation when no digital file is attached
        $lines = [
            'World Wide Adverts — Book Order Receipt',
            '=======================================',
            'Order ID: '.$purchase->id,
            'Title: '.$book->title,
            'Author: '.$book->author_name,
            'Format: '.$purchase->format,
            'Paid: '.$purchase->currency.' '.$purchase->price_paid,
            'Status: '.$purchase->payment_status,
            'Purchased: '.$purchase->updated_at,
            '',
            in_array($purchase->format, ['ebook', 'audiobook'], true)
                ? 'Digital fulfillment: contact the seller if a file was not attached to this listing.'
                : 'Physical fulfillment: the seller will arrange shipping. Contact them with this order ID.',
            'Seller email: '.($book->user?->email ?? 'n/a'),
        ];

        return response($content = implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="book-order-'.$purchase->id.'.txt"',
        ]);
    }

    /**
     * Process payment for upsell tier.
     */
    public function processPayment(Request $request, Book $book): JsonResponse
    {
        if ($book->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'payment_method' => 'required|string',
            'transaction_id' => 'required|string',
            'payment_id' => 'nullable|string',
            'payment_reference' => 'nullable|string',
        ]);

        try {
            $expected = (float) ($book->upsell_price
                ?? optional(AdPricingPlan::find($book->pricing_plan_id))->price
                ?? 0);
            if ($expected < 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => 'Listing plan amount missing — cannot mark paid without a priced tier.',
                    'defence' => 'payment_verification',
                ], 422);
            }

            $verified = $this->verifyClientPaymentOrFail(
                $request,
                $expected,
                'book_listing',
                $book->id,
                'USD',
                'transaction_id'
            );
            if ($verified instanceof JsonResponse) {
                return $verified;
            }

            $book->update([
                'payment_status' => 'paid',
                'payment_transaction_id' => $verified['payment_id'],
                'paid_at' => now(),
                'expires_at' => now()->addDays(30) // 30 days visibility
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully!',
                'data' => $book->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get books statistics for admin dashboard.
     */
    public function getStatistics(): JsonResponse
    {
        $topGenre = Book::selectRaw('genre, COUNT(*) as count')
            ->whereNotNull('genre')
            ->groupBy('genre')
            ->orderByDesc('count')
            ->first();

        $stats = [
            'total_books' => Book::count(),
            'active_books' => Book::active()->count(),
            'pending_books' => Book::where('status', 'pending')->count(),
            'promoted_books' => Book::promoted()->active()->count(),
            'verified_authors' => Book::where('verified_author', true)->distinct()->count('author_name'),
            'total_authors' => Book::distinct('user_id')->count('user_id'),
            'total_views' => (int) Book::sum('views_count'),
            'total_saves' => (int) Book::sum('saves_count'),
            'total_genres' => Book::whereNotNull('genre')->distinct()->count('genre'),
            'average_rating' => round((float) Book::whereNotNull('rating')->avg('rating'), 1) ?: 0,
            'most_popular_genre' => $topGenre?->genre ?? '—',
            'featured_books_count' => Book::active()->promoted()->count(),
            'books_by_type' => Book::selectRaw('book_type, COUNT(*) as count')
                ->groupBy('book_type')
                ->pluck('count', 'book_type'),
            'books_by_genre' => Book::selectRaw('genre, COUNT(*) as count')
                ->whereNotNull('genre')
                ->groupBy('genre')
                ->pluck('count', 'genre'),
            'books_by_country' => Book::selectRaw('country, COUNT(*) as count')
                ->groupBy('country')
                ->pluck('count', 'country'),
            'recent_books' => Book::with(['user', 'author'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get trending genres
     */
    public function getTrendingGenres(): JsonResponse
    {
        $trendingGenres = Book::selectRaw('genre as name, COUNT(*) as count, SUM(views_count) as total_views')
            ->whereNotNull('genre')
            ->where('status', 'active')
            ->groupBy('genre')
            ->orderBy('total_views', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trendingGenres
        ]);
    }

    /** Keep only columns that exist on the `books` table */
    private static function filterBookTableAttributes(array $data): array
    {
        $deny = [
            'is_promoted', 'is_featured', 'is_sponsored', 'is_top_category',
            'upsell_tier', 'upsell_price', 'pricing_plan_id', 'payment_status',
            'subtitle', 'author_bio', 'author_photo', 'author_photo_url',
            'author_social_links', 'location_address', 'latitude', 'longitude',
            'cover_image_url', 'agreed_to_terms', 'verified_author_badge',
            'upsell_type',
        ];

        foreach ($deny as $key) {
            unset($data[$key]);
        }

        $allowed = [];
        foreach ($data as $key => $value) {
            if (Schema::hasColumn('books', $key)) {
                $allowed[$key] = $value;
            }
        }

        return $allowed;
    }
}
