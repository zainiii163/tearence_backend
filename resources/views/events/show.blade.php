@extends('layouts.app')

@section('title', 'Event Details - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Event Hero Section -->
    <section class="relative bg-gradient-to-br from-purple-900 via-purple-700 to-indigo-800 text-white">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative container mx-auto px-4 py-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div>
                    <div class="mb-4">
                        <span id="event-category" class="px-3 py-1 bg-purple-600 text-white text-sm rounded-full"></span>
                        <span id="event-badge" class="ml-2 px-3 py-1 bg-yellow-500 text-white text-sm rounded-full hidden"></span>
                    </div>
                    <h1 id="event-title" class="text-4xl font-bold mb-4"></h1>
                    <p id="event-description" class="text-xl text-purple-100 mb-6"></p>
                    <div class="flex flex-wrap gap-4 text-purple-100">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span id="event-date"></span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span id="event-location"></span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span id="event-price" class="font-bold text-lg"></span>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div id="event-image" class="w-full h-96 bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl overflow-hidden">
                        <img id="event-main-image" class="w-full h-full object-cover" onerror="this.style.display='none'">
                    </div>
                    <div id="event-gallery" class="grid grid-cols-4 gap-2 mt-4">
                        <!-- Gallery images will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Event Details -->
    <section class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- About Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Event</h2>
                    <div id="event-full-description" class="prose max-w-none text-gray-700"></div>
                </div>

                <!-- Schedule Section -->
                <div id="schedule-section" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Schedule</h2>
                    <div id="event-schedule" class="text-gray-700"></div>
                </div>

                <!-- Venue Information -->
                <div id="venue-section" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Venue Information</h2>
                    <div id="venue-info" class="space-y-4">
                        <!-- Venue info will be loaded here -->
                    </div>
                </div>

                <!-- Video Section -->
                <div id="video-section" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hidden">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Event Video</h2>
                    <div id="event-video" class="aspect-w-16 aspect-h-9">
                        <!-- Video will be embedded here -->
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button id="save-event-btn" onclick="saveEvent()" 
                                class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                            Save Event
                        </button>
                        <button id="share-event-btn" onclick="shareEvent()" 
                                class="w-full px-4 py-3 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50 transition-colors duration-200">
                            Share Event
                        </button>
                        <button id="report-event-btn" onclick="reportEvent()" 
                                class="w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            Report Event
                        </button>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Contact Information</h3>
                    <div class="space-y-3">
                        <div class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 mr-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span id="event-email"></span>
                        </div>
                        <div id="event-social-links" class="space-y-2">
                            <!-- Social links will be loaded here -->
                        </div>
                    </div>
                    <button id="contact-btn" onclick="contactOrganizer()" 
                            class="w-full mt-4 px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                        Contact Organizer
                    </button>
                </div>

                <!-- Additional Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Additional Information</h3>
                    <div class="space-y-3">
                        <div id="age-restrictions" class="hidden">
                            <span class="text-gray-600">Age Restrictions:</span>
                            <span class="text-gray-900 font-semibold ml-2"></span>
                        </div>
                        <div id="dress-code" class="hidden">
                            <span class="text-gray-600">Dress Code:</span>
                            <span class="text-gray-900 font-semibold ml-2"></span>
                        </div>
                        <div id="expected-attendance" class="hidden">
                            <span class="text-gray-600">Expected Attendance:</span>
                            <span class="text-gray-900 font-semibold ml-2"></span>
                        </div>
                        <div class="pt-3 border-t border-gray-200">
                            <span class="text-gray-600">Posted by:</span>
                            <span id="event-poster" class="text-gray-900 font-semibold ml-2"></span>
                        </div>
                    </div>
                </div>

                <!-- Similar Events -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Similar Events</h3>
                    <div id="similar-events" class="space-y-3">
                        <!-- Similar events will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Modal -->
    <div id="contact-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Contact Event Organizer</h3>
            <form id="contact-form">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                        <input type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Your Email</label>
                        <input type="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea required rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeContactModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
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
let currentEvent = null;
let savedEvents = JSON.parse(localStorage.getItem('savedEvents') || '[]');

// Load event data
async function loadEvent() {
    const slug = window.location.pathname.split('/').pop();
    
    try {
        const response = await fetch(`/api/v1/events/${slug}`);
        const data = await response.json();
        
        if (data.success) {
            currentEvent = data.data;
            displayEventData(currentEvent);
            loadSimilarEvents(currentEvent.category, currentEvent.id);
        } else {
            alert('Event not found');
            window.location.href = '/events';
        }
    } catch (error) {
        console.error('Error loading event:', error);
        alert('Error loading event');
        window.location.href = '/events';
    }
}

// Display event data
function displayEventData(event) {
    document.getElementById('event-title').textContent = event.title;
    document.getElementById('event-description').textContent = event.description;
    document.getElementById('event-full-description').innerHTML = event.description;
    document.getElementById('event-date').textContent = new Date(event.date_time).toLocaleString();
    document.getElementById('event-location').textContent = `${event.city}, ${event.country}`;
    document.getElementById('event-price').textContent = event.formatted_price;
    document.getElementById('event-email').textContent = event.contact_email;
    document.getElementById('event-poster').textContent = event.user?.name || 'Unknown';
    
    // Category
    const categoryElement = document.getElementById('event-category');
    categoryElement.textContent = getCategoryLabel(event.category);
    
    // Promotion badge
    if (event.promotion_badge) {
        const badgeElement = document.getElementById('event-badge');
        badgeElement.textContent = event.promotion_badge;
        badgeElement.classList.remove('hidden');
    }
    
    // Images
    if (event.images && event.images.length > 0) {
        document.getElementById('event-main-image').src = event.images[0];
        
        const gallery = document.getElementById('event-gallery');
        event.images.forEach((image, index) => {
            const img = document.createElement('img');
            img.src = image;
            img.className = 'w-full h-20 object-cover rounded cursor-pointer hover:opacity-80 transition-opacity duration-200';
            img.onclick = () => {
                document.getElementById('event-main-image').src = image;
            };
            gallery.appendChild(img);
        });
    }
    
    // Schedule
    if (event.schedule) {
        document.getElementById('schedule-section').classList.remove('hidden');
        document.getElementById('event-schedule').innerHTML = event.schedule.replace(/\n/g, '<br>');
    }
    
    // Video
    if (event.video_link) {
        document.getElementById('video-section').classList.remove('hidden');
        document.getElementById('event-video').innerHTML = `
            <iframe src="${event.video_link}" class="w-full h-64 rounded-lg" frameborder="0" allowfullscreen></iframe>
        `;
    }
    
    // Additional info
    if (event.age_restrictions) {
        const ageDiv = document.getElementById('age-restrictions');
        ageDiv.classList.remove('hidden');
        ageDiv.querySelector('span:last-child').textContent = event.age_restrictions;
    }
    
    if (event.dress_code) {
        const dressDiv = document.getElementById('dress-code');
        dressDiv.classList.remove('hidden');
        dressDiv.querySelector('span:last-child').textContent = event.dress_code;
    }
    
    if (event.expected_attendance) {
        const attendanceDiv = document.getElementById('expected-attendance');
        attendanceDiv.classList.remove('hidden');
        attendanceDiv.querySelector('span:last-child').textContent = event.expected_attendance + ' attendees';
    }
    
    // Social links
    if (event.social_links && event.social_links.length > 0) {
        const socialLinksDiv = document.getElementById('event-social-links');
        event.social_links.forEach(link => {
            const a = document.createElement('a');
            a.href = link;
            a.target = '_blank';
            a.className = 'flex items-center text-purple-600 hover:text-purple-700';
            a.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
                ${link}
            `;
            socialLinksDiv.appendChild(a);
        });
    }
    
    // Venue info
    if (event.venue) {
        const venueInfo = document.getElementById('venue-info');
        venueInfo.innerHTML = `
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <h4 class="font-semibold text-gray-900">${event.venue.name}</h4>
                    <p class="text-gray-600">${event.venue.venue_type_label}</p>
                    <p class="text-gray-600">${event.venue.city}, ${event.venue.country}</p>
                </div>
                <a href="/venues/${event.venue.slug}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    View Venue
                </a>
            </div>
        `;
    } else if (event.venue_name) {
        const venueInfo = document.getElementById('venue-info');
        venueInfo.innerHTML = `
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-900">${event.venue_name}</h4>
                <p class="text-gray-600">${event.city}, ${event.country}</p>
            </div>
        `;
    }
    
    // Update save button
    updateSaveButton();
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

// Load similar events
async function loadSimilarEvents(category, eventId) {
    try {
        const response = await fetch(`/api/v1/events?category=${category}&per_page=3`);
        const data = await response.json();
        
        if (data.success) {
            const similarEvents = data.data.data.filter(e => e.id !== eventId);
            const container = document.getElementById('similar-events');
            
            container.innerHTML = similarEvents.map(event => `
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-lg overflow-hidden flex-shrink-0">
                        ${event.images && event.images.length > 0 ? 
                            `<img src="${event.images[0]}" class="w-full h-full object-cover">` : 
                            '<div class="w-full h-full flex items-center justify-center"><svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>'
                        }
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-gray-900 line-clamp-1">${event.title}</h4>
                        <p class="text-sm text-gray-600">${event.city}</p>
                        <p class="text-sm text-purple-600 font-semibold">${event.formatted_price}</p>
                    </div>
                    <a href="/events/${event.slug}" class="text-purple-600 hover:text-purple-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading similar events:', error);
    }
}

// Save event
function saveEvent() {
    if (!currentEvent) return;
    
    const index = savedEvents.findIndex(e => e.id === currentEvent.id);
    if (index > -1) {
        savedEvents.splice(index, 1);
    } else {
        savedEvents.push(currentEvent);
    }
    
    localStorage.setItem('savedEvents', JSON.stringify(savedEvents));
    updateSaveButton();
}

// Update save button
function updateSaveButton() {
    if (!currentEvent) return;
    
    const btn = document.getElementById('save-event-btn');
    const isSaved = savedEvents.some(e => e.id === currentEvent.id);
    
    if (isSaved) {
        btn.textContent = 'Remove from Saved';
        btn.className = 'w-full px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200';
    } else {
        btn.textContent = 'Save Event';
        btn.className = 'w-full px-4 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200';
    }
}

// Share event
function shareEvent() {
    if (navigator.share) {
        navigator.share({
            title: currentEvent.title,
            text: currentEvent.description,
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Event link copied to clipboard!');
    }
}

// Report event
function reportEvent() {
    const message = prompt('Please describe why you want to report this event:');
    if (message) {
        // In a real app, this would send the report to the server
        alert('Thank you for your report. We will review it shortly.');
    }
}

// Contact organizer
function contactOrganizer() {
    document.getElementById('contact-modal').classList.remove('hidden');
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
    loadEvent();
});
</script>
@endsection
