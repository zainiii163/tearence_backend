@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Job Alerts</h1>
            
            <!-- Create New Alert -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4">Create New Job Alert</h2>
                <form id="createAlertForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alert Title</label>
                            <input type="text" name="title" required 
                                   placeholder="e.g. Senior Developer Jobs in London"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select name="frequency" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="daily">Daily</option>
                                <option value="weekly" selected>Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                        <input type="text" name="keywords" 
                               placeholder="e.g. PHP, Laravel, JavaScript, React"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Separate keywords with commas</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="job_category_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <input type="text" name="country" 
                                   placeholder="e.g. United Kingdom"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Work Type</label>
                            <select name="work_type" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">All Types</option>
                                <option value="full_time">Full Time</option>
                                <option value="part_time">Part Time</option>
                                <option value="contract">Contract</option>
                                <option value="temporary">Temporary</option>
                                <option value="internship">Internship</option>
                                <option value="remote">Remote</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Salary Range</label>
                        <input type="text" name="salary_range" 
                               placeholder="e.g. $50,000 - $80,000"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" checked 
                               class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active" class="text-sm text-gray-700">Active</label>
                    </div>
                    
                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                            Create Alert
                        </button>
                        <button type="button" onclick="resetForm()" class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition duration-200">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Existing Alerts -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Your Job Alerts</h2>
                <div id="alertsList">
                    <!-- Alerts will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Alert Modal -->
<div id="editAlertModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all my-8 w-full max-w-2xl">
            <form id="editAlertForm">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-2xl font-bold text-gray-900">Edit Job Alert</h3>
                        <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <input type="hidden" name="alert_id" id="editAlertId">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alert Title</label>
                            <input type="text" name="title" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Frequency</label>
                            <select name="frequency" required 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                            <input type="text" name="keywords" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="job_category_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <input type="text" name="country" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="editIsActive" 
                                   class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="editIsActive" class="text-sm text-gray-700">Active</label>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Update Alert
                    </button>
                    <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadAlerts();
    
    // Event listeners
    document.getElementById('createAlertForm').addEventListener('submit', createAlert);
    document.getElementById('editAlertForm').addEventListener('submit', updateAlert);
});

// Load functions
async function loadCategories() {
    try {
        const response = await fetch('/api/public/jobs/categories');
        const data = await response.json();
        
        if (data.success) {
            const categorySelects = document.querySelectorAll('select[name="job_category_id"]');
            
            categorySelects.forEach(select => {
                data.data.forEach(category => {
                    const option = `<option value="${category.id}">${category.name}</option>`;
                    select.innerHTML += option;
                });
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

async function loadAlerts() {
    try {
        const response = await fetch('/api/jobs/alerts', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const alertsList = document.getElementById('alertsList');
            
            if (data.data.data.length === 0) {
                alertsList.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <div class="text-6xl mb-4">🔔</div>
                        <p>You haven't created any job alerts yet.</p>
                        <p class="text-sm">Create alerts above to get notified about new jobs that match your criteria.</p>
                    </div>
                `;
                return;
            }
            
            alertsList.innerHTML = '';
            
            data.data.data.forEach(alert => {
                const alertCard = createAlertCard(alert);
                alertsList.innerHTML += alertCard;
            });
        }
    } catch (error) {
        console.error('Error loading alerts:', error);
        document.getElementById('alertsList').innerHTML = `
            <div class="text-center py-8 text-red-500">
                <p>Error loading alerts. Please refresh the page.</p>
            </div>
        `;
    }
}

// Create alert card
function createAlertCard(alert) {
    const frequencyLabel = alert.frequency.charAt(0).toUpperCase() + alert.frequency.slice(1);
    const statusBadge = alert.is_active ? 
        '<span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Active</span>' :
        '<span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Inactive</span>';
    
    return `
        <div class="border rounded-lg p-4 mb-4 hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">${alert.title}</h3>
                    <p class="text-sm text-gray-600">Frequency: ${frequencyLabel}</p>
                </div>
                <div class="flex items-center gap-2">
                    ${statusBadge}
                    <div class="flex gap-2">
                        <button onclick="testAlert(${alert.id})" class="text-blue-600 hover:text-blue-800" title="Test Alert">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </button>
                        <button onclick="editAlert(${alert.id})" class="text-gray-600 hover:text-gray-800" title="Edit">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="deleteAlert(${alert.id})" class="text-red-600 hover:text-red-800" title="Delete">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="space-y-2 text-sm">
                ${alert.keywords ? `<p><strong>Keywords:</strong> ${alert.keywords}</p>` : ''}
                ${alert.job_category ? `<p><strong>Category:</strong> ${alert.job_category.name}</p>` : ''}
                ${alert.country ? `<p><strong>Location:</strong> ${alert.country}</p>` : ''}
                ${alert.work_type ? `<p><strong>Work Type:</strong> ${alert.work_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>` : ''}
                ${alert.salary_range ? `<p><strong>Salary:</strong> ${alert.salary_range}</p>` : ''}
                ${alert.last_sent_at ? `<p><strong>Last Sent:</strong> ${new Date(alert.last_sent_at).toLocaleDateString()}</p>` : '<p><strong>Last Sent:</strong> Never</p>'}
            </div>
        </div>
    `;
}

// CRUD operations
async function createAlert(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const alertData = Object.fromEntries(formData);
    
    try {
        const response = await fetch('/api/jobs/alerts', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(alertData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Job alert created successfully!');
            e.target.reset();
            loadAlerts();
        } else {
            alert(data.message || 'Error creating alert');
        }
    } catch (error) {
        console.error('Error creating alert:', error);
        alert('Error creating alert');
    }
}

async function updateAlert(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const alertData = Object.fromEntries(formData);
    const alertId = alertData.alert_id;
    delete alertData.alert_id;
    
    try {
        const response = await fetch(`/api/jobs/alerts/${alertId}`, {
            method: 'PUT',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(alertData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Job alert updated successfully!');
            closeEditModal();
            loadAlerts();
        } else {
            alert(data.message || 'Error updating alert');
        }
    } catch (error) {
        console.error('Error updating alert:', error);
        alert('Error updating alert');
    }
}

async function deleteAlert(alertId) {
    if (!confirm('Are you sure you want to delete this job alert?')) {
        return;
    }
    
    try {
        const response = await fetch(`/api/jobs/alerts/${alertId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Job alert deleted successfully!');
            loadAlerts();
        } else {
            alert(data.message || 'Error deleting alert');
        }
    } catch (error) {
        console.error('Error deleting alert:', error);
        alert('Error deleting alert');
    }
}

async function testAlert(alertId) {
    try {
        const response = await fetch(`/api/jobs/alerts/${alertId}/test`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Test alert generated! Found ${data.data.total_matches} matching jobs.`);
        } else {
            alert(data.message || 'Error testing alert');
        }
    } catch (error) {
        console.error('Error testing alert:', error);
        alert('Error testing alert');
    }
}

async function editAlert(alertId) {
    try {
        const response = await fetch(`/api/jobs/alerts/${alertId}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const alert = data.data;
            
            // Populate form
            document.getElementById('editAlertId').value = alert.id;
            document.querySelector('#editAlertForm input[name="title"]').value = alert.title;
            document.querySelector('#editAlertForm select[name="frequency"]').value = alert.frequency;
            document.querySelector('#editAlertForm input[name="keywords"]').value = alert.keywords || '';
            document.querySelector('#editAlertForm select[name="job_category_id"]').value = alert.job_category_id || '';
            document.querySelector('#editAlertForm input[name="country"]').value = alert.country || '';
            document.getElementById('editIsActive').checked = alert.is_active;
            
            // Show modal
            document.getElementById('editAlertModal').classList.remove('hidden');
        } else {
            alert(data.message || 'Error loading alert');
        }
    } catch (error) {
        console.error('Error loading alert:', error);
        alert('Error loading alert');
    }
}

function closeEditModal() {
    document.getElementById('editAlertModal').classList.add('hidden');
    document.getElementById('editAlertForm').reset();
}

function resetForm() {
    document.getElementById('createAlertForm').reset();
}
</script>
@endsection
