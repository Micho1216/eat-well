<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddressDefaultRequest extends FormRequest
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
        $user = Auth::user();
        $loggedInUserId = $user->userId;
        
        return [
            'address_id' => [
                'required',
                'numeric',
                Rule::exists('addresses', 'addressId')->where(function ($query) use ($loggedInUserId) {
                    $query->where('userId', $loggedInUserId);
                }),
            ],
        ];
    }
}
