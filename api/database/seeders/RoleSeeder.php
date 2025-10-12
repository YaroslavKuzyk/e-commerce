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

        $createdPermissions = [];
        foreach ($rolePermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
            $createdPermissions[] = $permission->id;
        }

        // Створюємо роль SuperAdmin
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'SuperAdmin'],
            ['description' => 'Super Administrator with full access to all permissions']
        );

        // Призначаємо всі permissions для ролей SuperAdmin
        $superAdminRole->permissions()->syncWithoutDetaching($createdPermissions);
    }
}
