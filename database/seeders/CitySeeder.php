<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Province;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = 'https://api.binderbyte.com/wilayah/';
        $hierarchy = 'kabupaten';
        $apiKey = '?api_key='.env('BINDER_BYTE_API_KEY');

        $provinces = Province::all();
        foreach($provinces as $province)
        {
            $provinceUrl = '&id_provinsi='.$province->id;
            $cities = HTTP::get($url.$hierarchy.$apiKey.$provinceUrl);
            $cities = $cities->json('value');
    
            foreach($cities as $city)
            {
                DB::table('cities')->insert([
                    'id' => (int) Str::replace('.', '', $city['id']),
                    'name' => $city['name'], 
                    'province_id' => $city['id_provinsi']
                ]);
            }
            
        }
    }
}
