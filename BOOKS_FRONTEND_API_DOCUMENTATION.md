# Books API Frontend Integration Documentation

## Overview

This document provides comprehensive API documentation for integrating the Books functionality into your frontend application. The Books system allows authors and publishers to advertise and sell books with advanced filtering, search, and promotional features.

## Base URL

```
https://your-domain.com/api/v1/books-adverts
```

## Authentication

Most endpoints require authentication using Bearer tokens:

```javascript
headers: {
    'Authorization': 'Bearer ' + authToken,
    'Content-Type': 'application/json'
}
```

## Public Endpoints (No Authentication Required)

### 1. Get All Books
**Endpoint:** `GET /api/v1/books-adverts`

Retrieve a paginated list of all active books with optional filtering.

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page, max 50 (default: 12)
- `search` (string): Search in title, description, author, publisher
- `genre` (string): Filter by genre
- `country` (string): Filter by country
- `format` (string): Filter by format ('paperback', 'hardcover', 'ebook', 'audiobook')
- `book_type` (string): Filter by book type (fiction, non-fiction, academic, etc.)
- `language` (string): Filter by language
- `min_price` (float): Minimum price filter
- `max_price` (float): Maximum price filter
- `verified_only` (boolean): Show only verified authors
- `promoted_only` (boolean): Show only promoted books
- `sort_by` (string): Sort by field ('created_at', 'title', 'price', 'views_count', 'saves_count', 'rating')
- `sort_order` (string): Sort order ('asc', 'desc')

**Example Request:**
```javascript
fetch('/api/v1/books-adverts?search=fiction&genre=romance&min_price=10&max_price=50&verified_only=true&sort_by=rating&sort_order=desc')
```

**Response:**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": "uuid-string",
                "title": "The Great Adventure",
                "slug": "the-great-adventure",
                "description": "An exciting tale of courage and discovery...",
                "author_name": "John Smith",
                "publisher": "Adventure Publishing",
                "isbn": "978-1234567890",
                "genre": "Fiction",
                "book_type": "Fiction",
                "language": "English",
                "format": "paperback",
                "pages": 350,
                "publication_date": "2024-01-15",
                "price": 24.99,
                "currency": "USD",
                "country": "USA",
                "views_count": 1250,
                "saves_count": 89,
                "rating": 4.5,
                "verified_author": true,
                "advert_type": "featured",
                "promoted_until": "2024-02-15T23:59:59Z",
                "cover_image": "/storage/covers/the-great-adventure.jpg",
                "additional_images": [
                    "/storage/images/book1-1.jpg",
                    "/storage/images/book1-2.jpg"
                ],
                "purchase_links": [
                    {
                        "platform": "Amazon",
                        "url": "https://amazon.com/dp/1234567890",
                        "price": 24.99
                    }
                ],
                "sample_files": [
                    {
                        "type": "pdf",
                        "url": "/storage/samples/chapter1.pdf",
                        "title": "Chapter 1 Sample"
                    }
                ],
                "author_bio": "John Smith is an award-winning author...",
                "author_social_links": {
                    "twitter": "https://twitter.com/johnsmith",
                    "website": "https://johnsmith.com"
                },
                "created_at": "2024-01-10T10:30:00Z",
                "updated_at": "2024-01-15T14:20:00Z",
                "user": {
                    "id": 1,
                    "name": "John Smith",
                    "email": "john@example.com"
                },
                "pricing_plan": {
                    "id": "uuid",
                    "name": "Featured Plan",
                    "duration_days": 30,
                    "price": 29.99
                }
            }
        ],
        "pagination": {
            "currentPage": 1,
            "totalPages": 8,
            "totalItems": 95,
            "itemsPerPage": 12,
            "hasNextPage": true,
            "hasPrevPage": false
        }
    }
}
```

### 2. Get Single Book
**Endpoint:** `GET /api/v1/books-adverts/{slug}`

Retrieve detailed information about a specific book using its slug.

**Response:** Same structure as individual item in the list response, but with all details.

### 3. Get Featured Books
**Endpoint:** `GET /api/v1/books-adverts/featured`

Retrieve all featured/promoted books.

### 4. Get Books by Genre
**Endpoint:** `GET /api/v1/books-adverts/genre/{genre}`

Retrieve books filtered by a specific genre.

### 5. Get Pricing Plans
**Endpoint:** `GET /api/v1/books-adverts/pricing-plans`

Retrieve available pricing plans for book promotions.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "name": "Basic Plan",
            "description": "Basic listing for 30 days",
            "duration_days": 30,
            "price": 9.99,
            "currency": "USD",
            "features": [
                "Basic listing",
                "30 days duration",
                "Standard visibility"
            ],
            "is_active": true
        },
        {
            "id": "uuid",
            "name": "Featured Plan",
            "description": "Featured listing with enhanced visibility",
            "duration_days": 30,
            "price": 29.99,
            "currency": "USD",
            "features": [
                "Featured placement",
                "30 days duration",
                "Enhanced visibility",
                "Social media promotion"
            ],
            "is_active": true
        }
    ]
}
```

### 6. Get Statistics
**Endpoint:** `GET /api/v1/books-adverts/statistics`

Retrieve overall books system statistics.

**Response:**
```json
{
    "success": true,
    "data": {
        "total_books": 1250,
        "active_books": 1180,
        "verified_authors": 450,
        "total_genres": 25,
        "average_rating": 4.2,
        "most_popular_genre": "Fiction",
        "recent_books_count": 45,
        "featured_books_count": 28
    }
}
```

## Protected Endpoints (Authentication Required)

### 1. Create Book Advert
**Endpoint:** `POST /api/v1/books-adverts`

Create a new book advert.

**Request Body:**
```json
{
    "title": "The Great Adventure",
    "description": "An exciting tale of courage and discovery that will take readers on an unforgettable journey...",
    "author_name": "John Smith",
    "publisher": "Adventure Publishing",
    "isbn": "978-1234567890",
    "genre": "Fiction",
    "book_type": "Fiction",
    "language": "English",
    "format": "paperback",
    "pages": 350,
    "publication_date": "2024-01-15",
    "price": 24.99,
    "currency": "USD",
    "country": "USA",
    "author_bio": "John Smith is an award-winning author with over 20 years of experience...",
    "cover_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
    "additional_images": [
        "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
        "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
    ],
    "purchase_links": [
        {
            "platform": "Amazon",
            "url": "https://amazon.com/dp/1234567890",
            "price": 24.99
        },
        {
            "platform": "Barnes & Noble",
            "url": "https://barnesandnoble.com/w/1234567890",
            "price": 24.99
        }
    ],
    "sample_files": [
        {
            "type": "pdf",
            "file": "data:application/pdf;base64,JVBERi0xLjQK...",
            "title": "Chapter 1 Sample"
        }
    ],
    "author_social_links": {
        "twitter": "https://twitter.com/johnsmith",
        "website": "https://johnsmith.com",
        "instagram": "https://instagram.com/johnsmith"
    },
    "pricing_plan_id": "uuid",
    "agreed_to_terms": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Book advert created successfully",
    "data": {
        "book": { /* book object */ }
    }
}
```

### 2. Update Book Advert
**Endpoint:** `PUT /api/v1/books-adverts/{id}`

Update an existing book advert (only by owner).

**Request Body:** Same as create book advert, but only include fields to update.

### 3. Delete Book Advert
**Endpoint:** `DELETE /api/v1/books-adverts/{id}`

Delete a book advert (only by owner).

### 4. Get My Books
**Endpoint:** `GET /api/v1/books-adverts/my-books`

Retrieve book adverts created by the authenticated user.

### 5. Save/Unsave Book
**Endpoint:** `POST /api/v1/books-adverts/{id}/save`

Save a book to user's favorites.

**Request Body:**
```json
{
    "save": true
}
```

To unsave, send `"save": false` or use DELETE method if available.

### 6. Track View
**Endpoint:** `POST /api/v1/books-adverts/{id}/views`

Track book view (automatically called when viewing book details).

**Request Body:** (Optional - server will auto-detect most fields)
```json
{
    "user_agent": "Mozilla/5.0...",
    "referrer": "https://example.com"
}
```

### 7. Process Payment
**Endpoint:** `POST /api/v1/books-adverts/{id}/payment`

Process payment for book promotion or purchase.

**Request Body:**
```json
{
    "payment_type": "promotion",
    "pricing_plan_id": "uuid",
    "payment_method": "stripe",
    "amount": 29.99,
    "currency": "USD"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Payment processed successfully",
    "data": {
        "payment_id": "uuid",
        "transaction_id": "txn_1234567890",
        "status": "completed",
        "amount": 29.99,
        "currency": "USD"
    }
}
```

## Data Models

### Book Advert Object Structure

```json
{
    "id": "uuid",
    "title": "string",
    "slug": "string",
    "description": "text",
    "author_name": "string",
    "publisher": "string",
    "isbn": "string",
    "genre": "string",
    "book_type": "string",
    "language": "string",
    "format": "string",
    "pages": "integer",
    "publication_date": "date",
    "price": "decimal",
    "currency": "string",
    "country": "string",
    "views_count": "integer",
    "saves_count": "integer",
    "rating": "decimal",
    "verified_author": "boolean",
    "advert_type": "basic|featured|promoted|sponsored",
    "promoted_until": "datetime",
    "cover_image": "string",
    "additional_images": ["string"],
    "purchase_links": [
        {
            "platform": "string",
            "url": "string",
            "price": "decimal"
        }
    ],
    "sample_files": [
        {
            "type": "string",
            "url": "string",
            "title": "string"
        }
    ],
    "author_bio": "text",
    "author_social_links": {
        "twitter": "string",
        "website": "string",
        "instagram": "string"
    },
    "agreed_to_terms": "boolean",
    "created_at": "datetime",
    "updated_at": "datetime",
    "user": {
        "id": "integer",
        "name": "string",
        "email": "string"
    },
    "pricing_plan": {
        "id": "uuid",
        "name": "string",
        "duration_days": "integer",
        "price": "decimal"
    }
}
```

### Pricing Plan Object Structure

```json
{
    "id": "uuid",
    "name": "string",
    "description": "text",
    "duration_days": "integer",
    "price": "decimal",
    "currency": "string",
    "features": ["string"],
    "is_active": "boolean"
}
```

## Error Handling

All endpoints return consistent error responses:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### Common HTTP Status Codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## File Upload

When creating or updating book adverts with images or files:

1. Convert images and files to base64 format
2. Include in the appropriate fields in the request body
3. Supported formats:
   - Images: JPEG, PNG, WebP (max 5MB per image)
   - Sample files: PDF, EPUB (max 10MB per file)

**Image Upload Example:**
```javascript
const coverInput = document.getElementById('cover-image');
const file = coverInput.files[0];

const reader = new FileReader();
reader.onload = function(e) {
    const base64String = e.target.result;
    
    // Send to API
    fetch('/api/v1/books-adverts', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            title: 'My Book',
            // ... other fields
            cover_image: base64String
        })
    });
};
reader.readAsDataURL(file);
```

## Rate Limiting

- Search endpoints: 100 requests per minute
- View tracking: 1000 requests per minute
- Other endpoints: 60 requests per minute
- File upload endpoints: 20 requests per minute

## Caching

- Book listings are cached for 5 minutes
- Individual books are cached for 10 minutes
- Pricing plans are cached for 1 hour
- Statistics are cached for 30 minutes

## WebSocket Events (Optional)

If your application supports real-time updates:

- `book.created` - New book advert created
- `book.updated` - Book advert updated
- `book.deleted` - Book advert deleted
- `book.promoted` - Book promoted to featured

## Integration Examples

### Basic Search Implementation

```javascript
class BooksAPI {
    constructor(baseURL, authToken) {
        this.baseURL = baseURL;
        this.authToken = authToken;
    }

    async searchBooks(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const response = await fetch(`${this.baseURL}?${queryString}`);
        return response.json();
    }

    async getBook(slug) {
        const response = await fetch(`${this.baseURL}/${slug}`);
        return response.json();
    }

    async createBook(bookData) {
        const response = await fetch(`${this.baseURL}`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.authToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(bookData)
        });
        return response.json();
    }

    async trackView(bookId) {
        const response = await fetch(`${this.baseURL}/${bookId}/views`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.authToken}`,
                'Content-Type': 'application/json'
            }
        });
        return response.json();
    }

    async saveBook(bookId, save = true) {
        const response = await fetch(`${this.baseURL}/${bookId}/save`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.authToken}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ save })
        });
        return response.json();
    }

    async getFeaturedBooks() {
        const response = await fetch(`${this.baseURL}/featured`);
        return response.json();
    }

    async getBooksByGenre(genre) {
        const response = await fetch(`${this.baseURL}/genre/${genre}`);
        return response.json();
    }

    async getPricingPlans() {
        const response = await fetch(`${this.baseURL}/pricing-plans`);
        return response.json();
    }
}

// Usage
const api = new BooksAPI('/api/v1/books-adverts', 'your-auth-token');

// Search for fiction books
const results = await api.searchBooks({
    search: 'adventure',
    genre: 'fiction',
    min_price: 10,
    max_price: 50,
    verified_only: true,
    sort_by: 'rating',
    sort_order: 'desc'
});

// Get featured books
const featuredBooks = await api.getFeaturedBooks();

// Track book view
await api.trackView('book-uuid');

// Save book to favorites
await api.saveBook('book-uuid', true);
```

### React Component Example

```jsx
import React, { useState, useEffect } from 'react';

const BooksListing = () => {
    const [books, setBooks] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filters, setFilters] = useState({});
    const [genres, setGenres] = useState([]);

    useEffect(() => {
        fetchBooks();
        fetchGenres();
    }, [filters]);

    const fetchBooks = async () => {
        setLoading(true);
        try {
            const response = await fetch(`/api/v1/books-adverts?${new URLSearchParams(filters)}`);
            const data = await response.json();
            if (data.success) {
                setBooks(data.data.items);
            }
        } catch (error) {
            console.error('Error fetching books:', error);
        } finally {
            setLoading(false);
        }
    };

    const fetchGenres = async () => {
        try {
            const response = await fetch('/api/v1/books-adverts/statistics');
            const data = await response.json();
            if (data.success) {
                // Extract genres from statistics or create a separate endpoint
                setGenres(['Fiction', 'Non-Fiction', 'Romance', 'Mystery', 'Sci-Fi', 'Biography']);
            }
        } catch (error) {
            console.error('Error fetching genres:', error);
        }
    };

    const handleFilterChange = (key, value) => {
        setFilters(prev => ({ ...prev, [key]: value }));
    };

    const handleBookView = async (bookId) => {
        try {
            await fetch(`/api/v1/books-adverts/${bookId}/views`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                }
            });
        } catch (error) {
            console.error('Error tracking view:', error);
        }
    };

    const handleSaveBook = async (bookId) => {
        try {
            const response = await fetch(`/api/v1/books-adverts/${bookId}/save`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ save: true })
            });
            
            if (response.ok) {
                // Update UI to reflect saved state
                setBooks(prev => prev.map(book => 
                    book.id === bookId 
                        ? { ...book, is_saved: true }
                        : book
                ));
            }
        } catch (error) {
            console.error('Error saving book:', error);
        }
    };

    return (
        <div className="books-container">
            {/* Search Filters */}
            <div className="filters-section">
                <input 
                    type="text" 
                    placeholder="Search books, authors..."
                    onChange={(e) => handleFilterChange('search', e.target.value)}
                    className="search-input"
                />
                
                <select onChange={(e) => handleFilterChange('genre', e.target.value)}>
                    <option value="">All Genres</option>
                    {genres.map(genre => (
                        <option key={genre} value={genre}>{genre}</option>
                    ))}
                </select>

                <select onChange={(e) => handleFilterChange('format', e.target.value)}>
                    <option value="">All Formats</option>
                    <option value="paperback">Paperback</option>
                    <option value="hardcover">Hardcover</option>
                    <option value="ebook">Ebook</option>
                    <option value="audiobook">Audiobook</option>
                </select>

                <div className="price-filters">
                    <input 
                        type="number" 
                        placeholder="Min Price"
                        onChange={(e) => handleFilterChange('min_price', e.target.value)}
                    />
                    <input 
                        type="number" 
                        placeholder="Max Price"
                        onChange={(e) => handleFilterChange('max_price', e.target.value)}
                    />
                </div>

                <label className="checkbox-label">
                    <input 
                        type="checkbox" 
                        onChange={(e) => handleFilterChange('verified_only', e.target.checked)}
                    />
                    Verified Authors Only
                </label>

                <select onChange={(e) => handleFilterChange('sort_by', e.target.value)}>
                    <option value="created_at">Latest</option>
                    <option value="title">Title</option>
                    <option value="price">Price</option>
                    <option value="rating">Rating</option>
                    <option value="views_count">Popularity</option>
                </select>

                <select onChange={(e) => handleFilterChange('sort_order', e.target.value)}>
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>

            {/* Books Grid */}
            {loading ? (
                <div className="loading">Loading books...</div>
            ) : (
                <div className="books-grid">
                    {books.map(book => (
                        <div key={book.id} className="book-card">
                            <div className="book-cover">
                                <img 
                                    src={book.cover_image} 
                                    alt={book.title}
                                    onClick={() => handleBookView(book.id)}
                                />
                                {book.verified_author && (
                                    <span className="verified-badge">✓ Verified Author</span>
                                )}
                                {book.advert_type !== 'basic' && (
                                    <span className="promoted-badge">{book.advert_type}</span>
                                )}
                            </div>
                            
                            <div className="book-info">
                                <h3 className="book-title">{book.title}</h3>
                                <p className="book-author">by {book.author_name}</p>
                                <p className="book-publisher">{book.publisher}</p>
                                
                                <div className="book-meta">
                                    <span className="genre">{book.genre}</span>
                                    <span className="format">{book.format}</span>
                                    <span className="pages">{book.pages} pages</span>
                                </div>
                                
                                <div className="book-rating">
                                    <span className="stars">{'★'.repeat(Math.floor(book.rating))}</span>
                                    <span className="rating-value">{book.rating}</span>
                                    <span className="views">({book.views_count} views)</span>
                                </div>
                                
                                <div className="book-price">
                                    ${book.price} {book.currency}
                                </div>
                                
                                <div className="book-actions">
                                    <button 
                                        className="btn-view-details"
                                        onClick={() => handleBookView(book.id)}
                                    >
                                        View Details
                                    </button>
                                    <button 
                                        className={`btn-save ${book.is_saved ? 'saved' : ''}`}
                                        onClick={() => handleSaveBook(book.id)}
                                    >
                                        {book.is_saved ? '♥ Saved' : '♡ Save'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default BooksListing;
```

### Book Creation Form Component

```jsx
import React, { useState } from 'react';

const CreateBookForm = () => {
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        author_name: '',
        publisher: '',
        isbn: '',
        genre: '',
        book_type: '',
        language: '',
        format: '',
        pages: '',
        publication_date: '',
        price: '',
        currency: 'USD',
        country: '',
        author_bio: '',
        agreed_to_terms: false
    });

    const [coverImage, setCoverImage] = useState('');
    const [additionalImages, setAdditionalImages] = useState([]);
    const [sampleFiles, setSampleFiles] = useState([]);
    const [purchaseLinks, setPurchaseLinks] = useState([]);
    const [authorSocialLinks, setAuthorSocialLinks] = useState({});
    const [loading, setLoading] = useState(false);

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleImageUpload = (e, type) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                if (type === 'cover') {
                    setCoverImage(event.target.result);
                } else {
                    setAdditionalImages(prev => [...prev, event.target.result]);
                }
            };
            reader.readAsDataURL(file);
        }
    };

    const handleFileUpload = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                setSampleFiles(prev => [...prev, {
                    type: file.type.includes('pdf') ? 'pdf' : 'epub',
                    file: event.target.result,
                    title: file.name.replace(/\.[^/.]+$/, "")
                }]);
            };
            reader.readAsDataURL(file);
        }
    };

    const addPurchaseLink = () => {
        setPurchaseLinks(prev => [...prev, { platform: '', url: '', price: '' }]);
    };

    const updatePurchaseLink = (index, field, value) => {
        setPurchaseLinks(prev => prev.map((link, i) => 
            i === index ? { ...link, [field]: value } : link
        ));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const response = await fetch('/api/v1/books-adverts', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('authToken')}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...formData,
                    cover_image: coverImage,
                    additional_images: additionalImages,
                    sample_files: sampleFiles,
                    purchase_links: purchaseLinks,
                    author_social_links: authorSocialLinks
                })
            });

            const result = await response.json();
            
            if (result.success) {
                alert('Book created successfully!');
                // Reset form or redirect
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error creating book:', error);
            alert('Error creating book');
        } finally {
            setLoading(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="create-book-form">
            <h2>Create New Book Advert</h2>
            
            <div className="form-section">
                <h3>Basic Information</h3>
                <input
                    type="text"
                    name="title"
                    placeholder="Book Title"
                    value={formData.title}
                    onChange={handleInputChange}
                    required
                />
                
                <textarea
                    name="description"
                    placeholder="Book Description"
                    value={formData.description}
                    onChange={handleInputChange}
                    required
                />
                
                <input
                    type="text"
                    name="author_name"
                    placeholder="Author Name"
                    value={formData.author_name}
                    onChange={handleInputChange}
                    required
                />
                
                <input
                    type="text"
                    name="publisher"
                    placeholder="Publisher"
                    value={formData.publisher}
                    onChange={handleInputChange}
                />
                
                <input
                    type="text"
                    name="isbn"
                    placeholder="ISBN"
                    value={formData.isbn}
                    onChange={handleInputChange}
                />
            </div>

            <div className="form-section">
                <h3>Book Details</h3>
                <select name="genre" value={formData.genre} onChange={handleInputChange} required>
                    <option value="">Select Genre</option>
                    <option value="Fiction">Fiction</option>
                    <option value="Non-Fiction">Non-Fiction</option>
                    <option value="Romance">Romance</option>
                    <option value="Mystery">Mystery</option>
                    <option value="Sci-Fi">Science Fiction</option>
                </select>
                
                <select name="format" value={formData.format} onChange={handleInputChange} required>
                    <option value="">Select Format</option>
                    <option value="paperback">Paperback</option>
                    <option value="hardcover">Hardcover</option>
                    <option value="ebook">Ebook</option>
                    <option value="audiobook">Audiobook</option>
                </select>
                
                <input
                    type="number"
                    name="pages"
                    placeholder="Number of Pages"
                    value={formData.pages}
                    onChange={handleInputChange}
                />
                
                <input
                    type="date"
                    name="publication_date"
                    value={formData.publication_date}
                    onChange={handleInputChange}
                />
                
                <div className="price-inputs">
                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        placeholder="Price"
                        value={formData.price}
                        onChange={handleInputChange}
                        required
                    />
                    <select name="currency" value={formData.currency} onChange={handleInputChange}>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                    </select>
                </div>
            </div>

            <div className="form-section">
                <h3>Cover Image</h3>
                <input
                    type="file"
                    accept="image/*"
                    onChange={(e) => handleImageUpload(e, 'cover')}
                    required
                />
                {coverImage && (
                    <img src={coverImage} alt="Cover preview" className="image-preview" />
                )}
            </div>

            <div className="form-section">
                <h3>Additional Images</h3>
                <input
                    type="file"
                    accept="image/*"
                    multiple
                    onChange={(e) => handleImageUpload(e, 'additional')}
                />
                <div className="additional-images-preview">
                    {additionalImages.map((img, index) => (
                        <img key={index} src={img} alt={`Additional ${index + 1}`} className="image-preview" />
                    ))}
                </div>
            </div>

            <div className="form-section">
                <h3>Sample Files</h3>
                <input
                    type="file"
                    accept=".pdf,.epub"
                    multiple
                    onChange={handleFileUpload}
                />
                {sampleFiles.map((file, index) => (
                    <div key={index} className="sample-file">
                        <span>{file.title}</span>
                        <span className="file-type">({file.type})</span>
                    </div>
                ))}
            </div>

            <div className="form-section">
                <h3>Purchase Links</h3>
                {purchaseLinks.map((link, index) => (
                    <div key={index} className="purchase-link">
                        <input
                            type="text"
                            placeholder="Platform (e.g., Amazon)"
                            value={link.platform}
                            onChange={(e) => updatePurchaseLink(index, 'platform', e.target.value)}
                        />
                        <input
                            type="url"
                            placeholder="Purchase URL"
                            value={link.url}
                            onChange={(e) => updatePurchaseLink(index, 'url', e.target.value)}
                        />
                        <input
                            type="number"
                            step="0.01"
                            placeholder="Price"
                            value={link.price}
                            onChange={(e) => updatePurchaseLink(index, 'price', e.target.value)}
                        />
                    </div>
                ))}
                <button type="button" onClick={addPurchaseLink}>Add Purchase Link</button>
            </div>

            <div className="form-section">
                <h3>Author Information</h3>
                <textarea
                    name="author_bio"
                    placeholder="Author Biography"
                    value={formData.author_bio}
                    onChange={handleInputChange}
                />
                
                <input
                    type="url"
                    name="website"
                    placeholder="Author Website"
                    value={authorSocialLinks.website || ''}
                    onChange={(e) => setAuthorSocialLinks(prev => ({ ...prev, website: e.target.value }))}
                />
                
                <input
                    type="url"
                    name="twitter"
                    placeholder="Twitter Profile"
                    value={authorSocialLinks.twitter || ''}
                    onChange={(e) => setAuthorSocialLinks(prev => ({ ...prev, twitter: e.target.value }))}
                />
            </div>

            <div className="form-section">
                <label>
                    <input
                        type="checkbox"
                        name="agreed_to_terms"
                        checked={formData.agreed_to_terms}
                        onChange={handleInputChange}
                        required
                    />
                    I agree to the terms and conditions
                </label>
            </div>

            <button type="submit" disabled={loading} className="submit-btn">
                {loading ? 'Creating...' : 'Create Book Advert'}
            </button>
        </form>
    );
};

export default CreateBookForm;
```

## Testing

Use the following curl commands for testing:

```bash
# Get all books
curl -X GET "http://localhost:8000/api/v1/books-adverts"

# Search with filters
curl -X GET "http://localhost:8000/api/v1/books-adverts?search=fiction&genre=romance&min_price=10&verified_only=true"

# Get featured books
curl -X GET "http://localhost:8000/api/v1/books-adverts/featured"

# Get books by genre
curl -X GET "http://localhost:8000/api/v1/books-adverts/genre/fiction"

# Get pricing plans
curl -X GET "http://localhost:8000/api/v1/books-adverts/pricing-plans"

# Create book advert (with auth)
curl -X POST "http://localhost:8000/api/v1/books-adverts" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test Book","description":"Test description","author_name":"Test Author","genre":"Fiction","price":19.99}'

# Track view
curl -X POST "http://localhost:8000/api/v1/books-adverts/UUID/views" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Save book
curl -X POST "http://localhost:8000/api/v1/books-adverts/UUID/save" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"save":true}'
```

## Support

For API support and questions:
- Email: support@your-domain.com
- Documentation: https://docs.your-domain.com
- Status Page: https://status.your-domain.com

---

*Last updated: March 2026*
*Version: 1.0*
