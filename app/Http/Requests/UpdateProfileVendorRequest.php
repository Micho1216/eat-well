<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;

class UpdateProfileVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $vendorId = optional(Auth::user()->vendor)->vendorId;

        return [
            'nameInput' => [
                'bail',
                'required',
                'string',
                'max:255',
                'unique:vendors,name,' . $vendorId . ',vendorId',
                'not_regex:/<[^>]*script.*?>.*?<\/[^>]*script.*?>/i',
                'not_regex:/<[^>]+>/i',
            ],
            'telpInput' => 'bail|required|string|max:15|min:10|regex:/^[0-9]+$/',
            'profilePicInput' => 'nullable|image|mimes:jpg,jpeg,png',

            // Time fields
            'breakfast_time_start' => ['nullable', 'date_format:H:i'],
            'breakfast_time_end'   => ['nullable', 'date_format:H:i', 'after:breakfast_time_start'],

            'lunch_time_start' => ['nullable', 'date_format:H:i'],
            'lunch_time_end'   => ['nullable', 'date_format:H:i', 'after:lunch_time_start'],

            'dinner_time_start' => ['nullable', 'date_format:H:i'],
            'dinner_time_end'   => ['nullable', 'date_format:H:i', 'after:dinner_time_start'],
        ];
    }

    public function messages(): array
    {
        $locale = App::getLocale();
        return [

            'nameInput.required' => $locale === 'id' ? 'Nama vendor harus diisi!' : 'Vendor name must be filled!',
            'nameInput.max' => $locale === 'id' ? 'Nama vendor tidak boleh lebih dari 255 karakter.' : 'Vendor name may not be greater than 255 characters.',
            'nameInput.unique' => $locale === 'id' ? 'Nama vendor sudah digunakan!' : 'Vendor name is already taken!',
            'nameInput.not_regex' => $locale === 'id' ? 'Tag HTML atau script tidak diperbolehkan dalam nama vendor.' : 'HTML or script tags are not allowed in the vendor name.',

            'telpInput.required' => $locale === 'id' ? 'Nomor telepon harus diisi!' : 'Telp number must be filled!',
            'telpInput.max' => $locale === 'id' ? 'Nomor telepon tidak boleh lebih dari 15 karakter.' : 'Telp number may not be greater than 15 characters.',
            'telpInput.min' => $locale === 'id' ? 'Nomor telepon minimal terdiri dari 10 karakter.' : 'Telp number must be at least 10 characters.',
            'telpInput.regex' => $locale === 'id' ? 'Nomor telepon harus berupa angka 0 - 9.' : 'Telp number must be number between 0 - 9',

            'profilePicInput.image' => $locale === 'id' ? 'Foto profil harus berupa gambar.' : 'Profile picture must be an image.',
            'profilePicInput.mimes' => $locale === 'id' ? 'Foto profil harus bertipe: jpg, jpeg, png.' : 'Profile picture must be a file of type: jpg, jpeg, png.',

            'breakfast_time_start.date_format' => $locale === 'id' ? 'Waktu mulai sarapan harus dalam format HH:MM.' : 'Breakfast start time must be in the format HH:MM.',
            'breakfast_time_end.date_format' => $locale === 'id' ? 'Waktu selesai sarapan harus dalam format HH:MM.' : 'Breakfast end time must be in the format HH:MM.',
            'breakfast_time_end.after' => $locale === 'id' ? 'Waktu selesai sarapan harus setelah waktu mulai.' : 'Breakfast end time must be after the start time.',

            'lunch_time_start.date_format' => $locale === 'id' ? 'Waktu mulai makan siang harus dalam format HH:MM.' : 'Lunch start time must be in the format HH:MM.',
            'lunch_time_end.date_format' => $locale === 'id' ? 'Waktu selesai makan siang harus dalam format HH:MM.' : 'Lunch end time must be in the format HH:MM.',
            'lunch_time_end.after' => $locale === 'id' ? 'Waktu selesai makan siang harus setelah waktu mulai.' : 'Lunch end time must be after the start time.',

            'dinner_time_start.date_format' => $locale === 'id' ? 'Waktu mulai makan malam harus dalam format HH:MM.' : 'Dinner start time must be in the format HH:MM.',
            'dinner_time_end.date_format' => $locale === 'id' ? 'Waktu selesai makan malam harus dalam format HH:MM.' : 'Dinner end time must be in the format HH:MM.',
            'dinner_time_end.after' => $locale === 'id' ? 'Waktu selesai makan malam harus setelah waktu mulai.' : 'Dinner end time must be after the start time.',


        ];
    }

    protected function withValidator(\Illuminate\Validation\Validator $validator)
    {
        $validator->after(function ($validator) {
            // Breakfast time must be between 07:00 - 10:00
            if ($this->breakfast_time_start && ($this->breakfast_time_start < '07:00' || $this->breakfast_time_start > '10:00')) {
                $validator->errors()->add('breakfast_time_start', 'Breakfast start time must be between 07:00 and 10:00.');
            }
            if ($this->breakfast_time_end && ($this->breakfast_time_end < '07:00' || $this->breakfast_time_end > '10:00')) {
                $validator->errors()->add('breakfast_time_end', 'Breakfast end time must be between 07:00 and 10:00.');
            }

            // Lunch time must be between 10:00 - 13:00
            if ($this->lunch_time_start && ($this->lunch_time_start < '10:00' || $this->lunch_time_start > '13:00')) {
                $validator->errors()->add('lunch_time_start', 'Lunch start time must be between 10:00 and 13:00.');
            }
            if ($this->lunch_time_end && ($this->lunch_time_end < '10:00' || $this->lunch_time_end > '13:00')) {
                $validator->errors()->add('lunch_time_end', 'Lunch end time must be between 10:00 and 13:00.');
            }

            // Dinner time must be between 14:00 - 21:00
            if ($this->dinner_time_start && ($this->dinner_time_start < '14:00' || $this->dinner_time_start > '21:00')) {
                $validator->errors()->add('dinner_time_start', 'Dinner start time must be between 14:00 and 21:00.');
            }
            if ($this->dinner_time_end && ($this->dinner_time_end < '14:00' || $this->dinner_time_end > '21:00')) {
                $validator->errors()->add('dinner_time_end', 'Dinner end time must be between 14:00 and 21:00.');
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        logActivity('Failed', 'Updated', "Vendor Profile, Validation Errors: " . implode(' | ', $validator->errors()->all()));

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}
