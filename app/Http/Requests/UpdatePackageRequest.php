<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'categoryId' => 'required|integer|exists:package_categories,categoryId',

            'breakfastPrice' => 'nullable|decimal:0,2|gte:0',
            'lunchPrice' => 'nullable|decimal:0,2|gte:0',
            'dinnerPrice' => 'nullable|decimal:0,2|gte:0',
            'averageCalories' => 'nullable|decimal:0,2|gte:0',

            'menuPDFPath' => 'nullable|file|mimes:pdf',
            'imgPath' => 'nullable|image|mimes:jpeg,png,jpg',

            'cuisine_types' => 'nullable|array',
            'cuisine_types.*' => 'exists:cuisine_types,cuisineId',
        ];
    }

    public function messages(): array
    {
        return [
            'categoryId.required' => 'Package category is required',
            'categoryId.exists' => 'Selected category is invalid or not found',

            'name.required' => 'Package name required',
            'name.max' => 'Package name maximal 255 characters',

            'breakfastPrice.decimal' => 'Breakfast price must be numeric',
            'breakfastPrice.gte' => 'Breakfast price must be >= 0',

            'lunchPrice.decimal' => 'Lunch price must be numeric',
            'lunchPrice.gte' => 'Lunch price must be >= 0',

            'dinnerPrice.decimal' => 'Dinner price must be numeric',
            'dinnerPrice.gte' => 'Dinner price must be >= 0',
        ];
    }
}
