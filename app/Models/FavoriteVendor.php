<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;

class FavoriteVendor extends Model
{
    use HasFactory;

    protected $table = 'favorite_vendors';
    public $incrementing = false;
    protected $primaryKey = ['userId', 'vendorId'];

    protected $fillable = [
        'userId',
        'vendorId',
    ];

    protected $casts = [
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
}