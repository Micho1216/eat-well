<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchVillagesRequest;
use Illuminate\Http\Request;
use App\Models\Village;

class VillageController extends Controller
{
    public function fetchVillages(FetchVillagesRequest $request)
    {
        $attrs = $request->validated();
        $districtId = $attrs['district_id'];
        $villages = Village::where('district_id', $districtId)->get(['id', 'name']);
        return response()->json($villages);
    }
}
