#!/bin/bash

# Production Deployment Script for WWA API
# This script prepares and deploys the Laravel API to production

echo "🚀 Starting WWA API Production Deployment..."

# 1. Install dependencies
echo "📦 Installing production dependencies..."
composer install --no-dev --optimize-autoloader
npm ci --production

# 2. Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 5. Seed production data if needed
echo "🌱 Seeding production data..."
php artisan db:seed --class=AdminUserSeeder --force

# 6. Set proper permissions
echo "🔐 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 7. Create storage link if not exists
echo "🔗 Creating storage link..."
php artisan storage:link

# 8. Restart services (if using supervisor)
echo "🔄 Restarting queue workers..."
php artisan queue:restart

echo "✅ Deployment completed successfully!"
echo "🌐 Your API is now available at: https://api.worldwideadverts.info"
