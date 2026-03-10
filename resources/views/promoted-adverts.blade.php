@extends('layouts.app')

@section('title', 'Promoted Adverts - High Visibility Listings')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Explore Promoted Adverts
                </h1>
                <p class="text-xl mb-8">
                    High-Visibility Listings from Around the World
                </p>
                <p class="text-lg mb-8 opacity-90">
                    These adverts are boosted for maximum exposure. Discover what's trending today.
                </p>
                
                <!-- Search Bar -->
                <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-2">
                    <form id="searchForm" class="flex flex-wrap gap-2">
                        <input type="text" id="searchKeyword" placeholder="Search promoted adverts..." 
                               class="flex-1 min-w-[200px] px-4 py-2 text-gray-800 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        
                        <select id="searchCategory" class="px-4 py-2 text-gray-800 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                        </select>
                        
                        <select id="searchCountry" class="px-4 py-2 text-gray-800 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Countries</option>
                        </select>
                        
                        <select id="searchPrice" class="px-4 py-2 text-gray-800 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Any Price</option>
                            <option value="0-100">Under £100</option>
                            <option value="100-500">£100 - £500</option>
                            <option value="500-1000">£500 - £1,000</option>
                            <option value="1000+">Over £1,000</option>
                        </select>
                        
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Activity Feed -->
    <section class="bg-white py-6 border-b">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600">Live Activity</span>
                </div>
                <div id="liveActivity" class="text-sm text-gray-600 italic">
                    <!-- Live activity will be updated here -->
                </div>
            </div>
        </div>
    </section>

    <!-- Global Category Explorer -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Explore Categories</h2>
            <div id="categoriesGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <!-- Categories will be loaded here -->
            </div>
        </div>
    </section>

    <!-- Promoted Adverts Carousel -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-8">Featured Promoted Adverts</h2>
            <div id="featuredCarousel" class="relative">
                <div class="overflow-hidden rounded-lg">
                    <div id="carouselContent" class="flex transition-transform duration-300">
                        <!-- Carousel items will be loaded here -->
                    </div>
                </div>
                <button id="prevBtn" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button id="nextBtn" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/80 p-2 rounded-full shadow-lg hover:bg-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Smart Filters & Sorting -->
    <section class="py-8 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap gap-2">
                    <button class="filter-btn px-4 py-2 bg-white rounded-lg shadow hover:shadow-md transition" data-filter="all">
                        All
                    </button>
                    <button class="filter-btn px-4 py-2 bg-white rounded-lg shadow hover:shadow-md transition" data-filter="featured">
                        Featured
                    </button>
                    <button class="filter-btn px-4 py-2 bg-white rounded-lg shadow hover:shadow-md transition" data-filter="recent">
                        Recent
                    </button>
                    <button class="filter-btn px-4 py-2 bg-white rounded-lg shadow hover:shadow-md transition" data-filter="most-viewed">
                        Most Viewed
                    </button>
                    <button class="filter-btn px-4 py-2 bg-white rounded-lg shadow hover:shadow-md transition" data-filter="most-saved">
                        Most Saved
                    </button>
                </div>
                
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Sort by:</label>
                    <select id="sortBy" class="px-4 py-2 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="created_at">Latest</option>
                        <option value="views_count">Most Viewed</option>
                        <option value="saves_count">Most Saved</option>
                        <option value="price">Price (Low to High)</option>
                        <option value="price_desc">Price (High to Low)</option>
                        <option value="title">Title (A-Z)</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- Promoted Adverts Grid -->
    <section class="py-12">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Promoted Adverts</h2>
                <div class="text-sm text-gray-600">
                    <span id="resultsCount">Loading...</span> adverts found
                </div>
            </div>
            
            <div id="advertsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Adverts will be loaded here -->
            </div>
            
            <!-- Load More Button -->
            <div class="text-center mt-12">
                <button id="loadMoreBtn" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition hidden">
                    Load More Adverts
                </button>
            </div>
        </div>
    </section>

    <!-- Upsell Section -->
    <section class="py-16 bg-gradient-to-r from-yellow-400 to-orange-500 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Want Your Advert Here?</h2>
            <p class="text-xl mb-8">Upgrade to Promoted for instant visibility</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Upsell cards will be loaded here -->
            </div>
            
            <a href="{{ route('promoted-adverts.create') }}" class="bg-white text-orange-500 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Post Your Promoted Advert
            </a>
        </div>
    </section>
</div>

<!-- Quick View Modal -->
<div id="quickViewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 id="modalTitle" class="text-2xl font-bold"></h3>
                    <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modalContent">
                    <!-- Modal content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let currentPage = 1;
let currentFilter = 'all';
let currentSort = 'created_at';
let isLoading = false;

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadFeaturedCarousel();
    loadPromotionOptions();
    loadAdverts();
    startLiveActivity();
    
    // Event listeners
    document.getElementById('searchForm').addEventListener('submit', handleSearch);
    document.getElementById('sortBy').addEventListener('change', handleSort);
    document.getElementById('loadMoreBtn').addEventListener('click', loadMoreAdverts);
    
    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', handleFilter);
    });
    
    // Modal
    document.getElementById('closeModal').addEventListener('click', closeModal);
    document.getElementById('quickViewModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    
    // Carousel
    document.getElementById('prevBtn').addEventListener('click', () => moveCarousel(-1));
    document.getElementById('nextBtn').addEventListener('click', () => moveCarousel(1));
});

// Load categories
async function loadCategories() {
    try {
        const response = await fetch('/api/v1/promoted-advert-categories');
        const data = await response.json();
        
        const grid = document.getElementById('categoriesGrid');
        const searchCategory = document.getElementById('searchCategory');
        
        if (data.success) {
            grid.innerHTML = data.data.map(category => `
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition cursor-pointer category-card" data-category="${category.id}">
                    <div class="h-24 bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center">
                        <div class="text-white text-3xl">
                            ${category.icon ? `<i class="${category.icon}"></i>` : '📦'}
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-sm">${category.name}</h3>
                        <p class="text-xs text-gray-600">${category.active_promoted_adverts_count || 0} ads</p>
                    </div>
                </div>
            `).join('');
            
            // Update search dropdown
            searchCategory.innerHTML = '<option value="">All Categories</option>' + 
                data.data.map(category => `<option value="${category.id}">${category.name}</option>`).join('');
            
            // Add click handlers
            document.querySelectorAll('.category-card').forEach(card => {
                card.addEventListener('click', function() {
                    const categoryId = this.dataset.category;
                    filterByCategory(categoryId);
                });
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load featured carousel
async function loadFeaturedCarousel() {
    try {
        const response = await fetch('/api/v1/promoted-adverts/featured');
        const data = await response.json();
        
        const carouselContent = document.getElementById('carouselContent');
        
        if (data.success) {
            carouselContent.innerHTML = data.data.map(advert => `
                <div class="min-w-[300px] bg-white rounded-lg shadow-lg overflow-hidden carousel-item">
                    <div class="relative">
                        <img src="${advert.main_image_url}" alt="${advert.title}" class="w-full h-48 object-cover">
                        <div class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold">
                            ${advert.promotion_badge}
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">${advert.country}</span>
                            <span class="text-xs text-gray-500">${advert.advert_type_display}</span>
                        </div>
                        <h3 class="font-semibold text-sm mb-2 line-clamp-2">${advert.title}</h3>
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-green-600">${advert.formatted_price}</span>
                            <button onclick="quickView(${advert.id})" class="text-blue-600 hover:text-blue-800 text-sm">
                                Quick View
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading featured carousel:', error);
    }
}

// Load promotion options for upsell section
async function loadPromotionOptions() {
    try {
        const response = await fetch('/api/v1/promoted-adverts/promotion-options');
        const data = await response.json();
        
        const upsellSection = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4');
        
        if (data.success) {
            upsellSection.innerHTML = data.data.map(option => `
                <div class="bg-white/10 backdrop-blur rounded-lg p-6 hover:bg-white/20 transition ${option.popular ? 'ring-2 ring-white' : ''}">
                    ${option.popular ? '<div class="bg-white text-orange-500 text-xs px-2 py-1 rounded-full inline-block mb-2">Most Popular</div>' : ''}
                    <h3 class="text-xl font-bold mb-2">${option.name}</h3>
                    <div class="text-3xl font-bold mb-4">£${option.price}</div>
                    <ul class="text-sm space-y-2 mb-4">
                        ${option.features.map(feature => `<li class="flex items-start"><span class="mr-2">✓</span>${feature}</li>`).join('')}
                    </ul>
                    <a href="{{ route('promoted-adverts.create') }}?tier=${option.tier}" class="bg-white text-orange-500 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition text-center block">
                        Choose ${option.name.split(' ')[1]}
                    </a>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading promotion options:', error);
    }
}

// Load adverts
async function loadAdverts(reset = true) {
    if (isLoading) return;
    isLoading = true;
    
    try {
        if (reset) {
            currentPage = 1;
        }
        
        const params = new URLSearchParams({
            page: currentPage,
            sort_by: currentSort,
            per_page: 12,
        });
        
        // Add filters
        if (currentFilter !== 'all') {
            if (currentFilter === 'featured') params.append('featured', '1');
            if (currentFilter === 'recent') params.append('sort_by', 'created_at');
            if (currentFilter === 'most-viewed') params.append('sort_by', 'views_count');
            if (currentFilter === 'most-saved') params.append('sort_by', 'saves_count');
        }
        
        // Add search filters
        const searchKeyword = document.getElementById('searchKeyword').value;
        const searchCategory = document.getElementById('searchCategory').value;
        const searchCountry = document.getElementById('searchCountry').value;
        const searchPrice = document.getElementById('searchPrice').value;
        
        if (searchKeyword) params.append('search', searchKeyword);
        if (searchCategory) params.append('category', searchCategory);
        if (searchCountry) params.append('country', searchCountry);
        if (searchPrice) {
            if (searchPrice === '0-100') {
                params.append('max_price', '100');
            } else if (searchPrice === '100-500') {
                params.append('min_price', '100');
                params.append('max_price', '500');
            } else if (searchPrice === '500-1000') {
                params.append('min_price', '500');
                params.append('max_price', '1000');
            } else if (searchPrice === '1000+') {
                params.append('min_price', '1000');
            }
        }
        
        const response = await fetch(`/api/v1/promoted-adverts?${params}`);
        const data = await response.json();
        
        const grid = document.getElementById('advertsGrid');
        const resultsCount = document.getElementById('resultsCount');
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        
        if (data.success) {
            const adverts = reset ? data.data.data : [...data.data.data];
            
            grid.innerHTML = adverts.map(advert => `
                <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition advert-card">
                    <div class="relative">
                        <img src="${advert.main_image_url}" alt="${advert.title}" class="w-full h-48 object-cover">
                        <div class="absolute top-2 right-2 bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold">
                            ${advert.promotion_badge}
                        </div>
                        ${advert.is_featured ? '<div class="absolute top-2 left-2 bg-purple-500 text-white px-2 py-1 rounded text-xs font-semibold">Featured</div>' : ''}
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">${advert.country}</span>
                            <span class="text-xs text-gray-500">${advert.advert_type_display}</span>
                        </div>
                        <h3 class="font-semibold text-sm mb-2 line-clamp-2">${advert.title}</h3>
                        ${advert.tagline ? `<p class="text-xs text-gray-600 mb-2 line-clamp-1">${advert.tagline}</p>` : ''}
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg font-bold text-green-600">${advert.formatted_price}</span>
                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                <span>👁 ${advert.views_count}</span>
                                <span>❤️ ${advert.saves_count}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="quickView(${advert.id})" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700 transition">
                                Quick View
                            </button>
                            <button onclick="toggleFavorite(${advert.id}, this)" class="favorite-btn px-3 py-2 border rounded text-sm hover:bg-gray-50 transition ${advert.is_favorited_by_current_user ? 'bg-red-50 border-red-500 text-red-500' : ''}" data-id="${advert.id}">
                                ${advert.is_favorited_by_current_user ? '❤️' : '🤍'}
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
            
            resultsCount.textContent = data.data.total;
            
            // Show/hide load more button
            if (data.data.next_page_url) {
                loadMoreBtn.classList.remove('hidden');
            } else {
                loadMoreBtn.classList.add('hidden');
            }
        }
    } catch (error) {
        console.error('Error loading adverts:', error);
    } finally {
        isLoading = false;
    }
}

// Load more adverts
function loadMoreAdverts() {
    currentPage++;
    loadAdverts(false);
}

// Handle search
function handleSearch(e) {
    e.preventDefault();
    loadAdverts();
}

// Handle sort
function handleSort() {
    currentSort = document.getElementById('sortBy').value;
    loadAdverts();
}

// Handle filter
function handleFilter(e) {
    // Update button states
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('bg-white');
    });
    
    e.target.classList.remove('bg-white');
    e.target.classList.add('bg-blue-600', 'text-white');
    
    currentFilter = e.target.dataset.filter;
    loadAdverts();
}

// Filter by category
function filterByCategory(categoryId) {
    document.getElementById('searchCategory').value = categoryId;
    loadAdverts();
}

// Quick view
async function quickView(id) {
    try {
        const response = await fetch(`/api/v1/promoted-adverts/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const advert = data.data;
            const modal = document.getElementById('quickViewModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');
            
            modalTitle.textContent = advert.title;
            modalContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <img src="${advert.main_image_url}" alt="${advert.title}" class="w-full rounded-lg">
                        ${advert.additional_images_urls.length > 0 ? `
                            <div class="grid grid-cols-4 gap-2 mt-4">
                                ${advert.additional_images_urls.map(img => `<img src="${img}" alt="" class="w-full h-20 object-cover rounded cursor-pointer">`).join('')}
                            </div>
                        ` : ''}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="bg-yellow-500 text-white px-2 py-1 rounded text-xs font-semibold">${advert.promotion_badge}</span>
                            ${advert.is_featured ? '<span class="bg-purple-500 text-white px-2 py-1 rounded text-xs font-semibold">Featured</span>' : ''}
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">${advert.country}</span>
                        </div>
                        
                        ${advert.tagline ? `<p class="text-lg text-gray-600 mb-4">${advert.tagline}</p>` : ''}
                        
                        <div class="text-3xl font-bold text-green-600 mb-4">${advert.formatted_price}</div>
                        
                        <div class="mb-6">
                            <h4 class="font-semibold mb-2">Description</h4>
                            <p class="text-gray-700">${advert.description}</p>
                        </div>
                        
                        ${advert.key_features && advert.key_features.length > 0 ? `
                            <div class="mb-6">
                                <h4 class="font-semibold mb-2">Key Features</h4>
                                <ul class="list-disc list-inside text-gray-700">
                                    ${advert.key_features.map(feature => `<li>${feature}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                        
                        <div class="mb-6">
                            <h4 class="font-semibold mb-2">Seller Information</h4>
                            <div class="space-y-2 text-gray-700">
                                <p><strong>Name:</strong> ${advert.seller_name}</p>
                                ${advert.business_name ? `<p><strong>Business:</strong> ${advert.business_name}</p>` : ''}
                                <p><strong>Phone:</strong> ${advert.phone}</p>
                                <p><strong>Email:</strong> ${advert.email}</p>
                                ${advert.website ? `<p><strong>Website:</strong> <a href="${advert.website}" target="_blank" class="text-blue-600 hover:underline">${advert.website}</a></p>` : ''}
                            </div>
                        </div>
                        
                        <div class="flex gap-3">
                            <button onclick="window.open('${advert.main_image_url}')" class="flex-1 bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition">
                                Contact Seller
                            </button>
                            <button onclick="toggleFavorite(${advert.id})" class="favorite-btn px-4 py-3 border rounded-lg hover:bg-gray-50 transition">
                                ${advert.is_favorited_by_current_user ? '❤️ Saved' : '🤍 Save'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading advert details:', error);
    }
}

// Close modal
function closeModal() {
    document.getElementById('quickViewModal').classList.add('hidden');
}

// Toggle favorite
async function toggleFavorite(id, button = null) {
    if (!button) {
        button = document.querySelector(`.favorite-btn[data-id="${id}"]`);
    }
    
    try {
        const response = await fetch(`/api/v1/promoted-adverts/${id}/toggle-favorite`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            const isFavorited = data.data.favorited;
            
            if (button) {
                if (isFavorited) {
                    button.classList.add('bg-red-50', 'border-red-500', 'text-red-500');
                    button.textContent = '❤️';
                } else {
                    button.classList.remove('bg-red-50', 'border-red-500', 'text-red-500');
                    button.textContent = '🤍';
                }
            }
        }
    } catch (error) {
        console.error('Error toggling favorite:', error);
    }
}

// Carousel navigation
let currentCarouselPosition = 0;
function moveCarousel(direction) {
    const carousel = document.getElementById('carouselContent');
    const items = carousel.querySelectorAll('.carousel-item');
    const itemWidth = 300 + 16; // width + gap
    const maxPosition = -(items.length - 1) * itemWidth;
    
    currentCarouselPosition += direction * itemWidth;
    currentCarouselPosition = Math.max(maxPosition, Math.min(0, currentCarouselPosition));
    
    carousel.style.transform = `translateX(${currentCarouselPosition}px)`;
}

// Live activity simulation
function startLiveActivity() {
    const activities = [
        'A user from Spain viewed a promoted advert in London',
        'New promoted advert added in Dubai',
        'A car in Manchester just got 12 saves',
        'Someone from Germany favorited a property advert',
        'New service listing promoted in Paris',
        'A business opportunity in New York got 50 views',
    ];
    
    let index = 0;
    setInterval(() => {
        const activityElement = document.getElementById('liveActivity');
        activityElement.style.opacity = '0';
        
        setTimeout(() => {
            activityElement.textContent = activities[index % activities.length];
            activityElement.style.opacity = '1';
            index++;
        }, 300);
    }, 5000);
}
</script>
@endpush
