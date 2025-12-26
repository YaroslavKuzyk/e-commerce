<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $fillable = [
        'type',
        'name',
        'name_uk',
        'description',
        'description_uk',
        'data',
        'is_active',
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * System setting types
     */
    public const TYPE_NOVA_POSHTA = 'nova_poshta';
    public const TYPE_UKRPOSHTA = 'ukrposhta';
    public const TYPE_MEEST = 'meest';
    public const TYPE_MONOPAY = 'monopay';
    public const TYPE_LIQPAY = 'liqpay';
    public const TYPE_SMS_CLUB = 'sms_club';
    public const TYPE_TURBOSMS = 'turbosms';

    /**
     * Get setting by type
     */
    public static function getByType(string $type): ?self
    {
        return static::where('type', $type)->first();
    }

    /**
     * Get active setting by type
     */
    public static function getActiveByType(string $type): ?self
    {
        return static::where('type', $type)->where('is_active', true)->first();
    }

    /**
     * Get data field value
     */
    public function getDataField(string $key, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    /**
     * Set data field value
     */
    public function setDataField(string $key, mixed $value): self
    {
        $data = $this->data ?? [];
        data_set($data, $key, $value);
        $this->data = $data;
        return $this;
    }

    /**
     * Get default structure for each type
     */
    public static function getDefaultStructure(string $type): array
    {
        return match ($type) {
            self::TYPE_NOVA_POSHTA => [
                'api_key' => '',
            ],
            self::TYPE_UKRPOSHTA => [
                'bearer_token' => '',
            ],
            self::TYPE_MEEST => [
                'api_key' => '',
                'api_secret' => '',
            ],
            self::TYPE_MONOPAY => [
                'token' => '',
            ],
            self::TYPE_LIQPAY => [
                'public_key' => '',
                'private_key' => '',
            ],
            self::TYPE_SMS_CLUB => [
                'token' => '',
            ],
            self::TYPE_TURBOSMS => [
                'login' => '',
                'password' => '',
            ],
            default => [],
        };
    }

    /**
     * Get all available types with their metadata
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_NOVA_POSHTA => [
                'name' => 'Nova Poshta',
                'name_uk' => 'Нова Пошта',
                'description' => 'Nova Poshta delivery service API',
                'description_uk' => 'API сервісу доставки Нова Пошта',
                'category' => 'delivery',
            ],
            self::TYPE_UKRPOSHTA => [
                'name' => 'Ukrposhta',
                'name_uk' => 'Укрпошта',
                'description' => 'Ukrposhta delivery service API',
                'description_uk' => 'API сервісу доставки Укрпошта',
                'category' => 'delivery',
            ],
            self::TYPE_MEEST => [
                'name' => 'Meest Express',
                'name_uk' => 'Meest Express',
                'description' => 'Meest Express delivery service API',
                'description_uk' => 'API сервісу доставки Meest Express',
                'category' => 'delivery',
            ],
            self::TYPE_MONOPAY => [
                'name' => 'MonoPay',
                'name_uk' => 'MonoPay',
                'description' => 'Monobank payment gateway API',
                'description_uk' => 'API платіжного шлюзу Monobank',
                'category' => 'payment',
            ],
            self::TYPE_LIQPAY => [
                'name' => 'LiqPay',
                'name_uk' => 'LiqPay',
                'description' => 'LiqPay payment gateway API',
                'description_uk' => 'API платіжного шлюзу LiqPay',
                'category' => 'payment',
            ],
            self::TYPE_SMS_CLUB => [
                'name' => 'SMS Club',
                'name_uk' => 'SMS Club',
                'description' => 'SMS Club messaging service API',
                'description_uk' => 'API сервісу SMS розсилок SMS Club',
                'category' => 'sms',
            ],
            self::TYPE_TURBOSMS => [
                'name' => 'TurboSMS',
                'name_uk' => 'TurboSMS',
                'description' => 'TurboSMS messaging service API',
                'description_uk' => 'API сервісу SMS розсилок TurboSMS',
                'category' => 'sms',
            ],
        ];
    }
}
