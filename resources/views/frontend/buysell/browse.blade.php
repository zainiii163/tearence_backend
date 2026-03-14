@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Browse Adverts</h1>
            <p class="text-gray-600">Discover great deals on items for sale</p>
        </div>
        <a href="{{ route('buysell.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Sell Something
        </a>
    </div>

    <!-- Categories -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Categories</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($categories as $category)
                <a href="{{ route('buysell.browse', ['category' => $category->id]) }}" 
                   class="bg-white rounded-lg shadow p-4 hover:shadow-lg transition text-center">
                    <div class="text-3xl mb-2">{{ $category->icon }}</div>
                    <h3 class="font-medium text-gray-900">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $category->children->count() }} subcategories</p>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Featured Adverts -->
    @if($featuredAdverts->count() > 0)
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Featured Adverts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($featuredAdverts as $advert)
                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                        <div class="relative">
                            @if($advert->images->count() > 0)
                                <img src="{{ asset('storage/' . $advert->images->first()->image_path) }}" 
                                     alt="{{ $advert->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            @if($advert->featured)
                                <span class="absolute top-2 left-2 px-2 py-1 bg-yellow-500 text-white text-xs rounded-full">
                                    Featured
                                </span>
                            @endif
                            @if($advert->urgent)
                                <span class="absolute top-2 right-2 px-2 py-1 bg-red-500 text-white text-xs rounded-full">
                                    Urgent
                                </span>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 mb-1 line-clamp-2">{{ $advert->title }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ $advert->category->icon }} {{ $advert->category->name }}</p>
                            <p class="text-lg font-bold text-green-600 mb-2">${{ number_format($advert->price, 2) }}</p>
                            <p class="text-sm text-gray-500 mb-2">{{ $advert->city }}, {{ $advert->country }}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">{{ $advert->created_at->diffForHumans() }}</span>
                                <a href="{{ route('buysell.show', $advert->slug) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Adverts -->
    @if($recentAdverts->count() > 0)
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Adverts</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($recentAdverts as $advert)
                    <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition">
                        <div class="relative">
                            @if($advert->images->count() > 0)
                                <img src="{{ asset('storage/' . $advert->images->first()->image_path) }}" 
                                     alt="{{ $advert->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            @if($advert->urgent)
                                <span class="absolute top-2 right-2 px-2 py-1 bg-red-500 text-white text-xs rounded-full">
                                    Urgent
                                </span>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-medium text-gray-900 mb-1 line-clamp-2">{{ $advert->title }}</h3>
                            <p class="text-sm text-gray-600 mb-2">{{ $advert->category->icon }} {{ $advert->category->name }}</p>
                            <p class="text-lg font-bold text-green-600 mb-2">${{ number_format($advert->price, 2) }}</p>
                            <p class="text-sm text-gray-500 mb-2">{{ $advert->city }}, {{ $advert->country }}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">{{ $advert->created_at->diffForHumans() }}</span>
                                <a href="{{ route('buysell.show', $advert->slug) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
