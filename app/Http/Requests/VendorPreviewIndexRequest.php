<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorPreviewIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendorId' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'vendorId.required' => 'vendorId is required',
        ];
    }

    public function validationData()
    {
        // agar bisa validasi query param
        return $this->query();
    }
}
