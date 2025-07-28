<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\District;
use Illuminate\Support\Str;
use GuzzleHttp\Exception\ConnectException;

class VillageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = 'https://api.binderbyte.com/wilayah/';
        $hierarchy = 'kelurahan';
        $apiKey = '?api_key='.env('BINDER_BYTE_API_KEY');

        $districts = District::all();
        foreach($districts as $district)
        {
            try {
                $districtUrl = '&id_kecamatan='.substr($district->id, 0, 2).'.'.substr($district->id, 2, 2).'.'.substr($district->id, 4, 2);
                $villages = HTTP::get($url.$hierarchy.$apiKey.$districtUrl);
                $villages = $villages->json('value');
        
                foreach($villages as $village)
                {
                    DB::table('villages')->insert([
                        'id' => (int) Str::replace('.', '', $village['id']),
                        'name' => $village['name'], 
                        'district_id' => (int) Str::replace('.', '', $village['id_kecamatan'])
                    ]);
                }
            } catch (ConnectException $e) {
                echo($e);
            }
        }
    }
}
