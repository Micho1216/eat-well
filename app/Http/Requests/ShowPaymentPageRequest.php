<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShowPaymentPageRequest extends FormRequest
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
            // 'address_id' is optional, but if provided, it must exist in the addresses table
            // and belong to the authenticated user.
            'address_id' => [
                'nullable',
                'integer',
            ],
            // Add any other rules for parameters you expect in the request
        ];
    }
}
