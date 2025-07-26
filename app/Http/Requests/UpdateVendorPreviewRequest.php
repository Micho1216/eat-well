<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorPreviewRequest extends FormRequest
{
    /**
     * Tentukan apakah user berhak melakukan request ini.
     */
    public function authorize(): bool
    {
        return true; // ubah ke true supaya request bisa diproses
    }

    /**
     * Aturan validasi
     */
    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Custom message jika validasi gagal
     */
    public function messages()
    {
        return [
            'image.required' => 'Image is required',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be JPG, JPEG, or PNG',
            'image.max' => 'Image size must not exceed 2MB',
        ];
    }
}
