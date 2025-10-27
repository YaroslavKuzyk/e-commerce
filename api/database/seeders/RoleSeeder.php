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

        // Об'єднуємо всі permissions
        $allPermissions = array_merge($rolePermissions, $adminUserPermissions);

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
