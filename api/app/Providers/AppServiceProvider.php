<?php

namespace App\Providers;

// Repository Interfaces
use App\Contracts\Repositories\DeliveryMethodRepositoryInterface;
use App\Contracts\Repositories\PaymentMethodRepositoryInterface;
use App\Contracts\Repositories\ProductCategoryRepositoryInterface;
use App\Contracts\Repositories\ProductBrandRepositoryInterface;
use App\Contracts\Repositories\FileRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\AttributeRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;

// Admin Service Interfaces
use App\Contracts\Services\Admin\AuthServiceInterface as AdminAuthServiceInterface;
use App\Contracts\Services\Admin\RoleServiceInterface as AdminRoleServiceInterface;
use App\Contracts\Services\Admin\PermissionServiceInterface as AdminPermissionServiceInterface;
use App\Contracts\Services\Admin\UserServiceInterface as AdminUserServiceInterface;
use App\Contracts\Services\Admin\CustomerServiceInterface as AdminCustomerServiceInterface;
use App\Contracts\Services\Admin\DeliveryMethodServiceInterface as AdminDeliveryMethodServiceInterface;
use App\Contracts\Services\Admin\PaymentMethodServiceInterface as AdminPaymentMethodServiceInterface;
use App\Contracts\Services\Admin\ProductCategoryServiceInterface as AdminProductCategoryServiceInterface;
use App\Contracts\Services\Admin\ProductBrandServiceInterface as AdminProductBrandServiceInterface;
use App\Contracts\Services\Admin\AttributeServiceInterface as AdminAttributeServiceInterface;
use App\Contracts\Services\Admin\ProductServiceInterface as AdminProductServiceInterface;
use App\Contracts\Services\Admin\BlogCategoryServiceInterface as AdminBlogCategoryServiceInterface;
use App\Contracts\Services\Admin\BlogPostServiceInterface as AdminBlogPostServiceInterface;

// Customer Service Interfaces
use App\Contracts\Services\Customer\AuthServiceInterface as CustomerAuthServiceInterface;
use App\Contracts\Services\Customer\ProductCategoryServiceInterface as CustomerProductCategoryServiceInterface;
use App\Contracts\Services\Customer\ProductServiceInterface as CustomerProductServiceInterface;

// Repositories
use App\Repositories\DeliveryMethodRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\ProductCategoryRepository;
use App\Repositories\ProductBrandRepository;
use App\Repositories\FileRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Repositories\AttributeRepository;
use App\Repositories\ProductRepository;

// Admin Services
use App\Services\Admin\AdminDeliveryMethodService;
use App\Services\Admin\AdminPaymentMethodService;
use App\Services\Admin\AdminProductCategoryService;
use App\Services\Admin\AdminProductBrandService;
use App\Services\Admin\AdminAttributeService;
use App\Services\Admin\AdminProductService;
use App\Services\Admin\AdminBlogCategoryService;
use App\Services\Admin\AdminBlogPostService;
use App\Services\Admin\AdminAuthService;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AdminPermissionService;
use App\Services\Admin\AdminUserService;
use App\Services\Admin\AdminCustomerService;

// Customer Services
use App\Services\CustomerAuthService;
use App\Services\CustomerProductCategoryService;
use App\Services\CustomerProductService;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Repository bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(DeliveryMethodRepositoryInterface::class, DeliveryMethodRepository::class);
        $this->app->bind(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->bind(ProductCategoryRepositoryInterface::class, ProductCategoryRepository::class);
        $this->app->bind(ProductBrandRepositoryInterface::class, ProductBrandRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        // Register Admin Service bindings
        $this->app->bind(AdminAuthServiceInterface::class, AdminAuthService::class);
        $this->app->bind(AdminRoleServiceInterface::class, AdminRoleService::class);
        $this->app->bind(AdminPermissionServiceInterface::class, AdminPermissionService::class);
        $this->app->bind(AdminUserServiceInterface::class, AdminUserService::class);
        $this->app->bind(AdminCustomerServiceInterface::class, AdminCustomerService::class);
        $this->app->bind(AdminDeliveryMethodServiceInterface::class, AdminDeliveryMethodService::class);
        $this->app->bind(AdminPaymentMethodServiceInterface::class, AdminPaymentMethodService::class);
        $this->app->bind(AdminProductCategoryServiceInterface::class, AdminProductCategoryService::class);
        $this->app->bind(AdminProductBrandServiceInterface::class, AdminProductBrandService::class);
        $this->app->bind(AdminAttributeServiceInterface::class, AdminAttributeService::class);
        $this->app->bind(AdminProductServiceInterface::class, AdminProductService::class);
        $this->app->bind(AdminBlogCategoryServiceInterface::class, AdminBlogCategoryService::class);
        $this->app->bind(AdminBlogPostServiceInterface::class, AdminBlogPostService::class);

        // Register Customer Service bindings
        $this->app->bind(CustomerAuthServiceInterface::class, CustomerAuthService::class);
        $this->app->bind(CustomerProductCategoryServiceInterface::class, CustomerProductCategoryService::class);
        $this->app->bind(CustomerProductServiceInterface::class, CustomerProductService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
