<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicles - WWA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .category-card {
            transition: all 0.3s ease;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .vehicle-card {
            transition: all 0.3s ease;
        }
        .vehicle-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .badge-promoted {
            background: linear-gradient(45deg, #10b981, #34d399);
        }
        .badge-featured {
            background: linear-gradient(45deg, #3b82f6, #60a5fa);
        }
        .badge-sponsored {
            background: linear-gradient(45deg, #ef4444, #f87171);
        }
        .badge-top-category {
            background: linear-gradient(45deg, #8b5cf6, #a78bfa);
        }
        .filter-section {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        .map-container {
            height: 400px;
            background: #f3f4f6;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-indigo-600">WWA</a>
                    <span class="ml-2 text-gray-600">Vehicles</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/vehicles/create" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i> Post Vehicle
                    </a>
                    <a href="/dashboard" class="text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="gradient-bg text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Find Your Next Vehicle</h1>
                <p class="text-xl mb-8">Discover Vehicles for Sale, Hire & Lease — Worldwide</p>
                
                <!-- Search Bar -->
                <div class="bg-white rounded-lg shadow-lg p-6 max-w-4xl mx-auto">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <select id="searchAdvertType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Types</option>
                                <option value="sale">For Sale</option>
                                <option value="hire">For Hire</option>
                                <option value="lease">For Lease</option>
                                <option value="transport_service">Transport Service</option>
                            </select>
                        </div>
                        <div>
                            <select id="searchCategory" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div>
                            <input type="text" id="searchLocation" placeholder="Location..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <input type="number" id="searchMinPrice" placeholder="Min Price" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <input type="number" id="searchMaxPrice" placeholder="Max Price" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-center">
                        <button id="searchBtn" class="bg-indigo-600 text-white px-8 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-search mr-2"></i> Search Vehicles
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Section -->
    <div class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Vehicle Categories</h2>
            <div id="categoriesGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <!-- Categories will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Filters Sidebar -->
                <div class="lg:w-1/4">
                    <div class="bg-white rounded-lg shadow-sm p-6 filter-section">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                        
                        <!-- Advert Type -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Advert Type</h4>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="advert_type" value="sale">
                                    <span class="text-sm">For Sale</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="advert_type" value="hire">
                                    <span class="text-sm">For Hire</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="advert_type" value="lease">
                                    <span class="text-sm">For Lease</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="advert_type" value="transport_service">
                                    <span class="text-sm">Transport Service</span>
                                </label>
                            </div>
                        </div>

                        <!-- Make -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Make</h4>
                            <select id="filterMake" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Makes</option>
                            </select>
                        </div>

                        <!-- Fuel Type -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Fuel Type</h4>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="fuel_type" value="petrol">
                                    <span class="text-sm">Petrol</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="fuel_type" value="diesel">
                                    <span class="text-sm">Diesel</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="fuel_type" value="electric">
                                    <span class="text-sm">Electric</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="fuel_type" value="hybrid">
                                    <span class="text-sm">Hybrid</span>
                                </label>
                            </div>
                        </div>

                        <!-- Transmission -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Transmission</h4>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="transmission" value="manual">
                                    <span class="text-sm">Manual</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="transmission" value="automatic">
                                    <span class="text-sm">Automatic</span>
                                </label>
                            </div>
                        </div>

                        <!-- Condition -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Condition</h4>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="condition" value="new">
                                    <span class="text-sm">New</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="condition" value="used">
                                    <span class="text-sm">Used</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="condition" value="excellent">
                                    <span class="text-sm">Excellent</span>
                                </label>
                            </div>
                        </div>

                        <!-- Year Range -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Year Range</h4>
                            <div class="flex space-x-2">
                                <input type="number" id="yearMin" placeholder="From" class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <input type="number" id="yearMax" placeholder="To" class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Price Range</h4>
                            <div class="flex space-x-2">
                                <input type="number" id="priceMin" placeholder="Min" class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <input type="number" id="priceMax" placeholder="Max" class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <!-- Upgrade Filters -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Special</h4>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="promoted" value="1">
                                    <span class="text-sm">Promoted</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="featured" value="1">
                                    <span class="text-sm">Featured</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="filter-checkbox mr-2" data-filter="sponsored" value="1">
                                    <span class="text-sm">Sponsored</span>
                                </label>
                            </div>
                        </div>

                        <button id="clearFilters" class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="lg:w-3/4">
                    <!-- Results Header -->
                    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">Vehicle Listings</h2>
                                <p class="text-sm text-gray-600 mt-1">
                                    <span id="resultsCount">0</span> vehicles found
                                </p>
                            </div>
                            <div class="flex space-x-4">
                                <select id="sortBy" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="created_at">Newest First</option>
                                    <option value="price_asc">Price: Low to High</option>
                                    <option value="price_desc">Price: High to Low</option>
                                    <option value="year_desc">Year: Newest First</option>
                                    <option value="views">Most Viewed</option>
                                </select>
                                <div class="flex space-x-2">
                                    <button id="gridView" class="p-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                        <i class="fas fa-th"></i>
                                    </button>
                                    <button id="listView" class="p-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading -->
                    <div id="loading" class="text-center py-12">
                        <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
                        <p class="mt-4 text-gray-600">Loading vehicles...</p>
                    </div>

                    <!-- Results Grid -->
                    <div id="vehiclesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Vehicles will be loaded here -->
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-8 flex justify-center">
                        <!-- Pagination will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Map Section (Optional) -->
    <div class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Vehicles Worldwide</h2>
            <div class="map-container flex items-center justify-center">
                <div class="text-center">
                    <i class="fas fa-map-marked-alt text-6xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">Interactive map showing vehicle locations worldwide</p>
                    <button class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-expand mr-2"></i> View Full Map
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Activity Feed -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Live Activity</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Views</h3>
                    <div id="recentViews" class="space-y-3">
                        <!-- Recent views will be loaded here -->
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Trending Vehicles</h3>
                    <div id="trendingVehicles" class="space-y-3">
                        <!-- Trending vehicles will be loaded here -->
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Popular Locations</h3>
                    <div id="popularLocations" class="space-y-3">
                        <!-- Popular locations will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let filters = {};
        let viewMode = 'grid';

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCategories();
            loadMakes();
            loadVehicles();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Search
            document.getElementById('searchBtn').addEventListener('click', performSearch);
            
            // Filters
            document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateFilters();
                    loadVehicles();
                });
            });

            document.getElementById('filterMake').addEventListener('change', function() {
                updateFilters();
                loadVehicles();
            });

            document.getElementById('yearMin').addEventListener('input', debounce(function() {
                updateFilters();
                loadVehicles();
            }, 500));

            document.getElementById('yearMax').addEventListener('input', debounce(function() {
                updateFilters();
                loadVehicles();
            }, 500));

            document.getElementById('priceMin').addEventListener('input', debounce(function() {
                updateFilters();
                loadVehicles();
            }, 500));

            document.getElementById('priceMax').addEventListener('input', debounce(function() {
                updateFilters();
                loadVehicles();
            }, 500));

            // Sort
            document.getElementById('sortBy').addEventListener('change', function() {
                loadVehicles();
            });

            // View mode
            document.getElementById('gridView').addEventListener('click', function() {
                viewMode = 'grid';
                document.getElementById('vehiclesGrid').className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
            });

            document.getElementById('listView').addEventListener('click', function() {
                viewMode = 'list';
                document.getElementById('vehiclesGrid').className = 'space-y-4';
            });

            // Clear filters
            document.getElementById('clearFilters').addEventListener('click', function() {
                clearAllFilters();
                loadVehicles();
            });
        }

        function updateFilters() {
            filters = {};
            
            // Checkbox filters
            document.querySelectorAll('.filter-checkbox:checked').forEach(checkbox => {
                const filterType = checkbox.dataset.filter;
                if (!filters[filterType]) {
                    filters[filterType] = [];
                }
                filters[filterType].push(checkbox.value);
            });

            // Text filters
            const make = document.getElementById('filterMake').value;
            if (make) filters.make_id = make;

            const yearMin = document.getElementById('yearMin').value;
            const yearMax = document.getElementById('yearMax').value;
            if (yearMin) filters.min_year = yearMin;
            if (yearMax) filters.max_year = yearMax;

            const priceMin = document.getElementById('priceMin').value;
            const priceMax = document.getElementById('priceMax').value;
            if (priceMin) filters.min_price = priceMin;
            if (priceMax) filters.max_price = priceMax;

            // Search filters
            const location = document.getElementById('searchLocation').value;
            if (location) filters.city = location;

            const advertType = document.getElementById('searchAdvertType').value;
            if (advertType) filters.advert_type = advertType;

            const category = document.getElementById('searchCategory').value;
            if (category) filters.category = category;
        }

        function clearAllFilters() {
            document.querySelectorAll('.filter-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('filterMake').value = '';
            document.getElementById('yearMin').value = '';
            document.getElementById('yearMax').value = '';
            document.getElementById('priceMin').value = '';
            document.getElementById('priceMax').value = '';
            document.getElementById('searchLocation').value = '';
            document.getElementById('searchAdvertType').value = '';
            document.getElementById('searchCategory').value = '';
            filters = {};
        }

        function performSearch() {
            updateFilters();
            currentPage = 1;
            loadVehicles();
        }

        async function loadCategories() {
            try {
                const response = await fetch('/api/vehicle-categories');
                const categories = await response.json();
                
                const grid = document.getElementById('categoriesGrid');
                const searchSelect = document.getElementById('searchCategory');
                
                grid.innerHTML = '';
                searchSelect.innerHTML = '<option value="">All Categories</option>';
                
                categories.forEach(category => {
                    // Category card
                    const card = document.createElement('div');
                    card.className = 'category-card bg-white border border-gray-200 rounded-lg p-4 text-center cursor-pointer hover:shadow-lg';
                    card.innerHTML = `
                        <i class="fas fa-${category.icon || 'car'} text-3xl text-indigo-600 mb-2"></i>
                        <h3 class="font-semibold text-gray-900">${category.name}</h3>
                        <p class="text-xs text-gray-600 mt-1">${category.vehicle_count || 0} vehicles</p>
                    `;
                    card.addEventListener('click', () => {
                        document.getElementById('searchCategory').value = category.id;
                        performSearch();
                    });
                    grid.appendChild(card);

                    // Search option
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    searchSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }

        async function loadMakes() {
            try {
                const response = await fetch('/api/vehicles/makes');
                const makes = await response.json();
                
                const select = document.getElementById('filterMake');
                select.innerHTML = '<option value="">All Makes</option>';
                
                makes.forEach(make => {
                    const option = document.createElement('option');
                    option.value = make.id;
                    option.textContent = make.name;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading makes:', error);
            }
        }

        async function loadVehicles(page = 1) {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('vehiclesGrid').innerHTML = '';
            
            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: 12,
                    sort_by: document.getElementById('sortBy').value,
                    sort_order: document.getElementById('sortBy').value.includes('desc') ? 'desc' : 'asc',
                    with_priority: 'true',
                    ...filters
                });

                const response = await fetch(`/api/vehicles?${params}`);
                const data = await response.json();
                
                displayVehicles(data.data);
                updatePagination(data);
                document.getElementById('resultsCount').textContent = data.total;
                
            } catch (error) {
                console.error('Error loading vehicles:', error);
                document.getElementById('vehiclesGrid').innerHTML = '<p class="text-center text-gray-600 col-span-full">Error loading vehicles. Please try again.</p>';
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }

        function displayVehicles(vehicles) {
            const grid = document.getElementById('vehiclesGrid');
            
            if (vehicles.length === 0) {
                grid.innerHTML = '<p class="text-center text-gray-600 col-span-full">No vehicles found matching your criteria.</p>';
                return;
            }

            vehicles.forEach(vehicle => {
                const card = createVehicleCard(vehicle);
                grid.appendChild(card);
            });
        }

        function createVehicleCard(vehicle) {
            const card = document.createElement('div');
            card.className = viewMode === 'grid' ? 'vehicle-card bg-white rounded-lg shadow-sm overflow-hidden' : 'vehicle-card bg-white rounded-lg shadow-sm p-4 flex space-x-4';
            
            const badges = getUpgradeBadges(vehicle);
            
            if (viewMode === 'grid') {
                card.innerHTML = `
                    <div class="relative">
                        <img src="${vehicle.main_image_url || '/images/placeholder.png'}" alt="${vehicle.title}" class="w-full h-48 object-cover">
                        ${badges.length > 0 ? `<div class="absolute top-2 left-2 space-y-1">${badges.join('')}</div>` : ''}
                        <div class="absolute top-2 right-2">
                            <button class="bg-white p-2 rounded-full shadow-md hover:bg-gray-100" onclick="toggleFavorite(${vehicle.id})">
                                <i class="far fa-heart text-red-500"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-gray-900 text-lg">${vehicle.title}</h3>
                            <span class="text-xs text-gray-500">${vehicle.year}</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">${vehicle.make?.name} ${vehicle.vehicleModel?.name}</p>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xl font-bold text-indigo-600">${vehicle.display_price}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded">${vehicle.advert_type}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500">
                            <span><i class="fas fa-eye mr-1"></i> ${vehicle.views || 0}</span>
                            <span><i class="fas fa-map-marker-alt mr-1"></i> ${vehicle.location}</span>
                        </div>
                    </div>
                `;
            } else {
                card.innerHTML = `
                    <img src="${vehicle.main_image_url || '/images/placeholder.png'}" alt="${vehicle.title}" class="w-32 h-32 object-cover rounded-lg">
                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-gray-900 text-lg">${vehicle.title}</h3>
                            <div class="flex space-x-1">
                                ${badges.length > 0 ? badges.join('') : ''}
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">${vehicle.make?.name} ${vehicle.vehicleModel?.name} • ${vehicle.year}</p>
                        <p class="text-sm text-gray-500 mb-2">${vehicle.description?.substring(0, 100)}...</p>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-indigo-600">${vehicle.display_price}</span>
                            <div class="flex space-x-4 text-sm text-gray-500">
                                <span><i class="fas fa-eye mr-1"></i> ${vehicle.views || 0}</span>
                                <span><i class="fas fa-map-marker-alt mr-1"></i> ${vehicle.location}</span>
                                <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded">${vehicle.advert_type}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <button class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors" onclick="viewVehicle(${vehicle.id})">
                            View Details
                        </button>
                        <button class="bg-white p-2 rounded-full shadow-md hover:bg-gray-100" onclick="toggleFavorite(${vehicle.id})">
                            <i class="far fa-heart text-red-500"></i>
                        </button>
                    </div>
                `;
            }
            
            card.addEventListener('click', function(e) {
                if (!e.target.closest('button')) {
                    viewVehicle(vehicle.id);
                }
            });
            
            return card;
        }

        function getUpgradeBadges(vehicle) {
            const badges = [];
            
            if (vehicle.is_top_of_category) {
                badges.push('<span class="badge-top-category text-xs px-2 py-1 text-white rounded">Top of Category</span>');
            } else if (vehicle.is_sponsored) {
                badges.push('<span class="badge-sponsored text-xs px-2 py-1 text-white rounded">Sponsored</span>');
            } else if (vehicle.is_featured) {
                badges.push('<span class="badge-featured text-xs px-2 py-1 text-white rounded">Featured</span>');
            } else if (vehicle.is_promoted) {
                badges.push('<span class="badge-promoted text-xs px-2 py-1 text-white rounded">Promoted</span>');
            }
            
            return badges;
        }

        function updatePagination(data) {
            const pagination = document.getElementById('pagination');
            
            if (data.last_page <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let html = '<div class="flex space-x-2">';
            
            // Previous
            if (data.prev_page_url) {
                html += `<button onclick="loadVehicles(${data.current_page - 1})" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-100">Previous</button>`;
            }
            
            // Page numbers
            for (let i = 1; i <= data.last_page; i++) {
                const active = i === data.current_page ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300';
                html += `<button onclick="loadVehicles(${i})" class="px-3 py-2 ${active} rounded-md hover:bg-gray-100">${i}</button>`;
            }
            
            // Next
            if (data.next_page_url) {
                html += `<button onclick="loadVehicles(${data.current_page + 1})" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-100">Next</button>`;
            }
            
            html += '</div>';
            pagination.innerHTML = html;
        }

        function viewVehicle(id) {
            window.location.href = `/vehicles/${id}`;
        }

        async function toggleFavorite(vehicleId) {
            try {
                const response = await fetch(`/api/vehicles/${vehicleId}/save`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    // Update UI based on result
                    console.log(result.message);
                } else {
                    // Redirect to login if not authenticated
                    window.location.href = '/login';
                }
            } catch (error) {
                console.error('Error toggling favorite:', error);
            }
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
</body>
</html>
