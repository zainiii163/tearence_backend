@extends('layouts.app')

@section('title', 'Post Your Book - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Post Your Book</h1>
            <p class="text-xl text-gray-600">Share your book with readers around the world</p>
            <p class="text-sm text-gray-500 mt-2">Reach thousands of potential readers with our premium author marketplace</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div id="step1-indicator" class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">1</div>
                    <div class="ml-2 text-sm font-medium text-gray-900">Book Type</div>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step2-indicator" class="w-8 h-8 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">2</div>
                    <div class="ml-2 text-sm font-medium text-gray-500">Basic Info</div>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step3-indicator" class="w-8 h-8 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">3</div>
                    <div class="ml-2 text-sm font-medium text-gray-500">Description</div>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step4-indicator" class="w-8 h-8 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">4</div>
                    <div class="ml-2 text-sm font-medium text-gray-500">Details</div>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step5-indicator" class="w-8 h-8 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">5</div>
                    <div class="ml-2 text-sm font-medium text-gray-500">Author Info</div>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step6-indicator" class="w-8 h-8 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">6</div>
                    <div class="ml-2 text-sm font-medium text-gray-500">Purchase Links</div>
                </div>
                <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                <div class="flex items-center">
                    <div id="step7-indicator" class="w-8 h-8 bg-gray-300 text-white rounded-full flex items-center justify-center text-sm font-semibold">7</div>
                    <div class="ml-2 text-sm font-medium text-gray-500">Premium</div>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form id="bookForm" enctype="multipart/form-data" class="space-y-8">
            <!-- Step 1: Book Type -->
            <div id="step1" class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">🧩 Select Book Type</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="book_type" value="fiction" class="sr-only peer" required>
                        <div class="p-4 border-2 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                            <div class="text-2xl mb-2">📚</div>
                            <div class="font-medium">Fiction</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="book_type" value="non-fiction" class="sr-only peer">
                        <div class="p-4 border-2 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                            <div class="text-2xl mb-2">📖</div>
                            <div class="font-medium">Non-Fiction</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="book_type" value="children" class="sr-only peer">
                        <div class="p-4 border-2 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                            <div class="text-2xl mb-2">🧸</div>
                            <div class="font-medium">Children's Book</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="book_type" value="poetry" class="sr-only peer">
                        <div class="p-4 border-2 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                            <div class="text-2xl mb-2">✍️</div>
                            <div class="font-medium">Poetry</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="book_type" value="academic" class="sr-only peer">
                        <div class="p-4 border-2 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                            <div class="text-2xl mb-2">🎓</div>
                            <div class="font-medium">Academic</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="book_type" value="self-help" class="sr-only peer">
                        <div class="p-4 border-2 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                            <div class="text-2xl mb-2">💡</div>
                            <div class="font-medium">Self-Help</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="book_type" value="business" class="sr-only peer">
                        <div class="p-4 border-2 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                            <div class="text-2xl mb-2">💼</div>
                            <div class="font-medium">Business</div>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="book_type" value="other" class="sr-only peer">
                        <div class="p-4 border-2 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition">
                            <div class="text-2xl mb-2">📄</div>
                            <div class="font-medium">Other</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 2: Basic Book Information -->
            <div id="step2" class="bg-white rounded-lg shadow-lg p-6 hidden">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">📝 Basic Book Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Book Title *</label>
                        <input type="text" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle / Tagline</label>
                        <input type="text" name="subtitle" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Author Name *</label>
                        <input type="text" name="author_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Genre *</label>
                        <input type="text" name="genre" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                        <select name="country" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Country</option>
                            <!-- Countries will be populated via JavaScript -->
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Language *</label>
                        <select name="language" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                            <option value="fr">French</option>
                            <option value="de">German</option>
                            <option value="it">Italian</option>
                            <option value="pt">Portuguese</option>
                            <option value="zh">Chinese</option>
                            <option value="ja">Japanese</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                        <input type="number" name="price" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Format *</label>
                        <select name="format" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Format</option>
                            <option value="paperback">Paperback</option>
                            <option value="hardcover">Hardcover</option>
                            <option value="ebook">eBook</option>
                            <option value="audiobook">Audiobook</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Book Cover Image *</label>
                        <input type="file" name="cover_image" accept="image/*" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">JPG, PNG, GIF. Max 2MB.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Additional Images (Optional)</label>
                        <input type="file" name="additional_images[]" accept="image/*" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">Up to 5 images. JPG, PNG, GIF. Max 2MB each.</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Book Trailer Video Link (Optional)</label>
                        <input type="url" name="trailer_video_url" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Step 3: Book Description -->
            <div id="step3" class="bg-white rounded-lg shadow-lg p-6 hidden">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">📖 Book Description</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Overview / Synopsis *</label>
                        <textarea name="description" rows="6" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        <p class="text-sm text-gray-500 mt-1">Minimum 50 characters. Maximum 5000 characters.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                        <textarea name="short_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        <p class="text-sm text-gray-500 mt-1">Brief summary for listings. Maximum 1000 characters.</p>
                    </div>
                </div>
            </div>

            <!-- Step 4: Additional Book Details -->
            <div id="step4" class="bg-white rounded-lg shadow-lg p-6 hidden">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">📄 Additional Book Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                        <input type="text" name="isbn" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Publisher</label>
                        <input type="text" name="publisher" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Publication Date</label>
                        <input type="date" name="publication_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Number of Pages</label>
                        <input type="number" name="pages" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Age Range (for children's books)</label>
                        <input type="text" name="age_range" placeholder="e.g., 8-12 years" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Series Name (if applicable)</label>
                        <input type="text" name="series_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Edition</label>
                        <input type="text" name="edition" placeholder="e.g., First Edition" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sample Files (Optional)</label>
                        <input type="file" name="sample_files[]" accept=".pdf,.mp3,.m4a,.wav,.epub" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">PDF, MP3, M4A, WAV, EPUB files. Max 10MB each. Up to 3 files.</p>
                    </div>
                </div>
            </div>

            <!-- Step 5: Author Information -->
            <div id="step5" class="bg-white rounded-lg shadow-lg p-6 hidden">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">🧑‍💼 Author Information</h2>
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Author Photo</label>
                        <input type="file" name="author_photo" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-1">JPG, PNG, GIF. Max 2MB.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Short Bio</label>
                        <textarea name="author_bio" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        <p class="text-sm text-gray-500 mt-1">Tell readers about yourself. Maximum 2000 characters.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Website / Social Links</label>
                        <div id="socialLinks" class="space-y-2">
                            <div class="flex gap-2">
                                <input type="url" name="author_social_links[]" placeholder="https://..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <button type="button" onclick="addSocialLink()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Purchase Links -->
            <div id="step6" class="bg-white rounded-lg shadow-lg p-6 hidden">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">🛒 Purchase & Distribution Links</h2>
                <div class="space-y-6">
                    <p class="text-gray-600">Allow readers to purchase your book from multiple platforms:</p>
                    <div id="purchaseLinks" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 purchase-link-row">
                            <select name="purchase_links[0][platform]" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Platform</option>
                                <option value="Amazon">Amazon</option>
                                <option value="Kobo">Kobo</option>
                                <option value="Apple Books">Apple Books</option>
                                <option value="Google Play">Google Play</option>
                                <option value="Author's Website">Author's Website</option>
                                <option value="Bookshop.org">Bookshop.org</option>
                                <option value="Audible">Audible</option>
                                <option value="Other">Other</option>
                            </select>
                            <input type="url" name="purchase_links[0][url]" placeholder="Purchase URL" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <button type="button" onclick="addPurchaseLink()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">+ Add Another Link</button>
                </div>
            </div>

            <!-- Step 7: Premium Upsell Options -->
            <div id="step7" class="bg-white rounded-lg shadow-lg p-6 hidden">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">💎 Premium Upsell Options</h2>
                
                <!-- Smart Recommendation Banner -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <div class="text-blue-600 text-xl mr-3">💡</div>
                        <div>
                            <p class="font-medium text-blue-900">Featured books get 5× more clicks on average!</p>
                            <p class="text-sm text-blue-700">Upgrade your visibility and reach more readers.</p>
                        </div>
                    </div>
                </div>

                <!-- Pricing Tiers -->
                <div id="pricingTiers" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Tiers will be populated via JavaScript -->
                </div>

                <!-- Comparison Table -->
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Features</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Promoted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sponsored</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Top of Category</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Visibility</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">2× More</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">5× More</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">10× More</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Maximum</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Placement</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Above Standard</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Top of Genre</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Homepage</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Pinned Top</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Email Inclusion</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">❌</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">✅ Weekly</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">✅ Campaign</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">✅ Newsletter</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Social Media</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">❌</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">✅ Mentions</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">✅ Promotion</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">✅ Campaign</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Sticky Summary Box -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Selected Tier:</span>
                            <span id="selectedTier" class="font-medium">Standard</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Duration:</span>
                            <span class="font-medium">30 Days</span>
                        </div>
                        <div class="border-t pt-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Total Cost:</span>
                                <span id="totalCost">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between">
                <button type="button" id="prevBtn" onclick="changeStep(-1)" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 hidden">Previous</button>
                <button type="button" id="nextBtn" onclick="changeStep(1)" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 ml-auto">Next</button>
                <button type="submit" id="submitBtn" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 hidden">Submit Book Advert</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 7;
let pricingPlans = [];

// Load pricing plans on page load
document.addEventListener('DOMContentLoaded', function() {
    loadPricingPlans();
    loadCountries();
});

function loadPricingPlans() {
    fetch('/api/books-adverts/pricing-plans')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pricingPlans = data.data;
                renderPricingTiers();
            }
        })
        .catch(error => console.error('Error loading pricing plans:', error));
}

function renderPricingTiers() {
    const container = document.getElementById('pricingTiers');
    container.innerHTML = '';
    
    pricingPlans.forEach((plan, index) => {
        const isMostPopular = plan.is_featured;
        const tierCard = document.createElement('div');
        tierCard.className = `relative bg-white border-2 rounded-lg p-6 cursor-pointer transition-all hover:shadow-lg ${isMostPopular ? 'border-yellow-400 ring-2 ring-yellow-400' : 'border-gray-200'}`;
        tierCard.onclick = () => selectPricingTier(plan.id, plan.price, plan.name);
        
        tierCard.innerHTML = `
            ${isMostPopular ? '<div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-xs font-semibold">Most Popular</div>' : ''}
            <div class="text-center">
                <div class="text-3xl mb-4">${getTierIcon(plan.tier_type)}</div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">${plan.name}</h3>
                <div class="text-3xl font-bold text-gray-900 mb-4">$${plan.price}</div>
                <div class="text-sm text-gray-500 mb-4">30 days</div>
                <ul class="text-left text-sm text-gray-600 space-y-2">
                    ${plan.features.map(feature => `<li class="flex items-start"><span class="text-green-500 mr-2">✓</span>${feature}</li>`).join('')}
                </ul>
                <div class="mt-6">
                    <input type="radio" name="upsell_tier" value="${plan.id}" class="sr-only" id="tier-${plan.id}">
                    <label for="tier-${plan.id}" class="block w-full py-2 px-4 rounded-lg border-2 ${isMostPopular ? 'border-yellow-400 bg-yellow-50 text-yellow-900' : 'border-gray-300 text-gray-700'} hover:${isMostPopular ? 'bg-yellow-100' : 'bg-gray-50'} transition">
                        Select
                    </label>
                </div>
            </div>
        `;
        
        container.appendChild(tierCard);
    });
}

function getTierIcon(tierType) {
    const icons = {
        'promoted': '⭐',
        'featured': '🌟',
        'sponsored': '🚀',
        'top_category': '👑'
    };
    return icons[tierType] || '📚';
}

function selectPricingTier(planId, price, name) {
    document.getElementById(`tier-${planId}`).checked = true;
    document.getElementById('selectedTier').textContent = name;
    document.getElementById('totalCost').textContent = `$${price}`;
    
    // Update visual selection
    document.querySelectorAll('#pricingTiers > div').forEach(card => {
        card.classList.remove('border-blue-500', 'bg-blue-50');
        card.classList.add('border-gray-200');
    });
    event.currentTarget.classList.remove('border-gray-200');
    event.currentTarget.classList.add('border-blue-500', 'bg-blue-50');
}

function loadCountries() {
    // Basic country list - in production, this should come from API
    const countries = [
        { code: 'US', name: 'United States' },
        { code: 'GB', name: 'United Kingdom' },
        { code: 'CA', name: 'Canada' },
        { code: 'AU', name: 'Australia' },
        { code: 'DE', name: 'Germany' },
        { code: 'FR', name: 'France' },
        { code: 'ES', name: 'Spain' },
        { code: 'IT', name: 'Italy' },
        { code: 'JP', name: 'Japan' },
        { code: 'CN', name: 'China' },
        { code: 'IN', name: 'India' },
        { code: 'BR', name: 'Brazil' },
        { code: 'MX', name: 'Mexico' },
        { code: 'NL', name: 'Netherlands' },
        { code: 'SE', name: 'Sweden' },
        { code: 'NO', name: 'Norway' },
        { code: 'DK', name: 'Denmark' },
        { code: 'FI', name: 'Finland' },
        { code: 'CH', name: 'Switzerland' },
        { code: 'AT', name: 'Austria' }
    ];
    
    const select = document.querySelector('select[name="country"]');
    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country.code;
        option.textContent = country.name;
        select.appendChild(option);
    });
}

function changeStep(direction) {
    // Validate current step before moving forward
    if (direction > 0 && !validateStep(currentStep)) {
        return;
    }
    
    // Hide current step
    document.getElementById(`step${currentStep}`).classList.add('hidden');
    
    // Update progress indicators
    document.getElementById(`step${currentStep}-indicator`).classList.remove('bg-blue-600');
    document.getElementById(`step${currentStep}-indicator`).classList.add('bg-green-600');
    
    // Show next/previous step
    currentStep += direction;
    document.getElementById(`step${currentStep}`).classList.remove('hidden');
    document.getElementById(`step${currentStep}-indicator`).classList.remove('bg-gray-300');
    document.getElementById(`step${currentStep}-indicator`).classList.add('bg-blue-600');
    
    // Update buttons
    updateButtons();
}

function validateStep(step) {
    const stepElement = document.getElementById(`step${step}`);
    const requiredFields = stepElement.querySelectorAll('[required]');
    
    for (let field of requiredFields) {
        if (!field.value.trim()) {
            field.focus();
            alert('Please fill in all required fields.');
            return false;
        }
    }
    
    // Special validation for file inputs
    if (step === 2) {
        const coverImage = document.querySelector('input[name="cover_image"]');
        if (!coverImage.files || coverImage.files.length === 0) {
            alert('Please upload a cover image.');
            return false;
        }
    }
    
    return true;
}

function updateButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Show/hide previous button
    if (currentStep === 1) {
        prevBtn.classList.add('hidden');
    } else {
        prevBtn.classList.remove('hidden');
    }
    
    // Show/hide next and submit buttons
    if (currentStep === totalSteps) {
        nextBtn.classList.add('hidden');
        submitBtn.classList.remove('hidden');
    } else {
        nextBtn.classList.remove('hidden');
        submitBtn.classList.add('hidden');
    }
}

function addSocialLink() {
    const container = document.getElementById('socialLinks');
    const newRow = document.createElement('div');
    newRow.className = 'flex gap-2';
    newRow.innerHTML = `
        <input type="url" name="author_social_links[]" placeholder="https://..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <button type="button" onclick="this.parentElement.remove()" class="px-4 py-2 bg-red-200 text-red-700 rounded-lg hover:bg-red-300">-</button>
    `;
    container.appendChild(newRow);
}

let purchaseLinkIndex = 1;
function addPurchaseLink() {
    const container = document.getElementById('purchaseLinks');
    const newRow = document.createElement('div');
    newRow.className = 'grid grid-cols-1 md:grid-cols-2 gap-4 purchase-link-row';
    newRow.innerHTML = `
        <select name="purchase_links[${purchaseLinkIndex}][platform]" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <option value="">Select Platform</option>
            <option value="Amazon">Amazon</option>
            <option value="Kobo">Kobo</option>
            <option value="Apple Books">Apple Books</option>
            <option value="Google Play">Google Play</option>
            <option value="Author's Website">Author's Website</option>
            <option value="Bookshop.org">Bookshop.org</option>
            <option value="Audible">Audible</option>
            <option value="Other">Other</option>
        </select>
        <input type="url" name="purchase_links[${purchaseLinkIndex}][url]" placeholder="Purchase URL" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <button type="button" onclick="this.parentElement.remove()" class="md:col-span-2 px-4 py-2 bg-red-200 text-red-700 rounded-lg hover:bg-red-300">Remove</button>
    `;
    container.appendChild(newRow);
    purchaseLinkIndex++;
}

// Form submission
document.getElementById('bookForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    fetch('/api/books-adverts', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Book posted successfully!');
            if (data.payment_required && data.payment_amount > 0) {
                // Redirect to payment page
                window.location.href = `/payment/${data.data.id}`;
            } else {
                // Redirect to book page
                window.location.href = `/books/${data.data.slug}`;
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Book Advert';
    });
});

// Initialize
updateButtons();
</script>
@endsection
