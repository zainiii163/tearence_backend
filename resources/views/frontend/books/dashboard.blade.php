@extends('frontend.layouts.app')

@section('title', 'Books Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Total Books</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total_books'] }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Active Books</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['active_books'] }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Total Views</h3>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_views']) }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900">Total Saves</h3>
                    <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['total_saves']) }}</p>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Books -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Books</h3>
            </div>
            <div class="p-6">
                @if($recentBooks->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentBooks as $book)
                            <div class="flex items-center space-x-4">
                                <img src="{{ $book->cover_image_url ?? asset('images/default-book.png') }}" 
                                     alt="{{ $book->title }}" 
                                     class="w-12 h-16 object-cover rounded">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">{{ Str::limit($book->title, 40) }}</h4>
                                    <p class="text-sm text-gray-500">{{ $book->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $book->advert_type === 'featured' ? 'bg-yellow-100 text-yellow-800' : ($book->advert_type === 'sponsored' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($book->advert_type) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No books yet</p>
                @endif
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
            </div>
            <div class="p-6">
                @if($recentPayments->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentPayments as $payment)
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ $payment->plan->name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $payment->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-green-600">${{ number_format($payment->amount, 2) }}</p>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">No payments yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 flex justify-center space-x-4">
        <a href="{{ route('books.create') }}" 
           class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
            Add New Book
        </a>
        <a href="{{ route('books.my') }}" 
           class="bg-gray-200 text-gray-800 px-6 py-3 rounded-lg font-medium hover:bg-gray-300 transition-colors">
            View All Books
        </a>
    </div>
</div>
@endsection
