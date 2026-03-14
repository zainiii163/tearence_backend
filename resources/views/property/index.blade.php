@extends('layouts.app')

@section('title', 'Property Hub - WorldwideAdverts')

@section('content')
<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white py-20">
    <div class="absolute inset-0 bg-black opacity-20"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 animate-fade-in">
                Discover Property Worldwide
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                Buy, Rent, Invest — Real estate, land, commercial, industrial, and more
            </p>
            
            <!-- Search Bar -->
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-xl p-6">
                <form id="propertySearchForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Location</label>
                        <input type="text" id="searchLocation" placeholder="City, Country" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Property Type</label>
                        <select id="propertyType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Types</option>
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="industrial">Industrial</option>
                            <option value="land">Land / Plots</option>
                            <option value="agricultural">Agricultural</option>
                            <option value="luxury">Luxury</option>
                            <option value="short_term_rental">Short-Term Rental</option>
                            <option value="investment">Investment</option>
                            <option value="new_development">New Development</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Category</label>
                        <select id="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Categories</option>
                            <option value="buy">Buy</option>
                            <option value="rent">Rent</option>
                            <option value="lease">Lease</option>
                            <option value="auction">Auction</option>
                            <option value="invest">Invest</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-2">Price Range</label>
                        <select id="priceRange" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Any Price</option>
                            <option value="0-100000">Under $100K</option>
                            <option value="100000-500000">$100K - $500K</option>
                            <option value="500000-1000000">$500K - $1M</option>
                            <option value="1000000-5000000">$1M - $5M</option>
                            <option value="5000000+">$5M+</option>
                        </select>
                    </div>
                </form>
                <div class="mt-4 flex justify-center">
                    <button type="submit" form="propertySearchForm" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                        Search Properties
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Interactive World Map Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Explore Properties by Region</h2>
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div id="worldMap" class="h-96 bg-blue-50 rounded-lg flex items-center justify-center">
                <div class="text-center">
                    <i class="fas fa-globe-americas text-6xl text-blue-300 mb-4"></i>
                    <p class="text-gray-600">Interactive world map will be displayed here</p>
                    <p class="text-sm text-gray-500 mt-2">Click on regions to explore properties</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Categories Grid -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Browse by Property Type</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300 p-6 cursor-pointer property-category" data-type="residential">
                <div class="text-blue-500 mb-4">
                    <i class="fas fa-home text-3xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Residential</h3>
                <p class="text-gray-600 text-sm">Homes, apartments, condos</p>
            </div>
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300 p-6 cursor-pointer property-category" data-type="commercial">
                <div class="text-green-500 mb-4">
                    <i class="fas fa-building text-3xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Commercial</h3>
                <p class="text-gray-600 text-sm">Office, retail spaces</p>
            </div>
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300 p-6 cursor-pointer property-category" data-type="industrial">
                <div class="text-yellow-500 mb-4">
                    <i class="fas fa-industry text-3xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Industrial</h3>
                <p class="text-gray-600 text-sm">Warehouses, factories</p>
            </div>
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300 p-6 cursor-pointer property-category" data-type="land">
                <div class="text-purple-500 mb-4">
                    <i class="fas fa-mountain text-3xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Land & Plots</h3>
                <p class="text-gray-600 text-sm">Land for development</p>
            </div>
            <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300 p-6 cursor-pointer property-category" data-type="luxury">
                <div class="text-gold-500 mb-4">
                    <i class="fas fa-gem text-3xl"></i>
                </div>
                <h3 class="font-semibold text-lg mb-2">Luxury</h3>
                <p class="text-gray-600 text-sm">Premium properties</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <h2 class="text-3xl font-bold">Featured Properties</h2>
            <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">View All →</a>
        </div>
        <div id="featuredProperties" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Properties will be loaded here -->
        </div>
    </div>
</section>

<!-- Recent Properties -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-12">
            <h2 class="text-3xl font-bold">Recent Properties</h2>
            <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold">View All →</a>
        </div>
        <div id="recentProperties" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Properties will be loaded here -->
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-16 bg-blue-600 text-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold mb-2" id="totalProperties">0</div>
                <div class="text-blue-100">Total Properties</div>
            </div>
            <div>
                <div class="text-4xl font-bold mb-2" id="totalCountries">0</div>
                <div class="text-blue-100">Countries</div>
            </div>
            <div>
                <div class="text-4xl font-bold mb-2" id="totalCities">0</div>
                <div class="text-blue-100">Cities</div>
            </div>
            <div>
                <div class="text-4xl font-bold mb-2" id="activeUsers">0</div>
                <div class="text-blue-100">Active Users</div>
            </div>
        </div>
    </div>
</section>

<!-- Floating Post Property Button -->
<button id="postPropertyBtn" class="fixed bottom-8 right-8 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-4 rounded-full shadow-2xl hover:shadow-3xl transition duration-300 z-50 flex items-center space-x-2">
    <i class="fas fa-plus-circle"></i>
    <span class="font-semibold">Post Property</span>
</button>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.animate-fade-in {
    animation: fadeIn 1s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.property-category:hover {
    transform: translateY(-5px);
}

.property-card {
    transition: all 0.3s ease;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.property-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 10;
}

.price-tag {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
}

.text-gold-500 {
    color: #f59e0b;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// API Base URL
const API_BASE = '/api/v1';

// Load featured properties
async function loadFeaturedProperties() {
    try {
        const response = await fetch(`${API_BASE}/properties/featured`);
        const data = await response.json();
        
        const container = document.getElementById('featuredProperties');
        container.innerHTML = '';
        
        data.data.forEach(property => {
            const card = createPropertyCard(property, 'featured');
            container.innerHTML += card;
        });
    } catch (error) {
        console.error('Error loading featured properties:', error);
    }
}

// Load recent properties
async function loadRecentProperties() {
    try {
        const response = await fetch(`${API_BASE}/properties?per_page=8`);
        const data = await response.json();
        
        const container = document.getElementById('recentProperties');
        container.innerHTML = '';
        
        data.data.forEach(property => {
            const card = createPropertyCard(property, 'compact');
            container.innerHTML += card;
        });
    } catch (error) {
        console.error('Error loading recent properties:', error);
    }
}

// Create property card HTML
function createPropertyCard(property, type = 'default') {
    const imageUrl = property.cover_image ? `/storage/${property.cover_image}` : 'https://via.placeholder.com/400x300';
    const price = formatPrice(property.price, property.currency);
    
    if (type === 'compact') {
        return `
            <div class="property-card bg-white rounded-lg shadow-md overflow-hidden cursor-pointer" onclick="viewProperty(${property.id})">
                <div class="relative">
                    <img src="${imageUrl}" alt="${property.title}" class="w-full h-48 object-cover">
                    ${property.advert_type !== 'standard' ? `<div class="property-badge bg-${getAdvertColor(property.advert_type)} text-white px-2 py-1 rounded text-xs font-semibold">${property.advert_type.toUpperCase()}</div>` : ''}
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2 truncate">${property.title}</h3>
                    <div class="price-tag text-blue-600 mb-2">${price}</div>
                    <div class="text-gray-600 text-sm">
                        <i class="fas fa-map-marker-alt mr-1"></i>${property.city}, ${property.country}
                    </div>
                </div>
            </div>
        `;
    }
    
    return `
        <div class="property-card bg-white rounded-lg shadow-lg overflow-hidden cursor-pointer" onclick="viewProperty(${property.id})">
            <div class="relative">
                <img src="${imageUrl}" alt="${property.title}" class="w-full h-56 object-cover">
                ${property.advert_type !== 'standard' ? `<div class="property-badge bg-${getAdvertColor(property.advert_type)} text-white px-3 py-1 rounded text-sm font-semibold">${property.advert_type.toUpperCase()}</div>` : ''}
            </div>
            <div class="p-6">
                <h3 class="font-bold text-xl mb-3">${property.title}</h3>
                <div class="price-tag text-blue-600 mb-3">${price}</div>
                <p class="text-gray-600 mb-4 line-clamp-3">${property.description}</p>
                <div class="flex justify-between items-center text-gray-600 text-sm">
                    <span><i class="fas fa-home mr-1"></i>${property.property_type}</span>
                    <span><i class="fas fa-tag mr-1"></i>${property.category}</span>
                </div>
                <div class="mt-3 text-gray-600 text-sm">
                    <i class="fas fa-map-marker-alt mr-1"></i>${property.city}, ${property.country}
                </div>
            </div>
        </div>
    `;
}

// Format price
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

// Get advert type color
function getAdvertColor(type) {
    const colors = {
        'promoted': 'yellow-500',
        'featured': 'blue-500',
        'sponsored': 'green-500'
    };
    return colors[type] || 'gray-500';
}

// View property details
function viewProperty(id) {
    window.location.href = `/property/${id}`;
}

// Search properties
document.getElementById('propertySearchForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const params = new URLSearchParams();
    const location = document.getElementById('searchLocation').value;
    const propertyType = document.getElementById('propertyType').value;
    const category = document.getElementById('category').value;
    const priceRange = document.getElementById('priceRange').value;
    
    if (location) params.append('search', location);
    if (propertyType) params.append('property_type', propertyType);
    if (category) params.append('category', category);
    if (priceRange) params.append('price_range', priceRange);
    
    window.location.href = `/property/search?${params.toString()}`;
});

// Property category clicks
document.querySelectorAll('.property-category').forEach(card => {
    card.addEventListener('click', function() {
        const type = this.dataset.type;
        window.location.href = `/property/search?property_type=${type}`;
    });
});

// Post Property button
document.getElementById('postPropertyBtn').addEventListener('click', function() {
    window.location.href = '/property/post';
});

// Load stats
async function loadStats() {
    try {
        // These would be actual API calls in production
        document.getElementById('totalProperties').textContent = '12,543';
        document.getElementById('totalCountries').textContent = '156';
        document.getElementById('totalCities').textContent = '2,847';
        document.getElementById('activeUsers').textContent = '48,921';
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadFeaturedProperties();
    loadRecentProperties();
    loadStats();
});
</script>
@endpush
