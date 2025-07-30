<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class VendorSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role == UserRole::Customer;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'query' => [
                'nullable',
                'string',
                'max:255',
            ],
            'min_price' => [
                'nullable',
                'integer',
            ],
            'max_price' => [
                'nullable',
                'integer',
            ],
            'rating' => [
                'nullable',
                'numeric',
                'min:1',
                'max:5'
            ],
            'category' => [
                'nullable',
                'array',
            ],
            'category.*' => [
                'string'
            ],
        ];
    }
}
