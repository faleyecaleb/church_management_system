<?php

// Simple admin user creation without Spatie permissions
// Run this with: C:\xampp\php\php.exe create_admin_basic.php

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
    
    echo "Connected to database successfully.\n";
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
    $stmt->execute(['admin@church.com']);
    $existingUser = $stmt->fetch();
    
    if ($existingUser) {
        echo "Admin user already exists!\n";
        echo "ID: " . $existingUser['id'] . "\n";
        echo "Name: " . $existingUser['name'] . "\n";
        echo "Email: " . $existingUser['email'] . "\n";
        echo "You can login with this email and your existing password.\n";
        exit(0);
    }
    
    // Create admin user
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');
    
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, email_verified_at, password, role, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        'Admin User',
        'admin@church.com',
        $now,
        $hashedPassword,
        'admin',
        $now,
        $now
    ]);
    
    if ($result) {
        echo "Admin user created successfully!\n";
        echo "Email: admin@church.com\n";
        echo "Password: admin123\n";
        echo "Please change the password after first login.\n";
        echo "\nYou can now access the admin panel at your application URL.\n";
    } else {
        echo "Failed to create admin user.\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure your database server is running\n";
    echo "2. Check your .env file for correct database credentials\n";
    echo "3. Make sure the database exists\n";
    echo "4. Run migrations first if you haven't: C:\\xampp\\php\\php.exe artisan migrate\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}