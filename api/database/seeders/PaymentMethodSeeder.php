<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Cash on Delivery',
                'name_uk' => 'Оплата при отриманні',
                'code' => 'cash_on_delivery',
                'description' => 'Pay with cash upon delivery',
                'description_uk' => 'Оплата готівкою при отриманні товару',
                'type' => 'cash_on_delivery',
                'provider' => null,
                'provider_config' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Card on Delivery',
                'name_uk' => 'Оплата карткою при отриманні',
                'code' => 'card_on_delivery',
                'description' => 'Pay with card upon delivery',
                'description_uk' => 'Оплата банківською карткою при отриманні',
                'type' => 'cash_on_delivery',
                'provider' => null,
                'provider_config' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'MonoPay',
                'name_uk' => 'MonoPay',
                'code' => 'monopay',
                'description' => 'Online payment via MonoPay',
                'description_uk' => 'Онлайн оплата через MonoPay',
                'type' => 'online',
                'provider' => 'monopay',
                'provider_config' => [
                    'token' => '',
                    'webhook_url' => '',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'LiqPay',
                'name_uk' => 'LiqPay',
                'code' => 'liqpay',
                'description' => 'Online payment via LiqPay',
                'description_uk' => 'Онлайн оплата через LiqPay',
                'type' => 'online',
                'provider' => 'liqpay',
                'provider_config' => [
                    'public_key' => '',
                    'private_key' => '',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Bank Transfer',
                'name_uk' => 'Банківський переказ',
                'code' => 'bank_transfer',
                'description' => 'Payment via bank transfer',
                'description_uk' => 'Оплата банківським переказом на рахунок',
                'type' => 'cash_on_delivery',
                'provider' => null,
                'provider_config' => null,
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
