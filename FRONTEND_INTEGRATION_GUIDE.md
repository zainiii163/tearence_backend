# Frontend Integration Guide - Books API

## Overview
This guide provides complete frontend integration instructions for the WWA Books API, including React/Vue.js components, state management, and UI examples.

## API Base Configuration

### Environment Setup
```env
# .env file
REACT_APP_API_BASE_URL=http://your-api-domain.com
REACT_APP_API_VERSION=v1
```

### API Client Configuration
```javascript
// src/services/api.js
class ApiClient {
  constructor() {
    this.baseURL = process.env.REACT_APP_API_BASE_URL;
    this.version = process.env.REACT_APP_API_VERSION;
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseURL}/api/${this.version}${endpoint}`;
    const config = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers
      },
      ...options
    };

    // Add auth token if available
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    const response = await fetch(url, config);
    
    if (!response.ok) {
      throw new Error(`API Error: ${response.status} ${response.statusText}`);
    }

    return response.json();
  }

  // Books API methods
  async getBooks(params = {}) {
    const queryString = new URLSearchParams(params).toString();
    return this.request(`/books${queryString ? `?${queryString}` : ''}`);
  }

  async getBook(id) {
    return this.request(`/books/${id}`);
  }

  async createBook(data) {
    return this.request('/books', {
      method: 'POST',
      body: JSON.stringify(data)
    });
  }

  async purchaseBook(id, paymentMethod) {
    return this.request(`/books/${id}/purchase`, {
      method: 'POST',
      body: JSON.stringify({ payment_method: paymentMethod })
    });
  }

  async downloadBook(token) {
    const url = `${this.baseURL}/api/${this.version}/books/download/${token}`;
    const response = await fetch(url);
    
    if (!response.ok) {
      throw new Error('Download failed');
    }
    
    return response.blob();
  }

  async getMyPurchases() {
    return this.request('/books/my-purchases');
  }

  async getBookStatistics() {
    return this.request('/books/statistics');
  }
}

export default new ApiClient();
```

## React Components

### BooksList Component
```jsx
// src/components/Books/BooksList.jsx
import React, { useState, useEffect } from 'react';
import api from '../../services/api';

const BooksList = () => {
  const [books, setBooks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({
    genre: '',
    book_type: '',
    author: '',
    min_price: '',
    max_price: '',
    search: '',
    sort: 'newest',
    per_page: 20
  });
  const [pagination, setPagination] = useState({});

  useEffect(() => {
    fetchBooks();
  }, [filters]);

  const fetchBooks = async () => {
    setLoading(true);
    try {
      const response = await api.getBooks(filters);
      setBooks(response.data);
      setPagination(response.meta);
    } catch (error) {
      console.error('Error fetching books:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleFilterChange = (key, value) => {
    setFilters(prev => ({ ...prev, [key]: value }));
  };

  const handleSearch = (e) => {
    e.preventDefault();
    fetchBooks();
  };

  if (loading) return <div>Loading books...</div>;

  return (
    <div className="books-list">
      <h1>Books Marketplace</h1>
      
      {/* Filters Section */}
      <div className="filters-section">
        <form onSubmit={handleSearch} className="filters-form">
          <div className="filter-row">
            <input
              type="text"
              placeholder="Search books..."
              value={filters.search}
              onChange={(e) => handleFilterChange('search', e.target.value)}
              className="search-input"
            />
            
            <select
              value={filters.genre}
              onChange={(e) => handleFilterChange('genre', e.target.value)}
              className="filter-select"
            >
              <option value="">All Genres</option>
              <option value="action">Action</option>
              <option value="education">Education</option>
              <option value="drama">Drama</option>
              <option value="thriller">Thriller</option>
              <option value="fiction">Fiction</option>
              <option value="non_fiction">Non-Fiction</option>
              <option value="textbook">Textbook</option>
              <option value="romance">Romance</option>
              <option value="mystery">Mystery</option>
              <option value="scifi">Sci-Fi</option>
              <option value="fantasy">Fantasy</option>
              <option value="biography">Biography</option>
              <option value="self_help">Self-Help</option>
              <option value="business">Business</option>
              <option value="children">Children</option>
            </select>

            <select
              value={filters.book_type}
              onChange={(e) => handleFilterChange('book_type', e.target.value)}
              className="filter-select"
            >
              <option value="">All Types</option>
              <option value="physical">Physical Books</option>
              <option value="pdf">PDF Downloads</option>
              <option value="audiobook">Audiobooks</option>
            </select>
          </div>

          <div className="filter-row">
            <input
              type="text"
              placeholder="Author name..."
              value={filters.author}
              onChange={(e) => handleFilterChange('author', e.target.value)}
              className="filter-input"
            />
            
            <input
              type="number"
              placeholder="Min price"
              value={filters.min_price}
              onChange={(e) => handleFilterChange('min_price', e.target.value)}
              className="filter-input small"
            />
            
            <input
              type="number"
              placeholder="Max price"
              value={filters.max_price}
              onChange={(e) => handleFilterChange('max_price', e.target.value)}
              className="filter-input small"
            />

            <select
              value={filters.sort}
              onChange={(e) => handleFilterChange('sort', e.target.value)}
              className="filter-select"
            >
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
              <option value="relevance">Most Relevant</option>
              <option value="author_az">Author: A-Z</option>
              <option value="title_az">Title: A-Z</option>
            </select>

            <button type="submit" className="search-button">
              Search
            </button>
          </div>
        </form>
      </div>

      {/* Books Grid */}
      <div className="books-grid">
        {books.map(book => (
          <BookCard key={book.listing_id} book={book} />
        ))}
      </div>

      {/* Pagination */}
      {pagination.total_pages > 1 && (
        <div className="pagination">
          <span>Page {pagination.current_page} of {pagination.last_page}</span>
          <span>Total: {pagination.total} books</span>
        </div>
      )}
    </div>
  );
};

const BookCard = ({ book }) => {
  const handlePurchase = async () => {
    try {
      const result = await api.purchaseBook(book.listing_id, 'credit_card');
      alert('Book purchased successfully! Download link available.');
    } catch (error) {
      alert('Purchase failed: ' + error.message);
    }
  };

  return (
    <div className="book-card">
      <div className="book-cover">
        {book.images && book.images.length > 0 ? (
          <img src={book.images[0].image_path} alt={book.title} />
        ) : (
          <div className="placeholder-cover">No Cover</div>
        )}
      </div>
      
      <div className="book-info">
        <h3 className="book-title">{book.title}</h3>
        <p className="book-author">by {book.author}</p>
        <p className="book-genre">{book.genre}</p>
        <p className="book-description">{book.description}</p>
        
        <div className="book-meta">
          <span className="book-type">{book.book_type}</span>
          <span className="book-format">{book.format}</span>
          {book.condition && <span className="book-condition">{book.condition}</span>}
        </div>
        
        <div className="book-price-section">
          <span className="book-price">${book.price}</span>
          {book.is_downloadable && (
            <span className="downloadable-badge">Downloadable</span>
          )}
        </div>
        
        <div className="book-actions">
          <button className="view-details-btn">
            View Details
          </button>
          <button className="purchase-btn" onClick={handlePurchase}>
            Purchase
          </button>
        </div>
      </div>
    </div>
  );
};

export default BooksList;
```

### BookDetail Component
```jsx
// src/components/Books/BookDetail.jsx
import React, { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import api from '../../services/api';

const BookDetail = () => {
  const { id } = useParams();
  const [book, setBook] = useState(null);
  const [loading, setLoading] = useState(true);
  const [purchasing, setPurchasing] = useState(false);

  useEffect(() => {
    fetchBook();
  }, [id]);

  const fetchBook = async () => {
    setLoading(true);
    try {
      const response = await api.getBook(id);
      setBook(response.data);
    } catch (error) {
      console.error('Error fetching book:', error);
    } finally {
      setLoading(false);
    }
  };

  const handlePurchase = async () => {
    setPurchasing(true);
    try {
      const result = await api.purchaseBook(id, 'credit_card');
      alert('Book purchased successfully!');
      // You could redirect to downloads page or show download link
    } catch (error) {
      alert('Purchase failed: ' + error.message);
    } finally {
      setPurchasing(false);
    }
  };

  if (loading) return <div>Loading book details...</div>;
  if (!book) return <div>Book not found</div>;

  return (
    <div className="book-detail">
      <div className="book-detail-header">
        <div className="book-cover-large">
          {book.images && book.images.length > 0 ? (
            <img src={book.images[0].image_path} alt={book.title} />
          ) : (
            <div className="placeholder-cover-large">No Cover Available</div>
          )}
        </div>
        
        <div className="book-detail-info">
          <h1 className="book-title">{book.title}</h1>
          <p className="book-author">by {book.author}</p>
          
          <div className="book-metadata">
            <span className="genre-tag">{book.genre}</span>
            <span className="type-tag">{book.book_type}</span>
            <span className="format-tag">{book.format}</span>
            {book.condition && <span className="condition-tag">{book.condition}</span>}
            {book.is_downloadable && <span className="downloadable-tag">Downloadable</span>}
          </div>
          
          <div className="book-description">
            <h3>Description</h3>
            <p>{book.description}</p>
          </div>
          
          <div className="book-details-grid">
            {book.isbn && (
              <div className="detail-item">
                <span className="detail-label">ISBN:</span>
                <span className="detail-value">{book.isbn}</span>
              </div>
            )}
            
            {book.file_size && (
              <div className="detail-item">
                <span className="detail-label">File Size:</span>
                <span className="detail-value">{book.formatted_file_size}</span>
              </div>
            )}
            
            {book.download_count > 0 && (
              <div className="detail-item">
                <span className="detail-label">Downloads:</span>
                <span className="detail-value">{book.download_count}</span>
              </div>
            )}
            
            {book.website_url && (
              <div className="detail-item">
                <span className="detail-label">External Link:</span>
                <a href={book.website_url} target="_blank" rel="noopener noreferrer" className="external-link">
                  Visit Website
                </a>
              </div>
            )}
          </div>
          
          <div className="purchase-section">
            <div className="price-display">
              <span className="price">${book.price}</span>
            </div>
            <button 
              className="purchase-button" 
              onClick={handlePurchase}
              disabled={purchasing}
            >
              {purchasing ? 'Processing...' : 'Purchase Book'}
            </button>
          </div>
        </div>
      </div>
      
      <div className="book-additional-info">
        {book.total_revenue > 0 && (
          <div className="stats-section">
            <h3>Book Statistics</h3>
            <div className="stats-grid">
              <div className="stat-item">
                <span className="stat-label">Total Revenue:</span>
                <span className="stat-value">${book.total_revenue}</span>
              </div>
              <div className="stat-item">
                <span className="stat-label">Total Downloads:</span>
                <span className="stat-value">{book.total_downloads}</span>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default BookDetail;
```

### CreateBookForm Component
```jsx
// src/components/Books/CreateBookForm.jsx
import React, { useState } from 'react';
import api from '../../services/api';

const CreateBookForm = () => {
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    price: '',
    book_type: 'physical',
    genre: '',
    author: '',
    isbn: '',
    format: 'physical',
    condition: 'new',
    website_url: '',
    is_downloadable: false,
    location_id: ''
  });
  const [file, setFile] = useState(null);
  const [images, setImages] = useState([]);
  const [submitting, setSubmitting] = useState(false);

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  const handleFileChange = (e) => {
    setFile(e.target.files[0]);
  };

  const handleImagesChange = (e) => {
    setImages(Array.from(e.target.files));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);

    try {
      const formDataToSend = new FormData();
      
      // Add all form fields
      Object.keys(formData).forEach(key => {
        if (key !== 'is_downloadable' || formData[key]) {
          formDataToSend.append(key, formData[key]);
        }
      });
      
      // Add file if downloadable
      if (file && formData.is_downloadable) {
        formDataToSend.append('file', file);
      }
      
      // Add images
      images.forEach((image, index) => {
        formDataToSend.append(`attachments[${index}]`, image);
      });

      const response = await api.createBook(formDataToSend);
      alert('Book created successfully!');
      // Reset form or redirect
    } catch (error) {
      alert('Failed to create book: ' + error.message);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <div className="create-book-form">
      <h2>Create New Book Listing</h2>
      
      <form onSubmit={handleSubmit} className="book-form">
        <div className="form-section">
          <h3>Basic Information</h3>
          
          <div className="form-group">
            <label htmlFor="title">Title *</label>
            <input
              type="text"
              id="title"
              name="title"
              value={formData.title}
              onChange={handleChange}
              required
              className="form-input"
            />
          </div>

          <div className="form-group">
            <label htmlFor="description">Description *</label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              required
              rows="4"
              className="form-textarea"
            />
          </div>

          <div className="form-group">
            <label htmlFor="author">Author *</label>
            <input
              type="text"
              id="author"
              name="author"
              value={formData.author}
              onChange={handleChange}
              required
              className="form-input"
            />
          </div>

          <div className="form-group">
            <label htmlFor="isbn">ISBN</label>
            <input
              type="text"
              id="isbn"
              name="isbn"
              value={formData.isbn}
              onChange={handleChange}
              className="form-input"
              placeholder="978-1234567890"
            />
          </div>
        </div>

        <div className="form-section">
          <h3>Book Details</h3>
          
          <div className="form-row">
            <div className="form-group">
              <label htmlFor="book_type">Book Type *</label>
              <select
                id="book_type"
                name="book_type"
                value={formData.book_type}
                onChange={handleChange}
                required
                className="form-select"
              >
                <option value="physical">Physical Book</option>
                <option value="pdf">PDF Download</option>
                <option value="audiobook">Audiobook</option>
              </select>
            </div>

            <div className="form-group">
              <label htmlFor="genre">Genre *</label>
              <select
                id="genre"
                name="genre"
                value={formData.genre}
                onChange={handleChange}
                required
                className="form-select"
              >
                <option value="">Select Genre</option>
                <option value="action">Action</option>
                <option value="education">Education</option>
                <option value="drama">Drama</option>
                <option value="thriller">Thriller</option>
                <option value="fiction">Fiction</option>
                <option value="non_fiction">Non-Fiction</option>
                <option value="textbook">Textbook</option>
                <option value="romance">Romance</option>
                <option value="mystery">Mystery</option>
                <option value="scifi">Sci-Fi</option>
                <option value="fantasy">Fantasy</option>
                <option value="biography">Biography</option>
                <option value="self_help">Self-Help</option>
                <option value="business">Business</option>
                <option value="children">Children</option>
              </select>
            </div>
          </div>

          <div className="form-row">
            <div className="form-group">
              <label htmlFor="format">Format *</label>
              <select
                id="format"
                name="format"
                value={formData.format}
                onChange={handleChange}
                required
                className="form-select"
              >
                <option value="physical">Physical Book</option>
                <option value="e_book">E-book</option>
                <option value="audiobook">Audiobook</option>
              </select>
            </div>

            <div className="form-group">
              <label htmlFor="condition">Condition</label>
              <select
                id="condition"
                name="condition"
                value={formData.condition}
                onChange={handleChange}
                className="form-select"
              >
                <option value="new">New</option>
                <option value="like_new">Like New</option>
                <option value="good">Good</option>
                <option value="fair">Fair</option>
              </select>
            </div>
          </div>

          <div className="form-group">
            <label htmlFor="price">Price</label>
            <input
              type="number"
              id="price"
              name="price"
              value={formData.price}
              onChange={handleChange}
              step="0.01"
              min="0"
              className="form-input"
              placeholder="0.00"
            />
          </div>
        </div>

        <div className="form-section">
          <h3>Digital Options</h3>
          
          <div className="form-group">
            <label>
              <input
                type="checkbox"
                name="is_downloadable"
                checked={formData.is_downloadable}
                onChange={handleChange}
                className="form-checkbox"
              />
              Allow download after purchase
            </label>
          </div>

          {formData.is_downloadable && (
            <div className="form-group">
              <label htmlFor="file">Book File (PDF/Audio) *</label>
              <input
                type="file"
                id="file"
                onChange={handleFileChange}
                accept=".pdf,.mp3,.m4a,.wav"
                className="form-file"
                required
              />
              <small>Accepted formats: PDF, MP3, M4A, WAV (Max 50MB)</small>
            </div>
          )}

          <div className="form-group">
            <label htmlFor="website_url">External Website URL</label>
            <input
              type="url"
              id="website_url"
              name="website_url"
              value={formData.website_url}
              onChange={handleChange}
              className="form-input"
              placeholder="https://example.com/book"
            />
          </div>
        </div>

        <div className="form-section">
          <h3>Cover Images</h3>
          
          <div className="form-group">
            <label htmlFor="images">Book Cover/Images</label>
            <input
              type="file"
              id="images"
              onChange={handleImagesChange}
              multiple
              accept="image/*"
              className="form-file"
            />
            <small>Upload up to 5 images (Max 2MB each)</small>
          </div>
        </div>

        <div className="form-actions">
          <button
            type="submit"
            disabled={submitting}
            className="submit-button"
          >
            {submitting ? 'Creating...' : 'Create Book Listing'}
          </button>
        </div>
      </form>
    </div>
  );
};

export default CreateBookForm;
```

### MyPurchases Component
```jsx
// src/components/Books/MyPurchases.jsx
import React, { useState, useEffect } from 'react';
import api from '../../services/api';

const MyPurchases = () => {
  const [purchases, setPurchases] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchPurchases();
  }, []);

  const fetchPurchases = async () => {
    setLoading(true);
    try {
      const response = await api.getMyPurchases();
      setPurchases(response.data);
    } catch (error) {
      console.error('Error fetching purchases:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDownload = async (purchase) => {
    try {
      const blob = await api.downloadBook(purchase.download_token);
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${purchase.listing.title} - ${purchase.listing.author}.${purchase.listing.file_type || 'pdf'}`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (error) {
      alert('Download failed: ' + error.message);
    }
  };

  const isDownloadAvailable = (purchase) => {
    return purchase.payment_status === 'completed' && 
           purchase.download_token && 
           new Date(purchase.download_token_expires_at) > new Date();
  };

  if (loading) return <div>Loading purchases...</div>;

  return (
    <div className="my-purchases">
      <h1>My Book Purchases</h1>
      
      {purchases.length === 0 ? (
        <div className="no-purchases">
          <p>You haven't purchased any books yet.</p>
          <a href="/books" className="browse-books-btn">Browse Books</a>
        </div>
      ) : (
        <div className="purchases-list">
          {purchases.map(purchase => (
            <div key={purchase.purchase_id} className="purchase-card">
              <div className="purchase-info">
                <h3 className="book-title">{purchase.listing.title}</h3>
                <p className="book-author">by {purchase.listing.author}</p>
                <p className="book-genre">{purchase.listing.genre}</p>
                
                <div className="purchase-details">
                  <div className="purchase-meta">
                    <span className="purchase-date">
                      Purchased: {new Date(purchase.created_at).toLocaleDateString()}
                    </span>
                    <span className="purchase-price">
                      ${purchase.price_paid}
                    </span>
                  </div>
                  
                  <div className="download-info">
                    <span className="download-count">
                      Downloads: {purchase.total_downloads}
                    </span>
                    {purchase.download_token_expires_at && (
                      <span className="expiry-date">
                        Expires: {new Date(purchase.download_token_expires_at).toLocaleDateString()}
                      </span>
                    )}
                  </div>
                </div>
              </div>
              
              <div className="purchase-actions">
                {isDownloadAvailable(purchase) ? (
                  <button
                    className="download-btn"
                    onClick={() => handleDownload(purchase)}
                  >
                    Download Now
                  </button>
                ) : (
                  <div className="download-unavailable">
                    {purchase.payment_status !== 'completed' 
                      ? 'Payment Pending' 
                      : 'Download Expired'}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default MyPurchases;
```

## CSS Styles

### Books List Styles
```css
/* src/components/Books/BooksList.css */
.books-list {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.filters-section {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
}

.filters-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.filter-row {
  display: flex;
  gap: 15px;
  align-items: center;
  flex-wrap: wrap;
}

.search-input {
  flex: 1;
  min-width: 200px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

.filter-select, .filter-input {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.filter-input.small {
  width: 120px;
}

.search-button {
  background: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.search-button:hover {
  background: #0056b3;
}

.books-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.book-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.book-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.book-cover {
  height: 200px;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
}

.book-cover img {
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
}

.placeholder-cover {
  color: #6c757d;
  font-size: 14px;
}

.book-info {
  padding: 15px;
}

.book-title {
  font-size: 18px;
  font-weight: bold;
  margin: 0 0 5px 0;
  line-height: 1.3;
}

.book-author {
  color: #6c757d;
  font-size: 14px;
  margin: 0 0 10px 0;
}

.book-genre {
  background: #e9ecef;
  color: #495057;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 12px;
  display: inline-block;
  margin-bottom: 10px;
}

.book-description {
  color: #6c757d;
  font-size: 14px;
  line-height: 1.4;
  margin: 0 0 10px 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.book-meta {
  display: flex;
  gap: 8px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.book-type, .book-format, .book-condition {
  background: #f8f9fa;
  color: #495057;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 11px;
  text-transform: uppercase;
}

.book-price-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.book-price {
  font-size: 20px;
  font-weight: bold;
  color: #28a745;
}

.downloadable-badge {
  background: #28a745;
  color: white;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 11px;
}

.book-actions {
  display: flex;
  gap: 10px;
}

.view-details-btn, .purchase-btn {
  flex: 1;
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  text-align: center;
}

.view-details-btn {
  background: #6c757d;
  color: white;
}

.purchase-btn {
  background: #007bff;
  color: white;
}

.view-details-btn:hover {
  background: #545b62;
}

.purchase-btn:hover {
  background: #0056b3;
}

.pagination {
  text-align: center;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.pagination span {
  margin: 0 15px;
  color: #6c757d;
}
```

## State Management (Redux/MobX Example)

### Redux Slice for Books
```javascript
// src/store/booksSlice.js
import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import api from '../services/api';

// Async thunks
export const fetchBooks = createAsyncThunk(
  'books/fetchBooks',
  async (params = {}) => {
    const response = await api.getBooks(params);
    return response;
  }
);

export const fetchBook = createAsyncThunk(
  'books/fetchBook',
  async (id) => {
    const response = await api.getBook(id);
    return response.data;
  }
);

export const purchaseBook = createAsyncThunk(
  'books/purchaseBook',
  async ({ id, paymentMethod }) => {
    const response = await api.purchaseBook(id, paymentMethod);
    return response;
  }
);

export const getMyPurchases = createAsyncThunk(
  'books/getMyPurchases',
  async () => {
    const response = await api.getMyPurchases();
    return response.data;
  }
);

const booksSlice = createSlice({
  name: 'books',
  initialState: {
    books: [],
    currentBook: null,
    purchases: [],
    loading: false,
    error: null,
    pagination: {},
    filters: {
      genre: '',
      book_type: '',
      author: '',
      min_price: '',
      max_price: '',
      search: '',
      sort: 'newest',
      per_page: 20
    }
  },
  reducers: {
    setFilters: (state, action) => {
      state.filters = { ...state.filters, ...action.payload };
    },
    clearCurrentBook: (state) => {
      state.currentBook = null;
    },
    clearError: (state) => {
      state.error = null;
    }
  },
  extraReducers: (builder) => {
    builder
      // Fetch books
      .addCase(fetchBooks.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchBooks.fulfilled, (state, action) => {
        state.loading = false;
        state.books = action.payload.data;
        state.pagination = action.payload.meta;
      })
      .addCase(fetchBooks.rejected, (state, action) => {
        state.loading = false;
        state.error = action.error.message;
      })
      // Fetch single book
      .addCase(fetchBook.fulfilled, (state, action) => {
        state.currentBook = action.payload;
      })
      // Purchase book
      .addCase(purchaseBook.fulfilled, (state, action) => {
        // You might want to refresh purchases or show success message
      })
      // Get purchases
      .addCase(getMyPurchases.fulfilled, (state, action) => {
        state.purchases = action.payload;
      });
  }
});

export const { setFilters, clearCurrentBook, clearError } = booksSlice.actions;
export default booksSlice.reducer;
```

## Usage Examples

### Complete App Integration
```jsx
// src/App.js
import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { Provider } from 'react-redux';
import store from './store';
import BooksList from './components/Books/BooksList';
import BookDetail from './components/Books/BookDetail';
import CreateBookForm from './components/Books/CreateBookForm';
import MyPurchases from './components/Books/MyPurchases';
import './App.css';

function App() {
  return (
    <Provider store={store}>
      <Router>
        <div className="App">
          <header className="app-header">
            <h1>WWA Books Marketplace</h1>
            <nav>
              <a href="/books">Browse Books</a>
              <a href="/books/create">Sell Book</a>
              <a href="/books/my-purchases">My Purchases</a>
            </nav>
          </header>
          
          <main className="app-main">
            <Routes>
              <Route path="/books" element={<BooksList />} />
              <Route path="/books/:id" element={<BookDetail />} />
              <Route path="/books/create" element={<CreateBookForm />} />
              <Route path="/books/my-purchases" element={<MyPurchases />} />
            </Routes>
          </main>
        </div>
      </Router>
    </Provider>
  );
}

export default App;
```

This comprehensive frontend integration guide provides everything needed to implement a complete book marketplace with PDF downloads, audiobooks, purchase tracking, and advanced filtering capabilities.
