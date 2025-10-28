<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DeliveryMethod extends Model
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
        'has_api',
        'api_config',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_api' => 'boolean',
        'is_active' => 'boolean',
        'api_config' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Get the payment methods available for this delivery method.
     */
    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class, 'delivery_payment_method')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Get only active payment methods for this delivery method.
     */
    public function activePaymentMethods(): BelongsToMany
    {
        return $this->paymentMethods()->wherePivot('is_active', true);
    }

    /**
     * Scope a query to only include active delivery methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
