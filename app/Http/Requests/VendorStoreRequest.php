<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VendorStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'startBreakfast' => [
                'nullable',
                'date_format:H:i'
            ],
            'closeBreakfast' => [
                'nullable',
                'date_format:H:i',
                'after:startBreakfast'
            ],
            'startLunch' => [
                'nullable',
                'date_format:H:i'
            ],
            'closeLunch' => [
                'nullable',
                'date_format:H:i',
                'after:startLunch'
            ],
            'startDinner' => [
                'nullable',
                'date_format:H:i'
            ],
            'closeDinner' => [
                'nullable',
                'date_format:H:i',
                'after:startDinner'
            ],
            'provinsi' => [
                'required',
                'string'
            ],
            'kota' => [
                'required',
                'string'
            ],
            'kecamatan' => [
                'required',
                'string'
            ],
            'kelurahan' => [
                'required',
                'string'
            ],
            'kode_pos' => [
                'required',
                'string',
                'digits:5'
            ],
            'phone_number' => [
                'required',
                'regex:/^08[0-9]{8,13}$/',
                
            ],
            'jalan' => [
                'required',
                'string'
            ],
            //
        ];
    }

    public function messages(): array
    {
        return[
            'logo.required' => 'Vendor logo is required',
            'logo.image' => 'Vendor logo must be an image',
            'logo.mimes' => 'Only JPG, JPEG, or PNG file is accepted',

            'name.required' => 'Vendor name is required',

            'closeBreakfast.after' => 'End time must be after start for breakfast',
            'closeLunch.after' => 'End time must be after start for lunch',
            'closeDinner.after' => 'End time must be after start for dinner',

            'provinsi.required' => 'Province is required',
            'kota.required' => 'City is required',
            'kecamatan.required' => 'District is required',
            'kelurahan.required' => 'Village is required',
            
            'kode_pos.required' => 'Zip code is required',
            'kode_pos.digits' => 'Zip code must be 5 digits',

            'phone_number.required' => 'Phone number is required',
            'phone_number.regex' => 'Phone number must start with "08" and be 10-15 digits',

            'jalan.required' => 'Jalan is required',
        ];
    }
}
