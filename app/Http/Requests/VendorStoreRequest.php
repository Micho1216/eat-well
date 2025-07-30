<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VendorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg'],
            'name' => ['required', 'string', 'max:255', 'not_regex:/<[^>]*>/'],

            'startBreakfast' => ['nullable', 'date_format:H:i'],
            'closeBreakfast' => ['nullable', 'date_format:H:i', 'after:startBreakfast'],

            'startLunch' => ['nullable', 'date_format:H:i'],
            'closeLunch' => ['nullable', 'date_format:H:i', 'after:startLunch'],

            'startDinner' => ['nullable', 'date_format:H:i'],
            'closeDinner' => ['nullable', 'date_format:H:i', 'after:startDinner'],

            'provinsi' => ['required', 'string'],
            'kota' => ['required', 'string'],
            'kecamatan' => ['required', 'string'],
            'kelurahan' => ['required', 'string'],
            'kode_pos' => ['required', 'string', 'digits:5'],
            'phone_number' => ['required', 'regex:/^08[0-9]{8,13}$/'],
            'jalan' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
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
            'nameInput.not_regex' => 'Name must not contain HTML or script tags.',
        ];
    }

    protected function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $validator->after(function ($validator) {
            // Validasi waktu breakfast
            if ($this->startBreakfast && ($this->startBreakfast < '07:00' || $this->startBreakfast > '10:00')) {
                $validator->errors()->add('startBreakfast', 'Breakfast start time must be between 07:00 and 10:00.');
            }
            if ($this->closeBreakfast && ($this->closeBreakfast < '07:00' || $this->closeBreakfast > '10:00')) {
                $validator->errors()->add('closeBreakfast', 'Breakfast end time must be between 07:00 and 10:00.');
            }

            // Validasi waktu lunch
            if ($this->startLunch && ($this->startLunch < '10:00' || $this->startLunch > '13:00')) {
                $validator->errors()->add('startLunch', 'Lunch start time must be between 10:00 and 13:00.');
            }
            if ($this->closeLunch && ($this->closeLunch < '10:00' || $this->closeLunch > '13:00')) {
                $validator->errors()->add('closeLunch', 'Lunch end time must be between 10:00 and 13:00.');
            }

            // Validasi waktu dinner
            if ($this->startDinner && ($this->startDinner < '14:00' || $this->startDinner > '21:00')) {
                $validator->errors()->add('startDinner', 'Dinner start time must be between 14:00 and 21:00.');
            }
            if ($this->closeDinner && ($this->closeDinner < '14:00' || $this->closeDinner > '21:00')) {
                $validator->errors()->add('closeDinner', 'Dinner end time must be between 14:00 and 21:00.');
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        logActivity('Failed', 'Updated', "Profile due to validation errors : " . implode($validator->errors()->all()));

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}
