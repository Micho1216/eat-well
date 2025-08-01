<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerCredentialStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'profilePath' => ['required', 'mimes:png, jpg, jpeg'],
            'province' => ['required'],
            'city' => ['required'],
            'district' => ['required'],
            'village' => ['required'],
            'phoneNumber' => ['required'],
            'zipCode' => ['required'],
            'address' => ['required'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        logActivity('Failed', 'Updated', "Profile due to validation errors : " . implode($validator->errors()->all()) . " ");

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}
