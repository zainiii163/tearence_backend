# Books Adverts System - Backend Flow Documentation

## 📋 Overview

This document explains the complete backend flow of the Books Adverts system, including data models, API endpoints, request/response structures, and business logic.

## 🗄️ Database Architecture

### Core Tables

#### 1. `ea_books` (Main Books Table)
```sql
- id (primary key)
- title, subtitle, slug, description, short_description
- book_type, genre, author_name, author_id
- country, language, price, currency, format
- isbn, publisher, publication_date, pages, age_range
- series_name, edition, cover_image, additional_images (JSON)
- trailer_video_url, sample_files (JSON)
- author_bio, author_photo, author_social_links (JSON)
- purchase_links (JSON)
- latitude, longitude, location_address
- advert_type, is_promoted, is_featured, is_sponsored, is_top_category
- upsell_price, payment_status, payment_transaction_id
- paid_at, expires_at, verified_author, verified_at
- user_id, pricing_plan_id (foreign keys)
- views_count, saves_count, status
- created_at, updated_at
```

#### 2. `ea_ad_pricing_plans` (Premium Tiers)
```sql
- id, name, ad_type, tier_type
- price, duration_days, description
- features (JSON array)
- is_active, is_featured, sort_order
```

#### 3. Related Tables
- `ea_users` - User management
- `ea_authors` - Author profiles
- `ea_book_upsells` - Upsell transactions
- `ea_book_saves` - User bookmarks
- `ea_book_purchases` - Purchase tracking

## 🔄 Request Flow Architecture

### 1. Book Creation Flow

```
User Request → Validation → File Upload → Database Insert → Payment Processing → Response
```

**Step-by-Step Process:**

1. **Frontend Submission** (`POST /api/books-adverts`)
   - Multi-step form data with files
   - Upsell tier selection

2. **Request Validation** (`StoreBookRequest`)
   - Field validation rules
   - File type/size checks
   - Required field verification

3. **Business Logic** (`BookAdvertController@store`)
   - Generate unique slug
   - Handle upsell pricing
   - Process file uploads
   - Set boolean flags for tiers

4. **Database Operations**
   - Insert book record
   - Handle relationships
   - Track analytics

5. **Payment Integration**
   - Create payment record if upsell selected
   - Generate transaction ID
   - Set expiry dates

### 2. Book Listing Flow

```
API Request → Query Building → Filtering → Sorting → Pagination → Response
```

**Advanced Filtering System:**

- **Search**: Title, author, description full-text search
- **Genre Filtering**: Exact match on genre field
- **Country Filtering**: By country code
- **Format Filtering**: Paperback, hardcover, ebook, audiobook
- **Price Range**: Min/max price boundaries
- **Book Type**: Fiction, non-fiction, children, etc.
- **Premium Filters**: Verified authors, promoted books only
- **Sorting Options**: Date, title, price, views, saves

### 3. Premium Upsell Flow

```
Tier Selection → Price Calculation → Payment Processing → Status Update → Visibility Enhancement
```

**Tier Implementation:**

1. **Standard** (Free)
   - Basic listing
   - Standard visibility

2. **Promoted** ($29.99)
   - `is_promoted = true`
   - 2× visibility multiplier
   - "Promoted" badge

3. **Featured** ($79.99)
   - `is_featured = true`
   - Top genre placement
   - Email inclusion

4. **Sponsored** ($149.99)
   - `is_sponsored = true`
   - Homepage placement
   - Social media promotion

5. **Top of Category** ($299.99)
   - `is_top_category = true`
   - Pinned top placement
   - Exclusive features

## 📊 API Endpoint Specifications

### Authentication Required
All endpoints except public listing/views require `auth:api` middleware.

### Core Endpoints

#### 1. Get Books List
```
GET /api/books-adverts
```

**Query Parameters:**
- `search` (string) - Search term
- `genre` (string) - Filter by genre
- `country` (string) - Filter by country code
- `format` (string) - Filter by format
- `book_type` (string) - Filter by book type
- `min_price` (decimal) - Minimum price
- `max_price` (decimal) - Maximum price
- `verified_only` (boolean) - Verified authors only
- `promoted_only` (boolean) - Promoted books only
- `sort_by` (string) - Sort field
- `sort_order` (string) - Sort direction
- `per_page` (integer) - Results per page (max 50)

**Response Structure:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Book Title",
        "slug": "book-title-123",
        "author_name": "Author Name",
        "genre": "Fiction",
        "price": "29.99",
        "currency": "USD",
        "format": "paperback",
        "cover_image_url": "http://example.com/storage/books/covers/cover.jpg",
        "advert_type": "featured",
        "verified_author": true,
        "views_count": 1250,
        "saves_count": 89,
        "status": "active",
        "created_at": "2026-03-10T12:00:00Z"
      }
    ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 12,
    "total": 60
  },
  "filters": {
    "genres": ["Fiction", "Non-Fiction", "Children"],
    "countries": ["US", "GB", "CA"],
    "book_types": ["fiction", "non-fiction"],
    "formats": ["paperback", "hardcover", "ebook", "audiobook"]
  }
}
```

#### 2. Create Book
```
POST /api/books-adverts
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:**
```
book_type=fiction
title=My Great Book
subtitle=An amazing story
author_name=John Doe
genre=Fiction
country=US
language=en
price=19.99
currency=USD
format=paperback
description=A compelling story about...
isbn=978-1234567890
publisher=My Publisher
publication_date=2024-01-15
pages=350
cover_image=[file]
additional_images=[files]
author_bio=Author bio here
purchase_links[0][platform]=Amazon
purchase_links[0][url]=https://amazon.com/book
upsell_tier=2
```

**Response Structure:**
```json
{
  "success": true,
  "message": "Book created successfully!",
  "data": {
    "id": 123,
    "title": "My Great Book",
    "slug": "my-great-book-1712345678",
    "advert_type": "featured",
    "payment_status": "pending"
  },
  "payment_required": true,
  "payment_amount": 79.99
}
```

#### 3. Get Book Details
```
GET /api/books-adverts/{slug}
```

**Response Structure:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Book Title",
    "slug": "book-title-123",
    "subtitle": "Amazing subtitle",
    "description": "Full book description...",
    "short_description": "Brief summary",
    "book_type": "fiction",
    "genre": "Fiction",
    "author_name": "Author Name",
    "author_bio": "Author biography...",
    "author_photo_url": "http://example.com/storage/authors/photo.jpg",
    "author_social_links": ["https://twitter.com/author"],
    "price": "29.99",
    "currency": "USD",
    "format": "paperback",
    "isbn": "978-1234567890",
    "publisher": "Publisher Name",
    "publication_date": "2024-01-15",
    "pages": 350,
    "cover_image_url": "http://example.com/storage/books/covers/cover.jpg",
    "additional_images": ["url1", "url2"],
    "trailer_video_url": "https://youtube.com/watch?v=...",
    "sample_files": [
      {
        "path": "storage/books/samples/chapter1.pdf",
        "name": "Chapter 1",
        "type": "pdf"
      }
    ],
    "purchase_links": [
      {
        "platform": "Amazon",
        "url": "https://amazon.com/book"
      }
    ],
    "country": "US",
    "language": "en",
    "advert_type": "featured",
    "verified_author": true,
    "views_count": 1250,
    "saves_count": 89,
    "status": "active",
    "expires_at": "2026-04-10T12:00:00Z",
    "created_at": "2026-03-10T12:00:00Z",
    "user": {
      "id": 123,
      "name": "User Name"
    },
    "author": {
      "id": 456,
      "name": "Author Name"
    }
  }
}
```

#### 4. Update Book
```
PUT /api/books-adverts/{book}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body:** Same as create, but all fields optional

#### 5. Delete Book
```
DELETE /api/books-adverts/{book}
Authorization: Bearer {token}
```

#### 6. Save/Bookmark Book
```
POST /api/books-adverts/{book}/save
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Book saved successfully!",
  "saved": true,
  "saves_count": 90
}
```

#### 7. Get Featured Books
```
GET /api/books-adverts/featured
```

#### 8. Get Books by Genre
```
GET /api/books-adverts/genre/{genre}
```

#### 9. Get Pricing Plans
```
GET /api/books-adverts/pricing-plans
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Promoted Book",
      "tier_type": "promoted",
      "price": "29.99",
      "duration_days": 30,
      "description": "Get your book highlighted...",
      "features": [
        "Highlighted listing",
        "Appears above standard book ads",
        "\"Promoted\" badge",
        "2× more visibility",
        "Basic analytics"
      ],
      "is_active": true,
      "is_featured": false
    }
  ]
}
```

#### 10. Process Payment
```
POST /api/books-adverts/{book}/payment
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "payment_method": "credit_card",
  "transaction_id": "txn_1234567890"
}
```

#### 11. Get User's Books
```
GET /api/books-adverts/my-books
Authorization: Bearer {token}
```

#### 12. Get Statistics (Admin)
```
GET /api/books-adverts/statistics
Authorization: Bearer {token}
```

## 🔧 Business Logic Implementation

### 1. Slug Generation
```php
$data['slug'] = Str::slug($data['title']) . '-' . time();
```

### 2. Upsell Tier Logic
```php
// Set boolean flags based on advert_type
$data['is_promoted'] = in_array($advertType, ['promoted', 'featured', 'sponsored', 'top_category']);
$data['is_featured'] = in_array($advertType, ['featured', 'sponsored', 'top_category']);
$data['is_sponsored'] = in_array($advertType, ['sponsored', 'top_category']);
$data['is_top_category'] = $advertType === 'top_category';
```

### 3. File Upload Handling
```php
// Cover image
if ($request->hasFile('cover_image')) {
    $data['cover_image'] = $request->file('cover_image')->store('books/covers', 'public');
}

// Multiple images
if ($request->hasFile('additional_images')) {
    $images = [];
    foreach ($request->file('additional_images') as $image) {
        $images[] = $image->store('books/additional', 'public');
    }
    $data['additional_images'] = $images;
}
```

### 4. View Count Tracking
```php
public function incrementViews()
{
    $this->increment('views_count');
}
```

### 5. Search Implementation
```php
$query->where(function ($q) use ($search) {
    $q->where('title', 'like', "%{$search}%")
      ->orWhere('author_name', 'like', "%{$search}%")
      ->orWhere('description', 'like', "%{$search}%");
});
```

## 🔒 Security Measures

### 1. Authentication
- JWT token required for protected endpoints
- User authorization checks for book ownership

### 2. Validation
- Comprehensive input validation
- File type and size restrictions
- SQL injection prevention

### 3. Rate Limiting
- API endpoint throttling
- Upload frequency limits

### 4. Data Protection
- Secure file storage
- Payment transaction encryption
- Personal data protection

## 📈 Performance Optimization

### 1. Database Indexing
```sql
INDEX idx_status_advert_type (status, advert_type)
INDEX idx_genre_country (genre, country)
INDEX idx_created_views (created_at, views_count)
INDEX idx_payment_status (payment_status)
INDEX idx_upsell_flags (is_promoted, is_featured, is_sponsored, is_top_category)
```

### 2. Caching Strategy
- Book listing cache
- Popular books cache
- Pricing plans cache

### 3. Query Optimization
- Eager loading relationships
- Efficient pagination
- Optimized search queries

## 🔄 Error Handling

### Standard Response Format
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### Common Error Codes
- `401` - Unauthorized
- `403` - Forbidden (not book owner)
- `404` - Book not found
- `422` - Validation error
- `500` - Server error

## 🎯 Integration Points

### 1. Payment Gateway
- Stripe integration ready
- Webhook handling
- Refund processing

### 2. Email System
- Book approval notifications
- Payment confirmations
- Expiry reminders

### 3. Analytics
- Google Events tracking
- Custom dashboard metrics
- Revenue reporting

### 4. Social Media
- Share functionality
- Auto-posting for sponsored books
- Author profile integration

## 🚀 Future Enhancements

### 1. AI Features
- Automated book categorization
- Price recommendations
- Cover image analysis

### 2. Advanced Analytics
- Reader demographics
- Conversion tracking
- A/B testing

### 3. Mobile API
- Dedicated mobile endpoints
- Push notifications
- Offline support

This documentation provides a complete understanding of the Books Adverts system backend flow and can be used for development, testing, and maintenance purposes.
