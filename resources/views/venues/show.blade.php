@extends('layouts.app')

@section('title', 'Venue Details - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Venue Hero Section -->
    <section class="relative bg-gradient-to-br from-indigo-900 via-indigo-700 to-blue-800 text-white">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div>
                    <div class="mb-4">
                        <span id="venue-type" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-full"></span>
                        <span id="venue-badge" class="ml-2 px-3 py-1 bg-yellow-500 text-white text-sm rounded-full hidden"></span>
                    </div>
                    <h1 id="venue-name" class="text-4xl font-bold mb-4"></h1>
                    <p id="venue-description" class="text-xl text-indigo-100 mb-6"></p>
                    <div class="flex flex-wrap gap-4 text-indigo-100">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span id="venue-location"></span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span id="venue-capacity" class="font-bold text-lg"></span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span id="venue-price" class="font-bold text-lg"></span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div id="venue-image" class="w-full h-96 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-xl overflow-hidden">
                        <img id="venue-main-image" class="w-full h-full object-cover" onerror="this.style.display='none'">
                    </div>
                    <div id="venue-gallery" class="grid grid-cols-4 gap-2 mt-4">
                        <!-- Gallery images will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Venue Details -->
    <section class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- About Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Venue</h2>
                    <div id="venue-full-description" class="prose max-w-none text-gray-700"></div>
                </div>

                <!-- Features & Amenities -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Features & Amenities</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="venue-features">
                        <!-- Features will be loaded here -->
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div id="events-section" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Upcoming Events at This Venue</h2>
                    <div id="venue-events" class="space-y-4">
                        <!-- Events will be loaded here -->
                    </div>
                </div>

                <!-- Video Tour -->
                <div id="video-section" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Venue Video Tour</h2>
                    <div id="venue-video" class="aspect-w-16 aspect-h-9">
                        <!-- Video will be embedded here -->
                    </div>
                </div>

                <!-- Floor Plan -->
                <div id="floor-plan-section" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Floor Plan</h2>
                    <div id="venue-floor-plan" class="text-center">
                        <!-- Floor plan will be displayed here -->
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button id="save-venue-btn" onclick="saveVenue()" 
                                class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                            Save Venue
                        </button>
                        <button id="share-venue-btn" onclick="shareVenue()" 
                                class="w-full px-4 py-3 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors duration-200">
                            Share Venue
                        </button>
                        <button id="contact-btn" onclick="contactVenue()" 
                                class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                            Contact Venue
                        </button>
                        <button id="report-venue-btn" onclick="reportVenue()" 
                                class="w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            Report Venue
                        </button>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span id="venue-email"></span>
                        </div>
                        <div id="venue-social-links" class="space-y-2">
                            <!-- Social links will be loaded here -->
                        </div>
                        <div id="opening-hours" class="pt-3 border-t border-gray-200">
                            <p class="text-gray-600 mb-2">Opening Hours</p>
                            <p id="venue-hours" class="text-gray-900"></p>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Venue Features</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Indoor Space</span>
                            <span id="indoor-badge" class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full hidden">Yes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Outdoor Space</span>
                            <span id="outdoor-badge" class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full hidden">Yes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Parking Available</span>
                            <span id="parking-badge" class="px-2 py-1 bg-purple-100 text-purple-800 text-xs rounded-full hidden">Yes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Catering Available</span>
                            <span id="catering-badge" class="px-2 py-1 bg-orange-100 text-orange-800 text-xs rounded-full hidden">Yes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Wheelchair Accessible</span>
                            <span id="accessibility-badge" class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full hidden">Yes</span>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-gray-200 mt-4">
                        <span class="text-gray-600">Posted by:</span>
                        <span id="venue-poster" class="text-gray-900 font-semibold ml-2"></span>
                    </div>
                </div>

                <!-- Similar Venues -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Similar Venues</h3>
                    <div id="similar-venues" class="space-y-3">
                        <!-- Similar venues will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Modal -->
    <div id="contact-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Contact Venue</h3>
            <form id="contact-form">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                        <input type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Your Email</label>
                        <input type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Event Date (Optional)</label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea required rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeContactModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentVenue = null;
let savedVenues = JSON.parse(localStorage.getItem('savedVenues') || '[]');

// Load venue data
async function loadVenue() {
    const slug = window.location.pathname.split('/').pop();
    
    try {
        const response = await fetch(`/api/v1/venues/${slug}`);
        const data = await response.json();
        
        if (data.success) {
            currentVenue = data.data;
            displayVenueData(currentVenue);
            loadSimilarVenues(currentVenue.venue_type, currentVenue.id);
            loadVenueEvents(currentVenue.id);
        } else {
            alert('Venue not found');
            window.location.href = '/venues';
        }
    } catch (error) {
        console.error('Error loading venue:', error);
        alert('Error loading venue');
        window.location.href = '/venues';
    }
}

// Display venue data
function displayVenueData(venue) {
    document.getElementById('venue-name').textContent = venue.name;
    document.getElementById('venue-description').textContent = venue.description;
    document.getElementById('venue-full-description').innerHTML = venue.description;
    document.getElementById('venue-location').textContent = `${venue.city}, ${venue.country}`;
    document.getElementById('venue-capacity').textContent = venue.formatted_capacity;
    document.getElementById('venue-price').textContent = venue.formatted_price_range;
    document.getElementById('venue-email').textContent = venue.contact_email;
    document.getElementById('venue-poster').textContent = venue.user?.name || 'Unknown';
    
    // Venue type
    const typeElement = document.getElementById('venue-type');
    typeElement.textContent = venue.venue_type_label;
    
    // Promotion badge
    if (venue.promotion_badge) {
        const badgeElement = document.getElementById('venue-badge');
        badgeElement.textContent = venue.promotion_badge;
        badgeElement.classList.remove('hidden');
    }
    
    // Images
    if (venue.images && venue.images.length > 0) {
        document.getElementById('venue-main-image').src = venue.images[0];
        
        const gallery = document.getElementById('venue-gallery');
        venue.images.forEach((image, index) => {
            const img = document.createElement('img');
            img.src = image;
            img.className = 'w-full h-20 object-cover rounded cursor-pointer hover:opacity-80 transition-opacity duration-200';
            img.onclick = () => {
                document.getElementById('venue-main-image').src = image;
            };
            gallery.appendChild(img);
        });
    }
    
    // Features
    if (venue.amenities && venue.amenities.length > 0) {
        const featuresContainer = document.getElementById('venue-features');
        const amenityLabels = {
            'wi_fi': 'Wi-Fi',
            'parking': 'Parking',
            'catering': 'Catering',
            'av_equipment': 'AV Equipment',
            'air_conditioning': 'Air Conditioning',
            'heating': 'Heating',
            'sound_system': 'Sound System',
            'lighting': 'Lighting',
            'stage': 'Stage',
            'dance_floor': 'Dance Floor',
            'bar': 'Bar',
            'kitchen': 'Kitchen',
            'restrooms': 'Restrooms',
            'wheelchair_access': 'Wheelchair Access',
            'elevator': 'Elevator',
            'security': 'Security'
        };
        
        featuresContainer.innerHTML = venue.amenities.map(amenity => `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span class="text-gray-700">${amenityLabels[amenity] || amenity}</span>
            </div>
        `).join('');
    }
    
    // Video
    if (venue.video_link) {
        document.getElementById('video-section').classList.remove('hidden');
        document.getElementById('venue-video').innerHTML = `
            <iframe src="${venue.video_link}" class="w-full h-64 rounded-lg" frameborder="0" allowfullscreen></iframe>
        `;
    }
    
    // Floor plan
    if (venue.floor_plan) {
        document.getElementById('floor-plan-section').classList.remove('hidden');
        document.getElementById('venue-floor-plan').innerHTML = `
            <img src="${venue.floor_plan}" class="max-w-full h-auto rounded-lg mx-auto" alt="Floor Plan">
        `;
    }
    
    // Opening hours
    if (venue.opening_hours) {
        const hoursDiv = document.getElementById('opening-hours');
        const hoursText = Array.isArray(venue.opening_hours) ? 
            venue.opening_hours.join(', ') : 
            venue.opening_hours;
        document.getElementById('venue-hours').textContent = hoursText;
    }
    
    // Feature badges
    if (venue.indoor) {
        document.getElementById('indoor-badge').classList.remove('hidden');
    }
    if (venue.outdoor) {
        document.getElementById('outdoor-badge').classList.remove('hidden');
    }
    if (venue.parking_available) {
        document.getElementById('parking-badge').classList.remove('hidden');
    }
    if (venue.catering_available) {
        document.getElementById('catering-badge').classList.remove('hidden');
    }
    if (venue.accessibility) {
        document.getElementById('accessibility-badge').classList.remove('hidden');
    }
    
    // Social links
    if (venue.social_links && venue.social_links.length > 0) {
        const socialLinksDiv = document.getElementById('venue-social-links');
        venue.social_links.forEach(link => {
            const a = document.createElement('a');
            a.href = link;
            a.target = '_blank';
            a.className = 'flex items-center text-indigo-600 hover:text-indigo-700';
            a.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                ${link}
            `;
            socialLinksDiv.appendChild(a);
        });
    }
    
    // Update save button
    updateSaveButton();
}

// Load similar venues
async function loadSimilarVenues(venueType, venueId) {
    try {
        const response = await fetch(`/api/v1/venues?venue_type=${venueType}&per_page=3`);
        const data = await response.json();
        
        if (data.success) {
            const similarVenues = data.data.data.filter(v => v.id !== venueId);
            const container = document.getElementById('similar-venues');
            
            container.innerHTML = similarVenues.map(venue => `
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-lg overflow-hidden flex-shrink-0">
                        ${venue.images && venue.images.length > 0 ? 
                            `<img src="${venue.images[0]}" class="w-full h-full object-cover">` : 
                            '<div class="w-full h-full flex items-center justify-center"><svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></div>'
                        }
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 line-clamp-1">${venue.name}</h4>
                        <p class="text-sm text-gray-600">${venue.city}</p>
                        <p class="text-sm text-indigo-600 font-semibold">${venue.formatted_price_range}</p>
                    </div>
                    <a href="/venues/${venue.slug}" class="text-indigo-600 hover:text-indigo-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading similar venues:', error);
    }
}

// Load venue events
async function loadVenueEvents(venueId) {
    try {
        const response = await fetch(`/api/v1/events?venue_id=${venueId}&per_page=3`);
        const data = await response.json();
        
        if (data.success && data.data.data.length > 0) {
            const container = document.getElementById('venue-events');
            
            container.innerHTML = data.data.data.map(event => `
                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg overflow-hidden flex-shrink-0">
                        ${event.images && event.images.length > 0 ? 
                            `<img src="${event.images[0]}" class="w-full h-full object-cover">` : 
                            '<div class="w-full h-full flex items-center justify-center"><svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>'
                        }
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900">${event.title}</h4>
                        <p class="text-sm text-gray-600">${new Date(event.date_time).toLocaleDateString()}</p>
                        <p class="text-sm text-purple-600 font-semibold">${event.formatted_price}</p>
                    </div>
                    <a href="/events/${event.slug}" class="text-purple-600 hover:text-purple-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            `).join('');
        } else {
            document.getElementById('events-section').classList.add('hidden');
        }
    } catch (error) {
        console.error('Error loading venue events:', error);
        document.getElementById('events-section').classList.add('hidden');
    }
}

// Save venue
function saveVenue() {
    if (!currentVenue) return;
    
    const index = savedVenues.findIndex(v => v.id === currentVenue.id);
    if (index > -1) {
        savedVenues.splice(index, 1);
    } else {
        savedVenues.push(currentVenue);
    }
    
    localStorage.setItem('savedVenues', JSON.stringify(savedVenues));
    updateSaveButton();
}

// Update save button
function updateSaveButton() {
    if (!currentVenue) return;
    
    const btn = document.getElementById('save-venue-btn');
    const isSaved = savedVenues.some(v => v.id === currentVenue.id);
    
    if (isSaved) {
        btn.textContent = 'Remove from Saved';
        btn.className = 'w-full px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200';
    } else {
        btn.textContent = 'Save Venue';
        btn.className = 'w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200';
    }
}

// Share venue
function shareVenue() {
    if (navigator.share) {
        navigator.share({
            title: currentVenue.name,
            text: currentVenue.description,
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Venue link copied to clipboard!');
    }
}

// Contact venue
function contactVenue() {
    document.getElementById('contact-modal').classList.remove('hidden');
}

// Report venue
function reportVenue() {
    const message = prompt('Please describe why you want to report this venue:');
    if (message) {
        // In a real app, this would send the report to the server
        alert('Thank you for your report. We will review it shortly.');
    }
}

// Close contact modal
function closeContactModal() {
    document.getElementById('contact-modal').classList.add('hidden');
}

// Contact form submission
document.getElementById('contact-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // In a real app, this would send the message to the server
    alert('Message sent successfully!');
    closeContactModal();
    this.reset();
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadVenue();
});
</script>
@endsection
