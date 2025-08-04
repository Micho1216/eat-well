<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'packages';
    protected $primaryKey = 'packageId';

    protected $fillable = [
        'categoryId',
        'vendorId',
        'name',
        'menuPDFPath',
        'imgPath',
        'averageCalories',
        'breakfastPrice',
        'lunchPrice',
        'dinnerPrice',
    ];

    protected $casts = [
        'averageCalories' => 'decimal:2',
        'breakfastPrice' => 'decimal:2',
        'lunchPrice' => 'decimal:2',
        'dinnerPrice' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(PackageCategory::class, 'categoryId', 'categoryId');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendorId', 'vendorId');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'packageId', 'packageId');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'packageId', 'packageId');
    }

    public function cuisineTypes()
    {
        return $this->belongsToMany(CuisineType::class, 'package_cuisine', 'packageId', 'cuisineId');
    }

}
