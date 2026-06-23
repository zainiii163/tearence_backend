#!/bin/bash
# Run on Hostinger after git pull — fixes posts/images not showing.
set -e

cd ~/domains/api.worldwideadverts.info/public_html

echo "==> Creating Laravel cache + storage folders..."
mkdir -p bootstrap/cache
mkdir -p storage/app/public/{community-posts/covers,community-posts/media,affiliate_images,affiliate_posts,buysell-images,buysell-thumbnails,books/covers,books/authors,vehicles,sponsored,banner,avatar,listings,services}

chmod -R 775 bootstrap/cache storage

echo "==> Pulling latest code..."
git pull origin main

echo "==> Composer install..."
composer config audit.block-insecure false
composer install --no-dev --optimize-autoloader

echo "==> Storage symlink..."
php artisan storage:link || true

echo "==> Clear and rebuild caches..."
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache

chmod -R 775 storage bootstrap/cache

echo "==> Done. Test:"
echo "curl -s https://api.worldwideadverts.info/api/v1/health"
echo "curl -I https://api.worldwideadverts.info/storage/"
