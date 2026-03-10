@extends('layouts.app')

@section('title', 'Create Sponsored Advert - Premium Global Advertising')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">Create Your Sponsored Advert</h1>
            <p class="text-xl text-gray-600">Get maximum visibility with premium sponsored advertising</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div id="step1-circle" class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                    <span class="ml-2 font-medium">Advert Type</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div id="step2-circle" class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">2</div>
                    <span class="ml-2 font-medium">Basic Info</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div id="step3-circle" class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">3</div>
                    <span class="ml-2 font-medium">Description</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div id="step4-circle" class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">4</div>
                    <span class="ml-2 font-medium">Seller Info</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-4"></div>
                <div class="flex items-center">
                    <div id="step5-circle" class="w-10 h-10 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center font-bold">5</div>
                    <span class="ml-2 font-medium">Sponsorship</span>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="sponsored-advert-form" class="bg-white rounded-lg shadow-lg p-8">
            <!-- Step 1: Advert Type -->
            <div id="step1" class="step-content">
                <h2 class="text-2xl font-bold mb-6">Select Sponsored Advert Type</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="advert_type" value="Product" class="hidden peer" required>
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <div class="text-3xl mb-2">🛍️</div>
                            <div class="font-medium">Product</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="advert_type" value="Service" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <div class="text-3xl mb-2">💼</div>
                            <div class="font-medium">Service</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="advert_type" value="Property" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <div class="text-3xl mb-2">🏠</div>
                            <div class="font-medium">Property</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="advert_type" value="Job" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <div class="text-3xl mb-2">💼</div>
                            <div class="font-medium">Job</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="advert_type" value="Event" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <div class="text-3xl mb-2">🎉</div>
                            <div class="font-medium">Event</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="advert_type" value="Vehicle" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <div class="text-3xl mb-2">🚗</div>
                            <div class="font-medium">Vehicle</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="advert_type" value="Business Opportunity" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <div class="text-3xl mb-2">📈</div>
                            <div class="font-medium">Business</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="advert_type" value="Miscellaneous" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <div class="text-3xl mb-2">📦</div>
                            <div class="font-medium">Other</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 2: Basic Information -->
            <div id="step2" class="step-content hidden">
                <h2 class="text-2xl font-bold mb-6">Basic Advert Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Advert Title *</label>
                        <input type="text" name="title" required maxlength="255" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter your advert title">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Short Tagline (max 80 chars)</label>
                        <input type="text" name="tagline" maxlength="80" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Catchy tagline for your advert">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Category</label>
                        <select name="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select a category</option>
                            <!-- Categories will be loaded here -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Price</label>
                        <div class="flex">
                            <select name="currency" class="px-3 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500">
                                <option value="GBP">£</option>
                                <option value="USD">$</option>
                                <option value="EUR">€</option>
                            </select>
                            <input type="number" name="price" step="0.01" min="0" 
                                   class="flex-1 px-4 py-2 border-t border-r border-b border-gray-300 rounded-r-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Condition</label>
                        <select name="condition" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select condition</option>
                            <option value="new">New</option>
                            <option value="used">Used</option>
                            <option value="not_applicable">Not Applicable</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Country *</label>
                        <select name="country" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select country</option>
                            <option value="United Kingdom">United Kingdom 🇬🇧</option>
                            <option value="United States">United States 🇺🇸</option>
                            <option value="Canada">Canada 🇨🇦</option>
                            <option value="Australia">Australia 🇦🇺</option>
                            <option value="Germany">Germany 🇩🇪</option>
                            <option value="France">France 🇫🇷</option>
                            <option value="Italy">Italy 🇮🇹</option>
                            <option value="Spain">Spain 🇪🇸</option>
                            <option value="Netherlands">Netherlands 🇳🇱</option>
                            <option value="United Arab Emirates">UAE 🇦🇪</option>
                            <option value="India">India 🇮🇳</option>
                            <option value="China">China 🇨🇳</option>
                            <option value="Japan">Japan 🇯🇵</option>
                            <option value="South Korea">South Korea 🇰🇷</option>
                            <option value="Singapore">Singapore 🇸🇬</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">City *</label>
                        <input type="text" name="city" required maxlength="100" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter city name">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Location Precision</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="location_precision" value="exact" class="mr-2">
                                <span>Exact Location</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="location_precision" value="approximate" checked class="mr-2">
                                <span>Approximate Location (Privacy Mode)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Media Upload -->
                <div class="mt-6">
                    <h3 class="text-xl font-semibold mb-4">Media Upload</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Main Image *</label>
                            <input type="file" name="main_image" accept="image/*" required 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-sm text-gray-500 mt-1">Required: Main product/service image</p>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Additional Images (up to 10)</label>
                            <input type="file" name="additional_images[]" accept="image/*" multiple 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-sm text-gray-500 mt-1">Optional: Additional photos</p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-medium mb-2">Video Link (Optional)</label>
                            <input type="url" name="video_link" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="https://youtube.com/watch?v=...">
                            <p class="text-sm text-gray-500 mt-1">YouTube, Vimeo, or other video platform link</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Description -->
            <div id="step3" class="step-content hidden">
                <h2 class="text-2xl font-bold mb-6">Description</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Overview *</label>
                        <textarea name="overview" rows="3" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Provide a brief overview of your advert..."></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Key Features</label>
                        <textarea name="key_features" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="List the key features and benefits..."></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">What Makes This Advert Special</label>
                        <textarea name="what_makes_special" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="What makes your product/service unique?"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Why It's Sponsored</label>
                        <textarea name="why_sponsored" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Why did you choose to sponsor this advert?"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Additional Notes</label>
                        <textarea name="additional_notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Any additional information..."></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Full Description *</label>
                        <textarea name="description" rows="5" required
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Provide a detailed description..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 4: Seller Information -->
            <div id="step4" class="step-content hidden">
                <h2 class="text-2xl font-bold mb-6">Seller / Poster Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Name *</label>
                        <input type="text" name="seller_name" required maxlength="255" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Your full name">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Business Name (Optional)</label>
                        <input type="text" name="business_name" maxlength="255" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Business or company name">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                        <input type="tel" name="phone" required maxlength="50" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="+44 20 1234 5678">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Email Address *</label>
                        <input type="email" name="email" required maxlength="255" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="your@email.com">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Website (Optional)</label>
                        <input type="url" name="website" maxlength="255" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="https://yourwebsite.com">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Logo (Optional)</label>
                        <input type="file" name="logo" accept="image/*" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="verified_seller" class="mr-2">
                            <span>Request Verified Seller Badge (Additional verification required)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 5: Sponsored Upsell Options -->
            <div id="step5" class="step-content hidden">
                <h2 class="text-2xl font-bold mb-6">Select Your Sponsorship Tier</h2>
                
                <!-- Comparison Banner -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-blue-800 text-center">
                        <strong>Smart Recommendation:</strong> Sponsored Plus adverts get 5× more views on average.
                    </p>
                </div>

                <!-- Pricing Tiers -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <label class="cursor-pointer">
                        <input type="radio" name="sponsorship_tier" value="basic" class="hidden peer" required>
                        <div class="border-2 border-gray-200 rounded-lg p-6 peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <h3 class="text-xl font-bold mb-2">Sponsored Basic</h3>
                            <div class="text-3xl font-bold mb-4">£29.99</div>
                            <ul class="space-y-2 mb-4">
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Listed on Sponsored Adverts Page</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Highlighted card</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>"Sponsored" badge</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>3× more visibility than standard ads</span>
                                </li>
                            </ul>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="sponsorship_tier" value="plus" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-6 peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300 relative">
                            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                Most Popular
                            </div>
                            <h3 class="text-xl font-bold mb-2">Sponsored Plus</h3>
                            <div class="text-3xl font-bold mb-4">£59.99</div>
                            <ul class="space-y-2 mb-4">
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>All Basic features</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Top of category placement</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Larger advert card</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Priority in search results</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Included in weekly "Sponsored Highlights" email</span>
                                </li>
                            </ul>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="sponsorship_tier" value="premium" class="hidden peer">
                        <div class="border-2 border-gray-200 rounded-lg p-6 peer-checked:border-blue-600 peer-checked:bg-blue-50 hover:border-gray-300">
                            <h3 class="text-xl font-bold mb-2">Sponsored Premium</h3>
                            <div class="text-3xl font-bold mb-4">£99.99</div>
                            <ul class="space-y-2 mb-4">
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Homepage placement</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Featured in homepage slider</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Category top placement</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Included in social media promotion</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>"Premium Sponsored" badge</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="text-green-500 mr-2">✓</span>
                                    <span>Maximum visibility across the platform</span>
                                </li>
                            </ul>
                        </div>
                    </label>
                </div>

                <!-- Sticky Summary Box -->
                <div class="bg-gray-100 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Selected Tier:</span>
                            <span id="selected-tier" class="font-bold">None</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Cost:</span>
                            <span id="total-cost" class="font-bold text-xl">£0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8">
                <button type="button" id="prev-btn" onclick="changeStep(-1)" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-3 px-6 rounded-lg transition duration-300 hidden">
                    Previous
                </button>
                <div class="flex space-x-4 ml-auto">
                    <button type="button" id="next-btn" onclick="changeStep(1)" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-300">
                        Next
                    </button>
                    <button type="submit" id="submit-btn" 
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition duration-300 hidden">
                        Submit Sponsored Advert
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 5;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    setupFormValidation();
    updateSummary();
});

// Load categories
async function loadCategories() {
    try {
        const response = await fetch('/api/v1/categories');
        const data = await response.json();
        
        const select = document.querySelector('select[name="category_id"]');
        select.innerHTML = '<option value="">Select a category</option>';
        
        data.data.forEach(category => {
            const option = document.createElement('option');
            option.value = category.category_id;
            option.textContent = category.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Setup form validation
function setupFormValidation() {
    const form = document.getElementById('sponsored-advert-form');
    
    // Update summary when sponsorship tier changes
    document.querySelectorAll('input[name="sponsorship_tier"]').forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });
    
    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        await submitForm();
    });
}

// Change step
function changeStep(direction) {
    // Validate current step before moving forward
    if (direction > 0 && !validateStep(currentStep)) {
        return;
    }
    
    // Hide current step
    document.getElementById(`step${currentStep}`).classList.add('hidden');
    document.getElementById(`step${currentStep}-circle`).classList.remove('bg-blue-600', 'text-white');
    document.getElementById(`step${currentStep}-circle`).classList.add('bg-gray-300', 'text-gray-600');
    
    // Show next step
    currentStep += direction;
    document.getElementById(`step${currentStep}`).classList.remove('hidden');
    document.getElementById(`step${currentStep}-circle`).classList.remove('bg-gray-300', 'text-gray-600');
    document.getElementById(`step${currentStep}-circle`).classList.add('bg-blue-600', 'text-white');
    
    // Update navigation buttons
    updateNavigationButtons();
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Update navigation buttons
function updateNavigationButtons() {
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    // Previous button
    if (currentStep === 1) {
        prevBtn.classList.add('hidden');
    } else {
        prevBtn.classList.remove('hidden');
    }
    
    // Next/Submit button
    if (currentStep === totalSteps) {
        nextBtn.classList.add('hidden');
        submitBtn.classList.remove('hidden');
    } else {
        nextBtn.classList.remove('hidden');
        submitBtn.classList.add('hidden');
    }
}

// Validate step
function validateStep(step) {
    const stepElement = document.getElementById(`step${step}`);
    const requiredFields = stepElement.querySelectorAll('[required]');
    
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.focus();
            field.classList.add('border-red-500');
            
            // Remove red border after 3 seconds
            setTimeout(() => {
                field.classList.remove('border-red-500');
            }, 3000);
            
            alert('Please fill in all required fields');
            return false;
        }
    }
    
    return true;
}

// Update summary
function updateSummary() {
    const selectedTier = document.querySelector('input[name="sponsorship_tier"]:checked');
    const tierDisplay = document.getElementById('selected-tier');
    const costDisplay = document.getElementById('total-cost');
    
    if (selectedTier) {
        const prices = {
            basic: '£29.99',
            plus: '£59.99',
            premium: '£99.99'
        };
        
        const tierNames = {
            basic: 'Sponsored Basic',
            plus: 'Sponsored Plus',
            premium: 'Sponsored Premium'
        };
        
        tierDisplay.textContent = tierNames[selectedTier.value];
        costDisplay.textContent = prices[selectedTier.value];
    } else {
        tierDisplay.textContent = 'None';
        costDisplay.textContent = '£0.00';
    }
}

// Submit form
async function submitForm() {
    const form = document.getElementById('sponsored-advert-form');
    const formData = new FormData(form);
    
    // Convert FormData to JSON
    const jsonData = {};
    for (let [key, value] of formData.entries()) {
        if (key === 'additional_images') {
            if (!jsonData[key]) jsonData[key] = [];
            jsonData[key].push(value);
        } else {
            jsonData[key] = value;
        }
    }
    
    try {
        const response = await fetch('/api/v1/sponsored-adverts', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(jsonData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Sponsored advert created successfully!');
            window.location.href = '/sponsored-adverts';
        } else {
            alert('Error: ' + (result.message || 'Failed to create advert'));
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        alert('Error submitting form. Please try again.');
    }
}
</script>
@endpush
