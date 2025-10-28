<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PaymentMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'name_uk',
        'code',
        'description',
        'description_uk',
        'type',
        'provider',
        'provider_config',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'provider_config' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the delivery methods that support this payment method.
     */
    public function deliveryMethods(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryMethod::class, 'delivery_payment_method')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include online payment methods.
     */
    public function scopeOnline($query)
    {
        return $query->where('type', 'online');
    }

    /**
     * Scope a query to only include cash on delivery payment methods.
     */
    public function scopeCashOnDelivery($query)
    {
        return $query->where('type', 'cash_on_delivery');
    }

    /**
     * Scope a query to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
