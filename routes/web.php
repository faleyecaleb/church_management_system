<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PledgeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SmsMessageController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\EmailMessageController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\InternalMessageController;
use App\Http\Controllers\MessageGroupController;
use App\Http\Controllers\PrayerRequestController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\EmergencyContactController;
use App\Http\Controllers\MemberDocumentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceSettingsController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ServiceAttendanceController;
use App\Http\Controllers\AttendanceMarkingController;
use App\Http\Controllers\NotificationController;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('admin.dashboard');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Financial Management
    Route::resource('pledges', PledgeController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('budgets', BudgetController::class);

    // Communication Management
    Route::resource('sms-messages', SmsMessageController::class);
    Route::resource('sms-templates', SmsTemplateController::class);
    Route::resource('email-messages', EmailMessageController::class);
    Route::resource('email-templates', EmailTemplateController::class);
    Route::resource('internal-messages', InternalMessageController::class);
    Route::resource('message-groups', MessageGroupController::class);

    // Prayer Requests
    Route::resource('prayer-requests', PrayerRequestController::class);

    // Profile Management
    Route::get('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

    // System Management
    Route::resource('settings', SettingController::class);
    Route::resource('audit-logs', AuditLogController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    
    // Notifications Management
    Route::resource('notifications', NotificationController::class);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Additional Financial Routes
    Route::post('pledges/{pledge}/record-payment', [PledgeController::class, 'recordPayment'])->name('pledges.record-payment');
    Route::get('pledges/reports/by-status', [PledgeController::class, 'reportByStatus'])->name('pledges.reports.by-status');
    Route::get('pledges/reports/by-campaign', [PledgeController::class, 'reportByCampaign'])->name('pledges.reports.by-campaign');
    Route::get('pledges/reports/monthly', [PledgeController::class, 'monthlyReport'])->name('pledges.reports.monthly');
    Route::get('pledges/reports/top-pledgers', [PledgeController::class, 'topPledgers'])->name('pledges.reports.top-pledgers');

    // Additional Expense Routes
    Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');
    Route::get('expenses/reports/by-category', [ExpenseController::class, 'reportByCategory'])->name('expenses.reports.by-category');
    Route::get('expenses/reports/by-department', [ExpenseController::class, 'reportByDepartment'])->name('expenses.reports.by-department');
    Route::get('expenses/reports/monthly', [ExpenseController::class, 'monthlyReport'])->name('expenses.reports.monthly');

    // Additional Budget Routes
    Route::get('budgets/reports/utilization', [BudgetController::class, 'utilizationReport'])->name('budgets.reports.utilization');
    Route::get('budgets/reports/by-category', [BudgetController::class, 'reportByCategory'])->name('budgets.reports.by-category');
    Route::get('budgets/reports/by-department', [BudgetController::class, 'reportByDepartment'])->name('budgets.reports.by-department');
    Route::get('budgets/reports/monthly-comparison', [BudgetController::class, 'monthlyComparison'])->name('budgets.reports.monthly-comparison');

    // Bulk SMS and Messaging Routes
    Route::prefix('messages')->group(function () {
        // Message Management
        Route::get('/', [MessageController::class, 'index'])->name('messages.index');
        Route::get('/create', [MessageController::class, 'create'])->name('messages.create');
        Route::post('/', [MessageController::class, 'store'])->name('messages.store');
        Route::get('/{message}', [MessageController::class, 'show'])->name('messages.show');
        Route::get('/{message}/edit', [MessageController::class, 'edit'])->name('messages.edit');
        Route::put('/{message}', [MessageController::class, 'update'])->name('messages.update');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        // Message Actions
        Route::post('/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.read');
        Route::post('/{message}/retry', [MessageController::class, 'retry'])->name('messages.retry');
        Route::post('/{message}/cancel', [MessageController::class, 'cancel'])->name('messages.cancel');

        // Legacy Message Routes
        Route::post('sms-messages/{message}/send', [SmsMessageController::class, 'send'])->name('sms-messages.send');
        Route::post('email-messages/{message}/send', [EmailMessageController::class, 'send'])->name('email-messages.send');
        Route::post('message-groups/{group}/members/{member}', [MessageGroupController::class, 'addMember'])->name('message-groups.add-member');
        Route::delete('message-groups/{group}/members/{member}', [MessageGroupController::class, 'removeMember'])->name('message-groups.remove-member');
    });

    // Analytics and Reporting Routes
    Route::prefix('reports')->group(function () {
        // Dashboard
        Route::get('/dashboard', [ReportController::class, 'dashboard'])->name('reports.dashboard');

        // Standard Reports
        Route::get('/membership', [ReportController::class, 'membership'])->name('reports.membership');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
        Route::get('/financial', [ReportController::class, 'financial'])->name('reports.financial');
        Route::get('/communication', [ReportController::class, 'communication'])->name('reports.communication');
        Route::get('/growth', [ReportController::class, 'growth'])->name('reports.growth');

        // Custom Reports
        Route::get('/custom', [ReportController::class, 'custom'])->name('reports.custom');
        Route::post('/custom/generate', [ReportController::class, 'generateCustom'])->name('reports.custom.generate');

        // Report Exports
        Route::post('/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/exports', [ReportController::class, 'exports'])->name('reports.exports.index');
        Route::get('/exports/{export}/download', [ReportController::class, 'download'])->name('reports.exports.download');
        Route::delete('/exports/{export}', [ReportController::class, 'destroyExport'])->name('reports.exports.destroy');

        // Dashboard Widgets
        Route::get('/widgets', [ReportController::class, 'widgets'])->name('reports.widgets.index');
        Route::post('/widgets/layout', [ReportController::class, 'updateWidgetLayout'])->name('reports.widgets.layout');
        Route::post('/widgets/{widget}/configure', [ReportController::class, 'configureWidget'])->name('reports.widgets.configure');
    });

    // Additional Prayer Request Routes
    Route::post('prayer-requests/{request}/complete', [PrayerRequestController::class, 'markAsCompleted'])->name('prayer-requests.complete');
    Route::post('prayer-requests/{request}/archive', [PrayerRequestController::class, 'archive'])->name('prayer-requests.archive');
    Route::post('prayer-requests/{request}/reactivate', [PrayerRequestController::class, 'reactivate'])->name('prayer-requests.reactivate');

    // Member Management Routes
    Route::resource('members', MemberController::class);

    // Member Emergency Contact Routes
    Route::resource('members.emergency-contacts', EmergencyContactController::class)->except(['index', 'show']);

    // Service Management
    Route::resource('services', ServiceController::class);

    // Attendance Management
    Route::prefix('attendance')->name('attendance.')->group(function () {
        // Settings
        Route::get('/settings', [AttendanceSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AttendanceSettingsController::class, 'update'])->name('settings.update');

        // Service Attendance Management
        Route::get('/', [ServiceAttendanceController::class, 'index'])->name('service');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::post('/{service}/check-in-multiple', [ServiceAttendanceController::class, 'checkInMultiple'])->name('check-in-multiple');
        Route::post('/{attendance}/check-out', [ServiceAttendanceController::class, 'checkOut'])->name('check-out');
        Route::post('/{service}/check-out-all', [ServiceAttendanceController::class, 'checkOutAll'])->name('check-out-all');

        // Legacy Service Attendance Management
        Route::get('/services/{service}', [AttendanceController::class, 'index'])->name('index');
        Route::get('/services/{service}/qr-code', [QrCodeController::class, 'generate'])->name('qr-code');
        Route::post('/services/{service}/process-qr', [QrCodeController::class, 'checkIn'])->name('process-qr');

        // Member Check-in/out
        Route::post('/services/{service}/members/{member}/check-in', [AttendanceController::class, 'checkInMember'])->name('check-in-member');
        Route::post('/services/{service}/members/{member}/check-out', [AttendanceController::class, 'checkOutMember'])->name('check-out-member');

        // Statistics and Reports
        Route::get('/stats', [AttendanceController::class, 'getStats'])->name('stats');
        Route::get('/report', [AttendanceController::class, 'report'])->name('report');
        Route::get('/dashboard', [AttendanceController::class, 'dashboard'])->name('dashboard');
        Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
        
        // Attendance Marking (Two-Step Process)
        Route::get('/marking', [AttendanceMarkingController::class, 'index'])->name('marking');
        Route::post('/marking/step1', [AttendanceMarkingController::class, 'processStep1'])->name('marking.step1.process');
        Route::post('/marking/step2', [AttendanceMarkingController::class, 'processStep2'])->name('marking.step2.process');
    });

    // Member Document Routes
    Route::resource('members.documents', MemberDocumentController::class)->except(['index', 'show']);
    Route::get('members/{member}/documents/{document}/download', [MemberDocumentController::class, 'download'])
        ->name('members.documents.download');
    Route::post('members/{member}/documents/{document}/verify', [MemberDocumentController::class, 'verify'])
        ->name('members.documents.verify');
});
