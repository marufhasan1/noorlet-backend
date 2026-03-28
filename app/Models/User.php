<?php
namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'phone',
        'date_of_birth', 'gender', 'role',
    ];

    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function addresses(): HasMany { return $this->hasMany(Address::class); }
    public function wishlists(): HasMany { return $this->hasMany(Wishlist::class); }
    public function store(): HasOne { return $this->hasOne(Store::class); }
    public function products(): HasMany { return $this->hasMany(Product::class, 'seller_id'); }

    public function isSeller(): bool { return $this->role === 'seller'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
