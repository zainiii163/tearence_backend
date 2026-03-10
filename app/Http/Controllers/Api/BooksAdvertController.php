<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Author;
use App\Models\BookCategory;
use App\Models\BookUpsell;
use App\Models\BookSave;
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
            'genre' => 'nullable|string',
            'book_type' => 'nullable|string|in:fiction,non-fiction,children,poetry,academic,self-help,business,other',
            'country' => 'nullable|string|size:2',
            'language' => 'nullable|string|max:10',
            'format' => 'nullable|string|in:paperback,hardcover,ebook,audiobook',
            'author' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'search' => 'nullable|string',
            'sort' => 'nullable|string|in:newest,oldest,price_low,price_high,relevance,author_az,title_az,most_viewed,trending',
            'advert_type' => 'nullable|string|in:standard,promoted,featured,sponsored,top_category',
            'per_page' => 'nullable|integer|min:1|max:50',
            'verified_author' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Book::with(['author', 'user', 'upsells' => function($q) {
            $q->active();
        }])->active();

        // Apply filters
        if ($request->genre) {
            $query->byGenre($request->genre);
        }

        if ($request->book_type) {
            $query->where('book_type', $request->book_type);
        }

        if ($request->country) {
            $query->byCountry($request->country);
        }

        if ($request->language) {
            $query->where('language', $request->language);
        }

        if ($request->format) {
            $query->where('format', $request->format);
        }

        if ($request->author) {
            $query->where('author_name', 'LIKE', '%' . $request->author . '%');
        }

        if ($request->verified_author) {
            $query->where('verified_author', $request->verified_author);
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->advert_type) {
            $query->where('advert_type', $request->advert_type);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('author_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('isbn', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Apply sorting
        switch ($request->sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'author_az':
                $query->orderBy('author_name', 'asc');
                break;
            case 'title_az':
                $query->orderBy('title', 'asc');
                break;
            case 'most_viewed':
                $query->orderBy('views_count', 'desc');
                break;
            case 'trending':
                $query->orderBy('saves_count', 'desc')->orderBy('views_count', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Prioritize promoted books if no specific advert_type is requested
        if (!$request->advert_type) {
            $query->orderByRaw("FIELD(advert_type, 'top_category', 'sponsored', 'featured', 'promoted', 'standard') ASC");
        }

        $perPage = $request->per_page ?? 20;
        $books = $query->paginate($perPage);

        return response()->json([
            'data' => $books->items(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
                'filters' => $this->getAvailableFilters()
            ]
        ]);
    }

    /**
     * Store a new book advert
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'book_type' => 'required|string|in:fiction,non-fiction,children,poetry,academic,self-help,business,other',
            'genre' => 'required|string|max:100',
            'author_name' => 'required|string|max:255',
            'author_id' => 'nullable|integer|exists:authors,id',
            'country' => 'required|string|size:2',
            'language' => 'required|string|max:10',
            'format' => 'required|string|in:paperback,hardcover,ebook,audiobook',
            'isbn' => 'nullable|string|max:20',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'pages' => 'nullable|integer|min:1',
            'age_range' => 'nullable|string|max:50',
            'series_name' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:100',
            'purchase_links' => 'nullable|array',
            'purchase_links.*.platform' => 'required|string',
            'purchase_links.*.url' => 'required|url',
            'trailer_video_url' => 'nullable|url',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'sample_files' => 'nullable|array',
            'sample_files.*' => 'file|mimes:pdf,mp3,m4a,wav|max:10240', // 10MB max
            'upsell_type' => 'nullable|string|in:promoted,featured,sponsored,top_category',
            'verified_author_badge' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['cover_image', 'additional_images', 'sample_files']);
        $data['user_id'] = auth('user')->id();
        $data['slug'] = Str::slug($request->title) . '-' . Str::random(6);
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['verified_author'] = $data['verified_author_badge'] ?? false;

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('books/covers', $imageName, 'public');
            $data['cover_image'] = $imagePath;
        }

        // Handle additional images
        if ($request->hasFile('additional_images')) {
            $additionalImages = [];
            foreach ($request->file('additional_images') as $image) {
                $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('books/additional', $imageName, 'public');
                $additionalImages[] = $imagePath;
            }
            $data['additional_images'] = $additionalImages;
        }

        // Handle sample files
        if ($request->hasFile('sample_files')) {
            $sampleFiles = [];
            foreach ($request->file('sample_files') as $file) {
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('books/samples', $fileName, 'public');
                $sampleFiles[] = [
                    'file' => $filePath,
                    'type' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize()
                ];
            }
            $data['sample_files'] = $sampleFiles;
        }

        $book = Book::create($data);

        // Handle upsell if selected
        if ($request->upsell_type) {
            $this->createUpsell($book, $request->upsell_type);
        }

        return response()->json([
            'message' => 'Book advert created successfully',
            'data' => $book->load(['author', 'user', 'upsells'])
        ], 201);
    }

    /**
     * Get book details
     */
    public function show($id)
    {
        $book = Book::with(['author', 'user', 'upsells' => function($q) {
            $q->active();
        }])->find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Increment view count
        $book->incrementViews();

        // Check if current user has saved this book
        $isSaved = false;
        if (auth('user')->check()) {
            $isSaved = BookSave::where('book_id', $book->id)
                ->where('user_id', auth('user')->id())
                ->exists();
        }

        $book->is_saved = $isSaved;
        $book->cover_image_url = $book->cover_image_url;

        return response()->json(['data' => $book]);
    }

    /**
     * Update book advert
     */
    public function update(Request $request, $id)
    {
        $book = Book::where('user_id', auth('user')->id())->find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found or unauthorized'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'book_type' => 'nullable|string|in:fiction,non-fiction,children,poetry,academic,self-help,business,other',
            'genre' => 'nullable|string|max:100',
            'author_name' => 'nullable|string|max:255',
            'author_id' => 'nullable|integer|exists:authors,id',
            'country' => 'nullable|string|size:2',
            'language' => 'nullable|string|max:10',
            'format' => 'nullable|string|in:paperback,hardcover,ebook,audiobook',
            'isbn' => 'nullable|string|max:20',
            'publisher' => 'nullable|string|max:255',
            'publication_date' => 'nullable|date',
            'pages' => 'nullable|integer|min:1',
            'age_range' => 'nullable|string|max:50',
            'series_name' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:100',
            'purchase_links' => 'nullable|array',
            'purchase_links.*.platform' => 'required|string',
            'purchase_links.*.url' => 'required|url',
            'trailer_video_url' => 'nullable|url',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'additional_images' => 'nullable|array',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'sample_files' => 'nullable|array',
            'sample_files.*' => 'file|mimes:pdf,mp3,m4a,wav|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['cover_image', 'additional_images', 'sample_files']);

        // Handle file uploads similar to store method
        if ($request->hasFile('cover_image')) {
            // Delete old cover image
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            
            $image = $request->file('cover_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('books/covers', $imageName, 'public');
            $data['cover_image'] = $imagePath;
        }

        // Handle additional images and sample files similarly
        // ... (implementation would be similar to store method)

        $book->update($data);

        return response()->json([
            'message' => 'Book advert updated successfully',
            'data' => $book->load(['author', 'user', 'upsells'])
        ]);
    }

    /**
     * Delete book advert
     */
    public function destroy($id)
    {
        $book = Book::where('user_id', auth('user')->id())->find($id);

        if (!$book) {
            return response()->json(['message' => 'Book not found or unauthorized'], 404);
        }

        // Delete associated files
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        if ($book->additional_images) {
            foreach ($book->additional_images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        if ($book->sample_files) {
            foreach ($book->sample_files as $file) {
                Storage::disk('public')->delete($file['file']);
            }
        }

        $book->delete();

        return response()->json(['message' => 'Book advert deleted successfully']);
    }

    /**
     * Save/unsave a book
     */
    public function toggleSave($id)
    {
        if (!auth('user')->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $book = Book::active()->find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $userId = auth('user')->id();
        $existingSave = BookSave::where('book_id', $book->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingSave) {
            $existingSave->delete();
            $book->decrement('saves_count');
            $message = 'Book removed from saved items';
        } else {
            BookSave::create([
                'book_id' => $book->id,
                'user_id' => $userId,
                'saved_at' => now()
            ]);
            $book->increment('saves_count');
            $message = 'Book saved successfully';
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Get user's saved books
     */
    public function savedBooks(Request $request)
    {
        $savedBooks = BookSave::with(['book.author', 'book.user'])
            ->where('user_id', auth('user')->id())
            ->orderBy('saved_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $savedBooks->items(),
            'meta' => [
                'current_page' => $savedBooks->currentPage(),
                'last_page' => $savedBooks->lastPage(),
                'per_page' => $savedBooks->perPage(),
                'total' => $savedBooks->total(),
            ]
        ]);
    }

    /**
     * Get book categories/genres
     */
    public function categories()
    {
        $categories = BookCategory::active()
            ->ordered()
            ->withCount(['books' => function($q) {
                $q->active();
            }])
            ->get();

        return response()->json(['data' => $categories]);
    }

    /**
     * Get featured authors
     */
    public function featuredAuthors()
    {
        $authors = Author::verified()
            ->withCount(['books' => function($q) {
                $q->active();
            }])
            ->orderBy('books_count', 'desc')
            ->take(20)
            ->get();

        return response()->json(['data' => $authors]);
    }

    /**
     * Get upsell pricing and options
     */
    public function upsellOptions()
    {
        $options = [
            'promoted' => [
                'name' => 'Promoted',
                'price' => 29.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'benefits' => BookUpsell::getDefaultBenefits('promoted'),
                'description' => 'Get highlighted listing and 2× more visibility'
            ],
            'featured' => [
                'name' => 'Featured',
                'price' => 59.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'benefits' => BookUpsell::getDefaultBenefits('featured'),
                'description' => 'Top placement in categories and priority search results',
                'is_popular' => true
            ],
            'sponsored' => [
                'name' => 'Sponsored',
                'price' => 99.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'benefits' => BookUpsell::getDefaultBenefits('sponsored'),
                'description' => 'Homepage placement and maximum visibility'
            ],
            'top_category' => [
                'name' => 'Top of Category',
                'price' => 199.99,
                'currency' => 'USD',
                'duration_days' => 30,
                'benefits' => BookUpsell::getDefaultBenefits('top_category'),
                'description' => 'Always pinned at the top of your chosen genre'
            ]
        ];

        return response()->json(['data' => $options]);
    }

    /**
     * Purchase upsell for a book
     */
    public function purchaseUpsell(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'upsell_type' => 'required|string|in:promoted,featured,sponsored,top_category',
            'payment_method' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $book = Book::where('user_id', auth('user')->id())->find($id);
        if (!$book) {
            return response()->json(['message' => 'Book not found or unauthorized'], 404);
        }

        // Check if there's already an active upsell of this type
        $existingUpsell = BookUpsell::where('book_id', $book->id)
            ->where('upsell_type', $request->upsell_type)
            ->active()
            ->first();

        if ($existingUpsell) {
            return response()->json(['message' => 'This upsell is already active for this book'], 400);
        }

        $this->createUpsell($book, $request->upsell_type, $request->payment_method);

        return response()->json(['message' => 'Upsell purchased successfully']);
    }

    /**
     * Get analytics for user's books
     */
    public function myAnalytics()
    {
        $userId = auth('user')->id();
        $books = Book::where('user_id', $userId)->get();

        $analytics = [
            'total_books' => $books->count(),
            'active_books' => $books->where('status', 'active')->count(),
            'total_views' => $books->sum('views_count'),
            'total_saves' => $books->sum('saves_count'),
            'books_by_genre' => $books->groupBy('genre')->map->count(),
            'books_by_type' => $books->groupBy('book_type')->map->count(),
            'recent_views' => $books->sortByDesc('created_at')->take(10)->values(),
            'top_performing' => $books->sortByDesc('views_count')->take(5)->values()
        ];

        return response()->json(['data' => $analytics]);
    }

    /**
     * Get trending books
     */
    public function trending()
    {
        $trendingBooks = Book::active()
            ->with(['author'])
            ->orderBy('saves_count', 'desc')
            ->orderBy('views_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json(['data' => $trendingBooks]);
    }

    /**
     * Get live activity feed
     */
    public function activityFeed()
    {
        $activities = [
            'recent_views' => Book::active()
                ->with(['author'])
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get(['id', 'title', 'author_name', 'country', 'updated_at'])
                ->map(function($book) {
                    return [
                        'type' => 'view',
                        'message' => "A user viewed \"{$book->title}\" from {$book->country}",
                        'timestamp' => $book->updated_at,
                        'book_id' => $book->id
                    ];
                }),
            'new_books' => Book::active()
                ->with(['author'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(['id', 'title', 'author_name', 'country', 'created_at'])
                ->map(function($book) {
                    return [
                        'type' => 'new_book',
                        'message' => "New book \"{$book->title}\" added from {$book->country}",
                        'timestamp' => $book->created_at,
                        'book_id' => $book->id
                    ];
                }),
            'popular_saves' => BookSave::with(['book.author'])
                ->orderBy('saved_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($save) {
                    return [
                        'type' => 'save',
                        'message' => "\"{$save->book->title}\" was saved",
                        'timestamp' => $save->saved_at,
                        'book_id' => $save->book_id
                    ];
                })
        ];

        return response()->json(['data' => $activities]);
    }

    private function createUpsell(Book $book, string $upsellType, ?string $paymentMethod = null)
    {
        $pricing = $this->getUpsellPricing($upsellType);
        
        BookUpsell::create([
            'book_id' => $book->id,
            'upsell_type' => $upsellType,
            'price' => $pricing['price'],
            'currency' => $pricing['currency'],
            'duration_days' => $pricing['duration_days'],
            'starts_at' => now(),
            'expires_at' => now()->addDays($pricing['duration_days']),
            'status' => 'active',
            'benefits' => BookUpsell::getDefaultBenefits($upsellType),
            'payment_reference' => $paymentMethod,
            'payment_date' => now(),
            'user_id' => auth('user')->id()
        ]);

        // Update book advert type
        $book->update(['advert_type' => $upsellType]);
    }

    private function getUpsellPricing(string $upsellType): array
    {
        $pricing = [
            'promoted' => ['price' => 29.99, 'currency' => 'USD', 'duration_days' => 30],
            'featured' => ['price' => 59.99, 'currency' => 'USD', 'duration_days' => 30],
            'sponsored' => ['price' => 99.99, 'currency' => 'USD', 'duration_days' => 30],
            'top_category' => ['price' => 199.99, 'currency' => 'USD', 'duration_days' => 30]
        ];

        return $pricing[$upsellType] ?? ['price' => 0, 'currency' => 'USD', 'duration_days' => 30];
    }

    private function getAvailableFilters(): array
    {
        return [
            'genres' => BookCategory::active()->pluck('name'),
            'book_types' => ['fiction', 'non-fiction', 'children', 'poetry', 'academic', 'self-help', 'business', 'other'],
            'formats' => ['paperback', 'hardcover', 'ebook', 'audiobook'],
            'countries' => Book::active()->distinct()->pluck('country'),
            'languages' => Book::active()->distinct()->pluck('language'),
            'advert_types' => ['standard', 'promoted', 'featured', 'sponsored', 'top_category']
        ];
    }
}
