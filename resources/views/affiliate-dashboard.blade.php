<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Affiliate Hub - WWA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">WWA</h1>
                    <div class="ml-8 flex space-x-8">
                        <a href="/user-dashboard" class="text-gray-700 hover:text-gray-900">My Ads</a>
                        <a href="/affiliate-dashboard" class="text-blue-600 font-medium">Affiliate Hub</a>
                        <a href="/profile" class="text-gray-700 hover:text-gray-900">Profile</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/affiliates" class="text-gray-700 hover:text-gray-900">
                        <i class="fas fa-store mr-1"></i>
                        Browse Affiliates
                    </a>
                    <button onclick="logout()" class="text-gray-700 hover:text-gray-900">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Affiliate Hub</h1>
                    <p class="text-gray-600">Manage your affiliate offers and posts</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="showBusinessOfferModal()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-briefcase mr-2"></i>
                        Create Business Offer
                    </button>
                    <button onclick="showUserPostModal()" 
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <i class="fas fa-users mr-2"></i>
                        Create User Post
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <i class="fas fa-briefcase text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Business Offers</p>
                        <p id="totalBusinessOffers" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <i class="fas fa-users text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">User Posts</p>
                        <p id="totalUserPosts" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <i class="fas fa-handshake text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Applications</p>
                        <p id="totalApplications" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                        <i class="fas fa-chart-line text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Clicks</p>
                        <p id="totalClicks" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Chart -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Performance Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Clicks vs Views</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Total Views</span>
                            <span id="totalViews" class="font-medium">0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Total Clicks</span>
                            <span id="totalClicksChart" class="font-medium">0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Click Rate</span>
                            <span id="clickRate" class="font-medium text-green-600">0%</span>
                        </div>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Top Performing Content</h4>
                    <div id="topContent" class="space-y-2">
                        <!-- Will be populated with top performing content -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white shadow rounded-lg">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="switchTab('business-offers')" id="business-offers-tab" 
                        class="tab-button border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Business Offers
                    </button>
                    <button onclick="switchTab('user-posts')" id="user-posts-tab" 
                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        User Posts
                    </button>
                    <button onclick="switchTab('applications')" id="applications-tab" 
                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        My Applications
                    </button>
                    <button onclick="switchTab('analytics')" id="analytics-tab" 
                        class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Analytics
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Business Offers Tab -->
                <div id="business-offers-content" class="tab-content">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Your Business Offers</h3>
                        <select id="businessStatusFilter" onchange="loadBusinessOffers()" 
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div id="businessOffersList" class="space-y-4">
                        <!-- Business offers will be loaded here -->
                    </div>
                </div>

                <!-- User Posts Tab -->
                <div id="user-posts-content" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Your User Posts</h3>
                        <select id="userStatusFilter" onchange="loadUserPosts()" 
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div id="userPostsList" class="space-y-4">
                        <!-- User posts will be loaded here -->
                    </div>
                </div>

                <!-- Applications Tab -->
                <div id="applications-content" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Your Applications</h3>
                        <select id="applicationStatusFilter" onchange="loadApplications()" 
                            class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div id="applicationsList" class="space-y-4">
                        <!-- Applications will be loaded here -->
                    </div>
                </div>

                <!-- Analytics Tab -->
                <div id="analytics-content" class="tab-content hidden">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Performance Analytics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Business Offers Performance</h4>
                            <div id="businessAnalytics" class="space-y-2">
                                <!-- Business analytics will be loaded here -->
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">User Posts Performance</h4>
                            <div id="userAnalytics" class="space-y-2">
                                <!-- User analytics will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Offer Modal -->
    <div id="businessOfferModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Create Business Offer</h3>
                <button onclick="hideBusinessOfferModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="businessOfferForm" class="space-y-4">
                <!-- Business offer form fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Business Name *</label>
                        <input type="text" name="business_name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter business name">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Product/Service Title *</label>
                        <input type="text" name="product_service_title" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter product or service title">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                    <input type="text" name="tagline" maxlength="80"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Short tagline (max 80 chars)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" required rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Describe your product or service"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                        <select name="affiliate_category_id" required id="businessCategorySelect"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select category</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                        <input type="text" name="country" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Country">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commission Type *</label>
                        <select name="commission_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed Amount</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Commission Rate *</label>
                        <input type="number" name="commission_rate" required step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cookie Duration (days) *</label>
                        <input type="number" name="cookie_duration" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="30">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tracking Link *</label>
                    <input type="url" name="tracking_link" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="https://example.com/track/12345">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Email *</label>
                    <input type="email" name="business_email" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="business@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Website URL</label>
                    <input type="url" name="website_url"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="https://example.com">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideBusinessOfferModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Create Business Offer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Post Modal -->
    <div id="userPostModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Create User Post</h3>
                <button onclick="hideUserPostModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="userPostForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                    <input type="text" name="title" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Enter post title">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" required rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Describe your affiliate content"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                        <select name="affiliate_category_id" required id="userCategorySelect"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select category</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Target Audience</label>
                        <input type="text" name="target_audience"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="e.g., beginners, experts">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Affiliate Link *</label>
                    <input type="url" name="affiliate_link" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="https://example.com/affiliate/12345">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Image URL *</label>
                    <input type="text" name="image" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="https://example.com/image.jpg">
                    <p class="text-xs text-gray-500 mt-1">Upload images first using the upload endpoint, then paste the URL here</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hashtags</label>
                    <input type="text" name="hashtags"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="#tech #review #affiliate (comma separated)">
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideUserPostModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Create User Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const API_BASE = window.location.origin + '/api/v1';
        const authToken = localStorage.getItem('auth_token');

        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardData();
            loadCategories();
        });

        async function loadDashboardData() {
            await Promise.all([
                loadBusinessOffers(),
                loadUserPosts(),
                loadApplications(),
                updateStats()
            ]);
        }

        async function loadBusinessOffers() {
            try {
                const statusFilter = document.getElementById('businessStatusFilter').value;
                const response = await fetch(`${API_BASE}/affiliates/my-business-offers?per_page=50${statusFilter ? '&status=' + statusFilter : ''}`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    displayBusinessOffers(data.data.data);
                }
            } catch (error) {
                console.error('Error loading business offers:', error);
            }
        }

        async function loadUserPosts() {
            try {
                const statusFilter = document.getElementById('userStatusFilter').value;
                const response = await fetch(`${API_BASE}/affiliates/my-user-posts?per_page=50${statusFilter ? '&status=' + statusFilter : ''}`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    displayUserPosts(data.data.data);
                }
            } catch (error) {
                console.error('Error loading user posts:', error);
            }
        }

        async function loadApplications() {
            try {
                const statusFilter = document.getElementById('applicationStatusFilter').value;
                const response = await fetch(`${API_BASE}/affiliates/my-applications?per_page=50${statusFilter ? '&status=' + statusFilter : ''}`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    displayApplications(data.data.data);
                }
            } catch (error) {
                console.error('Error loading applications:', error);
            }
        }

        function displayBusinessOffers(offers) {
            const container = document.getElementById('businessOffersList');
            
            if (offers.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-briefcase text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">You haven't created any business offers yet.</p>
                        <button onclick="showBusinessOfferModal()" 
                            class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Create Your First Business Offer
                        </button>
                    </div>
                `;
                return;
            }

            container.innerHTML = offers.map(offer => `
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900">${offer.product_service_title}</h4>
                            <p class="text-gray-600 mt-1">${offer.business_name}</p>
                            <p class="text-gray-500 mt-2">${offer.description.substring(0, 150)}...</p>
                            <div class="flex items-center mt-2 space-x-4 text-sm">
                                <span class="text-gray-500">
                                    <i class="fas fa-tag mr-1"></i>
                                    ${offer.affiliate_category?.name || 'Uncategorized'}
                                </span>
                                <span class="text-green-600 font-medium">
                                    <i class="fas fa-dollar-sign mr-1"></i>
                                    ${offer.display_commission} commission
                                </span>
                                <span class="text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    ${new Date(offer.created_at).toLocaleDateString()}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="mt-2">
                                ${getAffiliateStatusBadge(offer.status, offer.is_verified)}
                            </div>
                            <div class="mt-2 space-x-2">
                                <button onclick="editBusinessOffer(${offer.id})" 
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </button>
                                <button onclick="deleteBusinessOffer(${offer.id})" 
                                    class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash mr-1"></i>
                                    Delete
                                </button>
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                <i class="fas fa-eye mr-1"></i>${offer.views} 
                                <i class="fas fa-mouse-pointer ml-2 mr-1"></i>${offer.clicks}
                                <i class="fas fa-handshake ml-2 mr-1"></i>${offer.applications}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function displayUserPosts(posts) {
            const container = document.getElementById('userPostsList');
            
            if (posts.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">You haven't created any user posts yet.</p>
                        <button onclick="showUserPostModal()" 
                            class="mt-4 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            Create Your First User Post
                        </button>
                    </div>
                `;
                return;
            }

            container.innerHTML = posts.map(post => `
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900">${post.title}</h4>
                            <p class="text-gray-600 mt-1">${post.description.substring(0, 150)}...</p>
                            <div class="flex items-center mt-2 space-x-4 text-sm">
                                <span class="text-gray-500">
                                    <i class="fas fa-tag mr-1"></i>
                                    ${post.affiliate_category?.name || 'Uncategorized'}
                                </span>
                                <span class="text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    ${new Date(post.created_at).toLocaleDateString()}
                                </span>
                                ${post.target_audience ? `
                                    <span class="text-gray-500">
                                        <i class="fas fa-users mr-1"></i>
                                        ${post.target_audience}
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="mt-2">
                                ${getAffiliateStatusBadge(post.status)}
                            </div>
                            <div class="mt-2 space-x-2">
                                <button onclick="editUserPost(${post.id})" 
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-edit mr-1"></i>
                                    Edit
                                </button>
                                <button onclick="deleteUserPost(${post.id})" 
                                    class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash mr-1"></i>
                                    Delete
                                </button>
                            </div>
                            <div class="mt-2 text-sm text-gray-500">
                                <i class="fas fa-eye mr-1"></i>${post.views} 
                                <i class="fas fa-mouse-pointer ml-2 mr-1"></i>${post.clicks}
                                <i class="fas fa-share ml-2 mr-1"></i>${post.shares}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function displayApplications(applications) {
            const container = document.getElementById('applicationsList');
            
            if (applications.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-handshake text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-600">You haven't submitted any applications yet.</p>
                        <a href="/affiliates" 
                            class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Browse Affiliate Offers
                        </a>
                    </div>
                `;
                return;
            }

            container.innerHTML = applications.map(app => `
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900">${app.business_affiliate_offer?.product_service_title}</h4>
                            <p class="text-gray-600 mt-1">${app.business_affiliate_offer?.business_name}</p>
                            ${app.message ? `<p class="text-gray-500 mt-2">${app.message.substring(0, 150)}...</p>` : ''}
                            <div class="flex items-center mt-2 space-x-4 text-sm">
                                <span class="text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    ${new Date(app.created_at).toLocaleDateString()}
                                </span>
                                ${app.estimated_monthly_visitors ? `
                                    <span class="text-gray-500">
                                        <i class="fas fa-chart-line mr-1"></i>
                                        ${app.estimated_monthly_visitors.toLocaleString()} visitors/month
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="mt-2">
                                ${getAffiliateStatusBadge(app.status)}
                            </div>
                            <div class="mt-2 space-x-2">
                                <button onclick="viewApplication(${app.id})" 
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fas fa-eye mr-1"></i>
                                    View
                                </button>
                                ${app.status === 'pending' ? `
                                    <button onclick="withdrawApplication(${app.id})" 
                                        class="text-red-600 hover:text-red-800 text-sm">
                                        <i class="fas fa-times mr-1"></i>
                                        Withdraw
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function getAffiliateStatusBadge(status, isVerified = false) {
            const badges = {
                pending: '<span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>',
                approved: '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Approved</span>',
                rejected: '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Rejected</span>'
            };
            
            let badge = badges[status] || badges.pending;
            
            if (isVerified && status === 'approved') {
                badge += ' <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full ml-1">Verified</span>';
            }
            
            return badge;
        }

        async function updateStats() {
            try {
                const [businessResponse, userResponse, applicationsResponse] = await Promise.all([
                    fetch(`${API_BASE}/affiliates/my-business-offers?per_page=100`, {
                        headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
                    }),
                    fetch(`${API_BASE}/affiliates/my-user-posts?per_page=100`, {
                        headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
                    }),
                    fetch(`${API_BASE}/affiliates/my-applications?per_page=100`, {
                        headers: { 'Authorization': `Bearer ${authToken}`, 'Accept': 'application/json' }
                    })
                ]);

                const businessData = businessResponse.ok ? await businessResponse.json() : { data: { data: [] } };
                const userData = userResponse.ok ? await userResponse.json() : { data: { data: [] } };
                const applicationsData = applicationsResponse.ok ? await applicationsResponse.json() : { data: { data: [] } };

                const businessOffers = businessData.data.data || [];
                const userPosts = userData.data.data || [];
                const applications = applicationsData.data.data || [];

                // Update counts
                document.getElementById('totalBusinessOffers').textContent = businessOffers.length;
                document.getElementById('totalUserPosts').textContent = userPosts.length;
                document.getElementById('totalApplications').textContent = applications.length;

                // Calculate total clicks and views
                const totalClicks = [...businessOffers, ...userPosts].reduce((sum, item) => sum + (item.clicks || 0), 0);
                const totalViews = [...businessOffers, ...userPosts].reduce((sum, item) => sum + (item.views || 0), 0);

                document.getElementById('totalClicks').textContent = totalClicks.toLocaleString();
                document.getElementById('totalViews').textContent = totalViews.toLocaleString();
                document.getElementById('totalClicksChart').textContent = totalClicks.toLocaleString();

                // Calculate click rate
                const clickRate = totalViews > 0 ? ((totalClicks / totalViews) * 100).toFixed(2) : 0;
                document.getElementById('clickRate').textContent = clickRate + '%';

                // Update top performing content
                updateTopContent([...businessOffers, ...userPosts]);

            } catch (error) {
                console.error('Error updating stats:', error);
            }
        }

        function updateTopContent(allContent) {
            const topContent = allContent
                .sort((a, b) => (b.clicks || 0) - (a.clicks || 0))
                .slice(0, 3);

            const container = document.getElementById('topContent');
            
            if (topContent.length === 0) {
                container.innerHTML = '<p class="text-sm text-gray-500">No content available</p>';
                return;
            }

            container.innerHTML = topContent.map((content, index) => `
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-700">${index + 1}. ${content.product_service_title || content.title}</span>
                    <span class="font-medium text-green-600">${content.clicks || 0} clicks</span>
                </div>
            `).join('');
        }

        function switchTab(tabName) {
            // Hide all content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active state from all tabs
            document.querySelectorAll('.tab-button').forEach(tab => {
                tab.classList.remove('border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected content
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Add active state to selected tab
            const activeTab = document.getElementById(tabName + '-tab');
            activeTab.classList.remove('border-transparent', 'text-gray-500');
            activeTab.classList.add('border-blue-500', 'text-blue-600');
        }

        function showBusinessOfferModal() {
            document.getElementById('businessOfferModal').classList.remove('hidden');
        }

        function hideBusinessOfferModal() {
            document.getElementById('businessOfferModal').classList.add('hidden');
            document.getElementById('businessOfferForm').reset();
        }

        function showUserPostModal() {
            document.getElementById('userPostModal').classList.remove('hidden');
        }

        function hideUserPostModal() {
            document.getElementById('userPostModal').classList.add('hidden');
            document.getElementById('userPostForm').reset();
        }

        async function loadCategories() {
            try {
                const response = await fetch(`${API_BASE}/affiliates/categories`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    const data = await response.json();
                    const businessSelect = document.getElementById('businessCategorySelect');
                    const userSelect = document.getElementById('userCategorySelect');
                    
                    const options = '<option value="">Select category</option>' +
                        data.data.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
                    
                    businessSelect.innerHTML = options;
                    userSelect.innerHTML = options;
                }
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }

        // Handle business offer form submission
        document.getElementById('businessOfferForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            try {
                const response = await fetch(`${API_BASE}/affiliates/business-offers`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Business offer created successfully! It will be reviewed before going live.');
                    hideBusinessOfferModal();
                    loadBusinessOffers();
                    updateStats();
                } else {
                    alert('Error: ' + (data.message || 'Failed to create business offer'));
                }
            } catch (error) {
                alert('Error creating business offer: ' + error.message);
            } finally {
                submitBtn.disabled = false;
            }
        });

        // Handle user post form submission
        document.getElementById('userPostForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            // Process hashtags
            const hashtags = formData.get('hashtags');
            if (hashtags) {
                const hashtagArray = hashtags.split(',').map(tag => tag.trim().replace('#', ''));
                formData.set('hashtags', JSON.stringify(hashtagArray));
            }

            try {
                const response = await fetch(`${API_BASE}/affiliates/user-posts`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });

                const data = await response.json();

                if (response.ok) {
                    alert('User post created successfully! It will be reviewed before going live.');
                    hideUserPostModal();
                    loadUserPosts();
                    updateStats();
                } else {
                    alert('Error: ' + (data.message || 'Failed to create user post'));
                }
            } catch (error) {
                alert('Error creating user post: ' + error.message);
            } finally {
                submitBtn.disabled = false;
            }
        });

        function editBusinessOffer(id) {
            // Implementation for editing business offer
            alert('Edit functionality would be implemented here');
        }

        function editUserPost(id) {
            // Implementation for editing user post
            alert('Edit functionality would be implemented here');
        }

        async function deleteBusinessOffer(id) {
            if (confirm('Are you sure you want to delete this business offer? This action cannot be undone.')) {
                try {
                    const response = await fetch(`${API_BASE}/affiliates/business-offers/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${authToken}`,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        alert('Business offer deleted successfully');
                        loadBusinessOffers();
                        updateStats();
                    } else {
                        alert('Error deleting business offer');
                    }
                } catch (error) {
                    alert('Error deleting business offer: ' + error.message);
                }
            }
        }

        async function deleteUserPost(id) {
            if (confirm('Are you sure you want to delete this user post? This action cannot be undone.')) {
                try {
                    const response = await fetch(`${API_BASE}/affiliates/user-posts/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${authToken}`,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        alert('User post deleted successfully');
                        loadUserPosts();
                        updateStats();
                    } else {
                        alert('Error deleting user post');
                    }
                } catch (error) {
                    alert('Error deleting user post: ' + error.message);
                }
            }
        }

        function viewApplication(id) {
            // Implementation for viewing application details
            alert('View application functionality would be implemented here');
        }

        function withdrawApplication(id) {
            if (confirm('Are you sure you want to withdraw this application?')) {
                // Implementation for withdrawing application
                alert('Withdraw application functionality would be implemented here');
            }
        }

        function logout() {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
    </script>
</body>
</html>
