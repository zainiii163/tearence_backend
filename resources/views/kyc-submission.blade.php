<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Verification - WWA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-shield-check text-blue-600 mr-2"></i>
                    KYC Verification
                </h1>
                <p class="text-gray-600">
                    Complete your identity verification to access all platform features
                </p>
            </div>

            <!-- Status Card -->
            <div id="statusCard" class="bg-white rounded-lg shadow-md p-6 mb-8 hidden">
                <div class="flex items-center">
                    <div id="statusIcon" class="text-4xl mr-4"></div>
                    <div>
                        <h3 id="statusTitle" class="text-lg font-semibold"></h3>
                        <p id="statusMessage" class="text-gray-600"></p>
                    </div>
                </div>
            </div>

            <!-- KYC Form -->
            <div id="kycForm" class="bg-white rounded-lg shadow-md p-8">
                <form id="kycSubmissionForm" class="space-y-6">
                    <!-- Personal Information -->
                    <div class="border-b pb-6">
                        <h2 class="text-xl font-semibold mb-4 text-gray-900">
                            <i class="fas fa-user mr-2 text-blue-600"></i>
                            Personal Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    ID Document Type *
                                </label>
                                <select name="id_document_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Select document type</option>
                                    <option value="passport">Passport</option>
                                    <option value="national_id">National ID Card</option>
                                    <option value="driver_license">Driver's License</option>
                                    <option value="residence_permit">Residence Permit</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    ID Document Number *
                                </label>
                                <input type="text" name="id_document_number" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Enter document number">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number *
                                </label>
                                <input type="tel" name="phone_number" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="+1234567890">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Date of Birth *
                                </label>
                                <input type="date" name="date_of_birth" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nationality *
                                </label>
                                <input type="text" name="nationality" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Enter your nationality">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Occupation *
                                </label>
                                <input type="text" name="occupation" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Enter your occupation">
                            </div>
                        </div>
                    </div>

                    <!-- Document Uploads -->
                    <div>
                        <h2 class="text-xl font-semibold mb-4 text-gray-900">
                            <i class="fas fa-file-upload mr-2 text-blue-600"></i>
                            Document Uploads
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    ID Document (Front) *
                                </label>
                                <input type="file" name="id_document" required accept="image/*,.pdf"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">
                                    Accepted formats: JPG, PNG, PDF (Max 5MB)
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Photo with ID *
                                </label>
                                <input type="file" name="photo_with_id" required accept="image/*"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">
                                    Clear photo of you holding your ID document
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Address Proof *
                                </label>
                                <input type="file" name="address_proof_document" required accept="image/*,.pdf"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-500 mt-1">
                                    Utility bill, bank statement, or official correspondence (Max 5MB)
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center">
                        <button type="submit" id="submitBtn"
                            class="bg-blue-600 text-white px-8 py-3 rounded-md font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Submit KYC Documents
                        </button>
                    </div>
                </form>
            </div>

            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg p-8 text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-700">Processing your KYC submission...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = window.location.origin + '/api/v1';
        const authToken = localStorage.getItem('auth_token');

        // Check current KYC status on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkKycStatus();
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
                    updateStatusDisplay(data.data);
                }
            } catch (error) {
                console.error('Error checking KYC status:', error);
            }
        }

        function updateStatusDisplay(kycData) {
            const statusCard = document.getElementById('statusCard');
            const kycForm = document.getElementById('kycForm');
            const statusIcon = document.getElementById('statusIcon');
            const statusTitle = document.getElementById('statusTitle');
            const statusMessage = document.getElementById('statusMessage');

            if (kycData.kyc_status === 'verified') {
                statusCard.classList.remove('hidden');
                kycForm.classList.add('hidden');
                statusIcon.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
                statusTitle.textContent = 'KYC Verified';
                statusMessage.textContent = 'Your identity has been verified. You have full access to all platform features.';
            } else if (kycData.kyc_status === 'rejected') {
                statusCard.classList.remove('hidden');
                statusIcon.innerHTML = '<i class="fas fa-times-circle text-red-500"></i>';
                statusTitle.textContent = 'KYC Rejected';
                statusMessage.textContent = kycData.kyc_rejection_reason || 'Your KYC submission was rejected. Please resubmit with correct documents.';
            } else if (kycData.kyc_status === 'pending' || kycData.kyc_status === 'submitted') {
                statusCard.classList.remove('hidden');
                kycForm.classList.add('hidden');
                statusIcon.innerHTML = '<i class="fas fa-clock text-yellow-500"></i>';
                statusTitle.textContent = 'KYC Under Review';
                statusMessage.textContent = 'Your documents are being reviewed. This typically takes 1-3 business days.';
            }
        }

        // Handle form submission
        document.getElementById('kycSubmissionForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const loadingOverlay = document.getElementById('loadingOverlay');
            const submitBtn = document.getElementById('submitBtn');
            
            loadingOverlay.classList.remove('hidden');
            submitBtn.disabled = true;

            const formData = new FormData();
            
            // Add form fields
            const formFields = ['id_document_type', 'id_document_number', 'phone_number', 'date_of_birth', 'nationality', 'occupation'];
            formFields.forEach(field => {
                formData.append(field, document.querySelector(`[name="${field}"]`).value);
            });

            // Add files
            const fileFields = ['id_document', 'photo_with_id', 'address_proof_document'];
            fileFields.forEach(field => {
                const fileInput = document.querySelector(`[name="${field}"]`);
                if (fileInput.files[0]) {
                    formData.append(field, fileInput.files[0]);
                }
            });

            try {
                const response = await fetch(`${API_BASE}/kyc/submit`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${authToken}`,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    alert('KYC documents submitted successfully! Your application is now under review.');
                    checkKycStatus(); // Refresh status
                } else {
                    alert('Error: ' + (data.message || 'Failed to submit KYC documents'));
                }
            } catch (error) {
                alert('Error submitting KYC documents: ' + error.message);
            } finally {
                loadingOverlay.classList.add('hidden');
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
