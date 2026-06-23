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
# Create cache directory if missing (required before any artisan command)
mkdir -p bootstrap/cache
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
php artisan route:clear
php artisan route:cache
php artisan config:cache
```

## 7. Verify

```bash
curl https://api.worldwideadverts.info/api/v1/health
curl -I https://api.worldwideadverts.info/api/v1/banner-ads/my-banners
```

`401` on protected routes = working. `404` = route cache or old code.
