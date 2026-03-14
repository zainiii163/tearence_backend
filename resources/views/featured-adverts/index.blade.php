@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Featured Adverts</h1>
        <p class="text-lg text-gray-600 mb-8">Discover premium listings from around the world</p>
        
        <!-- Search Bar -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-2 flex">
                <input type="text" id="searchInput" placeholder="Search featured adverts..." 
                       class="flex-1 px-4 py-2 focus:outline-none">
                <select id="categoryFilter" class="px-4 py-2 border-l focus:outline-none">
                    <option value="">All Categories</option>
                </select>
                <select id="countryFilter" class="px-4 py-2 border-l focus:outline-none">
                    <option value="">All Countries</option>
                </select>
                <button onclick="searchAdverts()" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Search
                </button>
            </div>
        </div>
    </div>

    <!-- Category Grid -->
    <div class="mb-12">
        <h2 class="text-2xl font-semibold mb-6">Browse by Category</h2>
        <div id="categoryGrid" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <!-- Categories will be loaded here -->
        </div>
    </div>

    <!-- Featured Carousel -->
    <div class="mb-12">
        <h2 class="text-2xl font-semibold mb-6">Sponsored Listings</h2>
        <div id="sponsoredCarousel" class="relative">
            <div class="flex overflow-x-auto space-x-4 pb-4" id="carouselContainer">
                <!-- Sponsored adverts will be loaded here -->
            </div>
            <button onclick="scrollCarousel('left')" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button onclick="scrollCarousel('right')" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Filters and Sorting -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-2">
                <button onclick="filterByTier('all')" class="filter-btn px-4 py-2 rounded-md border" data-tier="all">All</button>
                <button onclick="filterByTier('promoted')" class="filter-btn px-4 py-2 rounded-md border" data-tier="promoted">Promoted</button>
                <button onclick="filterByTier('featured')" class="filter-btn px-4 py-2 rounded-md border" data-tier="featured">Featured</button>
                <button onclick="filterByTier('sponsored')" class="filter-btn px-4 py-2 rounded-md border" data-tier="sponsored">Sponsored</button>
            </div>
            
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium">Sort by:</label>
                <select id="sortSelect" onchange="sortAdverts()" class="px-3 py-2 border rounded-md">
                    <option value="priority">Priority</option>
                    <option value="newest">Newest</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="views">Most Viewed</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Main Adverts Grid -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">All Featured Adverts</h2>
            <div class="text-sm text-gray-600">
                <span id="resultCount">Loading...</span> results
            </div>
        </div>
        
        <div id="advertsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Adverts will be loaded here -->
        </div>
        
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Loading featured adverts...</p>
        </div>
        
        <!-- Empty State -->
        <div id="emptyState" class="text-center py-12 hidden">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-600 mb-2">No Featured Adverts Found</h3>
            <p class="text-gray-500">Try adjusting your filters or search terms</p>
        </div>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="flex justify-center items-center space-x-2">
        <!-- Pagination will be loaded here -->
    </div>

    <!-- Trending Countries -->
    <div class="mt-12 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6">Trending Countries</h2>
        <div id="trendingCountries" class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <!-- Trending countries will be loaded here -->
        </div>
    </div>

    <!-- Live Activity Feed -->
    <div class="mt-12 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold mb-6">Live Activity</h2>
        <div id="liveActivity" class="space-y-3">
            <!-- Live activity will be loaded here -->
        </div>
    </div>
</div>

<!-- Advert Detail Modal -->
<div id="advertModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white border-b p-4 flex justify-between items-center">
            <h3 class="text-xl font-semibold">Featured Advert Details</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="modalContent" class="p-4">
            <!-- Modal content will be loaded here -->
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {
    tier: 'all',
    category: '',
    country: '',
    search: '',
    sort: 'priority'
};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadInitialData();
});

async function loadInitialData() {
    try {
        await Promise.all([
            loadCategories(),
            loadSponsoredCarousel(),
            loadTrendingCountries(),
            loadLiveActivity(),
            loadAdverts()
        ]);
    } catch (error) {
        console.error('Error loading initial data:', error);
    }
}

async function loadCategories() {
    try {
        const response = await fetch('/api/featured-adverts/category-grid');
        const data = await response.json();
        
        if (data.success) {
            const grid = document.getElementById('categoryGrid');
            grid.innerHTML = data.data.map(category => `
                <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow cursor-pointer" onclick="filterByCategory(${category.category_id})">
                    <div class="text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-sm">${category.name}</h3>
                        <p class="text-xs text-gray-500">${category.featured_adverts_count || 0} ads</p>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadSponsoredCarousel() {
    try {
        const response = await fetch('/api/featured-adverts/carousel');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('carouselContainer');
            container.innerHTML = data.data.map(advert => createAdvertCard(advert, 'carousel')).join('');
        }
    } catch (error) {
        console.error('Error loading sponsored carousel:', error);
    }
}

async function loadTrendingCountries() {
    try {
        const response = await fetch('/api/featured-adverts/trending-countries');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('trendingCountries');
            container.innerHTML = data.data.map(country => `
                <div class="text-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer" onclick="filterByCountry('${country.country}')">
                    <div class="text-lg font-semibold">${country.count}</div>
                    <div class="text-sm text-gray-600">${country.country}</div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading trending countries:', error);
    }
}

async function loadLiveActivity() {
    try {
        const response = await fetch('/api/featured-adverts/live-activity');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('liveActivity');
            container.innerHTML = data.data.slice(0, 5).map(activity => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <div>
                            <p class="text-sm font-medium">${activity.message}</p>
                            <p class="text-xs text-gray-500">${activity.customer} • ${formatTime(activity.created_at)}</p>
                        </div>
                    </div>
                    <button onclick="viewAdvert(${activity.advert_id})" class="text-blue-600 text-sm hover:underline">View</button>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading live activity:', error);
    }
}

async function loadAdverts(page = 1) {
    try {
        document.getElementById('loadingState').classList.remove('hidden');
        document.getElementById('emptyState').classList.add('hidden');
        
        const params = new URLSearchParams({
            page: page,
            per_page: 12,
            sort_by: currentFilters.sort,
            ...currentFilters
        });
        
        // Remove 'all' filter values
        if (params.get('tier') === 'all') params.delete('tier');
        
        const response = await fetch(`/api/featured-adverts?${params}`);
        const data = await response.json();
        
        if (data.success) {
            const grid = document.getElementById('advertsGrid');
            const adverts = data.data.data;
            
            if (adverts.length === 0) {
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('emptyState').classList.remove('hidden');
                document.getElementById('resultCount').textContent = '0';
                return;
            }
            
            grid.innerHTML = adverts.map(advert => createAdvertCard(advert, 'grid')).join('');
            document.getElementById('resultCount').textContent = data.data.total;
            
            // Update pagination
            updatePagination(data.data);
        }
    } catch (error) {
        console.error('Error loading adverts:', error);
    } finally {
        document.getElementById('loadingState').classList.add('hidden');
    }
}

function createAdvertCard(advert, type) {
    const badgeColors = {
        promoted: 'bg-yellow-100 text-yellow-800',
        featured: 'bg-blue-100 text-blue-800',
        sponsored: 'bg-purple-100 text-purple-800'
    };
    
    const cardClass = type === 'carousel' 
        ? 'min-w-[300px] bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow'
        : 'bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow';
    
    return `
        <div class="${cardClass}">
            <div class="relative">
                ${advert.main_image ? `
                    <img src="${advert.main_image}" alt="${advert.title}" class="w-full h-48 object-cover rounded-lg mb-4">
                ` : `
                    <div class="w-full h-48 bg-gray-200 rounded-lg mb-4 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                `}
                <span class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold rounded-full ${badgeColors[advert.upsell_tier]}">
                    ${advert.upsell_tier}
                </span>
                ${advert.is_verified_seller ? `
                    <span class="absolute top-2 left-2 bg-green-100 text-green-800 px-2 py-1 text-xs font-semibold rounded-full">
                        Verified
                    </span>
                ` : ''}
            </div>
            
            <h3 class="font-semibold text-lg mb-2 line-clamp-2">${advert.title}</h3>
            
            <div class="flex items-center justify-between mb-2">
                <span class="text-lg font-bold text-blue-600">${advert.formatted_price}</span>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    ${advert.view_count}
                </div>
            </div>
            
            <div class="flex items-center text-sm text-gray-500 mb-3">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                ${advert.city}, ${advert.country}
            </div>
            
            <div class="flex space-x-2">
                <button onclick="viewAdvert(${advert.id})" class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-700">
                    View Details
                </button>
                <button onclick="saveAdvert(${advert.id})" class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;
}

function filterByTier(tier) {
    currentFilters.tier = tier;
    
    // Update UI
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('border');
    });
    
    document.querySelector(`[data-tier="${tier}"]`).classList.add('bg-blue-600', 'text-white');
    document.querySelector(`[data-tier="${tier}"]`).classList.remove('border');
    
    loadAdverts(1);
}

function filterByCategory(categoryId) {
    currentFilters.category = categoryId;
    loadAdverts(1);
}

function filterByCountry(country) {
    currentFilters.country = country;
    loadAdverts(1);
}

function sortAdverts() {
    currentFilters.sort = document.getElementById('sortSelect').value;
    loadAdverts(1);
}

function searchAdverts() {
    currentFilters.search = document.getElementById('searchInput').value;
    currentFilters.category = document.getElementById('categoryFilter').value;
    currentFilters.country = document.getElementById('countryFilter').value;
    loadAdverts(1);
}

function scrollCarousel(direction) {
    const container = document.getElementById('carouselContainer');
    const scrollAmount = 320;
    
    if (direction === 'left') {
        container.scrollLeft -= scrollAmount;
    } else {
        container.scrollLeft += scrollAmount;
    }
}

async function viewAdvert(id) {
    try {
        const response = await fetch(`/api/featured-adverts/${id}`);
        const data = await response.json();
        
        if (data.success) {
            showAdvertModal(data.data);
        }
    } catch (error) {
        console.error('Error loading advert details:', error);
    }
}

function showAdvertModal(advert) {
    const modal = document.getElementById('advertModal');
    const content = document.getElementById('modalContent');
    
    content.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                ${advert.main_image ? `
                    <img src="${advert.main_image}" alt="${advert.title}" class="w-full h-64 object-cover rounded-lg">
                ` : `
                    <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                `}
                
                ${advert.images && advert.images.length > 1 ? `
                    <div class="mt-4 grid grid-cols-4 gap-2">
                        ${advert.images.slice(1, 5).map(img => `
                            <img src="${img}" alt="Additional image" class="w-full h-16 object-cover rounded cursor-pointer">
                        `).join('')}
                    </div>
                ` : ''}
            </div>
            
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold">${advert.title}</h2>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full ${
                        advert.upsell_tier === 'promoted' ? 'bg-yellow-100 text-yellow-800' :
                        advert.upsell_tier === 'featured' ? 'bg-blue-100 text-blue-800' :
                        'bg-purple-100 text-purple-800'
                    }">
                        ${advert.upsell_tier}
                    </span>
                </div>
                
                <div class="text-2xl font-bold text-blue-600 mb-4">${advert.formatted_price}</div>
                
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold mb-2">Description</h3>
                        <p class="text-gray-600">${advert.description || 'No description available'}</p>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold mb-2">Details</h3>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div><strong>Type:</strong> ${advert.advert_type}</div>
                            <div><strong>Condition:</strong> ${advert.condition || 'N/A'}</div>
                            <div><strong>Location:</strong> ${advert.city}, ${advert.country}</div>
                            <div><strong>Views:</strong> ${advert.view_count}</div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold mb-2">Contact Information</h3>
                        <div class="space-y-1 text-sm">
                            <div><strong>Name:</strong> ${advert.contact_name}</div>
                            <div><strong>Email:</strong> ${advert.contact_email}</div>
                            ${advert.contact_phone ? `<div><strong>Phone:</strong> ${advert.contact_phone}</div>` : ''}
                            ${advert.website ? `<div><strong>Website:</strong> <a href="${advert.website}" target="_blank" class="text-blue-600 hover:underline">${advert.website}</a></div>` : ''}
                        </div>
                    </div>
                    
                    <div class="flex space-x-3 pt-4">
                        <button onclick="contactSeller(${advert.id})" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Contact Seller
                        </button>
                        <button onclick="saveAdvert(${advert.id})" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                            Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('advertModal').classList.add('hidden');
}

async function saveAdvert(id) {
    try {
        const response = await fetch(`/api/featured-adverts/${id}/save`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + getAuthToken()
            }
        });
        
        const data = await response.json();
        if (data.success) {
            alert('Advert saved successfully!');
        }
    } catch (error) {
        console.error('Error saving advert:', error);
    }
}

async function contactSeller(id) {
    const message = prompt('Enter your message to the seller:');
    if (!message) return;
    
    try {
        const response = await fetch(`/api/featured-adverts/${id}/contact`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + getAuthToken()
            },
            body: JSON.stringify({
                message: message,
                name: 'Your Name', // Get from user profile
                email: 'your@email.com', // Get from user profile
                phone: '' // Optional
            })
        });
        
        const data = await response.json();
        if (data.success) {
            alert('Message sent successfully!');
        }
    } catch (error) {
        console.error('Error contacting seller:', error);
    }
}

function updatePagination(paginationData) {
    const container = document.getElementById('pagination');
    const { current_page, last_page, per_page, total } = paginationData;
    
    let paginationHTML = '';
    
    // Previous button
    if (current_page > 1) {
        paginationHTML += `<button onclick="loadAdverts(${current_page - 1})" class="px-3 py-2 border rounded-md hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, current_page - 2);
    const endPage = Math.min(last_page, current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        const isActive = i === current_page;
        paginationHTML += `<button onclick="loadAdverts(${i})" class="px-3 py-2 border rounded-md ${isActive ? 'bg-blue-600 text-white' : 'hover:bg-gray-50'}">${i}</button>`;
    }
    
    // Next button
    if (current_page < last_page) {
        paginationHTML += `<button onclick="loadAdverts(${current_page + 1})" class="px-3 py-2 border rounded-md hover:bg-gray-50">Next</button>`;
    }
    
    container.innerHTML = paginationHTML;
}

function formatTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} min ago`;
    if (diffMins < 1440) return `${Math.floor(diffMins / 60)} hours ago`;
    return `${Math.floor(diffMins / 1440)} days ago`;
}

function getAuthToken() {
    return localStorage.getItem('auth_token') || '';
}

// Close modal when clicking outside
document.getElementById('advertModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
@endsection
