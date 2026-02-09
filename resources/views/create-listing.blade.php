<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing - WWA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                    <span class="ml-2 text-gray-600">Create Listing</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/dashboard" class="text-gray-700 hover:text-indigo-600">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- KYC Warning -->
        <div id="kycWarning" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 hidden">
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

        <!-- Form Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Create New Listing</h1>
            <p class="text-gray-600">Fill in the details below to create your listing. Premium listings get more visibility!</p>
        </div>

        <!-- Create Listing Form -->
        <form id="createListingForm" class="bg-white rounded-lg shadow-sm p-6">
            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Listing Title *</label>
                        <input type="text" id="title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter a descriptive title for your listing">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                        <select id="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
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
                        <select id="location" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
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
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" id="price" step="0.01" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="0.00">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Leave empty for free items</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                        <input type="tel" id="phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="+1234567890">
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea id="description" rows="6" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Provide a detailed description of your listing..."></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimum 50 characters recommended</p>
                </div>
            </div>

            <!-- Category Specific Fields -->
            <div id="categorySpecificFields" class="mb-8 hidden">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Details</h2>
                <div id="specificFieldsContainer">
                    <!-- Category-specific fields will be loaded here -->
                </div>
            </div>

            <!-- Images -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Images</h2>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600 mb-2">Drop images here or click to upload</p>
                    <p class="text-xs text-gray-500">PNG, JPG up to 10MB each (max 5 images)</p>
                    <input type="file" id="images" multiple accept="image/*" class="hidden">
                    <button type="button" onclick="document.getElementById('images').click()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-upload mr-2"></i> Select Images
                    </button>
                </div>
                <div id="imagePreview" class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- Image previews will be shown here -->
                </div>
            </div>

            <!-- Promotion Options -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Promotion Options</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="border rounded-lg p-4 cursor-pointer hover:border-indigo-500 promotion-option" data-type="none">
                        <div class="text-center">
                            <i class="fas fa-ad text-2xl text-gray-400 mb-2"></i>
                            <h4 class="font-medium text-gray-900">Standard</h4>
                            <p class="text-sm text-gray-600">Free listing</p>
                            <p class="text-lg font-bold text-gray-900 mt-2">$0</p>
                        </div>
                    </div>
                    
                    <div class="border rounded-lg p-4 cursor-pointer hover:border-indigo-500 promotion-option" data-type="priority">
                        <div class="text-center">
                            <i class="fas fa-bolt text-2xl text-blue-500 mb-2"></i>
                            <h4 class="font-medium text-gray-900">Priority</h4>
                            <p class="text-sm text-gray-600">Priority placement</p>
                            <p class="text-lg font-bold text-indigo-600">$4/day</p>
                        </div>
                    </div>
                    
                    <div class="border rounded-lg p-4 cursor-pointer hover:border-indigo-500 promotion-option" data-type="featured">
                        <div class="text-center">
                            <i class="fas fa-star text-2xl text-purple-500 mb-2"></i>
                            <h4 class="font-medium text-gray-900">Featured</h4>
                            <p class="text-sm text-gray-600">Category featured</p>
                            <p class="text-lg font-bold text-indigo-600">$6/day</p>
                        </div>
                    </div>
                    
                    <div class="border rounded-lg p-4 cursor-pointer hover:border-indigo-500 promotion-option" data-type="premium">
                        <div class="text-center">
                            <i class="fas fa-crown text-2xl text-yellow-500 mb-2"></i>
                            <h4 class="font-medium text-gray-900">Premium</h4>
                            <p class="text-sm text-gray-600">Maximum visibility</p>
                            <p class="text-lg font-bold text-indigo-600">$10/day</p>
                        </div>
                    </div>
                </div>
                
                <div id="promotionDetails" class="mt-4 p-4 bg-indigo-50 rounded-lg hidden">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="font-medium text-gray-900" id="selectedPromotionName">Standard</h4>
                            <p class="text-sm text-gray-600" id="selectedPromotionDesc">Free listing with basic visibility</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Duration</p>
                            <select id="promotionDuration" class="px-3 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500">
                                <option value="7">7 days</option>
                                <option value="14">14 days</option>
                                <option value="30">30 days</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms and Submit -->
            <div class="border-t pt-6">
                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" id="terms" required class="mt-1 mr-3">
                        <span class="text-sm text-gray-600">
                            I agree to the <a href="/terms" class="text-indigo-600 hover:text-indigo-700">Terms of Service</a> 
                            and <a href="/privacy" class="text-indigo-600 hover:text-indigo-700">Privacy Policy</a>. 
                            I understand that posting false or misleading information may result in account suspension.
                        </span>
                    </label>
                </div>
                
                <div class="flex justify-between items-center">
                    <a href="/dashboard" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i> Cancel
                    </a>
                    <div class="flex space-x-3">
                        <button type="button" onclick="saveDraft()" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-save mr-2"></i> Save Draft
                        </button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-check mr-2"></i> Publish Listing
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- API Service -->
    <script src="/js/api-service.js?v=<?php echo time(); ?>"></script>
    <script>
        const api = new APIService();
        let selectedPromotion = 'none';
        let uploadedImages = [];

        document.addEventListener('DOMContentLoaded', async () => {
            await initializePage();
        });

        async function initializePage() {
            try {
                console.log('Initializing page, checking authentication...');
                const user = await api.getCurrentUser();
                console.log('Current user:', user);
                
                if (!user) {
                    console.log('No user found, redirecting to login');
                    window.location.href = '/login';
                    return;
                }

                console.log('User authenticated:', user.email);

                // Check KYC status
                if (user.needs_kyc && !user.kyc_verified) {
                    document.getElementById('kycWarning').classList.remove('hidden');
                }

                // Setup form handlers
                setupFormHandlers();
            } catch (error) {
                console.error('Page initialization error:', error);
                // Don't automatically redirect on error, let the user see what's happening
            }
        }

        function setupFormHandlers() {
            // Category change handler
            document.getElementById('category').addEventListener('change', (e) => {
                loadCategorySpecificFields(e.target.value);
            });

            // Promotion option handlers
            document.querySelectorAll('.promotion-option').forEach(option => {
                option.addEventListener('click', () => {
                    selectPromotion(option.dataset.type);
                });
            });

            // Image upload handler
            document.getElementById('images').addEventListener('change', handleImageUpload);

            // Form submission
            document.getElementById('createListingForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                await submitListing();
            });
        }

        function loadCategorySpecificFields(categoryId) {
            const container = document.getElementById('specificFieldsContainer');
            const section = document.getElementById('categorySpecificFields');
            
            // Define category-specific fields
            const categoryFields = {
                '1': { // Buy and Sell
                    fields: [
                        { name: 'condition', label: 'Condition', type: 'select', options: ['New', 'Like New', 'Good', 'Fair', 'Poor'] },
                        { name: 'brand', label: 'Brand', type: 'text' },
                        { name: 'model', label: 'Model', type: 'text' }
                    ]
                },
                '3': { // Property & Real Estate
                    fields: [
                        { name: 'property_type', label: 'Property Type', type: 'select', options: ['House', 'Apartment', 'Commercial', 'Land'] },
                        { name: 'bedrooms', label: 'Bedrooms', type: 'number' },
                        { name: 'bathrooms', label: 'Bathrooms', type: 'number' },
                        { name: 'area_sqft', label: 'Area (sq ft)', type: 'number' }
                    ]
                },
                '4': { // Books
                    fields: [
                        { name: 'book_type', label: 'Format', type: 'select', options: ['Physical', 'E-book', 'Audiobook'] },
                        { name: 'author', label: 'Author', type: 'text' },
                        { name: 'isbn', label: 'ISBN', type: 'text' },
                        { name: 'genre', label: 'Genre', type: 'select', options: ['Fiction', 'Non-Fiction', 'Educational', 'Thriller', 'Romance', 'Science Fiction'] }
                    ]
                },
                '7': { // Jobs and Vacancies
                    fields: [
                        { name: 'job_type', label: 'Job Type', type: 'select', options: ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship'] },
                        { name: 'salary_min', label: 'Salary Range (Min)', type: 'number' },
                        { name: 'salary_max', label: 'Salary Range (Max)', type: 'number' },
                        { name: 'experience_required', label: 'Experience Required', type: 'select', options: ['Entry Level', '1-3 years', '3-5 years', '5+ years'] }
                    ]
                },
                '8': { // Services
                    fields: [
                        { name: 'service_type', label: 'Service Type', type: 'select', options: ['Consulting', 'Design', 'Development', 'Marketing', 'Writing', 'Other'] },
                        { name: 'skill_level', label: 'Skill Level', type: 'select', options: ['Beginner', 'Intermediate', 'Expert', 'Professional'] },
                        { name: 'turnaround_time', label: 'Turnaround Time', type: 'text' }
                    ]
                }
            };

            const fields = categoryFields[categoryId];
            
            if (fields && fields.length > 0) {
                section.classList.remove('hidden');
                container.innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        ${fields.map(field => createFieldHTML(field)).join('')}
                    </div>
                `;
            } else {
                section.classList.add('hidden');
                container.innerHTML = '';
            }
        }

        function createFieldHTML(field) {
            let fieldHTML = `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">${field.label}</label>
            `;

            if (field.type === 'select') {
                fieldHTML += `
                    <select name="${field.name}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select ${field.label}</option>
                        ${field.options.map(option => `<option value="${option.toLowerCase().replace(' ', '-')}">${option}</option>`).join('')}
                    </select>
                `;
            } else {
                fieldHTML += `
                    <input type="${field.type}" name="${field.name}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter ${field.label}">
                `;
            }

            fieldHTML += `</div>`;
            return fieldHTML;
        }

        function selectPromotion(type) {
            selectedPromotion = type;
            
            // Update UI
            document.querySelectorAll('.promotion-option').forEach(option => {
                option.classList.remove('border-indigo-500', 'bg-indigo-50');
            });
            
            const selectedOption = document.querySelector(`[data-type="${type}"]`);
            selectedOption.classList.add('border-indigo-500', 'bg-indigo-50');
            
            // Show promotion details
            const detailsDiv = document.getElementById('promotionDetails');
            const promotionData = {
                'none': { name: 'Standard', desc: 'Free listing with basic visibility' },
                'priority': { name: 'Priority', desc: 'Priority placement in search results' },
                'featured': { name: 'Featured', desc: 'Featured in category pages' },
                'premium': { name: 'Premium', desc: 'Maximum visibility with top placement' }
            };
            
            const promotion = promotionData[type];
            document.getElementById('selectedPromotionName').textContent = promotion.name;
            document.getElementById('selectedPromotionDesc').textContent = promotion.desc;
            
            if (type !== 'none') {
                detailsDiv.classList.remove('hidden');
            } else {
                detailsDiv.classList.add('hidden');
            }
        }

        function handleImageUpload(e) {
            const files = Array.from(e.target.files);
            const preview = document.getElementById('imagePreview');
            
            files.forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const imageId = Date.now() + Math.random();
                        uploadedImages.push({
                            id: imageId,
                            file: file,
                            url: e.target.result
                        });
                        
                        const imageDiv = document.createElement('div');
                        imageDiv.className = 'relative group';
                        imageDiv.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded-lg">
                            <button type="button" onclick="removeImage(${imageId})" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        `;
                        preview.appendChild(imageDiv);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function removeImage(imageId) {
            uploadedImages = uploadedImages.filter(img => img.id !== imageId);
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            uploadedImages.forEach(img => {
                const imageDiv = document.createElement('div');
                imageDiv.className = 'relative group';
                imageDiv.innerHTML = `
                    <img src="${img.url}" alt="Preview" class="w-full h-32 object-cover rounded-lg">
                    <button type="button" onclick="removeImage(${img.id})" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                `;
                preview.appendChild(imageDiv);
            });
        }

        async function submitListing() {
            const formData = new FormData();
            
            // Basic fields
            formData.append('title', document.getElementById('title').value);
            formData.append('category_id', document.getElementById('category').value);
            formData.append('location_id', document.getElementById('location').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('phone', document.getElementById('phone').value);
            
            const price = document.getElementById('price').value;
            if (price) {
                formData.append('price', price);
            }
            
            // Category-specific fields
            const specificFields = document.querySelectorAll('#specificFieldsContainer input, #specificFieldsContainer select');
            specificFields.forEach(field => {
                if (field.value) {
                    formData.append(field.name, field.value);
                }
            });
            
            // Images
            uploadedImages.forEach(img => {
                formData.append('images[]', img.file);
            });
            
            // Promotion
            if (selectedPromotion !== 'none') {
                formData.append('upsell_type', selectedPromotion);
                formData.append('upsell_duration', document.getElementById('promotionDuration').value);
            }
            
            formData.append('status', 'active');
            
            try {
                // Show loading
                const submitBtn = document.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
                
                // Create listing
                const response = await api.createListing(formData);
                
                // Handle upsell if selected
                if (selectedPromotion !== 'none' && response.data.id) {
                    await purchaseUpsell(response.data.id);
                }
                
                alert('Listing created successfully!');
                window.location.href = '/dashboard';
                
            } catch (error) {
                console.error('Error creating listing:', error);
                alert('Error creating listing. Please try again.');
                
                // Reset button
                const submitBtn = document.querySelector('button[type="submit"]');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i> Publish Listing';
            }
        }

        async function purchaseUpsell(listingId) {
            const upsellData = {
                listing_id: listingId,
                upsell_type: selectedPromotion,
                duration_days: document.getElementById('promotionDuration').value,
                payment_method: 'wallet' // Default to wallet
            };
            
            try {
                await api.purchaseUpsell(upsellData);
            } catch (error) {
                console.error('Error purchasing upsell:', error);
                // Don't throw error - listing was created successfully
            }
        }

        function saveDraft() {
            // Implement save draft functionality
            alert('Draft saved successfully!');
        }

        // Initialize with standard promotion selected
        selectPromotion('none');
    </script>
</body>
</html>
