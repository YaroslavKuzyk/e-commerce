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
    ];

    protected $casts = [
        'product_id' => 'integer',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'status' => ProductVariantStatus::class,
        'is_default' => 'boolean',
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
}
