<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Створюємо CRUD permissions для управління ролями
        $rolePermissions = [
            [
                'name' => 'Read Permissions',
                'type' => Permission::TYPE_READ,
                'group' => 'permissions_management',
            ],
            [
                'name' => 'Create Role',
                'type' => Permission::TYPE_CREATE,
                'group' => 'roles_management',
            ],
            [
                'name' => 'Read Roles',
                'type' => Permission::TYPE_READ,
                'group' => 'roles_management',
            ],
            [
                'name' => 'Update Role',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'roles_management',
            ],
            [
                'name' => 'Delete Role',
                'type' => Permission::TYPE_DELETE,
                'group' => 'roles_management',
            ],
        ];

        // Створюємо CRUD permissions для управління адміністраторами
        $adminUserPermissions = [
            [
                'name' => 'Create Admin User',
                'type' => Permission::TYPE_CREATE,
                'group' => 'admin_users_management',
            ],
            [
                'name' => 'Read Admin Users',
                'type' => Permission::TYPE_READ,
                'group' => 'admin_users_management',
            ],
            [
                'name' => 'Update Admin User',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'admin_users_management',
            ],
            [
                'name' => 'Delete Admin User',
                'type' => Permission::TYPE_DELETE,
                'group' => 'admin_users_management',
            ],
        ];

        // Створюємо CRUD permissions для управління покупцями
        $customerPermissions = [
            [
                'name' => 'Create Customer',
                'type' => Permission::TYPE_CREATE,
                'group' => 'customers_management',
            ],
            [
                'name' => 'Read Customers',
                'type' => Permission::TYPE_READ,
                'group' => 'customers_management',
            ],
            [
                'name' => 'Update Customer',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'customers_management',
            ],
            [
                'name' => 'Delete Customer',
                'type' => Permission::TYPE_DELETE,
                'group' => 'customers_management',
            ],
        ];

        // Створюємо permissions для управління методами доставки
        $deliveryMethodPermissions = [
            [
                'name' => 'Read Delivery Methods',
                'type' => Permission::TYPE_READ,
                'group' => 'Delivery Methods',
            ],
            [
                'name' => 'Create Delivery Method',
                'type' => Permission::TYPE_CREATE,
                'group' => 'Delivery Methods',
            ],
            [
                'name' => 'Update Delivery Method',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Delivery Methods',
            ],
        ];

        // Створюємо permissions для управління методами оплати
        $paymentMethodPermissions = [
            [
                'name' => 'Read Payment Methods',
                'type' => Permission::TYPE_READ,
                'group' => 'Payment Methods',
            ],
            [
                'name' => 'Create Payment Method',
                'type' => Permission::TYPE_CREATE,
                'group' => 'Payment Methods',
            ],
            [
                'name' => 'Update Payment Method',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Payment Methods',
            ],
        ];

        // Об'єднуємо всі permissions
        $allPermissions = array_merge(
            $rolePermissions,
            $adminUserPermissions,
            $customerPermissions,
            $deliveryMethodPermissions,
            $paymentMethodPermissions
        );

        $createdPermissions = [];
        foreach ($allPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
            $createdPermissions[] = $permission->id;
        }

        // Створюємо роль SuperAdmin
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'SuperAdmin']
        );

        // Призначаємо всі permissions для ролей SuperAdmin
        $superAdminRole->permissions()->syncWithoutDetaching($createdPermissions);
    }
}
