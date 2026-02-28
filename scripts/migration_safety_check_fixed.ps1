# Migration Safety Check Script (PowerShell)
# Run this before deploying any migration changes

Write-Host "🔍 Running Migration Safety Checks..." -ForegroundColor Yellow

# Check 1: All migration files have valid PHP syntax
Write-Host "1. Checking migration syntax..." -ForegroundColor Cyan
$syntaxErrors = @()
Get-ChildItem "database\migrations\*.php" | ForEach-Object {
    $result = php -l $_.FullName 2>&1
    if ($result -match "Parse error") {
        $syntaxErrors += $_.Name
    }
}

if ($syntaxErrors.Count -eq 0) {
    Write-Host "✅ All migration files have valid syntax" -ForegroundColor Green
} else {
    Write-Host "❌ Syntax errors found in migration files!" -ForegroundColor Red
    $syntaxErrors | ForEach-Object { Write-Host "   - $_" -ForegroundColor Red }
    exit 1
}

# Check 2: No duplicate migration file names
Write-Host "2. Checking for duplicate migration files..." -ForegroundColor Cyan
$migrationFiles = Get-ChildItem "database\migrations\*.php" | ForEach-Object { 
    $_.Name -replace '^\d{4}_\d{2}_\d{2}_\d{6}_(.+)\.php$', '$1'
}
$duplicates = $migrationFiles | Group-Object | Where-Object { $_.Count -gt 1 }

if ($duplicates.Count -eq 0) {
    Write-Host "✅ No duplicate migration file names" -ForegroundColor Green
} else {
    Write-Host "❌ Duplicate migration file names found!" -ForegroundColor Red
    $duplicates | ForEach-Object { Write-Host "   - $($_.Name)" -ForegroundColor Red }
    exit 1
}

# Check 3: Database is accessible
Write-Host "3. Checking database connection..." -ForegroundColor Cyan
try {
    $dbTest = php artisan tinker --execute="echo 'DB OK';" 2>&1
    if ($dbTest -match "DB OK") {
        Write-Host "✅ Database connection successful" -ForegroundColor Green
    } else {
        throw "Database connection failed"
    }
} catch {
    Write-Host "❌ Database connection failed!" -ForegroundColor Red
    exit 1
}

# Check 4: Migration status
Write-Host "4. Checking migration status..." -ForegroundColor Cyan
try {
    $statusTest = php artisan migrate:status 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Migration status check passed" -ForegroundColor Green
    } else {
        throw "Migration status check failed"
    }
} catch {
    Write-Host "❌ Migration status check failed!" -ForegroundColor Red
    exit 1
}

Write-Host "🎉 All migration safety checks passed!" -ForegroundColor Green
Write-Host "💡 Tips to prevent migration errors:" -ForegroundColor Cyan
Write-Host "   - Always test migrations in development first" -ForegroundColor White
Write-Host "   - Use descriptive migration names" -ForegroundColor White
Write-Host "   - Check foreign key references before creating constraints" -ForegroundColor White
Write-Host "   - Use proper dropIndex() syntax: dropIndex(['column'])" -ForegroundColor White
Write-Host "   - Run migrate:rollback test before deploying" -ForegroundColor White
