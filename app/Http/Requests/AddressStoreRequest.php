<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


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
