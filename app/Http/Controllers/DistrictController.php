<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchDistrictsRequest;
use App\Models\District;

class DistrictController extends Controller
{
    public function fetchDistricts(FetchDistrictsRequest $request)
    {
        $attrs = $request->validated();
        $cityId = $attrs['city_id'];
        $districts = District::where('city_id', $cityId)->get(['id', 'name']);
        return response()->json($districts);

    }
}
