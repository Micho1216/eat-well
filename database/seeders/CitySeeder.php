<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Province;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Str;
use Exception;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

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
        $provincesCount = count($provinces);

        $output = new ConsoleOutput();
        $insertProgress = new ProgressBar($output, $provincesCount);
        $insertProgress->setFormat("Inserting cities\n%percent:3s%% [%bar%]   %message%\n");
        $insertProgress->start();
        $startTime = microtime(true);

        $i = 0;
        foreach($provinces as $province)
        {
            try{
                $provinceUrl = '&id_provinsi='.$province->id;
                $cities = HTTP::retry(3, 1000, function (int $attempt, Exception $exception){
                    echo($exception);
                    return true;
                })->get($url.$hierarchy.$apiKey.$provinceUrl);
                
                $cities = $cities->json('value');

                foreach($cities as $city)
                {
                    DB::table('cities')->insert([
                        'id' => (int) Str::replace('.', '', $city['id']),
                        'name' => $city['name'], 
                        'province_id' => $city['id_provinsi']
                    ]);

                }
            } catch(Exception $e)
            {
                echo($e);
            }

            $elapsedTime = microtime(true) - $startTime;
            $remainingTime = ($elapsedTime/($i + 1)) * ($provincesCount - $i -1);
            $insertProgress->setMessage(sprintf('Elapsed: %.2f sec, Remaining: %.2f sec', $elapsedTime, $remainingTime));
            $insertProgress->advance();
            $i += 1;
        }

        $insertProgress->finish();
    }

}
