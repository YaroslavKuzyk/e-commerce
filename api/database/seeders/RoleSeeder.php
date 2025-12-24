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

        // Створюємо permissions для управління категоріями продуктів
        $productCategoryPermissions = [
            [
                'name' => 'Read Product Categories',
                'type' => Permission::TYPE_READ,
                'group' => 'Product Categories',
            ],
            [
                'name' => 'Create Product Category',
                'type' => Permission::TYPE_CREATE,
                'group' => 'Product Categories',
            ],
            [
                'name' => 'Update Product Category',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Product Categories',
            ],
            [
                'name' => 'Delete Product Category',
                'type' => Permission::TYPE_DELETE,
                'group' => 'Product Categories',
            ],
        ];

        // Створюємо permissions для управління брендами продуктів
        $productBrandPermissions = [
            [
                'name' => 'Read Product Brands',
                'type' => Permission::TYPE_READ,
                'group' => 'Product Brands',
            ],
            [
                'name' => 'Create Product Brand',
                'type' => Permission::TYPE_CREATE,
                'group' => 'Product Brands',
            ],
            [
                'name' => 'Update Product Brand',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Product Brands',
            ],
            [
                'name' => 'Delete Product Brand',
                'type' => Permission::TYPE_DELETE,
                'group' => 'Product Brands',
            ],
        ];

        // Створюємо permissions для управління атрибутами
        $attributePermissions = [
            [
                'name' => 'Read Attributes',
                'type' => Permission::TYPE_READ,
                'group' => 'Attributes',
            ],
            [
                'name' => 'Create Attribute',
                'type' => Permission::TYPE_CREATE,
                'group' => 'Attributes',
            ],
            [
                'name' => 'Update Attribute',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Attributes',
            ],
            [
                'name' => 'Delete Attribute',
                'type' => Permission::TYPE_DELETE,
                'group' => 'Attributes',
            ],
        ];

        // Створюємо permissions для управління продуктами
        $productPermissions = [
            [
                'name' => 'Read Products',
                'type' => Permission::TYPE_READ,
                'group' => 'Products',
            ],
            [
                'name' => 'Create Product',
                'type' => Permission::TYPE_CREATE,
                'group' => 'Products',
            ],
            [
                'name' => 'Update Product',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Products',
            ],
            [
                'name' => 'Delete Product',
                'type' => Permission::TYPE_DELETE,
                'group' => 'Products',
            ],
        ];

        // Створюємо permissions для управління категоріями блогу
        $blogCategoryPermissions = [
            [
                'name' => 'Read Blog Categories',
                'type' => Permission::TYPE_READ,
                'group' => 'Blog Categories',
            ],
            [
                'name' => 'Create Blog Category',
                'type' => Permission::TYPE_CREATE,
                'group' => 'Blog Categories',
            ],
            [
                'name' => 'Update Blog Category',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Blog Categories',
            ],
            [
                'name' => 'Delete Blog Category',
                'type' => Permission::TYPE_DELETE,
                'group' => 'Blog Categories',
            ],
        ];

        // Створюємо permissions для управління статтями блогу
        $blogPostPermissions = [
            [
                'name' => 'Read Blog Posts',
                'type' => Permission::TYPE_READ,
                'group' => 'Blog Posts',
            ],
            [
                'name' => 'Create Blog Post',
                'type' => Permission::TYPE_CREATE,
                'group' => 'Blog Posts',
            ],
            [
                'name' => 'Update Blog Post',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Blog Posts',
            ],
            [
                'name' => 'Delete Blog Post',
                'type' => Permission::TYPE_DELETE,
                'group' => 'Blog Posts',
            ],
        ];

        // Створюємо permissions для управління відгуками
        $productReviewPermissions = [
            [
                'name' => 'Read Product Reviews',
                'type' => Permission::TYPE_READ,
                'group' => 'Product Reviews',
            ],
            [
                'name' => 'Update Product Review',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Product Reviews',
            ],
            [
                'name' => 'Delete Product Review',
                'type' => Permission::TYPE_DELETE,
                'group' => 'Product Reviews',
            ],
        ];

        // Створюємо permissions для налаштувань магазину
        $storeSettingsPermissions = [
            [
                'name' => 'Read Store Settings',
                'type' => Permission::TYPE_READ,
                'group' => 'Store Settings',
            ],
            [
                'name' => 'Update Store Settings',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Store Settings',
            ],
        ];

        // Створюємо permissions для заявок на передзвінок
        $callbackRequestPermissions = [
            [
                'name' => 'Read Callback Requests',
                'type' => Permission::TYPE_READ,
                'group' => 'Callback Requests',
            ],
            [
                'name' => 'Update Callback Request',
                'type' => Permission::TYPE_UPDATE,
                'group' => 'Callback Requests',
            ],
            [
                'name' => 'Delete Callback Request',
                'type' => Permission::TYPE_DELETE,
                'group' => 'Callback Requests',
            ],
        ];

        // Об'єднуємо всі permissions
        $allPermissions = array_merge(
            $rolePermissions,
            $adminUserPermissions,
            $customerPermissions,
            $deliveryMethodPermissions,
            $paymentMethodPermissions,
            $productCategoryPermissions,
            $productBrandPermissions,
            $attributePermissions,
            $productPermissions,
            $blogCategoryPermissions,
            $blogPostPermissions,
            $productReviewPermissions,
            $storeSettingsPermissions,
            $callbackRequestPermissions
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
