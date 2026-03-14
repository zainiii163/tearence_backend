@extends('layouts.app')

@section('title', 'Events - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <section class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Events</h1>
                    <p class="text-gray-600 mt-1">Discover amazing events happening around you</p>
                </div>
                <a href="{{ route('events.create') }}" class="mt-4 md:mt-0 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                    Post an Event
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
                    <input type="text" id="search-input" placeholder="Search events..." 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                
                <!-- Category Filter -->
                <select id="category-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Categories</option>
                    <option value="concert">Concerts & Music</option>
                    <option value="workshop">Workshops</option>
                    <option value="party">Parties & Nightlife</option>
                    <option value="festival">Festivals</option>
                    <option value="conference">Business Conferences</option>
                    <option value="sports">Sports Events</option>
                    <option value="cultural">Cultural Events</option>
                    <option value="food_drink">Food & Drink</option>
                    <option value="charity">Charity Events</option>
                </select>
                
                <!-- Location Filter -->
                <input type="text" id="location-filter" placeholder="City or Country..." 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                
                <!-- Date Filter -->
                <input type="date" id="date-filter" 
                       class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                
                <!-- Price Type Filter -->
                <select id="price-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Prices</option>
                    <option value="free">Free</option>
                    <option value="paid">Paid</option>
                    <option value="donation">Donation</option>
                </select>
            </div>
            
            <!-- Sort Options -->
            <div class="flex justify-between items-center mt-4">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Sort by:</span>
                    <select id="sort-select" class="px-3 py-1 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="date">Date</option>
                        <option value="title">Title</option>
                        <option value="price">Price</option>
                        <option value="promotion">Promoted</option>
                    </select>
                </div>
                
                <div class="flex items-center space-x-2">
                    <button onclick="setViewMode('grid')" id="grid-view-btn" class="p-2 text-purple-600 border border-purple-300 rounded">
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

    <!-- Events Grid/List -->
    <section class="container mx-auto px-4 py-8">
        <div id="events-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Events will be loaded here via JavaScript -->
        </div>
        
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
            <p class="mt-4 text-gray-600">Loading events...</p>
        </div>
        
        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 hidden">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No events found</h3>
            <p class="text-gray-600 mb-4">Try adjusting your filters or search terms</p>
            <button onclick="clearFilters()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
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

// Load events
async function loadEvents(page = 1, filters = {}) {
    const loadingState = document.getElementById('loading-state');
    const container = document.getElementById('events-container');
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
        
        const response = await fetch(`/api/v1/events?${params}`);
        const data = await response.json();
        
        if (data.success && data.data.data.length > 0) {
            displayEvents(data.data.data);
            displayPagination(data.data);
        } else {
            emptyState.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading events:', error);
        emptyState.classList.remove('hidden');
    } finally {
        loadingState.classList.add('hidden');
    }
}

// Display events
function displayEvents(events) {
    const container = document.getElementById('events-container');
    
    if (viewMode === 'grid') {
        container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6';
        container.innerHTML = events.map(event => createEventCard(event)).join('');
    } else {
        container.className = 'space-y-4';
        container.innerHTML = events.map(event => createEventListItem(event)).join('');
    }
}

// Create event card (grid view)
function createEventCard(event) {
    const imageUrl = event.images && event.images.length > 0 ? event.images[0] : '/placeholder.png';
    
    return `
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 group">
            <div class="relative h-48 bg-gradient-to-br from-purple-100 to-purple-200">
                <img src="${imageUrl}" alt="${event.title}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                ${event.promotion_badge ? `<span class="absolute top-2 right-2 px-2 py-1 bg-purple-600 text-white text-xs rounded-full">${event.promotion_badge}</span>` : ''}
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300"></div>
            </div>
            <div class="p-4">
                <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">${event.title}</h3>
                <p class="text-purple-600 font-semibold mb-2">${event.formatted_price}</p>
                <p class="text-gray-600 text-sm mb-2">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    ${event.city}, ${event.country}
                </p>
                <p class="text-gray-500 text-sm mb-3">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    ${new Date(event.date_time).toLocaleDateString()}
                </p>
                <a href="/events/${event.slug}" class="block w-full text-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                    View Details
                </a>
            </div>
        </div>
    `;
}

// Create event list item (list view)
function createEventListItem(event) {
    const imageUrl = event.images && event.images.length > 0 ? event.images[0] : '/placeholder.png';
    
    return `
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-all duration-300">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-32 h-32 bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg overflow-hidden flex-shrink-0">
                    <img src="${imageUrl}" alt="${event.title}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                </div>
                <div class="flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg text-gray-900">${event.title}</h3>
                        ${event.promotion_badge ? `<span class="px-2 py-1 bg-purple-600 text-white text-xs rounded-full">${event.promotion_badge}</span>` : ''}
                    </div>
                    <p class="text-gray-600 mb-2 line-clamp-2">${event.description}</p>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                        <span class="text-purple-600 font-semibold">${event.formatted_price}</span>
                        <span>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            ${event.city}, ${event.country}
                        </span>
                        <span>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            ${new Date(event.date_time).toLocaleDateString()}
                        </span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="/events/${event.slug}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
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
        paginationHTML += `<button onclick="loadEvents(${paginationData.current_page - 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    for (let i = 1; i <= paginationData.last_page; i++) {
        if (i === paginationData.current_page) {
            paginationHTML += `<button class="px-3 py-2 bg-purple-600 text-white rounded-lg">${i}</button>`;
        } else {
            paginationHTML += `<button onclick="loadEvents(${i})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">${i}</button>`;
        }
    }
    
    // Next button
    if (paginationData.current_page < paginationData.last_page) {
        paginationHTML += `<button onclick="loadEvents(${paginationData.current_page + 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>`;
    }
    
    container.innerHTML = paginationHTML;
}

// Set view mode
function setViewMode(mode) {
    viewMode = mode;
    const gridBtn = document.getElementById('grid-view-btn');
    const listBtn = document.getElementById('list-view-btn');
    
    if (mode === 'grid') {
        gridBtn.className = 'p-2 text-purple-600 border border-purple-300 rounded';
        listBtn.className = 'p-2 text-gray-600 border border-gray-300 rounded';
    } else {
        listBtn.className = 'p-2 text-purple-600 border border-purple-300 rounded';
        gridBtn.className = 'p-2 text-gray-600 border border-gray-300 rounded';
    }
    
    loadEvents(currentPage, currentFilters);
}

// Clear filters
function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('category-filter').value = '';
    document.getElementById('location-filter').value = '';
    document.getElementById('date-filter').value = '';
    document.getElementById('price-filter').value = '';
    document.getElementById('sort-select').value = 'date';
    
    currentFilters = {};
    currentPage = 1;
    loadEvents();
}

// Setup filter listeners
function setupFilters() {
    const filters = ['search-input', 'category-filter', 'location-filter', 'date-filter', 'price-filter', 'sort-select'];
    
    filters.forEach(filterId => {
        const element = document.getElementById(filterId);
        element.addEventListener('change', () => {
            currentFilters = {
                search: document.getElementById('search-input').value,
                category: document.getElementById('category-filter').value,
                location: document.getElementById('location-filter').value,
                date_from: document.getElementById('date-filter').value,
                price_type: document.getElementById('price-filter').value,
                sort: document.getElementById('sort-select').value,
            };
            
            // Remove empty filters
            Object.keys(currentFilters).forEach(key => {
                if (!currentFilters[key]) delete currentFilters[key];
            });
            
            currentPage = 1;
            loadEvents(1, currentFilters);
        });
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
    loadEvents();
});
</script>
@endsection
