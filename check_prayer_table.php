<?php

// Check prayer_requests table structure and add missing columns
// Run this with: C:\xampp\php\php.exe check_prayer_table.php

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection
try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? 'church_management';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Connected to database successfully.\n\n";
    
    // Check if prayer_requests table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'prayer_requests'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        echo "Error: prayer_requests table does not exist!\n";
        echo "Please run migrations first: C:\\xampp\\php\\php.exe artisan migrate\n";
        exit(1);
    }
    
    echo "Checking prayer_requests table structure...\n\n";
    
    // Get current table structure
    $stmt = $pdo->query("DESCRIBE prayer_requests");
    $columns = $stmt->fetchAll();
    
    $existingColumns = array_column($columns, 'Field');
    
    echo "Current columns:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']}) " . 
             ($column['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . 
             ($column['Default'] !== null ? " DEFAULT '{$column['Default']}'" : '') . "\n";
    }
    
    // Check for missing columns
    $requiredColumns = [
        'requestor_id' => 'bigint unsigned',
        'is_public' => 'tinyint(1)',
        'prayer_target' => 'int',
        'prayer_frequency' => 'int',
        'end_date' => 'date'
    ];
    
    $missingColumns = [];
    foreach ($requiredColumns as $column => $type) {
        if (!in_array($column, $existingColumns)) {
            $missingColumns[] = $column;
        }
    }
    
    if (empty($missingColumns)) {
        echo "\n✓ All required columns exist!\n";
    } else {
        echo "\n✗ Missing columns:\n";
        foreach ($missingColumns as $column) {
            echo "  - $column\n";
        }
        
        echo "\nAdding missing columns...\n";
        
        // Add missing columns
        if (in_array('requestor_id', $missingColumns)) {
            $pdo->exec("ALTER TABLE prayer_requests ADD COLUMN requestor_id BIGINT UNSIGNED NULL AFTER member_id");
            echo "  ✓ Added requestor_id column\n";
        }
        
        if (in_array('is_public', $missingColumns)) {
            $pdo->exec("ALTER TABLE prayer_requests ADD COLUMN is_public TINYINT(1) DEFAULT 1 AFTER is_private");
            echo "  ✓ Added is_public column\n";
        }
        
        if (in_array('prayer_target', $missingColumns)) {
            $pdo->exec("ALTER TABLE prayer_requests ADD COLUMN prayer_target INT NULL AFTER prayer_count");
            echo "  ✓ Added prayer_target column\n";
        }
        
        if (in_array('prayer_frequency', $missingColumns)) {
            $pdo->exec("ALTER TABLE prayer_requests ADD COLUMN prayer_frequency INT NULL AFTER prayer_target");
            echo "  ✓ Added prayer_frequency column\n";
        }
        
        if (in_array('end_date', $missingColumns)) {
            $pdo->exec("ALTER TABLE prayer_requests ADD COLUMN end_date DATE NULL AFTER prayer_frequency");
            echo "  ✓ Added end_date column\n";
        }
        
        echo "\n✓ All missing columns have been added!\n";
    }
    
    echo "\nTable structure updated successfully!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure your database server is running\n";
    echo "2. Check your .env file for correct database credentials\n";
    echo "3. Make sure the database exists\n";
    echo "4. Run migrations: C:\\xampp\\php\\php.exe artisan migrate\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}