<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';
    protected $primaryKey = 'orderId'; // Matches your migration

    protected $fillable = [
        'userId',
        'vendorId',
        'totalPrice', // Matches your migration
        'startDate', // Matches your migration
        'endDate', // Matches your migration
        'isCancelled', // Matches your migration
        'provinsi', // Added based on your migration
        'kota',
        'kabupaten', // Added based on your migration
        'kecamatan',
        'kelurahan',
        'kode_pos',
        'jalan',
        'recipient_name', // Matches your migration
        'recipient_phone', // Matches your migration
        'notes',
    ];

    protected $casts = [
        'totalPrice' => 'decimal:2', // Matches your migration
        'startDate' => 'datetime', // Matches your migration
        'endDate' => 'datetime', // Matches your migration
        'isCancelled' => 'boolean', // Matches your migration
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
}