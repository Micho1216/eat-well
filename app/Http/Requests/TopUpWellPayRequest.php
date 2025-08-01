<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TopUpWellPayRequest extends FormRequest
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
            'amount' => 'required|integer|min:1000|max:20000000',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Top-up amount is required.',
            'amount.integer' => 'Top-up amount must be a number.',
            'amount.min' => 'The minimum top-up amount is Rp 1.000.',
            'amount.max' => 'The maximum top-up amount is Rp 20.000.000.',
            'password.required' => 'Please enter your password.',
        ];
    }
}
