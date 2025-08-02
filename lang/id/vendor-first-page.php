<?php

return [
    'fill_data' => 'Isi Data Anda untuk menjadi Vendor EatWell',
    'vendor_name' => 'Nama Vendor',
    'delivery_schedule' => 'Jadwal Pengiriman',
    'breakfast'=> 'Sarapan',
    'from' => 'Dari',
    'until' => 'Sampai',
    'lunch' => 'Makan Siang',
    'dinner' => 'Makan Malam',
    'province' => 'Provinsi',
    'city' => 'Kota/Kabupaten',
    'district' => 'Kecamatan',
    'village' => 'Kelurahan/Desa',
    'zip_code'=> 'Kode Pos',
    'phone_number' => 'Nomor Telepon',
    'address' => 'Alamat',
    'continue' => 'Lanjutkan',

    //REQUEST WARNING MESSAGES
    'logo_required' => 'Logo vendor wajib diisi',
    'logo_image' => 'Logo vendor harus berupa gambar',
    'logo_mimes' => 'Hanya file JPG, JPEG, atau PNG yang diterima',

    'name_required' => 'Nama vendor wajib diisi',

    'closeBreakfast_after' => 'Waktu selesai sarapan harus setelah waktu mulai sarapan',
    'closeLunch_after' => 'Waktu selesai makan siang harus setelah waktu mulai makan siang',
    'closeDinner_after' => 'Waktu selesai makan malam harus setelah waktu mulai makan malam',

    'province_required' => 'Provinsi wajib diisi',
    'city_required' => 'Kota wajib diisi',
    'district_required' => 'Kecamatan wajib diisi',
    'village_required' => 'Kelurahan wajib diisi',

    'zip_code_required' => 'Kode pos wajib diisi',
    'zip_code_digits' => 'Kode pos harus terdiri dari :digits digit',

    'phone_number_required' => 'Nomor telepon wajib diisi',
    'phone_number_regex' => 'Nomor telepon harus terdiri dari 10-15 digit',

    'address_required' => 'Alamat wajib diisi',


    'attributes' => [
        'provinsi' => 'provinsi',
        'kota' => 'kota',
        'kecamatan' => 'kecamatan',
        'kelurahan' => 'kelurahan',
        'kode_pos' => 'kode pos',
        'phone_number' => 'nomor telepon',
        'jalan' => 'alamat',
        'logo' => 'logo',
        'name' => 'nama',
    ],
    // Custom time validation messages
    'breakfast_time_range' => 'Waktu mulai sarapan harus antara 06:00 dan 10:00',
    'breakfast_close_max_time' => 'Waktu selesai sarapan harus pada atau sebelum 10:00',
    'lunch_time_range' => 'Waktu mulai makan siang harus antara 11:00 dan 14:00',
    'lunch_close_max_time' => 'Waktu selesai makan siang harus pada atau sebelum 14:00',
    'dinner_time_range' => 'Waktu makan malam harus antara 17:00 dan 20:00',
    'dinner_close_max_time' => 'Waktu selesai makan malam harus pada atau sebelum 20:00',
        
];