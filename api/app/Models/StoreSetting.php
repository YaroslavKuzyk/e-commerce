<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, mixed $value): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings(): array
    {
        $settings = static::all();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Bulk update settings
     */
    public static function bulkSet(array $settings): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value);
        }
    }

    /**
     * Get default settings structure
     */
    public static function getDefaults(): array
    {
        return [
            'general' => [
                'store_name' => '',
                'favicon_file_id' => null,
                'logo_file_id' => null,
            ],
            'contacts' => [
                'phones' => [],
                'emails' => [],
            ],
            'working_hours' => [
                'weekdays' => ['label' => 'Пн-Пт', 'from' => '09:00', 'to' => '20:00'],
                'weekends' => ['label' => 'Сб-Нд', 'from' => '10:00', 'to' => '20:00'],
            ],
            'footer_working_hours' => [
                'weekdays' => ['label' => 'Пн-Пт', 'from' => '09:00', 'to' => '20:00'],
                'weekends' => ['label' => 'Сб-Нд', 'from' => '10:00', 'to' => '20:00'],
                'phone1' => ['label' => '', 'value' => ''],
                'phone2' => ['label' => '', 'value' => ''],
            ],
            'social_links' => [],
            'slides' => [],
        ];
    }
}
