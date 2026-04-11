<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'seller_id', 'name', 'slug', 'sku', 'category_id', 'subcategory',
        'price', 'original_price', 'badge', 'description', 'long_description',
        'youtube_video_id',
        'details', 'care', 'fit', 'colors', 'sizes', 'tags',
        'related_ids', 'color1', 'color2', 'icon_class', 'icon_color',
        'in_stock', 'rating', 'reviews',
    ];

    protected $appends = ['url_slug'];

    public function getUrlSlugAttribute(): string
    {
        return Str::slug($this->name) . '_' . $this->id;
    }

    protected $casts = [
        'details'        => 'array',
        'colors'         => 'array',
        'sizes'          => 'array',
        'tags'           => 'array',
        'related_ids'    => 'array',
        'in_stock'       => 'boolean',
        'price'          => 'decimal:2',
        'original_price' => 'decimal:2',
        'rating'         => 'decimal:1',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(800)
            ->sharpen(10)
            ->nonQueued();
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function seller(): BelongsTo { return $this->belongsTo(User::class, 'seller_id'); }
    public function orderItems(): HasMany { return $this->hasMany(OrderItem::class); }
    public function wishlists(): HasMany { return $this->hasMany(Wishlist::class); }
}
