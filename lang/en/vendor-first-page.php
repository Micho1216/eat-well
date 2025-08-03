<?php

return[
    'fill_data' => 'Fill Your Data to be an EatWell Vendor',
    'vendor_name'=> 'Vendor Name',
    'delivery_schedule' => 'Delivery Schedule',
    'breakfast'=> 'Breakfast',
    'from' => 'From',
    'until' => 'Until',
    'lunch' => 'Lunch',
    'dinner' => 'Dinner',
    'province' => 'Province',
    'city' => 'City/Town',
    'district' => 'District',
    'village' => 'Municipality/Village',
    'zip_code' => 'Zip Code',
    'phone_number' => 'Phone Number',
    'address' => 'Address',
    'continue' => 'Continue',

    //REQUEST WARNING MESSAGES
    'logo_required' => 'Vendor logo is required',
    'logo_image' => 'Vendor logo must be an image',
    'logo_mimes' => 'Only JPG, JPEG, or PNG file is accepted',

    'name_required' => 'Vendor name is required',

    'closeBreakfast_after' => 'End time must be after start for breakfast',
    'closeLunch_after' => 'End time must be after start for lunch',
    'closeDinner_after' => 'End time must be after start for dinner',

    'province_required' => 'Province is required',
    'city_required' => 'City is required',
    'district_required' => 'District is required',
    'village_required' => 'Village is required',
    
    'zip_code_required' => 'Zip code is required',
    'zip_code_digits' => 'Zip code must be 5 digits',

    'phone_number_required' => 'Phone number is required',
    'phone_number_regex' => 'Phone number must be 10-15 digits',

    'address_required' => 'Address is required',

    'attributes' => [
        'provinsi' => 'province',
        'kota' => 'city',
        'kecamatan' => 'district',
        'kelurahan' => 'village',
        'kode_pos' => 'zip code',
        'phone_number' => 'phone number',
        'jalan' => 'address',
        'logo' => 'logo',
        'name' => 'name',
    ],

    // Custom time validation messages
    'breakfast_time_range' => 'The start breakfast time must be between 06:00 and 10:00',
    'breakfast_close_max_time' => 'The close breakfast time must be at or before 10:00',
    'lunch_time_range' => 'The start lunch time must be between 11:00 and 14:00',
    'lunch_close_max_time' => 'The close lunch time must be at or before 14:00',
    'dinner_time_range' => 'The start dinner time must be between 17:00 and 20:00',
    'dinner_close_max_time' => 'The close dinner time must be at or before 20:00',
];