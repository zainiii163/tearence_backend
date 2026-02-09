<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldWideAdverts - Global Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .premium-badge {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .sponsored-badge {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-indigo-600">WWA</h1>
                    <span class="ml-2 text-gray-600">WorldWideAdverts</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-700 hover:text-indigo-600 font-medium">Login</a>
                    <a href="/register" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Register</a>
                    <a href="/dashboard" class="text-gray-700 hover:text-indigo-600 font-medium">
                        <i class="fas fa-dashboard mr-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Search -->
    <section class="relative bg-gradient-to-r from-blue-600 to-purple-700 text-white py-12 md:py-20 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6">
                    Find Everything You Need
                </h1>
                <p class="text-lg sm:text-xl md:text-2xl text-blue-100 mb-6 md:mb-8 max-w-3xl mx-auto">
                    Jobs, Services, Products & More - All in One Place
                </p>
            </div>

            <!-- Advanced Search -->
            <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-4xl mx-auto">
                <form id="searchForm" class="space-y-4">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">What are you looking for?</label>
                            <div class="relative">
                                <input type="text" id="searchQuery" placeholder="Search jobs, services, products..." 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="categoryFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                                <option value="">All Categories</option>
                                <option value="1">Buy and Sell</option>
                                <option value="2">Hotel, Resorts & Travel</option>
                                <option value="3">Property & Real Estate</option>
                                <option value="4">Books</option>
                                <option value="5">Funding</option>
                                <option value="6">Charities and Donations</option>
                                <option value="7">Jobs and Vacancies</option>
                                <option value="8">Services</option>
                                <option value="9">Business and Stores</option>
                                <option value="10">Affiliate Programs</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <select id="locationFilter" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                                <option value="">All Locations</option>
                                <option value="1">New York</option>
                                <option value="2">Los Angeles</option>
                                <option value="3">London</option>
                                <option value="4">Paris</option>
                                <option value="5">Tokyo</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                                <input type="number" id="minPrice" placeholder="Min" 
                                    class="w-1/2 px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                                <input type="number" id="maxPrice" placeholder="Max" 
                                    class="w-1/2 px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" class="flex-1 sm:flex-none bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            <i class="fas fa-search mr-2"></i>
                            <span class="hidden sm:inline">Search</span>
                            <span class="sm:hidden">Go</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 md:mb-8 text-center">Popular Categories</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
                <div class="text-center card-hover cursor-pointer" onclick="filterByCategory(1)">
                    <div class="bg-indigo-100 rounded-lg p-6 mb-3">
                        <i class="fas fa-shopping-cart text-3xl text-indigo-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Buy & Sell</h3>
                    <p class="text-sm text-gray-600">2,341 items</p>
                </div>
                <div class="text-center card-hover cursor-pointer" onclick="filterByCategory(2)">
                    <div class="bg-green-100 rounded-lg p-6 mb-3">
                        <i class="fas fa-hotel text-3xl text-green-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Travel</h3>
                    <p class="text-sm text-gray-600">856 listings</p>
                </div>
                <div class="text-center card-hover cursor-pointer" onclick="filterByCategory(3)">
                    <div class="bg-blue-100 rounded-lg p-6 mb-3">
                        <i class="fas fa-home text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Real Estate</h3>
                    <p class="text-sm text-gray-600">1,234 properties</p>
                </div>
                <div class="text-center card-hover cursor-pointer" onclick="filterByCategory(4)">
                    <div class="bg-purple-100 rounded-lg p-6 mb-3">
                        <i class="fas fa-book text-3xl text-purple-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Books</h3>
                    <p class="text-sm text-gray-600">567 books</p>
                </div>
                <div class="text-center card-hover cursor-pointer" onclick="filterByCategory(7)">
                    <div class="bg-red-100 rounded-lg p-6 mb-3">
                        <i class="fas fa-briefcase text-3xl text-red-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Jobs</h3>
                    <p class="text-sm text-gray-600">3,456 jobs</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Results Section -->
    <section id="search-results" class="py-8 md:py-12 hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Search Results</h2>
                <div class="flex items-center space-x-4">
                    <select id="sortBy" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="priority">Sort by Priority</option>
                        <option value="newest">Newest First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                    </select>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="text-center py-12 hidden">
                <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
                <p class="mt-4 text-gray-600">Searching listings...</p>
            </div>

            <!-- Results Grid -->
            <div id="resultsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Results will be populated here -->
            </div>

            <!-- Pagination -->
            <div id="pagination" class="mt-8 flex justify-center">
                <!-- Pagination will be populated here -->
            </div>
        </div>
    </section>

    <!-- Premium Listings Section -->
    <section class="py-8 md:py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3 md:mb-4">Premium Listings</h2>
                <p class="text-lg text-gray-600">Get maximum visibility with our premium promotion options</p>
            </div>
            <div id="premiumListings" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Premium listings will be populated here -->
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 md:gap-8">
                <div>
                    <h3 class="text-xl md:text-2xl font-bold">WWA</h3>
                    <p class="text-gray-300 text-sm md:text-base mb-4 md:mb-6">Your global marketplace for jobs, services, and products.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-3 md:mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Buy & Sell</a></li>
                        <li><a href="#" class="hover:text-white">Real Estate</a></li>
                        <li><a href="#" class="hover:text-white">Jobs</a></li>
                        <li><a href="#" class="hover:text-white">Services</a></li>
                        <li><a href="#" class="hover:text-white">Terms</a></li>
                        <li><a href="#" class="hover:text-white">Privacy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-3 md:mb-4">Contact</h4>
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400">
                <p>&copy; 2024 WorldWideAdverts. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- API Service -->
    <script src="/js/api-service.js"></script>
    <script>
        // Initialize API service
        const api = new APIService();

        // Search functionality
        document.getElementById('searchForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await performSearch();
        });

        // Sort functionality
        document.getElementById('sortBy').addEventListener('change', async () => {
            await performSearch();
        });

        async function performSearch() {
            const query = document.getElementById('searchQuery').value;
            const categoryId = document.getElementById('categoryFilter').value;
            const locationId = document.getElementById('locationFilter').value;
            const minPrice = document.getElementById('minPrice').value;
            const maxPrice = document.getElementById('maxPrice').value;
            const sortBy = document.getElementById('sortBy').value;

            // Show loading state
            document.getElementById('search-results').classList.remove('hidden');
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('resultsGrid').innerHTML = '';

            try {
                const results = await api.searchListings({
                    q: query,
                    category_id: categoryId,
                    location_id: locationId,
                    min_price: minPrice,
                    max_price: maxPrice,
                    sort_by: sortBy,
                    page: 1,
                    per_page: 12
                });

                displaySearchResults(results);
            } catch (error) {
                console.error('Search error:', error);
                document.getElementById('resultsGrid').innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                        <p class="text-gray-600">Error loading search results. Please try again.</p>
                    </div>
                `;
            } finally {
                document.getElementById('loadingState').classList.add('hidden');
            }
        }

        function displaySearchResults(results) {
            const grid = document.getElementById('resultsGrid');
            
            if (!results.data || !results.data.data || results.data.data.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">No listings found matching your criteria.</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = results.data.data.map(listing => createListingCard(listing)).join('');
            displayPagination(results.data);
        }

        function createListingCard(listing) {
            const badge = listing.upsell_type ? getUpsellBadge(listing.upsell_type) : '';
            const price = listing.price ? `$${listing.price}` : 'Free';
            
            return `
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white rounded-lg shadow-xl p-4 sm:p-6 md:p-8 card-hover cursor-pointer" onclick="viewListing('${listing.slug}')">
                        <div class="relative">
                            <img src="https://via.placeholder.com/300x200" alt="${listing.title}" class="w-full h-48 object-cover rounded-t-lg">
                            ${badge}
                        </div>
                        <div class="p-4 md:p-6">
                            <h3 class="font-semibold text-lg text-gray-900 mb-2">${listing.title}</h3>
                            <p class="text-sm text-gray-600 line-clamp-2">${listing.description}</p>
                            <div class="flex justify-between items-center">
                                <span class="text-sm md:text-base font-bold text-blue-600">${price}</span>
                                <span class="text-xs md:text-sm text-gray-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    ${listing.location || 'Online'}
                                </span>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <p class="text-xs md:text-sm text-gray-600 line-clamp-2">
                                    <i class="fas fa-clock mr-1"></i>
                                    ${formatDate(listing.created_at)}
                                </p>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                    ${listing.status}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function getUpsellBadge(type) {
            const badges = {
                premium: '<span class="premium-badge absolute top-2 right-2">PREMIUM</span>',
                sponsored: '<span class="sponsored-badge absolute top-2 right-2">SPONSORED</span>',
                featured: '<span class="absolute top-2 right-2 bg-purple-600 text-white px-2 py-1 rounded text-xs font-bold">FEATURED</span>',
                priority: '<span class="absolute top-2 right-2 bg-blue-600 text-white px-2 py-1 rounded text-xs font-bold">PRIORITY</span>'
            };
            return badges[type] || '';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }

        function filterByCategory(categoryId) {
            document.getElementById('categoryFilter').value = categoryId;
            performSearch();
        }

        function displayPagination(paginationData) {
            const pagination = document.getElementById('pagination');
            
            if (!paginationData || paginationData.last_page <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let paginationHTML = '<div class="flex space-x-2">';
            
            // Previous button
            if (paginationData.prev_page_url) {
                paginationHTML += `
                    <button onclick="goToPage(${paginationData.current_page - 1})" 
                        class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                `;
            }

            // Page numbers
            for (let i = 1; i <= paginationData.last_page; i++) {
                if (i === paginationData.current_page) {
                    paginationHTML += `
                        <button class="px-3 py-2 bg-indigo-600 text-white rounded-md">
                            ${i}
                        </button>
                    `;
                } else {
                    paginationHTML += `
                        <button onclick="goToPage(${i})" 
                            class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            ${i}
                        </button>
                    `;
                }
            }

            // Next button
            if (paginationData.next_page_url) {
                paginationHTML += `
                    <button onclick="goToPage(${paginationData.current_page + 1})" 
                        class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                `;
            }

            paginationHTML += '</div>';
            pagination.innerHTML = paginationHTML;
        }

        function goToPage(page) {
            // Update search with new page
            const query = document.getElementById('searchQuery').value;
            const categoryId = document.getElementById('categoryFilter').value;
            const locationId = document.getElementById('locationFilter').value;
            const minPrice = document.getElementById('minPrice').value;
            const maxPrice = document.getElementById('maxPrice').value;
            const sortBy = document.getElementById('sortBy').value;

            // Show loading state
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('resultsGrid').innerHTML = '';

            api.searchListings({
                q: query,
                category_id: categoryId,
                location_id: locationId,
                min_price: minPrice,
                max_price: maxPrice,
                sort_by: sortBy,
                page: page,
                per_page: 12
            }).then(results => {
                displaySearchResults(results);
            }).catch(error => {
                console.error('Search error:', error);
                document.getElementById('resultsGrid').innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                        <p class="text-gray-600">Error loading search results. Please try again.</p>
                    </div>
                `;
            }).finally(() => {
                document.getElementById('loadingState').classList.add('hidden');
            });
        }

        function viewListing(slug) {
            window.location.href = `/listing/${slug}`;
        }

        // Load premium listings on page load
        async function loadPremiumListings() {
            try {
                const results = await api.searchListings({
                    upsell_type: 'premium',
                    page: 1,
                    per_page: 6
                });

                const container = document.getElementById('premiumListings');
                if (results.data && results.data.data && results.data.data.length > 0) {
                    container.innerHTML = results.data.data.map(listing => createListingCard(listing)).join('');
                } else {
                    container.innerHTML = `
                        <div class="col-span-full text-center py-12">
                            <i class="fas fa-star text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">No premium listings available at the moment.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading premium listings:', error);
                const container = document.getElementById('premiumListings');
                container.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                        <p class="text-gray-600">Error loading premium listings. Please try again.</p>
                    </div>
                `;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadPremiumListings();
        });
    </script>
</body>
</html>
