<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\City;
use Illuminate\Support\Str;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = 'https://api.binderbyte.com/wilayah/';
        $hierarchy = 'kecamatan';
        $apiKey = '?api_key='.env('BINDER_BYTE_API_KEY');

        $cities = City::all();
        foreach($cities as $city)
        {
            $cityUrl = '&id_kabupaten='.substr($city->id, 0, 2).'.'.substr($city->id, 2);
            $districts = HTTP::get($url.$hierarchy.$apiKey.$cityUrl);
            $districts = $districts->json('value');
    
            foreach($districts as $district)
            {
                DB::table('districts')->insert([
                    'id' => (int) Str::replace('.', '', $district['id']),
                    'name' => $district['name'], 
                    'city_id' => (int) Str::replace('.', '', $district['id_kabupaten'])
                ]);
            }
            
        }
    }
}
