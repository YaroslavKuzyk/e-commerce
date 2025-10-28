<?php

namespace Database\Seeders;

use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class DeliveryPaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Payment Methods first
        $paymentMethods = [
            [
                'name' => 'Cash on Delivery',
                'name_uk' => 'Оплата під час отримання товару',
                'code' => 'cash_on_delivery',
                'description' => 'Pay when you receive your order',
                'description_uk' => 'Оплата при отриманні замовлення',
                'type' => 'cash_on_delivery',
                'provider' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Monopay',
                'name_uk' => 'Monopay',
                'code' => 'monopay',
                'description' => 'Pay online with Monopay',
                'description_uk' => 'Онлайн оплата через Monopay',
                'type' => 'online',
                'provider' => 'monopay',
                'provider_config' => [],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'LiqPay',
                'name_uk' => 'LiqPay',
                'code' => 'liqpay',
                'description' => 'Pay online with LiqPay',
                'description_uk' => 'Онлайн оплата через LiqPay',
                'type' => 'online',
                'provider' => 'liqpay',
                'provider_config' => [],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($paymentMethods as $paymentMethod) {
            PaymentMethod::firstOrCreate(
                ['code' => $paymentMethod['code']],
                $paymentMethod
            );
        }

        // Create Delivery Methods
        $deliveryMethods = [
            [
                'name' => 'Nova Poshta',
                'name_uk' => 'Нова Пошта',
                'code' => 'nova_poshta',
                'description' => 'Pickup from Nova Poshta post office',
                'description_uk' => 'Самовивіз з відділення Нової Пошти',
                'has_api' => true,
                'api_config' => [
                    'api_key' => '',
                    'endpoint' => 'https://api.novaposhta.ua/v2.0/json/',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Ukrposhta',
                'name_uk' => 'Укрпошта',
                'code' => 'ukrposhta',
                'description' => 'Pickup from Ukrposhta post office',
                'description_uk' => 'Самовивіз з відділення Укрпошти',
                'has_api' => true,
                'api_config' => [
                    'api_key' => '',
                    'endpoint' => 'https://www.ukrposhta.ua/ecom/0.0.1/',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Meest Poshta',
                'name_uk' => 'Meest Пошта',
                'code' => 'meest_poshta',
                'description' => 'Pickup from Meest post office',
                'description_uk' => 'Самовивіз з відділення Meest',
                'has_api' => true,
                'api_config' => [
                    'api_key' => '',
                    'endpoint' => 'https://api.meest.com/',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Store Pickup',
                'name_uk' => 'Самовивіз з магазину',
                'code' => 'store_pickup',
                'description' => 'Pickup from our store',
                'description_uk' => 'Самовивіз з нашого магазину',
                'has_api' => false,
                'api_config' => null,
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($deliveryMethods as $deliveryMethodData) {
            $deliveryMethod = DeliveryMethod::firstOrCreate(
                ['code' => $deliveryMethodData['code']],
                $deliveryMethodData
            );

            // Attach all payment methods to each delivery method
            $allPaymentMethods = PaymentMethod::all();
            foreach ($allPaymentMethods as $paymentMethod) {
                $deliveryMethod->paymentMethods()->syncWithoutDetaching([
                    $paymentMethod->id => ['is_active' => true]
                ]);
            }
        }
    }
}
