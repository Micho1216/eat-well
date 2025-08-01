<?php

return [
    'title' => 'Address Management',
    'subtitle' => 'Places where healthy foods will be delivered. Have multiple delivery places? This page helps you manage them.',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'alamat' => 'Address',
    'kode_pos' => 'Postal Code',
    'catatan' => 'Notes (Optional)',
    'nama_penerima' => 'Recipient Name',
    'telepon' => 'Phone Number',
    'select' => [
        'provinsi' => 'Select Province',
        'kota' => 'Select City',
        'kecamatan' => 'Select District',
        'kelurahan' => 'Select Sub-district',
    ],
    'validation' => [
        'required' => 'This field is required.',
        'alamat_required' => 'Address is required.',
        'kode_pos_required' => 'Postal code is required.',
        'kode_pos_invalid' => 'Postal code must be 5 digits.',
        'nama_required' => 'Recipient name is required.',
        'telepon_required' => 'Phone number is required.',
        'telepon_invalid' => 'Phone number must be numeric.',
        'telepon_min' => 'Phone number must be at least 10 digits.',
        'telepon_max' => 'Phone number must not exceed 15 digits.',
    ]
];
