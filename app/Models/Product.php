<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'sku', 'category_id', 'subcategory',
        'price', 'original_price', 'badge', 'description',
        'details', 'care', 'fit', 'colors', 'sizes', 'tags',
        'related_ids', 'color1', 'color2', 'icon_class', 'icon_color',
        'in_stock', 'rating', 'reviews',
    ];

    protected $casts = [
        'details'     => 'array',
        'colors'      => 'array',
        'sizes'       => 'array',
        'tags'        => 'array',
        'related_ids' => 'array',
        'in_stock'    => 'boolean',
        'price'       => 'decimal:2',
        'original_price' => 'decimal:2',
        'rating'      => 'decimal:1',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
}
