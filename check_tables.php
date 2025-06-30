<?php

// Check if required tables exist and create them if needed
// Run this with: C:\xampp\php\php.exe check_tables.php

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
    
    // Check if order_of_services table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_of_services'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✓ order_of_services table exists.\n";
        
        // Check table structure
        $stmt = $pdo->query("DESCRIBE order_of_services");
        $columns = $stmt->fetchAll();
        echo "Table columns:\n";
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})\n";
        }
    } else {
        echo "✗ order_of_services table does not exist.\n";
        echo "Creating table...\n";
        
        // Create the table manually
        $createTableSQL = "
            CREATE TABLE `order_of_services` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `service_id` bigint unsigned NOT NULL,
                `program` varchar(255) NOT NULL,
                `start_time` time DEFAULT NULL,
                `end_time` time DEFAULT NULL,
                `order` int NOT NULL,
                `description` text,
                `leader` varchar(255) DEFAULT NULL,
                `notes` text,
                `duration_minutes` int DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT NULL,
                `updated_at` timestamp NULL DEFAULT NULL,
                `deleted_at` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `order_of_services_service_id_foreign` (`service_id`),
                KEY `order_of_services_service_id_order_index` (`service_id`,`order`),
                CONSTRAINT `order_of_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $pdo->exec($createTableSQL);
        echo "✓ order_of_services table created successfully.\n";
    }
    
    // Check other important tables
    $requiredTables = ['users', 'services', 'members', 'roles', 'permissions'];
    echo "\nChecking other required tables:\n";
    
    foreach ($requiredTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo ($exists ? "✓" : "✗") . " $table table " . ($exists ? "exists" : "missing") . "\n";
    }
    
    echo "\nDatabase check completed!\n";
    
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