# Admin Post Management System

This system provides comprehensive admin control over all category-related posts, allowing administrators to create, manage, and moderate user submissions.

## Features

### 1. Category Post Management
- **View all posts** in any category with filtering options
- **Create admin posts** directly in any category
- **Edit any post** (both user and admin posts)
- **Delete posts** with proper permissions
- **Bulk operations** for approving/rejecting multiple posts

### 2. Post Moderation
- **Moderation dashboard** with statistics and recent activity
- **Quick approve/reject** actions for pending posts
- **Mark harmful content** and restore functionality
- **User post history** tracking
- **Bulk actions** for efficient moderation

### 3. Notification System
- **Real-time notifications** for new post submissions
- **Unread count tracking** for admin dashboard
- **Notification management** (mark read, delete)
- **Custom notifications** for system alerts
- **Automatic cleanup** of old notifications

## API Endpoints

### Category Post Management
```
GET    /api/v1/admin/category-posts/category/{categoryId}
POST   /api/v1/admin/category-posts/create
PUT    /api/v1/admin/category-posts/{postId}
DELETE /api/v1/admin/category-posts/{postId}
GET    /api/v1/admin/category-posts/pending
POST   /api/v1/admin/category-posts/bulk-approve
POST   /api/v1/admin/category-posts/bulk-reject
GET    /api/v1/admin/category-posts/stats
```

### Post Moderation
```
GET    /api/v1/admin/moderation/dashboard
GET    /api/v1/admin/moderation/posts-needing-attention
POST   /api/v1/admin/moderation/{postId}/quick-approve
POST   /api/v1/admin/moderation/{postId}/quick-reject
POST   /api/v1/admin/moderation/{postId}/mark-harmful
POST   /api/v1/admin/moderation/{postId}/restore
GET    /api/v1/admin/moderation/user/{userId}/history
POST   /api/v1/admin/moderation/bulk-action
GET    /api/v1/admin/moderation/activity-log
```

### Notifications
```
GET    /api/v1/admin/notifications
GET    /api/v1/admin/notifications/unread-count
POST   /api/v1/admin/notifications/{notificationId}/mark-read
POST   /api/v1/admin/notifications/mark-all-read
DELETE /api/v1/admin/notifications/{notificationId}
POST   /api/v1/admin/notifications/create
GET    /api/v1/admin/notifications/stats
POST   /api/v1/admin/notifications/cleanup
```

## Permission System

Admins need the following permissions to access different features:

- `can_manage_listings` - Required for all post management operations
- `is_super_admin` - Required for creating custom notifications and cleanup operations
- `can_view_analytics` - Required for viewing statistics and activity logs

## Post Types

### Regular Posts
- Created by users
- Require admin approval before appearing publicly
- Can be promoted/sponsored by admins

### Admin Posts
- Created directly by administrators
- Auto-approved and published immediately
- Can be marked as sponsored, promoted, or featured

### Post Status Flow
```
Created → Pending → Approved → Published
                ↓
             Rejected
                ↓
             (Can be resubmitted)
```

## Notification Types

- `new_post` - New user post submitted
- `post_pending` - Post pending approval
- `harmful_content` - Content flagged as harmful
- `bulk_pending` - Multiple posts pending (bulk notification)
- `system_alert` - Custom admin notifications

## Usage Examples

### Creating an Admin Post
```json
POST /api/v1/admin/category-posts/create
{
    "category_id": 1,
    "location_id": 1,
    "title": "Featured Product",
    "description": "This is a featured admin post",
    "price": 99.99,
    "post_type": "sponsored",
    "sponsored_duration": 30,
    "images": ["image1.jpg", "image2.jpg"]
}
```

### Bulk Approving Posts
```json
POST /api/v1/admin/category-posts/bulk-approve
{
    "post_ids": [1, 2, 3, 4, 5],
    "post_type": "regular"
}
```

### Quick Approving with Special Features
```json
POST /api/v1/admin/moderation/{postId}/quick-approve
{
    "make_special": "sponsored"
}
```

### Creating Custom Notification
```json
POST /api/v1/admin/notifications/create
{
    "message": "System maintenance scheduled",
    "type": "system_alert",
    "data": {
        "maintenance_time": "2024-01-25 02:00:00"
    }
}
```

## Database Schema

### Admin Notifications Table
- `id` - Primary key
- `user_id` - Admin user ID
- `type` - Notification type
- `message` - Notification message
- `data` - JSON data (additional context)
- `read_at` - Timestamp when marked as read
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Security Features

- **Permission-based access control** - Only authorized admins can manage posts
- **Audit trail** - All approval/rejection actions are tracked
- **Content moderation** - Harmful content detection and management
- **Bulk operations protection** - Validations prevent accidental mass actions

## Best Practices

1. **Regular moderation** - Check pending posts frequently
2. **Use bulk operations** - For efficient management of multiple posts
3. **Monitor notifications** - Stay updated on new submissions
4. **Review user history** - Check user patterns when moderating
5. **Document decisions** - Use rejection reasons consistently

## Integration with Existing System

This admin management system integrates seamlessly with:
- Existing category structure
- User permission system
- Listing approval workflow
- Upsell and priority system
- KYC and posting limits

The system extends the existing functionality without breaking changes, ensuring smooth operation of current features while adding powerful admin capabilities.
