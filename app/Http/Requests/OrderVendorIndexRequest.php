<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderVendorIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'week' => 'nullable|string|in:current,next',
        ];
    }

    public function messages(): array
    {
        return [
            'week.in' => 'Week must be either "current" or "next".',
        ];
    }
}
