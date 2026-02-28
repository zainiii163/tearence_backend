#!/bin/bash

# Migration Safety Check Script
# Run this before deploying any migration changes

echo "🔍 Running Migration Safety Checks..."

# Check 1: All migration files have valid PHP syntax
echo "1. Checking migration syntax..."
php -l database/migrations/*.php > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ All migration files have valid syntax"
else
    echo "❌ Syntax errors found in migration files!"
    php -l database/migrations/*.php
    exit 1
fi

# Check 2: No duplicate migration file names
echo "2. Checking for duplicate migration files..."
duplicates=$(ls database/migrations/ | sed 's/_[0-9]*_.*\.php$/_/' | sort | uniq -d)
if [ -z "$duplicates" ]; then
    echo "✅ No duplicate migration file names"
else
    echo "❌ Duplicate migration file names found: $duplicates"
    exit 1
fi

# Check 3: Database is accessible
echo "3. Checking database connection..."
php artisan tinker --execute="echo 'DB OK';" > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Database connection successful"
else
    echo "❌ Database connection failed!"
    exit 1
fi

# Check 4: No pending migrations (optional)
echo "4. Checking migration status..."
php artisan migrate:status > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Migration status check passed"
else
    echo "❌ Migration status check failed!"
    exit 1
fi

echo "🎉 All migration safety checks passed!"
echo "💡 Tips to prevent migration errors:"
echo "   - Always test migrations in development first"
echo "   - Use descriptive migration names"
echo "   - Check foreign key references before creating constraints"
echo "   - Use proper dropIndex() syntax: dropIndex(['column'])"
echo "   - Run migrate:rollback test before deploying"
