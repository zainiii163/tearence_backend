@extends('layouts.app')

@section('title', 'Search Properties - WorldwideAdverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Search Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Property Search</h1>
                    <p class="text-gray-600 mt-1">Find your perfect property from our global marketplace</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span id="resultsCount" class="text-lg font-semibold text-blue-600">Loading results...</span>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-semibold">Filters</h2>
                        <button id="clearFilters" class="text-sm text-blue-600 hover:text-blue-700">Clear All</button>
                    </div>

                    <!-- Search -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Search</label>
                        <input type="text" id="searchInput" placeholder="Keywords, location..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Property Type -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Property Type</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" value="residential" class="property-type-filter mr-2">
                                <span>Residential</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="commercial" class="property-type-filter mr-2">
                                <span>Commercial</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="industrial" class="property-type-filter mr-2">
                                <span>Industrial</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="land" class="property-type-filter mr-2">
                                <span>Land / Plots</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="agricultural" class="property-type-filter mr-2">
                                <span>Agricultural</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="luxury" class="property-type-filter mr-2">
                                <span>Luxury</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="short_term_rental" class="property-type-filter mr-2">
                                <span>Short-Term Rental</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="investment" class="property-type-filter mr-2">
                                <span>Investment</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="new_development" class="property-type-filter mr-2">
                                <span>New Development</span>
                            </label>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Category</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" value="" name="category" class="category-filter mr-2" checked>
                                <span>All Categories</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" value="buy" name="category" class="category-filter mr-2">
                                <span>Buy</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" value="rent" name="category" class="category-filter mr-2">
                                <span>Rent</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" value="lease" name="category" class="category-filter mr-2">
                                <span>Lease</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" value="auction" name="category" class="category-filter mr-2">
                                <span>Auction</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" value="invest" name="category" class="category-filter mr-2">
                                <span>Invest</span>
                            </label>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Price Range</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" id="minPrice" placeholder="Min" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <span>-</span>
                            <input type="number" id="maxPrice" placeholder="Max" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Location</label>
                        <input type="text" id="locationFilter" placeholder="City, country..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <!-- Advanced Filters (for residential) -->
                    <div id="residentialFilters" class="mb-6 hidden">
                        <h3 class="font-medium text-gray-700 mb-3">Residential Filters</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Bedrooms</label>
                                <select id="bedroomsFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Any</option>
                                    <option value="1">1+</option>
                                    <option value="2">2+</option>
                                    <option value="3">3+</option>
                                    <option value="4">4+</option>
                                    <option value="5">5+</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Bathrooms</label>
                                <select id="bathroomsFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="">Any</option>
                                    <option value="1">1+</option>
                                    <option value="2">2+</option>
                                    <option value="3">3+</option>
                                    <option value="4">4+</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="mb-6">
                        <h3 class="font-medium text-gray-700 mb-3">Features</h3>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" value="parking" class="feature-filter mr-2">
                                <span>Parking</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="furnished" class="feature-filter mr-2">
                                <span>Furnished</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="pool" class="feature-filter mr-2">
                                <span>Swimming Pool</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="garden" class="feature-filter mr-2">
                                <span>Garden</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="sea_view" class="feature-filter mr-2">
                                <span>Sea View</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="mountain_view" class="feature-filter mr-2">
                                <span>Mountain View</span>
                            </label>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="mb-6">
                        <label class="block text-gray-700 font-medium mb-2">Sort By</label>
                        <select id="sortBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="price_low">Price (Low to High)</option>
                            <option value="price_high">Price (High to Low)</option>
                            <option value="popular">Most Popular</option>
                        </select>
                    </div>

                    <button id="applyFilters" class="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 transition duration-200">
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Results -->
            <div class="lg:w-3/4">
                <!-- View Toggle -->
                <div class="flex justify-between items-center mb-6">
                    <div class="flex space-x-2">
                        <button id="gridView" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                            <i class="fas fa-th"></i>
                        </button>
                        <button id="listView" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                    <div class="text-sm text-gray-600">
                        <span id="showingResults">Showing 0 results</span>
                    </div>
                </div>

                <!-- Map Toggle -->
                <div class="mb-6">
                    <button id="toggleMap" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                        <i class="fas fa-map mr-2"></i>Show Map
                    </button>
                </div>

                <!-- Map Container (Hidden by default) -->
                <div id="mapContainer" class="mb-6 hidden">
                    <div id="searchMap" class="h-96 bg-gray-200 rounded-lg"></div>
                </div>

                <!-- Results Grid -->
                <div id="resultsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Results will be loaded here -->
                </div>

                <!-- Pagination -->
                <div id="pagination" class="mt-8 flex justify-center">
                    <!-- Pagination will be loaded here -->
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                    <p class="text-gray-600">Loading properties...</p>
                </div>

                <!-- No Results -->
                <div id="noResults" class="text-center py-12 hidden">
                    <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No properties found</h3>
                    <p class="text-gray-600">Try adjusting your filters or search terms</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
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

.list-view .property-card {
    display: flex;
    height: auto;
}

.list-view .property-image {
    width: 300px;
    height: 200px;
    flex-shrink: 0;
}

.list-view .property-content {
    flex: 1;
    padding: 1.5rem;
}

.price-tag {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
}

.leaflet-container {
    border-radius: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// API Base URL
const API_BASE = '/api/v1';
let currentPage = 1;
let searchMap;
let searchMarkers = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    setupEventListeners();
    loadProperties();
});

function initializeSearch() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Set initial filters from URL
    if (urlParams.get('search')) {
        document.getElementById('searchInput').value = urlParams.get('search');
    }
    if (urlParams.get('property_type')) {
        document.querySelector(`.property-type-filter[value="${urlParams.get('property_type')}"]`).checked = true;
    }
    if (urlParams.get('category')) {
        document.querySelector(`.category-filter[value="${urlParams.get('category')}"]`).checked = true;
    }
    if (urlParams.get('price_range')) {
        const range = urlParams.get('price_range').split('-');
        document.getElementById('minPrice').value = range[0] || '';
        document.getElementById('maxPrice').value = range[1] || '';
    }
}

function setupEventListeners() {
    // Search input
    document.getElementById('searchInput').addEventListener('input', debounce(performSearch, 500));
    
    // Filters
    document.querySelectorAll('.property-type-filter, .category-filter, .feature-filter').forEach(filter => {
        filter.addEventListener('change', performSearch);
    });
    
    document.getElementById('minPrice').addEventListener('input', debounce(performSearch, 500));
    document.getElementById('maxPrice').addEventListener('input', debounce(performSearch, 500));
    document.getElementById('locationFilter').addEventListener('input', debounce(performSearch, 500));
    document.getElementById('bedroomsFilter').addEventListener('change', performSearch);
    document.getElementById('bathroomsFilter').addEventListener('change', performSearch);
    document.getElementById('sortBy').addEventListener('change', performSearch);
    
    // Buttons
    document.getElementById('clearFilters').addEventListener('click', clearAllFilters);
    document.getElementById('applyFilters').addEventListener('click', performSearch);
    
    // View toggle
    document.getElementById('gridView').addEventListener('click', () => setViewMode('grid'));
    document.getElementById('listView').addEventListener('click', () => setViewMode('list'));
    
    // Map toggle
    document.getElementById('toggleMap').addEventListener('click', toggleMapView);
    
    // Property type filter changes
    document.querySelectorAll('.property-type-filter').forEach(filter => {
        filter.addEventListener('change', updateAdvancedFilters);
    });
}

function updateAdvancedFilters() {
    const residentialTypes = ['residential'];
    const selectedTypes = Array.from(document.querySelectorAll('.property-type-filter:checked')).map(cb => cb.value);
    
    const residentialFilters = document.getElementById('residentialFilters');
    if (selectedTypes.some(type => residentialTypes.includes(type))) {
        residentialFilters.classList.remove('hidden');
    } else {
        residentialFilters.classList.add('hidden');
    }
}

function performSearch() {
    currentPage = 1;
    loadProperties();
    updateURL();
}

function getSearchParams() {
    const params = new URLSearchParams();
    
    // Search term
    const searchInput = document.getElementById('searchInput').value.trim();
    if (searchInput) params.append('search', searchInput);
    
    // Property types
    const propertyTypes = Array.from(document.querySelectorAll('.property-type-filter:checked')).map(cb => cb.value);
    if (propertyTypes.length > 0) params.append('property_types', propertyTypes.join(','));
    
    // Category
    const category = document.querySelector('.category-filter:checked').value;
    if (category) params.append('category', category);
    
    // Price range
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;
    if (minPrice) params.append('min_price', minPrice);
    if (maxPrice) params.append('max_price', maxPrice);
    
    // Location
    const location = document.getElementById('locationFilter').value.trim();
    if (location) params.append('location', location);
    
    // Residential filters
    const bedrooms = document.getElementById('bedroomsFilter').value;
    const bathrooms = document.getElementById('bathroomsFilter').value;
    if (bedrooms) params.append('bedrooms', bedrooms);
    if (bathrooms) params.append('bathrooms', bathrooms);
    
    // Features
    const features = Array.from(document.querySelectorAll('.feature-filter:checked')).map(cb => cb.value);
    if (features.length > 0) params.append('features', features.join(','));
    
    // Sort
    const sortBy = document.getElementById('sortBy').value;
    params.append('sort', sortBy);
    
    // Pagination
    params.append('page', currentPage);
    params.append('per_page', 12);
    
    return params.toString();
}

async function loadProperties() {
    const loadingState = document.getElementById('loadingState');
    const resultsContainer = document.getElementById('resultsContainer');
    const noResults = document.getElementById('noResults');
    
    loadingState.classList.remove('hidden');
    resultsContainer.innerHTML = '';
    noResults.classList.add('hidden');
    
    try {
        const response = await fetch(`${API_BASE}/properties?${getSearchParams()}`);
        const data = await response.json();
        
        loadingState.classList.add('hidden');
        
        if (data.data.length === 0) {
            noResults.classList.remove('hidden');
            updateResultsCount(0);
        } else {
            displayProperties(data.data);
            updatePagination(data.links);
            updateResultsCount(data.total);
            updateMap(data.data);
        }
    } catch (error) {
        console.error('Error loading properties:', error);
        loadingState.classList.add('hidden');
        noResults.classList.remove('hidden');
    }
}

function displayProperties(properties) {
    const container = document.getElementById('resultsContainer');
    
    properties.forEach(property => {
        const card = createPropertyCard(property);
        container.innerHTML += card;
    });
}

function createPropertyCard(property) {
    const imageUrl = property.cover_image ? `/storage/${property.cover_image}` : 'https://via.placeholder.com/400x300';
    const price = formatPrice(property.price, property.currency);
    
    return `
        <div class="property-card bg-white rounded-lg shadow-md overflow-hidden cursor-pointer" onclick="viewProperty(${property.id})">
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
                <div class="mt-4 flex justify-between items-center text-sm text-gray-500">
                    <span><i class="fas fa-eye mr-1"></i>${property.views || 0}</span>
                    <span><i class="fas fa-heart mr-1"></i>${property.saves || 0}</span>
                    <span><i class="fas fa-clock mr-1"></i>${formatDate(property.created_at)}</span>
                </div>
            </div>
        </div>
    `;
}

function updatePagination(links) {
    const pagination = document.getElementById('pagination');
    if (!links || links.length === 0) {
        pagination.innerHTML = '';
        return;
    }
    
    let html = '<div class="flex space-x-2">';
    
    links.forEach(link => {
        if (link.url) {
            const isActive = link.active;
            const isDisabled = !isActive && (link.label.includes('Previous') && link.url.includes('page=1')) || 
                              (link.label.includes('Next') && link.url.includes('page=' + (links.length - 2)));
            
            html += `
                <button 
                    onclick="goToPage('${link.url}')"
                    class="px-4 py-2 rounded-lg ${isActive ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'} ${isDisabled ? 'opacity-50 cursor-not-allowed' : ''}"
                    ${isDisabled ? 'disabled' : ''}
                >
                    ${link.label.replace('&laquo;', '‹').replace('&raquo;', '›')}
                </button>
            `;
        }
    });
    
    html += '</div>';
    pagination.innerHTML = html;
}

function goToPage(url) {
    const urlParams = new URLSearchParams(url.split('?')[1]);
    currentPage = parseInt(urlParams.get('page'));
    loadProperties();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function updateResultsCount(total) {
    document.getElementById('resultsCount').textContent = `${total} properties found`;
    document.getElementById('showingResults').textContent = `Showing ${total} results`;
}

function updateURL() {
    const params = getSearchParams();
    const newURL = window.location.pathname + (params ? '?' + params : '');
    window.history.pushState({}, '', newURL);
}

function clearAllFilters() {
    // Clear all inputs
    document.getElementById('searchInput').value = '';
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('locationFilter').value = '';
    document.getElementById('bedroomsFilter').value = '';
    document.getElementById('bathroomsFilter').value = '';
    document.getElementById('sortBy').value = 'newest';
    
    // Clear all checkboxes
    document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[type="radio"]').forEach(rb => rb.checked = false);
    
    // Set default category
    document.querySelector('.category-filter[value=""]').checked = true;
    
    performSearch();
}

function setViewMode(mode) {
    const container = document.getElementById('resultsContainer');
    const gridBtn = document.getElementById('gridView');
    const listBtn = document.getElementById('listView');
    
    if (mode === 'list') {
        container.classList.remove('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3');
        container.classList.add('space-y-4');
        listBtn.classList.add('bg-blue-600', 'text-white');
        listBtn.classList.remove('bg-gray-200', 'text-gray-700');
        gridBtn.classList.remove('bg-blue-600', 'text-white');
        gridBtn.classList.add('bg-gray-200', 'text-gray-700');
    } else {
        container.classList.add('grid', 'grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3');
        container.classList.remove('space-y-4');
        gridBtn.classList.add('bg-blue-600', 'text-white');
        gridBtn.classList.remove('bg-gray-200', 'text-gray-700');
        listBtn.classList.remove('bg-blue-600', 'text-white');
        listBtn.classList.add('bg-gray-200', 'text-gray-700');
    }
}

function toggleMapView() {
    const mapContainer = document.getElementById('mapContainer');
    const toggleBtn = document.getElementById('toggleMap');
    
    if (mapContainer.classList.contains('hidden')) {
        mapContainer.classList.remove('hidden');
        toggleBtn.innerHTML = '<i class="fas fa-list mr-2"></i>Hide Map';
        if (!searchMap) {
            initializeSearchMap();
        }
    } else {
        mapContainer.classList.add('hidden');
        toggleBtn.innerHTML = '<i class="fas fa-map mr-2"></i>Show Map';
    }
}

function initializeSearchMap() {
    searchMap = L.map('searchMap').setView([51.505, -0.09], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(searchMap);
}

function updateMap(properties) {
    if (!searchMap) return;
    
    // Clear existing markers
    searchMarkers.forEach(marker => searchMap.removeLayer(marker));
    searchMarkers = [];
    
    // Add markers for properties with coordinates
    properties.forEach(property => {
        if (property.latitude && property.longitude) {
            const marker = L.marker([property.latitude, property.longitude])
                .addTo(searchMap)
                .bindPopup(`
                    <div class="p-2">
                        <h4 class="font-semibold">${property.title}</h4>
                        <p class="text-sm text-gray-600">${property.city}, ${property.country}</p>
                        <p class="text-sm font-semibold text-blue-600">${formatPrice(property.price, property.currency)}</p>
                        <button onclick="viewProperty(${property.id})" class="mt-2 text-xs bg-blue-600 text-white px-2 py-1 rounded">View Details</button>
                    </div>
                `);
            searchMarkers.push(marker);
        }
    });
    
    // Fit map to show all markers
    if (searchMarkers.length > 0) {
        const group = new L.featureGroup(searchMarkers);
        searchMap.fitBounds(group.getBounds().pad(0.1));
    }
}

function viewProperty(id) {
    window.location.href = `/property/${id}`;
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

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) return '1 day ago';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
    return `${Math.floor(diffDays / 365)} years ago`;
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endpush
