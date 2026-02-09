# Books API Documentation

## Overview
The Books API provides comprehensive functionality for managing book listings, including physical books, PDF downloads, and audiobooks with advanced filtering and purchase/download capabilities.

## Base URL
```
/api/v1/books
```

## Authentication
Most endpoints require customer authentication using the `auth:customer` middleware.

## Endpoints

### 1. Get Books (Index)
**GET** `/api/v1/books`

Retrieve a paginated list of books with filtering and search capabilities.

**Query Parameters:**
- `genre` (string, optional): Filter by genre (action, education, drama, thriller, etc.)
- `book_type` (string, optional): Filter by book type (physical, pdf, audiobook)
- `format` (string, optional): Filter by format (physical, e_book, audiobook)
- `author` (string, optional): Filter by author name
- `min_price` (numeric, optional): Minimum price filter
- `max_price` (numeric, optional): Maximum price filter
- `search` (string, optional): Search in title, description, author, ISBN
- `sort` (string, optional): Sort options (newest, oldest, price_low, price_high, relevance, author_az, title_az)
- `per_page` (integer, optional): Results per page (1-50, default: 20)

**Response:**
```json
{
  "data": [
    {
      "listing_id": 1,
      "title": "Sample Book Title",
      "description": "Book description...",
      "price": 29.99,
      "book_type": "pdf",
      "genre": "education",
      "author": "John Doe",
      "isbn": "978-1234567890",
      "format": "e_book",
      "condition": "new",
      "is_downloadable": true,
      "file_size": 5242880,
      "formatted_file_size": "5 MB",
      "customer": {...},
      "location": {...},
      "category": {...},
      "created_at": "2026-01-22T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 100,
    "filters": {
      "genres": {
        "action": "Action",
        "education": "Education",
        "drama": "Drama",
        "thriller": "Thriller",
        "fiction": "Fiction",
        "non_fiction": "Non-Fiction",
        "textbook": "Textbook",
        "romance": "Romance",
        "mystery": "Mystery",
        "scifi": "Sci-Fi",
        "fantasy": "Fantasy",
        "biography": "Biography",
        "self_help": "Self-Help",
        "business": "Business",
        "children": "Children"
      },
      "book_types": {
        "physical": "Physical Books",
        "pdf": "PDF Downloads",
        "audiobook": "Audiobooks"
      },
      "formats": {
        "physical": "Physical Book",
        "e_book": "E-book",
        "audiobook": "Audiobook"
      },
      "conditions": {
        "new": "New",
        "like_new": "Like New",
        "good": "Good",
        "fair": "Fair"
      }
    }
  }
}
```

### 2. Get Book Details
**GET** `/api/v1/books/{id}`

Retrieve detailed information about a specific book.

**Response:**
```json
{
  "data": {
    "listing_id": 1,
    "title": "Sample Book Title",
    "description": "Comprehensive book description...",
    "price": 29.99,
    "book_type": "pdf",
    "genre": "education",
    "author": "John Doe",
    "isbn": "978-1234567890",
    "format": "e_book",
    "condition": "new",
    "is_downloadable": true,
    "file_path": "books/sample_book.pdf",
    "file_type": "pdf",
    "file_size": 5242880,
    "formatted_file_size": "5 MB",
    "file_url": "http://example.com/storage/books/sample_book.pdf",
    "website_url": "https://example.com/external-book",
    "download_count": 15,
    "last_downloaded_at": "2026-01-22T09:30:00Z",
    "total_revenue": 449.85,
    "total_downloads": 15,
    "customer": {...},
    "location": {...},
    "category": {...},
    "book_purchases": [...],
    "created_at": "2026-01-22T10:00:00Z"
  }
}
```

### 3. Create Book Listing
**POST** `/api/v1/books`

Create a new book listing. Requires customer authentication.

**Request Body:**
```json
{
  "title": "My New Book",
  "description": "A comprehensive description of the book...",
  "price": 19.99,
  "book_type": "pdf",
  "genre": "education",
  "author": "Jane Smith",
  "isbn": "978-0987654321",
  "format": "e_book",
  "condition": "new",
  "website_url": "https://mywebsite.com/book",
  "is_downloadable": true,
  "file": [PDF or audio file],
  "location_id": 1,
  "attachments": [image files]
}
```

**Response:**
```json
{
  "message": "Book listing created successfully",
  "data": {
    "listing_id": 123,
    "title": "My New Book",
    // ... other book fields
  }
}
```

### 4. Purchase Book
**POST** `/api/v1/books/{id}/purchase`

Purchase a book and receive download access. Requires customer authentication.

**Request Body:**
```json
{
  "payment_method": "credit_card"
}
```

**Response:**
```json
{
  "message": "Book purchased successfully",
  "data": {
    "purchase_id": 456,
    "download_url": "http://example.com/api/v1/books/download/abc123token",
    "download_token": "abc123token",
    "expires_at": "2026-01-29T10:00:00Z"
  }
}
```

### 5. Download Book
**GET** `/api/v1/books/download/{token}`

Download a purchased book using the provided token.

**Headers:**
- No authentication required (token-based access)

**Response:**
- Direct file download with appropriate headers
- File name format: `{Book Title} - {Author}.{extension}`

### 6. Get My Purchases
**GET** `/api/v1/books/my-purchases`

Get the current customer's purchased books. Requires customer authentication.

**Response:**
```json
{
  "data": [
    {
      "purchase_id": 456,
      "listing_id": 1,
      "price_paid": 29.99,
      "payment_status": "completed",
      "download_token": "abc123token",
      "download_token_expires_at": "2026-01-29T10:00:00Z",
      "total_downloads": 3,
      "first_downloaded_at": "2026-01-22T10:05:00Z",
      "last_downloaded_at": "2026-01-22T15:30:00Z",
      "listing": {
        "listing_id": 1,
        "title": "Sample Book Title",
        "author": "John Doe",
        // ... other book fields
      },
      "created_at": "2026-01-22T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 5
  }
}
```

### 7. Get Book Statistics (Admin Only)
**GET** `/api/v1/books/statistics`

Get comprehensive book statistics. Requires admin authentication.

**Response:**
```json
{
  "data": {
    "total_books": 150,
    "active_books": 120,
    "total_purchases": 500,
    "total_revenue": 12500.50,
    "total_downloads": 850,
    "books_by_type": {
      "physical": 80,
      "pdf": 50,
      "audiobook": 20
    },
    "books_by_genre": {
      "education": 40,
      "fiction": 35,
      "business": 25,
      // ... other genres
    },
    "recent_purchases": [
      {
        "purchase_id": 456,
        "price_paid": 29.99,
        "listing": {...},
        "customer": {...},
        "created_at": "2026-01-22T10:00:00Z"
      }
    ]
  }
}
```

## File Upload Requirements

### Book Files
- **PDF Files**: `.pdf` format, max 50MB
- **Audio Files**: `.mp3`, `.m4a`, `.wav` format, max 50MB
- **Storage Location**: `storage/app/books/`

### Cover Images
- **Formats**: `.jpeg`, `.png`, `.jpg`, `.gif`
- **Max Size**: 2MB per image
- **Max Files**: 5 images per listing
- **Storage Location**: `storage/app/listings/`

## Genre Categories

The system supports the following genres:
- Action
- Education
- Drama
- Thriller
- Fiction
- Non-Fiction
- Textbook
- Romance
- Mystery
- Sci-Fi
- Fantasy
- Biography
- Self-Help
- Business
- Children

## Book Types

- **Physical**: Physical books for shipping or local pickup
- **PDF**: Digital PDF downloads
- **Audiobook**: Audio file downloads or streaming

## Format Options

- **Physical**: Physical book format
- **E-book**: Digital e-book format (PDF)
- **Audiobook**: Audio book format

## Condition States

- **New**: Brand new condition
- **Like New**: Excellent condition with minimal wear
- **Good**: Good condition with normal wear
- **Fair**: Fair condition with noticeable wear

## Download System

### Purchase Flow
1. Customer purchases book through `/api/v1/books/{id}/purchase`
2. System creates purchase record with unique download token
3. Token expires after 7 days
4. Maximum 5 download attempts per token
5. Download tracking for analytics and security

### Download Limits
- Token expires: 7 days from purchase
- Maximum attempts: 5 per token
- File access: Only after successful payment
- IP tracking: Records download IP addresses

### Security Features
- Unique token per purchase
- Token expiration
- Download attempt limits
- IP address logging
- Purchase verification

## Error Responses

### Validation Errors (422)
```json
{
  "errors": {
    "title": ["The title field is required."],
    "genre": ["The genre field is required."],
    "author": ["The author field is required."]
  }
}
```

### Not Found (404)
```json
{
  "message": "Book not found"
}
```

### Unauthorized (401)
```json
{
  "message": "Invalid or expired download token"
}
```

### Forbidden (403)
```json
{
  "message": "Download limit exceeded"
}
```

### Payment Required (400)
```json
{
  "message": "You have already purchased this book"
}
```

## Admin Panel Features

The Filament admin panel provides comprehensive book management:

### Book Resource Management
- Create, edit, view, and delete book listings
- Bulk actions for multiple books
- Advanced filtering and search
- Approval workflow for book listings

### Purchase Management
- View all book purchases
- Filter by payment status and date
- Refund purchases with reason tracking
- Regenerate download tokens
- Purchase analytics and reporting

### Statistics Dashboard
- Total books and active books
- Revenue and download analytics
- Genre and type distribution
- Recent purchase activity

### Book Details View
- Complete book information
- File management and preview
- Purchase history
- Revenue tracking
- Download statistics

## Integration Notes

### Storage Configuration
Ensure the following storage directories are writable:
- `storage/app/books/` - Book files
- `storage/app/listings/` - Cover images
- `storage/app/public/` - Public file access

### Database Relationships
- Books belong to the "Books" category
- Books have one-to-many relationship with purchases
- Purchases track download tokens and attempts
- File paths stored relative to storage directory

### Search and Filtering
- Full-text search on title, description, author, ISBN
- Genre, type, and format filtering
- Price range filtering
- Multiple sorting options
- Paginated results

This comprehensive book system provides all the functionality needed for a modern book marketplace with digital downloads, physical books, and audiobooks.
