<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDeliveryMethodController;
use App\Http\Controllers\Admin\AdminPaymentMethodController;
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

        // Customer management routes with permissions
        Route::get('customers', [AdminCustomerController::class, 'index'])->middleware('permission:Read Customers');
        Route::get('customers/{customer}', [AdminCustomerController::class, 'show'])->middleware('permission:Read Customers');
        Route::post('customers', [AdminCustomerController::class, 'store'])->middleware('permission:Create Customer');
        Route::put('customers/{customer}', [AdminCustomerController::class, 'update'])->middleware('permission:Update Customer');
        Route::patch('customers/{customer}', [AdminCustomerController::class, 'update'])->middleware('permission:Update Customer');
        Route::delete('customers/{customer}', [AdminCustomerController::class, 'destroy'])->middleware('permission:Delete Customer');

        // Delivery methods management routes with permissions
        Route::get('delivery-methods', [AdminDeliveryMethodController::class, 'index'])->middleware('permission:Read Delivery Methods');
        Route::get('delivery-methods/{id}', [AdminDeliveryMethodController::class, 'show'])->middleware('permission:Read Delivery Methods');
        Route::post('delivery-methods', [AdminDeliveryMethodController::class, 'store'])->middleware('permission:Create Delivery Method');
        Route::put('delivery-methods/{id}', [AdminDeliveryMethodController::class, 'update'])->middleware('permission:Update Delivery Method');
        Route::patch('delivery-methods/{id}', [AdminDeliveryMethodController::class, 'update'])->middleware('permission:Update Delivery Method');
        Route::patch('delivery-methods/{id}/toggle-active', [AdminDeliveryMethodController::class, 'toggleActive'])->middleware('permission:Update Delivery Method');
        Route::post('delivery-methods/{id}/payment-methods', [AdminDeliveryMethodController::class, 'syncPaymentMethods'])->middleware('permission:Update Delivery Method');
        Route::patch('delivery-methods/{deliveryMethodId}/payment-methods/{paymentMethodId}/toggle-active', [AdminDeliveryMethodController::class, 'togglePaymentMethodActive'])->middleware('permission:Update Delivery Method');

        // Payment methods management routes with permissions
        Route::get('payment-methods', [AdminPaymentMethodController::class, 'index'])->middleware('permission:Read Payment Methods');
        Route::get('payment-methods/{id}', [AdminPaymentMethodController::class, 'show'])->middleware('permission:Read Payment Methods');
        Route::post('payment-methods', [AdminPaymentMethodController::class, 'store'])->middleware('permission:Create Payment Method');
        Route::put('payment-methods/{id}', [AdminPaymentMethodController::class, 'update'])->middleware('permission:Update Payment Method');
        Route::patch('payment-methods/{id}', [AdminPaymentMethodController::class, 'update'])->middleware('permission:Update Payment Method');
        Route::patch('payment-methods/{id}/toggle-active', [AdminPaymentMethodController::class, 'toggleActive'])->middleware('permission:Update Payment Method');
    });
});
