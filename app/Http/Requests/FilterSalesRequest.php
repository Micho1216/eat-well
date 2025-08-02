<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class FilterSalesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && (Auth::user()->role === UserRole::Vendor || Auth::user()->role === UserRole::Admin);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'startDate' => [
                'nullable',
                'date',
            ],
            'endDate' => [
                'nullable',
                'date',
                'after:startDate',
            ],
        ];
    }

    public function messages()
    {
        $locale = App::getLocale();

        return [
            'endDate.after' => $locale === 'id' ? 'Tanggal akhir harus sesudah tanggal mulai' : 'Invalid date range',
        ];
    }
}
