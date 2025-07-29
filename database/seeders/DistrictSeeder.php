<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\City;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use Exception;

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
        $citiesCount = count($cities);

        $output = new ConsoleOutput();
        $insertProgress = new ProgressBar($output, $citiesCount);
        $insertProgress->setFormat("Inserting districts\n%percent:3s%% [%bar%]   %message%\n");
        $insertProgress->start();
        $startTime = microtime(true);

        $i = 0;
        foreach($cities as $city)
        {
            try{
                $cityUrl = '&id_kabupaten='.substr($city->id, 0, 2).'.'.substr($city->id, 2);
                $districts = HTTP::retry(3, 1000, function(Exception $e){
                    echo($e);
                    return true;
                })->get($url.$hierarchy.$apiKey.$cityUrl);
                $districts = $districts->json('value');
        
                foreach($districts as $district)
                {
                    DB::table('districts')->insert([
                        'id' => (int) Str::replace('.', '', $district['id']),
                        'name' => $district['name'], 
                        'city_id' => (int) Str::replace('.', '', $district['id_kabupaten'])
                    ]);
                }
            } catch(Exception $e){
                echo($e);
            }

            $elapsedTime = microtime(true) - $startTime;
            $remainingTime = ($elapsedTime/($i + 1)) * ($citiesCount - $i -1);
            $insertProgress->setMessage(sprintf('Elapsed: %.2f sec, Remaining: %.2f sec', $elapsedTime, $remainingTime));
            $insertProgress->advance();
            $i += 1;
        }
        $insertProgress->finish();
    }
}
