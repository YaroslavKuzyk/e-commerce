<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_menu_section_id',
        'name',
        'link',
        'sort_order',
    ];

    protected $casts = [
        'catalog_menu_section_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CatalogMenuSection::class, 'catalog_menu_section_id');
    }
}
