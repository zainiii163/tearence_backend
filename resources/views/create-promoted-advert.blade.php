@extends('layouts.app')

@section('title', 'Create Promoted Advert - High Visibility Listing')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Create Promoted Advert</h1>
            <p class="text-xl text-gray-600">Get maximum visibility for your listing with our premium promotion options</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="step-indicator active" data-step="1">
                        <div class="step-circle">1</div>
                        <div class="step-label">Advert Type</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" data-step="2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Basic Info</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Description</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" data-step="4">
                        <div class="step-circle">4</div>
                        <div class="step-label">Seller Info</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" data-step="5">
                        <div class="step-circle">5</div>
                        <div class="step-label">Promotion</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" data-step="6">
                        <div class="step-circle">6</div>
                        <div class="step-label">Submit</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="promotedAdvertForm" class="space-y-8">
            <!-- Step 1: Advert Type -->
            <div class="form-step active" data-step="1">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Choose Advert Type</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <label class="advert-type-card">
                            <input type="radio" name="advert_type" value="product" class="hidden" required>
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition">
                                <div class="text-3xl mb-2">📦</div>
                                <div class="font-semibold">Product / Item</div>
                                <div class="text-sm text-gray-600">For Sale</div>
                            </div>
                        </label>
                        <label class="advert-type-card">
                            <input type="radio" name="advert_type" value="service" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition">
                                <div class="text-3xl mb-2">💼</div>
                                <div class="font-semibold">Service</div>
                                <div class="text-sm text-gray-600">Business Offer</div>
                            </div>
                        </label>
                        <label class="advert-type-card">
                            <input type="radio" name="advert_type" value="property" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition">
                                <div class="text-3xl mb-2">🏠</div>
                                <div class="font-semibold">Property</div>
                                <div class="text-sm text-gray-600">Real Estate</div>
                            </div>
                        </label>
                        <label class="advert-type-card">
                            <input type="radio" name="advert_type" value="vehicle" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition">
                                <div class="text-3xl mb-2">🚗</div>
                                <div class="font-semibold">Vehicle</div>
                                <div class="text-sm text-gray-600">Motors</div>
                            </div>
                        </label>
                        <label class="advert-type-card">
                            <input type="radio" name="advert_type" value="job" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition">
                                <div class="text-3xl mb-2">💼</div>
                                <div class="font-semibold">Job</div>
                                <div class="text-sm text-gray-600">Vacancy</div>
                            </div>
                        </label>
                        <label class="advert-type-card">
                            <input type="radio" name="advert_type" value="event" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition">
                                <div class="text-3xl mb-2">🎉</div>
                                <div class="font-semibold">Event</div>
                                <div class="text-sm text-gray-600">Experience</div>
                            </div>
                        </label>
                        <label class="advert-type-card">
                            <input type="radio" name="advert_type" value="business" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition">
                                <div class="text-3xl mb-2">📈</div>
                                <div class="font-semibold">Business</div>
                                <div class="text-sm text-gray-600">Opportunity</div>
                            </div>
                        </label>
                        <label class="advert-type-card">
                            <input type="radio" name="advert_type" value="miscellaneous" class="hidden">
                            <div class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition">
                                <div class="text-3xl mb-2">📌</div>
                                <div class="font-semibold">Other</div>
                                <div class="text-sm text-gray-600">Miscellaneous</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 2: Basic Information -->
            <div class="form-step" data-step="2">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Basic Advert Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Advert Title *</label>
                            <input type="text" name="title" required maxlength="255" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Short Tagline (max 80 chars)</label>
                            <input type="text" name="tagline" maxlength="80" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <div class="text-xs text-gray-500 mt-1">
                                <span id="taglineCount">0</span>/80 characters
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select a category</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                            <select name="country" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select a country</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City / Region</label>
                            <input type="text" name="city" maxlength="100" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                            <div class="flex gap-2">
                                <input type="number" name="price" step="0.01" min="0" 
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <select name="currency" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="GBP">£ GBP</option>
                                    <option value="USD">$ USD</option>
                                    <option value="EUR">€ EUR</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price Type</label>
                            <select name="price_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="fixed">Fixed Price</option>
                                <option value="negotiable">Negotiable</option>
                                <option value="free">Free</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Condition</label>
                            <select name="condition" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select condition</option>
                                <option value="new">New</option>
                                <option value="used">Used</option>
                                <option value="not_applicable">Not Applicable</option>
                            </select>
                        </div>
                    </div>

                    <!-- Media Upload -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold mb-4">Media Uploads</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Main Image *</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                    <input type="file" id="mainImageInput" accept="image/*" class="hidden" required>
                                    <div id="mainImagePreview" class="mb-4"></div>
                                    <button type="button" onclick="document.getElementById('mainImageInput').click()" 
                                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                        Choose Main Image
                                    </button>
                                    <p class="text-sm text-gray-500 mt-2">JPG, PNG, GIF up to 2MB</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Images (up to 10)</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                    <input type="file" id="additionalImagesInput" accept="image/*" multiple class="hidden">
                                    <div id="additionalImagesPreview" class="grid grid-cols-4 gap-2 mb-4"></div>
                                    <button type="button" onclick="document.getElementById('additionalImagesInput').click()" 
                                            class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                                        Add More Images
                                    </button>
                                    <p class="text-sm text-gray-500 mt-2">JPG, PNG, GIF up to 2MB each</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Video Link (optional)</label>
                                <input type="url" name="video_link" placeholder="https://youtube.com/watch?v=..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Description -->
            <div class="form-step" data-step="3">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Description Section</h2>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Overview *</label>
                            <textarea name="description" required rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Provide a detailed overview of your advert..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Key Features</label>
                            <textarea name="key_features" rows="4" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="List the key features (one per line)..."></textarea>
                            <p class="text-sm text-gray-500 mt-1">Enter each feature on a new line</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">What Makes This Advert Special</label>
                            <textarea name="special_notes" rows="3" 
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="What makes your listing stand out from the rest?"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Seller Information -->
            <div class="form-step" data-step="4">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Seller / Poster Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                            <input type="text" name="seller_name" required maxlength="255" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Name</label>
                            <input type="text" name="business_name" maxlength="255" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                            <input type="tel" name="phone" required maxlength="20" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                            <input type="email" name="email" required maxlength="255" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Website / Social Links</label>
                            <input type="url" name="website" placeholder="https://..." 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Business Logo</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <input type="file" id="logoInput" accept="image/*" class="hidden">
                                <div id="logoPreview" class="mb-2"></div>
                                <button type="button" onclick="document.getElementById('logoInput').click()" 
                                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                                    Upload Logo
                                </button>
                                <p class="text-sm text-gray-500 mt-1">Optional - JPG, PNG up to 1MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Promotion Options -->
            <div class="form-step" data-step="5">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Promotion Options</h2>
                    
                    <!-- Smart Recommendation Banner -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <div class="text-blue-600 mr-3">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-blue-900">Smart Recommendation</p>
                                <p class="text-blue-700">Promoted Plus adverts get 4× more views on average.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Promotion Tiers -->
                    <div id="promotionTiers" class="space-y-4">
                        <!-- Tiers will be loaded here -->
                    </div>

                    <!-- Sticky Summary Box -->
                    <div class="fixed bottom-4 right-4 bg-white border-2 border-blue-600 rounded-lg shadow-xl p-4 max-w-sm" id="summaryBox" style="display: none;">
                        <h3 class="font-semibold mb-2">Order Summary</h3>
                        <div id="summaryContent">
                            <!-- Summary will be updated here -->
                        </div>
                        <div class="mt-4 pt-4 border-t">
                            <div class="flex justify-between items-center mb-4">
                                <span class="font-semibold">Total:</span>
                                <span id="totalPrice" class="text-2xl font-bold text-green-600">£0.00</span>
                            </div>
                            <button type="button" onclick="proceedToPayment()" 
                                    class="w-full bg-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-green-700 transition">
                                Proceed to Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Final Submission -->
            <div class="form-step" data-step="6">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Final Submission</h2>
                    
                    <!-- Review Summary -->
                    <div id="reviewSummary" class="bg-gray-50 rounded-lg p-6 mb-6">
                        <!-- Review content will be loaded here -->
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="space-y-4">
                        <label class="flex items-start">
                            <input type="checkbox" name="terms_accepted" required class="mt-1 mr-3">
                            <span class="text-sm text-gray-700">
                                I confirm this advert is accurate and complies with all applicable laws and regulations.
                            </span>
                        </label>
                        <label class="flex items-start">
                            <input type="checkbox" name="privacy_accepted" required class="mt-1 mr-3">
                            <span class="text-sm text-gray-700">
                                I agree to the <a href="#" class="text-blue-600 hover:underline">Terms of Service</a> 
                                and <a href="#" class="text-blue-600 hover:underline">Privacy Policy</a>.
                            </span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button type="submit" id="submitBtn" 
                                class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:bg-gray-400">
                            Submit Promoted Advert
                        </button>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center">
                <button type="button" id="prevBtn" onclick="changeStep(-1)" 
                        class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition disabled:bg-gray-400" disabled>
                    Previous
                </button>
                <button type="button" id="nextBtn" onclick="changeStep(1)" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                    Next
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let currentStep = 1;
let promotionOptions = [];
let selectedTier = null;
let uploadedImages = {
    main: null,
    additional: [],
    logo: null
};

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadCountries();
    loadPromotionOptions();
    initializeFormHandlers();
});

// Load categories
async function loadCategories() {
    try {
        const response = await fetch('/api/v1/promoted-advert-categories');
        const data = await response.json();
        
        const categorySelect = document.querySelector('select[name="category_id"]');
        
        if (data.success) {
            data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load countries
function loadCountries() {
    const countries = [
        'United Kingdom', 'United States', 'Canada', 'Australia', 'Germany', 'France', 'Spain', 'Italy',
        'Netherlands', 'Belgium', 'Switzerland', 'Austria', 'Sweden', 'Norway', 'Denmark', 'Finland',
        'Poland', 'Czech Republic', 'Hungary', 'Romania', 'Bulgaria', 'Greece', 'Portugal', 'Ireland',
        'India', 'Pakistan', 'Bangladesh', 'Sri Lanka', 'Nepal', 'Maldives', 'China', 'Japan',
        'South Korea', 'Singapore', 'Malaysia', 'Thailand', 'Vietnam', 'Indonesia', 'Philippines',
        'United Arab Emirates', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Bahrain', 'Oman', 'Egypt',
        'South Africa', 'Nigeria', 'Kenya', 'Ghana', 'Morocco', 'Tunisia', 'Algeria', 'Libya',
        'Brazil', 'Argentina', 'Chile', 'Peru', 'Colombia', 'Mexico', 'Venezuela', 'Ecuador'
    ];
    
    const countrySelect = document.querySelector('select[name="country"]');
    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country;
        option.textContent = country;
        countrySelect.appendChild(option);
    });
}

// Load promotion options
async function loadPromotionOptions() {
    try {
        const response = await fetch('/api/v1/promoted-adverts/promotion-options');
        const data = await response.json();
        
        if (data.success) {
            promotionOptions = data.data;
            renderPromotionTiers();
        }
    } catch (error) {
        console.error('Error loading promotion options:', error);
    }
}

// Render promotion tiers
function renderPromotionTiers() {
    const container = document.getElementById('promotionTiers');
    
    container.innerHTML = promotionOptions.map((option, index) => `
        <label class="promotion-tier-card ${option.popular ? 'popular' : ''}" data-tier="${option.tier}">
            <input type="radio" name="promotion_tier" value="${option.tier}" class="hidden" required>
            <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                ${option.popular ? '<div class="bg-blue-600 text-white text-xs px-3 py-1 rounded-full inline-block mb-4">Most Popular</div>' : ''}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xl font-bold">${option.name}</h3>
                        <div class="text-3xl font-bold text-green-600">£${option.price}</div>
                    </div>
                    <div class="text-4xl">
                        ${index === 0 ? '⭐' : index === 1 ? '🌟' : index === 2 ? '💎' : '👑'}
                    </div>
                </div>
                <ul class="space-y-2">
                    ${option.features.map(feature => `
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm">${feature}</span>
                        </li>
                    `).join('')}
                </ul>
            </div>
        </label>
    `).join('');
    
    // Add click handlers
    document.querySelectorAll('.promotion-tier-card').forEach(card => {
        card.addEventListener('click', function() {
            selectPromotionTier(this.dataset.tier);
        });
    });
}

// Select promotion tier
function selectPromotionTier(tier) {
    selectedTier = tier;
    
    // Update UI
    document.querySelectorAll('.promotion-tier-card').forEach(card => {
        card.classList.remove('selected', 'border-blue-600', 'bg-blue-50');
        card.classList.add('border-gray-200');
    });
    
    const selectedCard = document.querySelector(`.promotion-tier-card[data-tier="${tier}"]`);
    selectedCard.classList.add('selected', 'border-blue-600', 'bg-blue-50');
    selectedCard.classList.remove('border-gray-200');
    
    // Update summary
    updateSummary();
    
    // Show summary box
    document.getElementById('summaryBox').style.display = 'block';
}

// Update summary
function updateSummary() {
    if (!selectedTier) return;
    
    const option = promotionOptions.find(opt => opt.tier === selectedTier);
    const summaryContent = document.getElementById('summaryContent');
    const totalPrice = document.getElementById('totalPrice');
    
    summaryContent.innerHTML = `
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>Selected Tier:</span>
                <span class="font-semibold">${option.name}</span>
            </div>
            <div class="flex justify-between">
                <span>Base Price:</span>
                <span>£${option.price}</span>
            </div>
            <div class="flex justify-between">
                <span>Duration:</span>
                <span>30 days</span>
            </div>
        </div>
    `;
    
    totalPrice.textContent = `£${option.price}`;
}

// Initialize form handlers
function initializeFormHandlers() {
    // Advert type cards
    document.querySelectorAll('.advert-type-card input').forEach(input => {
        input.addEventListener('change', function() {
            document.querySelectorAll('.advert-type-card > div').forEach(card => {
                card.classList.remove('border-blue-600', 'bg-blue-50');
                card.classList.add('border-gray-200');
            });
            
            if (this.checked) {
                this.nextElementSibling.classList.remove('border-gray-200');
                this.nextElementSibling.classList.add('border-blue-600', 'bg-blue-50');
            }
        });
    });
    
    // Tagline character counter
    const taglineInput = document.querySelector('input[name="tagline"]');
    const taglineCount = document.getElementById('taglineCount');
    
    taglineInput.addEventListener('input', function() {
        taglineCount.textContent = this.value.length;
    });
    
    // Image upload handlers
    document.getElementById('mainImageInput').addEventListener('change', handleMainImageUpload);
    document.getElementById('additionalImagesInput').addEventListener('change', handleAdditionalImagesUpload);
    document.getElementById('logoInput').addEventListener('change', handleLogoUpload);
    
    // Form submission
    document.getElementById('promotedAdvertForm').addEventListener('submit', handleFormSubmit);
}

// Handle main image upload
async function handleMainImageUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.size > 2 * 1024 * 1024) {
        alert('File size must be less than 2MB');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('images[]', file);
        
        const response = await fetch('/api/v1/promoted-adverts/upload-images', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
            body: formData,
        });
        
        const data = await response.json();
        
        if (data.success) {
            uploadedImages.main = data.data.images[0];
            document.getElementById('mainImagePreview').innerHTML = `
                <img src="/storage/promoted-adverts/${uploadedImages.main}" alt="Main image" class="w-32 h-32 object-cover rounded">
            `;
        }
    } catch (error) {
        console.error('Error uploading image:', error);
        alert('Error uploading image');
    }
}

// Handle additional images upload
async function handleAdditionalImagesUpload(e) {
    const files = Array.from(e.target.files);
    if (files.length === 0) return;
    
    if (uploadedImages.additional.length + files.length > 10) {
        alert('You can upload up to 10 additional images');
        return;
    }
    
    try {
        const formData = new FormData();
        files.forEach(file => {
            if (file.size > 2 * 1024 * 1024) {
                alert(`File ${file.name} is too large (max 2MB)`);
                return;
            }
            formData.append('images[]', file);
        });
        
        const response = await fetch('/api/v1/promoted-adverts/upload-images', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
            body: formData,
        });
        
        const data = await response.json();
        
        if (data.success) {
            uploadedImages.additional.push(...data.data.images);
            updateAdditionalImagesPreview();
        }
    } catch (error) {
        console.error('Error uploading images:', error);
        alert('Error uploading images');
    }
}

// Update additional images preview
function updateAdditionalImagesPreview() {
    const preview = document.getElementById('additionalImagesPreview');
    preview.innerHTML = uploadedImages.additional.map((image, index) => `
        <div class="relative">
            <img src="/storage/promoted-adverts/${image}" alt="Additional image ${index + 1}" 
                 class="w-full h-20 object-cover rounded">
            <button type="button" onclick="removeAdditionalImage(${index})" 
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600">
                ×
            </button>
        </div>
    `).join('');
}

// Remove additional image
function removeAdditionalImage(index) {
    uploadedImages.additional.splice(index, 1);
    updateAdditionalImagesPreview();
}

// Handle logo upload
async function handleLogoUpload(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    if (file.size > 1024 * 1024) {
        alert('File size must be less than 1MB');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('logo', file);
        
        const response = await fetch('/api/v1/promoted-adverts/upload-logo', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
            },
            body: formData,
        });
        
        const data = await response.json();
        
        if (data.success) {
            uploadedImages.logo = data.data.logo;
            document.getElementById('logoPreview').innerHTML = `
                <img src="/storage/promoted-adverts/logos/${uploadedImages.logo}" alt="Logo" class="w-16 h-16 object-cover rounded">
            `;
        }
    } catch (error) {
        console.error('Error uploading logo:', error);
        alert('Error uploading logo');
    }
}

// Change step
function changeStep(direction) {
    const newStep = currentStep + direction;
    
    if (newStep < 1 || newStep > 6) return;
    
    // Validate current step before moving forward
    if (direction > 0 && !validateStep(currentStep)) {
        return;
    }
    
    // Hide current step
    document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.remove('active');
    document.querySelector(`.step-indicator[data-step="${currentStep}"]`).classList.remove('active');
    
    // Show new step
    currentStep = newStep;
    document.querySelector(`.form-step[data-step="${currentStep}"]`).classList.add('active');
    document.querySelector(`.step-indicator[data-step="${currentStep}"]`).classList.add('active');
    
    // Update navigation buttons
    updateNavigationButtons();
    
    // Load review summary if on final step
    if (currentStep === 6) {
        loadReviewSummary();
    }
}

// Validate step
function validateStep(step) {
    const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
    const requiredInputs = currentStepElement.querySelectorAll('input[required], textarea[required], select[required]');
    
    for (let input of requiredInputs) {
        if (!input.value.trim()) {
            input.focus();
            alert('Please fill in all required fields');
            return false;
        }
    }
    
    // Special validation for step 2 (main image)
    if (step === 2 && !uploadedImages.main) {
        alert('Please upload a main image');
        return false;
    }
    
    // Special validation for step 5 (promotion tier)
    if (step === 5 && !selectedTier) {
        alert('Please select a promotion tier');
        return false;
    }
    
    return true;
}

// Update navigation buttons
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    
    prevBtn.disabled = currentStep === 1;
    
    if (currentStep === 6) {
        nextBtn.style.display = 'none';
    } else {
        nextBtn.style.display = 'block';
        nextBtn.textContent = currentStep === 5 ? 'Review' : 'Next';
    }
}

// Load review summary
function loadReviewSummary() {
    const formData = new FormData(document.getElementById('promotedAdvertForm'));
    const summary = document.getElementById('reviewSummary');
    
    const selectedOption = promotionOptions.find(opt => opt.tier === selectedTier);
    
    summary.innerHTML = `
        <h3 class="text-lg font-semibold mb-4">Review Your Promoted Advert</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold mb-2">Basic Information</h4>
                <div class="space-y-1 text-sm">
                    <p><strong>Title:</strong> ${formData.get('title')}</p>
                    <p><strong>Type:</strong> ${formData.get('advert_type')}</p>
                    <p><strong>Category:</strong> ${formData.get('category_id') ? document.querySelector(`select[name="category_id"] option:checked`).text : 'None'}</p>
                    <p><strong>Country:</strong> ${formData.get('country')}</p>
                    <p><strong>Price:</strong> ${formData.get('price') ? `${formData.get('currency')} ${formData.get('price')}` : 'Not specified'}</p>
                </div>
            </div>
            <div>
                <h4 class="font-semibold mb-2">Promotion Details</h4>
                <div class="space-y-1 text-sm">
                    <p><strong>Tier:</strong> ${selectedOption.name}</p>
                    <p><strong>Price:</strong> £${selectedOption.price}</p>
                    <p><strong>Duration:</strong> 30 days</p>
                    <p><strong>Features:</strong></p>
                    <ul class="list-disc list-inside ml-4">
                        ${selectedOption.features.map(feature => `<li>${feature}</li>`).join('')}
                    </ul>
                </div>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold">Total Amount:</span>
                <span class="text-2xl font-bold text-green-600">£${selectedOption.price}</span>
            </div>
        </div>
    `;
}

// Handle form submission
async function handleFormSubmit(e) {
    e.preventDefault();
    
    if (!validateStep(6)) {
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    try {
        const formData = new FormData(document.getElementById('promotedAdvertForm'));
        
        // Add uploaded images
        if (uploadedImages.main) {
            formData.set('main_image', uploadedImages.main);
        }
        
        if (uploadedImages.additional.length > 0) {
            formData.set('additional_images', JSON.stringify(uploadedImages.additional));
        }
        
        if (uploadedImages.logo) {
            formData.set('logo', uploadedImages.logo);
        }
        
        // Convert key_features to array
        const keyFeatures = formData.get('key_features');
        if (keyFeatures) {
            formData.set('key_features', JSON.stringify(keyFeatures.split('\n').filter(f => f.trim())));
        }
        
        const response = await fetch('/api/v1/promoted-adverts', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json',
            },
            body: formData,
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Promoted advert created successfully!');
            window.location.href = '/promoted-adverts';
        } else {
            alert('Error creating advert: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Error submitting form');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Promoted Advert';
    }
}

// Proceed to payment (placeholder)
function proceedToPayment() {
    alert('Payment integration would be implemented here. For now, proceed to final step.');
    changeStep(1);
}
</script>

<style>
/* Step indicators */
.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    opacity: 0.5;
    transition: opacity 0.3s;
}

.step-indicator.active {
    opacity: 1;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: background-color 0.3s;
}

.step-indicator.active .step-circle {
    background: #3b82f6;
    color: white;
}

.step-label {
    font-size: 12px;
    text-align: center;
}

.step-line {
    width: 40px;
    height: 2px;
    background: #e5e7eb;
    margin: 0 8px 20px 8px;
}

/* Form steps */
.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

/* Advert type cards */
.advert-type-card.selected > div {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
}

/* Promotion tier cards */
.promotion-tier-card.selected > div {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
}

.promotion-tier-card.popular > div {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
}

/* Responsive */
@media (max-width: 768px) {
    .step-indicator {
        font-size: 10px;
    }
    
    .step-circle {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
    
    .step-line {
        width: 20px;
        margin: 0 4px 20px 4px;
    }
}
</style>
@endpush
