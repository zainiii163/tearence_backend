<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Vehicles - WWA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .stats-card {
            transition: all 0.3s ease;
        }
        .stats-card:hover {
            transform: translateY(-2px);
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
                    <span class="ml-2 text-gray-600">My Vehicles</span>
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

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Vehicles</h1>
                    <p class="text-gray-600 mt-2">Manage your vehicle adverts</p>
                </div>
                <div class="flex space-x-4">
                    <button id="gridViewBtn" class="p-2 border border-gray-300 rounded-md hover:bg-gray-100 bg-indigo-600 text-white">
                        <i class="fas fa-th"></i>
                    </button>
                    <button id="listViewBtn" class="p-2 border border-gray-300 rounded-md hover:bg-gray-100">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stats-card bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <i class="fas fa-car text-indigo-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Vehicles</p>
                        <p class="text-2xl font-bold text-gray-900" id="totalVehicles">0</p>
                    </div>
                </div>
            </div>
            <div class="stats-card bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <i class="fas fa-eye text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Views</p>
                        <p class="text-2xl font-bold text-gray-900" id="totalViews">0</p>
                    </div>
                </div>
            </div>
            <div class="stats-card bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <i class="fas fa-heart text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Saves</p>
                        <p class="text-2xl font-bold text-gray-900" id="totalSaves">0</p>
                    </div>
                </div>
            </div>
            <div class="stats-card bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-full">
                        <i class="fas fa-envelope text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Enquiries</p>
                        <p class="text-2xl font-bold text-gray-900" id="totalEnquiries">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="flex space-x-4 border-b">
                <button class="filter-tab pb-4 px-2 border-b-2 border-indigo-600 text-indigo-600 font-medium" data-filter="all">
                    All Vehicles
                </button>
                <button class="filter-tab pb-4 px-2 border-b-2 border-transparent text-gray-600 hover:text-gray-900" data-filter="active">
                    Active
                </button>
                <button class="filter-tab pb-4 px-2 border-b-2 border-transparent text-gray-600 hover:text-gray-900" data-filter="pending">
                    Pending
                </button>
                <button class="filter-tab pb-4 px-2 border-b-2 border-transparent text-gray-600 hover:text-gray-900" data-filter="sold">
                    Sold
                </button>
                <button class="filter-tab pb-4 px-2 border-b-2 border-transparent text-gray-600 hover:text-gray-900" data-filter="expired">
                    Expired
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
            <p class="mt-4 text-gray-600">Loading your vehicles...</p>
        </div>

        <!-- Vehicles Grid/List -->
        <div id="vehiclesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Vehicles will be loaded here -->
        </div>

        <!-- Pagination -->
        <div id="pagination" class="mt-8 flex justify-center">
            <!-- Pagination will be loaded here -->
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full p-6 max-h-screen overflow-y-auto">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Edit Vehicle</h3>
            <form id="editForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" name="price" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Year</label>
                        <input type="number" name="year" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mileage</label>
                        <input type="number" name="mileage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="expired">Expired</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Active</label>
                        <div class="mt-2">
                            <input type="checkbox" name="is_active" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-600">Vehicle is active</span>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        Save Changes
                    </button>
                    <button type="button" onclick="hideEditModal()" class="flex-1 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentFilter = 'all';
        let currentView = 'grid';
        let editingVehicleId = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadVehicles();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Filter tabs
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    document.querySelectorAll('.filter-tab').forEach(t => {
                        t.classList.remove('border-indigo-600', 'text-indigo-600');
                        t.classList.add('border-transparent', 'text-gray-600');
                    });
                    this.classList.remove('border-transparent', 'text-gray-600');
                    this.classList.add('border-indigo-600', 'text-indigo-600');
                    
                    currentFilter = this.dataset.filter;
                    currentPage = 1;
                    loadVehicles();
                });
            });

            // View mode buttons
            document.getElementById('gridViewBtn').addEventListener('click', function() {
                currentView = 'grid';
                this.classList.add('bg-indigo-600', 'text-white');
                this.classList.remove('border-gray-300');
                document.getElementById('listViewBtn').classList.remove('bg-indigo-600', 'text-white');
                document.getElementById('listViewBtn').classList.add('border-gray-300');
                updateViewMode();
            });

            document.getElementById('listViewBtn').addEventListener('click', function() {
                currentView = 'list';
                this.classList.add('bg-indigo-600', 'text-white');
                this.classList.remove('border-gray-300');
                document.getElementById('gridViewBtn').classList.remove('bg-indigo-600', 'text-white');
                document.getElementById('gridViewBtn').classList.add('border-gray-300');
                updateViewMode();
            });

            // Edit form
            document.getElementById('editForm').addEventListener('submit', function(e) {
                e.preventDefault();
                saveVehicleChanges();
            });

            // Close modal when clicking outside
            document.getElementById('editModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    hideEditModal();
                }
            });
        }

        function updateViewMode() {
            const container = document.getElementById('vehiclesContainer');
            if (currentView === 'grid') {
                container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
            } else {
                container.className = 'space-y-4';
            }
        }

        async function loadVehicles(page = 1) {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('vehiclesContainer').innerHTML = '';
            
            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: 12
                });

                if (currentFilter !== 'all') {
                    params.append('status', currentFilter === 'active' ? 'approved' : currentFilter);
                }

                const response = await fetch(`/api/vehicles/my-vehicles?${params}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    displayVehicles(data.data);
                    updatePagination(data);
                    updateStatistics(data.data);
                } else {
                    throw new Error('Failed to load vehicles');
                }
            } catch (error) {
                console.error('Error loading vehicles:', error);
                document.getElementById('vehiclesContainer').innerHTML = '<p class="text-center text-gray-600 col-span-full">Error loading vehicles. Please try again.</p>';
            } finally {
                document.getElementById('loading').style.display = 'none';
            }
        }

        function displayVehicles(vehicles) {
            const container = document.getElementById('vehiclesContainer');
            
            if (vehicles.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-600 col-span-full">No vehicles found.</p>';
                return;
            }

            vehicles.forEach(vehicle => {
                const card = createVehicleCard(vehicle);
                container.appendChild(card);
            });
        }

        function createVehicleCard(vehicle) {
            const card = document.createElement('div');
            
            if (currentView === 'grid') {
                card.className = 'vehicle-card bg-white rounded-lg shadow-sm overflow-hidden';
                card.innerHTML = `
                    <div class="relative">
                        <img src="${vehicle.main_image_url || '/images/placeholder.png'}" alt="${vehicle.title}" class="w-full h-48 object-cover">
                        ${getStatusBadge(vehicle)}
                        <div class="absolute top-2 right-2">
                            <span class="bg-white px-2 py-1 rounded-full text-xs font-medium ${vehicle.is_active ? 'text-green-600' : 'text-red-600'}">
                                ${vehicle.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 text-lg mb-2">${vehicle.title}</h3>
                        <p class="text-sm text-gray-600 mb-2">${vehicle.make?.name} ${vehicle.vehicleModel?.name} • ${vehicle.year}</p>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xl font-bold text-indigo-600">${vehicle.display_price}</span>
                            <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded">${vehicle.advert_type}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-gray-500 mb-4">
                            <span><i class="fas fa-eye mr-1"></i> ${vehicle.views_count || 0}</span>
                            <span><i class="fas fa-heart mr-1"></i> ${vehicle.saves_count || 0}</span>
                            <span><i class="fas fa-envelope mr-1"></i> ${vehicle.enquiries_count || 0}</span>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="viewVehicle(${vehicle.id})" class="flex-1 bg-indigo-600 text-white px-3 py-2 rounded text-sm hover:bg-indigo-700 transition-colors">
                                View
                            </button>
                            <button onclick="editVehicle(${vehicle.id})" class="flex-1 bg-gray-200 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-300 transition-colors">
                                Edit
                            </button>
                            <button onclick="toggleStatus(${vehicle.id})" class="bg-${vehicle.is_active ? 'red' : 'green'}-600 text-white px-3 py-2 rounded text-sm hover:bg-${vehicle.is_active ? 'red' : 'green'}-700 transition-colors">
                                ${vehicle.is_active ? 'Hide' : 'Show'}
                            </button>
                        </div>
                    </div>
                `;
            } else {
                card.className = 'vehicle-card bg-white rounded-lg shadow-sm p-4 flex space-x-4';
                card.innerHTML = `
                    <img src="${vehicle.main_image_url || '/images/placeholder.png'}" alt="${vehicle.title}" class="w-32 h-32 object-cover rounded-lg">
                    <div class="flex-1">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-semibold text-gray-900 text-lg">${vehicle.title}</h3>
                            <div class="flex space-x-2">
                                ${getStatusBadge(vehicle)}
                                <span class="bg-white px-2 py-1 rounded-full text-xs font-medium ${vehicle.is_active ? 'text-green-600' : 'text-red-600'}">
                                    ${vehicle.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">${vehicle.make?.name} ${vehicle.vehicleModel?.name} • ${vehicle.year}</p>
                        <p class="text-sm text-gray-500 mb-3">${vehicle.description?.substring(0, 100)}...</p>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xl font-bold text-indigo-600">${vehicle.display_price}</span>
                            <div class="flex space-x-4 text-sm text-gray-500">
                                <span><i class="fas fa-eye mr-1"></i> ${vehicle.views_count || 0}</span>
                                <span><i class="fas fa-heart mr-1"></i> ${vehicle.saves_count || 0}</span>
                                <span><i class="fas fa-envelope mr-1"></i> ${vehicle.enquiries_count || 0}</span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="viewVehicle(${vehicle.id})" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 transition-colors">
                                View
                            </button>
                            <button onclick="editVehicle(${vehicle.id})" class="bg-gray-200 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-300 transition-colors">
                                Edit
                            </button>
                            <button onclick="toggleStatus(${vehicle.id})" class="bg-${vehicle.is_active ? 'red' : 'green'}-600 text-white px-4 py-2 rounded text-sm hover:bg-${vehicle.is_active ? 'red' : 'green'}-700 transition-colors">
                                ${vehicle.is_active ? 'Hide' : 'Show'}
                            </button>
                            <button onclick="deleteVehicle(${vehicle.id})" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700 transition-colors">
                                Delete
                            </button>
                        </div>
                    </div>
                `;
            }
            
            return card;
        }

        function getStatusBadge(vehicle) {
            const badges = [];
            
            if (vehicle.is_top_of_category) {
                badges.push('<span class="badge-top-category text-xs px-2 py-1 text-white rounded-full">Top</span>');
            } else if (vehicle.is_sponsored) {
                badges.push('<span class="badge-sponsored text-xs px-2 py-1 text-white rounded-full">Sponsored</span>');
            } else if (vehicle.is_featured) {
                badges.push('<span class="badge-featured text-xs px-2 py-1 text-white rounded-full">Featured</span>');
            } else if (vehicle.is_promoted) {
                badges.push('<span class="badge-promoted text-xs px-2 py-1 text-white rounded-full">Promoted</span>');
            }
            
            return badges.join(' ');
        }

        function updateStatistics(vehicles) {
            const stats = {
                total: vehicles.length,
                views: vehicles.reduce((sum, v) => sum + (v.views_count || 0), 0),
                saves: vehicles.reduce((sum, v) => sum + (v.saves_count || 0), 0),
                enquiries: vehicles.reduce((sum, v) => sum + (v.enquiries_count || 0), 0)
            };

            document.getElementById('totalVehicles').textContent = stats.total;
            document.getElementById('totalViews').textContent = stats.views.toLocaleString();
            document.getElementById('totalSaves').textContent = stats.saves.toLocaleString();
            document.getElementById('totalEnquiries').textContent = stats.enquiries.toLocaleString();
        }

        function updatePagination(data) {
            const pagination = document.getElementById('pagination');
            
            if (data.last_page <= 1) {
                pagination.innerHTML = '';
                return;
            }
            
            let html = '<div class="flex space-x-2">';
            
            if (data.prev_page_url) {
                html += `<button onclick="loadVehicles(${data.current_page - 1})" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-100">Previous</button>`;
            }
            
            for (let i = 1; i <= data.last_page; i++) {
                const active = i === data.current_page ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300';
                html += `<button onclick="loadVehicles(${i})" class="px-3 py-2 ${active} rounded-md hover:bg-gray-100">${i}</button>`;
            }
            
            if (data.next_page_url) {
                html += `<button onclick="loadVehicles(${data.current_page + 1})" class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-100">Next</button>`;
            }
            
            html += '</div>';
            pagination.innerHTML = html;
        }

        function viewVehicle(id) {
            window.location.href = `/vehicles/${id}`;
        }

        function editVehicle(id) {
            editingVehicleId = id;
            // Load vehicle data and populate form
            loadVehicleForEdit(id);
            document.getElementById('editModal').classList.remove('hidden');
        }

        async function loadVehicleForEdit(id) {
            try {
                const response = await fetch(`/api/vehicles/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    }
                });
                
                if (response.ok) {
                    const vehicle = await response.json();
                    const form = document.getElementById('editForm');
                    
                    form.title.value = vehicle.title;
                    form.price.value = vehicle.price;
                    form.year.value = vehicle.year;
                    form.mileage.value = vehicle.mileage || '';
                    form.status.value = vehicle.status;
                    form.is_active.checked = vehicle.is_active;
                    form.description.value = vehicle.description;
                }
            } catch (error) {
                console.error('Error loading vehicle data:', error);
            }
        }

        function hideEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            editingVehicleId = null;
        }

        async function saveVehicleChanges() {
            try {
                const form = document.getElementById('editForm');
                const formData = new FormData(form);
                
                const response = await fetch(`/api/vehicles/${editingVehicleId}`, {
                    method: 'PUT',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                        'Accept': 'application/json',
                    },
                    body: formData
                });
                
                if (response.ok) {
                    alert('Vehicle updated successfully!');
                    hideEditModal();
                    loadVehicles();
                } else {
                    const error = await response.json();
                    alert('Error updating vehicle: ' + error.message);
                }
            } catch (error) {
                console.error('Error updating vehicle:', error);
                alert('Error updating vehicle. Please try again.');
            }
        }

        async function toggleStatus(id) {
            try {
                const response = await fetch(`/api/vehicles/${id}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    }
                });
                
                if (response.ok) {
                    loadVehicles();
                } else {
                    alert('Error updating vehicle status');
                }
            } catch (error) {
                console.error('Error toggling status:', error);
                alert('Error updating vehicle status');
            }
        }

        async function deleteVehicle(id) {
            if (!confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/api/vehicles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('token')}`,
                    }
                });
                
                if (response.ok) {
                    alert('Vehicle deleted successfully!');
                    loadVehicles();
                } else {
                    alert('Error deleting vehicle');
                }
            } catch (error) {
                console.error('Error deleting vehicle:', error);
                alert('Error deleting vehicle');
            }
        }
    </script>
</body>
</html>
