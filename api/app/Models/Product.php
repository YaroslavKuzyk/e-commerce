<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'category_id',
        'brand_id',
        'status',
        'base_price',
        'discount_price',
        'discount_percent',
        'discount_starts_at',
        'discount_ends_at',
        'is_clearance',
        'clearance_price',
        'clearance_reason',
        'main_image_file_id',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'brand_id' => 'integer',
        'status' => ProductStatus::class,
        'base_price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',
        'is_clearance' => 'boolean',
        'clearance_price' => 'decimal:2',
        'main_image_file_id' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    public function mainImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'main_image_file_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function defaultVariant(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_default', true);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'product_attributes')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProductStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ProductStatus::DRAFT);
    }

    public function scopeClearance($query)
    {
        return $query->where('is_clearance', true);
    }

    public function scopeWithDiscount($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('discount_price')
              ->orWhereNotNull('discount_percent');
        })->where(function ($q) {
            $q->whereNull('discount_starts_at')
              ->orWhere('discount_starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('discount_ends_at')
              ->orWhere('discount_ends_at', '>=', now());
        });
    }

    /**
     * Get the current active price (considers clearance and discount)
     */
    public function getCurrentPriceAttribute(): float
    {
        // Clearance price takes priority
        if ($this->is_clearance && $this->clearance_price) {
            return (float) $this->clearance_price;
        }

        // Check if discount is active
        if ($this->hasActiveDiscount()) {
            if ($this->discount_price) {
                return (float) $this->discount_price;
            }
            if ($this->discount_percent) {
                return (float) $this->base_price * (1 - $this->discount_percent / 100);
            }
        }

        return (float) $this->base_price;
    }

    /**
     * Check if discount is currently active
     */
    public function hasActiveDiscount(): bool
    {
        if (!$this->discount_price && !$this->discount_percent) {
            return false;
        }

        $now = now();

        if ($this->discount_starts_at && $this->discount_starts_at > $now) {
            return false;
        }

        if ($this->discount_ends_at && $this->discount_ends_at < $now) {
            return false;
        }

        return true;
    }

    /**
     * Get the discount percentage (calculated if only price is set)
     */
    public function getDiscountPercentageAttribute(): ?float
    {
        if ($this->is_clearance && $this->clearance_price) {
            return round((1 - $this->clearance_price / $this->base_price) * 100, 0);
        }

        if (!$this->hasActiveDiscount()) {
            return null;
        }

        if ($this->discount_percent) {
            return (float) $this->discount_percent;
        }

        if ($this->discount_price) {
            return round((1 - $this->discount_price / $this->base_price) * 100, 0);
        }

        return null;
    }
}
