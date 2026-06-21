<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CounsellingBookingController;
use App\Http\Controllers\SmsMessageController;
use App\Http\Controllers\ServiceController;
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
        Route::post('/user/change-password', [AuthController::class, 'changePassword']);
        Route::post('/user/push-token', [AuthController::class, 'savePushToken']);
        
        // Member routes
        Route::apiResource('members', MemberController::class);
        Route::put('/user/profile', [MemberController::class, 'apiUpdate']);
        
        // Attendance routes
        Route::apiResource('attendance', AttendanceController::class);
        Route::post('/attendance/bulk', [AttendanceController::class, 'bulkStore']);
        Route::get('/attendance/stats', [AttendanceController::class, 'stats']);
        Route::post('/attendance/scan/{service}', [AttendanceController::class, 'processQrCode']);

        // Service routes
        Route::get('/services', [ServiceController::class, 'apiIndex']);

        // Counselling Booking routes
        Route::get('/counselling', [CounsellingBookingController::class, 'apiIndex']);
        Route::post('/counselling', [CounsellingBookingController::class, 'apiStore']);

        // Announcements (Communication Hub) routes
        Route::get('/announcements', [SmsMessageController::class, 'apiIndex']);
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