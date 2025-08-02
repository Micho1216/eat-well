<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;


class AddressStoreRequest extends FormRequest
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
            'provinsi_name' => [ // Tetap validasi nama untuk konsistensi, meskipun tidak digunakan secara langsung untuk penyimpanan
                'required',
                'string',
                'max:255',
            ],
            'kota_name' => [
                'required',
                'string',
                'max:255',
            ],
            'kecamatan_name' => [
                'required',
                'string',
                'max:255',
            ],
            'kelurahan_name' => [
                'required',
                'string',
                'max:255',
            ],
            'jalan' => [
                'required',
                'string',
                'max:255',
            ],
            'kode_pos' => [
                'required',
                'string',
                'digits:5',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:255',
            ],
            'recipient_name' => [
                'required',
                'string',
                'max:100',
            ],
            'recipient_phone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^[0-9]+$/',
            ],
        ];
    }
    public function messages()
{
    $locale = App::getLocale();

    return [
        // provinsi_name
        'provinsi_name.required' => $locale === 'id' ? 'Nama provinsi wajib diisi.' : 'Province name is required.',
        'provinsi_name.string' => $locale === 'id' ? 'Nama provinsi harus berupa teks.' : 'Province name must be a string.',
        'provinsi_name.max' => $locale === 'id' ? 'Nama provinsi tidak boleh lebih dari 255 karakter.' : 'Province name must not be more than 255 characters.',

        // kota_name
        'kota_name.required' => $locale === 'id' ? 'Nama kota wajib diisi.' : 'City name is required.',
        'kota_name.string' => $locale === 'id' ? 'Nama kota harus berupa teks.' : 'City name must be a string.',
        'kota_name.max' => $locale === 'id' ? 'Nama kota tidak boleh lebih dari 255 karakter.' : 'City name must not be more than 255 characters.',

        // kecamatan_name
        'kecamatan_name.required' => $locale === 'id' ? 'Nama kecamatan wajib diisi.' : 'District name is required.',
        'kecamatan_name.string' => $locale === 'id' ? 'Nama kecamatan harus berupa teks.' : 'District name must be a string.',
        'kecamatan_name.max' => $locale === 'id' ? 'Nama kecamatan tidak boleh lebih dari 255 karakter.' : 'District name must not be more than 255 characters.',

        // kelurahan_name
        'kelurahan_name.required' => $locale === 'id' ? 'Nama kelurahan wajib diisi.' : 'Sub-district name is required.',
        'kelurahan_name.string' => $locale === 'id' ? 'Nama kelurahan harus berupa teks.' : 'Sub-district name must be a string.',
        'kelurahan_name.max' => $locale === 'id' ? 'Nama kelurahan tidak boleh lebih dari 255 karakter.' : 'Sub-district name must not be more than 255 characters.',

        // jalan
        'jalan.required' => $locale === 'id' ? 'Nama jalan wajib diisi.' : 'Street is required.',
        'jalan.string' => $locale === 'id' ? 'Nama jalan harus berupa teks.' : 'Street must be a string.',
        'jalan.max' => $locale === 'id' ? 'Nama jalan tidak boleh lebih dari 255 karakter.' : 'Street must not be more than 255 characters.',

        // kode_pos
        'kode_pos.required' => $locale === 'id' ? 'Kode pos wajib diisi.' : 'Postal code is required.',
        'kode_pos.string' => $locale === 'id' ? 'Kode pos harus berupa teks.' : 'Postal code must be a string.',
        'kode_pos.digits' => $locale === 'id' ? 'Kode pos harus terdiri dari 5 digit.' : 'Postal code must be 5 digits.',

        // notes
        'notes.string' => $locale === 'id' ? 'Catatan harus berupa teks.' : 'Notes must be a string.',
        'notes.max' => $locale === 'id' ? 'Catatan tidak boleh lebih dari 255 karakter.' : 'Notes must not be more than 255 characters.',

        // recipient_name
        'recipient_name.required' => $locale === 'id' ? 'Nama penerima wajib diisi.' : 'Recipient name is required.',
        'recipient_name.string' => $locale === 'id' ? 'Nama penerima harus berupa teks.' : 'Recipient name must be a string.',
        'recipient_name.max' => $locale === 'id' ? 'Nama penerima tidak boleh lebih dari 100 karakter.' : 'Recipient name must not be more than 100 characters.',

        // recipient_phone
        'recipient_phone.required' => $locale === 'id' ? 'Nomor telepon penerima wajib diisi.' : 'Recipient phone is required.',
        'recipient_phone.string' => $locale === 'id' ? 'Nomor telepon penerima harus berupa teks.' : 'Recipient phone must be a string.',
        'recipient_phone.min' => $locale === 'id' ? 'Nomor telepon penerima minimal 10 digit.' : 'Recipient phone must be at least 10 digits.',
        'recipient_phone.max' => $locale === 'id' ? 'Nomor telepon penerima maksimal 15 digit.' : 'Recipient phone must not be more than 15 digits.',
        'recipient_phone.regex' => $locale === 'id' ? 'Nomor telepon penerima hanya boleh berisi angka.' : 'Recipient phone must contain only numbers.',
    ];
}


    protected function failedValidation(Validator $validator)
    {
        logActivity('Failed', 'Add', "Address due to validation errors : " . implode($validator->errors()->all()));

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}
