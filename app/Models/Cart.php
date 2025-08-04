<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart'; 
    protected $primaryKey = 'cartId'; 

    protected $fillable = [
        'userId',
        'vendorId', 
        'totalPrice',
    ];

    protected $casts = [
        'totalPrice' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendorId', 'vendorId');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cartId', 'cartId');
    }
}

