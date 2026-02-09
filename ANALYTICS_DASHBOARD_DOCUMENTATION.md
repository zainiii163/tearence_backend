# Analytics Dashboard Implementation

## Overview

This implementation provides comprehensive analytics dashboards for both users and administrators with role-based access control. The system tracks user activity, listing performance, revenue metrics, and system health.

## Features Implemented

### ✅ User Analytics Dashboard
- **Personal Activity Tracking**: Login/logout, profile views, listing interactions
- **Listing Performance**: Views, clicks, favorites, contacts for user's listings
- **Profile Analytics**: Profile views, message activity, login frequency
- **Data Export**: Export personal analytics in JSON/CSV format

### ✅ Admin Analytics Dashboard  
- **System Overview**: Total users, active users, listings, revenue metrics
- **User Analytics**: Registration trends, activity breakdown, daily user activity
- **Revenue Analytics**: Daily revenue tracking, revenue by source, financial metrics
- **Listing Analytics**: Total views/clicks, top performing listings, category performance
- **KYC Analytics**: Submission trends, approval/rejection rates, status breakdown

### ✅ Role-Based Permission System
- **Dashboard Sections**: System Overview, User Analytics, Revenue Analytics, Listing Analytics, KYC Analytics
- **Permission Levels**: View access, Export access, Filter access
- **Role Hierarchy**:
  - **Administrators**: Full access to all sections with export capabilities
  - **Moderators**: Limited access to user, listing, and KYC analytics
  - **Editors**: Access to listing analytics only
  - **Support**: Read-only access to user analytics and system overview

## Database Tables

### Core Analytics Tables
1. **`user_analytics`** - Tracks individual user activity events
2. **`system_analytics`** - Stores system-wide metrics and KPIs
3. **`dashboard_permissions`** - Manages role-based access control
4. **`analytics_reports`** - Caches and manages generated reports
5. **`listing_analytics`** - Tracks listing interactions (existing)

### Key Fields

#### User Analytics
- `user_id` - User identifier
- `event_type` - Activity type (login, profile_view, listing_created, etc.)
- `event_date` - Timestamp of the event
- `ip_address`, `user_agent`, `source` - Request metadata
- `metadata` - JSON field for additional event data

#### System Analytics
- `metric_type` - Type of metric (total_users, daily_revenue, etc.)
- `metric_value` - Integer value for counts
- `metric_value_decimal` - Decimal value for financial metrics
- `metric_date` - Date for the metric
- `breakdown` - JSON field for detailed breakdowns

#### Dashboard Permissions
- `group_id` / `user_id` - Permission target (group or individual user)
- `dashboard_section` - Section identifier
- `can_view` / `can_export` - Boolean permission flags
- `filters` - JSON array of available filters

## API Endpoints

### User Analytics (`/api/v1/user-analytics`)
- `GET /dashboard` - Get user's personal analytics dashboard
- `GET /listing-analytics` - Get user's listing performance data
- `GET /profile-analytics` - Get user's profile interaction data
- `GET /export` - Export user analytics data

### Admin Analytics (`/api/v1/admin-analytics`)
- `GET /dashboard` - Get admin analytics dashboard (with permission checks)
- `GET /user-analytics` - Get detailed user analytics with filtering
- `GET /listing-analytics` - Get detailed listing analytics with filtering
- `GET /export` - Export analytics data (permission required)
- `POST /permissions` - Manage dashboard permissions

## Permission System

### Dashboard Sections
1. **`system_overview`** - System-wide metrics and health
2. **`user_analytics`** - User activity and registration data
3. **`revenue_analytics`** - Financial metrics and revenue tracking
4. **`listing_analytics`** - Listing performance and interactions
5. **`kyc_analytics`** - KYC verification metrics

### Permission Checks
```php
// Check if user can view a section
DashboardPermission::userCanView($userId, 'system_overview');

// Check if user can export from a section
DashboardPermission::userCanExport($userId, 'user_analytics');

// Get available filters for a user
DashboardPermission::getUserFilters($userId, 'listing_analytics');

// Get all accessible sections for a user
DashboardPermission::getUserAccessibleSections($userId);
```

### Default Role Permissions

#### Administrators
- **All Sections**: View ✓, Export ✓
- **Filters**: All filters available

#### Moderators  
- **User Analytics**: View ✓, Export ✓
- **Listing Analytics**: View ✓, Export ✗
- **KYC Analytics**: View ✓, Export ✗
- **Filters**: Limited filters (event_type, date_range, kyc_status)

#### Editors
- **Listing Analytics**: View ✓, Export ✓
- **Filters**: Listing-focused filters (event_type, date_range, category)

#### Support
- **User Analytics**: View ✓, Export ✗
- **System Overview**: View ✓, Export ✗
- **Filters**: Basic filters (date_range only)

## Usage Examples

### Recording User Activity
```php
// Record a login event
UserAnalyticsController::recordActivity($userId, 'login', [
    'login_method' => 'email',
    'device_type' => 'mobile'
]);

// Record a listing view
UserAnalyticsController::recordActivity($userId, 'listing_created', [
    'listing_id' => $listingId,
    'category_id' => $categoryId
], 'web');
```

### Recording System Metrics
```php
// Record daily revenue
SystemAnalytics::recordMetric('daily_revenue', 1500.75, null, [
    'source' => 'listing_upsells',
    'currency' => 'USD'
]);

// Record new user registrations
SystemAnalytics::recordMetric('new_registrations', 25);
```

### Managing Permissions
```php
// Set group permissions
DashboardPermission::setGroupPermissions(
    $groupId, 
    'revenue_analytics', 
    true,  // can_view
    false, // can_export
    ['date_range', 'revenue_source'] // filters
);

// Set user-specific permissions
DashboardPermission::setUserPermissions(
    $userId,
    'user_analytics',
    true,
    true,
    ['event_type', 'date_range', 'user_group']
);
```

## Frontend Integration

### User Dashboard
```javascript
// Get user analytics
const response = await fetch('/api/v1/user-analytics/dashboard?days=30', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});
const data = await response.json();

// Data structure returned:
{
    "success": true,
    "data": {
        "activity_summary": {
            "login": 15,
            "listing_created": 3,
            "profile_view": 8
        },
        "daily_activity": {
            "2024-01-01": 5,
            "2024-01-02": 3
        },
        "listing_performance": {
            "total_views": 125,
            "total_clicks": 18,
            "total_favorites": 7
        },
        "recent_activity": [...]
    }
}
```

### Admin Dashboard
```javascript
// Get admin analytics with permission checks
const response = await fetch('/api/v1/admin-analytics/dashboard?days=30', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});

// Data structure includes permissions:
{
    "success": true,
    "data": {
        "system_overview": {...}, // Only if user has permission
        "user_analytics": {...},  // Only if user has permission
        "revenue_analytics": {...} // Only if user has permission
    },
    "permissions": {
        "accessible_sections": ["system_overview", "user_analytics"],
        "can_export": {
            "system_overview": true,
            "user_analytics": false
        }
    }
}
```

## Security Considerations

1. **Permission Checks**: All admin endpoints verify user permissions before returning data
2. **Data Filtering**: Users can only access their own analytics data
3. **Export Restrictions**: Export capabilities are controlled by permissions
4. **Filter Limits**: Available filters are restricted by user permissions
5. **Rate Limiting**: Consider implementing rate limiting for analytics endpoints

## Performance Optimization

1. **Database Indexing**: All analytics tables have appropriate indexes
2. **Data Aggregation**: System metrics are pre-aggregated for faster queries
3. **Caching**: Consider caching frequently accessed dashboard data
4. **Date Range Limits**: Implement reasonable limits on date range queries

## Migration and Setup

1. Run the migration: `php artisan migrate`
2. Run the seeders: `php artisan db:seed --class=DashboardPermissionSeeder`
3. The system will automatically start tracking events when controllers are called

## Extending the System

### Adding New Dashboard Sections
1. Add the section identifier to the `dashboard_sections` array
2. Update permission seeders with default permissions
3. Add corresponding methods to controllers
4. Update frontend to handle the new section

### Adding New Event Types
1. Add event type to the `user_analytics` table enum
2. Update tracking calls throughout the application
3. Add corresponding analytics methods to models

### Custom Reports
The `AnalyticsReport` model supports custom report types. Extend the `generateReportData()` method to add new report formats.

## Monitoring and Maintenance

1. **Data Retention**: Implement data cleanup policies for old analytics
2. **Performance Monitoring**: Monitor query performance on large analytics tables
3. **Permission Audits**: Regularly review and update role permissions
4. **Data Accuracy**: Implement validation checks for analytics data

This comprehensive analytics system provides powerful insights while maintaining security through role-based access control and ensuring users only see data they're authorized to view.
