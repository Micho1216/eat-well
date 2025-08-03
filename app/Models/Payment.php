<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payments';
    protected $primaryKey = 'paymentId'; // Matches your migration

    protected $fillable = [
        'methodId', // Matches your migration
        'orderId', // Matches your migration
        'paid_at', // Matches your migration
    ];

    protected $casts = [
        'paid_at' => 'datetime', // Matches your migration
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'methodId', 'methodId');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderId', 'orderId');
    }
}