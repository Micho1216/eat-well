<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class VendorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation(): void
    {
        $timeFields = [
            'startBreakfast', 'closeBreakfast',
            'startLunch', 'closeLunch',
            'startDinner', 'closeDinner'
        ];

        foreach ($timeFields as $field){
            if($this->has($field) && !is_null($this->input($field))){
                try{
                    $carbonTime = Carbon::parse($this->input($field));

                    $this->merge([
                        $field => $carbonTime->format('H:i'),
                    ]);
                } catch(\Exception $e){

                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'logo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg'
            ],
            'name' => [
                'required',
                'string',
                'max:255'
            ],
            'startBreakfast' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail){
                    if(is_null($value)){
                        return;
                    }

                    $time = Carbon::parse($value);

                    $minTime = Carbon::parse('06:00');
                    $maxTime = Carbon::parse('10:00');

                    if(!$time->greaterThanOrEqualTo($minTime) || !$time->lessThan($maxTime)){
                        $fail(__('vendor-first-page.breakfast_time_range', ['attribute' => $attribute]));
                    }
                },
                
            ],
            'closeBreakfast' => [
                'nullable',
                'date_format:H:i',
                'after:startBreakfast',
                function ($attribute, $value, $fail){
                    if(is_null($value)){
                        return;
                    }

                    $closeTime = Carbon::parse($value);
                    $maxAllowedCloseTime = Carbon::parse('10:00');

                    if(!$closeTime->lessThanOrEqualTo($maxAllowedCloseTime)){
                        $fail(__('vendor-first-page.breakfast_close_max_time', ['attribute' => $attribute]));
                    }
                },
            ],
            'startLunch' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail){
                    if(is_null($value)){
                        return;
                    }
                    $time = Carbon::parse($value);

                    $minTime = Carbon::parse('11:00');
                    $maxTime = Carbon::parse('14:00');

                    if(!$time->greaterThanOrEqualTo($minTime) || !$time->lessThan($maxTime)){
                        $fail(__('vendor-first-page.lunch_time_range', ['attribute' => $attribute]));
                    }
                },
            ],
            'closeLunch' => [
                'nullable',
                'date_format:H:i',
                'after:startLunch',
                function ($attribute, $value, $fail){
                    if(is_null($value)){
                        return;
                    }
                    
                    $closeTime = Carbon::parse($value);
                    $maxAllowedCloseTime = Carbon::parse('14:00');

                    if(!$closeTime->lessThanOrEqualTo($maxAllowedCloseTime)){
                        $fail(__('vendor-first-page.lunch_close_max_time', ['attribute' => $attribute]));
                    }
                },
            ],
            'startDinner' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail){
                    if(is_null($value)){
                        return;
                    }
                    $time = Carbon::parse($value);

                    $minTime = Carbon::parse('17:00');
                    $maxTime = Carbon::parse('20:00');

                    if(!$time->greaterThanOrEqualTo($minTime) || !$time->lessThan($maxTime)){
                        $fail(__('vendor-first-page.dinner_time_range', ['attribute' => $attribute]));
                    }
                    
                },
            ],
            'closeDinner' => [
                'nullable',
                'date_format:H:i',
                'after:startDinner',
                function ($attribute, $value, $fail){
                    if(is_null($value)){
                        return;
                    }
                    $closeTime = Carbon::parse($value);
                    $maxAllowedCloseTime = Carbon::parse('20:00');

                    if(!$closeTime->lessThanOrEqualTo($maxAllowedCloseTime)){
                        $fail(__('vendor-first-page.dinner_close_max_time', ['attribute' => $attribute]));
                    }
                },
            ],
            'provinsi_name' => [
                'required',
                'string', 
                Rule::exists('provinces', 'name'),
            ],
            'kota_name' => [
                'required',
                'string',
                Rule::exists('cities', 'name'),
            ],
            'kecamatan_name' => [
                'required',
                'string',
                Rule::exists('districts', 'name'),
            ],
            'kelurahan_name' => [
                'required',
                'string',
                Rule::exists('villages', 'name'),
            ],
            'kode_pos' => [
                'required',
                'string',
                'digits:5'
            ],
            'phone_number' => [
                'required',
                'regex:/^[0-9]{10,15}$/',
                
            ],
            'jalan' => [
                'required',
                'string'
            ],
            //
        ];
    }

    public function messages(): array
    {
        return[
            'logo.required' =>  __('vendor-first-page.logo_required'),
            'logo.image' => __('vendor-first-page.logo_image'),
            'logo.mimes' => __('vendor-first-page.logo_mimes'),

            'name.required' => __('vendor-first-page.name_required'),

            'closeBreakfast.after' => __('vendor-first-page.closeBreakfast_after'),
            'closeLunch.after' => __('vendor-first-page.closeLunch_after'),
            'closeDinner.after' => __('vendor-first-page.closeDinner_after'),

            'provinsi_name.required' => __('vendor-first-page.province_required'),
            'kota_name.required' => __('vendor-first-page.city_required'),
            'kecamatan_name.required' => __('vendor-first-page.district_required'),
            'kelurahan_name.required' => __('vendor-first-page.village_required'),
            
            'kode_pos.required' => __('vendor-first-page.zip_code_required'),
            'kode_pos.digits' => __('vendor-first-page.zip_code_digits'),

            'phone_number.required' => __('vendor-first-page.phone_number_required'),
            'phone_number.regex' => __('vendor-first-page.phone_number_regex'),

            'jalan.required' => __('vendor-first-page.address_required'),
        ];
    }

        public function attributes(): array
    {
        return [
            'provinsi_name' => __('vendor-first-page.attributes.province'),
            'kota_name' => __('vendor-first-page.attributes.city'),
            'kecamatan_name' => __('vendor-first-page.attributes.district'),
            'kelurahan_name' => __('vendor-first-page.attributes.village'),
            'kode_pos' => __('vendor-first-page.attributes.zip_code'),
            'phone_number' => __('vendor-first-page.attributes.phone_number'),
            'jalan' => __('vendor-first-page.attributes.street'),
            'logo' => __('vendor-first-page.attributes.logo'),
            'name' => __('vendor-first-page.attributes.name'),
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        logActivity('Failed', 'Updated', "Profile due to validation errors : " . implode($validator->errors()->all()));

        throw new HttpResponseException(
            redirect()->back()
                ->withErrors($validator)
                ->withInput()
        );
    }
}
