<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
    echo "Setting up Church Management System...\n\n";
    
    // Step 1: Check if we need to publish Spatie Permission migrations
    echo "Step 1: Checking permission system setup...\n";
    
    if (!Schema::hasTable('roles')) {
        echo "   Publishing Spatie Permission migrations...\n";
        Artisan::call('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider'
        ]);
        echo "   Permission migrations published.\n";
    }
    
    // Step 2: Run migrations
    echo "Step 2: Running database migrations...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo "   Migrations completed.\n";
    
    // Step 3: Check if admin user already exists
    echo "Step 3: Setting up admin user...\n";
    
    if (User::where('email', 'admin@church.com')->exists()) {
        echo "   Admin user already exists!\n";
        $admin = User::where('email', 'admin@church.com')->first();
        echo "   Email: admin@church.com\n";
        echo "   Use your existing password or reset it\n";
        exit(0);
    }

    // Step 4: Create admin user
    echo "   Creating admin user...\n";
    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@church.com',
        'password' => Hash::make('admin123'),
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);
    echo "   Admin user created.\n";

    // Step 5: Try to set up roles if the table exists
    try {
        if (Schema::hasTable('roles')) {
            echo "   Setting up admin role...\n";
            
            // Check if role already exists
            $existingRole = DB::table('roles')->where('name', 'admin')->first();
            
            if (!$existingRole) {
                // Use direct DB query to avoid potential issues
                $roleId = DB::table('roles')->insertGetId([
                    'name' => 'admin',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $roleId = $existingRole->id;
            }
            
            // Check if user already has role
            $hasRole = DB::table('model_has_roles')
                ->where('model_id', $admin->id)
                ->where('role_id', $roleId)
                ->exists();
                
            if (!$hasRole) {
                // Assign role to user
                DB::table('model_has_roles')->insert([
                    'role_id' => $roleId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $admin->id,
                ]);
            }
            
            echo "   Admin role assigned.\n";
        }
    } catch (Exception $roleError) {
        echo "   Role assignment skipped (will work with basic admin role): " . $roleError->getMessage() . "\n";
    }

    echo "\nSetup completed successfully!\n";
    echo "Email: admin@church.com\n";
    echo "Password: admin123\n";
    echo "Please change the password after first login.\n";
    echo "\nYou can now access the admin panel at your application URL.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "1. Make sure your database is running and accessible\n";
    echo "2. Check your .env file for correct database credentials\n";
    echo "3. Try running: php artisan migrate --force\n";
    echo "4. Then run this script again\n";
    exit(1);
}