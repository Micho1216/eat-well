<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendors';
    protected $primaryKey = 'vendorId'; 

    protected $fillable = [
        'userId',
        'name', 
        'breakfast_delivery',
        'lunch_delivery',
        'dinner_delivery',
        'logo',
        'phone_number', 
        'rating', 
        'provinsi', 
        'kota',
        'kabupaten', 
        'kecamatan',
        'kelurahan',
        'kode_pos',
        'jalan',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function packages()
    {
        return $this->hasMany(Package::class, 'vendorId', 'vendorId');
    }

    public function vendorReviews()
    {
        return $this->hasMany(VendorReview::class, 'vendorId', 'vendorId');
    }

    public function favoriteVendors()
    {
        return $this->belongsToMany(User::class, 'favorite_vendors', 'vendorId', 'userId')->withTimestamps();
    }

    public function favorited()
    {
        return (bool) FavoriteVendor::where('userId', Auth::id())
            ->where('vendorId', $this->id)
            ->first();
    }

    public function isFavoritedBy($userId)
    {
        return $this->favoriteVendors()->where('userId', $userId)->exists();
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'vendorId', 'vendorId');
    }

    public function carts()
    {
        return $this->hasOne(Cart::class, 'vendorId', 'vendorId');
    }

    public function previews()
    {
        return $this->hasMany(VendorPreview::class, 'vendorId', 'vendorId');
    }
}