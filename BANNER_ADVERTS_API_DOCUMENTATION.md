# Banner Adverts API Documentation

## Overview

The Banner Adverts system provides a comprehensive digital billboard marketplace with real-time data, analytics, and user interactions. This document outlines all API endpoints, data structures, and integration patterns.

## Base Configuration

### Environment Variables
```bash
REACT_APP_API_URL=http://localhost:8000/api/v1
REACT_APP_BANNER_API_URL=http://localhost:8000/api/v1/banner
```

### Authentication
All API endpoints require JWT authentication. Include the token in the Authorization header:
```
Authorization: Bearer <jwt_token>
```

## API Endpoints

### 1. Banner Categories API

#### Get All Categories
```http
GET /api/v1/banner-categories
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Real Estate",
      "slug": "real-estate",
      "description": "Property listings and real estate services",
      "icon": "🏢",
      "active_banners_count": 1247,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "total": 12,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

#### Get Trending Categories
```http
GET /api/v1/banner-categories/trending?limit=10
```

#### Get Category by Slug
```http
GET /api/v1/banner-categories/{slug}
```

#### Create Category (Admin)
```http
POST /api/v1/banner-categories
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "name": "New Category",
  "slug": "new-category",
  "description": "Category description",
  "icon": "📋"
}
```

### 2. Banner Ads API

#### Get All Banner Ads
```http
GET /api/v1/banner-ads?category_id=1&country=USA&banner_size=728×90&promotion_tier=featured&verified_only=true&search=keyword&sort_by=created_at&sort_order=desc&page=1&limit=20
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Premium Real Estate Banner",
      "slug": "premium-real-estate-banner",
      "description": "Luxury properties in prime locations",
      "banner_image": "https://example.com/banner.jpg",
      "destination_link": "https://realestate.com",
      "business_name": "Luxury Properties Inc",
      "contact_person": "John Doe",
      "email": "john@luxury.com",
      "phone": "+1-555-123-4567",
      "website": "https://luxury.com",
      "country": "USA",
      "city": "New York",
      "banner_category_id": 1,
      "banner_size": "728×90",
      "promotion_tier": "featured",
      "is_verified_business": true,
      "is_currently_promoted": true,
      "is_currently_valid": true,
      "views_count": 15420,
      "clicks_count": 892,
      "ctr": 5.78,
      "status": "active",
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "total": 1,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

#### Get Featured Banner Ads
```http
GET /api/v1/banner-ads/featured?limit=6
```

#### Get Most Viewed Banner Ads
```http
GET /api/v1/banner-ads/most-viewed?limit=10
```

#### Get Recent Banner Ads
```http
GET /api/v1/banner-ads/recent?limit=10
```

#### Get Banner Ad by Slug
```http
GET /api/v1/banner-ads/{slug}
```

#### Track Banner Click
```http
POST /api/v1/banner-ads/{slug}/track-click
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
  "ip_address": "192.168.1.1",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

#### Create Banner Ad
```http
POST /api/v1/banner-ads
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "banner_type": "standard",
  "business_name": "My Business",
  "contact_person": "Jane Smith",
  "email": "jane@business.com",
  "phone": "+1-555-987-6543",
  "website": "https://mybusiness.com",
  "business_logo": "https://example.com/logo.jpg",
  "verified_badge": true,
  "title": "My Amazing Banner",
  "tagline": "Click here for amazing deals",
  "banner_category_id": 1,
  "country": "USA",
  "city": "Los Angeles",
  "target_audience": "Adults 25-45",
  "destination_link": "https://mywebsite.com",
  "call_to_action": "Shop Now",
  "banner_size": "728×90",
  "description": "This is an amazing banner for your business",
  "key_selling_points": "Premium quality, fast delivery",
  "offer_details": "20% off this week only",
  "validity_start": "2024-01-20T00:00:00Z",
  "validity_end": "2024-01-27T23:59:59Z",
  "target_countries": ["USA", "Canada", "UK"],
  "target_categories": [1, 2, 3],
  "target_devices": "both",
  "promotion_tier": "featured",
  "terms_accepted": true,
  "privacy_accepted": true
}
```

#### Update Banner Ad
```http
PUT /api/v1/banner-ads/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "title": "Updated Banner Title"
}
```

#### Delete Banner Ad
```http
DELETE /api/v1/banner-ads/{id}
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

#### Get User's Banner Ads
```http
GET /api/v1/banner-ads/my-banners?page=1&limit=20
Content-Type: application/json
Authorization: Bearer <jwt_token>
```

### 3. Banner Upload API

#### Upload Banner Image
```http
POST /api/v1/banner-upload/banner-image
Content-Type: multipart/form-data
Authorization: Bearer <jwt_token>

{
  "image": <file>,
  "banner_size": "728×90"
}
```

#### Upload Business Logo
```http
POST /api/v1/banner-upload/business-logo
Content-Type: multipart/form-data
Authorization: Bearer <jwt_token>

{
  "logo": <file>
}
```

#### Upload Animated Banner
```http
POST /api/v1/banner-upload/animated-banner
Content-Type: multipart/form-data
Authorization: Bearer <jwt_token>

{
  "gif": <file>,
  "banner_size": "728×90"
}
```

#### Upload HTML5 Banner
```http
POST /api/v1/banner-upload/html5-banner
Content-Type: multipart/form-data
Authorization: Bearer <jwt_token>

{
  "zip": <file>,
  "banner_size": "728×90"
}
```

#### Upload Video Banner
```http
POST /api/v1/banner-upload/video-banner
Content-Type: multipart/form-data
Authorization: Bearer <jwt_token>

{
  "video": <file>,
  "banner_size": "728×90"
}
```

#### Delete Uploaded File
```http
DELETE /api/v1/banner-upload/file
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "filename": "banner.jpg",
  "type": "banner_image"
}
```

### 4. Banner Marketplace API

#### Get Homepage Data
```http
GET /api/v1/banner-marketplace/homepage
```

**Response:**
```json
{
  "success": true,
  "data": {
    "featured_banners": [...],
    "trending_categories": [...],
    "recent_banners": [...],
    "stats": {
      "total_banners": 15234,
      "active_categories": 12,
      "total_views": 8500000,
      "total_clicks": 45000,
      "average_ctr": 5.29
    }
  }
}
```

#### Get Carousel Data
```http
GET /api/v1/banner-marketplace/carousel
```

#### Get Categories
```http
GET /api/v1/banner-marketplace/categories
```

#### Get Analytics
```http
GET /api/v1/banner-marketplace/analytics
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_banners": 15234,
    "active_banners": 12345,
    "total_views": 8500000,
    "total_clicks": 45000,
    "average_ctr": 5.29,
    "promotion_stats": {
      "standard": 10000,
      "promoted": 2500,
      "featured": 1500,
      "sponsored": 800
    },
    "category_stats": [
      {
        "category_name": "Real Estate",
        "banner_count": 3421,
        "total_views": 250000
      }
    ]
  }
}
```

### 5. Promotion Options API

#### Get Promotion Options
```http
GET /api/v1/banner-ads/promotion-options
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "free",
      "name": "Basic Listing",
      "price": 0,
      "duration": "30 days",
      "features": [
        "Standard visibility",
        "Basic analytics",
        "Email support"
      ],
      "badge": "None",
      "color": "from-gray-500 to-gray-600"
    },
    {
      "id": "promoted",
      "name": "Promoted Banner",
      "price": 29,
      "duration": "30 days",
      "features": [
        "Enhanced visibility",
        "Advanced analytics",
        "Priority support",
        "Promoted badge",
        "Top placement in category"
      ],
      "badge": "Promoted",
      "color": "from-blue-500 to-blue-600"
    },
    {
      "id": "featured",
      "name": "Featured Banner",
      "price": 49,
      "duration": "30 days",
      "features": [
        "Premium visibility",
        "Real-time analytics",
        "Dedicated support",
        "Featured badge",
        "Top placement"
      ],
      "badge": "Featured",
      "color": "from-purple-500 to-purple-600"
    },
    {
      "id": "sponsored",
      "name": "Sponsored Banner",
      "price": 99,
      "duration": "30 days",
      "features": [
        "Maximum visibility",
        "Premium analytics dashboard",
        "Account manager",
        "Sponsored badge",
        "Homepage placement"
      ],
      "badge": "Sponsored",
      "color": "from-yellow-500 to-orange-500"
    }
  ]
}
```

## Data Models

### BannerCategory
```typescript
interface BannerCategory {
  id: number;
  name: string;
  slug: string;
  description: string;
  icon: string;
  active_banners_count: number;
  created_at: string;
  updated_at: string;
}
```

### BannerAd
```typescript
interface BannerAd {
  id: number;
  title: string;
  slug: string;
  description: string;
  banner_image: string;
  destination_link: string;
  business_name: string;
  contact_person: string;
  email: string;
  phone: string;
  website: string;
  country: string;
  city: string;
  banner_category_id: number;
  banner_size: string;
  promotion_tier: 'free' | 'promoted' | 'featured' | 'sponsored';
  is_verified_business: boolean;
  is_currently_promoted: boolean;
  is_currently_valid: boolean;
  views_count: number;
  clicks_count: number;
  ctr: string;
  status: 'active' | 'pending' | 'expired';
  created_at: string;
  updated_at: string;
}
```

### BannerStats
```typescript
interface BannerStats {
  total_banners: number;
  active_banners: number;
  total_views: number;
  total_clicks: number;
  average_ctr: number;
  promotion_stats: {
    standard: number;
    promoted: number;
    featured: number;
    sponsored: number;
  };
  category_stats: CategoryStat[];
}

interface CategoryStat {
  category_name: string;
  banner_count: number;
  total_views: number;
}
```

### PromotionOption
```typescript
interface PromotionOption {
  id: string;
  name: string;
  price: number;
  duration: string;
  features: string[];
  badge: string;
  color: string;
}
```

## Error Handling

### Standard Error Response Format
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "errors": {
      "title": ["The title field is required."],
      "email": ["The email must be a valid email address."]
    }
  }
}
```

### HTTP Status Codes
- `200 OK` - Success
- `201 Created` - Resource created successfully
- `400 Bad Request` - Validation error
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `500 Internal Server Error` - Server error

## Integration Examples

### React Hook Usage
```javascript
import { useBannerAds, useFeaturedBanners, useBannerCategories } from '../hooks/useBannerData';

// In component
const { data: banners, loading, error, refetch } = useBannerAds({
  category_id: selectedCategory !== 'all' ? selectedCategory : undefined,
  country: selectedCountry !== 'all' ? selectedCountry : undefined,
  banner_size: selectedSize !== 'all' ? selectedSize : undefined,
  promotion_tier: selectedBadge !== 'all' ? selectedBadge : undefined,
  verified_only: verifiedOnly,
  search: searchQuery,
  sort_by: sortBy === 'recent' ? 'created_at' : sortBy,
  sort_order: 'desc',
  page: currentPage,
  limit: itemsPerPage
});

// Handle click tracking
const handleBannerClick = async (banner) => {
  try {
    await bannerAdsApi.trackClick(banner.slug);
    // Navigate to banner details or show success message
  } catch (error) {
    console.error('Failed to track click:', error);
  }
};
```

### File Upload Example
```javascript
const handleFileUpload = async (file, type) => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', type);

  try {
    const response = await bannerUploadApi.uploadBannerImage(formData, '728×90');
    // Handle successful upload
    setBannerImageUrl(response.data.url);
  } catch (error) {
    console.error('Upload failed:', error);
    // Handle error
  }
};
```

## Rate Limiting

- **File Upload**: 10MB per file
- **API Calls**: 100 requests per minute per user
- **Banner Creation**: 5 requests per minute per user
- **Analytics**: 50 requests per minute per user

## Security Considerations

1. **Input Validation**: All user inputs must be validated on both client and server
2. **File Upload Security**: 
   - File type restrictions (images: jpg, png, gif; videos: mp4, webm)
   - File size limits (images: 5MB; videos: 50MB)
   - Virus scanning for all uploads
3. **SQL Injection**: Use parameterized queries
4. **XSS Protection**: Sanitize all user-generated content
5. **CSRF Protection**: Use CSRF tokens for state-changing operations

## Testing

### Postman Collection
A comprehensive Postman collection is available with all API endpoints for testing:
- `WWA_Banner_Adverts_API.postman_collection.json` 

### Environment Setup
```bash
# Development
REACT_APP_API_URL=http://localhost:8000/api/v1

# Production
REACT_APP_API_URL=https://api.worldwideadverts.com/api/v1
```

## Frontend Integration Checklist

- [ ] JWT token management implemented
- [ ] API interceptors configured
- [ ] Error handling implemented
- [ ] Loading states implemented
- [ ] File upload handling implemented
- [ ] Click tracking implemented
- [ ] Real-time updates implemented
- [ ] Responsive design maintained
- [ ] Accessibility features implemented

This documentation provides a complete reference for implementing and integrating the Banner Adverts API system.

## Backend Implementation Summary

### Database Structure
- **banner_ads table**: Complete table with all required fields including business info, banner details, targeting, analytics, and promotion settings
- **banner_categories table**: Category management with active banner counts
- **banner_clicks table**: Click tracking with IP and user agent (if needed)
- **banner_uploads table**: File upload management (if needed)

### Models Created
- **BannerAd**: Main model with relationships, scopes, and methods for filtering, searching, and analytics
- **BannerCategory**: Category model with relationships and banner counting

### API Controllers
- **BannerAdController**: Full CRUD operations with filtering, sorting, search, and analytics
- **BannerCategoryController**: Category management with trending and analytics
- **BannerUploadController**: File upload handling for banners and logos
- **BannerMarketplaceController**: Homepage data, carousel, and marketplace analytics

### Filament Admin Resources
- **BannerAdResource**: Complete admin resource with full CRUD operations
  - Comprehensive form with all banner fields
  - Advanced filtering and search capabilities
  - Tables with sorting and bulk actions
  - Status badges and promotion indicators
  - Image previews and file uploads

- **BannerCategoryResource**: Admin resource for managing categories
  - Category configuration with icons and colors
  - Active banner counting
  - Sorting and management features

### Frontend User Dashboard
- **BannerDashboardController**: Complete frontend controller
  - Dashboard with statistics and analytics
  - My banners management with filtering
  - Banner creation and editing
  - Analytics tracking and reporting
  - File upload integration

### Frontend Views
- **Dashboard**: User statistics overview
  - Statistics cards (total banners, active banners, views, clicks, CTR)
  - Recent banners list
  - Promotion statistics
  - Quick action buttons

- **Create Form**: Comprehensive banner submission
  - Multi-step form sections
  - File upload capabilities
  - Promotion tier selection
  - Terms agreement
  - AJAX form submission

- **My Banners**: User banner management
  - Search and filtering
  - Status management
  - Analytics access
  - Edit and delete actions

### Frontend Routes
- **Public routes**: Browse, show, category filtering
- **Authenticated routes**: Dashboard, create, edit, analytics
- **Route protection**: Proper middleware for authenticated routes

### Key Features Implemented

#### Admin Panel:
- Complete banner management with all metadata
- Visual promotion status indicators
- Advanced filtering and search
- Bulk operations support
- Analytics and statistics widgets
- Category management
- File upload handling

#### Frontend:
- User dashboard with comprehensive statistics
- Banner submission forms with file uploads
- Browse and search functionality
- Promotion tier selection
- Analytics tracking and reporting
- Responsive mobile design

#### API Integration:
- RESTful API design with proper HTTP status codes
- Comprehensive validation for all inputs
- File upload security and storage management
- Database relationships and foreign key constraints
- Query scopes for efficient filtering
- JSON response formatting
- Authentication middleware protection

### Seeders Created
- **BannerCategorySeeder**: Populates banner categories with sample data
- **BannerAdSeeder**: Sample banner data for testing
- Updated DatabaseSeeder to include new seeders

The system is now ready for deployment with a database connection and provides a complete backend solution for the Banner Adverts frontend. All forms are visible in the admin panel and user submissions are properly tracked and manageable.
