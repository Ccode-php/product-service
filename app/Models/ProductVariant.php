<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'variant',
        'price',
        'stock',
    ];

    protected $appends = ['image_url'];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
