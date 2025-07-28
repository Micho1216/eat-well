<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchCitiesRequest;
use Illuminate\Http\Request;
use App\Models\City;

class CityController extends Controller
{
    public function fetchCities(FetchCitiesRequest $request)
    {
        $attrs = $request->validated();
        $provinceId = $attrs['province_id'];

        $cities = City::where('province_id', $provinceId)->get(['id', 'name']);
        return response()->json($cities);
    }
}
