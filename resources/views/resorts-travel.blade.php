<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resorts & Travel - WorldwideAdverts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.9), rgba(16, 185, 129, 0.9)), url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1920&h=1080&fit=crop') center/cover;
        }
        .category-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #3b82f6;
        }
        .advert-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        .advert-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .promotion-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .world-map {
            height: 500px;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .flag-icon {
            width: 24px;
            height: 16px;
            object-fit: cover;
            border-radius: 2px;
        }
        .sticky-search {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .carousel-container {
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
        }
        .carousel-item {
            scroll-snap-align: start;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <i class="fas fa-globe text-blue-600 text-2xl mr-2"></i>
                        <span class="font-bold text-xl text-gray-900">WorldwideAdverts</span>
                    </div>
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="/" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Home</a>
                        <a href="/categories" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Categories</a>
                        <a href="/vehicles" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Vehicles</a>
                        <a href="/books" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Books & Literature</a>
                        <a href="/resorts-travel" class="text-blue-600 border-b-2 border-blue-600 px-3 py-2 rounded-md text-sm font-medium">Resorts & Travel</a>
                        <a href="/events" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Events</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="openPostAdvertModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-plus mr-2"></i>Post Advert
                    </button>
                    <a href="/login" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Login</a>
                    <a href="/register" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">Register</a>
                    <button onclick="toggleMobileMenu()" class="md:hidden text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="/categories" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Categories</a>
                <a href="/vehicles" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Vehicles</a>
                <a href="/books" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Books & Literature</a>
                <a href="/resorts-travel" class="block text-blue-600 border-l-4 border-blue-600 bg-blue-50 px-3 py-2 rounded-md text-base font-medium">Resorts & Travel</a>
                <a href="/events" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Events</a>
                <a href="/login" class="block text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-base font-medium">Login</a>
                <a href="/register" class="block bg-green-600 text-white px-3 py-2 rounded-md text-base font-medium">Register</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">Discover Resorts, Hotels & Travel Experiences Worldwide</h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100">Book your stay, plan your journey, or promote your travel business to a global audience.</p>
                
                <!-- Search Bar -->
                <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-xl p-6">
                    <form onsubmit="searchAdverts(event)" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Destination</label>
                            <input type="text" id="searchDestination" placeholder="City or Country" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="searchCategory" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Categories</option>
                                <option value="accommodation">Accommodation</option>
                                <option value="transport">Transport</option>
                                <option value="experience">Experiences</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                            <select id="searchPrice" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Any Price</option>
                                <option value="0-50">Under £50</option>
                                <option value="50-100">£50 - £100</option>
                                <option value="100-200">£100 - £200</option>
                                <option value="200+">£200+</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive World Map -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Explore Travel Destinations Worldwide</h2>
                <p class="text-lg text-gray-600">Click on any region to discover amazing resorts, hotels, and travel experiences</p>
            </div>
            <div id="worldMap" class="world-map"></div>
            <div class="mt-8 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <button onclick="focusRegion('europe')" class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-globe-europe mr-2"></i>Europe
                </button>
                <button onclick="focusRegion('northamerica')" class="bg-green-100 hover:bg-green-200 text-green-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-globe-americas mr-2"></i>North America
                </button>
                <button onclick="focusRegion('southamerica')" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-globe-americas mr-2"></i>South America
                </button>
                <button onclick="focusRegion('asia')" class="bg-red-100 hover:bg-red-200 text-red-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-globe-asia mr-2"></i>Asia
                </button>
                <button onclick="focusRegion('africa')" class="bg-purple-100 hover:bg-purple-200 text-purple-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-globe-africa mr-2"></i>Africa
                </button>
                <button onclick="focusRegion('middleeast')" class="bg-orange-100 hover:bg-orange-200 text-orange-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-mosque mr-2"></i>Middle East
                </button>
                <button onclick="focusRegion('oceania')" class="bg-teal-100 hover:bg-teal-200 text-teal-800 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-globe mr-2"></i>Oceania
                </button>
            </div>
        </div>
    </section>

    <!-- Travel Categories Grid -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Browse Travel Categories</h2>
                <p class="text-lg text-gray-600">Find exactly what you're looking for in our comprehensive travel marketplace</p>
            </div>
            
            <!-- Accommodation Categories -->
            <div class="mb-12">
                <h3 class="text-2xl font-semibold text-gray-800 mb-6">Accommodation</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6" id="accommodationCategories">
                    <!-- Categories will be loaded here -->
                </div>
            </div>

            <!-- Transport Categories -->
            <div class="mb-12">
                <h3 class="text-2xl font-semibold text-gray-800 mb-6">Transport Services</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6" id="transportCategories">
                    <!-- Categories will be loaded here -->
                </div>
            </div>

            <!-- Experience Categories -->
            <div>
                <h3 class="text-2xl font-semibold text-gray-800 mb-6">Travel Experiences</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6" id="experienceCategories">
                    <!-- Categories will be loaded here -->
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Destinations -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Featured Destinations</h2>
                <p class="text-lg text-gray-600">Discover the most popular travel destinations around the world</p>
            </div>
            
            <div class="carousel-container flex overflow-x-auto space-x-6 pb-4" id="featuredDestinations">
                <!-- Featured destinations will be loaded here -->
            </div>
        </div>
    </section>

    <!-- Travel Advert Listings -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Filters Sidebar -->
                <div class="lg:w-1/4">
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky-search">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                        
                        <!-- Accommodation Type Filter -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-3">Accommodation Type</h4>
                            <div class="space-y-2" id="accommodationFilters">
                                <!-- Filters will be loaded here -->
                            </div>
                        </div>

                        <!-- Transport Type Filter -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-3">Transport Type</h4>
                            <div class="space-y-2" id="transportFilters">
                                <!-- Filters will be loaded here -->
                            </div>
                        </div>

                        <!-- Experience Type Filter -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-3">Experience Type</h4>
                            <div class="space-y-2" id="experienceFilters">
                                <!-- Filters will be loaded here -->
                            </div>
                        </div>

                        <!-- Country Filter -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-3">Country</h4>
                            <select id="countryFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Countries</option>
                            </select>
                        </div>

                        <!-- Price Range Filter -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-3">Price Range</h4>
                            <div class="flex space-x-2">
                                <input type="number" id="minPrice" placeholder="Min" class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <input type="number" id="maxPrice" placeholder="Max" class="w-1/2 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <!-- Amenities Filter -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-3">Amenities</h4>
                            <div class="space-y-2 max-h-48 overflow-y-auto" id="amenitiesFilters">
                                <!-- Amenities will be loaded here -->
                            </div>
                        </div>

                        <!-- Additional Filters -->
                        <div class="mb-6">
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" id="verifiedFilter" class="mr-2">
                                    <span class="text-sm text-gray-700">Verified Businesses Only</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" id="promotedFilter" class="mr-2">
                                    <span class="text-sm text-gray-700">Promoted Adverts</span>
                                </label>
                            </div>
                        </div>

                        <button onclick="applyFilters()" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
                            Apply Filters
                        </button>
                        <button onclick="resetFilters()" class="w-full mt-2 bg-gray-200 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-300 transition">
                            Reset Filters
                        </button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:w-3/4">
                    <!-- Sorting Bar -->
                    <div class="bg-white rounded-lg shadow-lg p-4 mb-6">
                        <div class="flex flex-col sm:flex-row justify-between items-center">
                            <div class="mb-4 sm:mb-0">
                                <span class="text-gray-600">Showing <span id="resultCount">0</span> results</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <label class="text-gray-600">Sort by:</label>
                                <select id="sortOptions" onchange="sortAdverts()" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="created_at">Most Recent</option>
                                    <option value="views">Most Viewed</option>
                                    <option value="rating">Highest Rated</option>
                                    <option value="trending">Trending</option>
                                    <option value="price_asc">Price Low to High</option>
                                    <option value="price_desc">Price High to Low</option>
                                </select>
                                <div class="flex space-x-2">
                                    <button onclick="setViewMode('grid')" class="p-2 text-blue-600 border border-blue-600 rounded">
                                        <i class="fas fa-th"></i>
                                    </button>
                                    <button onclick="setViewMode('list')" class="p-2 text-gray-400 border border-gray-300 rounded">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Adverts Grid -->
                    <div id="advertsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <!-- Adverts will be loaded here -->
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8 flex justify-center">
                        <nav class="flex space-x-2" id="pagination">
                            <!-- Pagination will be loaded here -->
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Business Profiles Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Featured Travel Businesses</h2>
                <p class="text-lg text-gray-600">Connect with trusted travel providers and businesses</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="businessProfiles">
                <!-- Business profiles will be loaded here -->
            </div>
        </div>
    </section>

    <!-- Live Activity Feed -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Live Travel Activity</h2>
                <p class="text-lg text-gray-600">See what's happening in the travel community right now</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="space-y-4" id="activityFeed">
                    <!-- Activity items will be loaded here -->
                </div>
            </div>
        </div>
    </section>

    <!-- Upsell Promotion Banner -->
    <section class="py-16 bg-gradient-to-r from-blue-600 to-teal-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Boost Your Travel Business</h2>
            <p class="text-xl mb-8">Want your resort or travel service to appear at the top? Upgrade to Featured or Sponsored.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-6 border border-white border-opacity-20">
                    <div class="text-3xl mb-4">⭐</div>
                    <h3 class="text-xl font-semibold mb-2">Promoted</h3>
                    <p class="mb-4">Highlighted listing with 2x visibility</p>
                    <button onclick="selectPromotion('promoted')" class="bg-white text-blue-600 px-4 py-2 rounded-md hover:bg-gray-100 transition w-full">
                        Select - £29.99
                    </button>
                </div>
                
                <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-6 border-2 border-yellow-400">
                    <div class="text-3xl mb-4">🔥</div>
                    <h3 class="text-xl font-semibold mb-2">Featured <span class="bg-yellow-400 text-blue-600 text-xs px-2 py-1 rounded-full">Most Popular</span></h3>
                    <p class="mb-4">Top placement with 4x visibility</p>
                    <button onclick="selectPromotion('featured')" class="bg-yellow-400 text-blue-600 px-4 py-2 rounded-md hover:bg-yellow-300 transition w-full font-semibold">
                        Select - £59.99
                    </button>
                </div>
                
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-6 border border-white border-opacity-20">
                    <div class="text-3xl mb-4">🚀</div>
                    <h3 class="text-xl font-semibold mb-2">Sponsored</h3>
                    <p class="mb-4">Homepage placement with maximum exposure</p>
                    <button onclick="selectPromotion('sponsored')" class="bg-white text-blue-600 px-4 py-2 rounded-md hover:bg-gray-100 transition w-full">
                        Select - £99.99
                    </button>
                </div>
                
                <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-6 border border-white border-opacity-20">
                    <div class="text-3xl mb-4">💎</div>
                    <h3 class="text-xl font-semibold mb-2">Network-Wide Boost</h3>
                    <p class="mb-4">Ultimate visibility across all pages</p>
                    <button onclick="selectPromotion('network_wide')" class="bg-white text-blue-600 px-4 py-2 rounded-md hover:bg-gray-100 transition w-full">
                        Select - £199.99
                    </button>
                </div>
            </div>
            
            <button onclick="openPostAdvertModal()" class="bg-white text-blue-600 px-8 py-3 rounded-lg text-lg font-semibold hover:bg-gray-100 transition">
                <i class="fas fa-plus mr-2"></i>Post Your Travel Advert
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-globe text-blue-400 text-2xl mr-2"></i>
                        <span class="font-bold text-xl">WorldwideAdverts</span>
                    </div>
                    <p class="text-gray-400">Your global marketplace for travel services, accommodations, and experiences.</p>
                    <div class="flex space-x-4 mt-4">
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div>
                    <h3 class="font-semibold text-lg mb-4">Travel Listings</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Hotels & Resorts</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Transport Services</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Travel Experiences</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Business Directory</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold text-lg mb-4">Categories</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Accommodation</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Transport</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Experiences</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">All Categories</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold text-lg mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2026 WorldwideAdverts. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Post Advert Modal -->
    <div id="postAdvertModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b p-6 flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-900">Post Travel Advert</h2>
                    <button onclick="closePostAdvertModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="advertForm" class="p-6">
                    <!-- Step indicators -->
                    <div class="mb-8">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div id="step1Indicator" class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">1</div>
                                <div class="w-16 h-1 bg-blue-600"></div>
                                <div id="step2Indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">2</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step3Indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">3</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step4Indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">4</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step5Indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">5</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step6Indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">6</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step7Indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">7</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div id="step8Indicator" class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold">8</div>
                            </div>
                        </div>
                        <div class="flex justify-between mt-2 text-sm text-gray-600">
                            <span>Advert Type</span>
                            <span>Basic Info</span>
                            <span>Details</span>
                            <span>Description</span>
                            <span>Contact</span>
                            <span>Location</span>
                            <span>Promotion</span>
                            <span>Submit</span>
                        </div>
                    </div>

                    <!-- Step 1: Advert Type -->
                    <div id="step1" class="step-content">
                        <h3 class="text-xl font-semibold mb-6">Select Advert Type</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div onclick="selectAdvertType('accommodation')" class="border-2 border-gray-300 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition advert-type-card">
                                <div class="text-4xl mb-4">🏨</div>
                                <h4 class="text-lg font-semibold mb-2">Accommodation</h4>
                                <p class="text-gray-600 mb-4">Hotels, resorts, B&Bs, holiday homes and more</p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Resort</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Hotel</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>B&B</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Guest House</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Holiday Home</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Villa</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Lodge</div>
                                </div>
                            </div>
                            
                            <div onclick="selectAdvertType('transport')" class="border-2 border-gray-300 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition advert-type-card">
                                <div class="text-4xl mb-4">🚗</div>
                                <h4 class="text-lg font-semibold mb-2">Transport Services</h4>
                                <p class="text-gray-600 mb-4">Airport transfers, car hire, shuttles and more</p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Airport Transfer</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Taxi / Chauffeur</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Car Hire</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Shuttle Bus</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Tour Bus</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Boat / Ferry</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Motorbike Rental</div>
                                </div>
                            </div>
                            
                            <div onclick="selectAdvertType('experience')" class="border-2 border-gray-300 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition advert-type-card">
                                <div class="text-4xl mb-4">🎯</div>
                                <h4 class="text-lg font-semibold mb-2">Travel Experiences</h4>
                                <p class="text-gray-600 mb-4">Tours, excursions, adventures and wellness</p>
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Tours</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Excursions</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Adventure Packages</div>
                                    <div class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Wellness Retreats</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Basic Information -->
                    <div id="step2" class="step-content hidden">
                        <h3 class="text-xl font-semibold mb-6">Basic Advert Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Advert Title *</label>
                                <input type="text" id="advertTitle" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your advert title">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                                <input type="text" id="advertTagline" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="A catchy tagline">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="advertCategory" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select a category</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                                <input type="text" id="advertCountry" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Country">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City / Region *</label>
                                <input type="text" id="advertCity" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="City or region">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price per night / trip *</label>
                                <input type="number" id="advertPrice" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0.00" step="0.01">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Availability Dates</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <input type="date" id="availabilityStart" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <input type="date" id="availabilityEnd" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <h4 class="font-medium text-gray-700 mb-3">Media Uploads</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Main Image *</label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-600">Click to upload main image</p>
                                        <input type="file" id="mainImage" accept="image/*" class="hidden">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Images (max 10)</label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                        <i class="fas fa-images text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-600">Click to upload additional images</p>
                                        <input type="file" id="additionalImages" accept="image/*" multiple class="hidden">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Video Link (optional)</label>
                                <input type="url" id="videoLink" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="https://youtube.com/watch?v=...">
                            </div>
                        </div>
                    </div>

                    <!-- More steps would be added here for brevity -->
                    
                    <!-- Navigation buttons -->
                    <div class="flex justify-between mt-8">
                        <button onclick="previousStep()" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-300 transition" id="prevBtn" style="display: none;">
                            <i class="fas fa-arrow-left mr-2"></i>Previous
                        </button>
                        <button onclick="nextStep()" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition" id="nextBtn">
                            Next<i class="fas fa-arrow-right ml-2"></i>
                        </button>
                        <button onclick="submitAdvert()" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition" id="submitBtn" style="display: none;">
                            <i class="fas fa-check mr-2"></i>Submit Advert
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentStep = 1;
        let selectedAdvertType = '';
        let map = null;
        let currentPage = 1;
        let filters = {};
        let sortBy = 'created_at';

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            initializeMap();
            loadCategories();
            loadAdverts();
            loadFeaturedDestinations();
            loadBusinessProfiles();
            loadActivityFeed();
            loadAmenities();
            loadCountries();
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Initialize Leaflet map
        function initializeMap() {
            map = L.map('worldMap').setView([20, 0], 2);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add sample markers for popular destinations
            const destinations = [
                {name: 'Dubai', coords: [25.2048, 55.2708], country: 'UAE'},
                {name: 'Bali', coords: [-8.3405, 115.0920], country: 'Indonesia'},
                {name: 'London', coords: [51.5074, -0.1278], country: 'UK'},
                {name: 'Cape Town', coords: [-33.9249, 18.4241], country: 'South Africa'},
                {name: 'New York', coords: [40.7128, -74.0060], country: 'USA'},
                {name: 'Santorini', coords: [36.3932, 25.4615], country: 'Greece'},
                {name: 'Marrakech', coords: [31.6295, -7.9811], country: 'Morocco'}
            ];

            destinations.forEach(dest => {
                const marker = L.marker(dest.coords).addTo(map);
                marker.bindPopup(`<b>${dest.name}</b><br>${dest.country}<br><a href="#" onclick="searchDestination('${dest.name}')">View listings</a>`);
            });
        }

        // Focus on specific region
        function focusRegion(region) {
            const regions = {
                'europe': [50, 10],
                'northamerica': [45, -100],
                'southamerica': [-15, -60],
                'asia': [30, 100],
                'africa': [0, 20],
                'middleeast': [25, 45],
                'oceania': [-25, 135]
            };
            
            if (regions[region]) {
                map.setView(regions[region], 4);
            }
        }

        // Load categories
        async function loadCategories() {
            try {
                const response = await fetch('/api/resorts-travel/categories');
                const data = await response.json();
                
                if (data.success) {
                    displayCategories(data.data);
                }
            } catch (error) {
                console.error('Error loading categories:', error);
                // Display sample categories for demo
                displaySampleCategories();
            }
        }

        // Display categories
        function displayCategories(categories) {
            const accommodationContainer = document.getElementById('accommodationCategories');
            const transportContainer = document.getElementById('transportCategories');
            const experienceContainer = document.getElementById('experienceCategories');
            
            // Clear existing content
            accommodationContainer.innerHTML = '';
            transportContainer.innerHTML = '';
            experienceContainer.innerHTML = '';
            
            categories.forEach(category => {
                const categoryCard = createCategoryCard(category);
                if (category.type === 'accommodation') {
                    accommodationContainer.appendChild(categoryCard);
                } else if (category.type === 'transport') {
                    transportContainer.appendChild(categoryCard);
                } else if (category.type === 'experience') {
                    experienceContainer.appendChild(categoryCard);
                }
            });
        }

        // Display sample categories (for demo purposes)
        function displaySampleCategories() {
            const sampleCategories = {
                accommodation: [
                    {name: 'Luxury Resorts', count: 1250, icon: '🏰'},
                    {name: 'Boutique Hotels', count: 890, icon: '🏨'},
                    {name: 'Budget Hotels', count: 2100, icon: '💰'},
                    {name: 'Bed & Breakfasts', count: 1560, icon: '🏡'},
                    {name: 'Holiday Homes', count: 980, icon: '🏠'},
                    {name: 'Beachfront Stays', count: 750, icon: '🏖️'},
                    {name: 'Mountain Retreats', count: 420, icon: '🏔️'},
                    {name: 'City Breaks', count: 1890, icon: '🏙️'}
                ],
                transport: [
                    {name: 'Airport Transfers', count: 3200, icon: '✈️'},
                    {name: 'Car Hire', count: 1890, icon: '🚗'},
                    {name: 'Chauffeur Services', count: 450, icon: '🚖'},
                    {name: 'Taxi Services', count: 2800, icon: '🚕'},
                    {name: 'Shuttle Buses', count: 670, icon: '🚌'},
                    {name: 'Boat & Ferry Services', count: 340, icon: '⛵'},
                    {name: 'Tour Buses', count: 890, icon: '🚐'},
                    {name: 'Motorbike Rentals', count: 210, icon: '🏍️'}
                ],
                experience: [
                    {name: 'Tours', count: 3450, icon: '🎯'},
                    {name: 'Excursions', count: 1280, icon: '🚶'},
                    {name: 'Adventure Packages', count: 890, icon: '🏔️'},
                    {name: 'Wellness Retreats', count: 340, icon: '🧘'}
                ]
            };

            Object.keys(sampleCategories).forEach(type => {
                const container = document.getElementById(type + 'Categories');
                sampleCategories[type].forEach(category => {
                    const card = document.createElement('div');
                    card.className = 'category-card bg-white rounded-lg p-6 text-center cursor-pointer';
                    card.innerHTML = `
                        <div class="text-4xl mb-4">${category.icon}</div>
                        <h4 class="font-semibold text-gray-900 mb-2">${category.name}</h4>
                        <p class="text-sm text-gray-600 mb-4">${category.count} active adverts</p>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition text-sm">
                            Explore
                        </button>
                    `;
                    container.appendChild(card);
                });
            });
        }

        // Create category card
        function createCategoryCard(category) {
            const card = document.createElement('div');
            card.className = 'category-card bg-white rounded-lg p-6 text-center cursor-pointer';
            card.innerHTML = `
                <img src="${category.image || '/placeholder.png'}" alt="${category.name}" class="w-16 h-16 mx-auto mb-4 rounded-lg object-cover">
                <h4 class="font-semibold text-gray-900 mb-2">${category.name}</h4>
                <p class="text-sm text-gray-600 mb-4">${category.count || 0} active adverts</p>
                <button onclick="filterByCategory(${category.id})" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition text-sm">
                    Explore
                </button>
            `;
            return card;
        }

        // Load adverts
        async function loadAdverts(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: 12,
                    sort: sortBy,
                    ...filters
                });
                
                const response = await fetch(`/api/resorts-travel?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    displayAdverts(data.data.data);
                    updatePagination(data.data);
                    document.getElementById('resultCount').textContent = data.data.total;
                }
            } catch (error) {
                console.error('Error loading adverts:', error);
                // Display sample adverts for demo
                displaySampleAdverts();
            }
        }

        // Display adverts
        function displayAdverts(adverts) {
            const container = document.getElementById('advertsContainer');
            container.innerHTML = '';
            
            adverts.forEach(advert => {
                const card = createAdvertCard(advert);
                container.appendChild(card);
            });
        }

        // Display sample adverts (for demo purposes)
        function displaySampleAdverts() {
            const sampleAdverts = [
                {
                    title: 'Luxury Beach Resort in Bali',
                    type: 'accommodation',
                    subtype: 'resort',
                    location: 'Bali, Indonesia',
                    country: 'Indonesia',
                    price: 150,
                    currency: 'GBP',
                    price_type: 'per_night',
                    image: 'https://images.unsplash.com/photo-1571003123894-1f0594d4b541?w=400&h=300&fit=crop',
                    promotion: 'featured',
                    verified: true,
                    rating: 4.8,
                    reviews: 234
                },
                {
                    title: 'Airport Transfer Service London',
                    type: 'transport',
                    subtype: 'airport_transfer',
                    location: 'London, UK',
                    country: 'United Kingdom',
                    price: 45,
                    currency: 'GBP',
                    price_type: 'per_service',
                    image: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=400&h=300&fit=crop',
                    promotion: 'standard',
                    verified: true,
                    rating: 4.6,
                    reviews: 156
                },
                {
                    title: 'Santorini Sunset Experience',
                    type: 'experience',
                    subtype: 'tours',
                    location: 'Santorini, Greece',
                    country: 'Greece',
                    price: 89,
                    currency: 'GBP',
                    price_type: 'per_trip',
                    image: 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=400&h=300&fit=crop',
                    promotion: 'sponsored',
                    verified: false,
                    rating: 4.9,
                    reviews: 412
                },
                {
                    title: 'Mountain Retreat Lodge',
                    type: 'accommodation',
                    subtype: 'lodge',
                    location: 'Swiss Alps, Switzerland',
                    country: 'Switzerland',
                    price: 220,
                    currency: 'GBP',
                    price_type: 'per_night',
                    image: 'https://images.unsplash.com/photo-1549180030-48bf205e8ef5?w=400&h=300&fit=crop',
                    promotion: 'promoted',
                    verified: true,
                    rating: 4.7,
                    reviews: 89
                }
            ];
            
            const container = document.getElementById('advertsContainer');
            container.innerHTML = '';
            
            sampleAdverts.forEach(advert => {
                const card = createAdvertCard(advert);
                container.appendChild(card);
            });
            
            document.getElementById('resultCount').textContent = sampleAdverts.length;
        }

        // Create advert card
        function createAdvertCard(advert) {
            const card = document.createElement('div');
            card.className = 'advert-card bg-white rounded-lg overflow-hidden';
            
            const promotionBadge = advert.promotion && advert.promotion !== 'standard' ? 
                `<span class="promotion-badge absolute top-2 right-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-2 py-1 rounded-full text-xs font-semibold">
                    ${advert.promotion === 'featured' ? '⭐ Featured' : advert.promotion === 'sponsored' ? '🚀 Sponsored' : '💎 Promoted'}
                </span>` : '';
            
            const verifiedBadge = advert.verified ? 
                `<span class="absolute top-2 left-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                    ✓ Verified
                </span>` : '';
            
            card.innerHTML = `
                <div class="relative">
                    <img src="${advert.image || '/placeholder.png'}" alt="${advert.title}" class="w-full h-48 object-cover">
                    ${promotionBadge}
                    ${verifiedBadge}
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-blue-600 uppercase">${advert.subtype.replace('_', ' ')}</span>
                        <div class="flex items-center text-xs text-gray-500">
                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                            ${advert.rating || '4.5'} (${advert.reviews || 0})
                        </div>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">${advert.title}</h3>
                    <div class="flex items-center text-sm text-gray-600 mb-2">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        ${advert.location}
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-lg font-bold text-blue-600">
                            £${advert.price} <span class="text-xs text-gray-500">/${advert.price_type.replace('_', ' ')}</span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="quickView(${advert.id})" class="bg-blue-100 text-blue-600 px-3 py-1 rounded-md hover:bg-blue-200 transition text-sm">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="saveAdvert(${advert.id})" class="bg-gray-100 text-gray-600 px-3 py-1 rounded-md hover:bg-gray-200 transition text-sm">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            return card;
        }

        // Load featured destinations
        function loadFeaturedDestinations() {
            const destinations = [
                {name: 'Dubai', price: 120, count: 450, image: 'https://images.unsplash.com/photo-1555881400-b82f27b883ce?w=300&h=200&fit=crop'},
                {name: 'Bali', price: 65, count: 320, image: 'https://images.unsplash.com/photo-1537953773345-d172ccf0cfce?w=300&h=200&fit=crop'},
                {name: 'London', price: 95, count: 680, image: 'https://images.unsplash.com/photo-1513635269945-9222e7b2c245?w=300&h=200&fit=crop'},
                {name: 'Cape Town', price: 85, count: 190, image: 'https://images.unsplash.com/photo-1569688953244-6db5ebdb2e6f?w=300&h=200&fit=crop'},
                {name: 'New York', price: 140, count: 890, image: 'https://images.unsplash.com/photo-1496442226666-8ee423e817e3?w=300&h=200&fit=crop'},
                {name: 'Santorini', price: 110, count: 156, image: 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=300&h=200&fit=crop'},
                {name: 'Marrakech', price: 55, count: 234, image: 'https://images.unsplash.com/photo-1593086326149-0c18b3b8d889?w=300&h=200&fit=crop'}
            ];
            
            const container = document.getElementById('featuredDestinations');
            container.innerHTML = '';
            
            destinations.forEach(dest => {
                const card = document.createElement('div');
                card.className = 'carousel-item flex-none w-64 bg-white rounded-lg shadow-lg overflow-hidden';
                card.innerHTML = `
                    <img src="${dest.image}" alt="${dest.name}" class="w-full h-40 object-cover">
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-900 mb-2">${dest.name}</h4>
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-3">
                            <span>Avg: £${dest.price}/night</span>
                            <span>${dest.count} listings</span>
                        </div>
                        <button onclick="searchDestination('${dest.name}')" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition text-sm">
                            View All
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // Load business profiles
        function loadBusinessProfiles() {
            const businesses = [
                {
                    name: 'Luxury Resorts International',
                    logo: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=100&h=100&fit=crop',
                    country: 'UAE',
                    description: 'Premium luxury resorts and hotels in exotic destinations worldwide.',
                    services: 45,
                    rating: 4.9,
                    verified: true
                },
                {
                    name: 'QuickTransfers Ltd',
                    logo: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=100&h=100&fit=crop',
                    country: 'UK',
                    description: 'Reliable airport transfers and transportation services across Europe.',
                    services: 23,
                    rating: 4.7,
                    verified: true
                },
                {
                    name: 'Adventure Tours Co.',
                    logo: 'https://images.unsplash.com/photo-1551632811-561732d1e308?w=100&h=100&fit=crop',
                    country: 'New Zealand',
                    description: 'Thrilling adventure experiences and guided tours in stunning locations.',
                    services: 67,
                    rating: 4.8,
                    verified: false
                }
            ];
            
            const container = document.getElementById('businessProfiles');
            container.innerHTML = '';
            
            businesses.forEach(business => {
                const card = document.createElement('div');
                card.className = 'bg-white rounded-lg shadow-lg p-6';
                card.innerHTML = `
                    <div class="flex items-center mb-4">
                        <img src="${business.logo}" alt="${business.name}" class="w-16 h-16 rounded-full object-cover mr-4">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900">${business.name}</h4>
                            <div class="flex items-center text-sm text-gray-600">
                                <span class="flag-icon mr-2">🏳️</span>
                                ${business.country}
                                ${business.verified ? '<span class="ml-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs">✓ Verified</span>' : ''}
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">${business.description}</p>
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold">${business.services}</span> services
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                            <span class="font-semibold">${business.rating}</span>
                        </div>
                    </div>
                    <button class="w-full mt-4 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                        Contact Business
                    </button>
                `;
                container.appendChild(card);
            });
        }

        // Load activity feed
        function loadActivityFeed() {
            const activities = [
                {user: 'Maria from Germany', action: 'viewed a resort in', target: 'Bali', time: '2 minutes ago', icon: '👁️'},
                {user: 'New hotel listing', action: 'added in', target: 'Dubai', time: '5 minutes ago', icon: '🏨'},
                {user: 'A shuttle service in London', action: 'just received a booking', target: '', time: '8 minutes ago', icon: '🚌'},
                {user: 'John from USA', action: 'booked a tour in', target: 'Paris', time: '12 minutes ago', icon: '🎯'},
                {user: 'Luxury resort in Santorini', action: 'is now trending', target: '', time: '15 minutes ago', icon: '🔥'},
                {user: 'Sarah from Canada', action: 'saved an experience in', target: 'Japan', time: '18 minutes ago', icon: '❤️'}
            ];
            
            const container = document.getElementById('activityFeed');
            container.innerHTML = '';
            
            activities.forEach(activity => {
                const item = document.createElement('div');
                item.className = 'flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg transition';
                item.innerHTML = `
                    <div class="text-2xl">${activity.icon}</div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">
                            <span class="font-semibold">${activity.user}</span> ${activity.action} 
                            ${activity.target ? '<span class="font-semibold">' + activity.target + '</span>' : ''}
                        </p>
                        <p class="text-xs text-gray-500">${activity.time}</p>
                    </div>
                `;
                container.appendChild(item);
            });
        }

        // Load amenities
        function loadAmenities() {
            const amenities = [
                'wi_fi', 'pool', 'parking', 'breakfast', 'air_conditioning', 'heating',
                'kitchen', 'tv', 'washing_machine', 'elevator', 'wheelchair_access', 'pet_friendly',
                'gym', 'spa', 'restaurant', 'bar', 'room_service', 'concierge',
                'business_center', 'meeting_rooms', 'airport_shuttle', 'beach_access'
            ];
            
            const container = document.getElementById('amenitiesFilters');
            container.innerHTML = '';
            
            amenities.forEach(amenity => {
                const label = document.createElement('label');
                label.className = 'flex items-center';
                label.innerHTML = `
                    <input type="checkbox" value="${amenity}" class="mr-2 amenity-filter">
                    <span class="text-sm text-gray-700">${amenity.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                `;
                container.appendChild(label);
            });
        }

        // Load countries
        function loadCountries() {
            const countries = ['United Kingdom', 'United States', 'France', 'Germany', 'Italy', 'Spain', 'Greece', 'Portugal', 'Netherlands', 'Switzerland', 'Australia', 'Japan', 'Thailand', 'Indonesia', 'Malaysia', 'Singapore', 'UAE', 'Qatar', 'Egypt', 'South Africa', 'Morocco', 'Kenya', 'Brazil', 'Argentina', 'Mexico', 'Canada', 'India', 'China', 'Russia', 'Turkey'];
            
            const select = document.getElementById('countryFilter');
            countries.forEach(country => {
                const option = document.createElement('option');
                option.value = country;
                option.textContent = country;
                select.appendChild(option);
            });
        }

        // Search functionality
        function searchAdverts(event) {
            event.preventDefault();
            
            const destination = document.getElementById('searchDestination').value;
            const category = document.getElementById('searchCategory').value;
            const priceRange = document.getElementById('searchPrice').value;
            
            filters = {};
            
            if (destination) {
                filters.search = destination;
            }
            
            if (category) {
                filters.advert_type = category;
            }
            
            if (priceRange) {
                if (priceRange === '0-50') {
                    filters.max_price = 50;
                } else if (priceRange === '50-100') {
                    filters.min_price = 50;
                    filters.max_price = 100;
                } else if (priceRange === '100-200') {
                    filters.min_price = 100;
                    filters.max_price = 200;
                } else if (priceRange === '200+') {
                    filters.min_price = 200;
                }
            }
            
            loadAdverts();
        }

        // Search by destination
        function searchDestination(destination) {
            document.getElementById('searchDestination').value = destination;
            searchAdverts(new Event('submit'));
        }

        // Filter functionality
        function applyFilters() {
            filters = {};
            
            // Accommodation filters
            const accommodationFilters = document.querySelectorAll('#accommodationFilters input:checked');
            if (accommodationFilters.length > 0) {
                filters.accommodation_type = accommodationFilters[0].value;
            }
            
            // Transport filters
            const transportFilters = document.querySelectorAll('#transportFilters input:checked');
            if (transportFilters.length > 0) {
                filters.transport_type = transportFilters[0].value;
            }
            
            // Experience filters
            const experienceFilters = document.querySelectorAll('#experienceFilters input:checked');
            if (experienceFilters.length > 0) {
                filters.experience_type = experienceFilters[0].value;
            }
            
            // Country filter
            const country = document.getElementById('countryFilter').value;
            if (country) {
                filters.country = country;
            }
            
            // Price filters
            const minPrice = document.getElementById('minPrice').value;
            const maxPrice = document.getElementById('maxPrice').value;
            if (minPrice) filters.min_price = minPrice;
            if (maxPrice) filters.max_price = maxPrice;
            
            // Amenities filters
            const amenities = Array.from(document.querySelectorAll('.amenity-filter:checked')).map(input => input.value);
            if (amenities.length > 0) {
                filters.amenities = amenities;
            }
            
            // Additional filters
            if (document.getElementById('verifiedFilter').checked) {
                filters.verified = true;
            }
            
            if (document.getElementById('promotedFilter').checked) {
                filters.promotion_tier = 'promoted';
            }
            
            loadAdverts();
        }

        // Reset filters
        function resetFilters() {
            filters = {};
            document.getElementById('countryFilter').value = '';
            document.getElementById('minPrice').value = '';
            document.getElementById('maxPrice').value = '';
            document.getElementById('verifiedFilter').checked = false;
            document.getElementById('promotedFilter').checked = false;
            
            document.querySelectorAll('#accommodationFilters input').forEach(input => input.checked = false);
            document.querySelectorAll('#transportFilters input').forEach(input => input.checked = false);
            document.querySelectorAll('#experienceFilters input').forEach(input => input.checked = false);
            document.querySelectorAll('.amenity-filter').forEach(input => input.checked = false);
            
            loadAdverts();
        }

        // Sort adverts
        function sortAdverts() {
            sortBy = document.getElementById('sortOptions').value;
            loadAdverts();
        }

        // Set view mode
        function setViewMode(mode) {
            const container = document.getElementById('advertsContainer');
            const gridButton = document.querySelector('[onclick="setViewMode(\'grid\')"]');
            const listButton = document.querySelector('[onclick="setViewMode(\'list\')"]');
            
            if (mode === 'grid') {
                container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6';
                gridButton.classList.add('text-blue-600', 'border-blue-600');
                gridButton.classList.remove('text-gray-400', 'border-gray-300');
                listButton.classList.add('text-gray-400', 'border-gray-300');
                listButton.classList.remove('text-blue-600', 'border-blue-600');
            } else {
                container.className = 'space-y-4';
                listButton.classList.add('text-blue-600', 'border-blue-600');
                listButton.classList.remove('text-gray-400', 'border-gray-300');
                gridButton.classList.add('text-gray-400', 'border-gray-300');
                gridButton.classList.remove('text-blue-600', 'border-blue-600');
            }
        }

        // Update pagination
        function updatePagination(data) {
            const container = document.getElementById('pagination');
            container.innerHTML = '';
            
            if (data.prev_page_url) {
                const prevBtn = document.createElement('button');
                prevBtn.className = 'px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50';
                prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                prevBtn.onclick = () => loadAdverts(data.current_page - 1);
                container.appendChild(prevBtn);
            }
            
            for (let i = 1; i <= data.last_page; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = i === data.current_page ? 
                    'px-3 py-2 bg-blue-600 text-white rounded-md' : 
                    'px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50';
                pageBtn.textContent = i;
                pageBtn.onclick = () => loadAdverts(i);
                container.appendChild(pageBtn);
            }
            
            if (data.next_page_url) {
                const nextBtn = document.createElement('button');
                nextBtn.className = 'px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50';
                nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                nextBtn.onclick = () => loadAdverts(data.current_page + 1);
                container.appendChild(nextBtn);
            }
        }

        // Quick view advert
        function quickView(id) {
            // Implementation for quick view modal
            console.log('Quick view advert:', id);
        }

        // Save advert
        function saveAdvert(id) {
            // Implementation for saving/favoriting advert
            console.log('Save advert:', id);
        }

        // Filter by category
        function filterByCategory(categoryId) {
            filters.category_id = categoryId;
            loadAdverts();
        }

        // Post Advert Modal Functions
        function openPostAdvertModal() {
            document.getElementById('postAdvertModal').classList.remove('hidden');
            currentStep = 1;
            showStep(1);
        }

        function closePostAdvertModal() {
            document.getElementById('postAdvertModal').classList.add('hidden');
        }

        function selectAdvertType(type) {
            selectedAdvertType = type;
            
            // Update UI
            document.querySelectorAll('.advert-type-card').forEach(card => {
                card.classList.remove('border-blue-500', 'bg-blue-50');
                card.classList.add('border-gray-300');
            });
            
            event.currentTarget.classList.remove('border-gray-300');
            event.currentTarget.classList.add('border-blue-500', 'bg-blue-50');
            
            // Enable next button
            document.getElementById('nextBtn').disabled = false;
        }

        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.step-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Show current step
            document.getElementById('step' + step).classList.remove('hidden');
            
            // Update step indicators
            for (let i = 1; i <= 8; i++) {
                const indicator = document.getElementById('step' + i + 'Indicator');
                if (i < step) {
                    indicator.className = 'w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center font-semibold';
                    indicator.innerHTML = '✓';
                } else if (i === step) {
                    indicator.className = 'w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold';
                    indicator.innerHTML = i;
                } else {
                    indicator.className = 'w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-semibold';
                    indicator.innerHTML = i;
                }
            }
            
            // Update navigation buttons
            document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'block';
            document.getElementById('nextBtn').style.display = step === 8 ? 'none' : 'block';
            document.getElementById('submitBtn').style.display = step === 8 ? 'block' : 'none';
        }

        function nextStep() {
            if (currentStep < 8) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function submitAdvert() {
            // Implementation for submitting advert
            console.log('Submit advert with type:', selectedAdvertType);
            closePostAdvertModal();
        }

        function selectPromotion(tier) {
            // Implementation for promotion selection
            console.log('Selected promotion tier:', tier);
        }

        // Initialize accommodation filters
        document.addEventListener('DOMContentLoaded', function() {
            const accommodationTypes = ['resort', 'hotel', 'bnb', 'guest_house', 'holiday_home', 'villa', 'lodge'];
            const container = document.getElementById('accommodationFilters');
            
            accommodationTypes.forEach(type => {
                const label = document.createElement('label');
                label.className = 'flex items-center';
                label.innerHTML = `
                    <input type="radio" name="accommodation_type" value="${type}" class="mr-2">
                    <span class="text-sm text-gray-700">${type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                `;
                container.appendChild(label);
            });
            
            // Initialize transport filters
            const transportTypes = ['airport_transfer', 'taxi_chauffeur', 'car_hire', 'shuttle_bus', 'tour_bus', 'boat_ferry', 'motorbike_scooter'];
            const transportContainer = document.getElementById('transportFilters');
            
            transportTypes.forEach(type => {
                const label = document.createElement('label');
                label.className = 'flex items-center';
                label.innerHTML = `
                    <input type="radio" name="transport_type" value="${type}" class="mr-2">
                    <span class="text-sm text-gray-700">${type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                `;
                transportContainer.appendChild(label);
            });
            
            // Initialize experience filters
            const experienceTypes = ['tours', 'excursions', 'adventure_packages', 'wellness_retreats'];
            const experienceContainer = document.getElementById('experienceFilters');
            
            experienceTypes.forEach(type => {
                const label = document.createElement('label');
                label.className = 'flex items-center';
                label.innerHTML = `
                    <input type="radio" name="experience_type" value="${type}" class="mr-2">
                    <span class="text-sm text-gray-700">${type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</span>
                `;
                experienceContainer.appendChild(label);
            });
        });
    </script>
</body>
</html>
