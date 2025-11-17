<?php

namespace Database\Seeders;

use App\Models\DeliveryMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliveryMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveryMethods = [
            [
                'name' => 'Nova Poshta',
                'name_uk' => 'Нова Пошта',
                'code' => 'nova_poshta',
                'description' => 'Delivery by Nova Poshta courier service',
                'description_uk' => 'Доставка кур\'єрською службою Нова Пошта',
                'has_api' => true,
                'api_config' => [
                    'api_key' => '',
                    'base_url' => 'https://api.novaposhta.ua/v2.0/json/',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ukrposhta',
                'name_uk' => 'Укрпошта',
                'code' => 'ukrposhta',
                'description' => 'Delivery by Ukrposhta postal service',
                'description_uk' => 'Доставка поштовою службою Укрпошта',
                'has_api' => true,
                'api_config' => [
                    'api_key' => '',
                    'base_url' => 'https://www.ukrposhta.ua/api/',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Meest',
                'name_uk' => 'Meest Пошта',
                'code' => 'meest',
                'description' => 'Delivery by Meest postal service',
                'description_uk' => 'Доставка поштовою службою Meest',
                'has_api' => true,
                'api_config' => [
                    'api_key' => '',
                    'base_url' => 'https://api.meest.com/',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Self Pickup',
                'name_uk' => 'Самовивіз',
                'code' => 'self_pickup',
                'description' => 'Pickup from store location',
                'description_uk' => 'Самостійний вивіз з магазину',
                'has_api' => false,
                'api_config' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($deliveryMethods as $method) {
            DeliveryMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
