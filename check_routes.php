<?php

// Check available routes for order of service
// Run this with: C:\xampp\php\php.exe check_routes.php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
    $routes = app('router')->getRoutes();
    
    echo "Order of Service Related Routes:\n";
    echo "================================\n\n";
    
    foreach ($routes as $route) {
        $name = $route->getName();
        if ($name && (strpos($name, 'order-of-service') !== false || strpos($name, 'services.order-of-service') !== false)) {
            echo "Route Name: " . $name . "\n";
            echo "URI: " . $route->uri() . "\n";
            echo "Methods: " . implode(', ', $route->methods()) . "\n";
            echo "Action: " . $route->getActionName() . "\n";
            echo "---\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}