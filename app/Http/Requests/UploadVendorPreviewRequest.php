<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadVendorPreviewRequest extends FormRequest
{
    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'vendorId' => 'required|exists:vendors,vendorId',
        ];
    }

    public function messages()
    {
        return [
            'image.required' => 'Image is required',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be JPG, JPEG, or PNG',
            'vendorId.required' => 'vendorId is required',
            'vendorId.exists' => 'Vendor not found',
        ];
    }
}
