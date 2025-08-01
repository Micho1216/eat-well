<?php

return [
    'title' => 'Manajemen Alamat',
    'description' => 'Tempat di mana makanan sehat akan dikirimkan. Punya beberapa lokasi pengiriman? Halaman ini akan membantu kamu mengatur beberapa alamat.',
    'select_province' => 'Pilih Provinsi',
    'select_city' => 'Pilih Kota',
    'select_district' => 'Pilih Kecamatan',
    'select_subdistrict' => 'Pilih Kelurahan',
    'address' => 'Alamat',
    'zipcode' => 'Kode Pos',
    'note' => 'Catatan (Opsional)',
    'recipient_name' => 'Nama Penerima',
    'recipient_phone' => 'Nomor Telepon',
    'save' => 'Simpan',
    'cancel' => 'Batal',
    'required' => [
        'province' => 'Provinsi tidak boleh kosong.',
        'city' => 'Kota tidak boleh kosong.',
        'district' => 'Kecamatan tidak boleh kosong.',
        'subdistrict' => 'Kelurahan tidak boleh kosong.',
        'address' => 'Alamat tidak boleh kosong.',
        'zipcode' => 'Kode pos tidak boleh kosong.',
        'recipient_name' => 'Nama penerima tidak boleh kosong.',
        'recipient_phone' => 'Nomor telepon tidak boleh kosong.',
    ],
    'validation' => [
        'zipcode_format' => 'Kode pos harus 5 digit angka.',
        'phone_format' => 'Nomor telepon harus angka.',
        'phone_min' => 'Nomor telepon minimal 10 digit.',
        'phone_max' => 'Nomor telepon maksimal 15 digit.',
    ],
];
