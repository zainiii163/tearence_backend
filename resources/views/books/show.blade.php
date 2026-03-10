@extends('layouts.app')

@section('title', 'Book Details - World Wide Adverts')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <!-- Book Details will be loaded here dynamically -->
    <div id="bookDetail" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-gray-600">Loading book details...</p>
        </div>

        <!-- Book Content -->
        <div id="bookContent" class="hidden">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <!-- Book Header -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <div class="flex flex-col md:flex-row gap-6">
                            <div class="flex-shrink-0">
                                <img id="bookCover" src="" alt="" class="w-48 h-72 object-cover rounded-lg shadow-lg">
                                <div class="mt-4 flex gap-2">
                                    <div id="additionalImages" class="flex gap-2">
                                        <!-- Additional images will be loaded here -->
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h1 id="bookTitle" class="text-3xl font-bold text-gray-900 mb-2"></h1>
                                        <p id="bookSubtitle" class="text-lg text-gray-600 mb-2"></p>
                                        <p class="text-lg text-gray-700">by <span id="authorName" class="font-medium"></span></p>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <span id="verifiedBadge" class="hidden px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold text-center">✓ Verified Author</span>
                                        <span id="advertBadge" class="px-3 py-1 rounded-full text-sm font-semibold text-center"></span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span id="genreBadge" class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm"></span>
                                    <span id="formatBadge" class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm"></span>
                                    <span id="countryBadge" class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm"></span>
                                    <span id="languageBadge" class="px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full text-sm"></span>
                                </div>

                                <div class="flex items-center justify-between mb-4">
                                    <div class="text-3xl font-bold text-gray-900" id="bookPrice"></div>
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span>👁 <span id="viewCount"></span> views</span>
                                        <span>❤️ <span id="saveCount"></span> saves</span>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <button onclick="saveBook()" id="saveBtn" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition">
                                        <span id="saveBtnText">Save Book</span>
                                    </button>
                                    <button onclick="shareBook()" class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                        Share
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Book Description -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">📖 Description</h2>
                        <div id="bookDescription" class="prose max-w-none text-gray-700"></div>
                    </div>

                    <!-- Book Details -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">📄 Book Details</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">ISBN:</span>
                                    <span id="bookISBN" class="text-gray-900"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Publisher:</span>
                                    <span id="bookPublisher" class="text-gray-900"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Publication Date:</span>
                                    <span id="bookPublicationDate" class="text-gray-900"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Pages:</span>
                                    <span id="bookPages" class="text-gray-900"></span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Age Range:</span>
                                    <span id="bookAgeRange" class="text-gray-900"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Series:</span>
                                    <span id="bookSeries" class="text-gray-900"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Edition:</span>
                                    <span id="bookEdition" class="text-gray-900"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-600">Format:</span>
                                    <span id="bookFormat" class="text-gray-900"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Author Information -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">🧑‍💼 About the Author</h2>
                        <div class="flex gap-6">
                            <img id="authorPhoto" src="" alt="" class="w-24 h-24 rounded-full object-cover">
                            <div class="flex-1">
                                <h3 id="authorName" class="text-xl font-semibold text-gray-900 mb-2"></h3>
                                <p id="authorBio" class="text-gray-700 mb-4"></p>
                                <div id="authorSocialLinks" class="flex gap-3">
                                    <!-- Social links will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sample Files -->
                    <div id="sampleFilesSection" class="bg-white rounded-lg shadow-lg p-6 mb-6 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">📋 Sample Files</h2>
                        <div id="sampleFiles" class="space-y-3">
                            <!-- Sample files will be loaded here -->
                        </div>
                    </div>

                    <!-- Book Trailer -->
                    <div id="trailerSection" class="bg-white rounded-lg shadow-lg p-6 mb-6 hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">🎬 Book Trailer</h2>
                        <div class="aspect-w-16 aspect-h-9">
                            <iframe id="trailerVideo" src="" frameborder="0" allowfullscreen class="w-full h-96 rounded-lg"></iframe>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Purchase Links -->
                    <div id="purchaseSection" class="bg-white rounded-lg shadow-lg p-6 mb-6 sticky top-4">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">🛒 Purchase This Book</h2>
                        <div id="purchaseLinks" class="space-y-3">
                            <!-- Purchase links will be loaded here -->
                        </div>
                    </div>

                    <!-- Book Stats -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Book Stats</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Views:</span>
                                <span id="totalViews" class="font-medium text-gray-900"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Saves:</span>
                                <span id="totalSaves" class="font-medium text-gray-900"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Posted:</span>
                                <span id="postedDate" class="font-medium text-gray-900"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span id="bookStatus" class="font-medium"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Similar Books -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📚 Similar Books</h3>
                        <div id="similarBooks" class="space-y-4">
                            <!-- Similar books will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div id="errorState" class="hidden text-center py-12">
            <div class="text-6xl mb-4">📚</div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Book Not Found</h2>
            <p class="text-gray-600 mb-6">The book you're looking for doesn't exist or has been removed.</p>
            <a href="/books" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition">
                Browse All Books
            </a>
        </div>
    </div>
</div>

<script>
let bookSlug = '';
let bookData = null;
let isSaved = false;

// Load book data on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get slug from URL
    const pathParts = window.location.pathname.split('/');
    bookSlug = pathParts[pathParts.length - 1];
    
    if (bookSlug) {
        loadBookDetails();
    } else {
        showError();
    }
});

function loadBookDetails() {
    fetch(`/api/books-adverts/${bookSlug}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bookData = data.data;
                renderBookDetails();
                loadSimilarBooks();
            } else {
                showError();
            }
        })
        .catch(error => {
            console.error('Error loading book details:', error);
            showError();
        });
}

function renderBookDetails() {
    const book = bookData;
    
    // Hide loading, show content
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('bookContent').classList.remove('hidden');
    
    // Basic info
    document.getElementById('bookTitle').textContent = book.title;
    document.getElementById('bookSubtitle').textContent = book.subtitle || '';
    document.getElementById('authorName').textContent = book.author_name;
    document.getElementById('bookPrice').textContent = `$${book.price}`;
    document.getElementById('viewCount').textContent = book.views_count || 0;
    document.getElementById('saveCount').textContent = book.saves_count || 0;
    
    // Book cover
    document.getElementById('bookCover').src = book.cover_image_url || '/placeholder.png';
    document.getElementById('bookCover').alt = book.title;
    
    // Additional images
    if (book.additional_images && book.additional_images.length > 0) {
        const container = document.getElementById('additionalImages');
        container.innerHTML = book.additional_images.map(img => 
            `<img src="${img}" alt="Additional image" class="w-16 h-20 object-cover rounded cursor-pointer hover:opacity-75" onclick="changeMainImage('${img}')">`
        ).join('');
    }
    
    // Badges
    if (book.verified_author) {
        document.getElementById('verifiedBadge').classList.remove('hidden');
    }
    
    const advertBadge = document.getElementById('advertBadge');
    const badgeColors = {
        'promoted': 'bg-blue-100 text-blue-800',
        'featured': 'bg-yellow-100 text-yellow-800',
        'sponsored': 'bg-orange-100 text-orange-800',
        'top_category': 'bg-red-100 text-red-800'
    };
    advertBadge.textContent = book.advert_type === 'standard' ? 'Standard' : book.advert_type.replace('_', ' ').toUpperCase();
    advertBadge.className = `px-3 py-1 rounded-full text-sm font-semibold text-center ${badgeColors[book.advert_type] || 'bg-gray-100 text-gray-800'}`;
    
    // Category badges
    document.getElementById('genreBadge').textContent = book.genre;
    document.getElementById('formatBadge').textContent = book.format;
    document.getElementById('countryBadge').textContent = book.country;
    document.getElementById('languageBadge').textContent = book.language;
    
    // Description
    document.getElementById('bookDescription').innerHTML = `<p>${book.description}</p>`;
    
    // Book details
    document.getElementById('bookISBN').textContent = book.isbn || 'N/A';
    document.getElementById('bookPublisher').textContent = book.publisher || 'N/A';
    document.getElementById('bookPublicationDate').textContent = book.publication_date ? new Date(book.publication_date).toLocaleDateString() : 'N/A';
    document.getElementById('bookPages').textContent = book.pages || 'N/A';
    document.getElementById('bookAgeRange').textContent = book.age_range || 'N/A';
    document.getElementById('bookSeries').textContent = book.series_name || 'N/A';
    document.getElementById('bookEdition').textContent = book.edition || 'N/A';
    document.getElementById('bookFormat').textContent = book.format;
    
    // Author info
    document.getElementById('authorPhoto').src = book.author_photo || '/placeholder-avatar.png';
    document.getElementById('authorPhoto').alt = book.author_name;
    document.getElementById('authorBio').textContent = book.author_bio || 'No bio available.';
    
    // Author social links
    if (book.author_social_links && book.author_social_links.length > 0) {
        const container = document.getElementById('authorSocialLinks');
        container.innerHTML = book.author_social_links.map(link => 
            `<a href="${link}" target="_blank" class="text-blue-600 hover:text-blue-800">🔗 Link</a>`
        ).join('');
    }
    
    // Purchase links
    if (book.purchase_links && book.purchase_links.length > 0) {
        const container = document.getElementById('purchaseLinks');
        container.innerHTML = book.purchase_links.map(link => 
            `<a href="${link.url}" target="_blank" class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg text-center font-medium transition">
                Buy on ${link.platform}
            </a>`
        ).join('');
    } else {
        document.getElementById('purchaseLinks').innerHTML = '<p class="text-gray-500">No purchase links available</p>';
    }
    
    // Sample files
    if (book.sample_files && book.sample_files.length > 0) {
        const section = document.getElementById('sampleFilesSection');
        const container = document.getElementById('sampleFiles');
        section.classList.remove('hidden');
        
        container.innerHTML = book.sample_files.map(file => 
            `<div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">${getFileIcon(file.type)}</span>
                    <div>
                        <div class="font-medium text-gray-900">${file.name}</div>
                        <div class="text-sm text-gray-500">${file.type.toUpperCase()}</div>
                    </div>
                </div>
                <button onclick="downloadSample('${file.path}')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition">
                    Download
                </button>
            </div>`
        ).join('');
    }
    
    // Trailer video
    if (book.trailer_video_url) {
        const section = document.getElementById('trailerSection');
        const video = document.getElementById('trailerVideo');
        section.classList.remove('hidden');
        
        // Convert YouTube URL to embed URL
        const embedUrl = convertToEmbedUrl(book.trailer_video_url);
        video.src = embedUrl;
    }
    
    // Stats
    document.getElementById('totalViews').textContent = book.views_count || 0;
    document.getElementById('totalSaves').textContent = book.saves_count || 0;
    document.getElementById('postedDate').textContent = new Date(book.created_at).toLocaleDateString();
    
    const statusElement = document.getElementById('bookStatus');
    const statusColors = {
        'active': 'text-green-600',
        'pending': 'text-yellow-600',
        'rejected': 'text-red-600',
        'inactive': 'text-gray-600'
    };
    statusElement.textContent = book.status.charAt(0).toUpperCase() + book.status.slice(1);
    statusElement.className = `font-medium ${statusColors[book.status] || 'text-gray-600'}`;
}

function loadSimilarBooks() {
    if (!bookData.genre) return;
    
    fetch(`/api/books-adverts/genre/${encodeURIComponent(bookData.genre)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const similarBooks = data.data.data.filter(book => book.slug !== bookSlug).slice(0, 3);
                renderSimilarBooks(similarBooks);
            }
        })
        .catch(error => console.error('Error loading similar books:', error));
}

function renderSimilarBooks(books) {
    const container = document.getElementById('similarBooks');
    
    if (books.length === 0) {
        container.innerHTML = '<p class="text-gray-500">No similar books found</p>';
        return;
    }
    
    container.innerHTML = books.map(book => `
        <div class="flex gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 cursor-pointer transition" onclick="window.location.href='/books/${book.slug}'">
            <img src="${book.cover_image_url || '/placeholder.png'}" alt="${book.title}" class="w-12 h-16 object-cover rounded">
            <div class="flex-1">
                <h4 class="font-medium text-gray-900 text-sm line-clamp-1">${book.title}</h4>
                <p class="text-xs text-gray-600">${book.author_name}</p>
                <p class="text-sm font-medium text-gray-900">$${book.price}</p>
            </div>
        </div>
    `).join('');
}

function changeMainImage(imageUrl) {
    document.getElementById('bookCover').src = imageUrl;
}

function getFileIcon(fileType) {
    const icons = {
        'pdf': '📄',
        'mp3': '🎵',
        'm4a': '🎵',
        'wav': '🎵',
        'epub': '📱'
    };
    return icons[fileType.toLowerCase()] || '📄';
}

function convertToEmbedUrl(url) {
    // Handle YouTube URLs
    const youtubeRegex = /(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\n?#]+)/;
    const match = url.match(youtubeRegex);
    if (match) {
        return `https://www.youtube.com/embed/${match[1]}`;
    }
    
    // Handle other video platforms or return original
    return url;
}

function saveBook() {
    const btn = document.getElementById('saveBtn');
    const btnText = document.getElementById('saveBtnText');
    
    btn.disabled = true;
    btnText.textContent = 'Saving...';
    
    fetch(`/api/books-adverts/${bookSlug}/save`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            isSaved = data.saved;
            btnText.textContent = isSaved ? 'Saved ✓' : 'Save Book';
            btn.className = isSaved ? 
                'flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition' :
                'flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-lg transition';
            
            // Update save count
            document.getElementById('saveCount').textContent = data.saves_count;
            document.getElementById('totalSaves').textContent = data.saves_count;
            
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error saving book:', error);
        alert('Error saving book. Please try again.');
    })
    .finally(() => {
        btn.disabled = false;
    });
}

function shareBook() {
    if (navigator.share) {
        navigator.share({
            title: bookData.title,
            text: `Check out this book: ${bookData.title} by ${bookData.author_name}`,
            url: window.location.href
        });
    } else {
        // Fallback: Copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Book link copied to clipboard!');
        });
    }
}

function downloadSample(filePath) {
    window.open(`/storage/${filePath}`, '_blank');
}

function showError() {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('bookContent').classList.add('hidden');
    document.getElementById('errorState').classList.remove('hidden');
}
</script>
@endsection
