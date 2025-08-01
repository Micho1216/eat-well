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

    'close_breakfast_after' => 'Waktu selesai sarapan harus setelah waktu mulai sarapan',
    'close_lunch_after' => 'Waktu selesai makan siang harus setelah waktu mulai makan siang',
    'close_dinner_after' => 'Waktu selesai makan malam harus setelah waktu mulai makan malam',

    'province_required' => 'Provinsi wajib diisi',
    'city_required' => 'Kota wajib diisi',
    'district_required' => 'Kecamatan wajib diisi',
    'village_required' => 'Kelurahan wajib diisi',

    'zip_code_required' => 'Kode pos wajib diisi',
    'zip_code_digits' => 'Kode pos harus terdiri dari :digits digit',

    'phone_number_required' => 'Nomor telepon wajib diisi',
    'phone_number_regex' => 'Nomor telepon harus diawali dengan "08" dan terdiri dari 10-15 digit',

    'address_required' => 'Alamat wajib diisi',

    // Custom time validation messages
    'time_invalid_format' => ':attribute bukan format waktu yang valid.',
    'breakfast_time_range' => ':attribute harus antara 00:00 dan 10:29',
    'breakfast_close_max_time' => ':attribute harus pada atau sebelum 10:30',
    'lunch_time_range' => ':attribute harus antara 10:31 dan 14:59',
    'lunch_close_max_time' => ':attribute harus pada atau sebelum 15:00',
    'dinner_time_range' => ':attribute harus antara 15:01 dan 23:58',
    'dinner_close_max_time' => ':attribute harus pada atau sebelum 23:59',


    'attributes' => [
        'startBreakfast' => 'waktu mulai sarapan',
        'closeBreakfast' => 'waktu selesai sarapan',
        'startLunch' => 'waktu mulai makan siang',
        'closeLunch' => 'waktu selesai makan siang',
        'startDinner' => 'waktu mulai makan malam',
        'closeDinner' => 'waktu selesai makan malam',
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
        
];