<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerRatingStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === UserRole::Customer;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5',
            ],
            'review' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages()
    {
        $locale = App::getLocale();

        return [
            'rating.min' => $locale === 'id' ? "Rating harus lebih dari 0" : "The rating must be at least 1.",
            'rating.max' => $locale === 'id' ? "Rating maksimal 5" : "The rating may not be greater than 5.",
            'review.max' => $locale === 'id' ? "Review maksimal 1000 karakter" : "The review must below than 1000 characters.",
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        // Forces JSON output instead of redirect
        throw new HttpResponseException(response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
