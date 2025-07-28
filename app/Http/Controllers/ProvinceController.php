<?php

namespace App\Http\Controllers;

use App\Models\Province;

class ProvinceController extends Controller
{
    public function fetchProvinces()
    {
        $provinces = Province::get(['id', 'name']);
        return response()->json($provinces);
    }
}
