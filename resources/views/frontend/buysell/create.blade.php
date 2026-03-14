@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create New Advert</h1>
            <p class="text-gray-600">List your item for sale or trade</p>
        </div>
        <a href="{{ route('buysell.dashboard') }}" class="text-blue-600 hover:text-blue-800">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Create Form -->
    <form id="createAdvertForm" class="bg-white rounded-lg shadow p-6">
        @csrf
        
        <!-- Basic Information -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                    <input type="text" name="title" required maxlength="255" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" name="price" required min="0" step="0.01"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" required maxlength="5000" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
            </div>
        </div>

        <!-- Category Selection -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Category</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                    <select name="category_id" required id="categorySelect"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->icon }} {{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subcategory</label>
                    <select name="subcategory_id" id="subcategorySelect"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select a subcategory</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Item Details -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Item Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Condition *</label>
                    <select name="condition" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select condition</option>
                        <option value="new">New</option>
                        <option value="like_new">Like New</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="poor">Poor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                    <select name="currency"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="CAD">CAD ($)</option>
                        <option value="AUD">AUD ($)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Location</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                    <select name="country" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select country</option>
                        @foreach($countries as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                    <input type="text" name="city" maxlength="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <input type="text" name="address" maxlength="500"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                    <input type="text" name="postal_code" maxlength="20"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="tel" name="phone" maxlength="20"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" maxlength="255"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp</label>
                    <input type="tel" name="whatsapp" maxlength="20"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Contact</label>
                    <select name="preferred_contact"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select preference</option>
                        <option value="phone">Phone</option>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Promotion -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Promotion Options</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($promotionPlans as $plan)
                    <div class="border rounded-lg p-4 hover:border-blue-500 cursor-pointer promotion-plan" data-plan-id="{{ $plan->id }}">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-medium text-gray-900">{{ $plan->name }}</h3>
                            <input type="radio" name="promotion_plan_id" value="{{ $plan->id }}" class="text-blue-600">
                        </div>
                        <p class="text-sm text-gray-600 mb-2">{{ $plan->description }}</p>
                        <p class="text-lg font-bold text-green-600">${{ number_format($plan->price, 2) }}</p>
                        <p class="text-xs text-gray-500">{{ $plan->duration_days }} days</p>
                        <ul class="text-xs text-gray-600 mt-2">
                            @foreach($plan->features as $feature)
                                <li>• {{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Images Upload -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Images</h2>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <div class="mt-4">
                    <label for="imageUpload" class="cursor-pointer">
                        <span class="mt-2 block text-sm font-medium text-gray-900">Click to upload images</span>
                        <span class="mt-1 block text-xs text-gray-500">PNG, JPG, GIF up to 5MB each (max 10 images)</span>
                    </label>
                    <input id="imageUpload" name="images" type="file" multiple accept="image/*" class="hidden">
                </div>
            </div>
            <div id="imagePreview" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"></div>
        </div>

        <!-- Terms -->
        <div class="mb-8">
            <div class="flex items-center">
                <input type="checkbox" name="terms_accepted" required id="termsAccepted" class="mr-2">
                <label for="termsAccepted" class="text-sm text-gray-700">
                    I agree to the <a href="#" class="text-blue-600 hover:underline">Terms and Conditions</a> *
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                Create Advert
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category/Subcategory handling
    const categorySelect = document.getElementById('categorySelect');
    const subcategorySelect = document.getElementById('subcategorySelect');
    
    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        subcategorySelect.innerHTML = '<option value="">Select a subcategory</option>';
        
        if (categoryId) {
            // Load subcategories via API
            fetch(`/api/v1/buysell-categories/${categoryId}/subcategories`)
                .then(response => response.json())
                .then(data => {
                    data.data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        subcategorySelect.appendChild(option);
                    });
                });
        }
    });
    
    // Promotion plan selection
    document.querySelectorAll('.promotion-plan').forEach(plan => {
        plan.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            document.querySelectorAll('.promotion-plan').forEach(p => {
                p.classList.remove('border-blue-500', 'bg-blue-50');
            });
            this.classList.add('border-blue-500', 'bg-blue-50');
        });
    });
    
    // Image upload preview
    const imageUpload = document.getElementById('imageUpload');
    const imagePreview = document.getElementById('imagePreview');
    let uploadedImages = [];
    
    imageUpload.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        files.forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-32 object-cover rounded-lg">
                        <button type="button" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    `;
                    imagePreview.appendChild(div);
                    
                    uploadedImages.push(file);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    // Form submission
    document.getElementById('createAdvertForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Add uploaded images
        uploadedImages.forEach((image, index) => {
            formData.append(`images[${index}]`, image);
        });
        
        // Submit via AJAX
        fetch('{{ route("buysell.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '{{ route("buysell.dashboard") }}';
            } else {
                alert(data.message || 'Error creating advert');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating advert');
        });
    });
});
</script>
@endsection
