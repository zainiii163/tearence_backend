<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ads - WWA</title>
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
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/kyc-submission" class="text-gray-700 hover:text-gray-900">
                        <i class="fas fa-shield-check mr-1"></i>
                        KYC Status
                    </a>
                    <button onclick="logout()" class="text-gray-700 hover:text-gray-900">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- KYC Status Alert -->
        <div id="kycAlert" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 hidden">
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

        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Ads</h1>
                    <p class="text-gray-600">Manage your classified advertisements</p>
                </div>
                <button onclick="showCreateModal()" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-plus mr-2"></i>
                    Post New Ad
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                        <i class="fas fa-ad text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Ads</p>
                        <p id="totalAds" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Approved</p>
                        <p id="approvedAds" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p id="pendingAds" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Rejected</p>
                        <p id="rejectedAds" class="text-2xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ads List -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Your Listings</h3>
                    <select id="statusFilter" onchange="loadAds()" 
                        class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div id="adsList" class="space-y-4">
                    <!-- Ads will be loaded here -->
                </div>

                <!-- Loading -->
                <div id="loadingSpinner" class="text-center py-8 hidden">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="text-gray-600 mt-2">Loading your ads...</p>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-8 hidden">
                    <i class="fas fa-ad text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-600">You haven't posted any ads yet.</p>
                    <button onclick="showCreateModal()" 
                        class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Post Your First Ad
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Ad Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Post New Ad</h3>
                <button onclick="hideCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="createAdForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                    <input type="text" name="title" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter ad title">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" required rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Describe your item or service"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                        <input type="number" name="price" required step="0.01"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                        <select name="category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select category</option>
                            <!-- Categories will be loaded here -->
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="hideCreateModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const API_BASE = window.location.origin + '/api/v1';
        const authToken = localStorage.getItem('auth_token');

        document.addEventListener('DOMContentLoaded', function() {
            checkKycStatus();
            loadAds();
            loadCategories();
        });

        async function checkKycStatus() {
            try {
                const response = await fetch(`${API_BASE}/kyc/status`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const kycAlert = document.getElementById('kycAlert');
                    
                    if (!data.data.is_verified) {
                        kycAlert.classList.remove('hidden');
                    }
                }
            } catch (error) {
                console.error('Error checking KYC status:', error);
            }
        }

        async function loadAds() {
            const loadingSpinner = document.getElementById('loadingSpinner');
            const adsList = document.getElementById('adsList');
            const emptyState = document.getElementById('emptyState');
            const statusFilter = document.getElementById('statusFilter').value;

            loadingSpinner.classList.remove('hidden');
            adsList.classList.add('hidden');
            emptyState.classList.add('hidden');

            try {
                const response = await fetch(`${API_BASE}/listing?status=${statusFilter}`, {
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    displayAds(data.data);
                    updateStats(data.data);
                }
            } catch (error) {
                console.error('Error loading ads:', error);
            } finally {
                loadingSpinner.classList.add('hidden');
            }
        }

        function displayAds(ads) {
            const adsList = document.getElementById('adsList');
            const emptyState = document.getElementById('emptyState');

            if (ads.length === 0) {
                adsList.classList.add('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            adsList.innerHTML = ads.map(ad => `
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900">${ad.title}</h4>
                            <p class="text-gray-600 mt-1">${ad.description.substring(0, 150)}...</p>
                            <div class="flex items-center mt-2 space-x-4 text-sm">
                                <span class="text-gray-500">
                                    <i class="fas fa-tag mr-1"></i>
                                    ${ad.category?.name || 'Uncategorized'}
                                </span>
                                <span class="text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    ${new Date(ad.created_at).toLocaleDateString()}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="text-2xl font-bold text-gray-900">$${ad.price}</div>
                            <div class="mt-2">
                                ${getStatusBadge(ad.approval_status)}
                            </div>
                            <div class="mt-2 space-x-2">
                                ${getActionButtons(ad)}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            adsList.classList.remove('hidden');
            emptyState.classList.add('hidden');
        }

        function getStatusBadge(status) {
            const badges = {
                pending: '<span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending Review</span>',
                approved: '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Approved</span>',
                rejected: '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Rejected</span>'
            };
            return badges[status] || badges.pending;
        }

        function getActionButtons(ad) {
            let buttons = '';
            
            if (ad.approval_status === 'rejected') {
                buttons += `
                    <button onclick="repostAd(${ad.listing_id})" 
                        class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-redo mr-1"></i>
                        Repost
                    </button>
                `;
            }
            
            buttons += `
                <button onclick="editAd(${ad.listing_id})" 
                    class="text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-edit mr-1"></i>
                    Edit
                </button>
            `;
            
            return buttons;
        }

        function updateStats(ads) {
            const stats = {
                total: ads.length,
                approved: ads.filter(ad => ad.approval_status === 'approved').length,
                pending: ads.filter(ad => ad.approval_status === 'pending').length,
                rejected: ads.filter(ad => ad.approval_status === 'rejected').length
            };

            document.getElementById('totalAds').textContent = stats.total;
            document.getElementById('approvedAds').textContent = stats.approved;
            document.getElementById('pendingAds').textContent = stats.pending;
            document.getElementById('rejectedAds').textContent = stats.rejected;
        }

        function showCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function hideCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        async function repostAd(adId) {
            if (confirm('This will update the ad date and require re-approval. Continue?')) {
                try {
                    const response = await fetch(`${API_BASE}/ads/${adId}/repost`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${authToken}`,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        alert('Ad reposted successfully! It will be reviewed again.');
                        loadAds();
                    }
                } catch (error) {
                    alert('Error reposting ad: ' + error.message);
                }
            }
        }

        function editAd(adId) {
            // Implementation for editing ad
            alert('Edit functionality would be implemented here');
        }

        async function loadCategories() {
            try {
                const response = await fetch(`${API_BASE}/category`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    const categorySelect = document.querySelector('select[name="category_id"]');
                    
                    categorySelect.innerHTML = '<option value="">Select category</option>' +
                        data.data.map(cat => `<option value="${cat.category_id}">${cat.name}</option>`).join('');
                }
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }

        // Handle create ad form submission
        document.getElementById('createAdForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;

            try {
                const response = await fetch(`${API_BASE}/listing`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });

                const data = await response.json();

                if (response.ok) {
                    alert('Ad submitted successfully! It will be reviewed before going live.');
                    hideCreateModal();
                    e.target.reset();
                    loadAds();
                } else {
                    alert('Error: ' + (data.message || 'Failed to submit ad'));
                }
            } catch (error) {
                alert('Error submitting ad: ' + error.message);
            } finally {
                submitBtn.disabled = false;
            }
        });

        function logout() {
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        }
    </script>
</body>
</html>
