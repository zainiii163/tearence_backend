# Jobs & Vacancies Backend Integration Guide

## Overview

This document provides a comprehensive guide for integrating the Jobs & Vacancies backend system with frontend applications. The backend is built using Laravel 11 with RESTful API endpoints, comprehensive authentication, and real-time analytics.

## Base URLs

- **Production**: `https://api.worldwideadverts.com/api/v1`
- **Development**: `https://dev-api.worldwideadverts.com/api/v1`

## Authentication

### JWT Authentication
All protected endpoints require JWT authentication. Include the token in the Authorization header:

```http
Authorization: Bearer <jwt_token>
```

### Authentication Endpoints

#### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com"
        }
    }
}
```

#### Register
```http
POST /api/v1/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

---

## API Endpoints

### 📋 Jobs Management

#### Browse Jobs (Public)
```http
GET /api/v1/jobs/public?search=developer&location=New York&category=technology-it&work_type=Full-time&experience_level=senior&remote_only=1&sort_by=most_recent&per_page=20
```

**Query Parameters:**
- `search` - Keywords search in title, description, company name
- `location` - Filter by city or country
- `category` - Filter by category slug
- `work_type` - Filter by work type (Full-time, Part-time, Contract, Freelance, Internship, Temporary)
- `experience_level` - Filter by experience level (entry, mid, senior, executive)
- `remote_only` - Boolean, filter remote jobs only
- `sort_by` - Sort options: most_recent, salary_high_low, salary_low_high, most_viewed
- `per_page` - Number of results per page (max 100)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Senior Full Stack Developer",
            "slug": "senior-full-stack-developer",
            "company_name": "TechCorp Solutions",
            "logo_url": "https://example.com/logo.jpg",
            "city": "San Francisco",
            "country": "United States",
            "work_type": "Full-time",
            "salary_range": "90000-120000",
            "currency": "USD",
            "experience_level": "senior",
            "education_level": "bachelor",
            "remote_available": true,
            "description": "We are looking for an experienced Full Stack Developer...",
            "skills_needed": "React, Node.js, PostgreSQL, AWS",
            "promotion_type": "featured",
            "views": 245,
            "applications_count": 12,
            "saves_count": 8,
            "posted_at": "2024-01-15T10:30:00Z",
            "expires_at": "2024-02-15T23:59:59Z",
            "category": {
                "id": 1,
                "name": "Technology & IT",
                "slug": "technology-it",
                "icon": "laptop-code",
                "color": "#3B82F6"
            },
            "user": {
                "id": 5,
                "name": "TechCorp HR"
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 20,
        "total": 156,
        "total_pages": 8,
        "has_next": true,
        "has_prev": false
    }
}
```

#### Get Single Job (Public)
```http
GET /api/v1/jobs/public/{slug}
```

**Response:** Same structure as individual job item in browse response

#### Get Featured Jobs (Public)
```http
GET /api/v1/jobs/public/featured?limit=10
```

#### Get Job Categories (Public)
```http
GET /api/v1/jobs/public/categories
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": "technology-it",
            "name": "Technology & IT",
            "description": "Software development, IT support, cybersecurity...",
            "icon": "laptop-code",
            "job_count": 45,
            "trending": true
        }
    ]
}
```

#### Get Jobs by Category (Public)
```http
GET /api/v1/jobs/public/genre/{category_slug}
```

#### Get Platform Statistics (Public)
```http
GET /api/v1/jobs/public/stats
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_jobs": 1245,
        "active_companies": 523,
        "total_applications": 8967,
        "success_rate": 98,
        "popular_categories": [
            {
                "category": "Technology & IT",
                "count": 234,
                "growth": 12.5
            }
        ],
        "top_locations": [
            {
                "country": "United States",
                "city": "New York",
                "job_count": 156
            }
        ],
        "average_salary": {
            "USD": 75000,
            "EUR": 65000,
            "GBP": 55000
        }
    }
}
```

---

### 🔐 Protected Job Endpoints (Authentication Required)

#### Create Job Posting
```http
POST /api/v1/jobs
Authorization: Bearer <token>
Content-Type: application/json

{
    "title": "Senior Full Stack Developer",
    "description": "We are looking for an experienced Full Stack Developer...",
    "responsibilities": "Develop and maintain web applications...",
    "requirements": "5+ years of experience in web development...",
    "benefits": "Competitive salary, health insurance...",
    "skills_needed": "React, Node.js, PostgreSQL, AWS",
    "company_name": "TechCorp Solutions",
    "company_description": "TechCorp Solutions is a leading technology company...",
    "company_size": "51-200",
    "company_industry": "Technology",
    "company_founded": "2015",
    "company_website": "https://techcorp.example.com",
    "country": "United States",
    "city": "San Francisco",
    "state": "California",
    "latitude": 37.7749,
    "longitude": -122.4194,
    "work_type": "Full-time",
    "salary_range": "90000-120000",
    "currency": "USD",
    "experience_level": "senior",
    "education_level": "bachelor",
    "remote_available": true,
    "application_method": "email",
    "application_email": "careers@techcorp.example.com",
    "verified_employer": true,
    "terms_accepted": "accepted",
    "accurate_info": "accepted"
}
```

#### Update Job Posting
```http
PUT /api/v1/jobs/{id}
Authorization: Bearer <token>
Content-Type: application/json
```

#### Delete Job Posting
```http
DELETE /api/v1/jobs/{id}
Authorization: Bearer <token>
```

#### Get My Jobs
```http
GET /api/v1/jobs/my-jobs?status=active&per_page=20
Authorization: Bearer <token>
```

#### Save/Unsave Job
```http
POST /api/v1/jobs/{id}/save
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "message": "Job saved successfully",
    "saved": true
}
```

#### Get Saved Jobs
```http
GET /api/v1/jobs/saved?per_page=20
Authorization: Bearer <token>
```

---

### 📝 Job Applications

#### Apply for Job
```http
POST /api/v1/jobs/{jobId}/apply
Authorization: Bearer <token>
Content-Type: application/json

{
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+1-555-0123-4567",
    "location": "New York, USA",
    "cover_letter": "I am excited to apply for this position...",
    "cv_file": "base64_encoded_cv_file_or_url",
    "portfolio_links": [
        "https://portfolio.example.com",
        "https://github.com/username"
    ],
    "expected_salary": "80000-100000",
    "available_start_date": "2024-02-01",
    "additional_notes": "Available for immediate interview"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Application submitted successfully",
    "data": {
        "application_id": 123,
        "job_id": 456,
        "status": "submitted",
        "submitted_at": "2024-01-20T14:30:00Z"
    }
}
```

#### Get Job Applications (Employer)
```http
GET /api/v1/jobs/applications?job_id=456&status=submitted&per_page=20
Authorization: Bearer <token>
```

#### Get Single Application
```http
GET /api/v1/jobs/applications/{applicationId}
Authorization: Bearer <token>
```

#### Update Application Status (Employer)
```http
PUT /api/v1/jobs/applications/{applicationId}/status
Authorization: Bearer <token>
Content-Type: application/json

{
    "status": "shortlisted",
    "employer_notes": "Strong candidate, schedule interview",
    "next_steps": "Technical interview followed by HR interview",
    "interview_date": "2024-01-25T15:00:00Z",
    "interview_type": "video",
    "interview_notes": "Focus on React and Node.js experience"
}
```

#### Get Application Statistics (Employer)
```http
GET /api/v1/jobs/applications/stats
Authorization: Bearer <token>
```

#### Get My Applications (Job Seeker)
```http
GET /api/v1/jobs/my-applications?status=submitted&per_page=20
Authorization: Bearer <token>
```

#### Withdraw Application (Job Seeker)
```http
POST /api/v1/jobs/applications/{applicationId}/withdraw
Authorization: Bearer <token>
```

---

### 👥 Job Seeker Profiles

#### Create Job Seeker Profile
```http
POST /api/v1/jobs/seekers
Authorization: Bearer <token>
Content-Type: application/json

{
    "full_name": "John Doe",
    "profession": "Full Stack Developer",
    "bio": "Experienced developer with 5+ years in web technologies...",
    "profile_photo_url": "https://example.com/photo.jpg",
    "country": "United States",
    "city": "New York",
    "state": "New York",
    "latitude": 40.7128,
    "longitude": -74.0060,
    "years_of_experience": "5-10",
    "key_skills": "React, Node.js, PostgreSQL, AWS, Docker",
    "education_level": "bachelor",
    "education_details": "Bachelor of Science in Computer Science...",
    "experience_summary": "5 years of full-stack development experience...",
    "desired_role": "Senior Full Stack Developer",
    "salary_expectation": "90000-120000",
    "work_type_preference": "Full-time",
    "remote_availability": true,
    "preferred_locations": [
        "New York, USA",
        "San Francisco, USA",
        "Remote"
    ],
    "preferred_industries": [
        "Technology",
        "Finance",
        "Healthcare"
    ],
    "portfolio_link": "https://portfolio.example.com",
    "linkedin_link": "https://linkedin.com/in/johndoe",
    "github_link": "https://github.com/johndoe",
    "cv_file_url": "https://example.com/cv.pdf",
    "additional_links": [
        {
            "label": "Personal Website",
            "url": "https://johndoe.dev"
        }
    ],
    "terms_accepted": "accepted",
    "accurate_info": "accepted"
}
```

#### Update Job Seeker Profile
```http
PUT /api/v1/jobs/seekers/{id}
Authorization: Bearer <token>
```

#### Delete Job Seeker Profile
```http
DELETE /api/v1/jobs/seekers/{id}
Authorization: Bearer <token>
```

#### Get My Job Seeker Profile
```http
GET /api/v1/jobs/seekers/my-profile
Authorization: Bearer <token>
```

#### Browse Job Seekers (Public)
```http
GET /api/v1/jobs/public/seekers?search=developer&profession=Full%20Stack%20Developer&location=New%20York&experience_level=senior&remote_available=1&per_page=20
```

#### Get Single Job Seeker (Public)
```http
GET /api/v1/jobs/public/seekers/{seekerId}
```

#### Get Job Seeker Statistics (Public)
```http
GET /api/v1/jobs/public/seekers/stats
```

---

### 🔔 Job Alerts

#### Create Job Alert
```http
POST /api/v1/jobs/alerts
Authorization: Bearer <token>
Content-Type: application/json

{
    "name": "Senior Developer Jobs in NYC",
    "keywords": "senior, full stack, react, node.js",
    "location": "New York",
    "category": "technology-it",
    "work_type": "Full-time",
    "salary_range": "90000-120000",
    "experience_level": "senior",
    "education_level": "bachelor",
    "remote_only": true,
    "frequency": "daily"
}
```

#### Get My Job Alerts
```http
GET /api/v1/jobs/alerts?per_page=20
Authorization: Bearer <token>
```

#### Get Single Job Alert
```http
GET /api/v1/jobs/alerts/{id}
Authorization: Bearer <token>
```

#### Update Job Alert
```http
PUT /api/v1/jobs/alerts/{id}
Authorization: Bearer <token>
```

#### Delete Job Alert
```http
DELETE /api/v1/jobs/alerts/{id}
Authorization: Bearer <token>
```

#### Test Job Alert
```http
POST /api/v1/jobs/alerts/{id}/test
Authorization: Bearer <token>
```

#### Get Alert Statistics
```http
GET /api/v1/jobs/alerts/stats
Authorization: Bearer <token>
```

#### Get Matching Jobs for Alert
```http
GET /api/v1/jobs/alerts/{id}/matching-jobs?limit=20
Authorization: Bearer <token>
```

---

### 💰 Premium Upsells

#### Get Pricing Plans
```http
GET /api/v1/jobs/upsells/pricing
Authorization: Bearer <token>
```

**Response:**
```json
{
    "success": true,
    "data": {
        "basic": {
            "id": "basic",
            "name": "Basic Listing",
            "price": 29.99,
            "currency": "USD",
            "period": "month",
            "features": [
                "30-day posting duration",
                "Standard visibility",
                "Basic applicant tracking"
            ],
            "recommended": false
        },
        "promoted": {
            "id": "promoted",
            "name": "Promoted Listing",
            "price": 49.99,
            "currency": "USD",
            "period": "month",
            "features": [
                "30-day posting duration",
                "2x visibility boost",
                "Highlighted in search results"
            ],
            "recommended": true
        }
    }
}
```

#### Purchase Promotion
```http
POST /api/v1/jobs/upsells
Authorization: Bearer <token>
Content-Type: application/json

{
    "upsellable_type": "job_listing",
    "upsellable_id": 123,
    "upsell_type": "featured",
    "price": 89.99,
    "currency": "USD",
    "duration_months": 1
}
```

#### Get My Upsells
```http
GET /api/v1/jobs/upsells?status=active&per_page=20
Authorization: Bearer <token>
```

#### Get Single Upsell
```http
GET /api/v1/jobs/upsells/{id}
Authorization: Bearer <token>
```

#### Process Payment
```http
POST /api/v1/jobs/upsells/{id}/pay
Authorization: Bearer <token>
Content-Type: application/json

{
    "payment_method": "stripe",
    "transaction_id": "txn_1234567890"
}
```

#### Activate Promotion
```http
POST /api/v1/jobs/upsells/{id}/activate
Authorization: Bearer <token>
```

#### Cancel Promotion
```http
POST /api/v1/jobs/upsells/{id}/cancel
Authorization: Bearer <token>
```

#### Get Upsell Statistics
```http
GET /api/v1/jobs/upsells/stats
Authorization: Bearer <token>
```

---

## 📊 Response Formats

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
        "message": "The given data was invalid.",
        "details": {
            "title": ["The title field is required."],
            "email": ["The email must be a valid email address."]
        }
    }
}
```

### Common Error Codes
- `AUTHORIZATION_FAILED` - User not authenticated or unauthorized
- `RESOURCE_NOT_FOUND` - Resource not found
- `VALIDATION_ERROR` - Input validation failed
- `DUPLICATE_APPLICATION` - User already applied for this job
- `DUPLICATE_UPSELL` - Active upsell already exists
- `PAYMENT_REQUIRED` - Payment required for this action

---

## 🔧 Rate Limiting

- **Public endpoints**: 100 requests per minute per IP
- **Authenticated endpoints**: 1000 requests per minute per user
- **File uploads**: 10 requests per minute per user

---

## 📁 File Uploads

### Supported File Formats
- **CV Files**: PDF, DOC, DOCX (max 5MB)
- **Profile Photos**: JPG, PNG, GIF (max 2MB)
- **Company Logos**: JPG, PNG, GIF (max 2MB)

### Upload Methods
1. **Base64 Encoding**: Include file as base64 string in JSON payload
2. **Direct URL**: Provide file URL that will be downloaded and stored

---

## 🌍 Geographic Data

### Location Handling
- Jobs and seekers support latitude/longitude coordinates
- Automatic geocoding for city/country combinations
- Geographic filtering and distance calculations
- Location-based search with radius support

### Geographic Analytics
- View tracking by country and city
- Popular locations analysis
- Remote job statistics by region

---

## 🔄 Real-time Features

### Webhook Support
Configure webhooks to receive real-time notifications:

```json
{
    "event": "job.application.submitted",
    "data": {
        "job_id": 123,
        "application_id": 456,
        "applicant": {
            "name": "John Doe",
            "email": "john@example.com"
        }
    },
    "timestamp": "2024-01-20T14:30:00Z"
}
```

### Supported Events
- `job.application.submitted`
- `job.application.status_updated`
- `job.viewed`
- `job.saved`
- `job.expired`
- `upsell.purchased`
- `upsell.activated`

---

## 📱 Frontend Integration Examples

### JavaScript/React Example

```javascript
// API Client Setup
const API_BASE_URL = 'https://api.worldwideadverts.com/api/v1';

class JobsAPI {
    constructor(token) {
        this.token = token;
    }

    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.token}`,
                ...options.headers
            },
            ...options
        };

        const response = await fetch(url, config);
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error?.message || 'Request failed');
        }

        return data;
    }

    // Browse jobs
    async browseJobs(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/jobs/public?${params}`);
    }

    // Get single job
    async getJob(slug) {
        return this.request(`/jobs/public/${slug}`);
    }

    // Apply for job
    async applyForJob(jobId, applicationData) {
        return this.request(`/jobs/${jobId}/apply`, {
            method: 'POST',
            body: JSON.stringify(applicationData)
        });
    }

    // Save job
    async saveJob(jobId) {
        return this.request(`/jobs/${jobId}/save`, {
            method: 'POST'
        });
    }

    // Get my jobs
    async getMyJobs(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/jobs/my-jobs?${params}`);
    }

    // Create job posting
    async createJob(jobData) {
        return this.request('/jobs', {
            method: 'POST',
            body: JSON.stringify(jobData)
        });
    }

    // Get job categories
    async getCategories() {
        return this.request('/jobs/public/categories');
    }

    // Get pricing plans
    async getPricingPlans() {
        return this.request('/jobs/upsells/pricing');
    }

    // Purchase promotion
    async purchasePromotion(upsellData) {
        return this.request('/jobs/upsells', {
            method: 'POST',
            body: JSON.stringify(upsellData)
        });
    }
}

// Usage Example
const jobsAPI = new JobsAPI(localStorage.getItem('jwt_token'));

// Browse jobs with filters
const jobs = await jobsAPI.browseJobs({
    search: 'react developer',
    location: 'New York',
    work_type: 'Full-time',
    remote_only: true,
    per_page: 20
});

// Apply for job
const application = await jobsAPI.applyForJob(123, {
    full_name: 'John Doe',
    email: 'john@example.com',
    cover_letter: 'I am excited to apply...'
});
```

### Vue.js Example

```javascript
// Jobs Service
import axios from 'axios';

const API_BASE_URL = 'https://api.worldwideadverts.com/api/v1';

class JobsService {
    constructor() {
        this.client = axios.create({
            baseURL: API_BASE_URL,
            headers: {
                'Content-Type': 'application/json'
            }
        });

        // Add auth token to requests
        this.client.interceptors.request.use(config => {
            const token = localStorage.getItem('jwt_token');
            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            return config;
        });
    }

    async browseJobs(params = {}) {
        const response = await this.client.get('/jobs/public', { params });
        return response.data;
    }

    async getJob(slug) {
        const response = await this.client.get(`/jobs/public/${slug}`);
        return response.data;
    }

    async applyForJob(jobId, data) {
        const response = await this.client.post(`/jobs/${jobId}/apply`, data);
        return response.data;
    }

    async saveJob(jobId) {
        const response = await this.client.post(`/jobs/${jobId}/save`);
        return response.data;
    }
}

export default new JobsService();
```

---

## 🧪 Testing

### Testing Environment
Use the development API for testing:
- **Base URL**: `https://dev-api.worldwideadverts.com/api/v1`

### Test Data
Run seeders to populate test data:
```bash
php artisan db:seed --class=JobCategorySeeder
php artisan db:seed --class=JobPricingPlanSeeder
php artisan db:seed --class=JobSeeder
```

### Testing Authentication
Use test credentials or create new accounts via the register endpoint.

---

## 🚀 Deployment Considerations

### Environment Variables
```env
API_BASE_URL=https://api.worldwideadverts.com/api/v1
JWT_SECRET=your-jwt-secret-key
FILE_UPLOAD_MAX_SIZE=5120
RATE_LIMIT_PUBLIC=100
RATE_LIMIT_AUTH=1000
```

### CORS Configuration
Ensure your frontend domain is whitelisted for CORS requests.

### SSL/TLS
All API endpoints require HTTPS in production.

---

## 📞 Support

For technical support or integration assistance:
- **Email**: api-support@worldwideadverts.com
- **Documentation**: https://docs.worldwideadverts.com
- **Status Page**: https://status.worldwideadverts.com

---

## 📋 Checklist for Frontend Integration

- [ ] Implement JWT authentication flow
- [ ] Handle API errors gracefully
- [ ] Implement rate limiting awareness
- [ ] Add file upload functionality
- [ ] Create responsive job listing pages
- [ ] Implement advanced search and filtering
- [ ] Add job application forms
- [ ] Create user dashboards
- [ ] Implement job alerts system
- [ ] Add premium promotion purchasing
- [ ] Handle real-time updates (webhooks)
- [ ] Add analytics and reporting
- [ ] Test all endpoints thoroughly
- [ ] Implement proper error handling
- [ ] Add loading states and pagination
- [ ] Ensure mobile responsiveness

---

**Last Updated**: January 2024
**Version**: 1.0.0
**API Version**: v1
