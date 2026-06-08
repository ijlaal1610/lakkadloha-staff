<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');

    // Profile
    Route::get('/profile', [StaffController::class, 'profile'])->name('profile');
    Route::put('/profile', [StaffController::class, 'updateProfile'])->name('profile.update');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::get('/inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{product}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{product}', [InventoryController::class, 'destroy'])->name('inventory.destroy');
    Route::post('/inventory/{product}/add-stock', [InventoryController::class, 'addStock'])->name('inventory.add-stock');
    Route::post('/inventory/{product}/adjust-stock', [InventoryController::class, 'adjustStock'])->name('inventory.adjust-stock');

    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/edit', [SalesController::class, 'edit'])->name('sales.edit');
    Route::put('/sales/{sale}', [SalesController::class, 'update'])->name('sales.update');
    Route::post('/sales/{sale}/cancel', [SalesController::class, 'cancel'])->name('sales.cancel');
    Route::post('/sales/{sale}/refund', [SalesController::class, 'refund'])->name('sales.refund');
    Route::get('/sales/{sale}/receipt', [SalesController::class, 'receipt'])->name('sales.receipt');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
    Route::post('/attendance/mark', [AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('/attendance/bulk-mark', [AttendanceController::class, 'bulkMark'])->name('attendance.bulk-mark');
    Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    // Salary
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
    Route::get('/salary/create', [SalaryController::class, 'create'])->name('salary.create');
    Route::post('/salary', [SalaryController::class, 'store'])->name('salary.store');
    Route::get('/salary/{salary}', [SalaryController::class, 'show'])->name('salary.show');
    Route::delete('/salary/{salary}', [SalaryController::class, 'destroy'])->name('salary.destroy');
    Route::get('/salary/employee/{employee}', [SalaryController::class, 'employeeSummary'])->name('salary.employee-summary');

    // Staff Management (Admin/Manager only)
    Route::middleware('can:admin,App\Models\User')->group(function () {
        Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
        Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
        Route::post('/staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
        Route::get('/staff/{staff}/login-history', [StaffController::class, 'loginHistory'])->name('staff.login-history');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('can:viewReports,App\Models\Sale')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/sales', [ReportsController::class, 'sales'])->name('sales');
        Route::get('/attendance', [ReportsController::class, 'attendance'])->name('attendance');
        Route::get('/salary', [ReportsController::class, 'salary'])->name('salary');
        Route::get('/inventory', [ReportsController::class, 'inventory'])->name('inventory');
        // Exports
        Route::get('/sales/export/pdf', [ReportsController::class, 'exportSalesPdf'])->name('sales.pdf');
        Route::get('/sales/export/csv', [ReportsController::class, 'exportSalesCsv'])->name('sales.csv');
        Route::get('/attendance/export/pdf', [ReportsController::class, 'exportAttendancePdf'])->name('attendance.pdf');
        Route::get('/salary/export/pdf', [ReportsController::class, 'exportSalaryPdf'])->name('salary.pdf');
    });

    // Audit Logs (Super Admin only)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

// PWA manifest and service worker
Route::get('/manifest.json', function () {
    return response()->file(public_path('manifest.json'), ['Content-Type' => 'application/manifest+json']);
});
