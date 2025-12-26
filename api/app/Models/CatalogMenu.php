<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'is_enabled',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'is_enabled' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CatalogMenuSection::class)->orderBy('column_index')->orderBy('sort_order');
    }

    public function sectionsByColumn(int $column): HasMany
    {
        return $this->hasMany(CatalogMenuSection::class)
            ->where('column_index', $column)
            ->orderBy('sort_order');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
