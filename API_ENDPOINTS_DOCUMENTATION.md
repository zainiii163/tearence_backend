# WWA API Endpoints Documentation

## Base URL
```
https://your-domain.com/api/v1
```

## Authentication
Most endpoints require `Authorization: Bearer {token}` header.

---

## 🏷️ BANNER ADS SYSTEM ✅ **FIXED**

### Base Path: `/banner-ads`

#### Public Endpoints
```
GET    /banner-ads                    # List all banner ads
GET    /banner-ads/featured           # Get featured banner ads
GET    /banner-ads/most-viewed        # Get most viewed banner ads
GET    /banner-ads/recent             # Get recent banner ads
GET    /banner-ads/{slug}             # Get specific banner ad by slug
GET    /banner-ads/promotion-options   # Get promotion options and pricing
POST   /banner-ads/{slug}/track-click # Track banner ad click
```

#### Authenticated Endpoints (Require API Token)
```
POST   /banner-ads                    # Create new banner ad
PUT    /banner-ads/{id}              # Update banner ad
DELETE /banner-ads/{id}              # Delete banner ad
GET    /banner-ads/my-banners         # Get user's banner ads
```

---

## 📚 BOOKS ADVERTS SYSTEM

### Base Path: `/books-adverts`

#### Public Endpoints
```
GET    /books-adverts              # List all book adverts
GET    /books-adverts/{slug}       # Get specific book advert
GET    /books-adverts/featured     # Get featured book adverts
GET    /books-adverts/genre/{genre} # Get books by genre
GET    /books-adverts/pricing-plans # Get pricing plans
GET    /books-adverts/statistics   # Get statistics
```

#### Authenticated Endpoints
```
POST   /books-adverts              # Create new book advert
PUT    /books-adverts/{id}         # Update book advert
DELETE /books-adverts/{id}         # Delete book advert
GET    /books-adverts/my-books     # Get user's books
POST   /books-adverts/{id}/save   # Save book advert
POST   /books-adverts/{id}/views  # Track book views
POST   /books-adverts/{id}/payment # Process payment
```

---

## 🏪 BUY/SELL MARKETPLACE

### Base Path: `/buy-sell`

#### Public Endpoints
```
GET    /buy-sell                    # List all items
GET    /buy-sell/featured           # Get featured items
GET    /buy-sell/categories          # Get categories
GET    /buy-sell/{id}              # Get specific item
```

#### Authenticated Endpoints
```
POST   /buy-sell                    # Create new listing
PUT    /buy-sell/{id}              # Update listing
DELETE /buy-sell/{id}              # Delete listing
GET    /buy-sell/my-listings        # Get user's listings
```

---

## 🚗 VEHICLES MARKETPLACE

### Base Path: `/vehicles`

#### Public Endpoints
```
GET    /vehicles                    # List all vehicles
GET    /vehicles/featured           # Get featured vehicles
GET    /vehicles/promoted           # Get promoted vehicles
GET    /vehicles/sponsored          # Get sponsored vehicles
GET    /vehicles/recent             # Get recent vehicles
GET    /vehicles/{id}              # Get specific vehicle
GET    /vehicles/{id}/related       # Get related vehicles
GET    /vehicles/makes              # Get vehicle makes
GET    /vehicles/models/{makeId}    # Get models by make
GET    /vehicles/categories         # Get vehicle categories
```

#### Authenticated Endpoints
```
POST   /vehicles                    # Create new vehicle listing
PUT    /vehicles/{id}              # Update vehicle
DELETE /vehicles/{id}              # Delete vehicle
GET    /vehicles/my-vehicles        # Get user's vehicles
GET    /vehicles/saved             # Get saved vehicles
POST   /vehicles/{id}/save         # Save vehicle
POST   /vehicles/{id}/toggle-status # Toggle vehicle status
POST   /vehicles/{id}/mark-sold   # Mark as sold
POST   /vehicles/{id}/enquiry     # Send enquiry
```

---

## 🏠 PROPERTIES MARKETPLACE

### Base Path: `/properties`

#### Public Endpoints
```
GET    /properties                   # List all properties
GET    /properties/featured          # Get featured properties
GET    /properties/promoted          # Get promoted properties
GET    /properties/sponsored         # Get sponsored properties
GET    /properties/{id}             # Get specific property
GET    /properties/data/property-types
GET    /properties/data/categories
GET    /properties/data/commercial-types
GET    /properties/data/land-types
GET    /properties/data/planning-permissions
GET    /properties/data/view-types
```

#### Authenticated Endpoints
```
POST   /properties                   # Create new property
PUT    /properties/{id}             # Update property
DELETE /properties/{id}             # Delete property
GET    /properties/my-properties     # Get user's properties
POST   /properties/{id}/save         # Save property
POST   /properties/{id}/contact-agent # Contact agent
POST   /properties/{id}/track-event  # Track property event
```

---

## 💼 JOBS SYSTEM

### Base Path: `/jobs`

#### Public Endpoints
```
GET    /jobs/public                  # List public jobs
GET    /jobs/public/{jobId}         # Get specific public job
GET    /jobs/public/featured         # Get featured jobs
GET    /jobs/public/categories       # Get job categories
GET    /jobs/public/genre/{genre}    # Get jobs by genre
GET    /jobs/public/stats           # Get job statistics
GET    /jobs/public/seekers        # Get job seekers
GET    /jobs/public/seekers/stats    # Get seeker statistics
```

#### Authenticated Endpoints
```
POST   /jobs                        # Create new job
PUT    /jobs/{id}                  # Update job
DELETE /jobs/{id}                  # Delete job
GET    /jobs/my-jobs                 # Get user's jobs
POST   /jobs/{id}/save               # Save job
GET    /jobs/saved                   # Get saved jobs
POST   /jobs/{jobId}/apply           # Apply for job
GET    /jobs/applications             # Get applications
GET    /jobs/applications/{appId}     # Get specific application
PUT    /jobs/applications/{appId}/status # Update application status
GET    /jobs/my-applications         # Get user's applications
```

---

## 🎯 SERVICES MARKETPLACE

### Base Path: `/services`

#### Public Endpoints
```
OPTIONS /services                         # CORS preflight
GET    /services                          # List all services
GET    /services/popular                   # Get popular services
GET    /services/featured                   # Get featured services
GET    /services/categories                 # Get service categories
GET    /services/{service}                 # Get specific service
POST   /services/{service}/enquiries       # Increment enquiries
GET    /services/promotion-options          # Get promotion options
```

#### Authenticated Endpoints
```
POST   /services                          # Create new service
PUT    /services/{service}                 # Update service
DELETE /services/{service}                 # Delete service
GET    /services/my-services               # Get user's services
POST   /services/{service}/toggle-status   # Toggle service status
POST   /services/{service}/media           # Upload service media
POST   /services/{service}/purchase-promotion # Purchase promotion
```

---

## 🏢 VENUES & EVENTS

### Venues (`/venues`)
```
GET    /venues                    # List venues
GET    /venues/featured           # Get featured venues
GET    /venues/types              # Get venue types
GET    /venues/amenities          # Get amenities
GET    /venues/{slug}            # Get specific venue
POST   /venues                  # Create venue (auth)
PUT    /venues/{id}              # Update venue (auth)
DELETE /venues/{id}              # Delete venue (auth)
GET    /venues/my-venues          # Get user's venues (auth)
POST   /venues/upload-images      # Upload images (auth)
POST   /venues/upload-floor-plan  # Upload floor plan (auth)
```

### Events (`/events`)
```
GET    /events                    # List events
GET    /events/featured           # Get featured events
GET    /events/categories         # Get event categories
GET    /events/{slug}            # Get specific event
POST   /events                  # Create event (auth)
PUT    /events/{id}              # Update event (auth)
DELETE /events/{id}              # Delete event (auth)
GET    /events/my-events          # Get user's events (auth)
POST   /events/upload-images      # Upload images (auth)
```

---

## 🔍 SEARCH SYSTEM

### Base Path: `/search`
```
GET    /search/listings     # Search listings
GET    /search/services      # Search services
GET    /search/suggestions   # Get search suggestions
GET    /search/popular      # Get popular searches
GET    /search/trending     # Get trending searches
```

---

## 👤 USER MANAGEMENT

### Authentication (`/auth`)
```
POST   /auth/register           # User registration
POST   /auth/login              # User login
POST   /auth/login-admin        # Admin login
POST   /auth/forgot-password    # Forgot password
POST   /auth/reset-password     # Reset password
GET    /auth/logout             # Logout (auth)
GET    /auth/user-profile       # Get user profile (auth)
POST   /auth/change-password    # Change password (auth)
POST   /auth/web-login         # Web login
POST   /auth/web-logout        # Web logout
GET    /auth/web-check          # Check web auth
GET|POST /auth/refresh          # Refresh token
GET    /auth/debug             # Debug auth
```

### Users (`/customer`)
```
GET    /customer              # List customers
GET    /customer/{id}         # Get specific customer
POST   /customer              # Create customer
PUT    /customer/{id}         # Update customer
DELETE /customer/{id}         # Delete customer
POST   /customer/upload-avatar/{id} # Upload avatar
```

---

## 📊 ANALYTICS & STATISTICS

### User Analytics (`/user-analytics`) - Auth Required
```
GET    /user-analytics/dashboard          # User dashboard
GET    /user-analytics/listing-analytics # Listing analytics
GET    /user-analytics/profile-analytics  # Profile analytics
GET    /user-analytics/export           # Export analytics
```

### General Analytics (`/analytics`) - Auth Required
```
GET    /analytics/revenue      # Revenue analytics
GET    /analytics/jobs         # Jobs analytics
GET    /analytics/candidates   # Candidates analytics
GET    /analytics/upsells      # Upsells analytics
GET    /analytics/overview     # Overview analytics
GET    /analytics/user-posts    # User posts analytics
POST   /analytics/track-event # Track analytics event
```

---

## 🏢 ADMIN ENDPOINTS

### Admin Analytics (`/admin-analytics`) - Auth Required
```
GET    /admin-analytics/dashboard         # Admin dashboard
GET    /admin-analytics/user-analytics   # User analytics
GET    /admin-analytics/listing-analytics # Listing analytics
GET    /admin-analytics/export          # Export analytics
POST   /admin-analytics/permissions     # Manage permissions
```

### Admin Maintenance (`/admin/maintenance`) - Auth Required
```
GET    /admin/maintenance/status  # Get maintenance status
POST   /admin/maintenance/down    # Put site in maintenance
POST   /admin/maintenance/up      # Bring site up
POST   /admin/maintenance/schedule # Schedule maintenance
GET    /admin/maintenance/logs   # Get maintenance logs
```

### Admin Notifications (`/admin/notifications`) - Auth Required
```
GET    /admin/notifications              # Get notifications
GET    /admin/notifications/unread-count # Get unread count
POST   /admin/notifications/{id}/mark-read # Mark as read
POST   /admin/notifications/mark-all-read # Mark all as read
DELETE /admin/notifications/{id} # Delete notification
POST   /admin/notifications/create # Create notification
GET    /admin/notifications/stats # Get stats
POST   /admin/notifications/cleanup # Cleanup notifications
```

---

## 🏷️ CATEGORIES

### Base Path: `/category`
```
GET    /category              # List categories
GET    /category/tree          # Get category tree
GET    /category/{id}/filters # Get category filters
GET    /category/{id}/posting-form # Get posting form
GET    /category/{id}         # Get specific category
POST   /category              # Create category
PUT    /category/{id}         # Update category
DELETE /category/{id}         # Delete category
```

---

## 📍 LOCATIONS

### Base Path: `/location`
```
GET    /location         # List locations
GET    /location/{id}    # Get specific location
POST   /location         # Create location
PUT    /location/{id}    # Update location
DELETE /location/{id}    # Delete location
```

---

## 🌍 MASTER DATA

### Base Path: `/master`
```
GET    /master/currency # Get currencies
GET    /master/country  # Get countries
GET    /master/zone     # Get zones
```

---

## 📈 PROMOTIONS & UPSELLS

### Promotions (`/promotions`) - Auth Required
```
GET    /promotions/tiers           # Get promotion tiers
POST   /promotions/calculate-total # Calculate total
POST   /promotions/purchase       # Purchase promotion
GET    /promotions/my-promotions  # Get user's promotions
POST   /promotions/{id}/cancel   # Cancel promotion
```

### Upsells (`/upsells`) - Auth Required
```
GET    /upsells/options           # Get upsell options
POST   /upsells/purchase         # Purchase upsell
GET    /upsells/my-upsells       # Get user's upsells
GET    /upsells/stats            # Get upsell stats
POST   /upsells/{upsellId}/cancel # Cancel upsell
```

---

## 📁 FILE UPLOADS

### Base Path: `/upload` - Auth Required
```
POST   /upload/service-media  # Upload service media
POST   /upload/avatar        # Upload user avatar
DELETE /upload/{fileId}     # Delete file
GET    /upload/{fileId}     # Get file info
```

---

## 🔗 REFERRAL SYSTEM

### Base Path: `/referral`
```
POST   /referral/validate        # Validate referral code
GET    /referral/info           # Get referral info
GET    /referral/my             # Get user's referrals (auth)
POST   /referral/create         # Create referral (auth)
PUT    /referral/{referral_id} # Update referral (auth)
GET    /referral/history        # Get referral history (auth)
GET    /referral/{referral_id}/share # Share referral (auth)
```

---

## ✅ HEALTH CHECK

### System Status
```
GET    /health                 # API health check
GET    /cors-test              # CORS test endpoint
```

---

## 📝 RESPONSE FORMAT

### Success Response
```json
{
    "success": true,
    "data": { ... },
    "meta": {
        "count": 10,
        "total": 100,
        "per_page": 20,
        "current_page": 1,
        "last_page": 5
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description",
    "errors": { ... }
}
```

---

## 🔧 INTEGRATION NOTES

### 1. Authentication
- Include `Authorization: Bearer {token}` header for protected endpoints
- Use `/auth/login` to obtain access token
- Refresh tokens using `/auth/refresh`

### 2. Pagination
- Use `?page=2` for page navigation
- Use `?limit=50` to adjust page size
- Response includes pagination metadata

### 3. Error Handling
- HTTP 422: Validation errors
- HTTP 401: Unauthorized
- HTTP 404: Not found
- HTTP 500: Server error

### 4. Rate Limiting
- Check `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers
- Default limit: 60 requests per minute

### 5. CORS
- All endpoints support CORS
- Include `Origin`, `Content-Type`, `Authorization` in preflight requests

---

## 🚀 QUICK START

### 1. Get Featured Banner Ads
```bash
curl -X GET "https://your-domain.com/api/v1/banner-ads/featured" \
  -H "Accept: application/json"
```

### 2. Create Banner Ad (Authenticated)
```bash
curl -X POST "https://your-domain.com/api/v1/banner-ads" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My Banner Ad",
    "business_name": "My Business",
    "banner_type": "image",
    "banner_size": "728x90",
    "destination_link": "https://example.com"
  }'
```

### 3. Search Services
```bash
curl -X GET "https://your-domain.com/api/v1/search/services?q=web+design" \
  -H "Accept: application/json"
```

---

*Last Updated: March 2026*
*Banner Ads System: ✅ FIXED and Working*
