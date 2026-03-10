<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthorController extends Controller
{
    /**
     * Get authors with filtering
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country' => 'nullable|string|size:2',
            'verified' => 'nullable|boolean',
            'search' => 'nullable|string',
            'sort' => 'nullable|string|in:name_asc,name_desc,books_count,rating,newest',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = Author::withCount(['books' => function($q) {
            $q->active();
        }]);

        // Apply filters
        if ($request->country) {
            $query->byCountry($request->country);
        }

        if ($request->verified !== null) {
            $query->where('verified', $request->verified);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('bio', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Apply sorting
        switch ($request->sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'books_count':
                $query->orderBy('books_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = $request->per_page ?? 20;
        $authors = $query->paginate($perPage);

        return response()->json([
            'data' => $authors->items(),
            'meta' => [
                'current_page' => $authors->currentPage(),
                'last_page' => $authors->lastPage(),
                'per_page' => $authors->perPage(),
                'total' => $authors->total(),
            ]
        ]);
    }

    /**
     * Store a new author
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'required|email|unique:authors,email',
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|string',
            'social_links.*.url' => 'required|url',
            'country' => 'required|string|size:2',
            'user_id' => 'nullable|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['photo']);
        $data['slug'] = Str::slug($request->name) . '-' . Str::random(6);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->storeAs('authors/photos', $photoName, 'public');
            $data['photo'] = $photoPath;
        }

        $author = Author::create($data);

        return response()->json([
            'message' => 'Author created successfully',
            'data' => $author
        ], 201);
    }

    /**
     * Get author details
     */
    public function show($id)
    {
        $author = Author::with(['books' => function($q) {
            $q->active()->orderBy('created_at', 'desc');
        }])->find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        $author->photo_url = $author->photo_url;
        $author->books_count = $author->books->count();

        return response()->json(['data' => $author]);
    }

    /**
     * Update author
     */
    public function update(Request $request, $id)
    {
        $author = Author::find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'nullable|email|unique:authors,email,' . $id,
            'website' => 'nullable|url',
            'social_links' => 'nullable|array',
            'social_links.*.platform' => 'required|string',
            'social_links.*.url' => 'required|url',
            'country' => 'nullable|string|size:2',
            'verified' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->except(['photo']);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($author->photo) {
                Storage::disk('public')->delete($author->photo);
            }
            
            $photo = $request->file('photo');
            $photoName = time() . '_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->storeAs('authors/photos', $photoName, 'public');
            $data['photo'] = $photoPath;
        }

        $author->update($data);

        return response()->json([
            'message' => 'Author updated successfully',
            'data' => $author
        ]);
    }

    /**
     * Delete author
     */
    public function destroy($id)
    {
        $author = Author::find($id);

        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        // Delete photo
        if ($author->photo) {
            Storage::disk('public')->delete($author->photo);
        }

        $author->delete();

        return response()->json(['message' => 'Author deleted successfully']);
    }

    /**
     * Get author's books
     */
    public function books($id, Request $request)
    {
        $author = Author::find($id);
        
        if (!$author) {
            return response()->json(['message' => 'Author not found'], 404);
        }

        $books = Book::where('author_id', $id)
            ->active()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => $books->items(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ],
            'author' => $author
        ]);
    }

    /**
     * Get featured authors for spotlight
     */
    public function spotlight()
    {
        $authors = Author::verified()
            ->withCount(['books' => function($q) {
                $q->active();
            }])
            ->having('books_count', '>', 0)
            ->orderBy('books_count', 'desc')
            ->orderBy('average_rating', 'desc')
            ->take(12)
            ->get();

        return response()->json(['data' => $authors]);
    }

    /**
     * Search authors
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $limit = $request->limit ?? 10;
        
        $authors = Author::where('name', 'LIKE', '%' . $request->q . '%')
            ->orWhere('bio', 'LIKE', '%' . $request->q . '%')
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'photo', 'country', 'verified']);

        return response()->json(['data' => $authors]);
    }
}
