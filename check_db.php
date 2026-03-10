<?php

echo "Checking database status...\n";

try {
    $tables = DB::select('SHOW TABLES');
    echo "Total tables: " . count($tables) . "\n\n";
    
    $eaTables = [];
    $cleanTables = [];
    
    foreach ($tables as $table) {
        $tableName = array_values((array)$table)[0];
        if (str_starts_with($tableName, 'ea_')) {
            $eaTables[] = $tableName;
        } else {
            $cleanTables[] = $tableName;
        }
    }
    
    echo "EA prefixed tables (" . count($eaTables) . "):\n";
    foreach (array_slice($eaTables, 0, 10) as $table) {
        echo "  - $table\n";
    }
    if (count($eaTables) > 10) {
        echo "  ... and " . (count($eaTables) - 10) . " more\n";
    }
    
    echo "\nClean tables (" . count($cleanTables) . "):\n";
    foreach (array_slice($cleanTables, 0, 10) as $table) {
        echo "  - $table\n";
    }
    if (count($cleanTables) > 10) {
        echo "  ... and " . (count($cleanTables) - 10) . " more\n";
    }
    
    // Check a few specific tables
    echo "\nChecking specific tables:\n";
    $specificTables = ['ea_users', 'users', 'ea_books', 'books', 'ea_services', 'services'];
    
    foreach ($specificTables as $table) {
        try {
            $status = DB::select("SHOW TABLE STATUS LIKE '$table'");
            if (!empty($status)) {
                $info = $status[0];
                echo "  $table: {$info->Engine} engine, {$info->Rows} rows\n";
            } else {
                echo "  $table: NOT FOUND\n";
            }
        } catch (Exception $e) {
            echo "  $table: ERROR - " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
