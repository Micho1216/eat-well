<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ubah ke false jika mau batasi user
    }

    public function rules(): array
    {
        return [
            'excel_file' => 'required|file|mimes:xlsx,csv|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'excel_file.required' => __('validation.required', ['attribute' => 'file']),
            'excel_file.file'     => __('validation.file', ['attribute' => 'file']),
            'excel_file.mimes'    => __('validation.mimes', ['attribute' => 'file']),
            'excel_file.max'      => __('validation.max.file', ['attribute' => 'file', 'max' => 2048]),
        ];
    }
}
