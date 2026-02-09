<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WWA</title>
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
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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
        .featured-badge {
            background: linear-gradient(45deg, #9C27B0, #7B1FA2);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .priority-badge {
            background: linear-gradient(45deg, #2196F3, #1976D2);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .tab-active {
            border-bottom: 3px solid #4F46E5;
            color: #4F46E5;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-indigo-600">WWA</a>
                    <span class="ml-2 text-gray-600">Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/" class="text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <button onclick="showNotifications()" class="relative text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-bell"></i>
                        <span id="notificationBadge" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                    </button>
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center text-gray-700 hover:text-indigo-600">
                            <i class="fas fa-user-circle mr-2"></i>
                            <span id="userName">User</span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border hidden">
                            <a href="/profile" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i> Profile
                            </a>
                            <a href="/settings" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i> Settings
                            </a>
                            <a href="/kyc-submission" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-shield-check mr-2"></i> KYC Status
                            </a>
                            <hr class="my-2">
                            <button onclick="logout()" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- KYC Status Alert -->
    <div id="kycAlert" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>KYC Verification Required:</strong> You must complete KYC verification to post new ads. 
                        <a href="/kyc-submission" class="underline font-medium">Complete Verification</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-lg">
                        <i class="fas fa-ad text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Listings</p>
                        <p id="totalListings" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="fas fa-eye text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Views</p>
                        <p id="totalViews" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="fas fa-star text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Active Upsells</p>
                        <p id="activeUpsells" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 card-hover">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Spent</p>
                        <p id="totalSpent" class="text-2xl font-bold text-gray-900">$0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b">
                <nav class="flex space-x-8 px-6">
                    <button onclick="switchTab('listings')" id="listingsTab" class="py-4 px-1 border-b-2 font-medium text-sm tab-active">
                        <i class="fas fa-ad mr-2"></i> My Listings
                    </button>
                    <button onclick="switchTab('upsells')" id="upsellsTab" class="py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-star mr-2"></i> Upsells
                    </button>
                    <button onclick="switchTab('create')" id="createTab" class="py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-plus mr-2"></i> Create Listing
                    </button>
                    <button onclick="switchTab('analytics')" id="analyticsTab" class="py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                        <i class="fas fa-chart-line mr-2"></i> Analytics
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- My Listings Tab -->
                <div id="listingsContent" class="tab-content">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">My Listings</h3>
                        <div class="flex space-x-3">
                            <select id="listingFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                                <option value="expired">Expired</option>
                            </select>
                            <button onclick="refreshListings()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-sync mr-2"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div id="listingsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Listings will be populated here -->
                    </div>

                    <div id="listingsLoading" class="text-center py-12 hidden">
                        <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
                        <p class="mt-4 text-gray-600">Loading your listings...</p>
                    </div>
                </div>

                <!-- Upsells Tab -->
                <div id="upsellsContent" class="tab-content hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Upsell Options -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Available Upsells</h3>
                            <div id="upsellOptions" class="space-y-4">
                                <!-- Upsell options will be populated here -->
                            </div>
                        </div>

                        <!-- My Upsells -->
                        <div>
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">My Active Upsells</h3>
                                <button onclick="refreshUpsells()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    <i class="fas fa-sync mr-2"></i> Refresh
                                </button>
                            </div>
                            <div id="myUpsells" class="space-y-4">
                                <!-- User upsells will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Create Listing Tab -->
                <div id="createContent" class="tab-content hidden">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Create New Listing</h3>
                    <form id="createListingForm" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Listing Title *</label>
                                <input type="text" id="listingTitle" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                                <select id="listingCategory" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Select Category</option>
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                                <select id="listingLocation" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Select Location</option>
                                    <option value="1">New York</option>
                                    <option value="2">Los Angeles</option>
                                    <option value="3">London</option>
                                    <option value="4">Paris</option>
                                    <option value="5">Tokyo</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                                <input type="number" id="listingPrice" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                            <textarea id="listingDescription" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="resetCreateForm()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-plus mr-2"></i> Create Listing
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Analytics Tab -->
                <div id="analyticsContent" class="tab-content hidden">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Performance Analytics</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white border rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Listing Performance</h4>
                            <canvas id="performanceChart"></canvas>
                        </div>
                        <div class="bg-white border rounded-lg p-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Upsell ROI</h4>
                            <canvas id="roiChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upsell Purchase Modal -->
    <div id="upsellModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Purchase Upsell</h3>
            <form id="upsellPurchaseForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Listing</label>
                    <select id="upsellListing" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Listing</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (days)</label>
                    <select id="upsellDuration" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="7">7 days</option>
                        <option value="14">14 days</option>
                        <option value="30">30 days</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <select id="paymentMethod" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                        <option value="wallet">Wallet Balance</option>
                    </select>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Unit Price:</span>
                        <span id="unitPrice">$0.00</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600">Duration:</span>
                        <span id="durationText">7 days</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total:</span>
                        <span id="totalPrice">$0.00</span>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeUpsellModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Purchase Upsell
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- API Service -->
    <script src="/js/api-service.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize API service
        const api = new APIService();
        let currentTab = 'listings';
        let currentUser = null;
        let upsellOptions = {};
        let selectedUpsellType = null;

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', async () => {
            await initializeDashboard();
        });

        async function initializeDashboard() {
            try {
                currentUser = await api.getCurrentUser();
                if (!currentUser) {
                    window.location.href = '/login';
                    return;
                }

                document.getElementById('userName').textContent = currentUser.first_name || 'User';
                
                await loadDashboardData();
                await checkKYCStatus();
            } catch (error) {
                console.error('Dashboard initialization error:', error);
            }
        }

        async function loadDashboardData() {
            await Promise.all([
                loadListings(),
                loadUpsellOptions(),
                loadMyUpsells(),
                loadStats()
            ]);
        }

        async function loadListings() {
            try {
                document.getElementById('listingsLoading').classList.remove('hidden');
                const response = await api.getMyListings({ per_page: 100 });
                displayListings(response.data);
            } catch (error) {
                console.error('Error loading listings:', error);
            } finally {
                document.getElementById('listingsLoading').classList.add('hidden');
            }
        }

        function displayListings(listings) {
            const grid = document.getElementById('listingsGrid');
            
            if (!listings || listings.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-ad text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">You haven't created any listings yet.</p>
                        <button onclick="switchTab('create')" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Create Your First Listing
                        </button>
                    </div>
                `;
                return;
            }

            grid.innerHTML = listings.map(listing => createListingCard(listing)).join('');
        }

        function createListingCard(listing) {
            const badge = listing.upsell_type ? getUpsellBadge(listing.upsell_type) : '';
            const statusColor = getStatusColor(listing.status);
            
            return `
                <div class="bg-white border rounded-lg overflow-hidden card-hover">
                    <div class="relative">
                        <img src="https://via.placeholder.com/300x200" alt="${listing.title}" class="w-full h-48 object-cover">
                        ${badge}
                        <span class="absolute top-2 left-2 px-2 py-1 ${statusColor} text-white text-xs rounded-full">
                            ${listing.status}
                        </span>
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold text-gray-900 mb-2">${listing.title}</h4>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">${listing.description}</p>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-lg font-bold text-indigo-600">
                                ${listing.price ? `$${listing.price}` : 'Free'}
                            </span>
                            <span class="text-xs text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                ${formatDate(listing.created_at)}
                            </span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="editListing(${listing.id})" class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            <button onclick="promoteListing(${listing.id})" class="flex-1 px-3 py-2 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-sm">
                                <i class="fas fa-star mr-1"></i> Promote
                            </button>
                            <button onclick="deleteListing(${listing.id})" class="px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        async function loadUpsellOptions() {
            try {
                const response = await api.getUpsellOptions();
                upsellOptions = response.data;
                displayUpsellOptions();
            } catch (error) {
                console.error('Error loading upsell options:', error);
            }
        }

        function displayUpsellOptions() {
            const container = document.getElementById('upsellOptions');
            container.innerHTML = Object.entries(upsellOptions).map(([type, option]) => `
                <div class="border rounded-lg p-4 card-hover cursor-pointer" onclick="selectUpsell('${type}')">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-semibold text-gray-900">${option.name}</h4>
                        ${getUpsellBadge(type)}
                    </div>
                    <p class="text-sm text-gray-600 mb-3">${option.description}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-indigo-600">$${option.price_per_day}/day</span>
                        <button class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                            Purchase
                        </button>
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1">
                        ${option.features.map(feature => `
                            <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded">
                                ${feature}
                            </span>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        }

        async function loadMyUpsells() {
            try {
                const response = await api.getMyUpsells({ per_page: 100 });
                displayMyUpsells(response.data.upsells);
            } catch (error) {
                console.error('Error loading my upsells:', error);
            }
        }

        function displayMyUpsells(upsells) {
            const container = document.getElementById('myUpsells');
            
            if (!upsells || upsells.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-star text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600">No active upsells</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = upsells.map(upsell => `
                <div class="border rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-semibold text-gray-900">${upsell.listing_title}</h4>
                            <p class="text-sm text-gray-600">${getUpsellBadge(upsell.upsell_type)}</p>
                        </div>
                        <span class="text-lg font-bold text-indigo-600">$${upsell.total_cost}</span>
                    </div>
                    <div class="text-sm text-gray-600 mb-3">
                        <p>Duration: ${upsell.duration_days} days</p>
                        <p>Expires: ${formatDate(upsell.expires_at)}</p>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="px-2 py-1 ${upsell.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'} rounded text-xs">
                            ${upsell.is_active ? 'Active' : 'Expired'}
                        </span>
                        ${upsell.is_active ? `
                            <button onclick="cancelUpsell(${upsell.id})" class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                Cancel
                            </button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        async function loadStats() {
            try {
                const listingsResponse = await api.getMyListings({ per_page: 100 });
                const upsellsResponse = await api.getMyUpsells({ per_page: 100 });
                
                const listings = listingsResponse.data || [];
                const upsells = upsellsResponse.data?.upsells || [];
                const stats = upsellsResponse.data?.statistics || {};
                
                document.getElementById('totalListings').textContent = listings.length;
                document.getElementById('totalViews').textContent = listings.reduce((sum, listing) => sum + (listing.views || 0), 0);
                document.getElementById('activeUpsells').textContent = upsells.filter(u => u.is_active).length;
                document.getElementById('totalSpent').textContent = `$${stats.total_spent || 0}`;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        async function checkKYCStatus() {
            try {
                const user = await api.getCurrentUser();
                if (user && user.needs_kyc && !user.kyc_verified) {
                    document.getElementById('kycAlert').classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error checking KYC status:', error);
            }
        }

        // Tab switching
        function switchTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('[id$="Tab"]').forEach(tabBtn => {
                tabBtn.classList.remove('tab-active');
                tabBtn.classList.add('text-gray-500');
            });
            
            // Show selected tab
            document.getElementById(tab + 'Content').classList.remove('hidden');
            document.getElementById(tab + 'Tab').classList.add('tab-active');
            document.getElementById(tab + 'Tab').classList.remove('text-gray-500');
            
            currentTab = tab;
            
            // Load tab-specific data
            if (tab === 'analytics') {
                loadAnalytics();
            }
        }

        // Upsell functions
        function selectUpsell(type) {
            selectedUpsellType = type;
            openUpsellModal(type);
        }

        function openUpsellModal(type) {
            const modal = document.getElementById('upsellModal');
            const option = upsellOptions[type];
            
            document.getElementById('unitPrice').textContent = `$${option.price_per_day}`;
            updateUpsellTotal();
            
            // Load user listings for selection
            loadUserListingsForUpsell();
            
            modal.classList.remove('hidden');
        }

        async function loadUserListingsForUpsell() {
            try {
                const response = await api.getMyListings({ per_page: 100 });
                const select = document.getElementById('upsellListing');
                
                select.innerHTML = '<option value="">Select Listing</option>' +
                    response.data.map(listing => `
                        <option value="${listing.id}">${listing.title}</option>
                    `).join('');
            } catch (error) {
                console.error('Error loading listings for upsell:', error);
            }
        }

        function updateUpsellTotal() {
            const duration = parseInt(document.getElementById('upsellDuration').value);
            const unitPrice = upsellOptions[selectedUpsellType].price_per_day;
            const total = unitPrice * duration;
            
            document.getElementById('durationText').textContent = `${duration} days`;
            document.getElementById('totalPrice').textContent = `$${total.toFixed(2)}`;
        }

        function closeUpsellModal() {
            document.getElementById('upsellModal').classList.add('hidden');
            document.getElementById('upsellPurchaseForm').reset();
        }

        // Form handlers
        document.getElementById('createListingForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await createListing();
        });

        document.getElementById('upsellPurchaseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            await purchaseUpsell();
        });

        document.getElementById('upsellDuration').addEventListener('change', updateUpsellTotal);

        async function createListing() {
            const formData = {
                title: document.getElementById('listingTitle').value,
                category_id: document.getElementById('listingCategory').value,
                location_id: document.getElementById('listingLocation').value,
                price: document.getElementById('listingPrice').value,
                description: document.getElementById('listingDescription').value
            };

            try {
                await api.createListing(formData);
                alert('Listing created successfully!');
                resetCreateForm();
                switchTab('listings');
                await loadListings();
            } catch (error) {
                console.error('Error creating listing:', error);
                alert('Error creating listing. Please try again.');
            }
        }

        async function purchaseUpsell() {
            const formData = {
                listing_id: document.getElementById('upsellListing').value,
                upsell_type: selectedUpsellType,
                duration_days: document.getElementById('upsellDuration').value,
                payment_method: document.getElementById('paymentMethod').value
            };

            try {
                await api.purchaseUpsell(formData);
                alert('Upsell purchased successfully!');
                closeUpsellModal();
                await loadMyUpsells();
                await loadStats();
            } catch (error) {
                console.error('Error purchasing upsell:', error);
                alert('Error purchasing upsell. Please try again.');
            }
        }

        // Utility functions
        function getUpsellBadge(type) {
            const badges = {
                premium: '<span class="premium-badge">PREMIUM</span>',
                sponsored: '<span class="sponsored-badge">SPONSORED</span>',
                featured: '<span class="featured-badge">FEATURED</span>',
                priority: '<span class="priority-badge">PRIORITY</span>'
            };
            return badges[type] || '';
        }

        function getStatusColor(status) {
            const colors = {
                active: 'bg-green-500',
                pending: 'bg-yellow-500',
                expired: 'bg-red-500',
                draft: 'bg-gray-500'
            };
            return colors[status] || 'bg-gray-500';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }

        function resetCreateForm() {
            document.getElementById('createListingForm').reset();
        }

        async function refreshListings() {
            await loadListings();
        }

        async function refreshUpsells() {
            await loadMyUpsells();
        }

        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }

        async function logout() {
            try {
                await api.logout();
                window.location.href = '/login';
            } catch (error) {
                console.error('Logout error:', error);
                window.location.href = '/login';
            }
        }

        function editListing(id) {
            // Implement edit functionality
            console.log('Edit listing:', id);
        }

        function promoteListing(id) {
            // Implement promote functionality
            console.log('Promote listing:', id);
        }

        async function deleteListing(id) {
            if (confirm('Are you sure you want to delete this listing?')) {
                try {
                    await api.deleteListing(id);
                    alert('Listing deleted successfully!');
                    await loadListings();
                } catch (error) {
                    console.error('Error deleting listing:', error);
                    alert('Error deleting listing. Please try again.');
                }
            }
        }

        async function cancelUpsell(id) {
            if (confirm('Are you sure you want to cancel this upsell?')) {
                try {
                    await api.cancelUpsell(id);
                    alert('Upsell cancelled successfully!');
                    await loadMyUpsells();
                    await loadStats();
                } catch (error) {
                    console.error('Error cancelling upsell:', error);
                    alert('Error cancelling upsell. Please try again.');
                }
            }
        }

        function showNotifications() {
            // Implement notifications functionality
            console.log('Show notifications');
        }

        function loadAnalytics() {
            // Implement analytics charts
            console.log('Load analytics');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) {
                document.getElementById('userMenu').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
