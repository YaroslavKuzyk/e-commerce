<?php

namespace App\Models;

use App\Enums\BlogStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'status' => BlogStatus::class,
        'sort_order' => 'integer',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', BlogStatus::PUBLISHED);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', BlogStatus::DRAFT);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
