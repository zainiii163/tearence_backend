@extends('layouts.app')

@section('title', 'Venue Services - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <section class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Venue Services</h1>
                    <p class="text-gray-600 mt-1">Find professional services for your events</p>
                </div>
                <a href="{{ route('venue-services.create') }}" class="mt-4 md:mt-0 px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors duration-200">
                    Post Your Service
                </a>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <button onclick="filterByCategory('catering')" class="p-4 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg hover:shadow-lg transition-all duration-300 text-center">
                    <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Catering</h3>
                    <p class="text-sm text-gray-600">Food & beverage services</p>
                </button>
                
                <button onclick="filterByCategory('decor')" class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg hover:shadow-lg transition-all duration-300 text-center">
                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Decor</h3>
                    <p class="text-sm text-gray-600">Event styling</p>
                </button>
                
                <button onclick="filterByCategory('photography')" class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg hover:shadow-lg transition-all duration-300 text-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Photography</h3>
                    <p class="text-sm text-gray-600">Photo & video services</p>
                </button>
                
                <button onclick="filterByCategory('dj')" class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg hover:shadow-lg transition-all duration-300 text-center">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">DJ Services</h3>
                    <p class="text-sm text-gray-600">Music & entertainment</p>
                </button>
                
                <button onclick="filterByCategory('event_planning')" class="p-4 bg-gradient-to-br from-pink-50 to-pink-100 rounded-lg hover:shadow-lg transition-all duration-300 text-center">
                    <div class="w-12 h-12 bg-pink-500 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Event Planning</h3>
                    <p class="text-sm text-gray-600">Full event management</p>
                </button>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="container mx-auto px-4 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <input type="text" id="search-input" placeholder="Search services..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                </div>
                
                <!-- Category Filter -->
                <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">All Categories</option>
                    <option value="catering">Catering</option>
                    <option value="decor">Decor</option>
                    <option value="dj">DJ Services</option>
                    <option value="photography">Photography</option>
                    <option value="videography">Videography</option>
                    <option value="security">Security</option>
                    <option value="event_planning">Event Planning</option>
                    <option value="lighting">Lighting</option>
                    <option value="sound">Sound System</option>
                    <option value="transportation">Transportation</option>
                    <option value="other">Other</option>
                </select>
                
                <!-- Location Filter -->
                <input type="text" id="location-filter" placeholder="City or Country..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                
                <!-- Price Filter -->
                <select id="price-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <option value="">Any Price</option>
                    <option value="0-500">Under $500</option>
                    <option value="500-1000">$500-$1,000</option>
                    <option value="1000-2000">$1,000-$2,000</option>
                    <option value="2000+">$2,000+</option>
                </select>
            </div>
            
            <!-- Sort Options -->
            <div class="flex justify-between items-center mt-4">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Sort by:</span>
                    <select id="sort-select" class="px-3 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <option value="created_at">Newest</option>
                        <option value="name">Name</option>
                        <option value="price">Price</option>
                        <option value="promotion">Promoted</option>
                    </select>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button onclick="setViewMode('grid')" id="grid-view-btn" class="p-2 text-teal-600 border border-teal-300 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </button>
                    <button onclick="setViewMode('list')" id="list-view-btn" class="p-2 text-gray-600 border border-gray-300 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid/List -->
    <section class="container mx-auto px-4 py-8">
        <div id="services-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Services will be loaded here via JavaScript -->
        </div>
        
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-teal-600"></div>
            <p class="mt-4 text-gray-600">Loading services...</p>
        </div>
        
        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 hidden">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A9.001 9.001 0 0011.745 3 9.001 9.001 0 003 13.255V21h18v-7.745z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No services found</h3>
            <p class="text-gray-600 mb-4">Try adjusting your filters or search terms</p>
            <button onclick="clearFilters()" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors duration-200">
                Clear Filters
            </button>
        </div>
        
        <!-- Pagination -->
        <div id="pagination" class="flex justify-center items-center space-x-2 mt-8">
            <!-- Pagination will be loaded here via JavaScript -->
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
let currentPage = 1;
let currentFilters = {};
let viewMode = 'grid';

// Load services
async function loadServices(page = 1, filters = {}) {
    const loadingState = document.getElementById('loading-state');
    const container = document.getElementById('services-container');
    const emptyState = document.getElementById('empty-state');
    
    loadingState.classList.remove('hidden');
    container.innerHTML = '';
    emptyState.classList.add('hidden');
    
    try {
        const params = new URLSearchParams({
            page: page,
            per_page: 12,
            ...filters
        });
        
        const response = await fetch(`/api/v1/venue-services?${params}`);
        const data = await response.json();
        
        if (data.success && data.data.data.length > 0) {
            displayServices(data.data.data);
            displayPagination(data.data);
        } else {
            emptyState.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading services:', error);
        emptyState.classList.remove('hidden');
    } finally {
        loadingState.classList.add('hidden');
    }
}

// Display services
function displayServices(services) {
    const container = document.getElementById('services-container');
    
    if (viewMode === 'grid') {
        container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6';
        container.innerHTML = services.map(service => createServiceCard(service)).join('');
    } else {
        container.className = 'space-y-4';
        container.innerHTML = services.map(service => createServiceListItem(service)).join('');
    }
}

// Create service card (grid view)
function createServiceCard(service) {
    const imageUrl = service.images && service.images.length > 0 ? service.images[0] : '/placeholder.png';
    const categoryColors = {
        'catering': 'from-orange-100 to-orange-200',
        'decor': 'from-purple-100 to-purple-200',
        'dj': 'from-green-100 to-green-200',
        'photography': 'from-blue-100 to-blue-200',
        'event_planning': 'from-pink-100 to-pink-200',
        'other': 'from-gray-100 to-gray-200'
    };
    
    return `
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 group">
            <div class="relative h-48 bg-gradient-to-br ${categoryColors[service.category] || categoryColors.other}">
                <img src="${imageUrl}" alt="${service.name}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                ${service.promotion_badge ? `<span class="absolute top-2 right-2 px-2 py-1 bg-teal-600 text-white text-xs rounded-full">${service.promotion_badge}</span>` : ''}
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300"></div>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">${service.name}</h3>
                <p class="text-teal-600 font-semibold mb-2">${service.formatted_price_range}</p>
                <p class="text-gray-600 text-sm mb-2">${getCategoryLabel(service.category)}</p>
                <p class="text-gray-500 text-sm mb-3">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    ${service.city}, ${service.country}
                </p>
                <a href="/venue-services/${service.slug}" class="block w-full text-center px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors duration-200">
                    View Details
                </a>
            </div>
        </div>
    `;
}

// Create service list item (list view)
function createServiceListItem(service) {
    const imageUrl = service.images && service.images.length > 0 ? service.images[0] : '/placeholder.png';
    
    return `
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all duration-300">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-32 h-32 bg-gradient-to-br from-teal-100 to-teal-200 rounded-lg overflow-hidden flex-shrink-0">
                    <img src="${imageUrl}" alt="${service.name}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-gray-900">${service.name}</h3>
                        ${service.promotion_badge ? `<span class="px-2 py-1 bg-teal-600 text-white text-xs rounded-full">${service.promotion_badge}</span>` : ''}
                    </div>
                    <p class="text-gray-600 mb-2 line-clamp-2">${service.description}</p>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                        <span class="text-teal-600 font-semibold">${service.formatted_price_range}</span>
                        <span>${getCategoryLabel(service.category)}</span>
                        <span>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            ${service.city}, ${service.country}
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="/venue-services/${service.slug}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors duration-200">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    `;
}

// Get category label
function getCategoryLabel(category) {
    const labels = {
        'catering': 'Catering',
        'decor': 'Decor',
        'dj': 'DJ Services',
        'photography': 'Photography',
        'videography': 'Videography',
        'security': 'Security',
        'event_planning': 'Event Planning',
        'lighting': 'Lighting',
        'sound': 'Sound System',
        'transportation': 'Transportation',
        'other': 'Other'
    };
    return labels[category] || category;
}

// Display pagination
function displayPagination(paginationData) {
    const container = document.getElementById('pagination');
    
    if (paginationData.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Previous button
    if (paginationData.current_page > 1) {
        paginationHTML += `<button onclick="loadServices(${paginationData.current_page - 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    for (let i = 1; i <= paginationData.last_page; i++) {
        if (i === paginationData.current_page) {
            paginationHTML += `<button class="px-3 py-2 bg-teal-600 text-white rounded-lg">${i}</button>`;
        } else {
            paginationHTML += `<button onclick="loadServices(${i})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">${i}</button>`;
        }
    }
    
    // Next button
    if (paginationData.current_page < paginationData.last_page) {
        paginationHTML += `<button onclick="loadServices(${paginationData.current_page + 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>`;
    }
    
    container.innerHTML = paginationHTML;
}

// Set view mode
function setViewMode(mode) {
    viewMode = mode;
    const gridBtn = document.getElementById('grid-view-btn');
    const listBtn = document.getElementById('list-view-btn');
    
    if (mode === 'grid') {
        gridBtn.className = 'p-2 text-teal-600 border border-teal-300 rounded';
        listBtn.className = 'p-2 text-gray-600 border border-gray-300 rounded';
    } else {
        listBtn.className = 'p-2 text-teal-600 border border-teal-300 rounded';
        gridBtn.className = 'p-2 text-gray-600 border border-gray-300 rounded';
    }
    
    loadServices(currentPage, currentFilters);
}

// Filter by category
function filterByCategory(category) {
    document.getElementById('category-filter').value = category;
    updateFilters();
}

// Clear filters
function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('category-filter').value = '';
    document.getElementById('location-filter').value = '';
    document.getElementById('price-filter').value = '';
    document.getElementById('sort-select').value = 'created_at';
    
    currentFilters = {};
    currentPage = 1;
    loadServices();
}

// Update filters
function updateFilters() {
    currentFilters = {
        search: document.getElementById('search-input').value,
        category: document.getElementById('category-filter').value,
        location: document.getElementById('location-filter').value,
        sort: document.getElementById('sort-select').value,
    };
    
    // Price filter
    const priceFilter = document.getElementById('price-filter').value;
    if (priceFilter) {
        if (priceFilter === '2000+') {
            currentFilters.min_price = 2000;
        } else {
            const [min, max] = priceFilter.split('-').map(Number);
            currentFilters.min_price = min;
            if (max) currentFilters.max_price = max;
        }
    }
    
    // Remove empty filters
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) delete currentFilters[key];
    });
    
    currentPage = 1;
    loadServices(1, currentFilters);
}

// Setup filter listeners
function setupFilters() {
    const filters = ['search-input', 'category-filter', 'location-filter', 'price-filter', 'sort-select'];
    
    filters.forEach(filterId => {
        const element = document.getElementById(filterId);
        element.addEventListener('change', () => {
            updateFilters();
        });
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
    loadServices();
});
</script>
@endsection
