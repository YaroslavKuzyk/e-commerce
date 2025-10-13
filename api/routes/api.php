<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Role management routes with permissions
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:Read Roles');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:Read Roles');
    Route::post('roles', [RoleController::class, 'store'])->middleware('permission:Create Role');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:Update Role');
    Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware('permission:Update Role');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:Delete Role');

    // Permission management routes with permissions
    Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:Read Permissions');
});



