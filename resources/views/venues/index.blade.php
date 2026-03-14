@extends('layouts.app')

@section('title', 'Venues - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <section class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Venues</h1>
                    <p class="text-gray-600 mt-1">Find the perfect venue for your event</p>
                </div>
                <a href="{{ route('venues.create') }}" class="mt-4 md:mt-0 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                    Post a Venue
                </a>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="container mx-auto px-4 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- Search -->
                <div class="md:col-span-2 lg:col-span-2">
                    <input type="text" id="search-input" placeholder="Search venues..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <!-- Venue Type Filter -->
                <select id="type-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="wedding_hall">Wedding Venues</option>
                    <option value="conference_centre">Conference Centres</option>
                    <option value="party_hall">Party Halls</option>
                    <option value="outdoor_space">Outdoor Spaces</option>
                    <option value="hotel_banquet">Hotels & Banquet Rooms</option>
                    <option value="bar_restaurant">Bars & Restaurants</option>
                    <option value="meeting_room">Meeting Rooms</option>
                    <option value="exhibition_space">Exhibition Spaces</option>
                    <option value="sports_venue">Sports Venues</option>
                </select>
                
                <!-- Location Filter -->
                <input type="text" id="location-filter" placeholder="City or Country..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                
                <!-- Capacity Filter -->
                <select id="capacity-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Any Capacity</option>
                    <option value="0-50">Up to 50</option>
                    <option value="50-100">50-100</option>
                    <option value="100-200">100-200</option>
                    <option value="200-500">200-500</option>
                    <option value="500+">500+</option>
                </select>
                
                <!-- Price Filter -->
                <select id="price-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Any Price</option>
                    <option value="0-500">Under $500</option>
                    <option value="500-1000">$500-$1,000</option>
                    <option value="1000-2000">$1,000-$2,000</option>
                    <option value="2000+">$2,000+</option>
                </select>
            </div>
            
            <!-- Sort Options and Features -->
            <div class="flex justify-between items-center mt-4">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Sort by:</span>
                    <select id="sort-select" class="px-3 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="created_at">Newest</option>
                        <option value="name">Name</option>
                        <option value="capacity">Capacity</option>
                        <option value="price">Price</option>
                        <option value="promotion">Promoted</option>
                    </select>
                </div>
                
                <div class="flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="indoor-filter" class="mr-2">
                        <span class="text-sm text-gray-700">Indoor</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="outdoor-filter" class="mr-2">
                        <span class="text-sm text-gray-700">Outdoor</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="parking-filter" class="mr-2">
                        <span class="text-sm text-gray-700">Parking</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="catering-filter" class="mr-2">
                        <span class="text-sm text-gray-700">Catering</span>
                    </label>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button onclick="setViewMode('grid')" id="grid-view-btn" class="p-2 text-indigo-600 border border-indigo-300 rounded">
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

    <!-- Venues Grid/List -->
    <section class="container mx-auto px-4 py-8">
        <div id="venues-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Venues will be loaded here via JavaScript -->
        </div>
        
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-4 text-gray-600">Loading venues...</p>
        </div>
        
        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 hidden">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No venues found</h3>
            <p class="text-gray-600 mb-4">Try adjusting your filters or search terms</p>
            <button onclick="clearFilters()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
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

// Load venues
async function loadVenues(page = 1, filters = {}) {
    const loadingState = document.getElementById('loading-state');
    const container = document.getElementById('venues-container');
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
        
        const response = await fetch(`/api/v1/venues?${params}`);
        const data = await response.json();
        
        if (data.success && data.data.data.length > 0) {
            displayVenues(data.data.data);
            displayPagination(data.data);
        } else {
            emptyState.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading venues:', error);
        emptyState.classList.remove('hidden');
    } finally {
        loadingState.classList.add('hidden');
    }
}

// Display venues
function displayVenues(venues) {
    const container = document.getElementById('venues-container');
    
    if (viewMode === 'grid') {
        container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6';
        container.innerHTML = venues.map(venue => createVenueCard(venue)).join('');
    } else {
        container.className = 'space-y-4';
        container.innerHTML = venues.map(venue => createVenueListItem(venue)).join('');
    }
}

// Create venue card (grid view)
function createVenueCard(venue) {
    const imageUrl = venue.images && venue.images.length > 0 ? venue.images[0] : '/placeholder.png';
    
    return `
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 group">
            <div class="relative h-48 bg-gradient-to-br from-indigo-100 to-indigo-200">
                <img src="${imageUrl}" alt="${venue.name}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                ${venue.promotion_badge ? `<span class="absolute top-2 right-2 px-2 py-1 bg-indigo-600 text-white text-xs rounded-full">${venue.promotion_badge}</span>` : ''}
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300"></div>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">${venue.name}</h3>
                <p class="text-indigo-600 font-semibold mb-2">${venue.formatted_price_range}</p>
                <p class="text-gray-600 text-sm mb-2">${venue.venue_type_label}</p>
                <p class="text-gray-500 text-sm mb-3">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    ${venue.city}, ${venue.country}
                </p>
                <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                    <span>
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        ${venue.formatted_capacity}
                    </span>
                    <div class="flex space-x-2">
                        ${venue.indoor ? '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Indoor</span>' : ''}
                        ${venue.outdoor ? '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Outdoor</span>' : ''}
                        ${venue.parking_available ? '<span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">Parking</span>' : ''}
                    </div>
                </div>
                <a href="/venues/${venue.slug}" class="block w-full text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                    View Details
                </a>
            </div>
        </div>
    `;
}

// Create venue list item (list view)
function createVenueListItem(venue) {
    const imageUrl = venue.images && venue.images.length > 0 ? venue.images[0] : '/placeholder.png';
    
    return `
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all duration-300">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-32 h-32 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-lg overflow-hidden flex-shrink-0">
                    <img src="${imageUrl}" alt="${venue.name}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-gray-900">${venue.name}</h3>
                        ${venue.promotion_badge ? `<span class="px-2 py-1 bg-indigo-600 text-white text-xs rounded-full">${venue.promotion_badge}</span>` : ''}
                    </div>
                    <p class="text-gray-600 mb-2 line-clamp-2">${venue.description}</p>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                        <span class="text-indigo-600 font-semibold">${venue.formatted_price_range}</span>
                        <span>${venue.venue_type_label}</span>
                        <span>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            ${venue.city}, ${venue.country}
                        </span>
                        <span>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            ${venue.formatted_capacity}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        ${venue.indoor ? '<span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Indoor</span>' : ''}
                        ${venue.outdoor ? '<span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Outdoor</span>' : ''}
                        ${venue.parking_available ? '<span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">Parking</span>' : ''}
                        ${venue.catering_available ? '<span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded">Catering</span>' : ''}
                        ${venue.accessibility ? '<span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded">Accessible</span>' : ''}
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="/venues/${venue.slug}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    `;
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
        paginationHTML += `<button onclick="loadVenues(${paginationData.current_page - 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    for (let i = 1; i <= paginationData.last_page; i++) {
        if (i === paginationData.current_page) {
            paginationHTML += `<button class="px-3 py-2 bg-indigo-600 text-white rounded-lg">${i}</button>`;
        } else {
            paginationHTML += `<button onclick="loadVenues(${i})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">${i}</button>`;
        }
    }
    
    // Next button
    if (paginationData.current_page < paginationData.last_page) {
        paginationHTML += `<button onclick="loadVenues(${paginationData.current_page + 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>`;
    }
    
    container.innerHTML = paginationHTML;
}

// Set view mode
function setViewMode(mode) {
    viewMode = mode;
    const gridBtn = document.getElementById('grid-view-btn');
    const listBtn = document.getElementById('list-view-btn');
    
    if (mode === 'grid') {
        gridBtn.className = 'p-2 text-indigo-600 border border-indigo-300 rounded';
        listBtn.className = 'p-2 text-gray-600 border border-gray-300 rounded';
    } else {
        listBtn.className = 'p-2 text-indigo-600 border border-indigo-300 rounded';
        gridBtn.className = 'p-2 text-gray-600 border border-gray-300 rounded';
    }
    
    loadVenues(currentPage, currentFilters);
}

// Clear filters
function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('type-filter').value = '';
    document.getElementById('location-filter').value = '';
    document.getElementById('capacity-filter').value = '';
    document.getElementById('price-filter').value = '';
    document.getElementById('sort-select').value = 'created_at';
    document.getElementById('indoor-filter').checked = false;
    document.getElementById('outdoor-filter').checked = false;
    document.getElementById('parking-filter').checked = false;
    document.getElementById('catering-filter').checked = false;
    
    currentFilters = {};
    currentPage = 1;
    loadVenues();
}

// Setup filter listeners
function setupFilters() {
    const filters = ['search-input', 'type-filter', 'location-filter', 'capacity-filter', 'price-filter', 'sort-select'];
    const checkboxes = ['indoor-filter', 'outdoor-filter', 'parking-filter', 'catering-filter'];
    
    filters.forEach(filterId => {
        const element = document.getElementById(filterId);
        element.addEventListener('change', () => {
            updateFilters();
        });
    });
    
    checkboxes.forEach(checkboxId => {
        const element = document.getElementById(checkboxId);
        element.addEventListener('change', () => {
            updateFilters();
        });
    });
}

// Update filters
function updateFilters() {
    currentFilters = {
        search: document.getElementById('search-input').value,
        venue_type: document.getElementById('type-filter').value,
        location: document.getElementById('location-filter').value,
        sort: document.getElementById('sort-select').value,
    };
    
    // Capacity filter
    const capacityFilter = document.getElementById('capacity-filter').value;
    if (capacityFilter) {
        if (capacityFilter === '500+') {
            currentFilters.min_capacity = 500;
        } else {
            const [min, max] = capacityFilter.split('-').map(Number);
            currentFilters.min_capacity = min;
            if (max) currentFilters.max_capacity = max;
        }
    }
    
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
    
    // Feature filters
    if (document.getElementById('indoor-filter').checked) currentFilters.indoor = true;
    if (document.getElementById('outdoor-filter').checked) currentFilters.outdoor = true;
    if (document.getElementById('parking-filter').checked) currentFilters.parking_available = true;
    if (document.getElementById('catering-filter').checked) currentFilters.catering_available = true;
    
    // Remove empty filters
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key]) delete currentFilters[key];
    });
    
    currentPage = 1;
    loadVenues(1, currentFilters);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
    loadVenues();
});
</script>
@endsection
