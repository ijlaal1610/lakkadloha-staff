<?php

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\SalaryApiController;
use App\Http\Controllers\Api\SalesApiController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login']);
});

// Protected routes
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    Route::put('/auth/profile', [AuthApiController::class, 'updateProfile']);

    // Dashboard
    Route::get('/dashboard', [SalesApiController::class, 'dashboard']);

    // Inventory
    Route::get('/inventory', [InventoryApiController::class, 'index']);
    Route::get('/inventory/{product}', [InventoryApiController::class, 'show']);
    Route::post('/inventory', [InventoryApiController::class, 'store']);
    Route::put('/inventory/{product}', [InventoryApiController::class, 'update']);
    Route::post('/inventory/{product}/add-stock', [InventoryApiController::class, 'addStock']);

    // Sales
    Route::get('/sales', [SalesApiController::class, 'index']);
    Route::get('/sales/{sale}', [SalesApiController::class, 'show']);
    Route::post('/sales', [SalesApiController::class, 'store']);

    // Attendance
    Route::get('/attendance', [AttendanceApiController::class, 'index']);
    Route::get('/attendance/today', [AttendanceApiController::class, 'today']);
    Route::post('/attendance/check-in', [AttendanceApiController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceApiController::class, 'checkOut']);

    // Salary
    Route::get('/salary', [SalaryApiController::class, 'index']);
    Route::post('/salary', [SalaryApiController::class, 'store']);
    Route::get('/salary/summary', [SalaryApiController::class, 'summary']);

    // Notifications
    Route::get('/notifications', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'notifications' => $request->user()->notifications()->paginate(20),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    });
    Route::post('/notifications/{id}/read', function (\Illuminate\Http\Request $request, $id) {
        $request->user()->notifications()->findOrFail($id)->markAsRead();
        return response()->json(['message' => 'Marked as read.']);
    });
});
