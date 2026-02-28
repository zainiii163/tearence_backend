<?php

/**
 * Migration Validation Script
 * 
 * This script validates migration integrity and prevents common migration errors
 * Run this script after any migration changes to ensure system stability
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MigrationValidator
{
    public static function validateMigrationIntegrity()
    {
        $issues = [];
        
        // 1. Check for orphaned migration records
        $orphanedRecords = self::findOrphanedMigrationRecords();
        if (!empty($orphanedRecords)) {
            $issues[] = "Orphaned migration records found: " . implode(', ', $orphanedRecords);
        }
        
        // 2. Check for missing migration records
        $missingRecords = self::findMissingMigrationRecords();
        if (!empty($missingRecords)) {
            $issues[] = "Missing migration records for existing tables: " . implode(', ', $missingRecords);
        }
        
        // 3. Validate foreign key constraints
        $invalidForeignKeys = self::validateForeignKeys();
        if (!empty($invalidForeignKeys)) {
            $issues[] = "Invalid foreign key references: " . implode(', ', $invalidForeignKeys);
        }
        
        // 4. Check index consistency
        $indexIssues = self::validateIndexes();
        if (!empty($indexIssues)) {
            $issues[] = "Index inconsistencies: " . implode(', ', $indexIssues);
        }
        
        return $issues;
    }
    
    private static function findOrphanedMigrationRecords()
    {
        $orphaned = [];
        $migrationRecords = DB::table('migrations')->pluck('migration');
        
        foreach ($migrationRecords as $migration) {
            // Check if migration file exists
            $migrationFile = database_path("migrations/{$migration}.php");
            if (!file_exists($migrationFile)) {
                $orphaned[] = $migration;
            }
        }
        
        return $orphaned;
    }
    
    private static function findMissingMigrationRecords()
    {
        $missing = [];
        $migrationFiles = glob(database_path('migrations/*.php'));
        $migrationRecords = DB::table('migrations')->pluck('migration')->toArray();
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');
            if (!in_array($migrationName, $migrationRecords)) {
                $missing[] = $migrationName;
            }
        }
        
        return $missing;
    }
    
    private static function validateForeignKeys()
    {
        $invalid = [];
        $tables = Schema::getTableListing();
        
        foreach ($tables as $table) {
            if ($table === 'migrations') continue;
            
            $columns = DB::select("DESCRIBE `{$table}`");
            foreach ($columns as $column) {
                if (strpos($column->Field, '_id') !== false) {
                    // This might be a foreign key, validate if referenced table exists
                    $referencedTable = str_replace('_id', '', $column->Field);
                    if (!Schema::hasTable($referencedTable)) {
                        $invalid[] = "{$table}.{$column->Field} -> {$referencedTable}";
                    }
                }
            }
        }
        
        return $invalid;
    }
    
    private static function validateIndexes()
    {
        $issues = [];
        $tables = Schema::getTableListing();
        
        foreach ($tables as $table) {
            if ($table === 'migrations') continue;
            
            $indexes = DB::select("SHOW INDEX FROM `{$table}`");
            $indexNames = array_unique(array_column($indexes, 'Key_name'));
            
            // Check for duplicate index names
            if (count($indexNames) !== count($indexes)) {
                $issues[] = "Duplicate indexes found in table: {$table}";
            }
        }
        
        return $issues;
    }
    
    public static function runValidation()
    {
        echo "Running migration validation...\n";
        
        $issues = self::validateMigrationIntegrity();
        
        if (empty($issues)) {
            echo "✅ All migration checks passed!\n";
            return true;
        } else {
            echo "❌ Migration validation failed:\n";
            foreach ($issues as $issue) {
                echo "  - {$issue}\n";
            }
            return false;
        }
    }
}

// Run validation if this script is executed directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    MigrationValidator::runValidation();
}
