@extends('frontend.layouts.app')

@section('title', 'Browse Books')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Browse Books</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Books</label>
                    <input type="text" id="searchInput" placeholder="Search by title, author, or ISBN..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Genre Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                    <select id="genreFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Genres</option>
                        <option value="fiction">Fiction</option>
                        <option value="non-fiction">Non-Fiction</option>
                        <option value="children">Children</option>
                        <option value="academic">Academic</option>
                        <option value="poetry">Poetry</option>
                        <option value="business">Business</option>
                    </select>
                </div>
                
                <!-- Format Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                    <select id="formatFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Formats</option>
                        <option value="paperback">Paperback</option>
                        <option value="hardcover">Hardcover</option>
                        <option value="ebook">Ebook</option>
                        <option value="audiobook">Audiobook</option>
                    </select>
                </div>
                
                <!-- Sort -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                    <select id="sortBy" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="created_at">Newest First</option>
                        <option value="title">Title A-Z</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="views_count">Most Viewed</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button onclick="searchBooks()" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
            </div>
        </div>

        <!-- Featured Books -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Books</h2>
            <div id="featuredBooks" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Books will be loaded here -->
            </div>
        </div>

        <!-- All Books -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">All Books</h2>
            <div id="booksGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Books will be loaded here -->
            </div>
            
            <!-- Load More Button -->
            <div class="mt-8 text-center">
                <button id="loadMoreBtn" onclick="loadMoreBooks()" 
                        class="bg-gray-200 text-gray-800 px-8 py-3 rounded-lg font-medium hover:bg-gray-300 transition-colors">
                    Load More Books
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let isLoading = false;

// Load initial books
document.addEventListener('DOMContentLoaded', function() {
    loadBooks();
    loadFeaturedBooks();
});

function loadBooks() {
    if (isLoading) return;
    isLoading = true;
    
    const search = document.getElementById('searchInput').value;
    const genre = document.getElementById('genreFilter').value;
    const format = document.getElementById('formatFilter').value;
    const sortBy = document.getElementById('sortBy').value;
    
    const params = new URLSearchParams({
        search: search,
        genre: genre,
        format: format,
        sort_by: sortBy,
        sort_order: 'desc',
        per_page: 12,
        page: currentPage
    });
    
    fetch(`/api/v1/books-adverts?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.data) {
                displayBooks(data.data.data);
                
                // Hide load more if no more pages
                if (data.data.current_page >= data.data.last_page) {
                    document.getElementById('loadMoreBtn').style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error loading books:', error))
        .finally(() => {
            isLoading = false;
        });
}

function loadFeaturedBooks() {
    fetch('/api/v1/books-adverts/featured')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                displayFeaturedBooks(data.data);
            }
        })
        .catch(error => console.error('Error loading featured books:', error));
}

function displayBooks(books) {
    const grid = document.getElementById('booksGrid');
    
    if (currentPage === 1) {
        grid.innerHTML = '';
    }
    
    books.forEach(book => {
        const bookCard = createBookCard(book);
        grid.appendChild(bookCard);
    });
}

function displayFeaturedBooks(books) {
    const grid = document.getElementById('featuredBooks');
    grid.innerHTML = '';
    
    books.forEach(book => {
        const bookCard = createBookCard(book);
        grid.appendChild(bookCard);
    });
}

function createBookCard(book) {
    const div = document.createElement('div');
    div.className = 'bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer';
    div.innerHTML = `
        <div onclick="window.location.href='/books/${book.slug}'" class="p-4">
            <div class="aspect-w-3 aspect-h-4 mb-4">
                <img src="${book.cover_image_url || '/images/default-book.png'}" 
                     alt="${book.title}" 
                     class="w-full h-full object-cover rounded-lg">
            </div>
            <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1">${book.title}</h3>
            <p class="text-sm text-gray-600 mb-2">by ${book.author_name}</p>
            <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-blue-600">$${parseFloat(book.price).toFixed(2)}</span>
                <span class="px-2 py-1 text-xs font-medium rounded-full ${getPromotionBadgeClass(book.advert_type)}">
                    ${getPromotionLabel(book.advert_type)}
                </span>
            </div>
        </div>
    `;
    return div;
}

function getPromotionBadgeClass(type) {
    switch(type) {
        case 'featured': return 'bg-yellow-100 text-yellow-800';
        case 'sponsored': return 'bg-red-100 text-red-800';
        case 'promoted': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getPromotionLabel(type) {
    switch(type) {
        case 'featured': return 'Featured';
        case 'sponsored': return 'Sponsored';
        case 'promoted': return 'Promoted';
        default: return 'Basic';
    }
}

function searchBooks() {
    currentPage = 1;
    document.getElementById('loadMoreBtn').style.display = 'block';
    loadBooks();
}

function loadMoreBooks() {
    currentPage++;
    loadBooks();
}
</script>
@endsection
