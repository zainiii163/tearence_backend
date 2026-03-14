<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BookAdvert;
use App\Models\BookPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BooksDashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get user's books with statistics
        $books = BookAdvert::where('user_id', $user->id)
            ->withCount(['saves', 'views'])
            ->latest()
            ->paginate(10);

        // Get statistics
        $stats = [
            'total_books' => BookAdvert::where('user_id', $user->id)->count(),
            'active_books' => BookAdvert::where('user_id', $user->id)->where('status', 'active')->count(),
            'total_views' => BookAdvert::where('user_id', $user->id)->sum('views_count'),
            'total_saves' => BookAdvert::where('user_id', $user->id)->sum('saves_count'),
            'total_payments' => BookPayment::where('user_id', $user->id)->count(),
            'total_spent' => BookPayment::where('user_id', $user->id)->where('status', 'completed')->sum('amount'),
        ];

        // Get recent activity
        $recentBooks = BookAdvert::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = BookPayment::where('user_id', $user->id)
            ->with('plan')
            ->latest()
            ->take(5)
            ->get();

        return view('frontend.books.dashboard', compact('books', 'stats', 'recentBooks', 'recentPayments'));
    }

    public function myBooks(Request $request)
    {
        $user = Auth::user();
        
        $books = BookAdvert::where('user_id', $user->id)
            ->withCount(['saves', 'views'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->advert_type, function ($query, $advertType) {
                return $query->where('advert_type', $advertType);
            })
            ->latest()
            ->paginate(12);

        return view('frontend.books.my-books', compact('books'));
    }

    public function create()
    {
        return view('frontend.books.create');
    }

    public function edit($id)
    {
        $book = BookAdvert::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        return view('frontend.books.edit', compact('book'));
    }

    public function analytics($id)
    {
        $book = BookAdvert::where('user_id', Auth::id())
            ->with(['saves' => function ($query) {
                $query->latest()->take(10);
            }, 'views' => function ($query) {
                $query->latest()->take(10);
            }])
            ->findOrFail($id);

        // Calculate analytics
        $analytics = [
            'total_views' => $book->views_count,
            'total_saves' => $book->saves_count,
            'daily_views' => $book->views()->where('created_at', '>=', now()->subDays(7))->count(),
            'daily_saves' => $book->saves()->where('created_at', '>=', now()->subDays(7))->count(),
            'recent_views' => $book->views()->latest()->take(10)->get(),
            'recent_saves' => $book->saves()->latest()->take(10)->get(),
        ];

        return view('frontend.books.analytics', compact('book', 'analytics'));
    }

    public function payments()
    {
        $user = Auth::user();
        
        $payments = BookPayment::where('user_id', $user->id)
            ->with(['book', 'plan'])
            ->latest()
            ->paginate(15);

        return view('frontend.books.payments', compact('payments'));
    }
}
