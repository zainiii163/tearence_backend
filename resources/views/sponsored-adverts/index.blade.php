@extends('layouts.app')

@section('title', 'Sponsored Adverts - Premium Global Advertising')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white py-20">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h1 class="text-5xl md:text-6xl font-bold mb-6">
                    Explore Sponsored Adverts From Across the Globe
                </h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-8">
                    Premium, high-visibility listings from top businesses and creators.
                </p>
            </div>

            <!-- Universal Search Bar -->
            <div class="max-w-4xl mx-auto">
                <form id="sponsored-search-form" class="bg-white rounded-lg shadow-xl p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Keyword</label>
                            <input type="text" id="search-keyword" placeholder="Search adverts..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Category</label>
                            <select id="search-category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Categories</option>
                                <option value="Product">Products</option>
                                <option value="Service">Services</option>
                                <option value="Property">Property</option>
                                <option value="Job">Jobs</option>
                                <option value="Event">Events</option>
                                <option value="Vehicle">Vehicles</option>
                                <option value="Business Opportunity">Business Opportunities</option>
                                <option value="Miscellaneous">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Country</label>
                            <select id="search-country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Countries</option>
                                <option value="United Kingdom">United Kingdom 🇬🇧</option>
                                <option value="United States">United States 🇺🇸</option>
                                <option value="Canada">Canada 🇨🇦</option>
                                <option value="Australia">Australia 🇦🇺</option>
                                <option value="Germany">Germany 🇩🇪</option>
                                <option value="France">France 🇫🇷</option>
                                <option value="Italy">Italy 🇮🇹</option>
                                <option value="Spain">Spain 🇪🇸</option>
                                <option value="Netherlands">Netherlands 🇳🇱</option>
                                <option value="United Arab Emirates">UAE 🇦🇪</option>
                                <option value="India">India 🇮🇳</option>
                                <option value="China">China 🇨🇳</option>
                                <option value="Japan">Japan 🇯🇵</option>
                                <option value="South Korea">South Korea 🇰🇷</option>
                                <option value="Singapore">Singapore 🇸🇬</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2">Price Range</label>
                            <select id="search-price-range" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Any Price</option>
                                <option value="0-100">Under £100</option>
                                <option value="100-500">£100 - £500</option>
                                <option value="500-1000">£500 - £1,000</option>
                                <option value="1000-5000">£1,000 - £5,000</option>
                                <option value="5000-10000">£5,000 - £10,000</option>
                                <option value="10000+">Over £10,000</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-center">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
                            Search Sponsored Adverts
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Global Category Explorer -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12 text-gray-800">Explore Categories</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="category-grid">
                <!-- Categories will be loaded here -->
            </div>
        </div>
    </section>

    <!-- Featured Carousel -->
    <section class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12 text-gray-800">Featured Sponsored Adverts</h2>
            <div class="relative">
                <div id="featured-carousel" class="flex overflow-x-auto space-x-6 pb-4">
                    <!-- Featured adverts will be loaded here -->
                </div>
                <button id="carousel-prev" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button id="carousel-next" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Live Activity Feed -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Live Activity</h2>
            <div class="max-w-4xl mx-auto">
                <div id="activity-feed" class="space-y-4">
                    <!-- Activity items will be loaded here -->
                </div>
            </div>
        </div>
    </section>

    <!-- Sponsored Adverts Grid -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">All Sponsored Adverts</h2>
                <div class="flex space-x-4">
                    <select id="sort-by" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="created_at">Most Recent</option>
                        <option value="views">Most Viewed</option>
                        <option value="rating">Highest Rated</option>
                        <option value="saves">Most Saved</option>
                        <option value="popularity">Trending</option>
                        <option value="price_low">Price (Low to High)</option>
                        <option value="price_high">Price (High to Low)</option>
                        <option value="tier">Sponsorship Tier</option>
                    </select>
                    <select id="filter-tier" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Tiers</option>
                        <option value="premium">Premium</option>
                        <option value="plus">Plus</option>
                        <option value="basic">Basic</option>
                    </select>
                </div>
            </div>
            <div id="adverts-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Adverts will be loaded here -->
            </div>
            <div id="pagination" class="mt-8 flex justify-center">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    </section>

    <!-- Upsell Section -->
    <section class="py-16 bg-gradient-to-r from-yellow-400 to-orange-500 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-6">Want Your Advert Here?</h2>
            <p class="text-xl mb-8">Upgrade to Sponsored for maximum visibility and reach millions of potential customers.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div class="bg-white text-gray-800 rounded-lg p-6 shadow-xl">
                    <h3 class="text-2xl font-bold mb-4 text-blue-600">Sponsored</h3>
                    <div class="text-3xl font-bold mb-4">£29.99</div>
                    <ul class="text-left mb-6 space-y-2">
                        <li>✓ Listed on Sponsored Adverts Page</li>
                        <li>✓ Highlighted card</li>
                        <li>✓ "Sponsored" badge</li>
                        <li>✓ 3× more visibility than standard ads</li>
                    </ul>
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        Choose Basic
                    </button>
                </div>
                <div class="bg-white text-gray-800 rounded-lg p-6 shadow-xl transform scale-105">
                    <div class="bg-orange-500 text-white text-sm font-bold px-3 py-1 rounded-full inline-block mb-4">Most Popular</div>
                    <h3 class="text-2xl font-bold mb-4 text-blue-600">Sponsored Plus</h3>
                    <div class="text-3xl font-bold mb-4">£59.99</div>
                    <ul class="text-left mb-6 space-y-2">
                        <li>✓ All Basic features</li>
                        <li>✓ Top of category placement</li>
                        <li>✓ Larger advert card</li>
                        <li>✓ Priority in search results</li>
                        <li>✓ Weekly "Sponsored Highlights" email</li>
                    </ul>
                    <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        Choose Plus
                    </button>
                </div>
                <div class="bg-white text-gray-800 rounded-lg p-6 shadow-xl">
                    <h3 class="text-2xl font-bold mb-4 text-blue-600">Sponsored Premium</h3>
                    <div class="text-3xl font-bold mb-4">£99.99</div>
                    <ul class="text-left mb-6 space-y-2">
                        <li>✓ Homepage placement</li>
                        <li>✓ Featured in homepage slider</li>
                        <li>✓ Category top placement</li>
                        <li>✓ Social media promotion</li>
                        <li>✓ "Premium Sponsored" badge</li>
                        <li>✓ Maximum visibility across platform</li>
                    </ul>
                    <button class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                        Choose Premium
                    </button>
                </div>
            </div>
            <div class="mt-12">
                <a href="{{ route('sponsored-adverts.create') }}" class="bg-white text-orange-500 hover:bg-gray-100 font-bold py-4 px-8 rounded-lg text-lg transition duration-300">
                    Post Your Sponsored Advert Now
                </a>
            </div>
        </div>
    </section>
</div>

<!-- Quick View Modal -->
<div id="quick-view-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold">Quick View</h3>
                    <button id="close-modal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modal-content">
                    <!-- Modal content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// API Base URL
const API_BASE = '/api/v1/sponsored-adverts';

// State management
let currentPage = 1;
let currentFilters = {};
let currentSort = 'created_at';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadFeaturedAdverts();
    loadCategories();
    loadAdverts();
    loadLiveActivity();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Search form
    document.getElementById('sponsored-search-form').addEventListener('submit', function(e) {
        e.preventDefault();
        handleSearch();
    });

    // Sort and filter
    document.getElementById('sort-by').addEventListener('change', function() {
        currentSort = this.value;
        currentPage = 1;
        loadAdverts();
    });

    document.getElementById('filter-tier').addEventListener('change', function() {
        currentFilters.tier = this.value;
        currentPage = 1;
        loadAdverts();
    });

    // Carousel controls
    document.getElementById('carousel-prev').addEventListener('click', function() {
        document.getElementById('featured-carousel').scrollBy({ left: -300, behavior: 'smooth' });
    });

    document.getElementById('carousel-next').addEventListener('click', function() {
        document.getElementById('featured-carousel').scrollBy({ left: 300, behavior: 'smooth' });
    });

    // Modal close
    document.getElementById('close-modal').addEventListener('click', closeModal);
    document.getElementById('quick-view-modal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
}

// Handle search
function handleSearch() {
    const keyword = document.getElementById('search-keyword').value;
    const category = document.getElementById('search-category').value;
    const country = document.getElementById('search-country').value;
    const priceRange = document.getElementById('search-price-range').value;

    currentFilters = {};
    if (keyword) currentFilters.search = keyword;
    if (category) currentFilters.advert_type = category;
    if (country) currentFilters.country = country;
    if (priceRange) {
        const [min, max] = priceRange.split('-');
        if (min) currentFilters.min_price = min === '0' ? 0 : min;
        if (max) currentFilters.max_price = max === '+' ? 999999 : max;
    }

    currentPage = 1;
    loadAdverts();
}

// Load featured adverts
async function loadFeaturedAdverts() {
    try {
        const response = await fetch(`${API_BASE}/featured?limit=10`);
        const data = await response.json();
        
        const carousel = document.getElementById('featured-carousel');
        carousel.innerHTML = data.data.map(advert => createFeaturedCard(advert)).join('');
    } catch (error) {
        console.error('Error loading featured adverts:', error);
    }
}

// Load categories
async function loadCategories() {
    const categories = [
        { type: 'Product', name: 'Products', icon: '🛍️', count: 0 },
        { type: 'Service', name: 'Services', icon: '💼', count: 0 },
        { type: 'Property', name: 'Property', icon: '🏠', count: 0 },
        { type: 'Job', name: 'Jobs', icon: '💼', count: 0 },
        { type: 'Event', name: 'Events', icon: '🎉', count: 0 },
        { type: 'Vehicle', name: 'Vehicles', icon: '🚗', count: 0 },
        { type: 'Business Opportunity', name: 'Business', icon: '📈', count: 0 },
        { type: 'Miscellaneous', name: 'Other', icon: '📦', count: 0 }
    ];

    // Load counts for each category
    for (let category of categories) {
        try {
            const response = await fetch(`${API_BASE}?advert_type=${category.type}&per_page=1`);
            const data = await response.json();
            category.count = data.meta.total;
        } catch (error) {
            console.error(`Error loading count for ${category.type}:`, error);
        }
    }

    const grid = document.getElementById('category-grid');
    grid.innerHTML = categories.map(category => createCategoryCard(category)).join('');
}

// Load adverts
async function loadAdverts() {
    try {
        const params = new URLSearchParams({
            page: currentPage,
            per_page: 12,
            sort_by: currentSort.replace('_low', '').replace('_high', ''),
            sort_order: currentSort.includes('_high') ? 'desc' : 'desc',
            ...currentFilters
        });

        const response = await fetch(`${API_BASE}?${params}`);
        const data = await response.json();
        
        const grid = document.getElementById('adverts-grid');
        grid.innerHTML = data.data.data.map(advert => createAdvertCard(advert)).join('');
        
        updatePagination(data.meta);
    } catch (error) {
        console.error('Error loading adverts:', error);
    }
}

// Load live activity
async function loadLiveActivity() {
    const activities = [
        { type: 'view', message: 'A user from France viewed a sponsored car in London', time: '2 minutes ago' },
        { type: 'new', message: 'New sponsored advert added in Dubai', time: '5 minutes ago' },
        { type: 'save', message: 'A property in Lagos just got 10 saves', time: '8 minutes ago' },
        { type: 'inquiry', message: 'Someone inquired about a sponsored service in Berlin', time: '12 minutes ago' },
        { type: 'rating', message: '5-star rating received for a sponsored product in Tokyo', time: '15 minutes ago' }
    ];

    const feed = document.getElementById('activity-feed');
    feed.innerHTML = activities.map(activity => createActivityItem(activity)).join('');
}

// Create featured card
function createFeaturedCard(advert) {
    return `
        <div class="flex-none w-80 bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow cursor-pointer" onclick="quickView('${advert.slug}')">
            <div class="relative">
                <img src="${advert.main_image_url || '/placeholder.png'}" alt="${advert.title}" class="w-full h-48 object-cover">
                <div class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                    ${advert.sponsorship_tier_display}
                </div>
                <div class="absolute top-2 right-2">
                    <span class="text-2xl">${advert.country_flag}</span>
                </div>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg mb-2 line-clamp-2">${advert.title}</h3>
                <p class="text-gray-600 text-sm mb-2 line-clamp-2">${advert.tagline || advert.description}</p>
                <div class="flex justify-between items-center">
                    <span class="text-xl font-bold text-blue-600">${advert.formatted_price}</span>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span>⭐ ${advert.rating || '0'}</span>
                        <span>👁️ ${advert.views_count}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Create category card
function createCategoryCard(category) {
    return `
        <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-all cursor-pointer group" onclick="filterByCategory('${category.type}')">
            <div class="text-4xl mb-4 group-hover:scale-110 transition-transform">${category.icon}</div>
            <h3 class="font-bold text-lg mb-2">${category.name}</h3>
            <p class="text-gray-600 text-sm mb-4">${category.count} sponsored ads</p>
            <button class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                Explore Sponsored Ads
            </button>
        </div>
    `;
}

// Create advert card
function createAdvertCard(advert) {
    return `
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-all group">
            <div class="relative">
                <img src="${advert.main_image_url || '/placeholder.png'}" alt="${advert.title}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                <div class="absolute top-2 left-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                    ${advert.sponsorship_tier_display}
                </div>
                <div class="absolute top-2 right-2 flex space-x-2">
                    <span class="text-2xl">${advert.country_flag}</span>
                </div>
                <button onclick="saveAdvert(${advert.sponsored_advert_id})" class="absolute bottom-2 right-2 bg-white rounded-full p-2 shadow-lg hover:bg-gray-100">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"></path>
                    </svg>
                </button>
            </div>
            <div class="p-4">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-bold text-lg line-clamp-2 flex-1">${advert.title}</h3>
                </div>
                <p class="text-gray-600 text-sm mb-3 line-clamp-2">${advert.tagline || advert.description}</p>
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xl font-bold text-blue-600">${advert.formatted_price}</span>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span>⭐ ${advert.rating || '0'}</span>
                        <span>👁️ ${advert.views_count}</span>
                        <span>💾 ${advert.saves_count}</span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button onclick="quickView('${advert.slug}')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                        Quick View
                    </button>
                    <button onclick="viewDetails('${advert.slug}')" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-300">
                        View Details
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Create activity item
function createActivityItem(activity) {
    const icons = {
        view: '👁️',
        new: '✨',
        save: '💾',
        inquiry: '💬',
        rating: '⭐'
    };

    return `
        <div class="bg-white rounded-lg p-4 shadow hover:shadow-md transition-shadow flex items-center space-x-3">
            <span class="text-2xl">${icons[activity.type]}</span>
            <div class="flex-1">
                <p class="text-gray-800">${activity.message}</p>
                <p class="text-gray-500 text-sm">${activity.time}</p>
            </div>
        </div>
    `;
}

// Update pagination
function updatePagination(meta) {
    const pagination = document.getElementById('pagination');
    
    if (meta.last_page <= 1) {
        pagination.innerHTML = '';
        return;
    }

    let html = '<div class="flex space-x-2">';
    
    // Previous button
    if (meta.current_page > 1) {
        html += `<button onclick="changePage(${meta.current_page - 1})" class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    for (let i = Math.max(1, meta.current_page - 2); i <= Math.min(meta.last_page, meta.current_page + 2); i++) {
        const active = i === meta.current_page ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 hover:bg-gray-50';
        html += `<button onclick="changePage(${i})" class="px-3 py-2 ${active} rounded-lg">${i}</button>`;
    }
    
    // Next button
    if (meta.current_page < meta.last_page) {
        html += `<button onclick="changePage(${meta.current_page + 1})" class="px-3 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>`;
    }
    
    html += '</div>';
    pagination.innerHTML = html;
}

// Change page
function changePage(page) {
    currentPage = page;
    loadAdverts();
}

// Filter by category
function filterByCategory(type) {
    currentFilters.advert_type = type;
    currentPage = 1;
    loadAdverts();
    
    // Scroll to adverts section
    document.getElementById('adverts-grid').scrollIntoView({ behavior: 'smooth' });
}

// Quick view
async function quickView(slug) {
    try {
        const response = await fetch(`${API_BASE}/${slug}`);
        const data = await response.json();
        
        const modal = document.getElementById('quick-view-modal');
        const content = document.getElementById('modal-content');
        
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <img src="${data.data.main_image_url || '/placeholder.png'}" alt="${data.data.title}" class="w-full h-64 object-cover rounded-lg">
                    ${data.data.additional_images_urls.length > 0 ? `
                        <div class="grid grid-cols-3 gap-2 mt-4">
                            ${data.data.additional_images_urls.slice(0, 3).map(img => `<img src="${img}" alt="Additional image" class="w-full h-20 object-cover rounded cursor-pointer hover:opacity-75">`).join('')}
                        </div>
                    ` : ''}
                </div>
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <span class="text-2xl">${data.data.country_flag}</span>
                        <span class="bg-yellow-500 text-white px-2 py-1 rounded-full text-xs font-bold">${data.data.sponsorship_tier_display}</span>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">${data.data.title}</h3>
                    <p class="text-gray-600 mb-4">${data.data.tagline}</p>
                    <div class="text-3xl font-bold text-blue-600 mb-4">${data.data.formatted_price}</div>
                    
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Description</h4>
                        <p class="text-gray-700">${data.data.description}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="font-semibold mb-2">Seller Information</h4>
                        <p class="text-gray-700"><strong>Name:</strong> ${data.data.seller_name}</p>
                        ${data.data.business_name ? `<p class="text-gray-700"><strong>Business:</strong> ${data.data.business_name}</p>` : ''}
                        <p class="text-gray-700"><strong>Email:</strong> ${data.data.email}</p>
                        <p class="text-gray-700"><strong>Phone:</strong> ${data.data.phone}</p>
                        ${data.data.verified_seller ? '<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">✓ Verified Seller</span>' : ''}
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="contactSeller(${data.data.sponsored_advert_id})" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg">
                            Contact Seller
                        </button>
                        <button onclick="viewDetails('${data.data.slug}')" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">
                            View Full Details
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        modal.classList.remove('hidden');
    } catch (error) {
        console.error('Error loading advert details:', error);
    }
}

// Close modal
function closeModal() {
    document.getElementById('quick-view-modal').classList.add('hidden');
}

// View details
function viewDetails(slug) {
    window.location.href = `/sponsored-adverts/${slug}`;
}

// Contact seller
function contactSeller(advertId) {
    // This would open a contact form or modal
    alert('Contact form would open here for advert ID: ' + advertId);
}

// Save advert
async function saveAdvert(advertId) {
    // This would require authentication and API call
    alert('Save functionality would require user authentication');
}
</script>
@endpush
