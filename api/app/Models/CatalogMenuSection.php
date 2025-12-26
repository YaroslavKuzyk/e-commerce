<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CatalogMenuSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_menu_id',
        'column_index',
        'name',
        'link',
        'icon_file_id',
        'sort_order',
    ];

    protected $casts = [
        'catalog_menu_id' => 'integer',
        'column_index' => 'integer',
        'icon_file_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function catalogMenu(): BelongsTo
    {
        return $this->belongsTo(CatalogMenu::class, 'catalog_menu_id');
    }

    public function iconFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'icon_file_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CatalogMenuItem::class)->orderBy('sort_order');
    }

    public function scopeByColumn($query, int $column)
    {
        return $query->where('column_index', $column);
    }
}
