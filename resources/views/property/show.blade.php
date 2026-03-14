@extends('layouts.app')

@section('title', $property->title . ' - WorldwideAdverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Property Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <nav class="flex items-center space-x-2 text-sm text-gray-600">
                    <a href="/property" class="hover:text-blue-600">Property Hub</a>
                    <span>/</span>
                    <a href="/property/search" class="hover:text-blue-600">Search</a>
                    <span>/</span>
                    <span class="text-gray-900">{{ $property->title }}</span>
                </nav>
                <div class="flex items-center space-x-4">
                    <button id="savePropertyBtn" class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                        <i class="far fa-heart"></i>
                        <span>Save</span>
                    </button>
                    <button class="flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-share-alt"></i>
                        <span>Share</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Property Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Image Gallery -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="relative">
                        <img src="{{ $property->cover_image ? asset('storage/' . $property->cover_image) : 'https://via.placeholder.com/800x500' }}" 
                             alt="{{ $property->title }}" class="w-full h-96 object-cover">
                        @if($property->advert_type !== 'standard')
                            <div class="absolute top-4 right-4">
                                <span class="bg-{{ getAdvertColor($property->advert_type) }} text-white px-4 py-2 rounded-full text-sm font-semibold">
                                    {{ strtoupper($property->advert_type) }}
                                </span>
                            </div>
                        @endif
                        @if($property->video_tour_link)
                            <div class="absolute bottom-4 left-4">
                                <button id="playVideoBtn" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                                    <i class="fas fa-play mr-2"></i>Video Tour
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    @if($property->additional_images && count(json_decode($property->additional_images)) > 0)
                        <div class="p-4">
                            <h4 class="font-semibold mb-3">Additional Images</h4>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach(json_decode($property->additional_images) as $image)
                                    <img src="{{ asset('storage/' . $image) }}" 
                                         alt="{{ $property->title }}" 
                                         class="w-full h-20 object-cover rounded cursor-pointer hover:opacity-80 transition duration-200"
                                         onclick="changeMainImage('{{ asset('storage/' . $image) }}')">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Property Overview -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $property->title }}</h1>
                            @if($property->tagline)
                                <p class="text-lg text-gray-600 mb-4">{{ $property->tagline }}</p>
                            @endif
                            <div class="flex items-center space-x-4 text-gray-600">
                                <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $property->city }}, {{ $property->country }}</span>
                                <span><i class="fas fa-home mr-1"></i>{{ ucfirst($property->property_type) }}</span>
                                <span><i class="fas fa-tag mr-1"></i>{{ ucfirst($property->category) }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-blue-600">{{ formatPrice($property->price, $property->currency) }}</div>
                            @if($property->negotiable)
                                <p class="text-sm text-gray-600 mt-1">Negotiable</p>
                            @endif
                        </div>
                    </div>

                    <!-- Key Specifications -->
                    @if($property->specifications)
                        <div class="border-t pt-4">
                            <h3 class="font-semibold text-lg mb-3">Key Specifications</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach(json_decode($property->specifications) as $key => $value)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">{{ ucfirst($key) }}:</span>
                                        <span class="font-medium">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Amenities -->
                    @if($property->amenities && count(json_decode($property->amenities)) > 0)
                        <div class="border-t pt-4">
                            <h3 class="font-semibold text-lg mb-3">Amenities</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach(json_decode($property->amenities) as $amenity)
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">{{ $amenity }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-4">Description</h2>
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed">{{ $property->description }}</p>
                    </div>
                </div>

                <!-- Location Details -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-4">Location</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold mb-2">Address</h3>
                            <p class="text-gray-700">
                                @if($property->address)
                                    {{ $property->address }},<br>
                                @endif
                                {{ $property->city }},<br>
                                {{ $property->country }}
                            </p>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-2">Location Highlights</h3>
                            @if($property->location_highlights && count(json_decode($property->location_highlights)) > 0)
                                <ul class="space-y-1">
                                    @foreach(json_decode($property->location_highlights) as $highlight)
                                        <li class="text-gray-700">• {{ $highlight }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-600">No location highlights provided</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Map -->
                    @if($property->latitude && $property->longitude)
                        <div class="mt-6">
                            <div id="propertyMap" class="h-64 bg-gray-200 rounded-lg"></div>
                        </div>
                    @endif
                </div>

                <!-- Transport Links -->
                @if($property->transport_links && count(json_decode($property->transport_links)) > 0)
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-2xl font-bold mb-4">Transport Links</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach(json_decode($property->transport_links) as $transport)
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-train text-blue-600"></i>
                                    <span class="text-gray-700">{{ $transport }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column - Contact & Actions -->
            <div class="space-y-6">
                <!-- Contact Agent -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Contact Agent</h2>
                    
                    <!-- Agent Info -->
                    <div class="flex items-center space-x-4 mb-6">
                        @if($property->seller_logo)
                            <img src="{{ asset('storage/' . $property->seller_logo) }}" 
                                 alt="{{ $property->seller_name }}" 
                                 class="w-16 h-16 rounded-full object-cover">
                        @else
                            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-400 text-2xl"></i>
                            </div>
                        @endif
                        <div>
                            <h3 class="font-semibold">{{ $property->seller_name }}</h3>
                            @if($property->seller_company)
                                <p class="text-sm text-gray-600">{{ $property->seller_company }}</p>
                            @endif
                            @if($property->verified_agent)
                                <span class="inline-flex items-center text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full mt-1">
                                    <i class="fas fa-check-circle mr-1"></i>Verified Agent
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <form id="contactForm" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Your Name</label>
                            <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Phone (Optional)</label>
                            <input type="tel" name="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Message</label>
                            <textarea name="message" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="I'm interested in this property..."></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Inquiry Type</label>
                            <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="general">General Inquiry</option>
                                <option value="schedule_viewing">Schedule Viewing</option>
                                <option value="price_inquiry">Price Inquiry</option>
                                <option value="financing">Financing Information</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                            Send Message
                        </button>
                    </form>

                    <!-- Direct Contact -->
                    <div class="mt-6 pt-6 border-t space-y-3">
                        <a href="tel:{{ $property->seller_phone }}" class="flex items-center space-x-3 text-gray-700 hover:text-blue-600">
                            <i class="fas fa-phone"></i>
                            <span>{{ $property->seller_phone }}</span>
                        </a>
                        <a href="mailto:{{ $property->seller_email }}" class="flex items-center space-x-3 text-gray-700 hover:text-blue-600">
                            <i class="fas fa-envelope"></i>
                            <span>{{ $property->seller_email }}</span>
                        </a>
                        @if($property->seller_website)
                            <a href="{{ $property->seller_website }}" target="_blank" class="flex items-center space-x-3 text-gray-700 hover:text-blue-600">
                                <i class="fas fa-globe"></i>
                                <span>Website</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Property Stats -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Property Stats</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Views</span>
                            <span class="font-semibold">{{ $property->views ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Saved</span>
                            <span class="font-semibold">{{ $property->saves ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Enquiries</span>
                            <span class="font-semibold">{{ $property->enquiries ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Listed</span>
                            <span class="font-semibold">{{ \Carbon\Carbon::parse($property->created_at)->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Similar Properties -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Similar Properties</h2>
                    <div id="similarProperties" class="space-y-4">
                        <!-- Similar properties will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div id="videoModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Property Video Tour</h3>
            <button id="closeVideoBtn" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="p-4">
            <div id="videoContainer"></div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.prose {
    color: #374151;
    line-height: 1.75;
}

.prose p {
    margin-bottom: 1.25rem;
}

.feature-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #eff6ff;
    color: #3b82f6;
    margin-bottom: 1rem;
}

.contact-form input,
.contact-form textarea,
.contact-form select {
    transition: all 0.3s ease;
}

.contact-form input:focus,
.contact-form textarea:focus,
.contact-form select:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.property-image-thumb {
    transition: all 0.3s ease;
    cursor: pointer;
}

.property-image-thumb:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.saved {
    background-color: #ef4444 !important;
    color: white !important;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Property data
const property = @json($property);
const API_BASE = '/api/v1';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    setupEventListeners();
    loadSimilarProperties();
    trackView();
});

function setupEventListeners() {
    // Contact form
    document.getElementById('contactForm').addEventListener('submit', submitContactForm);
    
    // Save property
    document.getElementById('savePropertyBtn').addEventListener('click', toggleSaveProperty);
    
    // Video modal
    document.getElementById('playVideoBtn').addEventListener('click', showVideoModal);
    document.getElementById('closeVideoBtn').addEventListener('click', hideVideoModal);
    
    // Click outside modal to close
    document.getElementById('videoModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideVideoModal();
        }
    });
}

function initializeMap() {
    if (property.latitude && property.longitude) {
        const map = L.map('propertyMap').setView([property.latitude, property.longitude], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        const marker = L.marker([property.latitude, property.longitude])
            .addTo(map)
            .bindPopup(`
                <div class="p-2">
                    <h4 class="font-semibold">${property.title}</h4>
                    <p class="text-sm text-gray-600">${property.city}, ${property.country}</p>
                </div>
            `);
    }
}

function changeMainImage(imageUrl) {
    document.querySelector('.w-full.h-96.object-cover').src = imageUrl;
}

function showVideoModal() {
    const modal = document.getElementById('videoModal');
    const container = document.getElementById('videoContainer');
    
    // Extract video ID from YouTube/Vimeo URL
    const videoUrl = property.video_tour_link;
    let embedUrl = '';
    
    if (videoUrl.includes('youtube.com') || videoUrl.includes('youtu.be')) {
        const videoId = videoUrl.includes('youtu.be') 
            ? videoUrl.split('youtu.be/')[1].split('?')[0]
            : videoUrl.split('v=')[1].split('&')[0];
        embedUrl = `https://www.youtube.com/embed/${videoId}`;
    } else if (videoUrl.includes('vimeo.com')) {
        const videoId = videoUrl.split('vimeo.com/')[1].split('?')[0];
        embedUrl = `https://player.vimeo.com/video/${videoId}`;
    }
    
    if (embedUrl) {
        container.innerHTML = `
            <div class="aspect-w-16 aspect-h-9">
                <iframe src="${embedUrl}" 
                        class="w-full h-96 rounded-lg" 
                        frameborder="0" 
                        allowfullscreen>
                </iframe>
            </div>
        `;
        modal.classList.remove('hidden');
    }
}

function hideVideoModal() {
    document.getElementById('videoModal').classList.add('hidden');
    document.getElementById('videoContainer').innerHTML = '';
}

async function submitContactForm(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch(`${API_BASE}/properties/${property.id}/contact-agent`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: formData.get('message'),
                phone: formData.get('phone'),
                type: formData.get('type')
            })
        });
        
        if (response.ok) {
            alert('Message sent successfully! The agent will contact you soon.');
            e.target.reset();
        } else {
            const error = await response.json();
            alert('Error sending message: ' + error.message);
        }
    } catch (error) {
        alert('Error sending message: ' + error.message);
    }
}

async function toggleSaveProperty() {
    const btn = document.getElementById('savePropertyBtn');
    
    try {
        const response = await fetch(`${API_BASE}/properties/${property.id}/save`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            
            if (result.message.includes('removed')) {
                btn.classList.remove('saved');
                btn.innerHTML = '<i class="far fa-heart"></i><span>Save</span>';
            } else {
                btn.classList.add('saved');
                btn.innerHTML = '<i class="fas fa-heart"></i><span>Saved</span>';
            }
        } else {
            const error = await response.json();
            alert('Error saving property: ' + error.message);
        }
    } catch (error) {
        alert('Error saving property: ' + error.message);
    }
}

async function loadSimilarProperties() {
    try {
        const response = await fetch(`${API_BASE}/properties?property_type=${property.property_type}&category=${property.category}&per_page=3&exclude=${property.id}`);
        const data = await response.json();
        
        const container = document.getElementById('similarProperties');
        container.innerHTML = '';
        
        data.data.forEach(similarProperty => {
            const card = createSimilarPropertyCard(similarProperty);
            container.innerHTML += card;
        });
    } catch (error) {
        console.error('Error loading similar properties:', error);
    }
}

function createSimilarPropertyCard(similarProperty) {
    const imageUrl = similarProperty.cover_image ? `/storage/${similarProperty.cover_image}` : 'https://via.placeholder.com/300x200';
    const price = formatPrice(similarProperty.price, similarProperty.currency);
    
    return `
        <div class="flex space-x-4 p-4 border rounded-lg hover:shadow-md transition duration-200 cursor-pointer" onclick="viewProperty(${similarProperty.id})">
            <img src="${imageUrl}" alt="${similarProperty.title}" class="w-24 h-24 object-cover rounded-lg">
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 mb-1">${similarProperty.title}</h4>
                <div class="text-blue-600 font-semibold mb-2">${price}</div>
                <div class="text-sm text-gray-600">
                    <i class="fas fa-map-marker-alt mr-1"></i>${similarProperty.city}, ${similarProperty.country}
                </div>
            </div>
        </div>
    `;
}

function viewProperty(id) {
    window.location.href = `/property/${id}`;
}

function trackView() {
    // Track the view event (already tracked by the backend when loading the property)
    console.log('Property view tracked');
}

// Utility functions
function formatPrice(price, currency) {
    const symbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'AED': 'د.إ',
        'SAR': '﷼'
    };
    
    const symbol = symbols[currency] || currency;
    
    if (price >= 1000000) {
        return symbol + (price / 1000000).toFixed(2) + 'M';
    } else if (price >= 1000) {
        return symbol + (price / 1000).toFixed(1) + 'K';
    }
    
    return symbol + price.toLocaleString();
}

function getAdvertColor(type) {
    const colors = {
        'promoted': 'yellow-500',
        'featured': 'blue-500',
        'sponsored': 'green-500'
    };
    return colors[type] || 'gray-500';
}
</script>

@php
function formatPrice($price, $currency) {
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'AED' => 'د.إ',
        'SAR' => '﷼'
    ];
    
    $symbol = $symbols[$currency] ?? $currency;
    
    if ($price >= 1000000) {
        return $symbol . number_format($price / 1000000, 2) . 'M';
    } else if ($price >= 1000) {
        return $symbol . number_format($price / 1000, 1) . 'K';
    }
    
    return $symbol . number_format($price);
}

function getAdvertColor($type) {
    $colors = [
        'promoted' => 'yellow-500',
        'featured' => 'blue-500',
        'sponsored' => 'green-500'
    ];
    return $colors[$type] ?? 'gray-500';
}
@endphp
@endpush
