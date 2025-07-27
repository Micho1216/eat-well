<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // pastikan true
    }

    public function rules(): array
    {
        return [
            'week' => 'nullable|string|in:current,next',
        ];
    }
}
