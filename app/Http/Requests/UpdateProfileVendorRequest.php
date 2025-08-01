<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
            'telpInput' => 'bail|required|string|max:255|starts_with:08',
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
        return [
            'nameInput.required' => 'Vendor name must be filled!',
            'nameInput.max' => 'Vendor name may not be greater than 255 characters.',
            'nameInput.unique' => 'Vendor name is already taken!',
            'nameInput.not_regex' => 'HTML or script tags are not allowed in the vendor name.',

            'telpInput.required' => 'Telp number must be filled!',
            'telpInput.starts_with' => 'Telp number must start with 08',

            'profilePicInput.image' => 'Profile picture must be an image.',
            'profilePicInput.mimes' => 'Profile picture must be a file of type: jpg, jpeg, png.',

            'breakfast_time_start.date_format' => 'Breakfast start time must be in the format HH:MM.',
            'breakfast_time_end.date_format' => 'Breakfast end time must be in the format HH:MM.',
            'breakfast_time_end.after' => 'Breakfast end time must be after the start time.',

            'lunch_time_start.date_format' => 'Lunch start time must be in the format HH:MM.',
            'lunch_time_end.date_format' => 'Lunch end time must be in the format HH:MM.',
            'lunch_time_end.after' => 'Lunch end time must be after the start time.',

            'dinner_time_start.date_format' => 'Dinner start time must be in the format HH:MM.',
            'dinner_time_end.date_format' => 'Dinner end time must be in the format HH:MM.',
            'dinner_time_end.after' => 'Dinner end time must be after the start time.',
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
