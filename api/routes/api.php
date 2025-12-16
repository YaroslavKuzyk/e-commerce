<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminPermissionController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminDeliveryMethodController;
use App\Http\Controllers\Admin\AdminPaymentMethodController;
use App\Http\Controllers\Admin\AdminProductCategoryController;
use App\Http\Controllers\Admin\AdminProductBrandController;
use App\Http\Controllers\Admin\AdminFileController;
use App\Http\Controllers\Admin\AdminAttributeController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminBlogCategoryController;
use App\Http\Controllers\Admin\AdminBlogPostController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerProductCategoryController;
use App\Http\Controllers\CustomerProductController;
use App\Http\Controllers\CustomerFileController;
use Illuminate\Support\Facades\Route;

// Customer auth routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout']);
    Route::get('/user', [CustomerAuthController::class, 'user']);
});

// Customer auth routes (public)
Route::post('/register', [CustomerAuthController::class, 'register']);
Route::post('/login', [CustomerAuthController::class, 'login']);

// Customer categories routes (public)
Route::get('/product-categories', [CustomerProductCategoryController::class, 'index']);
Route::get('/product-categories/flat', [CustomerProductCategoryController::class, 'flatIndex']);
Route::get('/product-categories/resolve-path', [CustomerProductCategoryController::class, 'resolvePath']);
Route::get('/product-categories/slug/{slug}', [CustomerProductCategoryController::class, 'showBySlug']);
Route::get('/product-categories/{id}', [CustomerProductCategoryController::class, 'show']);

// Customer files routes (public)
Route::get('/files/{id}/download', [CustomerFileController::class, 'download']);

// Customer products routes (public)
Route::get('/products', [CustomerProductController::class, 'index']);
Route::get('/products/filters', [CustomerProductController::class, 'filters']);
Route::get('/products/by-slugs', [CustomerProductController::class, 'indexBySlugs']);
Route::get('/products/filters-by-slug', [CustomerProductController::class, 'filtersByCategorySlug']);
Route::get('/products/attribute-slugs', [CustomerProductController::class, 'attributeSlugs']);
Route::get('/products/slug/{slug}', [CustomerProductController::class, 'showBySlug']);
Route::get('/products/{id}', [CustomerProductController::class, 'show']);

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

        // Product categories management routes with permissions
        Route::get('product-categories', [AdminProductCategoryController::class, 'index'])->middleware('permission:Read Product Categories');
        Route::get('product-categories/{id}', [AdminProductCategoryController::class, 'show'])->middleware('permission:Read Product Categories');
        Route::post('product-categories', [AdminProductCategoryController::class, 'store'])->middleware('permission:Create Product Category');
        Route::put('product-categories/{id}', [AdminProductCategoryController::class, 'update'])->middleware('permission:Update Product Category');
        Route::patch('product-categories/{id}', [AdminProductCategoryController::class, 'update'])->middleware('permission:Update Product Category');
        Route::delete('product-categories/{id}', [AdminProductCategoryController::class, 'destroy'])->middleware('permission:Delete Product Category');
        Route::post('product-categories/generate-slug', [AdminProductCategoryController::class, 'generateSlug'])->middleware('permission:Create Product Category');

        // Product brands management routes with permissions
        Route::get('product-brands', [AdminProductBrandController::class, 'index'])->middleware('permission:Read Product Brands');
        Route::get('product-brands/{id}', [AdminProductBrandController::class, 'show'])->middleware('permission:Read Product Brands');
        Route::post('product-brands', [AdminProductBrandController::class, 'store'])->middleware('permission:Create Product Brand');
        Route::put('product-brands/{id}', [AdminProductBrandController::class, 'update'])->middleware('permission:Update Product Brand');
        Route::patch('product-brands/{id}', [AdminProductBrandController::class, 'update'])->middleware('permission:Update Product Brand');
        Route::delete('product-brands/{id}', [AdminProductBrandController::class, 'destroy'])->middleware('permission:Delete Product Brand');
        Route::post('product-brands/generate-slug', [AdminProductBrandController::class, 'generateSlug'])->middleware('permission:Create Product Brand');

        // File management routes
        Route::get('files', [AdminFileController::class, 'index']);
        Route::get('files/{id}', [AdminFileController::class, 'show']);
        Route::post('files', [AdminFileController::class, 'store']);
        Route::post('files/bulk-delete', [AdminFileController::class, 'bulkDelete']);
        Route::delete('files/{id}', [AdminFileController::class, 'destroy']);
        Route::get('files/{id}/download', [AdminFileController::class, 'download']);

        // Attributes management routes with permissions
        Route::get('attributes', [AdminAttributeController::class, 'index'])->middleware('permission:Read Attributes');
        Route::get('attributes/{id}', [AdminAttributeController::class, 'show'])->middleware('permission:Read Attributes');
        Route::post('attributes', [AdminAttributeController::class, 'store'])->middleware('permission:Create Attribute');
        Route::put('attributes/{id}', [AdminAttributeController::class, 'update'])->middleware('permission:Update Attribute');
        Route::patch('attributes/{id}', [AdminAttributeController::class, 'update'])->middleware('permission:Update Attribute');
        Route::delete('attributes/{id}', [AdminAttributeController::class, 'destroy'])->middleware('permission:Delete Attribute');
        Route::post('attributes/generate-slug', [AdminAttributeController::class, 'generateSlug'])->middleware('permission:Create Attribute');
        Route::post('attributes/{id}/values', [AdminAttributeController::class, 'addValue'])->middleware('permission:Update Attribute');
        Route::put('attributes/{id}/values/{valueId}', [AdminAttributeController::class, 'updateValue'])->middleware('permission:Update Attribute');
        Route::delete('attributes/{id}/values/{valueId}', [AdminAttributeController::class, 'deleteValue'])->middleware('permission:Update Attribute');

        // Products management routes with permissions
        Route::get('products', [AdminProductController::class, 'index'])->middleware('permission:Read Products');
        Route::get('products/{id}', [AdminProductController::class, 'show'])->middleware('permission:Read Products');
        Route::post('products', [AdminProductController::class, 'store'])->middleware('permission:Create Product');
        Route::put('products/{id}', [AdminProductController::class, 'update'])->middleware('permission:Update Product');
        Route::patch('products/{id}', [AdminProductController::class, 'update'])->middleware('permission:Update Product');
        Route::delete('products/{id}', [AdminProductController::class, 'destroy'])->middleware('permission:Delete Product');
        Route::post('products/generate-slug', [AdminProductController::class, 'generateSlug'])->middleware('permission:Create Product');

        // Product variants routes
        Route::get('products/{id}/variants', [AdminProductController::class, 'getVariants'])->middleware('permission:Read Products');
        Route::post('products/{id}/variants', [AdminProductController::class, 'addVariant'])->middleware('permission:Update Product');
        Route::put('products/{productId}/variants/{variantId}', [AdminProductController::class, 'updateVariant'])->middleware('permission:Update Product');
        Route::delete('products/{productId}/variants/{variantId}', [AdminProductController::class, 'deleteVariant'])->middleware('permission:Update Product');

        // Product attributes management
        Route::post('products/{id}/attributes', [AdminProductController::class, 'syncAttributes'])->middleware('permission:Update Product');

        // Product specifications routes
        Route::get('products/{id}/specifications', [AdminProductController::class, 'getSpecifications'])->middleware('permission:Read Products');
        Route::post('products/{id}/specifications', [AdminProductController::class, 'addSpecification'])->middleware('permission:Update Product');
        Route::put('products/{productId}/specifications/{specificationId}', [AdminProductController::class, 'updateSpecification'])->middleware('permission:Update Product');
        Route::delete('products/{productId}/specifications/{specificationId}', [AdminProductController::class, 'deleteSpecification'])->middleware('permission:Update Product');
        Route::post('products/{id}/specifications/reorder', [AdminProductController::class, 'reorderSpecifications'])->middleware('permission:Update Product');

        // Blog categories management routes with permissions
        Route::get('blog-categories', [AdminBlogCategoryController::class, 'index'])->middleware('permission:Read Blog Categories');
        Route::get('blog-categories/{id}', [AdminBlogCategoryController::class, 'show'])->middleware('permission:Read Blog Categories');
        Route::post('blog-categories', [AdminBlogCategoryController::class, 'store'])->middleware('permission:Create Blog Category');
        Route::put('blog-categories/{id}', [AdminBlogCategoryController::class, 'update'])->middleware('permission:Update Blog Category');
        Route::patch('blog-categories/{id}', [AdminBlogCategoryController::class, 'update'])->middleware('permission:Update Blog Category');
        Route::delete('blog-categories/{id}', [AdminBlogCategoryController::class, 'destroy'])->middleware('permission:Delete Blog Category');
        Route::post('blog-categories/generate-slug', [AdminBlogCategoryController::class, 'generateSlug'])->middleware('permission:Create Blog Category');
        Route::post('blog-categories/reorder', [AdminBlogCategoryController::class, 'reorder'])->middleware('permission:Update Blog Category');

        // Blog posts management routes with permissions
        Route::get('blog-posts', [AdminBlogPostController::class, 'index'])->middleware('permission:Read Blog Posts');
        Route::get('blog-posts/{id}', [AdminBlogPostController::class, 'show'])->middleware('permission:Read Blog Posts');
        Route::post('blog-posts', [AdminBlogPostController::class, 'store'])->middleware('permission:Create Blog Post');
        Route::put('blog-posts/{id}', [AdminBlogPostController::class, 'update'])->middleware('permission:Update Blog Post');
        Route::patch('blog-posts/{id}', [AdminBlogPostController::class, 'update'])->middleware('permission:Update Blog Post');
        Route::delete('blog-posts/{id}', [AdminBlogPostController::class, 'destroy'])->middleware('permission:Delete Blog Post');
        Route::post('blog-posts/generate-slug', [AdminBlogPostController::class, 'generateSlug'])->middleware('permission:Create Blog Post');
        Route::post('blog-posts/{id}/products', [AdminBlogPostController::class, 'syncProducts'])->middleware('permission:Update Blog Post');
    });
});
