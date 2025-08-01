<?php

return [
    'title' => 'Address Management',
    'description' => 'Places where healthy foods will be delivered to. Have multiple places you wish food could be delivered? This page will help you manage multiple addresses.',
    'select_province' => 'Select Province',
    'select_city' => 'Select City',
    'select_district' => 'Select District',
    'select_subdistrict' => 'Select Sub-district',
    'address' => 'Address',
    'zipcode' => 'Postal Code',
    'note' => 'Note (Optional)',
    'recipient_name' => 'Recipient Name',
    'recipient_phone' => 'Phone Number',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'required' => [
        'province' => 'Province is required.',
        'city' => 'City is required.',
        'district' => 'District is required.',
        'subdistrict' => 'Sub-district is required.',
        'address' => 'Address is required.',
        'zipcode' => 'Postal code is required.',
        'recipient_name' => 'Recipient name is required.',
        'recipient_phone' => 'Phone number is required.',
    ],
    'validation' => [
        'zipcode_format' => 'Postal code must be 5 digits.',
        'phone_format' => 'Phone number must be numeric.',
        'phone_min' => 'Phone number must be at least 10 digits.',
        'phone_max' => 'Phone number must be at most 15 digits.',
    ],
];
