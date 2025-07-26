<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorPreview extends Model
{
    /** @use HasFactory<\Database\Factories\VendorPreviewFactory> */
    use HasFactory;
    protected $table = 'vendor_previews'; // Nama tabel di database
    protected $primaryKey = 'vendorPreviewId'; // Nama primary key di tabel

    protected $fillable = [
        'vendorId',
        'previewPicturePath',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendorId', 'vendorId');
    }
}
