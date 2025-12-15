<?php

namespace App\Models;

use App\Enums\ProductCategoryStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'subtitle',
        'slug',
        'status',
        'body_description',
        'logo_file_id',
        'menu_image_file_id',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'logo_file_id' => 'integer',
        'menu_image_file_id' => 'integer',
        'status' => ProductCategoryStatus::class,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function menuImageFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'menu_image_file_id');
    }

    public function allSubcategories(): HasMany
    {
        return $this->subcategories()->with('allSubcategories');
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProductCategoryStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ProductCategoryStatus::DRAFT);
    }
}
