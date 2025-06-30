<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "Creating admin user...\n";
    
    // Check if admin user already exists
    if (User::where('email', 'admin@church.com')->exists()) {
        echo "Admin user already exists!\n";
        echo "Email: admin@church.com\n";
        echo "Use your existing password or reset it\n";
        exit(0);
    }

    // Create admin user (without Spatie roles)
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@church.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin', // This uses the simple role field in users table
        'email_verified_at' => now(),
    ]);

    echo "Admin user created successfully!\n";
    echo "Email: admin@church.com\n";
    echo "Password: admin123\n";
    echo "Please change the password after first login.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}