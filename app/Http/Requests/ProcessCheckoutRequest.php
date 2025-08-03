<?php

namespace App\Http\Requests;

use App\Models\PaymentMethod;
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
            'payment_method_id' => 'required|exists:payment_methods,methodId',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'password' => [
                Rule::requiredIf(function () {
                    return $this->input('payment_method_id') == PaymentMethod::where('name', 'like', 'WellPay')->first()->methodId;
                }),
                'string',
            ],
        ];
    }

        public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Payment method is required.',
            'payment_method_id.exists' => 'The selected payment method is invalid.',
            'start_date.required' => 'Start date is required.',
            'start_date.date_format' => 'Start date must be in YYYY-MM-DD format.',
            'end_date.required' => 'End date is required.',
            'end_date.date_format' => 'End date must be in YYYY-MM-DD format.',
            'end_date.after_or_equal' => 'End date must be on or after start date.',
            'password.required_if' => 'Password is required for this payment method.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'vendor_id' => (int) $this->input('vendor_id'),
            'payment_method_id' => (int) $this->input('payment_method_id'),
        ]);
    }
}
