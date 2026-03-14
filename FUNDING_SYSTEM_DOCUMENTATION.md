# WorldwideAdverts Funding System - Complete Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture & Database Structure](#architecture--database-structure)
3. [User Relationships & Authentication](#user-relationships--authentication)
4. [Project Lifecycle](#project-lifecycle)
5. [API Endpoints](#api-endpoints)
6. [Promotion System](#promotion-system)
7. [File Management](#file-management)
8. [Frontend Integration Guide](#frontend-integration-guide)
9. [Admin Panel Features](#admin-panel-features)
10. [Security & Validation](#security--validation)

---

## System Overview

The WorldwideAdverts Funding System is a comprehensive crowdfunding platform that allows users to create, manage, and promote fundraising projects. The system supports multiple funding models, promotion tiers, and provides complete project management capabilities.

### Key Features
- **Multi-step Project Creation**: 9-step comprehensive project setup
- **Multiple Funding Models**: Donation, Reward-based, Equity, Loan
- **Promotion System**: 4-tier visibility enhancement (Basic, Promoted, Featured, Sponsored)
- **Identity Verification**: Secure user and project verification
- **File Management**: Document upload and organization
- **Analytics & Reporting**: Real-time funding progress and statistics
- **Admin Management**: Complete oversight and approval workflow

---

## Architecture & Database Structure

### Core Tables

#### 1. Projects Table
**Purpose**: Main project information and metadata
```php
// Key Fields
- id (UUID) - Primary identifier
- user_id (UUID) - Foreign key to users table
- title, tagline, description, story, vision - Project content
- project_type - technology, social, environment, healthcare, education, arts, business
- funding_model - donation, reward, equity, loan
- funding_goal, current_funding - Financial targets
- status - draft, active, completed, cancelled
- promotion_tier - basic, promoted, featured, sponsored
- start_date, end_date - Campaign timeline
```

#### 2. Project Funding Details
**Purpose**: Detailed financial breakdown and milestones
```php
// Key Fields
- project_id (UUID) - Links to projects
- use_of_funds (JSON) - Array of funding purposes with amounts
- milestones (JSON) - Project milestones with dates and requirements
- funding_breakdown (JSON) - Statistical breakdown of funding sources
```

#### 3. Project Verification
**Purpose**: Identity verification and social proof
```php
// Key Fields
- project_id (UUID) - Links to projects
- verification_status - pending, verified, rejected
- identity_document_url - Uploaded verification document
- social_links (JSON) - Social media profiles and follower counts
- verification_data (JSON) - Additional verification information
```

#### 4. Project Rewards
**Purpose**: Reward tiers for reward-based funding
```php
// Key Fields
- project_id (UUID) - Links to projects
- title, description - Reward details
- minimum_contribution - Required donation amount
- limit_quantity - Optional quantity limit
- estimated_delivery - When backers receive the reward
- includes_shipping, shipping_cost - Shipping information
```

#### 5. Project Marketing Assets
**Purpose**: Marketing materials and pitch content
```php
// Key Fields
- project_id (UUID) - Links to projects
- pitch_video_url - YouTube/Vimeo video URL
- documents (JSON) - Additional marketing documents
```

#### 6. Project Documents
**Purpose**: File management and organization
```php
// Key Fields
- project_id (UUID) - Links to projects
- name, file_url - File information
- file_type, file_size, mime_type - File metadata
- document_type - identity, marketing, reward, other
```

#### 7. Promotion Plans
**Purpose**: Available promotion tiers and features
```php
// Key Fields
- id (string) - Plan identifier (basic, promoted, featured, sponsored)
- name, description - Plan details
- price, currency - Cost information
- features (JSON) - Array of included features
- visibility_multiplier - How much more visible projects become
- badge_color, ribbon_text - Visual indicators
```

#### 8. Project Promotions
**Purpose**: Active promotions for projects
```php
// Key Fields
- project_id (UUID) - Links to projects
- plan_id (string) - Links to promotion plans
- status - pending, active, expired, cancelled
- starts_at, ends_at - Promotion timeline
- amount_paid, payment_id - Payment information
```

---

## User Relationships & Authentication

### User Model Integration
The funding system integrates with the existing User model through UUID relationships:

```php
// User Model (existing)
- id (UUID) - Primary identifier
- name, email - Basic user information
- password - Authentication
- email_verified_at - Verification status

// Relationship to Projects
public function projects()
{
    return $this->hasMany(Project::class);
}
```

### Authentication Flow
1. **User Registration**: Standard Laravel registration
2. **Email Verification**: Required for project creation
3. **Login**: JWT-based authentication for API access
4. **Authorization**: Users can only manage their own projects

### User Permissions
- **Create Projects**: Authenticated users with verified email
- **Manage Projects**: Only project owners
- **Upload Files**: Project owners only
- **Purchase Promotions**: Project owners only
- **Admin Access**: Separate admin panel with full permissions

---

## Project Lifecycle

### 1. Project Creation (Draft Phase)
```
User Authentication → Create Draft Project → Add Details → Upload Documents → Set Rewards → Configure Marketing
```

**Steps:**
1. **Basic Information**: Title, tagline, description, story, vision
2. **Funding Details**: Goals, milestones, use of funds
3. **Verification**: Identity documents and social links
4. **Rewards**: Set up reward tiers (for reward-based funding)
5. **Marketing**: Upload pitch video and marketing materials
6. **Review**: Preview and edit all information

### 2. Project Submission
```
Draft Complete → Submit for Review → Admin Review → Approval/Rejection
```

**Process:**
- User clicks "Submit Project"
- Status changes to "draft" with submitted_at timestamp
- Admin receives notification for review
- Admin can approve (status: active) or reject (status: cancelled)

### 3. Active Campaign
```
Project Active → Receive Funding → Update Progress → Manage Backers → Complete/Cancel
```

**Features:**
- Real-time funding progress tracking
- Backer communication tools
- Milestone updates
- Promotion visibility boost

### 4. Campaign Completion
```
Goal Reached/Deadline → Status Update → Fund Disbursement → Reward Fulfillment
```

**Outcomes:**
- **Successful**: Goal reached, status: completed
- **Unsuccessful**: Goal not reached, status: completed
- **Cancelled**: By owner or admin, status: cancelled

---

## API Endpoints

### Base URL: `/api/v1/funding`

### Public Endpoints (No Authentication Required)

#### Project Discovery
```http
GET /projects                    # List all projects with filtering
GET /projects/{id}               # Get single project details
GET /projects/featured           # Get featured projects
GET /metadata/project-types      # Get available project types
GET /metadata/funding-models    # Get available funding models
GET /metadata/promotion-plans   # Get available promotion plans
```

#### Filtering Parameters (for GET /projects)
```http
?status=active                  # Filter by status
?project_type=technology        # Filter by project type
?funding_model=reward          # Filter by funding model
?promotion_tier=featured       # Filter by promotion tier
?search=keyword                # Search in title and description
?sort=created_at               # Sort field
?order=desc                    # Sort direction
?page=1                        # Pagination
```

### Authenticated Endpoints (Authentication Required)

#### Project Management
```http
POST /projects                  # Create new project
PUT /projects/{id}             # Update project
DELETE /projects/{id}           # Delete project
GET /projects/my                # Get current user's projects
POST /projects/{id}/submit     # Submit project for review
```

#### Project Details Management
```http
PUT /projects/{id}/funding-details    # Update funding details
GET /projects/{id}/funding-details     # Get funding details
PUT /projects/{id}/verification         # Update verification
GET /projects/{id}/verification          # Get verification
PUT /projects/{id}/rewards              # Update rewards
GET /projects/{id}/rewards               # Get rewards
PUT /projects/{id}/marketing-assets      # Update marketing assets
GET /projects/{id}/marketing-assets       # Get marketing assets
```

#### File Management
```http
POST /projects/{id}/documents            # Upload document
GET /projects/{id}/documents             # Get project documents
DELETE /projects/{id}/documents/{docId}  # Delete document
POST /upload                             # General file upload
DELETE /file                             # Delete uploaded file
GET /file/info                          # Get file information
```

#### Promotion System
```http
POST /upsells/purchase                   # Purchase promotion
GET /upsells/my-upsells                  # Get user's promotions
GET /upsells/project/{id}                # Get project promotions
POST /upsells/{id}/cancel                # Cancel promotion
GET /upsells/stats                       # Get promotion statistics
```

---

## Promotion System

### Promotion Tiers

#### 1. Basic (Free)
- **Price**: $0.00
- **Visibility**: 1x (standard)
- **Features**: 
  - Standard project visibility
  - Basic analytics
  - Project updates
  - Comment system
  - 7-day campaign duration

#### 2. Promoted ($29.99)
- **Price**: $29.99
- **Visibility**: 2x (enhanced)
- **Features**: 
  - Enhanced project visibility
  - Priority placement in search
  - Advanced analytics
  - Social media sharing tools
  - 14-day campaign duration
  - Email support

#### 3. Featured ($79.99)
- **Price**: $79.99
- **Visibility**: 3x (maximum)
- **Features**: 
  - Maximum project visibility
  - Featured placement on homepage
  - Premium placement in search results
  - Comprehensive analytics dashboard
  - 30-day campaign duration
  - Priority email support
  - Promotional badge
  - Weekly analytics reports

#### 4. Sponsored ($199.99)
- **Price**: $199.99
- **Visibility**: 5x (ultimate)
- **Features**: 
  - Ultimate project visibility
  - Sponsored placement on homepage
  - Top placement in all search results
  - Premium analytics with insights
  - 60-day campaign duration
  - 24/7 priority support
  - Premium promotional badge
  - Daily analytics reports
  - Marketing consultation
  - Cross-promotion opportunities

### Promotion Purchase Flow
```
Select Project → Choose Promotion Tier → Process Payment → Activate Promotion → Track Performance
```

### Promotion Management
- **Status Tracking**: pending, active, expired, cancelled
- **Automatic Expiration**: Based on end_date
- **Manual Control**: Admin can activate/expire/cancel
- **Revenue Tracking**: Payment amount and transaction ID

---

## File Management

### File Types & Validation

#### Identity Documents
- **Allowed Types**: PDF, JPEG, PNG
- **Max Size**: 5MB
- **Purpose**: User and project identity verification

#### Marketing Materials
- **Allowed Types**: PDF, JPEG, PNG, GIF, MP4, QuickTime, AVI
- **Max Size**: 20MB
- **Purpose**: Marketing documents and videos

#### Reward Information
- **Allowed Types**: PDF, JPEG, PNG, MP4, MP3, WAV
- **Max Size**: 15MB
- **Purpose**: Reward specifications and delivery information

#### Other Documents
- **Allowed Types**: PDF, Office documents, images, videos, audio
- **Max Size**: 10MB
- **Purpose**: Additional project documentation

### File Storage Structure
```
storage/
├── app/
│   ├── public/
│   │   ├── project-documents/
│   │   │   ├── identity/{project_id}/
│   │   │   ├── marketing/{project_id}/
│   │   │   ├── rewards/{project_id}/
│   │   │   └── other/{project_id}/
│   │   ├── project-covers/
│   │   └── user-profiles/
```

### File Upload Process
1. **Validation**: Check file type, size, and permissions
2. **Storage**: Save to appropriate directory with unique filename
3. **Database**: Record file metadata in project_documents table
4. **URL Generation**: Create public URL for file access
5. **Cleanup**: Optional cleanup of old files

---

## Frontend Integration Guide

### 1. Authentication Setup
```javascript
// Login and get JWT token
const login = async (email, password) => {
  const response = await fetch('/api/v1/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  localStorage.setItem('token', data.access_token);
};

// API calls with authentication
const apiCall = async (endpoint, options = {}) => {
  const token = localStorage.getItem('token');
  const response = await fetch(`/api/v1/funding${endpoint}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...options.headers
    }
  });
  return response.json();
};
```

### 2. Project Creation Flow
```javascript
// Step 1: Create basic project
const createProject = async (projectData) => {
  return await apiCall('/projects', {
    method: 'POST',
    body: JSON.stringify(projectData)
  });
};

// Step 2: Update funding details
const updateFundingDetails = async (projectId, details) => {
  return await apiCall(`/projects/${projectId}/funding-details`, {
    method: 'PUT',
    body: JSON.stringify(details)
  });
};

// Step 3: Upload documents
const uploadDocument = async (projectId, file, type) => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', type);
  formData.append('project_id', projectId);
  
  return await fetch(`/api/v1/funding/projects/${projectId}/documents`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  });
};

// Step 4: Submit project
const submitProject = async (projectId) => {
  return await apiCall(`/projects/${projectId}/submit`, {
    method: 'POST'
  });
};
```

### 3. Project Display
```javascript
// Fetch projects with filtering
const fetchProjects = async (filters = {}) => {
  const params = new URLSearchParams(filters);
  return await apiCall(`/projects?${params}`);
};

// Display project progress
const ProjectProgress = ({ project }) => {
  const progress = (project.current_funding / project.funding_goal) * 100;
  return (
    <div className="project-progress">
      <div className="progress-bar" style={{ width: `${progress}%` }} />
      <span>{progress.toFixed(1)}% funded</span>
      <span>${project.current_funding} of ${project.funding_goal}</span>
    </div>
  );
};
```

### 4. Promotion Purchase
```javascript
// Get promotion plans
const getPromotionPlans = async () => {
  return await apiCall('/metadata/promotion-plans');
};

// Purchase promotion
const purchasePromotion = async (projectId, planId) => {
  return await apiCall('/upsells/purchase', {
    method: 'POST',
    body: JSON.stringify({
      project_id: projectId,
      plan_id: planId
    })
  });
};
```

### 5. File Upload Component
```javascript
const FileUpload = ({ projectId, type, onUpload }) => {
  const handleFileSelect = async (event) => {
    const file = event.target.files[0];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);
    formData.append('project_id', projectId);
    
    try {
      const response = await fetch(`/api/v1/funding/projects/${projectId}/documents`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}` },
        body: formData
      });
      
      const result = await response.json();
      onUpload(result.data);
    } catch (error) {
      console.error('Upload failed:', error);
    }
  };
  
  return (
    <input type="file" onChange={handleFileSelect} accept=".pdf,.jpg,.png" />
  );
};
```

---

## Admin Panel Features

### 1. Project Management
- **List View**: All projects with filtering and search
- **Project Details**: Complete project information and relationships
- **Approval Workflow**: Quick approve/reject submitted projects
- **Bulk Actions**: Mass approval, deletion, and status changes

### 2. Promotion Management
- **Plan Configuration**: Edit promotion tiers and pricing
- **Active Promotions**: Monitor and manage current promotions
- **Revenue Tracking**: Promotion income and statistics
- **Promotion Control**: Activate, expire, or cancel promotions

### 3. User Management
- **Project Owners**: View user project history
- **Verification Status**: Monitor identity verification
- **Activity Tracking**: User engagement statistics

### 4. Analytics Dashboard
- **Overview Statistics**: Total projects, active campaigns, funding totals
- **Performance Metrics**: Funding progress, success rates
- **Revenue Analytics**: Promotion income and trends
- **Recent Activity**: Latest projects and promotions

### 5. File Management
- **Document Review**: View and manage uploaded files
- **Storage Monitoring**: Track file usage and storage
- **File Validation**: Ensure compliance with upload rules

---

## Security & Validation

### 1. Input Validation
- **Project Data**: Comprehensive validation rules for all fields
- **File Upload**: Type, size, and content validation
- **Financial Data**: Proper decimal handling and range validation
- **User Input**: XSS protection and sanitization

### 2. Authentication & Authorization
- **JWT Tokens**: Secure API authentication
- **Role-Based Access**: User vs admin permissions
- **Project Ownership**: Users can only manage their own projects
- **API Rate Limiting**: Prevent abuse and ensure stability

### 3. Data Protection
- **UUID Primary Keys**: Prevent enumeration attacks
- **File Security**: Secure storage with access controls
- **Database Security**: Proper foreign key constraints
- **Input Sanitization**: Prevent SQL injection and XSS

### 4. Payment Security
- **Payment Integration**: Secure payment processing (Stripe/PayPal)
- **Transaction Tracking**: Complete payment history
- **Refund Protection**: Proper refund handling
- **Financial Validation**: Accurate amount calculations

---

## Integration Checklist

### Backend Setup
- [ ] Run database migrations
- [ ] Run seeders for promotion plans
- [ ] Configure file storage permissions
- [ ] Set up payment gateway credentials
- [ ] Configure email notifications

### Frontend Integration
- [ ] Implement authentication flow
- [ ] Create project creation forms (9 steps)
- [ ] Build project listing and filtering
- [ ] Implement file upload components
- [ ] Create promotion purchase flow
- [ ] Build user dashboard
- [ ] Implement project management interface

### Testing
- [ ] Test API endpoints
- [ ] Verify file upload functionality
- [ ] Test promotion purchase flow
- [ ] Validate user permissions
- [ ] Test admin panel features

### Deployment
- [ ] Configure production database
- [ ] Set up file storage (S3 or local)
- [ ] Configure payment gateway
- [ ] Set up email service
- [ ] Deploy frontend assets
- [ ] Monitor system performance

---

## Conclusion

The WorldwideAdverts Funding System provides a complete, scalable crowdfunding platform with comprehensive features for project creation, management, and promotion. The system is designed with security, performance, and user experience in mind, providing both frontend users and administrators with powerful tools for managing fundraising campaigns.

The modular architecture allows for easy customization and extension, while the comprehensive API ensures seamless integration with any frontend framework. The admin panel provides complete oversight and management capabilities, making it suitable for both small and large-scale crowdfunding operations.
