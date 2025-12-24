<?php

namespace App\Models;

use App\Enums\ProductVariantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'slug',
        'name',
        'price',
        'stock',
        'status',
        'is_default',
        'override_pricing',
        'discount_price',
        'discount_percent',
        'discount_starts_at',
        'discount_ends_at',
        'is_clearance',
        'clearance_price',
        'clearance_reason',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'status' => ProductVariantStatus::class,
        'is_default' => 'boolean',
        'override_pricing' => 'boolean',
        'discount_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',
        'is_clearance' => 'boolean',
        'clearance_price' => 'decimal:2',
    ];

    protected $appends = [
        'current_price',
        'discount_percentage',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attribute_values')
            ->withTimestamps()
            ->join('attributes', 'attribute_values.attribute_id', '=', 'attributes.id')
            ->orderBy('attributes.sort_order')
            ->orderBy('attribute_values.sort_order')
            ->select('attribute_values.*');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductVariantImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(ProductVariantImage::class)->where('is_primary', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProductVariantStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ProductVariantStatus::DRAFT);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get effective discount settings (from variant or parent product)
     */
    public function getEffectiveDiscountPrice(): ?float
    {
        if ($this->override_pricing) {
            return $this->discount_price ? (float) $this->discount_price : null;
        }
        return $this->product?->discount_price ? (float) $this->product->discount_price : null;
    }

    public function getEffectiveDiscountPercent(): ?float
    {
        if ($this->override_pricing) {
            return $this->discount_percent ? (float) $this->discount_percent : null;
        }
        return $this->product?->discount_percent ? (float) $this->product->discount_percent : null;
    }

    public function getEffectiveDiscountStartsAt(): ?\Carbon\Carbon
    {
        if ($this->override_pricing) {
            return $this->discount_starts_at;
        }
        return $this->product?->discount_starts_at;
    }

    public function getEffectiveDiscountEndsAt(): ?\Carbon\Carbon
    {
        if ($this->override_pricing) {
            return $this->discount_ends_at;
        }
        return $this->product?->discount_ends_at;
    }

    public function getEffectiveIsClearance(): bool
    {
        if ($this->override_pricing) {
            return $this->is_clearance;
        }
        return $this->product?->is_clearance ?? false;
    }

    public function getEffectiveClearancePrice(): ?float
    {
        if ($this->override_pricing) {
            return $this->clearance_price ? (float) $this->clearance_price : null;
        }
        return $this->product?->clearance_price ? (float) $this->product->clearance_price : null;
    }

    public function getEffectiveClearanceReason(): ?string
    {
        if ($this->override_pricing) {
            return $this->clearance_reason;
        }
        return $this->product?->clearance_reason;
    }

    /**
     * Check if variant has an active discount
     */
    public function hasActiveDiscount(): bool
    {
        $discountPrice = $this->getEffectiveDiscountPrice();
        $discountPercent = $this->getEffectiveDiscountPercent();

        if (!$discountPrice && !$discountPercent) {
            return false;
        }

        $now = now();
        $startsAt = $this->getEffectiveDiscountStartsAt();
        $endsAt = $this->getEffectiveDiscountEndsAt();

        if ($startsAt && $startsAt > $now) {
            return false;
        }

        if ($endsAt && $endsAt < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get current price considering discounts and clearance
     */
    public function getCurrentPriceAttribute(): float
    {
        $basePrice = (float) $this->price;

        // Clearance price takes priority
        if ($this->getEffectiveIsClearance() && $this->getEffectiveClearancePrice()) {
            return $this->getEffectiveClearancePrice();
        }

        // Check for active discount
        if ($this->hasActiveDiscount()) {
            $discountPrice = $this->getEffectiveDiscountPrice();
            if ($discountPrice) {
                return $discountPrice;
            }

            $discountPercent = $this->getEffectiveDiscountPercent();
            if ($discountPercent) {
                return $basePrice * (1 - $discountPercent / 100);
            }
        }

        return $basePrice;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute(): ?float
    {
        $basePrice = (float) $this->price;

        if ($basePrice <= 0) {
            return null;
        }

        $currentPrice = $this->current_price;

        if ($currentPrice >= $basePrice) {
            return null;
        }

        return round((($basePrice - $currentPrice) / $basePrice) * 100, 2);
    }
}
