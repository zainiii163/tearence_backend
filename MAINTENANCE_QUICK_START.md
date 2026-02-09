# 🚀 Maintenance Panel - Quick Start Guide

## ⚡ 5-Minute Setup

### Step 1: Configure Admin Access (Choose One Method)

#### Method A: Email Whitelist (Fastest - For Testing)
Edit `app/Http/Middleware/AdminMiddleware.php` line 38:
```php
$adminEmails = [
    'your-email@example.com',  // Add your email here
];
```

#### Method B: Add Role Column (Production)
```bash
php artisan make:migration add_role_to_customer_table
```

In migration file:
```php
public function up()
{
    Schema::table('customer', function (Blueprint $table) {
        $table->string('role')->default('user')->after('email');
    });
}

public function down()
{
    Schema::table('customer', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
```

Run migration:
```bash
php artisan migrate
```

Update your user:
```sql
UPDATE customer SET role = 'admin' WHERE email = 'your@email.com';
```

---

### Step 2: Test the System

#### A. Login and Get JWT Token
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your@email.com",
    "password": "your-password"
  }'
```

Copy the `access_token` from response.

#### B. Enable Maintenance Mode
```bash
curl -X POST http://localhost:8000/api/v1/admin/maintenance/down \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Testing maintenance mode!",
    "retry": 60,
    "secret": "test123"
  }'
```

#### C. Check Maintenance Page
Visit: `http://localhost:8000`

You should see the beautiful maintenance page!

#### D. Use Bypass URL (Admin Access)
Visit: `http://localhost:8000/test123`

Site should work normally for you!

#### E. Disable Maintenance Mode
```bash
curl -X POST http://localhost:8000/api/v1/admin/maintenance/up \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📋 Available Endpoints

### Public (No Auth)
```
GET  /api/v1/maintenance/status
```

### Admin Only (Requires JWT + Admin Role)
```
GET  /api/v1/admin/maintenance/status
POST /api/v1/admin/maintenance/down
POST /api/v1/admin/maintenance/up
POST /api/v1/admin/maintenance/schedule
GET  /api/v1/admin/maintenance/logs
```

---

## 🎯 Common Use Cases

### 1. Quick Maintenance (5 minutes)
```json
POST /api/v1/admin/maintenance/down
{
    "message": "Quick update. Back in 5 minutes!",
    "retry": 300,
    "secret": "admin2026"
}
```

### 2. Extended Maintenance (30 minutes)
```json
POST /api/v1/admin/maintenance/down
{
    "message": "Database upgrade in progress. Estimated time: 30 minutes.",
    "retry": 1800,
    "refresh": 60,
    "secret": "db-upgrade-2026"
}
```

### 3. Emergency Maintenance
```json
POST /api/v1/admin/maintenance/down
{
    "message": "Emergency maintenance. We'll be back ASAP!",
    "retry": 0
}
```

---

## 🔧 Artisan Commands (Alternative)

### Enable via Command Line
```bash
php artisan down --message="Under maintenance" --retry=60 --secret=admin123
```

### Disable via Command Line
```bash
php artisan up
```

### Check Status
```bash
# If maintenance file exists, site is down
ls storage/framework/down
```

---

## 🎨 Customize 503 Page

Edit: `resources/views/errors/503.blade.php`

### Quick Customizations:

**Change message:**
```php
{{ $exception->getMessage() ?: 'Your custom default message here' }}
```

**Change colors:**
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
/* Change to your brand colors */
```

**Change icon:**
```html
<div class="maintenance-icon">🚧</div>
<!-- Try: ⚙️ 🔧 🛠️ 🔨 -->
```

**Update contact email:**
```html
<a href="mailto:support@example.com">support@example.com</a>
```

---

## 📦 Import Postman Collection

1. Open Postman
2. Click **Import**
3. Select `Maintenance_Panel_API.postman_collection.json`
4. Update `jwt_token` variable after login
5. Test all endpoints!

---

## ✅ Verification Checklist

- [ ] Admin middleware configured
- [ ] JWT token obtained
- [ ] Can enable maintenance mode
- [ ] 503 page displays correctly
- [ ] Bypass URL works
- [ ] Can disable maintenance mode
- [ ] Logs are being written
- [ ] Public status endpoint works

---

## 🐛 Quick Troubleshooting

### "403 Forbidden"
→ Your user doesn't have admin role. Check `AdminMiddleware.php` configuration.

### "401 Unauthorized"
→ JWT token expired or invalid. Login again.

### "Site not showing maintenance page"
→ Clear cache: `php artisan cache:clear && php artisan view:clear`

### "Can't disable maintenance"
→ Use artisan: `php artisan up`

---

## 📞 Need Help?

1. Check full documentation: `MAINTENANCE_PANEL_DOCUMENTATION.md`
2. Review logs: `storage/logs/laravel.log`
3. Test routes: `php artisan route:list --path=maintenance`

---

**Ready to go! 🎉**

Your maintenance panel is now fully operational and production-ready!
