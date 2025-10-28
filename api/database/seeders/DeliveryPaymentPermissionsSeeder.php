<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class DeliveryPaymentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Delivery Methods permissions
            [
                'name' => 'Read Delivery Methods',
                'type' => 'read',
                'group' => 'Delivery Methods',
            ],
            [
                'name' => 'Create Delivery Method',
                'type' => 'create',
                'group' => 'Delivery Methods',
            ],
            [
                'name' => 'Update Delivery Method',
                'type' => 'update',
                'group' => 'Delivery Methods',
            ],

            // Payment Methods permissions
            [
                'name' => 'Read Payment Methods',
                'type' => 'read',
                'group' => 'Payment Methods',
            ],
            [
                'name' => 'Create Payment Method',
                'type' => 'create',
                'group' => 'Payment Methods',
            ],
            [
                'name' => 'Update Payment Method',
                'type' => 'update',
                'group' => 'Payment Methods',
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
