<?php

namespace App\Models;

use App\Enums\TimeSlot;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_items';
    protected $primaryKey = 'orderItemId'; 

    protected $fillable = [
        'orderId', 
        'packageId', 
        'packageTimeSlot', 
        'price',
        'quantity',
    ];

    protected $casts = [
        'packageTimeSlot' => 'string',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderId', 'orderId');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'packageId', 'packageId')->withTrashed();;
    }
}
