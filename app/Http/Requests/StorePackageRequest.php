<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class StorePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoryId' => 'required|integer|exists:package_categories,categoryId',
            'name' => 'required|string|max:255',

            'averageCalories' => 'nullable|numeric|gt:0',
            'breakfastPrice' => 'nullable|numeric|gt:0',
            'lunchPrice' => 'nullable|numeric|gt:0',
            'dinnerPrice' => 'nullable|numeric|gt:0',

            'menuPDFPath' => 'nullable|file|mimes:pdf,csv',
            'imgPath' => 'nullable|image|mimes:jpeg,png,jpg',
        ];
    }

    public function messages(): array
    {
        return [
            'categoryId.required' => 'Package category is required',
            'categoryId.exists' => 'Selected category does not exist in the database',
            'name.required' => 'Package name required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        logActivity('Failed', 'Add', 'Package due to validation errors : ' . implode($validator->errors()->all()));

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}
