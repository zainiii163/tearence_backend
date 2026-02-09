# 🚧 Laravel Admin Maintenance Panel - Complete Documentation

## 📋 Overview

A production-ready maintenance control panel for your Laravel API that allows administrators to enable/disable maintenance mode, schedule maintenance windows, and monitor system status.

---

## ✅ Features Implemented

### 🔐 Security Features
- **Admin-only access** via custom middleware
- **JWT authentication** required for all admin endpoints
- **Role-based authorization** (admin role check)
- **Audit logging** for all maintenance actions
- **Secret bypass URL** support for admin access during maintenance

### 🎛️ Control Features
- **Enable/Disable** maintenance mode instantly
- **Custom messages** for maintenance page
- **Retry-after headers** configuration
- **Auto-refresh** settings for maintenance page
- **Maintenance logs** viewing
- **Schedule maintenance** (future feature ready)

### 🎨 User Experience
- **Beautiful custom 503 page** with modern design
- **Auto-refresh** every 60 seconds
- **Progress animation** to show activity
- **Responsive design** for all devices
- **Social links** and contact information

---

## 🛣️ API Endpoints

### Base URL
```
http://localhost:8000/api/v1
```

### Public Endpoints

#### Get Maintenance Status (Public)
```http
GET /api/v1/maintenance/status
```

**Response:**
```json
{
    "status": "success",
    "message": "Maintenance status retrieved",
    "data": {
        "is_maintenance": false,
        "maintenance_data": null,
        "timestamp": "2026-02-04T15:12:00+05:00"
    }
}
```

---

### Admin Endpoints (Requires Authentication + Admin Role)

#### 1. Get Maintenance Status (Admin)
```http
GET /api/v1/admin/maintenance/status
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Maintenance status retrieved",
    "data": {
        "is_maintenance": true,
        "maintenance_data": {
            "time": 1738668720,
            "message": "Site is under maintenance",
            "retry": 60,
            "refresh": 0
        },
        "timestamp": "2026-02-04T15:12:00+05:00"
    }
}
```

---

#### 2. Enable Maintenance Mode
```http
POST /api/v1/admin/maintenance/down
Authorization: Bearer {token}
Content-Type: application/json

{
    "message": "We're upgrading our systems. Back soon!",
    "retry": 60,
    "secret": "admin-bypass-token",
    "refresh": 0
}
```

**Parameters:**
- `message` (optional): Custom message shown on maintenance page
- `retry` (optional): Retry-After header value in seconds (default: 60)
- `secret` (optional): Secret token for admin bypass URL
- `refresh` (optional): Auto-refresh interval in seconds (default: 0)

**Response:**
```json
{
    "status": "success",
    "message": "Website is now in Maintenance Mode",
    "data": {
        "is_maintenance": true,
        "message": "We're upgrading our systems. Back soon!",
        "retry": 60,
        "secret": "admin-bypass-token"
    }
}
```

**Bypass URL (if secret provided):**
```
http://localhost:8000/admin-bypass-token
```

---

#### 3. Disable Maintenance Mode
```http
POST /api/v1/admin/maintenance/up
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Website is Live Now",
    "data": {
        "is_maintenance": false
    }
}
```

---

#### 4. Schedule Maintenance (Future Feature)
```http
POST /api/v1/admin/maintenance/schedule
Authorization: Bearer {token}
Content-Type: application/json

{
    "scheduled_at": "2026-02-05 02:00:00",
    "duration_minutes": 30,
    "message": "Scheduled maintenance window",
    "notify_users": true
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Maintenance scheduled successfully",
    "data": {
        "scheduled_at": "2026-02-05 02:00:00",
        "duration_minutes": 30,
        "message": "Scheduled maintenance window"
    }
}
```

---

#### 5. Get Maintenance Logs
```http
GET /api/v1/admin/maintenance/logs
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": "Maintenance logs retrieved",
    "data": {
        "logs": [
            "[2026-02-04 15:10:00] Maintenance mode enabled by admin@example.com",
            "[2026-02-04 15:15:00] Maintenance mode disabled by admin@example.com"
        ],
        "count": 2
    }
}
```

---

## 🔧 Installation & Setup

### Step 1: Verify Files Created

Ensure these files exist:
- ✅ `app/Http/Controllers/Admin/MaintenanceController.php`
- ✅ `app/Http/Middleware/AdminMiddleware.php`
- ✅ `resources/views/errors/503.blade.php`

### Step 2: Configure Admin Access

Edit `app/Http/Middleware/AdminMiddleware.php` and configure admin identification:

**Option 1: Role-based (Recommended)**
```php
if (isset($user->role) && $user->role === 'admin') {
    return $next($request);
}
```

**Option 2: Flag-based**
```php
if (isset($user->is_admin) && $user->is_admin == 1) {
    return $next($request);
}
```

**Option 3: Email whitelist (Temporary)**
```php
$adminEmails = [
    'admin@example.com',
    'your-email@example.com',
];

if (in_array($user->email, $adminEmails)) {
    return $next($request);
}
```

### Step 3: Add Admin Role to Database (If Using Role-based)

**Migration Example:**
```bash
php artisan make:migration add_role_to_customers_table
```

```php
public function up()
{
    Schema::table('customer', function (Blueprint $table) {
        $table->string('role')->default('user')->after('email');
    });
}
```

**Update a user to admin:**
```sql
UPDATE customer SET role = 'admin' WHERE email = 'admin@example.com';
```

### Step 4: Test the Routes

```bash
php artisan route:list --path=maintenance
```

Expected output:
```
GET|HEAD   api/v1/admin/maintenance/logs
GET|HEAD   api/v1/admin/maintenance/status
POST       api/v1/admin/maintenance/down
POST       api/v1/admin/maintenance/schedule
POST       api/v1/admin/maintenance/up
GET|HEAD   api/v1/maintenance/status
```

---

## 🧪 Testing Guide

### Test 1: Enable Maintenance Mode

```bash
curl -X POST http://localhost:8000/api/v1/admin/maintenance/down \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Upgrading database. Back in 10 minutes!",
    "retry": 600,
    "secret": "my-secret-123"
  }'
```

### Test 2: Check Public Status

```bash
curl http://localhost:8000/api/v1/maintenance/status
```

### Test 3: Access Site (Should Show 503 Page)

Visit: `http://localhost:8000`

### Test 4: Admin Bypass (Using Secret)

Visit: `http://localhost:8000/my-secret-123`

### Test 5: Disable Maintenance Mode

```bash
curl -X POST http://localhost:8000/api/v1/admin/maintenance/up \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

---

## 🎨 Customizing the 503 Page

Edit `resources/views/errors/503.blade.php`:

### Change Colors
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
/* Change to your brand colors */
background: linear-gradient(135deg, #FF6B6B 0%, #4ECDC4 100%);
```

### Change Icon
```html
<div class="maintenance-icon">🚧</div>
<!-- Change to: -->
<div class="maintenance-icon">⚙️</div>
<!-- or -->
<div class="maintenance-icon">🔧</div>
```

### Update Contact Info
```html
<p>Contact us at <a href="mailto:support@example.com">support@example.com</a></p>
<!-- Change to your email -->
<p>Contact us at <a href="mailto:support@yoursite.com">support@yoursite.com</a></p>
```

### Disable Auto-Refresh
```javascript
// Remove or comment out this script block
setTimeout(function() {
    location.reload();
}, 60000);
```

---

## 🔒 Security Best Practices

### 1. Always Use HTTPS in Production
```env
APP_URL=https://yoursite.com
```

### 2. Rotate Secret Tokens Regularly
```php
// Generate random secret
$secret = Str::random(32);
```

### 3. Log All Maintenance Actions
Already implemented in controller:
```php
Log::info('Maintenance mode enabled', [
    'admin_id' => auth('api')->id(),
    'admin_email' => auth('api')->user()->email
]);
```

### 4. Limit Admin Access by IP (Optional)
Add to `AdminMiddleware.php`:
```php
$allowedIPs = ['192.168.1.1', '10.0.0.1'];
if (!in_array($request->ip(), $allowedIPs)) {
    abort(403, 'IP not authorized');
}
```

### 5. Use Rate Limiting
Add to routes:
```php
Route::group(['middleware' => ['auth:api', 'admin', 'throttle:10,1']], function () {
    // Maintenance routes
});
```

---

## 📊 Advanced Features (Future Enhancements)

### 1. Scheduled Maintenance with Cron
Create a scheduled task:
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        // Check for scheduled maintenance
        // Enable if time matches
    })->everyMinute();
}
```

### 2. Email Notifications
```php
Mail::to($users)->send(new MaintenanceNotification($message));
```

### 3. Maintenance Status Badge
Add to your frontend navbar:
```javascript
fetch('/api/v1/maintenance/status')
    .then(res => res.json())
    .then(data => {
        if (data.data.is_maintenance) {
            showMaintenanceBadge();
        }
    });
```

### 4. Partial Maintenance (API Only)
Create separate maintenance modes for frontend vs API.

### 5. Multi-Admin Approval
Require multiple admins to approve maintenance mode.

---

## 🐛 Troubleshooting

### Issue: 403 Forbidden when accessing admin endpoints

**Solution:** Ensure your user has admin role:
```sql
UPDATE customer SET role = 'admin' WHERE email = 'your@email.com';
```

### Issue: 503 page not showing custom design

**Solution:** Clear Laravel cache:
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

### Issue: Can't disable maintenance mode

**Solution:** Use artisan command directly:
```bash
php artisan up
```

### Issue: Secret bypass not working

**Solution:** Ensure secret is passed correctly:
```bash
# Correct format
http://localhost:8000/your-secret-token

# NOT
http://localhost:8000?secret=your-secret-token
```

---

## 📝 Maintenance Checklist

Before enabling maintenance mode:

- [ ] Notify users in advance (email/notification)
- [ ] Set appropriate retry-after time
- [ ] Generate and save bypass secret
- [ ] Test bypass URL works
- [ ] Verify admin access still works
- [ ] Check logs are being written
- [ ] Plan rollback strategy
- [ ] Monitor system during maintenance
- [ ] Test site after bringing back up
- [ ] Notify users when complete

---

## 🎯 Quick Reference

### Enable Maintenance
```bash
POST /api/v1/admin/maintenance/down
```

### Disable Maintenance
```bash
POST /api/v1/admin/maintenance/up
```

### Check Status
```bash
GET /api/v1/maintenance/status
```

### View Logs
```bash
GET /api/v1/admin/maintenance/logs
```

### Artisan Commands
```bash
php artisan down --message="Custom message" --retry=60 --secret=token
php artisan up
```

---

## 📞 Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review documentation above
- Test with Postman/Insomnia
- Verify JWT token is valid
- Ensure admin role is set

---

**Version:** 1.0.0  
**Last Updated:** February 4, 2026  
**Author:** Laravel Maintenance Panel System
