<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProcessCheckoutRequest extends FormRequest
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
            'vendor_id' => 'required|exists:vendors,vendorId',
            'payment_method_id' => 'required|exists:payment_methods,methodId',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            // 'password' is required only if payment_method_id is 1 (Wellpay)
            'password' => [
                Rule::requiredIf(function () {
                    return $this->input('payment_method_id') == 1; // Assuming 1 is Wellpay methodId
                }),
                'string',
            ],
            'provinsi' => 'required|string|max:255',
            'kota' => 'required|string|max:255',
            'kabupaten' => 'required|string|max:255', // You had 'kabupaten' and 'kota' as required, ensure this is correct based on your address structure.
            'kecamatan' => 'required|string|max:255',
            'kelurahan' => 'required|string|max:255',
            'kode_pos' => 'required|string|digits:5',
            'jalan' => 'required|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'notes' => 'nullable|string|max:255',
        ];
    }

        public function messages(): array
    {
        return [
            'vendor_id.required' => 'Vendor ID is required.',
            'vendor_id.exists' => 'The selected vendor is invalid.',
            'payment_method_id.required' => 'Payment method is required.',
            'payment_method_id.exists' => 'The selected payment method is invalid.',
            'start_date.required' => 'Start date is required.',
            'start_date.date_format' => 'Start date must be in YYYY-MM-DD format.',
            'end_date.required' => 'End date is required.',
            'end_date.date_format' => 'End date must be in YYYY-MM-DD format.',
            'end_date.after_or_equal' => 'End date must be on or after start date.',
            'password.required_if' => 'Password is required for this payment method.',
            'provinsi.required' => 'Province is required.',
            'kota.required' => 'City is required.',
            'kabupaten.required' => 'District is required.',
            'kecamatan.required' => 'Sub-district is required.',
            'kelurahan.required' => 'Village/Kelurahan is required.',
            'kode_pos.required' => 'Postal code is required.',
            'kode_pos.digits' => 'Postal code must be 5 digits.',
            'jalan.required' => 'Street address is required.',
            'recipient_name.required' => 'Recipient name is required.',
            'recipient_phone.required' => 'Recipient phone number is required.',
            'recipient_phone.min' => 'Recipient phone number must be at least 10 digits.',
            'recipient_phone.max' => 'Recipient phone number cannot exceed 15 digits.',
            'recipient_phone.regex' => 'Recipient phone number must contain only numbers.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Example: If you need to cast certain string inputs to integers or booleans before validation
        $this->merge([
            'vendor_id' => (int) $this->input('vendor_id'),
            'payment_method_id' => (int) $this->input('payment_method_id'),
        ]);
    }
}
