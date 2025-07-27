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
            // 'packages' adalah objek, dimana key adalah packageId, dan value adalah objek { id: packageId, items: { mealType: qty } }
            'packages' => 'required|array',
            'packages.*.id' => 'required|integer|exists:packages,packageId', // packageId dari frontend
            'packages.*.items' => 'required|array',
            'packages.*.items.breakfast' => 'nullable|integer|min:0',
            'packages.*.items.lunch' => 'nullable|integer|min:0',
            'packages.*.items.dinner' => 'nullable|integer|min:0',
            'vendor_id' => 'required|integer|exists:vendors,vendorId', // Pastikan vendor_id ada dan valid
        ];
    }

    /**
     * Prepare the data for validation.
     * Opsional: membersihkan atau memformat data sebelum validasi.
     * Untuk 'packages', pastikan key-nya adalah string 'packageId'
     * dan 'items' mengandung key 'breakfast', 'lunch', 'dinner'.
     * Ini akan menyelaraskan dengan apa yang diharapkan di controller.
     */
    protected function prepareForValidation()
    {
        $packages = $this->input('packages', []);
        $formattedPackages = [];

        // Loop melalui paket yang diterima dari frontend
        foreach ($packages as $packageId => $packageData) {
            $formattedPackages[$packageId] = [
                'id' => $packageData['id'] ?? $packageId, // Gunakan id dari data atau key
                'items' => [
                    'breakfast' => (int) ($packageData['items']['breakfast'] ?? 0),
                    'lunch' => (int) ($packageData['items']['lunch'] ?? 0),
                    'dinner' => (int) ($packageData['items']['dinner'] ?? 0),
                ],
            ];
        }

        $this->merge([
            'packages' => $formattedPackages,
            'user_id' => Auth::id(), // Tambahkan user_id dari auth ke request untuk digunakan di controller
            'session_id' => session()->getId(), // Tambahkan session_id
        ]);
    }
}
