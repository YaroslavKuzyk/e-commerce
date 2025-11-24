<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class ProductCategoryPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'Read Product Categories',
                'type' => 'read',
                'group' => 'Product Categories',
            ],
            [
                'name' => 'Create Product Category',
                'type' => 'create',
                'group' => 'Product Categories',
            ],
            [
                'name' => 'Update Product Category',
                'type' => 'update',
                'group' => 'Product Categories',
            ],
            [
                'name' => 'Delete Product Category',
                'type' => 'delete',
                'group' => 'Product Categories',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
