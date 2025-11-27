<?php

namespace App\Models;

use App\Enums\BlogStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'short_description',
        'slug',
        'content',
        'preview_image_id',
        'status',
        'publication_date',
        'blog_category_id',
    ];

    protected $casts = [
        'status' => BlogStatus::class,
        'publication_date' => 'datetime',
        'preview_image_id' => 'integer',
        'blog_category_id' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function previewImage(): BelongsTo
    {
        return $this->belongsTo(File::class, 'preview_image_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'blog_post_products')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function scopePublished($query)
    {
        return $query->where('status', BlogStatus::PUBLISHED)
            ->where('publication_date', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', BlogStatus::DRAFT);
    }
}
