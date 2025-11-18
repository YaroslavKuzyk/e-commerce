<?php

namespace App\Providers;

use App\Contracts\DeliveryMethodRepositoryInterface;
use App\Contracts\PaymentMethodRepositoryInterface;
use App\Contracts\FileRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\AdminDeliveryMethodServiceInterface;
use App\Contracts\AdminPaymentMethodServiceInterface;
use App\Contracts\Services\Admin\AdminAuthServiceInterface;
use App\Contracts\Services\Admin\AdminRoleServiceInterface;
use App\Contracts\Services\Admin\AdminPermissionServiceInterface;
use App\Contracts\Services\Admin\AdminUserServiceInterface;
use App\Contracts\Services\Admin\AdminCustomerServiceInterface;
use App\Repositories\DeliveryMethodRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\FileRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Services\Admin\AdminDeliveryMethodService;
use App\Services\Admin\AdminPaymentMethodService;
use App\Services\Admin\AdminAuthService;
use App\Services\Admin\AdminRoleService;
use App\Services\Admin\AdminPermissionService;
use App\Services\Admin\AdminUserService;
use App\Services\Admin\AdminCustomerService;
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
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);

        // Register Admin Service bindings
        $this->app->bind(AdminAuthServiceInterface::class, AdminAuthService::class);
        $this->app->bind(AdminRoleServiceInterface::class, AdminRoleService::class);
        $this->app->bind(AdminPermissionServiceInterface::class, AdminPermissionService::class);
        $this->app->bind(AdminUserServiceInterface::class, AdminUserService::class);
        $this->app->bind(AdminCustomerServiceInterface::class, AdminCustomerService::class);
        $this->app->bind(AdminDeliveryMethodServiceInterface::class, AdminDeliveryMethodService::class);
        $this->app->bind(AdminPaymentMethodServiceInterface::class, AdminPaymentMethodService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
