<?php

namespace App\Models;

use App\Enums\AttributeStatus;
use App\Enums\AttributeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'type' => AttributeType::class,
        'status' => AttributeStatus::class,
        'sort_order' => 'integer',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attributes')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function scopePublished($query)
    {
        return $query->where('status', AttributeStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', AttributeStatus::DRAFT);
    }
}
