@extends('layouts.app')

@section('title', 'Post Property - WorldwideAdverts')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Post Your Property</h1>
            <p class="text-xl text-gray-600">List your property on our global marketplace</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div id="step1Indicator" class="step-indicator active">
                        <span class="step-number">1</span>
                        <span class="step-title">Property Type</span>
                    </div>
                    <div class="step-line"></div>
                    <div id="step2Indicator" class="step-indicator">
                        <span class="step-number">2</span>
                        <span class="step-title">Basic Info</span>
                    </div>
                    <div class="step-line"></div>
                    <div id="step3Indicator" class="step-indicator">
                        <span class="step-number">3</span>
                        <span class="step-title">Specifications</span>
                    </div>
                    <div class="step-line"></div>
                    <div id="step4Indicator" class="step-indicator">
                        <span class="step-number">4</span>
                        <span class="step-title">Pricing</span>
                    </div>
                    <div class="step-line"></div>
                    <div id="step5Indicator" class="step-indicator">
                        <span class="step-number">5</span>
                        <span class="step-title">Seller Info</span>
                    </div>
                    <div class="step-line"></div>
                    <div id="step6Indicator" class="step-indicator">
                        <span class="step-number">6</span>
                        <span class="step-title">Description</span>
                    </div>
                    <div class="step-line"></div>
                    <div id="step7Indicator" class="step-indicator">
                        <span class="step-number">7</span>
                        <span class="step-title">Location</span>
                    </div>
                    <div class="step-line"></div>
                    <div id="step8Indicator" class="step-indicator">
                        <span class="step-number">8</span>
                        <span class="step-title">Promotion</span>
                    </div>
                    <div class="step-line"></div>
                    <div id="step9Indicator" class="step-indicator">
                        <span class="step-number">9</span>
                        <span class="step-title">Review</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <form id="propertyForm" class="bg-white rounded-lg shadow-lg p-8">
            <!-- Step 1: Property Type -->
            <div id="step1" class="form-step active">
                <h2 class="text-2xl font-bold mb-6">Select Property Type</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="residential" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-home text-3xl text-blue-500 mb-3"></i>
                            <h3 class="font-semibold">Residential</h3>
                            <p class="text-sm text-gray-600">Homes, apartments, condos</p>
                        </div>
                    </label>
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="commercial" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-building text-3xl text-green-500 mb-3"></i>
                            <h3 class="font-semibold">Commercial</h3>
                            <p class="text-sm text-gray-600">Office, retail spaces</p>
                        </div>
                    </label>
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="industrial" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-industry text-3xl text-yellow-500 mb-3"></i>
                            <h3 class="font-semibold">Industrial</h3>
                            <p class="text-sm text-gray-600">Warehouses, factories</p>
                        </div>
                    </label>
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="land" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-mountain text-3xl text-purple-500 mb-3"></i>
                            <h3 class="font-semibold">Land / Plots</h3>
                            <p class="text-sm text-gray-600">Land for development</p>
                        </div>
                    </label>
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="agricultural" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-seedling text-3xl text-green-600 mb-3"></i>
                            <h3 class="font-semibold">Agricultural</h3>
                            <p class="text-sm text-gray-600">Farms, agricultural land</p>
                        </div>
                    </label>
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="luxury" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-gem text-3xl text-yellow-600 mb-3"></i>
                            <h3 class="font-semibold">Luxury</h3>
                            <p class="text-sm text-gray-600">Premium properties</p>
                        </div>
                    </label>
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="short_term_rental" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-calendar-alt text-3xl text-purple-600 mb-3"></i>
                            <h3 class="font-semibold">Short-Term Rental</h3>
                            <p class="text-sm text-gray-600">Holiday homes, vacation rentals</p>
                        </div>
                    </label>
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="investment" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-chart-line text-3xl text-orange-500 mb-3"></i>
                            <h3 class="font-semibold">Investment</h3>
                            <p class="text-sm text-gray-600">Investment properties</p>
                        </div>
                    </label>
                    <label class="property-type-card">
                        <input type="radio" name="property_type" value="new_development" class="hidden">
                        <div class="border-2 border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-500 transition">
                            <i class="fas fa-hard-hat text-3xl text-red-500 mb-3"></i>
                            <h3 class="font-semibold">New Development</h3>
                            <p class="text-sm text-gray-600">Off-plan properties</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 2: Basic Information -->
            <div id="step2" class="form-step">
                <h2 class="text-2xl font-bold mb-6">Basic Property Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Property Title *</label>
                        <input type="text" name="title" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Tagline</label>
                        <input type="text" name="tagline" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Category *</label>
                        <select name="category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Category</option>
                            <option value="buy">Buy</option>
                            <option value="rent">Rent</option>
                            <option value="lease">Lease</option>
                            <option value="auction">Auction</option>
                            <option value="invest">Invest</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Country *</label>
                        <input type="text" name="country" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">City *</label>
                        <input type="text" name="city" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Address</label>
                        <input type="text" name="address" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Cover Image *</label>
                        <input type="file" name="cover_image" accept="image/*" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Additional Images (up to 10)</label>
                        <input type="file" name="additional_images[]" accept="image/*" multiple class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2">Video Tour Link</label>
                        <input type="url" name="video_tour_link" placeholder="YouTube or Vimeo URL" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Step 3: Specifications (Dynamic based on property type) -->
            <div id="step3" class="form-step">
                <h2 class="text-2xl font-bold mb-6">Property Specifications</h2>
                <div id="specificationsContainer">
                    <!-- Dynamic content will be loaded here based on property type -->
                </div>
            </div>

            <!-- Step 4: Pricing -->
            <div id="step4" class="form-step">
                <h2 class="text-2xl font-bold mb-6">Pricing & Financial Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Price *</label>
                        <input type="number" name="price" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Currency *</label>
                        <select name="currency" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="USD">USD ($)</option>
                            <option value="EUR">EUR (€)</option>
                            <option value="GBP">GBP (£)</option>
                            <option value="AED">AED (د.إ)</option>
                            <option value="SAR">SAR (﷼)</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="negotiable" class="mr-2">
                            <span class="text-gray-700">Price is negotiable</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Deposit</label>
                        <input type="number" name="deposit" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Service Charges</label>
                        <input type="number" name="service_charges" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Maintenance Fees</label>
                        <input type="number" name="maintenance_fees" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Step 5: Seller/Agent Information -->
            <div id="step5" class="form-step">
                <h2 class="text-2xl font-bold mb-6">Seller / Agent Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Name *</label>
                        <input type="text" name="seller_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Company</label>
                        <input type="text" name="seller_company" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Phone *</label>
                        <input type="tel" name="seller_phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Email *</label>
                        <input type="email" name="seller_email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Website</label>
                        <input type="url" name="seller_website" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Agent Logo</label>
                        <input type="file" name="seller_logo" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="verified_agent" class="mr-2">
                            <span class="text-gray-700">Verified Agent (Premium upgrade)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 6: Description -->
            <div id="step6" class="form-step">
                <h2 class="text-2xl font-bold mb-6">Property Description</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Overview *</label>
                        <textarea name="description" rows="6" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Key Features</label>
                        <textarea name="key_features" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Location Highlights</label>
                        <textarea name="location_highlights" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Nearby Amenities</label>
                        <textarea name="nearby_amenities" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Transport Links</label>
                        <textarea name="transport_links" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Additional Notes</label>
                        <textarea name="additional_notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 7: Location Map -->
            <div id="step7" class="form-step">
                <h2 class="text-2xl font-bold mb-6">Property Location</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Drop Pin on Map</label>
                        <div id="locationMap" class="h-96 bg-gray-200 rounded-lg"></div>
                        <p class="text-sm text-gray-600 mt-2">Click on the map to set the exact location of your property</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Latitude</label>
                            <input type="number" name="latitude" step="0.00000001" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Longitude</label>
                            <input type="number" name="longitude" step="0.00000001" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="show_exact_location" class="mr-2">
                            <span class="text-gray-700">Show exact location on map</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 8: Premium Upsell Options -->
            <div id="step8" class="form-step">
                <h2 class="text-2xl font-bold mb-6">Boost Your Listing</h2>
                <div class="space-y-6">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h3 class="font-semibold text-yellow-800 mb-2">🎯 Promoted Listing</h3>
                        <ul class="text-sm text-gray-700 mb-4">
                            <li>• Highlighted card design</li>
                            <li>• Above standard listings</li>
                            <li>• "Promoted" badge</li>
                            <li>• 7 days visibility boost</li>
                        </ul>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="radio" name="advert_type" value="promoted" class="mr-2">
                                <span class="font-semibold">$29.99</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 relative">
                        <div class="absolute -top-3 left-4 bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-semibold">MOST POPULAR</div>
                        <h3 class="font-semibold text-blue-800 mb-2">⭐ Featured Listing</h3>
                        <ul class="text-sm text-gray-700 mb-4">
                            <li>• Top of category placement</li>
                            <li>• Larger card with priority</li>
                            <li>• Priority in search results</li>
                            <li>• Weekly email inclusion</li>
                            <li>• "Featured" badge</li>
                            <li>• 14 days visibility boost</li>
                        </ul>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="radio" name="advert_type" value="featured" class="mr-2">
                                <span class="font-semibold">$79.99</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-800 mb-2">🚀 Sponsored Listing</h3>
                        <ul class="text-sm text-gray-700 mb-4">
                            <li>• Homepage premium placement</li>
                            <li>• Category top placement</li>
                            <li>• Homepage slider rotation</li>
                            <li>• Social media promotion</li>
                            <li>• "Sponsored" badge</li>
                            <li>• 30 days maximum visibility</li>
                        </ul>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="radio" name="advert_type" value="sponsored" class="mr-2">
                                <span class="font-semibold">$199.99</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-2">📝 Standard Listing</h3>
                        <ul class="text-sm text-gray-700 mb-4">
                            <li>• Basic listing placement</li>
                            <li>• Standard visibility</li>
                            <li>• No additional features</li>
                        </ul>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="radio" name="advert_type" value="standard" checked class="mr-2">
                                <span class="font-semibold">FREE</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 9: Review & Submit -->
            <div id="step9" class="form-step">
                <h2 class="text-2xl font-bold mb-6">Review & Submit</h2>
                <div id="reviewContent" class="space-y-6">
                    <!-- Review content will be populated here -->
                </div>
                <div class="mt-8 space-y-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="confirm_accuracy" required class="mr-2">
                        <span class="text-gray-700">I confirm that all information provided is accurate</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="agree_terms" required class="mr-2">
                        <span class="text-gray-700">I agree to the terms and conditions</span>
                    </label>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-8">
                <button type="button" id="prevBtn" class="px-6 py-3 border border-gray-300 rounded-lg font-semibold hover:bg-gray-50 transition duration-200 hidden">
                    Previous
                </button>
                <button type="button" id="nextBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition duration-200 ml-auto">
                    Next
                </button>
                <button type="submit" id="submitBtn" class="px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition duration-200 ml-auto hidden">
                    Submit Listing
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

.step-indicator {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step-title {
    font-size: 12px;
    color: #6b7280;
    text-align: center;
    transition: all 0.3s ease;
}

.step-indicator.active .step-number {
    background: #3b82f6;
    color: white;
}

.step-indicator.active .step-title {
    color: #3b82f6;
}

.step-indicator.completed .step-number {
    background: #10b981;
    color: white;
}

.step-line {
    flex: 1;
    height: 2px;
    background: #e5e7eb;
    margin: 0 8px;
    margin-top: 20px;
}

.property-type-card input:checked + div {
    border-color: #3b82f6;
    background: #eff6ff;
}

.property-type-card:hover div {
    border-color: #3b82f6;
}

.spec-group {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.spec-group h3 {
    font-semibold text-lg mb-4;
}

.review-section {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
}

.review-section h3 {
    font-semibold text-lg mb-3;
}

.review-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.review-item:last-child {
    border-bottom: none;
}

.review-label {
    font-medium text-gray-600;
}

.review-value {
    text-gray-900;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let currentStep = 1;
const totalSteps = 9;
let map;
let marker;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    setupEventListeners();
});

function setupEventListeners() {
    // Property type cards
    document.querySelectorAll('.property-type-card input').forEach(input => {
        input.addEventListener('change', function() {
            updateSpecifications(this.value);
        });
    });

    // Navigation buttons
    document.getElementById('nextBtn').addEventListener('click', nextStep);
    document.getElementById('prevBtn').addEventListener('click', prevStep);
    
    // Form submission
    document.getElementById('propertyForm').addEventListener('submit', submitForm);
}

function initializeMap() {
    map = L.map('locationMap').setView([51.505, -0.09], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    map.on('click', function(e) {
        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map);
        document.querySelector('input[name="latitude"]').value = e.latlng.lat;
        document.querySelector('input[name="longitude"]').value = e.latlng.lng;
    });
}

function updateSpecifications(propertyType) {
    const container = document.getElementById('specificationsContainer');
    let html = '';

    switch(propertyType) {
        case 'residential':
            html = `
                <div class="spec-group">
                    <h3>Residential Specifications</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Bedrooms</label>
                            <input type="number" name="bedrooms" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Bathrooms</label>
                            <input type="number" name="bathrooms" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Property Size</label>
                            <input type="number" name="property_size" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Size Unit</label>
                            <select name="size_unit" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="sq_m">Square Meters</option>
                                <option value="sq_ft">Square Feet</option>
                            </select>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="furnished" class="mr-2">
                                <span class="text-gray-700">Furnished</span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Parking Spaces</label>
                            <input type="number" name="parking_spaces" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>
            `;
            break;
        case 'commercial':
            html = `
                <div class="spec-group">
                    <h3>Commercial Specifications</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Commercial Type</label>
                            <select name="commercial_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="office">Office Space</option>
                                <option value="retail">Retail Space</option>
                                <option value="warehouse">Warehouse</option>
                                <option value="industrial">Industrial Unit</option>
                                <option value="restaurant">Restaurant/Cafe</option>
                                <option value="showroom">Showroom</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Floor Area</label>
                            <input type="number" name="floor_area" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-2">Footfall Rating</label>
                            <select name="footfall_rating" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="accessibility_features" class="mr-2">
                                <span class="text-gray-700">Accessibility Features</span>
                            </label>
                        </div>
                    </div>
                </div>
            `;
            break;
        // Add other property types as needed
        default:
            html = '<p class="text-gray-600">Specifications will be customized based on your property type selection.</p>';
    }

    container.innerHTML = html;
}

function nextStep() {
    if (validateStep(currentStep)) {
        document.getElementById(`step${currentStep}`).classList.remove('active');
        document.getElementById(`step${currentStep}Indicator`).classList.remove('active');
        document.getElementById(`step${currentStep}Indicator`).classList.add('completed');
        
        currentStep++;
        
        document.getElementById(`step${currentStep}`).classList.add('active');
        document.getElementById(`step${currentStep}Indicator`).classList.add('active');
        
        updateNavigationButtons();
        
        if (currentStep === 9) {
            generateReview();
        }
    }
}

function prevStep() {
    document.getElementById(`step${currentStep}`).classList.remove('active');
    document.getElementById(`step${currentStep}Indicator`).classList.remove('active');
    
    currentStep--;
    
    document.getElementById(`step${currentStep}`).classList.add('active');
    document.getElementById(`step${currentStep}Indicator`).classList.remove('completed');
    
    updateNavigationButtons();
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    prevBtn.classList.toggle('hidden', currentStep === 1);
    nextBtn.classList.toggle('hidden', currentStep === totalSteps);
    submitBtn.classList.toggle('hidden', currentStep !== totalSteps);
}

function validateStep(step) {
    // Add validation logic for each step
    const currentStepElement = document.getElementById(`step${step}`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.focus();
            alert('Please fill in all required fields');
            return false;
        }
    }
    
    return true;
}

function generateReview() {
    const formData = new FormData(document.getElementById('propertyForm'));
    const reviewContent = document.getElementById('reviewContent');
    
    let html = '<div class="space-y-4">';
    
    // Basic Information
    html += `
        <div class="review-section">
            <h3>Basic Information</h3>
            <div class="review-item">
                <span class="review-label">Title:</span>
                <span class="review-value">${formData.get('title')}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Property Type:</span>
                <span class="review-value">${formData.get('property_type')}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Category:</span>
                <span class="review-value">${formData.get('category')}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Location:</span>
                <span class="review-value">${formData.get('city')}, ${formData.get('country')}</span>
            </div>
        </div>
    `;
    
    // Pricing
    html += `
        <div class="review-section">
            <h3>Pricing</h3>
            <div class="review-item">
                <span class="review-label">Price:</span>
                <span class="review-value">${formData.get('currency')} ${formData.get('price')}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Negotiable:</span>
                <span class="review-value">${formData.get('negotiable') ? 'Yes' : 'No'}</span>
            </div>
        </div>
    `;
    
    // Promotion
    const advertType = formData.get('advert_type') || 'standard';
    html += `
        <div class="review-section">
            <h3>Promotion</h3>
            <div class="review-item">
                <span class="review-label">Listing Type:</span>
                <span class="review-value">${advertType.charAt(0).toUpperCase() + advertType.slice(1)}</span>
            </div>
        </div>
    `;
    
    html += '</div>';
    reviewContent.innerHTML = html;
}

async function submitForm(e) {
    e.preventDefault();
    
    const formData = new FormData(document.getElementById('propertyForm'));
    
    try {
        const response = await fetch('/api/v1/properties', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        if (response.ok) {
            const result = await response.json();
            alert('Property posted successfully!');
            window.location.href = '/property/' + result.data.id;
        } else {
            const error = await response.json();
            alert('Error posting property: ' + error.message);
        }
    } catch (error) {
        alert('Error posting property: ' + error.message);
    }
}
</script>
@endpush
