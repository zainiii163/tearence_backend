<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\AdPricingPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookAdvertController extends Controller
{
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

            // Set boolean flags for upsell tiers
            $data['is_promoted'] = in_array($advertType, ['promoted', 'featured', 'sponsored', 'top_category']);
            $data['is_featured'] = in_array($advertType, ['featured', 'sponsored', 'top_category']);
            $data['is_sponsored'] = in_array($advertType, ['sponsored', 'top_category']);
            $data['is_top_category'] = $advertType === 'top_category';

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
        $book = Book::with(['user', 'author', 'upsells', 'purchases'])
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

        return response()->json([
            'success' => true,
            'data' => $book
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
     * Save/bookmark a book.
     */
    public function saveBook(Book $book): JsonResponse
    {
        $user = Auth::user();
        
        if ($book->saves()->where('user_id', $user->id)->exists()) {
            $book->saves()->where('user_id', $user->id)->delete();
            $saved = false;
        } else {
            $book->saves()->create(['user_id' => $user->id]);
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
            'transaction_id' => 'required|string'
        ]);

        try {
            $book->update([
                'payment_status' => 'paid',
                'payment_transaction_id' => $request->input('transaction_id'),
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
        $stats = [
            'total_books' => Book::count(),
            'active_books' => Book::active()->count(),
            'pending_books' => Book::where('status', 'pending')->count(),
            'promoted_books' => Book::promoted()->count(),
            'total_revenue' => Book::where('payment_status', 'paid')->sum('upsell_price'),
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
}
