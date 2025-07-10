<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Middleware\RateLimitMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware([RateLimitMiddleware::class . ':5,1']); // 5 attempts per minute
    
    // Protected API routes
    Route::middleware(['auth:sanctum', RateLimitMiddleware::class . ':100,1'])->group(function () {
        // Auth routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        
        // Member routes
        Route::apiResource('members', MemberController::class);
        
        // Attendance routes
        Route::apiResource('attendance', AttendanceController::class);
        Route::post('/attendance/bulk', [AttendanceController::class, 'bulkStore']);
        Route::get('/attendance/stats', [AttendanceController::class, 'stats']);
    });
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});