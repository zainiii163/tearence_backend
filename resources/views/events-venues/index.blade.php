@extends('layouts.app')

@section('title', 'Events & Venues - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-purple-900 via-purple-700 to-indigo-800 text-white">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative container mx-auto px-4 py-20">
            <div class="text-center">
                <h1 class="text-5xl font-bold mb-6">Discover Events & Venues Worldwide</h1>
                <p class="text-xl mb-8 text-purple-100">Find the perfect venue, explore upcoming events, or promote your own</p>
                
                <!-- Dual Search Bar -->
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg shadow-xl p-2">
                        <div class="flex flex-col md:flex-row gap-2">
                            <button onclick="showTab('events')" id="events-tab" class="flex-1 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 bg-purple-600 text-white">
                                Search Events
                            </button>
                            <button onclick="showTab('venues')" id="venues-tab" class="flex-1 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 text-gray-700 hover:bg-gray-100">
                                Search Venues
                            </button>
                        </div>
                        
                        <!-- Events Search Form -->
                        <div id="events-search" class="mt-4 p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input type="text" placeholder="Search concerts, workshops, parties..." class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <select class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
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
                                <button class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                                    Search Events
                                </button>
                            </div>
                        </div>
                        
                        <!-- Venues Search Form -->
                        <div id="venues-search" class="mt-4 p-4 hidden">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <input type="text" placeholder="Search wedding halls, conference centres..." class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <select class="px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <option value="">All Venue Types</option>
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
                                <button class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                                    Search Venues
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Action Buttons -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Post Your Event or Venue</h2>
                <p class="text-gray-600">Reach thousands of potential attendees and venue seekers</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <a href="{{ route('events.create') }}" class="group bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200 hover:shadow-lg transition-all duration-300 text-center">
                    <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-purple-900 mb-2">Post an Event</h3>
                    <p class="text-purple-700">Concerts, workshops, parties, conferences, festivals</p>
                </a>
                
                <a href="{{ route('venues.create') }}" class="group bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 rounded-xl border border-indigo-200 hover:shadow-lg transition-all duration-300 text-center">
                    <div class="w-16 h-16 bg-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-indigo-900 mb-2">Post a Venue</h3>
                    <p class="text-indigo-700">Wedding halls, conference centres, party venues</p>
                </a>
                
                <a href="{{ route('venue-services.create') }}" class="group bg-gradient-to-br from-teal-50 to-teal-100 p-6 rounded-xl border border-teal-200 hover:shadow-lg transition-all duration-300 text-center">
                    <div class="w-16 h-16 bg-teal-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A9.001 9.001 0 0011.745 3 9.001 9.001 0 003 13.255V21h18v-7.745z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-teal-900 mb-2">Post Services</h3>
                    <p class="text-teal-700">Catering, decor, DJ, photography, event planning</p>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Events -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Featured Events</h2>
                <a href="{{ route('events.index') }}" class="text-purple-600 hover:text-purple-700 font-semibold">View All Events →</a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6" id="featured-events">
                <!-- Events will be loaded here via JavaScript -->
            </div>
        </div>
    </section>

    <!-- Featured Venues -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Featured Venues</h2>
                <a href="{{ route('venues.index') }}" class="text-purple-600 hover:text-purple-700 font-semibold">View All Venues →</a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6" id="featured-venues">
                <!-- Venues will be loaded here via JavaScript -->
            </div>
        </div>
    </section>

    <!-- Live Activity Feed -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">What's Happening</h2>
                <p class="text-gray-600">Real-time activity from our global community</p>
            </div>
            
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="space-y-4" id="activity-feed">
                    <!-- Activity items will be loaded here via JavaScript -->
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
// Tab switching functionality
function showTab(tab) {
    const eventsTab = document.getElementById('events-tab');
    const venuesTab = document.getElementById('venues-tab');
    const eventsSearch = document.getElementById('events-search');
    const venuesSearch = document.getElementById('venues-search');
    
    if (tab === 'events') {
        eventsTab.className = 'flex-1 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 bg-purple-600 text-white';
        venuesTab.className = 'flex-1 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 text-gray-700 hover:bg-gray-100';
        eventsSearch.classList.remove('hidden');
        venuesSearch.classList.add('hidden');
    } else {
        venuesTab.className = 'flex-1 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 bg-purple-600 text-white';
        eventsTab.className = 'flex-1 px-6 py-3 rounded-lg font-semibold transition-colors duration-200 text-gray-700 hover:bg-gray-100';
        venuesSearch.classList.remove('hidden');
        eventsSearch.classList.add('hidden');
    }
}

// Load featured events
async function loadFeaturedEvents() {
    try {
        const response = await fetch('/api/v1/events/featured');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('featured-events');
            container.innerHTML = data.data.map(event => `
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="h-48 bg-gradient-to-br from-purple-100 to-purple-200 relative">
                        ${event.promotion_badge ? `<span class="absolute top-2 right-2 px-2 py-1 bg-purple-600 text-white text-xs rounded-full">${event.promotion_badge}</span>` : ''}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">${event.title}</h3>
                        <p class="text-purple-600 font-semibold mb-2">${event.formatted_price}</p>
                        <p class="text-gray-600 text-sm mb-2">${event.city}, ${event.country}</p>
                        <p class="text-gray-500 text-sm">${new Date(event.date_time).toLocaleDateString()}</p>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading featured events:', error);
    }
}

// Load featured venues
async function loadFeaturedVenues() {
    try {
        const response = await fetch('/api/v1/venues/featured');
        const data = await response.json();
        
        if (data.success) {
            const container = document.getElementById('featured-venues');
            container.innerHTML = data.data.map(venue => `
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="h-48 bg-gradient-to-br from-indigo-100 to-indigo-200 relative">
                        ${venue.promotion_badge ? `<span class="absolute top-2 right-2 px-2 py-1 bg-indigo-600 text-white text-xs rounded-full">${venue.promotion_badge}</span>` : ''}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-16 h-16 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">${venue.name}</h3>
                        <p class="text-indigo-600 font-semibold mb-2">${venue.formatted_price_range}</p>
                        <p class="text-gray-600 text-sm mb-2">${venue.venue_type_label}</p>
                        <p class="text-gray-500 text-sm">${venue.city}, ${venue.country}</p>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading featured venues:', error);
    }
}

// Load activity feed
function loadActivityFeed() {
    const activities = [
        { icon: '👁️', text: 'Someone from Paris viewed a concert in London', time: '2 minutes ago' },
        { icon: '🎉', text: 'New workshop added in Manchester', time: '5 minutes ago' },
        { icon: '🏢', text: 'Conference centre registered in Dubai', time: '8 minutes ago' },
        { icon: '🎵', text: 'Music festival announced in Barcelona', time: '12 minutes ago' },
        { icon: '💍', text: 'Wedding hall featured in New York', time: '15 minutes ago' }
    ];
    
    const container = document.getElementById('activity-feed');
    container.innerHTML = activities.map(activity => `
        <div class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg transition-colors duration-200">
            <span class="text-2xl">${activity.icon}</span>
            <div class="flex-1">
                <p class="text-gray-800">${activity.text}</p>
                <p class="text-gray-500 text-sm">${activity.time}</p>
            </div>
        </div>
    `).join('');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadFeaturedEvents();
    loadFeaturedVenues();
    loadActivityFeed();
    
    // Refresh activity feed every 30 seconds
    setInterval(loadActivityFeed, 30000);
});
</script>
@endsection
