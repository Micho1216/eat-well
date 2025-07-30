<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CategoryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === UserRole::Admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'categoryName' => [
                'required',
                Rule::unique('package_categories', 'categoryName')
            ]
        ];
    }

    public function messages()
    {
        $locale = App::getLocale();

        return [
            'categoryName.required' => $locale === 'id' ? "Nama kategori tidak boleh kosong." : "Category name can't be null.",
            'categoryName.unique' => $locale === 'id' ? "Kategori sudah ada." : "The category name has already been taken.",

        ];
    }
}
