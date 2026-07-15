# Hostinger Deployment Guide

## 1. Clone (first time only)

```bash
cd ~/domains/api.worldwideadverts.info
git clone https://github.com/zainiii163/tearence_backend.git laravel
cd laravel
```

## 2. Install PHP dependencies

Hostinger Composer may block packages with security advisories. Use:

```bash
composer config audit.block-insecure false
composer install --no-dev --optimize-autoloader
```

## 3. Environment file

```bash
cp .env.example .env
nano .env
```

Set at minimum:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.worldwideadverts.info
APP_KEY=base64:YOUR_KEY_HERE

DB_HOST=localhost
DB_DATABASE=u235482616_wwa
DB_USERNAME=u235482616_wwa
DB_PASSWORD=your_db_password

JWT_SECRET=your_jwt_secret
FILESYSTEM_DISK=public
```

Generate app key:

```bash
php artisan key:generate
```

## 4. Laravel setup

```bash
# Create required folders (fixes "posts/images not showing")
mkdir -p bootstrap/cache
mkdir -p storage/app/public/{community-posts/covers,community-posts/media,affiliate_images,affiliate_posts,buysell-images,vehicles,sponsored,banner,avatar}
chmod -R 775 bootstrap/cache storage

php artisan migrate --force
php artisan storage:link
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
chmod -R 775 storage bootstrap/cache
```

Or run the helper script after pull:

```bash
bash scripts/hostinger-storage-setup.sh
```

## 5. Document root (hPanel)

Set domain document root to:

```
domains/api.worldwideadverts.info/laravel/public
```

## 6. Updates (after git push)

```bash
cd ~/domains/api.worldwideadverts.info/laravel
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=ClientStockImagesSeeder --force
php artisan route:clear
php artisan route:cache
php artisan config:cache
```

### Client stock images (Stock Images & Media page)

After deploying, seed the client test photos once:

```bash
php artisan db:seed --class=ClientStockImagesSeeder --force
```

This copies the bundled images into `storage/app/public/images/client-stock/` and creates verified, active listings.

## 7. Verification API (email/phone OTP, company checks)

After deploy, add to `.env` on the server:

```env
VERIFICATION_OTP_DIGITS=6
VERIFICATION_OTP_TTL=10
VERIFICATION_VERIFIED_TTL=60
VERIFICATION_RESEND_COOLDOWN=60
COMPANIES_HOUSE_API_KEY=          # optional — UK Companies House live lookup
TWILIO_SID=                       # optional — SMS OTP
TWILIO_AUTH_TOKEN=
TWILIO_FROM_NUMBER=
```

Run the verification migration (included in `php artisan migrate --force`):

```bash
php artisan migrate --path=database/migrations/2026_07_15_000001_add_verification_and_business_fields_to_customer.php --force
php artisan config:cache
php artisan route:cache
```

Test endpoints:

```bash
curl -X POST https://api.worldwideadverts.info/api/v1/verification/email/send \
  -H "Content-Type: application/json" \
  -d '{"email":"you@example.com"}'
```

Expected: `200` with `"Verification code sent"`. `404` means route cache or code not deployed.

- Email OTP uses existing SMTP settings
- SMS logs codes to `storage/logs/laravel.log` until Twilio is configured
- Business signup: `POST /api/v1/auth/register` with `user_type: business`

## 8. Verify

```bash
curl https://api.worldwideadverts.info/api/v1/health
curl -I https://api.worldwideadverts.info/api/v1/banner-ads/my-banners
```

`401` on protected routes = working. `404` = route cache or old code.
