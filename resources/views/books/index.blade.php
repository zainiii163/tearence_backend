@extends('layouts.app')

@section('title', 'Books - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Explore Books From Authors Around the World</h1>
                <p class="text-xl mb-8">Find your next read or promote your book to a global audience</p>
                
                <!-- Search Bar -->
                <div class="max-w-3xl mx-auto">
                    <form id="searchForm" class="bg-white rounded-lg shadow-lg p-2 flex flex-col md:flex-row gap-2">
                        <input type="text" id="searchInput" placeholder="Search by title, author, or keyword..." class="flex-1 px-4 py-3 text-gray-900 focus:outline-none">
                        <select id="genreFilter" class="px-4 py-3 text-gray-900 border-l focus:outline-none">
                            <option value="">All Genres</option>
                        </select>
                        <select id="countryFilter" class="px-4 py-3 text-gray-900 border-l focus:outline-none">
                            <option value="">All Countries</option>
                        </select>
                        <select id="formatFilter" class="px-4 py-3 text-gray-900 border-l focus:outline-none">
                            <option value="">All Formats</option>
                            <option value="paperback">Paperback</option>
                            <option value="hardcover">Hardcover</option>
                            <option value="ebook">eBook</option>
                            <option value="audiobook">Audiobook</option>
                        </select>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg font-medium transition">
                            Search
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-lg p-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Filters</h3>
                    
                    <!-- Price Range -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-700 mb-2">Price Range</h4>
                        <div class="flex gap-2">
                            <input type="number" id="minPrice" placeholder="Min" class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <input type="number" id="maxPrice" placeholder="Max" class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <!-- Book Type -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-700 mb-2">Book Type</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" value="fiction" class="book-type-filter mr-2">
                                <span>Fiction</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="non-fiction" class="book-type-filter mr-2">
                                <span>Non-Fiction</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="children" class="book-type-filter mr-2">
                                <span>Children's Books</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="poetry" class="book-type-filter mr-2">
                                <span>Poetry</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="academic" class="book-type-filter mr-2">
                                <span>Academic</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="self-help" class="book-type-filter mr-2">
                                <span>Self-Help</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="business" class="book-type-filter mr-2">
                                <span>Business</span>
                            </label>
                        </div>
                    </div>

                    <!-- Verified Authors -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="verifiedOnly" class="mr-2">
                            <span class="font-medium text-gray-700">Verified Authors Only</span>
                        </label>
                    </div>

                    <!-- Promoted Books -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="promotedOnly" class="mr-2">
                            <span class="font-medium text-gray-700">Promoted Books Only</span>
                        </label>
                    </div>

                    <!-- Sort By -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-700 mb-2">Sort By</h4>
                        <select id="sortBy" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="created_at">Newest First</option>
                            <option value="title">Title A-Z</option>
                            <option value="price_low">Price Low to High</option>
                            <option value="price_high">Price High to Low</option>
                            <option value="views_count">Most Viewed</option>
                            <option value="saves_count">Most Saved</option>
                        </select>
                    </div>

                    <button onclick="applyFilters()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition">
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Books Grid -->
            <div class="lg:w-3/4">
                <!-- Featured Books Section -->
                <div id="featuredSection" class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">🌟 Featured Books</h2>
                    <div id="featuredBooks" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Featured books will be loaded here -->
                    </div>
                </div>

                <!-- All Books Section -->
                <div>
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">All Books</h2>
                        <div class="flex items-center gap-4">
                            <span id="resultCount" class="text-gray-600"></span>
                            <div class="flex gap-2">
                                <button onclick="setViewMode('grid')" id="gridViewBtn" class="p-2 bg-blue-600 text-white rounded-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM13 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"/>
                                    </svg>
                                </button>
                                <button onclick="setViewMode('list')" id="listViewBtn" class="p-2 bg-gray-200 text-gray-700 rounded-lg">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Loading State -->
                    <div id="loadingState" class="text-center py-12 hidden">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">Loading books...</p>
                    </div>

                    <!-- Books Grid/List -->
                    <div id="booksContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Books will be loaded here -->
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-12 hidden">
                        <div class="text-6xl mb-4">📚</div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No books found</h3>
                        <p class="text-gray-600">Try adjusting your filters or search terms</p>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination" class="mt-8 flex justify-center">
                        <!-- Pagination will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Book Detail Modal -->
    <div id="bookModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 id="modalTitle" class="text-2xl font-bold text-gray-900"></h2>
                    <button onclick="closeBookModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                <div id="modalContent">
                    <!-- Book details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentFilters = {};
let viewMode = 'grid';
let allBooks = [];
let featuredBooks = [];

// Load data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadFeaturedBooks();
    loadBooks();
    loadFilterOptions();
});

function loadFeaturedBooks() {
    fetch('/api/books-adverts/featured')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                featuredBooks = data.data;
                renderFeaturedBooks();
            }
        })
        .catch(error => console.error('Error loading featured books:', error));
}

function renderFeaturedBooks() {
    const container = document.getElementById('featuredBooks');
    if (featuredBooks.length === 0) {
        container.innerHTML = '<p class="text-gray-500 col-span-full">No featured books at the moment.</p>';
        return;
    }
    
    container.innerHTML = featuredBooks.map(book => createBookCard(book)).join('');
}

function loadBooks(page = 1) {
    showLoadingState();
    
    const params = new URLSearchParams({
        page: page,
        per_page: 12,
        ...currentFilters
    });
    
    fetch(`/api/books-adverts?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allBooks = data.data.data;
                renderBooks(data.data);
                updateResultCount(data.data.total);
                renderPagination(data.data);
            }
        })
        .catch(error => {
            console.error('Error loading books:', error);
            hideLoadingState();
        });
}

function renderBooks(booksData) {
    const container = document.getElementById('booksContainer');
    const emptyState = document.getElementById('emptyState');
    
    if (booksData.data.length === 0) {
        container.innerHTML = '';
        emptyState.classList.remove('hidden');
    } else {
        emptyState.classList.add('hidden');
        container.innerHTML = booksData.data.map(book => createBookCard(book)).join('');
    }
    
    hideLoadingState();
}

function createBookCard(book) {
    const badgeColors = {
        'promoted': 'bg-blue-100 text-blue-800',
        'featured': 'bg-yellow-100 text-yellow-800',
        'sponsored': 'bg-orange-100 text-orange-800',
        'top_category': 'bg-red-100 text-red-800'
    };
    
    const badgeClass = badgeColors[book.advert_type] || 'bg-gray-100 text-gray-800';
    
    return `
        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow cursor-pointer" onclick="showBookDetails('${book.slug}')">
            <div class="relative">
                <img src="${book.cover_image_url || '/placeholder.png'}" alt="${book.title}" class="w-full h-64 object-cover">
                ${book.advert_type !== 'standard' ? `<div class="absolute top-2 right-2"><span class="px-2 py-1 text-xs font-semibold rounded-full ${badgeClass}">${book.advert_type}</span></div>` : ''}
                ${book.verified_author ? '<div class="absolute top-2 left-2"><span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✓ Verified Author</span></div>' : ''}
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2">${book.title}</h3>
                <p class="text-sm text-gray-600 mb-2">by ${book.author_name}</p>
                <p class="text-sm text-gray-500 mb-3">${book.genre} • ${book.format}</p>
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold text-gray-900">$${book.price}</span>
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <span>👁 ${book.views_count || 0}</span>
                        <span>❤️ ${book.saves_count || 0}</span>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button onclick="event.stopPropagation(); saveBook('${book.slug}')" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm transition">
                        Save
                    </button>
                    <button onclick="event.stopPropagation(); showBookDetails('${book.slug}')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded-lg text-sm transition">
                        View Details
                    </button>
                </div>
            </div>
        </div>
    `;
}

function showBookDetails(slug) {
    fetch(`/api/books-adverts/${slug}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const book = data.data;
                document.getElementById('modalTitle').textContent = book.title;
                document.getElementById('modalContent').innerHTML = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <img src="${book.cover_image_url || '/placeholder.png'}" alt="${book.title}" class="w-full rounded-lg">
                            <div class="mt-4 flex gap-2">
                                ${book.additional_images ? book.additional_images.map(img => `<img src="${img}" alt="Additional image" class="w-20 h-20 object-cover rounded">`).join('') : ''}
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">${book.title}</h3>
                            <p class="text-lg text-gray-600 mb-4">by ${book.author_name}</p>
                            
                            <div class="mb-4">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">${book.genre}</span>
                                <span class="inline-block px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm ml-2">${book.format}</span>
                            </div>
                            
                            <div class="text-2xl font-bold text-gray-900 mb-4">$${book.price}</div>
                            
                            <div class="prose max-w-none mb-6">
                                <p>${book.description}</p>
                            </div>
                            
                            ${book.purchase_links && book.purchase_links.length > 0 ? `
                                <div class="mb-6">
                                    <h4 class="font-semibold text-gray-900 mb-2">Purchase Links:</h4>
                                    <div class="space-y-2">
                                        ${book.purchase_links.map(link => `
                                            <a href="${link.url}" target="_blank" class="block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-center transition">
                                                Buy on ${link.platform}
                                            </a>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="flex gap-2">
                                <button onclick="saveBook('${book.slug}'); closeBookModal();" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg transition">
                                    Save Book
                                </button>
                                <button onclick="closeBookModal()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('bookModal').classList.remove('hidden');
            }
        })
        .catch(error => console.error('Error loading book details:', error));
}

function closeBookModal() {
    document.getElementById('bookModal').classList.add('hidden');
}

function saveBook(slug) {
    fetch(`/api/books-adverts/${slug}/save`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Refresh the books to update save counts
            loadBooks(currentPage);
        }
    })
    .catch(error => console.error('Error saving book:', error));
}

function loadFilterOptions() {
    // Load genres, countries, etc. from API
    fetch('/api/books-adverts')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const genreFilter = document.getElementById('genreFilter');
                const countryFilter = document.getElementById('countryFilter');
                
                // Add genre options
                data.filters.genres.forEach(genre => {
                    const option = document.createElement('option');
                    option.value = genre;
                    option.textContent = genre;
                    genreFilter.appendChild(option);
                });
                
                // Add country options
                data.filters.countries.forEach(country => {
                    const option = document.createElement('option');
                    option.value = country;
                    option.textContent = country;
                    countryFilter.appendChild(option);
                });
            }
        });
}

function applyFilters() {
    currentFilters = {
        search: document.getElementById('searchInput').value,
        genre: document.getElementById('genreFilter').value,
        country: document.getElementById('countryFilter').value,
        format: document.getElementById('formatFilter').value,
        min_price: document.getElementById('minPrice').value,
        max_price: document.getElementById('maxPrice').value,
        book_type: getCheckedValues('.book-type-filter'),
        verified_only: document.getElementById('verifiedOnly').checked,
        promoted_only: document.getElementById('promotedOnly').checked,
        sort_by: document.getElementById('sortBy').value,
        sort_order: document.getElementById('sortBy').value.includes('price_high') ? 'desc' : 'asc'
    };
    
    // Remove empty values
    Object.keys(currentFilters).forEach(key => {
        if (!currentFilters[key] || (Array.isArray(currentFilters[key]) && currentFilters[key].length === 0)) {
            delete currentFilters[key];
        }
    });
    
    currentPage = 1;
    loadBooks();
}

function getCheckedValues(selector) {
    const checkboxes = document.querySelectorAll(selector);
    return Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);
}

function setViewMode(mode) {
    viewMode = mode;
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    const container = document.getElementById('booksContainer');
    
    if (mode === 'grid') {
        gridBtn.className = 'p-2 bg-blue-600 text-white rounded-lg';
        listBtn.className = 'p-2 bg-gray-200 text-gray-700 rounded-lg';
        container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
    } else {
        gridBtn.className = 'p-2 bg-gray-200 text-gray-700 rounded-lg';
        listBtn.className = 'p-2 bg-blue-600 text-white rounded-lg';
        container.className = 'space-y-4';
    }
}

function updateResultCount(total) {
    document.getElementById('resultCount').textContent = `${total} books found`;
}

function renderPagination(data) {
    const container = document.getElementById('pagination');
    if (data.last_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let pagination = '<div class="flex gap-2">';
    
    // Previous button
    if (data.prev_page_url) {
        pagination += `<button onclick="loadBooks(${data.current_page - 1})" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Previous</button>`;
    }
    
    // Page numbers
    for (let i = Math.max(1, data.current_page - 2); i <= Math.min(data.last_page, data.current_page + 2); i++) {
        const isActive = i === data.current_page;
        pagination += `<button onclick="loadBooks(${i})" class="px-3 py-2 ${isActive ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'} rounded-lg">${i}</button>`;
    }
    
    // Next button
    if (data.next_page_url) {
        pagination += `<button onclick="loadBooks(${data.current_page + 1})" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Next</button>`;
    }
    
    pagination += '</div>';
    container.innerHTML = pagination;
    currentPage = data.current_page;
}

function showLoadingState() {
    document.getElementById('loadingState').classList.remove('hidden');
    document.getElementById('booksContainer').classList.add('hidden');
}

function hideLoadingState() {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('booksContainer').classList.remove('hidden');
}

// Search form submission
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    applyFilters();
});

// Auto-apply filters on change
document.querySelectorAll('#genreFilter, #countryFilter, #formatFilter, #sortBy').forEach(element => {
    element.addEventListener('change', applyFilters);
});
</script>
@endsection
