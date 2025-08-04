<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional tapi sangat direkomendasikan
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuisineType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cuisine_types';
    protected $primaryKey = 'cuisineId';

    protected $fillable = [
        'cuisineName',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'package_cuisine', 'cuisineId', 'packageId');
    }
}
