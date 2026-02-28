@echo off
echo 🔍 Running Migration Safety Checks...

echo 1. Checking migration syntax...
php -l database\migrations\*.php >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Syntax errors found in migration files!
    php -l database\migrations\*.php
    exit /b 1
)
echo ✅ All migration files have valid syntax

echo 2. Checking database connection...
php artisan tinker --execute="echo 'DB OK';" >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Database connection failed!
    exit /b 1
)
echo ✅ Database connection successful

echo 3. Checking migration status...
php artisan migrate:status >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Migration status check failed!
    exit /b 1
)
echo ✅ Migration status check passed

echo 🎉 All migration safety checks passed!
echo 💡 Tips to prevent migration errors:
echo    - Always test migrations in development first
echo    - Use descriptive migration names
echo    - Check foreign key references before creating constraints
echo    - Use proper dropIndex() syntax: dropIndex(['column'])
echo    - Run migrate:rollback test before deploying

pause
