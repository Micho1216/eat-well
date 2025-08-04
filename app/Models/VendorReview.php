<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_reviews';
    protected $primaryKey = 'reviewId';

    protected $fillable = [
        'vendorId',
        'userId',
        'orderId',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendorId', 'vendorId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderId', 'orderId');
    }
}