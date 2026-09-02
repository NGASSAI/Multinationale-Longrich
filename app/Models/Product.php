<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'promo_price',
        'stock', 'sku', 'is_active', 'is_featured', 'meta_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProductComment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(ProductLike::class);
    }

    // Utile côté front pour savoir si le produit est en promo
    public function getIsOnSaleAttribute(): bool
    {
        return !is_null($this->promo_price) && $this->promo_price < $this->price;
    }
}
