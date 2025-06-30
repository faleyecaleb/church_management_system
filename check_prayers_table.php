<?php

// Check prayers table structure and create if missing
// Run this with: C:\xampp\php\php.exe check_prayers_table.php

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
    
    // Check if prayers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'prayers'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✓ prayers table exists.\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE prayers");
        $columns = $stmt->fetchAll();
        echo "Table columns:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    } else {
        echo "✗ prayers table does not exist.\n";
        echo "Creating prayers table...\n";
        
        // Create the table manually
        $createTableSQL = "
            CREATE TABLE `prayers` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `prayer_request_id` bigint unsigned NOT NULL,
                `member_id` bigint unsigned DEFAULT NULL,
                `user_id` bigint unsigned DEFAULT NULL,
                `notes` text,
                `prayed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `prayers_prayer_request_id_foreign` (`prayer_request_id`),
                KEY `prayers_member_id_foreign` (`member_id`),
                KEY `prayers_user_id_foreign` (`user_id`),
                KEY `prayers_prayer_request_id_prayed_at_index` (`prayer_request_id`,`prayed_at`),
                KEY `prayers_member_id_prayed_at_index` (`member_id`,`prayed_at`),
                KEY `prayers_user_id_prayed_at_index` (`user_id`,`prayed_at`),
                CONSTRAINT `prayers_prayer_request_id_foreign` FOREIGN KEY (`prayer_request_id`) REFERENCES `prayer_requests` (`id`) ON DELETE CASCADE,
                CONSTRAINT `prayers_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
                CONSTRAINT `prayers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($createTableSQL);
        echo "✓ prayers table created successfully.\n";
    }
    
    echo "\nPrayers table setup completed!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure your database server is running\n";
    echo "2. Check your .env file for correct database credentials\n";
    echo "3. Make sure the database exists\n";
    echo "4. Run migrations: C:\\xampp\\php\\php.exe artisan migrate --force\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}