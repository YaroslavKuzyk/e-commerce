<?php

namespace App\Models;

use App\Enums\ProductBrandStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'body_description',
        'logo_file_id',
        'menu_image_file_id',
    ];

    protected $casts = [
        'logo_file_id' => 'integer',
        'menu_image_file_id' => 'integer',
        'status' => ProductBrandStatus::class,
    ];

    public function logoFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }

    public function menuImageFile(): BelongsTo
    {
        return $this->belongsTo(File::class, 'menu_image_file_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', ProductBrandStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ProductBrandStatus::DRAFT);
    }
}
