<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

// Admin routes group with /admin prefix
Route::prefix('admin')->group(function () {
    // Public admin routes
    Route::post('/register', [AdminAuthController::class, 'register']);
    Route::post('/login', [AdminAuthController::class, 'login']);

    // Protected admin routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/user', [AdminAuthController::class, 'user']);

        // Role management routes with permissions
        Route::get('roles', [AdminRoleController::class, 'index'])->middleware('permission:Read Roles');
        Route::get('roles/{role}', [AdminRoleController::class, 'show'])->middleware('permission:Read Roles');
        Route::post('roles', [AdminRoleController::class, 'store'])->middleware('permission:Create Role');
        Route::put('roles/{role}', [AdminRoleController::class, 'update'])->middleware('permission:Update Role');
        Route::patch('roles/{role}', [AdminRoleController::class, 'update'])->middleware('permission:Update Role');
        Route::delete('roles/{role}', [AdminRoleController::class, 'destroy'])->middleware('permission:Delete Role');

        // Permission management routes with permissions
        Route::get('permissions', [AdminPermissionController::class, 'index'])->middleware('permission:Read Permissions');

        // Admin users management routes with permissions
        Route::get('users', [AdminUserController::class, 'index'])->middleware('permission:Read Admin Users');
        Route::get('users/{user}', [AdminUserController::class, 'show'])->middleware('permission:Read Admin Users');
        Route::post('users', [AdminUserController::class, 'store'])->middleware('permission:Create Admin User');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->middleware('permission:Update Admin User');
        Route::patch('users/{user}', [AdminUserController::class, 'update'])->middleware('permission:Update Admin User');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->middleware('permission:Delete Admin User');
    });
});



