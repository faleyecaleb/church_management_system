<?php

/**
 * Platform-independent script to execute Laravel migrations sequentially one by one.
 * Run this from the root of your church_management_system directory:
 * 
 * php migrate_one_by_one.php
 */

$migrationsDir = __DIR__ . '/database/migrations';

if (!is_dir($migrationsDir)) {
    die("Error: database/migrations directory not found in the current working directory.\n");
}

// Get all files in the migrations directory
$files = scandir($migrationsDir);

// Filter out directories and any non-PHP files
$migrations = array_filter($files, function($file) use ($migrationsDir) {
    return is_file($migrationsDir . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php';
});

// Sort them alphabetically (this matches Laravel's standard execution order)
sort($migrations);

echo "==================================================\n";
echo "Sequential Migration Runner\n";
echo "==================================================\n";
echo "Found " . count($migrations) . " migration files.\n";
echo "Executing migrations one-by-one...\n\n";

foreach ($migrations as $migration) {
    $path = "database/migrations/" . $migration;
    echo "\033[33mMigrating:\033[0m {$migration}\n";
    
    // Construct the artisan command
    $command = "php artisan migrate --path=" . escapeshellarg($path);
    
    // Execute the command, stream output in real time, and capture exit code
    passthru($command, $exitCode);
    
    if ($exitCode !== 0) {
        echo "\n\033[31mError: Migration failed for {$migration} with exit code {$exitCode}.\033[0m\n";
        exit($exitCode);
    }
    
    echo "\033[90m--------------------------------------------------\033[0m\n\n";
}

echo "\033[32mSuccess: All migrations completed successfully one-by-one!\033[0m\n";
