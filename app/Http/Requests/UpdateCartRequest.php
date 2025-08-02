<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateCartRequest extends FormRequest
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
            'packages' => 'required|array',
            'packages.*.id' => 'required|integer|exists:packages,packageId',
            'packages.*.items' => 'required|array',
            'packages.*.items.breakfast' => 'nullable|integer|min:0',
            'packages.*.items.lunch' => 'nullable|integer|min:0',
            'packages.*.items.dinner' => 'nullable|integer|min:0',
            'vendor_id' => 'required|integer|exists:vendors,vendorId',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $packages = $this->input('packages', []);
        $formattedPackages = [];

        foreach ($packages as $packageId => $packageData) {
            $formattedPackages[$packageId] = [
                'id' => $packageData['id'] ?? $packageId,
                'items' => [
                    'breakfast' => (int) ($packageData['items']['breakfast'] ?? 0),
                    'lunch' => (int) ($packageData['items']['lunch'] ?? 0),
                    'dinner' => (int) ($packageData['items']['dinner'] ?? 0),
                ],
            ];
        }

        $this->merge([
            'packages' => $formattedPackages,
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ]);
    }
}
