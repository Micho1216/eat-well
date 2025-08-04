<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';
    protected $primaryKey = 'orderId'; 

    protected $fillable = [
        'userId',
        'vendorId',
        'totalPrice', 
        'startDate', 
        'endDate', 
        'isCancelled', 
        'provinsi', 
        'kota',
        'kecamatan',
        'kelurahan',
        'kode_pos',
        'jalan',
        'recipient_name', 
        'recipient_phone', 
        'notes',
    ];

    protected $casts = [
        'totalPrice' => 'decimal:2', 
        'startDate' => 'datetime', 
        'endDate' => 'datetime', 
        'isCancelled' => 'boolean', 
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendorId', 'vendorId');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'orderId', 'orderId');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'orderId', 'orderId')->orderBy('updated_at', 'asc');
    }

    public function deliveryStatuses()
    {
        return $this->hasMany(DeliveryStatus::class, 'orderId', 'orderId');
    }

    public function vendorReview()
    {
        return $this->hasOne(VendorReview::class, 'orderId', 'orderId');
    }

    public function getOrderStatus()
    {
        if($this->isCancelled == 1) {
            return 'cancelled';
        } else if (Carbon::now()->greaterThan($this->endDate)){
            return 'finished';
        } else if (Carbon::now()->lessThan($this->startDate)){
            return 'upcoming';
        } else {
            return 'active';
        }
    }
}