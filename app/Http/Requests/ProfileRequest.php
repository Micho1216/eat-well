<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return false;
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
            'nameInput' => [
                'required',
                'string',
                'max:255',
                'not_regex:/<[^>]*>/'
            ],

            'dateOfBirth' => 'nullable|date|before:today',
            'gender' => 'required|in:male,female',
            'profilePicInput' => 'nullable|image|mimes:jpg,jpeg,png',
            'nameInput.not_regex' => 'Name must not contain HTML or script tags.',

        ];
    }

    public function messages()
    {
        $locale = App::getLocale();


        return [
            'nameInput.required' => $locale === 'id' ? 'Nama wajib diisi.' : 'Name is required.',
            'nameInput.string' => $locale === 'id' ? 'Nama harus berupa teks.' : 'Name must be a string.',
            'nameInput.max' => $locale === 'id' ? 'Nama tidak boleh lebih dari 255 karakter.' : 'Name must not be more than 255 characters.',

            // dateOfBirth
            'dateOfBirth.date' => $locale === 'id' ? 'Tanggal lahir harus berupa tanggal yang valid.' : 'Date of Birth must be a valid date.',
            'dateOfBirth.before' => $locale === 'id' ? 'Tanggal lahir harus sebelum hari ini.' : 'Date of Birth must be before today.',

            // gender
            'gender.required' => $locale === 'id' ? 'Jenis kelamin wajib diisi.' : 'Gender is required.',
            'gender.in' => $locale === 'id' ? 'Jenis kelamin harus laki-laki atau perempuan.' : 'Gender must be either male or female.',

            // profilePicInput
            'profilePicInput.image' => $locale === 'id' ? 'Foto profil harus berupa gambar.' : 'Profile picture must be an image.',
            'profilePicInput.mimes' => $locale === 'id' ? 'Foto profil harus bertipe: jpg, jpeg, png.' : 'Profile picture must be a file of type: jpg, jpeg, png.',
            'profilePicInput.max' => $locale === 'id' ? 'Foto profil tidak boleh lebih dari 2MB.' : 'Profile picture must not be larger than 2MB.',
        ];
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
