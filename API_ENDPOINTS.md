# WWA API Endpoints Documentation

## Base URL
```
https://your-domain.com/api/v1/
```

## Authentication
Most endpoints require authentication using Bearer token:
```
Authorization: Bearer {your_jwt_token}
```

---

## 📝 AUTHENTICATION ENDPOINTS

### Public Routes (No Authentication Required)
- `POST /auth/register` - Register new user
- `POST /auth/login` - User login
- `POST /auth/login-admin` - Admin login
- `POST /auth/forgot-password` - Send password reset email
- `POST /auth/reset-password` - Reset password
- `POST /auth/web-login` - Web login (JWT for frontend)
- `POST /auth/web-logout` - Web logout
- `GET /auth/web-check` - Check web authentication status
- `GET|POST /auth/refresh` - Refresh JWT token
- `GET /auth/debug` - Debug authentication

### Protected Routes (Authentication Required)
- `GET /auth/logout` - User logout
- `GET /auth/user-profile` - Get user profile
- `POST /auth/change-password` - Change password

---

## 🔍 GENERAL ENDPOINTS

### Health & Testing
- `GET /health` - API health check
- `GET /cors-test` - CORS testing endpoint

---

## 📢 LISTINGS/ADS MANAGEMENT

### Public Routes
- `GET /listing` - Get all listings
- `GET /listing/{slug}` - Get specific listing by slug
- `GET /listing/{slug}/classified` - Get classified listing

### Protected Routes
- `POST /listing` - Create new listing
- `PUT /listing/{id}` - Update listing
- `DELETE /listing/{id}` - Delete listing
- `GET /listing/my-listing` - Get user's listings
- `POST /listing/featured` - Mark as featured
- `POST /listing/new` - Mark as new
- `POST /listing/promoted` - Mark as promoted
- `POST /listing/ebay` - eBay integration
- `POST /listing/global` - Global listing

---

## ✅ LISTING APPROVAL SYSTEM
*Authentication Required*

- `GET /listing-approval/pending` - Get pending listings
- `GET /listing-approval/harmful` - Get harmful listings
- `GET /listing-approval/statistics` - Get approval statistics
- `POST /listing-approval/{listingId}/approve` - Approve listing
- `POST /listing-approval/{listingId}/reject` - Reject listing
- `POST /listing-approval/{listingId}/mark-harmful` - Mark as harmful

---

## 🔐 KYC VERIFICATION
*Authentication Required*

- `GET /kyc/status` - Get KYC status
- `POST /kyc/submit` - Submit KYC documents
- `GET /kyc/pending` - Get pending KYC applications
- `POST /kyc/{userId}/approve` - Approve KYC
- `POST /kyc/{userId}/reject` - Reject KYC
- `GET /kyc/statistics` - Get KYC statistics

---

## 📂 CATEGORIES
- `GET /category` - Get all categories
- `GET /category/tree` - Get category tree structure
- `GET /category/{id}/filters` - Get category filters
- `GET /category/{id}/posting-form` - Get posting form for category
- `GET /category/{id}` - Get specific category
- `POST /category` - Create category
- `PUT /category/{id}` - Update category
- `DELETE /category/{id}` - Delete category

---

## 👥 CUSTOMER MANAGEMENT
- `GET /customer` - Get all customers
- `GET /customer/{id}` - Get specific customer
- `POST /customer` - Create customer
- `PUT /customer/{id}` - Update customer
- `DELETE /customer/{id}` - Delete customer
- `POST /customer/upload-avatar/{id}` - Upload customer avatar

---

## ❤️ LISTING FAVORITES
- `GET /listing-favorite` - Get all favorites
- `GET /listing-favorite/{id}` - Get specific favorite
- `POST /listing-favorite` - Add to favorites
- `PUT /listing-favorite/{id}` - Update favorite
- `DELETE /listing-favorite/{id}` - Remove favorite

---

## 📦 LISTING PACKAGES
- `GET /listing-package` - Get all packages
- `GET /listing-package/{id}` - Get specific package
- `POST /listing-package` - Create package
- `PUT /listing-package/{id}` - Update package
- `DELETE /listing-package/{id}` - Delete package

---

## 🌍 MASTER DATA
- `GET /master/currency` - Get currencies
- `GET /master/country` - Get countries
- `GET /master/zone` - Get zones

---

## 📍 LOCATIONS
- `GET /location` - Get all locations
- `GET /location/{id}` - Get specific location
- `POST /location` - Create location
- `PUT /location/{id}` - Update location
- `DELETE /location/{id}` - Delete location

---

## 🏢 BUSINESS MANAGEMENT

### Public Routes
- `GET /business` - Get all businesses
- `GET /business/{id}` - Get specific business
- `GET /business/{slug}` - Get business by slug
- `GET /business/{customer_id}/detail` - Get business details

### Protected Routes
- `POST /business` - Create business
- `PUT /business/{id}` - Update business
- `DELETE /business/{id}` - Delete business
- `GET /business/my-business` - Get user's business

---

## 📰 CLASSIFIED ADS
- `GET /classified` - Get classified ads
- `GET /classified/{slug}` - Get specific classified ad

---

## 🎯 CAMPAIGNS
- `GET /campaign` - Get all campaigns
- `GET /campaign/{slug}` - Get specific campaign
- `POST /campaign` - Create campaign
- `PUT /campaign/{id}` - Update campaign
- `DELETE /campaign/{id}` - Delete campaign

---

## 🩸 DONORS
- `GET /donor` - Get all donors
- `GET /donor/{id}` - Get specific donor
- `POST /donor` - Create donor
- `PUT /donor/{id}` - Update donor
- `DELETE /donor/{id}` - Delete donor

---

## 📚 BLOG
- `GET /blog` - Get all blog posts
- `GET /blog/{slug}` - Get specific blog post
- `POST /blog` - Create blog post
- `PUT /blog/{id}` - Update blog post
- `DELETE /blog/{id}` - Delete blog post

---

## 🤝 AFFILIATE SYSTEM
- `GET /affiliate` - Get all affiliates
- `GET /affiliate/{id}` - Get specific affiliate
- `POST /affiliate` - Create affiliate
- `PUT /affiliate/{id}` - Update affiliate
- `DELETE /affiliate/{id}` - Delete affiliate
- `GET /affiliate/pricing-plans` - Get pricing plans
- `POST /affiliate/payment` - Process payment
- `GET /affiliate/my-affiliate` - Get user's affiliate

---

## 📖 BOOKS ADVERTS SYSTEM

### Public Routes
- `GET /books-adverts` - Get all books
- `GET /books-adverts/{slug}` - Get specific book
- `GET /books-adverts/featured` - Get featured books
- `GET /books-adverts/genre/{genre}` - Get books by genre
- `GET /books-adverts/pricing-plans` - Get pricing plans
- `GET /books-adverts/statistics` - Get platform statistics

### Protected Routes
- `POST /books-adverts` - Create book advert
- `PUT /books-adverts/{id}` - Update book advert
- `DELETE /books-adverts/{id}` - Delete book advert
- `GET /books-adverts/my-books` - Get user's books
- `POST /books-adverts/{id}/save` - Save/bookmark book
- `POST /books-adverts/{id}/views` - Increment view count
- `POST /books-adverts/{id}/payment` - Process promotion payment

---

## 📚 BOOKS SYSTEM
- `GET /books` - Get all books
- `GET /books/{id}` - Get specific book
- `POST /books` - Create book (auth:customer)
- `POST /books/{id}/purchase` - Purchase book (auth:customer)
- `GET /books/download/{token}` - Download book
- `GET /books/my-purchases` - Get user's purchases (auth:customer)
- `GET /books/statistics` - Get book statistics (auth:api)
- `POST /books/scrape` - Scrape book data

---

## 🎨 BANNER ADS
- `GET /banner` - Get all banners
- `GET /banner/{id}` - Get specific banner
- `GET /banner/{slug}` - Get banner by slug
- `POST /banner` - Create banner
- `PUT /banner/{id}` - Update banner
- `DELETE /banner/{id}` - Delete banner
- `POST /banner/upload` - Upload banner
- `GET /banner/pricing-plans` - Get pricing plans
- `POST /banner/payment` - Process payment
- `GET /banner/my-banner` - Get user's banner

---

## 💰 AD PRICING PLANS
*Authentication Required*

- `GET /ad-pricing-plans` - Get all pricing plans
- `POST /ad-pricing-plans` - Create pricing plan
- `PUT /ad-pricing-plans/{id}` - Update pricing plan
- `DELETE /ad-pricing-plans/{id}` - Delete pricing plan

---

## 👨‍💼 CANDIDATE PROFILES

### Public Routes
- `GET /candidate-profile` - Get all candidate profiles
- `GET /candidate-profile/{id}` - Get specific candidate profile

### Protected Routes
- `GET /candidate-profile/my-profile` - Get user's profile
- `POST /candidate-profile` - Create candidate profile
- `PUT /candidate-profile/{id}` - Update candidate profile
- `DELETE /candidate-profile/{id}` - Delete candidate profile

---

## 💼 JOB UPSELLS
*Authentication Required*

- `GET /job-upsell` - Get all job upsells
- `POST /job-upsell` - Create job upsell
- `POST /job-upsell/{id}/complete-payment` - Complete payment
- `GET /job-upsell/listing/{listingId}` - Get upsells by listing

---

## 🎯 CANDIDATE UPSELLS
*Authentication Required*

- `GET /candidate-upsell` - Get all candidate upsells
- `POST /candidate-upsell` - Create candidate upsell
- `POST /candidate-upsell/{id}/complete-payment` - Complete payment
- `GET /candidate-upsell/profile/{profileId}` - Get upsells by profile

---

## 🔔 JOB ALERTS
*Authentication Required*

- `GET /job-alert` - Get all job alerts
- `POST /job-alert` - Create job alert
- `GET /job-alert/{id}` - Get specific job alert
- `PUT /job-alert/{id}` - Update job alert
- `DELETE /job-alert/{id}` - Delete job alert
- `GET /job-alert/{id}/matching-jobs` - Get matching jobs
- `POST /job-alert/{id}/toggle-active` - Toggle alert status

### Job Alert Notifications (for cron jobs)
- `GET /job-alert-notifications/ready` - Get alerts ready for notification
- `POST /job-alert-notifications/{id}/notified` - Mark as notified

---

## 📊 DASHBOARD
*Authentication Required*

- `GET /dashboard/user` - Get user dashboard
- `GET /dashboard/admin` - Get admin dashboard

---

## 📈 ANALYTICS

### Protected Routes
- `GET /analytics/revenue` - Get revenue analytics
- `GET /analytics/jobs` - Get job analytics
- `GET /analytics/candidates` - Get candidate analytics
- `GET /analytics/upsells` - Get upsell analytics
- `GET /analytics/overview` - Get overview analytics
- `GET /analytics/user-posts` - Get user posts analytics

### Public Routes
- `POST /analytics/track-event` - Track event (no auth required for views)

---

## 💬 CHAT
*Authentication Required*

- `GET /chat/conversations` - Get conversations
- `GET /chat/unread-count` - Get unread count

---

## 👥 STAFF MANAGEMENT
*Authentication Required*

- `GET /staff` - Get all staff
- `GET /staff/my-memberships` - Get user's staff memberships
- `POST /staff` - Create staff
- `PUT /staff/{id}` - Update staff
- `DELETE /staff/{id}` - Delete staff
- `POST /staff/search-users` - Search users
- `POST /staff/check-and-invite` - Check and invite user
- `POST /staff/add-staff-member` - Add staff member

---

## 🚀 LISTING UPSELLS
*Authentication Required*

- `GET /upsell/options` - Get upsell options
- `POST /upsell/purchase` - Purchase upsell
- `GET /upsell/my-upsells` - Get user's upsells
- `GET /upsell/stats` - Get upsell statistics
- `POST /upsell/{upsellId}/cancel` - Cancel upsell

---

## 🔍 SEARCH
- `GET /search/listings` - Search listings with priority ordering

---

## 🛠️ SERVICES MARKETPLACE (Fiverr-like)

### Public Routes
- `OPTIONS /services` - Handle OPTIONS preflight request
- `GET /services` - Get all services
- `GET /services/popular` - Get popular services
- `GET /services/featured` - Get featured services
- `GET /services/categories` - Get service categories
- `GET /services/{service}` - Get specific service
- `POST /services/{service}/enquiries` - Increment enquiries
- `GET /services/promotion-options` - Get promotion options

### Protected Routes
- `POST /services` - Create service
- `PUT /services/{service}` - Update service
- `DELETE /services/{service}` - Delete service
- `GET /services/my-services` - Get user's services
- `POST /services/{service}/toggle-status` - Toggle service status
- `POST /services/{service}/media` - Upload service media
- `POST /services/{service}/purchase-promotion` - Purchase promotion

---

## 📊 SERVICE ANALYTICS
- `GET /service-analytics/live-activity` - Get live activity feed
- `GET /service-analytics/trending` - Get trending services
- `GET /service-analytics/marketplace-stats` - Get marketplace stats

### Protected Routes
- `GET /service-analytics/service/{service}` - Get service analytics

---

## ⚖️ SERVICE COMPARISON
*Authentication Required*

- `POST /service-comparison/compare` - Compare services
- `POST /service-comparison/save-comparison` - Save comparison

---

## 📦 SERVICE ORDERS
*Authentication Required*

- `GET /service-orders` - Get all orders
- `GET /service-orders/seller` - Get seller orders
- `GET /service-orders/buyer` - Get buyer orders
- `GET /service-orders/stats` - Get order statistics
- `POST /service-orders` - Create order
- `GET /service-orders/{order}` - Get specific order
- `PUT /service-orders/{order}/status` - Update order status
- `POST /service-orders/{order}/accept` - Accept order
- `POST /service-orders/{order}/reject` - Reject order
- `POST /service-orders/{order}/complete` - Complete order
- `POST /service-orders/{order}/refund` - Request refund
- `POST /service-orders/{order}/review` - Add review

---

## 🤝 AFFILIATE PROGRAMS
- `GET /affiliate-programs` - Get all affiliate programs
- `GET /affiliate-programs/featured` - Get featured programs
- `GET /affiliate-programs/networks` - Get networks
- `GET /affiliate-programs/{program}` - Get specific program
- `POST /affiliate-programs/{program}/track-click` - Track click
- `POST /affiliate-programs/record-conversion` - Record conversion

### Protected Routes
- `POST /affiliate-programs` - Create affiliate program
- `PUT /affiliate-programs/{program}` - Update program
- `DELETE /affiliate-programs/{program}` - Delete program
- `GET /affiliate-programs/my-programs` - Get user's programs

---

## 🏨 EVENTS & VENUES

### Events
- `GET /events` - Get all events
- `GET /events/{event}` - Get specific event
- `POST /events` - Create event
- `PUT /events/{event}` - Update event
- `DELETE /events/{event}` - Delete event

### Venues
- `GET /venues` - Get all venues
- `GET /venues/{venue}` - Get specific venue
- `POST /venues` - Create venue
- `PUT /venues/{venue}` - Update venue
- `DELETE /venues/{venue}` - Delete venue

### Venue Services
- `GET /venue-services` - Get all venue services
- `GET /venue-services/{service}` - Get specific venue service
- `POST /venue-services` - Create venue service
- `PUT /venue-services/{service}` - Update venue service
- `DELETE /venue-services/{service}` - Delete venue service

---

## 🚗 VEHICLES SYSTEM

### Vehicles
- `GET /vehicles` - Get all vehicles
- `GET /vehicles/{vehicle}` - Get specific vehicle
- `POST /vehicles` - Create vehicle
- `PUT /vehicles/{vehicle}` - Update vehicle
- `DELETE /vehicles/{vehicle}` - Delete vehicle

### Vehicle Categories
- `GET /vehicle-categories` - Get all vehicle categories
- `GET /vehicle-categories/{category}` - Get specific category
- `POST /vehicle-categories` - Create category
- `PUT /vehicle-categories/{category}` - Update category
- `DELETE /vehicle-categories/{category}` - Delete category
- `POST /vehicle-categories/{category}/toggle-status` - Toggle category status

---

## 💰 BUY & SELL SYSTEM

### Public Routes
- `GET /buysell/adverts` - Get all adverts
- `GET /buysell/adverts/{id}` - Get specific advert
- `GET /buysell/categories` - Get categories
- `GET /buysell/categories/{categoryId}/subcategories` - Get subcategories
- `GET /buysell/featured` - Get featured adverts
- `GET /buysell/promotions/pricing` - Get promotion pricing

### Protected Routes
- `POST /buysell/adverts` - Create advert
- `PUT /buysell/adverts/{id}` - Update advert
- `DELETE /buysell/adverts/{id}` - Delete advert
- `GET /buysell/my-adverts` - Get user's adverts
- `POST /buysell/adverts/{id}/promote` - Promote advert
- `POST /buysell/adverts/{id}/report` - Report advert
- `GET /buysell/recently-viewed` - Get recently viewed

---

## 🏖️ RESORTS & TRAVEL

### Resorts
- `GET /resorts-travel` - Get all resorts
- `GET /resorts-travel/{resort}` - Get specific resort
- `POST /resorts-travel` - Create resort
- `PUT /resorts-travel/{resort}` - Update resort
- `DELETE /resorts-travel/{resort}` - Delete resort

### Resort Categories
- `GET /resorts-travel-categories` - Get all categories
- `GET /resorts-travel-categories/{category}` - Get specific category
- `POST /resorts-travel-categories` - Create category
- `PUT /resorts-travel-categories/{category}` - Update category
- `DELETE /resorts-travel-categories/{category}` - Delete category

---

## 🎯 BANNER MARKETPLACE

### Banners
- `GET /banner-marketplace` - Get all banners
- `GET /banner-marketplace/{banner}` - Get specific banner
- `POST /banner-marketplace` - Create banner
- `PUT /banner-marketplace/{banner}` - Update banner
- `DELETE /banner-marketplace/{banner}` - Delete banner

### Banner Categories
- `GET /banner-categories` - Get all categories
- `GET /banner-categories/{category}` - Get specific category
- `POST /banner-categories` - Create category
- `PUT /banner-categories/{category}` - Update category
- `DELETE /banner-categories/{category}` - Delete category

---

## 📈 BANNER UPLOADS
- `POST /banner-uploads` - Upload banner

---

## 🎯 SPONSORED ADVERTS

### Sponsored Adverts
- `GET /sponsored-adverts` - Get all sponsored adverts
- `GET /sponsored-adverts/{advert}` - Get specific advert
- `POST /sponsored-adverts` - Create sponsored advert
- `PUT /sponsored-adverts/{advert}` - Update advert
- `DELETE /sponsored-adverts/{advert}` - Delete advert

### Sponsored Categories
- `GET /sponsored-categories` - Get all categories
- `GET /sponsored-categories/{category}` - Get specific category
- `POST /sponsored-categories` - Create category
- `PUT /sponsored-categories/{category}` - Update category
- `DELETE /sponsored-categories/{category}` - Delete category

### Sponsored Pricing Plans
- `GET /sponsored-pricing-plans` - Get all pricing plans
- `GET /sponsored-pricing-plans/{plan}` - Get specific plan
- `POST /sponsored-pricing-plans` - Create pricing plan
- `PUT /sponsored-pricing-plans/{plan}` - Update pricing plan
- `DELETE /sponsored-pricing-plans/{plan}` - Delete pricing plan

---

## ⭐ FEATURED ADVERTS

### Featured Adverts
- `GET /featured-adverts` - Get all featured adverts
- `GET /featured-adverts/{advert}` - Get specific advert
- `POST /featured-adverts` - Create featured advert
- `PUT /featured-adverts/{advert}` - Update advert
- `DELETE /featured-adverts/{advert}` - Delete advert

### Featured Advert Banners
- `GET /featured-advert-banners` - Get all banners
- `GET /featured-advert-banners/{banner}` - Get specific banner
- `POST /featured-advert-banners` - Create banner
- `PUT /featured-advert-banners/{banner}` - Update banner
- `DELETE /featured-advert-banners/{banner}` - Delete banner

---

## 🏠 PROPERTIES

### Properties
- `GET /properties` - Get all properties
- `GET /properties/{property}` - Get specific property
- `POST /properties` - Create property
- `PUT /properties/{property}` - Update property
- `DELETE /properties/{property}` - Delete property

### Property Upsells
- `GET /property-upsells` - Get all property upsells
- `GET /property-upsells/{upsell}` - Get specific upsell
- `POST /property-upsells` - Create upsell
- `PUT /property-upsells/{upsell}` - Update upsell
- `DELETE /property-upsells/{upsell}` - Delete upsell

---

## 🎯 PROMOTED ADVERT CATEGORIES
- `GET /promoted-advert-categories` - Get all categories
- `GET /promoted-advert-categories/{category}` - Get specific category
- `POST /promoted-advert-categories` - Create category
- `PUT /promoted-advert-categories/{category}` - Update category
- `DELETE /promoted-advert-categories/{category}` - Delete category

---

## 💰 FUNDING SYSTEM

### Funding Projects
- `GET /funding-projects` - Get all funding projects
- `GET /funding-projects/{project}` - Get specific project
- `POST /funding-projects` - Create project
- `PUT /funding-projects/{project}` - Update project
- `DELETE /funding-projects/{project}` - Delete project

### Funding Pledges
- `GET /funding-pledges` - Get all pledges
- `GET /funding-pledges/{pledge}` - Get specific pledge
- `POST /funding-pledges` - Create pledge
- `PUT /funding-pledges/{pledge}` - Update pledge
- `DELETE /funding-pledges/{pledge}` - Delete pledge

---

## 👔 JOBS & VACANCIES SYSTEM (COMPREHENSIVE)

### Public Routes (No Authentication Required)
- `GET /jobs/public` - Get all jobs
- `GET /jobs/public/{jobId}` - Get specific job
- `GET /jobs/public/featured` - Get featured jobs
- `GET /jobs/public/categories` - Get job categories
- `GET /jobs/public/genre/{genre}` - Get jobs by category
- `GET /jobs/public/stats` - Get job statistics
- `GET /jobs/public/seekers` - Get job seekers
- `GET /jobs/public/seekers/{seekerId}` - Get specific job seeker
- `GET /jobs/public/seekers/stats` - Get seeker statistics

### Protected Routes (Authentication Required)
#### Job Management
- `POST /jobs` - Create job
- `PUT /jobs/{id}` - Update job
- `DELETE /jobs/{id}` - Delete job
- `GET /jobs/my-jobs` - Get user's jobs
- `POST /jobs/{id}/save` - Save/bookmark job
- `GET /jobs/saved` - Get saved jobs

#### Job Applications
- `POST /jobs/{jobId}/apply` - Apply for job
- `GET /jobs/applications` - Get job applications
- `GET /jobs/applications/{applicationId}` - Get specific application
- `PUT /jobs/applications/{applicationId}/status` - Update application status
- `GET /jobs/applications/stats` - Get application statistics
- `GET /jobs/my-applications` - Get sent applications
- `POST /jobs/applications/{applicationId}/withdraw` - Withdraw application

#### Job Seeker Profiles
- `POST /jobs/seekers` - Create job seeker profile
- `PUT /jobs/seekers/{id}` - Update seeker profile
- `DELETE /jobs/seekers/{id}` - Delete seeker profile
- `GET /jobs/seekers/my-profile` - Get my seeker profile
- `GET /jobs/seekers/my-applications` - Get seeker's applications
- `GET /jobs/seekers/my-statistics` - Get seeker statistics

#### Job Alerts
- `POST /jobs/alerts` - Create job alert
- `GET /jobs/alerts` - Get job alerts
- `GET /jobs/alerts/{id}` - Get specific alert
- `PUT /jobs/alerts/{id}` - Update alert
- `DELETE /jobs/alerts/{id}` - Delete alert
- `POST /jobs/alerts/{id}/test` - Test job alert
- `GET /jobs/alerts/stats` - Get alert statistics
- `GET /jobs/alerts/{id}/matching-jobs` - Get matching jobs

#### Premium Upsells
- `GET /jobs/upsells/pricing` - Get pricing plans
- `POST /jobs/upsells` - Purchase upsell
- `GET /jobs/upsells` - Get user's upsells
- `GET /jobs/upsells/{id}` - Get specific upsell
- `POST /jobs/upsells/{id}/activate` - Activate upsell
- `POST /jobs/upsells/{id}/cancel` - Cancel upsell
- `POST /jobs/upsells/{id}/pay` - Pay for upsell
- `GET /jobs/upsells/stats` - Get upsell statistics

---

## 👥 JOB CATEGORIES
- `GET /job-categories` - Get all job categories
- `GET /job-categories/{category}` - Get specific category
- `POST /job-categories` - Create category
- `PUT /job-categories/{category}` - Update category
- `DELETE /job-categories/{category}` - Delete category

---

## 📊 ADMIN ANALYTICS
*Authentication Required*

- `GET /admin-analytics` - Get admin analytics

---

## 🛡️ AD MODERATION
*Authentication Required*

- `POST /ads/cleanup-old-ads` - Cleanup old ads
- `GET /ads/pending-approval` - Get pending ads
- `POST /ads/{adId}/approve` - Approve ad
- `POST /ads/{adId}/reject` - Reject ad
- `POST /ads/detect-harmful` - Detect harmful content
- `POST /ads/delete-harmful` - Delete harmful ads
- `PUT /ads/{adId}/poster-role` - Update poster role
- `POST /ads/{adId}/repost` - Repost ad
- `GET /ads/moderation-stats` - Get moderation statistics

---

## 📝 AUTHORS
- `GET /authors` - Get all authors
- `GET /authors/{author}` - Get specific author
- `POST /authors` - Create author
- `PUT /authors/{author}` - Update author
- `DELETE /authors/{author}` - Delete author

---

## 🎯 BOOK ADVERTS
- `GET /book-adverts` - Get all book adverts
- `GET /book-adverts/{advert}` - Get specific advert
- `POST /book-adverts` - Create advert
- `PUT /book-adverts/{advert}` - Update advert
- `DELETE /book-adverts/{advert}` - Delete advert

---

## 🏢 ADMIN SERVICE MANAGEMENT
*Authentication Required*

- `GET /admin/services` - Get all services
- `POST /admin/services` - Create service
- `PUT /admin/services/{service}` - Update service
- `DELETE /admin/services/{service}` - Delete service
- `GET /admin/services/promotions/pricing` - Get promotion pricing
- `GET /admin/services/analytics` - Get service analytics

---

## 📊 ADMIN STATISTICS
- `GET /admin/statistics` - Get admin statistics

---

## 📢 ADMIN NOTIFICATIONS
*Authentication Required*

- `GET /admin/notifications` - Get notifications
- `POST /admin/notifications` - Create notification
- `PUT /admin/notifications/{notification}` - Update notification
- `DELETE /admin/notifications/{notification}` - Delete notification

---

## 🔧 ADMIN MAINTENANCE
*Authentication Required*

- `GET /admin/maintenance` - Get maintenance status
- `POST /admin/maintenance` - Update maintenance status

---

## 📝 ADMIN POSTS
*Authentication Required*

- `GET /admin/posts` - Get all posts
- `POST /admin/posts` - Create post
- `PUT /admin/posts/{post}` - Update post
- `DELETE /admin/posts/{post}` - Delete post

---

## 🗂️ ADMIN CATEGORY POSTS
*Authentication Required*

- `GET /admin/category-posts` - Get category posts
- `POST /admin/category-posts` - Create category post
- `PUT /admin/category-posts/{post}` - Update post
- `DELETE /admin/category-posts/{post}` - Delete post

---

## 📊 POST MODERATION
*Authentication Required*

- `GET /post-moderation/pending` - Get pending posts
- `GET /post-moderation/reported` - Get reported posts
- `POST /post-moderation/{post}/approve` - Approve post
- `POST /post-moderation/{post}/reject` - Reject post
- `POST /post-moderation/{post}/report` - Report post

---

## 📊 USER ANALYTICS
*Authentication Required*

- `GET /user-analytics/posts` - Get user posts analytics
- `GET /user-analytics/stats` - Get user statistics

---

## 💰 REFERRAL SYSTEM
- `GET /referral` - Get referrals
- `POST /referral` - Create referral
- `GET /referral/stats` - Get referral statistics

---

## 🏪 STORES
- `GET /stores` - Get all stores
- `GET /stores/{store}` - Get specific store
- `POST /stores` - Create store
- `PUT /stores/{store}` - Update store
- `DELETE /stores/{store}` - Delete store

---

## 🎯 LISTING APPROVAL
*Authentication Required*

- `GET /listing-approval/pending` - Get pending listings
- `GET /listing-approval/harmful` - Get harmful listings
- `GET /listing-approval/statistics` - Get approval statistics
- `POST /listing-approval/{listingId}/approve` - Approve listing
- `POST /listing-approval/{listingId}/reject` - Reject listing
- `POST /listing-approval/{listingId}/mark-harmful` - Mark as harmful

---

## 🎯 BUYSELL PROMOTIONS
- `GET /buysell-promotions` - Get promotions
- `GET /buysell-promotions/{promotion}` - Get specific promotion
- `POST /buysell-promotions` - Create promotion
- `PUT /buysell-promotions/{promotion}` - Update promotion
- `DELETE /buysell-promotions/{promotion}` - Delete promotion

---

## 🎯 BUYSELL CATEGORIES
- `GET /buysell-categories` - Get categories
- `GET /buysell-categories/{category}` - Get specific category
- `POST /buysell-categories` - Create category
- `PUT /buysell-categories/{category}` - Update category
- `DELETE /buysell-categories/{category}` - Delete category

---

## 🎯 BUYSELL ITEMS
- `GET /buysell-items` - Get items
- `GET /buysell-items/{item}` - Get specific item
- `POST /buysell-items` - Create item
- `PUT /buysell-items/{item}` - Update item
- `DELETE /buysell-items/{item}` - Delete item

---

## 📊 SUMMARY STATISTICS

This API provides a comprehensive marketplace platform with:
- **200+ endpoints** covering all major functionality
- **Authentication & Authorization** with JWT tokens
- **Multi-category support** (Jobs, Vehicles, Books, Services, Properties, etc.)
- **Premium features** with upsells and promotions
- **Analytics & Reporting** for all modules
- **Admin panel integration** with full management capabilities
- **File upload support** for media and documents
- **Search & Filtering** capabilities
- **Notification systems** for alerts and updates

---

## 🚀 QUICK START

1. **Register/Login**: Use `/auth/register` and `/auth/login`
2. **Get Token**: Use the returned JWT token for authenticated requests
3. **Browse**: Use public endpoints like `/jobs/public` or `/services`
4. **Create**: Use protected endpoints like `POST /jobs` to create content
5. **Manage**: Use various management endpoints for your content

---

*Last Updated: March 2026*
*Total Endpoints: 200+*
*Version: v1*
