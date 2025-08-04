<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model;

class RelationCustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'relation_customer_addresses';
    public $incrementing = false; 
    protected $primaryKey = ['customerId', 'addressId']; 

    protected $fillable = [
        'customerId',
        'addressId',
        'recepient_name',
        'recepient_phone',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 'addressId', 'addressId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'customerId', 'userId'); // customerId in relation_customer_addresses links to userId in users
    }
}