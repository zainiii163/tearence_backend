<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckTables extends Command
{
    protected $signature = 'db:check-tables';
    protected $description = 'Check what tables exist in the database';

    public function handle()
    {
        $this->info('📋 Checking database tables...');
        
        $tables = DB::select('SHOW TABLES');
        $tableCount = count($tables);
        
        $this->info("Found {$tableCount} tables:");
        
        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];
            $this->line("  - {$tableName}");
        }
        
        // Check for specific tables
        $importantTables = [
            'users', 'customers', 'campaign', 'books', 'services', 
            'vehicles', 'banners', 'listings', 'events', 'venues'
        ];
        
        $this->info("\n🔍 Checking important tables:");
        foreach ($importantTables as $table) {
            $exists = DB::select("SHOW TABLES LIKE '{$table}'");
            $status = $exists ? "✅" : "❌";
            $this->line("  {$status} {$table}");
        }
        
        return 0;
    }
}
