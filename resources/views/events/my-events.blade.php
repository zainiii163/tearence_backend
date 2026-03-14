@extends('layouts.app')

@section('title', 'My Events - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <section class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Events</h1>
                    <p class="text-gray-600 mt-1">Manage and track your posted events</p>
                </div>
                <a href="{{ route('events.create') }}" class="mt-4 md:mt-0 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                    Create New Event
                </a>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600" id="total-events">0</div>
                    <p class="text-gray-600">Total Events</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600" id="active-events">0</div>
                    <p class="text-gray-600">Active Events</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600" id="upcoming-events">0</div>
                    <p class="text-gray-600">Upcoming Events</p>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-orange-600" id="promoted-events">0</div>
                    <p class="text-gray-600">Promoted Events</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters -->
    <section class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-wrap gap-4">
                <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                
                <select id="date-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Dates</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="past">Past</option>
                    <option value="today">Today</option>
                </select>
                
                <select id="promotion-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Promotion Levels</option>
                    <option value="standard">Standard</option>
                    <option value="promoted">Promoted</option>
                    <option value="featured">Featured</option>
                    <option value="sponsored">Sponsored</option>
                    <option value="spotlight">Spotlight</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Events List -->
    <section class="container mx-auto px-4 py-8">
        <div id="events-container" class="space-y-6">
            <!-- Events will be loaded here via JavaScript -->
        </div>
        
        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12 hidden">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
            <p class="mt-4 text-gray-600">Loading your events...</p>
        </div>
        
        <!-- Empty State -->
        <div id="empty-state" class="text-center py-12 hidden">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No events found</h3>
            <p class="text-gray-600 mb-4">Start by creating your first event</p>
            <a href="{{ route('events.create') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                Create Your First Event
            </a>
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
let eventStats = {};

// Load user's events
async function loadMyEvents(page = 1, filters = {}) {
    const loadingState = document.getElementById('loading-state');
    const container = document.getElementById('events-container');
    const emptyState = document.getElementById('empty-state');
    
    loadingState.classList.remove('hidden');
    container.innerHTML = '';
    emptyState.classList.add('hidden');
    
    try {
        const params = new URLSearchParams({
            page: page,
            per_page: 10,
            ...filters
        });
        
        const response = await fetch('/api/v1/events/my-events', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.data.data.length > 0) {
            displayMyEvents(data.data.data);
            displayPagination(data.data);
            updateStatistics(data.data.data);
        } else {
            emptyState.classList.remove('hidden');
            updateStatistics([]);
        }
    } catch (error) {
        console.error('Error loading events:', error);
        emptyState.classList.remove('hidden');
        updateStatistics([]);
    } finally {
        loadingState.classList.add('hidden');
    }
}

// Display user's events
function displayMyEvents(events) {
    const container = document.getElementById('events-container');
    
    container.innerHTML = events.map(event => createMyEventCard(event)).join('');
}

// Create event card for my events
function createMyEventCard(event) {
    const imageUrl = event.images && event.images.length > 0 ? event.images[0] : '/placeholder.png';
    const eventDate = new Date(event.date_time);
    const isPast = eventDate < new Date();
    const isToday = eventDate.toDateString() === new Date().toDateString();
    
    return `
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300">
            <div class="flex flex-col md:flex-row">
                <div class="w-full md:w-48 h-48 bg-gradient-to-br from-purple-100 to-purple-200">
                    <img src="${imageUrl}" alt="${event.title}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                </div>
                <div class="flex-1 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">${event.title}</h3>
                            <div class="flex flex-wrap gap-2 mb-2">
                                ${event.promotion_badge ? `<span class="px-2 py-1 bg-purple-600 text-white text-xs rounded-full">${event.promotion_badge}</span>` : ''}
                                <span class="px-2 py-1 ${event.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'} text-xs rounded-full">
                                    ${event.is_active ? 'Active' : 'Inactive'}
                                </span>
                                ${isPast ? '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Past</span>' : ''}
                                ${isToday ? '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">Today</span>' : ''}
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="/events/${event.slug}/edit" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button onclick="deleteEvent(${event.id})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-gray-600 mb-4 line-clamp-2">${event.description}</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Date</p>
                            <p class="font-semibold text-gray-900">${eventDate.toLocaleDateString()}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Location</p>
                            <p class="font-semibold text-gray-900">${event.city}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Price</p>
                            <p class="font-semibold text-gray-900">${event.formatted_price}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Category</p>
                            <p class="font-semibold text-gray-900">${getCategoryLabel(event.category)}</p>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200">
                        <a href="/events/${event.slug}" class="text-purple-600 hover:text-purple-700 font-semibold">
                            View Event →
                        </a>
                        <div class="text-sm text-gray-500">
                            Created: ${new Date(event.created_at).toLocaleDateString()}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Update statistics
function updateStatistics(events) {
    const totalEvents = events.length;
    const activeEvents = events.filter(e => e.is_active).length;
    const upcomingEvents = events.filter(e => new Date(e.date_time) >= new Date()).length;
    const promotedEvents = events.filter(e => e.isPromoted()).length;
    
    document.getElementById('total-events').textContent = totalEvents;
    document.getElementById('active-events').textContent = activeEvents;
    document.getElementById('upcoming-events').textContent = upcomingEvents;
    document.getElementById('promoted-events').textContent = promotedEvents;
}

// Get category label
function getCategoryLabel(category) {
    const labels = {
        'concert': 'Concerts & Music',
        'workshop': 'Workshops',
        'party': 'Parties & Nightlife',
        'festival': 'Festivals',
        'conference': 'Business Conferences',
        'sports': 'Sports Events',
        'cultural': 'Cultural Events',
        'food_drink': 'Food & Drink',
        'charity': 'Charity Events',
        'other': 'Other'
    };
    return labels[category] || category;
}

// Delete event
async function deleteEvent(eventId) {
    if (!confirm('Are you sure you want to delete this event?')) return;
    
    try {
        const response = await fetch(`/api/v1/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Event deleted successfully');
            loadMyEvents(currentPage, currentFilters);
        } else {
            alert('Error deleting event: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting event. Please try again.');
    }
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
        paginationHTML += `<button onclick="loadMyEvents(${paginationData.current_page - 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>`;
    }
    
    // Page numbers
    for (let i = 1; i <= paginationData.last_page; i++) {
        if (i === paginationData.current_page) {
            paginationHTML += `<button class="px-3 py-2 bg-purple-600 text-white rounded-lg">${i}</button>`;
        } else {
            paginationHTML += `<button onclick="loadMyEvents(${i})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">${i}</button>`;
        }
    }
    
    // Next button
    if (paginationData.current_page < paginationData.last_page) {
        paginationHTML += `<button onclick="loadMyEvents(${paginationData.current_page + 1})" class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>`;
    }
    
    container.innerHTML = paginationHTML;
}

// Setup filter listeners
function setupFilters() {
    const filters = ['status-filter', 'date-filter', 'promotion-filter'];
    
    filters.forEach(filterId => {
        const element = document.getElementById(filterId);
        element.addEventListener('change', () => {
            currentFilters = {};
            
            const status = document.getElementById('status-filter').value;
            const dateFilter = document.getElementById('date-filter').value;
            const promotion = document.getElementById('promotion-filter').value;
            
            if (status) currentFilters.status = status;
            if (dateFilter) currentFilters.date_filter = dateFilter;
            if (promotion) currentFilters.promotion_tier = promotion;
            
            currentPage = 1;
            loadMyEvents(1, currentFilters);
        });
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
    loadMyEvents();
});
</script>
@endsection
