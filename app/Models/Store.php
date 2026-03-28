<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'logo', 'banner',
        'email', 'phone', 'address', 'city', 'country', 'status',
        'rating', 'total_sales',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function products(): HasMany { return $this->hasMany(Product::class, 'seller_id', 'user_id'); }
}
