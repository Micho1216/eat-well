<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = 'https://api.binderbyte.com/wilayah/';
        $hierarchy = 'provinsi';
        $apiKey = '?api_key='.env('BINDER_BYTE_API_KEY');

        $provinces = HTTP::get($url.$hierarchy. $apiKey);
        $provinces = $provinces->json('value');

        foreach($provinces as $province)
        {
            DB::table('provinces')->insert([
                'id' => $province['id'],
                'name' => $province['name']
            ]);
        }
        
    }
}
