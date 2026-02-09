# PowerShell script to fix route cache issues on production server (Windows)
# Run this script on the production server if using Windows

Write-Host "🔧 Fixing Laravel Route Cache Issues..." -ForegroundColor Cyan
Write-Host ""

# Change to project directory (adjust path as needed)
# Set-Location "C:\path\to\project"

Write-Host "1. Clearing route cache..." -ForegroundColor Yellow
php artisan route:clear

Write-Host "2. Clearing config cache..." -ForegroundColor Yellow
php artisan config:clear

Write-Host "3. Clearing application cache..." -ForegroundColor Yellow
php artisan cache:clear

Write-Host "4. Clearing view cache..." -ForegroundColor Yellow
php artisan view:clear

Write-Host "5. Regenerating autoload files..." -ForegroundColor Yellow
composer dump-autoload

Write-Host "6. Optimizing application..." -ForegroundColor Yellow
php artisan optimize:clear

Write-Host "7. Recaching routes..." -ForegroundColor Yellow
php artisan route:cache

Write-Host ""
Write-Host "✅ Route cache cleared and regenerated!" -ForegroundColor Green
Write-Host ""

Write-Host "8. Verifying dashboard routes..." -ForegroundColor Yellow
php artisan route:list --path=dashboard

Write-Host ""
Write-Host "9. Verifying job-alert routes..." -ForegroundColor Yellow
php artisan route:list --path=job-alert

Write-Host ""
Write-Host "✅ Done! Routes should now be accessible." -ForegroundColor Green

