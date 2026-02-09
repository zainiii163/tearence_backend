<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookPurchase;
use App\Models\Listing;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookController extends Controller
{
    /**
     * Get books with filtering and search
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'genre' => 'nullable|string',
            'book_type' => 'nullable|string|in:physical,pdf,audiobook',
            'format' => 'nullable|string|in:physical,e_book,audiobook',
            'author' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'search' => 'nullable|string',
            'sort' => 'nullable|string|in:newest,oldest,price_low,price_high,relevance,author_az,title_az',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $booksCategory = Category::where('name', 'Books')->first();
        
        if (!$booksCategory) {
            return response()->json(['message' => 'Books category not found'], 404);
        }

        $query = Listing::approved()
            ->active()
            ->where('category_id', $booksCategory->category_id)
            ->with(['customer', 'location', 'category']);

        // Apply filters
        if ($request->genre) {
            $query->byGenre($request->genre);
        }

        if ($request->book_type) {
            $query->byBookType($request->book_type);
        }

        if ($request->format) {
            $query->byFormat($request->format);
        }

        if ($request->author) {
            $query->where('author', 'LIKE', '%' . $request->author . '%');
        }

        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('author', 'LIKE', '%' . $request->search . '%')
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
                $query->orderBy('author', 'asc');
                break;
            case 'title_az':
                $query->orderBy('title', 'asc');
                break;
            case 'relevance':
            case 'newest':
            default:
                $query->orderBySearchPriority()->orderBy('created_at', 'desc');
                break;
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
                'filters' => [
                    'genres' => $booksCategory->filter_config['genres'] ?? [],
                    'book_types' => $booksCategory->filter_config['book_types'] ?? [],
                    'formats' => $booksCategory->filter_config['formats'] ?? [],
                    'conditions' => $booksCategory->filter_config['conditions'] ?? [],
                ]
            ]
        ]);
    }

    /**
     * Store a new book listing
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'nullable|numeric|min:0',
            'book_type' => 'required|string|in:physical,pdf,audiobook',
            'genre' => 'required|string',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'format' => 'required|string|in:physical,e_book,audiobook',
            'condition' => 'nullable|string|in:new,like_new,good,fair',
            'website_url' => 'nullable|url',
            'is_downloadable' => 'boolean',
            'file' => 'required_if:is_downloadable,true|file|mimes:pdf,mp3,m4a,wav|max:51200', // 50MB max
            'location_id' => 'nullable|integer|exists:location,location_id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $booksCategory = Category::where('name', 'Books')->first();
        
        if (!$booksCategory) {
            return response()->json(['message' => 'Books category not found'], 404);
        }

        $data = $request->except(['file', 'attachments']);
        $data['customer_id'] = auth('customer')->id();
        $data['category_id'] = $booksCategory->category_id;
        $data['currency_id'] = 1; // Default currency
        $data['package_id'] = 1; // Default package

        // Handle file upload for downloadable books
        if ($request->is_downloadable && $request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('books', $fileName, 'public');
            
            $data['file_path'] = $filePath;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        // Handle image attachments
        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $image) {
                $path = $image->store('listings', 'public');
                $attachments[] = $path;
            }
            $data['attachments'] = $attachments;
        }

        $listing = Listing::create($data);

        return response()->json([
            'message' => 'Book listing created successfully',
            'data' => $listing->load(['customer', 'location', 'category'])
        ], 201);
    }

    /**
     * Get book details
     */
    public function show($id)
    {
        $book = Listing::with(['customer', 'location', 'category', 'bookPurchases'])
            ->where('listing_id', $id)
            ->first();

        if (!$book || !$book->isBook()) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        // Add formatted data
        $book->formatted_file_size = $book->getFormattedFileSize();
        $book->file_url = $book->getFileUrl();
        $book->total_revenue = $book->getTotalRevenue();
        $book->total_downloads = $book->getTotalDownloads();

        return response()->json(['data' => $book]);
    }

    /**
     * Purchase a book
     */
    public function purchase(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $book = Listing::where('listing_id', $id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->first();

        if (!$book || !$book->isBook()) {
            return response()->json(['message' => 'Book not found or not available'], 404);
        }

        // Check if customer already purchased this book
        $existingPurchase = BookPurchase::where('listing_id', $id)
            ->where('customer_id', auth('customer')->id())
            ->where('payment_status', 'completed')
            ->first();

        if ($existingPurchase) {
            return response()->json(['message' => 'You have already purchased this book'], 400);
        }

        // Create purchase record
        $purchase = BookPurchase::create([
            'listing_id' => $id,
            'customer_id' => auth('customer')->id(),
            'price_paid' => $book->price ?? 0,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        // In a real implementation, you would integrate with a payment gateway here
        // For now, we'll mark it as completed automatically
        $purchase->markAsCompleted($request->payment_method);

        return response()->json([
            'message' => 'Book purchased successfully',
            'data' => [
                'purchase_id' => $purchase->purchase_id,
                'download_url' => $purchase->getDownloadUrl(),
                'download_token' => $purchase->download_token,
                'expires_at' => $purchase->download_token_expires_at
            ]
        ]);
    }

    /**
     * Download a purchased book
     */
    public function download($token)
    {
        $purchase = BookPurchase::with('listing')
            ->where('download_token', $token)
            ->first();

        if (!$purchase || !$purchase->isDownloadTokenValid()) {
            return response()->json(['message' => 'Invalid or expired download token'], 401);
        }

        $book = $purchase->listing;
        
        if (!$book || !$book->file_path) {
            return response()->json(['message' => 'File not found'], 404);
        }

        // Record the download
        if (!$purchase->recordDownload(request()->ip())) {
            return response()->json(['message' => 'Download limit exceeded'], 403);
        }

        $filePath = storage_path('app/public/' . $book->file_path);
        
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found on server'], 404);
        }

        $fileName = $book->title . ' - ' . $book->author . '.' . $book->file_type;
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);

        return response()->download($filePath, $fileName);
    }

    /**
     * Get customer's purchased books
     */
    public function myPurchases(Request $request)
    {
        $purchases = BookPurchase::with(['listing.customer', 'listing.category'])
            ->where('customer_id', auth('customer')->id())
            ->completed()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $purchases->items(),
            'meta' => [
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
                'per_page' => $purchases->perPage(),
                'total' => $purchases->total(),
            ]
        ]);
    }

    /**
     * Get book statistics for admin
     */
    public function statistics()
    {
        $booksCategory = Category::where('name', 'Books')->first();
        
        if (!$booksCategory) {
            return response()->json(['message' => 'Books category not found'], 404);
        }

        $stats = [
            'total_books' => Listing::where('category_id', $booksCategory->category_id)->count(),
            'active_books' => Listing::where('category_id', $booksCategory->category_id)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->count(),
            'total_purchases' => BookPurchase::completed()->count(),
            'total_revenue' => BookPurchase::completed()->sum('price_paid'),
            'total_downloads' => BookPurchase::completed()->sum('total_downloads'),
            'books_by_type' => Listing::where('category_id', $booksCategory->category_id)
                ->selectRaw('book_type, COUNT(*) as count')
                ->groupBy('book_type')
                ->pluck('count', 'book_type'),
            'books_by_genre' => Listing::where('category_id', $booksCategory->category_id)
                ->selectRaw('genre, COUNT(*) as count')
                ->groupBy('genre')
                ->pluck('count', 'genre'),
            'recent_purchases' => BookPurchase::with(['listing', 'customer'])
                ->completed()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get(),
        ];

        return response()->json(['data' => $stats]);
    }
}
