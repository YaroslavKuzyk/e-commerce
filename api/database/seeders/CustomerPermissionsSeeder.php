<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class CustomerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        $createdPermissions = [];
        foreach ($customerPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
            $createdPermissions[] = $permission->id;
        }

        // Знаходимо роль SuperAdmin та призначаємо нові permissions
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();

        if ($superAdminRole) {
            $superAdminRole->permissions()->syncWithoutDetaching($createdPermissions);
        }
    }
}
