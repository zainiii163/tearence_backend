@extends('layouts.app')

@section('title', $advert['title'] ?? 'Promoted Advert Detail')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumb -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-4">
            <nav class="flex items-center space-x-2 text-sm">
                <a href="/" class="text-gray-600 hover:text-gray-900">Home</a>
                <span class="text-gray-400">/</span>
                <a href="/promoted-adverts" class="text-gray-600 hover:text-gray-900">Promoted Adverts</a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-900">{{ $advert['title'] ?? 'Advert Detail' }}</span>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Advert Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Images Gallery -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="relative">
                        <img id="mainImage" src="{{ $advert['main_image_url'] ?? '/images/placeholder.png' }}" 
                             alt="{{ $advert['title'] }}" class="w-full h-96 object-cover">
                        
                        <!-- Badges -->
                        <div class="absolute top-4 left-4 flex flex-col gap-2">
                            <div class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                {{ $advert['promotion_badge'] ?? 'Promoted' }}
                            </div>
                            @if($advert['is_featured'] ?? false)
                                <div class="bg-purple-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    Featured
                                </div>
                            @endif
                        </div>
                        
                        <!-- Seller Verification -->
                        @if($advert['verified_seller'] ?? false)
                            <div class="absolute top-4 right-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Verified
                            </div>
                        @endif
                    </div>
                    
                    <!-- Additional Images -->
                    @if(isset($advert['additional_images_urls']) && count($advert['additional_images_urls']) > 0)
                        <div class="p-4">
                            <div class="grid grid-cols-4 gap-2">
                                @foreach($advert['additional_images_urls'] as $image)
                                    <img src="{{ $image }}" alt="Additional image" 
                                         class="w-full h-20 object-cover rounded cursor-pointer hover:opacity-80 transition"
                                         onclick="changeMainImage('{{ $image }}')">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Advert Information -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $advert['title'] ?? 'Advert Title' }}</h1>
                        @if($advert['tagline'] ?? null)
                            <p class="text-lg text-gray-600">{{ $advert['tagline'] }}</p>
                        @endif
                    </div>

                    <!-- Price and Stats -->
                    <div class="flex flex-wrap items-center justify-between mb-6 pb-6 border-b">
                        <div class="text-3xl font-bold text-green-600">
                            {{ $advert['formatted_price'] ?? 'Price on request' }}
                        </div>
                        <div class="flex items-center gap-6 text-sm text-gray-500">
                            <div class="flex items-center gap-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span>{{ number_format($advert['views_count'] ?? 0) }} views</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span>{{ number_format($advert['saves_count'] ?? 0) }} saves</span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <span class="text-sm text-gray-500">Type</span>
                            <p class="font-semibold">{{ $advert['advert_type_display'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Condition</span>
                            <p class="font-semibold">{{ $advert['condition_display'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Location</span>
                            <p class="font-semibold">{{ $advert['city'] ?? 'N/A' }}, {{ $advert['country'] ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Posted</span>
                            <p class="font-semibold">{{ \Carbon\Carbon::parse($advert['created_at'] ?? now())->diffForHumans() }}</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3">Description</h3>
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($advert['description'] ?? 'No description available.')) !!}
                        </div>
                    </div>

                    <!-- Key Features -->
                    @if(isset($advert['key_features']) && is_array($advert['key_features']) && count($advert['key_features']) > 0)
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-3">Key Features</h3>
                            <ul class="space-y-2">
                                @foreach($advert['key_features'] as $feature)
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Special Notes -->
                    @if($advert['special_notes'] ?? null)
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-3">What Makes This Special</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-gray-700">{{ $advert['special_notes'] }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Video -->
                    @if($advert['video_link'] ?? null)
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-3">Video Presentation</h3>
                            <div class="aspect-w-16 aspect-h-9">
                                <iframe src="{{ $advert['video_link'] }}" 
                                        class="w-full h-64 rounded-lg"
                                        frameborder="0" allowfullscreen></iframe>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3">
                        <button onclick="contactSeller()" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                            Contact Seller
                        </button>
                        <button onclick="toggleFavorite({{ $advert['id'] ?? 0 }})" id="favoriteBtn" 
                                class="px-6 py-3 border-2 border-gray-300 rounded-lg font-semibold hover:border-red-500 hover:text-red-500 transition">
                            {{ ($advert['is_favorited_by_current_user'] ?? false) ? '❤️ Saved' : '🤍 Save' }}
                        </button>
                        <button onclick="shareAdvert()" class="px-6 py-3 border-2 border-gray-300 rounded-lg font-semibold hover:border-gray-400 transition">
                            Share
                        </button>
                    </div>
                </div>

                <!-- Location Map -->
                @if(($advert['latitude'] ?? null) && ($advert['longitude'] ?? null))
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold mb-4">Location</h3>
                        <div class="aspect-w-16 aspect-h-9 bg-gray-200 rounded-lg flex items-center justify-center">
                            <div class="text-center">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <p class="text-gray-600">{{ $advert['city'] ?? 'Location' }}, {{ $advert['country'] ?? 'Country' }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ $advert['location_privacy'] ?? 'exact' === 'approximate' ? 'Approximate location' : 'Exact location' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column - Seller Info & Promotion -->
            <div class="space-y-6">
                <!-- Seller Information -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Seller Information</h3>
                    
                    <!-- Seller Profile -->
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mr-4">
                            @if($advert['logo_url'] ?? null)
                                <img src="{{ $advert['logo_url'] }}" alt="Logo" class="w-16 h-16 rounded-full object-cover">
                            @else
                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-semibold text-lg">{{ $advert['seller_name'] ?? 'Seller Name' }}</h4>
                            @if($advert['business_name'] ?? null)
                                <p class="text-gray-600">{{ $advert['business_name'] }}</p>
                            @endif
                            @if($advert['verified_seller'] ?? false)
                                <div class="flex items-center text-green-600 text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Verified Seller
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contact Details -->
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>{{ $advert['phone'] ?? 'Phone not available' }}</span>
                        </div>
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ $advert['email'] ?? 'Email not available' }}</span>
                        </div>
                        @if($advert['website'] ?? null)
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                <a href="{{ $advert['website'] }}" target="_blank" class="text-blue-600 hover:underline">
                                    {{ $advert['website'] }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Contact Button -->
                    <button onclick="contactSeller()" class="w-full bg-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-green-700 transition mt-4">
                        Contact Now
                    </button>
                </div>

                <!-- Promotion Details -->
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg shadow-lg p-6 border border-yellow-200">
                    <h3 class="text-xl font-semibold mb-4">Promotion Details</h3>
                    
                    <div class="mb-4">
                        <div class="text-2xl font-bold text-orange-600 mb-2">
                            {{ $advert['promotion_tier_display'] ?? 'Promoted' }}
                        </div>
                        <div class="text-3xl font-bold text-green-600">
                            £{{ number_format($advert['promotion_price'] ?? 0, 2) }}
                        </div>
                    </div>

                    @if($advert['is_currently_promoted'] ?? false)
                        <div class="bg-green-100 border border-green-300 rounded-lg p-3 mb-4">
                            <p class="text-green-800 text-sm font-semibold">
                                ✓ Currently Active Promotion
                            </p>
                            <p class="text-green-700 text-xs">
                                Valid until {{ \Carbon\Carbon::parse($advert['promotion_end'] ?? now())->format('M j, Y') }}
                            </p>
                        </div>
                    @endif

                    <div class="space-y-2 text-sm text-gray-700">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Enhanced visibility across platform</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Premium placement in search results</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Featured in promotional emails</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-yellow-200">
                        <p class="text-xs text-gray-600 text-center">
                            Want to promote your own advert? 
                            <a href="/promoted-adverts/create" class="text-orange-600 hover:underline font-semibold">
                                Get started here
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Similar Adverts -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Similar Promoted Adverts</h3>
                    <div id="similarAdverts" class="space-y-3">
                        <!-- Similar adverts will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Load advert data
let advertData = @json($advert ?? []);

// Change main image
function changeMainImage(imageUrl) {
    document.getElementById('mainImage').src = imageUrl;
}

// Contact seller
function contactSeller() {
    // Create contact modal or redirect to contact form
    alert('Contact functionality would be implemented here. Phone: ' + (advertData.phone || 'Not available'));
}

// Toggle favorite
async function toggleFavorite(advertId) {
    try {
        const response = await fetch(`/api/v1/promoted-adverts/${advertId}/toggle-favorite`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            const btn = document.getElementById('favoriteBtn');
            if (data.data.favorited) {
                btn.textContent = '❤️ Saved';
                btn.classList.add('border-red-500', 'text-red-500');
            } else {
                btn.textContent = '🤍 Save';
                btn.classList.remove('border-red-500', 'text-red-500');
            }
        } else {
            // Redirect to login if not authenticated
            if (response.status === 401) {
                window.location.href = '/login';
            }
        }
    } catch (error) {
        console.error('Error toggling favorite:', error);
    }
}

// Share advert
function shareAdvert() {
    if (navigator.share) {
        navigator.share({
            title: advertData.title,
            text: advertData.tagline || advertData.description,
            url: window.location.href,
        });
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard!');
    }
}

// Load similar adverts
async function loadSimilarAdverts() {
    try {
        const params = new URLSearchParams({
            category: advertData.category_id,
            country: advertData.country,
            per_page: 3,
        });
        
        // Exclude current advert
        if (advertData.id) {
            params.append('exclude', advertData.id);
        }
        
        const response = await fetch(`/api/v1/promoted-adverts?${params}`);
        const data = await response.json();
        
        if (data.success && data.data.data.length > 0) {
            const container = document.getElementById('similarAdverts');
            container.innerHTML = data.data.data.map(advert => `
                <div class="border rounded-lg p-3 hover:shadow-md transition cursor-pointer" onclick="window.location.href='/promoted-adverts/${advert.slug}'">
                    <div class="flex gap-3">
                        <img src="${advert.main_image_url}" alt="${advert.title}" class="w-20 h-20 object-cover rounded">
                        <div class="flex-1">
                            <h4 class="font-semibold text-sm line-clamp-1">${advert.title}</h4>
                            <p class="text-green-600 font-bold">${advert.formatted_price}</p>
                            <p class="text-xs text-gray-500">${advert.country}</p>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            document.getElementById('similarAdverts').innerHTML = '<p class="text-gray-500 text-sm">No similar adverts found</p>';
        }
    } catch (error) {
        console.error('Error loading similar adverts:', error);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadSimilarAdverts();
    
    // Track view
    if (advertData.id) {
        fetch(`/api/v1/promoted-adverts/${advertData.slug}`, {
            method: 'GET'
        }).catch(console.error);
    }
});
</script>
@endpush
