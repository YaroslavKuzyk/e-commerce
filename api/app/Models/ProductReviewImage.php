<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReviewImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'file_id',
        'sort_order',
    ];

    protected $casts = [
        'review_id' => 'integer',
        'file_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function review(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class, 'review_id');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
