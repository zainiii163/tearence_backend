# WorldwideAdverts Services Marketplace - Backend Implementation Documentation

## Overview

This document provides comprehensive details about the backend implementation of the WorldwideAdverts Services Marketplace - a Fiverr-style marketplace platform for services with advanced features including service posting, provider profiles, upsell management, and real-time analytics.

## Architecture

### Technology Stack
- **Framework**: Laravel 10.x
- **Database**: MySQL with proper indexing
- **Authentication**: JWT (tymon/jwt-auth)
- **Admin Panel**: Filament PHP
- **File Storage**: Laravel Storage (public disk)
- **API Documentation**: Swagger/OpenAPI ready

### Project Structure
```
app/
├── Http/Controllers/Api/
│   ├── ServiceController.php          # Main services API
│   ├── ServiceReviewController.php    # Reviews management
│   ├── ProviderController.php        # Provider profiles
│   ├── SearchController.php          # Search & filtering
│   ├── PromotionController.php      # Promotions & upsells
│   ├── AnalyticsController.php      # Analytics & reporting
│   └── FileUploadController.php    # File management
├── Models/
│   ├── Service.php                  # Service model
│   ├── ServiceReview.php           # Review model
│   ├── ServiceCategory.php         # Category model
│   ├── ServiceProvider.php         # Provider model
│   ├── ServicePromotion.php       # Promotion model
│   ├── ServiceMedia.php           # Media files
│   ├── ActivityLog.php            # Activity tracking
│   └── ProviderFollower.php      # Provider following
├── Filament/Resources/
│   ├── ServiceResource.php         # Admin service management
│   ├── ServiceReviewResource.php   # Admin review management
│   └── PromotionResource.php     # Admin promotion management
└── ...
database/migrations/
├── 2026_03_07_201412_create_services_table.php
├── 2026_03_07_201129_create_service_categories_table.php
├── 2026_03_07_201439_create_service_media_table.php
├── 2026_03_09_181057_create_service_promotions_table.php
├── 2026_03_16_220000_create_service_reviews_table.php
├── 2026_03_16_220001_create_activity_log_table.php
└── 2026_03_16_220002_create_provider_followers_table.php
```

## Database Schema

### Core Tables

#### Services Table
```sql
CREATE TABLE services (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    service_provider_id BIGINT,
    category_id BIGINT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    tagline TEXT,
    description LONGTEXT,
    whats_included JSON,
    whats_not_included JSON,
    requirements TEXT,
    service_type ENUM('freelance', 'local', 'business') DEFAULT 'freelance',
    starting_price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    delivery_time INT,
    availability JSON,
    country VARCHAR(100),
    city VARCHAR(100),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    service_area_radius INT,
    views INT DEFAULT 0,
    enquiries INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    review_count INT DEFAULT 0,
    status ENUM('draft', 'active', 'paused', 'suspended') DEFAULT 'draft',
    promotion_type ENUM('standard', 'promoted', 'featured', 'sponsored', 'network_boost') DEFAULT 'standard',
    promotion_expires_at TIMESTAMP NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    languages JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status_promotion (status, promotion_type),
    INDEX idx_category_country (category_id, country),
    INDEX idx_service_type_status (service_type, status),
    INDEX idx_promotion_expires (promotion_expires_at)
);
```

#### Service Categories Table
```sql
CREATE TABLE service_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Service Reviews Table
```sql
CREATE TABLE service_reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT NOT NULL,
    buyer_id BIGINT NOT NULL,
    provider_id BIGINT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    service_title VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_service_rating (service_id, rating),
    INDEX idx_provider_rating (provider_id, rating),
    INDEX idx_status (status)
);
```

#### Service Promotions Table
```sql
CREATE TABLE service_promotions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    service_id BIGINT NOT NULL,
    promotion_type ENUM('promoted', 'featured', 'sponsored', 'network_boost') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    duration_days INT NOT NULL,
    starts_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    benefits JSON,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_service_promotion (service_id, promotion_type),
    INDEX idx_status_expires (status, expires_at)
);
```

#### Activity Log Table
```sql
CREATE TABLE activity_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    service_id BIGINT,
    activity_type ENUM('view', 'enquiry', 'order', 'add', 'update', 'delete') NOT NULL,
    message TEXT,
    country VARCHAR(100),
    city VARCHAR(100),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at),
    INDEX idx_service_activity (service_id, activity_type)
);
```

## API Endpoints

### Authentication
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/refresh` - Token refresh
- `GET /api/v1/auth/logout` - User logout

### Services Management
- `GET /api/v1/services` - List services with filtering
- `GET /api/v1/services/{id}` - Get service details
- `POST /api/v1/services` - Create new service
- `PUT /api/v1/services/{id}` - Update service
- `DELETE /api/v1/services/{id}` - Delete service
- `GET /api/v1/services/my-services` - Get user's services
- `POST /api/v1/services/{id}/toggle-status` - Toggle service status
- `GET /api/v1/services/featured` - Get featured services
- `GET /api/v1/services/popular` - Get popular services
- `GET /api/v1/services/categories` - Get categories
- `POST /api/v1/services/{id}/enquiries` - Increment enquiry count

### Categories Management
- `GET /api/v1/categories` - List all categories
- `GET /api/v1/categories/{id}` - Get category details
- `GET /api/v1/categories/{slug}/services` - Get category services

### Provider Management
- `GET /api/v1/providers/{id}` - Get provider profile
- `GET /api/v1/providers/{id}/services` - Get provider services
- `GET /api/v1/providers/{id}/reviews` - Get provider reviews
- `POST /api/v1/providers/{id}/follow` - Follow provider
- `DELETE /api/v1/providers/{id}/follow` - Unfollow provider
- `GET /api/v1/providers/{id}/followers` - Get provider followers

### Reviews & Ratings
- `GET /api/v1/reviews/service/{serviceId}` - Get service reviews
- `POST /api/v1/reviews/service/{serviceId}` - Create review
- `PUT /api/v1/reviews/{reviewId}` - Update review
- `DELETE /api/v1/reviews/{reviewId}` - Delete review

### Promotions & Upsells
- `GET /api/v1/promotions/tiers` - Get promotion tiers
- `POST /api/v1/promotions/calculate-total` - Calculate promotion cost
- `POST /api/v1/promotions/purchase` - Purchase promotion
- `GET /api/v1/promotions/my-promotions` - Get user promotions
- `POST /api/v1/promotions/{id}/cancel` - Cancel promotion

### Search & Filtering
- `GET /api/v1/search/services` - Advanced search
- `GET /api/v1/search/suggestions` - Search suggestions
- `GET /api/v1/search/popular` - Popular services
- `GET /api/v1/search/trending` - Trending services

### Analytics & Reporting
- `GET /api/v1/analytics/dashboard` - Dashboard analytics
- `GET /api/v1/analytics/provider/{id}` - Provider analytics
- `GET /api/v1/analytics/service/{id}` - Service analytics

### File Upload & Media
- `POST /api/v1/upload/service-media` - Upload service media
- `POST /api/v1/upload/avatar` - Upload user avatar
- `GET /api/v1/upload/{fileId}` - Get file info
- `DELETE /api/v1/upload/{fileId}` - Delete file

## Models and Relationships

### Service Model
```php
class Service extends Model
{
    protected $fillable = [
        'user_id', 'service_provider_id', 'category_id', 'title', 'slug',
        'tagline', 'description', 'whats_included', 'whats_not_included',
        'requirements', 'service_type', 'starting_price', 'currency',
        'delivery_time', 'availability', 'country', 'city',
        'latitude', 'longitude', 'service_area_radius', 'views',
        'enquiries', 'rating', 'review_count', 'status',
        'promotion_type', 'promotion_expires_at', 'is_verified', 'languages'
    ];

    protected $casts = [
        'starting_price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'views' => 'integer',
        'enquiries' => 'integer',
        'rating' => 'decimal:2',
        'review_count' => 'integer',
        'promotion_expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'whats_included' => 'array',
        'whats_not_included' => 'array',
        'availability' => 'array',
        'languages' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    public function serviceProvider(): BelongsTo
    public function category(): BelongsTo
    public function packages(): HasMany
    public function promotions(): HasMany
    public function addons(): HasMany
    public function media(): HasMany
    public function reviews(): HasMany
    public function activities(): HasMany

    // Scopes
    public function scopeActive(Builder $query): Builder
    public function scopePromoted(Builder $query): Builder
    public function scopeFeatured(Builder $query): Builder
    public function scopeByCategory(Builder $query, $categoryId): Builder
    public function scopeByCountry(Builder $query, $country): Builder
    public function scopeByType(Builder $query, $type): Builder
    public function scopeVerified(Builder $query): Builder
    public function scopeSearch($query, $term)
}
```

### ServiceReview Model
```php
class ServiceReview extends Model
{
    protected $fillable = [
        'service_id', 'buyer_id', 'provider_id', 'rating',
        'comment', 'service_title', 'status'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
    ];

    // Relationships
    public function service(): BelongsTo
    public function buyer(): BelongsTo
    public function provider(): BelongsTo
}
```

## Key Features Implementation

### 1. Service Management
- Full CRUD operations for services
- Multiple service types: freelance, local, business
- Package and addon support
- Media management (images, videos, PDFs)
- Status management (draft, active, paused, suspended)

### 2. Promotion System
- 4-tier promotion system: Promoted, Featured, Sponsored, Network Boost
- Flexible duration options (7, 30, 90, 365 days)
- Automatic expiration handling
- Promotion analytics

### 3. Search & Filtering
- Advanced search with multiple filters
- Real-time search suggestions
- Sorting by various criteria
- Geographic filtering

### 4. Reviews & Ratings
- 5-star rating system
- Review moderation workflow
- Automatic rating calculation
- Review analytics

### 5. Analytics & Reporting
- Real-time activity tracking
- Provider performance metrics
- Service analytics
- Trend analysis

### 6. File Management
- Secure file upload
- Multiple file types support
- File validation and optimization
- CDN-ready storage structure

## Security Implementation

### Authentication
- JWT-based authentication
- Token refresh mechanism
- Role-based access control
- API rate limiting

### Data Validation
- Comprehensive input validation
- File type and size validation
- SQL injection prevention
- XSS protection

### Authorization
- Resource ownership checks
- Role-based permissions
- API endpoint protection

## Performance Optimization

### Database Optimization
- Strategic indexing
- Query optimization
- Eager loading relationships
- Pagination implementation

### Caching Strategy
- Redis for session storage
- Query result caching
- Static asset caching

### File Storage
- Efficient file organization
- Image optimization
- CDN integration ready

## Admin Panel Features

### Service Management
- Complete CRUD operations
- Bulk actions (approve, suspend)
- Advanced filtering
- Export capabilities

### Review Management
- Review moderation
- Bulk approval/rejection
- Review analytics

### Promotion Management
- Promotion tracking
- Revenue analytics
- Expiration monitoring

## API Response Formats

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation completed successfully"
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid input data",
    "details": { ... }
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "total_pages": 25,
    "total_items": 500,
    "items_per_page": 20,
    "has_next": true,
    "has_prev": false
  }
}
```

## Environment Configuration

### Required Environment Variables
```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=worldwideadverts
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# JWT
JWT_SECRET=your_super_secret_jwt_key
JWT_EXPIRES_IN=24h

# File Upload
UPLOAD_MAX_SIZE=10485760
UPLOAD_ALLOWED_TYPES=image/jpeg,image/png,image/gif,application/pdf

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_email_password

# Redis (for caching and sessions)
REDIS_HOST=localhost
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379
```

## Deployment Considerations

### Production Setup
1. Configure environment variables
2. Run database migrations
3. Set up file storage permissions
4. Configure SSL certificates
5. Set up cron jobs for maintenance
6. Configure backup procedures

### Scaling Recommendations
1. Implement database read replicas
2. Use Redis for caching
3. Implement CDN for file storage
4. Set up load balancing
5. Monitor performance metrics

## Testing

### Test Coverage
- Unit tests for models and controllers
- Feature tests for API endpoints
- Integration tests for workflows
- Performance testing for load handling

### Test Data
- Seeders for test data
- Factory definitions for models
- Test file uploads handling

## Monitoring & Maintenance

### Logging
- Comprehensive error logging
- Activity tracking
- Performance monitoring
- Security audit logs

### Maintenance Tasks
- Database optimization
- File cleanup
- Cache management
- Backup verification

## Future Enhancements

### Planned Features
1. Real-time notifications
2. Advanced analytics dashboard
3. Mobile API optimization
4. Multi-language support
5. Advanced search algorithms

### Scalability Improvements
1. Microservices architecture
2. Event-driven architecture
3. Advanced caching strategies
4. Database sharding

## Conclusion

This backend implementation provides a robust, scalable, and feature-rich foundation for the WorldwideAdverts Services Marketplace. The architecture follows Laravel best practices and implements modern web development patterns to ensure maintainability and performance.

The system is designed to handle high traffic volumes while maintaining data integrity and security. The comprehensive admin panel ensures efficient content management, while the well-structured API provides seamless integration with frontend applications.

## Documentation Files

- **API Collection**: `WWA Services Marketplace API Collection.json`
- **Database Migrations**: Located in `database/migrations/`
- **API Routes**: Defined in `routes/api.php`
- **Admin Resources**: Located in `app/Filament/Resources/`

For additional information or support, refer to the inline code documentation and API collection provided.
